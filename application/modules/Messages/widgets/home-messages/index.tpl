<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>

<?php if( engine_count($this->paginator) ): ?>
  <ul class="sidebar_list recent_messages">
    <?php foreach( $this->paginator as $conversation ):
      $message = $conversation->getInboxMessage($this->viewer());
      $recipient = $conversation->getRecipientInfo($this->viewer());
      if( $conversation->hasResource() &&
                ($resource = $conversation->getResource()) ) {
        $sender = $resource;
      } else if( $conversation->recipients > 1 ) {
        $sender = $this->viewer();
      } else {
        foreach( $conversation->getRecipients() as $tmpUser ) {
          if( $tmpUser->getIdentity() != $this->viewer()->getIdentity() ) {
            $sender = $tmpUser;
          }
        }
      }
      if( !isset($sender) || !$sender ) {
        $sender = $this->viewer();
      }
      if( $resource ) {
        $author = $resource->toString();
      } else if( $conversation->recipients == 1 ) {
        $author = $this->htmlLink($sender->getHref(), $sender->getTitle());
      } else {
        $author = $this->translate(array('%s person', '%s people', $conversation->recipients),
            $this->locale()->toNumber($conversation->recipients));
      }
      ?>
      <li class="sidebar_list_item <?php if( !$recipient->inbox_read ): ?> new<?php endif; ?>">
        <div class="sidebar_list_item_thumb">
          <span class="bg_item_photo bg_thumb_icon bg_item_photo_message bg_item_nophoto"></span>
        </div>
        <div class="sidebar_list_item_info">
          <div class="sidebar_list_item_owner"><?php echo $this->translate('From %s %s', $author, $this->timestamp($message->date)) ?></div>
          <div class="sidebar_list_item_owner">
          <?php
            ( '' != ($title = trim($message->getTitle())) ||
              '' != ($title = trim($conversation->getTitle())) ||
              $title = '<em>' . $this->translate('(No Subject)') . '</em>' );
            $title = $this->string()->truncate($this->string()->stripTags($title));
          ?>
          <span>Subject:</span><?php echo $this->htmlLink($conversation->getHref(), $title) ?>
        </div>
        <p class="body">
          <span>Message:</span><?php echo $this->string()->truncate($this->string()->stripTags(str_replace('&nbsp;', ' ', html_entity_decode($message->body)))) ?>
        </p>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
