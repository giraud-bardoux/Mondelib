<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manage.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Steve
 */
?>

<script type="text/javascript">
  var searchPolls = function() {
    $('filter_form').submit();
  }
</script>

<?php if( 0 == engine_count($this->paginator) ): ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate("No Result")?>"></i>
    <p><?php echo $this->translate('There are no polls yet.') ?></p>
    <?php if( $this->canCreate): ?>
      <p><?php echo $this->translate('Why don\'t you %1$screate one%2$s?', '<a href="'.$this->url(array('action' => 'create'), 'poll_general').'">', '</a>') ?></p>
    <?php endif; ?>
  </div>
<?php else: // $this->polls is NOT empty ?>
  <ul class="manage_listing polls_manage">
    <?php foreach( $this->paginator as $poll ): ?>
      <li id="poll-item-<?php echo $poll->poll_id ?>" class="manage_listing_item">
        <article>
          <div class="manage_listing_thumb">
            <?php echo $this->htmlLink($poll->getHref(), $this->itemBackgroundPhoto($poll, 'thumb.normal')) ?>
          </div>
          <div class="manage_listing_info">
            <div class="manage_listing_header">
            <div class="manage_listing_title">
              <?php echo $this->htmlLink($poll->getHref(), $poll->getTitle()) ?>
              <?php if( $poll->closed ): ?>
                <i class="icon_closed" data-bs-toggle="tooltip" title="<?php echo $this->translate("Closed")?>"></i>
              <?php endif ?>
            </div>
            <div class="dropdown options_menu">
              <button class="btn btn-alt" type="button" id="manageoption" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon_option_menu"></i></button>
              <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="manageoption">
                <li><?php echo $this->htmlLink(array('route' => 'poll_specific', 'action' => 'edit', 'poll_id' => $poll->poll_id, 'reset' => true,), $this->translate('Edit Privacy'), array('class' => 'dropdown-item icon_edit')) ?></li>
                <?php if( !$poll->closed ): ?>
                  <li><?php echo $this->htmlLink(array('route' => 'poll_specific', 'action' => 'close', 'poll_id' => $poll->getIdentity(), 'closed' => 1), $this->translate('Close Poll'), array('class' => 'dropdown-item icon_close')) ?></li>
                <?php else: ?>
                  <li><?php echo $this->htmlLink(array('route' => 'poll_specific', 'action' => 'close', 'poll_id' => $poll->getIdentity(), 'closed' => 0), $this->translate('Open Poll'), array('class' => 'dropdown-item icon_open')) ?></li>
                <?php endif; ?>

                <li><?php echo $this->htmlLink(array('route' => 'poll_specific', 'action' => 'delete', 'poll_id' => $poll->getIdentity(), 'format' => 'smoothbox'
                ), $this->translate('Delete Poll'), array(
                  'class' => 'dropdown-item smoothbox icon_delete')) ?></li>
                </ul>
              </div>
            </div>
            <div class="manage_listing_owner">
              <span><?php echo $this->translate('Posted by %s', $this->htmlLink($this->owner, $this->owner->getTitle())) ?></span>
              <span><?php echo $this->timestamp($poll->creation_date) ?></span>
            </div>
            
            <div class="manage_listing_stats">
              <span><i class="icon_vote"></i><?php echo $this->translate(array('%s vote', '%s votes', $poll->vote_count), $this->locale()->toNumber($poll->vote_count)) ?></span>
              <span><i class="icon_view"></i><?php echo $this->translate(array('%s view', '%s views', $poll->view_count), $this->locale()->toNumber($poll->view_count)) ?></span>
            </div>
            
            <?php // echo $this->partial('_rating.tpl', 'core', array('item' => $poll, 'param' => 'show', 'module' => 'poll')); ?>
            
            <?php if( '' != ($description = $poll->getDescription()) ): ?>
              <?php // echo $description ?>
            <?php endif; ?>
            <?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $poll)); ?>
          </div>
        </article>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; // $this->polls is NOT empty ?>

<?php echo $this->paginationControl($this->paginator, null, null, array(
  'pageAsQuery' => true,
  'query' => $this->formValues,
  //'params' => $this->formValues,
)); ?>


<script type="text/javascript">
  scriptJquery('.core_main_poll').parent().addClass('active');
</script>
