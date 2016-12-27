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
$document->addScript(JUri::root() . 'media/com_pthranking/js/jquery.tablesorter.min.js');
$document->addStyleSheet(JUri::root() . 'media/com_pthranking/css/tablesorter.css?tx=20161227_0136');
?>
<div class="rt-flex-container">
    <div class="rt-grid-12">
        <div>
            <h1>Poker Table: <?php echo $this->gamename?></h1>
            <h3>Ranking information:</h3>
            <?php echo $this->rankinginfo; ?>
            <?php echo $this->notfound; ?>
            
        </div>
    </div>
</div>
<script>
    document.onreadystatechange = function() {
        if (document.readyState === 'complete') {
            jQuery("#gameTable").tablesorter(); 
        }
    }
</script>


