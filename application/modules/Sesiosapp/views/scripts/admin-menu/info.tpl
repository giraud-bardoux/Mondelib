<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesiosapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: info.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<?php $baseURL = $this->layout()->staticBaseUrl; ?>
<div class="sesiosapp_view_stats_popup">
  <h3>Information</h3>
  <table>
  	<tr>
    <td colspan="1"><img src="<?php echo $this->item->getPhotoUrl(); ?>" style="height:40px; width:40px;"/></td>
    <td><?php if(!is_null($this->item->label) && $this->item->label != '') {
              echo  $this->item->label ;
            }else{ 
                echo "-";
            } ?>
     </td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Module') ?>:</td>
      <td><?php echo $this->item->module; ;?></td>
    </tr>
    <tr>
      <td><?php echo $this->translate('Type') ?>:</td>
      <td><?php echo $this->item->type == 0 ? "Category" : "Sub-Category" ; ?></td>
    </tr>
    
    <?php  if(isset($this->item->status)){ ?>
     <tr>
      <td><?php echo $this->translate('Status') ?>:</td>
      <td><?php  if($this->item->status == 1){ ?>
      <img src="<?php echo $baseURL . 'application/modules/Sesiosapp/externals/images/admin/check.png'; ?>"/> <?php }else{ ?> 
      <img src="<?php echo $baseURL . 'application/modules/Sesiosapp/externals/images/admin/error.png'; ?>" /> <?php } ?>
     </td>
    </tr>
    <?php } ?>

     <tr>
      <td><?php echo $this->translate('Visible To') ?>:</td>
      <td><?php echo $this->item->visibility == 0 ? "Both Logged-In and logged-out User" : ($this->item->visibility == 1 ? "Logged-in User" : "Logged-out user"); ?></td>
    </tr>
    <?php if($this->item->url){ ?>
    <tr>
      <td><?php echo $this->translate('Url') ?>:</td>
      <td><?php echo $this->item->url; ; ?></td>
    </tr>
    <?php } ?>
  </table>
  <br />
  <button onclick='javascript:parent.Smoothbox.close()'>
    <?php echo $this->translate("Close") ?>
  </button>
</div>
<?php if( @$this->closeSmoothbox ): ?>
<script type="text/javascript">
  TB_close();
</script>
<?php endif; ?>
