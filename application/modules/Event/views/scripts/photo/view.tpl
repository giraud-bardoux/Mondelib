<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: view.tpl 10166 2014-04-14 16:59:00Z lucas $
 * @author     Sami
 */
?>
<?php
$this->headScript()
->appendFile($this->layout()->staticBaseUrl . 'externals/tagger/tagger.js');
$this->headTranslate(array(
'Save', 'Cancel', 'delete',
));
?>
<script type="text/javascript">
    var taggerInstance;
    en4.core.runonce.add(function() {
      taggerInstance = new Tagger('#media_photo_next', {
        'title' : '<?php echo $this->translate('ADD TAG');?>',
        'description' : '<?php echo $this->translate('Type a tag or select a name from the list.');?>',
        'createRequestOptions' : {
            'url' : '<?php echo $this->url(array('module' => 'core', 'controller' => 'tag', 'action' => 'add'), 'default', true) ?>',
            'data' : {
                'subject' : '<?php echo $this->subject()->getGuid() ?>'
            }
        },
        'deleteRequestOptions' : {
            'url' : '<?php echo $this->url(array('module' => 'core', 'controller' => 'tag', 'action' => 'remove'), 'default', true) ?>',
            'data' : {
                'subject' : '<?php echo $this->subject()->getGuid() ?>'
            }
        },
        'cropOptions' : {
            'container' : scriptJquery('#media_photo_next')
        },
        'tagListElement' : '#media_tags',
        'existingTags' : <?php echo $this->action('retrieve', 'tag', 'core', array('sendNow' => false)) ?>,
        'suggestProto' : 'request.json',
            'suggestParam' : '<?php echo $this->url(array('module' => 'user', 'controller' => 'friends', 'action' => 'suggest', 'includeSelf' => true), 'default', true) ?>',
            'guid' : <?php echo ( $this->viewer()->getIdentity() ? "'".$this->viewer()->getGuid()."'" : 'false' ) ?>,
        'enableCreate' : <?php echo ( $this->canEdit ? 'true' : 'false') ?>,
        'enableDelete' : <?php echo ( $this->canEdit ? 'true' : 'false') ?>
      });
      taggerInstance.initialize();
    });
</script>

