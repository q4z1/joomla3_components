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


$document = JFactory::getDocument();
$document->setMimeEncoding('image/png');


// header("Content-type:image/png");
// $outputimage= TODO
imagepng($this->img);

// TODO: deallocate

?>
