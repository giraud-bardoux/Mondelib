<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: edit.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<?php echo $this->partial('_location.tpl', 'core', array('item' => $this->subject(), 'modulename' => 'album')); ?>
<script type="text/javascript">
  var modulename = 'album';
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
<div class="layout_middle">
  <div class="generic_layout_container">
  <div class="headline">
    <h2>
     <?php echo $this->translate('Photo Albums');?>
    </h2>
   <div class="tabs">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render();
    ?>
    </div>
  </div>
 </div>
</div>
<?php echo $this->partial('_approved_tip.tpl', 'core', array('item' => $this->subject())); ?>
<div class="layout_middle">
  <div class="generic_layout_container">
    <div class="global_form_wrap">
      <?php echo $this->form->render(); ?>
    </div>
  </div>
</div>
