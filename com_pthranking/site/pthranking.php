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

// @XXX: db related stuff in one central place now
define('RDB_SALT', "mySalt"); // AES_ENCRYPT Salt
define('RDB_DRIVER', 'mysql'); // Database driver name
define('RDB_HOST', 'localhost'); // Database host name
define('RDB_USER', 'pthrdbuser'); // User for database authentication
define('RDB_PASS', 'BKmTEOUOeRjgiwyP'); // Password for database authentication
define('RDB_DB', 'pokerth_ranking'); // Database name
define('RDB_PREF', ''); // Database table prefix

// Get an instance of the controller prefixed by HelloWorld
$controller = JControllerLegacy::getInstance('PthRanking');
 
// Perform the Request task
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));
 
// Redirect if set by the controller
$controller->redirect();