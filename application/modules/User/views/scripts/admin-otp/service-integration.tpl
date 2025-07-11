<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: sms-template-settings.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'parentMenuItemName' => 'core_admin_main_otp', 'childMenuItemName' => 'core_admin_otp_integration')); ?>

<h2 class="page_heading"><?php echo $this->translate('OTP Settings') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<p><?php echo $this->translate("Here, you can configure the 3rd party services to enable OTP on the signup and login on your website. You can also enable or disable any service anytime. To enable and disable the service click on edit button and here you can enable and disable it."); ?></p>
<br>

<table class="admin_table" style="width: 100%;">
  <thead>
    <tr>
      <th align="left" style="width: 50%;"><?php echo $this->translate("Title"); ?></th>
      <th align="center" class="admin_table_centered" style="width: 25%;"><?php echo $this->translate("Status"); ?></th>
      <th align="left" style="width: 25%;"><?php echo $this->translate("Option"); ?></th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td align="left" style="width: 50%;"><?php echo $this->translate("Amazon"); ?></td>
      <td class="admin_table_centered" style="width: 25%;">
        <?php if($this->enabledService == "amazon"){ ?>
          <img src="application/modules/Core/externals/images/admin/check.png" alt="" title="Enabled">
        <?php }else{ ?>
          <img src="application/modules/Core/externals/images/admin/uncheck.png" alt="" title="Disabled">
        <?php  } ?>
<!--        <a href="<?php //echo $this->url(array('module'=>'user','controller'=>'otp','action'=>'enable-service','type'=>'amazon'),'admin_default',true); ?>">
          
        </a>-->
      </td>
      <td align="left" style="width: 25%;">
        <a href="<?php echo $this->url(array('module'=>'user','controller'=>'otp','action'=>'amazon'),'admin_default',true); ?>"><?php echo $this->translate("Edit"); ?></a>
     </td>
    </tr>
    <tr>
      <td align="left" style="width: 50%;"><?php echo $this->translate("Twilio"); ?></td>
      <td class="admin_table_centered" style="width: 25%;">
        <?php if($this->enabledService == "twilio"){ ?>
          <img src="application/modules/Core/externals/images/admin/check.png" alt="" title="Enabled">
        <?php }else{ ?>
          <img src="application/modules/Core/externals/images/admin/uncheck.png" alt="" title="Disabled">
        <?php  } ?>
<!--        <a href="<?php //echo $this->url(array('module'=>'user','controller'=>'otp','action'=>'enable-service','type'=>'twilio'),'admin_default',true); ?>">

        </a>-->
      </td>
      <td align="left" style="width: 25%;">
        <a href="<?php echo $this->url(array('module'=>'user','controller'=>'otp','action'=>'twilio'),'admin_default',true); ?>"><?php echo $this->translate("Edit"); ?></a>
      </td>
    </tr>
    <tr>
      <td align="left" style="width: 50%;"><?php echo $this->translate("MSG91"); ?></td>
      <td class="admin_table_centered" style="width: 25%;">
        <!--<a href="<?php //echo $this->url(array('module'=>'user','controller'=>'otp','action'=>'enable-service','type'=>'message91'),'admin_default',true); ?>">-->
          <?php if($this->enabledService == "message91"){ ?>
          <img src="application/modules/Core/externals/images/admin/check.png" alt="" title="Enabled">
          <?php }else{ ?>
          <img src="application/modules/Core/externals/images/admin/uncheck.png" alt="" title="Disabled">
          <?php  } ?>
        <!--</a>-->
      </td>
      <td align="left" style="width: 25%;">
        <a href="<?php echo $this->url(array('module'=>'user','controller'=>'otp','action'=>'message91'),'admin_default',true); ?>"><?php echo $this->translate("Edit"); ?></a>
      </td>
    </tr>
  </tbody>
</table>
<script>
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_otp').addClass('active');
</script>
