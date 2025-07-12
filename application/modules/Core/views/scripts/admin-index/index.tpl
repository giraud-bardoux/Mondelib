<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php $flushData = Engine_Api::_()->getDbTable('files', 'storage')->getFlushPhotoData(array('count' => 1)); ?>
<?php $menuType = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.menutype', 'vertical'); ?> 
<div class="admin_home_wrapper">
  <div class="admin_home_top">
    <?php echo $this->content()->renderWidget('core.admin-notification') ?>
    <?php if($flushData > 0) { ?>
      <ul class="admin_home_dashboard_messages">
        <li class="notification-warning priority-error">
          <?php echo $this->translate("You have %s unmapped photos from TinyMCE Editor.", $flushData); ?>
          <?php echo $this->htmlLink(array('module' => 'core', 'controller' => 'index', 'action' => 'flush-photo'), $this->translate('Click here'), array('class' => 'smoothbox')); ?><?php echo $this->translate(' to remove them from the storage of your site.'); ?>
        </li>
      </ul>
    <?php } ?>
    <?php if($this->paginator->getTotalItemCount() > 0) { ?>
      <ul class="admin_home_dashboard_messages">
        <li class="notification-warning priority-error">
          <?php echo $this->translate("You have %s members whose data has not been imported yet.", $this->paginator->getTotalItemCount()); ?>
          <?php echo $this->htmlLink(array('module' => 'core', 'controller' => 'index', 'action' => 'user-data-import'), $this->translate('Click here'), array('class' => 'smoothbox')); ?><?php echo $this->translate(' to import their data.'); ?>
        </li>
      </ul>
    <?php } ?>
    
    <?php echo $this->content()->renderWidget('core.admin-statistics') ?>
    <?php // echo $this->content()->renderWidget('core.admin-environment') ?>
    <div class="admin_home_dashboard">
      <div class="row">
        <div class="col-md-8">
          <?php echo $this->content()->renderWidget('core.admin-chart') ?>
        </div>
        <div class="col-md-4">
          <?php echo $this->content()->renderWidget('core.admin-recent-activity') ?>
        </div>
        <div class="col-md-4 core_admin_notes">
          <?php echo $this->content()->renderWidget('core.admin-notes') ?>
        </div>
        <div class="col-md-4">
          <?php echo $this->content()->renderWidget('core.admin-private-comment') ?>
        </div>
        <div class="col-md-4 ">
          <div class="new_update_checkbox">
            <?php echo $this->content()->renderWidget('core.admin-content-show') ?>
            <?php //if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.newsupdates')) { ?>
              <?php echo $this->content()->renderWidget('core.admin-news') ?>
            <?php //} ?>
          </div>
        </div> 
        <div class="col-md-4">
          <?php echo $this->content()->renderWidget('core.admin-quick-start') ?>
        </div>
        <div class="col-md-4">
          <?php echo $this->content()->renderWidget('core.admin-quick-link') ?>
        </div>  
        <div class="col-md-4">
          <?php echo $this->content()->renderWidget('core.admin-plugin-statistics') ?>
        </div>
      </div>
    </div>
  </div>
<script type="text/javascript">
  function menuType(value) {
    var checkBox = document.getElementById("newsupdates");
    (scriptJquery.ajax({
      method: 'post',
      dataType: 'json',
      url: en4.core.baseUrl + 'core/index/adminmenutype/',
      data: {
        format: 'json',
        //showcontent: checkBox.checked,
        value: value,
      },
      success : function(responseHTML) {
        location.reload();
      }
    }));
    return false;
  }
</script>
<script type="application/javascript">
  scriptJquery('.core_admin_main_home').parent().addClass('active');
</script>
