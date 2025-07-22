<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Album/views/scripts/_adminHeader.tpl';?>
<script type="text/javascript">
  function multiDelete(){
    return confirm("<?php echo $this->translate('Are you sure you want to delete the selected photos?');?>");
  }
  function selectAll(obj){
    scriptJquery('.checkbox').each(function(){
      scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
    });
  }
</script>
<p>
  <?php echo $this->translate("This page lists all of the photos your users have posted. You can use this page to monitor these photos and delete offensive material if necessary.") ?>
<br />
<?php
$settings = Engine_Api::_()->getApi('settings', 'core');
if( $settings->getSetting('user.support.links', 0) == 1 ) {
	echo 'More info: <a href="https://community.socialengine.com/blogs/597/55/photo-albums" target="_blank">See KB article</a>.';
} 
?>
</p>	
<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<?php if( engine_count($this->paginator) ): ?>
<form id="multidelete_form" action="<?php echo $this->url();?>" onSubmit="return multiDelete()" method="POST">
  <div class="admin_responsive_table">
    <table class='admin_table'>
      <thead>
        <tr>
          <th class='admin_table_short'><input onclick="selectAll(this)" type='checkbox' class='checkbox' /></th>
          <th class='admin_table_short'>ID</th>
          <th><?php echo $this->translate('Image') ?></th>
          <th><?php echo $this->translate('Title') ?></th>
          <th><?php echo $this->translate('Owner') ?></th>
          <th><?php echo $this->translate('Item Name') ?></th>
          <th><?php echo $this->translate('Views') ?></th>
          <th><?php echo $this->translate('Date') ?></th>
          <th><?php echo $this->translate("Approve") ?></th>
          <th><?php echo $this->translate('Options') ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($this->paginator as $item): ?>
          <tr>
            <td><input type='checkbox' class='checkbox' name='delete_<?php echo $item->photo_id;?>' value="<?php echo $item->photo_id ?>"/></td>
            <td data-label="ID"><?php echo $item->getIdentity() ?></td>
            <td data-label="Image"><img src="<?php echo $item->getPhotoUrl('thumb.normal'); ?>" style="height:75px; width:75px;"/></td>
            <td class="nowrap" data-label="<?php echo $this->translate("Title") ?>">
            <?php if($item->getTitle()) { ?>
              <a href="<?php echo $item->getHref(); ?>" target="_blank"><?php echo $item->getTitle(); ?></a>
            <?php } else { ?>
              <?php echo "---"; ?>
            <?php } ?>
            </td>
            <td data-label="<?php echo $this->translate("Owner") ?>" class="admin_table_name nowrap"><a href="<?php echo $this->user($item->owner_id)->getHref() ?>" target="_blank"><?php echo $this->user($item->owner_id)->getTitle() ?></a></td>
            <td class="nowrap" data-label="<?php echo $this->translate("Item Name") ?>">
              <?php if($item->parent_type && $item->parent_id) { ?>
                <?php $resource = Engine_Api::_()->getItem($item->parent_type, $item->parent_id); ?>
                <?php if($resource) { ?>
                  <a href="<?php echo $resource->getHref(); ?>"><?php echo $resource->getTitle() ?></a>
                <?php } else { ?>
                  <?php echo "---"; ?>
                <?php } ?>
              <?php } else { ?>
                <a href="<?php echo $item->getParent()->getHref(); ?>"><?php echo $item->getParent()->getTitle() ?></a>
              <?php } ?>
            </td>
            <td data-label="<?php echo $this->translate("Views") ?>"><?php echo $this->locale()->toNumber($item->view_count) ?></td>
            <td class="nowrap" data-label="<?php echo $this->translate("Date") ?>"><?php echo $this->locale()->toDateTime($item->creation_date) ?></td>
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
              <a href="<?php echo $item->getHref(); ?>" target="_blank"><?php echo is_array($this->translate('view')) ? $this->translate('view')[0] : $this->translate('view'); ?></a>
              |
              <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'album', 'controller' => 'admin-manage-photos', 'action' => 'delete', 'id' => $item->photo_id), $this->translate("delete"), array('class' => 'smoothbox')); ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class='buttons'>
    <button type='submit'>
      <?php echo $this->translate('Delete Selected') ?>
    </button>
  </div>
</form>
<br />
<div>
  <?php echo $this->paginationControl($this->paginator); ?>
</div>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no photos posted by your members yet.") ?>
    </span>
  </div>
<?php endif; ?>
