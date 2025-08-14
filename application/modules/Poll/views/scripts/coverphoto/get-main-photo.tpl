<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: get-main-photo.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Alex
 */
?>
<div class="profile_cover_head_inner">
  <div class="profile_main_photo_wrapper">
    <div class="profile_main_photo b_dark">
      <div class="item_photo">
        <?php if (empty($this->uploadDefaultCover)): ?>
          <div class="main_thumb_photo">
            <?php if($this->poll->getPhotoUrl('thumb.profile')) : ?>
              <span style="background-image:url('<?php echo $this->poll->getPhotoUrl('thumb.profile');?>'); text-align:left;" id="poll_profile_photo"></span>
            <?php else : ?>
              <span class="bg_item_photo bg_thumb_profile bg_item_photo_poll bg_item_nophoto" id="poll_profile_photo"></span>
            <?php endif;?>
          </div>
        <?php else: ?>
          <div class="main_thumb_photo">
            <span class="bg_item_photo bg_thumb_profile bg_item_photo_poll bg_item_nophoto" id="poll_profile_photo"></span>
          </div>
        <?php endif; ?>
        <?php if (!empty($this->can_edit) && empty($this->uploadDefaultCover)) : ?>
          <div id="mainphoto_options" class="profile_cover_options
            <?php if (!empty($this->uploadDefaultCover)) : ?> profile_main_photo_options is_hidden
            <?php else: ?> profile_main_photo_options<?php endif; ?>">
            <ul class="edit-button">
              <li>
                <?php if (!empty($this->poll->photo_id)) : ?>
                  <span class="profile_cover_btn">
                    <i class="fa fa-camera" aria-hidden="true"></i>
                  </span>
                <?php else: ?>
                  <span class="profile_cover_btn">
                    <i class="fa fa-camera" aria-hidden="true"></i>
                  </span>
                <?php endif; ?>

                <ul class="profile_options_pulldown">
                  <li>
                    <a href='<?php echo $this->url(array(
                      'action' => 'upload-cover-photo',
                      'poll_id' => $this->poll->poll_id,
                      'photoType' => 'profile'), 'poll_coverphoto', true); ?>' class="profile_cover_icon_photo_upload smoothbox">
                      <?php echo $this->translate('Upload Photo'); ?>
                    </a>
                  </li>
                  <li>
                    <?php echo $this->htmlLink(
                      $this->url(array(
                        'action' => 'choose-from-albums',
                        'poll_id' => $this->poll->poll_id,
                        'recent' => 1,
                        'photoType' => 'profile'
                      ), 'poll_coverphoto', true),
                      $this->translate('Choose from Albums'),
                      array(' class' => 'profile_cover_icon_photo_view smoothbox')); ?>
                  </li>
                  <?php if (!empty($this->poll->photo_id)) : ?>
                    <li>
                      <?php echo $this->htmlLink(
                        array('route' => 'poll_coverphoto', 'action' => 'remove-cover-photo', 'poll_id' => $this->poll->poll_id, 'photoType' => 'profile'),
                        $this->translate('Remove'),
                        array(' class' => 'smoothbox profile_cover_icon_photo_delete')); ?>
                    </li>
                  <?php endif; ?>
                </ul>
              </li>
            </ul>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php if (empty($this->uploadDefaultCover)): ?>
    <div class="cover_photo_profile_information">
      <div class='cover_photo_profile_status'>
        <?php if($this->subject()) { ?>
          <h2>
            <?php echo $this->subject()->getTitle() ?>
          </h2>
        <?php } ?>
        <?php if($this->poll->vote_count){ ?>
        <span class="profile_status_text">
        <?php echo $this->translate(array('%s Vote', '%s Votes', $this->poll->vote_count), $this->locale()->toNumber($this->poll->vote_count)); ?>
        </span>
        <?php } ?>
      </div>
      <?php if($this->viewer()->getIdentity()) { ?>
        <div class="coverphoto_navigation">
          <ul class="coverphoto_navigation_list">
          <?php if($this->viewer()->getIdentity()){ ?>
            <li>
              <a class="smoothbox" href="<?php echo $this->url(array('action' => 'share','module' => 'activity','format' => 'smoothbox','type'=>$this->poll->getType(),'id'=>$this->poll->getIdentity()),'default',true) ?>">
                <i class="fas fa-share-alt"></i>
                <span> <?php echo $this->translate('Share Poll'); ?> </span>
              </a>
            </li>  
            <?php } ?>    
          </ul>
        </div>
      <?php } ?>
    </div>
  <?php endif; ?>
</div>
