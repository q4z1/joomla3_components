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
class PthRankingController extends JControllerLegacy
{
	
	public function webservice() 
	{
		// Set view
		
		// Joomla 2.5
		JRequest::setVar('view', 'Webservice');
		
		// (use JInput in 3.x)
		$this->input->set('view', 'Webservice');

		parent::display();
	}
	
	public function registration() 
	{
		// Set view
		
		// Joomla 2.5
		JRequest::setVar('view', 'Registration');
		
		// (use JInput in 3.x)
		$this->input->set('view', 'Registration');

		parent::display();
	}

    public function pthranking()
    {
        // 
        // dunno if this is the right place
        $view = $this->getView('PthRanking','html'); // TODO: maybe profile instead of html?
        $view->setModel( $this->getModel('Webservice'),true);
        $view->display();

    }
}
