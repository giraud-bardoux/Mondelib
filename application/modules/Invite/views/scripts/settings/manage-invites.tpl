<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
<div class="generic_layout_container layout_top">
  <div class="generic_layout_container layout_middle">
    <?php echo $this->content()->renderWidget('user.user-setting-cover-photo'); ?>
  </div>
</div>
<div class="generic_layout_container layout_main user_setting_main_page_main">
  <div class="generic_layout_container layout_left">
    <div class="theiaStickySidebar">
      <?php echo $this->content()->renderWidget('user.settings-menu'); ?>
    </div>
  </div>
  <div class="generic_layout_container layout_middle user_setting_main_middle">
    <div class="user_invite_tabs">
      <div class="tabs">
        <ul class="navigation">
          <li>
            <a class="" href="<?php echo $this->url(array("module" => "invite", "controller" => "index", "action" => "index"), 'default', true); ?>"><?php echo $this->translate("Invite Your Friends"); ?></a>
          </li>
          <li class="active">
            <a class="" href="<?php echo $this->url(array("module" => "invite", "controller" => "settings", "action" => "manage-invites"), 'default', true); ?>"><?php echo $this->translate("Manage Invites / Referrals"); ?></a>
          </li>
        </ul>
        <?php if($settings->getSetting('invite.enable', 1) && !empty($settings->getSetting('invite.signupenable', 0)) && Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.referralforsingup', 1)) { ?>
          <div class="copy_link_container">
            <div class="copy_link_field">
              <div class="_des font_color_light"><?php echo $this->translate("Referral Code"); ?></div>
              <input type="type" value="<?php echo $this->referral_code;?>" id="myreferralcode" />
              <button class="copy_link copy_referral_code" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?php echo $this->translate("Copy");?>"><i class="far fa-copy"></i></button>
            </div>
          </div>
          <script type="text/javascript">
            AttachEventListerSE('click','.copy_referral_code',function (e) {
              if(scriptJquery('#myreferralcode').val().length) {
                scriptJquery("<textarea/>").appendTo("body").val(scriptJquery('#myreferralcode').val()).select().each(function () {
                  document.execCommand('copy');
                }).remove();
                showSuccessTooltip('<i class="fas fa-check-circle"></i><span>'+('<?php echo $this->translate("Referral code copied successfully."); ?>')+'</span>');
              }
            });
          </script>
        <?php } ?>
      </div>
    </div>  
    <div class="theiaStickySidebar">
      <div class="user_setting_global_form">
        <div>
          <h3><?php echo $this->translate("Manage Invites"); ?></h3>
          <p><?php echo $this->translate('Below you can view the invitation history. Entering criteria into the filter fields will help you find specific invitation.'); ?></p>
        </div>
        <!--<div class="mb-2">
          <a href='<?php //echo $this->url(array('module' => 'invite'), 'default', true) ?>' class="btn btn-primary"><i class="fa fa-envelope"></i><span><?php //echo $this->translate("Invite Your Friends") ?></span></a>
        </div>-->
        <div class="manage_search invite_manage_search core_search_form">
          <?php echo $this->formFilter->render($this) ?>
        </div>
        <?php if( $this->paginator->getTotalItemCount() > 0 ): ?>
          <div class="manage_table manage_invite_table">
            <table>
              <thead>
                <tr>
                  <th><?php echo $this->translate("Recipient Email") ?></th>
                  <?php if($settings->getSetting('otpsms.signup.phonenumber', 0)) { ?>
                    <th><?php echo $this->translate("Country Code") ?></th>
                    <th><?php echo $this->translate("Phone Number") ?></th>
                  <?php } ?>
                  <th><?php echo $this->translate("Invitation Sent Date") ?></th>
                  <th><?php echo $this->translate("Invitation Method") ?></th>
                  <th><?php echo $this->translate("Options") ?></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach( $this->paginator as $item): ?>
                  <?php $inviteUrl = (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'invite', 'controller' => 'signup'), 'default', true) . '?' . http_build_query(array('code' => $item->code, 'email' => $item->recipient)); ?>
                  <?php $user = Engine_Api::_()->getItem('user', $item->user_id); ?>
                  <?php $newUser = Engine_Api::_()->getItem('user', $item->new_user_id); ?>
                  <tr>
                    <td data-label="<?php echo $this->translate("Recipient Email") ?>">
                      <?php if($newUser && isset($newUser->email) && !empty($newUser->email)) { ?>
                        <a href='mailto:<?php echo $newUser->email ?>'><?php echo $newUser->email ?></a>
                      <?php } else if($item->recipient && !is_numeric($item->recipient)) { ?>
                        <a href='mailto:<?php echo $item->recipient ?>'><?php echo $item->recipient ?></a>
                      <?php } else { ?>
                        <?php echo "---"; ?>
                      <?php } ?>
                    </td>
                    <?php if($settings->getSetting('otpsms.signup.phonenumber', 0)) { ?>
                      <td data-label="<?php echo $this->translate("Country Code") ?>">
                        <?php if($item->new_user_id && isset($newUser->country_code) && !empty($newUser->country_code)) { ?>
                          <?php echo '+'.$newUser->country_code ?>
                        <?php } else { ?>
                          <?php echo "---"; ?>
                        <?php } ?>
                      </td>
                      <td data-label="<?php echo $this->translate("Phone Number") ?>">
                        <?php if($item->new_user_id && isset($newUser->phone_number) && !empty($newUser->phone_number)) { ?>
                          <?php echo $newUser->phone_number ?>
                        <?php } else { ?>
                          <?php echo "---"; ?>
                        <?php } ?>
                      </td>
                    <?php } ?>
                    <td data-label="<?php echo $this->translate("Invitation Sent Date") ?>"><?php echo $this->locale()->toDateTime($item->timestamp) ?></td>
                    <td data-label="<?php echo $this->translate("Invitation Method") ?>"><?php echo ucfirst($item->import_method); ?></td>
                    <td class='manage_table_options'>
                      <?php $isAlreadyJoined = Engine_Api::_()->getDbtable('invites', 'invite')->isAlreadyJoined($item->recipient); ?>
                      <?php if(empty($item->new_user_id) && empty($isAlreadyJoined) && Engine_Api::_()->getApi('settings', 'core')->getSetting('invite.enable', 1)) { ?>
                        <a id="notify_button_<?php echo $item->id; ?>" href="javascript:void(0);" onclick="resendInvite('<?php echo $item->id; ?>');" class="_btn_icon" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?php echo $this->translate("Resend Invite"); ?>"><i class="fas fa-redo"></i></a>
                        <a style="display:none;" id="notifybutton_<?php echo $item->id; ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?php echo $this->translate("Invited"); ?>" href="javascript:void(0);" class="_btn_icon"><i class="fas fa-redo"></i></a>
                        <a href="javascript:void(0);" class="invite_code _btn_icon" id="invite_code" data-invite-url="<?php echo $inviteUrl; ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?php echo $this->translate("Copy Link"); ?>"><i class="far fa-copy"></i></a>
                        <a class='smoothbox _btn_icon' href='<?php echo $this->url(array('action' => 'delete', 'invite_id' => $item->id));?>' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?php echo $this->translate("Delete"); ?>">
                          <i class="fas fa-trash-alt"></i>
                        </a>
                        <?php } else { ?>
                          <?php if(!empty($item->new_user_id) && $item->user_id == $this->viewer()->getIdentity()) { ?>
                            <span class='_btn_joined'>
                              <i class="fas fa-user-check"></i> <?php echo $this->translate("Joined"); ?> 
                            </span>
                          <?php } elseif(!empty($isAlreadyJoined)) { ?> 
                            <span class='_btn_expired'>
                              <i class="fas fa-calendar-times"></i> <?php echo $this->translate("Expired"); ?>
                            </span>
                          <?php } ?>
                          <a class='smoothbox _btn_icon' href='<?php echo $this->url(array('action' => 'delete', 'invite_id' => $item->id));?>' data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="<?php echo $this->translate("Delete"); ?>">
                            <i class="fas fa-trash-alt"></i>
                          </a>
                       <?php } ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div>
            <?php echo $this->paginationControl($this->paginator, null, null, array('query' => $this->filterValues, 'pageAsQuery' => true)); ?>
          </div>
        <?php else: ?>
          <div class="tip">
            <span>
              <?php echo $this->translate("There are no invites yet.") ?>
            </span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">

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
          scriptJquery('#notify_button_'+invite_id).hide();
          scriptJquery('#notifybutton_'+invite_id).show();
        }
      }
    }));
  }
  
  scriptJquery('.user_settings_invites ').parent().addClass('active');

  AttachEventListerSE('click','.invite_code',function (e) {
    scriptJquery("<textarea/>").appendTo("body").val(scriptJquery(this).attr('data-invite-url')).select().each(function () {
        document.execCommand('copy');
      }).remove();
      showSuccessTooltip('<i class="fas fa-check-circle"></i><span>'+('<?php echo $this->translate("Referral link copied successfully."); ?>')+'</span>');
  });

  scriptJquery(``).insertBefore(scriptJquery('#date-date_from').attr("type","text").attr("autocomplete","off").attr("placeholder","From").datepicker({
      timepicker: false,
    })
  );
  scriptJquery(``).insertBefore(scriptJquery('#date-date_to').attr("type","text").attr("autocomplete","off").attr("placeholder","To").datepicker({
    timepicker: false,
   })
  );
</script>
