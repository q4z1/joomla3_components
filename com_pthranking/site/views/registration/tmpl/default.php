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
$document->addScript(JUri::root() . 'media/com_pthranking/js/pthreg.js');
$document->addStyleSheet(JUri::root() . 'media/com_pthranking/css/pthranking.css');
?>

<div class="rt-flex-container">
    <div class="rt-grid-6">
        <div class="rt-block">
            <div id="errors"></div>
        <form action="" method="post" id="pthsignup-form">
        <fieldset class="userdata">
            <p id="form-signup-email">
                <label for="pthemail">E-Mail <sup class="mandatory">*)</sup>:</label>
                <input id="pthranking-email" type="text" name="pthemail" class="inputbox" size="16">
            </p>
            <p id="form-signup-username">
                <label for="pthusername">Username <sup class="mandatory">*)</sup>:</label>
                <input id="pthranking-username" type="text" name="pthusername" class="inputbox" size="16">
            </p>
            <p id="form-signup-password">
                <label for="pthpassword">Password <sup class="mandatory">*)</sup>:</label>
                <input id="pthranking-password" type="password" name="pthpassword" class="inputbox" size="16">
            </p>
            <p id="form-signup-password2">
                <label for="pthpassword2">Password repeat <sup class="mandatory">*)</sup>:</label>
                <input id="pthranking-password2" type="password" name="pthpassword2" class="inputbox" size="16">
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
            <input type="submit" name="pthbtn" id="pthreg" class="button" value="Register">
         </fieldset>
        </form>
        <p>*) mandatory fields</p>
        </div>
    </div>
    <div class="rt-grid-6">
        <div class="rt-block">
        <h3>Explanation of new login-/ranking registration:</h3>
        <p>Here you can register for the new login-/ranking system of pokerth.net</p>
        <p>This registration page is for players <bold>who don't yet have a forum account</bold>.</p>
        <p>If your chosen username is already taken you will get a hint.</p>
        <p>After clicking on the Register Button you will get an email in order to validate your email-address.<br />
        After successfully validating your email-address you will have access to the game with your credentials in the moment we switch the game-server
        to the new login-system.<br />
        A forum-account will be created with this registration in parallel too - so you can just login to the forum after the email validation.
        </p>
        <p>For those <bold>who already have a forum-account</bold>, you will need to enter a password for the game-server, as it is
        not possible to use the existing forum-account password due to technical reasons.<br />Please login to the forum and move <a href="#">here</a> in order to enter a password for entering the game in the future.</p>
        </div>
    </div>
</div>


