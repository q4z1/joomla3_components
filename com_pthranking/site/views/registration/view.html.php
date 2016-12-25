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
class PthRankingViewRegistration extends JViewLegacy
{
	
	protected $submit = false;
	protected $exists = false;
	
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null)
	{
        $user = JFactory::getUser();
        if(!$user->guest){
			$uri = JUri::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$url = $base . JRoute::_('index.php?option=com_pthranking&view=activategame', false);
            header("Location: $url");
			die();
        }
		
		// re-captcha:
		JPluginHelper::importPlugin('captcha');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onInit','dynamic_recaptcha');
			
		$jinput = JFactory::getApplication()->input;
		$this->submit = $jinput->get('submit', false, 'BOOL');
		

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');
 
			return false;
		}

		$app    = JFactory::getApplication();
		$pathway = $app->getPathway();
		$pathway->addItem('Registration', JURI::base() . '/component/pthranking/?view=registration');
		
        parent::display($tpl);
	}
}