<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: performance.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_settings", 'childMenuItemName' => 'core_admin_main_settings_performance')); ?>

<div class='settings'>
  <?php echo $this->form->render($this) ?>
</div>
<script type="text/javascript">
    en4.core.runonce.add(function() {
        showLogSize('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('core.empty.log',0); ?>');
    });


    function showLogSize(value) {
        if(value == 1) {
            scriptJquery('#logfile_size-wrapper').css("display",'flex');
        } else {
            scriptJquery('#logfile_size-wrapper').css("display",'none');
        }
    }

    //<![CDATA[
    function updateFields() {
        scriptJquery('div[id$=-wrapper][id^=file_]').hide();
        scriptJquery('div[id$=-wrapper][id^=memcache_]').hide();
        scriptJquery('div[id$=-wrapper][id^=xcache_]').hide();
        scriptJquery('div[id$=-wrapper][id^=redis_]').hide();
        var new_value = scriptJquery('input[name=type]:checked').val();
        if ('File' == new_value)
            scriptJquery('div[id$=-wrapper][id^=file_]').show();
        else if ('Memcached' == new_value)
            scriptJquery('div[id$=-wrapper][id^=memcache_]').show();
        else if ('Engine_Cache_Backend_Redis' == new_value)
            scriptJquery('div[id$=-wrapper][id^=redis_]').show();
    }
    window.addEventListener('load', function(){

        updateFields();

    <?php if ($this->isPost): ?>
        if (scriptJquery('#message').text().length) {
            scriptJquery('#message').show();
            scriptJquery('#message').insertBefore(scriptJquery('div.form-elements'));
        }
    <?php endif; ?>
        scriptJquery('input[name=type]:disabled').parents('li').addClass('disabled');
    });
    //]]>
</script>

<div id="message" style="display:none;">
  <?php echo $this->message ?>
</div>
