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

        }

        return;
    }
}
