<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _composeAlbumVideo.tpl 2024-10-28 00:00:00Z 
 * @author     SocialEngine
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 */
?>
<?php 
	$albumEnable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('album');
	$videoEnable = Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('video');

	if(empty($albumEnable) && empty($videoEnable)) {
		return;
	}

	$subject = Engine_Api::_()->core()->hasSubject() ? Engine_Api::_()->core()->getSubject() : false;

	$allowed_photocreate = $allowed_videoupload = 0;
	$user = Engine_Api::_()->user()->getViewer();

	if($albumEnable) {
		$allowed_photocreate = (bool) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('album', $user, 'create');
		if( Engine_Api::_()->core()->hasSubject() ) {
			// Get subject
			if($subject && $subject->getType() == 'group') {
				$allowed_photocreate = Engine_Api::_()->authorization()->isAllowed('group', $user, 'photo');
			}
		}
	}
	
	if($videoEnable) {
		$is_allowed_option = (array) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'video_uploadoptn');
		$iframely = (bool) in_array('iframely', $is_allowed_option) ? 1 : 0;

		$myComputer = (bool) in_array('myComputer', $is_allowed_option) ? 1 : 0;
		$myComputerCheck = 0;

		$allowedvideoupload = (bool) Engine_Api::_()->authorization()->getAdapter('levels')->getAllowed('video', $user, 'create');
		
		$ffmpeg_path = (bool) Engine_Api::_()->getApi('settings', 'core')->video_ffmpeg_path;
		$myComputerCheck = $myComputer && $ffmpeg_path ? 1 : 0;
		if ($allowedvideoupload && $ffmpeg_path)
			$allowed_videoupload = 1;
	}

	if(!empty($albumEnable) && !empty($videoEnable)) {
		if(empty($allowed_videoupload) && empty($allowed_photocreate)) {
			return;
		} else if(!empty($allowed_videoupload) && !empty($allowed_photocreate)) {
			$label = $this->translate("Add Photo/Video");
		} else if(!empty($allowed_videoupload)) {
			$label = $this->translate("Add Video");
		} else if(!empty($allowed_photocreate)) {
			$label = $this->translate("Add Photo");
		}
	} else if(!empty($albumEnable)) {
		$label = $this->translate("Add Photo");
		if(empty($allowed_photocreate))
			return;
	} else if(!empty($videoEnable)) {
		$label = $this->translate("Add Video");
		if(empty($allowed_videoupload))
			return;
	}

?>
<style>
	#compose-photo-error {
		display: none;
	}
