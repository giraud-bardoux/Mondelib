
Uploader = class {
  uploadedFileArray = [];
  options = {
    uploadLinkClass : '',
    uploadLinkTitle : '',
    uploadLinkDesc : '',
		removePhotoURL: '',
  };

  constructor(uploadElement, options) {
    this.uploadElement = scriptJquery(uploadElement);
    scriptJquery.extend(this.options,options);
    this.attachUploadEvent();
  }

  attachUploadEvent() {
    var self = this;
    var FileSize;
    var valid = true;
    var fileStatusElement = scriptJquery.crtEle('div', {
    }).html(self.options.uploadLinkDesc).prependTo(scriptJquery('#file-status'));

    scriptJquery.crtEle('a', {
       id: "upload_file_link",
       class: self.options.uploadLinkClass,
       href:"#"
    }).html(self.options.uploadLinkTitle).click(function(e) {
      e.preventDefault();
      self.uploadElement.trigger("click");
    }).appendTo(scriptJquery('#file-status'));

    this.uploadElement.on('change', function () {
      var files = scriptJquery(this)[0].files;
      var total = files.length;
      //Maximum photo upload
      if(total > max_photo_upload_limit) {
        alert(photo_upload_text);
        return;
      }
      var iteration = 0;
      for(var i = 0; i < files.length; i++) {
        FileSize = files[i].size / 1024 / 1024; // in MB
        if(FileSize > post_max_size) {
          valid = false;
          continue;
        }
        iteration++;
        self.uploadFile(self.uploadElement,files[i], iteration, total);
      }
      //if(!valid) alert("The size of the file exceeds the limits set on the server.");
    });
    this.uploadElement.on('click', function () {
      scriptJquery(this).val('');
    });
    scriptJquery('#remove_all_files').on('click', function () {
      scriptJquery('.file-remove').each(function (el) {
        self.removeFile(scriptJquery(this));
      });
      scriptJquery('.file-error').each(function (el) {
        scriptJquery(this).remove();
      });
      scriptJquery('remove_all_files').hide();
      scriptJquery('uploaded-file-list').hide();
    });
  }

  uploadFile(obj, file, iteration, total) {
    var self = this;

    //Upload file size check
    var FileSize = file.size / 1024 / 1024; // in MB
    if(FileSize > post_max_size) {
      return;
    }

    //Check image
		if(obj.attr('accept').split('/')[0] == 'image') {
			if(file.type.split('/')[0] != 'image') {
				return self.processUploadError(file['name'] + ' is not an image.');
			}
		}

    if (this.alreadyUploaded(file)) {
      return self.processUploadError(file['name'] + ' already added.');
    }

    var url = obj.attr('data-url');
    if (url === '') {
      return;
    }

    var xhr = new XMLHttpRequest();
    var fd = new FormData();
    xhr.open("POST", url, true);
    document.getElementById('files-status-overall').style.display = 'block';
    if( document.getElementById('submit-wrapper'))
    document.getElementById('submit-wrapper').style.display = 'none';
    xhr.upload.addEventListener('progress', function (e) {
      var per = (total <= 1 ? e.loaded/e.total : iteration/total).toFixed(2) * 100;
      var overAllFileProgress = -400 + ((per) * 2.5);
      scriptJquery('div#files-status-overall img').css('background-position', overAllFileProgress + 'px 0px');
      scriptJquery('div#files-status-overall span').html(per + '%');
    }, false);

    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        try {
          // Show progress
          var res = JSON.parse(xhr.responseText);
        } catch (err) {
          self.processUploadError('An error occurred.');
          return false;
        }

        if (res['error'] !== undefined || res['error'] == false) {
          self.processUploadError('FAILED (' + res['name'] + ') : ' + res['error']);
          return false;
        }
        self.processResponseData(res);
        if (typeof self.processCustomResponse !== "undefined") {
          self.processCustomResponse(res);
        }

        if (iteration === total) {
          self.showButton();
        }
      }
    };
    fd.append('ajax-upload', 'true');
    fd.append(obj.attr('name'), file);
    xhr.send(fd);
  }

  alreadyUploaded(file) {
    if (this.uploadedFileArray.length === 0) {
      return false;
    }
    return false;
    return this.uploadedFileArray.every(function (uploadedFile) {
      return uploadedFile === file.name;
    });
  }

  processResponseData(response) {
    var self = this;
    var fancyUploadFileds = scriptJquery('#fancyuploadfileids');
    var currentValue = fancyUploadFileds.attr('value');
    currentValue += response['id'] + ' ';

    fancyUploadFileds.val(currentValue);
    this.uploadedFileArray[response['id']] = response['fileName'];
    var uploadedFileList = scriptJquery("#uploaded-file-list");
    var uploadedFile = scriptJquery.crtEle('li', {
      'class': 'file file-success',
    }).appendTo(uploadedFileList);

    var fileLink = scriptJquery.crtEle('a', {
       class: "file-remove",
       href: 'javascript:void(0);',
       title: 'Click to remove this entry.',
       'data-file_id': response['id'],
    }).html('<b>Remove</b>').click(function() {
          self.removeFile(scriptJquery(this));
      }).appendTo(uploadedFile);

    scriptJquery.crtEle('span', {
      class: 'file-name',
    }).html(response['fileName']).appendTo(uploadedFile);

    // If hidden show upload list
    if (uploadedFileList.is(":hidden")) {
      uploadedFileList.show();
    }
    if (scriptJquery('#remove_all_files').is(":hidden")) {
      scriptJquery('#remove_all_files').css('display', 'inline');
    }
  }

  processUploadError(errorMessage) {
    var uploadedFileList = scriptJquery("uploaded-file-list");
    var uploadedFile = scriptJquery.crtEle('li', {
      'class': 'file file-error',
    }).html('<span class="validation-error" title="Click to remove this entry.">' + errorMessage + '</span>').click(
      function() {
          scriptJquery(this).remove();
          if (scriptJquery('ul#uploaded-file-list li').length === 0) {
            scriptJquery('#submit-wrapper').css('display', 'none');
            scriptJquery('#remove_all_files').css('display', 'none');
            scriptJquery('#uploaded-file-list').css('display', 'none');
          }
      }
    ).prependTo(uploadedFileList);
    // If hidden show upload list
    if (uploadedFile.is(":hidden")) {
      uploadedFileList.show();
    }
    scriptJquery('#files-status-overall').css('display', 'none');
    return false;
  }

  showButton() {
    scriptJquery("#submit-wrapper").show();
    scriptJquery('#files-status-overall').css('display', 'none');
  }

  removeFile(el) {
    var file_id = el.attr('data-file_id');
		
		if(this.options.removePhotoURL) {
			scriptJquery.ajax({
				dataType: 'json',
				'url' : this.options.removePhotoURL,
				'data': {
					'photo_id' : file_id
				},
				success : function(responseJSON) {
				}
			});
		}
		
    delete this.uploadedFileArray[file_id];
    var fancyUploadFileds = scriptJquery('#fancyuploadfileids');
    var currentValue = fancyUploadFileds.val();
    currentValue = currentValue.replace(file_id + ' ', '');

    if (typeof deleteFile !== "undefined") {
      deleteFile(el);
    }
    fancyUploadFileds.val(currentValue);
    el.parent().remove();
    if (scriptJquery('ul#uploaded-file-list li').length === 0) {
      scriptJquery('#remove_all_files').css('display', 'none');
      scriptJquery('#uploaded-file-list').css('display', 'none');
      document.getElementById("submit-wrapper").style.display = "none";
    }
  }
};
