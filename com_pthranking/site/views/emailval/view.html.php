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
class PthRankingViewEmailval extends JViewLegacy
{
	
	protected $act_key;
	protected $success = false;
	
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	function display($tpl = null)
	{
		$uri = JUri::getInstance();
		$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$jinput = JFactory::getApplication()->input;
		$this->act_key = $jinput->get('actkey', "", 'ALNUM');
		
		if($this->act_key != ""){
			// validate the activation key - and output success or fail
			$this->success = $this->get("DoValidation");
			if($this->success){
				$this->msg = '<p>Congratulations!</p><p>Your E-Mail address is validated.</p><p>You can now login to the Game and to the <a href="'
					. $base . '">Forum</a> with your login credentials mentioned in the mail you received before.</p><p>Enjoy the game! ;)</p>';
			}else{
				$this->msg = "Error: There is no match with the activation key entered below!";
			}

		}else{
			// nothing to do - just show the validation site with an input field for the activation key
		}
		
		$app    = JFactory::getApplication();
		$pathway = $app->getPathway();
		$pathway->addItem('Email Address Validation', JURI::base() . '/component/pthranking/?view=emailval');
 
		// Display the view
		parent::display($tpl);
	}
}