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
class PthHelperModelUddeim extends JModelItem
{
	/**
	 * @var string message
	 */
	protected $message;
 
	/**
	 * Get the message
         *
	 * @return  json 	array of user objects for ajax-called userlist in uddeim pm component
	 */
	public function getMsg()
	{
		if (!isset($this->message))
		{
			$jinput = JFactory::getApplication()->input;

			$db    = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('username');
			$query->from('#__users');
			$query->where($db->quoteName('block') . ' = 0');
			$query->order('username ASC');
			$db->setQuery($query);  
			
			$rows = $db->loadObjectList();
			
			$users = array();
			
			foreach($rows as $row){
				$users[] = $row->username;
			}
 
			$this->message = json_encode($users);
			
		}
		return $this->message;
	}
}