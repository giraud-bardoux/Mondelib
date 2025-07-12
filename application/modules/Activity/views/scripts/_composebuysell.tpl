<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2024 Ahead WebSoft Technologies
 * @license    https://socialengine.com/eula
 * @version    $Id: _composebuysell.tpl 2024-10-28 00:00:00Z 
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
$request = Zend_Controller_Front::getInstance()->getRequest();
$requestParams = $request->getParams();

$allowBuySell = false;
$module = $this->subject() ? strtolower($this->subject()->getModuleName()) : "";
try {
	if ($this->subject() && Engine_Api::_()->$module()->allowBuySellInFeed()) {
		$allowBuySell = true;
	}
} catch (Exception $e) {
	// 
}

if ((($requestParams['action'] == 'home' || $requestParams['action'] == 'index') && $requestParams['module'] == 'user' && ($requestParams['controller'] == 'index' || ($requestParams['controller'] == 'profile' && $this->subject() && $this->viewer()->isSelf($this->subject())))) || ($allowBuySell)) { ?>
	<?php $this->headTranslate(array('What are you selling?', 'Add price', 'Add location (optional)', 'Describe your item (optional)', 'Choose a file to upload', 'Upload smaller file.', 'Where to Buy (URL Optional)')); ?>
	<script type="text/javascript">
		<?php
		$fullySupportedCurrencies = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrencies(array('enabled' => 1, 'change_rate' => 1));

		$currentCurrency = Engine_Api::_()->payment()->defaultCurrency();
		$currentData = Engine_Api::_()->getDbTable('currencies', 'payment')->getCurrency($currentCurrency);
 
		if (engine_count($fullySupportedCurrencies) > 1) {

			$currencyData = '<div class="dropdown"><a href="javascript:;" id="currency_btn_currency_sell" class="show_icons" type="button" aria-expanded="false">';
      if(isset($currentData->icon) && !empty($currentData->icon)) {
        $path = Engine_Api::_()->core()->getFileUrl($currentData->icon);
        if($path) {
          $currencyData .= '<i id="currency_icon_sell" class="icon_modal"><img src="'.$path.'" alt="'.$currentCurrency.'" height="18" width="18"></i>';
        }
      } else {
        $currencyData .= '<i id="currency_icon_sell" class="icon_modal" style="display:none;"><img src="" alt="'.$currentCurrency.'" height="18" width="18"></i>';
      }
      $currencyData .= '<span id="currency_text_sell">'.$currentCurrency.'</span></a>';
								
			$currencyData .= '<div class="dropdown-menu"><ul id="currency_change_data_sell">';
        $defaultCurrency = Engine_Api::_()->payment()->defaultCurrency();
        foreach ($fullySupportedCurrencies as $currency) {
        	if($currentCurrency == $currency->code)
            $active ='selected';
          else
            $active ='';
          
          $currencyData .= '<li class="'.$active.'"><a href="javascript:;" class="dropdown-item" data-rel="'.$currency->code.'" title=""'.$currency->title.'">';
            if(isset($currency->icon) && !empty($currency->icon)) {
            $path = Engine_Api::_()->core()->getFileUrl($currency->icon);
              if($path) {
                $currencyData .= '<i class="dropdown_icon"><img src="'.$path.'" alt="img"></i>';
              }
            }
            $currencyData .= '<span>'.$currency->code.'</span></a></li>';
        }
      $currencyData .= '</ul></div></div>';
		} else {
			$currencyData = Engine_Api::_()->payment()->getCurrentCurrency();
		}

		?>
		en4.core.runonce.add(function () {
			composeInstance.addPlugin(new Composer.Plugin.Buysell({
				title: '<?php echo $this->string()->escapeJavascript($this->translate('Sell Something')) ?>',
				currency: '<?php echo $currencyData; ?>',
				currencySymbol: '<?php echo $currentCurrency; ?>',
				photoUpload: <?php echo (int) (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('activityalbum') || Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) ?>,
				lang: {
					'cancel': '<?php echo $this->string()->escapeJavascript($this->translate('cancel')) ?>',
				},
			}));
		});

		//currency change
		AttachEventListerSE('click','ul#currency_change_data_sell li > a',function(e) {
	    scriptJquery('#currency_text_sell').html(scriptJquery(this).attr('data-rel'));
	    scriptJquery('#currency_icon_sell').attr('src', scriptJquery(this).find('img').attr('src'));
			scriptJquery('#buysell-currency').val(scriptJquery(this).attr('data-rel'));
	    if(scriptJquery(this).find('img').length > 0) {
	      scriptJquery('#currency_icon_sell').show();
	      scriptJquery('#currency_icon_sell').find('img').attr('src', scriptJquery(this).find('img').attr('src'));
	    } else {
	      scriptJquery('#currency_icon_sell').hide();
	    }
	  });

		AttachEventListerSE('input propertychange', '#buysell-title, #buysell-title-edit', function () { validateMaxLength(this); });
		function validateMaxLength(obj) {
			var text = scriptJquery(obj).val();
			var maxlength = 100;
			if (text.length > maxlength) {
				scriptJquery(obj).val(text.substr(0, maxlength));
			}
			if (!scriptJquery('#buysell-title-count-edit').length)
				scriptJquery('#buysell-title-count').html(100 - scriptJquery(obj).val().length);
			else
				scriptJquery('#buysell-title-count-edit').html(100 - scriptJquery(obj).val().length);
		}
		AttachEventListerSE('input propertychange', '#buysell-price, #buysell-price-edit', function (e) {
			var val = scriptJquery(this).val();
			val = val.replace(/[^0-9\.]/g, '');
			if (val.split('.').length > 2)
				val = val.replace(/\.+$/, "");
			scriptJquery(this).val(val);
		});

		AttachEventListerSE('click', '.buysellloc_remove_act, .buysellloc_remove_act_edit', function () {
			if (scriptJquery(this).hasClass('buysellloc_remove_act_edit')) {
				var edit = '-edit';
			} else
				var edit = '';
			scriptJquery('#locValuesbuysell-element' + edit).html('');
			scriptJquery('#locValuesbuysell-element' + edit).hide();
			scriptJquery('#buyselllocal' + edit).show();
			scriptJquery('#buysell-location' + edit).val('');
			document.getElementById('activitybuyselllng' + edit).value = '';
			document.getElementById('activitybuyselllat' + edit).value = '';
		});

		en4.core.runonce.add(function () {
			var obj = scriptJquery('#dragandrophandlerbuysell');
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
				handleFileUploadactivitybuysell(files, obj);
			});
			scriptJquery(document).on('dragenter', function (e) {
				e.stopPropagation();
				e.preventDefault();
			});
			scriptJquery(document).on('dragover', function (e) {
				e.stopPropagation();
				e.preventDefault();
			});
			scriptJquery(document).on('drop', function (e) {
				e.stopPropagation();
				e.preventDefault();
			});
		});
		var rowCount = 0;
		AttachEventListerSE('click', 'div[id^="abortPhotobusell_"]', function () {
			var id = scriptJquery(this).attr('id').match(/\d+/)[0];
			if (typeof jqXHRbuysell[id] != 'undefined') {
				jqXHRbuysell[id].abort();
				delete filesArray[id];
				execute = true;
				scriptJquery(this).parent().remove();
				executeuploadactivitybuysell();
			} else {
				delete filesArray[id];
				scriptJquery(this).parent().remove();
			}
		});
		function createStatusbarbuysell(obj, file) {
			rowCount++;
			var row = "odd";
			if (rowCount % 2 == 0) row = "even";
			var checkedId = scriptJquery("input[name=cover]:checked");
			this.objectInsert = scriptJquery('<div class="activity_compose_photo_item ' + row + '"></div>');
			this.overlay = scriptJquery("<div class='overlay activity_compose_photo_item_overlay'></div>").appendTo(this.objectInsert);
			this.abort = scriptJquery('<div class="abort activityalbum_upload_item_abort" id="abortPhotobusell_' + countUploadActivity + '"><span><?php echo $this->translate("Cancel"); ?></span></div>').appendTo(this.objectInsert);
			this.progressBar = scriptJquery('<div class="overlay_image progressBar"><div></div></div>').appendTo(this.objectInsert);
			this.imageContainer = scriptJquery('<div class="activity_compose_photo_item_photo"></div>').appendTo(this.objectInsert);
			this.src = scriptJquery('<img src="' + en4.core.baseUrl + 'application/modules/Core/externals/images/blank-img.gif">').appendTo(this.imageContainer);
			this.infoContainer = scriptJquery('<div class="activity_compose_photo_item_info"></div>').appendTo(this.objectInsert);
			this.size = scriptJquery('<span class="activityalbum_upload_item_size font_color_light"></span>').appendTo(this.infoContainer);
			this.filename = scriptJquery('<span class="activityalbum_upload_item_name"></span>').appendTo(this.infoContainer);
			//this.option = scriptJquery('<div class="activity_compose_photo_item_option"><span class="activityalbum_upload_item_radio"></span><a class="delete_image_upload_buysell" href="javascript:void(0);"><i class="fas fa-times"></i></a></div>').appendTo(this.objectInsert);
			this.option = scriptJquery('<div class="activity_compose_photo_item_option"><span class="activityalbum_upload_item_radio"></span><a class="edit_image_upload_buysell" href="javascript:void(0);"><i class="fa fa-edit"></i></a><a class="delete_image_upload_buysell" href="javascript:void(0);"><i class="fas fa-times"></i></a></div>').appendTo(this.objectInsert);
			var objectAdd = scriptJquery(this.objectInsert).appendTo('#show_photo');
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
			this.setAbort = function (jqXHRbuysell) {
				var sb = this.objectInsert;

				this.abort.click(function () {
					jqXHRbuysell.abort();
					sb.hide();
					executeuploadactivitybuysell();
				});
			}
		}

		var selectedFileLength = 0;
		var statusArray = new Array();
		var filesArray = [];
		var countUploadActivity = 0;
		var fdActivity = new Array();
		function handleFileUploadactivitybuysell(files, obj) {
			selectedFileLength = files.length;
			for (var i = 0; i < files.length; i++) {
				var url = files[i].name;
				var ext = url.substring(url.lastIndexOf('.') + 1).toLowerCase();
				if ((ext == "png" || ext == "jpeg" || ext == "jpg" || ext == 'PNG' || ext == 'JPEG' || ext == 'JPG' || ext == 'gif' || ext == 'GIF' || ext == "webp")) {
					var status = new createStatusbarbuysell(obj, files[i]); //Using this we can set progress.
					status.setFileNameSize(files[i].name, files[i].size);
					statusArray[countUploadActivity] = status;
					filesArray[countUploadActivity] = files[i];
					countUploadActivity++;
				}
			}
			executeuploadactivitybuysell();
		}
		var execute = true;
		function executeuploadactivitybuysell() {
			if (Object.keys(filesArray).length == 0 && scriptJquery('#show_photo').html() != '') {
				scriptJquery('#compose-menu').show();
			}
			if (execute == true) {
				for (var i in filesArray) {
					if (filesArray.hasOwnProperty(i)) {
						sendFileToServerBuySell(filesArray[i], statusArray[i], filesArray[i], 'upload', i);
						break;
					}
				}
			}
		}
		var jqXHRbuysell = new Array();
		function sendFileToServerBuySell(formData, status, file, isURL, i) {
			execute = false;
			var formData = new FormData();
			formData.append('Filedata', file);
			if (isURL == 'upload') {
				var reader = new FileReader();
				reader.onload = function (e) {
					status.src.attr('src', e.target.result);
				}
				reader.readAsDataURL(file);
				var urlIs = '';
			} else {
				status.src.attr('src', file);
				var urlIs = true;
			}
			var type = 'wall';
			if (composeInstance.options.type) type = composeInstance.options.type;
			scriptJquery('#show_photo_container').addClass('iscontent');
			var url = '&isURL=' + urlIs;
			var uploadURL = en4.core.baseUrl + 'activity/album/compose-upload/isactivity/true/type/' + type; //Upload URL
			var extraData = {}; //Extra Data.
			jqXHRbuysell[i] = scriptJquery.ajax({
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
				url: uploadURL,
				type: "POST",
				contentType: false,
				processData: false,
				cache: false,
				data: formData,
				success: function (response) {
					execute = true;
					delete filesArray[i];
					//scriptJquery('#submit-wrapper').show();
					response = scriptJquery.parseJSON(response);
					if (response.status) {
						var fileids = document.getElementById('fancyalbumuploadfileids');
						fileids.value = fileids.value + response.photo_id + " ";
						status.src.attr('src', response.url);
						status.option.attr('data-src', response.photo_id);
						status.overlay.css('display', 'none');
						status.setProgress(100);
						status.abort.remove();
						composeInstance.signalPluginReady(true);
					} else
						status.abort.html('<span>Error In Uploading File</span>');
					executeuploadactivitybuysell();
				}
			});
		}
		function readImageUrlbuysell(input) {
			handleFileUploadactivitybuysell(input.files, scriptJquery('#dragandrophandlerbuysell'));
		}
		AttachEventListerSE('click', '#dragandrophandlerbuysell', function () {
			document.getElementById('file_multi').click();
		});
		var isUploadUrl = false;
		AttachEventListerSE('click', '.edit_image_upload_buysell', function (e) {
			e.preventDefault();
			var photo_id = scriptJquery(this).closest('.activity_compose_photo_item_option').attr('data-src');
			if (photo_id) {
				editImage(photo_id);
			} else
				return false;
		});
		AttachEventListerSE('click', '.delete_image_upload_buysell', function (e) {
			e.preventDefault();
			
			scriptJquery(this).parent().parent().find('.activityalbum_upload_item_overlay').css('display', 'block');
			var activitythat = this;
			var photo_id = scriptJquery(this).closest('.activity_compose_photo_item_option').attr('data-src');
			if (photo_id) {
				request = scriptJquery.ajax({
					'format': 'json',
					'url': '<?php echo $this->url(array('module' => 'activity', 'controller' => 'album', 'action' => 'remove'), 'default') ?>',
					'data': {
						'photo_id': photo_id
					},
					'success': function (responseJSON) {
						scriptJquery(activitythat).parent().parent().remove();
						var fileids = document.getElementById('fancyalbumuploadfileids');
						scriptJquery('#fancyalbumuploadfileids').val(fileids.value.replace(photo_id + " ", ''));
						if (scriptJquery('#show_photo').html() == '') {
							scriptJquery('#show_photo_container').removeClass('iscontent');
						}
						return false;
					}
				});
			} else
				return false;
		});
		<?php if (isset($_POST['file']) && $_POST['file'] != '') { ?>
			scriptJquery('#fancyalbumuploadfileids').val("<?php echo $_POST['file'] ?>");
		<?php } ?>
		function editImage(photo_id) {
			var url = '<?php echo $this->url(array('module' => 'activity', 'controller' => 'album', 'action' => 'edit-photo'), 'default') ?>' + '/photo_id/' + photo_id;
			Smoothbox.open(url);
		}
	</script>
<?php } ?>