<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: templates.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_mailtemplates')); ?>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>


<script type="text/javascript">
  var mailTemplateLanguage = '<?php echo $this->language ?>';
  
  var setEmailLanguage = function(language) {
    var url = '<?php echo $this->url(array('language' => null, 'template' => null)) ?>';
    window.location.href = url + '/language/' + language;
  }

  var fetchEmailTemplate = function(template_id) {
    var url = '<?php echo $this->url(array('language' => null, 'template' => null)) ?>';
    window.location.href = url + '/language/' + mailTemplateLanguage + '/template/' + template_id;
  }

</script>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_mailtemplates').addClass('active');
</script>
