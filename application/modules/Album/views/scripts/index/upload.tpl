<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: upload.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Sami
 */
?>
<?php echo $this->partial('_location.tpl', 'core', array('modulename' => 'album')); ?>
<script type="text/javascript">
  var updateTextFields = function()
  {
    var fieldToggleGroup = ['#title-wrapper', '#category_id-wrapper', '#location-wrapper', '#description-wrapper', '#search-wrapper',
                            '#auth_view-wrapper',  '#auth_comment-wrapper', '#auth_tag-wrapper', '#networks-wrapper'];
        fieldToggleGroup = scriptJquery(fieldToggleGroup.join(','))
    if (scriptJquery('#album').val() == 0) {
      fieldToggleGroup.show();
    } else {
      fieldToggleGroup.hide();
    }
  }
  en4.core.runonce.add(function() {
    new Uploader('#upload_file', {
      uploadLinkClass : 'buttonlink icon_photos_new',
      uploadLinkTitle : '<?php echo $this->translate("Add Photos");?>',
      uploadLinkDesc : '<?php echo $this->translate("Click \"Add Photos\" to select one or more photos from your computer."
        . " After you have selected the photos, they will begin to upload right away. "
        . "When your upload is finished, click the button below your photo list to save them to your album.");?>'
    });
    updateTextFields();
  });
  
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
<div class="global_form_wrap">
  <?php echo $this->form->render($this) ?>
</div>
<script>
  AttachEventListerSE('submit','#form-upload',function() {
    scriptJquery('#submit').attr('disabled','disabled');
  });
  scriptJquery('.core_main_album').parent().addClass('active');
</script>
