<h2>Language Translator Plugin</h2>

<?php if (engine_count($this->navigation)): ?>
<div class='tabs seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
</div>
<?php endif; ?>
<?php if(Engine_Api::_()->getApi('settings','core')->getSetting('core.translate.adapter') != 'array') : ?>
<div class='clear sitetranslator_settings_form'>
    <div class="tip">
        <span>  To increase the speed of translation process, please enable <b>‘Translation Performance’</b> from <a href="<?php echo $this->baseUrl()?>/admin/core/settings/performance"> here.</a>
        </span>
    </div>
</div>
<?php endif; ?>
<?php if(empty(Engine_Api::_()->getApi('settings','core')->getSetting('sitetranslator.google.api.key')) && !empty(Engine_Api::_()->getApi('settings','core')->getSetting('sitetranslator.isActivate'))) : ?>
<div class='clear sitetranslator_settings_form'>
    <div class="tip">
        <span>  To start translation process, please generate and configure ‘Google Translator API Key. Please <a href="<?php echo $this->baseUrl()?>/admin/sitetranslator/translator/support" target="_blank"> click here</a> to know the steps.
        </span>
    </div>
</div>
<?php endif; ?>
<div class='clear sitetranslator_settings_form'>
    <div class='settings'>
        <?php echo $this->form->render($this) ?>
    </div>
</div>
