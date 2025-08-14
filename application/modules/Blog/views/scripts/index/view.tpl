<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Jung
 */

?>

<h1 class="blog_view_title p-0 m-0"><?php echo $this->blog->getTitle() ?></h1>

<div class='blog_view_header d-flex flex-wrap gap-3 mt-2'>
  <div class="blog_view_header_icon">
    <?php echo $this->htmlLink($this->owner->getHref(), $this->itemBackgroundPhoto($this->owner, 'thumb.icon')); ?>
  </div>
  <div class="blog_view_header_info">
    <p class="blog_view_header_stat m-0 font_color_light font_size_small">
      <span><?php echo $this->translate('Posted by');?> <?php echo $this->htmlLink($this->owner->getHref(), $this->owner->getTitle(), array('class' => 'font_color_light')) ?></span>
      <span><?php echo $this->timestamp($this->blog->creation_date) ?></span>
    </p>
    <p class="blog_view_header_stat m-0 font_color_light font_size_small">
      <?php if( $this->category ): ?>
        <span>
          <i class="icon_category"></i>
          <span>
            <?php echo $this->translate('Filed in') ?>
            <a href='javascript:void(0);' onclick='javascript:categoryAction(<?php echo $this->category->category_id?>);' class="font_color_light"><?php echo $this->translate($this->category->category_name) ?></a>
          </span>
        </span>
      <?php endif; ?>

      <span>
        <i class="icon_view"></i>
        <span><?php echo $this->translate(array('%s view', '%s views', $this->blog->view_count), $this->locale()->toNumber($this->blog->view_count)) ?></span>
      </span>
      
      <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.enable.location', 0) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0) && !empty($this->blog->location)) { ?>
        <span>
          <i class="icon_location"></i>
          <span><a class="font_color_light" href="<?php echo 'http://maps.google.com/?q='.$this->blog->location; ?>" target="_blank"><?php echo $this->blog->location; ?></a></span>
        </span>
      <?php } ?>

    </p>
  </div>
</div>

<?php if( $this->blog && $this->blog->photo_id ) { ?>
  <div class="blog_view_photo mt-3">
    <?php echo $this->htmlLink($this->blog->getHref(), $this->itemPhoto($this->blog, 'thumb.main')); ?>
  </div>
<?php } ?>
<div class="blog_view_body rich_content_body mt-3">
  <?php echo Engine_Api::_()->core()->smileyToEmoticons($this->blog->body); ?>
</div>
<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('blog.enable.rating', 1)) { ?>
  <div class="blog_rating_view_page mt-3">
    <?php echo $this->partial('_rating.tpl', 'core', array('item' => $this->blog, 'module' => 'blog', 'param' => 'create', 'notificationType' => 'blog_rating')); ?>
  </div>
<?php } ?>
<?php if (engine_count($this->blogTags )):?>
  <div class="blog_view_tags mt-3">
    <?php foreach ($this->blogTags as $tag): ?>
      <a href='javascript:void(0);' onclick='javascript:tagAction(<?php echo $tag->getTag()->tag_id; ?>);'>#<?php echo $tag->getTag()->text?></a>&nbsp;
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<script type="text/javascript">
  en4.core.runonce.add(function() {
    // Enable links
    scriptJquery('.blog_entrylist_entry_body').enableLinks();
  });

  var tagAction = function(tag_id){
    var url = "<?php echo $this->url(array('module' => 'blog','action'=>'index'), 'blog_general', true) ?>?tag_id="+tag_id;
    loadAjaxContentApp(url);
  }

  scriptJquery('.core_main_blog').parent().addClass('active');

  // Add parant element to table
  scriptJquery('.rich_content_body table').each(function() {                            
    scriptJquery(this).addClass('table');
    scriptJquery(this).wrap('<div class="table_wrap"></div>');
  });
</script>
