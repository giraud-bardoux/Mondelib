<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: list.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<div class="layout_middle">
  <div class="generic_layout_container layout_core_content">
    <h2><?php echo $this->event->__toString()." ".$this->translate("&#187; Photos") ?></h2>
    <?php if( $this->canUpload ): ?>
      <div class="event_photos_list_options">
        <?php echo $this->htmlLink(array(
            'route' => 'event_extended',
            'controller' => 'photo',
            'action' => 'upload',
            'subject' => $this->subject()->getGuid(),
          ), $this->translate('Upload Photos'), array(
            'class' => 'btn btn-alt icon_add'
        )) ?>
      </div>
    <?php endif; ?>

    <div class="container no-padding">
      <div class="row event_photos_list">
        <?php foreach( $this->paginator as $photo ): ?>
          <div class="col-lg-3 col-6 grid_outer">
            <div class="grid_wrapper albums_block">
              <a class="thumbs_photo" href="<?php echo $photo->getHref(); ?>">
                <?php echo $this->itemBackgroundPhoto($photo, 'thumb.main')?>          
              </a>
            </div>
          </div>
        <?php endforeach;?>
      </div>
    </div>
    <?php if( $this->paginator->count() > 0 ): ?>
      <?php echo $this->paginationControl($this->paginator); ?>
    <?php endif; ?>
  </div>
</div>
