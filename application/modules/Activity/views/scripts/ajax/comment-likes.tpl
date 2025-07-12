<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: comment-likes.tpl 2024-10-28 00:00:00Z 
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
 	  <?php echo $this->translate($this->title); ?>
 </div>
 <div class="users_listing_popup_cont">
<?php } ?>

 <div class="container_like_contnent_main users_listing_popup_cont_inner" id="container_like_contnent">
 	<ul id="like_contnent">
  
       <?php
         echo $this->partial(
            '_contentlikesuser.tpl',
            'activity',
            array('users'=>$this->users,'paginator'=>$this->paginator,'randonNumber'=>'contentlikeusers','resource_id'=>$this->resource_id,'resource_type'=>$this->resource_type,'execute'=>true,'page'=>$this->page,'comment_id'=>$this->comment_id)
          );                    
        ?>   
    <?php $randonNumber= 'contentlikeusers'; ?>

 </ul>
 
<div class="load_more" style="display: none;" id="view_more_<?php echo $randonNumber;?>" onclick="viewMore_<?php echo $randonNumber; ?>();" >
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_$randonNumber", 'class' => ' btn btn-alt')); ?>
</div>
<div class="load_more view_more_loading_<?php echo $randonNumber;?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;"><i class="icon_loading"></i></div>
 
 </div> 
 </div>
</div>
