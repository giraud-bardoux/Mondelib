<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'childMenuItemName' => 'user_admin_phone_messages')); ?>

<div class="admin_common_top_section">
  <h2 class="page_heading"><?php echo $this->translate("Manage & Send Messages") ?></h2>
  <p><?php echo $this->translate("Below, you will find all the messages which you have sent to your users from this website in relation to OTP. You can use this page to monitor these messages and modify them, if required. Entering criteria into the filter fields will help you find particular message(s). Leaving the filter fields blank will show all the messages on your social network."); ?>	
</div>

<p>
  <a href="<?php echo $this->url(array('module' => "user", "controller" => 'phone-messages', 'action' => 'send-message'), 'admin_default', true); ?>" class="smoothbox admin_link_btn"><?php echo $this->translate("Send Message (SMS)"); ?></a>
</p>
<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>

<?php if($this->paginator->getTotalItemCount() > 0) { ?>
<div class="admin_table_form admin_responsive_table">
  <form id='multimodify_form' method="post" action="" onSubmit="multiModify()">
    <div class="admin_manage_action d-flex flex-wrap">
      <div class="_count">
        <?php echo $this->translate(array('%s entry found.', '%s entries found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
      </div>

      <div class="admin_manage_action_right d-flex flex-wrap align-items-center">
        <?php echo $this->paginationControl($this->paginator, null, null, array('pageAsQuery' => true,'query' => $this->formValues)); ?>
      </div>
    </div>
    <table class='admin_table'>
      <thead>
        <tr>
          <th style='width: 1%;'><?php echo $this->translate("ID") ?></th>
          <th class="admin_table_centered"><?php echo $this->translate("Sent To") ?></th>
          <th style='width: 1%;'><?php echo $this->translate("Based On") ?></th>
          <th class='admin_table_centered'><?php echo $this->translate("Profile Type") ?></th>
          <th class='admin_table_centered'><?php echo $this->translate("Member Level") ?></th>
          <th class='admin_table_centered'><?php echo $this->translate("Message"); ?></th>
          <th style='width: 1%;' class='admin_table_options'><?php echo $this->translate("Creation Date") ?></th>
        </tr>
      </thead>
      <tbody>
        <?php if (is_countable($this->paginator) && engine_count($this->paginator)): ?>
          <?php foreach ($this->paginator as $item):
            $user = $this->item('user', $item->user_id);
            ?>
            <tr>
              <td><?php echo $item->phonemessage_id; ?></td>
              <td class='admin_table_bold admin_table_centered nowrap'>
                <?php if (!empty($user->user_id)) { ?>
                  <?php echo $this->htmlLink($user->getHref(), $user->getTitle(), array('target' => '_blank')); ?>
                <?php } else {
                  echo "-";
                } ?>
              </td>
              <td class='admin_table_centered nowrap'>
                <?php if ($item->parent_type == "profiletype") {
                  echo "Profile Types";
                } else {
                  echo "Member Levels";
                }
                ?>
              </td>
              <td class='admin_table_centered nowrap'>
                <?php if ($item->parent_type == "profiletype") {
                  if (!empty($item->type)) {
                    echo $this->profile_type[$item->type];
                  } else {
                    echo "All Profile Types";
                  }
                } else {
                  echo "-";
                } ?>
              </td>
              <td class="admin_table_centered nowrap nowrap">
                <?php if ($item->parent_type != "profiletype") {
                  if (!empty($item->type)) {
                    $level = Engine_Api::_()->getItem('authorization_level', $item->type);
                    echo $level->getTitle();
                  } else {
                    echo "All Member Levels";
                  }
                } else {
                  echo "-";
                } ?>
              </td>
              <td class="admin_table_centered"><?php echo $item->message; ?></td>
              <td class='admin_table_centered nowrap'>
                <?php echo date('dS F Y ', strtotime($item->creation_date)) ?>
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
      <?php echo $this->translate("There are no messages.") ?>
    </span>
  </div>
<?php } ?>
<script type="application/javascript">
  scriptJquery('#starttime-date').attr("type","text").attr("autocomplete","off").attr("placeholder","<?php echo $this->translate('From'); ?>").datepicker({
      timepicker: false,
  });
  scriptJquery('#endtime-date').attr("type","text").attr("autocomplete","off").attr("placeholder","<?php echo $this->translate('To'); ?>").datepicker({
    timepicker: false,
  });
  
  scriptJquery('#starttime-hour, #starttime-minute, #starttime-ampm, #endtime-hour, #endtime-minute, #endtime-ampm').hide();
  AttachEventListerSE('change', '#interval', function (e) {
    var value = scriptJquery(this).val();

    if (value == 'specific') {
      scriptJquery('#starttime-wrapper, #endtime-wrapper').show();
    } else {
      scriptJquery('#starttime-wrapper, #endtime-wrapper').hide();
    }
  });
  scriptJquery('#interval').trigger('change');

  AttachEventListerSE('change', '#type', function (e) {
    var value = scriptJquery(this).val();
    if (value == 'memberlevel') {
      scriptJquery('#memberlevel').parent().show();
      scriptJquery('#profiletype').parent().hide();
    } else if (value == "profiletype") {
      scriptJquery('#memberlevel').parent().hide();
      scriptJquery('#profiletype').parent().show();
    } else {
      scriptJquery('#memberlevel').parent().hide();
      scriptJquery('#profiletype').parent().hide();
    }
  });
  scriptJquery('#type').trigger('change');

</script>
