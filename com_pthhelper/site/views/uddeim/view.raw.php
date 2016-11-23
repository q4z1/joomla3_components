<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class PthHelperViewUddeim extends JViewLegacy
{
    function display($tpl = null)
    {
		// restrict db query output to logged in users
		defined('DS') or define('DS', DIRECTORY_SEPARATOR);
		$app = JFactory::getApplication('site');
		$user = JFactory::getUser();
		$groups = $user->groups;

		if($user->id) {
			$this->msg = $this->get('Msg');
		}else{
			$this->msg = "Not logged in!";
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');
 
			return false;
		}
		
        parent::display($tpl);
    }
}