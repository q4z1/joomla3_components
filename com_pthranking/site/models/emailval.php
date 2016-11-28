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
            // @XXX: set active to 1
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
			
			// @XXX: create entry in #__player_ranking
			// Create a new query object.
			$query = $db->getQuery(true);
			
			// Insert columns.
			$columns = array(
				'player_id',
				'username',
			);
			 
			// Insert values.
			$values = array(
				$db->quote($player_entry->player_id),
				$db->quote($player_entry->username),
			);
			
			// Prepare the insert query.
			$query
				->insert($db->quoteName('#__player_ranking'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
			 
			// Set the query using our newly populated query object and execute it.
			$db->setQuery($query);
			$res = $db->execute();
            
            // create entry in #__users table
			
			// create a joomla3 encrypted password
			jimport('joomla.user.helper');
			$joomla3password = JUserHelper::hashPassword($player_entry->password);
			
            $db = JFactory::getDBO(); // db object for joomla database
			// Create a new query object.
			$query = $db->getQuery(true);
			
			// Insert columns.
			$columns = array(
				'name',
				'username',
				'email',
				'password',
				'registerDate',
				'params',
			);
			 
			// Insert values.
			$values = array(
				$db->quote($player_entry->username),
				$db->quote($player_entry->username),
				$db->quote($player_entry->email),
				$db->quote($joomla3password),
				$db->quote(date("Y-m-d H:i:s")),
				$db->quote("{}"),
			);
			
			// Prepare the insert query.
			$query
				->insert($db->quoteName('#__users'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));
			 
			// Set the query using our newly populated query object and execute it.
			$db->setQuery($query);
			$res = $db->execute();
			
			if($res){
				$userid = $db->insertid(); // fetch last insert id
				
				// entry in `#__user_usergroup_map` table necessary
				$query = $db->getQuery(true);
				// Insert columns.
				$columns = array(
					'user_id',
					'group_id',
				);
				// Insert values.
				$values = array(
					$userid,
					2,
				);
				// Prepare the insert query.
				$query
					->insert($db->quoteName('#__user_usergroup_map'))
					->columns($db->quoteName($columns))
					->values(implode(',', $values));
				 
				// Set the query using our newly populated query object and execute it.
				$db->setQuery($query);
				$res = $db->execute();

				// create entry in #__kunenan_users table
				$query = $db->getQuery(true);
				
				// Insert columns.
				$columns = array(
					'userid',
					'signature',
					'personalText',
					'ip',
				);
				
				// Insert values.
				$values = array(
					$userid,
					"NULL",
					"NULL",
					$db->quote($_SERVER['REMOTE_ADDR']),
				);
				
				// Prepare the insert query.
				$query
					->insert($db->quoteName('#__kunena_users'))
					->columns($db->quoteName($columns))
					->values(implode(',', $values));
				 
				// Set the query using our newly populated query object and execute it.
				$db->setQuery($query);
				$res = $db->execute();
			}
        }
        return $return;
	}

}