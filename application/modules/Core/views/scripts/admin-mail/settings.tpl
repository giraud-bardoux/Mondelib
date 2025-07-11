<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: settings.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_mailsettings')); ?>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>


<script type="text/javascript">
  en4.core.runonce.add(function() {
    var smtp = scriptJquery('#mail_smtp_server-wrapper, \
              #mail_smtp_port-wrapper, \
              #mail_smtp_authentication-wrapper, \
              #mail_smtp_username-wrapper, \
              #mail_smtp_password-wrapper, \
              #mail_smtp_ssl-wrapper');

    var auth = scriptJquery('#mail_smtp_username-wrapper, #mail_smtp_password-wrapper');

    if(scriptJquery('input[id=mail_smtp-1]:checked').length){
      smtp.css('display','flex');
    } else {
      smtp.css('display','none');
    }
    scriptJquery('input[name=mail_smtp]').on('change', function(){
      if(!scriptJquery(this).prop("checked")) return;
      if(scriptJquery(this).val() == 1 ){
        smtp.css('display','flex');
        if( (scriptJquery('input[id=mail_smtp_authentication-1]:checked').length) ){
          auth.css('display','flex');
        } else {
          auth.css('display','none');
        }
      } else {
        smtp.css('display','none');
      }
    });
    if(scriptJquery('input[id=mail_smtp-1]:checked').length && scriptJquery('input[id=mail_smtp_authentication-1]:checked').length ){
      auth.css('display','flex');
    } else {
      auth.css('display','none');
    }
    scriptJquery('input[name=mail_smtp_authentication]').on('change', function(){
      if(!scriptJquery(this).prop("checked")) return;
      if(scriptJquery(this).val() == 1 ){
        auth.css('display','flex');
      } else {
        auth.css('display','none');
      }
    });
  });

</script>
