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
$document->addScript(JUri::root() . 'media/com_pthranking/js/pthleader.js');
// $document->addStyleSheet(JUri::root() . 'media/com_pthranking/css/pthranking.css');
$uri = JUri::getInstance();
$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
// $url = $base . JRoute::_('index.php?option=com_pthranking&view=activategame', false);
?>

<h1>Ranking</h1>

<p id="pagenum"></p>

<button id="but_prev" onclick="loadprev();">Previous</button>
<button id="but_next" onclick="loadnext();">Next</button>

<table id="ranking_table" border=1>
<!--<tr>
 <th>Rank</th>
 <th>Name</th>
 <th>Average Points</th>
 <th>Games (Season)</th>
 <th>Score</th>
</tr> -->
</table>
<?php echo $this->msg; ?>

