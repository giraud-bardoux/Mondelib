<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Forum/views/scripts/_adminHeader.tpl';?>
<script type="text/javascript">

function multiDelete()
{
  return confirm("<?php echo $this->translate('Are you sure you want to delete the selected topic entries?');?>");
}

function selectAll(obj)
{
  scriptJquery('.checkbox').each(function(){
    scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
  });
}
</script>

<p>
  <?php echo $this->translate("This page lists all of the topic entries your users have posted. You can use this page to monitor these topics and delete offensive material if necessary.") ?><br>
  <?php
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) {
      echo 'More info: <a href="https://community.socialengine.com/blogs/597/45/blogs" target="_blank">See KB article</a>.';
    } 
  ?>	
</p>
<?php if( engine_count($this->paginator) ): ?>
<form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
<div class="admin_responsive_table">
  <table class='admin_table'>
    <thead>
      <tr>
        <th class='admin_table_short'><input onclick='selectAll(this);' type='checkbox' class='checkbox' /></th>
        <th class='admin_table_short'>ID</th>
        <th><?php echo $this->translate("Topic Title") ?></th>
        <th><?php echo $this->translate("Forum Title") ?></th>
        <th><?php echo $this->translate("Owner") ?></th>
        <th><?php echo $this->translate("Views") ?></th>
        <th><?php echo $this->translate("Posts") ?></th>
        <th><?php echo $this->translate("Date") ?></th>
        <th><?php echo $this->translate("Approve") ?></th>
        <th><?php echo $this->translate("Options") ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($this->paginator as $item): ?>
        <tr>
          <td ><input type='checkbox' class='checkbox' name='delete_<?php echo $item->getIdentity(); ?>' value="<?php echo $item->getIdentity(); ?>" /></td>
          <td data-label="ID"><?php echo $item->getIdentity() ?></td>
          <td data-label="<?php echo $this->translate("Topic Title") ?>" class="nowrap"><a href="<?php echo $item->getHref() ?>"><?php echo $item->getTitle() ?></a></td>
          <td class="nowrap" data-label="<?php echo $this->translate("Forum Title") ?>"><a href="<?php echo $item->getParent()->getHref() ?>"><?php echo $item->getParent()->getTitle() ?></a></td>
          <td  class="nowrap"  data-label="<?php echo $this->translate("Owner") ?>"><a href="<?php echo $item->getOwner()->getHref() ?>"><?php echo $item->getOwner()->getTitle() ?></a></td>
          <td data-label="<?php echo $this->translate("Views") ?>"><?php echo $this->locale()->toNumber($item->view_count) ?></td>
          <td data-label="<?php echo $this->translate("Posts") ?>"><?php echo $this->locale()->toNumber($item->post_count) ?></td>
          <td  class="nowrap" data-label="<?php echo $this->translate("Date") ?>"><?php echo $this->locale()->toDateTime($item->creation_date) ?></td>
          <td data-label="<?php echo $this->translate("Approve") ?>">
            <?php if(!$item->resubmit) { ?>
              <?php echo "---"; ?>
            <?php } else { ?>
              <?php if($item->approved == 1): ?>
                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'core', 'controller' => 'admin-approve-content', 'action' => 'approved', 'resource_id' => $item->getIdentity(), 'resource_type' => $item->getType()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/check.png', '', array('title'=> $this->translate('Unapprove'))), array('class' => "smoothbox")) ?>
              <?php else: ?>
                <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'core', 'controller' => 'admin-approve-content', 'action' => 'approved', 'resource_id' => $item->getIdentity(), 'resource_type' => $item->getType()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Core/externals/images/admin/uncheck.png', '', array('title'=> $this->translate('Approve'))), array('class' => "smoothbox")) ?>
              <?php endif; ?>
            <?php } ?>
          </td>
          <td class="admin_table_options nowrap">
            <?php if(!$item->resubmit && empty($item->approved)) { ?>
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'core', 'controller' => 'admin-approve-content', 'action' => 'approved', 'resource_id' => $item->getIdentity(), 'resource_type' => $item->getType()), $this->translate("Approve"),array('class' => 'smoothbox')) ?>
              |
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'core', 'controller' => 'admin-approve-content', 'action' => 'reject', 'resource_id' => $item->getIdentity(), 'resource_type' => $item->getType()), $this->translate("Reject"),array('class' => 'smoothbox')) ?>
              |
            <?php } ?>
            <?php echo $this->htmlLink($item->getHref(), $this->translate('view')) ?>
              |
            <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'forum', 'controller' => 'manage-topics', 'action' => 'delete', 'id' => $item->getIdentity()), $this->translate("delete"),array('class' => 'smoothbox')) ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div> 
<div class='buttons'>
  <button type='submit'><?php echo $this->translate("Delete Selected") ?></button>
</div>
</form>
<br/>
<div>
  <?php echo $this->paginationControl($this->paginator); ?>
</div>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no topic entries by your members yet.") ?>
    </span>
  </div>
<?php endif; ?>
