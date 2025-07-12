<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manage-imports.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'core_admin_main_manage_members', 'lastMenuItemName' => 'Bulk Import Members Using CSV File')); ?>

<h2 class="page_heading"><?php echo $this->translate("Bulk Import Members Using CSV File") ?></h2>
<p><?php echo $this->translate('This page enables you to import Members on your website from CSV file. Please download the template file using the "Download Template File" button below. To start importing Members, click on the "Import Members" button.<br /><b>Notes:</b> See the points below and make sure the csv is created follows each one:<br />1. Do not add any new column in the downloaded template file.<br />2. The data in the file should be pipe ("|") separated and in same ordering as that of the template file.<br />3. We recommend you to import 100 Members from the csv file at a time.<br />4. File must be in .csv format Only.<br />5. If you are adding fields- First Name, Last Name, Gender (Male/Female/Other), Birthdate (yyyy-mm-dd),  in csv file, then make sure you have added these fields in the profile type you will select while importing your csv file. This ensures that the data is properly mapped and not lost during the import process.<br />'); ?></p>
<div class="mt-4">
  <a href="<?php echo $this->url(array('action' => 'download')) ?>" class="admin_link_btn icon_download"><?php echo $this->translate('Download Template File')?></a>
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'user', 'controller' => 'manage', 'action' => 'import-users'), $this->translate('Import Members'), array('class' => 'smoothbox admin_link_btn icon_import')) ?>
</div>
<script type="text/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_members').addClass('active');
</script>
