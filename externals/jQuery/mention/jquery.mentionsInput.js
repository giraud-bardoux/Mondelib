//     Underscore.js 1.8.3
//     http://underscorejs.org
//     (c) 2009-2015 Jeremy Ashkenas, DocumentCloud and Investigative Reporters & Editors
//     Underscore may be freely distributed under the MIT license.
(function(){function n(n){function t(t,r,e,u,i,o){for(;i>=0&&o>i;i+=n){var a=u?u[i]:i;e=r(e,t[a],a,t)}return e}return function(r,e,u,i){e=b(e,i,4);var o=!k(r)&&m.keys(r),a=(o||r).length,c=n>0?0:a-1;return arguments.length<3&&(u=r[o?o[c]:c],c+=n),t(r,e,u,o,c,a)}}function t(n){return function(t,r,e){r=x(r,e);for(var u=O(t),i=n>0?0:u-1;i>=0&&u>i;i+=n)if(r(t[i],i,t))return i;return-1}}function r(n,t,r){return function(e,u,i){var o=0,a=O(e);if("number"==typeof i)n>0?o=i>=0?i:Math.max(i+a,o):a=i>=0?Math.min(i+1,a):i+a+1;else if(r&&i&&a)return i=r(e,u),e[i]===u?i:-1;if(u!==u)return i=t(l.call(e,o,a),m.isNaN),i>=0?i+o:-1;for(i=n>0?o:a-1;i>=0&&a>i;i+=n)if(e[i]===u)return i;return-1}}function e(n,t){var r=I.length,e=n.constructor,u=m.isFunction(e)&&e.prototype||a,i="constructor";for(m.has(n,i)&&!m.contains(t,i)&&t.push(i);r--;)i=I[r],i in n&&n[i]!==u[i]&&!m.contains(t,i)&&t.push(i)}var u=this,i=u._,o=Array.prototype,a=Object.prototype,c=Function.prototype,f=o.push,l=o.slice,s=a.toString,p=a.hasOwnProperty,h=Array.isArray,v=Object.keys,g=c.bind,y=Object.create,d=function(){},m=function(n){return n instanceof m?n:this instanceof m?void(this._wrapped=n):new m(n)};"undefined"!=typeof exports?("undefined"!=typeof module&&module.exports&&(exports=module.exports=m),exports._=m):u._=m,m.VERSION="1.8.3";var b=function(n,t,r){if(t===void 0)return n;switch(null==r?3:r){case 1:return function(r){return n.call(t,r)};case 2:return function(r,e){return n.call(t,r,e)};case 3:return function(r,e,u){return n.call(t,r,e,u)};case 4:return function(r,e,u,i){return n.call(t,r,e,u,i)}}return function(){return n.apply(t,arguments)}},x=function(n,t,r){return null==n?m.identity:m.isFunction(n)?b(n,t,r):m.isObject(n)?m.matcher(n):m.property(n)};m.iteratee=function(n,t){return x(n,t,1/0)};var _=function(n,t){return function(r){var e=arguments.length;if(2>e||null==r)return r;for(var u=1;e>u;u++)for(var i=arguments[u],o=n(i),a=o.length,c=0;a>c;c++){var f=o[c];t&&r[f]!==void 0||(r[f]=i[f])}return r}},j=function(n){if(!m.isObject(n))return{};if(y)return y(n);d.prototype=n;var t=new d;return d.prototype=null,t},w=function(n){return function(t){return null==t?void 0:t[n]}},A=Math.pow(2,53)-1,O=w("length"),k=function(n){var t=O(n);return"number"==typeof t&&t>=0&&A>=t};m.each=m.forEach=function(n,t,r){t=b(t,r);var e,u;if(k(n))for(e=0,u=n.length;u>e;e++)t(n[e],e,n);else{var i=m.keys(n);for(e=0,u=i.length;u>e;e++)t(n[i[e]],i[e],n)}return n},m.map=m.collect=function(n,t,r){t=x(t,r);for(var e=!k(n)&&m.keys(n),u=(e||n).length,i=Array(u),o=0;u>o;o++){var a=e?e[o]:o;i[o]=t(n[a],a,n)}return i},m.reduce=m.foldl=m.inject=n(1),m.reduceRight=m.foldr=n(-1),m.find=m.detect=function(n,t,r){var e;return e=k(n)?m.findIndex(n,t,r):m.findKey(n,t,r),e!==void 0&&e!==-1?n[e]:void 0},m.filter=m.select=function(n,t,r){var e=[];return t=x(t,r),m.each(n,function(n,r,u){t(n,r,u)&&e.push(n)}),e},m.reject=function(n,t,r){return m.filter(n,m.negate(x(t)),r)},m.every=m.all=function(n,t,r){t=x(t,r);for(var e=!k(n)&&m.keys(n),u=(e||n).length,i=0;u>i;i++){var o=e?e[i]:i;if(!t(n[o],o,n))return!1}return!0},m.some=m.any=function(n,t,r){t=x(t,r);for(var e=!k(n)&&m.keys(n),u=(e||n).length,i=0;u>i;i++){var o=e?e[i]:i;if(t(n[o],o,n))return!0}return!1},m.contains=m.includes=m.include=function(n,t,r,e){return k(n)||(n=m.values(n)),("number"!=typeof r||e)&&(r=0),m.indexOf(n,t,r)>=0},m.invoke=function(n,t){var r=l.call(arguments,2),e=m.isFunction(t);return m.map(n,function(n){var u=e?t:n[t];return null==u?u:u.apply(n,r)})},m.pluck=function(n,t){return m.map(n,m.property(t))},m.where=function(n,t){return m.filter(n,m.matcher(t))},m.findWhere=function(n,t){return m.find(n,m.matcher(t))},m.max=function(n,t,r){var e,u,i=-1/0,o=-1/0;if(null==t&&null!=n){n=k(n)?n:m.values(n);for(var a=0,c=n.length;c>a;a++)e=n[a],e>i&&(i=e)}else t=x(t,r),m.each(n,function(n,r,e){u=t(n,r,e),(u>o||u===-1/0&&i===-1/0)&&(i=n,o=u)});return i},m.min=function(n,t,r){var e,u,i=1/0,o=1/0;if(null==t&&null!=n){n=k(n)?n:m.values(n);for(var a=0,c=n.length;c>a;a++)e=n[a],i>e&&(i=e)}else t=x(t,r),m.each(n,function(n,r,e){u=t(n,r,e),(o>u||1/0===u&&1/0===i)&&(i=n,o=u)});return i},m.shuffle=function(n){for(var t,r=k(n)?n:m.values(n),e=r.length,u=Array(e),i=0;e>i;i++)t=m.random(0,i),t!==i&&(u[i]=u[t]),u[t]=r[i];return u},m.sample=function(n,t,r){return null==t||r?(k(n)||(n=m.values(n)),n[m.random(n.length-1)]):m.shuffle(n).slice(0,Math.max(0,t))},m.sortBy=function(n,t,r){return t=x(t,r),m.pluck(m.map(n,function(n,r,e){return{value:n,index:r,criteria:t(n,r,e)}}).sort(function(n,t){var r=n.criteria,e=t.criteria;if(r!==e){if(r>e||r===void 0)return 1;if(e>r||e===void 0)return-1}return n.index-t.index}),"value")};var F=function(n){return function(t,r,e){var u={};return r=x(r,e),m.each(t,function(e,i){var o=r(e,i,t);n(u,e,o)}),u}};m.groupBy=F(function(n,t,r){m.has(n,r)?n[r].push(t):n[r]=[t]}),m.indexBy=F(function(n,t,r){n[r]=t}),m.countBy=F(function(n,t,r){m.has(n,r)?n[r]++:n[r]=1}),m.toArray=function(n){return n?m.isArray(n)?l.call(n):k(n)?m.map(n,m.identity):m.values(n):[]},m.size=function(n){return null==n?0:k(n)?n.length:m.keys(n).length},m.partition=function(n,t,r){t=x(t,r);var e=[],u=[];return m.each(n,function(n,r,i){(t(n,r,i)?e:u).push(n)}),[e,u]},m.first=m.head=m.take=function(n,t,r){return null==n?void 0:null==t||r?n[0]:m.initial(n,n.length-t)},m.initial=function(n,t,r){return l.call(n,0,Math.max(0,n.length-(null==t||r?1:t)))},m.last=function(n,t,r){return null==n?void 0:null==t||r?n[n.length-1]:m.rest(n,Math.max(0,n.length-t))},m.rest=m.tail=m.drop=function(n,t,r){return l.call(n,null==t||r?1:t)},m.compact=function(n){return m.filter(n,m.identity)};var S=function(n,t,r,e){for(var u=[],i=0,o=e||0,a=O(n);a>o;o++){var c=n[o];if(k(c)&&(m.isArray(c)||m.isArguments(c))){t||(c=S(c,t,r));var f=0,l=c.length;for(u.length+=l;l>f;)u[i++]=c[f++]}else r||(u[i++]=c)}return u};m.flatten=function(n,t){return S(n,t,!1)},m.without=function(n){return m.difference(n,l.call(arguments,1))},m.uniq=m.unique=function(n,t,r,e){m.isBoolean(t)||(e=r,r=t,t=!1),null!=r&&(r=x(r,e));for(var u=[],i=[],o=0,a=O(n);a>o;o++){var c=n[o],f=r?r(c,o,n):c;t?(o&&i===f||u.push(c),i=f):r?m.contains(i,f)||(i.push(f),u.push(c)):m.contains(u,c)||u.push(c)}return u},m.union=function(){return m.uniq(S(arguments,!0,!0))},m.intersection=function(n){for(var t=[],r=arguments.length,e=0,u=O(n);u>e;e++){var i=n[e];if(!m.contains(t,i)){for(var o=1;r>o&&m.contains(arguments[o],i);o++);o===r&&t.push(i)}}return t},m.difference=function(n){var t=S(arguments,!0,!0,1);return m.filter(n,function(n){return!m.contains(t,n)})},m.zip=function(){return m.unzip(arguments)},m.unzip=function(n){for(var t=n&&m.max(n,O).length||0,r=Array(t),e=0;t>e;e++)r[e]=m.pluck(n,e);return r},m.object=function(n,t){for(var r={},e=0,u=O(n);u>e;e++)t?r[n[e]]=t[e]:r[n[e][0]]=n[e][1];return r},m.findIndex=t(1),m.findLastIndex=t(-1),m.sortedIndex=function(n,t,r,e){r=x(r,e,1);for(var u=r(t),i=0,o=O(n);o>i;){var a=Math.floor((i+o)/2);r(n[a])<u?i=a+1:o=a}return i},m.indexOf=r(1,m.findIndex,m.sortedIndex),m.lastIndexOf=r(-1,m.findLastIndex),m.range=function(n,t,r){null==t&&(t=n||0,n=0),r=r||1;for(var e=Math.max(Math.ceil((t-n)/r),0),u=Array(e),i=0;e>i;i++,n+=r)u[i]=n;return u};var E=function(n,t,r,e,u){if(!(e instanceof t))return n.apply(r,u);var i=j(n.prototype),o=n.apply(i,u);return m.isObject(o)?o:i};m.bind=function(n,t){if(g&&n.bind===g)return g.apply(n,l.call(arguments,1));if(!m.isFunction(n))throw new TypeError("Bind must be called on a function");var r=l.call(arguments,2),e=function(){return E(n,e,t,this,r.concat(l.call(arguments)))};return e},m.partial=function(n){var t=l.call(arguments,1),r=function(){for(var e=0,u=t.length,i=Array(u),o=0;u>o;o++)i[o]=t[o]===m?arguments[e++]:t[o];for(;e<arguments.length;)i.push(arguments[e++]);return E(n,r,this,this,i)};return r},m.bindAll=function(n){var t,r,e=arguments.length;if(1>=e)throw new Error("bindAll must be passed function names");for(t=1;e>t;t++)r=arguments[t],n[r]=m.bind(n[r],n);return n},m.memoize=function(n,t){var r=function(e){var u=r.cache,i=""+(t?t.apply(this,arguments):e);return m.has(u,i)||(u[i]=n.apply(this,arguments)),u[i]};return r.cache={},r},m.delay=function(n,t){var r=l.call(arguments,2);return setTimeout(function(){return n.apply(null,r)},t)},m.defer=m.partial(m.delay,m,1),m.throttle=function(n,t,r){var e,u,i,o=null,a=0;r||(r={});var c=function(){a=r.leading===!1?0:m.now(),o=null,i=n.apply(e,u),o||(e=u=null)};return function(){var f=m.now();a||r.leading!==!1||(a=f);var l=t-(f-a);return e=this,u=arguments,0>=l||l>t?(o&&(clearTimeout(o),o=null),a=f,i=n.apply(e,u),o||(e=u=null)):o||r.trailing===!1||(o=setTimeout(c,l)),i}},m.debounce=function(n,t,r){var e,u,i,o,a,c=function(){var f=m.now()-o;t>f&&f>=0?e=setTimeout(c,t-f):(e=null,r||(a=n.apply(i,u),e||(i=u=null)))};return function(){i=this,u=arguments,o=m.now();var f=r&&!e;return e||(e=setTimeout(c,t)),f&&(a=n.apply(i,u),i=u=null),a}},m.wrap=function(n,t){return m.partial(t,n)},m.negate=function(n){return function(){return!n.apply(this,arguments)}},m.compose=function(){var n=arguments,t=n.length-1;return function(){for(var r=t,e=n[t].apply(this,arguments);r--;)e=n[r].call(this,e);return e}},m.after=function(n,t){return function(){return--n<1?t.apply(this,arguments):void 0}},m.before=function(n,t){var r;return function(){return--n>0&&(r=t.apply(this,arguments)),1>=n&&(t=null),r}},m.once=m.partial(m.before,2);var M=!{toString:null}.propertyIsEnumerable("toString"),I=["valueOf","isPrototypeOf","toString","propertyIsEnumerable","hasOwnProperty","toLocaleString"];m.keys=function(n){if(!m.isObject(n))return[];if(v)return v(n);var t=[];for(var r in n)m.has(n,r)&&t.push(r);return M&&e(n,t),t},m.allKeys=function(n){if(!m.isObject(n))return[];var t=[];for(var r in n)t.push(r);return M&&e(n,t),t},m.values=function(n){for(var t=m.keys(n),r=t.length,e=Array(r),u=0;r>u;u++)e[u]=n[t[u]];return e},m.mapObject=function(n,t,r){t=x(t,r);for(var e,u=m.keys(n),i=u.length,o={},a=0;i>a;a++)e=u[a],o[e]=t(n[e],e,n);return o},m.pairs=function(n){for(var t=m.keys(n),r=t.length,e=Array(r),u=0;r>u;u++)e[u]=[t[u],n[t[u]]];return e},m.invert=function(n){for(var t={},r=m.keys(n),e=0,u=r.length;u>e;e++)t[n[r[e]]]=r[e];return t},m.functions=m.methods=function(n){var t=[];for(var r in n)m.isFunction(n[r])&&t.push(r);return t.sort()},m.extend=_(m.allKeys),m.extendOwn=m.assign=_(m.keys),m.findKey=function(n,t,r){t=x(t,r);for(var e,u=m.keys(n),i=0,o=u.length;o>i;i++)if(e=u[i],t(n[e],e,n))return e},m.pick=function(n,t,r){var e,u,i={},o=n;if(null==o)return i;m.isFunction(t)?(u=m.allKeys(o),e=b(t,r)):(u=S(arguments,!1,!1,1),e=function(n,t,r){return t in r},o=Object(o));for(var a=0,c=u.length;c>a;a++){var f=u[a],l=o[f];e(l,f,o)&&(i[f]=l)}return i},m.omit=function(n,t,r){if(m.isFunction(t))t=m.negate(t);else{var e=m.map(S(arguments,!1,!1,1),String);t=function(n,t){return!m.contains(e,t)}}return m.pick(n,t,r)},m.defaults=_(m.allKeys,!0),m.create=function(n,t){var r=j(n);return t&&m.extendOwn(r,t),r},m.clone=function(n){return m.isObject(n)?m.isArray(n)?n.slice():m.extend({},n):n},m.tap=function(n,t){return t(n),n},m.isMatch=function(n,t){var r=m.keys(t),e=r.length;if(null==n)return!e;for(var u=Object(n),i=0;e>i;i++){var o=r[i];if(t[o]!==u[o]||!(o in u))return!1}return!0};var N=function(n,t,r,e){if(n===t)return 0!==n||1/n===1/t;if(null==n||null==t)return n===t;n instanceof m&&(n=n._wrapped),t instanceof m&&(t=t._wrapped);var u=s.call(n);if(u!==s.call(t))return!1;switch(u){case"[object RegExp]":case"[object String]":return""+n==""+t;case"[object Number]":return+n!==+n?+t!==+t:0===+n?1/+n===1/t:+n===+t;case"[object Date]":case"[object Boolean]":return+n===+t}var i="[object Array]"===u;if(!i){if("object"!=typeof n||"object"!=typeof t)return!1;var o=n.constructor,a=t.constructor;if(o!==a&&!(m.isFunction(o)&&o instanceof o&&m.isFunction(a)&&a instanceof a)&&"constructor"in n&&"constructor"in t)return!1}r=r||[],e=e||[];for(var c=r.length;c--;)if(r[c]===n)return e[c]===t;if(r.push(n),e.push(t),i){if(c=n.length,c!==t.length)return!1;for(;c--;)if(!N(n[c],t[c],r,e))return!1}else{var f,l=m.keys(n);if(c=l.length,m.keys(t).length!==c)return!1;for(;c--;)if(f=l[c],!m.has(t,f)||!N(n[f],t[f],r,e))return!1}return r.pop(),e.pop(),!0};m.isEqual=function(n,t){return N(n,t)},m.isEmpty=function(n){return null==n?!0:k(n)&&(m.isArray(n)||m.isString(n)||m.isArguments(n))?0===n.length:0===m.keys(n).length},m.isElement=function(n){return!(!n||1!==n.nodeType)},m.isArray=h||function(n){return"[object Array]"===s.call(n)},m.isObject=function(n){var t=typeof n;return"function"===t||"object"===t&&!!n},m.each(["Arguments","Function","String","Number","Date","RegExp","Error"],function(n){m["is"+n]=function(t){return s.call(t)==="[object "+n+"]"}}),m.isArguments(arguments)||(m.isArguments=function(n){return m.has(n,"callee")}),"function"!=typeof/./&&"object"!=typeof Int8Array&&(m.isFunction=function(n){return"function"==typeof n||!1}),m.isFinite=function(n){return isFinite(n)&&!isNaN(parseFloat(n))},m.isNaN=function(n){return m.isNumber(n)&&n!==+n},m.isBoolean=function(n){return n===!0||n===!1||"[object Boolean]"===s.call(n)},m.isNull=function(n){return null===n},m.isUndefined=function(n){return n===void 0},m.has=function(n,t){return null!=n&&p.call(n,t)},m.noConflict=function(){return u._=i,this},m.identity=function(n){return n},m.constant=function(n){return function(){return n}},m.noop=function(){},m.property=w,m.propertyOf=function(n){return null==n?function(){}:function(t){return n[t]}},m.matcher=m.matches=function(n){return n=m.extendOwn({},n),function(t){return m.isMatch(t,n)}},m.times=function(n,t,r){var e=Array(Math.max(0,n));t=b(t,r,1);for(var u=0;n>u;u++)e[u]=t(u);return e},m.random=function(n,t){return null==t&&(t=n,n=0),n+Math.floor(Math.random()*(t-n+1))},m.now=Date.now||function(){return(new Date).getTime()};var B={"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#x27;","`":"&#x60;"},T=m.invert(B),R=function(n){var t=function(t){return n[t]},r="(?:"+m.keys(n).join("|")+")",e=RegExp(r),u=RegExp(r,"g");return function(n){return n=null==n?"":""+n,e.test(n)?n.replace(u,t):n}};m.escape=R(B),m.unescape=R(T),m.result=function(n,t,r){var e=null==n?void 0:n[t];return e===void 0&&(e=r),m.isFunction(e)?e.call(n):e};var q=0;m.uniqueId=function(n){var t=++q+"";return n?n+t:t},m.templateSettings={evaluate:/<%([\s\S]+?)%>/g,interpolate:/<%=([\s\S]+?)%>/g,escape:/<%-([\s\S]+?)%>/g};var K=/(.)^/,z={"'":"'","\\":"\\","\r":"r","\n":"n","\u2028":"u2028","\u2029":"u2029"},D=/\\|'|\r|\n|\u2028|\u2029/g,L=function(n){return"\\"+z[n]};m.template=function(n,t,r){!t&&r&&(t=r),t=m.defaults({},t,m.templateSettings);var e=RegExp([(t.escape||K).source,(t.interpolate||K).source,(t.evaluate||K).source].join("|")+"|$","g"),u=0,i="__p+='";n.replace(e,function(t,r,e,o,a){return i+=n.slice(u,a).replace(D,L),u=a+t.length,r?i+="'+\n((__t=("+r+"))==null?'':_.escape(__t))+\n'":e?i+="'+\n((__t=("+e+"))==null?'':__t)+\n'":o&&(i+="';\n"+o+"\n__p+='"),t}),i+="';\n",t.variable||(i="with(obj||{}){\n"+i+"}\n"),i="var __t,__p='',__j=Array.prototype.join,"+"print=function(){__p+=__j.call(arguments,'');};\n"+i+"return __p;\n";try{var o=new Function(t.variable||"obj","_",i)}catch(a){throw a.source=i,a}var c=function(n){return o.call(this,n,m)},f=t.variable||"obj";return c.source="function("+f+"){\n"+i+"}",c},m.chain=function(n){var t=m(n);return t._chain=!0,t};var P=function(n,t){return n._chain?m(t).chain():t};m.mixin=function(n){m.each(m.functions(n),function(t){var r=m[t]=n[t];m.prototype[t]=function(){var n=[this._wrapped];return f.apply(n,arguments),P(this,r.apply(m,n))}})},m.mixin(m),m.each(["pop","push","reverse","shift","sort","splice","unshift"],function(n){var t=o[n];m.prototype[n]=function(){var r=this._wrapped;return t.apply(r,arguments),"shift"!==n&&"splice"!==n||0!==r.length||delete r[0],P(this,r)}}),m.each(["concat","join","slice"],function(n){var t=o[n];m.prototype[n]=function(){return P(this,t.apply(this._wrapped,arguments))}}),m.prototype.value=function(){return this._wrapped},m.prototype.valueOf=m.prototype.toJSON=m.prototype.value,m.prototype.toString=function(){return""+this._wrapped},"function"==typeof define&&define.amd&&define("underscore",[],function(){return m})}).call(this);




/*
 * Mentions Input
 * Version 1.0.2
 * Written by: Kenneth Auchenberg (Podio)
 *
 * Using underscore.js
 *
 * License: MIT License - http://www.opensource.org/licenses/mit-license.php
 */
var nbr = 0;
var isoneditpage = false;
var mentionsCollectionValEdit = [];
(function ($, _, undefined) {

    // Settings
    var KEY = { BACKSPACE : 8, TAB : 9, RETURN : 13, ESC : 27, LEFT : 37, UP : 38, RIGHT : 39, DOWN : 40, COMMA : 188, SPACE : 32, HOME : 36, END : 35 }; // Keys "enum"

    //Default settings
    var defaultSettings = {
        triggerChar   : '@', //Char that respond to event
        onDataRequest : $.noop, //Function where we can search the data
        minChars      : 1, //Minimum chars to fire the event
        allowRepeat   : true, //Allow repeat mentions
        showAvatars   : true, //Show the avatars
        elastic       : false, //Grow the textarea automatically
        defaultValue  : '',
        onCaret       : false,
        classes       : {
            autoCompleteItemActive : "active" //Classes to apply in each item
        },
        templates     : {
            wrapper                    : _.template('<div class="mentions-input-box"></div>'),
            autocompleteList           : _.template('<div class="mentions-autocomplete-list"></div>'),
            autocompleteListItem       : _.template('<li data-ref-id="<%= id %>" data-ref-type="<%= type %>" data-display="<%= display %>"><%= content %></li>'),
            autocompleteListItemAvatar : _.template('<%= avatar %>'),
            autocompleteListItemIcon   : _.template('<div class="icon <%= icon %>"></div>'),
            mentionsOverlay            : _.template('<div class="highlighter"><div></div></div>'),
            mentionItemSyntax          : _.template('@_user_<%= id %>'),
            mentionItemHighlight       : _.template('<strong><span><%= value %></span></strong>')
        }
    };

    //Class util
    var utils = {
	    //Encodes the character with _.escape function (undersocre)
        htmlEncode       : function (str) {
            return _.escape(str);
        },
        //Encodes the character to be used with RegExp
        regexpEncode     : function (str) {
            return str;//str.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
        },
	    highlightTerm    : function (value, term) {
            if (!term && !term.length) {
                return value;
            }
            return value.replace(new RegExp("(?![^&;]+;)(?!<[^<>]*)(" + term + ")(?![^<>]*>)(?![^&;]+;)", "gi"), "<b>$1</b>");
        },
        //Sets the caret in a valid position
        setCaratPosition : function (domNode, caretPos) {
            if (domNode.createTextRange) {
                var range = domNode.createTextRange();
                range.move('character', caretPos);
                range.select();
            } else {
                if (domNode.selectionStart) {
                    domNode.focus();
                    domNode.setSelectionRange(caretPos, caretPos);
                } else {
                    domNode.focus();
                }
            }
        },
	    //Deletes the white spaces
        rtrim: function(string) {
            return string.replace(/\s+$/,"");
        }
    };

    //Main class of MentionsInput plugin
    var MentionsInput = function (settings) {

        var domInput,
            elmInputBox,
            elmInputWrapper,
            elmAutocompleteList,
            elmWrapperBox,
            elmMentionsOverlay,
            elmActiveAutoCompleteItem,
            autocompleteItemCollection = {},
            inputBuffer = [],
            currentDataQuery = '';

	    //Mix the default setting with the users settings
        settings = $.extend(true, {}, defaultSettings, settings );

	    //Initializes the text area target
        function initTextarea() {
            elmInputBox = $(domInput); //Get the text area target
            //If the text area is already configured, return
            if (elmInputBox.attr('data-mentions-input') === 'true') {
                return;
            }
            elmInputWrapper = elmInputBox.parent(); //Get the DOM element parent
            elmWrapperBox = $(settings.templates.wrapper());
            elmInputBox.wrapAll(elmWrapperBox); //Wrap all the text area into the div elmWrapperBox
            elmWrapperBox = elmInputWrapper.find('> div.mentions-input-box'); //Obtains the div elmWrapperBox that now contains the text area

            elmInputBox.attr('data-mentions-input', 'true'); //Sets the attribute data-mentions-input to true -> Defines if the text area is already configured
            elmInputBox.bind('keydown', onInputBoxKeyDown); //Bind the keydown event to the text area
            elmInputBox.bind('keypress', onInputBoxKeyPress); //Bind the keypress event to the text area
            elmInputBox.bind('click', onInputBoxClick); //Bind the click event to the text area
            elmInputBox.bind('blur', onInputBoxBlur); //Bind the blur event to the text area

            if (navigator.userAgent.indexOf("MSIE 8") > -1) {
                elmInputBox.bind('propertychange', onInputBoxInput); //IE8 won't fire the input event, so let's bind to the propertychange
            } else {
                elmInputBox.bind('input', onInputBoxInput); //Bind the input event to the text area
            }

            // Elastic textareas, grow automatically
            if( settings.elastic ) {
                elmInputBox.elastic();
            }
        }

        //Initializes the autocomplete list, append to elmWrapperBox and delegate the mousedown event to li elements
        function initAutocomplete() {
            elmAutocompleteList = $(settings.templates.autocompleteList()); //Get the HTML code for the list
            elmAutocompleteList.appendTo(elmWrapperBox); //Append to elmWrapperBox element
            elmAutocompleteList.delegate('li', 'mousedown', onAutoCompleteItemClick); //Delegate the event
        }

        //Initializes the mentions' overlay
        function initMentionsOverlay() {
            elmMentionsOverlay = $('.jqueryHashtags'); //Get the HTML code of the mentions' overlay
            //elmMentionsOverlay.prependTo(elmWrapperBox); //Insert into elmWrapperBox the mentions overlay
        }

	    //Updates the values of the main variables
        function updateValues() {

          if(scriptJquery('#ajaxsmoothbox_main').length){
              var className = 'highlighter_edit';
              var textAreal ='edit_activity_body';
              var elmMentionsOverlayElem = 'jqueryHashtags_edit';
             }else{
              var textAreal ='activity_body';
              var className = 'highlighter';
               var elmMentionsOverlayElem = 'jqueryHashtags';
             }
            var classNameElem = $(domInput).closest('.jqueryHashtags').find('div').eq(0);
           // var textAreal = $(domInput);
            elmMentionsOverlay = $('.'+elmMentionsOverlayElem);
            var syntaxMessage = getInputBoxValue(); //Get the actual value of the text area

            EditFieldValue = syntaxMessage;
           
            var id = $(domInput).attr('id'); 
            if(id){
              if(typeof mentiondataarray["mention_data_"+id]  != 'undefined'){
                mentionsCollection = mentiondataarray["mention_data_"+id];
              }
            }
            _.each(mentionsCollection, function (mention) {
                var textSyntax = settings.templates.mentionItemSyntax(mention);
                syntaxMessage = syntaxMessage.replace(new RegExp(utils.regexpEncode(mention.value), 'g'), textSyntax);
            });

            //var mentionText = utils.htmlEncode(syntaxMessage); //Encode the syntaxMessage
            var mentionText = syntaxMessage;
            _.each(mentionsCollection, function (mention) {
                var formattedMention = _.extend({}, mention, {value: utils.htmlEncode(mention.value)});
                var textSyntax = settings.templates.mentionItemSyntax(formattedMention);
                var textHighlight = settings.templates.mentionItemHighlight(formattedMention);

                mentionText = mentionText.replace(new RegExp(utils.regexpEncode(textSyntax), 'g'), textHighlight);
            });

            mentionText = mentionText.replace(/\n/g, '<br />'); //Replace the escape character for <br />
            mentionText = mentionText.replace(/ {2}/g, '&nbsp; '); //Replace the 2 preceding token to &nbsp;

            elmInputBox.data('messageText', syntaxMessage); //Save the messageText to elmInputBox
	        elmInputBox.trigger('updated');
            
            
            var str = mentionText;
             
            classNameElem.css("width",$(domInput).css("width"));
            str = str.replace(/\n/g, '<br>');
            
            var tagslistarr = str.match(/\B(#[^\s[!\"\#$%&'()*+,\-.\/\\:;<=>?@\[\]\^`{|}~]+)/g);
            if(tagslistarr && tagslistarr.length){
              for(var i=0;i<tagslistarr.length;i++){
                //hashtag must be on 1st position
                if(tagslistarr[i].indexOf('#') != 0 && str.indexOf(tagslistarr[i]) < 0 )
                  continue;
                var regex = new RegExp(tagslistarr[i], "g");
                str = str.replace(regex, '<strong></span>'+tagslistarr[i]+'</span></strong>');
              } 
            }
						
						
            /*if(!str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)?#([a-zA-Z0-9]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)?@([a-zA-Z0-9]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)?#([\u0600-\u06FF]+)/g) && !str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)?@([\u0600-\u06FF]+)/g)) {
              if(!str.match(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))#/g)) { //arabic support
                str = str.replace(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<strong><span>#$1</span></strong>');
              }else{
                str = str.replace(/#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))#(([a-zA-Z0-9]+)|([\u0600-\u06FF]+))/g,'<strong></span>#$1</span></strong>');
              }
            }*/
                        
            var p = str;
            var rx = /([\uD800-\uDBFF][\uDC00-\uDFFF](?:[\u200D\uFE0F][\uD800-\uDBFF][\uDC00-\uDFFF]){2,}|\uD83D\uDC69(?:\u200D(?:(?:\uD83D\uDC69\u200D)?\uD83D\uDC67|(?:\uD83D\uDC69\u200D)?\uD83D\uDC66)|\uD83C[\uDFFB-\uDFFF])|\uD83D\uDC69\u200D(?:\uD83D\uDC69\u200D)?\uD83D\uDC66\u200D\uD83D\uDC66|\uD83D\uDC69\u200D(?:\uD83D\uDC69\u200D)?\uD83D\uDC67\u200D(?:\uD83D[\uDC66\uDC67])|\uD83C\uDFF3\uFE0F\u200D\uD83C\uDF08|(?:\uD83C[\uDFC3\uDFC4\uDFCA]|\uD83D[\uDC6E\uDC71\uDC73\uDC77\uDC81\uDC82\uDC86\uDC87\uDE45-\uDE47\uDE4B\uDE4D\uDE4E\uDEA3\uDEB4-\uDEB6]|\uD83E[\uDD26\uDD37-\uDD39\uDD3D\uDD3E\uDDD6-\uDDDD])(?:\uD83C[\uDFFB-\uDFFF])\u200D[\u2640\u2642]\uFE0F|\uD83D\uDC69(?:\uD83C[\uDFFB-\uDFFF])\u200D(?:\uD83C[\uDF3E\uDF73\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92])|(?:\uD83C[\uDFC3\uDFC4\uDFCA]|\uD83D[\uDC6E\uDC6F\uDC71\uDC73\uDC77\uDC81\uDC82\uDC86\uDC87\uDE45-\uDE47\uDE4B\uDE4D\uDE4E\uDEA3\uDEB4-\uDEB6]|\uD83E[\uDD26\uDD37-\uDD39\uDD3C-\uDD3E\uDDD6-\uDDDF])\u200D[\u2640\u2642]\uFE0F|\uD83C\uDDFD\uD83C\uDDF0|\uD83C\uDDF6\uD83C\uDDE6|\uD83C\uDDF4\uD83C\uDDF2|\uD83C\uDDE9(?:\uD83C[\uDDEA\uDDEC\uDDEF\uDDF0\uDDF2\uDDF4\uDDFF])|\uD83C\uDDF7(?:\uD83C[\uDDEA\uDDF4\uDDF8\uDDFA\uDDFC])|\uD83C\uDDE8(?:\uD83C[\uDDE6\uDDE8\uDDE9\uDDEB-\uDDEE\uDDF0-\uDDF5\uDDF7\uDDFA-\uDDFF])|(?:\u26F9|\uD83C[\uDFCB\uDFCC]|\uD83D\uDD75)(?:\uFE0F\u200D[\u2640\u2642]|(?:\uD83C[\uDFFB-\uDFFF])\u200D[\u2640\u2642])\uFE0F|(?:\uD83D\uDC41\uFE0F\u200D\uD83D\uDDE8|\uD83D\uDC69(?:\uD83C[\uDFFB-\uDFFF])\u200D[\u2695\u2696\u2708]|\uD83D\uDC69\u200D[\u2695\u2696\u2708]|\uD83D\uDC68(?:(?:\uD83C[\uDFFB-\uDFFF])\u200D[\u2695\u2696\u2708]|\u200D[\u2695\u2696\u2708]))\uFE0F|\uD83C\uDDF2(?:\uD83C[\uDDE6\uDDE8-\uDDED\uDDF0-\uDDFF])|\uD83D\uDC69\u200D(?:\uD83C[\uDF3E\uDF73\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]|\u2764\uFE0F\u200D(?:\uD83D\uDC8B\u200D(?:\uD83D[\uDC68\uDC69])|\uD83D[\uDC68\uDC69]))|\uD83C\uDDF1(?:\uD83C[\uDDE6-\uDDE8\uDDEE\uDDF0\uDDF7-\uDDFB\uDDFE])|\uD83C\uDDEF(?:\uD83C[\uDDEA\uDDF2\uDDF4\uDDF5])|\uD83C\uDDED(?:\uD83C[\uDDF0\uDDF2\uDDF3\uDDF7\uDDF9\uDDFA])|\uD83C\uDDEB(?:\uD83C[\uDDEE-\uDDF0\uDDF2\uDDF4\uDDF7])|[#\*0-9]\uFE0F\u20E3|\uD83C\uDDE7(?:\uD83C[\uDDE6\uDDE7\uDDE9-\uDDEF\uDDF1-\uDDF4\uDDF6-\uDDF9\uDDFB\uDDFC\uDDFE\uDDFF])|\uD83C\uDDE6(?:\uD83C[\uDDE8-\uDDEC\uDDEE\uDDF1\uDDF2\uDDF4\uDDF6-\uDDFA\uDDFC\uDDFD\uDDFF])|\uD83C\uDDFF(?:\uD83C[\uDDE6\uDDF2\uDDFC])|\uD83C\uDDF5(?:\uD83C[\uDDE6\uDDEA-\uDDED\uDDF0-\uDDF3\uDDF7-\uDDF9\uDDFC\uDDFE])|\uD83C\uDDFB(?:\uD83C[\uDDE6\uDDE8\uDDEA\uDDEC\uDDEE\uDDF3\uDDFA])|\uD83C\uDDF3(?:\uD83C[\uDDE6\uDDE8\uDDEA-\uDDEC\uDDEE\uDDF1\uDDF4\uDDF5\uDDF7\uDDFA\uDDFF])|\uD83C\uDFF4\uDB40\uDC67\uDB40\uDC62(?:\uDB40\uDC77\uDB40\uDC6C\uDB40\uDC73|\uDB40\uDC73\uDB40\uDC63\uDB40\uDC74|\uDB40\uDC65\uDB40\uDC6E\uDB40\uDC67)\uDB40\uDC7F|\uD83D\uDC68(?:\u200D(?:\u2764\uFE0F\u200D(?:\uD83D\uDC8B\u200D)?\uD83D\uDC68|(?:(?:\uD83D[\uDC68\uDC69])\u200D)?\uD83D\uDC66\u200D\uD83D\uDC66|(?:(?:\uD83D[\uDC68\uDC69])\u200D)?\uD83D\uDC67\u200D(?:\uD83D[\uDC66\uDC67])|\uD83C[\uDF3E\uDF73\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92])|(?:\uD83C[\uDFFB-\uDFFF])\u200D(?:\uD83C[\uDF3E\uDF73\uDF93\uDFA4\uDFA8\uDFEB\uDFED]|\uD83D[\uDCBB\uDCBC\uDD27\uDD2C\uDE80\uDE92]))|\uD83C\uDDF8(?:\uD83C[\uDDE6-\uDDEA\uDDEC-\uDDF4\uDDF7-\uDDF9\uDDFB\uDDFD-\uDDFF])|\uD83C\uDDF0(?:\uD83C[\uDDEA\uDDEC-\uDDEE\uDDF2\uDDF3\uDDF5\uDDF7\uDDFC\uDDFE\uDDFF])|\uD83C\uDDFE(?:\uD83C[\uDDEA\uDDF9])|\uD83C\uDDEE(?:\uD83C[\uDDE8-\uDDEA\uDDF1-\uDDF4\uDDF6-\uDDF9])|\uD83C\uDDF9(?:\uD83C[\uDDE6\uDDE8\uDDE9\uDDEB-\uDDED\uDDEF-\uDDF4\uDDF7\uDDF9\uDDFB\uDDFC\uDDFF])|\uD83C\uDDEC(?:\uD83C[\uDDE6\uDDE7\uDDE9-\uDDEE\uDDF1-\uDDF3\uDDF5-\uDDFA\uDDFC\uDDFE])|\uD83C\uDDFA(?:\uD83C[\uDDE6\uDDEC\uDDF2\uDDF3\uDDF8\uDDFE\uDDFF])|\uD83C\uDDEA(?:\uD83C[\uDDE6\uDDE8\uDDEA\uDDEC\uDDED\uDDF7-\uDDFA])|\uD83C\uDDFC(?:\uD83C[\uDDEB\uDDF8])|(?:\u26F9|\uD83C[\uDFCB\uDFCC]|\uD83D\uDD75)(?:\uD83C[\uDFFB-\uDFFF])|(?:\uD83C[\uDFC3\uDFC4\uDFCA]|\uD83D[\uDC6E\uDC71\uDC73\uDC77\uDC81\uDC82\uDC86\uDC87\uDE45-\uDE47\uDE4B\uDE4D\uDE4E\uDEA3\uDEB4-\uDEB6]|\uD83E[\uDD26\uDD37-\uDD39\uDD3D\uDD3E\uDDD6-\uDDDD])(?:\uD83C[\uDFFB-\uDFFF])|(?:[\u261D\u270A-\u270D]|\uD83C[\uDF85\uDFC2\uDFC7]|\uD83D[\uDC42\uDC43\uDC46-\uDC50\uDC66\uDC67\uDC70\uDC72\uDC74-\uDC76\uDC78\uDC7C\uDC83\uDC85\uDCAA\uDD74\uDD7A\uDD90\uDD95\uDD96\uDE4C\uDE4F\uDEC0\uDECC]|\uD83E[\uDD18-\uDD1C\uDD1E\uDD1F\uDD30-\uDD36\uDDD1-\uDDD5])(?:\uD83C[\uDFFB-\uDFFF])|\uD83D\uDC68(?:\u200D(?:(?:(?:\uD83D[\uDC68\uDC69])\u200D)?\uD83D\uDC67|(?:(?:\uD83D[\uDC68\uDC69])\u200D)?\uD83D\uDC66)|\uD83C[\uDFFB-\uDFFF])|(?:[\u261D\u26F9\u270A-\u270D]|\uD83C[\uDF85\uDFC2-\uDFC4\uDFC7\uDFCA-\uDFCC]|\uD83D[\uDC42\uDC43\uDC46-\uDC50\uDC66-\uDC69\uDC6E\uDC70-\uDC78\uDC7C\uDC81-\uDC83\uDC85-\uDC87\uDCAA\uDD74\uDD75\uDD7A\uDD90\uDD95\uDD96\uDE45-\uDE47\uDE4B-\uDE4F\uDEA3\uDEB4-\uDEB6\uDEC0\uDECC]|\uD83E[\uDD18-\uDD1C\uDD1E\uDD1F\uDD26\uDD30-\uDD39\uDD3D\uDD3E\uDDD1-\uDDDD])(?:\uD83C[\uDFFB-\uDFFF])?|(?:[\u231A\u231B\u23E9-\u23EC\u23F0\u23F3\u25FD\u25FE\u2614\u2615\u2648-\u2653\u267F\u2693\u26A1\u26AA\u26AB\u26BD\u26BE\u26C4\u26C5\u26CE\u26D4\u26EA\u26F2\u26F3\u26F5\u26FA\u26FD\u2705\u270A\u270B\u2728\u274C\u274E\u2753-\u2755\u2757\u2795-\u2797\u27B0\u27BF\u2B1B\u2B1C\u2B50\u2B55]|\uD83C[\uDC04\uDCCF\uDD8E\uDD91-\uDD9A\uDDE6-\uDDFF\uDE01\uDE1A\uDE2F\uDE32-\uDE36\uDE38-\uDE3A\uDE50\uDE51\uDF00-\uDF20\uDF2D-\uDF35\uDF37-\uDF7C\uDF7E-\uDF93\uDFA0-\uDFCA\uDFCF-\uDFD3\uDFE0-\uDFF0\uDFF4\uDFF8-\uDFFF]|\uD83D[\uDC00-\uDC3E\uDC40\uDC42-\uDCFC\uDCFF-\uDD3D\uDD4B-\uDD4E\uDD50-\uDD67\uDD7A\uDD95\uDD96\uDDA4\uDDFB-\uDE4F\uDE80-\uDEC5\uDECC\uDED0-\uDED2\uDEEB\uDEEC\uDEF4-\uDEF8]|\uD83E[\uDD10-\uDD3A\uDD3C-\uDD3E\uDD40-\uDD45\uDD47-\uDD4C\uDD50-\uDD6B\uDD80-\uDD97\uDDC0\uDDD0-\uDDE6])|(?:[#\*0-9\xA9\xAE\u203C\u2049\u2122\u2139\u2194-\u2199\u21A9\u21AA\u231A\u231B\u2328\u23CF\u23E9-\u23F3\u23F8-\u23FA\u24C2\u25AA\u25AB\u25B6\u25C0\u25FB-\u25FE\u2600-\u2604\u260E\u2611\u2614\u2615\u2618\u261D\u2620\u2622\u2623\u2626\u262A\u262E\u262F\u2638-\u263A\u2640\u2642\u2648-\u2653\u2660\u2663\u2665\u2666\u2668\u267B\u267F\u2692-\u2697\u2699\u269B\u269C\u26A0\u26A1\u26AA\u26AB\u26B0\u26B1\u26BD\u26BE\u26C4\u26C5\u26C8\u26CE\u26CF\u26D1\u26D3\u26D4\u26E9\u26EA\u26F0-\u26F5\u26F7-\u26FA\u26FD\u2702\u2705\u2708-\u270D\u270F\u2712\u2714\u2716\u271D\u2721\u2728\u2733\u2734\u2744\u2747\u274C\u274E\u2753-\u2755\u2757\u2763\u2764\u2795-\u2797\u27A1\u27B0\u27BF\u2934\u2935\u2B05-\u2B07\u2B1B\u2B1C\u2B50\u2B55\u3030\u303D\u3297\u3299]|\uD83C[\uDC04\uDCCF\uDD70\uDD71\uDD7E\uDD7F\uDD8E\uDD91-\uDD9A\uDDE6-\uDDFF\uDE01\uDE02\uDE1A\uDE2F\uDE32-\uDE3A\uDE50\uDE51\uDF00-\uDF21\uDF24-\uDF93\uDF96\uDF97\uDF99-\uDF9B\uDF9E-\uDFF0\uDFF3-\uDFF5\uDFF7-\uDFFF]|\uD83D[\uDC00-\uDCFD\uDCFF-\uDD3D\uDD49-\uDD4E\uDD50-\uDD67\uDD6F\uDD70\uDD73-\uDD7A\uDD87\uDD8A-\uDD8D\uDD90\uDD95\uDD96\uDDA4\uDDA5\uDDA8\uDDB1\uDDB2\uDDBC\uDDC2-\uDDC4\uDDD1-\uDDD3\uDDDC-\uDDDE\uDDE1\uDDE3\uDDE8\uDDEF\uDDF3\uDDFA-\uDE4F\uDE80-\uDEC5\uDECB-\uDED2\uDEE0-\uDEE5\uDEE9\uDEEB\uDEEC\uDEF0\uDEF3-\uDEF8]|\uD83E[\uDD10-\uDD3A\uDD3C-\uDD3E\uDD40-\uDD45\uDD47-\uDD4C\uDD50-\uDD6B\uDD80-\uDD97\uDDC0\uDDD0-\uDDE6])\uFE0F)/;

          //FeelingEmojis Work
          // if(scriptJquery('#activity_feeling_emojisa').length > 0) {
          //   str = emojiscontent.toImage(str);
          // }
          //var res = str.replace(/&lt;br ?\/\>|&lt;br ?\/&rt;|\<br ?\/\>/g, "").split(' '); //p.split(customres).filter(Boolean); 
          var checkEmoji = false;

          //Text size increase in status box
          var composeInstancecheck = false;
          if(typeof composeInstance != 'undefined'){
            Object.entries(composeInstance.plugins).forEach(function([key,plugin]) {
              if(plugin.active) {
                composeInstancecheck = true;
              }
            });
          } else {
            isoneditpage = false;
            composeInstancecheck = true;
          }

          if(!isonCommentBox){
            if(!composeInstancecheck && !isoneditpage) {
              //var fontSize = $(domInput).css('font-size');
              var textlength = $(domInput).val().length;
              var activitytextlimit = 120;
              var rows = $(domInput).val().split("\n").length;
              if(textlength <= activitytextlimit && rows <= 10) {
                classNameElem.css("font-size", '');
                $(domInput).css("font-size", '');
                
                //Feed Background image work
                if($('feedbgid')) {
                  
                  var feelingactivity_type = 1;
                  if(document.getElementById('feelingactivity_type')) {
                    var feelingactivity_type = document.getElementById('feelingactivity_type').value;
                  }
                  if(feelingactivity_type != 2) {
                    var feedbgid = scriptJquery('#feedbgid').val();
                    var activitylng = scriptJquery('#activitylng').val();
                    if(feedbgid) {
                        var feedagainsrcurl = scriptJquery('#feed_bg_image_' + feedbgid).attr('src');
                        scriptJquery('.activity_post_box').css("background-image", "url(" + feedagainsrcurl + ")");
                        scriptJquery('#feedbgid_isphoto').val(1);
                        scriptJquery('#feedbg_main_continer').css('display','block');
                    }
                    
                    if(feedbgid) {
                      scriptJquery('#activity-form').addClass('feed_background_image');
                    }
                    if(feedbgid == 0) {
                      scriptJquery('#activity-form').removeClass('feed_background_image');
                    }
                    if(activitylng) {
                      scriptJquery('#feedbgid_isphoto').val(0);
                      scriptJquery('.activity_post_box').css('background-image', 'none');
                      scriptJquery('#activity-form').removeClass('feed_background_image');
                      scriptJquery('#feedbg_main_continer').css('display','none');
                    }
                  }
                }
                //Feed Background image work
              } else {
                classNameElem.css("font-size", '');
                $(domInput).css("font-size", '');

                //Feed Background image work
                scriptJquery('#feedbgid_isphoto').val(0);
                scriptJquery('.activity_post_box').css('background-image', 'none');
                scriptJquery('#activity-form').removeClass('feed_background_image');
                scriptJquery('#feedbg_main_continer').css('display','none');
               
              }
            } else if(composeInstancecheck) {
                classNameElem.css("font-size", '');
                $(domInput).css("font-size", '');
            }
          }
        
        //Text size increase in status box
        $(domInput).closest('.mentions-input-box').find('div').eq(0).find('div').eq(0).html(str); //Insert into a div of the elmMentionsOverlay the mention text
				
        if((composeInstancecheck && !isoneditpage) || isonCommentBox) {
          //  `mInput).css("fontSize", '');
        }
      		
		    if(typeof feedUpdateFunction != 'undefined'){
            feedUpdateFunction();
          }
        }
                
        //Cleans the buffer
        function resetBuffer() {
            inputBuffer = [];
        }

	      //Updates the mentions collection
        function updateMentionsCollection() {
            var inputText = getInputBoxValue(); //Get the actual value of text area

	        //Returns the values that doesn't match the condition
            mentionsCollection = _.reject(mentionsCollection, function (mention, index) {
                return !mention.value || inputText.indexOf(mention.value) == -1;
            });
            mentionsCollection = _.compact(mentionsCollection); //Delete all the falsy values of the array and return the new array
        }

	    //Adds mention to mentions collections
        function addMention(mention) {

            var currentMessage = getInputBoxValue(),
                caretStart = elmInputBox[0].selectionStart,
                shortestDistance = false,
                bestLastIndex = false;

            // Using a regex to figure out positions
            var regex = new RegExp("\\" + settings.triggerChar + currentDataQuery, "gi"),
                regexMatch;
            
            while(regexMatch = regex.exec(currentMessage)) {
                if (shortestDistance === false || Math.abs(regex.lastIndex - caretStart) < shortestDistance) {
                    shortestDistance = Math.abs(regex.lastIndex - caretStart);
                    bestLastIndex = regex.lastIndex;
                }
            }

            var startCaretPosition = bestLastIndex - currentDataQuery.length - 1; //Set the start caret position (right before the @)
            var currentCaretPosition = bestLastIndex; //Set the current caret position (right after the end of the "mention")


            var start = currentMessage.substr(0, startCaretPosition);
            var end = currentMessage.substr(currentCaretPosition, currentMessage.length);
            var startEndIndex = (start + mention.value).length + 1;

            // See if there's the same mention in the list
            if( !_.find(mentionsCollection, function (object) { return object.id == mention.id; }) ) {
                mentionsCollection.push(mention);//Add the mention to mentionsColletions
                var id = $(domInput).attr('id');
                if(id){
                  if(typeof mentiondataarray["mention_data_"+id]  == 'undefined'){
                    mentiondataarray["mention_data_"+id] = mentionsCollection;
                  }else{
                    mentiondataarray["mention_data_"+id].push(mention);  
                  }
                }
            }
            
            // Cleaning before inserting the value, otherwise auto-complete would be triggered with "old" inputbuffer
            resetBuffer();
            currentDataQuery = '';
            hideAutoComplete();

            // Mentions and syntax message
            var updatedMessageText = start + mention.value + ' ' + end;
            elmInputBox.val(updatedMessageText); //Set the value to the txt area
	          elmInputBox.trigger('mention');
            updateValues('addmention');

            // Set correct focus and selection
            elmInputBox.focus();
            utils.setCaratPosition(elmInputBox[0], startEndIndex);
        }

        //Gets the actual value of the text area without white spaces from the beginning and end of the value
        function getInputBoxValue() {
            //return $.trim(elmInputBox.val());
            //return $.trim(EditFieldValue);            
            //var html = $.trim($(domInput).html());
            var value = $(domInput).val(); //$.trim($(domInput).val());

//             if(!value)
//               return html;
            return value;
        }

        // This is taken straight from live (as of Sep 2012) GitHub code. The
        // technique is known around the web. Just google it. Github's is quite
        // succint though. NOTE: relies on selectionEnd, which as far as IE is concerned,
        // it'll only work on 9+. Good news is nothing will happen if the browser
        // doesn't support it.
        function textareaSelectionPosition($el) {
            var a, b, c, d, e, f, g, h, i, j, k;
            if (!(i = $el[0])) return;
            if (!$(i).is("textarea")) return;
            if (i.selectionEnd == null) return;
            g = {
                position: "absolute",
                overflow: "auto",
                whiteSpace: "pre-wrap",
                wordWrap: "break-word",
                boxSizing: "content-box",
                top: 0,
                left: -9999
              }, h = ["boxSizing", "fontFamily", "fontSize", "fontStyle", "fontVariant", "fontWeight", "height", "letterSpacing", "lineHeight", "paddingBottom", "paddingLeft", "paddingRight", "paddingTop", "textDecoration", "textIndent", "textTransform", "width", "word-spacing"];
            for (j = 0, k = h.length; j < k; j++) e = h[j], g[e] = $(i).css(e);
            return c = document.createElement("div"), $(c).css(g), $(i).after(c), b = document.createTextNode(i.value.substring(0, i.selectionEnd)), a = document.createTextNode(i.value.substring(i.selectionEnd)), d = document.createElement("span"), d.innerHTML = "&nbsp;", c.appendChild(b), c.appendChild(d), c.appendChild(a), c.scrollTop = i.scrollTop, f = $(d).position(), $(c).remove(), f
        }

        //same as above function but return offset instead of position
        function textareaSelectionOffset($el) {
            var a, b, c, d, e, f, g, h, i, j, k;
            if (!(i = $el[0])) return;
            if (!$(i).is("textarea")) return;
            if (i.selectionEnd == null) return;
            g = {
                position: "absolute",
                overflow: "auto",
                whiteSpace: "pre-wrap",
                wordWrap: "break-word",
                boxSizing: "content-box",
                top: 0,
                left: -9999
            }, h = ["boxSizing", "fontFamily", "fontSize", "fontStyle", "fontVariant", "fontWeight", "height", "letterSpacing", "lineHeight", "paddingBottom", "paddingLeft", "paddingRight", "paddingTop", "textDecoration", "textIndent", "textTransform", "width", "word-spacing"];
            for (j = 0, k = h.length; j < k; j++) e = h[j], g[e] = $(i).css(e);
            return c = document.createElement("div"), $(c).css(g), $(i).after(c), b = document.createTextNode(i.value.substring(0, i.selectionEnd)), a = document.createTextNode(i.value.substring(i.selectionEnd)), d = document.createElement("span"), d.innerHTML = "&nbsp;", c.appendChild(b), c.appendChild(d), c.appendChild(a), c.scrollTop = i.scrollTop, f = $(d).offset(), $(c).remove(), f
        }

        //Scrolls back to the input after autocomplete if the window has scrolled past the input
        function scrollToInput() {
            var elmDistanceFromTop = $(elmInputBox).offset().top; //input offset
            var bodyDistanceFromTop = $('body').offset().top; //body offset
            var distanceScrolled = $(window).scrollTop(); //distance scrolled

            if (distanceScrolled > elmDistanceFromTop) {
                //subtracts body distance to handle fixed headers
                $(window).scrollTop(elmDistanceFromTop - bodyDistanceFromTop);
              }
        }

        //Takes the click event when the user select a item of the dropdown
        function onAutoCompleteItemClick(e) {
            var elmTarget = $(this); //Get the item selected
            var mention = autocompleteItemCollection[elmTarget.attr('data-uid')]; //Obtains the mention

            addMention(mention);
            scrollToInput();
            return false;
        }

        //Takes the click event on text area
        function onInputBoxClick(e) {
            resetBuffer();
        }

        //Takes the blur event on text area
        function onInputBoxBlur(e) {
            hideAutoComplete();
        }

        //Takes the input event when users write or delete something
        function onInputBoxInput(e) {
            updateValues();
            updateMentionsCollection();

            var triggerCharIndex = _.lastIndexOf(inputBuffer, settings.triggerChar); //Returns the last match of the triggerChar in the inputBuffer
            if (triggerCharIndex > -1) { //If the triggerChar is present in the inputBuffer array
                currentDataQuery = inputBuffer.slice(triggerCharIndex + 1).join(''); //Gets the currentDataQuery
                currentDataQuery = utils.rtrim(currentDataQuery); //Deletes the whitespaces
                _.defer(_.bind(doSearch, this, currentDataQuery)); //Invoking the function doSearch ( Bind the function to this)
            }
        }

        //Takes the keypress event
        function onInputBoxKeyPress(e) {
            if(e.keyCode !== KEY.BACKSPACE) { //If the key pressed is not the backspace
                var typedValue = String.fromCharCode(e.which || e.keyCode); //Takes the string that represent this CharCode
                inputBuffer.push(typedValue); //Push the value pressed into inputBuffer
            }
            
//             //Background work
//             if(e.which == 13) {
//               nbr++;
//               if(nbr > 4) { console.log('bada');
//                 scriptJquery('#feedbgid_isphoto').val(0);
//                 scriptJquery('.activity_post_box').css('background-image', 'none');
//                 scriptJquery('#activity-form').removeClass('feed_background_image');
//               }
//               console.log(nbr); console.log(scriptJquery('#feedbgid_isphoto').val()); 
//             }
        }

	    //Takes the keydown event
        function onInputBoxKeyDown(e) {
          
            // This also matches HOME/END on OSX which is CMD+LEFT, CMD+RIGHT
            if (e.keyCode === KEY.LEFT || e.keyCode === KEY.RIGHT || e.keyCode === KEY.HOME || e.keyCode === KEY.END) {
                // Defer execution to ensure carat pos has changed after HOME/END keys then call the resetBuffer function
                _.defer(resetBuffer);

                // IE9 doesn't fire the oninput event when backspace or delete is pressed. This causes the highlighting
                // to stay on the screen whenever backspace is pressed after a highlighed word. This is simply a hack
                // to force updateValues() to fire when backspace/delete is pressed in IE9.
                if (navigator.userAgent.indexOf("MSIE 9") > -1) {
                  _.defer(updateValues); //Call the updateValues function
                }

                return;
            }

            //If the key pressed was the backspace
            if (e.keyCode === KEY.BACKSPACE) {
              //Background work
//               if(nbr > 0) 
//                 nbr--;
                inputBuffer = inputBuffer.slice(0, -1 + inputBuffer.length); // Can't use splice, not available in IE
                return;
            }

            //If the elmAutocompleteList is hidden
            if (!elmAutocompleteList.is(':visible')) {
                return true;
            }

            switch (e.keyCode) {
                case KEY.UP: //If the key pressed was UP or DOWN
                case KEY.DOWN:
                    var elmCurrentAutoCompleteItem = null;
                    if (e.keyCode === KEY.DOWN) { //If the key pressed was DOWN
                        if (elmActiveAutoCompleteItem && elmActiveAutoCompleteItem.length) { //If elmActiveAutoCompleteItem exits
                            elmCurrentAutoCompleteItem = elmActiveAutoCompleteItem.next(); //Gets the next li element in the list
                        } else {
                            elmCurrentAutoCompleteItem = elmAutocompleteList.find('li').first(); //Gets the first li element found
                        }
                    } else {
                        elmCurrentAutoCompleteItem = $(elmActiveAutoCompleteItem).prev(); //The key pressed was UP and gets the previous li element
                    }
                    if (elmCurrentAutoCompleteItem.length) {
                        selectAutoCompleteItem(elmCurrentAutoCompleteItem);
                    }
                    return false;
                case KEY.RETURN: //If the key pressed was RETURN or TAB
                case KEY.TAB:
                    if (elmActiveAutoCompleteItem && elmActiveAutoCompleteItem.length) { //If the elmActiveAutoCompleteItem exists
                        elmActiveAutoCompleteItem.trigger('mousedown'); //Calls the mousedown event
                        return false;
                    }
                break;
            }

            return true;
        }

        //Hides the autoomplete
        function hideAutoComplete() {
            elmActiveAutoCompleteItem = null;
            elmAutocompleteList.empty().hide();
        }

        //Selects the item in the autocomplete list
        function selectAutoCompleteItem(elmItem) {
            elmItem.addClass(settings.classes.autoCompleteItemActive); //Add the class active to item
            elmItem.siblings().removeClass(settings.classes.autoCompleteItemActive); //Gets all li elements in autocomplete list and remove the class active

            elmActiveAutoCompleteItem = elmItem; //Sets the item to elmActiveAutoCompleteItem
        }

	    //Populates dropdown
        function populateDropdown(query, results) {
            elmAutocompleteList.show(); //Shows the autocomplete list

            if(!settings.allowRepeat) {
                // Filter items that has already been mentioned
                var mentionValues = _.pluck(mentionsCollection, 'value');
                results = _.reject(results, function (item) {
                    return _.include(mentionValues, item.name);
                });
            }

            if (!results.length) { //If there are not elements hide the autocomplete list
                hideAutoComplete();
                return;
            }

            elmAutocompleteList.empty(); //Remove all li elements in autocomplete list
            var elmDropDownList = $("<ul>").appendTo(elmAutocompleteList).hide(); //Inserts a ul element to autocomplete div and hide it

            _.each(results, function (item, index) {
                var itemUid = _.uniqueId('mention_'); //Gets the item with unique id

                autocompleteItemCollection[itemUid] = _.extend({}, item, {value: item.name}); //Inserts the new item to autocompleteItemCollection

                var elmListItem = $(settings.templates.autocompleteListItem({
                    'id'      : utils.htmlEncode(item.id),
                    'display' : utils.htmlEncode(item.name),
                    'type'    : utils.htmlEncode(item.type),
                    'content' : utils.highlightTerm(utils.htmlEncode((item.display ? item.display : item.name)), query)
                })).attr('data-uid', itemUid); //Inserts the new item to list

                //If the index is 0
                if (index === 0) {
                    selectAutoCompleteItem(elmListItem);
                }

                //If show avatars is true
                if (settings.showAvatars) {
                    var elmIcon;

                    //If the item has an avatar
                    if (item.avatar) {
                        elmIcon = $(settings.templates.autocompleteListItemAvatar({ avatar : item.avatar }));
                    } else { //If not then we set an default icon
                        elmIcon = $(settings.templates.autocompleteListItemIcon({ icon : item.icon }));
                    }
                    elmIcon.prependTo(elmListItem); //Inserts the elmIcon to elmListItem
                }
                elmListItem = elmListItem.appendTo(elmDropDownList); //Insets the elmListItem to elmDropDownList
            });

            elmAutocompleteList.show(); //Shows the elmAutocompleteList div
	        if (settings.onCaret) {
		        positionAutocomplete(elmAutocompleteList, elmInputBox);
            }
	        elmDropDownList.show(); //Shows the elmDropDownList
        }

        //Search into data list passed as parameter
        function doSearch(query) {
            //If the query is not null, undefined, empty and has the minimum chars
            if (query && query.length && query.length >= settings.minChars) {
                //Call the onDataRequest function and then call the populateDropDrown
                settings.onDataRequest.call(this, 'search', query, function (responseData) {
                    populateDropdown(query, responseData);
                });
            } else { //If the query is null, undefined, empty or has not the minimun chars
                hideAutoComplete(); //Hide the autocompletelist
            }
        }
    
	    function positionAutocomplete(elmAutocompleteList, elmInputBox) {
            var elmAutocompleteListPosition = elmAutocompleteList.css('position');
            if (elmAutocompleteListPosition == 'absolute') {
                var position = textareaSelectionPosition(elmInputBox),
                    lineHeight = parseInt(elmInputBox.css('line-height'), 10) || 18;
                elmAutocompleteList.css('width', '15em'); // Sort of a guess
                elmAutocompleteList.css('left', position.left);
                elmAutocompleteList.css('top', lineHeight + position.top);

                //check if the right position of auto complete is larger than the right position of the input
                //if yes, reset the left of auto complete list to make it fit the input
                var elmInputBoxRight = elmInputBox.offset().left + elmInputBox.width(),
                    elmAutocompleteListRight = elmAutocompleteList.offset().left + elmAutocompleteList.width();
                if (elmInputBoxRight <= elmAutocompleteListRight) {
                    elmAutocompleteList.css('left', Math.abs(elmAutocompleteList.position().left - (elmAutocompleteListRight - elmInputBoxRight)));
                }
            }
            else if (elmAutocompleteListPosition == 'fixed') {
                var offset = textareaSelectionOffset(elmInputBox),
                    lineHeight = parseInt(elmInputBox.css('line-height'), 10) || 18;
                elmAutocompleteList.css('width', '15em'); // Sort of a guess
                elmAutocompleteList.css('left', offset.left + 10000);
                elmAutocompleteList.css('top', lineHeight + offset.top);
            }
        }

        //Resets the text area
        function resetInput(currentVal) {
          if(EditFieldValue)
            currentVal = EditFieldValue;
            mentionsCollection = [];
            //var mentionText = utils.htmlEncode(currentVal);
            var mentionText = currentVal;
            var regex = new RegExp("(" + settings.triggerChar + ")\\[(.*?)\\]\\((.*?):(.*?)\\)", "gi");
            var match, newMentionText = mentionText;
            while ((match = regex.exec(mentionText)) != null) {
                newMentionText = newMentionText.replace(match[0], match[1] + match[2]);
                mentionsCollection.push({ 'id': match[4], 'type': match[3], 'value': match[2], 'trigger': match[1] });
            }
            
            elmInputBox.val(newMentionText);
            updateValues();
        }
        // Public methods
        return {
            //Initializes the mentionsInput component on a specific element.
	        init : function (domTarget) {

                domInput = domTarget;

                initTextarea();
                initAutocomplete();
                initMentionsOverlay();
                resetInput(settings.defaultValue);

                //If the autocomplete list has prefill mentions
                if( settings.prefillMention ) {
                    addMention( settings.prefillMention );
                }
            },

	        //An async method which accepts a callback function and returns a value of the input field (including markup) as a first parameter of this function. This is the value you want to send to your server.
            val : function (callback) {
                if (!_.isFunction(callback)) {
                    return;
                }
                callback.call(this, mentionsCollection.length ? elmInputBox.data('messageText') : getInputBoxValue());
            },

        	//Resets the text area value and clears all mentions
            reset : function () {
                resetInput();
            },

            //Reinit with the text area value if it was changed programmatically
            reinit : function () {
                resetInput(false);
            },

            addmention : function () {
                addMention(arguments[0]);
            },
	        //An async method which accepts a callback function and returns a collection of mentions as hash objects as a first parameter.
            getMentions : function (callback) {
                if (!_.isFunction(callback)) {
                    return;
                }
                callback.call(this, mentionsCollection);
            },
            update: function() {
            var messageText = getInputBoxValue();
            // Strip codes
            // add each mention to mentionsCollection
            // And update

           // var mentionText = utils.htmlEncode(getInputBoxValue());
           var mentionText = getInputBoxValue();
            var newMentionText = mentionText;
            if(typeof mentionsCollectionValEdit != 'undefined' && userTagsEnable){
                   for (i=0;i<mentionsCollectionValEdit.length;i++) {    // Find all matches in a string
                      newMentionText = newMentionText.replace('@_user_'+mentionsCollectionValEdit[i]['id'],mentionsCollectionValEdit[i]['name']);
                      var mention = {
                          'id': mentionsCollectionValEdit[i]['id'],
                          'type': 'user',
                          'name': mentionsCollectionValEdit[i]['name'],
                          'avatar' : mentionsCollectionValEdit[i]['avatar'],
                          'value' : mentionsCollectionValEdit[i]['name'],
                         };
                      mentionsCollection.push(mention);
                      var id = $(domInput).attr('id');
                      if(id){
                        if(typeof mentiondataarray["mention_data_"+id]  == 'undefined'){
                          mentiondataarray["mention_data_"+id] = mentionsCollection;
                        }else{
                          mentiondataarray["mention_data_"+id].push(mention);  
                        }
                      }
                  }
                  elmInputBox.val(newMentionText);
                  updateValues();
            }
          },
        };
    };

    //Main function to include into jQuery and initialize the plugin
    $.fn.mentionsInput = function (method, settings) {

        var outerArguments = arguments; //Gets the arguments
        //If method is not a function
        if (typeof method === 'object' || !method) {
            settings = method;
        }

        return this.each(function () {
            var instance = $.data(this, 'mentionsInput') || $.data(this, 'mentionsInput', new MentionsInput(settings));

            if (_.isFunction(instance[method])) {
                return instance[method].apply(this, Array.prototype.slice.call(outerArguments, 1));
            } else if (typeof method === 'object' || !method) {
                return instance.init.call(this, this);
            } else {
                $.error('Method ' + method + ' does not exist');
            }
        });
    };

})(scriptJquery, _);
