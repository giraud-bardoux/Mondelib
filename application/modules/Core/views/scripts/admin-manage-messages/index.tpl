<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9915 2013-02-15 01:30:19Z alex $
 * @author     John
 */
?>
<div class="admin_common_top_section">
  <h2><?php echo $this->translate("Manage Messages") ?></h2>
  <p><?php echo $this->translate("This page lists all of the messages your users have posted. You can use this page to monitor these messages and delete offensive material if necessary. Entering criteria into the filter fields will help you find specific messages. Leaving the filter fields blank will show all of the messages on your social network.") ?> </p>
  <?php
  $settings = Engine_Api::_()->getApi('settings', 'core');
  if( $settings->getSetting('user.support.links', 0) == 1 ) {
  echo 'More info: <a href="https://community.socialengine.com/blogs/597/47/tag-management" target="_blank">See KB article</a>.';
  }
  ?>
</div>  
<script type="text/javascript">
function multiModify(){
  var multimodify_form = scriptJquery('#multimodify_form');
  if (multimodify_form.submit_button.value == 'delete')
  {
    return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected messages?")) ?>');
  }
}
function selectAll(obj){
  scriptJquery('.checkbox').each(function(){
    scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
  });
}
</script>
<div class='admin_search admin_common_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<?php $count = $this->paginator->getTotalItemCount() ?>
<?php if($count > 0) { ?>
  <div class='admin_results'>
    <div>
      <?php echo $this->translate(array("%s message found.", "%s messages found.", $count), $this->locale()->toNumber($count)) ?>
    </div>
    <div>
      <?php echo $this->paginationControl($this->paginator, null, null, array(
        'pageAsQuery' => true,
        'query' => $this->formValues,
      )); ?>
    </div>
  </div>
  <div class="admin_table_form">
    <form id='multimodify_form' method="post" action="<?php echo $this->url(array('action'=>'multi-modify'));?>" onSubmit="multiModify()">
      <table class='admin_table admin_responsive_table'>
        <thead>
          <tr>
            <th style='width: 1%;'><input onclick="selectAll(this)" type='checkbox' class='checkbox'></th>
            <th style='width: 1%;'><?php echo $this->translate("ID") ?></th>
            <th><?php echo $this->translate("Messages") ?></th>
            <th><?php echo $this->translate("Posted By") ?></th>
            <th><?php echo $this->translate("Recipient") ?></th>
            <th style='width:220px;' class='admin_table_options'><?php echo $this->translate("Options") ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if( engine_count($this->paginator) ): ?>
            <?php foreach( $this->paginator as $item ): ?>
            
              <?php $recipient = Engine_Api::_()->getDbTable('recipients', 'messages')->getRecipient(array('conversation_id' => $item->conversation_id, 'message_id' => $item->message_id)); ?> 
              <?php $recipient = $user = Engine_Api::_()->getItem('user', $recipient); ?>
              <?php $user = Engine_Api::_()->getItem('user', $item->user_id); ?>
              
              <tr>
                <td ><input name='modify_<?php echo $item->getIdentity();?>' value='<?php echo $item->getIdentity();?>' type='checkbox' class='checkbox'></td>
                <td data-label="<?php echo $this->translate("ID") ?>"><?php echo $item->getIdentity() ?></td>
                <td data-label="<?php echo $this->translate("Messages") ?>"><?php echo $item->body; ?></td>
                <td data-label="<?php echo $this->translate("Posted By") ?>">
                  <div class="admin_table_comments">
                    <?php echo $this->itemBackgroundPhoto($user, 'thumb.icon'); ?>
                    <a href="<?php echo $user->getHref(); ?>"><?php echo $user->getTitle(); ?></a>
                  </div>
                </td>
                <td data-label="<?php echo $this->translate("Recipient") ?>">
                  <div class="admin_table_comments">
                    <?php echo $this->itemBackgroundPhoto($recipient, 'thumb.icon'); ?>
                    <a href="<?php echo $recipient->getHref(); ?>"><?php echo $recipient->getTitle(); ?></a>
                  </div>
                </td>
                <td class='admin_table_options _comment_options'>
									<a target="_blank" href='<?php echo $item->getHref(); ?>'><?php echo $this->translate("View") ?></a>
									|
                  <a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->getIdentity()));?>'>
                    <?php echo $this->translate("Delete") ?>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
      <div class='buttons'>
        <button type='submit' name="submit_button" value="delete">
          <?php echo $this->translate("Delete Selected") ?>
        </button>
      </div>
    </form>
  </div>

<?php } else { ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no message yet."); ?>
    </span>
  </div>
<?php } ?>
