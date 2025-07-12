<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: view.tpl 9902 2013-02-14 02:35:41Z shaun $
 * @author     John
 */
?>

<h3>
  <?php if( '' != ($title = trim($this->conversation->getTitle())) ): ?>
    <?php echo $title ?>
  <?php else: ?>
    <em>
      <?php echo $this->translate('(No Subject)') ?>
    </em>
  <?php endif; ?>
</h3>

<div class="message_view_header">
  <div class="message_view_between">
    <?php
      // Resource
      if( $this->resource ) {
        echo $this->translate('To members of %1$s', $this->resource->toString());
      }
      // Recipients
      else {
        $you  = $this->viewer();
        $you  = $this->htmlLink($you->getHref(), ($this->viewer()->isSelf($you) ? $this->translate('You') : $you->getTitle()));
        $them = array();
        foreach ($this->recipients as $r) {
          if ($r != $this->viewer()) {
              $them[] = ($r==$this->blocker?"<s>":"").$this->htmlLink($r->getHref(), $r->getTitle()).($r==$this->blocker?"</s>":"");
          } else {
              $them[] = $this->htmlLink($r->getHref(), $this->translate('You'));
          }
        }

        if (engine_count($them) > 1) {
          echo $this->translate('Between %1$s, %2$s', $you, $this->fluentList($them));
        } else if (engine_count($them)) {
          echo $this->translate('Between %1$s and %2$s', $you, $this->fluentList($them));
        }
        else {
          echo $this->translate('Conversation with a deleted member.');
        }
      }
    ?>
  </div>
  <div class="message_view_actions">
    <?php echo $this->htmlLink(array(
      'action' => 'delete',
      'id' => null,
      'place' => 'view',
      'message_ids' => $this->conversation->conversation_id,
    ), $this->translate('Delete'), array(
      'class' => 'buttonlink smoothbox icon_delete', //'buttonlink icon_message_delete',
    )) ?>
  </div>
</div>

<ul class="message_view">
  <?php foreach( $this->messages as $message ):
    $user = $this->user($message->user_id); ?>
    <li>
      <div class='message_view_leftwrapper'>
        <div class='message_view_photo'>
          <?php echo $this->htmlLink($user->getHref(), $this->itemBackgroundPhoto($user, 'thumb.icon')) ?>
        </div>
        <div class='message_view_from'>
          <p>
            <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
          </p>
          <p class="message_view_date font_color_light font_small">
            <?php echo $this->timestamp($message->date) ?>
          </p>
        </div>
      </div>
      <div class='message_view_info rich_content_body'>
        <?php echo nl2br($this->getMessageBody(($message))) ?>
        <?php if( !empty($message->attachment_type) && null !== ($attachment = $this->item($message->attachment_type, $message->attachment_id))): ?>
          <div class="message_attachment">
            <?php if(null != ( $richContent = $attachment->getRichContent(false, array('message'=>$message->conversation_id)))): ?>
              <?php if($message->attachment_type == 'video' && $attachment->status !=1) { ?>
                <div class="error_msg">
                  <?php echo $this->translate('The video you are looking for does not exist or has not been processed yet.');?>
                </div>
              <?php } else { ?>
                <?php echo $richContent; ?>
              <?php } ?>
            <?php else: ?>
              <div class="message_attachment_photo">
                <?php if( null !== $attachment->getPhotoUrl() ): ?>
                  <?php echo $this->itemPhoto($attachment, 'thumb.normal') ?>
                <?php endif; ?>
              </div>
              <div class="message_attachment_info">
                <div class="message_attachment_title">
                  <?php echo $this->htmlLink($attachment->getHref(array('message'=>$message->conversation_id)), $attachment->getTitle()) ?>
                </div>
                <div class="message_attachment_desc">
                  <?php echo $attachment->getDescription() ?>
                </div>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </li>
  <?php endforeach; ?>

  <?php if( !$this->locked ): ?>
    <li class='message_quick_entry'>
      <div class='message_view_leftwrapper'>
        <div class='message_view_photo'>
          &nbsp;
        </div>
        <div class='message_view_from'>
          <p>
            &nbsp;
          </p>
          <p class="message_view_date">
            &nbsp;
          </p>
        </div>
      </div>

      <div class='message_view_info'>
      <?php if( (!$this->blocked && !$this->viewer_blocked) || (engine_count($this->recipients)>1)): ?>
        <?php echo $this->form->setAttrib('id', 'messages_form_reply')->render($this) ?>
      <?php elseif ($this->viewer_blocked):?>
        <?php echo $this->translate('You can no longer respond to this message because you have blocked %1$s.', $this->viewer_blocker->getTitle())?>
      <?php else:?>
        <?php echo $this->translate('You can no longer respond to this message because %1$s has blocked you.', $this->blocker->getTitle())?>
      <?php endif; ?>
      </div>
  <?php endif ?>

  </li>
</ul>

<script type="text/javascript">
  //scriptJquery('.message_view_info').enableLinks();

  // Add parant element to table
  scriptJquery('.rich_content_body table').each(function() {                            
    scriptJquery(this).addClass('table');
    scriptJquery(this).wrap('<div class="table_wrap"></div>');
  });
</script>
</script>

<?php if( !$this->locked ): ?>
  <script type="text/javascript">
    var composeInstance;
    en4.core.runonce.add(function() {
      var tel = scriptJquery.crtEle('div', {
        'id' : 'compose-tray',
        'styles' : {
          'display' : 'none'
        }
      }).insertAfter(scriptJquery('#reply_submit'), 'before');

      var mel = scriptJquery.crtEle('div', {
        'id' : 'compose-menu'
      }).insertAfter(scriptJquery('#reply_submit'), 'after');

      activityDesign = 2;
      counterLoopComposerItem = 1;
    
      scriptJquery('<div class="activity_post_media_options clearfix"><div id="activity_post_media_options_before"></div></div>').insertBefore(mel);

      // @todo integrate this into the composer
      if ('<?php $id = Engine_Api::_()->user()->getViewer()->level_id;
      echo Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('messages', $id, 'editor');
           ?>' == 'plaintext' ) {
        //if( !Browser.Engine.trident && !DetectMobileQuick() && !DetectIpad() ) {
          composeInstance = new Composer('body', {
            overText : false,
            menuElement : 'compose-menu',
            trayElement: tel,
            baseHref : '<?php echo $this->baseUrl() ?>',
            hideSubmitOnBlur : false,
            allowEmptyWithAttachment : false,
            submitElement: 'reply_submit',
            type: 'message'
          });
        //}
      }
    });
  </script>
  <?php foreach( $this->composePartials as $partial ): ?>
    <?php echo $this->partial($partial[0], $partial[1]) ?>
  <?php endforeach; ?>

<?php endif ?>
