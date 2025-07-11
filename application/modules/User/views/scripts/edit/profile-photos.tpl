<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: profile-photos.tpl 10247 2014-05-30 21:34:25Z andres $
 * @author     John
 */
?>
<script type="text/javascript">
  var previewFileForceOpen;
  var check_selected = function () {
    try {
      scriptJquery('input[type=checkbox]').each(function(el) {
        if (el.attr("id") != 'checkall' && el.prop("checked") == true) {
          throw true;
        }
      });
      alert("No entry selected to delete.");
      return false;
    } catch (e) {
      return confirm("<?php echo $this->translate('Are you sure you want to delete selected files?');?>");
    }
  }

  function selectAll(obj)
  {
    scriptJquery('.checkbox').each(function(){
      scriptJquery(this).prop("checked",scriptJquery(obj).prop("checked"));
    });
  }

  var previewFile = function(event)
  {
    element = scriptJquery(event.target).closest('.admin_file').find('.admin_file_preview');

    // Ignore ones with no preview
    if( !element || element.children().length < 1 ) {
      return;
    }

    if(event.type == 'click' ) {
      if( previewFileForceOpen ) {
        previewFileForceOpen.css('display', 'none');
        previewFileForceOpen = false;
      } else {
        previewFileForceOpen = element;
        previewFileForceOpen.css('display', 'block');
      }
    }
    if( previewFileForceOpen ) {
      return;
    }

    var targetState = ( event.type == 'mouseover' ? true : false );
    element.css('display', (targetState ? 'block' : 'none'));
  }

  window.addEventListener('load', function() {
    scriptJquery('.admin_file_name').on('click mouseout mouseover',function(event){
      previewFile(event);
    });
    scriptJquery('.admin_file_preview').click(function(event){
      previewFile(event);
    });
  });

</script>
<div class="generic_layout_container layout_top">
  <div class="generic_layout_container layout_middle">
    <?php echo $this->content()->renderWidget('user.user-setting-cover-photo'); ?>
  </div>
</div>
<div class="generic_layout_container layout_main user_setting_main_page_main">
  <div class="generic_layout_container layout_left">
    <div class="theiaStickySidebar">
      <?php echo $this->content()->renderWidget('user.settings-menu'); ?>
    </div>
  </div>
  <div class="generic_layout_container layout_middle user_setting_main_middle">
    <div class="theiaStickySidebar">
      <div class="user_setting_global_form">
        <h3><?php echo $this->translate("Delete Profile Photos"); ?></h3>
        <?php if(engine_count($this->paginator) > 0): ?>
          <p><?php echo $this->translate("Below, you can delete your previously uploaded profile photos and they will no longer be part of your space."); ?></p>
          <div class="profile_photo_wrapper">
            <div class="paginator_pages">
              <?php $pageInfo = $this->paginator->getPages(); ?>
              <?php echo $this->translate(array('Showing %s-%s of %s file.', 'Showing %s-%s of %s files.', $pageInfo->totalItemCount),
                  $pageInfo->firstItemNumber, $pageInfo->lastItemNumber, $pageInfo->totalItemCount) ?>
              <span>
                <?php if( !empty($pageInfo->previous) ): ?>
                  <?php echo $this->htmlLink(array('reset' => false, 'APPEND' => '?page=' . $pageInfo->previous), 'Previous Page') ?>
                <?php endif; ?>
                <?php if( !empty($pageInfo->previous) && !empty($pageInfo->next) ): ?>
                  |
                <?php endif; ?>
                <?php if( !empty($pageInfo->next) ): ?>
                  <?php echo $this->htmlLink(array('reset' => false, 'APPEND' => '?page=' . $pageInfo->next), 'Next Page') ?>
                <?php endif; ?>
              </span>
            </div>
            <form id="multidelete_form" action="<?php echo $this->url(array('action' => 'delete-profile-photos')) ?>" method="post" onsubmit="return check_selected();">
              <table class='profile_photos_table'>
                <thead>
                  <tr>
                    <th style="width: 1%"><input onclick='selectAll(this);' type='checkbox' class='checkbox' /></th>
                    <th><?php echo $this->translate("Name") ?></th>
                    <th><?php echo $this->translate("Options") ?></th>
                  </tr>
                </thead>
                <tbody class="profile_photos">
                  <?php foreach ($this->paginator as $file): ?>
                  <tr class="admin_file">
                      <td><input type='checkbox' class='checkbox' name='photo_ids[]' id='photo_id' value="<?php echo $file['file_id']; ?>" /></td>
                      <td>
                          <div class="admin_file_name">
                              <?php echo $file['name'] ?>
                          </div>
                          <div class="admin_file_preview" style="display:none">
                            <?php echo $this->htmlImage($this->baseUrl() . '/' . $file['storage_path'], $file['name']) ?>
                          </div></td>
                      <td>
                        <a href="<?php echo $this->url(array('action' => 'delete-profile-photos', 'photo_ids' => $file['file_id'])) ?>" class="icon_photos_delete"> <?php echo $this->translate('delete') ?></a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
              <div class='buttons'>
                <button type='submit'><?php echo $this->translate("Delete Selected") ?></button>
              </div>
            </form>
          </div>
        <?php else: ?>
          <div class="tip">
            <span><?php echo $this->translate("No profile photo found"); ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
