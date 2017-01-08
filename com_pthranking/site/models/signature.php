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
 
// @FIXME: this model is obviously not needed
class PthRankingModelSignature extends JModelItem
{
	/**
	 * @var string message
	 */
	protected $message;
 

	public function getSignature()
	{
       $sig = JPATH_ROOT . '/media/com_pthranking/images/signature/test.png';
       
       // @XXX: here comes the logic: get player stats, create img with specific font, include background image, etc.
       // url will be like this: /component/pthranking/?view=signature&format=raw&username=sp0ck
       $jinput = JFactory::getApplication()->input;
       $username = $jinput->get('username', "", 'STRING');
       if($username == ""){
            $sig = JPATH_ROOT . '/media/com_pthranking/images/signature/blank.png';
       }
       
       
       return $sig;
	}

}