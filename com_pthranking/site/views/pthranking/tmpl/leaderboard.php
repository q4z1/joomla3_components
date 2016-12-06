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
            <br />
            <p>
                <h4>Ranking calculation:</h4>
                <ol>
                    <li><b>Placement Points:</b><br />
                        1. = 24 | 2. = 16 | 3. = 10 | 4. = 6 | 5. = 3 | 6. = 2 | 7. = 1
                    </li>
                    <li><b>Average Points:</b><br />
                     (Placement Points of last 100 games played / number of games played (max.100))
                    </li>
                    <li><b>Score:</b><br />
                     (Average Points * 100) / Community Average Points   
                    </li>
                    <li>Bonus:<br />
                    (+ 0,01 % per game, maximum 5% at 500 games)
                    </li>
                    <li><b>First-30-Games-Malus:</b><br />
                    (- 96,7% at 1st game reduced by 3,3% steps down to 0% at 31th game)
                    </li>
                </ol>
            </p>
        </div>
    </div>
</div>


