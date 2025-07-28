<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Video
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: edit.tpl 9987 2013-03-20 00:58:10Z john $
 * @author     Jung
 */
?>
<?php echo $this->partial('_location.tpl', 'core', array('item' => $this->subject(), 'modulename' => 'video')); ?>
<div class="layout_middle">
  <div class="generic_layout_container">
    <div class="headline">
      <h2>
        <?php echo $this->translate('Videos');?>
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
    <div class="video_edit_form global_form_wrap"><?php echo $this->form->render(); ?></div>
  </div>
</div>
<script type="text/javascript">
  var tagsUrl = '<?php echo $this->url(array('controller' => 'tag', 'action' => 'suggest'), 'default', true) ?>';
  scriptJquery('#tags').selectize({
    maxItems: 10,
    valueField: 'label',
    labelField: 'label',
    searchField: 'label',
    create: true,
    load: function(query, callback) {
      if (!query.length) return callback();
      scriptJquery.ajax({
        url: tagsUrl,
        data: { value: query },
        success: function (transformed) {
          callback(transformed);
        },
        error: function () {
            callback([]);
        }
      });
    }
  });
  
  var modulename = 'video';
  var category_id = '<?php echo $this->category_id; ?>';
  var subcat_id = '<?php echo $this->subcat_id; ?>';
  var subsubcat_id = '<?php echo $this->subsubcat_id; ?>';

  en4.core.runonce.add(function() {
    if(category_id && category_id != 0) {
      showSubCategory(category_id, subcat_id);
    } else {
      if(scriptJquery('#category_id').val() != 0) {
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
  scriptJquery('.core_main_video').parent().addClass('active');
</script>