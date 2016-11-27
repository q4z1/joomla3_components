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
$document->addScript(JUri::root() . 'media/com_pthranking/js/pthact.js');
$user = JFactory::getUser();
?>

<?php if($user->guest): ?>
<h3>Game-Account Activation for existing Forum-Users:</h3>
<div id="errors"><ul class="text-danger"><li>You need to be logged in!</li></ul></div>
<?php elseif($this->game_acc_exists): ?>
<h3>Game-Account Activation for your username <?php echo $user->username ?>:</h3>
<div id="errors"><ul class="text-danger"><li>You already activated your game-account!</li></ul></div>
<?php else: ?>
<h3>Game-Account Activation for your username <?php echo $user->username ?>:</h3>
<div class="rt-flex-container">
    <div class="rt-grid-6">
        <div class="rt-block">
        <div id="errors"></div>
        <form action="" method="post" id="pthactivategame-form">
        <fieldset class="userdata">
            <p id="form-signup-password">
                <label for="pthpassword">Password <sup class="mandatory">*)</sup>:</label>
                <input id="pthranking-password" type="password" name="pthpassword" class="inputbox" size="16">
            </p>
            <p id="form-signup-gender">
                <label for="pthgender">Gender:</label>
                <select id="pthranking-gender" name="pthgender" class="inputbox">
                    <option value="">---</option>
                    <option value="m">Male</option>
                    <option value="f">Female</option>
                </select>
            </p>
            <p id="form-signup-country">
                <label for="pthcountry">Country:</label>
                <select id="pthranking-country" name="pthcountry" class="inputbox">
                    <option value="">---</option>
                    <?php foreach($this->country_iso as $country => $iso):?>
                    <option value="<?php echo $iso ?>"><?php echo $country ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <input type="hidden" name="submit" value="true" />
            <input type="submit" name="pthbtn" id="pthact" class="button" value="Activate">
         </fieldset>
        </form>
        <p id="pthmand">*) mandatory fields</p>
        </div>
    </div>
    <div class="rt-grid-6">
        <div class="rt-block">
        <p>Here you can activate your forum-account for the game.</p>
        <p>Just re-enter your existing password and optionally choose a gender and a country.</p>
        <p>Your account will then be activated for the login to the game.</p>
        </div>
    </div>
</div>
<?php endif; ?>





