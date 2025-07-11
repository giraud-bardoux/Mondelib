<?php 

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php $action = Engine_Api::_()->getItem('activity_action', $this->subject->feedupload); ?>
<?php if($this->subject->getType() == 'album_photo') { ?>
  <div class="photo_view_info">
    <div class="photo_view_info_header d-flex flex-wrap mb-3">
      <div class="photo_view_info_header_img">
        <?php echo $this->htmlLink($this->album->getOwner()->getHref(), $this->itemBackgroundPhoto($this->album->getOwner(), 'thumb.icon')); ?>
      </div>
      <div class="photo_view_info_header_cont">
        <p class="photo_view_info_header_name"><?php echo $this->album->getOwner()->__toString(); ?></p>
        <p class="photo_view_info_header_date font_color_light font_small"><?php echo $this->timestamp($this->subject->creation_date) ?></p>
      </div>
      <?php if ($this->viewer()->getIdentity()):?>
        <div class="dropdown options_menu">
          <button class="btn btn-alt" type="button" id="photooption" data-bs-toggle="dropdown" aria-expanded="true"><i class="icon_option_menu"></i></button>
          <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="photooption">
            <?php if( $this->canEdit ): ?>
              <li><?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'index', 'action' => 'edit-media', 'action_id' => $this->action_id, 'id' => $this->subject->getIdentity(), 'type' => 'album_photo', 'reset' => true), $this->translate('Edit'), array('class' => 'dropdown-item smoothbox icon_edit')) ?></li>
            <?php endif; ?>
            <?php if( $this->canDelete ): ?>
              <li><?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'index', 'action' => 'delete-media', 'action_id' => $this->action_id, 'id' => $this->subject->getIdentity(), 'type' => 'album_photo', 'reset' => true), $this->translate('Delete'), array('class' => 'dropdown-item smoothbox icon_delete')) ?></li>
            <?php endif; ?>
            <?php if( !$this->message_view && $action):?>
              <li><?php echo $this->htmlLink(array('module'=> 'activity', 'controller' => 'index', 'action' => 'share', 'route' => 'default', 'type' => $action->getType(), 'id' => $action->getIdentity(), 'format' => 'smoothbox'), $this->translate("Share"), array('class' => 'dropdown-item smoothbox icon_share')); ?></li>
              <li><?php echo $this->htmlLink(Array('module'=> 'core', 'controller' => 'report', 'action' => 'create', 'route' => 'default', 'subject' => $this->subject->getGuid(), 'format' => 'smoothbox'), $this->translate("Report"), array('class' => 'dropdown-item smoothbox icon_report')); ?></li>
            <?php endif;?>
          </ul>
        </div>
      <?php endif ?>
    </div>
    <?php //if( $this->subject->getTitle() || $this->subject->getDescription() ): ?>          
      <div class="mb-2">
        <?php //if( $this->subject->getTitle() ): ?>
          <div class="photo_view_info_title font_bold">
            <?php echo $this->subject->getTitle(); ?>
          </div>
        <?php //endif; ?>
        <?php //if( $this->subject->getDescription() ): ?>
          <div class="photo_view_info_caption font_small">
            <?php echo Engine_Api::_()->core()->smileyToEmoticons(nl2br($this->subject->getDescription())); ?>
          </div>
        <?php //endif; ?>
      </div>
    <?php //endif; ?>
    <div class="photo_view_tagged_users mb-2 font_color_light font_small" id="media_tags" style="display: none;">
      <?php echo $this->translate('Tagged:') ?>
    </div>
    <?php if (engine_count($this->subjectTags )):?>
      <div class="photo_view_tags mb-2">
        <?php foreach ($this->subjectTags as $tag): ?>
          <?php if ($tag->getTag()->getType() == 'core_tag'): ?>
          <a href='javascript:void(0);' onclick='javascript:tagAction(<?php echo $tag->getTag()->getIdentity(); ?>);'>#<?php echo $tag->getTag()->getTitle();?></a>&nbsp;
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('album.enable.rating', 1)) { ?>
      <div class="photo_view_rating">
        <?php echo $this->partial('_rating.tpl', 'core', array('item' => $this->subject, 'module' => 'album', 'param' => 'create', 'notificationType' => 'album_photo_rating')); ?>
      </div>
    <?php } ?>
  </div>
  <script type="application/javascript">
    	en4.core.runonce.add(function() {
  		var htmlElement = scriptJquery("#global_wrapper");
  		htmlElement.addClass('photo_view_page');
  	});
  </script>
