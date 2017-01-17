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
$document->addStyleSheet(JUri::root() . 'media/com_pthranking/css/pthranking.css?tx=20170117_1209');
$document->addScript(JUri::root() . 'media/com_pthranking/js/pthaccdelete.js?tx=20170117_1209');
$user = JFactory::getUser();
?>
<?php if($user->guest): ?>
<h3>Account Deletion:</h3>
<div id="errors"><ul class="text-danger"><li>You need to be logged in!</li></ul></div>
<?php else: ?>
<h3>Account Deletion for <span class="text-success"><?php echo $user->username ?></span> (<?php echo $user->email ?>):</h3>
<div class="rt-flex-container">
    <div class="rt-grid-6">
        <div class="rt-block">
        <div id="errors"></div>
        <p class="pthdel">If you press the delete button, your game- and forum-account will be deleted.</p>
        <p class="pthdel text-danger">Your email address will be immedeately free for a new registration - but your username
        will be blocked until next game season.
        </p>
        <input class="pthdel" type="hidden" name="email" id="email" value="<?php echo $user->email ?>" />
        <input type="submit" name="pthbtn" id="pthdel" class="button" value="I agree - Delete my account!">
        </div>
    </div>
</div>
<!-- Modal -->
<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header" id="deleteModalHeader">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="deleteModalLabel"></h3>
  </div>
  <div class="modal-body" id="deleteModalBody">
    Are you sure to delete you account?
  </div>
  <div class="modal-footer" id="deleteModalFooter">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
    <button class="btn" data-dismiss="modal" aria-hidden="true" id="pthDoDel">Yes, Iam.</button>
  </div>
</div>
<?php endif; ?>
