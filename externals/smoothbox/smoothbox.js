/*
 * Smoothbox v20080623 by Boris Popoff (http://gueschla.com)
 * To be used with mootools 1.2
 *
 * Based on Cody Lindley's Thickbox, MIT License
 *
 * Licensed under the MIT License:
 *   http://www.opensource.org/licenses/mit-license.php
 */


//THIS IS A LEGACY FILE.  PLEASE USE smoothbox4.js INSTEAD

// on page load call TB_init
window.addEventListener('DOMContentLoaded', TB_init);

// prevent javascript error before the content has loaded
TB_WIDTH = 0;
TB_HEIGHT = 0;
var TB_doneOnce = 0;

// add smoothbox to href elements that have a class of .smoothbox
function TB_init(){
    scriptJquery("a.smoothbox").each(function(el){
        el.onclick = TB_bind
    });
}

function TB_bind(event){
    var event = new Event(event);
    // stop default behaviour
    event.preventDefault();
    // remove click border
    this.blur();
    // get caption: either title or name attribute
    var caption = this.title || this.name || "";
    // get rel attribute for image groups
    var group = this.rel || false;
    // display the box for the elements href
    TB_show(caption, this.href, group, this);
    this.onclick = TB_bind;
    return false;
}

// called when the user clicks on a smoothbox link
function TB_show(caption, url, rel, orginalEl)
{

    // create iframe, overlay and box if non-existent
    if (!scriptJquery("TB_overlay")) {
        scriptJquery('iframe').attr('id', 'TB_HideSelect').(scriptJquery(document.body));
        scriptJquery('TB_HideSelect').css({ opacity: 0 });
        scriptJquery('div').attr('id', 'TB_overlay').appendTo(scriptJquery(document.body));
        scriptJquery('TB_overlay').css({ opacity: 0 });
        TB_overlaySize();
        scriptJquery('div').attr('id', 'TB_load').appendTo(scriptJquery(document.body));
//        scriptJquery('TB_load').innerHTML = "<img src='./images/smoothbox_loading.gif' />";
        TB_load_position();
        
       /* scriptJquery('TB_overlay').set('tween', {
            duration: 0
        });
        scriptJquery('TB_overlay').tween('opacity', 0, 0.6);
        */
    }
    
    if (!scriptJquery("TB_load")) {
        scriptJquery('div').attr('id', 'TB_load').appendTo(scriptJquery(document.body));
        TB_load_position();
    }
    
    if (!scriptJquery("TB_window")) {
        scriptJquery('div').attr('id', 'TB_window').appendTo(scriptJquery(document.body));
        scriptJquery('TB_window').css({ opacity: 0 });
    }
    
    scriptJquery("TB_overlay").onclick = TB_remove;
    window.onscroll = TB_position;
    
    // check if a query string is involved
    var baseURL = url.match(/(.+)?/)[1] || url;
    
    // regex to check if a href refers to an image
    var imageURL = /\.(jpe?g|png|gif|bmp)/gi;
    
    // check for images
    if (baseURL.match(imageURL)) {
        var dummy = {
            caption: "",
            url: "",
            html: ""
        };
        
        var prev = dummy, next = dummy, imageCount = "";
        
        // if an image group is given
        if (rel) {
            function getInfo(image, id, label){
                return {
                    caption: image.title,
                    url: image.href,
                    html: "<span id='TB_" + id + "'>&nbsp;&nbsp;<a href='#'>" + label + "</a></span>"
                }
            }
            
            // find the anchors that point to the group
            var imageGroup = [];
            scriptJquery("a.smoothbox").each(function(el){
                if (el.rel == rel) {
                    imageGroup[imageGroup.length] = el;
                }
            })
            
            var foundSelf = false;
            
            // loop through the anchors, looking for ourself, saving information about previous and next image
            for (var i = 0; i < imageGroup.length; i++) {
                var image = imageGroup[i];
                var urlTypeTemp = image.href.match(imageURL);
                
                // look for ourself
                if (image.href == url) {
                    foundSelf = true;
                    imageCount = "Image " + (i + 1) + " of " + (imageGroup.length);
                }
                else {
                    // when we found ourself, the current is the next image
                    if (foundSelf) {
                        next = getInfo(image, "next", "Next &gt;");
                        // stop searching
                        break;
                    }
                    else {
                        // didn't find ourself yet, so this may be the one before ourself
                        prev = getInfo(image, "prev", "&lt; Prev");
                    }
                }
            }
        }
        
        imgPreloader = new Image();
        imgPreloader.onload = function(){
            imgPreloader.onload = null;
            
            // Resizing large images
            var x = window.getWidth() - 150;
            var y = window.getHeight() - 150;
            var imageWidth = imgPreloader.width;
            var imageHeight = imgPreloader.height;
            if (imageWidth > x) {
                imageHeight = imageHeight * (x / imageWidth);
                imageWidth = x;
                if (imageHeight > y) {
                    imageWidth = imageWidth * (y / imageHeight);
                    imageHeight = y;
                }
            }
            else 
                if (imageHeight > y) {
                    imageWidth = imageWidth * (y / imageHeight);
                    imageHeight = y;
                    if (imageWidth > x) {
                        imageHeight = imageHeight * (x / imageWidth);
                        imageWidth = x;
                    }
                }
            // End Resizing
            
            // TODO don't use globals
            TB_WIDTH = imageWidth + 30;
            TB_HEIGHT = imageHeight + 60;
            
            // TODO empty window content instead
            scriptJquery("TB_window").innerHTML += "<a href='' id='TB_ImageOff' title='Close'><img id='TB_Image' src='" + url + "' width='" + imageWidth + "' height='" + imageHeight + "' alt='" + caption + "'/></a>" + "<div id='TB_caption'>" + caption + "<div id='TB_secondLine'>" + imageCount + prev.html + next.html + "</div></div><div id='TB_closeWindow'><a href='#' id='TB_closeWindowButton' title='Close'>close</a></div>";
            
            scriptJquery("TB_closeWindowButton").onclick = TB_remove;
            
            function buildClickHandler(image){
                return function(){
                    scriptJquery("TB_window").dispose();
                    new Element('div').setProperty('id', 'TB_window').injectInside(document.body);
                    
                    TB_show(image.caption, image.url, rel);
                    return false;
                };
            }
            var goPrev = buildClickHandler(prev);
            var goNext = buildClickHandler(next);
            if (scriptJquery('TB_prev')) {
                scriptJquery("TB_prev").onclick = goPrev;
            }
            
            if (scriptJquery('TB_next')) {
                scriptJquery("TB_next").onclick = goNext;
            }
            
            document.onkeydown = function(event){
                var event = new Event(event);
                switch (event.code) {
                    case 27:
                        TB_remove();
                        break;
                    case 190:
                        if (scriptJquery('TB_next')) {
                            document.onkeydown = null;
                            goNext();
                        }
                        break;
                    case 188:
                        if (scriptJquery('TB_prev')) {
                            document.onkeydown = null;
                            goPrev();
                        }
                        break;
                }
            }
            
            // TODO don't remove loader etc., just hide and show later
            scriptJquery("TB_ImageOff").onclick = TB_remove;
            TB_position();
            TB_showWindow();
        }
        imgPreloader.src = url;
        
    }
    else { //code to show html pages
        var queryString = url.match(/\?(.+)/);
        var params;
        
        if( queryString !== null && typeof(queryString[1]) )
        {
          params = TB_parseQuery(queryString[1]);
        }
        
        else
        {
          params = {
            'height' : 300,
            'width' : 400
          };
          url += 'TB_iframe';
        }

        TB_WIDTH = (params['width'] * 1) + 30;
        TB_HEIGHT = (params['height'] * 1) + 40;
        
        var ajaxContentW = TB_WIDTH - 30, ajaxContentH = TB_HEIGHT - 45;
        
        if (url.indexOf('TB_iframe') != -1) {
            urlNoQuery = url.split('TB_');
            scriptJquery("TB_window").innerHTML += "<div id='TB_title'><div id='TB_ajaxWindowTitle'>" + caption + "</div><div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton' title='Close'>close</a></div></div><iframe frameborder='0' hspace='0' src='" + urlNoQuery[0] + "' id='TB_iframeContent' name='TB_iframeContent' style='width:" + (ajaxContentW + 29) + "px;height:" + (ajaxContentH + 17) + "px;' onload='TB_showWindow()'> </iframe>";
        }
        else {
            scriptJquery("TB_window").innerHTML += "<div id='TB_title'><div id='TB_ajaxWindowTitle'>" + caption + "</div><div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton'>close</a></div></div><div id='TB_ajaxContent' style='width:" + ajaxContentW + "px;height:" + ajaxContentH + "px;'></div>";
        }
        
        scriptJquery("TB_closeWindowButton").onclick = TB_remove;
        
        if (url.indexOf('TB_inline') != -1) {
            scriptJquery("TB_ajaxContent").innerHTML = (scriptJquery(params['inlineId']).innerHTML);
            TB_position();
            TB_showWindow();
        }
        else 
            if (url.indexOf('TB_iframe') != -1) {
                TB_position();
                if (frames['TB_iframeContent'] == undefined) {//be nice to safari
                    scriptJquery(document).keyup(function(e){
                        var key = e.keyCode;
                        if (key == 27) {
                            TB_remove()
                        }
                    });
                    TB_showWindow();
                }
            }
            else {
                var handlerFunc = function(){
                    TB_position();
                    TB_showWindow();
                };

				new Request.HTML({
                    method: 'get',
                    update: scriptJquery("TB_ajaxContent"),
                    onComplete: handlerFunc
                }).get(url);
            }
    }
    
    window.onresize = function(){
        TB_position();
        TB_load_position();
        TB_overlaySize();
    }
    
    document.onkeyup = function(event){
        var event = new Event(event);
        if (event.code == 27) { // close
            TB_remove();
        }
    }
    
}

