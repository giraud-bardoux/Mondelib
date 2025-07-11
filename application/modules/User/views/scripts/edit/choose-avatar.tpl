<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: update-member-level.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<script>
  AttachEventListerSE('submit', '#user_avatar', function(e) {
    e.preventDefault();
    addAvatar(this);
  });

  function addAvatar(formObject) {
    
    var avatar = scriptJquery("input[name='avatar']:checked").val();
    
    if(typeof avatar == 'undefined') {
      alert('<?php echo $this->string()->escapeJavascript("Please choose avatar."); ?>');
      return false;
    } else {
      scriptJquery('#user_avatar_overlay').show();
      var formData = new FormData(formObject);
      formData.append('is_ajax', 1);
      formData.append('id', '<?php echo $this->user->getIdentity(); ?>');
      scriptJquery.ajax({
        url: "<?php echo $this->url(array('module' => 'user', 'controller' => 'edit', 'id' => $this->user->getIdentity(), 'action' => 'choose-avatar'), 'user_extended', true); ?>",
        type: "POST",
        contentType:false,
        processData: false,
        cache: false,
        data: formData,
        success: function(response) {
          var result = scriptJquery.parseJSON(response);
          if(result.status == 1) {
            scriptJquery('#user_avatar_overlay').hide();
            scriptJquery('#ajaxsmoothbox_container').html("<div id='success_message' class='success_msg m-2'><span><?php echo $this->translate('Your avatar is successfully added.'); ?></span></div>");

            scriptJquery('#success_message').fadeOut("slow", function(){
              setTimeout(function() {
                ajaxsmoothboxclose();
                location.reload();
              }, 1000);
            });
          }
        }
      });
    }
  }
</script>
<div class="user_avatar_popup">
  <div class="core_loading_cont_overlay" id="user_avatar_overlay" style="display:none;"></div>
  <div class="user_avatar_popup_content">
    <?php if(empty($this->is_ajax) ) { ?>
      <?php echo $this->form->render($this);?>
    <?php } ?>
  </div>
</div>
<?php die; ?>
