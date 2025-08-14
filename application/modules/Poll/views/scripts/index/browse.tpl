<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: browse.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */
?>

<?php if( 0 == engine_count($this->paginator) ): ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('There are no polls yet.') ?></p>
    <?php if( $this->canCreate): ?>
      <p><?php echo $this->translate('Why don\'t you %1$screate one%2$s?', '<a href="'.$this->url(array('action' => 'create'), 'poll_general').'">', '</a>') ?></p>
    <?php endif; ?>
  </div>
<?php else: // $this->polls is NOT empty ?>

<div class="container no-padding polls_listing">
  <div class='row grid_listing'>
    <?php foreach ($this->paginator as $poll): ?>
      <div class="col-lg-4 col-md-6 grid_listing_item" id="poll-item-<?php echo $poll->poll_id ?>">
        <article>
          <div class='grid_listing_item_thumb'>
              <?php echo $this->htmlLink($poll->getHref(), $this->itemBackgroundPhoto($poll, 'thumb.normal')) ?>
          </div>
          <div class="grid_listing_item_info">
            <div class="grid_listing_item_title">
              <?php echo $this->htmlLink($poll->getHref(), $poll->getTitle()) ?>
              <?php if( $poll->closed ): ?>
                <i class="icon_closed" data-bs-toggle="tooltip" title="<?php echo $this->translate("Closed")?>"></i>
              <?php endif ?>
            </div>
            <div class='grid_listing_item_owner'>
              <span><?php echo $this->htmlLink($poll->getOwner()->getHref(), $poll->getOwner()->getTitle()) ?></span>
              <span><?php echo $this->timestamp(strtotime($poll->creation_date)) ?></span>
              <?php // echo $this->partial('_rating.tpl', 'core', array('item' => $poll, 'param' => 'show', 'module' => 'poll')); ?>
            </div>
   
            <div class="grid_listing_item_owner">
              <span><i class="icon_vote"></i><?php echo $this->translate(array('%s vote', '%s votes', $poll->vote_count), $this->locale()->toNumber($poll->vote_count)) ?></span>
              <span><i class="icon_view"></i><?php echo $this->translate(array('%s view', '%s views', $poll->view_count), $this->locale()->toNumber($poll->view_count)) ?></span>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</div>


<?php endif; // $this->polls is NOT empty ?>

<?php echo $this->paginationControl($this->paginator, null, null, array(
  'pageAsQuery' => true,
  'query' => $this->formValues,
  //'params' => $this->formValues,
)); ?>
