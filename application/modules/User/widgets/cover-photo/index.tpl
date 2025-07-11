<?php ?>
<?php $settings = Engine_Api::_()->getApi('settings', 'core'); ?>

<script type="application/javascript">
	<?php if ($this->is_fullwidth) { ?>
		en4.core.runonce.add(function() {
			var htmlElement = scriptJquery("#body");
			htmlElement.addClass('user_cover_full');
			scriptJquery('#global_content').css('padding-top', 0);
			scriptJquery('#global_wrapper').css('padding-top', 0);
		});
	<?php } ?>
</script>
<?php
if (isset($this->subject->coverphoto) && !empty($this->subject->coverphoto)) {
	$memberCover =	Engine_Api::_()->storage()->get($this->subject->coverphoto, '');
	if ($memberCover)
		$memberCover = $memberCover->map();
} else if($this->defaultCoverPhoto) {
	$memberCover = Engine_Api::_()->core()->getFileUrl($this->defaultCoverPhoto);
}

if(!$memberCover) {
	$memberCover = "application/modules/Core/externals/images/blank.png";
}

$coverphotoparams = !empty($this->subject->coverphotoparams) ? Zend_Json_Decoder::decode($this->subject->coverphotoparams) : Zend_Json_Decoder::decode('{"top":"0","left":0}');

$imgurl = empty($this->subject->photo_id) ? 'application/modules/User/externals/images/nophoto_user_thumb_profile.png' : $this->subject->getPhotoUrl('thumb.profile');
?>

