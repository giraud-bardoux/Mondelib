<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="footer_left_links">
  <span class="footer_copyright"><?php echo $this->translate('Copyright &copy;%s', date('Y')) ?></span>
  <?php foreach( $this->navigation as $item ):
    $attribs = array_diff_key(array_filter($item->toArray()), array_flip(array(
      'reset_params', 'route', 'module', 'controller', 'action', 'type',
      'visible', 'label', 'href'
    )));
    ?>
    <?php echo $this->htmlLink($item->getHref(), $this->translate($item->getLabel()), $attribs) ?>
  <?php endforeach; ?>
</div>

<?php //Languages ?>
<?php echo $this->partial('_languages.tpl', 'core', array('languageNameList' => $this->languageNameList)); ?>

<?php if(!empty($this->viewer_id)) { ?>
  <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.sell.info')): ?>
    <div class="footer_donotsell">
      <input type="checkbox" id="donosellinfo" onclick="donotSellInfo()" <?php if($this->viewer->donotsellinfo == 1) { ?> checked <?php } ?>> <?php echo $this->translate("Do Not Sell My Personal Information."); ?>
    </div>
  <?php endif; ?>
  <script type="application/javascript">
    function donotSellInfo() {
      var checkBox = document.getElementById("donosellinfo");
      (scriptJquery.ajax({
        method: 'post',
        url: en4.core.baseUrl + 'core/index/donotsellinfo/',
        dataType: 'json',
        data: {
          format: 'json',
          donotsellinfo: checkBox.checked,
        },
        success: function(responseHTML) {
        }
      }));
      return false;
    }
  </script>
<?php } ?>
