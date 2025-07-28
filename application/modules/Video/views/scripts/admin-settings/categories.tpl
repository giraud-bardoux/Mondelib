<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Video
 * @package    Video
 * @copyright  Copyright 2015-2016 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: index.tpl 2016-07-23 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */

?>
<?php include APPLICATION_PATH .  '/application/modules/Video/views/scripts/_adminHeader.tpl';?>
<?php $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'application/modules/Core/externals/scripts/admin/categories.js'); ?>
<script type="application/javascript">
  ajaxurl = en4.core.baseUrl+"admin/video/settings/change-order";
</script>

<div class='admin-categories-form'>
  <div>
    <div class="admin-form-cont">
      <h3><?php echo $this->translate("Video Categories") ?> </h3>
      <p class="description"><?php echo $this->translate('VIEW_SCRIPTS_ADMINSETTINGS_CATEGORIES_DESCRIPTION'); ?></p>
      <div class="admin-categories-add-form">
        <h4 class="bold"><?php echo $this->translate("Add New Category"); ?></h4>
        <form id="addcategory" method="post" enctype="multipart/form-data">
          <div class="admin-form-field" id="name-required">
            <div class="admin-form-field-label">
              <label for="tag-name"><?php echo $this->translate("Category Name"); ?></label>
            </div>
            <div class="admin-form-field-element">
              <input name="category_name" autocomplete="off" id="tag-name" type="text"  size="40" >
            </div>
          </div>
          <div class="admin-form-field">
            <div class="admin-form-field-label">
              <label for="parent"><?php echo $this->translate("Parent Category"); ?></label>
            </div>
            <div class="admin-form-field-element">
              <select name="parent" id="parent" class="postform">
                <option value="-1"><?php echo $this->translate("None"); ?></option>
                <?php foreach ($this->categories as $category): ?>
                  <?php if($category->category_id == 0) : ?>
                  <?php continue; ?>
                  <?php endif; ?>
                    <option class="level-0" value="<?php echo $category->category_id; ?>"><?php echo $category->category_name; ?></option>
                  <?php 
                    $subcategory = Engine_Api::_()->getDbtable('categories', 'video')->getSubcategory(array('column_name' => "*", 'category_id' => $category->category_id));          
                    foreach ($subcategory as $subCategory):  
                  ?>
                  <option class="level-1" value="<?php echo $subCategory->category_id; ?>">&nbsp;&nbsp;&nbsp;<?php echo $subCategory->category_name; ?></option>
                <?php 
                  endforeach;
                  endforeach; 
                ?>
              </select>
            </div>
          </div>
          <div class="submit admin-form-field">
            <button type="button" id="submitaddcategory" class="upload_image_button button"><?php echo $this->translate("Add New Category"); ?></button>
          </div>
        </form>
        <div class="admin-categories-add-form-overlay" id="add-category-overlay" style="display:none"></div>
      </div>
      <div class="admin-categories-listing">
      	<div id="error-message-category-delete"></div>
        <form id="multimodify_form" method="post" onsubmit="return multiModify();">
          <table class='admin_table admin_table_order' style="width: 100%;">
            <thead>
              <tr>
                <th><input type="checkbox" onclick="selectAll()"  name="checkbox" /></th>
                <th><?php echo $this->translate("Category Name") ?></th>
                <th><?php echo $this->translate("Options") ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($this->categories as $category): ?>
              <?php if($category->category_id == 0) : ?>
                <?php continue; ?>
              <?php endif; ?>
              <tr id="categoryid-<?php echo $category->category_id; ?>" data-id="<?php echo $category->category_id; ?>">
                <td><input type="checkbox" class="checkbox check-column" name="delete_tag[]" value="<?php echo $category->category_id; ?>" /></td>
                <td><?php echo $category->category_name ?>
                  <div class="hidden" style="display:none" id="inline_<?php echo $category->category_id; ?>">
                    <div class="parent">0</div>
                  </div>
                </td>
                <td>
                  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'video', 'controller' => 'settings', 'action' => 'edit-category', 'id' => $category->category_id), $this->translate('Edit'), array('class' => 'smoothbox')) ?> | <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Delete'), array('class' => 'deleteCat','data-url'=>$category->category_id)); ?>
                </td>
              </tr>
              <?php //Subcategory Work
                $subcategory = Engine_Api::_()->getDbtable('categories', 'video')->getSubcategory(array('column_name' => "*", 'category_id' => $category->category_id));
                foreach ($subcategory as $subCategory):  ?>
                  <tr id="categoryid-<?php echo $subCategory->category_id; ?>" data-id="<?php echo $subCategory->category_id; ?>">
                    <td><input type="checkbox"  class="checkbox check-column" name="delete_tag[]" value="<?php echo $subCategory->category_id; ?>" /></td>
                    <td>-&nbsp;<?php echo $subCategory->category_name ?>
                      <div class="hidden" style="display:none" id="inline_<?php echo $subCategory->category_id; ?>">
                        <div class="parent"><?php echo $subCategory->subcat_id; ?></div>
                      </div>
                    </td>
                    <td>
                      <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'video', 'controller' => 'settings', 'action' => 'edit-category', 'id' => $subCategory->category_id), $this->translate('Edit'), array('class' => 'smoothbox')) ?> | <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Delete'), array('class' => 'deleteCat','data-url'=>$subCategory->category_id)) ?>
                    </td>
                  </tr>
                  <?php //SubSubcategory Work
                  $subsubcategory = Engine_Api::_()->getDbtable('categories', 'video')->getSubsubcategory(array('column_name' => "*", 'category_id' => $subCategory->category_id));
                  foreach ($subsubcategory as $subsubCategory): ?>
                    <tr id="categoryid-<?php echo $subsubCategory->category_id; ?>" data-id="<?php echo $subsubCategory->category_id; ?>">
                      <td><input type="checkbox" class="checkbox check-column" name="delete_tag[]" value="<?php echo $subsubCategory->category_id; ?>" /></td>
                      <td>--&nbsp;<?php echo $subsubCategory->category_name ?>
                        <div class="hidden" style="display:none" id="inline_<?php echo $subCategory->category_id; ?>">
                          <div class="parent"><?php echo $subsubCategory->subsubcat_id; ?></div>
                        </div>
                      </td>
                      <td>
                        <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'video', 'controller' => 'settings', 'action' => 'edit-category', 'id' => $subsubCategory->category_id), $this->translate('Edit'), array('class' => 'smoothbox')) ?> | <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Delete'), array('class' => 'deleteCat','data-url'=>$subsubCategory->category_id)) ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endforeach; ?>
              <?php endforeach; ?>
            </tbody>
          </table>
          <span class='buttons'>
            <button type="button" id="deletecategoryselected" class="upload_image_button button"><?php echo $this->translate("Delete Selected") ?></button>
          </span>
        </form>
      </div>
    </div>
  </div>
</div>
