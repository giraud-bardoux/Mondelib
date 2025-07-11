
/* $Id: composer.js 10019 2013-03-27 01:52:21Z john $ */



(function() { // START NAMESPACE

Composer = function(element, options){

  //Implements : [Events, Options],

  this.elements = {};

  this.plugins = {};
  this.hashRegex = /\B(#[^\s[!\"\#$%&'()*+,\-.\/\\:;<=>?@\[\]\^`{|}~]+)/g;
  this.lastCaretPos = 0;

  this.options = {
    lang : {},
    overText : true,
    hashtagEnabled : false,
    allowEmptyWithoutAttachment : false,
    allowEmptyWithAttachment : true,
    hideSubmitOnBlur : true,
    submitElement : false,
    useContentEditable : true,
    submitCallBack: null,
  };

  this.isMobile ={
    Android: navigator.userAgent.match(/Android/i),
    BlackBerry: navigator.userAgent.match(/BlackBerry/i),
    iOS: navigator.userAgent.match(/iPhone|iPad|iPod/i),
    Opera: navigator.userAgent.match(/Opera Mini/i),
    Windows: navigator.userAgent.match(/IEMobile/i),
  }

  this.initialize = function(element, options) {
    this.options = scriptJquery.extend(this.options,options);
    this.elements = new Hash(this.elements);
    this.plugins = new Hash(this.plugins);

    this.elements.textarea = scriptJquery(element);
    this.elements.textarea.data('Composer');

    this.attach();
    this.getTray();
    this.getMenu();

    this.pluginReady = false;
    this.getForm().on('submit', function(e) {
      //e.preventDefault();
      this.getForm().trigger('editorSubmit');
      if( this.pluginReady ) {
        if( !this.options.allowEmptyWithAttachment && this.getContent().trim() == '' ) {
          e.preventDefault();
          return;
        }
      } else {
        if( !this.options.allowEmptyWithoutAttachment && this.getContent().trim() == '' ) {
          e.preventDefault();
          return;
        }
      }
      this.saveContent();
      if (this.options.submitCallBack) {
        this.options.submitCallBack(this, e);
      }
    }.bind(this));
  }

  this.getMenu = function() {
    if(!$type(this.elements.menu)) {
      try {
        this.elements.menu = scriptJquery(this.options.menuElement);
      } catch(err){ console.log(err); }
      if(!$type(this.elements.menu)) {
        this.elements.menu = scriptJquery.crtEle('div',{
          'id' : 'compose-menu',
          'class' : 'compose-menu'
        }).insertAfter(this.getForm());
      }
    }
    return this.elements.menu;
  }

  this.getTray = function() {
    if( !$type(this.elements.tray) ) {
      try {
        this.elements.tray = scriptJquery(this.options.trayElement);
      } catch(err){  console.log(err); }
      if( !$type(this.elements.tray) || !this.elements.tray.length) {
        this.elements.tray = scriptJquery.crtEle('div',{
          'id' : 'compose-tray',
          'class' : 'compose-tray',
        }).css({
            'display' : 'none'
        }).insertAfter(this.getForm());
      }
    }
    return this.elements.tray;
  }

  this.getInputArea = function() {
    if( !$type(this.elements.inputarea) ) {
      var form = this.elements.textarea.parents('form');
      this.elements.inputarea = scriptJquery.crtEle('div', {}).css({
          'display' : 'none'
      }).appendTo(form);
    }
    return this.elements.inputarea;
  }

  this.getForm =function() {
    return this.elements.textarea.closest('form');
  }

  // Editor
  this.attach = function() {
    // Modify textarea
    this.elements.textarea.addClass('compose-textarea').css('display', 'none');

    // Create container
    this.elements.container = scriptJquery.crtEle('div', {
      'id' : 'compose-container',
      'class' : 'compose-container',
    });
    this.elements.textarea.wrap(this.elements.container);
    // Create body
    var supportsContentEditable = this._supportsContentEditable();
    if( supportsContentEditable ) {
      this.elements.body = scriptJquery.crtEle('div', {
        'class' : 'compose-content',
      }).css({
          'display' : 'block',
          'width' : '100%'
      }).keypress(function(event) {
            if( event.key == 'a' && event.control ) {
              // FF only
              // if( Browser.Engine.gecko ) {
              //   //fix_gecko_select_all_contenteditable_bug(this, event);
              // }
            }
      }).insertBefore(this.elements.textarea);

      if(this.options.postLength > 0 && scriptJquery(this.getMenu()).find(".compose-right-content > .privacy_list").length){
        var parentItem = this;
        scriptJquery(this.elements.body).bind('input', function(e) {
          parentItem.checkPostLength(e);
        });
        this.elements.textCounter = scriptJquery.crtEle('div', {
          'class' : 'compose-content-counter',
        }).css({
            'display' : 'none'
        }).insertBefore(scriptJquery(this.getMenu()).find(".compose-right-content > .privacy_list"));
      }

    } else {
      this.elements.body = this.elements.textarea;
    }

    this.prepareTag();
    // Attach blur event
    var self = this;
    this.elements.body.on('blur', function(e) {
      var curVal;
      if( supportsContentEditable ) {
        curVal = scriptJquery(this).html().replace(/\s/, '').replace(/<[^<>]+?>/ig, '');
      } else {
        curVal = scriptJquery(this).html().replace(/\s/, '').replace(/<[^<>]+?>/ig, '')
      }
      if( '' == curVal ) {
        if( supportsContentEditable ) {
          scriptJquery(this).html('<br />');
        } else {
          scriptJquery(this).html('');
        }
        if( self.options.hideSubmitOnBlur ) {
          setTimeout(function() {
            if( !self.hasActivePlugin() ) {
              self.getMenu().css('display', 'none');
            }
          },250);
        }
      }
    });

    if( self.options.hideSubmitOnBlur ) {
      if(this.getMenu().length){
        this.getMenu().css('display', 'none');
      }
      this.elements.body.on('focus', function(e) {
        self.getMenu().css('display', '');
      });
    }

    if( supportsContentEditable ) {
      scriptJquery(this.elements.body);
      this.elements.body.attr("contentEditable","true");
      this.elements.body.attr("designMode",'On');

      ['MouseUp', 'MouseDown', 'ContextMenu', 'Click', 'Dblclick', 'KeyPress', 'KeyUp', 'KeyDown'].forEach(function(eventName) {
        var method = (this['editor' + eventName] || function(){}).bind(this);
        this.elements.body.on(eventName.toLowerCase(), method);
      }.bind(this));

      this.setContent(this.elements.textarea.val());

      this.selection = new Composer.Selection(this.elements.body);
    } else {
      this.elements.textarea.css('display', '');
    }

    if( this.options.overText && supportsContentEditable ) {
      new ComposerOverText(this.elements.body, scriptJquery.extend({
        textOverride : this._lang('Post Something...'),
        poll : true,
        isPlainText : !supportsContentEditable,
        positionOptions: {
          position: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          edge: ( en4.orientation == 'rtl' ? 'upperRight' : 'upperLeft' ),
          offset: {
            x: ( en4.orientation == 'rtl' ? -4 : 4 ),
            y: 2
          }
        }
      }, this.options.overTextOptions));
    }
    ////this.fireEvent('attach', this);
  }

  this.detach = function() {
    this.saveContent();
    this.textarea.css('display', '').removeClass('compose-textarea').insertBefore(this.container);
    this.container.dispose();
    //this.fireEvent('detach', this);
    return this;
  }

  this.prepareTag = function () {
    this.hiddenTagContent = scriptJquery.crtEle('input', {
      'type': 'hidden'
    }).insertAfter(this.elements.body);

    if (!this.isMobileorIE()) {
      //this.on('editorKeyDown', this.extractTag);
    }
    this.elements.body.on('input', this.extractTag.bind(this));
    this.elements.body.on('click', this.extractTag.bind(this));
  }

  this.extractTag = function () {
    if (this.getSelectedText() !== "") {
      return;
    }
    var content = this.getContent();
    this.tagText = content;
    this.extractHashTag();
    this.getForm().trigger('editorExtractTag');
    try {
      var pos = this.getCaretPos();
    } catch (e) {
      // Listen empty caret error.
    }

    if (!this._supportsContentEditable()) {
      return;
    }

    if ( this.tagText.trim() === '' && this.tagText.split(/\r\n|\r|\n/).length <= 2) {
      this.lastCaretPos = 0;
      return;
    }

    this.elements.body.html(this.tagText);
    this.setCaretPos(pos);
    this.hiddenTagContent.val(this.hiddenTags);
  }

  this.extractHashTag = function() {
    if (!this.options.hashtagEnabled) {
      return;
    }

    var content = this.tagText;
    content = content.replace(/\<span\>\<\/span\>/ig, '');
    var hashTags = content.split(this.hashRegex);
    if (hashTags.length === 0) {
      return;
    }

    var tagString = '';
    var updateString = true;
    for (var i=0; i < hashTags.length; i++) {
      var subString = hashTags[i] || '';
      if (subString.indexOf('#') === 0 && updateString) {
        // extract hashTags
        tagString += this.getTagString(subString);
        updateString = false;
        continue;
      }
      updateString = true;
      tagString += subString;
    }

    this.tagText = tagString;
  }

  this.getTagString = function (str) {
    return '<span class="feed_composer_hashtag">' + str + '</span>';
  }

  this.getCaretPos = function() {
    var element = this.elements.body[0];
    var caretPos = 0;
    if (typeof window.getSelection != "undefined") {
      var range = window.getSelection().getRangeAt(0);
      var caretRange = range.cloneRange();
      caretRange.selectNodeContents(element);
      caretRange.setEnd(range.endContainer, range.endOffset);
      caretPos = caretRange.toString().length;
    } else if (typeof document.selection != "undefined" && document.selection.type != "Control") {
      var range = document.selection.createRange();
      var caretRange = document.body.createTextRange();
      caretRange.expand(element);
      caretRange.setEndPoint("EndToEnd", range);
      caretPos = caretRange.text.length;
    }
    return caretPos;
  
  }

  this.setCaretPos = function(pos) {
    this.lastCaretPos = pos;
    var index = 0, range = document.createRange(), body = this.elements.body[0];
    range.setStart(body, 0);
    range.collapse(true);
    var nodeArray = [body], node, isStart = false, stop = false;

    while (!stop && (node = nodeArray.pop())) {
      if (node.nodeType === 3) {
        var nextIndex = index + node.length;
        if (!isStart && pos >= index && pos <= nextIndex) {
          range.setStart(node, pos - index);
          isStart = true;
        } else if (isStart && pos >= index && pos <= nextIndex) {
          range.setEnd(node, pos - index);
          stop = true;
        }
        index = nextIndex;
      } else {
        var i = node.childNodes.length;
        while (i--) {
          nodeArray.push(node.childNodes[i]);
        }
      }
    }
    var selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);

  }

  this.placeCaretAtEnd = function() {
    this.elements.body.focus();
    if (typeof window.getSelection != "undefined" && typeof document.createRange != "undefined") {
      var range = document.createRange();
      range.selectNodeContents(this.elements.body[0]);
      range.collapse(false);
      var sel = window.getSelection();
      sel.removeAllRanges();
      sel.addRange(range);
    } else if (typeof document.body.createTextRange != "undefined") {
      var textRange = document.body.createTextRange();
      textRange.moveToElementText(this.elements.body[0]);
      textRange.collapse(false);
      textRange.select();
    }
  }

  this.getSelectedText = function() {
    var text = "";
    if (typeof window.getSelection != "undefined") {
      text = window.getSelection().toString();
    } else if (typeof document.selection != "undefined" && document.selection.type == "Text") {
      text = document.selection.createRange().text;
    }
    return text;
  }

  this.focus = function(){
    // needs the delay to get focus working
    setTimeout(function(){
      this.elements.body.focus();
      //this.fireEvent('focus', this);
    }.bind(this),10);
    return this;
  }

  this.reset = function(){
    this.setContent(' ');
    this.deactivate();
  }

  // Content

  this.getContent = function(){
    if( this._supportsContentEditable() ) {
      return this.cleanup(this.elements.body.html());
    } else {
      return this.cleanup(this.elements.body.val());
    }
  }

  this.setContent = function(newContent) {
    if( this._supportsContentEditable() && newContent) {
      if(!newContent.trim()) newContent = '<br />';
      this.elements.body.html(newContent.replace(/&nbsp;/g, ' '));
    } else {
      this.elements.body.val(newContent);
    }
    try {
      this.extractTag();
    } catch (e) {
      //Handle caret error.
    }
    return this;
  }

  this.saveContent = function(){
    if( this._supportsContentEditable() ) {
      this.elements.textarea.val(this.getContent().trim().replace(/&nbsp;/g, ' '));
    }
    return this;
  }

  this.cleanup = function(html) {
    // @todo
    return unescape(html)
      .replace(/<(br|p|div)[^<>]*?>/ig, "\r\n")
      .replace(/<\/?span[^>]*>/g, '')
      .replace(/<[^<>]+?>/ig, '')
      .replace(/(\r\n){2,}/ig, "\n");
  }

  // Check IE
  this.isMobileorIE = function() {
    var isIE = window.navigator.userAgent.indexOf("MSIE ") > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./);
    return (this.isMobile.Android || this.isMobile.BlackBerry || this.isMobile.iOS || this.isMobile.Opera || this.isMobile.Windows || isIE);
  }
  // Plugins
  this.addPlugin  = function(plugin) {
    var key = plugin.getName();
    this.plugins.set(key, plugin);
    plugin.setComposer(this);
    return this;
  }

  this.addPlugins = function(plugins) {
    plugins.each(function(plugin) {
      this.addPlugin(plugin);
    }.bind(this));
  }

  this.getPlugin = function(name) {
    return this.plugins.get(name);
  }

  this.activate = function(name) {
    this.deactivate();
    this.plugins.get(name).activate();
  }

  this.deactivate = function() {
    Object.entries(this.plugins).forEach(function([key,plugin]) {
      plugin.deactivate();
    });
    this.getTray().empty();
  }

  this.signalPluginReady = function(state) {
    this.pluginReady = state;
  }
  this.hasActivePlugin = function() {
    var active = false;
    Object.entries(this.plugins).forEach(function(plugin) {
      active = active || plugin.active;
    });
    return active;
  }
  // Key events

  this.editorMouseUp = function(e){
    //this.fireEvent('editorMouseUp', e);
  }

  this.editorMouseDown = function(e){
    //this.fireEvent('editorMouseDown', e);
  }

  this.editorContextMenu = function(e){
    //this.fireEvent('editorContextMenu', e);
  }

  this.editorClick =  function(e){
    // make images selectable and draggable in Safari
    // if (Browser.Engine.webkit){
    //   var el = e.target;
    //   if (el.get('tag') == 'img'){
    //     this.selection.selectNode(el);
    //   }
    // }

    //this.fireEvent('editorClick', e);
  }

  this.editorDoubleClick = function(e){
    //this.fireEvent('editorDoubleClick', e);
  }

  this.editorKeyPress = function(e){
    //this.keyListener(e);
    //this.fireEvent('editorKeyPress', e);
  }

  this.editorKeyUp = function(e){
    //this.fireEvent('editorKeyUp', e);
  }

  this.editorKeyDown = function(e){
    if (this.isMobileorIE()) {
      //this.fireEvent('editorKeyDown', e);
      return;
    }
    // Work for line break
    if (e.key == 'enter') {
      var content = this.getContent();
      content = content.replace(/&nbsp;/g, ' ');
      content = content.replace(/&amp;/g, '&');
      content = content.replace(/&lt;/g, '<'); 
      content = content.replace(/&gt;/g, '>');
      var textBeforeCaret = content.substr(0, this.getCaretPos());
      var textAfterCaret = content.substr(this.getCaretPos());
      var text = textBeforeCaret.endsWith('\n') || content.trim() == '' || textAfterCaret.startsWith('\n') ? '<br>' : '<br>\n';
      this.setContent(textBeforeCaret + text + textAfterCaret);
      this.setCaretPos(textBeforeCaret.length + 1);
      this.extractTag();
      e.preventDefault();
    }

    // Handle selection with ctrl+a
    if (e.ctrlKey) {
      if (e.keyCode == 65 || e.keyCode == 97) { // 'A' or 'a'
        return;
      }
    }
    // Handle deletion with ctrl+a
    if (e.keyCode == 8 || e.keyCode == 46) {
      return;
    }

    //this.fireEvent('editorKeyDown', e);
  }

  this.checkPostLength = function(e) {
    var content = this.getContent();
    content = content.replace(/&nbsp;/g, ' ');
    content = content.replace(/&amp;/g, '&'); 
    content = content.replace(/&lt;/g, '<'); 
    content = content.replace(/&gt;/g, '>');

    if(this.options.postLength < content.length){
      content = content.substr(0,this.options.postLength);
      this.setContent(content);
      this.setCaretPos(content.length);
    }
    this.elements.textCounter.css("display","inline-block");
    this.elements.textCounter.html(this.options.postLength-content.length);
    return;
  }

  this._lang = function() {
    try {
      if( arguments.length < 1 ) {
        return '';
      }
      var string = arguments[0];
      if( $type(this.options.lang) && $type(this.options.lang[string]) ) {
        string = this.options.lang[string];
      }
      if( arguments.length <= 1 ) {
        return string;
      }
      var args = new Array();
      for( var i = 1, l = arguments.length; i < l; i++ ) {
        args.push(arguments[i]);
      }
      return string.vsprintf(args);
    } catch( e ) {
      alert(e);
    }
  }
  
  this._supportsContentEditable = function() {
    if( 'useContentEditable' in this.options &&
        this.options.useContentEditable ) {
      return true;
    } else {
      return false;
    }
  }
  this.initialize(element, options);
};



Composer.Selection = function(win){
  this.initialize = function(win){
    this.win = win;
  }

  this.getSelection = function(){
    //this.win.focus();
    return window.getSelection();
  }

  this.getRange = function(){
    var s = this.getSelection();

    if (!s) return null;

    try {
      return s.rangeCount > 0 ? s.getRangeAt(0) : (s.createRange ? s.createRange() : null);
    } catch(e) {
      // IE bug when used in frameset
      return document.body.createTextRange();
    }
  }

  this.setRange = function(range){
    if (range.select){
      try{
        (function(){
          range.select();
        });
      } catch(err){ console.log(err); }
    } else {
      var s = this.getSelection();
      if (s.addRange){
        s.removeAllRanges();
        s.addRange(range);
      }
    }
  }
  this.selectNode = function(node, collapse){
    var r = this.getRange();
    var s = this.getSelection();
    if (r.moveToElementText){
      try{
        (function(){
          r.moveToElementText(node);
          r.select();
        });
      } catch(err){ console.log(err); }
    } else if (s.addRange){
      collapse ? r.selectNodeContents(node) : r.selectNode(node);
      s.removeAllRanges();
      s.addRange(r);
    } else {
      s.setBaseAndExtent(node, 0, node, 1);
    }

    return node;
  }

  this.isCollapsed = function(){
    var r = this.getRange();
    if (r.item) return false;
    return r.boundingWidth == 0 || this.getSelection().isCollapsed;
  }

  this.collapse = function(toStart){
    var r = this.getRange();
    var s = this.getSelection();
    if (r.select){
      r.collapse(toStart);
      r.select();
    } else {
      toStart ? s.collapseToStart() : s.collapseToEnd();
    }
  }
  this.getContent = function(){
    var r = this.getRange();
    var body = scriptJquery.crtEle('body',{});
    if (this.isCollapsed()) return '';
    if (r.cloneContents){
      body.appendChild(r.cloneContents());
    } else if ($defined(r.item) || $defined(r.htmlText)){
      body.html(r.item ? r.item(0).outerHTML : r.htmlText);
    } else {
      body.html(r.toString());
    }
    var content = body.html();
    return content;
  }
  this.getText = function(){
    var r = this.getRange();
    var s = this.getSelection();
    return this.isCollapsed() ? '' : r.text || s.toString();
  }
  this.getNode = function(){
    var r = this.getRange();
    if (!Browser.Engine.trident){
      var el = null;
      if (r){
        el = r.commonAncestorContainer;
        // Handle selection a image or other control like element such as anchors
        if (!r.collapsed)
          if (r.startContainer == r.endContainer)
            if (r.startOffset - r.endOffset < 2)
              if (r.startContainer.hasChildNodes())
                el = r.startContainer.childNodes[r.startOffset];

        while ($type(el) != 'element') el = el.parentNode;
      }
      return scriptJquery(el);
    }
    return scriptJquery(r.item ? r.item(0) : r.parent());
  }
  this.insertContent = function(content){
    var r = this.getRange();
    if (r.insertNode){
      r.deleteContents();
      r.insertNode(r.createContextualFragment(content));
    } else {
      // Handle text and control range
      (r.pasteHTML) ? r.pasteHTML(content) : r.item(0).outerHTML = content;
    }
  }
  this.initialize(win);
};

class ComposerOverText extends OverText{
  //Extends : OverText,
  constructor(element, options){
    super(element, options);
  }
  test() {
    if( !$type(this.options.isPlainText) || !this.options.isPlainText) {
      return !this.element.html().replace(/\s+/, '').replace(/<br.*?>/, '');
    } else {
      return this.parent();
    }
  }
  hide(suppressFocus, force){
    if (this.text && (this.text.is(":visible") && (!this.element.prop('disabled') || force))){
      this.text.hide();
      //this.fireEvent('textHide', [this.text, this.element]);
      try {
        this.element.trigger('focus');
        this.element.focus();
      } catch(e){} //IE barfs if you call focus on hidden elements

      this.pollingPaused = true;
    }
    return this;
  }
}

Composer.Plugin = {};
Composer.Plugin.Interface = function(options){
  //Implements : [Options, Events],
  this.name = 'interface';
  this.active = false;
  this.composer = false;
  this.options = {
    loadingImage : en4.core.staticBaseUrl + 'application/modules/Core/externals/images/loading.gif'
  };
  this.elements = {};
  this.persistentElements = ['activator', 'loadingImage'];
  this.params = {};
  this.initialize = function(options) {
    this.params = new Hash();
    this.elements = new Hash();
    this.reset();
    this.options = scriptJquery.extend(this.options,options);
  }
  this.getName = function() {
    return this.name;
  }
  this.setComposer = function(composer) {
    this.composer = composer;
    this.attach();
    return this;
  }
  this.getComposer = function() {
    if( !this.composer ) throw "No composer defined";
    return this.composer;
  }
  this.attach = function() {
    this.reset();
  }
  this.detach = function() {
    this.reset();
    if( this.elements.activator ) {
      this.elements.activator.remove();
      this.elements.erase('menu');
    }
  }
  this.reset = function() {
    Object.entries(this.elements).forEach(function([key,element]) {
      if(!this.persistentElements.includes(key)) {
        //console.log(element,"element");
        // if(scriptJquery(element).length)
        //   scriptJquery(element).remove();
        this.elements.erase(key);
      }
    }.bind(this));
    this.params = new Hash();
    this.elements = new Hash();
  }
  this.activate = function() {
    if( this.active ) return;
    this.active = true;
    this.reset();
    this.getComposer().getTray().show();
    this.getComposer().getMenu().hide();
    var submitButtonEl = scriptJquery(this.getComposer().options.submitElement);
    if(submitButtonEl.length) {
      submitButtonEl.css('display', 'none');
    }
    this.getComposer().getMenu().css('border', 'none');
    this.getComposer().getMenu().find('.compose-activator').each(function(e) {
      scriptJquery(this).css('display', 'none');
    });

    switch($type(this.options.loadingImage)) {
      case 'object':
        break;
      case 'string':
        this.elements.loadingImage = scriptJquery.crtEle('img', {
          'id' : 'compose-' + this.getName() + '-loading-image',
          'class' : 'compose-loading-image',
          'src' : this.options.loadingImage
        });
        break;
      default:
        this.elements.loadingImage = scriptJquery.crtEle('img', {
          'id' : 'compose-' + this.getName() + '-loading-image',
          'class' : 'compose-loading-image',
          'src' : en4.core.staticBaseUrl + 'application/modules/Core/externals/images/loading.gif',
        });
        break;
    }
  }
  this.deactivate = function() {
    if( !this.active ) return;
    this.active = false;
    this.reset();
    this.getComposer().getTray().css('display', 'none');
    this.getComposer().getMenu().css('display', '');
    var submitButtonEl = scriptJquery(this.getComposer().options.submitElement);
    if( submitButtonEl.length) {
      submitButtonEl.css('display', '');
    }
    this.getComposer().getMenu().find('.compose-activator').each(function(e) {
      scriptJquery(this).css('display', '');
    });
    this.getComposer().getMenu().attr('style', '');
    this.getComposer().signalPluginReady(false);
    
    scriptJquery('#compose-menu').next().html('');
  }

  this.ready = function() {
    this.getComposer().signalPluginReady(true);
    this.getComposer().getMenu().css('display', '');

    var submitEl = scriptJquery(this.getComposer().options.submitElement);
    if(submitEl.length){
      submitEl.css('display', '');
    }
  }
  // Utility
  this.makeActivator = function() {
    var self = this;
    if( !this.elements.activator ) {
      this.elements.activator = scriptJquery.crtEle('a', {
        'id' : 'compose-' + this.getName() + '-activator',
        'class' : 'compose-activator buttonlink',
        'href' : 'javascript:void(0);',
      }).html(this._lang(this.options.title)).appendTo(this.getComposer().getMenu()).click(self.activate.bind(self));
    }
  }
  this.makeMenu = function() {
    if(!this.elements.menu) {
      var tray = this.getComposer().getTray();
      this.elements.menu = scriptJquery.crtEle('div', {
        'id' : 'compose-' + this.getName() + '-menu',
        'class' : 'compose-menu'
      }).appendTo(tray);
      this.elements.menuTitle = scriptJquery.crtEle('span', {}).html(this._lang(this.options.title) + ' (').appendTo(this.elements.menu);
      this.elements.menuClose = scriptJquery.crtEle('a', {
        'href' : 'javascript:void(0);',
      }).html(this._lang('cancel')).click(function(e) {
        e.preventDefault();
        this.getComposer().deactivate();
      }.bind(this)).appendTo(this.elements.menuTitle);
      this.elements.menuTitle.append(')');
    }
  }
  this.makeBody = function() {
    if(!this.elements.body || this.elements.body.length) {
      var tray = this.getComposer().getTray();
      this.elements.body = scriptJquery.crtEle('div', {
        'id' : 'compose-' + this.getName() + '-body',
        'class' : 'compose-body'
      }).appendTo(tray);
    }
  }
  this.makeLoading = function(action) {
    if(!this.elements.loading) {
      if( action == 'empty' ) {
        this.elements.body.empty();
      } else if( action == 'hide' ) {
        this.elements.body.children().each(function(e){ scriptJqury(e.target).css('display', 'none')});
      } else if( action == 'invisible' ) {
        this.elements.body.children().each(function(e){ scriptJqury(e.target).css('height', '0px').css('visibility', 'hidden')});
      }

      this.elements.loading = scriptJquery.crtEle('div', {
        'id' : 'compose-' + this.getName() + '-loading',
        'class' : 'compose-loading'
      }).appendTo(this.elements.body);

      var image = this.elements.loadingImage || (scriptJquery.crtEle('img', {
        'id' : 'compose-' + this.getName() + '-loading-image',
        'class' : 'compose-loading-image'
      }));
      
      image.appendTo(this.elements.loading);
      scriptJquery.crtEle('span', {}).html(this._lang('Loading...')).appendTo(this.elements.loading);
    }
  }

  this.makeError = function(message, action) {
    if( !$type(action) ) action = 'empty';
    message = message || 'An error has occurred';
    message = this._lang(message);
    
    this.elements.error = scriptJquery.crtEle('div', {
      'id' : 'compose-' + this.getName() + '-error',
      'class' : 'compose-error',
    }).html(message).appendTo(this.elements.body);
  }

  this.makeFormInputs = function(data) {
    this.ready();
    this.getComposer().getInputArea().empty();
    data.type = this.getName();
    Object.entries(data).forEach(function([key,value]) {
      this.setFormInputValue(key, value);
    }.bind(this));
  }

  this.setFormInputValue = function(key, value) {
    var elName = 'attachmentForm' + key.replace(/\b[a-z]/g, function(match){
      return match.toUpperCase();
    });
    if( !this.elements.has(elName) ) {
      this.elements.set(elName,scriptJquery.crtEle('input', {
        'type' : 'hidden',
        'name' : 'attachment[' + key + ']',
        'value' : value || ''
      }).appendTo(this.getComposer().getInputArea()));
    }
    this.elements.get(elName).val(value);
  }
  this._lang = function() {
    try {
      if( arguments.length < 1 ) {
        return '';
      }
      var string = arguments[0];
      if( $type(this.options.lang) && $type(this.options.lang[string]) ) {
        string = this.options.lang[string];
      }
      if( arguments.length <= 1 ) {
        return string;
      }
      var args = new Array();
      for( var i = 1, l = arguments.length; i < l; i++ ) {
        args.push(arguments[i]);
      }
      return string.vsprintf(args);
    } catch( e ) {
      alert(e);
    }
  } 
  this.initialize(options);
};
})(); // END NAMESPACE
