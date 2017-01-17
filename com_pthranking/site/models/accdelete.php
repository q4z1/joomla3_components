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
 
class PthRankingModelAccDelete extends JModelItem
{
	/**
	 * @var string message
	 */

     // get the other database
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
     
     public function getCheckGameAcc(){
        $return = false;
        $user = JFactory::getUser();
        if($user->guest){
            return $return;
        }
        $email = $user->username;
        $db = $this->mydb();
        $query = $db->getQuery(true);
        $query->select('player_id');
        $query->from('#__player');
        $query->where($db->quoteName('email') . " = ".$db->quote($email) );
        $db->setQuery($query);
        
        $rows = $db->loadObjectList();

        if(is_array($rows) && count($rows) > 0){
			$return = true;
		}
        return $return;
     }
}