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
$document->addScript(JUri::root() . 'media/com_pthranking/js/pthleader.js?tx=20161227_1039');
$document->addStyleSheet(JUri::root() . 'media/com_pthranking/css/pthranking.css?tx=20161220_1858');
$document->addStyleSheet(JUri::root() . 'media/com_pthranking/css/easy-autocomplete.min.css');
$uri = JUri::getInstance();
$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
// $url = $base . JRoute::_('index.php?option=com_pthranking&view=activategame', false);
?>

<div class="rt-flex-container">
    <div class="rt-grid-12">
        <div>
            <h1>Ranking</h1>
            <p>
                <label for="username">Username:</label>
                <input id="username" type="text" name="username" class="inputbox" size="16">
                <button id="btn-search" type="submit" class="btn">Search</button>
            </p>
            <div class="pagination" style="text-align:left;"></div>
        </div>
    </div>
    <div class="rt-grid-12">
        <div>
            <table class="table table-striped table-hover table-bordered" id="ranking_table">
                
            </table>
        </div>
    </div>
    <div class="rt-grid-12">
        <div>
            <div class="pagination" style="text-align:left;"></div>
        </div>
    </div>
    <div class="rt-grid-12">
        <div>
            <hr />
            <h4>Ranking calculation:</h4>
            <ol>
                <li><b>Placement Points:</b><br />
                 1. = 15 | 2. = 9 | 3. = 6 | 4. = 4 | 5. = 3 | 6. = 2 | 7. = 1
                </li>
                <li><b>Formula:</b><br />
                25*(total points)/(10+games)
                </li>
            </ol>
        </div>
    </div>
</div>


