<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9915 2013-02-15 01:30:19Z alex $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'core_admin_main_manage_invites')); ?>
<div class="manage_invite_heading_top">
  <h2 class="page_heading"><?php echo $this->translate('Manage Invites') ?></h2>
  <?php $levels = Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.allowlevels', 'a:4:{i:0;s:1:"1";i:1;s:1:"2";i:2;s:1:"3";i:3;s:1:"4";}');
  $levelsvalue = unserialize($levels); ?>
  <?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.enable', 1) && engine_in_array($this->viewer()->level_id, $levelsvalue)) { ?>
    <a class="admin_link_btn" href="invite"><?php echo $this->translate("Invite New User"); ?></a>
  <?php } ?>
</div>


<?php if (engine_count($this->navigation)): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class="admin_common_top_section">
  <p><?php echo $this->translate("The email address of users invited to your social network are listed below. if you need to search for a specific member, enter email address in the field below.") ?></p>
  <?php
  $settings = Engine_Api::_()->getApi('settings', 'core');
  if ($settings->getSetting('user.support.links', 0) == 1) {
    echo 'More info: <a href="https://community.socialengine.com/blogs/597/23/manage-invites" target="_blank">See KB article</a>.';
  }
  ?>
</div>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery("#selectall").click(function() {
      if (this.checked) {
        scriptJquery('.checkbox').each(function() {
          scriptJquery(".checkbox").prop('checked', true);
        });
      } else {
        scriptJquery('.checkbox').each(function() {
          scriptJquery(".checkbox").prop('checked', false);
        });
      }
    });

    scriptJquery("input[name='delete']").on('click', function(event) {
      event.preventDefault();
      var selectedItems = scriptJquery("input[name='selectedItems[]']");
      var name = scriptJquery(this).attr('name');
      if (selectedItems.filter(':checked').length == 0) {
        alert('<?php echo $this->string()->escapeJavascript($this->translate("Please select items for any mass action.")) ?>');
      } else {
        if (confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected user accounts?")) ?>')) {
          scriptJquery('#multidelete_form').append("<input type='hidden' value='" + name + "' name='" + name + "'>");
          scriptJquery('#multidelete_form').trigger("submit");
        }
      }
    });
  });

  <?php if ($this->openUser): ?>
    scriptJquery(window).load(function() {
      scriptJquery('#multimodify_form .admin_table_options a').each(function(el) {
        if (-1 < el.get('href').indexOf('/edit/')) {
          el.click();
          //el.fireEvent('click');
        }
      });
    });
  <?php endif ?>

  function resendInvite(invite_id) {
    scriptJquery('#loading_image').show();
    (scriptJquery.ajax({
      url: en4.core.baseUrl + 'invite/index/resendinvite',
      method: 'get',
      data: {
        'is_ajax': 1,
        'format': 'json',
        'invite_id': invite_id,
      },
      success: function(responseJSON) {
        var responseJSON = JSON.parse(responseJSON);
        if (responseJSON.status == 'true') {
          scriptJquery('#notifyadmin_button_' + invite_id).hide();
          scriptJquery('#notifyadminbutton_' + invite_id).show();
        }
      }
    }));
  }

  AttachEventListerSE('click', '.invite_code', function(e) {
    scriptJquery("<textarea/>").appendTo("body").val(scriptJquery(this).attr('data-invite-url')).select().each(function() {
      document.execCommand('copy');
    }).remove();
    scriptJquery("#invite_code_" + scriptJquery(this).attr('data-invite-id')).html("Copied");
  });
</script>

<div class='admin_search admin_common_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>