//helper functions below

function TB_showWindow(){
    //scriptJquery("TB_load").dispose();
    //scriptJquery("TB_window").setStyles({display:"block",opacity:'0'});
    
    if (TB_doneOnce == 0) {
        TB_doneOnce = 1;
        
        scriptJquery('TB_window').set('tween', {
            duration: 0,
            onComplete: function(){
                if (scriptJquery('TB_load')) {
                    scriptJquery('TB_load').dispose();
                }
            }
        });
        scriptJquery('TB_window').tween('opacity', 0, 1);
        
    }
    else {
        scriptJquery('TB_window').setStyle('opacity', 1);
        if (scriptJquery('TB_load')) {
            scriptJquery('TB_load').dispose();
        }
    }
}

function TB_remove(){
    scriptJquery("TB_overlay").onclick = null;
    document.onkeyup = null;
    document.onkeydown = null;
    
    if (scriptJquery('TB_imageOff')) 
        scriptJquery("TB_imageOff").onclick = null;
    if (scriptJquery('TB_closeWindowButton')) 
        scriptJquery("TB_closeWindowButton").onclick = null;
    if (scriptJquery('TB_prev')) {
        scriptJquery("TB_prev").onclick = null;
    }
    if (scriptJquery('TB_next')) {
        scriptJquery("TB_next").onclick = null;
    }
    
    
    scriptJquery('TB_window').set('tween', {
        duration: 0,
        onComplete: function(){
            scriptJquery('TB_window').dispose();
        }
    });
    scriptJquery('TB_window').tween('opacity', 1, 0);
    
    
    
    scriptJquery('TB_overlay').set('tween', {
        duration: 0,
        onComplete: function(){
            scriptJquery('TB_overlay').dispose();
        }
    });
    scriptJquery('TB_overlay').tween('opacity', 0.6, 0);
    
    window.onscroll = null;
    window.onresize = null;
    
    scriptJquery('TB_HideSelect').dispose();
    TB_init();
    TB_doneOnce = 0;
    return false;
}

