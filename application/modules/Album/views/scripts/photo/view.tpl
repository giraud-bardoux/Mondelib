<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: view.tpl 10110 2013-10-31 02:04:11Z andres $
 * @author     John Boehr <j@webligo.com>
*/
?>

<?php
$this->headTranslate(array(
'Save', 'Cancel', 'delete',
));
?>
<script type="text/javascript">
    en4.core.runonce.add(function() {
        var descEls = scriptJquery('.albums_viewmedia_info_caption');
        if( descEls.length > 0 ) {
           descEls.enableLinks();
        }
        var taggerInstance = window.taggerInstance = new Tagger('#media_photo_next',{
        'title' : '<?php echo $this->string()->escapeJavascript($this->translate('ADD TAG'));?>',
        'description' : '<?php echo $this->string()->escapeJavascript($this->translate('Type a tag or select a name from the list.'));?>',
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
        'existingTags' : <?php echo Zend_Json::encode($this->tags) ?>,
        'suggestProto' : 'request.json',
        'suggestParam' : "<?php echo $this->url(array('module' => 'user', 'controller' => 'friends', 'action' => 'suggest', 'includeSelf' => true), 'default', true) ?>",
        'guid' : <?php echo ( $this->viewer()->getIdentity() ? "'".$this->viewer()->getGuid()."'" : 'false' ) ?>,
        'enableCreate' : <?php echo ( $this->canTag ? 'true' : 'false') ?>,
        'enableDelete' : <?php echo ($this->canUntagGlobal ? 'true' : 'false') ?>
      });   
    });

    var tagAction = window.tagAction = function(tag) {
        scriptJquery('#tag').val(tag);
        const formData = new FormData(scriptJquery('#filter_form')[0]);
        const params = new URLSearchParams(formData);
        let url = scriptJquery('#filter_form').attr("action")+"?"+params;
        window.history.pushState({state:'new'},'', url);
        loadAjaxContentApp(url);
    }
</script>

