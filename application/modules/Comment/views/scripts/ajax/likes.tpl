<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: likes.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php $isPageSubject = !empty($this->isPageSubject) ? $this->isPageSubject : $this->viewer(); ?>
<?php if (!$this->is_ajax) { ?>
  <div class="users_listing_popup ">
    <div class="users_listing_popup_tabs">
      <ul class="clearfix like_main_cnt_reaction">
        <li <?php if ($this->typeSelected == 'all') { ?> class="active" <?php } ?>>
          <a href="javascript:;" data-type="comment" data-rel="all">
            <span>All <?php echo $this->countAll; ?></span>
          </a>
        </li>
        <?php foreach ($this->AllTypesCount as $AllTypesCount) { ?>
          <li <?php if ($this->typeSelected == $AllTypesCount['type']) { ?> class="active" <?php } ?>>
            <a href="javascript:;" data-type="comment" data-rel="<?php echo $AllTypesCount['type']; ?>">
              <i
                style="background-image:url(<?php echo Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($AllTypesCount['type']); ?>);"></i>
              <span><?php echo $AllTypesCount['counts']; ?></span>
            </a>
          </li>
        <?php } ?>
      </ul>
    </div>
    <div class="users_listing_popup_cont ">
    <?php } ?>

    <?php foreach ($this->typesLikeData as $key => $itemTypes) { ?>
      <div class="container_like_contnent_main users_listing_popup_cont_inner"
        id="container_like_contnent_<?php echo $key; ?>"
        style="display:<?php echo $this->typeSelected == $key ? 'block' : 'none'; ?>">
        <ul id="like_contnent_<?php echo $key; ?>">

          <?php
          echo $this->partial(
            '_reactionlikesuser.tpl',
            'comment',
            array('users' => $this->users, 'paginator' => $this->paginator, 'randonNumber' => $key, 'resource_id' => $this->resource_id, 'resource_type' => $this->resource_type, 'typeSelected' => $this->typeSelected, 'execute' => ($this->typeSelected == $key), 'page' => $this->page, 'type' => $this->type, 'item_id' => $this->item_id, 'isPageSubject' => $isPageSubject)
          );
          ?>
          <?php $randonNumber = $key; ?>

        </ul>

        <div class="load_more" style="display:<?php echo $this->typeSelected == $key ? 'block' : 'none'; ?>" id="view_more_<?php echo $randonNumber; ?>" onclick="viewMore_<?php echo $randonNumber; ?>();">
          <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array('id' => "feed_viewmore_link_$randonNumber", 'class' => 'btn btn-alt')); ?>
        </div>
        <div class="load_more view_more_loading_<?php echo $randonNumber; ?>" id="loading_image_<?php echo $randonNumber; ?>" style="display: none;"><i class="icon_loading"></i></div>
      </div>
    <?php } ?>

  </div>
</div>
<?php die; ?>