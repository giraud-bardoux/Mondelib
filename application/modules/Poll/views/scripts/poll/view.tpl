<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Poll
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: view.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Steve
 */
?>
<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.enable.rating', 1)) { ?>
  <script type="text/javascript">
    var modulename = 'poll';
    var pre_rate = <?php echo $this->poll->rating;?>;
    var rated = '<?php echo $this->rated;?>';
    var resource_id = <?php echo $this->poll->poll_id;?>;
    var total_votes = <?php echo $this->rating_count;?>;
    var viewer = <?php echo $this->viewer_id;?>;
    new_text = '';
    var resource_type = 'poll';
    var rating_text = "<?php echo $this->translate(array('%s rating', '%s ratings', $this->rating_count),$this->locale()->toNumber($this->rating_count)) ?>";
    var ratingIcon = '<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.ratingicon', 'fas fa-star'); ?>';
  </script>
<?php } ?>
<!--<h1>
  <?php if($this->poll->getParentItem()): ?>
    <?php echo $this->poll->getParentItem()->__toString(); ?>
    <?php echo $this->translate('&#187;'); ?>
  <?php else: ?>
    <?php echo $this->htmlLink(array('route' => 'poll_general'), "Polls", array()); ?>
    <?php echo $this->translate('&#187;'); ?>
  <?php endif; ?>
  <?php echo $this->poll->getTitle(); ?>
</h1>-->
<div class='polls_view'>
  <h3 class="polls_view_title">
    <?php echo $this->poll->title ?>
    <?php if( $this->poll->closed ): ?>
      <i class="icon_closed" data-bs-toggle="tooltip" title="<?php echo $this->translate("Closed")?>"></i>
    <?php endif ?>
  </h3>
  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('poll.enable.rating', 1)) { ?>
    <?php echo $this->partial('_rating.tpl', 'core', array('item'=>$this->poll, 'rated' => $this->rated, 'param' => 'create', 'module' => 'poll')); ?>
  <?php } ?>
  <br/>
  <div class="poll_desc">
    <?php echo Engine_Api::_()->core()->smileyToEmoticons($this->poll->description); ?>
  </div>

  <?php
    // poll, pollOptions, canVote, canChangeVote, hasVoted, showPieChart
    echo $this->render('_poll.tpl')
  ?>
  </div>

<script type="text/javascript">
    scriptJquery('.core_main_poll').parent().addClass('active');
</script>
