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
            $db = $this->mydb();

            $query = $db->getQuery(true);
            $query->select('pr.*, p.username, p.country_iso, p.gender');
            $query->from('#__player AS p');
            $query->join('LEFT', '#__player_ranking AS pr ON (pr.player_id = p.player_id)');
            $query->where("p.username = ".$db->quote($username) );
            try{
                $db->setQuery($query);
                $rows = $db->loadObjectList();
            }catch(Exception $e){
                die($e->getMessage());
            }
            if(is_array($rows) && count($rows) > 0){
                $player = $rows[0];
                $sig = imagecreate (500, 100);
                imagesavealpha( $sig, true );
                //imagealphablending( $sig, true );
                $trans_background = imagecolorallocatealpha($sig, 255, 255, 255, 127);
                //imagefill($sig, 0, 0, $trans_background);
                $black = ImageColorAllocate ($sig, 0, 0, 0);
                $white = ImageColorAllocate ($sig, 255, 255, 255);
                imagefill($sig, 0, 0, $black);
                imagecolortransparent($sig , $trans_background );

                $country = imagecreatefrompng(JPATH_ROOT . "/media/flags_iso/".$player->country_iso.".png");
                list($cwidth, $cheight) = getimagesize(JPATH_ROOT . "/media/flags_iso/".$player->country_iso.".png");
                imagecolortransparent($country , $trans_background);
                imagecopymerge($sig, $country, 5, 2, 0, 0, $cwidth , $cheight, 100);
                imagedestroy($country);
                if($player->gender != ""){
                    $g = JPATH_ROOT . "/media/flags_iso/".(($player->gender == "m") ? "male" : "female").".png";
                    $g = JPATH_ROOT . "/media/flags_iso/female.png";
                    $gender = imagecreatefrompng($g);
                    imagecolortransparent($gender , $trans_background);
                    list($gwidth, $gheight) = getimagesize($g);
                    imagecopymerge($sig, $gender, 35, 5, 0, 0, $gwidth , $gheight, 100);
                    imagedestroy($gender);
                }

                ImageTTFText ($sig, 16, 0, 55, 20, $white, JPATH_ROOT . "/media/com_pthranking/fonts/Nunito-Bold.ttf", 
                    $player->username);
                // @TODO: add country flag and gender icon
                ImageTTFText ($sig, 12, 0, 5, 40, $white, JPATH_ROOT . "/media/com_pthranking/fonts/Nunito-Bold.ttf", 
                    "Average: " . $player->average_score . " / Score: " . $player->final_score);

            }
       }
       return $sig;
	}

}