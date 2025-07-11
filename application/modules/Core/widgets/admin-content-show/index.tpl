<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9905 2013-02-14 02:46:28Z alex $
 * @author     John
 */
?>

<div class="admin_quick_heading">
  <h5>
    <input type="checkbox" id="newsupdates" onclick="showHide(2)"  
      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.newsupdates')) { ?> checked <?php  } ?>> 
      <?php echo $this->translate("News & Updates"); ?>
    </h5>
    <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.newsupdates')) { ?>
      <div class="dropdwon_section">
        <a class="view_btn" href="https://socialengine.com/blogs/" target="_blank"><?php echo $this->translate("View All") ?></a>
      </div>
    <?php } ?>
</div>

<script>
  function showHide(value) {
    var checkBox = document.getElementById("newsupdates");
    (scriptJquery.ajax({
      method: 'post',
      dataType: 'json',
      url: en4.core.baseUrl + 'core/index/showadmincontent/',
      data: {
        format: 'json',
        showcontent: checkBox.checked,
        value: value,
      },
      success : function(responseHTML) {
        location.reload();
      }
    }));
    return false;
  }
</script>
