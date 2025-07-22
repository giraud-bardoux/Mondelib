/* $Id: composer_photo.js 10262 2014-06-05 19:47:29Z lucas $ */

(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;

Composer.Plugin.Photo = function(options){

  this.__proto__ = new Composer.Plugin.Interface(options)

  this.name = 'photo';

  this.options = {
    title : 'Add Photo',
    lang : {},
    requestOptions : false,
    fancyUploadEnabled : true,
    fancyUploadOptions : {}
  }

  this.allowToSetInInput = true;

  this.initialize = function(options) {
    this.elements = new Hash(this.elements);
    this.params = new Hash(this.params);
    this.__proto__.initialize.call(this,scriptJquery.extend(options,this.__proto__.options));
    this.uploadedPhotos = [];
  }

  this.attach = function() {
    this.__proto__.attach.call(this);
    this.makeActivator();
    return this;
  }

  this.detach = function() {
    this.__proto__.detach.call(this);
    return this;
  }

  this.activate = function() {
    if (this.active) {
      return;
    }

    this.__proto__.activate.call(this);
    this.makeMenu();
    this.makeBody();

    var fullUrl = this.options.requestOptions.url;
    var hasFlash = false;

    this.elements.formFancyContainer = scriptJquery.crtEle('div', {
    })
    .css({
        'display' : 'none',
        'visibility' : 'hidden'
    })
    .appendTo(this.elements.body);

    this.elements.scrollContainer = scriptJquery.crtEle('div', {
      'class': 'scrollbars',
    })
    .css({
        'width' : this.elements.body.width() + 'px',
    })
    .appendTo(this.elements.formFancyContainer);

    // This is the list
    this.elements.formFancyList = scriptJquery.crtEle('ul', {
      'class': 'compose-photos-fancy-list',
    }).appendTo(this.elements.scrollContainer);

    // This is the browse button
    this.elements.formFancyFile = scriptJquery.crtEle('div', {
      'id' : 'compose-photo-form-fancy-file',
      'class' : '',
    }).appendTo(this.elements.scrollContainer);

    this.elements.selectFileLink = scriptJquery.crtEle('a', {
      'class' : 'buttonlink',
    })
    .html(this._lang('Select File'))
    .css({
        'cursor' : 'pointer'
    })
    .appendTo(this.elements.formFancyFile);

    // this.elements.scrollContainer.scrollbars({
    //   scrollBarSize: 5,
    //   fade: true
    // });

    // Ajax Upload Work
    this.elements.formInput = scriptJquery.crtEle('input', {
      'id' : 'compose-photo-form-input',
      'class' : 'compose-form-input',
      'type' : 'file',
      'multiple': this.options.fancyUploadOptions.limitFiles != 1,
      'value': '',
      'accepts': 'images/*',
    })
    .change(this.onFileSelectAfter.bind(this))
    .appendTo(this.elements.scrollContainer);

    bindEvent = window.matchMedia("(min-width: 400px)").matches ? 'click' : 'touchend';
    this.elements.selectFileLink.on(bindEvent, this.onSelectFileClick.bind(this));
    this.showForm();
    if (en4.isMobile) {
      this.elements.scrollContainer.find("ul.scrollbar.vertical").addClass('inactive');
      this.elements.scrollContainer.find("ul.scrollbar.horizontal").addClass('inactive');
    }
  }

  this.onSelectFileClick = function () {
    this.elements.formInput.click();
  }

  this.onFileSelectAfter = function() {
    this.elements.formFancyList.css("display",'inline-block');
    this.getComposer().getMenu().css('display', 'none');
    if (this.elements.formInput[0].files.length === 0) {
      return;
    }
    //Maximum photo upload
    if(this.elements.formInput[0].files.length > max_photo_upload_limit) {
      alert(photo_upload_text);
      return;
    }
    var FileSize;
    var valid = true;
    this.elements.fileElement = [];
    this.elements.filePreview = [];
    this.elements.fileRemoveLink = [];
    for (var i = 0; i < this.elements.formInput[0].files.length; i++) {
      FileSize = this.elements.formInput[0].files[i].size / 1024 / 1024; // in MB
      if(FileSize > post_max_size) {
        valid = false;
        continue;
      }

      if (!this.canUploadPhoto(this.elements.formInput[0].files[i])) {
        this.getComposer().getMenu().css('display', '');
        continue;
      }
      this.elements.fileElement[i] = scriptJquery.crtEle('li', {
          'class' : 'file compose-photo-preview',
        }).appendTo(this.elements.formFancyList);

      this.elements.filePreview[i] = scriptJquery.crtEle('span', {
        'class' : 'compose-photo-preview-image compose-photo-preview-loading',
      }).appendTo(this.elements.fileElement[i]);

      var overlay = scriptJquery.crtEle('span', {
        'class' : 'compose-photo-preview-overlay',
      }).appendTo(this.elements.filePreview[i], 'after');

      this.elements.fileRemoveLink[i] = scriptJquery.crtEle('a', {
        'class': 'file-remove',
         title: 'Click to remove this entry.',
      })
      .html('X')
      .appendTo(overlay);
      this.uploadFile(this.elements.formInput[0].files[i], i);

      if (this.canUploadPhoto(null) !== true) {
        this.elements.formFancyFile.css('display', 'none');
        break;
      }
    }

    if(!valid) alert("The size of the file exceeds the limits set on the server.");

    this.elements.formInput.val('');
    this.updateScrollBar();
    var scrollbarContent = this.elements.formFancyList.closest('.scrollbars');
    scrollbarContent.scrollLeft(this.elements.formFancyFile.offset().left, scrollbarContent.scrollTop());
    scrollbarContent.scrollTop(scrollbarContent.scrollTop());
  }

  this.uploadFile = function (file, iteration) {
    var xhr = new XMLHttpRequest();
    var fd = new FormData();
    xhr.open("POST", this.options.requestOptions.url, true);
    var composerInstance = this;
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        var res = JSON.parse(xhr.responseText);
        if (res['error'] !== undefined) {
          //return false;
        }else{
            res['iteration'] = iteration;
            composerInstance.uploadedPhotos[res.photo_id] = res.fileName;
        }
        composerInstance.doProcessResponse(res);
      }
    };
    fd.append('ajax-upload', 'true');
    fd.append('Filedata', file);
    xhr.send(fd);
  }

  this.removeFile = function(photo_id) {
    var composerInstance = this;
    scriptJquery('#file_remove-' + photo_id).parent().parent().parent().remove();
		if(photo_id) {
			scriptJquery.ajax({
				dataType: 'json',
				'url' : this.options.requestOptions.removePhotoURL,
				'data': {
					'photo_id' : photo_id
				},
				success : function(responseJSON) {
				}
			});
		}
    composerInstance.removePhoto(photo_id);
    delete composerInstance.uploadedPhotos[photo_id];
    if (this.canUploadPhoto(null)) {
      this.elements.formFancyFile.css('display', '');
    }
    setTimeout(function() {composerInstance.updateScrollBar();},1000);
  }

  this.showForm = function () {
    this.elements.formFancyContainer.css('display', '');
    this.elements.formInput.css('display', 'none');
    this.elements.formFancyContainer.css('visibility', 'visible');
  }

  this.canUploadPhoto = function (photo) {
    if (photo === null) {
      return this.options.fancyUploadOptions.limitFiles === 0 ||
        scriptJquery('ul.compose-photos-fancy-list li').length < this.options.fancyUploadOptions.limitFiles;
    }
    if (this.uploadedPhotos.length === 0) {
      return true;
    }
    return this.uploadedPhotos.every(function (uploadedPhoto) {
        return uploadedPhoto !== photo.name;
    });
  }

  this.updateScrollBar = function () {
    var height = this.elements.formFancyFile.offsetHeight;
    if( height == 0 ) {
      height = 106;
    }
    var scrollbarContent = this.elements.formFancyList.parent();
    scrollbarContent.css('height', height + 20);
    var li = this.elements.formFancyList.find('li');
    scrollbarContent.css('width', ((li.width() + 11) * li.length) + this.elements.formFancyFile.height() + 10);
    //this.elements.scrollContainer.data('scrollbars').updateScrollBars();
    scrollbarContent.parent().css('overflow', 'hidden');
  }

  this.deactivate = function() {
    if( !this.active ) return;
    this.uploadedPhotos = [];
    this.__proto__.deactivate.call(this);
  }

  this.doProcessResponse = function(responseJSON, file) {
	  if( typeof responseJSON == 'object' && typeof responseJSON.error != 'undefined' ) {
		  if( this.elements.loading ) {
			  this.elements.loading.remove();
		  }
		  this.elements.body.empty();
		  return this.makeError(responseJSON.error, 'empty');
	  }

    // An error occurred
    if( ($type(responseJSON) != 'hash' && $type(responseJSON) != 'object') || $type(responseJSON.src) != 'string' || $type(parseInt(responseJSON.photo_id)) != 'number' ) {
      this.elements.loading ? this.elements.loading.remove() : '';
      this.elements.body.empty();
      if( responseJSON.error == 'Invalid data' ) {
        this.makeError(this._lang('The image you tried to upload exceeds the maximum file size.'), 'empty');
      } else {
        this.makeError(this._lang(responseJSON.error), 'empty');
      }
      return;
      //throw "unable to upload image";
    }

    // Success
    if (file) {
      file = file || {};
      file.rawParams = responseJSON;
      this.setPhotoId(responseJSON.photo_id);
      this.elements.preview = scriptJquery('img', {
        'id' : 'compose-photo-preview-image',
        'class' : 'compose-preview-image',
        'src' : responseJSON.src,
      }).load(function() {
          this.doImageLoaded(file);
    }.bind(this));
      return;
    }
    // In case of ajax upload
    this.elements.filePreview[responseJSON['iteration']].removeClass('compose-photo-preview-loading');
    this.elements.filePreview[responseJSON['iteration']].css(
      'backgroundImage',
      'url(' + responseJSON.src + ')'
    );
    this.elements.fileRemoveLink[responseJSON['iteration']].attr('id', 'file_remove-' + responseJSON.photo_id);
    this.elements.fileRemoveLink[responseJSON['iteration']].on('click',this.removeFile.bind(this, responseJSON.photo_id));
    
    this.setPhotoId(responseJSON.photo_id);
    this.makeFormInputs();
  }

  this.doImageLoaded = function(file) {
    //compose-photo-error
    if(scriptJquery('#compose-photo-error').length){
      scriptJquery('#compose-photo-error').remove();
    }

    if( this.elements.loading ) this.elements.loading.remove();
    if( this.elements.formFancyContainer ) {
      file.preview.removeClass('compose-photo-preview-loading');
      file.preview.css('backgroundImage', 'url(' + this.elements.preview.src + ')' );
    } else {
      this.elements.preview.attr('width','');
      this.elements.preview.attr('height','');
      this.elements.preview.appendTo(this.elements.body);
    }
    if(this.allowToSetInInput) {
      this.makeFormInputs();
    }
  }

  this.removePhoto = function(removePhotoId) {
    this.setPhotoId(removePhotoId, 'remove');
    var photo_id = this.setPhotoId(removePhotoId, 'remove');
    const index = photo_id.indexOf(removePhotoId);
    if (index > -1) {
      photo_id.splice(index, 1);
    }
    if (photo_id.length === 0) {
      this.getComposer().deactivate();
      this.activate();
      return;
    }
    this.makeFormInputs();
  }

  this.setPhotoId = function (photoId, action) {
    var photo_id =  this.params.get('photo_id') || [];
    if (action === 'remove') {
      var index = photo_id.indexOf(photoId);
      if (index > -1) {
        photo_id.splice(index, 1);
      }
    } else {
      photo_id.push(photoId);
    }
    this.params.set('photo_id', photo_id);
    return photo_id;
  }

  this.makeFormInputs = function() {
    this.ready();
    if( this.elements.has('attachmentFormPhoto_id') ) {
      return this.setFormInputValue('photo_id', this.getPhotoIdsString());
    }

    this.__proto__.makeFormInputs.call(this,{
      'photo_id': this.getPhotoIdsString()
    });
  }
  this.getPhotoIdsString = function() {
    var photo_id_str = '';
    this.params.photo_id.forEach(function(value) {
      photo_id_str += value + ',';
    });
    return photo_id_str.substr(0, photo_id_str.length-1);
  }
  this.initialize(options);
};

})(); // END NAMESPACE
