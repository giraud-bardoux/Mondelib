<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: photo.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php $href = $this->url(array('controller' => 'edit', 'action' => 'profile-photos'), 'user_extended'); ?>
<script>
  function replaceError(content, newContent) {
    scriptJquery('ul.errors li').each( function(li) {
      if (li.html() === content) {
        li.html(newContent);
      }
    }
  );}
  document.addEventListener('DOMContentLoaded', function() {
    replaceError(
      '<?php echo $this->translate("File creation failed. You may be over your upload limit. Try uploading a smaller file, or delete some files to free up space. ")?>',
      '<?php echo $this->translate(sprintf("File creation failed. You may be over your upload limit. Try uploading a smaller file, or %1sdelete%2s some files to free up space. ",
         "<a href=\'$href\' target=\'_blank\'>", "</a>"))?>'
    );
  }, false);
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
        <?php echo $this->form->render($this) ?>
      </div>
    </div>
  </div>
</div>
