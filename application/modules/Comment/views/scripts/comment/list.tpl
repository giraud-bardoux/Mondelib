<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: list.tpl 2024-10-29 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Comment
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */

?>
<?php $this->headTranslate(array('Are you sure you want to delete this?')); ?>
<script>
  var activitycommentreverseorder = <?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.commentreverseorder', 1); ?>;
</script>
<?php if ($this->is_ajax_load) { ?>
  <div id="comment_ajax_load_cnt" class="comment_ajax_load_cnt" style="position:relative;">
    <div class="loading_container" style="display:block;"></div>
  <?php } ?>
  <?php if (!$this->is_ajax_load) { ?>
    <?php $canComment = $this->canComment; ?>
    <?php if (!$this->page): ?>
      <div class='comment_list_wrapper comment-feed' id="comments">
        <div class='comment_options'>
          <ul>
            <?php if ($this->viewer()->getIdentity() && $this->canComment){
              if ($likeRow = $this->subject()->likes()->getLike($this->viewer())) {

                $like = true;
                if ($likeRow->type)
                  $type = $likeRow->type;
                else
                  $type = 1;
                $imageLike = Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($type);
                $text = Engine_Api::_()->getDbTable('reactions', 'comment')->likeWord($type);
              } else {
                $like = false;
                $type = '';
                $imageLike = '';
                $text = 'CORELIKE';
              }
              ?>
              <li
                class="feed_item_option_<?php echo $like ? 'unlike' : 'like'; ?> actionBox showEmotions comment_hoverbox_wrapper">
                <?php $getReactions = Engine_Api::_()->getDbTable('reactions', 'comment')->getReactions(array('userside' => 1, 'fetchAll' => 1)); ?>
                <?php if (engine_count($getReactions) > 0): ?>
                  <div class="comment_hoverbox">
                    <?php foreach ($getReactions as $getReaction): ?>
                      <span>
                        <span data-text="<?php echo $this->translate($getReaction->title); ?>"
                          data-subjectid="<?php echo $this->subject()->getIdentity(); ?>"
                          data-sbjecttype="<?php echo $this->subject()->getType(); ?>"
                          data-type="<?php echo $getReaction->reaction_id; ?>"
                          class="commentlike reaction_btn comment_hoverbox_btn">
                          <div class="reaction comment_hoverbox_btn_icon"> <i class="react"
                              style="background-image:url(<?php echo Engine_Api::_()->getDbTable('reactions', 'comment')->likeImage($getReaction->reaction_id); ?>)"></i>
                          </div>
                        </span>
                        <div class="text">
                          <div><?php echo $this->translate($getReaction->title); ?></div>
                        </div>
                      </span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
                <a href="javascript:void(0);" <?php if (!empty($_SESSION["corefromLightbox"])) { ?>
                    id="comment_like_action_<?php echo $this->subject()->getIdentity(); ?>" <?php $_SESSION["corefromLightbox"] = '';
                } else { ?>
                    id="comment_like_actionrec_<?php echo $this->subject()->getIdentity(); ?>" <?php } ?>
                  data-like="<?php echo $this->translate('CORELIKEC') ?>"
                  data-unlike="<?php echo $this->translate('COREUNLIKEC') ?>"
                  data-subjectid="<?php echo $this->subject()->getIdentity(); ?>"
                  data-sbjecttype="<?php echo $this->subject()->getType(); ?>" data-type="1"
                  class="comment<?php echo $like ? 'unlike _reaction' : 'like'; ?>">
                  <i <?php if ($imageLike) { ?> style="background-image:url(<?php echo $imageLike; ?>)" <?php } ?>><svg viewBox="0 0 24 24"><path d="M22.773,7.721A4.994,4.994,0,0,0,19,6H15.011l.336-2.041A3.037,3.037,0,0,0,9.626,2.122L7.712,6H5a5.006,5.006,0,0,0-5,5v5a5.006,5.006,0,0,0,5,5H18.3a5.024,5.024,0,0,0,4.951-4.3l.705-5A5,5,0,0,0,22.773,7.721ZM2,16V11A3,3,0,0,1,5,8H7V19H5A3,3,0,0,1,2,16Zm19.971-4.581-.706,5A3.012,3.012,0,0,1,18.3,19H9V7.734a1,1,0,0,0,.23-.292l2.189-4.435A1.07,1.07,0,0,1,13.141,2.8a1.024,1.024,0,0,1,.233.84l-.528,3.2A1,1,0,0,0,13.833,8H19a3,3,0,0,1,2.971,3.419Z"/></svg></i>
                  <span><?php echo $this->translate($text); ?></span>
                </a>
              </li>
              <li class="feed_item_option_comment">
                <a href="javascript:void(0);" id="adv_comment_subject_btn_<?php echo $this->subject()->getIdentity(); ?>"
                  class="advanced_comment_btn">
                  <i><svg viewBox="0 0 24 24"><path d="m13.5,10.5c0,.828-.672,1.5-1.5,1.5s-1.5-.672-1.5-1.5.672-1.5,1.5-1.5,1.5.672,1.5,1.5Zm3.5-1.5c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm-10,0c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm17-5v12c0,2.206-1.794,4-4,4h-2.852l-3.848,3.18c-.361.322-.824.484-1.292.484-.476,0-.955-.168-1.337-.507l-3.749-3.157h-2.923c-2.206,0-4-1.794-4-4V4C0,1.794,1.794,0,4,0h16c2.206,0,4,1.794,4,4Zm-2,0c0-1.103-.897-2-2-2H4c-1.103,0-2,.897-2,2v12c0,1.103.897,2,2,2h3.288c.235,0,.464.083.645.235l4.048,3.41,4.171-3.416c.179-.148.404-.229.637-.229h3.212c1.103,0,2-.897,2-2V4Z"/></svg></i>
                  <span><?php echo $this->translate('CORECOMMENT'); ?></span>
                </a>
              </li>
            <?php }else if(!$this->viewer()->getIdentity()){ ?> 
              <li class="feed_item_option_like">
              <a href="<?php echo $this->url(array(), 'user_login', true); ?>" class="">
                <i><svg viewBox="0 0 24 24"><path d="M22.773,7.721A4.994,4.994,0,0,0,19,6H15.011l.336-2.041A3.037,3.037,0,0,0,9.626,2.122L7.712,6H5a5.006,5.006,0,0,0-5,5v5a5.006,5.006,0,0,0,5,5H18.3a5.024,5.024,0,0,0,4.951-4.3l.705-5A5,5,0,0,0,22.773,7.721ZM2,16V11A3,3,0,0,1,5,8H7V19H5A3,3,0,0,1,2,16Zm19.971-4.581-.706,5A3.012,3.012,0,0,1,18.3,19H9V7.734a1,1,0,0,0,.23-.292l2.189-4.435A1.07,1.07,0,0,1,13.141,2.8a1.024,1.024,0,0,1,.233.84l-.528,3.2A1,1,0,0,0,13.833,8H19a3,3,0,0,1,2.971,3.419Z"/></svg></i>
                <span><?php echo $this->translate('CORELIKEC');?></span>
              </a>
            </li> 
            <li class="feed_item_option_comment">
            <a href="<?php echo $this->url(array(), 'user_login', true); ?>" class="">
            <i><svg viewBox="0 0 24 24"><path d="m13.5,10.5c0,.828-.672,1.5-1.5,1.5s-1.5-.672-1.5-1.5.672-1.5,1.5-1.5,1.5.672,1.5,1.5Zm3.5-1.5c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm-10,0c-.828,0-1.5.672-1.5,1.5s.672,1.5,1.5,1.5,1.5-.672,1.5-1.5-.672-1.5-1.5-1.5Zm17-5v12c0,2.206-1.794,4-4,4h-2.852l-3.848,3.18c-.361.322-.824.484-1.292.484-.476,0-.955-.168-1.337-.507l-3.749-3.157h-2.923c-2.206,0-4-1.794-4-4V4C0,1.794,1.794,0,4,0h16c2.206,0,4,1.794,4,4Zm-2,0c0-1.103-.897-2-2-2H4c-1.103,0-2,.897-2,2v12c0,1.103.897,2,2,2h3.288c.235,0,.464.083.645.235l4.048,3.41,4.171-3.416c.179-.148.404-.229.637-.229h3.212c1.103,0,2-.897,2-2V4Z"/></svg></i>
                <span><?php echo $this->translate('CORECOMMENT');?></span>
              </a>
            </li>
            <?php } ?>
            <?php
            $params = isset($params) ? $params : "";
            echo $this->partial('_epage_content.tpl', 'comment', array('subject' => $this->subject(), 'params' => $params));
            echo $this->partial('_egroup_content.tpl', 'comment', array('subject' => $this->subject(), 'params' => $params));
            echo $this->partial('_ebusiness_content.tpl', 'comment', array('subject' => $this->subject(), 'params' => $params));
            ?>

          </ul>
        </div>
        <div class='comments comment_comments'>
          <?php if ($canComment) { ?>
            <form class="activity-comment-form advcomment_form" method="post" style="display:none;">
              <div class="comments_author_photo comment_user_img">
                <?php
                echo $this->itemPhoto($this->item('user', Engine_Api::_()->user()->getViewer()->getIdentity()), 'thumb.icon', $this->item('user', Engine_Api::_()->user()->getViewer()->getIdentity())->getTitle());
                ?>
              </div>
              <?php echo $this->partial('_commentAttachments.tpl', 'comment', array('enableattachementComment' => @$enableattachementComment, 'item' => $this->subject(), 'type' => 'commentlist')); ?>
            </form>
          <?php } ?>
          <ul class="comments_cnt_ul">
            <?php
            echo $this->partial(
              'list-comment/_subjectlikereaction.tpl',
              'comment',
              array('subject' => $this->subject(), 'isPageSubject' => isset($isPageSubject) ? $isPageSubject : "")
            );
            ?>
          <?php endif; ?>
          <?php if ($this->comments->getTotalItemCount() > 0):
            ?>
            <?php foreach ($this->comments as $comment): ?>
              <?php
              echo $this->partial(
                'list-comment/_subjectcommentbody.tpl',
                'comment',
                array('comment' => $comment, 'subject' => $this->subject(), 'isPageSubject' => isset($isPageSubject) ? $isPageSubject : "")
              );
              ?>
            <?php endforeach; ?>
            <?php if ($this->comments->count() != 0 && $this->comments->getCurrentPageNumber() < $this->comments->count()): ?>
              <li class="comment_more">
                <div class="comments_viewall">
                  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View later comments'), array(
                    'onclick' => 'commentactivitycomment("' . $this->subject()->getIdentity() . '", "' . ($this->comments->getCurrentPageNumber() + 1) . '",this,"' . $this->subject()->getType() . '")'
                  )
                  ) ?>
                </div>
              </li>
            <?php endif; ?>
          <?php endif; ?>
          <?php if (!$this->page): ?>
          </ul>
        </div>

      </div>
    <?php endif; ?>
  <?php } ?>
  <?php if ($this->is_ajax_load) { ?>
  </div>
<?php } ?>

<?php if ($this->is_ajax_load) { ?>
  <script type="application/javascript">
    en4.core.runonce.add(function () {
      scriptJquery.post(en4.core.baseUrl + 'comment/comment/list', { is_ajax_load_req: true, id: <?php echo $this->idtype; ?>,type:"<?php echo $this->type ?>"},function(result){
        scriptJquery('.comment_ajax_load_cnt').css('position', '');
        scriptJquery('.comment_ajax_load_cnt').html(result);
      })
    })

  </script>
<?php } ?>
<?php if ($this->is_ajax_load_req) {
  die;
} ?>
