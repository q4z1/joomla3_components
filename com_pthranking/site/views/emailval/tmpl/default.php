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
$document->addStyleSheet(JUri::root() . 'media/com_pthranking/css/pthranking.css');
$uri = JUri::getInstance();
$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
?>
<h3>E-Mail Validation:</h3>
<?php if($this->act_key == ""): ?>
<div class="rt-flex-container">
    <div class="rt-grid-12">
        <div class="rt-block">
        <form action="<?php echo $base ?>/component/pthranking/" method="get" id="pthemailval-form" name="pthemailval-form">
        <input type="hidden" name="view" value="emailval" />
        <fieldset class="userdata">
            <p id="form-actkey">
                <label for="actkey">Activation Key:</label>
                <input type="text" name="actkey" class="inputbox input-large" size="32">
            </p>
            <input type="submit" name="pthbtn" id="pthemailval" class="button" value="Validate">
        </fieldset>
        </form>
        </div>
    </div>
</div>
<?php elseif($this->act_key != "" && $this->success): ?>
<?php echo $this->msg; ?>
<?php else: ?>
<div class="rt-flex-container">
    <div class="rt-grid-12">
        <div class="rt-block">
        <div id="errors"><ul class='text-danger'><li><?php echo $this->msg; ?></li></ul></div>
        <form action="<?php echo $base ?>/component/pthranking/" method="get" id="pthemailval-form" name="pthemailval-form">
        <input type="hidden" name="view" value="emailval" />
        <fieldset class="userdata">
            <p id="form-actkey">
                <label for="actkey">Activation Key:</label>
                <input type="text" name="actkey" class="inputbox input-large" size="32" value="<?php echo $this->act_key?>">
            </p>
            <input type="submit" name="pthbtn" id="pthemailval" class="button" value="Validate">
        </fieldset>
        </form>
        </div>
    </div>
</div>
<?php endif;




