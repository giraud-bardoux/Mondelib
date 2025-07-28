<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: index.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php include APPLICATION_PATH .  '/application/modules/Sesandroidapp/views/scripts/dismiss_message.tpl';?>
<h2 class="page_heading">
  <?php echo $this->translate("Native Android Mobile App") ?>
</h2>
<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
  </div>
<?php endif; ?>
<script type="text/javascript">
function multiDelete()
{
  return confirm("<?php echo $this->translate('Are you sure you want to delete the selected playlists?');?>");
}

function selectAll(obj)
{
  scriptJquery('.checkbox').each(function(){
    scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
  });
}
</script>


  
<?php if( engine_count($this->paginator) ): ?>
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()"> 
  <?php endif; ?>
  <div>
  	 
        <h3><?php echo "Manage Graphic Assets"; ?></h3>
        <p><?php echo $this->translate("") ?>	 </p>
        <br />
        <div>
         <div class="sesandroidapp_search_result"><?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesandroidapp', 'controller' => 'graphic', 'action' => 'create-graphic'), $this->translate("Add New Graphic"), array('class'=>'buttonlink sesandroidapp_icon_add')); ?>

</div>
        </div>
        <?php if( engine_count($this->paginator) ): ?>
  <div class="sesandroidapp_search_result">
    <?php echo $this->translate(array('%s graphic found.', '%s graphics found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
  </div><?php endif; ?>
        <?php if(engine_count($this->paginator) > 0):?>
        	<div class="sesandroidapp_manage_table">
          	<div class="sesandroidapp_manage_table_head" style="width:100%;">
              <div style="width:5%" class="admin_table_centered">
                <input onclick='selectAll(this);' type='checkbox' class='checkbox' />
              </div>
              <div style="width:5%" class="admin_table_centered">
                <?php echo "Id";?>
              </div>
              <div style="width:20%" class="admin_table_centered">
               <?php echo $this->translate("Title") ?>
              </div>
              <div style="width:20%"  class="admin_table_centered">
               <?php echo $this->translate("Thumbnail") ?>
              </div>
              
              <div style="width:10%"  class="admin_table_centered">
               <?php echo $this->translate("Status") ?>
              </div>
              <div style="width:10%" class="admin_table_centered">
               <?php echo $this->translate("Creation Date") ?>
              </div>
              <div style="width:20%">
               <?php echo $this->translate("Options"); ?>
              </div>  
            </div>
          	<ul class="sesandroidapp_manage_table_list" id='menu_list' style="width:100%;">
            <?php foreach ($this->paginator as $item) : ?>
              <li class="item_label" id="slide_<?php echo $item->graphic_id ?>">
                <div style="width:5%;" class="admin_table_centered">
                  <input type='checkbox' class='checkbox' name='delete_<?php echo $item->graphic_id;?>' value='<?php echo $item->graphic_id ?>' />
                </div>
                <div style="width:5%;" class="admin_table_centered">
                  <?php echo $item->graphic_id; ?>
                </div>
                <div style="width:20%;" class="admin_table_centered">
                  <?php echo $item->title ?>
                </div>
                <div style="width:20%;" class="admin_table_centered">
                  <?php if($item->file_id): ?>
	                  <img height="100px;" width="100px;" alt="" src="<?php echo Engine_Api::_()->storage()->get($item->file_id, '')->getPhotoUrl(); ?>" />
                  <?php else: ?>
	                  <?php echo "---"; ?>
                  <?php endif; ?>
                </div>
                
                <div style="width:10%;" class="admin_table_centered">
                  <?php echo ( $item->status ? $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesandroidapp', 'controller' => 'graphic', 'action' => 'enabled',  'id' => $item->graphic_id), $this->htmlImage($this->layout()->staticBaseUrl . 'application/modules/Sesandroidapp/externals/images/admin/check.png', '', array('title' => $this->translate('Disabled'))), array()) : $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesandroidapp', 'controller' => 'graphic', 'action' => 'enabled',  'id' => $item->graphic_id), $this->htmlImage('application/modules/Sesandroidapp/externals/images/admin/error.png', '', array('title' => $this->translate('Enabled')))) ) ?>
                </div>                   
                <div style="width:10%;" class="admin_table_centered">
                    <?php echo date('Y-m-d H:i:s',strtotime($item->creation_date)); ?>
                  </div>
                <div style="width:20%;" class="admin_table_centered">          
                  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'sesandroidapp', 'controller' => 'graphic', 'action' => 'create-graphic', 'graphic_id' => $item->graphic_id), $this->translate("Edit Graphic"), array()) ?>
            |
            <?php echo $this->htmlLink(
                array('route' => 'admin_default', 'module' => 'sesandroidapp', 'controller' => 'graphic', 'action' => 'delete-graphic', 'id' => $item->graphic_id),
                $this->translate("Delete"),
                array('class' => 'smoothbox')) ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
          	<div class='buttons'>
            <button type='submit'><?php echo $this->translate('Delete Selected'); ?></button>
          </div>
          </div>
        <?php else:?>
          <div class="tip">
            <span>
              <?php echo "There are no graphics added by you.";?>
            </span>
          </div>
        <?php endif;?>
      </div>
  <br />
  </form>
  <br />
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>

<script type="text/javascript"> 
  
  var SortablesInstance;
  en4.core.runonce.add(function() {
     SortablesInstance = scriptJquery('#menu_list').sortable({
        helper: "clone",
        handle : '.item_label',
        stop: function( event, ui ) {
          reorder(event);
        }
    });
  });

  var reorder = function(e) {
   var menuitems = e.target.childNodes;
   var ordering = {};
   var i = 1;
   for (var menuitem in menuitems)
   {
     var child_id = menuitems[menuitem].id;
     if ((child_id != undefined))
     {
       ordering[child_id] = i;
       i++;
     }
   }
    ordering['format'] = 'json';

    //Send request
    var url = '<?php echo $this->url(array("action" => "order")) ?>';
    var request = scriptJquery.ajax({
      'url' : url,
      'method' : 'POST',
      'dataType' : 'json',
      'data' : ordering,
      success : function(responseJSON) {
      }
    });
  }
</script>
<style type="text/css">
.sesandroidapp_manage_form_head > div,
.sesandroidapp_manage_form_list li > div{
	box-sizing:border-box;
}
</style>
