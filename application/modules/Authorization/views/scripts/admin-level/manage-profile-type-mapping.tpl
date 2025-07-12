<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: mange-profile-type-mapping.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_manage", 'parentMenuItemName' => 'authorization_admin_main_manage', 'childMenuItemName' => 'authorization_admin_main_level_mapprofiletype')); ?>

<script type="text/javascript">
    var checkAll = function(obj) {
      scriptJquery('.checkbox').each(function(){
        scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"))
      });
    }
    
    var check_selected = function () {
      try {
        scriptJquery('input[type=checkbox]').each(function(el) {
          if (el.attr("id") != 'checkall' && el.prop("checked") == true) {
            throw true;
          }
        });
        alert("No entry selected to delete.");
        return false;
      } catch (e) {
        return true;
      }
    }
</script>

<h2 class="page_heading">
  <?php echo $this->translate("Member Levels") ?>
</h2>

<div class='tabs'>
  <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render() ?>
</div>

<h3><?php echo $this->translate("Manage Profile Types and Member Levels Mapping") ?></h3>
<p>
  <?php echo $this->translate("Below you can map a Profile Type created on your website to any existing Member Level. This will assign a member level to new users based on the profile type chosen by them during signup. While creating these mappings, you can also decide if you want existing users of a profile type to fall under the mapped member level or not.") ?>
</p>
<div class="admin_search">
    <?php echo $this->htmlLink(array('action' => 'map-profile-type', 'reset' => false, 'format' => 'smoothbox'), $this->translate('Create a Mapping'), array('class' => 'admin_link_btn smoothbox',)) ?>
</div>
<?php if(engine_count($this->paginator)): ?>
<form id='delete_selected' method='post' action='<?php echo $this->url(array('action' => 'delete-selected-mapping')) ?>' onsubmit="return check_selected();">

  <table class='admin_table admin_responsive_table'>
    <thead>
      <tr>
        <th style="width: 1%;">
          <input type='checkbox' class='checkbox' id="checkall" onchange="checkAll(this);" />
        </th>
        <th style="width: 1%;">
            <?php echo $this->translate("ID") ?>
        </th>
        <th style="width: 3%;">
            <?php echo $this->translate("Profile Type") ?>
        </th>
        <th style="width: 3%;">
            <?php echo $this->translate("Member Level") ?>
        </th>
        <th style="width: 3%;" class="admin_table_centered">
          <?php echo $this->translate("Members") ?>
        </th>
        <th style="width: 2%;">
          <?php echo $this->translate("Options") ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach( $this->paginator as $mapprofile ): ?>
      <tr>
        <td>
          <input class="checkbox" type="checkbox" name="actions[]" id="actions" value="<?php echo $mapprofile->mapprofiletypelevel_id; ?>">
          <?php //echo $this->formCheckbox('actions[]', $mapprofile->mapprofiletypelevel_id) ?>
        </td>
        <td data-label="ID">
          <?php echo $this->locale()->toNumber($mapprofile->mapprofiletypelevel_id) ?>
        </td>
        <td data-label="<?php echo $this->translate("Profile Type") ?>" class="admin_table_bold">
            <?php echo $this->string()->truncate(Engine_Api::_()->getItem('option',$mapprofile->profile_type_id)->label, 20); ?>
        </td>
        <td data-label="<?php echo $this->translate("Member Level") ?>" class="admin_table_bold">
          <?php echo $this->translate(Engine_Api::_()->getItem('authorization_level',$mapprofile->member_level_id)->getTitle()) ?>
        </td>
        <td data-label="<?php echo $this->translate("Members") ?>"  class="admin_table_centered">
          <?php
            $memberCount = $mapprofile->getMembershipCount($mapprofile->member_level_id);
            echo $this->translate(
                array("%s member", "%s members", $memberCount),
                $this->locale()->toNumber($memberCount)
            );
          ?>
        </td>
        <td class="admin_table_options">
          <?php echo $this->htmlLink(array('action' => 'map-profile-type', 'id' => $mapprofile->mapprofiletypelevel_id, 'reset' => false,'profileTypeId' => $mapprofile->profile_type_id), $this->translate('edit'),  array('class' => 'smoothbox')) ?> 
          <?php echo $this->htmlLink(array('action' => 'delete-mapping', 'id' => $mapprofile->mapprofiletypelevel_id, 'reset' => false, 'format' => 'smoothbox'), $this->translate('delete'), array('class' => 'smoothbox')) ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class='buttons'>
    <button type='submit'>
      <?php echo $this->translate("Delete Selected") ?>
    </button>
  </div>
</form>
<?php else: ?>
  <div class="tip">
    <span>
      <?php echo $this->translate("No mappings have been created yet.") ?>
    </span>
  </div>
<?php endif ?>
<script type="application/javascript">
  scriptJquery('.core_admin_main_manage').parent().addClass('active');
  scriptJquery('.core_admin_main_manage_levels').addClass('active');
</script>
