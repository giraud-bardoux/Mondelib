<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<script type="text/javascript">
  function loadMoreSell() {
    if (document.getElementById('view_more_sell'))
      document.getElementById('view_more_sell').style.display = "<?php echo ( $this->paginator->count() == $this->paginator->getCurrentPageNumber() || $this->count == 0 ? 'none' : '' ) ?>";

    if(document.getElementById('view_more_sell'))
      document.getElementById('view_more_sell').style.display = 'none';
    
    if(document.getElementById('loading_image_sell'))
     document.getElementById('loading_image_sell').style.display = '';

    (scriptJquery.ajax({
      method: 'post',
      'url': en4.core.baseUrl + 'widget/index/mod/activity/name/sell-something',
      'data': {
        format: 'html',
        page: "<?php echo sprintf('%d', $this->paginator->getCurrentPageNumber() + 1) ?>",
        viewmore: 1,
        params: '<?php echo json_encode($this->all_params); ?>',
        
      },
      success : function(responseHTML) {
        scriptJquery('#sell_results').append(responseHTML);
        if(document.getElementById('view_more_sell'))
          scriptJquery('#view_more_sell').remove();
        
        if(document.getElementById('loading_image_sell'))
         scriptJquery('#loading_image_sell').remove();
        if(document.getElementById('loadmore_list_sell'))
         scriptJquery('#loadmore_list_sell').remove();
      }
    }));
    return false;
  }
</script>

<?php if($this->paginator->getTotalItemCount() > 0) { ?>
  <?php if (empty($this->viewmore)): ?>
  <div class="activity_sell_main" >
    <div class="activity_sell_inner" id= "sell_results">
  <?php endif; ?>
      <?php foreach($this->paginator as $item) { ?> 
        <?php $action = Engine_Api::_()->getItem('activity_action', $item->action_id);
        $attachmentItems = $action->getAttachments();
        $actionAttachment = engine_count($attachmentItems) ? $attachmentItems : array();
        list($attachment) = $actionAttachment;
        $photo = Engine_Api::_()->getItem('album_photo', $attachment->item->photo_id);
        ?>
        <div class="activity_sell_box">
          <article>
            <a class="ajaxsmoothbox activity_buysell" href="javascript:;" data-url="<?php echo 'activity/ajax/feed-buy-sell/action_id/'.$action->action_id.'/photo_id/'.$attachment->item->photo_id.'/main_action/'.$action->action_id; ?>">
              <div class="_img">
                <?php if($photo) { ?>
                  <img src="<?php echo $photo->getPhotoUrl(); ?>" />
                <?php } else { ?>
                  <img src="application/modules/Activity/externals/images/default.png" />
                <?php } ?>
              </div>
              <div class="activity_sell_box_info">
                <div class="activity_feed_item_buysell_title"><?php echo $item->title; ?></div>
                <div class="activity_feed_item_buysell_price font_color_hl"><?php echo Engine_Api::_()->payment()->getCurrencySymbol().$item->price; ?></div>
                <?php $locationBuySell = Engine_Api::_()->getDbTable('locations','core')->getLocationData(array('resource_type'=>'activity_buysell','resource_id'=>$item->getIdentity())) ?>
                <?php if($locationBuySell){ ?>
                  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) { ?>
                    <div class="activity_feed_item_buysell_location font_color_light"><span><a target="_blank" class="font_color_light" href="<?php echo 'http://maps.google.com/?q='.$locationBuySell->venue; ?>"><?php echo $locationBuySell->venue; ?></a></span></div>
                  <?php } ?>
                <?php } else if($item->location) { ?>
                  <div class="activity_feed_item_buysell_location font_color_light"><span><a target="_blank" class="font_color_light" href="<?php echo 'http://maps.google.com/?q='.$item->location; ?>"><?php echo $item->location; ?></a></span></div>
                <?php } ?>
              </div>
            </a>
          </article>
        </div>
      <?php } ?>
      
      <?php if (!empty($this->paginator) && $this->paginator->count() > 1): ?>
        <?php if ($this->paginator->getCurrentPageNumber() < $this->paginator->count()): ?>
          <div class="clr" id="loadmore_list_sell"></div>
          <div class="load_more" id="view_more_sell" onclick="loadMoreSell();" style="display: block;">
            <a href="javascript:void(0);" class=" btn btn-alt" ><span><?php echo $this->translate('View More');?></span></a>
          </div>
          <div class="load_more" id="loading_image_sell" style="display: none;">
            <span class="btn btn-alt"><i class="icon_loading"></i></span>
          </div>
        <?php endif; ?>
      <?php endif; ?>
<?php if (empty($this->viewmore)): ?>
    </div>
  </div>
<?php endif; ?>
<?php } else { ?>
  <div class="no_result_tip">
    <i><img src="application/modules/Core/externals/images/no-results.png" height="100" width="100" alt="<?php echo $this->translate('No Result'); ?>"> </i>
    <p><?php echo $this->translate('Nobody has posted sell something activity.');?></p>
  </div>
<?php } ?>

