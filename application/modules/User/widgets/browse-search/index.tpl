
<?php
/* Include the common user-end field switching javascript */
echo $this->partial('_jsSwitch.tpl', 'fields', array(
    'topLevelId' => (int) $this->topLevelId,
    'topLevelValue' => (int) $this->topLevelValue
));
?>
<?php echo $this->partial('_location.tpl', 'core', array('modulename' => 'user')); ?>
<div class="sidebar_search_form core_search_form">
<?php echo $this->form->setAction($this->url(array(), 'user_general', true))->render($this); ?>
</div>
<script type="text/javascript">
  en4.core.runonce.add(function () {
    var formElement = scriptJquery('.layout_user_browse_search .field_search_criteria');
    // On search
    formElement.on('submit', function (event) {
      if (!window.searchMembers) {
          return;
      }
      searchMembers();
    });
    
    AutocompleterRequestJSON('displayname', "<?php echo $this->url(array('module' => 'user', 'controller' => 'index', 'action' => 'getusers'), 'default', true) ?>", function(selecteditem) {
      //scriptJquery('#user_id').val(selecteditem.id);
    });
  });
</script>
