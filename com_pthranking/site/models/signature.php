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
 
// @FIXME: this model is obviously not needed
class PthRankingModelSignature extends JModelItem
{
     private function mydb()
     {
        $option = array(); //prevent problems
        $option['driver']   = RDB_DRIVER;
        $option['host']     = RDB_HOST;
        $option['user']     = RDB_USER;
        $option['password'] = RDB_PASS;
        $option['database'] = RDB_DB;
        $option['prefix']   = RDB_PREF;
        $db = JDatabaseDriver::getInstance( $option );
        return($db); // TODO: maybe remember the result
     }
    
	public function getSignature()
	{
      
       // @XXX: here comes the logic: get player stats, create img with specific font, include background image, etc.
       // url will be like this: /component/pthranking/?view=signature&format=raw&username=sp0ck
       $jinput = JFactory::getApplication()->input;
       $username = $jinput->get('username', "", 'STRING');
       $sig = imagecreatefrompng(JPATH_ROOT . '/media/com_pthranking/images/signature/blank.png');
       if($username != ""){
            $player = $this->getSigData($username);
		  //die(var_export($player,true));
            if(is_array($player) && count($player) > 0){
                $sig = imagecreatefrompng(JPATH_ROOT . '/media/com_pthranking/images/signature/sigbg.png');
			$white = ImageColorAllocate ($sig, 255, 255, 255);
			$nickX = 8;
			
			if($player['country_iso'] != ""){
			    $country = imagecreatefrompng(JPATH_ROOT . '/media/flags_iso/'.$player['country_iso'].".png");
			    list($cwidth, $cheight) = getimagesize(JPATH_ROOT . '/media/flags_iso/'.$player['country_iso'].".png");
			    imagecopy($sig, $country, 8, 8, 1, 4, $cwidth-2 , $cheight-8);
			    imagedestroy($country);
			    $nickX = 36;
			}
			
			if($player["avatar_mime"] != ""){
				$ava = null;
				if(file_exists(RPT_AVADIR . $player["avatar_hash"] . "." . $player["avatar_mime"])){
					switch($player["avatar_mime"]){
						
						case("png"):
							$ava = imagecreatefrompng(RPT_AVADIR . $player["avatar_hash"] . "." . $player["avatar_mime"]);
							break;
						case("jpg"):
							$ava = imagecreatefromjpeg(RPT_AVADIR . $player["avatar_hash"] . "." . $player["avatar_mime"]);
							break;
						case("gif"):
							$ava = imagecreatefromgif(RPT_AVADIR . $player["avatar_hash"] . "." . $player["avatar_mime"]);
							break;
						default:
							break;
					}
				}

				if(!is_null($ava)){
					$h = 69;
					list($awidth, $aheight) = getimagesize(RPT_AVADIR . $player["avatar_hash"] . "." . $player["avatar_mime"]);
					$width = (($awidth * $h) / $aheight);
					imagecopyresized($sig, $ava, 500-$width-8, 8, 0, 0, $width , $h, $awidth, $aheight);
					imagedestroy($ava);
				}
			}
			
			ImageTTFText ($sig, 15, 0, $nickX, 23, $white, JPATH_ROOT . '/media/com_pthranking/fonts/Nunito-Bold.ttf', 
			    $player['username']);
			// 1st line average & score
			ImageTTFText ($sig, 12, 0, 75, 48, $white, JPATH_ROOT . '/media/com_pthranking/fonts/Nunito-Bold.ttf', 
			    "Rank: ".$player['rank']." / Average: ".$player['average_points']." / Score: ".$player['final_score']);
			// 2nd line games & won
			ImageTTFText ($sig, 12, 0, 75, 68, $white, JPATH_ROOT . '/media/com_pthranking/fonts/Nunito-Bold.ttf', 
			    "Games: ".$player['season_games']." / Won: ".$player['wins']);
			// footer url
			ImageTTFText ($sig, 12, 0, (500-132-8), 93, $white, JPATH_ROOT . '/media/com_pthranking/fonts/Nunito-Bold.ttf', 
			    "www.pokerth.net");
            }
       }
       return $sig;
	}
	
    private function getSigData($username)
    {
        $db=$this->mydb();

        $query = $db->getQuery(true);
        $query->select('pr.*, rank(pr.final_score,pr.season_games,pr.player_id) AS myrank, p.gender, p.country_iso, p.avatar_hash, p.avatar_mime');
        $query->from('#__player_ranking as pr');
		$query->join('LEFT', '#__player AS p ON p.player_id = pr.player_id');
        $query->where('p.username = '.$db->quote($username));
        $db->setQuery($query);
        $ret=array();
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0)
        {
            $row=$rows[0];
            $ret["final_score"]=sprintf("%.2f %%",max(0.0,($row->final_score)/10000.0));
            $average_points=sprintf("%.2f",max(0.0,($row->average_score)*6.2/1000000.0));
            $ret["average_points"]=$average_points;
            $ret["points_sum"]=$row->points_sum;
            $ret["username"]=$row->username;
            $ret["playerid"]=$row->player_id;
            $ret["season_games"]=$row->season_games;
            $ret["games_seven_days"]=$row->games_seven_days;
			$ret["country_iso"] = $row->country_iso;
			$ret["gender"] = $row->gender;
            $ret["rank"]=$row->myrank;
			$ret["avatar_hash"] = $row->avatar_hash;
			$ret["avatar_mime"] = $row->avatar_mime;
        }
        $query = $db->getQuery(true);
        $query->select('COUNT(place) as wins');
        $query->from('#__game_has_player as pr');
        $query->where('player_idplayer = '.$ret["playerid"]);
	   $query->where('place = 1');
        $db->setQuery($query);
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0)
        {
		$ret["wins"] = $rows[0]->wins;
	   }
        return $ret;
    }

}