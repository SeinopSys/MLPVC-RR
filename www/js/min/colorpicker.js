"use strict";function _possibleConstructorReturn(e,t){if(!e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!t||"object"!=typeof t&&"function"!=typeof t?e:t}function _inherits(e,t){if("function"!=typeof t&&null!==t)throw new TypeError("Super expression must either be null or a function, not "+typeof t);e.prototype=Object.create(t&&t.prototype,{constructor:{value:e,enumerable:!1,writable:!0,configurable:!0}}),t&&(Object.setPrototypeOf?Object.setPrototypeOf(e,t):e.__proto__=t)}function _classCallCheck(e,t){if(!(e instanceof t))throw new TypeError("Cannot call a class as a function")}var _createClass=function(){function e(e,t){for(var a=0;a<t.length;a++){var i=t[a];i.enumerable=i.enumerable||!1,i.configurable=!0,"value"in i&&(i.writable=!0),Object.defineProperty(e,i.key,i)}}return function(t,a,i){return a&&e(t.prototype,a),i&&e(t,i),t}}();!function(e,t){var a={menubar:t,statusbar:t,tabbar:t,picker:t},i=window.parent.Key,n={min:.004,max:32,step:1.1},o=function(e){e.getContext("2d").clearRect(0,0,e.width,e.height)},s=function(){function e(){_classCallCheck(this,e)}return _createClass(e,[{key:"getAverageOf",value:function(e){}}]),e}(),r=function(e){function t(e){_classCallCheck(this,t);var a=_possibleConstructorReturn(this,(t.__proto__||Object.getPrototypeOf(t)).call(this));return a.boundingRect=e,a}return _inherits(t,e),_createClass(t,[{key:"getPixels",value:function(){}},{key:"getAverageOf",value:function(){}}]),t}(s),l=function(e){function t(e,a){_classCallCheck(this,t);var i=_possibleConstructorReturn(this,(t.__proto__||Object.getPrototypeOf(t)).call(this));return i.boundingRect=e,i.slices=a,i}return _inherits(t,e),_createClass(t,[{key:"getPixels",value:function(){}},{key:"getAverageOf",value:function(){}}]),t}(s),c=function(){function a(){_classCallCheck(this,a)}return _createClass(a,null,[{key:"calcRectanglePoints",value:function(e,t,a){var i=Math.floor(a/2);return{sideLength:a,topLeft:{x:e-i,y:t-i}}}},{key:"distance",value:function(e,a){var i=arguments.length>2&&arguments[2]!==t?arguments[2]:0,n=arguments.length>3&&arguments[3]!==t?arguments[3]:0;return Math.sqrt(Math.pow(n-a,2)+Math.pow(i-e,2))}},{key:"calcCircleSlices",value:function(t){var i=t/2,n=new Array(t);e.each(n,function(e){n[e]=new Array(t)});for(var o=0;o<n.length;o++)for(var s=0;s<n[o].length;s++)n[o][s]=a.distance(o,s,i-.5,i-.5)<=i?1:0;return e.each(n,function(e,t){var a=t.join("").replace(/(^|0)1/g,"$1|1").replace(/1(0|$)/g,"1|$1").split("|");n[e]={skip:a[0].length,length:a[1].length}}),n}},{key:"snapPointToPixelGrid",value:function(e,t){return Math.round(Math.round(e/t)*t)}}]),a}(),h=function(){function t(a){_classCallCheck(this,t);var i=/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*([01]|(?:0?\.\d+)))?\)$/i,n=/^#([a-f0-9]{3}|[a-f0-9]{6})$/i,o=(a=a.trim()).match(i);if(o&&o[1]<=255&&o[2]<=255&&o[3]<=255&&(!o[4]||o[4]<=1))this.red=parseInt(o[1],10),this.green=parseInt(o[2],10),this.blue=parseInt(o[3],10),this.alpha=o[4]?parseFloat(o[4]):1;else{var s=a.match(n);if(!s)throw new Error("Unrecognized color format: "+a);var r=s[1];3===r.length&&(r=r[0]+r[0]+r[1]+r[1]+r[2]+r[2]);var l=e.hex2rgb("#"+r);this.red=l.r,this.green=l.g,this.blue=l.b,this.alpha=1}this.opacity=Math.round(100*this.alpha)}return _createClass(t,[{key:"toString",value:function(){return 1===this.alpha?e.rgb2hex({r:this.red,g:this.green,b:this.blue}):"rgba("+this.red+","+this.green+","+this.blue+","+this.alpha+")"}}]),t}();window.ColorFormatter=h;var u=function(){function t(){var a=this;_classCallCheck(this,t),this._$menubar=e("#menubar"),this._$menubar.children().children("a.dropdown").on("click",function(t){t.preventDefault(),t.stopPropagation(),a._$menubar.addClass("open"),e(t.target).trigger("mouseenter")}).on("mouseenter",function(t){if(a._$menubar.hasClass("open")){var i=e(t.target);i.hasClass("dropdown")&&(a._$menubar.find("a.active").removeClass("active"),i.addClass("active").next().removeClass("hidden"))}}),this._$filein=e.mk("input","screenshotin").attr({type:"file",accept:"image/png,image/jpeg",tabindex:-1,class:"fileinput"}).prop("multiple",!0).appendTo($body),this._$openImage=e("#open-image").on("click",function(e){e.preventDefault(),a._$filein.trigger("click")}),this._$closeActiveTab=e("#close-active-tab").on("click",function(e){e.preventDefault();var t=m.getInstance().getActiveTab();t&&t.getElement().find(".close").trigger("click")}),this._$filein.on("change",function(){var t=a._$filein[0].files;if(0!==t.length){var i=1!==t.length?"s":"";e.Dialog.wait("Opening file"+i,"Reading opened file"+i+", please wait");var n=0;!function i(){if(void 0===t[n])return a._$openImage.removeClass("disabled"),a._$filein.val(""),a.updateCloseActiveTab(),void e.Dialog.close();a.handleFileOpen(t[n],function(t){if(t)return n++,i();a._$openImage.removeClass("disabled"),e.Dialog.fail("Drag and drop","Failed to read file #"+n+", aborting")})}()}});var i=e("#about-dialog-template").children();this._$aboutDialog=e("#about-dialog").on("click",function(){e.Dialog.info("About",i.clone())}),$body.on("click",function(){a._$menubar.removeClass("open"),a._$menubar.find("a.active").removeClass("active"),a._$menubar.children("li").children("ul").addClass("hidden")})}return _createClass(t,[{key:"updateCloseActiveTab",value:function(){this._$closeActiveTab[m.getInstance().hasTabs()?"removeClass":"addClass"]("disabled")}},{key:"handleFileOpen",value:function(t,a){if(!/^image\/(png|jpeg)$/.test(t.type))return e.Dialog.fail("Invalid file","You may only use PNG or JPG images with this tool"),void a(!1);var i=new FileReader;i.onload=function(){_.getInstance().openImage(i.result,t.name,a)},i.readAsDataURL(t)}}],[{key:"getInstance",value:function(){return void 0===a.menubar&&(a.menubar=new t),a.menubar}}]),t}(),g=function(){function i(){var t=this;_classCallCheck(this,i),this._$el=e("#statusbar"),this._$info=this._$el.children(".info"),this._$pos=this._$el.children(".pos"),this._$colorat=this._$el.children(".colorat"),this._$color=this._$colorat.children(".color"),this._$opacity=this._$colorat.children(".opacity"),this.infoLocked=!1,this.Pos={mouse:"mousepos"},this["_$"+this.Pos.mouse]=this._$pos.children(".mouse"),e.each(this.Pos,function(e){t.setPosition(e)})}return _createClass(i,[{key:"lockInfo",value:function(){this.infoLocked=!0}},{key:"unlockInfo",value:function(){this.infoLocked=!1}},{key:"setInfo",value:function(){var e=arguments.length>0&&arguments[0]!==t?arguments[0]:"";this.infoLocked||this._$info.text(e)}},{key:"setPosition",value:function(a){var i=arguments.length>1&&arguments[1]!==t?arguments[1]:{top:NaN,left:NaN},n=arguments.length>2&&arguments[2]!==t?arguments[2]:1,o=this.Pos[a];if("string"!=typeof o)throw new Error("[Statusbar.setPosition] Invalid position display key: "+a);1!==n&&(i.left*=n,i.top*=n),this["_$"+o].text(isNaN(i.left)||isNaN(i.top)?"":e.roundTo(i.left,2)+","+e.roundTo(i.top,2))}},{key:"setColorAt",value:function(){var a=arguments.length>0&&arguments[0]!==t?arguments[0]:"",i=arguments.length>1&&arguments[1]!==t?arguments[1]:"";a.length?this._$color.css({backgroundColor:a,color:e.yiq(a)>127?"black":"white"}):this._$color.css({backgroundColor:"",color:""}),this._$color.text(a||""),this._$opacity.text(i||"")}}],[{key:"getInstance",value:function(){return void 0===a.statusbar&&(a.statusbar=new i),a.statusbar}}]),i}(),p=function(t,a){t.find(".color-preview").html(e.mk("div").css("background-color","rgba("+a.red+","+a.green+","+a.blue+","+a.opacity/100+")"))},f=function(t){var a=e(t.target).closest("form"),i=a.mkData();p(a,i)},d=e.mk("form","set-area-color").append('<div class="label">\n\t\t\t\t<span>Red, Green, Blue (0-255)</span>\n\t\t\t\t<div class="input-group-3">\n\t\t\t\t\t<input type="number" min="0" max="255" step="1" name="red"   class="change input-red">\n\t\t\t\t\t<input type="number" min="0" max="255" step="1" name="green" class="change input-green">\n\t\t\t\t\t<input type="number" min="0" max="255" step="1" name="blue"  class="change input-blue">\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class="label">\n\t\t\t\t<span>Opacity (%)</span>\n\t\t\t\t<input type="number" min="0" max="100" step="1" name="opacity" class="change">\n\t\t\t</div>\n\t\t\t<div>\n\t\t\t\t<div class="color-preview"></div>\n\t\t\t</div>').on("change keyup input",".change",f).on("set-color",function(t,a){var i=e(this);e.each(["red","green","blue","opacity"],function(e,t){i.find('input[name="'+t+'"]').val(a[t])}),p(i,a)}),v=function(){function a(i,n){var o=this;_classCallCheck(this,a),this._fileHash=n,this._imgel=new Image,this._imgdata={},this._pickingAreas=[],this.file={extension:t,name:t},this.setName(i),this._$pickAreaColorDisplay=e.mk("span").attr({class:"pickcolor","data-info":"Color of the picking areas on this specific tab"}),this._$el=e.mk("li").attr("class","tab").append(this._$pickAreaColorDisplay,e.mk("span").attr({class:"filename","data-info":this.file.name+"."+this.file.extension}).text(this.file.name),e.mk("span").attr("class","fileext").text(this.file.extension),e.mk("span").attr({class:"close","data-info":"Close tab"}).text("×")),this.setPickingAreaColor("rgba(255,0,0,.5)"),this._$el.on("click",function(t){switch(t.preventDefault(),t.target.className){case"close":return e.Dialog.confirm("Close tab","Please confirm that you want to close this tab.",["Close","Cancel"],function(t){t&&(o.close(),e.Dialog.close())});case"pickcolor":return e.Dialog.request("Select a picking area color",d.clone(!0,!0),"Set",function(t){t.triggerHandler("set-color",[o.getPickingAreaColor()]),t.on("submit",function(a){a.preventDefault();var i=t.mkData();e.Dialog.wait(!1,"Setting picking area color");try{o.setPickingAreaColor("rgba("+i.red+","+i.green+","+i.blue+","+Math.round(i.opacity)/100+")")}catch(t){return e.Dialog.fail(!1,a.message)}e.Dialog.close()})})}m.getInstance().activateTab(o)})}return _createClass(a,[{key:"activate",value:function(){this._$el.addClass("active")}},{key:"deactivate",value:function(){this._$el.removeClass("active")}},{key:"isActive",value:function(){return this._$el.hasClass("active")}},{key:"getFileHash",value:function(){return this._fileHash}},{key:"setImage",value:function(t,a){var i=this;e(this._imgel).attr("src",t).on("load",function(){i._imgdata.size={width:i._imgel.width,height:i._imgel.height},a(!0)}).on("error",function(){a(!1)})}},{key:"setName",value:function(e){var t=e.split(/\./g);this.file.extension=t.pop(),this.file.name=t.join(".")}},{key:"getImageSize",value:function(){return this._imgdata.size}},{key:"setImagePosition",value:function(e){this._imgdata.position=e}},{key:"getImagePosition",value:function(){return this._imgdata.position}},{key:"getElement",value:function(){return this._$el}},{key:"placeArea",value:function(e,a){var i=!(arguments.length>2&&arguments[2]!==t)||arguments[2],n=c.calcRectanglePoints(e.left,e.top,a);if(i)this.addPickingArea(new r(n));else{var o=c.calcCircleSlices(a);this.addPickingArea(new l(n,o))}}},{key:"addPickingArea",value:function(e){this._pickingAreas.push(e)}},{key:"getPickingAreas",value:function(){return this._pickingAreas}},{key:"clearPickingAreas",value:function(){this._pickingAreas=[],this.isActive()&&_.getInstance().redrawPickingAreas()}},{key:"getPickingAreaColor",value:function(){return this._pickingAreaColor}},{key:"setPickingAreaColor",value:function(t){this._pickingAreaColor=new h(t),this._$pickAreaColorDisplay.html(e.mk("span").css("background-color",this._pickingAreaColor.toString())),this.isActive()&&_.getInstance().redrawPickingAreas()}},{key:"drawImage",value:function(){_.getInstance().getImageCanvasCtx().drawImage(this._imgel,0,0,this._imgdata.size.width,this._imgdata.size.height,0,0,this._imgdata.size.width,this._imgdata.size.height)}},{key:"close",value:function(){m.getInstance().closeTab(this)}}]),a}(),m=function(){function i(){_classCallCheck(this,i),this._$tabbar=e("#tabbar"),this._activeTab=!1,this._tabStorage=[]}return _createClass(i,[{key:"newTab",value:function(){for(var e=arguments.length,t=Array(e),a=0;a<e;a++)t[a]=arguments[a];var i=new(Function.prototype.bind.apply(v,[null].concat(t)));return this._tabStorage.push(i),this.updateTabs(),i}},{key:"activateTab",value:function(t){var a=this;t instanceof v&&(t=this.indexOf(t)),this._tabStorage[t]instanceof v?this._activeTab=t:this._activeTab=!1,e.each(this._tabStorage,function(e,t){e===a._activeTab?t.activate():t.deactivate()}),!1!==this._activeTab&&_.getInstance().openTab(this._tabStorage[this._activeTab])}},{key:"indexOf",value:function(t){var a=parseInt(t.getElement().attr("data-ix"),10);if(isNaN(a)&&e.each(this._tabStorage,function(e,i){if(i===t)return a=e,!1}),isNaN(a))throw console.log(t),new Error("Could not find index of the tab logged above");return a}},{key:"updateTabs",value:function(){var t=this;this._$tabbar.children().detach(),e.each(this._tabStorage,function(e,a){t._$tabbar.append(a.getElement().attr("data-ix",e))})}},{key:"getActiveTab",value:function(){return!1!==this._activeTab?this._tabStorage[this._activeTab]:t}},{key:"getTabs",value:function(){return this._tabStorage}},{key:"hasTabs",value:function(){return this._tabStorage.length>0}},{key:"closeTab",value:function(e){var t=this.indexOf(e),a=this._tabStorage.length,i=a>1;i||(_.getInstance().clearImage(),u.getInstance().updateCloseActiveTab()),this._tabStorage.splice(t,1),i&&this.activateTab(Math.min(a-1,t)),this.updateTabs()}}],[{key:"getInstance",value:function(){return void 0===a.tabbar&&(a.tabbar=new i),a.tabbar}}]),i}(),_=function(){function s(){var a=this;_classCallCheck(this,s),this._mousepos={top:NaN,left:NaN},this._zoomlevel=1,this._moveMode=!1,this._$picker=e("#picker"),this.updateWrapSize(),this._$imageOverlay=e.mk("canvas").attr("class","image-overlay"),this._$imageCanvas=e.mk("canvas").attr("class","image-element"),this._$mouseOverlay=e.mk("canvas").attr("class","mouse-overlay"),this._$placeArea=e.mk("button").attr({class:"place-area typcn typcn-starburst","data-info":"Randomly place a new square picking area on the image (hold Alt to place rounded)"}).on("click",function(e){e.preventDefault();var t=a.getImageCanvasSize();a.placeArea({left:Math.floor(Math.random()*t.width),top:Math.floor(Math.random()*t.height)},45,!e.altKey)}),this._$clearAreas=e.mk("button").attr({class:"place-area typcn typcn-delete","data-info":"Clear all picking areas"}).on("click",function(e){e.preventDefault();var t=m.getInstance().getActiveTab();t&&t.clearPickingAreas()}),this._$zoomin=e.mk("button").attr({class:"zoom-in typcn typcn-zoom-in","data-info":"Zoom in (Alt+Scroll Up)"}).on("click",function(e,t){e.preventDefault(),a.setZoomLevel(a._zoomlevel*n.step,t)}),this._$zoomout=e.mk("button").attr({class:"zoom-out typcn typcn-zoom-out","data-info":"Zoom out (Alt+Scroll Down)"}).on("click",function(e,t){e.preventDefault(),a.setZoomLevel(a._zoomlevel/n.step,t)}),this._$zoomfit=e.mk("button").attr({class:"zoom-fit typcn typcn typcn-arrow-minimise","data-info":"Fit in view (Ctrl+0)"}).on("click",function(e){e.preventDefault(),a.setZoomFit()}),this._$zoomorig=e.mk("button").attr({class:"zoom-orig typcn typcn typcn-zoom","data-info":"Original size (Ctrl+1)"}).on("click",function(e){e.preventDefault(),a.setZoomOriginal()}),this._$zoomperc=e.mk("span").attr({class:"zoom-perc","data-info":"Current zoom level (Click to enter a custom value)",contenteditable:!0}).text("100%").on("keydown",function(t){if(e.isKey(i.Enter,t)){t.preventDefault();var n=parseFloat(a._$zoomperc.text());isNaN(n)||a.setZoomLevel(n/100),a.updateZoomLevelInputs()}}).on("mousedown",function(){a._$zoomperc.data("mousedown",!0)}).on("mouseup",function(){a._$zoomperc.data("mousedown",!1)}).on("click",function(){!0!==a._$zoomperc.data("focused")&&(a._$zoomperc.data("focused",!0),a._$zoomperc.select())}).on("dblclick",function(e){e.preventDefault(),a._$zoomperc.select()}).on("blur",function(){a._$zoomperc.data("mousedown")||a._$zoomperc.data("focused",!1),0===a._$zoomperc.html().trim().length&&a.updateZoomLevelInputs(),e.clearSelection()}),this._$actionTopLeft=e.mk("div").attr("class","actions actions-tl").append(e.mk("div").attr("class","editing-tools").append(this._$placeArea,this._$clearAreas),e.mk("div").attr("class","zoom-controls").append(this._$zoomin,this._$zoomout,this._$zoomfit,this._$zoomorig,this._$zoomperc)).on("mousedown",function(e){e.stopPropagation(),a._$zoomperc.triggerHandler("blur")}),this._$actionsBottomLeft=e.mk("div").attr("class","actions actions-bl").append("Unused panel").on("mousedown",function(e){e.stopPropagation(),a._$zoomperc.triggerHandler("blur")}),this._$loader=e.mk("div").attr("class","loader"),$w.on("resize",e.throttle(250,function(){a.resizeHandler()})),this._$picker.append(this._$actionTopLeft,this._$actionsBottomLeft,this._$mouseOverlay,this._$imageOverlay,this._$loader);var o=void 0,r=void 0;$body.on("mousemove",e.throttle(50,function(t){if(m.getInstance().getActiveTab()){var i=a.getWrapPosition(),n=a.getImagePosition(),s=a.getImageCanvasSize();if(a._mousepos.top=t.pageY-i.top,a._mousepos.left=t.pageX-i.left,a._mousepos.top<n.top||a._mousepos.top>n.top+s.height-1||a._mousepos.left<n.left||a._mousepos.left>n.left+s.width-1)a._mousepos.top=NaN,a._mousepos.left=NaN,g.getInstance().setColorAt();else{a._mousepos.top=Math.floor((a._mousepos.top-Math.floor(n.top))/a._zoomlevel),a._mousepos.left=Math.floor((a._mousepos.left-Math.floor(n.left))/a._zoomlevel);var l=a.getImageCanvasCtx().getImageData(a._mousepos.left,a._mousepos.top,1,1).data;g.getInstance().setColorAt(e.rgb2hex({r:l[0],g:l[1],b:l[2]}),e.roundTo(l[3]/255*100,2)+"%")}if(g.getInstance().setPosition("mouse",a._mousepos),o&&r){var h={top:t.pageY,left:t.pageX},u=a.getWrapPosition(),p=c.snapPointToPixelGrid(o.top+(h.top-r.top)-u.top,a._zoomlevel),f=c.snapPointToPixelGrid(o.left+(h.left-r.left)-u.left,a._zoomlevel);a._$imageOverlay.add(a._$imageCanvas).add(a._$mouseOverlay).css({top:p,left:f}),a.updateZoomLevelInputs()}}})),$w.on("mousewheel",function(e){if(e.altKey){e.preventDefault();var t=a.getWrapPosition(),i={top:e.pageY-t.top,left:e.pageX-t.left};e.originalEvent.deltaY>0?a._$zoomout.trigger("click",[i]):a._$zoomin.trigger("click",[i])}}),this._$picker.on("mousewheel",function(e){if(!e.altKey){e.preventDefault();var t=a._wrapheight*(e.shiftKey?.1:.025)*Math.sign(e.originalEvent.wheelDelta);e.ctrlKey?a.move({left:"+="+t+"px"}):a.move({top:"+="+t+"px"})}}),$body.on("mousedown",function(t){m.getInstance().getActiveTab()&&e(t.target).is(a._$imageOverlay)&&a._$imageOverlay.hasClass("draggable")&&(t.preventDefault(),a._$imageOverlay.addClass("dragging"),o=a._$imageOverlay.offset(),r={top:t.pageY,left:t.pageX})}),$body.on("mouseup mouseleave blur",function(e){m.getInstance().getActiveTab()&&"mouseup"===e.type&&(o=t,r=t,a._$imageOverlay.removeClass("dragging"))})}return _createClass(s,[{key:"getTopLeft",value:function(e,a){var i=arguments.length>2&&arguments[2]!==t?arguments[2]:this.getWrapCenterPosition(),n=e.left,o=e.top,s=i.left,r=i.top;return{top:r+a*(o-r),left:s+a*(n-s)}}},{key:"getImageCanvasSize",value:function(){return{width:this._$imageCanvas.width(),height:this._$imageCanvas.height()}}},{key:"getImagePosition",value:function(){var e=arguments.length>0&&arguments[0]!==t?arguments[0]:this._$imageCanvas.offset(),a=this.getWrapPosition();return{top:e.top-a.top,left:e.left-a.left}}},{key:"getImageCenterPosition",value:function(e,t){var a=this.getWrapPosition();return{top:e.top-a.top+t.height/2,left:e.left-a.left+t.width/2}}},{key:"getWrapCenterPosition",value:function(){return{top:this._wrapheight/2,left:this._wrapwidth/2}}},{key:"getWrapPosition",value:function(){var e=this._$picker.offset();return e.top-=(this._wrapheight-this._$picker.outerHeight())/2,e.left-=(this._wrapwidth-this._$picker.outerWidth())/2,e}},{key:"placeArea",value:function(e,a){var i=!(arguments.length>2&&arguments[2]!==t)||arguments[2],n=m.getInstance().getActiveTab();n&&(n.placeArea(e,a,i),this.redrawPickingAreas())}},{key:"redrawPickingAreas",value:function(){var t=m.getInstance().getActiveTab();if(t){this.clearImageOverlay();var a=this.getImageOverlayCtx();a.fillStyle=t.getPickingAreaColor().toString(),e.each(t.getPickingAreas(),function(t,i){i instanceof r?a.fillRect(i.boundingRect.topLeft.x,i.boundingRect.topLeft.y,i.boundingRect.sideLength,i.boundingRect.sideLength):i instanceof l&&e.each(i.slices,function(e,t){var n=i.boundingRect.topLeft.x+t.skip,o=i.boundingRect.topLeft.y+e;a.fillRect(n,o,t.length,1)})})}}},{key:"clearImageOverlay",value:function(){o(this._$imageOverlay[0])}},{key:"updateZoomLevelInputs",value:function(){this._$zoomperc.text(e.roundTo(100*this._zoomlevel,2)+"%"),document.activeElement.blur(),this._$zoomout.attr("disabled",this._zoomlevel<=n.min),this._$zoomin.attr("disabled",this._zoomlevel>=n.max)}},{key:"setZoomLevel",value:function(t,a){var i=m.getInstance().getActiveTab();if(i){var o=i.getImageSize(),s=e.rangeLimit(t,!1,n.min,n.max),r=void 0,l=void 0;s!==this._zoomlevel?(r=e.scaleResize(o.width,o.height,{scale:s}),l=this._zoomlevel,this._zoomlevel=r.scale):(r={width:this._$imageCanvas.width(),height:this._$imageCanvas.height()},l=this._zoomlevel);var c=this.getTopLeft(this.getImagePosition(),s/l,a);this.move({top:c.top,left:c.left,width:r.width,height:r.height}),this.updateZoomLevelInputs()}}},{key:"setZoomFit",value:function(){var t=this;this._fitImageHandler(function(a){var i=t._wrapwidth>t._wrapheight,n=a.width===a.height?i:a.width>a.height,o=e.scaleResize(a.width,a.height,n?{height:t._wrapheight}:{width:t._wrapwidth});return i&&(o.width>t._wrapwidth?o=e.scaleResize(o.width,o.height,{width:t._wrapwidth}):o.height>t._wrapheight&&(o=e.scaleResize(o.width,o.height,{height:t._wrapheight}))),i||(o.height>t._wrapheight?o=e.scaleResize(o.width,o.height,{height:t._wrapheight}):o.width>t._wrapwidth&&(o=e.scaleResize(o.width,o.height,{width:t._wrapwidth}))),o})}},{key:"setZoomOriginal",value:function(){this._fitImageHandler(function(e,t){return{width:e.width,height:e.height,scale:1}})}},{key:"_fitImageHandler",value:function(e){var t=m.getInstance().getActiveTab();if(t){var a=e(t.getImageSize()),i=(this._wrapheight-a.height)/2,n=(this._wrapwidth-a.width)/2;this.move({top:i,left:n,width:a.width,height:a.height}),this._zoomlevel=a.scale,this.setZoomLevel(this._zoomlevel)}}},{key:"move",value:function(e){var a=arguments.length>1&&arguments[1]!==t&&arguments[1],i=m.getInstance().getActiveTab();i&&(this._$imageOverlay.add(this._$imageCanvas).add(this._$mouseOverlay).css(e),a||i.setImagePosition({top:this._$imageOverlay.css("top"),left:this._$imageOverlay.css("left"),width:this._$imageOverlay.css("width"),height:this._$imageOverlay.css("height")}))}},{key:"updateWrapSize",value:function(){this._wrapwidth=this._$picker.innerWidth(),this._wrapheight=this._$picker.innerHeight()}},{key:"resizeHandler",value:function(){this.updateWrapSize(),"number"==typeof this._zoomlevel&&this.setZoomLevel(this._zoomlevel),g.getInstance().setPosition("pickerCenter",this.getWrapCenterPosition())}},{key:"_setCanvasSize",value:function(e,t){this._$imageOverlay[0].width=this._$imageCanvas[0].width=e,this._$imageOverlay[0].height=this._$imageCanvas[0].height=t}},{key:"openImage",value:function(t,a,i){var n=this;if(this._$picker.hasClass("loading"))throw new Error("The picker is already loading another image");this._$picker.addClass("loading"),g.getInstance().setInfo();var o=CryptoJS.MD5(t).toString(),s=m.getInstance().getTabs(),r=void 0;if(e.each(s,function(e,t){if(t.getFileHash()===o)return r=t,!1}),void 0!==r)return this._$picker.removeClass("loading"),m.getInstance().activateTab(r),void i(!0);var l=m.getInstance().newTab(a,o);l.setImage(t,function(t){n._$picker.removeClass("loading"),t?m.getInstance().activateTab(l):e.Dialog.fail("Oh no","The provided image could not be loaded. This is usually caused by attempting to open a file that is, in fact, not an image."),i(t)})}},{key:"openTab",value:function(e){var t=e.getImageSize();if(!t)throw new Error("Attempt to open a tab without an image");this._$imageCanvas.appendTo(this._$picker),this._setCanvasSize(t.width,t.height),e.drawImage();var a=e.getImagePosition();a?this.move(a,!0):this.setZoomFit(),this.redrawPickingAreas()}},{key:"clearImage",value:function(){m.getInstance().getActiveTab()&&(this._$imageCanvas.detach(),o(this._$imageCanvas[0]),o(this._$imageOverlay[0]),g.getInstance().setColorAt(),g.getInstance().setPosition("mouse"),this._zoomlevel=1,this.updateZoomLevelInputs(),e.Dialog.close())}},{key:"moveMode",value:function(e){e&&!this._moveMode?(this._moveMode=!0,this._$imageOverlay.addClass("draggable")):!e&&this._moveMode&&(this._moveMode=!1,this._$imageOverlay.removeClass("draggable dragging"))}},{key:"getImageCanvasCtx",value:function(){return this._$imageCanvas[0].getContext("2d")}},{key:"getImageOverlayCtx",value:function(){return this._$imageOverlay[0].getContext("2d")}},{key:"getMouseOverlayCtx",value:function(){return this._$mouseOverlay[0].getContext("2d")}}],[{key:"getInstance",value:function(){return void 0===a.picker&&(a.picker=new s),a.picker}}]),s}();u.getInstance(),g.getInstance(),m.getInstance(),_.getInstance(),e(document).on("keydown",function(e){var t=e.target.tagName.toLowerCase();if(("input"!==t||"file"===e.target.type)&&"textarea"!==t&&null===e.target.getAttribute("contenteditable")){switch(e.keyCode){case i[0]:if(!e.ctrlKey||e.altKey)return;_.getInstance().setZoomFit();break;case i[1]:if(!e.ctrlKey||e.altKey)return;_.getInstance().setZoomOriginal();break;case i.Space:if(e.ctrlKey||e.altKey)return;_.getInstance().moveMode(!0);break;default:return}e.preventDefault()}}),e(document).on("keyup",function(e){var t=e.target.tagName.toLowerCase();if(("input"!==t||"file"===e.target.type)&&"textarea"!==t&&null===e.target.getAttribute("contenteditable")){switch(e.keyCode){case i.Space:if(e.ctrlKey||e.altKey)return;_.getInstance().moveMode(!1);break;case i.Alt:break;default:return}e.preventDefault()}}),e(document).on("paste","[contenteditable]",function(t){var a="",i=e(this);if(t.clipboardData?a=t.clipboardData.getData("text/plain"):window.clipboardData?a=window.clipboardData.getData("Text"):t.originalEvent.clipboardData&&(a=e.mk("div").text(t.originalEvent.clipboardData.getData("text"))),document.queryCommandSupported("insertText"))return document.execCommand("insertHTML",!1,e(a).html()),!1;i.find("*").each(function(){e(this).addClass("within")}),setTimeout(function(){i.find("*").each(function(){e(this).not(".within").contents().unwrap()})},1)}),$body.on("mouseenter","[data-info]",function(){g.getInstance().setInfo(e(this).attr("data-info"))}).on("mouseleave","[data-info]",function(){g.getInstance().setInfo()}).on("dragover dragend",function(e){e.stopPropagation(),e.preventDefault()}).on("drop",function(t){t.preventDefault();var a=t.originalEvent.dataTransfer.files;if(0!==a.length){var i=1!==a.length?"s":"";e.Dialog.wait("Drag and drop","Reading dropped file"+i+", please wait");var n=0;!function t(){void 0!==a[n]?u.getInstance().handleFileOpen(a[n],function(a){if(a)return n++,t();e.Dialog.fail("Drag and drop","Failed to read file #"+n+", aborting")}):e.Dialog.close()}()}}),window.Plugin=a}(jQuery);
//# sourceMappingURL=/js/min/colorpicker.js.map
