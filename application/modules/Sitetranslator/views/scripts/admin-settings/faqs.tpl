<h2>Language Translator Plugin</h2>

<?php if (engine_count($this->navigation)): ?>
<div class='tabs seaocore_admin_tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
</div>
<?php endif; ?>
<?php
include_once APPLICATION_PATH .
'/application/modules/Sitetranslator/views/scripts/admin-settings/faq_help.tpl';
?>

