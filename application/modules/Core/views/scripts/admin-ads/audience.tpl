<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: audience.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>

<script type="text/javascript">
  en4.core.runonce.add(function(){
    scriptJquery('th.admin_table_short input[type=checkbox]').on('click', function(event) {
      var el = scriptJquery(event.target);
      scriptJquery('input[type=checkbox]').prop('checked', el.prop('checked'));
    });
  });

  var changeOrder = function(orderby, direction){
    scriptJquery('#orderby').val(orderby);
    scriptJquery('#orderby_direction').val(direction);
    scriptJquery('#filter_form').trigger("submit");
  }


  var delectSelected =function(){
    var checkboxes = scriptJquery('input[type=checkbox]');
    var selecteditems = [];
    checkboxes.each(function(){
      var item = scriptJquery(this);
      var checked = item.prop('checked');
      var value = item.val();
      if (checked == true && value != 'on'){
        selecteditems.push(value);
      }
    });

    scriptJquery('#ids').val(selecteditems);
    scriptJquery('#delete_selected').trigger("submit");
    //en4.core.baseUrl+'admin/announcements/deleteselected/selected/'+selecteditems;
    //window.location = "http://www.google.com/";
  }

 function changeStatus(adcampaign_id) {
    scriptJquery('input[type=radio]').attr('disabled', true);
    (scriptJquery.ajax({
      dataType: 'json',
      url : '<?php echo $this->url(array('module' => 'core', 'controller' => 'admin-ads', 'action' => 'status'), 'default', true) ?>',
      data : {
        format : 'json',
        adcampaign_id : adcampaign_id
      },
      success : function(responseJSON)
      {
        window.location.reload();
      }
    }));

  }
</script>
<h2 class="page_heading"><?php echo $this->translate("Editing Ad Campaign") ?></h2>


<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>
<p>
  <?php echo $this->translate("Specify which members will be shown advertisements from this campaign. To include the entire member population in this campaign, leave all of the user levels and subnetworks selected. To select multiple member levels or subnetworks, use CTRL-click. Note that this advertising campaign will only be displayed to logged-in users that match both a member level AND a network you've selected.") ?>
</p>
<br/>
<form method='post'>
<table cellspacing="0" cellpadding="0" align="center">
  <tbody><tr>
  <td><b><?php echo $this->translate("User Levels") ?></b></td>
  <td style="padding-left: 10px;"><b><?php echo $this->translate("Networks") ?></b></td>
  </tr>
  <tr>
  <td>
    <select style="width: 335px;" multiple="multiple" name="ad_levels[]" class="text" size="<?php echo max(engine_count($this->levels), engine_count($this->networks))?>">
      <?php foreach ($this->levels as $level): ?>
        <option value="<?php echo $level->getIdentity();?>" <?php if(@engine_in_array($level->getIdentity(), $this->selected_levels)) echo "selected";?>><?php echo $level->getTitle();?></option>
      <?php endforeach; ?>
    </select>
  </td>
  <td style="padding-left: 10px;">
    <select style="width: 335px;" multiple="multiple" name="ad_networks[]" class="text" size="<?php echo max(engine_count($this->levels), engine_count($this->networks))?>">
      <?php foreach ($this->networks as $network): ?>
        <option value="<?php echo $network->getIdentity();?>" <?php if(@engine_in_array($network->getIdentity(), $this->selected_networks)) echo "selected";?>><?php echo $network->getTitle();?></option>
      <?php endforeach; ?>
    </select>
  </td>
  </tr>
  </tbody>
</table>
<br/>

<div class='buttons'>
  <button type='submit'><?php echo $this->translate("Save Settings") ?></button>
</div>
</form>
