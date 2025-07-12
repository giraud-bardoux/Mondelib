<?php

/**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Comment
 * @copyright  Copyright 2014-2020 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2017-01-12 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */

?>
<script>
  var activitycommentreverseorder = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.commentreverseorder', 1); ?>;
</script>
<div class="comments_container">
  <?php echo $this->action("list", "comment", "comment", array("type" => $this->subject->getType(), "id" => $this->subject->getIdentity(), 'is_ajax_load' => true)); ?>
</div>
