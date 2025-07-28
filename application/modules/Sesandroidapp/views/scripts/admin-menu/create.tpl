<?php

 /**
 * socialnetworking.solutions
 *
 * @category   Application_Modules
 * @package    Sesandroidapp
 * @copyright  Copyright 2014-2019 Ahead WebSoft Technologies Pvt. Ltd.
 * @license    https://socialnetworking.solutions/license/
 * @version    $Id: create.tpl 2018-08-14 00:00:00 socialnetworking.solutions $
 * @author     socialnetworking.solutions
 */
 
?>
<div class='clear'>
  <div class='settings sesandroidapp_popup_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script type="application/javascript">

function hideFun(value){
  var display = "block" ;
  if(value == 1){
    display = "block"  
  }else{
    display = "none" 
  }  
    document.getElementById('module-wrapper').style.display = display;
    document.getElementById('file-wrapper').style.display = display;
    if(document.getElementById('url-wrapper'))
    document.getElementById('url-wrapper').style.display = display; 
}
hideFun(document.getElementById('type').value);
</script>