<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Extensions
 * @package    Blog
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
?>
<script type="text/javascript">
  var pageAction = function(page){
    document.getElementById('page').value = page;
    const formData = new FormData(scriptJquery('#filter_form')[0]);
    const params = new URLSearchParams(formData);
    let url = scriptJquery('#filter_form').attr("action")+"?"+params;
    window.history.pushState({state:'new'},'', url);
    loadAjaxContentApp(url);
  }
  var categoryAction = function(category){
    document.getElementById('page').value = 1;
    document.getElementById('blog_search_field').value = '';
    document.getElementById('category').value = category;
    document.getElementById('tag').value = '';
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';

    const formData = new FormData(scriptJquery('#filter_form')[0]);
    const params = new URLSearchParams(formData);
    let url = scriptJquery('#filter_form').attr("action")+"?"+params;
    window.history.pushState({state:'new'},'', url);
    loadAjaxContentApp(url);
  }
  AttachEventListerSE('submit', '.blog_search_form', function(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const params = new URLSearchParams(formData);
    let url = scriptJquery(this).attr("action")+"?"+params;
    window.history.pushState({state:'new'},'', url);
    loadAjaxContentApp(url);
  })
  var tagAction = function(tag){
    document.getElementById('page').value = 1;
    document.getElementById('blog_search_field').value = '';
    document.getElementById('tag').value = tag;
    document.getElementById('category').value = '';
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    const formData = new FormData(scriptJquery('#filter_form')[0]);
    const params = new URLSearchParams(formData);
    let url = scriptJquery('#filter_form').attr("action")+"?"+params;
    window.history.pushState({state:'new'},'', url);
    loadAjaxContentApp(url);
  }
  var dateAction = function(start_date, end_date){
    document.getElementById('page').value = 1;
    document.getElementById('blog_search_field').value = '';
    document.getElementById('start_date').value = start_date;
    document.getElementById('end_date').value = end_date;
    document.getElementById('tag').value = '';
    document.getElementById('category').value = '';
    const formData = new FormData(scriptJquery('#filter_form')[0]);
    const params = new URLSearchParams(formData);
    let url = scriptJquery('#filter_form').attr("action")+"?"+params;
    window.history.pushState({state:'new'},'', url);
    loadAjaxContentApp(url);
  }
</script>
<div class="blog_view_search">
  <?php $searchUrl = $this->url(array('user_id' => $this->owner->getIdentity()), 'blog_view', true); ?>

  <form id='filter_form' class="blog_search_form" method='GET' action="<?php echo $this->escape($searchUrl) ?>">
    <input type='text' class='text suggested' name='search' id='blog_search_field' size='20' maxlength='100' placeholder='<?php echo $this->translate('Search Blogs') ?>' value="<?php if( $this->search ) echo $this->escape($this->search); ?>" />
    <input type="hidden" id="tag" name="tag" value="<?php if( $this->tag ) echo $this->escape($this->tag); ?>"/>
    <input type="hidden" id="category" name="category" value="<?php if( $this->category ) echo $this->escape($this->category); ?>"/>
    <input type="hidden" id="page" name="page" value="<?php if( $this->page ) echo $this->escape($this->page); ?>"/>
    <input type="hidden" id="start_date" name="start_date" value="<?php if( $this->start_date) echo $this->escape($this->start_date); ?>"/>
    <input type="hidden" id="end_date" name="end_date" value="<?php if( $this->end_date) echo $this->escape($this->end_date); ?>"/>
  </form>

  <?php if( engine_count($this->userCategories) ): ?>
    <div class="blog_view_search_section">
      <p><?php echo $this->translate('Categories');?></p>
      <ul>
        <li>
          <a href='javascript:void(0);' onclick='javascript:categoryAction(0);' <?php if( $this->category == 0 ) echo " class='_active'" ?>>
              <?php echo $this->translate('All Categories') ?>
          </a>
        </li>
        <?php foreach( $this->userCategories as $categoryId => $categoryName ): ?>
          <li>
            <a href='javascript:void(0);' onclick='javascript:categoryAction(<?php echo $categoryId ?>);' <?php if( $this->category == $categoryId ) echo " style='font-weight: bold;'" ?>>
              <?php echo $this->translate($categoryName) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>


  <?php if( engine_count($this->userTags) ): ?>
    <div class="blog_view_search_section">
      <p><?php echo $this->translate('Tags'); ?></p>
      <ul>
        <?php foreach ($this->userTags as $tag): ?>
          <li><a href='javascript:void(0);' onclick='javascript:tagAction(<?php echo $tag->tag_id; ?>);' <?php if ($this->tag==$tag->tag_id) echo " class='_active' ";?>>#<?php echo $tag->text?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if( engine_count($this->archiveList) ):?>
    <div class="blog_view_search_section">
      <p><?php echo $this->translate('Archives');?></p>
      <ul>
        <?php foreach( $this->archiveList as $archive ): ?>
        <li>
          <a href='javascript:void(0);' onclick='javascript:dateAction(<?php echo $archive['date_start']?>, <?php echo $archive['date_end']?>);' <?php if ($this->start_date==$archive['date_start']) echo " class='_active'";?>>
            <?php echo !empty($archive['label']) ? $archive['label'] : date("F Y", $archive['date']); ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</div>