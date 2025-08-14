<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: view.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     John
 */
?>
<?php $isForumModerator = $this->forum->isModerator(Engine_Api::_()->user()->getViewer()); ?>
<div class="layout_page_forum_topic_view">
  <div class="generic_layout_container layout_main">
    <div class="generic_layout_container layout_middle">
      
      <div class="generic_layout_container">
        <div class="breadcrumb_wrap">
          <div class="forum_breadcrumb">
            <p><?php echo $this->htmlLink(array('route'=>'forum_general'), $this->translate("Forums"));?>
            &#187; <?php echo $this->htmlLink(array('route'=>'forum_forum', 'forum_id'=>$this->forum->getIdentity()), $this->translate($this->forum->getTitle()));?></p>
          </div>
        </div>
      </div>

      <div class="generic_layout_container layout_core_content">
        <div class="topic_view_header mb-3">
          <h1 class="mb-3"><?php echo $this->topic->getTitle() ?></h1>
          <div class="topic_view_header_btn d-flex flex-wrap justify-content-between gap-3">
            <div class="topic_view_header_stats font_color_light d-flex gap-3">
              <span title="<?php echo $this->translate(array('%s post', '%s posts', $this->topic->post_count), $this->locale()->toNumber($this->topic->post_count)) ?>" ><i class="icon_edit"></i> <?php echo $this->topic->post_count; ?></span>
              <span  title="<?php echo $this->translate(array('%s view', '%s views', $this->topic->view_count), $this->locale()->toNumber($this->topic->view_count)) ?>"><i class="icon_view"></i> <?php echo $this->topic->view_count; ?></span>
            </div>
            <div class="topic_view_header_buttons d-flex gap-2">
              <?php echo $this->htmlLink($this->forum->getHref(), $this->translate('Back To Topics'), array('class' => 'btn btn-alt btn-small icon_back')) ?>
              <?php if( $this->viewer->getIdentity() ): ?>
                <?php if( !$this->isWatching ): ?>
                  <div><?php echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '1')), $this->translate('Watch Topic'), array('class' => 'btn btn-alt btn-small icon_topic_watch')) ?></div>
                <?php else: ?>
                  <div><?php echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '0')), $this->translate('Stop Watching Topic'), array('class' => 'btn btn-alt btn-small icon_topic_unwatch')) ?></div>
                <?php endif; ?>
              <?php endif; ?>
              <?php if( $this->canPost ): ?>
                <div><?php echo $this->htmlLink($this->topic->getHref(array('action' => 'post-create')), $this->translate('Post Reply'), array('class' => 'btn btn-primary btn-small icon_add')) ?></div>
              <?php endif; ?>
              <?php if( $this->canEdit || $this->canDelete): ?>
                <div class="topic_view_header_options dropdown">
                  <button class="btn btn-alt btn-small" type="button" id="manageoption" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="icon_option_menu"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="manageoption">
                    <?php if( $this->canEdit ): ?>
                      <?php if( !$this->topic->sticky ): ?>
                        <li><?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '1', 'reset' => false), $this->translate('Make Sticky'), array('class' => 'dropdown-item icon_post_stick')) ?></li>
                      <?php else: ?>
                        <li><?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '0', 'reset' => false), $this->translate('Remove Sticky'), array('class' => 'dropdown-item icon_post_unstick')) ?></li>
                      <?php endif; ?>
                      <?php if( !$this->topic->closed ): ?>
                        <li><?php echo $this->htmlLink(array('action' => 'close', 'close' => '1', 'reset' => false), $this->translate('Close'), array('class' => 'dropdown-item icon_close')) ?></li>
                      <?php else: ?>
                        <li><?php echo $this->htmlLink(array('action' => 'close', 'close' => '0', 'reset' => false), $this->translate('Open'), array('class' => 'dropdown-item icon_open')) ?></li>
                      <?php endif; ?>
                      <li><?php echo $this->htmlLink(array('action' => 'rename', 'reset' => false), $this->translate('Rename'), array(  'class' => 'dropdown-item smoothbox icon_post_rename')) ?></li>
                      <li><?php echo $this->htmlLink(array('action' => 'move', 'reset' => false), $this->translate('Move'), array('class' => 'dropdown-item smoothbox icon_post_move')) ?></li>
                    <?php endif; ?>
                    <?php if( $this->canDelete ): ?>
                      <li><?php echo $this->htmlLink(array('action' => 'delete', 'reset' => false), $this->translate('Delete'), array('class' => 'dropdown-item smoothbox icon_delete')) ?></li>
                    <?php endif; ?>
                  </ul>
                </div>
              <?php elseif($isForumModerator): ?>
                <div class="topic_view_header_options dropdown">
                  <button class="btn btn-alt" type="button" id="manageoption" data-bs-toggle="dropdown" aria-expanded="false">
                    <i></i>
                  </button>
                  <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="manageoption">
                    <?php if( !$this->topic->sticky ): ?>
                      <li><?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '1', 'reset' => false), $this->translate('Make Sticky'), array('class' => 'dropdown-item icon_post_stick')) ?></li>
                    <?php else: ?>
                      <li><?php echo $this->htmlLink(array('action' => 'sticky', 'sticky' => '0', 'reset' => false), $this->translate('Remove Sticky'), array('class' => 'dropdown-item icon_post_unstick')) ?></li>li>
                    <?php endif; ?>
                    <?php if( !$this->topic->closed ): ?>
                      <li><?php echo $this->htmlLink(array('action' => 'close', 'close' => '1', 'reset' => false), $this->translate('Close'), array('class' => 'dropdown-item icon_close')) ?></li>
                    <?php else: ?>
                      <li><?php echo $this->htmlLink(array('action' => 'close', 'close' => '0', 'reset' => false), $this->translate('Open'), array('class' => 'dropdown-item icon_open')) ?></li>
                    <?php endif; ?>
                    <li><?php echo $this->htmlLink(array('action' => 'rename', 'reset' => false), $this->translate('Rename'), array('class' => 'dropdown-item smoothbox icon_post_rename')) ?></li>
                    <li><?php echo $this->htmlLink(array('action' => 'move', 'reset' => false), $this->translate('Move'), array('class' => 'dropdown-item smoothbox icon_post_move')) ?></li>
                    <li><?php echo $this->htmlLink(array('action' => 'delete', 'reset' => false), $this->translate('Delete'), array('class' => 'dropdown-item smoothbox icon_delete')) ?></li>
                  </ul>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php if( $this->topic->closed ): ?>
          <div class="error_msg mb-3">
            <?php echo $this->translate('This topic has been closed.');?>
          </div>
        <?php endif; ?>
      
        <?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $this->topic)); ?>

        <script type="text/javascript">
          en4.core.runonce.add(function() {
              // Scroll to the selected post
              var post_id = <?php echo sprintf('%d', $this->post_id) ?>;
              if( post_id > 0 ) {
                  window.scrollTo(0, $('forum_post_' + post_id).getPosition().y);
              }
          });
        </script>

        <ul class="topic_posts">
          <?php foreach( $this->paginator as $i => $post ): ?>
          <?php $user = $this->user($post->user_id); ?>
          <?php $signature = $post->getSignature(); ?>
          <?php $isModeratorPost = $this->forum->isModerator($post->getOwner()); ?>
          <li id="forum_post_<?php echo $post->post_id ?>" class="topic_posts_item forum_nth_<?php echo $i % 2 ?><?php if( $isModeratorPost ): ?> moderator_post<?php endif ?>">
            <?php //if($this->paginator->getTotalItemCount() > 1) { ?>
              <?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $post)); ?>
            <?php //} ?>
            <div class="topic_posts_author">
              <div class="topic_posts_author_photo">
                <?php echo $this->itemBackgroundPhoto($user, 'thumb.normal'); ?>
              </div>
              <ul class="topic_posts_author_info">
                <li class="topic_posts_author_name">
                  <?php echo $user->__toString(); ?>
                </li>
                <?php if( $post->user_id != 0 ): ?>
                  <?php if( $post->getOwner() ): ?>
                    <?php if( $isModeratorPost ): ?>
                      <li class="topic_posts_author_info_title font_small"><?php echo $this->translate('Moderator') ?></li>
                    <?php endif; ?>
                  <?php endif; ?>
                <?php endif; ?>

                <?php if( $signature ): ?>
                  <li class="font_small">
                    <?php echo $signature->post_count; ?>
                    <?php echo $this->translate('posts');?>
                  </li>
                <?php endif; ?>
              </ul>
            </div>
            <div class="topic_posts_info">
              <div class="topic_posts_info_top d-flex justify-content-between mb-2 align-items-center">
                <div class="topic_posts_info_date font_color_light font_small">
                  <?php echo $this->locale()->toDateTime(strtotime($post->creation_date)) ?>
                </div>
                <div class="topic_posts_info_options d-flex gap-2">
                  <?php if( $this->canPost ): ?> 
                    <div>
                      <?php $user = $this->item('user', $post->user_id);
                      echo $this->htmlLink('javascript:void(0);', $this->translate('Quote'), array('class' => 'btn btn-alt btn-small icon_post_quote','onclick' => 'quotePost("'.$this->escape($user->getTitle()).'", "'.$this->escape($user->getHref()).'", this, "'.$post->getIdentity().'");')) ?>
                    </div>
                    <?php endif;?>

                    <script type="text/javascript">
                      var quotePost = function(user, href, body, post_id) {
                          body = scriptJquery('#forum_discussions_thread_body_raw_'+post_id).html();
                          body = scriptJquery.trim(body);
                          if( $type(body) == 'element' ) {
                              body = $(body).getParent('li').getElement('.forum_discussions_thread_body_raw').get('html').trim();
                          }
                          var value = '<blockquote><strong>' + '<a href=' + href + '>' + user + '</a> <?php echo $this->translate('said');?>: </strong>\n</a>' + htmlspecialchars_decode(body) + '</blockquote>\n\n';
                          value = value.replace(/\s+/g,' ').trim()+"<br />";
                      <?php if ( $this->form && ($this->form->body->getType() === 'Engine_Form_Element_TinyMce') ): ?>
                          tinymce.activeEditor.execCommand('mceInsertContent', false, value);
                          tinyMCE.activeEditor.focus();
                      <?php else: ?>
                          document.getElementById('body').value = value;
                          scriptJquery("#body").focus();
                      <?php endif; ?>
                          scriptJquery('html, body').animate({ scrollTop: scriptJquery("#body").offset().top }, 'fast');
                      }
                    </script>

                  <div class="dropdown">
                    <button class="btn btn-alt btn-small" type="button" id="topic_post_option_<?php echo $post->post_id ?>" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="icon_option_menu"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="topic_post_option_<?php echo $post->post_id ?>">
                      <?php if( $this->canEdit):?>
                        <?php if($this->canEdit_Post) { ?>
                          <li><a href="<?php echo $this->url(array('post_id'=>$post->getIdentity(), 'action'=>'edit'), 'forum_post'); ?>" class="dropdown-item icon_edit"><?php echo $this->translate('Edit');?></a></li>
                        <?php } ?>
                        <li><a href="<?php echo $this->url(array('post_id'=>$post->getIdentity(), 'action'=>'delete'), 'forum_post');?>" class="dropdown-item smoothbox icon_delete"><?php echo $this->translate('Delete');?></a></li>
                      <?php elseif( $post->user_id != 0 && $post->isOwner($this->viewer) && !$this->topic->closed ): ?>
                        <?php if( $this->canEdit_Post ): ?>
                          <li><a href="<?php echo $this->url(array('post_id'=>$post->getIdentity(), 'action'=>'edit'), 'forum_post'); ?>" class="dropdown-item icon_edit"><?php echo $this->translate('Edit');?></a></li>
                        <?php endif; ?>
                        <?php if( $this->canDelete_Post ): ?>
                          <li><a href="<?php echo $this->url(array('post_id'=>$post->getIdentity(), 'action'=>'delete'), 'forum_post');?>" class="dropdown-item smoothbox icon_delete"><?php echo $this->translate('Delete');?></a></li>
                        <?php endif; ?>
                      <?php elseif($isForumModerator && $post->user_id != 0 && !$this->topic->closed): ?>
                        <li><a href="<?php echo $this->url(array('post_id'=>$post->getIdentity(), 'action'=>'edit'), 'forum_post'); ?>" class="dropdown-item icon_edit"><?php echo $this->translate('Edit');?></a></li>
                        <li><a href="<?php echo $this->url(array('post_id'=>$post->getIdentity(), 'action'=>'delete'), 'forum_post');?>" class="dropdown-item smoothbox icon_delete"><?php echo $this->translate('Delete');?></a></li>
                      <?php endif; ?>
                      <?php if( $this->viewer()->getIdentity() && $post->user_id != $this->viewer()->getIdentity() ): ?>
                        <li><?php echo $this->htmlLink(array('route' => 'default', 'module' => 'core', 'controller' => 'report', 'action' => 'create', 'subject' => $post->getGuid(), 'format' => 'smoothbox'), $this->translate('Report'), array('class' => 'dropdown-item icon_report smoothbox')) ?></li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="rich_content_body topic_posts_body">
                <?php
                $body = $post->body;
                $doNl2br = false;
                if( strip_tags($body) == $body ) {
                  $body = nl2br($body);
                }
                if( !$this->decode_html && $this->decode_bbcode ) {
                  $body = $this->BBCode($body, array('link_no_preparse' => true));
                }
                  $body = Engine_Api::_()->core()->DecodeEmoji($body);
                  $body = Engine_Api::_()->core()->smileyToEmoticons($body);
                  $dom = new DOMDocument; 
                  $dom->loadHTML($body, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                  $xpath = new DOMXPath($dom);
                  $counter = 0;
                  foreach ($xpath->query('//img[not(contains(concat(" ", @class, " ")," open-lightbox "))]') as $img) {
                      $img->setAttribute('class', ltrim($img->getAttribute('class') . ' open-lightbox'));
                      $img->setAttribute('data-darkbox', $img->getAttribute("src"));
                      $img->setAttribute('tab-index', $counter);
                      $img->setAttribute('data-darkbox-group', 'group-forum');
                      $counter++;
                  }
                  echo $dom->saveHTML();
                ?>
              </div>
              <span id="forum_discussions_thread_body_raw_<?php echo $post->getIdentity(); ?>" class="forum_discussions_thread_body_raw" style="display: none;"> <?php echo $post->body; ?> </span> 

              <?php if( $post->edit_id && !empty($post->modified_date) ):?>
                <div class="topic_posts_body_edit"><?php echo $this->translate('This post was edited by %1$s at %2$s', $this->user($post->edit_id)->__toString(), $this->locale()->toDateTime(strtotime($post->modified_date))); ?></div>
              <?php endif;?>
              <?php if ($post->file_id):?>
                <div class="topic_posts_body_img">
                  <?php echo $this->itemPhoto($post, null, '', array('class'=>'forum_post_photo'));?>
                </div>
              <?php endif;?>
            </li>
          <?php endforeach;?>
        </ul>

        <?php echo $this->paginationControl($this->paginator, null, null, array('params' => array('post_id' => null,),)); ?>

        <?php if( $this->canPost && $this->form ): ?>
          <div class="topic_reply"><?php echo $this->form->render(); ?></div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
  scriptJquery('.core_main_forum').parent().addClass('active');
  // Add parant element to table
  scriptJquery('.rich_content_body table').each(function() {                            
    scriptJquery(this).addClass('table');
    scriptJquery(this).wrap('<div class="table_wrap"></div>');
  });
</script>
