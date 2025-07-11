<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _followmembers.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php
$subject = $this->subject ? $this->subject : $subject;

$followTable = Engine_Api::_()->getDbTable('follows', 'user');
$isFollow = $followTable->getFollowStatus($subject->user_id);

$followClass = (!$isFollow) ? 'icon_user_follow' : 'icon_user_unfollow' ;

$getFollowResourceStatus = $followTable->getFollowResourceStatus($subject->user_id);

$getFollowUserStatus = $followTable->getFollowUserStatus($subject->user_id);

$iconType = $this->iconType ? $this->iconType : ' ';
?>
<?php if($isFollow && $getFollowResourceStatus->user_approved == 1 && $getFollowResourceStatus->resource_approved == 1) { ?>
  <a href='javascript:;' data-icontype="<?php echo $iconType; ?>" data-url='<?php echo $subject->getIdentity(); ?>' <?php if($iconType == 'icon') { ?> data-bs-toggle="tooltip" data-bs-title="<?php echo $this->translate('Following'); ?>" <?php } ?> class='btn btn-alt user_follow user_follow_<?php echo $subject->getIdentity() ?> <?php echo $iconType; ?>'><i class='icon_user_unfollow'></i><span><?php if($iconType != 'icon') { ?><?php echo $this->translate('Following'); ?></span><?php } ?></a>
<?php } else if($getFollowResourceStatus &&  $getFollowResourceStatus->user_approved == 0 && $getFollowResourceStatus->resource_approved == 1) { ?>
  <a href='javascript:;' data-icontype="<?php echo $iconType; ?>" data-url='<?php echo $subject->getIdentity(); ?>' class='btn btn-alt user_follow user_follow_<?php echo $subject->getIdentity(); ?> <?php echo $iconType; ?>' data-bs-toggle="tooltip" data-bs-title="<?php echo $this->translate('Cancel Follow Request'); ?>"><i class='icon_user_follow_requested'></i> <?php if($iconType != 'icon') { ?><span><?php echo $this->translate('Requested'); ?></span><?php } ?></a>
<?php } else if( $getFollowResourceStatus && $getFollowResourceStatus->user_approved == 0 && $getFollowResourceStatus->resource_approved == 1 ) { ?>
  <a href='javascript:;' data-icontype="<?php echo $iconType; ?>" data-url='<?php echo $subject->getIdentity(); ?>' class='btn user_follow user_follow_<?php echo $subject->getIdentity(); ?> <?php echo $iconType; ?>'><i class='fa fa-times'  title='<?php echo $this->translate('Confirm'); ?>'></i> <span><?php echo $this->translate('Confirm'); ?></span></a>
<?php } else if(empty($isFollow) && empty($getFollowResourceStatus)) { ?>
  <?php if(!empty($getFollowUserStatus) && !empty($getFollowUserStatus->user_approved) && !empty($getFollowUserStatus->resource_approved)) { ?>
    <?php $follow_back = $this->translate('Follow Back'); ?>
  <?php } else { ?>
    <?php $follow_back = $this->translate('Follow'); ?>
  <?php } ?>
  <a href='javascript:;' data-icontype="<?php echo $iconType; ?>" data-url='<?php echo $subject->getIdentity(); ?>' <?php if($iconType == 'icon') { ?> data-bs-toggle="tooltip" data-bs-title="<?php echo $follow_back; ?>" <?php } ?> class='btn btn-primary user_followers user_follow user_follow_<?php echo $subject->getIdentity(); ?> <?php echo $iconType; ?>'><i class='icon_user_follow'></i> 
  <?php if($iconType != 'icon') { ?>
    <span>
      <?php echo $follow_back; ?>
    </span>
  <?php } ?>
  </a>
<?php } ?>
