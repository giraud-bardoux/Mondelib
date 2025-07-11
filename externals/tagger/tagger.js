
var Tagger = function(el,options){
  defaultOptions = {
    'title' : false,
    'description' : false,
    'transImage' : 'application/modules/Core/externals/images/trans.gif',
    'existingTags' : [],
    'tagListElement' : false,
    'linkElement' : false,
    'noTextTagHref' : true,
    'guid' : false,
    'enableCreate' : false,
    'enableDelete' : false,
    'createRequestOptions' : {
      'url' : '',
      'data' : {
        'format' : 'json'
      }
    },
    'deleteRequestOptions' : {
      'url' : '',
      'data' : {
        'format' : 'json'
      }
    },
    // Cropper options
    'cropOptions' : {
      'preset' : [10,10,58,58],
      'min' : [48,48],
      'max' : [128,128],
      'handleSize' : 8,
      'opacity' : .6,
      'color' : '#7389AE',
      'border' : 'externals/moolasso/crop.gif'
    },

    // Autosuggest options
    'suggestProto' : 'local',
    'suggestParam' : [

    ],
    'suggestOptions' : {
      'minLength': 0,
      'maxChoices' : 100,
      'delay' : 250,
      'selectMode': 'pick',
      //'autocompleteType': 'message',
      'multiple': false,
      'className': 'message-autosuggest',
      'filterSubset' : true,
      'tokenFormat' : 'object',
      'tokenValueKey' : 'label',
      'injectChoice': function(){},
      'onPush' : function(){},

      'prefetchOnInit' : true,
      'alwaysOpen' : true,
      'ignoreKeys' : true
    }
  };
  
  this.options = scriptJquery.extend(true,{},defaultOptions, options);
  el = scriptJquery(el);
  if(el.prop("tagName") != 'IMG' ) {
    this.image = el.find('img:first');
  } else {
    this.image = el;
  }
  this.element = el;
  this.count = 0;
  this.initialize = function(){
    try{
      this.options.existingTags.forEach((params)=>{
        this.addTag(params);
      });
    } catch(error){ console.log(error); }
  }
  this.begin = function() {
    if( !this.options.enableCreate ) return;
    this.getForm();
    this.getSuggest();
    this.element.click(this.makeTagForm.bind(this));
    this.onMove({y:78,x:78,w:48,h:48});
    if(this.input.hasClass("ui-draggable")){
      this.input.trigger("drag");
    }
  }
  this.end = function() {
    if( this.form ) {
      this.form.remove();
      delete this.form;
    }
    if( this.suggest ) {
      delete this.suggest;
    }
  }
  this.makeTagForm = function(e){
    var imgtag = scriptJquery(e.target); 
    if(imgtag.closest("#tagger_form").length){
      return false;
    }
    var mouseX = ( e.pageX - imgtag.offset().left ) - 50; 
    var mouseY = ( e.pageY - imgtag.offset().top ) - 50;
    //this.onMove({y:parseInt(mouseY),x:parseInt(mouseX),w:48,h:48});
    //imgtag.parent().find('#tagger_form').css({position:"absolute"});    
  }
  this.getForm = function() {
    if( !this.form ) {
      this.form = scriptJquery.crtEle('div', {
        'id' : 'tagger_form',
        'class' : 'tagger_form',
      }).css({
          'position' : 'absolute',
          'z-index' : '10000',
          'width' : '150px'
          //'height' : '300px'
      }).appendTo(this.element);

      scriptJquery.crtEle('div', {
        'class' : 'box_area'
      }).appendTo(this.form);

      // Title
      if( this.options.title ) {
        scriptJquery.crtEle('div', {
          'class' : 'media_photo_tagform_titlebar'
        }).html(this.options.title).appendTo(this.form);
      }

      // Container
      this.formContainer = scriptJquery.crtEle('div', {
        'class' : 'media_photo_tagform_container'
      }).appendTo(this.form);

      // Description
      if( this.options.description ) {
        scriptJquery.crtEle('div', {
          'class' : 'media_photo_tagform_text',
        }).html(this.options.description).appendTo(this.formContainer);
      }

      // Input
      this.input = scriptJquery.crtEle('input', {
        'id' : 'tagger_input',
        'class' : 'tagger_input',
        'type' : 'text',
      }).appendTo(this.formContainer);

      // Choices
      this.choices = scriptJquery.crtEle('div', {
        'class' : 'tagger_list'
      }).appendTo(this.formContainer);

      // Submit container
      var submitContainer = scriptJquery.crtEle('div', {
        'class' : 'media_photo_tagform_submits'
      }).appendTo(this.formContainer);

      var self = this;
      // var tag_save = scriptJquery.crtEle('a', {
      //   'id' : 'tag_save',
      //   'href' : 'javascript:void(0);',
      // }).html(en4.core.language.translate('Save')).appendTo(submitContainer);

      // tag_save.click(function() {
      //       var data = {};
      //       data.label = self.input.value;
      //       if( $type(data.label) && data.label != '' ) {
      //         data.extra = self.coords;
      //         self.createTag(data);
      //       }
      // });
      var tag_cancel = scriptJquery.crtEle('a', {
        'id' : 'tag_cancel',
        'href' : 'javascript:void(0);',
      }).html(en4.core.language.translate('Cancel')).appendTo(submitContainer);
      tag_cancel.click(function() {
            this.end();
          }.bind(self));
      this.form.css({ top:0, left:0,position:"absolute"});
      this.input.focus();
      this.draggable(this.form);
    }
    return this.form;
  }

  this.draggable = function(element){
    var self = this;
    var draggableOptions = {
      zIndex: 10000,
      drag: function(event, ui) {
        var newTop = ui.position.top;
        var newLeft = ui.position.left;
        newTop = self.image.height() < newTop ? self.image.height()-48 : (newTop < 1 ? 1 : newTop);
        newLeft = self.image.width() < newLeft ? self.image.width()-48 : (newLeft < 1 ? 1 : newLeft);
        ui.position.top = newTop;
        ui.position.left = newLeft;
        self.coords = {y:parseInt(newTop),x:parseInt(newLeft),w:48,h:48}
      },
    };
    element.draggable(draggableOptions);
  }
  this.cache = {};
  this.getSuggest = function() {
    if(!this.suggest ) {
      var self = this;
      var options = {
        disabledSuggest: true,
        select: function(event, ui) {
          var data = ui.item;
          data.extra = self.coords;
          let tagexisted = self.options.existingTags.find((item)=> {
            return item.guid == token.guid;
          });
          if (tagexisted) {
            return false;
          }
          self.createTag(data);
          self.options.existingTags.push(data);
        },
        response: function(event, ui ) {
          self.choices.html("");
          if(typeof ui.content === "undefined"){
            return false;
          }
          ui.content.forEach((token)=>{
            let tagexisted = self.options.existingTags.find((item)=> {
              return item.guid == token.guid;
            });
            if (tagexisted) {
              return false;
            }

            var choice = scriptJquery.crtEle('li', {
              'class': 'autocompleter-choices',
              //'value': token.id,
              'id': token.guid
            }).appendTo(token.photo);

            if(token.photo) {
              scriptJquery.crtEle('div', {
                'class' : 'autocompleter-choice'
              }).html(token.photo).appendTo(choice);
            }
            
            scriptJquery.crtEle('div', {
              'class' : 'autocompleter-choice'
            }).html(token.label).appendTo(choice);

            scriptJquery.crtEle('input', {
              'type' : 'hidden',
              'value' : JSON.stringify(token)
            }).appendTo(choice);
            choice.data('autocompleteChoice', token);
            choice.click(self.createTag.bind(self,token));
            choice.appendTo(self.choices);
          });
        },
        close: function(event, ui ) {
          scriptJquery("#tagfriends").val("");
        },
      };
      if( this.options.suggestProto == 'local' ) {
        options['source'] = this.options.suggestParam;
        this.suggest = this.input.autocomplete(options);
      } else if( this.options.suggestProto == 'request.json' ) {
        options['source'] = function (request, response) { 
            if(self.cache[request.term]){
               response(self.cache[request.term]);
            } else {
              scriptJquery.ajax({
                url: self.options.suggestParam,
                data: { value: request.term.replace('initial_autocomplete','') },
                success: function (transformed) {
                  response(transformed);
                  self.cache[request.term] = transformed;
                },
                error: function () {
                    response([]);
                }
            });
          }
        };
        this.suggest = this.input.autocomplete(options);
      }
    }
    this.input.autocomplete('search','initial_autocomplete');
    scriptJquery('.ui-autocomplete').remove();
    return this.suggest;
  }
  this.getTagList = function() {
    if( !this.tagList ) {
      if( !this.options.tagListElement ) {
        this.tagList = scriptJquery.crtEle('div', {
          'class' : 'tag_list'
        }).appendTo(this.element);
      } else {
        this.tagList = scriptJquery(this.options.tagListElement);
      }
    }
    return this.tagList;
  }
  this.onMove = function(coords) {
    this.coords = coords;
    var pos = {x:0,y:0}; //this.element.getPosition();
    var form = this.getForm();
    form.css({
      'top' : pos.y + coords.h + 20,
      'left' : pos.x + coords.x + coords.w + 20
    });
  }

  // Tagging stuff
  this.addTag = function(params) {
    // Required: id, text, x, y, w, h
    if ( 'object' != $type(params) ) {
      //alert('This entry has already been tagged.');
     return;
    }
    if (params.extra === null) {
      return;
    }
    var baseX = 0, baseY = 0, baseW = 0, baseH = 0;
      ["x", "y", "w", "h"].forEach(function([key,value]) {
          params.extra[key] = parseInt(params.extra[key]);
      });

    if( this.options.noTextTagHref && params.tag_type == 'core_tag' ) {
      delete params.href;
    }
    var self = this;
    // Make tag
    var tag = scriptJquery.crtEle('div', {
      'id' : 'tag_' + params.id,
      'class' : 'tag_div'
    }).css({
        'position' : 'absolute',
        'width' : params.extra.w,
        'height' : params.extra.h,
        'top' : baseY + params.extra.y,
        'left' : baseX + params.extra.x
    }).html('<img src="'+this.options.transImage+'" width="100%" height="100%" />').appendTo(this.element);

    tag.mouseover(function() {
      self.showTag(params.id);
    }.bind(self)).mouseout(function() {
      self.hideTag(params.id);
    }.bind(self));

    // Make label
    // Note: we need to use visibility hidden to position correctly in IE
    var label = scriptJquery.crtEle("span", {
      'id' : 'tag_label_' + params.id,
      'class' : 'tag_label'
    }).css({'position' : 'absolute'}).html(params.text).appendTo(this.element);

    var labelPos = {};
    labelPos.top = ( baseY + params.extra.y + tag.height());
    labelPos.left = Math.round( ( baseX + params.extra.x ) + ( tag.width() / 2 ) - (label.width() / 2) );

    if( this.element.height() < parseInt(labelPos.top) + 20 ){
      labelPos.top = baseY + params.extra.y - label.height();
    }
    label.css(labelPos);

    this.hideTag(params.id);

    var isFirst = ( !$type(this.count) || this.count == 0 );
    this.getTagList().css('display', '');

    // Make list
    if( !isFirst ) scriptJquery.crtEle('span', {
      'id' : 'tag_comma_' + params.id,
      'class' : 'tag_comma',
    }).html(',').appendTo(this.getTagList());

    // Make other thingy
    var info = scriptJquery.crtEle('span', {
      'id' : 'tag_info_' + params.id,
      'class' : 'tag_info media_tag_listcontainer'
    }).appendTo(this.getTagList());

    var activator = scriptJquery.crtEle('a', {
      'id' : 'tag_activator_' + params.id,
      'class' : 'tag_activator',
      'href' : params.href || null,
    }).html(params.text).appendTo(info);

    activator.mouseover(function() {
      self.showTag(params.id);
    }.bind(self)).mouseout(function() {
      self.hideTag(params.id);
    }.bind(self));

    // Delete
    if(this.checkCanRemove(params.id))
    {
      info.append(' (');
      var destroyer = scriptJquery.crtEle('a', {
        'id' : 'tag_destroyer_' + params.id,
        'class' : 'tag_destroyer albums_tag_delete',
        'href' : 'javascript:void(0);',
      }).html(en4.core.language.translate('delete')).click(function() {
        this.removeTag(params.id);
      }.bind(this)).appendTo(info);
      info.append(')');
    }
    this.count++;
  }

  this.createTag = function(params) {
    params.extra = this.coords;
    if( !this.options.enableCreate ) return;
    var request = scriptJquery.ajax({
      url: this.options.createRequestOptions.url,
      data: scriptJquery.extend(params,{format : 'json'},this.options.createRequestOptions.data),
      method:'post',
      dataType: 'json',
      success: function (response) {
         this.addTag(response);
      }.bind(this),
      error: function () {
         
      }
    });
    // End tagging
    this.end();
  }

  this.removeTag = function(id) {
    if( !this.checkCanRemove(id) ) return;
    // Remove from frontend
    var next = scriptJquery('#tag_info_' + id).next();
    if( next.length && next.html().trim() == ',' ) next.remove();
    scriptJquery('#tag_' + id).remove();
    scriptJquery('#tag_label_' + id).remove();
    scriptJquery('#tag_info_' + id).remove();
    this.count--;
    let self = this;
    scriptJquery.ajax({
      url: this.options.deleteRequestOptions.url,
      data: scriptJquery.extend({
        'tagmap_id' : id
      },{format : 'json'},this.options.createRequestOptions.data),
      method:'post',
      dataType: 'json',
      success: function (response) {
        self.options.existingTags.forEach((datum,i)=>{
          if( datum.tagmap_id == id ) {
            self.options.existingTags.splice(i,1);
          }
        });
      },
      error: function () {  
      }
    });
  }
  this.checkCanRemove = function(id) {
    // Check if can remove
    var tagData;
    this.options.existingTags.forEach(function(datum) {
      if( datum.tagmap_id == id ) {
        tagData = datum;
      }
    });
    if(this.options.enableDelete) return true;
    if(tagData) {
      if( tagData.tag_type + '_' + tagData.tag_id == this.options.guid ) return true;
      if( tagData.tagger_type + '_' + tagData.tagger_id == this.options.guid ) return true;
    }
    return false;
  }

  this.showTag = function(id) {
    scriptJquery('#tag_' + id).removeClass('tag_div_hidden');
    scriptJquery('#tag_label_' + id).removeClass('tag_label_hidden');
  }

  this.hideTag = function(id) {
    scriptJquery('#tag_' + id).addClass('tag_div_hidden');
    scriptJquery('#tag_label_' + id).addClass('tag_label_hidden');
  }
  this.initialize();
};
