(function(){
  var Hash = function(object = {}){
  	var proto = {
  		has: Object.prototype.hasOwnProperty,
  		getClean: function(){
  			var clean = {};
  			for (var key in this){
  				if (this.hasOwnProperty(key)) clean[key] = this[key];
  			}
  			return clean;
  		},
  		extend: function(properties){
  			Object.entries((properties || {})).forEach(([key,value])=>{
  				this.set(this, key, value);
  			});
  			return this;
  		},
  		getLength: function(){
  			var length = 0;
  			for (var key in this){
  				if (this.hasOwnProperty(key)) length++;
  			}
  			return length;
  		},
  		erase: function(key){
  			if (this.hasOwnProperty(key)) delete this[key];
  			return this;
  		},
  		get: function(key){
  			return (this.hasOwnProperty(key)) ? this[key] : null;
  		},
  		set: function(key, value){
  			if (!this[key] || this.hasOwnProperty(key)) this[key] = value;
  			return this;
  		},
  		empty: function(){
  			Object.entries(this).forEach(function([key,value]){
  				delete this[key];
  			}, this);
  			return this;
  		},
  	};
  	for (var key in proto) Hash.prototype[key] = proto[key];
    for (var key in object) this[key] = object[key];
    return this;
  }
  this.Hash = Hash;
})();
(function(){
  var serverOffset = 0;
  Date.setServerOffset = function(ts){
    var server = new Date(ts);
    var client = new Date();
    serverOffset = server - client;
  };

  Date.getServerOffset = function() {
    return serverOffset;
  };
})();
$type = function(object){
  var type = typeof (object);
  return (object == null || type == 'null' || type == 'undefined') ? false : type;
}

$time = function() {
  return (new Date()).getTime();
}
String.prototype.vsprintf = function(args) {
  str = this;
  // Check for no params
  if( !args || !args.length )
  {
    return str;
  }
  // Replace params
  var out = '';
  var m;
  var masterIndex = 0;
  var currentIndex;
  var arg;
  var instr;
  var meth;
  var sign;
  while( str.length > 0 )
  {
    // Check for no more expressions
    if( !str.match(/[%]/) )
    {
      out += str;
      break;
    }
    // Remove any preceeding non-expressions
    m = str.match(/^([^%]+?)([%].+)?$/)
    if( m )
    {
      out += m[1];
      str = typeof(m[2]) ? m[2] : '';
      if( str == '' )
      {
        break;
      }
    }
    // Check for escaped %
    if( str.substring(0, 2) == '%%' )
    {
      str = str.substring(2);
      out += '%';
      continue;
    }
    // Proc next params
    m = str.match(/^[%](?:([0-9]+)\x24)?(\x2B)?(\x30|\x27[^$])?(\x2D)?([0-9]+)?(?:\x2E([0-9]+))?([bcdeEfosuxX])/)
    if( m )
    {
      instr = m[7];
      meth = m[6] || false;
      sign = m[2] || false;
      currentIndex = ( m[1] ? m[1] - 1 : masterIndex++ );
      if($type(args[currentIndex]) )
      {
        arg = args[currentIndex];
      }
      else
      {
        throw('Undefined argument for index ' + currentIndex);
      }
      // Make sure passed sane argument type
      switch( typeof(arg) )
      {
        case 'number':
        case 'string':
        case 'boolean':
          // Okay
          break;
        case 'undefined':
          if( arg == null )
          {
            arg = '';
            break;
          }
        default:
          throw('Unknown argument type: ' + typeof(arg));
          break;
      }
      // Now proc instr
      switch( instr )
      {
        // Binary
        case 'b':
          if( typeof(arg) != 'number' ) arg = parseInt(arg);
          arg = arg.toString(2);
          break;
        // Char
        case 'c':
          arg = String.fromCharCode(arg);
          break;
        // Integer
        case 'd':
          arg = parseInt(arg);
          break;
        // Scientific notation
        case 'E':
        case 'e':
          if( typeof(arg) != 'number' ) arg = parseFloat(arg);
          if( meth )
          {
            arg = arg.toExponential(meth);
          }
          else
          {
            arg = arg.toExponential();
          }
          if( instr == 'E' ) arg = arg.toUpperCase();
          break;

        // Unsigned integer
        case 'u':
          arg = Math.abs(parseInt(arg));
          break;

        // Float
        case 'f':
          if( meth )
          {
            arg = parseFloat(arg).toFixed(meth)
          }
          else
          {
            arg = parseFloat(arg);
          }
          break;
        // Octal
        case 'o':
          if( typeof(arg) != 'number' ) arg = parseInt(arg);
          arg = arg.toString(8);
          break;

        // String
        case 's':
          if( typeof(arg) != 'string' ) arg = String(arg);
          if( meth )
          {
            arg = arg.substring(0, meth);
          }
          break;

        // Hex
        case 'x':
        case 'X':
          if( typeof(arg) != 'number' ) arg = parseInt(arg);
          arg = arg.toString(8);
          if( instr == 'X' ) arg = arg.toUpperCase();
          break;
      }

      // Add a sign if requested
      if( (instr == 'd' || instr == 'e' || instr == 'f') && sign && arg > 0 )
      {
        arg = '+' + arg;
      }
      // Do repeating if necessary
      var repeatChar, repeatCount;
      if( m[3] )
      {
        repeatChar = m[3];
      }
      else
      {
        repeatChar = ' ';
      }
      if( m[5] )
      {
        repeatCount = m[5];
      }
      else
      {
        repeatCount = 0;
      }
      repeatCount -= arg.length;

      // Do the repeating
      if( repeatCount > 0 )
      {
        var paddedness = function(str, count)
        {
          var ret = '';
          while( count > 0 )
          {
            ret += str;
            count--;
          }
          return ret;
        }(repeatChar, repeatCount);

        if( m[4] )
        {
          out += arg + paddedness;
        }
        else
        {
          out += paddedness + arg;
        }
      }
      // Just add the string
      else
      {
        out += arg;
      }
      // Remove from str
      str = str.substring(m[0].length);
    }
    else
    {
      throw('Malformed expression in string: ' + str);
    }
  }
  return out;
}

