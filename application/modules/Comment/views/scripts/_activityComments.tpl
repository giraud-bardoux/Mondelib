<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _activityComments.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
 
?>

<?php if( empty($this->actions) ) {
  echo $this->translate("The action you are looking for does not exist.");
  return;
} else {
   $actions = $this->actions;
}
  $isOnThisDayPage = !empty($this->isOnThisDayPage) ? true : false;
  $isPageSubject = empty($this->isPageSubject) ? $this->viewer() : $this->isPageSubject;
  $params = !empty($this->params) ? $this->params : '';
  
 ?>

<?php if( !$this->getUpdate && $this->onlyComment): ?>
<ul class='comment-feed'>
<?php endif ?>
<?php
  foreach( $actions as $action ): // (goes to the end of the file)


    try { // prevents a bad feed item from destroying the entire page
      // Moved to controller, but the items are kept in memory, so it shouldn't 'hurt to double-check
      if( !$action->getTypeInfo()->enabled ) continue;
      if( !$action->getSubject() || !$action->getSubject()->getIdentity() ) continue;
      if( !$action->getObject() || !$action->getObject()->getIdentity() ) continue;
      ob_start();
    ?>
  <?php if( !$this->noList && $this->onlyComment): ?>
  <li id="activity-item-<?php echo $action->action_id ?>" data-activity-feed-item="<?php echo $action->action_id ?>"><?php endif; ?>
      <?php
        $canComment = ( $action->getTypeInfo()->commentable &&
            $this->viewer()->getIdentity() &&
            Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment') &&
            !empty($this->commentForm) );
      ?>
	      <?php if($action && $action->commentable){ ?>
      <?php if( $action->getTypeInfo()->commentable ): // Comments - likes ?>
      <?php if($this->onlyComment){ ?>
       <li>
       <div class='activity_feed_stats _comments _comment_comments' >
	      <ul class="comments_cnt_ul">
              <?php
                   echo $this->partial(
                      '_activitylikereaction.tpl',
                      'comment',
                      array('comment'=>@$comment,'action'=>$action,'isOnThisDayPage'=>$isOnThisDayPage,'isPageSubject'=>$this->isPageSubject)
                    );                    
                  ?>
          <?php  } ?>  
           <?php if($this->onlyComment){ ?> 
          </ul>
        </div> 
        <?php } ?>
      <?php endif; ?>
    <?php } ?>
     <?php if($this->onlyComment){ ?>
      <div class='feed_item_date feed_item_icon <?php // echo $icon_type ?>'>
        <ul>

        <?php if($action && $action->commentable && !$isOnThisDayPage){ ?>
          <?php if( $canComment ): ?>
            <?php 
             if($likeRow =  $action->likes()->getLike($isPageSubject) ){ 
                
                $like = true;
                if($likeRow->type)
                  $type = $likeRow->type;
                else 
                  $type = 1;
                $imageLike = Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($type);
                $text = Engine_Api::_()->getDbTable('reactions', 'comment')->likeWord($type);
             }else{
                $like = false;
                $type = '';
                $imageLike = '';
                $text = 'CORELIKEC';
             }
             ?>
              <li class="feed_item_option_<?php echo $like ? 'unlike' : 'like'; ?> actionBox showEmotions comment_hoverbox_wrapper">
                <?php $getReactions = Engine_Api::_()->getDbTable('reactions', 'comment')->getReactions(array('userside' => 1, 'fetchAll' => 1)); ?>
                <?php if(engine_count($getReactions) > 0): ?>
                  <div class="comment_hoverbox">
                    <?php foreach($getReactions as $getReaction): ?>
                      <span>
                        <span data-text="<?php echo $this->translate($getReaction->title);?>" data-actionid = "<?php echo  $action->action_id; ?>" data-type="<?php echo $getReaction->reaction_id; ?>" data-guid="<?php echo $isPageSubject->getGuid(); ?>" class="commentlike reaction_btn comment_hoverbox_btn"><div class="reaction comment_hoverbox_btn_icon"> <i class="react"  style="background-image:url(<?php echo Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($getReaction->reaction_id);?>)"></i> </div></span>
                        <div class="text">
                          <div><?php echo $this->translate($getReaction->title); ?></div>
                        </div>
                      </span> 
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <a href="javascript:void(0);" data-guid="<?php echo $isPageSubject->getGuid(); ?>" data-like="<?php echo $this->translate('CORELIKEC') ?>" data-unlike="<?php echo $this->translate('COREUNLIKEC') ?>" data-actionid = "<?php echo  $action->action_id; ?>" data-type="1" class="comment<?php echo $like ? 'unlike _reaction' : 'like' ;  ?>">
                  <i <?php if($imageLike){ ?> style="background-image:url(<?php echo $imageLike; ?>)" <?php } ?>><svg viewBox="0 0 24 24"><path d="M22.773,7.721A4.994,4.994,0,0,0,19,6H15.011l.336-2.041A3.037,3.037,0,0,0,9.626,2.122L7.712,6H5a5.006,5.006,0,0,0-5,5v5a5.006,5.006,0,0,0,5,5H18.3a5.024,5.024,0,0,0,4.951-4.3l.705-5A5,5,0,0,0,22.773,7.721ZM2,16V11A3,3,0,0,1,5,8H7V19H5A3,3,0,0,1,2,16Zm19.971-4.581-.706,5A3.012,3.012,0,0,1,18.3,19H9V7.734a1,1,0,0,0,.23-.292l2.189-4.435A1.07,1.07,0,0,1,13.141,2.8a1.024,1.024,0,0,1,.233.84l-.528,3.2A1,1,0,0,0,13.833,8H19a3,3,0,0,1,2.971,3.419Z"/></svg></i>
                  <span><?php echo $this->translate($text);?></span>
                </a> 
              </li>
            <?php if( Engine_Api::_()->getApi('settings', 'core')->core_spam_comment ): // Comments - likes ?>
              <li class="feed_item_option_comment">
              	<a id="adv_comment_btn_<?php echo $action->getIdentity(); ?>" href="<?php echo $this->url(array('module'=>'activity','controller'=>'index','action'=>'viewcomment','action_id'=>$action->getIdentity(),'format'=>'smoothbox'),'default',true); ?>" class="openSmoothbox">
                  <i><svg viewBox="0 0 24 24"><path d="m13.5,10.5c0,.828-.672,1.5-1.5,1.5s-1.5-.672-1.5-1.5.672-1.5,1.5-1.5,1.5.672,1.5,1.5Zm3.5-1.5c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm-10,0c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm17-5v12c0,2.206-1.794,4-4,4h-2.852l-3.848,3.18c-.361.322-.824.484-1.292.484-.476,0-.955-.168-1.337-.507l-3.749-3.157h-2.923c-2.206,0-4-1.794-4-4V4C0,1.794,1.794,0,4,0h16c2.206,0,4,1.794,4,4Zm-2,0c0-1.103-.897-2-2-2H4c-1.103,0-2,.897-2,2v12c0,1.103.897,2,2,2h3.288c.235,0,.464.083.645.235l4.048,3.41,4.171-3.416c.179-.148.404-.229.637-.229h3.212c1.103,0,2-.897,2-2V4Z"/></svg></i>
                  <span><?php echo $this->translate('CORECOMMENT');?></span>
                </a>              
              </li>
            <?php else: ?>
              <li class="feed_item_option_comment">
              	<a href="javascript:void(0);" id="adv_comment_btn_<?php echo $action->getIdentity(); ?>" class="advanced_comment_btn">
                  <i><svg viewBox="0 0 24 24"><path d="m13.5,10.5c0,.828-.672,1.5-1.5,1.5s-1.5-.672-1.5-1.5.672-1.5,1.5-1.5,1.5.672,1.5,1.5Zm3.5-1.5c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm-10,0c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm17-5v12c0,2.206-1.794,4-4,4h-2.852l-3.848,3.18c-.361.322-.824.484-1.292.484-.476,0-.955-.168-1.337-.507l-3.749-3.157h-2.923c-2.206,0-4-1.794-4-4V4C0,1.794,1.794,0,4,0h16c2.206,0,4,1.794,4,4Zm-2,0c0-1.103-.897-2-2-2H4c-1.103,0-2,.897-2,2v12c0,1.103.897,2,2,2h3.288c.235,0,.464.083.645.235l4.048,3.41,4.171-3.416c.179-.148.404-.229.637-.229h3.212c1.103,0,2-.897,2-2V4Z"/></svg></i>
                  <span><?php echo $this->translate('CORECOMMENT');?></span>
                </a>
              </li>
            <?php endif; ?>
          <?php endif; ?>
        <?php } ?>  
          <?php $eneblelikecommentshare = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.eneblelikecommentshare', 1);
          $viewer_id = $this->viewer()->getIdentity(); ?>
          <?php //Show like, comment and share to non loggined member accorditg to admin settings
            if($eneblelikecommentshare && empty($viewer_id)) { ?>
            <li class="feed_item_option_like">
              <a href="<?php echo $this->url(array(), 'user_login', true); ?>" class="">
                <i><svg viewBox="0 0 24 24"><path d="M22.773,7.721A4.994,4.994,0,0,0,19,6H15.011l.336-2.041A3.037,3.037,0,0,0,9.626,2.122L7.712,6H5a5.006,5.006,0,0,0-5,5v5a5.006,5.006,0,0,0,5,5H18.3a5.024,5.024,0,0,0,4.951-4.3l.705-5A5,5,0,0,0,22.773,7.721ZM2,16V11A3,3,0,0,1,5,8H7V19H5A3,3,0,0,1,2,16Zm19.971-4.581-.706,5A3.012,3.012,0,0,1,18.3,19H9V7.734a1,1,0,0,0,.23-.292l2.189-4.435A1.07,1.07,0,0,1,13.141,2.8a1.024,1.024,0,0,1,.233.84l-.528,3.2A1,1,0,0,0,13.833,8H19a3,3,0,0,1,2.971,3.419Z"/></svg></i>
                <span><?php echo $this->translate('CORELIKEC');?></span>
              </a>
            </li> 
            <li class="feed_item_option_comment">
            <a href="javascript:void(0);" id="adv_comment_btn_<?php echo $action->getIdentity(); ?>" class="advanced_comment_btn">
            <i><svg viewBox="0 0 24 24"><path d="m13.5,10.5c0,.828-.672,1.5-1.5,1.5s-1.5-.672-1.5-1.5.672-1.5,1.5-1.5,1.5.672,1.5,1.5Zm3.5-1.5c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm-10,0c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm17-5v12c0,2.206-1.794,4-4,4h-2.852l-3.848,3.18c-.361.322-.824.484-1.292.484-.476,0-.955-.168-1.337-.507l-3.749-3.157h-2.923c-2.206,0-4-1.794-4-4V4C0,1.794,1.794,0,4,0h16c2.206,0,4,1.794,4,4Zm-2,0c0-1.103-.897-2-2-2H4c-1.103,0-2,.897-2,2v12c0,1.103.897,2,2,2h3.288c.235,0,.464.083.645.235l4.048,3.41,4.171-3.416c.179-.148.404-.229.637-.229h3.212c1.103,0,2-.897,2-2V4Z"/></svg></i>
                <span><?php echo $this->translate('CORECOMMENT');?></span>
              </a>
            </li>
          <?php } ?>
          
          <?php // Share ?>
          <?php if(empty($_SESSION['fromActivityFeed'])){ ?>
          <?php if( $action->getTypeInfo()->shareable): ?>
            <?php if($action && $action->getTypeInfo()->shareable == 1 && ($attachment = $action->getFirstAttachment('comment')) ): ?>
              <li class="feed_item_option_share">
                <a href="<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'share', 'type' => $attachment->item->getType(), 'id' => $attachment->item->getIdentity(), 'format' => 'smoothbox', 'action_id' => $action->getIdentity()), 'default', true); ?>" class="openSmoothbox">
                	<i><svg viewBox="0 0 24 24"><path d="M19.333,14.667a4.66,4.66,0,0,0-3.839,2.024L8.985,13.752a4.574,4.574,0,0,0,.005-3.488l6.5-2.954a4.66,4.66,0,1,0-.827-2.643,4.633,4.633,0,0,0,.08.786L7.833,8.593a4.668,4.668,0,1,0-.015,6.827l6.928,3.128a4.736,4.736,0,0,0-.079.785,4.667,4.667,0,1,0,4.666-4.666ZM19.333,2a2.667,2.667,0,1,1-2.666,2.667A2.669,2.669,0,0,1,19.333,2ZM4.667,14.667A2.667,2.667,0,1,1,7.333,12,2.67,2.67,0,0,1,4.667,14.667ZM19.333,22A2.667,2.667,0,1,1,22,19.333,2.669,2.669,0,0,1,19.333,22Z"/></svg></i>
                  <span><?php echo $this->translate('CORESHARE');?></span>
                </a>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 2 ): ?>
              <li class="feed_item_option_share">
                 <a href="<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getSubject()->getType(), 'id' => $action->getSubject()->getIdentity(), 'format' => 'smoothbox'), 'default', true); ?>" class="openSmoothbox">
                  <i><svg viewBox="0 0 24 24"><path d="M19.333,14.667a4.66,4.66,0,0,0-3.839,2.024L8.985,13.752a4.574,4.574,0,0,0,.005-3.488l6.5-2.954a4.66,4.66,0,1,0-.827-2.643,4.633,4.633,0,0,0,.08.786L7.833,8.593a4.668,4.668,0,1,0-.015,6.827l6.928,3.128a4.736,4.736,0,0,0-.079.785,4.667,4.667,0,1,0,4.666-4.666ZM19.333,2a2.667,2.667,0,1,1-2.666,2.667A2.669,2.669,0,0,1,19.333,2ZM4.667,14.667A2.667,2.667,0,1,1,7.333,12,2.67,2.67,0,0,1,4.667,14.667ZM19.333,22A2.667,2.667,0,1,1,22,19.333,2.669,2.669,0,0,1,19.333,22Z"/></svg></i>
                  <span><?php echo $this->translate('CORESHARE');?></span>
                </a>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 3 ): ?>
              <li class="feed_item_option_share">
                 <a href="<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getObject()->getType(), 'id' => $action->getObject()->getIdentity(), 'format' => 'smoothbox', 'action_id' => $action->getIdentity()), 'default', true); ?>" class="openSmoothbox">
                  <i><svg viewBox="0 0 24 24"><path d="M19.333,14.667a4.66,4.66,0,0,0-3.839,2.024L8.985,13.752a4.574,4.574,0,0,0,.005-3.488l6.5-2.954a4.66,4.66,0,1,0-.827-2.643,4.633,4.633,0,0,0,.08.786L7.833,8.593a4.668,4.668,0,1,0-.015,6.827l6.928,3.128a4.736,4.736,0,0,0-.079.785,4.667,4.667,0,1,0,4.666-4.666ZM19.333,2a2.667,2.667,0,1,1-2.666,2.667A2.669,2.669,0,0,1,19.333,2ZM4.667,14.667A2.667,2.667,0,1,1,7.333,12,2.67,2.67,0,0,1,4.667,14.667ZM19.333,22A2.667,2.667,0,1,1,22,19.333,2.669,2.669,0,0,1,19.333,22Z"/></svg></i>
                  <span><?php echo $this->translate('CORESHARE');?></span>
                </a>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 4 ): ?>
              <li class="feed_item_option_share">
								<a href="<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'share', 'type' => $action->getType(), 'id' => $action->getIdentity(), 'action_id' => $action->getIdentity(), 'format' => 'smoothbox'), 'default', true); ?>" class="openSmoothbox">
                  <i><svg viewBox="0 0 24 24"><path d="M19.333,14.667a4.66,4.66,0,0,0-3.839,2.024L8.985,13.752a4.574,4.574,0,0,0,.005-3.488l6.5-2.954a4.66,4.66,0,1,0-.827-2.643,4.633,4.633,0,0,0,.08.786L7.833,8.593a4.668,4.668,0,1,0-.015,6.827l6.928,3.128a4.736,4.736,0,0,0-.079.785,4.667,4.667,0,1,0,4.666-4.666ZM19.333,2a2.667,2.667,0,1,1-2.666,2.667A2.669,2.669,0,0,1,19.333,2ZM4.667,14.667A2.667,2.667,0,1,1,7.333,12,2.67,2.67,0,0,1,4.667,14.667ZM19.333,22A2.667,2.667,0,1,1,22,19.333,2.669,2.669,0,0,1,19.333,22Z"/></svg></i>
                  <span><?php echo $this->translate('CORESHARE');?></span>
                </a>
              </li>
            <?php elseif( $action->getTypeInfo()->shareable == 5 ):
                  $attachment = $action->getBuySellItem();
             ?>
              <li class="feed_item_option_share">
                <a href="<?php echo $this->url(array('module' => 'activity', 'controller' => 'index', 'action' => 'share', 'type' => $attachment->getType(), 'id' => $attachment->getIdentity(), 'format' => 'smoothbox', 'action_id' => $action->getIdentity()), 'default', true); ?>" class="openSmoothbox">
                  <i><svg viewBox="0 0 24 24"><path d="M19.333,14.667a4.66,4.66,0,0,0-3.839,2.024L8.985,13.752a4.574,4.574,0,0,0,.005-3.488l6.5-2.954a4.66,4.66,0,1,0-.827-2.643,4.633,4.633,0,0,0,.08.786L7.833,8.593a4.668,4.668,0,1,0-.015,6.827l6.928,3.128a4.736,4.736,0,0,0-.079.785,4.667,4.667,0,1,0,4.666-4.666ZM19.333,2a2.667,2.667,0,1,1-2.666,2.667A2.669,2.669,0,0,1,19.333,2ZM4.667,14.667A2.667,2.667,0,1,1,7.333,12,2.67,2.67,0,0,1,4.667,14.667ZM19.333,22A2.667,2.667,0,1,1,22,19.333,2.669,2.669,0,0,1,19.333,22Z"/></svg></i>
                  <span><?php echo $this->translate('CORESHARE');?></span>
                </a>
              </li>
            <?php endif; ?>
          <?php endif; ?>
            <?php $content = $action->body; ?>
            <?php if($action->type != "friends" && strlen(preg_replace("/(\\\u[0-9a-f]{4})+?|\s+/","",strip_tags($content))) && Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.translate', 1)){
              $languageTranslate = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.language', 'en');
             ?>
              <li class="feed_item_option_translate">
                <a href="javascript:void(0);" onClick="translateTextWithLink('<?php echo $action->getIdentity(); ?>', '<?php echo $languageTranslate; ?>');">
                 <i><svg viewBox="0 -960 960 960" fill="#5f6368"><path d="m476-80 182-480h84L924-80h-84l-43-122H603L560-80h-84ZM160-200l-56-56 202-202q-35-35-63.5-80T190-640h84q20 39 40 68t48 58q33-33 68.5-92.5T484-720H40v-80h280v-80h80v80h280v80H564q-21 72-63 148t-83 116l96 98-30 82-122-125-202 201Zm468-72h144l-72-204-72 204Z"/></svg></i>
				        <span><?php echo $this->translate("Translate"); ?> </span></a>
              </li>	
            <?php } ?>
          <?php } ?>
          <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.enablenactivityupdownvote', 0)){ ?>
            <?php echo $this->partial('_updownvote.tpl', 'comment', array('item' => $action,'isPageSubject'=>$this->isPageSubject)); ?>
          <?php } ?>
          
          <?php echo $this->partial('_epage_content.tpl', 'comment', array('action' => $action,'isPageSubject'=>$this->isPageSubject)); ?>
          <?php echo $this->partial('_egroup_content.tpl', 'comment', array('action' => $action,'isPageSubject'=>$this->isPageSubject)); ?>
          <?php echo $this->partial('_ebusiness_content.tpl', 'comment', array('action' => $action,'isPageSubject'=>$this->isPageSubject)); ?>
            <?php echo $this->partial('_estore_content.tpl', 'comment', array('action' => $action,'isPageSubject'=>$this->isPageSubject)); ?>
        </ul>
      </div>
     <?php } ?>
    
    <?php if($action && $action->commentable){ ?>
      <?php if( $action->getTypeInfo()->commentable ): // Comments - likes ?>
      
      <div class='comments comment_comments' style="display:none;" data-json='<?php echo json_encode(array('isOnThisDayPage'=>$isOnThisDayPage ? $isOnThisDayPage : "",'isPageSubject'=>$this->isPageSubject ? $this->isPageSubject : "","searchType"=>$this->type ? $this->type : "",'onlyComment'=>$this->onlyComment ? $this->onlyComment : "")) ?>' id="activity-comment-item-<?php echo $action->action_id ?>">
      <?php if( $canComment && !$isOnThisDayPage ){ ?>
        <form name="myForm"  class="activity-comment-form advcomment_form" method="post">
          <div class="comments_author_photo comment_user_img">
            <?php echo $this->itemPhoto($isPageSubject, 'thumb.icon', $isPageSubject->getTitle()); ?>
          </div>
          <?php echo $this->partial('_commentAttachments.tpl', 'comment', array('item' => $action, 'type' => 'activitycomments')); ?>
        </form>
      <?php } ?>
	    <ul class="comments_cnt_ul">
        <?php if($this->onlyComment) { ?> 
      </ul>
        </div> 
        <?php } ?>
				</li>
      <?php endif; ?>
    <?php } ?>
   <!--  </div> -->
  <?php if( !$this->noList ): ?></li><?php endif; ?>
<?php
      ob_end_flush();
    } catch (Exception $e) {
      ob_end_clean();
      if( APPLICATION_ENV === 'development' ) {
        echo $e->__toString();
      }
    };
  endforeach;
?>
<?php if( !$this->getUpdate && $this->onlyComment):  ?>
</ul>
<?php endif ?>
