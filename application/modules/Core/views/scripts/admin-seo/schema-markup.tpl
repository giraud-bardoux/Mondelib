<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: schema-markup.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_seo_schemamarkup')); ?>

<h2 class="page_heading"><?php echo $this->translate('SEO Settings') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<script>

  en4.core.runonce.add(function() {
    hideside("<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('coreseo_schema_type', 1); ?>");
  });

  function hideside(value) {

    if(value == 1) {
      document.getElementById('coreseo_sitetitle-wrapper').style.display = 'flex';
      document.getElementById('coreseo_alternatetitle-wrapper').style.display = 'flex';
      document.getElementById('coreseo_facebook-wrapper').style.display = 'flex';
      document.getElementById('coreseo_twitter-wrapper').style.display = 'flex';
      document.getElementById('coreseo_linkedin-wrapper').style.display = 'flex';
      document.getElementById('coreseo_instagram-wrapper').style.display = 'flex';
      document.getElementById('coreseo_youtube-wrapper').style.display = 'flex';
      document.getElementById('coreseo_othermediaurl-wrapper').style.display = 'flex';
      document.getElementById('coreseo_customschema-wrapper').style.display = 'none';
    } else if(value == 3) {
      document.getElementById('coreseo_customschema-wrapper').style.display = 'flex';
      document.getElementById('coreseo_sitetitle-wrapper').style.display = 'none';
      document.getElementById('coreseo_alternatetitle-wrapper').style.display = 'none';
      document.getElementById('coreseo_facebook-wrapper').style.display = 'none';
      document.getElementById('coreseo_twitter-wrapper').style.display = 'none';
      document.getElementById('coreseo_linkedin-wrapper').style.display = 'none';
      document.getElementById('coreseo_instagram-wrapper').style.display = 'none';
      document.getElementById('coreseo_youtube-wrapper').style.display = 'none';
      document.getElementById('coreseo_othermediaurl-wrapper').style.display = 'none';
    }
  }

  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_seo').addClass('active');
</script>