<div class="user_cover_wrapper user_cover_design1 <?php if ($this->is_fullwidth) { ?>user_cover_container_full<?php } ?> <?php echo $this->tab == 'inside' ? 'user_cover_tabs_wrap' : '' ?>">
	<div class="user_cover_main block">
		<div class="user_cover_image_wrapper" style="height:<?php echo $this->height; ?>px">
			<div class="user_cover_image_container" style="height:<?php echo $this->height; ?>px">
				<div id="user_cover_default" class="user_cover_thumbs" style="display:<?php echo !$memberCover ? 'block' : 'none'; ?>;"></div>
				<div class="user_cover_image">
					<img id="user_cover_id" src="<?php echo $memberCover; ?>" style="top:<?php echo $coverphotoparams['top'] . 'px'; ?>; <?php if(!empty($memberCover)) { ?> dislpay:none; <?php } ?>" />
				</div>
				<span class="user_cover_fade"></span>
				<!--Upload/Change Cover Options-->
				<?php if ($this->can_edit) { ?>
					<?php include APPLICATION_PATH .  '/application/modules/User/widgets/cover-photo/cover-options.tpl'; ?>
				<?php } ?>
				<!--Main Photo-->
				<div id="user_cover_photo_loading" class="user_cover_overlay" style="display:none;">
					<div class="user_cover_loader"></div>
				</div>
			</div>
		</div>

		<div class="user_cover_information">
			<div class="user_cover_content">
				<div class="user_cover_main_photo">
					<?php if($this->subject->getPhotoUrl('thumb.profile')) : ?>
						<span class="bg_item_photo bg_thumb_profile bg_item_photo_user" style="background-image:url(<?php echo $imgurl; ?>);" id="user_profile_photo"></span>
					<?php else : ?>
						<span class="bg_item_photo bg_thumb_profile bg_item_photo_user bg_item_nophoto" id="user_profile_photo"></span>
					<?php endif;?>
					<?php if ($this->can_edit) { ?>
						<?php include APPLICATION_PATH .  '/application/modules/User/widgets/cover-photo/main-photo-options.tpl'; ?>
					<?php } ?>
				</div>
				<div class="user_cover_info">
					<div class="user_cover_info_left">
						<?php if ($this->subject) { ?>
							<h1 class="user_cover_title"><?php echo $this->subject->getTitle() ?></h1>
							<div class="user_cover_info_stats">
								<?php if (Engine_Api::_()->getApi('settings', 'core')->getSetting('user.signup.username', 1) && $this->subject->username) { ?>
									<div>
										<span><?php echo $this->translate("@%s", $this->subject->username); ?></span>
									</div>
								<?php } ?>
								<?php if ($settings->getSetting('user.friends.eligible', '1') && $this->subject->member_count) { ?>
									<div>
										<span><?php echo $this->translate(array('%s Friend', '%s Friends', $this->subject->member_count), $this->locale()->toNumber($this->subject->member_count)) ?></span>
									</div>
								<?php } ?>
								<?php if ($settings->getSetting('core.followenable', '1')) { ?>
									<?php $followersCount = Engine_Api::_()->getDbTable('follows', 'user')->followers(array('user_id' => $this->subject->getIdentity())); ?>
									<?php if (engine_count($followersCount)) { ?>
										<div>
											<span><?php echo $this->translate(array('%s Follower', '%s Followers', engine_count($followersCount)), engine_count($followersCount)) ?></span>
										</div>
									<?php } ?>
									<?php $followingCount = Engine_Api::_()->getDbTable('follows', 'user')->following(array('user_id' => $this->subject->getIdentity())); ?>
									<?php if (engine_count($followingCount)) { ?>
										<div>
											<span><?php echo $this->translate('%s Following', engine_count($followingCount)); ?></span>
										</div>
									<?php } ?>
								<?php } ?>
							</div>
							<!-- <div class="user_cover_info_stats">
								<div>
									<i class="icon_time"></i>
									<span><?php //echo  $this->translate('Member Since') . ': ' . $this->timestamp($this->subject->creation_date); ?></span>
								</div>
							</div> -->
							<?php if (!empty($this->subject->location) && Engine_Api::_()->getApi('settings', 'core')->getSetting('enableglocation', 0)) { ?>
								<div class="user_cover_info_stats">
									<div>
										<i class="icon_map"></i>
										<span><a href="<?php echo 'http://maps.google.com/?q=' . $this->subject->location; ?>" target="_blank"><?php echo $this->subject->location; ?></a></span>
									</div>
								</div>
							<?php } ?>
							<?php include APPLICATION_PATH .  '/application/modules/User/widgets/cover-photo/status.tpl'; ?>
						<?php } ?>
					</div>
					<?php include APPLICATION_PATH .  '/application/modules/User/widgets/cover-photo/icon_button.tpl'; ?>
				</div>
			</div>
		</div>
		<?php if ($this->tab == 'inside') { ?>
			<div class="user_tabs user_cover_tabs"></div>
		<?php } ?>
	</div>
</div>

<?php if (($this->module == 'user') && $this->controller == 'profile' && $this->action == 'index' && $this->tab == 'inside') : ?>
  <style type="text/css">
    @media only screen and (min-width:767px) {
      .generic_layout_container.layout_core_container_tabs>.tabs_alt {
        display: none;
      }
    }
    .generic_layout_container.layout_core_container_tabs>.tabs_alt.displayF {
      display: none !important;
    }
  </style>
  <script type="application/javascript">
    if (matchMedia('only screen and (min-width: 768px)').matches) {

			en4.core.runonce.add(function() {
				var tabs = scriptJquery('.generic_layout_container.layout_core_container_tabs').find('.tabs_alt').get(0).outerHTML;
				//scriptJquery('#main_tabs').attr('id', 'main_tabs_cover_p');
				scriptJquery('.tab_pulldown_contents').find('ul').addClass('core_ul_mail');
				scriptJquery('.tab_pulldown_contents').removeClass('tab_pulldown_contents');
				scriptJquery('.user_tabs').html(tabs);
				scriptJquery('.user_tabs').find('#main_tabs').attr('id', 'main_tabs_cover_p');
			});

      AttachEventListerSE('click', 'ul#main_tabs_cover_p li > a', function() {
        if (scriptJquery(this).parent().hasClass('more_tab'))
          return;
        var index = scriptJquery(this).parent().index() + 2;
        var divLength = scriptJquery('.generic_layout_container.layout_core_container_tabs > div');
        for (i = 0; i < divLength.length; i++) {
          scriptJquery(divLength[i]).hide();
        }
        scriptJquery('#main_tabs_cover_p').children().eq(index - 2).trigger('click');
        scriptJquery('.generic_layout_container.layout_core_container_tabs').children().eq(index).show();
      });

      AttachEventListerSE('click', '.tab_pulldown_contents ul li', function() {
        var totalLi = scriptJquery('ul#main_tabs > li').length + 1;
        var index = scriptJquery(this).index();
        var divLength = scriptJquery('.generic_layout_container.layout_core_container_tabs > div');
        for (i = 0; i < divLength.length; i++) {
          scriptJquery(divLength[i]).hide();
        }
        scriptJquery('.core_ul_mail').children().eq(index).trigger('click');
        scriptJquery('.generic_layout_container.layout_core_container_tabs').children().eq(index + totalLi).show();
      });
    }
  </script>
