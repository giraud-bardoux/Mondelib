<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: create.tpl 10110 2013-10-31 02:04:11Z andres $
 * @author     Jung
 */
?>
<?php echo $this->partial('_location.tpl', 'core', array('modulename' => 'blog')); ?>

<script type="text/javascript">
  en4.core.runonce.add(function()
  {
    scriptJquery('#tags').selectize({
      maxItems: 10,
      valueField: 'label',
      labelField: 'label',
      searchField: 'label',
      create: true,
      load: function(query, callback) {
          if (!query.length) return callback();
          scriptJquery.ajax({
            url: '<?php echo $this->url(array('controller' => 'tag', 'action' => 'suggest'), 'default', true) ?>',
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
  });
  
  var modulename = 'blog';
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
<?php if (($this->current_count >= $this->quota) && !empty($this->quota)):?>
  <div class="tip">
    <span>
      <?php echo $this->translate('You have already uploaded the maximum number of entries allowed.');?>
      <?php echo $this->translate('If you would like to upload a new entry, please <a href="%1$s">delete</a> an old one first.', $this->url(array('action' => 'manage'), 'blog_general'));?>
    </span>
  </div>
  <br/>
<?php else:?>
  <div class="global_form_wrap">
    <?php echo $this->form->render($this);?>
  </div>
<?php endif; ?>
<script type="text/javascript">
  scriptJquery('.core_main_blog').parent().addClass('active');
</script>
