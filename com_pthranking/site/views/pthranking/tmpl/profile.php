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
// $document->addScript(JUri::root() . 'media/com_pthranking/js/pthleader.js?tx=20161203_1841');
// $document->addStyleSheet(JUri::root() . 'media/com_pthranking/css/pthranking.css');
// $uri = JUri::getInstance();
// $base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
// $url = $base . JRoute::_('index.php?option=com_pthranking&view=activategame', false);
?>
<div class="rt-flex-container">
    <div class="rt-grid-12">
        <div>
            <?php if($this->userexists): ?>
            
            <h1>Profile: <?php echo $this->username?></h1>
            
            <h3>Ranking information about this season (beta phase 2016-12):</h3>
            <?php echo $this->basicinfo_html; ?>
            
            <h3>Statistics for this season (beta phase 2016-12):</h3>
            <?php echo $this->seasonpiedata; ?>
            <?php echo $this->season_pie_pic; ?>
            <?php echo $this->season_bar_pic; ?>
            <h3>Statistics for all time:</h3>
            <?php echo $this->alltimepiedata; ?>
            <?php echo $this->alltime_pie_pic; ?>
            <?php echo $this->alltime_bar_pic; ?>
            <?php else: ?>
            <p>Player not found</p>
            
            <?php endif; ?>
        </div>
    </div>
</div>