<div class='generic_layout_container layout_main'>
  <div class='generic_layout_container layout_middle'>
    <div class='generic_layout_container'>
      <div class="breadcrumb_wrap">
        <div class="photo_breadcrumb">
          <p>
            <?php echo $this->event->__toString(); ?> <?php echo $this->translate('&#187;'); ?> <?php echo $this->htmlLink(array(
            'route' => 'event_extended',
            'controller' => 'photo',
            'action' => 'list',
            'subject' => $this->event->getGuid(),
            ), $this->translate('Photos')) ?>
          </p>
        </div>
      </div>  
    </div>
    <div class='generic_layout_container layout_core_content'> 
      <div class='block'>
        <div class="photo_view_container">
          <div class='photo_view_wrapper'>
            <?php if ($this->album->count() > 1): ?>
              <div class="photo_view_nav">
                <?php echo $this->htmlLink($this->photo->getPrevCollectible()->getHref(), $this->translate('<i class="fa-solid fa-angle-left"></i>'), array('id' => 'photo_prev', 'title' => $this->translate('Prev'))) ?> 
                <?php echo $this->htmlLink($this->photo->getNextCollectible()->getHref(), $this->translate('<i class="fa-solid fa-angle-right"></i>'), array('id' => 'photo_next', 'title' => $this->translate('Next'))) ?>
              </div>
            <?php endif; ?>
            <div class="photo_view_media_container">
              <div class="photo_view_media" id="media_photo_div">
                <a id='media_photo_next' href='<?php echo $this->photo->getNextCollectible()->getHref() ?>'>
                  <?php echo $this->htmlImage($this->photo->getPhotoUrl(), $this->photo->getTitle(), array('id' => 'media_photo')); ?> 
                </a> 
              </div>
            </div>
            <div class="photo_view_footer d-flex align-items-center">
              <div class="photo_view_footer_left">
              <a href='javascript:void(0);' class="icon_tag" onclick='taggerInstance.begin();'><?php echo $this->translate('Add Tag');?></a>
              </div>
            </div>
          </div>
        </div>
        <div class='photo_view_info'>
          <div class="photo_view_info_header d-flex flex-wrap mb-3">
            <div class="photo_view_info_header_img">
              <?php echo $this->htmlLink($this->photo->getOwner()->getHref(), $this->itemBackgroundPhoto($this->photo->getOwner(), 'thumb.icon')); ?>
            </div>
            <div class="photo_view_info_header_cont">
              <p class="photo_view_info_header_name">
                <?php echo $this->photo->getOwner()->__toString(); ?>
              </p>
              <p class="photo_view_info_header_date font_color_light font_small"><?php echo $this->timestamp($this->photo->creation_date) ?></p>
            </div>
            <?php if ($this->viewer()->getIdentity()):?>
              <div class="dropdown options_menu">
                <button class="btn btn-alt" type="button" id="photooption" data-bs-toggle="dropdown" aria-expanded="true"><i class="icon_option_menu"></i></button>
                <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="photooption">
                  <?php if( $this->canEdit ): ?>
                    <li><?php echo $this->htmlLink(array('reset' => false, 'action' => 'edit'), $this->translate('Edit'), array('class' => 'dropdown-item smoothbox icon_edit')) ?></li>
                  <?php endif; ?>
                  <?php if( $this->canDelete ): ?>
                    <li><?php echo $this->htmlLink(array('reset' => false, 'action' => 'delete'), $this->translate('Delete'), array('class' => 'dropdown-item smoothbox icon_delete')) ?></li>
                  <?php endif; ?>
                  <?php if( !$this->message_view ):?>
                    <li><?php echo $this->htmlLink(Array('module'=> 'activity', 'controller' => 'index', 'action' => 'share', 'route' => 'default', 'type' => 'album_photo', 'id' => $this->photo->getIdentity(), 'format' => 'smoothbox'), $this->translate("Share"), array('class' => 'dropdown-item smoothbox icon_share')); ?></li>
                    <li><?php echo $this->htmlLink(Array('module'=> 'core', 'controller' => 'report', 'action' => 'create', 'route' => 'default', 'subject' => $this->photo->getGuid(), 'format' => 'smoothbox'), $this->translate("Report"), array('class' => 'dropdown-item smoothbox icon_report')); ?></li>
                    <li><?php echo $this->htmlLink(array('route' => 'user_extended', 'controller' => 'edit', 'action' => 'external-photo', 'photo' => $this->photo->getGuid(), 'format' => 'smoothbox'), $this->translate('Make Profile Photo'), array('class' => 'dropdown-item smoothbox icon_photo')) ?></li>
                  <?php endif;?>
                </ul>
              </div>
            <?php endif ?>
          </div>
          <div class="mb-2">
            <div class="photo_view_info_title font_bold">
              <?php if( $this->photo->getTitle() ): ?>
                <?php echo $this->photo->getTitle(); ?>
              <?php endif; ?>
            </div>
            <div class="photo_view_info_caption font_small">
              <?php if( $this->photo->getDescription() ): ?>
                <?php echo Engine_Api::_()->core()->smileyToEmoticons($this->photo->getDescription()); ?>
              <?php endif; ?>
            </div>
            <div class="photo_view_tagged_users mb-2 font_color_light font_small" id="media_tags" style="display: none;">
              <?php echo $this->translate('Tagged:');?> 
            </div>
          </div>            
        </div>   
      </div>
    </div>
    <div class='generic_layout_container'> 
      <?php echo $this->content()->renderWidget("core.comments"); ?> 
    </div>
  </div>
</div>
