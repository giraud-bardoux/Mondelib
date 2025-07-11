<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: createad.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<?php echo $this->partial('_admin_breadcrumb.tpl', 'core', array('parentMenu' => "core_admin_main_monetization", 'parentMenuItemName' => 'core_admin_main_ads', 'childMenuItemName' => 'adcampaign_admin_main_manageads', 'lastMenuItemName' => 'Create Advertisement')); ?>

<script type="text/javascript">
//<![CDATA[
var updateTextFields = function() {
  if (scriptJquery('#mediatype-0:checked').length) {
    scriptJquery('#upload_image-wrapper').show();
    scriptJquery('#html_field-wrapper').hide();
    scriptJquery('#submit-wrapper').show();
  } else if (scriptJquery('#mediatype-1:checked').length) {
    scriptJquery('#upload_image-wrapper').hide();
    scriptJquery('#html_field-wrapper').show();
    scriptJquery('#submit-wrapper').show();
  } else {
    scriptJquery('#upload_image-wrapper').hide();
    scriptJquery('#html_field-wrapper').hide();
    scriptJquery('#submit-wrapper').hide();
  } 
};

  en4.core.runonce.add(function () {
    var uploaderInstance = new Uploader('#upload_file', {
      uploadLinkClass : 'buttonlink icon_photos_new',
      uploadLinkTitle : '<?php echo $this->translate("Add Photos");?>',
      uploadLinkDesc : '<?php echo $this->translate('CORE_VIEWS_SCRIPTS_FANCYUPLOAD_ADDPHOTOS');?>'
    });
    uploaderInstance['processCustomResponse'] = function (responseData) {
      scriptJquery('#photo_id').val(responseData.photo_id);
      var html_code_element = document.getElementById("html_field-wrapper");
      html_code_element.style.display = "block";
      scriptJquery('#html_code').val(responseData.photo_url);
    };
  });
  



  var deleteFile = function (el) {
    var photo_id = el.attr('data-file_id');
    el.parents('li').remove();
    scriptJquery.ajax({
      url : '<?php echo $this->url(Array('module'=>'core', 'controller' => 'admin-ads', 'action'=>'removephoto'), 'default') ?>',
      data: {
        format: 'json',
        photo_id: photo_id,
      },
      success : function(responseJSON) {
        var html_code_element = document.getElementById("html_field-wrapper");
        html_code_element.style.display = "none";
      }
    });
  }

var preview = function(){
  var code = scriptJquery('#html_code').val();
  var preview = scriptJquery.crtEle('div', {
  }).html(code).css({
        'height': 'auto',
        'width' : 'auto'
  });
  //if ($type(console)) console.log(preview.getAttribute('width'));
  Smoothbox.open(preview);
}
en4.core.runonce.add(updateTextFields);
//]]>
</script>
<h2 class="page_heading"><?php echo $this->translate("Editing Ad Campaign: ") ?><?php echo $this->campaign->name;?></h2>

<?php if( engine_count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>
<script type="application/javascript">
  scriptJquery('.core_admin_main_monetization').parent().addClass('active');
  scriptJquery('.core_admin_main_ads').addClass('active');
</script>
