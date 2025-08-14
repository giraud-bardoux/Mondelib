<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: index.tpl 2024-01-24 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Warning
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php include APPLICATION_PATH .  '/application/modules/Warning/views/scripts/_adminHeader.tpl';?>
<div class='tabs'>
  <ul class="navigation">
    <li>
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'warning', 'controller' => 'comingsoon'), $this->translate('Settings')) ?>
    </li>
    <li class="active">
      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'warning', 'controller' => 'visitors', 'action' => 'index'), $this->translate('Manage Visitors')) ?>
    </li>
  </ul>
</div>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    scriptJquery("#selectall").click(function(){
      if(this.checked){
        scriptJquery('.checkbox').each(function(){
          scriptJquery(".checkbox").prop('checked', true);
        });
      } else {
        scriptJquery('.checkbox').each(function(){
          scriptJquery(".checkbox").prop('checked', false);
        });
      }
    });
    
    scriptJquery("input[name='delete']").on('click', function( event ) {
      event.preventDefault();
      var selectedItems = scriptJquery("input[name='selectedItems[]']");
      var name = scriptJquery(this).attr('name');
      if (selectedItems.filter(':checked').length == 0) {
        alert('<?php echo $this->string()->escapeJavascript($this->translate("Please select items for any mass action.")) ?>');
      } else {
        if (confirm('<?php echo $this->string()->escapeJavascript($this->translate("Are you sure you want to perform this action on selected entries?")) ?>')) {
          scriptJquery('#multidelete_form').append("<input type='hidden' value='"+name+"' name='"+name+"'>");
          scriptJquery('#multidelete_form').trigger("submit");
        }
      }
    });
  });
</script>

<div class='clear settings'>
  <h3><?php echo $this->translate("Manage Visitors"); ?></h3>
  <p><?php echo $this->translate('Here, you can manage the visitors who have contacted you from the Coming Soon page. You can email individual visitor using the "Reply" link beside each of them or email all visitors using "Email All Visitors" link below.') ?></p>
  <div class="mb-2">
    <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'warning', 'controller' => 'visitors', 'action' => 'mail'), $this->translate('Email All Visitors'), array('class' => ' warning_icon_email smoothbox buttonlink')); ?>
  </div>
  <div class='admin_search admin_common_search admin_manage_activity_search'>
    <?php echo $this->formFilter->render($this) ?>
  </div>
  <?php if(engine_count($this->paginator) > 0):?>
    <form id="multidelete_form" action="<?php echo $this->url();?>" method="POST">
      <div class="admin_manage_action d-flex flex-wrap">
        <div class="admin_manage_action_option">
          <span><?php echo $this->translate('With Selected:'); ?></span>
          <input type='submit' value="Delete" name="delete" class="btn btn-danger">
        </div>
        <div class="admin_manage_action_right d-flex flex-wrap align-items-center">
          <?php echo $this->paginationControl($this->paginator, null, null, array('pageAsQuery' => true, 'query' => $this->formValues)); ?>
        </div>
        <div class="_count w-100 mt-2">
          <?php echo $this->translate(array('%s entry found.', '%s entries found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
        </div>
      </div>
      <table class="admin_table admin_responsive_table">
        <thead>
          <tr>
            <th class='admin_table_short'><input id="selectall" type='checkbox' /></th>
            <th><?php echo $this->translate("ID");?></th>
            <th><?php echo $this->translate("Name");?></th>
            <th><?php echo $this->translate("Email");?></th>
            <th><?php echo $this->translate("Message");?></th>
            <th class='admin_table_options'><?php echo $this->translate("Option");?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($this->paginator as $item): ?>
            <tr id="teams_<?php echo $item->visitor_id ?>">
              <td><input type='checkbox' class='checkbox' name='selectedItems[]' value="<?php echo $item->getIdentity(); ?>"/></td>
              <td data-label="<?php echo $this->translate("ID");?>"><?php echo  $item->getIdentity(); ?></td>
              <td data-label="<?php echo $this->translate("Name");?>"><?php echo  $item->name; ?></td>
              <td data-label="<?php echo $this->translate("Email");?>"><?php echo $item->email; ?></td>
              <td data-label="<?php echo $this->translate("Message");?>"><?php echo $this->string()->truncate($item->body, 45, '...') ?></td>
              <td class="admin_table_options">
                <a class="smoothbox" href='<?php echo $this->url(array('action' => 'read-message', 'id' => $item->getIdentity(), 'resource_type' => 'warning_visitor')) ?>'>
                  <?php echo $this->translate("Read Message") ?>
                </a>
                |
                <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'warning', 'controller' => 'visitors', 'action' => 'mail', 'email' => $item->email), $this->translate('Reply'), array('class' => ' smoothbox')); ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </form>
  <?php else:?>
    <div class="tip">
      <span>
        <?php echo $this->translate("No one has contacted yet.");?>
      </span>
    </div>
  <?php endif;?>
</div>
