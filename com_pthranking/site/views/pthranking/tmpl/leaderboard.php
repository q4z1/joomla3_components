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
$document->addScript(JUri::root() . 'media/com_pthranking/js/pthleader.js?tx=20161203_1841');
$document->addStyleSheet(JUri::root() . 'media/com_pthranking/css/pthranking.css');
$uri = JUri::getInstance();
$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
// $url = $base . JRoute::_('index.php?option=com_pthranking&view=activategame', false);
?>
<h1>Ranking</h1>
<div class="rt-flex-container">
    <div class="rt-grid-12">
        <div class="rt-block">
            <p>
                <label for="username">Username:</label>
                <input id="username" type="text" name="username" class="inputbox" size="16">
                <button id="btn-search" type="submit" class="btn">Search</button>
            </p>
            <p id="pagenum"></p>
            <button class="btn" id="but_prev">Previous</button>
            <button class="btn" id="but_next">Next</button>
            <table class="table table-striped table-hover table-bordered" id="ranking_table">
                
            </table>
            </div>
    </div>
</div>


