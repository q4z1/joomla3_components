<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_pthranking
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');

/**
 * HTML View class for the HelloWorld Component
 *
 * @since  0.0.1
 */
class PthRankingViewPthRanking extends JViewLegacy
{
	/**
	 * Display the Hello World view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null)
	{
		// Assign data to the view
        $layout = $this->getLayout();
		$app    = JFactory::getApplication();
		$pathway = $app->getPathway();
        $jinput = JFactory::getApplication()->input;
        $inputid= $jinput->get('userid',0,'INT');
        $inputname= $jinput->get('username',"",'STRING');
		$pathway->addItem('Ranking - Leaderboard', JURI::base() . '/component/pthranking/?view=pthranking&layout=leaderboard');
        if($layout=="profile"){
			if($inputid > 0){
				$param = "&userid=" . $inputid;
			}elseif($inputname != ""){
				$param = "&username=" . $inputname;
			}
			$pathway->addItem('Ranking - Profile Page', JURI::base() . '/component/pthranking/?view=pthranking&layout=profile'.$param);
			$this->prepareprofile();
		}
        if($layout=="gametable"){
			$pathway->addItem('Ranking - Gametable', '#');
			$this->preparegametable();
		}
 
		// Display the view
		parent::display($tpl);
	}

    private function prepareprofile()
    {
		$this->setModel(JModelLegacy::getInstance('Webservice', 'PthRankingModel'));
		$model = $this->getModel("Webservice");
        $profiledatajson= $this->get('Profile', 'Webservice');
        $profiledata=json_decode($profiledatajson,true);
        $basic = $profiledata["basic"];
        if(count($basic)>0) $this->userexists=true;
        else $this->userexists=false;
        
        if($this->userexists)
        {
			$this->userid = $basic["playerid"];
            $this->username=$basic["username"];
			$this->usernameExt = $this->username;
			$this->avatar = $basic["avatar"];
			// http://pokerth.net/media/kunena/avatars/resized/size144/crypsis/nophoto.png
			$ava = '<img width="144" src="'.JURI::base().'/media/kunena/avatars/resized/size144/crypsis/nophoto.png" alt="no avatar" />';
            if($this->avatar != "." && file_exists(RPT_AVADIR . $this->avatar)){
                $image = RPT_AVADIR . $this->avatar;
                $imageData = base64_encode(file_get_contents($image));
                $src = 'data: '.mime_content_type($image).';base64,'.$imageData;
                $ava = '<img width="200" src="' . $src . '" alt="' . $this->username . '" />';
            }
			
			if($basic["gender"] != "" || $basic['country'] != ""){
				if($basic["gender"] != ""){
					$this->usernameExt .= " (" . $model->getGenderIcon($basic["gender"]);
				}else{
					$this->usernameExt .= " (n/a";
				}
				
				if($basic['country'] != ""){
					$this->usernameExt .= " | ".$model->getFlagTooltip($basic["country"]).")";
				}else{
					$this->usernameExt .= " | n/a)";
				}
			}

            $html="<div class='table-responsive'><table class='table table-striped table-hover table-bordered'>\n";
			
            $html .= "<tr><td>Name:</td><td>" . $basic["username"]."</td>"
					. "<td rowspan='5' style='width: 210px; text-align:center; vertical-align: middle;'>" . $ava . "</td></tr>";
            
            $html .= "<tr><td>Rank:<td>";
            $html .= $basic["rank"]."</td></tr>\n";
			
            $html .= "<tr><td>Final Score:</td><td>";
            $html .= $basic["final_score"]."</td></tr>\n";
			
            $html .= "<tr><td>Player id:</td><td>";
            $html .= $basic["playerid"]."</td></tr>\n";

            $html .= "<tr><td>Games:</td><td>";
            $html .= $basic["season_games"]."</td></tr>\n";
			
			if(array_key_exists("last5", $profiledata) && is_array($profiledata["last5"]) && count($profiledata["last5"]) > 0){
				$html .= "<tr><td>Last 5 game places:</td><td colspan='2'>";
				$html .= implode(", ", $profiledata["last5"]);
				$html .= "</td></tr>\n";
			}

            
            $html .= "<tr><td>Total points:</td><td colspan='2'>";
            $html .= $basic["points_sum"]."</td></tr>\n";
            
            $html .= "<tr><td>Average Points:</td><td colspan='2'>";
            $html .= $basic["average_points"]."</td></tr>\n";
            
//             $html .= "<tr><td>Games last 7 days:</td><td colspan='2'>";
//             $html .= $basic["games_seven_days"]."</td></tr>\n"; // No more needed
            
            $html .="</table></div>\n";
			


			$this->all_seasons = $profiledata["all"];
			//$html .= "<hr /><pre>".var_export($this->all_seasons, true). "</pre><hr />";
			
            $this->basicinfo_html=$html;

            $seasonpie=$profiledata["seasonpie"]["data"]; // 
            $this->seasonpiedata= $this->pietohtml($seasonpie);

			$uri = JUri::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
            $urlprefix= $base ."/component/pthranking/?view=graphics&format=png&layout=piechart&"; // TODO: check if this works
//             $urlprefix= "?option=com_pthranking&view=graphics&format=png&layout=piechart&"; // TODO: use url for real pth
            $seasonurls=$profiledata["seasonpie"]["url"];
            $alltimeurls=$profiledata["alltimepie"]["url"];

            $this->season_pie_pic="<img src=\"$urlprefix".$seasonurls[0]."\" width=120 height=120 alt=\"pie\">\n";
            $this->season_bar_pic="<img src=\"$urlprefix".$seasonurls[1]."\" width=160 height=120 alt=\"bar\">\n";
            $this->alltime_pie_pic="<img src=\"$urlprefix".$alltimeurls[0]."\" width=120 height=120 alt=\"pie\">\n";
            $this->alltime_bar_pic="<img src=\"$urlprefix".$alltimeurls[1]."\" width=160 height=120 alt=\"bar\">\n";

            $alltimepie=$profiledata["alltimepie"]["data"]; // 

            $this->alltimepiedata= $this->pietohtml($alltimepie);
			
			// @XXX: season & alltime data for chart.js
			$this->season_data = array();
			$this->alltime_data = array();
			for($i=0;$i<10;$i++){
				$this->season_data[] = (int)$seasonpie[$i]["count"];
				$this->alltime_data[] = (int)$alltimepie[$i]["count"];
			}
        }

        return;
    }

    function pietohtml($piedata)
    {
        $ret="<div class='table-responsive'><table class='table table-striped table-hover table-bordered'>\n"; // maybe removej
        $row="<tr><td>Place:</td>";
        $key="place";
        foreach($piedata as $entry) $row.="<td>".$entry[$key]."</td>";
        $row.="</tr>\n";
        $ret.=$row;

        $row="<tr><th>Games:</th>";
        $key="count";
        foreach($piedata as $entry) $row.="<td>".$entry[$key]."</td>";
        $row.="</tr>\n";
        $ret.=$row;

        $row="<tr><td>Percent:</td>";
        $key="percent";
        foreach($piedata as $entry) $row.="<td>".$entry[$key]."</td>";
        $row.="</tr>\n";
        $ret.=$row;
        $ret.="</table></div>";
        return $ret;
    }

    function preparegametable()
    {

		$this->setModel(JModelLegacy::getInstance('Webservice', 'PthRankingModel'));
        $datajson= $this->get('gamingtable', 'Webservice');
        $data=json_decode($datajson,true); // assoc
        $table = $data["table"]; // arr
        $this->gamename = $data["gamename"]; // str
        $notfound = $data["notfound"]; // arr

        $this->notfound="";

        if(count($notfound)>0)
        {

            $html_notfound="<h4>Players not found in ranking:<h4>\n<ul>\n";
            foreach($notfound as $player)
            {
                $html_notfound.="<li>".$player."</li>\n";
            }
            $html_notfound .="</ul>";
            $this->notfound=$html_notfound;
        }

        $table_html="<table class='table table-striped table-hover table-bordered tablesorter' id='gameTable'>\n";
        $table_html.="<thead><tr><th>Name</th><th>avg. Points</th><th>games (season)</th>";
        $table_html.="<th>Score</th><th>Rank</th></tr></thead><tbody>\n";
		$uri = JUri::getInstance();
		$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
        $base_url=$base."/component/pthranking/?view=pthranking&layout=profile&userid="; // TODO - change/check if correct
//         $base_url="?option=com_pthranking&view=pthranking&layout=profile&userid="; // supernoob local testing link

//                 var profilelink="/component/pthranking/?view=pthranking&layout=profile&userid="+myList[i]["userid"];
        foreach($table as $entry)
        {
            $url=$base_url.$entry["userid"];
            $table_html.="<tr><td><a href=\"$url\" target=\"_blank\">";
            $table_html.= $entry["username"]."</a></td>\n";
            $table_html.=" <td>".$entry["average_points"]."</td>";
            $table_html.="<td>".$entry["season_games"]."</td>";
            $table_html.="<td>".$entry["final_score"]."</td>";
            $table_html.="<td>".$entry["rank"]."</td></tr>\n";
        }
        $table_html.="</tbody></table>\n";
        $this->rankinginfo=$table_html;
        return ;
    }
}
