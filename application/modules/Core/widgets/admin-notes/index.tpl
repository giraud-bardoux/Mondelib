<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9905 2013-02-14 02:46:28Z alex $
 * @author     John
 */
?>
<div class="admin_home_dashboard_item admin_user_activity_main">
  <div class="admin_quick_heading">
    <h5><?php echo $this->translate("Users Activity") ?></h5>
  </div>
   <ul class="admin_user_activity_inner">
      <li class="admin_user_activity_content">
        <h6><?php echo $this->translate("Users Online") ?></h6>
        <span> <b><?php echo $this->onlineUserCount ?></b> <?php echo $this->translate("Last Hour") ?></span> 
      </li>
      <li class="admin_user_activity_content _active">
        <h6><?php echo $this->translate("Active Users") ?></h6>
        <span> <b><?php echo $this->countActiveMembers; ?></b> <?php echo $this->translate("Active") ?></span> 
      </li>
   </ul>
</div>

<div class="admin_home_dashboard_item private_message_box">
  <div class="admin_quick_heading">
    <h5>
      <?php echo $this->translate("Admin Notes"); ?>
    </h5>
    <p><?php echo $this->translate("This note is common for all admins and super admins."); ?></p>
  </div>
  <form type="post" class="global_form" id="core_admin_notes" action="<?php echo $this->url(array("module" => "core", "controller" => "index", "action" => "notes"), 'admin_default', true); ?>">
    <div class="form-wrapper">
      <div class="form-element">
         <textarea name="coreadmin_notes" id="coreadmin_notes" cols="35" rows="8" spellcheck="false"><?php echo $this->adminnotes; ?></textarea>
      </div>
    </div>
    <div class="form-wrapper">
      <div class="form-element">
        <button type="submit"><?php echo $this->translate("Save"); ?></button>
      </div>
    </div>
  </form>
</div>
<script type="application/javascript">
  
  AttachEventListerSE('submit','#core_admin_notes',function(e){
    e.preventDefault();
    
    if(!scriptJquery('#core_admin_overlay_content').length)
      scriptJquery('#core_admin_notes').before('<div class="core_loading_cont_overlay" id="core_admin_overlay_content" style="display:block;"></div>');
    else
      scriptJquery('#core_admin_overlay_content').show();
    
    var form = scriptJquery('#core_admin_notes');
    var formData = new FormData(this);
    formData.append('is_ajax', 1);
    
    submitFormAjax = scriptJquery.ajax({
      dataType: 'json',
      type:'POST',
      url: scriptJquery(this).attr('action'),
      data:formData,
      cache:false,
      contentType: false,
      processData: false,
      success:function(data) {
        scriptJquery('#core_admin_overlay_content').hide();
        if(data.status == 1) {
          if(scriptJquery('#core_success_message').length)
            scriptJquery('#core_success_message').remove();
          scriptJquery('#core_admin_notes').before('<div class="success_message" id="core_success_message" style="display:block;">'+en4.core.language.translate('Saved Successfully.')+'</div>');
          //silence
        }
      },
      error: function(data){
        //silence
      }
    });
  });

</script>
