<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9981 2013-03-19 22:17:08Z john $
 * @access	   John
 */
?>

<?php if( $this->paginator->count() > 1 || $this->canUpload ): ?>
  <div class="profile_tab_options">
    <?php if( $this->canUpload ): ?>
      <div>
        <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'photo', 'action' => 'upload', 'subject' => $this->subject()->getGuid()), $this->translate('Upload Photos'), array('class' => 'btn btn-alt icon_add')) ?>
      </div>
    <?php endif; ?>
    <?php if( $this->paginator->count() > 1 ): ?>
      <div>
        <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'photo', 'action' => 'list', 'subject' => $this->subject()->getGuid()), $this->translate('View All Photos'), array( 'class' => 'btn btn-alt icon_viewall')) ?>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
<div class="container no-padding">
  <div class="row">
    <?php foreach( $this->paginator as $photo ): ?>
    <div class="col-lg-4 col-md-6 grid_outer">
      <div class="grid_wrapper albums_block">
        <a class="thumbs_photo" href="<?php echo $photo->getHref(); ?>">
          <?php echo $this->itemBackgroundPhoto($photo, 'thumb.profile')?>          
        </a>
        <p class="thumbs_info">
          <?php echo $this->translate('By');?>
          <?php echo $this->htmlLink($photo->getOwner()->getHref(), $photo->getOwner()->getTitle(), array('class' => 'thumbs_author')) ?>
          <br />
          <?php echo $this->timestamp($photo->creation_date) ?>
        </p>
      </div>
    </div>
    <?php endforeach;?>
  </div>
</div>
<?php else: ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('No photos have been uploaded to this event yet.');?></p>
  </div>
<?php endif; ?>
