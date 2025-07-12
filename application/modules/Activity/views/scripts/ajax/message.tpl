<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: message.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<?php if(empty($this->isajax)){ ?>
<div class="activity_msg_pupup ">
<?php echo $this->form->render($this); ?>
</div>
<div id="attachment_content" style="display:none;">
<?php } ?>

<div class="activitiy_sell_item_attachment  clearfix">
  <?php $attachment = $this->action->getAttachments(); ?>
  <?php if(engine_count($attachment)){  
        $firstAttachment = $attachment[0]->item;
  ?>
    <div class="activitiy_sell_item_attachment_img ">
      <a href="<?php echo $this->item->getHref(); ?>"><img src="<?php echo $firstAttachment->getPhotoUrl(); ?>" class="" /></a>
    </div>
  <?php } ?>
  <div class="activitiy_sell_item_attachment_cont">
    <div class="activitiy_sell_item_attachment_title"><a href="<?php echo $this->item->getHref(); ?>"><?php echo $this->item->getTitle(); ?></a></div>
    <div class="activitiy_sell_item_attachment_price font_small"><?php echo Engine_Api::_()->payment()->getCurrencySymbol().$this->item->price; ?></div>
    <?php $location = Engine_Api::_()->getDbTable('locations','core')->getLocationData(array('resource_type'=>'activity_buysell','resource_id'=>$this->item->getIdentity())); ?>
    <?php if($location){?>
    	<div class="activitiy_sell_item_attachment_location font_small font_color_light d-flex flex-wrap g-1">
      	<i class="icon_map"></i>
      	<span><?php echo $location->venue; ?></span>
      </div>
    <?php } ?>
  </div>
</div>

<?php if(empty($this->isajax)){ ?>
</div>
<script type="application/javascript">
scriptJquery('#attachment_content_div-wrapper').html(scriptJquery('#attachment_content').html());
</script>
<?php } ?>
