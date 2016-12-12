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
		$this->msg = 'PokerTH ranking - default view';
        $layout = $this->getLayout();
        if($layout=="profile") $this->prepareprofile();
 
		// Display the view
		parent::display($tpl);
	}

    private function prepareprofile()
    {
        $username=""; // TODO

        $this->testmsg = "hello sun";

		$this->setModel(JModelLegacy::getInstance('Webservice', 'PthRankingModel'));

        $profiledatajson= $this->get('Profile', 'Webservice');
        $profiledata=json_decode($profiledatajson,true);
        $basic = $profiledata["basic"];
        if(count($basic)>0) $this->userexists=true;
        else $this->userexists=false;
        
        if($this->userexists)
        {
            $this->username=$basic["username"];
            $html="<table class='table table-striped table-hover table-bordered'>\n";

            $html .= "<tr><td>Name:</td><td>";
            $html .= $basic["username"]."</td></tr>\n";
            
            $html .= "<tr><td>Player id:</td><td>";
            $html .= $basic["playerid"]."</td></tr>\n";

            $html .= "<tr><td>Games:</td><td>";
            $html .= $basic["season_games"]."</td></tr>\n";
			
			if(array_key_exists("last5", $profiledata) && is_array($profiledata["last5"]) && count($profiledata["last5"]) > 0){
				$html .= "<tr><td>Last 5 game places:</td><td>";
				$html .= implode(", ", $profiledata["last5"]);
				$html .= "</td></tr>\n";
			}

            
            $html .= "<tr><td>Total points:</td><td>";
            $html .= $basic["points_sum"]."</td></tr>\n";
            
            $html .= "<tr><td>Average Points:</td><td>";
            $html .= $basic["average_points"]."</td></tr>\n";
            
            $html .= "<tr><td>Games last 7 days:</td><td>";
            $html .= $basic["games_seven_days"]."</td></tr>\n";
            
            $html .= "<tr><th>Final Score:</th><td>";
            $html .= $basic["final_score"]."</td></tr>\n";
            
            $html .= "<tr><th>Rank:</th><td>";
            $html .= $basic["rank"]."</td></tr>\n";
            
            $html .="</table>\n";

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
        $ret="<table class='table table-striped table-hover table-bordered'>\n"; // maybe removej
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
        $ret.="</table>";
        return $ret;
    }
}
