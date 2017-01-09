<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class PthRankingViewSignature extends JViewLegacy
{
    function display($tpl = null)
    {
		$this->sig = $this->get("signature");
 
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');
 
			return false;
		}
		
        parent::display($tpl);
    }
}