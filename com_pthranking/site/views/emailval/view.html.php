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
		$jinput = JFactory::getApplication()->input;
		$this->submit = $jinput->get('submit', false, 'BOOL');
		
		if($this->submit){

		}else{

		}
		
        
        
		// Assign data to the view
        
        
		$this->msg = 'PokerTH ranking - registration view - submit = ' .var_export($this->submit, true);
 
		// Display the view
		parent::display($tpl);
	}
}