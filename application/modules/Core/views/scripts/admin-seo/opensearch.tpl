<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: opensearch.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_seo_opensearch')); ?>

<h2 class="page_heading"><?php echo $this->translate('SEO Settings') ?></h2>
<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<?php $basePath = APPLICATION_PATH . DIRECTORY_SEPARATOR .'osdd.xml'; ?>
<script type="text/javascript">
    var modifications = [];
    window.onbeforeunload = function() {
        if( modifications.length > 0 ) {
            return '<?php echo $this->translate("If you leave the page now, your changes will be lost. Are you sure you want to continue?") ?>';
        }
    }
    var pushModification = function(type) {
        modifications.push(type);
    }
    var removeModification = function(type) {
        for (var i = modifications.length; i--;){
            if (modifications[i] === type) modifications.splice(i, 1);
        }
    }
    var changeThemeFile = function(file) {
        var url = '<?php echo $this->url() ?>?file=' + file;
        window.location.href = url;
    }
    var saveFileChanges = function() {
        var request = scriptJquery.ajax({
            url : '<?php echo $this->url(array('action' => 'save')) ?>',
            dataType : 'json',
            method : 'post',
            data : {
                body : scriptJquery('#body').val(),
                format : 'json',
                basePath: "<?php echo $this->string()->escapeJavascript($basePath); ?>",
            },
            success : function(responseJSON) {
                if( responseJSON.status ) {
                    removeModification('body');
                    scriptJquery('.admin_themes_header_revert').css('display', 'inline');
                    alert('<?php echo $this->string()->escapeJavascript($this->translate("Your changes have been saved!")) ?>');
                } else {
                    alert('<?php echo $this->string()->escapeJavascript($this->translate("An error has occurred. Changes could NOT be saved.")) ?>');
                }
            }
        });
    }
</script>

<p><?php echo $this->translate("Open search is way to search for products in your website from the address bar of the browsers like Chrome without loading the website."); ?></p>

<div class="admin_theme_editor_wrapper">
  <form action="<?php echo $this->url(array('action' => 'save')) ?>" method="post">
    <div class="admin_theme_edit">
        <div class="admin_theme_editor_edit_wrapper">
          <div class="admin_theme_editor">
            <?php echo $this->formTextarea('body', $this->activeFileContents, array('onkeypress' => 'pushModification("body")', 'spellcheck' => 'false')) ?>
          </div>
          <button class="activate_button" type="submit" onclick="saveFileChanges();return false;"><?php echo $this->translate("Save Changes") ?></button>
          <?php echo $this->formHidden('basePath', 'osdd.xml', array()) ?>
        </div>
    </div>
  </form>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_settings').parent().addClass('active');
  scriptJquery('.core_admin_main_settings_seo').addClass('active');
</script>
