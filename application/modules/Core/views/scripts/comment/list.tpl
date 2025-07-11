<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: list.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php $attachUserTags = engine_in_array(
  "userTags",
  Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options')
);
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Core/externals/scripts/comments_composer.js');

if ($attachUserTags) {
  $this->headScript()
    ->appendFile($this->layout()->staticBaseUrl . 'application/modules/Core/externals/scripts/comments_composer_tag.js');
} ?>


<script type="text/javascript">
  var CommentLikesTooltips;
  var composeCommentInstance;
  en4.core.runonce.add(function() {
    // Scroll to comment
    if( window.proxyLocation.hash != '' ) {
      var hel = scriptJquery(window.proxyLocation.hash);
      if( hel ) {
        window.scrollTo(hel);
      }
    }
    // Add hover event to get likes
    scriptJquery('.comments_comment_likes').on('mouseover', function(event) {
      var el = scriptJquery(event.target);
      if( !el.data('tip-loaded', false) ) {
        el.data('tip-loaded', true);
        el.attr('title', '<?php echo  $this->string()->escapeJavascript($this->translate('Loading...')) ?>');
        var id = el.attr('id').match(/\d+/)[0];
        // Load the likes
        var url = '<?php echo $this->url(array('module' => 'core', 'controller' => 'comment', 'action' => 'get-likes'), 'default', true) ?>';
        var req = scriptJquery.ajax({
          url : url,
          dataType : 'json',
          method : 'post',
          data : {
            format : 'json',
            type : 'core_comment',
            id : id
          },
          success : function(responseJSON) {
            el.attr('title', responseJSON.body);
            el.tooltip("close");
            el.tooltip("open");
          }
        });
      }
    }).tooltip({
      classes: {
        "ui-tooltip": "comments_comment_likes_tips"
      }
    });
    // Enable links
    scriptJquery('.comments_body').enableLinks();
  });
</script>

<?php $this->headTranslate(array(
  'Are you sure you want to delete this?',
)); ?>