<?php endif; ?>

<?php if (isset($this->can_edit)) { ?>

	<script type="application/javascript">
		var previousPositionOfCover = scriptJquery('#user_cover_id').css('top');
		//Reposition Photo
		AttachEventListerSE('click', '#user_main_photo_reposition', function() {
			scriptJquery('.user_cover_reposition_btns').show();
			scriptJquery('.user_cover_fade').hide();
			scriptJquery('#user_cover_change').hide();
			// scriptJquery('.user_cover_inner').hide();
			scriptJquery('#user_cover_id').dragncrop({
				instruction: true,
				instructionText: '<?php echo $this->translate("Drag to Reposition") ?>'
			});
		});
		AttachEventListerSE('click', '#cancelreposition', function() {
			scriptJquery('.user_cover_reposition_btns').hide();
			scriptJquery('#user_cover_id').css('top', previousPositionOfCover);
			scriptJquery('.user_cover_fade').show();
			scriptJquery('#user_cover_change').show();
			// scriptJquery('.user_cover_inner').show();
			scriptJquery("#user_cover_id").dragncrop('destroy');

			if(typeof uploadCoverPhoto != 'undefined') {
				scriptJquery('#user_cover_id').attr('src', uploadCoverPhoto);
				uploadCoverPhoto = undefined;
			}
		});
		AttachEventListerSE('click', '#savereposition', function() {
			
			var sendposition = scriptJquery('#user_cover_id').css('top');

			if(typeof uploadCoverPhoto != 'undefined') {
				uploadCoverPhoto = undefined;
				uploadImageAfterSelect(sendposition);
				return;
			}

			var sendposition = scriptJquery('#user_cover_id').css('top');
			scriptJquery('#user_cover_photo_loading').show();
			var uploadURL = en4.core.baseUrl + 'user/coverphoto/reposition-cover/user_id/<?php echo $this->subject->user_id ?>';
			var formData = new FormData();
			formData.append('position', sendposition);
			var jqXHR = scriptJquery.ajax({
				url: uploadURL,
				type: "POST",
				contentType: false,
				processData: false,
				data: formData,
				cache: false,
				success: function(response) {
					response = scriptJquery.parseJSON(response);
					if (response.status == 1) {
						previousPositionOfCover = sendposition;
						scriptJquery('.user_cover_reposition_btns').hide();
						scriptJquery("#user_cover_id").dragncrop('destroy');
						scriptJquery('.user_cover_fade').show();
						scriptJquery('#user_cover_change').show();
					} else {
						alert('<?php echo $this->string()->escapeJavascript("Something went wrong, please try again later.") ?>');
					}
					scriptJquery('#user_cover_photo_loading').hide();
					//silence
				}
			});
		});

		//Upload Main User Photo Code
		function uploadFileToServerMain(files) {
			<?php if ($this->fullwidth) { ?>
				scriptJquery('.user_cover_main_photo').append('<div id="user_cover_loading_main" class="user_cover_overlay" style="display:flex;"><div class="user_cover_loader"></div></div>');
			<?php } else { ?>
				scriptJquery('.user_cover_main_photo').append('<div id="user_cover_loading_main" class="user_cover_overlay" style="display:flex;"><div class="user_cover_loader"></div></div>');
			<?php } ?>
			var formData = new FormData();
			formData.append('webcam', files);
			uploadURL = en4.core.baseUrl + 'user/coverphoto/upload-main/user_id/<?php echo $this->subject->user_id ?>';
			var jqXHR = scriptJquery.ajax({
				url: uploadURL,
				type: "POST",
				contentType: false,
				processData: false,
				cache: false,
				data: formData,
				success: function(response) {
					response = scriptJquery.parseJSON(response);
					scriptJquery('#uploadFileMainCoverPhoto').val('');
					scriptJquery('#user_cover_loading_main').remove();
					scriptJquery('#user_profile_photo').removeClass('bg_item_nophoto');
					scriptJquery('#user_profile_photo').css("background-image", "url(" + response.src + ")");
					scriptJquery('#change_main_cvr_pht').html('<i class="fa fa-plus"></i>' + en4.core.language.translate('Change Cover Photo'));
					scriptJquery('#user_main_photo_i').css('display', 'block !important');
					loadAjaxContentApp(window.location.href,false,"full");
				}
			});
		}

		function uploadFileMainCoverPhoto(input) {
			var url = input.value;
			var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
			if (input.files && input.files[0] && (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF' || ext == "webp" || ext == 'webp')) {
				uploadFileToServerMain(input.files[0]);
			} else {
				//Silence
			}
		}
		
		AttachEventListerSE('click', '#change_main_cvr_pht', function() {
			document.getElementById('uploadFileMainCoverPhoto').click();
		});

		//Upload Cover Photo Code
		en4.core.runonce.add(function() {
			scriptJquery('<div class="user_photo_update_popup " id="coverphoto_popup_existing_upload" style="display:none"><div class="user_photo_update_popup_overlay"></div><div class="user_photo_update_popup_container" id="coverphoto_popup_container_existing"><div class="user_photo_update_popup_header "><span><?php echo $this->translate("Select a photo") ?></span><a class="fa fa-times" href="javascript:;" onclick="hideCoverPhotoUpload()" title="<?php echo $this->translate("Close") ?>"></a></div><div class="user_photo_update_popup_content"><div id="coverphoto_existing_data"></div><div id="coverphoto_profile_existing_img" style="display:none;text-align:center;"><img src="application/modules/Core/externals/images/loading.gif" alt="<?php echo $this->translate("Loading"); ?>" style="margin-top:10px;"  /></div></div></div></div>').appendTo('#append-script-data');
		});
		
		AttachEventListerSE('click', '#uploadCoverPhoto', function() {
			document.getElementById('uploadFileUserCoverPhoto').click();
		});

		var uploadCoverPhoto;
		function readCoverPhotoImageUrl(input) {
			var url = input.files[0].name;
			var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
			if ((ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF' || ext == "webp")) {
				var reader = new FileReader();
		    reader.onload = function (e) {
		      
					uploadCoverPhoto = scriptJquery('#user_cover_id').attr('src');
					if (scriptJquery('#user_cover_video_id').length)
						scriptJquery('#user_cover_video_id').hide();
					scriptJquery('#user_cover_id').show();
					scriptJquery('#user_cover_id').attr('src', e.target.result);
					
					scriptJquery('#user_cover_default').hide();
					//scriptJquery('#uploadCoverPhoto').html('<i class="fa fa-plus"></i>' + en4.core.language.translate('Change Cover Photo'));
					//scriptJquery('#removeCover').css('display', 'flex');
					//scriptJquery('#user_main_photo_reposition').css('display', 'flex');
					
					scriptJquery('#user_cover_photo_loading').hide();
					//scriptJquery('#uploadFileUserCoverPhoto').val('');
					scriptJquery('#user_main_photo_reposition').trigger('click'); 
		    }
		    
		    reader.readAsDataURL(input.files[0]);
			}
		}

		function uploadImageAfterSelect(sendposition) {
			var formData = new FormData();
			formData.append('position', sendposition ? sendposition : '');
			formData.append('webcam', scriptJquery('#uploadFileUserCoverPhoto')[0].files[0]);
			formData.append('user_id', '<?php echo $this->subject->user_id; ?>');
			scriptJquery('#user_cover_photo_loading').show();
			scriptJquery.ajax({
				xhr: function() {
					var xhrobj = scriptJquery.ajaxSettings.xhr();
					if (xhrobj.upload) {
						xhrobj.upload.addEventListener('progress', function(event) {
							var percent = 0;
							var position = event.loaded || event.position;
							var total = event.total;
							if (event.lengthComputable) {
								percent = Math.ceil(position / total * 100);
							}
							//Set progress
						}, false);
					}
					return xhrobj;
				},
				url: en4.core.baseUrl + 'user/coverphoto/edit-coverphoto/',
				type: "POST",
				contentType: false,
				processData: false,
				cache: false,
				data: formData,
				success: function(response) {
					text = JSON.parse(response);
					if (text.status == 'true') {
						if (text.src != '') {
							if (scriptJquery('#user_cover_video_id').length)
								scriptJquery('#user_cover_video_id').hide();
							scriptJquery('#user_cover_id').show();
							scriptJquery('#user_cover_id').attr('src', text.src);
						}
						scriptJquery('#user_cover_default').hide();
						scriptJquery('#uploadCoverPhoto').html('<i class="fa fa-plus"></i>' + en4.core.language.translate('Change Cover Photo'));
						scriptJquery('#removeCover').css('display', 'flex');
						scriptJquery('#user_main_photo_reposition').css('display', 'flex');

						previousPositionOfCover = sendposition;
						scriptJquery('.user_cover_reposition_btns').hide();
						scriptJquery("#user_cover_id").dragncrop('destroy');
						scriptJquery('.user_cover_fade').show();
						scriptJquery('#user_cover_change').show();
					}
					scriptJquery('#user_cover_photo_loading').hide();
					scriptJquery('#uploadFileUserCoverPhoto').val('');
					loadAjaxContentApp(window.location.href,false,"full");  
				}
			});
		}

		function removeProfilePhoto() {
		//scriptJquery('#user_main_photo_i').click(function() {
			<?php if ($this->fullwidth) { ?>
				scriptJquery('.user_cover_main_photo').append('<div id="user_cover_loading_main" class="user_cover_overlay" style="display:flex;"><div class="user_cover_loader"></div></div>');
			<?php } else { ?>
				scriptJquery('.user_cover_main_photo').append('<div id="user_cover_loading_main" class="user_cover_overlay" style="display:flex;"><div class="user_cover_loader"></div></div>');
			<?php } ?>
			var user_id = '<?php echo $this->subject->user_id; ?>';
			uploadURL = en4.core.baseUrl + 'user/coverphoto/remove-main/user_id/' + user_id;
			var jqXHR = scriptJquery.ajax({
				url: uploadURL,
				type: "POST",
				contentType: false,
				processData: false,
				cache: false,
				success: function(response) {
					scriptJquery('#change_main_cvr_pht').html('<i class="fa fa-plus"></i>' + en4.core.language.translate('Add User Photo'));
					response = scriptJquery.parseJSON(response);
					scriptJquery('#user_profile_photo').css("background-image", "url('')");
					scriptJquery('#user_profile_photo').addClass('bg_item_nophoto');
					scriptJquery('#user_cover_loading_main').remove();
					scriptJquery('#user_main_photo_i').hide();
					//silence
					loadAjaxContentApp(window.location.href,false,"full"); 
				}
			});
		//});
		}

		function removeCoverPhoto() {
			scriptJquery('#removeCover').css('display', 'none');
			//scriptJquery('#user_cover_id').attr('src',  '' );
			scriptJquery('#user_cover_photo_loading').show();
			//scriptJquery('#user_cover_default').show();
			var user_id = '<?php echo $this->subject->user_id; ?>';
			uploadURL = en4.core.baseUrl + 'user/coverphoto/remove-cover/user_id/' + user_id;
			var jqXHR = scriptJquery.ajax({
				url: uploadURL,
				type: "POST",
				contentType: false,
				processData: false,
				cache: false,
				success: function(response) {
					var response = scriptJquery.parseJSON(response);
					scriptJquery('#user_cover_photo_loading').hide();
					scriptJquery('#uploadCoverPhoto').html('<i class="fa fa-plus"></i>' + en4.core.language.translate('Add Cover Photo'));
					scriptJquery('#user_main_photo_reposition').css('display', 'none');
					scriptJquery('#user_cover_id').css('top', '0');
					//update defaultphoto if available from admin.
					if (response.src) {
						scriptJquery('#usercovevideo_cover_video_id').hide();
						scriptJquery('#user_cover_id').show();
						scriptJquery('#user_cover_id').attr('src', response.src);
					} else {
						scriptJquery('#usercovevideo_cover_video_id').hide();
						scriptJquery('#user_cover_id').show();
						scriptJquery('#user_cover_id').attr('src', '');
					}
					loadAjaxContentApp(window.location.href,false,"full");  
				}
			});
		}

		function hideCoverPhotoUpload() {
			canPaginatePageNumber = 1;
			scriptJquery('#coverphoto_popup_cam_upload').hide();
			scriptJquery('#coverphoto_popup_existing_upload').hide();
			if (typeof Webcam != 'undefined') {
				scriptJquery('.slimScrollDiv').remove();
				scriptJquery('.user_photo_update_popup_content').html('<div id="coverphoto_existing_data"></div><div id="coverphoto_profile_existing_img" style="display:none;text-align:center;"><img src="application/modules/Core/externals/images/loading.gif" alt="Loading" style="margin-top:10px;"  /></div>');
			}
		}
		
		AttachEventListerSE('click', function(event) {
			if (event.target.id == 'change_coverphoto_profile_txt' || event.target.id == 'cover_change_btn_i' || event.target.id == 'cover_change_btn') {
				scriptJquery('#user_cover_option_main_id').removeClass('active')
				if (scriptJquery('#user_cover_change').hasClass('active'))
					scriptJquery('#user_cover_change').removeClass('active');
				else
					scriptJquery('#user_cover_change').addClass('active');
			} else if (event.target.id == 'change_main_txt' || event.target.id == 'change_main_btn' || event.target.id == 'change_main_i') {
				console.log('tyes');
				scriptJquery('#user_cover_change').removeClass('active');
				if (scriptJquery('#user_cover_option_main_id').hasClass('active'))
					scriptJquery('#user_cover_option_main_id').removeClass('active');
				else
					scriptJquery('#user_cover_option_main_id').addClass('active');

			} else {
				scriptJquery('#user_cover_change').removeClass('active')
				scriptJquery('#user_cover_option_main_id').removeClass('active')
			}
		});


		AttachEventListerSE('click', '#fromCoverPhotoExistingAlbum', function() {
			scriptJquery('#coverphoto_popup_existing_upload').show();
			existingCoverPhotosGet();
		});
		
		var canPaginatePageNumber = 1;
		function existingCoverPhotosGet() {
			scriptJquery('#coverphoto_profile_existing_img').show();
			var URL = en4.core.baseUrl + 'user/coverphoto/existing-photos/';
			scriptJquery.ajax({
				method: 'post',
				'url': URL,
				'data': {
					format: 'html',
					cover: 'cover',
					page: canPaginatePageNumber,
					is_ajax: 1
				},
				success: function(responseHTML) {
					scriptJquery('#coverphoto_existing_data').append(responseHTML);
					scriptJquery('#coverphoto_existing_data').slimscroll({
						height: 'auto',
						alwaysVisible: true,
						color: '#000',
						railOpacity: '0.5',
						disableFadeOut: true,
					});
					scriptJquery('#coverphoto_existing_data').slimScroll().bind('slimscroll', function(event, pos) {
						if (canPaginateExistingPhotos == '1' && pos == 'bottom' && scriptJquery('#coverphoto_profile_existing_img').css('display') != 'block') {
							scriptJquery('#coverphoto_profile_existing_img').css('position', 'absolute').css('width', '100%').css('bottom', '5px');
							existingCoverPhotosGet();
						}
					});
					scriptJquery('#coverphoto_profile_existing_img').hide();
				}
			});
		}

		AttachEventListerSE('click', 'a[id^="user_cover_existing_album_see_more_"]', function(event) {
			event.preventDefault();
			var thatObject = this;
			scriptJquery(thatObject).parent().hide();
			var id = scriptJquery(this).attr('id').match(/\d+/)[0];
			var pageNum = parseInt(scriptJquery(this).attr('data-src'), 10);
			scriptJquery('#user_existing_album_see_more_loading_' + id).show();
			if (pageNum == 0) {
				scriptJquery('#user_existing_album_see_more_page_' + id).remove();
				return;
			}
			var URL = en4.core.baseUrl + 'user/coverphoto/existing-albumphotos/';
			scriptJquery.ajax({
				method: 'post',
				'url': URL,
				'data': {
					format: 'html',
					page: pageNum + 1,
					id: id,
					cover: 'cover',
				},
				success: function(responseHTML) {
					scriptJquery('#user_photo_content_' + id).append(responseHTML);
					var dataSrc = scriptJquery('#user_existing_album_see_more_page_' + id).html();
					scriptJquery('#user_existing_album_see_more_' + id).attr('data-src', dataSrc);
					scriptJquery('#user_existing_album_see_more_page_' + id).remove();
					if (dataSrc == 0)
						scriptJquery('#user_existing_album_see_more_' + id).parent().remove();
					else
						scriptJquery(thatObject).parent().show();
					scriptJquery('#user_existing_album_see_more_loading_' + id).hide();
				}
			});
		});

		AttachEventListerSE('click', 'a[id^="user_cover_upload_existing_photos_"]', function(event) {
			event.preventDefault();
			var id = scriptJquery(this).attr('id').match(/\d+/)[0];
			if (!id)
				return;
			scriptJquery('#user_cover_photo_loading').show();
			hideCoverPhotoUpload();
			var URL = en4.core.baseUrl + 'user/coverphoto/uploadexistingcoverphoto/';
			scriptJquery.ajax({
				method: 'post',
				'url': URL,
				'data': {
					format: 'html',
					id: id,
					cover: 'cover',
					user_id: '<?php echo $this->subject->user_id; ?>'
				},
				success: function(responseHTML) {
					text = JSON.parse(responseHTML);
					if (text.status == 'true') {
						if (text.src != '') {
							if (scriptJquery('#user_cover_video_id').length)
								scriptJquery('#user_cover_video_id').hide();
							scriptJquery('#user_cover_id').show();
							scriptJquery('#user_cover_id').attr('src', text.src);
						}
						scriptJquery('#user_cover_default').hide();
						scriptJquery('#uploadCoverPhoto').html('<i class="icon_add"></i>' + en4.core.language.translate('Change Cover Photo'));
						scriptJquery('#removeCover').css('display', 'flex');
						scriptJquery('#user_main_photo_reposition').css('display', 'flex');
						scriptJquery('#user_main_photo_reposition').trigger('click'); 
					}
					scriptJquery('#user_cover_photo_loading').hide();
					scriptJquery('#uploadFileUserCoverPhoto').val('');
				}
			});
		});
	</script>
<?php } ?>
