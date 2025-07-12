<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9915 2013-02-15 01:30:19Z alex $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'core_admin_main_manage_members')); ?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>
<div class="admin_common_top_section">
  <h2 class="page_heading"><?php echo $this->translate("Manage Members") ?></h2>
  <p><?php echo $this->translate("USER_VIEWS_SCRIPTS_ADMINMANAGE_INDEX_DESCRIPTION") ?><br /><?php echo $this->translate("Note: You will be able to edit <a href='admin/user/fields' target='_blank'>Profile Types</a> of members only if there are more than 1 profile type on your site excluding Admin & Super Admin profile types."); ?></p>
  <p>
    <?php
      if( $settings->getSetting('user.support.links', 0) == 1 ) {
        echo 'More info: <a href="https://community.socialengine.com/blogs/597/12/members" target="_blank">See KB article</a>.';
      } 
    ?>
  </p>	
</div>
<script type="text/javascript">
  var currentOrder = '<?php echo $this->order ?>';
  var currentOrderDirection = '<?php echo $this->order_direction ?>';
  var changeOrder = function(order, default_direction){
    // Just change direction
    if( order == currentOrder ) {
      scriptJquery('#order_direction').value = ( currentOrderDirection == 'ASC' ? 'DESC' : 'ASC' );
    } else {
      scriptJquery('#order').val(order);
      scriptJquery('#order_direction').val(default_direction);
    }
    scriptJquery('#filter_form').trigger("submit");
  }

  function multiModify()
  {
    var multimodify_form = scriptJquery('#multimodify_form');
    if (multimodify_form.find("#submit_button").val() == 'delete')
    {
      return confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to delete the selected user accounts?")) ?>');
    }
  }
  function selectAll(obj) {
    scriptJquery('.checkbox').each(function(){
      scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
    });
  }

  function loginAsUser(id) {
    if( !confirm('<?php echo $this->translate('Note that you will be logged out of your current account if you click ok.') ?>') ) {
      return;
    }
    var url = '<?php echo $this->url(array('action' => 'login')) ?>';
    var baseUrl = '<?php echo $this->url(array(), 'default', true) ?>';
    (scriptJquery.ajax({
      url : url,
      dataType: 'json',
      method : 'post',
      data : {
        format : 'json',
        id : id
      },
      success : function() {
        window.location.replace( baseUrl );
      }
    }));
  }
  
  <?php if( $this->openUser ): ?>
    en4.core.runonce.add(function() {
      scriptJquery('#multimodify_form .admin_table_options a').each(function() {
        var el = scriptJquery(this);
        if( -1 < el.attr('href').indexOf('/edit/') ) {
          el.trigger("click");
        }
      });
    });
  <?php endif ?>

  en4.core.runonce.add(function() {
    scriptJquery("input[name='approved'],input[name='disapproved'],input[name='enable'],input[name='disable'],input[name='delete']").on('click', function( event ) {
      event.preventDefault();
      var selectedItems = scriptJquery("input[name='selectedItems[]']");
      var name = scriptJquery(this).attr('name');
      if (selectedItems.filter(':checked').length == 0) {
        alert('<?php echo $this->string()->escapeJavascript($this->translate("Please select items for any mass action.")) ?>');
      } else {
        if (confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to perform this action on selected entries?")) ?>')) {
          scriptJquery('#multimodify_form').append("<input type='hidden' value='"+name+"' name='"+name+"'>");
          scriptJquery('#multimodify_form').trigger("submit");
        }
      }
    });
  });
</script>
<?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('acppro')) { ?>
  <?php $countActiveMembers = Engine_Api::_()->getDbTable('users', 'user')->countActiveMembers(); ?>
  <?php $maxusers = Engine_Api::_()->getApi('settings', 'core')->getSetting('acppro.maxusers', 0); ?>
  <?php if($maxusers > 0 && $countActiveMembers >= $maxusers) { ?>
    <div class="error_message">
      <span>
        <?php echo $this->translate('Your active member limit has been reached. To enable more members, kindly <a href="https://socialengine.com/socialengine-cloud/" target="_blank">upgrade</a> your plan.'); ?>
      </span>
    </div>
    <br />
  <?php } ?>
<?php } ?>