<?php } elseif($this->subject->getType() == 'video') { ?>
  <div class="photo_view_info">
    <div class="photo_view_info_header d-flex flex-wrap mb-3">
      <div class="photo_view_info_header_img">
        <?php echo $this->htmlLink($this->subject->getOwner()->getHref(), $this->itemBackgroundPhoto($this->subject->getOwner(), 'thumb.icon')); ?>
      </div>
      <div class="photo_view_info_header_cont">
        <p class="photo_view_info_header_name"><?php echo $this->subject->getOwner()->__toString(); ?></p>
        <p class="photo_view_info_header_date font_color_light font_small"><?php echo $this->timestamp($this->subject->creation_date) ?></p>
      </div>
      <?php if ($this->viewer()->getIdentity()):?>
        <div class="dropdown options_menu">
          <button class="btn btn-alt" type="button" id="photooption" data-bs-toggle="dropdown" aria-expanded="true"><i class="icon_option_menu"></i></button>
          <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="photooption">
            <?php if( $this->canEdit ): ?>
              <li><?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'index', 'action' => 'edit-media', 'action_id' => $this->action_id, 'id' => $this->subject->getIdentity(), 'type' => $this->subject->getType(), 'reset' => true), $this->translate('Edit'), array('class' => 'dropdown-item smoothbox icon_edit')) ?></li>
            <?php endif; ?>
            <?php if( $this->canDelete ): ?>
              <li><?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'index', 'action' => 'delete-media', 'action_id' => $this->action_id, 'id' => $this->subject->getIdentity(), 'type' => 'video', 'reset' => true), $this->translate('Delete'), array('class' => 'dropdown-item smoothbox icon_delete')) ?></li>
            <?php endif; ?>
            <?php if( !$this->message_view  && $action):?>
              <li><?php echo $this->htmlLink(array('module'=> 'activity', 'controller' => 'index', 'action' => 'share', 'route' => 'default', 'type' => $action->getType(), 'id' => $action->getIdentity(), 'format' => 'smoothbox'), $this->translate("Share"), array('class' => 'dropdown-item smoothbox icon_share')); ?></li>
              <li><?php echo $this->htmlLink(Array('module'=> 'core', 'controller' => 'report', 'action' => 'create', 'route' => 'default', 'subject' => $this->subject->getGuid(), 'format' => 'smoothbox'), $this->translate("Report"), array('class' => 'dropdown-item smoothbox icon_report')); ?></li>
            <?php endif;?>
          </ul>
        </div>
      <?php endif ?>
    </div>
    <?php //if( $this->subject->getTitle() || $this->subject->getDescription() ): ?>          
      <div class="mb-2">
        <?php //if( $this->subject->getTitle() ): ?>
          <div class="photo_view_info_title font_bold">
            <?php echo $this->subject->getTitle(); ?>
          </div>
        <?php //endif; ?>
        <?php //if( $this->subject->getDescription() ): ?>
          <div class="photo_view_info_caption font_small">
            <?php echo Engine_Api::_()->core()->smileyToEmoticons($this->subject->description);?>
          </div>
        <?php //endif; ?>
      </div>
    <?php //endif; ?>
    <div class="item_view_stats font_color_light font_small mb-2">
      <span><i class="icon_like"></i><?php echo $this->translate(array('%s like', '%s likes', $this->subject->like_count), $this->locale()->toNumber($this->subject->like_count)) ?></span>
      <span><i class="icon_comment"></i><?php echo $this->translate(array('%s comment', '%s comments', $this->subject->comment_count), $this->locale()->toNumber($this->subject->comment_count)) ?></span>
      <span><i class="icon_view"></i><?php echo $this->translate(array('%s view', '%s views', $this->subject->view_count), $this->locale()->toNumber($this->subject->view_count)) ?></span>
    </div>
    <div class="photo_view_tagged_users mb-2 font_color_light font_small" id="media_tags" style="display: none;">
      <?php echo $this->translate('Tagged:') ?>
    </div>
    <?php if (engine_count($this->subjectTags )):?>
      <div class="photo_view_tags mb-2">
        <?php foreach ($this->subjectTags as $tag): ?>
          <?php if ($tag->getTag()->getType() == 'core_tag'): ?>
          <a href='javascript:void(0);' onclick='javascript:tagAction(<?php echo $tag->getTag()->getIdentity(); ?>);'>#<?php echo $tag->getTag()->getTitle();?></a>&nbsp;
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('video.enable.rating', 1)) { ?>
      <div class="photo_view_rating">
        <?php echo $this->partial('_rating.tpl', 'core', array('item' => $this->subject, 'module' => 'video', 'param' => 'create', 'notificationType' => 'video_rating')); ?>
      </div>
    <?php } ?>
  </div>
<?php } ?>