var Cookie = function(key, options){
  defaultOptions = {
    path: '/',
    domain: false,
    duration: false,
    secure: false,
    document: document,
    encode: true
  }
  this.write = function(value){
    if (this.options.encode) value = encodeURIComponent(value);
    if (this.options.domain) value += '; domain=' + this.options.domain;
    if (this.options.path) value += '; path=' + this.options.path;
    if (this.options.duration){
      var date = new Date();
      date.setTime(date.getTime() + this.options.duration * 24 * 60 * 60 * 1000);
      value += '; expires=' + date.toGMTString();
    }
    if (this.options.secure) value += '; secure';
    this.options.document.cookie = this.key + '=' + value;
    return this;
  }

  this.read = function(){
    var value = this.options.document.cookie.match('(?:^|;)\\s*' + this.key.replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1') + '=([^;]*)');
    return (value) ? decodeURIComponent(value[1]) : null;
  }

  this.dispose = function(){
    new Cookie(this.key, scriptJquery.extend(true,{}, this.options, {duration: -1})).write('');
    return this;
  }
  this.key = key;
  this.options = scriptJquery.extend(true,{},defaultOptions,options);
};

Cookie.write = function(key, value, options){
  return new Cookie(key, options).write(value);
};

Cookie.read = function(key){
  return new Cookie(key).read();
};

Cookie.dispose = function(key, options){
  return new Cookie(key, options).dispose();
};

scriptJquery.extend(scriptJquery,{
	crtEle:function(tagName,options){
		function makeInline(options,sub= false){
			let eleContent = '';
			if(typeof options === "object"){
				Object.entries(options).forEach(([key, value])=>{
					if(typeof value === "object"){
						eleContent += `${key}="${makeInline(value,1)}"`;
					} else {
						eleContent += `${key}${sub ? ":" : "="}'${value}'${sub ? ";" : ""}`;
					}
				});
			} else if(typeof options === "string"){
				eleContent = options;
			}
			return eleContent;
		}
		eleContent = makeInline(options);
		return scriptJquery(`<${tagName} ${eleContent}>`);
	}
});

scriptJquery.fn.enableLinks = function(){
  this.each(function(){
    scriptJquery(this).html(scriptJquery.parseHTML(scriptJquery(this).html()));
  });
}


class Occlude{
  constructor(){
  }
  occlude(property, element){
    element = (element || this.element);
    var instance = element.data(property || this.property);
    if (instance && !this.occluded)
      return (this.occluded = instance);

    this.occluded = false;
    element.data(property || this.property, this);
    return this.occluded;
  }

};

class OverText extends Occlude{
  Binds = ['reposition', 'assert', 'focus', 'hide'];
  options = {
    element: 'label',
    labelClass: 'overTxtLabel',
    positionOptions: {
      position: 'upperLeft',
      edge: 'upperLeft',
      offset: {
        x: 4,
        y: 2
      }
    },
    poll: false,
    pollInterval: 250,
    wrap: false
  }
  property = 'OverText';
  constructor(element, options){
    super();
    element = this.element = element;

    if (this.occlude()) return this.occluded;
      this.options = scriptJquery.extend(this.options,options);

    this.attach(element);
    OverText.instances.push(this);
    if (this.options.poll) this.poll();
  }
  toElement(){
    return this.element;
  }

  attach(){
    var element = this.element,
      options = this.options,
      value = options.textOverride || element.attr('alt') || element.attr('title');

    if (!value) return this;

    var text = this.text = scriptJquery.crtEle(options.element, {
      'class': options.labelClass,
    }).css({
        lineHeight: 'normal',
        position: 'absolute',
        cursor: 'text',
        top : 2,
        left : 4
    }).click(this.hide.bind(this,options.element == 'label')).html(value)
    .insertAfter(element);

    if (options.element == 'label'){
      if (!element.attr('id')) element.attr('id', 'input_' + $time().toString(36));
      text.attr('for', element.attr('id'));
    }

    if (options.wrap){
      this.textHolder = scriptJquery.crtEle('div', {
        class: 'overTxtWrapper',
      }).css({
          lineHeight: 'normal',
          position: 'relative'
      }).append(text).insertBefore(element);
    }
    this.element.parent().css("position","relative");
    return this.enable();
  }

  destroy(){
   // this.element.eliminate(this.property); // Class.Occlude storage
    this.disable();
    if (this.text) this.text.remove();
    if (this.textHolder) this.textHolder.remove();
    return this;
  }

  disable(){
    this.element.off({
      focus: this.focus.bind(this),
      blur: this.assert.bind(this),
      input: this.assert.bind(this)
    });
    //scriptJquery(window).off('resize', this.reposition.bind(this));
    this.hide(true, true);
    return this;
  }

  enable(){
    this.element.on({
      focus: this.focus.bind(this),
      blur: this.assert.bind(this),
      input: this.assert.bind(this)
    });
    //scriptJquery(window).off().on('resize', this.reposition.bind(this));
    this.reposition();
    return this;
  }

  wrap(){
    if (this.options.element == 'label'){
      if (!this.element.attr('id')) this.element.attr('id', 'input_' + $time().toString(36));
      this.text.attr('for', this.element.attr('id'));
    }
  }

  startPolling(){
    this.pollingPaused = false;
    return this.poll();
  }

  poll(stop){
    //start immediately
    //pause on focus
    //resumeon blur
    if (this.poller && !stop) return this;
    if (stop){
      clearInterval(this.poller);
    } else {
      this.poller = setInterval(function(){
        if (!this.pollingPaused) this.assert(true);
      }.bind(this),this.options.pollInterval);
    }

    return this;
  }

  stopPolling(){
    this.pollingPaused = true;
    return this.poll(true);
  }

  focus(){
    if (this.text && (!this.text.is(":visible") || this.element.prop('disabled'))) return this;
    return this.hide();
  }

  hide(suppressFocus, force){
    if (this.text && (this.text.is(":visible") && (!this.element.prop('disabled') || force))){
      this.text.hide();
      this.pollingPaused = true;
    }
    return this;
  }

  show(){
    if (this.text && !this.text.is(":visible")){
      this.text.show();
      this.reposition();
      //scriptJquery(this).trigger('textShow',this.text,this.element);
      this.pollingPaused = false;
    }
    return this;
  }

  test(){
    return !this.element.val();
  }

  assert(suppressFocus){
    return this[this.test() ? 'show' : 'hide'](suppressFocus);
  }

  reposition(){
    if (!this.element.is(":visible")) return this.stopPolling().hide();
    if (this.text && this.test()){
      let obj = this.options.positionOptions;
      this.text.css({top:obj.offset.y,left:obj.offset.x});
    }
    return this;
  }
};


function htmlspecialchars_decode (string, quote_style) {
  // Convert special HTML entities back to characters
  //
  // version: 1004.2314
  // discuss at: http://phpjs.org/functions/htmlspecialchars_decode
  // +   original by: Mirek Slugen
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Mateusz "loonquawl" Zalega
  // +      input by: ReverseSyntax
  // +      input by: Slawomir Kaniecki
  // +      input by: Scott Cariss
  // +      input by: Francois
  // +   bugfixed by: Onno Marsman
  // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
  // +      input by: Ratheous
  // +      input by: Mailfaker (http://www.weedem.fr/)
  // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
  // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
  // *     example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');
  // *     returns 1: '<p>this -> &quot;</p>'
  // *     example 2: htmlspecialchars_decode("&amp;quot;");
  // *     returns 2: '&quot;'
  var optTemp = 0, i = 0, noquotes= false;
  if (typeof quote_style === 'undefined') {
    quote_style = 2;
  }
  string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
  var OPTS = {
    'ENT_NOQUOTES': 0,
    'ENT_HTML_QUOTE_SINGLE' : 1,
    'ENT_HTML_QUOTE_DOUBLE' : 2,
    'ENT_COMPAT': 2,
    'ENT_QUOTES': 3,
    'ENT_IGNORE' : 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i=0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      }
      else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
    // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
  }
  if (!noquotes) {
    string = string.replace(/&quot;/g, '"');
  }
  // Put this in last place to avoid escape being double-decoded
  string = string.replace(/&amp;/g, '&');
  
  return string;
}

