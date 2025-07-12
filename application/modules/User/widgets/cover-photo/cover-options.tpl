<?php ?>

<div class="user_cover_change_cover" id="user_cover_change">
  <div class="dropdown">
    <button class="btn btn-alt" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="icon_camera m-0"></i>
    </button>
    <ul class="dropdown-menu">
      <input accept="image/*" type="file" id="uploadFileUserCoverPhoto" name="art_cover" onchange="readCoverPhotoImageUrl(this);" style="display:none">
  
      <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) { ?>
        <li class="cover_option_select"><a class="dropdown-item" id="fromCoverPhotoExistingAlbum" href="javascript:;"><i class="icon_photos"></i><?php echo $this->translate("Choose From Existing Albums"); ?></a></li>
      <?php } ?>

      <li class="cover_option_add"><a class="dropdown-item" id="uploadCoverPhoto" href="javascript:;"><i class="icon_upload"></i><?php echo ($this->subject->coverphoto != 0 && $this->subject->coverphoto != '') ? $this->translate('Change Cover Photo') : $this->translate('Add Cover Photo'); ?></a></li>
      <li class="cover_option_remove"><a id="removeCover" href="<?php echo $this->baseUrl() . '/user/coverphoto/confirmation/'; ?>" class="dropdown-item ajaxsmoothbox" style="display:<?php echo ((isset($this->subject->coverphoto) && $this->subject->coverphoto != 0 && $this->subject->coverphoto != '')) ? 'flex' : 'none'; ?>;" data-src="<?php echo $this->subject->coverphoto; ?>"><i class="icon_delete"></i><?php echo $this->translate('Remove Cover'); ?></a></li>
      <li class="cover_option_reposition"><a class="dropdown-item" style="display:<?php echo $this->subject->coverphoto ? 'flex' : 'none !important'; ?>;" href="javascript:;" id="user_main_photo_reposition"><i class="icon_move"></i><?php echo $this->translate("Reposition"); ?></a></li>
    </ul>
  </div>
</div>
<div class="user_cover_reposition_btns" style="display:none;">
  <a class="btn btn-alt" href="javascript:;" id="cancelreposition"><?php echo $this->translate("Cancel"); ?></a>
  <a class="btn btn-primary" href="javascript:;" id="savereposition"><?php echo $this->translate("Save"); ?></a>
</div>