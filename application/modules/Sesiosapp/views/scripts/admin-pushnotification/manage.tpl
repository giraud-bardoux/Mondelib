<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: manage.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesiosapp/views/scripts/dismiss_message.tpl';?>
<h2>
  <?php echo $this->translate('Native iOS Mobile App'); ?>
</h2>
<?php if( engine_count($this->navigation)): ?>
  <div class='tabs'> <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?> </div>
<?php endif; ?>
<?php if( engine_count($this->subnavigation)): ?>
  <div class='tabs'> <?php echo $this->navigation()->menu()->setContainer($this->subnavigation)->render(); ?> </div>
<?php endif; ?>
<h3><?php echo $this->translate('Manage & Send Push Notifications'); ?></h3>
<p><?php echo $this->translate('Here you can configure the push notification message and send to all subscribers of your choice. You can send new push notification by using the “Send Push Notification” link below.'); ?></p><br>
<div class="sesiosapp_search_result">
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesiosapp', 'controller' => 'pushnotification', 'action' => 'create'), $this->translate("Send Push Notifications"), array('class'=>'buttonlink sesiosapp_icon_add smoothbox')); ?>
</div>
<div class="sesiosapp_manage_table">
  <div class="sesiosapp_manage_table_head" style="width:100%;">
    
    <div style="width:5%" class="admin_table_centered">
      <?php echo "Id";?>
    </div>
    <div style="width:20%" class="admin_table_centered">
     <?php echo $this->translate("Title") ?>
    </div>
    <div style="width:25%" class="admin_table_centered">
     <?php echo $this->translate("Description") ?>
    </div>
    <div style="width:20%" class="admin_table_centered">
     <?php echo $this->translate("Send To") ?>
    </div>
    <div style="width:10%" class="admin_table_centered">
     <?php echo $this->translate("Date") ?>
    </div>
    <div style="width:20%" class="admin_table_centered">
     <?php echo $this->translate("Options"); ?>
    </div>  
  </div>
  <ul class="sesiosapp_manage_table_list" id='menu_list' style="width:100%;">
  <?php foreach ($this->noti as $item) : ?>
    <li class="item_label" id="slide_<?php echo $item->pushnotification_id; ?>" style="cursor:pointer;">
      <div style="width:5%;" class="admin_table_centered">
        <?php echo $item->pushnotification_id; ?>
      </div>
      <div style="width:20%;" class="admin_table_centered">
        <?php echo $this->string()->truncate($item->title,30); ?>
      </div>
      <div style="width:25%;" class="admin_table_centered">
        <?php echo $this->string()->truncate($item->description,30); ?>
      </div>                
      <div style="width:20%;" class="admin_table_centered">
        <?php echo $item->criteria; ?>
      </div>
      <div style="width:10%;" class="admin_table_centered">
        <?php echo date('Y-m-d H:i:s',strtotime($item->creation_date)); ?>
      </div>                                   
      <div style="width:20%;" class="admin_table_centered">          
        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesiosapp', 'controller' => 'pushnotification', 'action' => 'resend', 'id' => $item->pushnotification_id), $this->translate("Resend"), array('class'=>'smoothbox')) ?>
        |
        <?php echo $this->htmlLink(
          array('route' => 'admin_default', 'module' => 'sesiosapp', 'controller' => 'pushnotification', 'action' => 'delete', 'id' => $item->pushnotification_id),
          $this->translate("Delete"),
          array('class' => 'smoothbox'));
        ?>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
</div>
