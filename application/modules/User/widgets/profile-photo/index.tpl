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
  $coverphoto = Engine_Api::_()->authorization()->getPermission($this->subject()->level_id, 'user', 'coverphoto');
  $coverphoto = $coverphoto ? Engine_Api::_()->core()->getFileUrl($coverphoto) : '';
?>
<div class="user_sidebar_photo">

  <?php if(is_array($this->options) && engine_in_array('coverphoto', $this->options)) { ?>
    <div class="user_sidebar_photo_cover">
      <?php if(!empty($this->photo)) { ?>
        <img src="<?php echo $this->photo->getPhotoUrl('thumb.cover'); ?>" alt="profile img">
      <?php } else if(!empty($coverphoto)) { ?>
        <img src="<?php echo $coverphoto; ?>" alt="profile img">
      <?php } ?>
    </div>
  <?php } ?>
  <div class="user_sidebar_photo_content">
    <div class="user_sidebar_photo_main">
      <?php if(is_array($this->options) && engine_in_array('coverphoto', $this->options)) { ?>
        <div class="profile_photo">
          <?php echo $this->htmlLink($this->subject()->getHref(), $this->itemBackgroundPhoto($this->subject(), 'thumb.profile')) ?>
        </div>
      <?php } else{ ?>
        <div class="profile_photo_full">
          <?php echo $this->htmlLink($this->subject()->getHref(), $this->itemBackgroundPhoto($this->subject(), 'thumb.profile')) ?>
        </div>
      <?php } ?>
      <div class="user_sidebar_photo_info">
        <?php if($this->subject()) { ?>
          <h4>
            <a href="<?php echo $this->subject()->getHref(); ?>"><?php echo $this->subject()->getTitle() ?></a>
          </h4>
        <?php } ?>
        <?php if(is_array($this->options) && engine_in_array('username' , $this->options) && Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1)) { ?>
          <span class="username"><?php echo '@'.$this->subject()->username;?></span>
        <?php } ?>
      </div>
    </div>
    <?php if(is_array($this->options) && engine_in_array('recentfriends' , $this->options) && $this->friends->getTotalItemCount() > 0) { ?>
      <div class="user_sidebar_photo_friends">
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
      </div>
    <?php } ?>
  </div>
</div>
