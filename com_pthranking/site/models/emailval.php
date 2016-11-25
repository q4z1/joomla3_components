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
 * HelloWorld Model
 *
 * @since  0.0.1
 */
class PthRankingModelEmailval extends JModelItem
{
	/**
	 * @var string message
	 */
	protected $message;
    
    protected $act_key;

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
     
	public function getDoValidation()
	{
        $return = false;
        $player_entry = null;
        
        // @XXX: have to catch the get param again as $this->act_key is not inherited from view :(
		$jinput = JFactory::getApplication()->input;
		$this->act_key = $jinput->get('actkey', "", 'ALNUM');
        
        $db = $this->mydb();
        $query = $db->getQuery(true);
        $query->select("player_id,username,CAST(AES_DECRYPT(password, '".RDB_SALT."') AS CHAR) as password,email");
        $query->from('#__player');
        $query->where($db->quoteName('act_key') . " = ".$db->quote($this->act_key) );
        $query->where($db->quoteName('active') . " = 0" );
        $db->setQuery($query);
        
        $rows = $db->loadObjectList();
        if(is_array($rows) && count($rows) > 0){
			$return = true;
            $player_entry = $rows[0];
		}
        
        
        if($return === true){
            // @TODO: set active to 1
            $query = $db->getQuery(true);
            // Fields to update.
            $fields = array(
                $db->quoteName('active') . ' = 1',
            );
            // Conditions for which records should be updated.
            $conditions = array(
                $db->quoteName('player_id') . ' = ' . $player_entry->player_id
            );
            $query->update($db->quoteName('#__player'))->set($fields)->where($conditions);
            $db->setQuery($query);
            $result = $db->execute();
            
            // @TODO: create forum account
            
			/*
				CREATE TABLE `j25_users` (
				  `id` int(11) NOT NULL,
				  `name` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
				  `username` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
				  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
				  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
				  `block` tinyint(4) NOT NULL DEFAULT '0',
				  `sendEmail` tinyint(4) DEFAULT '0',
				  `registerDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				  `lastvisitDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
				  `activation` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
				  `params` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
				  `lastResetTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date of last password reset',
				  `resetCount` int(11) NOT NULL DEFAULT '0' COMMENT 'Count of password resets since lastResetTime',
				  `otpKey` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Two factor authentication encrypted keys',
				  `otep` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'One time emergency passwords',
				  `requireReset` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Require user to reset password on next login'
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
			 */
        }

        return $return;
	}

}