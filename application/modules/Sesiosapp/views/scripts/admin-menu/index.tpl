<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>

<?php include APPLICATION_PATH .  '/application/modules/Sesiosapp/views/scripts/dismiss_message.tpl';?>
<?php 
$this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jQuery/odering.js'); 
?>
<h2>
  <?php echo $this->translate('Native iOS Mobile App'); ?>
</h2>
<?php if( engine_count($this->navigation)): ?>
  <div class='tabs'> <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?> </div>
<?php endif; ?>

<h3>Manage Dashboard Menu Items</h3>
<br>
<p>
 Here, you can manage the iOS app Dashboard by creating new Menu Items and arrange them in categories. You can create new Categories also and add or arrange menu items under them by simply dragging and dropping them vertically on the page below.<br><br>

You can take various actions like editing, enable / disable, delete on menu items and categories from the here. The default menu items can not be deleted, but can be disabled.<br><br>

Note: Below, you can also add links to any other 3rd party plugin (which is not natively supported with this app) in the menu items and show them in Web View on your website.<br><br>

</p>
<div class="sesiosapp_search_result"><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesiosapp', 'controller' => 'menu', 'action' => 'create'), $this->translate("Add New Menu Item / Category"), array('class'=>'buttonlink sesiosapp_icon_add smoothbox')); ?>

</div>
  <div class="sesiosapp_manage_table">
          	<div class="sesiosapp_manage_table_head" style="width:100%;">
              <div style="width:5%" class="admin_table_centered">
                <?php echo "Id";?>
              </div>
              <div style="width:15%">
               <?php echo $this->translate("Content Title") ?>
              </div>
              <div style="width:15%">
               <?php echo $this->translate("Module") ?>
              </div>
              <div style="width:15%">
               <?php echo $this->translate("Type") ?>
              </div>
              <div style="width:10%"  class="admin_table_centered">
               <?php echo $this->translate("Status") ?>
              </div>
              <div style="width:10%" class="admin_table_centered">
               <?php echo $this->translate("Icon") ?>
              </div>
              <div style="width:15%">
               <?php echo $this->translate("Visibility") ?>
              </div>
              <div style="width:15%">
               <?php echo $this->translate("Options"); ?>
              </div>  
            </div>
          	<ul class="sesiosapp_manage_table_list" id='menu_list' style="width:100%;">
            <?php foreach ($this->menus as $item) : 
              if($item->module_name)
              { 
                  if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($item->module_name))
                    continue;;
              } 
            
            ?>
              <li class="item_label" id="slide_<?php echo $item->getIdentity(); ?>">
                
                <div style="width:5%;" class="admin_table_centered">
                  <?php echo $item->getIdentity(); ?>
                </div>
                <div style="width:15%;">
                  <?php //echo $item->label ?>
                  <?php echo $item->type == 0 ? "<b class='bold'>$item->label</b>" : "$item->label" ; ?>
                </div>
                <div style="width:15%;">
                  <?php echo $item->module ?>
                </div>
                
                <div style="width:15%;">
                  <?php echo $item->type == 0 ? "Category" : "Sub-Category" ; ?>
                </div>
                
                <div style="width:10%;" class="admin_table_centered">
                  <?php $enable = true; ?>
                  <?php if($item->module_name != ""){ ?>
                    <?php if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled($item->module_name)){ ?>
                            <?php $enable = false; ?>
                    <?php } ?>
                  <?php } ?>
                  <?php if($enable){ ?>
                  <?php echo ( $item->status ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesiosapp', 'controller' => 'menu', 'action' => 'status', 'id' => $item->getIdentity()), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesiosapp/externals/images/admin/check.png', '', array('title' => $this->translate('Disable'))), array()) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesiosapp', 'controller' => 'menu', 'action' => 'status', 'id' => $item->getIdentity()), $this->htmlImage('application/modules/Sesiosapp/externals/images/admin/error.png', '', array('title' => $this->translate('Enable')))) ) ?>
                  <?php }else{ ?>
                     not installed/not enabled
                  <?php } ?>
                </div>
                <div style="width:10%;" class="admin_table_centered">
                  <?php 
                  $url = $item->getPhotoUrl(); ?>
                  <?php if(!empty($url)){ ?>
                    <img src="<?php echo $url; ?>" style="width:48px;">
                  <?php }else{ echo "-";} ?>
                </div>                   
                <div style="width:15%;">
                  <?php echo $item->visibility == 0 ? "All users" : ($item->visibility == 1 ? "Only logged-in" : "Only non-logged in") ; ?>
                </div> 
                <div style="width:15%;">          
                  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesiosapp', 'controller' => 'menu', 'action' => 'create', 'id' => $item->getIdentity()), $this->translate("Edit"), array('class'=>'smoothbox')) ?> |
                  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesiosapp', 'controller' => 'menu', 'action' => 'info', 'id' => $item->getIdentity()), $this->translate("Info"), array('class'=>'smoothbox')) ?>
                  <?php if($item->is_delete == 1 && !$item->class){ ?>
              |
            <?php echo $this->htmlLink(
                array('route' => 'admin_default', 'module' => 'sesiosapp', 'controller' => 'menu', 'action' => 'delete', 'id' => $item->getIdentity()),
                $this->translate("Delete"),
                array('class' => 'smoothbox')) ?>
            <?php } ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
          </div>
<script type="text/javascript"> 
  en4.core.runonce.add(function() {
    scriptJquery('#menu_list').addClass('sortable');
    var SortablesInstance = scriptJquery('#menu_list').sortable({
      stop: function( event, ui ) {
        var ids = [];
        scriptJquery('#menu_list > li').each(function(e) {
          var el = scriptJquery(this);
          ids.push(el.attr('id'));
        });
        // Send request
        var url = '<?php echo $this->url(array('action' => 'order')) ?>';
        scriptJquery.ajax({
            url : url,
            dataType : 'json',
            data : {
                format : 'json',
                order : ids
            }
        });
      }
    });
  });
</script>