<form id='filter_form' class='global_form_box' method='post' action='<?php echo $this->url(array('module' => 'album', 'controller' => 'index', 'action' => 'browse-photos'), 'album_general', true) ?>' style='display:none;'><input type="hidden" id="tag" name="tag" value=""/></form>
<div class="block">
  <div class="photo_view_container">
    <div class='photo_view_wrapper'>
      <?php if( !$this->message_view): ?>
        <?php if( $this->album->count() > 1 ): ?>
          <div class="photo_view_nav">
            <?php echo $this->htmlLink(( $this->previousPhoto ? $this->previousPhoto->getHref() : null ), $this->translate('<i class="fa-solid fa-angle-left"></i>'), array('id' => 'photo_prev', 'title' => $this->translate('Prev'))) ?>
            <?php echo $this->htmlLink(( $this->nextPhoto ? $this->nextPhoto->getHref() : null ), $this->translate('<i class="fa-solid fa-angle-right"></i>'), array('id' => 'photo_next', 'title' => $this->translate('Next'))) ?>
          </div>
        <?php endif ?>
      <?php endif ?>
      <div class="photo_view_media_container">    
        <div class='photo_view_media' id='media_photo_div'>
          <a id='media_photo_next'  href='<?php echo (0 && $this->nextPhoto && !$this->message_view)? $this->escape($this->nextPhoto->getHref()) : 'javascript::void()' ?>'>
          <?php echo $this->htmlImage($this->photo->getPhotoUrl(), $this->photo->getTitle(), array('id' => 'media_photo')); ?>
          </a>
        </div>
      </div>
      <div class="photo_view_footer d-flex align-items-center">
        <div class="photo_view_footer_left">
          <?php if ($this->viewer()->getIdentity()):?>
            <?php if( $this->canTag ): ?>
              <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Add Tag'), array('class' => 'icon_tag', 'onclick'=>'taggerInstance.begin();')) ?>
            <?php endif; ?>
          <?php endif ?>
        </div>
        <?php if( $this->canEdit ): ?>
          <div class="photo_view_footer_options">
            <a class="buttonlink icon_photos_rotate_ccw" href="javascript:void(0)" onclick="scriptJquery(this).attr('class', 'buttonlink icon_loading');en4.album.rotate(<?php echo $this->photo->getIdentity() ?>, 90).then(function(){ this.attr('class', 'buttonlink icon_photos_rotate_ccw') }.bind(scriptJquery(this)));">&nbsp;</a>
            <a class="buttonlink icon_photos_rotate_cw" href="javascript:void(0)" onclick="scriptJquery(this).attr('class', 'buttonlink icon_loading');en4.album.rotate(<?php echo $this->photo->getIdentity() ?>, 270).then(function(){ this.attr('class', 'buttonlink icon_photos_rotate_cw') }.bind(scriptJquery(this)));">&nbsp;</a>
            <a class="buttonlink icon_photos_flip_horizontal" href="javascript:void(0)" onclick="scriptJquery(this).attr('class', 'buttonlink icon_loading');en4.album.flip(<?php echo $this->photo->getIdentity() ?>, 'horizontal').then(function(){ this.attr('class', 'buttonlink icon_photos_flip_horizontal') }.bind(scriptJquery(this)));">&nbsp;</a>
            <a class="buttonlink icon_photos_flip_vertical" href="javascript:void(0)" onclick="scriptJquery(this).attr('class', 'buttonlink icon_loading');en4.album.flip(<?php echo $this->photo->getIdentity() ?>, 'vertical').then(function(){ this.attr('class', 'buttonlink icon_photos_flip_vertical') }.bind(scriptJquery(this)));">&nbsp;</a>
          </div>
        <?php endif ?>
      </div>
    </div>
  </div>
  <div class="photo_view_info">
    <div class="photo_view_info_header d-flex flex-wrap mb-3">
      <div class="photo_view_info_header_img">
        <?php echo $this->htmlLink($this->album->getOwner()->getHref(), $this->itemBackgroundPhoto($this->album->getOwner(), 'thumb.icon')); ?>
      </div>
      <div class="photo_view_info_header_cont">
        <p class="photo_view_info_header_name"><?php echo $this->album->getOwner()->__toString(); ?></p>
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
    <?php if( $this->photo->getTitle() || $this->photo->getDescription() ): ?>          
      <div class="mb-2">
        <?php if( $this->photo->getTitle() ): ?>
          <div class="photo_view_info_title font_bold">
            <?php echo $this->photo->getTitle(); ?>
          </div>
        <?php endif; ?>
        <?php if( $this->photo->getDescription() ): ?>
          <div class="photo_view_info_caption font_small">
            <?php echo Engine_Api::_()->core()->smileyToEmoticons(nl2br($this->photo->getDescription())); ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <div class="photo_view_tagged_users mb-2 font_color_light font_small" id="media_tags" style="display: none;">
      <?php echo $this->translate('Tagged:') ?>
    </div>
    <?php if (engine_count($this->photoTags )):?>
      <div class="photo_view_tags mb-2">
        <?php foreach ($this->photoTags as $tag): ?>
          <?php if ($tag->getTag()->getType() == 'core_tag'): ?>
          <a href='javascript:void(0);' onclick='javascript:tagAction(<?php echo $tag->getTag()->getIdentity(); ?>);'>#<?php echo $tag->getTag()->getTitle();?></a>&nbsp;
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('album.enable.rating', 1)) { ?>
      <div class="photo_view_rating">
        <?php echo $this->partial('_rating.tpl', 'core', array('item' => $this->photo, 'module' => 'album', 'param' => 'create', 'notificationType' => 'album_photo_rating')); ?>
      </div>
    <?php } ?>
  </div>
</div>
<script type="text/javascript">
    scriptJquery('.core_main_album').parent().addClass('active');
</script>