<p>
  <a href="<?php echo $this->url(array('module' => "user", "controller" => 'manage', 'action' => 'add-new-user'), 'admin_default', true); ?>" class="admin_link_btn"><?php echo $this->translate("Add New User"); ?></a>

  <a href="<?php echo $this->url(array('module' => "user", "controller" => 'manage', 'action' => 'manage-imports'), 'admin_default', true); ?>" class="admin_link_btn icon_import"><?php echo $this->translate("Bulk Import Members"); ?></a>
</p>
<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>

<?php if($this->paginator->getTotalItemCount() > 0) { ?>
  <div class="admin_table_form ">
    <form id='multimodify_form' method="post" action="<?php echo $this->url(array('action'=>'multi-modify'));?>" onSubmit="multiModify()">
      <div class="admin_manage_action d-flex flex-wrap">
        <div class="_count">
          <?php echo $this->translate(array('%s entry found.', '%s entries found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
        </div>
        <div class="admin_manage_action_option">
          <span><?php echo $this->translate('With Selected:'); ?></span>
          <?php if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('acppro') && @$maxusers > 0 && @$countActiveMembers >= @$maxusers) { ?>
            <?php if($maxusers > 0 && $countActiveMembers >= $maxusers) { ?>
              <!--<input type='submit' value="Approve" name="approved" class="btn btn-primary"  disabled="disabled">-->
            <?php } ?>
          <?php } else { ?>
            <input type='submit' value="Approve" name="approved" class="btn btn-primary">
          <?php } ?>
          <input type='submit' value="Disapprove" name="disapproved" class="btn btn-primary">
          <input type='submit' value="Enable" name="enable" class="btn btn-primary">
          <input type='submit' value="Disable" name="disable" class="btn btn-primary">
          <input type='submit' value="Delete" name="delete" class="btn btn-danger">
        </div>
        <div class="admin_manage_action_right d-flex flex-wrap align-items-center">
          <?php echo $this->paginationControl($this->paginator, null, null, array('pageAsQuery' => true,'query' => $this->formValues)); ?>
        </div>
      </div>
      <table class='admin_table admin_responsive_table'>
        <thead>
          <tr>
            <th style='width: 1%;'><input onclick="selectAll(this)" type='checkbox' class='checkbox'></th>
            <th style='width: 1%;'><a href="javascript:void(0);" onclick="javascript:changeOrder('user_id', 'DESC');"><?php echo $this->translate("ID") ?></a></th>
            <th style='width: 15%;'><a href="javascript:void(0);" onclick="javascript:changeOrder('displayname', 'ASC');"><?php echo $this->translate("Display Name") ?></a></th>
            <?php if($settings->getSetting('user.signup.username', 1)) { ?>
              <th><a href="javascript:void(0);" onclick="javascript:changeOrder('username', 'ASC');"><?php echo $this->translate("Username") ?></a></th>
            <?php } ?>
            <th style=''><a href="javascript:void(0);" onclick="javascript:changeOrder('email', 'ASC');"><?php echo $this->translate("Email") ?></a></th>
            <?php if($settings->getSetting('otpsms.signup.phonenumber', 0)) { ?>
              <th style=''><?php echo $this->translate("Country Code") ?></th>
              <th style=''><?php echo $this->translate("Phone Number") ?></th>
            <?php } ?>
            <th style='' class='admin_table_centered'><a href="javascript:void(0);" onclick="javascript:changeOrder('level_id', 'ASC');"><?php echo $this->translate("User Level") ?></a></th>
            <th style='' class='admin_table_centered'><?php echo $this->translate("Profile Type") ?></th>
            <th style='' class='admin_table_centered'><?php echo $this->translate("Approved") ?></th>
            <th style='' class='admin_table_centered'><?php echo $this->translate("Verified") ?></th>
            <th style=''><a href="javascript:void(0);" onclick="javascript:changeOrder('creation_date', 'DESC');"><?php echo $this->translate("Signup Date") ?></a></th>
            <th style=''><?php echo $this->translate("Last Login Date") ?></th>
            <th style='' class='admin_table_options'><?php echo $this->translate("Options") ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if( engine_count($this->paginator) ): ?>
            <?php foreach( $this->paginator as $item ):
              $user = $this->item('user', $item->user_id);
              ?>
              <tr>
                <td><input <?php if ($item->level_id == 1) echo 'disabled';?> name='selectedItems[]' value='<?php echo $item->getIdentity();?>' type='checkbox' class='checkbox'></td>
                <td data-label="ID"><?php echo $item->user_id ?></td>
                <td data-label="<?php echo $this->translate("Display Name") ?>" class='admin_table_bold admin_table_name'>
                  <?php echo $this->htmlLink($user->getHref(), $user->getTitle(), array('target' => '_blank'))?>
                </td>
                <?php if($settings->getSetting('user.signup.username', 1)) { ?>
                  <td data-label="<?php echo $this->translate("Username") ?>" class='admin_table_user'><?php echo $this->htmlLink($this->item('user', $item->user_id)->getHref(), $this->item('user', $item->user_id)->username, array('target' => '_blank')) ?></td>
                <?php } ?>
                <td data-label="<?php echo $this->translate("Email") ?>" class='admin_table_email'>
                  <?php if( !$this->hideEmails ): ?>
                    <?php if(!empty($item->email)) { ?>
                    <a href='mailto:<?php echo $item->email ?>'><?php echo $item->email ?></a>
                    <?php } else { ?>
                      ---
                    <?php } ?>
                  <?php else: ?>
                    <?php echo $this->translate('(hidden)') ?>
                  <?php endif; ?>
                </td>
                <?php if($settings->getSetting('otpsms.signup.phonenumber', 0)) { ?>
                  <td data-label="<?php echo $this->translate("Country Code") ?>" class='admin_table_email'>
                    <?php if(!empty($item->country_code) && !empty($item->phone_number)): ?>
                      +<?php echo $item->country_code; ?>
                    <?php else: ?>
                      ---
                    <?php endif; ?>
                  </td>
                  <td data-label="<?php echo $this->translate("Phone Number") ?>" class='admin_table_email'>
                    <?php if( !$this->hideEmails ): ?>
                      <?php if(!empty($item->phone_number)): ?>
                        <?php echo $item->phone_number; ?>
                      <?php else: ?>
                        ---
                      <?php endif; ?>
                    <?php else: ?>
                      <?php echo $this->translate('(hidden)') ?>
                    <?php endif; ?>
                  </td>
                <?php } ?>
                <td data-label="<?php echo $this->translate("User Level") ?>" class="admin_table_centered nowrap">
                  <a href="<?php echo $this->url(array('module'=>'authorization','controller'=>'level', 'action' => 'edit', 'id' => $item->level_id)) ?>">
                    <?php $level = Engine_Api::_()->getItem('authorization_level', $item->level_id); ?>
                    <?php if($level) { ?>
                      <?php echo $this->translate($level->getTitle()); ?>
                    <?php } ?>
                  </a>
                </td>
                <td data-label="<?php echo $this->translate("Profile Type") ?>" class="admin_table_centered nowrap">
                  <?php $optionId = Engine_Api::_()->user()->getProfileFieldValue(array('user_id' => $item->getIdentity(), 'field_id' => 1)); ?>
                  <?php if(!empty($optionId)) { ?>
                    <?php $optionLabel = Engine_Api::_()->fields()->getTable('user', 'options')->getOptionValue(array('option_id' => $optionId)); ?>
                    <?php if ($optionLabel) { ?>
                      <a href="admin/user/fields?option_id=<?php echo $optionId; ?>"><?php echo $this->translate($optionLabel); ?></a>
                    <?php } ?>
                  <?php } ?>
                </td>
                <td data-label="<?php echo $this->translate("Approved") ?>" class='admin_table_centered'>
                  <?php echo ( $item->enabled ? $this->translate('Yes') : $this->translate('No') ) ?>
                </td>
                <td data-label="<?php echo $this->translate("Verified") ?>" class='admin_table_centered'>
                  <?php echo ( $item->is_verified ? $this->translate('Yes') : $this->translate('No') ) ?>
                </td>
                <td data-label="<?php echo $this->translate("Date") ?>" class="nowrap">
                  <?php echo $this->locale()->toDateTime($item->creation_date) ?>
                </td>
                <td data-label="<?php echo $this->translate("Last Login Date") ?>" class="nowrap">
                  <?php echo $item->lastlogin_date ? $this->locale()->toDateTime($item->lastlogin_date) : '---'; ?>
                </td>
                <td class='admin_table_options'>
                  <a class='ajaxsmoothbox' href='<?php echo $this->url(array('action' => 'stats', 'id' => $item->user_id));?>'>
                    <?php echo $this->translate("stats") ?>
                  </a>
                  <?php $auth = $item->isSuperAdmin() ? $this->viewer()->isSuperAdmin($item) : 1; ?>
                  <?php if ($auth && !$this->hideEmails): ?>
                    |
                    <a class='smoothbox' href='<?php echo $this->url(array('action' => 'edit', 'id' => $item->user_id));?>'>
                      <?php echo $this->translate("edit") ?>
                    </a>
                    <?php if(!empty($this->editProfileType) && $item->level_id != 1) { ?>
                      |
                      <a class='smoothbox' href='<?php echo $this->url(array('action' => 'edit-profile-type', 'id' => $item->user_id));?>'>
                        <?php echo $this->translate("Edit Profile Type") ?>
                      </a>
                    <?php } ?>
                  <?php endif; ?>
                  <?php if ( $item->level_id != 1 ): ?>
                    |
                    <a class='smoothbox' href='<?php echo $this->url(array('action' => 'delete', 'id' => $item->user_id));?>'>
                      <?php echo $this->translate("delete") ?>
                    </a>
                    |
                    <?php if(empty($item->approved) || empty($item->enabled)) { ?>
                      <a class="text_light" data-bs-toggle="tooltip" href='javascript:;' data-bs-original-title="<?php echo $this->translate('You can not login until this member is approved.'); ?>">
                        <?php echo $this->translate("login") ?>
                      </a>
                    <?php } else { ?>
                      <a href='<?php echo $this->url(array('action' => 'login', 'id' => $item->user_id));?>' onclick="loginAsUser(<?php echo $item->user_id ?>); return false;">
                        <?php echo $this->translate("login") ?>
                      </a>
                    <?php } ?>
                  <?php endif; ?>
                  <?php if ( $this->emailResend && $item->email && $item->user_id != 1 && $item->verified == 0 ): ?>
                    |
                    <a class='smoothbox' href='<?php echo $this->url(array('action' => 'resend-email', 'id' => $item->user_id));?>'>
                      <?php echo $this->translate("Resend Email") ?>
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($settings->getSetting('otpsms.signup.phonenumber', 0)) && $item->phone_number && $item->country_code): ?>
                    |
                    <a class='smoothbox' href='<?php echo $this->url(array('action' => 'send-message', 'user_id' => $item->user_id));?>'>
                      <?php echo $this->translate("Send Message (SMS)") ?>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </form>
  </div>
<?php } else { ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no members.") ?>
    </span>
  </div>
<?php } ?>
<script type="text/javascript">
  scriptJquery(``).insertBefore(scriptJquery('#date-date_from').attr("type","text").attr("autocomplete","off").attr("placeholder","From").datepicker({
      timepicker: false,
    })
  );
  scriptJquery(``).insertBefore(scriptJquery('#date-date_to').attr("type","text").attr("autocomplete","off").attr("placeholder","To").datepicker({
    timepicker: false,
   })
  );

  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_members').addClass('active');
</script>
