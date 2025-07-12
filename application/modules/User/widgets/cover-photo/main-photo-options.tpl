<?php ?>

<div class="user_cover_change_cover_main" id="user_cover_option_main_id">
  <input type="file" id="uploadFileMainCoverPhoto" name="main_photo_cvr" onchange="uploadFileMainCoverPhoto(this);" style="display:none" />
  <div class="dropdown">
    <button class="btn btn-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="<?php echo $this->translate("Upload Profile Picture"); ?>">
      <i class="icon_camera m-0"></i>
    </button>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item" href="javascript:;" id="change_main_cvr_pht"><i class="icon_upload"></i><?php echo $this->subject->photo_id ? $this->translate("Change User Photo") : $this->translate("Add User Photo"); ?></a></li>
      <li><a href="<?php echo $this->baseUrl() . '/user/coverphoto/remove-profile-photo/'; ?>" class="dropdown-item ajaxsmoothbox" style="display:<?php echo ((isset($this->subject->photo_id) && $this->subject->photo_id != 0 && $this->subject->photo_id != '')) ? 'flex !important' : 'none !important'; ?>;" data-src="<?php echo $this->subject->photo_id; ?>"><i class="icon_delete"></i><?php echo $this->translate('Remove User Photo'); ?></a></li>
    </ul>
  </div>
</div>