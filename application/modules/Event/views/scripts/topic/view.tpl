<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Event
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: view.tpl 9990 2013-03-20 19:59:59Z john $
 * @author     Sami
 */
?>

<div class="layout_page_event_topic_view">
  <div class="generic_layout_container layout_main">
    <div class="generic_layout_container layout_middle">

      <div class="generic_layout_container">
        <div class="breadcrumb_wrap">
          <div class="forum_breadcrumb">
            <p><?php echo $this->event->__toString() ?> <?php echo $this->translate('&#187;'); ?> <?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'topic', 'action' => 'index', 'event_id' => $this->event->getIdentity()), $this->translate('Discussions')) ?></p>
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
              <div><?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'topic', 'action' => 'index', 'event_id' => $this->event->getIdentity()), $this->translate('Back to Topics'), array('class' => 'btn btn-alt btn-small icon_back')) ?></div>
              <?php if( $this->viewer->getIdentity() ): ?>
                <?php if( !$this->isWatching ): ?>
                  <div><?php echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '1')), $this->translate('Watch Topic'), array('class' => 'btn btn-alt btn-small icon_topic_watch')) ?></div>
                <?php else: ?>
                  <div><?php echo $this->htmlLink($this->url(array('action' => 'watch', 'watch' => '0')), $this->translate('Stop Watching Topic'), array('class' => 'btn btn-alt btn-small icon_topic_unwatch')) ?></div>
                <?php endif; ?>
              <?php endif; ?>
              <?php if( $this->canPost && !$this->topic->closed): ?>
                <div><?php echo $this->htmlLink($this->url(array()) . '#reply', $this->translate('Post Reply'), array('class' => 'btn btn-small btn-primary icon_add')) ?></div>
              <?php endif; ?>
              <?php if( ($this->canEdit || $this->canDelete) && $this->topic->user_id == $this->viewer()->getIdentity()): ?>
                <div class="topic_view_header_options dropdown">
                  <button class="btn btn-alt btn-small" type="button" id="manageoption" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="icon_option_menu"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="manageoption">
                    <?php if( $this->canEdit && $this->topic->user_id == $this->viewer()->getIdentity()): ?>
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
                      <li><?php echo $this->htmlLink(array('action' => 'rename', 'reset' => false), $this->translate('Rename'), array('class' => 'dropdown-item smoothbox icon_post_rename')) ?></li>
                    <?php endif; ?>
                    <?php if( $this->canDelete && $this->topic->user_id == $this->viewer()->getIdentity()): ?>
                      <li><?php echo $this->htmlLink(array('action' => 'delete', 'reset' => false), $this->translate('Delete'), array('class' => 'dropdown-item smoothbox icon_delete')) ?></li>
                    <?php endif; ?>
                  </ul>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php if( $this->topic->closed ): ?>
          <div class="error_msg mb-3">
            <?php echo $this->translate('This topic has been closed.')?>
          </div>
        <?php endif; ?>
        <script type="text/javascript">
          var quotePost = function(user, href, body, post_id) {
              body = scriptJquery('#event_discussions_thread_body_raw_'+post_id).html();
              body = scriptJquery.trim(body);
              if( $type(body) == 'element' ) {
                  body = $(body).getParent('li').getElement('.event_discussions_thread_body_raw').get('html').trim();
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
              scriptJquery('html, body').animate({ scrollTop: scriptJquery(document).height() }, 'fast');
          }
        </script>
        <ul class='topic_posts'>
          <?php foreach( $this->paginator as $post ):
          $user = $this->item('user', $post->user_id);
          $isOwner = false;
          $isMember = false;
          $liClass = 'author_none';
          if( $this->event->isOwner($user) ) {
            $isOwner = true;
            $isMember = true;
            $liClass = 'author_isowner';
          } else if( $this->event->membership()->isMember($user) ) {
            $isMember = true;
            $liClass = 'author_ismember';
          }
          ?>
          <li class="<?php echo $liClass ?> topic_posts_item">
            <div class="topic_posts_author">
              <div class="topic_posts_author_photo">
                <?php echo $this->htmlLink($user->getHref(), $this->itemBackgroundPhoto($user, 'thumb.normal')) ?>
              </div>
              <ul class="topic_posts_author_info">
                <li class="topic_posts_author_name"> <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?> </li>
                <li>
                  <?php
                    if( $isOwner ) {
                      echo $this->translate('Host');
                    } else if( $isMember ) {
                      echo $this->translate('Member');
                    }
                  ?>
                </li>
              </ul>
            </div>
            <div class="topic_posts_info">
              <div class="topic_posts_info_top d-flex justify-content-between mb-2 align-items-center">
                <div class="topic_posts_info_date font_color_light font_small">
                  <?php echo $this->locale()->toDateTime(strtotime($post->creation_date)) ?>
                </div>
                <div class="topic_posts_info_options d-flex gap-2">
                  <?php if( $this->form ): ?>
                    <?php if( $this->canPostCreate ): ?>
                      <div><?php echo $this->htmlLink('javascript:void(0);', $this->translate('Quote'), array('class' => 'btn btn-alt btn-small icon_post_quote','onclick' => 'quotePost("'.$this->escape($user->getTitle(false)).'", "'.$this->escape($user->getHref()).'", this, "'.$post->getIdentity().'");')) ?></div>
                    <?php endif; ?>
                  <?php endif; ?>
                  
                  <?php if( $post->user_id == $this->viewer()->getIdentity() || $this->event->getOwner()->getIdentity() == $this->viewer()->getIdentity() || $this->canAdminEdit ): ?>
                    <div class="dropdown">
                      <button class="btn btn-alt btn-small" type="button" id="topic_post_option_<?php echo $post->post_id ?>" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon_option_menu"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-option-menu dropdown-menu-end" aria-labelledby="topic_post_option_<?php echo $post->post_id ?>">
                        <?php if($this->canPostEdit) { ?>
                          <li><?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'post', 'action' => 'edit', 'post_id' => $post->getIdentity()), $this->translate('Edit'), array('class' => 'dropdown-item icon_edit')) ?></li>
                        <?php } ?>
                        <?php if($this->canPostDelete) { ?>
                          <li><?php echo $this->htmlLink(array('route' => 'event_extended', 'controller' => 'post', 'action' => 'delete', 'post_id' => $post->getIdentity(), 'format' => 'smoothbox'), $this->translate('Delete'), array('class' => 'dropdown-item smoothbox icon_delete')) ?></li>
                        <?php } ?>
                      </ul>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
              <div class="rich_content_body topic_posts_body">
                <?php 
                  $body = $post->body;
                  if( strip_tags($body) == $body ) {
                    $body = nl2br($body);
                  }
                  if( !$this->decode_html && $this->decode_bbcode ) {
                    $body = $this->BBCode($body, array('link_no_preparse' => true));
                  }
                ?>
                <?php echo $body ?> </div>
              <span id="event_discussions_thread_body_raw_<?php echo $post->getIdentity(); ?>" class="event_discussions_thread_body_raw" style="display: none;"> <?php echo $post->body; ?> </span> </div>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php if($this->paginator->getCurrentItemCount() > 4): ?>
          <?php echo $this->paginationControl(null, null, null, array(
            'params' => array(
            'post_id' => null // Remove post id
            )
          )) ?>
          <?php echo $this->placeholder('eventtopicnavi') ?>
        <?php endif; ?>
        <?php if( $this->form ): ?>
          <div class="topic_reply">
            <a name="reply"></a>
            <?php echo $this->form->setAttrib('id', 'event_topic_reply')->render($this) ?>
          </div>
        <?php endif; ?>
        <script type="text/javascript">
          scriptJquery('.core_main_event').parent().addClass('active');
        </script> 
      </div>
    </div>
  </div>
</div>  
<script type="text/javascript">
  // Add parant element to table
  scriptJquery('.rich_content_body table').each(function() {                            
    scriptJquery(this).addClass('table');
    scriptJquery(this).wrap('<div class="table_wrap"></div>');
  });
</script>
