<?php

/**
* SocialEngine
*
* @category   Application_Extensions
* @package    Core
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: _languages.tpl 9785 2012-09-25 08:34:18Z $
*/

?>
<?php if( 1 !== engine_count($this->languageNameList) ): ?>

  <?php $selectedLanguage = (isset($_COOKIE['en4_language']) && !empty($_COOKIE['en4_language'])) ? $_COOKIE['en4_language'] : ($this->viewer && !empty($this->viewer->getIdentity() && $this->viewer->language) ? $this->viewer->language : Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en')); ?>
  <?php $isLanguageExist = Engine_Api::_()->getDbTable('languages', 'core')->isLanguageExist($selectedLanguage); ?>
  <?php if($isLanguageExist) { ?>
    <?php 
      $languageItem = Engine_Api::_()->getItem('core_language', $isLanguageExist);
      $path = '';
      if($languageItem && !empty($languageItem->icon)) {
        $path = Engine_Api::_()->core()->getFileUrl($languageItem->icon);
      }
    ?>
  <?php } ?>
  <div class="language_chooser">
    <a href="javascript:void(0))" class='language_chooser_btn dropdown-toggle'  data-bs-toggle="dropdown" aria-expanded="false">
      <?php if($path) { ?>
        <img src="<?php echo $path; ?>" alt="img" data-bs-toggle="tooltip" data-bs-placement="bottom" title="<?php echo $this->translate("Language") ?>">
      <?php } ?>
      <span><?php echo $languageItem->name; ?></span>	
    </a>
    <ul class="dropdown-menu">
      <?php if( 1 !== engine_count($this->languageNameList) ): ?>
        <?php foreach($this->languageNameList as $key => $languageNameList) { ?>
          <?php $isLanguageExist = Engine_Api::_()->getDbTable('languages', 'core')->isLanguageExist($key); ?>
          <?php if($isLanguageExist) {
            $languageItem = Engine_Api::_()->getItem('core_language', $isLanguageExist);
            $path = '';
            if($languageItem && !empty($languageItem->icon)) {
              $path = Engine_Api::_()->core()->getFileUrl($languageItem->icon);
            }
          }?>
          <li id="footer_language_<?php echo $this->identity; ?>" <?php if($selectedLanguage == $key) { ?> selected="selected" <?php } ?> >
            <a class="dropdown-item" href="javascript:void(0);" onclick="setLanguage('<?php echo $key; ?>')">
              <?php if(!empty($path)) { ?>
                <img src="<?php echo $path; ?>" alt="img">
              <?php } ?>
              <span><?php echo $this->translate($languageNameList) ?></span>	
            </a>
          </li>
        <?php } ?>
      <?php endif; ?>
    </ul>
  </div>
  <script type="application/javascript">
    function setLanguage(value) {
      scriptJquery.post("core/utility/locale",{
        language:value, 
        return:'<?php echo $this->url(); ?>',
        admin: true,
      },function (response) {
        proxyLocation.reload("full");
      });
      scriptJquery('#footer_language_<?php echo $this->identity; ?>').submit();
    }
  </script>
<?php endif; ?>
