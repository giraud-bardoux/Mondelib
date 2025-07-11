<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: create.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php echo $this->form->setAttrib('class', 'global_form_popup')->render($this) ?>
<script type="text/javascript">
  en4.core.runonce.add(function() {
    AutocompleterRequestJSON('name', "<?php echo $this->url(array('module' => 'user', 'controller' => 'index', 'action' => 'getusers'), 'default', true) ?>", function(selecteditem) {
      scriptJquery('#user_id').val(selecteditem.id);
    })
  });
  
  var modulename = 'core';
  var type = 'tickets';
  var category_id = '<?php echo $this->category_id; ?>';
  var subcat_id = '<?php echo $this->subcat_id; ?>';
  var subsubcat_id = '<?php echo $this->subsubcat_id; ?>';

  en4.core.runonce.add(function() {
    if(category_id && category_id != 0) {
      showSubCategory(category_id, subcat_id);
    } else {
      if(scriptJquery('#category_id').val()) {
        showSubCategory(scriptJquery('#category_id').val());
      } else {
        if(document.getElementById('subcat_id-wrapper'))
          document.getElementById('subcat_id-wrapper').style.display = "none";
      }
    }

    if(subsubcat_id) {
      if(subcat_id && subcat_id != 0) {
        showSubSubCategory(subcat_id, subsubcat_id);
      } else {
        if(document.getElementById('subsubcat_id-wrapper'))
          document.getElementById('subsubcat_id-wrapper').style.display = "none";
      }
    } else if(subcat_id) {
      showSubSubCategory(subcat_id);
    }
    else {
      if(document.getElementById('subsubcat_id-wrapper'))
        document.getElementById('subsubcat_id-wrapper').style.display = "none";
    }
  });
</script>