<?php $count = $this->paginator->getTotalItemCount() ?>
<?php if ($count > 0) { ?>
  <div class="admin_table_form">
    <form id="multidelete_form" action="<?php echo $this->url(); ?>" method="POST">
      <div class="admin_manage_action d-flex flex-wrap">
        <div class="_count">
          <?php echo $this->translate(array('%s invite found.', '%s invites found.', $count), $count) ?>
        </div>
        <div class="admin_manage_action_option">
          <span><?php echo $this->translate('With Selected:'); ?></span>
          <input type='submit' value="Delete" name="delete" class="btn btn-danger">
        </div>
        <div class="admin_manage_action_right d-flex flex-wrap align-items-center">
          <?php echo $this->paginationControl($this->paginator, null, null, array('pageAsQuery' => true, 'query' => $this->formValues)); ?>
        </div>
      </div>
      <table class='admin_table admin_responsive_table' style="width:100%;">
        <thead>
          <tr>
            <th style='width: 1%;'><input id="selectall" type='checkbox' class='checkbox'></th>
            <th style='width: 1%;'><?php echo $this->translate("ID") ?></th>
            <th style='width: 1%;'><?php echo $this->translate("Sender Name") ?></th>
            <th style='width: 1%;'><?php echo $this->translate("Sender Email") ?></th>
            <th style='width: 1%;'><?php echo $this->translate("Recipient Email") ?></th>
            <?php if ($settings->getSetting('otpsms.signup.phonenumber', 0)) { ?>
              <th style='width: 1%;'><?php echo $this->translate("Country Code") ?></th>
              <th style='width: 1%;'><?php echo $this->translate("Phone Number") ?></th>
            <?php } ?>
            <th style='width: 1%;'><?php echo $this->translate("Invite Code") ?></th>
            <th style='width: 1%;'><?php echo $this->translate("Invitation Method") ?></th>
            <th style='width: 1%;'><?php echo $this->translate("Invitation Sent Date") ?></th>
            <th style='width: 1%;' class='admin_table_options'><?php echo $this->translate("Options") ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if (engine_count($this->paginator)): ?>
            <?php foreach ($this->paginator as $item): ?>

              <?php $inviteUrl = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'invite', 'controller' => 'signup'), 'default', true) . '?' . http_build_query(array('code' => $item->code, 'email' => $item->recipient)); ?>
              <?php $user = Engine_Api::_()->getItem('user', $item->user_id); ?>
              <?php $newUser = Engine_Api::_()->getItem('user', $item->new_user_id); ?>
              <tr>
                <td><input name='selectedItems[]' value="<?php echo $item->id ?>" type='checkbox' class='checkbox'></td>
                <td><?php echo $item->id ?></td>

                <td><a href="<?php echo $user->getHref(); ?>"><?php echo $user->getTitle(); ?></a></td>
                <td class='admin_table_email'>
                  <?php if (!$this->hideEmails): ?>
                    <?php echo isset($user->email) ? $user->email : ''; ?>
                  <?php else: ?>
                    (hidden)
                  <?php endif; ?>
                </td>
                <td class='admin_table_email'>
                  <?php if (!$this->hideEmails): ?>
                    <?php if ($newUser && isset($newUser->email) && !empty($newUser->email)) { ?>
                      <a href='mailto:<?php echo $newUser->email ?>'><?php echo $newUser->email ?></a>
                    <?php } else if ($item->recipient && !is_numeric($item->recipient)) { ?>
                      <a href='mailto:<?php echo $item->recipient ?>'><?php echo $item->recipient ?></a>
                    <?php } else { ?>
                      <?php echo "---"; ?>
                    <?php } ?>
                  <?php else: ?>
                    (hidden)
                  <?php endif; ?>
                </td>
                <?php if ($settings->getSetting('otpsms.signup.phonenumber', 0)) { ?>
                  <td class='admin_table_email'>
                    <?php if ($item->new_user_id && isset($newUser->country_code) && !empty($newUser->country_code)) { ?>
                      <?php echo '+' . $newUser->country_code ?>
                    <?php } else { ?>
                      <?php echo "---"; ?>
                    <?php } ?>
                  </td>
                  <td class='admin_table_email'>
                    <?php if (!$this->hideEmails): ?>
                      <?php if ($item->new_user_id && isset($newUser->phone_number) && !empty($newUser->phone_number)) { ?>
                        <?php echo $newUser->phone_number ?>
                      <?php } else { ?>
                        <?php echo "---"; ?>
                      <?php } ?>
                    <?php else: ?>
                      (hidden)
                    <?php endif; ?>
                  </td>
                <?php } ?>
                <td><?php echo $item->code ?></td>
                <td><?php echo ucfirst($item->import_method); ?></td>
                <td class="nowrap">
                  <?php echo $this->locale()->toDateTime($item->timestamp) ?>
                </td>
                <td class='admin_table_options'>
                  <?php $isAlreadyJoined = Engine_Api::_()->getDbtable('invites', 'invite')->isAlreadyJoined($item->recipient); ?>
                  <?php if (empty($item->new_user_id) && empty($isAlreadyJoined)) { ?>
                    <a id="notifyadmin_button_<?php echo $item->id; ?>" href="javascript:void(0);" onclick="resendInvite('<?php echo $item->id; ?>');" class="invite-secondary"><?php echo $this->translate("Resend Invite"); ?></a>
                    <a style="display:none;" id="notifyadminbutton_<?php echo $item->id; ?>" href="javascript:void(0);" class="invite-secondary"><?php echo $this->translate("Invited"); ?></a>
                    |
                    <a href="javascript:void(0);" class="invite-secondary invite_code" id="invite_code_<?php echo $item->id ?>" data-invite-id="<?php echo $item->id ?>" data-invite-url="<?php echo $inviteUrl; ?>"><?php echo $this->translate("Copy URL"); ?></a>
                  <?php } else { ?>
                    <?php if (!empty($item->new_user_id)) { ?>
                      <?php echo $this->translate("Joined"); ?>
                    <?php } elseif (!empty($isAlreadyJoined)) { ?>
                      <?php echo $this->translate("Expired"); ?>
                    <?php } ?>
                    |
                    <a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->id)); ?>'>
                      <?php echo $this->translate("Delete") ?>
                    </a>
                  <?php } ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </form>
  </div>
<?php } else { ?>
  <br />
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no invites yet."); ?>
    </span>
  </div>
<?php } ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_invites').addClass('active');
</script>