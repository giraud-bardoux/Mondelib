<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: settings.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
  
?>
<?php if(!$this->is_ajax){ ?>
<div class="users_listing_popup clearfix ">
  <div class="users_listing_popup_header clearfix">
 	  <?php echo $this->translate("$this->title"); ?>
  </div>
  <div class="clearfix">
<?php } ?>
    <form method="post" id="activity_settings_form" style="position:relative;">
      <div class="users_listing_popup_cont_inner">
        <div class="container_like_contnent_main" id="container_like_contnent">
          <ul id="like_contnent">
              <?php
                echo $this->partial(
                    '_contentlikesuser.tpl',
                    'activity',
                    array('users'=>$this->users,'paginator'=>$this->paginator,'randonNumber'=>'contentlikeusers','resource_id'=>$this->resource_id,'resource_type'=>$this->resource_type,'execute'=>true,'page'=>$this->page,'comment_id'=>$this->comment_id,'checkbox'=>true, 'notdie'=>true)
                  );                    
                ?>   
            <?php if(!$this->paginator->count()){ ?>
              <li class="_tip"><?php echo $this->translate("You have not hidden activity feed(s) from any user."); ?></li>
            <?php } ?>
            <?php $randonNumber= 'contentlikeusers'; ?>
          </ul>
          <div class="load_more" style="display: none;" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" >
            <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_$randonNumber", 'class' => ' btn btn-alt')); ?>
          </div>
          <div class="load_more view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;"><i class="icon_loading"></i></div>
        </div> 
  <?php if($this->paginator->count()){ ?>
      </div>
      <div class="users_listing_popup_footer">
        <button class="btn btn-primary" type="submit"><?php echo $this->translate("Remove Selected"); ?></button>
        <button class="btn btn-link" type="submit" onClick="ajaxsmoothboxclose();return false;"><?php echo $this->translate("Cancel"); ?></button>
      </div>
  <?php } ?>
      <div class="core_loading_cont_overlay" style="display:none;"></div>
    </form>
  </div>
</div>
