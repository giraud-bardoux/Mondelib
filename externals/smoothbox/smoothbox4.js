
(function(){
  this.Smoothbox = {
  instance : false,
  bind : function(selector)
  {
    // All children of element
    var elements;
    if( $type(selector) == 'element' ){
      elements = selector.find('a.smoothbox');
    } else if( $type(selector) == 'string' ){
      elements = scriptJquery(selector);
    } else {
      elements = scriptJquery("a.smoothbox");
    }
    elements.each(function(el)
    {
      if( scriptJquery(this).prop("tagName") != 'A' || typeof scriptJquery(this).data('smoothboxed') !=="undefined")
      {
        return;
      }
      var params = {};
      params.title = scriptJquery(this).attr('title');
      params.url = scriptJquery(this).attr('href');
      scriptJquery(this).data('smoothbox', params);
      scriptJquery(this).data('smoothboxed', true);
      scriptJquery(this).on('click', function(event)
      {
        if(scriptJquery(this).attr('href') == 'javascript:;' || scriptJquery(this).attr('href') == 'javascript:void(0)' || scriptJquery(this).attr('href') == 'javascript:void(0);')
          return;
        event.preventDefault(); // Maybe move this to after next line when done debugging
        Smoothbox.open(scriptJquery(this));
      });
    });
  },
  close : function()
  {
    if( this.instance )
    {
      this.instance.close();
      scriptJquery('html').removeClass('overflow-hidden');
      scriptJquery('body').removeClass('overflow-hidden');
    }
  },
  open : function(spec, options)
  {
    if(this.instance )
    {
      this.instance.close();
    } 
    // Check the options array
    if( $type(options) == 'object' ) {
      options = new Hash(options);
    } else if( $type(options) != 'hash' ) {
      options = new Hash();
    }
    // Check the arguments
    // Spec as element
    if( $type(spec) == 'object' && Object.getPrototypeOf(spec) === scriptJquery.prototype) {
      // This is a link
      if(spec.prop("tagName").toLowerCase() == 'a' ) {
        spec = new Hash({
          'mode' : 'Iframe',
          'link' : spec,
          'element' : spec,
          'url' : spec.attr('href'),
          'title' : spec.attr('title')
        });
      }
      // This is some other element
      else {
        spec = new Hash({
          'mode' : 'Inline',
          'title' : spec.attr('title'),
          'element' : spec
        });
      }
    }
    // Spec as string
    else if( $type(spec) == 'string' ) {
      // Spec is url
      if( spec.length < 4000 && (spec.substring(0, 1) == '/' ||
          spec.substring(0, 1) == '.' ||
          spec.substring(0, 4) == 'http' ||
          !spec.match(/[ <>"'{}|^~\[\]`]/)
        )
      ) {
        spec = new Hash({
          'mode' : 'Iframe',
          'url' : spec
        });
      }
      // Spec is a string
      else {
        spec = new Hash({
          'mode' : 'String',
          'bodyText' : spec
        });
      }
    }
    // Spec as object or hash
    else if( $type(spec) == 'object' || $type(spec) == 'hash' ) {
      // Don't do anything?
    }
    // Unknown spec
    else {
      spec = new Hash();
    }
    // Now lets start the fun stuff
    spec.extend(options);
    var mode = spec.get('mode');
    spec.erase('mode');
    if( !mode ) {
      if( spec.has('url') ) {
        //if( spec.get('url').match(/\.(jpe?g|png|gif|bmp)/gi) ) {
          //mode = 'Image';
        //} else {
          mode = 'Iframe';
        //}
      }
      else if( spec.has('element') ) {
        mode = 'Inline';
      }
      else if( spec.has('bodyText') ) {
        mode = 'String';
      }
      else {
        return;
      }
    }
    if( !$type(Smoothbox.Modal[mode]) )
    {
      //mode = 'Iframe';
      return;
    }
    this.instance = new Smoothbox.Modal[mode](spec.getClean());
    this.instance.load(spec);
  }
};

class Modal {
  options =  {
    url : null,
    width : 480,
    height : 320,

    // Do or do not
    transitions : false,
    overlay : true,
    loading : true,
    
    noOverlayClose : false,

    autoResize : true,
    autoFormat : 'smoothbox'

    //useFixed : false
  }

  eventProto = {};

  overlay = false;

  window = false;

  content = false;

  loading = false;
  constructor(options)
  {
    scriptJquery.extend(this.options,options);
    if($type(this.options.url)){
      this.options.url = this.getAbsoluteURL(this.options.url);
    }
  }
  getAbsoluteURL(url){
    let urlObj = new URL(window.location.href);
    if(url.indexOf(urlObj.host) === -1){
      return urlObj.origin+url;
    }  
    return url;    
  }
  close()
  {
    this.onClose();

    if(this.window){
      window.removeEventListener('scroll', this.eventProto.scroll);
      window.removeEventListener('resize', this.eventProto.resize);
    }
    if( this.window ) this.window.remove();
    if( this.overlay ) this.overlay.remove();
    if( this.loading ) this.loading.remove();
    Smoothbox.instance = false;
  }

  load()
  {
    this.create();
    
    // Add Events
    var bind = this;
    this.eventProto.resize = function() {
      bind.positionOverlay();
      bind.positionWindow();
    }

    this.eventProto.scroll = function()
    {
      bind.positionOverlay();
      bind.positionWindow();
    };

    window.addEventListener('resize', this.eventProto.resize);
    window.addEventListener('scroll', this.eventProto.scroll);

    this.position();
    this.showOverlay();
    this.showLoading();
  }

  create()
  {
    this.createOverlay();
    this.createLoading();
    this.createWindow();
  }

  createLoading()
  {
    if( this.loading || !this.options.loading ) {
      return;
    }

    var bind = this;
    
    this.loading = scriptJquery.crtEle('div', {
      id : 'TB_load'
    });
    this.loading.appendTo(document.body);

    var loadingImg = scriptJquery.crtEle('img', {
      src : 'externals/smoothbox/loading.gif' // @todo Move to CSS
    });
    loadingImg.appendTo(this.loading);
  }

  createOverlay()
  {
    if( this.overlay || !this.options.overlay ) {
      return;
    }
    var bind = this;
    this.overlay = scriptJquery.crtEle('div', {
      'id' : 'TB_overlay',
      'style':`'position'='absolute';'top'='0px';'left'='0px';'visibility'='visible`,
      'opacity' : 0
    });
    this.overlay.appendTo(document.body);

    if( !this.options.noOverlayClose ) {
      this.overlay.on('click', function() {
        bind.close();
        scriptJquery('html').removeClass('overflow-hidden');
        scriptJquery('body').removeClass('overflow-hidden');
      }.bind(bind));
    }
  }

  createWindow()
  {
    if( this.window ) {
      return;
    }

    var bind = this;
    
    this.window = scriptJquery.crtEle('div', {
      'id' : 'TB_window',
    }).css('opacity',0);
    this.window.appendTo(scriptJquery(document.body));

    var title = scriptJquery.crtEle('div', {
      id : 'TB_title'
    });
    title.appendTo(this.window);

    var titleText = scriptJquery.crtEle('div', {
      id : 'TB_ajaxWindowTitle',
      html : this.options.title
    });
    titleText.appendTo(title);

    var titleClose = scriptJquery.crtEle('div', {
      id : 'TB_closeAjaxWindow',
    });
    titleClose.on("click",function() {
         bind.close();
    });
    titleClose.appendTo(title);

    var titleCloseLink = scriptJquery.crtEle('a',{
      id : 'TB_title',
      href : 'javascript:void(0);',
      title : 'close',
      html : 'close',
    })
    titleCloseLink.on("click",function() {
         bind.close();
    });
    titleCloseLink.appendTo(titleClose);
  }

  position()
  {
    this.positionOverlay();
    this.positionWindow();
    this.positionLoading();
  }

  positionLoading()
  {
    if(!this.loading)
    {
      return;
    }
    this.loading.css({
        "left": (this.getScroll(window).x + (this.getSize().x - 56) / 2) + 'px',
        "top": (this.getScroll(window).y + ((this.getSize().y - 20) / 2)) + 'px',
        "display": "block"
    });
  }

  positionOverlay()
  {
    if( !this.overlay )
    {
      return;
    }
    this.overlay.css({
        "height" : '0px',
        "width" : '0px'
    });
    
    if( !this.options.noOverlay )
    {
      this.overlay.css({
          "height" : this.getScrollSize().y + 'px',
          "width" : this.getScrollSize().x + 'px'
      }); 
    }
  }

  positionWindow()
  {
    if( !this.window ) {
      return;
    }
    this.window.css({
      "width" : this.options.width + 'px',
      "left" : (this.getScroll(window).x + (this.getSize().x - this.options.width) / 2) + 'px',
      "top" : (this.getScroll(window).y + (this.getSize().y - this.options.height) / 2) + 'px'
    });
  }

  show()
  {
    this.showOverlay();
    this.showLoading();
    this.showWindow();
  }

  showLoading()
  {
    if( !this.loading )
    {
      return;
    }

    if( this.options.transitions )
    {
      //this.loading.tween('opacity', [0, 1]);
    }
    else
    {
      this.loading.css('opacity', 1);
      this.loading.css('visibility', 'visible');
    }
  }
  
  showOverlay()
  {
    if( !this.overlay ) {
      return;
    }

    // if( Browser.Engine.trident /*&& this.overlay.style.visibility == 'hidden'*/ ){
    //   //this.overlay.style.visibility = 'visible';
    //   this.overlay.style.display = '';
    // }

    if( this.options.transitions )
    {
      //this.overlay.tween('opacity', [0, 0.6]);
    }
    else
    {
      this.overlay.css('opacity', 0.6);
      this.overlay.css('visibility', 'visible');
    }
  }

  showWindow()
  {
    if( !this.window )
    {
      return;
    }

    // if( Browser.Engine.trident /* && this.window.style.visibility == 'hidden'*/ ){
    //   //this.window.style.visibility = 'visible';
    //   this.window.style.display = '';
    // }
    // Try to autoresize the window
    if( typeof(this.doAutoResize) == 'function' )
    {
      this.doAutoResize();
    }

    if(this.options.transitions ) {
      //this.window.tween('opacity', [0, 1]);
    } else {
      this.window.css('opacity', 1);
      this.window.css('visibility', 'visible');
    }
  }

  hide()
  {
    this.hideLoading();
    this.hideOverlay();
    this.hideWindow();
  }

  hideLoading()
  {
    if( !this.loading ) {
      return;
    }

    if( this.options.transitions ) {
      //this.loading.tween('opacity', [1, 0]);
    } else {
      this.loading.css('opacity', 0);
    }
  }

  hideOverlay()
  {
    if( !this.overlay )
    {
      return;
    }
    
    if( this.options.transitions ) {
      //this.overlay.tween('opacity', [0.6, 0]);
    } else {
      this.overlay.css('opacity', 0);
    }
  }

  hideWindow()
  {
    if( !this.window )
    {
      return;
    }
    
    if( this.options.transitions ) {
     /* var bind = this;
      this.window.tween('opacity', [1, 0]);
      this.window.get('tween').addEventListener('complete', function() {
        bind.fireEvent('closeafter');
      }); */
    }
    else
    {
      this.window.css('opacity', 0);
    }
  }

  getCoordinates(element){
    return {
      x : element["clientWidth"],
      y : element["clientHeight"]
    }
  }
  getScrollSize(element){
    return {x: Math.max(
        document.body.scrollWidth, document.documentElement.scrollWidth,
        document.body.offsetWidth, document.documentElement.offsetWidth,
        document.body.clientWidth, document.documentElement.clientWidth
      ),
      y: Math.max(
      document.body.scrollHeight, document.documentElement.scrollHeight,
      document.body.offsetHeight, document.documentElement.offsetHeight,
      document.body.clientHeight, document.documentElement.clientHeight
    )};
  }
  getSize(){
    return {x:document.documentElement.clientWidth,y:document.documentElement.clientHeight};
  }
  getScroll(n){
    let m = scriptJquery(n)
    return {x:n.pageXOffset|| m.scrollLeft(),y:n.pageYOffset||m.scrollTop()};
  }
  doAutoResize(element)
  {
    if( !element || !this.options.autoResize )
    {
      return;
    }

    var size = ({x:element.width(),y:element.height()} || this.getScrollSize(element)); 
    var winSize = this.getCoordinates(document.documentElement);
    if( size.x + 70 > winSize.x ) size.x = winSize.x - 70;
    if( size.y + 70 > winSize.y ) size.y = winSize.y - 70;

    this.content.css({
      'width' : (size.x + 20) + 'px',
      'height' : (size.y + 20) + 'px'
    });

    this.options.width = this.content.width();
    this.options.height = this.content.height();
    this.positionWindow();
  }
  // events
  onLoad()
  {
    //this.fireEvent('load', this);
  }

  onOpen()
  {
    //this.fireEvent('open', this);
  }

  onClose()
  {
    //this.fireEvent('close', this);
  }

  onCloseAfter()
  {
    //this.fireEvent('closeafter', this);
  }
}
Smoothbox.Modal = Modal;

class Iframe extends Modal{
  constructor(options){
    super(options);
  }
  load()
  {
    super.load();
    if( this.content ) {
      return;
    }
    var bind = this;
    var loadIsOkay = true;
    var uriSrc = new URL(this.options.url);
    if( this.options.autoFormat ) {
      uriSrc.searchParams.set('format',this.options.autoFormat);
    }
    this.content = scriptJquery.crtEle('iframe',{
      src : uriSrc.toString(),
      id : 'TB_iframeContent',
      name : 'TB_iframeContent',
      frameborder : '0',
      width : this.options.width,
      height : this.options.height,
    });
    this.content.load(function() {
      if( loadIsOkay ) {
        loadIsOkay = false;
        this.hideLoading();
        this.showWindow();
        this.onLoad();
      } else {
        this.doAutoResize();
      }
      if(!scriptJquery('body').hasClass('admin')) {
        scriptJquery('html').addClass('overflow-hidden');
        scriptJquery('body').addClass('overflow-hidden');
      }
    }.bind(bind));
    this.content.appendTo(this.window);
  }
  doAutoResize()
  {
    if(!this.options.autoResize ) {
      return;
    }
    // Check if from same host
    var iframe = this.content;
    var host = (new URL(iframe.attr("src"))).host;
  
    if( !host || host != window.location.host ) {
      return;
    }
    // Try to get element
    if( this.options.autoResize == true ) {
      var element = iframe.contents().find('body').children().eq(0) || iframe.contents().find('body')
       || iframe.contents()[0].documentElement;
      return super.doAutoResize( element );
    }
    else if( $type(this.options.autoResize) == 'element' )
    {
      return super.doAutoResize(this.options.autoResize);
    }
  }
}
Smoothbox.Modal.Iframe = Iframe;
class Inline extends Modal{
  Extends = Smoothbox.Modal
  element = false;
  cloneElement = false;
  load(spec)
  {
    if( this.content )
    {
      return;
    }
    super.load();
    this.content = scriptJquery.crtEle('div', {
      id : 'TB_ajaxContent',
      width : this.options.width,
      height : this.options.height
    });
    this.content.appendTo(this.window);
    this.cloneElement = scriptJquery(spec.element);
    scriptJquery(this.cloneElement).appendTo(this.content);
    // scriptJquery(this.content).append(spec);
        
    this.hideLoading();
   this.showWindow();
    this.onLoad();
  }
  setOptions(options)
  {
    this.element = scriptJquery(options.element);
    this.parent(options);
  }
  doAutoResize()
  {
    super.doAutoResize(this.cloneElement);
  } 
}
Smoothbox.Modal.Inline = Inline;
class Modal_String extends Modal{
  constructor(options){
    super(options);
    this.load();
  }
  load()
  {
    if( this.content )
    {
      return;
    }
    super.load();
    this.content = scriptJquery.crtEle('div', {
      id : 'TB_ajaxContent',
      width : this.options.width,
      height : this.options.height,
    }).html('<div>' + this.options.bodyText + '</div>');
    this.content.appendTo(this.window);
    
    this.hideLoading();
    this.showWindow();
    this.onLoad();
  }
  doAutoResize()
  {
    if( !this.options.autoResize )
    {
      return;
    }
    var bind = this;
    var element = bind.content.children().eq(0);
    return super.doAutoResize( element );
  } 
}
Smoothbox.Modal.String = Modal_String;

window.addEventListener('DOMContentLoaded', function()
{
  Smoothbox.bind();
})

window.addEventListener('load', function()
{
  Smoothbox.bind();
})
})();

