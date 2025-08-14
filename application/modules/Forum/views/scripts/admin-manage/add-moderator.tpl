<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Forum
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: add-moderator.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<?php echo $this->form->render($this) ?>
<div class="forum_admin_manage_users">
  <ul id="user_list"></ul>
</div>
<script type="text/javascript">

  en4.core.runonce.add(function() {
    scriptJquery('#forum_form_admin_moderator_create').on('submit', function(event) {
      event.preventDefault();
      var user_id  = scriptJquery('#user_id').val();
      if(user_id) {
        document.getElementById('forum_form_admin_moderator_create').submit();
      } else {
        updateUsers();
      }
    });
  });

function addModerator(user_id) {
  scriptJquery('#user_id').val(user_id);
  scriptJquery('#forum_form_admin_moderator_create').trigger("submit");
}

function updateUsers() {
  var request = scriptJquery.ajax({
    url : '<?php echo $this->url(array('module' => 'forum', 'controller' => 'manage', 'action' => 'user-search'), 'admin_default', true);?>',
    method: 'GET',
    data : {
      format : 'html',
      page : '1',
      forum_id : <?php echo $this->forum->getIdentity();?>,
      username : scriptJquery('#username').val()
    },
    success : function(responseHTML) {
      if( responseHTML.length > 0 ) {
        scriptJquery('#user_list').css('display', 'block');
      } else {
        scriptJquery('#user_list').css('display', 'none');
      }
      scriptJquery('#user_list').html(responseHTML);
      parent.Smoothbox.instance.doAutoResize();
      return false;
    }
  });
}
</script>
