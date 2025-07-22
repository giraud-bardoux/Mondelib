<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Event
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
            <?php if($this->event->getPhotoUrl('thumb.profile')) : ?>
              <span style="background-image:url('<?php echo $this->event->getPhotoUrl('thumb.profile');?>'); text-align:left;" id="event_profile_photo"></span>
            <?php else : ?>
              <span class="bg_item_photo bg_thumb_profile bg_item_photo_event bg_item_nophoto" id="event_profile_photo"></span>
            <?php endif;?>
          </div>
          <?php else: ?>
            <div class="main_thumb_photo">
              <span class="bg_item_photo bg_thumb_profile bg_item_photo_event bg_item_nophoto" id="event_profile_photo"></span>
            </div>
          <?php endif; ?>
          <?php if (!empty($this->can_edit) && empty($this->uploadDefaultCover)) : ?>
            <div id="mainphoto_options" class="profile_cover_options
              <?php if (!empty($this->uploadDefaultCover)) : ?> profile_main_photo_options is_hidden
              <?php else: ?> profile_main_photo_options<?php endif; ?>">
              <ul class="edit-button">
                <li>
                  <?php if (!empty($this->event->photo_id)) : ?>
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
                        'event_id' => $this->event->event_id,
                        'photoType' => 'profile'), 'event_coverphoto', true); ?>' class="profile_cover_icon_photo_upload smoothbox">
                        <?php echo $this->translate('Upload Photo'); ?>
                      </a>
                    </li>
                    <li>
                      <?php echo $this->htmlLink(
                        $this->url(array(
                          'action' => 'choose-from-albums',
                          'event_id' => $this->event->event_id,
                          'recent' => 1,
                          'photoType' => 'profile'
                        ), 'event_coverphoto', true),
                        $this->translate('Choose from Albums'),
                        array(' class' => 'profile_cover_icon_photo_view smoothbox')); ?>
                    </li>
                    <?php if (!empty($this->event->photo_id)) : ?>
                      <li>
                        <?php echo $this->htmlLink(
                          array('route' => 'event_coverphoto', 'action' => 'remove-cover-photo', 'event_id' => $this->event->event_id, 'photoType' => 'profile'),
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
      <div class="cover_photo_profile_status">
        <?php if($this->subject()) { ?>
          <h2>
            <?php echo $this->subject()->getTitle() ?>
          </h2>
        <?php } ?> 
        <p class="cover_photo_stats">
          <span>
          <?php echo $this->translate(array('%s People Joined Event', '%s Peoples Joined Event', $this->event->member_count), $this->locale()->toNumber($this->event->member_count)); ?></span> 
        </p>        
      </div>
      <?php if($this->viewer()->getIdentity()) { ?>
      <div class="coverphoto_navigation">
        <ul class="coverphoto_navigation_list">
          <?php
            $viewer = Engine_Api::_()->user()->getViewer();
           
            $allowJoinEvent = true;
            if( !$viewer->getIdentity() ) {
              $allowJoinEvent = false;
            }
        
            $row = $this->event->membership()->getRow($viewer);
            $menus = [];
            // Not yet associated at all
            if( null === $row ) {
              if( $this->event->membership()->isResourceApprovalRequired() ) {
                $menus[] = array(
                  'label' => 'Request Invite',
                  'class' => 'smoothbox icon_invite',
                  'route' => 'event_extended',
                  'params' => array(
                    'controller' => 'member',
                    'action' => 'request',
                    'event_id' => $this->event->getIdentity(),
                  ),
                );
              } else {
                $menus[] = array(
                  'label' => 'Join Event',
                  'class' => 'smoothbox icon_event_join',
                  'route' => 'event_extended',
                  'params' => array(
                    'controller' => 'member',
                    'action' => 'join',
                    'event_id' => $this->event->getIdentity()
                  ),
                );
              }
            } elseif( $row->active ) {
              if( !$this->event->isOwner($viewer) ) {
                $menus[] = array(
                  'label' => 'Leave Event',
                  'class' => 'smoothbox icon_event_leave',
                  'route' => 'event_extended',
                  'params' => array(
                    'controller' => 'member',
                    'action' => 'leave',
                    'event_id' => $this->event->getIdentity()
                  ),
                );
              } else {
                $allowJoinEvent = false;
              }
            } elseif( !$row->resource_approved && $row->user_approved ) {
              $menus[] =  array(
                'label' => 'Cancel Invite Request',
                'class' => 'smoothbox icon_event_reject',
                'route' => 'event_extended',
                'params' => array(
                  'controller' => 'member',
                  'action' => 'cancel',
                  'event_id' => $this->event->getIdentity()
                ),
              );
            } elseif( !$row->user_approved && $row->resource_approved ) {
              $menus = array(
                array(
                  'label' => 'Accept Event Invite',
                  'class' => 'smoothbox icon_event_accept',
                  'route' => 'event_extended',
                  'params' => array(
                    'controller' => 'member',
                    'action' => 'accept',
                    'event_id' => $this->event->getIdentity()
                  ),
                ), array(
                  'label' => 'Ignore Event Invite',
                  'class' => 'smoothbox icon_event_reject',
                  'route' => 'event_extended',
                  'params' => array(
                    'controller' => 'member',
                    'action' => 'reject',
                    'event_id' => $this->event->getIdentity()
                  ),
                )
              );
            } else {
              $allowJoinEvent = false;
            }
          ?>

          <?php if($allowJoinEvent){ ?>
            <?php foreach($menus as $params){ ?>
            <li>
              <a href="<?php echo $this->url($params["params"],$params["route"],true) ?>" class="buttonlink <?php echo $params['class']; ?>">
                <span> <?php echo $this->translate($params["label"]); ?> </span>
              </a>
            </li> 
            <?php } ?>
          <?php } ?>
          <?php if($viewer->getIdentity()){ ?>
          <li>
            <a class="smoothbox" href="<?php echo $this->url(array('action' => 'share','module' => 'activity','format' => 'smoothbox','type'=>$this->event->getType(),'id'=>$this->event->getIdentity()),'default',true) ?>">
              <i class="fas fa-share-alt"></i>
              <span> <?php echo $this->translate('Share Event'); ?> </span>
            </a>
          </li>  
          <?php } ?>    
          <li>
            <a href="javascript:void(0)" class="coverphoto_navigation_dropdown_btn" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-ellipsis-h"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php foreach( $this->eventNavigation as $link ): ?>
                <li>
                  <a class="<?php echo  'buttonlink' . ( $link->getClass() ? ' ' . $link->getClass() : '' ) . (!empty($link->get('icon')) ? $link->get('icon') : ''); ?>" href='<?php echo $link->getHref() ?>' aria-label="<?php echo $this->translate($link->getlabel()) ?>">
                    <span><?php echo $this->translate($link->getlabel()) ?></span>
                    </a>
                </li>
              <?php endforeach; ?>
            </ul> 
          </li>   
        </ul>
      </div>
      <?php } ?>
    </div>
  <?php endif; ?>
</div>  
