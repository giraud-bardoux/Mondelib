
/* $Id: comments_composer_tag.js 9572 2011-12-27 23:41:06Z john $ */



(function() { // START NAMESPACE
var $ = 'id' in document ? document.id : window.$;

CommentsComposer.Plugin.Tag = function(options){

  this.__proto__ = new CommentsComposer.Plugin.Interface({})

  this.name = 'tag';

  this.options = {
    'enabled' : false,
    requestOptions : {},
    'suggestOptions' : {
      'minLength': 0,
      'maxChoices' : 9,
      'delay' : 250,
      'selectMode': 'pick',
      'multiple': false,
      'filterSubset' : true,
      'tokenFormat' : 'object',
      'tokenValueKey' : 'label',
      'injectChoice': function(){},
      'onPush' : function(){},
      'prefetchOnInit' : true,
      'alwaysOpen' : false,
      'ignoreKeys' : true
    }
  };

  this.initialize = function(options) {
    this.params = new Hash(this.params);
    this.__proto__.initialize.call(this,options);
  };

  this.ssuggest = false;

  this.attach = function() {
    if( !this.options.enabled ) return;
    this.__proto__.attach.call(this);

    var self=this;
    this.getComposer().elements.body.on('keydown',function (event) {
        // if (self.suggest && self.suggest.visible && event) {
        //   self.suggest.onCommand(event);
        //   if (self.suggest.stopKeysEvent){
        //     event.stop();
        //     return;
        //   }
        // }
        self.monitor.bind(self)(event);
      }
    );

    this.getComposer().elements.body.on('keypress', this.monitor.bind(this));
    this.getComposer().elements.body.on('click', this.monitor.bind(this));
    this.getComposer().getForm().on('editorSubmit', this.submit.bind(this));
    this.getComposer().getForm().on('editorExtractTag', this.extractTag.bind(this));

    return this;
  };

  this.detach = function() {
    if( !this.options.enabled ) return;
   this.__proto__.detach.call(this);
    this.getComposer().elements.body.off('keypress', this.monitor.bind(this));
    this.getComposer().elements.body.off('click', this.monitor.bind(this));
    this.getComposer().getForm().off('editorSubmit', this.submit.bind(this));
    this.getComposer().getForm().off('editorExtractTag', this.extractTag.bind(this));
    if( this.interval ) clearInterval(this.interval);
    return this;
  };

  this.activate = function(){};

  this.deactivate = function(){};

  this.poll = function() {};

  this.monitor = function(e) {
    // seems like we have to do this stupid delay or otherwise the last key
    // doesn't get in the content
    setTimeout(function () {

      var content = this.getComposer().getContent();
      content = content.replace(/&nbsp;/g, ' ');
      if (!content) {
        return;
      }
      this.caretPosition = this.getComposer().getCaretPos();
      content = content.substring(0, this.caretPosition);
      var currentIndex = content.lastIndexOf('@');
      if (currentIndex === -1) {
        return this.endSuggest();
      }

      var value = content.replace(/\n/gi, ' ');
      if (currentIndex > 0 && value.substr((currentIndex - 1), 1) !== ' ') {
        return this.endSuggest();
      }

      var segment = content.substring(currentIndex + 1, this.caretPosition).trim();
      // Check next space
      var spaceIndex = segment.indexOf(' ');
      if (spaceIndex > -1) {
        segment = segment.substring(0, spaceIndex);
      }

      if( segment == '' ) {
        this.endSuggest();
        return;
      }
      this.doSuggest(segment);

    }.bind(this),5);
  };

  this.doSuggest = function(text) {
    this.currentText = text;
    var suggest = this.getSuggest();
    var input = this.getHiddenInput();
    input.val(text);
    suggest.autocomplete('search').autocomplete( "widget" ).addClass("d-none");
  };

  this.endSuggest = function() {
    this.currentText = '';
    this.positions = {};
    if( this.suggest ) {
      this.getSuggest().remove();
      delete this.suggest;
    }
  };

  this.getHiddenInput = function() {
    if( !this.hiddenInput ) {
      this.hiddenInput = scriptJquery.crtEle('input', {
        'type' : 'text',
      }).css({
          'display' : 'none'
      }).appendTo(scriptJquery(document.body));
    }
    return this.hiddenInput;
  }
  this.cache = {};
  this.getSuggest = function() {
    if( !this.suggest ) {
      var width = this.getComposer().elements.body.width();
      this.choices = scriptJquery.crtEle('ul', {
				'class':'tag-autosuggest',
      }).css({
          'width' : (width-2 )+ 'px'
      }).insertAfter(this.getComposer().elements.body);

      var self = this;
      var options = {
        disabledSuggest: true,
        response: function(event, ui ) {
          if(self.choices)
            self.choices.html("");
          if(typeof ui.content === "undefined"){
            return false;
          }
          ui.content.forEach((token)=>{
            var choice = scriptJquery.crtEle('li', {
              'class': 'autocompleter-choices',
              'id': token.guid,
              'tabindex' : "-1"
            }).html(token.photo || '');
            scriptJquery.crtEle('div', {
              'class' : 'autocompleter-choice'
            }).html(token.label).appendTo(choice);

            scriptJquery.crtEle('input', {
              'type' : 'hidden',
              'value' : JSON.stringify(token)
            }).appendTo(choice);
            choice.data('autocompleteChoice', token);
            choice.click(function(e){
              options.select.call(this,e);
            });
            choice.appendTo(self.choices);
          });
        },
        select: function(event) { 
          
          var data = JSON.parse(scriptJquery(this).find('input').val());
         
          var replaceString = '@' + self.currentText;
          var newString = '&nbsp;<span class="feed_composer_tag" rel="'+data.guid+'" rev="'+data.title+'" >'+data.title+'</span>&nbsp;';
          var content = self.getComposer().getContent();

          content = content.replace(replaceString, newString);
          var hiddenTag = self.getComposer().hiddenTagContent.val() + '@userTags:' + data.guid + ':'
            + data.title + '@';
          self.getComposer().hiddenTagContent.val(hiddenTag);
          self.getComposer().setContent(content);
          self.getComposer().setCaretPos(self.caretPosition + 2 - replaceString.length + data.title.length);
          self.choices.remove();
          delete self.choices;
        },
        close: function(event, ui ) {
          self.choices.remove();
          delete self.choices;
        },
      };
      if( this.options.suggestProto == 'local' ) {
        options['source'] = this.options.suggestParam;
        this.suggest = this.getHiddenInput().autocomplete(options);
      } else if( this.options.suggestProto == 'request.json' ) {
        options['source'] = function(request, response) { 
            if(self.cache[request.term]){
               response(self.cache[request.term]);
            } else {
              scriptJquery.ajax({
                url: self.options.suggestOptions.url,
                dataTyp: 'json',
                method: 'post',
                data: scriptJquery.extend({ value: request.term },self.options.suggestOptions.data),
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
        this.suggest = this.getHiddenInput().autocomplete(options);
      }
    }
    return this.suggest;
  }

  this.extractTag = function () {
    var tagText = this.getComposer().tagText;
    var tagRegex = /@userTags:\w+:[^\@]+@/gim;
    var tagContent = this.getComposer().hiddenTagContent.val();
    var tagMatch = tagContent.match(tagRegex);
    if (tagMatch === null) {
      return;
    }

    var tempText = '';
    var hiddenTags = '';
    var tagRel = new Array();
    var tagLabel = new Array();
    var tagsArray = tagContent.split(tagRegex);
    var matchCount = tagMatch.length;
    for (var i = 0; i < matchCount; ++i) {
      tagRel[i] = tagMatch[i].replace(/@userTags:/, '').replace(/:(.*)@/, '').trim();
      tagLabel[i] = tagMatch[i].replace(/^@userTags+:\w+:/, '').replace(/\@$/, '').trim();
      var tagIndex = tagText.indexOf(tagLabel[i]);
      if (tagIndex > -1) {
        tagsArray[i] = tagText.substr(0, tagIndex);
        tagText = tagText.substr(tagIndex + tagLabel[i].length);
      } else {
        tagMatch[i] = tagLabel[i] = '';
      }

      var subText = tagsArray[i] || '';
      hiddenTags += tagMatch[i];
      if (tagLabel[i] && tagRel[i]) {
        // extract user tags
        tempText += subText + '<span class="feed_composer_tag" rel="' + tagRel[i] + '" rev="' + tagLabel[i] + '" >' +
          tagLabel[i] + '</span>';
      }
    }

    var tempString = '';
    if (i > 0) {
      tempString = tagText.replace(tagLabel[i - 1], '');
      tempText += tempString;
    }

    this.getComposer().tagText = tempText;
    this.getComposer().hiddenTags = hiddenTags;
  };

  this.submit = function () {
    this.makeFormInputs({
      tag: this.getTagsFromComposer()
    });
  }

  this.getTagsFromComposer = function () {
    this.filterTagsFromComposer();
    var composerTags = new Hash();
    var body = this.getComposer().elements.body;
    body.find('.feed_composer_tag').each(function (e) {
       var tag = scriptJquery(this);
      composerTags[tag.attr('rel')] = tag.text();
    });
    return scriptJquery.param(composerTags.getClean());
  }
  this.filterTagsFromComposer =function () {
    var body = this.getComposer().elements.body;
    body.find('.feed_composer_tag').each(function (e) {
      var tag = scriptJquery(this);
      if (tag.text() != tag.attr('rev')){
        tag.removeClass('feed_composer_tag');
      }
    });
  }
  this.makeFormInputs = function(data) {
    Object.entries(data).forEach(function([key,value]) {
      this.setFormInputValue(key, value);
    }.bind(this));
  }
  // make tag hidden input and set value into composer form
  this.setFormInputValue = function(key, value) {
    var elName = 'aafComposerForm' + key.replace(/\b[a-z]/g, function(match){
      return match.toUpperCase();
    });
    var composerObj=this.getComposer();
    if(composerObj.elements.has(elName))
      composerObj.elements.get(elName).remove();
      composerObj.elements.set(elName, scriptJquery.crtEle('input', {
        'type' : 'hidden',
        'name' : 'composer[' + key + ']',
        'value' : value || ''
      }).appendTo(composerObj.getInputArea()));
    composerObj.elements.get(elName).val(value);
  }
  this.initialize(options);
};
})(); // END NAMESPACE