function TB_position(){
    scriptJquery('TB_window').set('morph', {
        duration: 75
    });
    scriptJquery('TB_window').morph({
		width: TB_WIDTH + 'px',
		left: (window.getScrollLeft() + (window.getWidth() - TB_WIDTH) / 2) + 'px',
		top: (window.getScrollTop() + (window.getHeight() - TB_HEIGHT) / 2) + 'px'
	});	
}

function TB_overlaySize(){
    // we have to set this to 0px before so we can reduce the size / width of the overflow onresize 
    scriptJquery("TB_overlay").setStyles({
        "height": '0px',
        "width": '0px'
    });
    scriptJquery("TB_HideSelect").setStyles({
        "height": '0px',
        "width": '0px'
    });
    scriptJquery("TB_overlay").setStyles({
        "height": window.getScrollHeight() + 'px',
        "width": window.getScrollWidth() + 'px'
    });
    scriptJquery("TB_HideSelect").setStyles({
        "height": window.getScrollHeight() + 'px',
        "width": window.getScrollWidth() + 'px'
    });
}

function TB_load_position(){
    if (scriptJquery("TB_load")) {
        scriptJquery("TB_load").setStyles({
            left: (window.getScrollLeft() + (window.getWidth() - 56) / 2) + 'px',
            top: (window.getScrollTop() + ((window.getHeight() - 20) / 2)) + 'px',
            display: "block"
        });
    }
}

function TB_parseQuery(query){
    // return empty object
    if (!query) 
        return {};
    var params = {};
    
    // parse query
    var pairs = query.split(/[;&]/);
    for (var i = 0; i < pairs.length; i++) {
        var pair = pairs[i].split('=');
        if (!pair || pair.length != 2) 
            continue;
        // unescape both key and value, replace "+" with spaces in value
        params[unescape(pair[0])] = unescape(pair[1]).replace(/\+/g, ' ');
    }
    return params;
}
