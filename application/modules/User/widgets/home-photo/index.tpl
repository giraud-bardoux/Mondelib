<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php 
  $coverphoto = Engine_Api::_()->authorization()->getPermission($this->viewer->level_id, 'user', 'coverphoto');
  $coverphoto = $coverphoto ? Engine_Api::_()->core()->getFileUrl($coverphoto) : '';
?>
<div class="user_sidebar_photo">

  <?php if(is_array($this->options) && engine_in_array('coverphoto', $this->options)) { ?>
    <div class="user_sidebar_photo_cover">
      <?php if(!empty($this->photo)) { ?>
        <img src="<?php echo $this->photo->getPhotoUrl('thumb.cover'); ?>" alt="<?php echo $this->viewer->getTitle(); ?>">
      <?php } else if(!empty($coverphoto)) { ?>
        <img src="<?php echo $coverphoto; ?>" alt="<?php echo $this->viewer->getTitle(); ?>">
      <?php } ?>
    </div>
  <?php } ?>
  <div class="user_sidebar_photo_content">
    <div class="user_sidebar_photo_main">
      <?php if(is_array($this->options) && engine_in_array('coverphoto', $this->options)) { ?>
        <div class="profile_photo">
          <?php echo $this->htmlLink($this->viewer->getHref(), $this->itemBackgroundPhoto($this->viewer, 'thumb.profile')) ?>
        </div>
      <?php } else{ ?>
        <div class="profile_photo_full">
          <?php echo $this->htmlLink($this->viewer->getHref(), $this->itemBackgroundPhoto($this->viewer, 'thumb.profile')) ?>
        </div>  
      <?php } ?>
      <div class="user_sidebar_photo_info">
        <h4>
          <a href="<?php echo $this->viewer->getHref(); ?>"><?php echo $this->viewer->getTitle(); ?></a>
        </h4>
        <?php if(is_array($this->options) && engine_in_array('username' , $this->options) && Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1)) { ?>
          <span class="username"><?php echo '@'. $this->viewer->username; ?></span>
        <?php } ?>
      </div>
    </div>

    <?php if(is_array($this->options) && engine_in_array('recentfriends', $this->options) && Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2)) { ?>
      <div class="user_sidebar_photo_friends">
        <?php if($this->friends->getTotalItemCount() > 0) { ?>
          <h6><?php echo $this->translate("Recent Friends"); ?></h6>
          <ul>
            <?php foreach( $this->friends as $membership ) { ?>
              <?php if( !isset($this->friendUsers[$membership->resource_id]) ) continue;
                $member = $this->friendUsers[$membership->resource_id];
              ?>
              <li>
                <?php echo $this->htmlLink($member->getHref(), $this->itemBackgroundPhoto($member, 'thumb.icon')) ?>
              </li>
            <?php } ?>
          </ul>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
</div>
