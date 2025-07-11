<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_layout", 'childMenuItemName' => 'core_admin_main_layout_language')); ?>

<div class="admin_common_top_section">
  <h2 class="page_heading"><?php echo $this->translate("Language Manager") ?></h2>
  <p><?php echo $this->translate("CORE_VIEWS_SCRIPTS_ADMINLANGUAGE_INDEX_DESCRIPTION") ?></p>
  <?php
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) {
      echo 'More info: <a href="https://community.socialengine.com/blogs/597/65/language-manager" target="_blank">See KB article</a>.';
    } 
  ?>	
</div>  
<script type="text/javascript">
  var changeDefaultLanguage = function(locale) {
    var url = '<?php echo $this->url(array('module'=>'core','controller'=>'language','action'=>'default')) ?>';
    scriptJquery.ajax({
      url : url,
      dataType : 'json',
      method : 'post',
      data : {
        locale : locale,
        format : 'json'
      },
      success : function() {
        window.location.replace( window.location.href );
      }
    });
  }
  var disableLanguage = function(locale, disableLocale) {
    var url = '<?php echo $this->url(array('module'=>'core','controller'=>'language','action'=>'enabled')) ?>';
    scriptJquery.ajax({
      url : url,
      dataType : 'json',
      method : 'post',
      data : {
        locale : locale,
        disableLocale: disableLocale,
        format : 'json'
      },
      success : function() {
        window.location.replace( window.location.href );
      }
    });
  }
</script>
<div class="admin_language_options">
  <a href="<?php echo $this->url(array('action' => 'create')) ?>" class="admin_link_btn admin_language_options_new"><?php echo $this->translate("Create New Pack") ?></a>
  <a href="<?php echo $this->url(array('action' => 'upload')) ?>" class="admin_link_btn admin_language_options_upload"><?php echo $this->translate("Upload New Pack") ?></a>
</div>
<?php if ($this->customLocale) : ?>
<div class="tip">
   <span>The Locale "<?php echo $this->customLocale; ?>" does not have a language package, so the default language is set to English. Please create the language pack.</span>
</div>
<?php endif; ?>

<table class="admin_table admin_languages admin_responsive_table">
  <thead>
    <tr>
      <th><?php echo $this->translate("Language") ?></th>
      <th><?php echo $this->translate("Options") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach( $this->languageNameList as $locale => $translatedLanguageTitle ): ?>
      <?php $isEnabled = Engine_Api::_()->getDbTable('languages', 'core')->isEnabled($locale);  ?>
      <tr>
        <td data-label="<?php echo $this->translate("Language") ?>">
          <?php $isLanguageExist = Engine_Api::_()->getDbTable('languages', 'core')->isLanguageExist($locale); ?>
          <?php if($isLanguageExist) {
            $languageItem = Engine_Api::_()->getItem('core_language', $isLanguageExist);
            $path = '';
            if($languageItem && !empty($languageItem->icon)) {
              $path = Engine_Api::_()->core()->getFileUrl($languageItem->icon);
            }
          }?>
          <?php if(!empty($path)) { ?>
            <img src="<?php echo $path; ?>" alt="img" class="admin_langauge_icon">
          <?php } ?>
          <?php echo $translatedLanguageTitle . ' ('.$locale.')' ?>
        </td>
        <td class="admin_table_options">
          <a href="<?php echo $this->url(array('action' => 'edit', 'locale' => $locale)) ?>"><?php echo $this->translate("edit phrases") ?></a>
          |
          <a href="<?php echo $this->url(array('action' => 'export', 'locale' => $locale)) ?>"><?php echo $this->translate("export") ?></a>
          |
          <?php echo $this->htmlLink(array('module'=>'core','controller'=>'language','action'=>'edit-icon',  'locale'=>$locale), $this->translate('Edit Image'), array('class'=>'smoothbox')) ?>
          <?php if( $this->defaultLanguage != $locale): ?>
            <?php if($isEnabled) { ?>
              |
              <?php echo $this->htmlLink('javascript:void(0);', $this->translate('make default'), array('onclick' => 'changeDefaultLanguage(\'' . $locale . '\');')) ?>
            <?php } ?>
            <?php if($locale != 'en') { ?>  
              |
              <?php echo $this->htmlLink(array('module'=>'core','controller'=>'language','action'=>'delete',  'locale'=>$locale), $this->translate('delete'), array('class'=>'smoothbox text-danger')) ?>
            <?php } ?>
            <?php //if($locale == 'en') { ?>
              |
              <?php $text = !empty($isEnabled) ? $this->translate('Disable') : $this->translate('Enable'); ?>
              <?php echo $this->htmlLink('javascript:void(0);', $text, array('onclick' => 'disableLanguage(\'' . $locale . '\', \'' . $isEnabled . '\');', 'class' => 'text_light')) ?>
            <?php //} ?>
          <?php else: ?>
            |
            <?php echo $this->translate("default") ?>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