<?php if( !$this->page ): ?>
<div class='comments' id="comments">
<?php endif; ?>
  <div class='comments_options'>
    <span><?php echo $this->translate(array('%s comment', '%s comments', $this->comments->getTotalItemCount()), $this->locale()->toNumber($this->comments->getTotalItemCount())) ?></span>

    <?php if( isset($this->form) ): ?>
      - <a href='javascript:void(0);' onclick="showCommentBody();"><?php echo $this->translate('Post Comment') ?></a>
    <?php endif; ?>

    <?php if( $this->viewer()->getIdentity() && $this->canComment ): ?>
      <?php if( $this->subject()->likes()->isLike($this->viewer()) ): ?>
        - <a href="javascript:void(0);" onclick="en4.core.comments.unlike('<?php echo $this->subject()->getType()?>', '<?php echo $this->subject()->getIdentity() ?>')"><?php echo $this->translate('Unlike This') ?></a>
      <?php else: ?>
        - <a href="javascript:void(0);" onclick="en4.core.comments.like('<?php echo $this->subject()->getType()?>', '<?php echo $this->subject()->getIdentity() ?>')"><?php echo $this->translate('Like This') ?></a>
      <?php endif; ?>
    <?php endif; ?>
  </div>
  <ul>
    
    <?php if( $this->likes->getTotalItemCount() > 0 ): // LIKES ------------- ?>
      <li>
        <?php if( $this->viewAllLikes || $this->likes->getTotalItemCount() <= 3 ): ?>
          <?php $this->likes->setItemCountPerPage($this->likes->getTotalItemCount()) ?>
          <div> </div>
          <div class="comments_likes">
            <?php echo $this->translate(array('%s likes this', '%s like this', $this->likes->getTotalItemCount()), $this->fluentList($this->subject()->likes()->getAllLikesUsers())) ?>
          </div>
        <?php else: ?>
          <div> </div>
          <div class="comments_likes">
            <?php echo $this->htmlLink('javascript:void(0);', 
                          $this->translate(array('%s person likes this', '%s people like this', $this->likes->getTotalItemCount()), $this->locale()->toNumber($this->likes->getTotalItemCount())),
                          array('onclick' => 'en4.core.comments.showLikes("'.$this->subject()->getType().'", "'.$this->subject()->getIdentity().'");')
                      ); ?>
          </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if( $this->comments->getTotalItemCount() > 0 ): // COMMENTS ------- ?>

      <?php if( $this->page && $this->comments->getCurrentPageNumber() > 1 ): ?>
        <li>
          <div> </div>
          <div class="comments_viewall">
            <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View previous comments'), array(
              'onclick' => 'en4.core.comments.loadComments("'.$this->subject()->getType().'", "'.$this->subject()->getIdentity().'", "'.($this->page - 1).'")'
            )) ?>
          </div>
        </li>
      <?php endif; ?>

      <?php if( !$this->page && $this->comments->getCurrentPageNumber() < $this->comments->count() ): ?>
        <li>
          <div> </div>
          <div class="comments_viewall">
            <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View more comments'), array(
              'onclick' => 'en4.core.comments.loadComments("'.$this->subject()->getType().'", "'.$this->subject()->getIdentity().'", "'.($this->comments->getCurrentPageNumber()).'")'
            )) ?>
          </div>
        </li>
      <?php endif; ?>

      <?php // Iterate over the comments backwards (or forwards!)
      $comments = $this->comments->getIterator();
      if( $this->page ):
        $i = 0;
        $l = engine_count($comments) - 1;
        $d = 1;
        $e = $l + 1;
      else:
        $i = engine_count($comments) - 1;
        $l = engine_count($comments);
        $d = -1;
        $e = -1;
      endif;
      for( ; $i != $e; $i += $d ):
        $comment = $comments[$i];
        $poster = $this->item($comment->poster_type, $comment->poster_id);
        $canDelete = ( $this->canDelete || $poster->isSelf($this->viewer()) );
        ?>
        <li id="comment-<?php echo $comment->comment_id ?>">
          <div class="comments_author_photo">
            <?php echo $this->htmlLink($poster->getHref(),
              $this->itemBackgroundPhoto($poster, 'thumb.icon', $poster->getTitle(false))
            ) ?>
          </div>
          <div class="comments_info">
            <span class='comments_author'>
              <?php echo $this->htmlLink($poster->getHref(), $poster->getTitle()); ?>
            </span>
            <span class="comments_body">
              <?php echo $this->viewMore($this->getHelper('getActionContent')->updateActionContent($comment, $comment->body), 255, 50000, 511, true) ?>
            </span>
                        <ul class="comments_date"> 
              <?php echo $this->timestamp($comment->creation_date); ?>
              <?php if( $canDelete ): ?>
                <li class="sep">-</li>
                <li class="comments_delete">
                <a href="javascript:void(0);" onclick="en4.core.comments.deleteComment('<?php echo $this->subject()->getType()?>', '<?php echo $this->subject()->getIdentity() ?>', '<?php echo $comment->comment_id ?>')">
                  <?php echo $this->translate('delete') ?>
                </a>
                </li>
              <?php endif; ?>
              <?php if( $this->canComment ):
                $isLiked = $comment->likes()->isLike($this->viewer());
                ?>
                <li class="sep">-</li>
                <li class="comments_like">
                <?php if( !$isLiked ): ?>
                  <a href="javascript:void(0)" onclick="en4.core.comments.like(<?php echo sprintf("'%s', %d, %d", $this->subject()->getType(), $this->subject()->getIdentity(), $comment->getIdentity()) ?>)">
                    <?php echo $this->translate('like') ?>
                  </a>
                <?php else: ?>
                  <a href="javascript:void(0)" onclick="en4.core.comments.unlike(<?php echo sprintf("'%s', %d, %d", $this->subject()->getType(), $this->subject()->getIdentity(), $comment->getIdentity()) ?>)">
                    <?php echo $this->translate('unlike') ?>
                  </a>
                <?php endif ?>
               </li>
              <?php endif ?>
              <?php if( $comment->likes()->getLikeCount() > 0 ): ?>
                <li class="sep">-</li>
                  <li class="comments_likes_total">
                <a href="javascript:void(0);" id="comments_comment_likes_<?php echo $comment->comment_id ?>" class="comments_comment_likes" title="<?php echo $this->translate('Loading...') ?>">
                  <?php echo $this->translate(array('%s likes this', '%s like this', $comment->likes()->getLikeCount()), $this->locale()->toNumber($comment->likes()->getLikeCount())) ?>
                </a>
                </li>
              <?php endif ?>
              <?php if( !$poster->isSelf($this->viewer()) ): ?>
                <li class="sep">-</li>
                <li class="comments_report">
                <?php echo $this->htmlLink(array('module'=>'core','controller'=>'report','action'=>'create','route'=>'default','subject'=>$comment->getGuid(),'format' => 'smoothbox'), $this->translate("Report"), array('class' => 'smoothbox')); ?></li>
              <?php endif; ?>
            </ul>
            <?php /*
            <div class="comments_date">
              <?php echo $this->timestamp($comment->creation_date); ?>
              <?php if( $comment->likes()->getLikeCount() > 0 ): ?>
                -
                <a href="javascript:void(0);" id="comments_comment_likes_<?php echo $comment->comment_id ?>" class="comments_comment_likes" title="<?php echo $this->translate('Loading...') ?>">
                  <?php echo $this->translate(array('%s likes this', '%s like this', $comment->likes()->getLikeCount()), $this->locale()->toNumber($comment->likes()->getLikeCount())) ?>
                </a>
              <?php endif ?>
            </div>
            <div class="comments_comment_options">
              <?php if( $canDelete && $this->canComment ): ?>
                -
              <?php endif ?>
            </div>
             *
             */ ?>
          </div>
        </li>
      <?php endfor; ?>

      <?php if( $this->page && $this->comments->getCurrentPageNumber() < $this->comments->count() ): ?>
        <li>
          <div> </div>
          <div class="comments_viewall">
            <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View later comments'), array(
              'onclick' => 'en4.core.comments.loadComments("'.$this->subject()->getType().'", "'.$this->subject()->getIdentity().'", "'.($this->page + 1).'")'
            )) ?>
          </div>
        </li>
      <?php endif; ?>

    <?php endif; ?>

  </ul>
  <script type="text/javascript">
    en4.core.runonce.add(function(){
      //$($('comment-form').body).autogrow();
      var attachComposerTag = '<?php echo $attachUserTags ?>';
      composeCommentInstance = new CommentsComposer(scriptJquery('#comment-form').find("#body"), {
        'submitCallBack' : en4.core.comments.comment,
      });
      if (attachComposerTag === '1') {
        composeCommentInstance.addPlugin(new CommentsComposer.Plugin.Tag({
          enabled: true,
          suggestOptions : {
            'url' : '<?php echo $this->url(array(), 'default', true) . 'user/friends/suggest' ?>',
            'data' : {
              'format' : 'json'
            }
          },
          'suggestProto' : 'request.json',
          'suggestParam' : [],
        }));
      }
    });
    var showCommentBody = function () {
      scriptJquery('#comment-form').css("display",'');
      if(composeCommentInstance){
        composeCommentInstance.focus();
      }
    };
  </script>
  <?php if( isset($this->form) ) echo $this->form->setAttribs(array('id' => 'comment-form', 'style' => 'display:none;'))->render() ?>
<?php if( !$this->page ): ?>
</div>
    <?php endif; ?>
