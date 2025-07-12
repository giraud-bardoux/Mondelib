var Coverphoto = function(options) {
  this.defaultOptions = {
    element : null,
    buttons : '#cover_photo_options',
    photoUrl : '',
    positionUrl : '',
    position : {
      top : 0,
      left : 0
    }
  }
  this.block = null;
  this.buttons = null;
  this.element = null;
  this.changeButton = null;
  this.saveButton = null;
  this.options = scriptJquery.extend(this.defaultOptions,options);
 
  if (this.options.block == null) {
      return;
  }
  this.block = this.options.block;
    
  this.attach = function () {
    var self = this;
    if (!scriptJquery(this.options.buttons).length) {
      return;
    }
    this.element = self.block.find('.cover_photo:first');
    this.buttons = scriptJquery(this.options.buttons);
    this.editButton = this.buttons.find('.edit-button:first');
    this.saveButton = this.buttons.find('.save-button:first');
    if (this.saveButton) {
      this.saveButton.find('.save-positions:first').on('click', function () {
        self.reposition.save();
      });
      this.saveButton.find('.cancel:first').on('click', function () {
        self.reposition.stop(1);
      });
    }
  }

 this.getCoverPhoto = function (canRepostion) {
    var self = this;
    scriptJquery.ajax({
      method : 'get',
      url : self.options.photoUrl,
      dataType : 'html',
      data : {
        format : 'html',
        subject : en4.core.subject.guid,
      },
      success : function (responseHTML) {
        if ( responseHTML.length <= 0) {
          return;
        }
        self.block.html(responseHTML);
        Smoothbox.bind(self.block);
        self.attach();
        if (canRepostion === 0) {
          return;
        }
        self.options.position = {
          top: 0,
          left : 0
        };
        Smoothbox.close();
        setTimeout(function () {
          self.reposition.start()
        }, '1000');
      }
    });
  };
  this.reposition = {
    drag : null,
    active : false,
    start : function () {
      if (this.active) {
        return;
      }
      
      var self = document.coverPhoto;
      var cover = self.getCover();
      this.active = true;
      self.getButton().addClass('is_hidden');
      self.buttons.addClass('cover_photo_options');
      self.getButton('save').removeClass('is_hidden');
      self.block.find('.cover_tip_wrap:first').removeClass('is_hidden');
      if(scriptJquery('.cover_photo_profile_information').length) {
        scriptJquery('.cover_photo_profile_information').addClass('is_hidden');
				// scriptJquery('.profile_main_photo_wrapper').addClass('is_hidden');
				scriptJquery('.profile_cover_photo').addClass('remove_overlay');
      }
      cover.addClass('draggable');
      var content = cover.parent();
      var verticalLimit = parseInt(cover.offsetHeight) - parseInt(content.offsetHeight);
      var horizontalLimit = parseInt(cover.offsetWidth) - parseInt(content.offsetWidth);
      var limit = {
        x:[0, 0],
        y:[0, 0]
      };
      limit.y = verticalLimit > 0 ? [-verticalLimit, 0] : limit.y;
      limit.x = horizontalLimit > 0 ? [-horizontalLimit, 0] : limit.x;

      //Drag Photo on cover
      scriptJqueryUIMin('#cover_photo_image').dragncrop({instruction: true});
    },
    stop : function() {
      var self = document.coverPhoto;
      //self.reposition.drag.detach();
      self.getButton('save').addClass('is_hidden');
      //self.block.getElement('.cover_tip_wrap').addClass('is_hidden');
      self.buttons.removeClass('cover_photo_options');
      self.getButton().removeClass('is_hidden');

      if(scriptJquery('.cover_photo_profile_information').length) {
        scriptJquery('.cover_photo_profile_information').removeClass('is_hidden');
				// scriptJquery('.profile_main_photo_wrapper').removeClass('is_hidden');
				scriptJquery('.profile_cover_photo').removeClass('remove_overlay');
      }
      self.getCover().removeClass('draggable');
      self.reposition.drag = null;
      self.reposition.active = false;
    },
    save : function () {
      if (!this.active) {
        return;
      }
      var self = document.coverPhoto;
      var current = this;
      self.options.position.top = parseInt(scriptJquery('#cover_photo_image').css('top'), 10);
      self.options.position.left = parseInt(scriptJquery('#cover_photo_image').css('left'), 10);
      scriptJquery.ajax({
        method : 'get',
        url : self.options.positionUrl,
        format : 'json',
        data : {
          'position' : self.options.position
        },
        success:function () {
          if(scriptJquery('.cover_photo_profile_information').length) {
            scriptJquery('.cover_photo_profile_information').removeClass('is_hidden');
            self.reposition.stop(1);
            scriptJquery('.dragncrop-instruction').remove();
          }
          //current.preventDefault();
        }
      });
    }
  };
  this.getCover = function (type) {
    if (type == 'block') {
      return this.block;
    }
    return this.element;
  };
  this.getButton = function (type) {
    if (type == 'save') {
      return this.saveButton;
    }
    return this.editButton;
  }
  this.getCoverPhoto(0);
};

var Mainphoto = function(options){
  this.defaultOptions = {
    element : null,
    buttons : 'mainphoto_options',
    photoUrl : '',
    showContent : {},
    position : {
      top : 0,
      left : 0
    }
  };
  this.block = null;
  this.buttons = null;
  this.element = null;
  this.changeButton = null;
  this.saveButton = null;
  this.options = scriptJquery.extend(this.defaultOptions,options);
  if (this.options.block == null) {
    return;
  }
  this.block = this.options.block;

  this.attach = function () {
    var self = this;
    if(!scriptJquery(this.options.buttons).length){
      return;
    }
    this.element = self.block.find('.cover_photo:first');
  };

  this.getMainPhoto = function () {
    var self = this;
    scriptJquery.ajax({
      method : 'get',
      url : self.options.photoUrl,
      dataType : 'html',
      data : {
        format : 'html',
        subject : en4.core.subject.guid,
      },
      success:function (responseHTML) {
        if (responseHTML.length <= 0) {
          return;
        }
        self.block.html(responseHTML);
        // Smoothbox.bind(self.block);
        self.attach();
        // en4.core.runonce.trigger();
        Smoothbox.close();
      }
    });
  }
  this.getMainPhoto();
};
