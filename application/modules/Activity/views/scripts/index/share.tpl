<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: share.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . ''); ?>
<div class="activity_share_popup">
  <?php echo $this->form->setAttrib('class', 'global_form_popup')->render($this) ?>
  <?php if($this->viewer()->getIdentity()){ ?>
  <div class="sharebox_attachment">
   <?php if (!empty($this->action)){
        $previousAction = '1';
        $previousAttachment = '1';
        $action = $this->action;
        echo "<div class='activity_feed'><ul class='feed'>";
        include('application/modules/Activity/views/scripts/_activity.tpl');
        echo "</ul></div>";
    }else{ ?>
    <?php if( $this->attachment->getPhotoUrl() ): ?>
      <div class="sharebox_attachment_img">
        <?php echo $this->htmlLink($this->attachment->getHref(), $this->itemPhoto($this->attachment, 'thumb.icon'), array('target' => '_parent')) ?>
      </div>
    <?php endif; ?>
    <div class="sharebox_attachment_content">
      <div class="sharebox_attachment_title">
        <?php echo $this->htmlLink($this->attachment->getHref(), $this->attachment->getTitle(), array('class' => 'font_color font_bold') , array('target' => '_parent')) ?>
      </div>
      <div class="sharebox_attachment_desc">
        <?php 
          if($this->attachment->getType() == 'activity_action') {
            $content =  $this->getContent($this->attachment);
            echo $content[0].': '.$content[1];
          }
          else
            echo $this->attachment->getDescription();
       ?>
      </div>
    </div>
    <?php } ?>
    
  </div>
  <?php } ?>
</div>
<script type="text/javascript">
scriptJquery('.sharebox_description > a').attr('href','javascript:;');
scriptJquery('.sharebox_description').find('.core_tooltip').removeClass('core_tooltip');
</script>
