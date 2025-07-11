
/* $Id: composer_link.js 10090 2013-09-27 03:38:28Z ivan $ */



(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;



Composer.Plugin.Link = function(options){

  this.__proto__ = new Composer.Plugin.Interface(options);

  this.name = 'link';

  this.options = {
    title : 'Add Link',
    lang : {},
    // Options for the link preview request
    requestOptions : {},
    // Various image filtering options
    imageMaxAspect : ( 10 / 3 ),
    imageMinAspect : ( 3 / 10 ),
    imageMinSize : 48,
    imageMaxSize : 5000,
    imageMinPixels : 2304,
    imageMaxPixels : 1000000,
    imageTimeout : 5000,
    // Delay to detect links in input
    monitorDelay : 600,
    debug : false
  }

  this.initialize = function(options) {
    this.params = new Hash(this.params);
    this.__proto__.initialize.call(this,scriptJquery.extend(options,this.__proto__.options));
  }

  this.attach = function() {
    this.__proto__.attach.call(this);
    this.makeActivator();

    // Poll for links
    //this.interval = (function() {
    //  this.poll();
    //}).periodical(250, this);
    this.monitorLastContent = '';
    this.monitorLastMatch = '';
    this.monitorLastKeyPress = $time();
    // this.getComposer().addEvent('editorKeyPress', function() {
    //   this.monitorLastKeyPress = $time();
    // }.bind(this));
    return this;
  }

  this.detach = function() {
    this.__proto__.detach.call(this);
    if( this.interval ) $clear(this.interval);
    return this;
  }

  this.activate = function() {
    if( this.active ) return;
    this.__proto__.activate.call(this);

    this.makeMenu();
    this.makeBody();
    
    // Generate body contents
    // Generate form
    this.elements.formInput = scriptJquery.crtEle('input', {
      'id' : 'compose-link-form-input',
      'class' : 'compose-form-input',
      'type' : 'text'
    }).appendTo(this.elements.body);

    this.elements.formSubmit = scriptJquery.crtEle('button', {
      'id' : 'compose-link-form-submit',
      'class' : 'compose-form-submit',
    }).html(this._lang('Attach')).appendTo(this.elements.body).click(function(e) {
      e.preventDefault();
      this.doAttach();
    }.bind(this));
    this.elements.formInput.focus();
  }

  this.deactivate = function() {
    if( !this.active ) return;
    this.__proto__.deactivate.call(this);
    
    this.request = false;
  }

  this.poll = function() {
    // Active plugin, ignore
    if( this.getComposer().hasActivePlugin() ) return;
    // Recent key press, ignore
    if( $time() < this.monitorLastKeyPress + this.options.monitorDelay ) return;
    // Get content and look for links
    var content = this.getComposer().getContent();
    // Same as last body
    if( content == this.monitorLastContent ) return;
    this.monitorLastContent = content;
    // Check for match
    var m = content.match(/http:\/\/([-\w\.]+)+(:\d+)?(\/([-#:\w/_\.]*(\?\S+)?)?)?/);
    if( $type(m) && $type(m[0]) && this.monitorLastMatch != m[0] )
    {
      this.monitorLastMatch = m[0];
      this.activate();
      this.elements.formInput.value = this.monitorLastMatch;
      this.doAttach();
    }
  }



  // Getting into the core stuff now

  this.doAttach = function() {
    var val = this.elements.formInput.val();
    if( !val ) {
      return;
    }
    if( !val.match(/^[a-zA-Z]{1,5}:\/\//) )
    {
      val = 'http://' + val;
    }
    this.params.set('uri', val)
    // Input is empty, ignore attachment
    if( val == '' ) {
      return;
    }

    // Send request to get attachment
    var options = scriptJquery.extend({
      'dataType': 'json',
      'method': 'post',
      'data' : {
        'format' : 'json',
        'uri' : val
      },
      'success' : this.doProcessResponse.bind(this)
    }, this.options.requestOptions);

    // Inject loading
    this.makeLoading('empty');

    // Send request
    scriptJquery.ajax(options);;
  }

  this.doProcessResponse = function(responseJSON, responseText) {
    // Handle error
    if( $type(responseJSON) != 'object' ) {
      responseJSON = {
        'status' : false
      };
    }
    this.params.set('uri', responseJSON.url);

    // If google docs then just output Google Document for title and descripton
    var uristr = responseJSON.url;
    if (uristr.substr(0, 23) == 'https://docs.google.com') {
      var title = uristr;
      var description = 'Google Document';
    } else {
      var title = responseJSON.title || responseJSON.url;
      var description = responseJSON.description || responseJSON.title || responseJSON.url;
    }
    title = title.substr(0, 64);
    description = description.substr(0, 256);
    var images = responseJSON.images || [];
    var richHtml = responseJSON.richHtml || '';
    this.params.set('richHtml', richHtml);
    this.params.set('title', title);
    this.params.set('description', description);
    this.params.set('images', images);
    this.params.set('loadedImages', []);
    this.params.set('thumb', '');

    if( images.length > 0 && !richHtml) {
      this.doLoadImages();
    } else {
      this.doShowPreview();
    }
  }

  // Image loading
  this.doLoadImages = function() {
    // Start image load timeout
    var interval = setTimeout(function() {
      // Debugging
      if( this.options.debug ) {
        console.log('Timeout reached');
      }
      //this.doShowPreview();
    }.bind(this),this.options.imageTimeout);

    // Load them images
    let imgs = []; 
    this.params.get('images').forEach(function(imgSrc){
//       let img = scriptJquery.crtEle('img',{
//         'src': imgSrc,      
//         'class' : 'compose-link-image'
//       });
      //imgs.push('<img src="'+imgSrc+'" class="compose-link-image ">');
      imgs.push(imgSrc);
    });
  
    this.params.set('assets',imgs);
    this.doShowPreview();
  }


  // Preview generation
  this.doShowPreview = function() {
    var self = this;
    this.elements.body.empty();
    this.makeFormInputs();

    // Generate image thingy
    //var tmp = new Array();
    this.elements.previewImages = scriptJquery.crtEle('div', {
      'id' : 'compose-link-preview-images',
      'class' : 'compose-preview-images'
    }).appendTo(this.elements.body);

    
    if(typeof this.params.assets != 'undefined') {
      Object.entries(this.params.assets).forEach(function([index,element]) {
        
        let img = scriptJquery.crtEle('img',{
          'src': element,      
          'class' : 'compose-link-image',
					'data-index': parseInt(index) + 1,
        }).appendTo(this.elements.previewImages);
          //element.appendTo(this.elements.previewImages);
        if(!this.checkImageValid(element) ) {
          scriptJquery(img).remove();
        } else {
					if(index != 0)
          scriptJquery(img).removeClass('compose-preview-image-invisible').addClass('compose-preview-image-hidden');
        }
      }.bind(this));
			
      if(Object.entries(this.params.assets).length <= 0 ) {
        this.elements.previewImages.remove();
      }
    }

    if (this.params.richHtml) {
      this.elements.previewHtml = scriptJquery.crtEle('div', {
        'id': 'compose-link-preview-html',
        'class': 'compose-preview-link-html',
      }).html(this.params.richHtml).appendTo(this.elements.body);
      var arr = this.elements.previewHtml.find('script');
      for (var n = 0; n < arr.length; n++) {
        if (arr.eq(n).attr('src')) {
          var scriptTag = scriptJquery("script");
          scriptTag.attr('src',arr.eq(n).attr('src'));
          scriptTag.appendTo(this.elements.body);
        }
      }
    }

    this.elements.previewInfo = scriptJquery.crtEle('div', {
      'id' : 'compose-link-preview-info',
      'class' : 'compose-preview-info'
    }).appendTo(this.elements.body);

    // Generate title and description
    this.elements.previewTitle = scriptJquery.crtEle('div', {
      'id' : 'compose-link-preview-title',
      'class' : 'compose-preview-title'
    }).appendTo(this.elements.previewInfo);

    this.elements.previewTitleLink = scriptJquery.crtEle('a', {
      'href' : this.params.uri,
    }).html(this.params.title).appendTo(this.elements.previewTitle).click(function(e) {
      e.preventDefault();
      self.handleEditTitle(this); 
    });

    this.elements.previewDescription = scriptJquery.crtEle('div', {
      'id' : 'compose-link-preview-description',
      'class' : 'compose-preview-description',
    }).html(this.params.description).appendTo(this.elements.previewInfo).click(function(e) {
      e.preventDefault();
      self.handleEditDescription(this);
    });
    
    // Generate image selector thingy
    if(typeof this.params.assets != 'undefined' && this.params.assets.length > 0 ) {
      this.elements.previewOptions = scriptJquery.crtEle('div', {
        'id' : 'compose-link-preview-options',
        'class' : 'compose-preview-options'
      }).appendTo(this.elements.previewInfo);

      if( this.params.assets.length > 1 ) {
        this.elements.previewChoose = scriptJquery.crtEle('div', {
          'id' : 'compose-link-preview-options-choose',
          'class' : 'compose-preview-options-choose'
        }).html('<span>' + this._lang('Choose Image:') + '</span>').appendTo(this.elements.previewOptions);

        this.elements.previewPrevious = scriptJquery.crtEle('a', {
          'id' : 'compose-link-preview-options-previous',
          'class' : 'compose-preview-options-previous',
          'href' : 'javascript:void(0);',
        }).html('&#171; ' + this._lang('Previous')).appendTo(this.elements.previewChoose).click(function(e) {
					e.preventDefault();
					self.doSelectImagePrevious(this);
				});

        this.elements.previewCount = scriptJquery.crtEle('span', {
          'id' : 'compose-link-preview-options-count',
          'class' : 'compose-preview-options-count'
        }).appendTo(this.elements.previewChoose);
				this.elements.previewCount.html(' | ' + (1) + ' of ' + this.params.assets.length + ' | ');


        this.elements.previewPrevious = scriptJquery.crtEle('a', {
          'id' : 'compose-link-preview-options-next',
          'class' : 'compose-preview-options-next',
          'href' : 'javascript:void(0);',
        }).html(this._lang('Next') + ' &#187;').appendTo(this.elements.previewChoose).click(function(e) {
					e.preventDefault();
					self.doSelectImageNext(this);
				});
      }

      this.elements.previewNoImage = scriptJquery.crtEle('div', {
        'id' : 'compose-link-preview-options-none',
        'class' : 'compose-preview-options-none'
      }).appendTo(this.elements.previewOptions);

      this.elements.previewNoImageInput = scriptJquery.crtEle('input', {
        'id' : 'compose-link-preview-options-none-input',
        'class' : 'compose-preview-options-none-input',
        'type' : 'checkbox',
      }).appendTo(this.elements.previewNoImage).click(function(e) {
				
				self.doToggleNoImage(this);
			});

      this.elements.previewNoImageLabel = scriptJquery.crtEle('label', {
        'for' : 'compose-link-preview-options-none-input',
        //'html' : this._lang('Don\'t show an image'),
        // 'events' : {
        //   //'click' : this.doToggleNoImage.bind(this)
        // }
      }).html(this._lang('Don\'t show an image')).appendTo(this.elements.previewNoImage);

      this.setFormInputValue('thumb', this.elements.previewImages.find('img').attr('src'));
      // Show first image
      //this.setImageThumb(this.elements.previewImages.getChildren()[0]);
    }
  }

  this.checkImageValid = function(element) {

    //var size = element.getSize(); 
    var sizeAlt = {x:element.width,y:element.height};
    
    var width = sizeAlt.x;
    var height = sizeAlt.y;
    var pixels = width * height;
    var aspect = width / height;
    // Debugging
    if( this.options.debug ) {
      console.log(element.attr('src'), sizeAlt, size, width, height, pixels, aspect);
    }
    
    if(aspect == 1) {
      return true;
    }

    // Check aspect
    if( aspect > this.options.imageMaxAspect ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Aspect greater than max - ', element.attr('src'), aspect, this.options.imageMaxAspect);
      }
      return false;
    } else if( aspect < this.options.imageMinAspect ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Aspect less than min - ', element.attr('src'), aspect, this.options.imageMinAspect);
      }
      return false;
    }
    // Check min size
    if( width < this.options.imageMinSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Width less than min - ', element.attr('src'), width, this.options.imageMinSize);
      }
      return false;
    } else if( height < this.options.imageMinSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Height less than min - ', element.attr('src'), height, this.options.imageMinSize);
      }
      return false;
    }
    // Check max size
    if( width > this.options.imageMaxSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Width greater than max - ', element.attr('src'), width, this.options.imageMaxSize);
      }
      return false;
    } else if( height > this.options.imageMaxSize ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Height greater than max - ', element.attr('src'), height, this.options.imageMaxSize);
      }
      return false;
    }
    // Check  pixels
    if( pixels < this.options.imageMinPixels ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Pixel count less than min - ', element.attr('src'), pixels, this.options.imageMinPixels);
      }
      return false;
    } else if( pixels > this.options.imageMaxPixels ) {
      // Debugging
      if( this.options.debug ) {
        console.log('Pixel count greater than max - ', element.attr('src'), pixels, this.options.imageMaxPixels);
      }
      return false;
    }

    return true;
  }

  this.doSelectImagePrevious = function(obj) {
    var total = scriptJquery('.compose-preview-images').children();
		var thumbImg = scriptJquery('.compose-preview-images').children(':not(.compose-preview-image-hidden)');
		var thumbImgDataIndex = thumbImg.attr('data-index');
		thumbImgDataIndex = parseInt(thumbImgDataIndex);
    if( thumbImg.length > 0 && thumbImgDataIndex != 1 ) {
			thumbImg.addClass('compose-preview-image-hidden');
			scriptJquery('.compose-preview-images').children().eq(thumbImgDataIndex - 2).removeClass('compose-preview-image-hidden');
			this.setFormInputValue('thumb', scriptJquery('.compose-preview-images').children().eq(thumbImgDataIndex - 2).attr('src'));
			this.elements.previewCount.html(' | ' + (thumbImgDataIndex - 1) + ' of ' + total.length + ' | ');
    }
  }

  this.doSelectImageNext = function(obj) {
		var total = scriptJquery('.compose-preview-images').children();
		var thumbImg = scriptJquery('.compose-preview-images').children(':not(.compose-preview-image-hidden)');
		var thumbImgDataIndex = thumbImg.attr('data-index');
		thumbImgDataIndex = parseInt(thumbImgDataIndex);
    if( thumbImg.length > 0 && thumbImgDataIndex < total.length ) {
			thumbImg.addClass('compose-preview-image-hidden');
			scriptJquery('.compose-preview-images').children().eq(thumbImgDataIndex).removeClass('compose-preview-image-hidden');
			this.setFormInputValue('thumb', scriptJquery('.compose-preview-images').children().eq(thumbImgDataIndex).attr('src'));
			this.elements.previewCount.html(' | ' + (thumbImgDataIndex + 1) + ' of ' + total.length + ' | ');
    }
  }

  this.doToggleNoImage = function() {
    if( !$type(this.params.thumb) ) {
      this.params.thumb = scriptJquery(this.elements.previewImages).find("img").attr("src");
      this.setFormInputValue('thumb', this.params.thumb);
      this.elements.previewImages.css('display', '');
      if( this.elements.previewChoose )
       this.elements.previewChoose.css('display', '');
    } else {
      delete this.params.thumb;
      this.setFormInputValue('thumb', '');
      this.elements.previewImages.css('display', 'none');
      if( this.elements.previewChoose ) this.elements.previewChoose.css('display', 'none');
    }
  }

  this.setImageThumb = function(element) {
    // Hide old thumb
    if( this.elements.imageThumb ) {
      this.elements.imageThumb.addClass('compose-preview-image-hidden');
    }
    if( element ) {
      element.removeClass('compose-preview-image-hidden');
      this.elements.imageThumb = element;
      this.params.thumb = element.src;
      this.setFormInputValue('thumb', element.src);
      if( this.elements.previewCount ) {
        var index = this.params.loadedImages.indexOf(element.src);
        //this.elements.previewCount.set('html', ' | ' + (index + 1) + ' of ' + this.params.loadedImages.length + ' | ');
	     if ( index < 0 ) { index = 0; }
        this.elements.previewCount.html(' | ' + this._lang('%d of %d', index + 1, this.params.loadedImages.length) + ' | ');
      }
    } else {
      this.elements.imageThumb = false;
      delete this.params.thumb;
    }
  }

  this.makeFormInputs = function() {

    this.ready();
    this.__proto__.makeFormInputs.call(this,{
      'uri' : this.params.uri,
      'title' : this.params.title,
      'description' : this.params.description,
      'thumb' : this.params.thumb
    });
  }

  this.handleEditTitle = function(element) {
    scriptJquery(element).css('display', 'none');
    var input = scriptJquery.crtEle('input', {
      'type' : 'text',
      'value' : scriptJquery(element).text().trim(),
    }).insertAfter(scriptJquery(element)).blur(function() {
      if( input.val().trim() != '' ) {
        this.params.title = input.val();
        scriptJquery(element).text(this.params.title);
        this.setFormInputValue('title', this.params.title);
      }
      scriptJquery(element).css('display', '');
      input.remove();
    }.bind(this));
    input.focus();
  }

  this.handleEditDescription = function(element) {
    scriptJquery(element).css('display', 'none');
    var input = scriptJquery.crtEle('textarea', {}).html(scriptJquery(element).text().trim()).insertAfter(scriptJquery(element)).blur(function() {
      if( input.val().trim() != '' ) {
        this.params.description = input.val();
        scriptJquery(element).text(this.params.description);
        this.setFormInputValue('description', this.params.description);
      }
      scriptJquery(element).css('display', '');
      input.remove();
    }.bind(this));
    input.focus();
  }
  this.initialize(options);
};



})(); // END NAMESPACE