</style>
<script type="text/javascript">	
	if (window.proxyLocation.href.indexOf("messages/compose") > -1 || window.proxyLocation.href.indexOf("messages/view/id") > -1) {
		var isMessagePage = true;
	} else {
		var isMessagePage = false;
	}
	
	var allowed_videoupload = '<?php echo $allowed_videoupload; ?>';
	var allowed_photocreate = '<?php echo $allowed_photocreate; ?>';
	
	en4.core.runonce.add(function () {
		var type = 'wall';
		
		if (composeInstance.options.type) 
			type = composeInstance.options.type;

		composeInstance.addPlugin(new Composer.Plugin.AlbumVideo({
			title: '<?php echo $this->string()->escapeJavascript($label) ?>',
			//iframelyCheck: <?php //echo $iframely; ?>,
			//fromURL: 0,
			//myComputerCheck: <?php //echo $myComputerCheck; ?>,
			//allowed: <?php //echo $allowed; ?>,
			type: type,
			isMessagePage: isMessagePage,
			//advancedactvity: 1,
			albumEnable: '<?php echo $albumEnable && $allowed_photocreate ? 1 : 0; ?>',
			videoEnable: '<?php echo $videoEnable && $allowed_videoupload ? 1 : 0; ?>',
			requestOptions: {
				'url': en4.core.baseUrl + 'video/index/compose-upload/format/json/c_type/' + type,
				'uploadurl': en4.core.baseUrl + 'video/index/upload-video/format/json/c_type/' + type
			}
		}));
	});

	en4.core.runonce.add(function () {
		var obj = scriptJquery('#videodragandrophandler');
		obj.on('dragenter', function (e) {
			e.stopPropagation();
			e.preventDefault();
			scriptJquery(this).addClass("activitybd");
		});
		obj.on('dragover', function (e) {
			e.stopPropagation();
			e.preventDefault();
		});
		obj.on('drop', function (e) {
			scriptJquery(this).removeClass("activitybd");
			scriptJquery(this).addClass("activitybm");
			e.preventDefault();
			var files = e.originalEvent.dataTransfer.files;
			//We need to send dropped files to Server
			videohandleFileUpload(files, obj);
		});
		AttachEventListerSE('dragenter', function (e) {
			e.stopPropagation();
			e.preventDefault();
		});
		AttachEventListerSE('dragover', function (e) {
			e.stopPropagation();
			e.preventDefault();
		});
		AttachEventListerSE('drop', function (e) {
			e.stopPropagation();
			e.preventDefault();
		});
	});

	var rowCount = 0;
	AttachEventListerSE('click', 'div[id^="abortPhoto_"]', function () {
		var id = scriptJquery(this).attr('id').match(/\d+/)[0];
		if (typeof jqXHR[id] != 'undefined') {
			jqXHR[id].abort();
			delete filesArray[id];
			execute = true;
			scriptJquery(this).parent().remove();
			executeuploadVideo();
		} else {
			delete filesArray[id];
			scriptJquery(this).parent().remove();
		}
		if (isMessagePage && (scriptJquery('#toValues-wrapper').length > 0 || scriptJquery('#submit-wrapper').length > 0)) {
			scriptJquery('#videodragandrophandler').show();
		}
	});

	function videocreateStatusbar(obj, file) {
		rowCount++;
		var row = "odd";
		if (rowCount % 2 == 0) row = "even";
		var checkedId = scriptJquery("input[name=cover]:checked");
		this.objectInsert = scriptJquery('<div class="activity_compose_photo_item ' + row + '"></div>');
		this.overlay = scriptJquery("<div class='overlay activity_compose_photo_item_overlay'></div>").appendTo(this.objectInsert);
		this.abort = scriptJquery('<div class="abort" id="abortPhoto_' + countUploadAlbumVideo + '"><span><?php echo $this->string()->escapeJavascript($this->translate("Cancel")); ?></span></div>').appendTo(this.objectInsert);
		this.progressBar = scriptJquery('<div class="overlay_image progressBar"><div></div></div>').appendTo(this.objectInsert);
		this.imageContainer = scriptJquery('<div class="activity_compose_photo_item_photo"></div>').appendTo(this.objectInsert);
		this.src = scriptJquery('<img src="' + en4.core.baseUrl + 'application/modules/Core/externals/images/blank-img.gif">').appendTo(this.imageContainer);
		this.infoContainer = scriptJquery('<div class="activity_compose_photo_item_info"></div>').appendTo(this.objectInsert);
		this.size = scriptJquery('<span class="font_color_light"></span>').appendTo(this.infoContainer);
		this.filename = scriptJquery('<span class=""></span>').appendTo(this.infoContainer);

		this.option = scriptJquery('<div class="activity_compose_photo_item_option"><span class=""></span><a class="video_delete_image_upload" href="javascript:void(0);"><i class="fas fa-times"></i></a></div>').appendTo(this.objectInsert);

		var objectAdd = scriptJquery(this.objectInsert).appendTo('#show_video');

		this.setFileNameSize = function (name, size) {
			if (typeof size != 'undefined') {
				var sizeStr = "";
				var sizeKB = size / 1024;
				if (parseInt(sizeKB) > 1024) {
					var sizeMB = sizeKB / 1024;
					sizeStr = sizeMB.toFixed(2) + " MB";
				}
				else {
					sizeStr = sizeKB.toFixed(2) + " KB";
				}
				this.size.html(sizeStr);
			}
			this.filename.html(name);
		}
		this.setProgress = function (progress) {
			var progressBarWidth = progress * this.progressBar.width() / 100;
			this.progressBar.find('div').animate({ width: progressBarWidth }, 10).html(progress + "% ");
			if (parseInt(progress) >= 100) {
				scriptJquery(this.progressBar).remove();
			}
		}
		this.setAbort = function (jqxhr) {
			var sb = this.objectInsert;
			this.abort.click(function () {
				jqxhr.abort();
				sb.hide();
				executeuploadVideo();
			});
		}
	}

	var selectedFileLength = 0;
	var statusArray = new Array();
	var filesArray = [];
	var countUploadAlbumVideo = 0;
	var fdActivity = new Array();

	function videohandleFileUpload(files, obj) {
		
		selectedFileLength = files.length;

		//Max upload file size check
		var FileSize = files[0].size; // in byte
    	if(FileSize > post_max_size) {
			return;
		}

		for (var i = 0; i < files.length; i++) {
			var url = files[i].name;

			//Check video and photo privacy
			var mimeType = files[i]["type"];
			if(mimeType.split('/')[0] === 'image' && !allowed_photocreate) {
				return false;
			} else if(mimeType.split('/')[0] === 'video' && allowed_videoupload == 0) {
				return false;
			}

			var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();

			if (isMessagePage) {
				if (ext == "mp4" || (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF' || ext == "webp")) {
					var status = new videocreateStatusbar(obj, files[i]); //Using this we can set progress.
					status.setFileNameSize(files[i].name, files[i].size);
					statusArray[countUploadAlbumVideo] = status;
					filesArray[countUploadAlbumVideo] = files[i];
					countUploadAlbumVideo++;
				}
			} else {
				if ((ext == "mp4" || ext == "flv" || ext == "mov" || ext == "avi" || ext == "m4v" || ext == "wmv" || ext == "webm") || (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF' || ext == "webp")) {
					var status = new videocreateStatusbar(obj, files[i]); //Using this we can set progress.
					status.setFileNameSize(files[i].name, files[i].size);
					statusArray[countUploadAlbumVideo] = status;
					filesArray[countUploadAlbumVideo] = files[i];
					countUploadAlbumVideo++;
				}
			}
		}
		executeuploadVideo();
	}

	var execute = true;
	function executeuploadVideo() {
		if (Object.keys(filesArray).length == 0 && scriptJquery('#show_video').html() != '') {
			if (isMessagePage && (scriptJquery('#toValues-wrapper').length > 0 || scriptJquery('#submit-wrapper').length > 0)) { } else {
				scriptJquery('#compose-menu').show();
			}
		}
		if (execute == true) {
			for (var i in filesArray) {
				if (filesArray.hasOwnProperty(i)) {
					sendFileToServerVideo(filesArray[i], statusArray[i], filesArray[i], 'upload', i);
					break;
				}
			}
		}
	}

	var jqXHR = new Array();
	function sendFileToServerVideo(formData, status, file, isURL, i) {
		execute = false;
		var formData = new FormData();
		formData.append('Filedata', file.url ? file.url : file);
		if (isURL == 'upload' && !file.url) {
			var reader = new FileReader();
			reader.onload = function (e) {
				//status.src.attr('src', e.target.result);
			}
			reader.readAsDataURL(file);
			var urlIs = '';
		} else {
			status.src.attr('src', file.url ? file.url : file);
			var urlIs = true;
		}
		if(file.url){
			formData.append('isUrl', 1);
		}
		if(isMessagePage) {
			var type = 'message';
		} else {
			var type = 'wall';
		}

		var url = file.name;
		var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();

		if (isMessagePage) {
			scriptJquery('#videodragandrophandler').hide();
		}

		if (composeInstance.options.type) type = composeInstance.options.type;
		scriptJquery('#show_video_container').addClass('iscontent');
		var url = '&isURL=' + urlIs;

		if (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF' || ext == 'webp') {
			var videouploadURL = en4.core.baseUrl + 'album/album/compose-upload/isactivity/true/format/json/type/' + type;
		} else if ((ext == "mp4" || ext == "flv" || ext == "mov" || ext == "avi" || ext == "m4v" || ext == "wmv" || ext == "webm")) {
			var videouploadURL = en4.core.baseUrl + 'video/index/upload-video/isactivity/true/format/json/c_type/' + type; //Upload URL
		}

		var extraData = {}; //Extra Data.
		jqXHR[i] = scriptJquery.ajax({
			xhr: function () {
				var xhrobj = scriptJquery.ajaxSettings.xhr();
				if (xhrobj.upload) {
					xhrobj.upload.addEventListener('progress', function (event) {
						var percent = 0;
						var position = event.loaded || event.position;
						var total = event.total;
						if (event.lengthComputable) {
							percent = Math.ceil(position / total * 100);
						}
						//Set progress
						status.setProgress(percent);
					}, false);
				}
				return xhrobj;
			},
			url: videouploadURL,
			type: "POST",
			contentType: false,
			processData: false,
			cache: false,
			data: formData,
			success: function (response) {
				execute = true;
				delete filesArray[i];
				//scriptJquery('#submit-wrapper').show();
				if (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF' || ext == 'webp') {
					//response = scriptJquery.parseJSON(response);
				}

				if (response.status) {
					var fileids = document.getElementById('fancyalbumuploadfileidsvideo');
					scriptJquery('#multipleupload').val(1);
					if (ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF' || ext == 'webp') {
						fileids.value = fileids.value + "photo_" + response.photo_id + " ";
						status.src.attr('src', response.src);
						status.option.attr('data-src', response.photo_id);
						status.option.attr('data-type', 'photo');
					} else if ((ext == "mp4" || ext == "flv" || ext == "mov" || ext == "avi" || ext == "m4v" || ext == "wmv" || ext == "webm")) {
						fileids.value = fileids.value + "video_" + response.video_id + " ";
						status.src.attr('src', response.src);
						status.option.attr('data-src', response.video_id);
						status.option.attr('data-type', 'video');
					}
					status.overlay.css('display', 'none');
					status.setProgress(100);
					status.abort.remove();
					if (isMessagePage && scriptJquery('#fancyalbumuploadfileidsvideo').val()) {
						scriptJquery('#submit').show();
						scriptJquery('#videodragandrophandler').hide();
					}
					composeInstance.signalPluginReady(true);
				} else
					status.abort.html('<span>Error In Uploading File</span>');
				executeuploadVideo();
			}
		});
	}

	function readVideoImageUrl(input) {
		videohandleFileUpload(input.files, scriptJquery('#videodragandrophandler'));
	}
	AttachEventListerSE('click', '#videodragandrophandler', function () {
		setTimeout(function () { document.getElementById('video_file_multi').click();; }, 100);
	});

	var isUploadUrl = false;
	AttachEventListerSE('click', '.video_delete_image_upload', function (e) {
		e.preventDefault();
		scriptJquery(this).parent().parent().find('.upload_item_overlay').css('display', 'block');
		var AlbumVideothat = this;
		var video_id = scriptJquery(this).closest('.activity_compose_photo_item_option').attr('data-src');
		var video_type = scriptJquery(this).closest('.activity_compose_photo_item_option').attr('data-type');
		if (video_id) {
			var deleteId = '';
			if (video_type == 'photo') {
				var URLDelete = '<?php echo $this->url(array('module' => 'album', 'controller' => 'index', 'action' => 'remove'), 'default') ?>' + "?photo_id=" + video_id;
				deleteId = 'photo_id';
			} else if (video_type == 'video') {
				var URLDelete = '<?php echo $this->url(array('module' => 'video', 'controller' => 'index', 'action' => 'remove'), 'default') ?>' + "?video_id=" + video_id;
				deleteId = 'video_id';
			}

			request = scriptJquery.ajax({
				'format': 'json',
				method: 'post',
				'url': URLDelete,
				'data': {
					deleteId: video_id
				},
				'success': function (responseJSON) {
					scriptJquery(AlbumVideothat).parent().parent().remove();
					var fileids = document.getElementById('fancyalbumuploadfileidsvideo');
					scriptJquery('#fancyalbumuploadfileidsvideo').val(fileids.value.replace(video_type+'_'+video_id + " ", ''));
					if (scriptJquery('#show_video').html() == '') {
						console.log(scriptJquery('#fancyalbumuploadfileidsvideo').val());
						if (isMessagePage && scriptJquery('#fancyalbumuploadfileidsvideo').val() == '') {
							//scriptJquery('#submit').hide();
							scriptJquery('#videodragandrophandler').show();
						}
						scriptJquery('#show_video_container').removeClass('iscontent');
					}
					return false;
				}
			});
		} else
			return false;
	});
	<?php if (isset($_POST['file']) && $_POST['file'] != '') { ?>
		scriptJquery('#fancyalbumuploadfileidsvideo').val("<?php echo $_POST['file'] ?>");
	<?php } ?>
</script>