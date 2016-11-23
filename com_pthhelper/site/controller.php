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
 * Hello World Component Controller
 *
 * @since  0.0.1
 */
class PthHelperController extends JControllerLegacy
{
	public function uddeim() 
	{
		// Set view
		
		// Joomla 2.5
		JRequest::setVar('view', 'Uddeim');
		
		// (use JInput in 3.x)
		$this->input->set('view', 'Uddeim');

		parent::display();
	}
}