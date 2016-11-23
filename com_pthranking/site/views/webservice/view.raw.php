<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class PthRankingViewWebservice extends JViewLegacy
{
    function display($tpl = null)
    {
		$jinput = JFactory::getApplication()->input;
        $type = $jinput->get('pthtype', "", 'STRING');
		
		if($type != ""){
			$this->msg = $this->get($type);
		}else{
			$this->msg = json_encode(array("status" => "nok", "reason" => "type empty"));
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