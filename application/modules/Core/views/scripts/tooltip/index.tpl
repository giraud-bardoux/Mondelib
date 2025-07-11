<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
?>
<?php 
$settings = Engine_Api::_()->getApi('settings', 'core');
$subject = $this->subject;
$viewer = Engine_Api::_()->user()->getViewer();

$cover = '';
if($subject->getType() == 'user') {
  if(isset($subject->coverphoto) && $subject->coverphoto != 0 && $subject->coverphoto != '') {
    $cover = Engine_Api::_()->storage()->get($subject->coverphoto, '');
    if($cover) {
      $cover = $cover->map(); 
    }
  } else {
    $authApi = Engine_Api::_()->authorization();
    $cover = $authApi->getPermission($subject, 'user', 'coverphoto');
  }
} else if(isset($subject->cover) && $subject->cover != 0 && $subject->cover != '') {
  $cover = Engine_Api::_()->storage()->get($subject->cover, '');
  if($cover) {
    $cover = $cover->getPhotoUrl(); 
  } else {
    $cover = '';
  }
}
?>
<div class="info_tooltip">
  <?php if(!empty($cover)) { ?>
    <div class="info_tooltip_cover"><img src="<?php echo $cover; ?>"></div>
  <?php } ?>
  <div class="info_tooltip_content">
    <div class="info_tooltip_photo">
      <?php echo $this->htmlLink($subject->getHref(), $this->itemBackgroundPhoto($subject, 'thumb.profile', $subject->getTitle())) ?>
    </div>
    <div class="info_tooltip_info">
      <div class="info_tooltip_info_title">  
        <a href="<?php echo $subject->getHref(); ?>" class="font_color"><?php echo $subject->getTitle(); ?></a></a>
      </div>
      <?php if (isset($subject->username) && $settings->getSetting('user.signup.username', 1) && $subject->username) { ?>
        <div class="info_tooltip_stats font_color_light font_small">
          <span><?php echo $this->translate("@%s", $subject->username); ?></span>
        </div>
      <?php } ?>
    	<div class="info_tooltip_stats font_color_light font_small">
        <?php if($subject->getType() == 'user') { ?>
    			<?php if ($settings->getSetting('user.friends.eligible', '1') && $subject->member_count) { ?>
    		    <span><?php echo $this->translate(array('%s Friend', '%s Friends', $subject->member_count), $this->locale()->toNumber($subject->member_count)) ?></span>
    			<?php } ?>
    			<?php if ($settings->getSetting('core.followenable', '1')) { ?>
    				<?php $followersCount = Engine_Api::_()->getDbTable('follows', 'user')->followers(array('user_id' => $subject->getIdentity())); ?>
    				<?php if (engine_count($followersCount)) { ?>
    			    <span><?php echo $this->translate(array('%s Follower', '%s Followers', engine_count($followersCount)), $this->locale()->toNumber(engine_count($followersCount))) ?></span>
    				<?php } ?>
    				<?php $followingCount = Engine_Api::_()->getDbTable('follows', 'user')->following(array('user_id' => $subject->getIdentity())); ?>
    				<?php if (engine_count($followingCount)) { ?>
    				  <span><?php echo $this->translate('%s Following', $this->locale()->toNumber(engine_count($followingCount))); ?></span>
    				<?php } ?>
    			<?php } ?>
        <?php } ?>
    	</div>
      <div class="info_tooltip_stats font_color_light font_small">
        <?php if(isset($subject->view_count) && !empty($subject->view_count)):?>
          <span title="<?php echo $this->translate(array('%s view', '%s views', $subject->view_count), $this->locale()->toNumber($subject->view_count))?>"><i class="icon_view "></i><?php echo $this->locale()->toNumber($subject->view_count); ?></span>
        <?php endif;?>
        <?php if(!empty($subject->like_count) && isset($subject->like_count)):?>
          <span title="<?php echo $this->translate(array('%s like', '%s likes', $subject->like_count), $this->locale()->toNumber($subject->like_count))?>"><i class="icon_like"></i><?php echo $this->locale()->toNumber($subject->like_count); ?></span>
        <?php endif;?>
        <?php if(isset($subject->comment_count) && !empty($subject->comment_count)):?>
          <span title="<?php echo $this->translate(array('%s comment', '%s comments', $subject->comment_count), $this->locale()->toNumber($subject->comment_count))?>"><i class="icon_comment"></i><?php echo $this->locale()->toNumber($subject->comment_count); ?></span>
        <?php endif;?>
      </div>
      <?php if(isset($subject->location) && !empty($subject->location) && $settings->getSetting('enableglocation', 0)) { ?>
    	  <div class="info_tooltip_stats font_color_light font_small">
    	    <span class="widthfull">
    	    <i class="icon_location" title="<?php echo $this->translate('location'); ?>"></i>
    	    <span><a href="<?php echo 'http://maps.google.com/?q='.$subject->location; ?>" target="_blank" class="font_color_light"><?php echo $subject->location; ?></a></span>
    	    </span>
    	  </div>
	    <?php } ?>
    </div>
    
    <?php if($subject->getType() == 'user' && $viewer->getIdentity() && !$viewer->isSelf($subject) && ($settings->getSetting('user.friends.eligible', '1') || $settings->getSetting('core.followenable', 1))) { ?>
      <div class="info_tooltip_buttons">
        <?php if($settings->getSetting('user.friends.eligible', '1')) { ?>
          <div>
            <?php echo $this->partial('_addFriend.tpl', 'user', array('subject' => $subject)); ?>
          </div>
        <?php } ?>
        <?php if ($settings->getSetting('core.followenable', 1)) { ?>
        <?php $getFollowUserStatus = Engine_Api::_()->getDbTable('follows', 'user')->getFollowUserStatus($subject->user_id); ?>
          <div>
            <?php echo $this->partial('_followmembers.tpl', 'user', array('subject' => $subject)); ?>
          </div>
        <?php } ?>
      </div>
    <?php } ?>
	</div>
</div>
<?php die; ?>