OverText.instances = [];


// Auto complete / Auto suggest

AutocompleterRequestJSON = function (field_name, url, hiddenFunction, extraParams, customParams) {
  scriptJquery('#'+field_name).parent().addClass('acWrap');
  scriptJquery('#'+field_name).parent().append('<div class="acBox"></div>');
  scriptJquery('#'+field_name).autocomplete({
    source: function (request, response) {
			var extraParamsObj = {};
			if(extraParams) {
				if(typeof extraParams != 'object') {
					extraParams = [extraParams];
				}
				extraParams.forEach(item => {
					extraParamsObj[item] = scriptJquery('#'+item).val();
				});
			}
      scriptJquery.ajax({
        type: "POST",
        url: url,
          data: {
            text: scriptJquery('#'+field_name).val(),
						...extraParamsObj,
            //type: "user-all"
          },
          success: function( data ) {  
            response(data);
          },
          dataType: 'json',
          minLength: 1,
          delay: 500
      });
    },
    select : function(event, ui) {
      var label = ui.item.label;
      scriptJquery('#'+field_name).val(label.replace(/(<([^>]+)>)/ig,""));
      hiddenFunction(ui.item);
      return false;
    }
  }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {
    if(customParams && customParams.class)
    ul.addClass(customParams.class); //Ul custom class here
    if(item.photo) {
      return scriptJquery( "<li></li>" ).data("item.autocomplete", item).append(item.photo + '<span>' + item.label + '</span>').appendTo(ul); 
    } else if(item.icon) {
      return scriptJquery( "<li></li>" ).data("item.autocomplete", item).append(item.icon + '<span>' + item.label + '</span>').appendTo(ul);  
    }
  }
}

//Ajax Smoothbox 

//Prevent javascript error before the content has loaded
var executetimesmoothbox = false;

//Add ajaxsmoothbox to href elements that have a class of .ajaxsmoothbox
AttachEventListerSE('click','.ajaxsmoothbox',function(event){
	event.preventDefault();
	ajaxsmoothboxopen(this);
});

function ajaxsmoothboxopen(obj) {
  
	if(!scriptJquery('.ajaxsmoothbox_main').length) {

    scriptJquery.crtEle('div', {
      'id': 'ajaxsmoothbox_overlay',
      'class': 'ajaxsmoothbox_overlay'
    }).appendTo(document.body);
    
		scriptJquery.crtEle('div', {
      'id': 'ajaxsmoothbox_main',
      'class': 'ajaxsmoothbox_main'
    }).appendTo(document.body);
    
		scriptJquery("#ajaxsmoothbox_main").html('<div class="ajaxsmoothbox_container" id="ajaxsmoothbox_container"><div class="ajaxsmoothbox_loading"></div></div>');
    
    if(scriptJquery(obj).data('addclass')){
      scriptJquery('#ajaxsmoothbox_container').addClass(scriptJquery(obj).data('addclass'));
    }
    loaddefaultcontent();
	}
	// display the box for the elements href
	ajaxsmoothboxshow(obj);
	return false;
}

//esc key close
scriptJquery(document).on('keyup', function (e) {
  if(scriptJquery('#'+e.target.id).prop('tagName') == 'INPUT' || scriptJquery('#'+e.target.id).prop('tagName') == 'TEXTAREA' || !scriptJquery('#ajaxsmoothbox_container').length)
    return true;
  //ESC key close
  if (e.keyCode === 27) {
    ajaxsmoothboxclose();return false; 
  }
});

scriptJquery(document).on('click','.ajaxsmoothbox_main',function(e){
  if (e.target !== this)
    return;
	ajaxsmoothboxclose();
});

function loaddefaultcontent(){
	var htmlElement = document.getElementsByTagName("html")[0];
  htmlElement.style.overflow = 'hidden';
	scriptJquery("#ajaxsmoothbox_container").css({
		left: ((scriptJquery(window).width() - 300 ) / 2) + 'px',
		top: ((scriptJquery(window).height() - 100 ) / 2) + 'px',
		display: "block"
	});	
}

var Ajaxsmoothbox = {
		javascript : [],
		css : [],
}

// called when the user clicks on a ajaxsmoothbox link
function ajaxsmoothboxshow(obj) {
    if(obj){
      //initialize blank array value
      Ajaxsmoothbox.javascript = Array();
      Ajaxsmoothbox.css = [];
      var url = scriptJquery(obj).attr('href');
      if(url == 'javascript:;' || scriptJquery(obj).hasClass('open'))
        url = scriptJquery(obj).attr('data-url');
      var params = scriptJquery(obj).attr('rel');
      var requestSmoothbox = scriptJquery.ajax({
      dataType: 'html',
      url: url,
      method: 'get',
      data: {
        format: 'html',
        params:params,
        typesmoothbox:'ajaxsmoothbox'
      },
      evalScripts: true,
      success: function(responseHTML) {
        executeCssJavascriptFiles(responseHTML);
      }
    });
  }
}

function ajaxsmoothboxExecuteCode(responseHTML,prevWidth){
  if(typeof ajaxsmoothboxcallbackBefore == 'function')
		ajaxsmoothboxcallbackBefore(responseHTML);

	responseHTML = '<a title="'+en4.core.language.translate("Close")+'" class="ajaxsmoothbox_close_btn fas fa-times" href="javascript:;" onclick="javascript:ajaxsmoothboxclose();"></a>'+responseHTML;
	scriptJquery('#ajaxsmoothbox_container').html(responseHTML);	
	//execute code at run once
	if(!executetimesmoothbox){
		executetimesmoothboxTimeinterval = 10;	
	}
	setTimeout(function(){en4.core.runonce.trigger(); }, executetimesmoothboxTimeinterval);
	resizeajaxsmoothbox(prevWidth);
}

function ajaxsmoothboxclose(){
	scriptJquery('.ajaxsmoothbox_main').remove();
	scriptJquery('#ajaxsmoothbox_overlay').remove();
	var htmlElement = document.getElementsByTagName("html")[0];
	htmlElement.style.overflow = '';
	executetimesmoothbox = false;
  ajaxsmoothboxcallback = function () {};
  ajaxsmoothboxcallbackBefore = function () {};
  if(typeof ajaxsmoothboxcallbackclose == 'function')
		ajaxsmoothboxcallbackclose();
}

function resizeajaxsmoothbox(prevWidth) {

 var linkClose = '<a title="'+en4.core.language.translate("Close")+'" class="ajaxsmoothbox_close_btn fas fa-times" href="javascript:;" onclick="javascript:ajaxsmoothboxclose();"></a>';
 scriptJquery('#ajaxsmoothbox_container').prepend(linkClose);
 var windowheight = scriptJquery(window).height();
 var objHeight =	scriptJquery('#ajaxsmoothbox_container').height();
 var windowwidth= scriptJquery(window).width();
 var objWidth=	scriptJquery('#ajaxsmoothbox_container').width();
 if(objHeight >= windowheight){
  var top = '10'; 
 } else if(objHeight <= windowheight){
  var top = (windowheight - objHeight)/2;		 
 }
 var width = scriptJquery('#ajaxsmoothbox_container').find('div').first().width();
 var	setwidth= width /2 ;
 scriptJquery("#ajaxsmoothbox_container").animate({
		top: top+'px',
		width: width+'px',
		left: (((scriptJquery(window).width() ) / 2) - setwidth) + 'px',
 },0,function() {
    if(typeof ajaxsmoothboxcallback == 'function')
		  ajaxsmoothboxcallback();
    // Animation complete.
  });
}

var successLoad;
function executeCssJavascriptFiles(responseHTML) {
	var jsCount = Ajaxsmoothbox.javascript.length;
	var cssCount = Ajaxsmoothbox.css.length;
	//store the total file so we execute all required function after css and js load.
	var totalFiles = jsCount + cssCount;
	successLoad= 0;
	var isLoaded = 0;
	var prevWidth = scriptJquery('#ajaxsmoothbox_container').width();
	if(jsCount == cssCount){
		isLoaded = 1;
		ajaxsmoothboxExecuteCode(responseHTML,prevWidth);
	}
	//execute jsvascript files
	for(var i=0;i < jsCount;i++){
			Asset.javascript(Ajaxsmoothbox.javascript[i], {
			onLoad: function(e) {
				successLoad++;
				if (successLoad === totalFiles){
				    isLoaded = 1;
					ajaxsmoothboxExecuteCode(responseHTML,prevWidth);
				}
			}});
	}
		//execute css files
	for(var i=0;i < cssCount;i++){
			Asset.css(Ajaxsmoothbox.css[i], {
			onLoad: function() {
				successLoad++;
				if (successLoad === totalFiles){
				    isLoaded = 1;
					ajaxsmoothboxExecuteCode(responseHTML,prevWidth);
				}
			}});
	}
	if(!isLoaded){
	    ajaxsmoothboxExecuteCode(responseHTML,prevWidth);
	}
}


function ajaxsmoothboxDialoge(html) {  
	if(!scriptJquery('.ajaxsmoothbox_main').length){
    scriptJquery.crtEle('div', {
      'id': 'ajaxsmoothbox_overlay',
      'class': 'ajaxsmoothbox_overlay'
    }).appendTo(document.body);
    
    scriptJquery.crtEle('div', {
      'id': 'ajaxsmoothbox_main',
      'class': 'ajaxsmoothbox_main'
    }).appendTo(document.body);

		document.getElementById("ajaxsmoothbox_main").innerHTML = '<div class="ajaxsmoothbox_container" id="ajaxsmoothbox_container"><div class="sesbasic_loading_container"></div></div>';
    loaddefaultcontent();
    executeCssJavascriptFiles("<div class='sesbasic_smoothbox_view_number'><div class='_header'>"+en4.core.language.translate("Phone Number")+"</div><div class='_cont'>" + html + "</div></div>");
	}
	// display the box for the elements href
	return false;
}

//Ajax Smoothbox 


/* Modifided script from the simple-page-ordering plugin */
var ajaxurl;
var type_id;
scriptJquery(function($) {
  scriptJquery('table.widefat.admin_table_order tbody th, table.admin_table_order tbody tr').css('cursor','move');
  scriptJquery("table.admin_table_order").sortable({
		items: 'tbody tr:not(.inline-edit-row)',
		cursor: 'move',
		axis: 'y',
		forcePlaceholderSize: true,
		helper: function (e, item) {
			return item.clone();
		},
		opacity: .3,
		placeholder: 'product-cat-placeholder',
		scrollSensitivity: 40,
		start: function(event, ui) {
			ui.placeholder.html(ui.item.html());
			if ( ! ui.item.hasClass('alternate') ) ui.item.css( 'background-color', '#ffffff' );
			ui.item.children('td,th').css('border-bottom-width','0');
			ui.item.css( 'outline', '1px solid #aaa' );
		},
		stop: function(event, ui) {
			ui.item.removeAttr('style');
			ui.item.css('cursor','move');
			ui.item.children('td,th').css('border-bottom-width','1px');
		},
		update: function(event, ui) {
			$('table.admin_table_order tbody th, table.widefat tbody td').css('cursor','default');
			//$("table.admin_table_order tbody").sortable('disable');
			var termid = ui.item.find('.check-column').val();	// this post id
			var termparent = ui.item.find('.parent').html(); 	// post parent

			var prevtermid = ui.item.prev().find('.check-column').val();
			var nexttermid = ui.item.next().find('.check-column').val();
			
			// can only sort in same tree
			var prevtermparent = undefined;
			if ( prevtermid != undefined ) {
				var prevtermparent = ui.item.prev().find('.parent').html();
				if ( prevtermparent != termparent) prevtermid = undefined;
			}

			var nexttermparent = undefined;
			if ( nexttermid != undefined ) {
				nexttermparent = ui.item.next().find('.parent').html();
				if ( nexttermparent != termparent) nexttermid = undefined;
			}
			// if previous and next not at same tree level, or next not at same tree level and the previous is the parent of the next, or just moved item beneath its own children
			if ( ( prevtermid == undefined && nexttermid == undefined ) || ( nexttermid == undefined && nexttermparent == prevtermid ) || ( nexttermid != undefined && prevtermparent == termid ) ) {
				$("table.admin_table_order").sortable('cancel');
				return;
			}
			var categoryorder = "";
			scriptJquery(".ui-sortable tbody tr").each(function(i) {
        if (categoryorder=='')
          categoryorder = scriptJquery(this).attr('data-id');
        else
          categoryorder += "," + scriptJquery(this).attr('data-id');
      });
			// show spinner
      var imageURL = en4.core.baseUrl+"application/modules/Core/externals/images/large-loading.gif";
			ui.item.find('.check-column').hide().after('<img alt="processing" src="'+imageURL+'" class="waiting" style="margin-left: 6px;" />');
			// go do the sorting stuff via ajax
      $.post( ajaxurl, {id: termid, nextid: nexttermid,categoryorder:categoryorder, type_id: type_id}, function(response){
        scriptJquery('table.admin_table_order tbody th, table.admin_table_order tbody td').css('cursor','move');
				//$("table.admin_table_order tbody").sortable('enable');
				if ( response == 'children' ) window.location.reload();
				else {
					ui.item.find('.check-column').show().siblings('img').remove();
				}
			});
			// fix cell colors
      scriptJquery( 'table.admin_table_order tbody tr' ).each(function(){
				 scriptJquery(this).css('cursor','move');
			});
		}
	});

});
