<h2>Language Translator Plugin</h2>

<?php if (engine_count($this->navigation)): ?>
<div class='tabs seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
</div>
<?php endif; ?>
<br />
<?php if(empty(Engine_Api::_()->getApi('settings','core')->getSetting('sitetranslator.google.api.key'))) : ?>
<div class='clear sitetranslator_settings_form'>
    <div class="tip">
        <span>  To start translation process, please generate and configure ‘Google Translator API Key. Please <a href="<?php echo $this->baseUrl()?>/admin/sitetranslator/translator/support" target="_blank"> click here</a> to know the steps.
        </span>
    </div>
</div>
<?php endif; ?>
<?php if(!empty($this->siteandroidapp) || !empty($this->siteiosapp)): ?>
<div style="font-size: 12px"> <?php echo $this->translate("You can translate any file from one language to any other language for your Mobile Apps as well. Please click on respective buttons to translate the Mobile App files."); ?> </div>
<br/> <br/><?php $url = $this->baseUrl(array('route' => 'admin_default', 'module' => 'siteandroidapp', 'controller' => 'app-builder','action'=>'create','package'=>'pro','tab'=>'4'), $this->translate('Click To Go'), array()); ?>

<?php endif; ?>

<div class='clear sitetranslator_settings_form'>
<?php if(!empty($this->siteandroidapp)): ?>
   <button onclick="document.location=en4.core.baseUrl+'admin/siteandroidapp/app-builder/create/package/pro/tab/4'"><?php echo $this->translate('Android Mobile App') ?></button>
<?php else: ?>
<div class="tip">
   <span>You do not have "Android Mobile App" plugin.
           <a href="https://www.socialengineaddons.com/socialengine-android-mobile-application" ><?php echo $this->translate('Click here to purchase'); ?></a>
    </span> 
    
</div>

<?php endif; ?>
<?php if(!empty($this->siteiosapp)): ?>
<button onclick="document.location=en4.core.baseUrl+'admin/siteiosapp/app-builder/create/package/pro/tab/5'">IOS Mobile App</button>
<?php else: ?>
<div class="tip">
    <span> You do not have "IOS Mobile App" plugin.
        <a href="https://www.socialengineaddons.com/socialengine-ios-mobile-application-iphone-ipad" >Click here to purchase</a>
    </span> 
</div>
<?php endif; ?>
</div>


