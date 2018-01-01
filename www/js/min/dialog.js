"use strict";var _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},_createClass=function(){function t(t,e){for(var i=0;i<e.length;i++){var o=e[i];o.enumerable=o.enumerable||!1,o.configurable=!0,"value"in o&&(o.writable=!0),Object.defineProperty(t,o.key,o)}}return function(e,i,o){return i&&t(e.prototype,i),o&&t(e,o),e}}();function _classCallCheck(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}!function(t,e){var i={fail:"red",success:"green",wait:"blue",request:"",confirm:"orange",info:"darkblue",segway:"lavander"},o={fail:"fail",success:"success",wait:"info",request:"warn",confirm:"caution",info:"info",segway:"reload"},n={fail:"Error",success:"Success",wait:"Sending request",request:"Input required",confirm:"Confirmation",info:"Info",segway:"Pending navigation"},a={fail:"There was an issue while processing the request.",success:"Whatever you just did, it was completed successfully.",wait:"Sending request",request:"The request did not require any additional info.",confirm:"Are you sure?",info:"No message provided.",segway:"A previous action requires reloading the current page. Press reload once you're ready."},l=function(){t.Dialog.close()},s=function(){function e(i,o){var n=this;_classCallCheck(this,e),this.label=i,t.each(o,function(t,e){return n[t]=e})}return _createClass(e,[{key:"setLabel",value:function(t){return this.label=t,this}},{key:"setFormId",value:function(t){return this.formid=t,this}}]),e}(),r=function(){function r(){_classCallCheck(this,r),this.$dialogOverlay=t("#dialogOverlay"),this.$dialogContent=t("#dialogContent"),this.$dialogHeader=t("#dialogHeader"),this.$dialogBox=t("#dialogBox"),this.$dialogWrap=t("#dialogWrap"),this.$dialogScroll=t("#dialogScroll"),this.$dialogButtons=t("#dialogButtons"),this._open=this.$dialogContent.length?{}:e,this._CloseButton=new s("Close",{action:l}),this._$focusedElement=e}return _createClass(r,[{key:"isOpen",value:function(){return"object"===_typeof(this._open)}},{key:"_display",value:function(l){var s=this;if("string"!=typeof l.type||void 0===i[l.type])throw new TypeError("Invalid dialog type: "+_typeof(l.type));l.content||(l.content=a[l.type]);var r=t.extend({content:a[l.type]},l);r.color=i[l.type];var d=Boolean(this._open),c=t.mk("div").append(r.content),u=d&&"request"===this._open.type&&["fail","wait"].includes(r.type)&&!r.forceNew,h=void 0;r.color.length&&c.addClass(r.color);var g=c.find(".tab-wrap");if(g.length>0){var f=function(t){var e=t.closest(".tab-wrap").find(".tab-contents");t.addClass("selected").siblings().removeClass("selected"),e.children().addClass("hidden").filter(".content-"+t.attr("data-content")).removeClass("hidden")};g.on("click",".tab-list .tab",function(){f(t(this))});var p=g.find(".tab-default");0===p.length&&(p=g.find(".tab").first()),f(p)}if(d)if(this.$dialogOverlay=t("#dialogOverlay"),this.$dialogBox=t("#dialogBox"),this.$dialogHeader=t("#dialogHeader"),"string"==typeof r.title&&this.$dialogHeader.text(r.title),this.$dialogContent=t("#dialogContent"),u){var y=(h=this.$dialogContent.children(":not(#dialogButtons)").last()).children(".notice:last-child");y.length?y.show():(y=t.mk("div").append(t.mk("p")),h.append(y)),y.attr("class","notice "+o[r.type]).children("p").html(r.content).show(),this._controlInputs("wait"===r.type)}else this._open=r,this.$dialogButtons=t("#dialogButtons").empty(),this._controlInputs(!0),this.$dialogContent.append(c),r.buttons&&(0===this.$dialogButtons.length&&(this.$dialogButtons=t.mk("div","dialogButtons")),this.$dialogButtons.appendTo(this.$dialogContent));else this._storeFocus(),this._open=r,this.$dialogOverlay=t.mk("div","dialogOverlay"),this.$dialogHeader=t.mk("div","dialogHeader"),"string"==typeof r.title?this.$dialogHeader.text(r.title):!1===r.title&&this.$dialogHeader.text(n[r.type]),this.$dialogContent=t.mk("div","dialogContent"),this.$dialogBox=t.mk("div","dialogBox").attr({role:"dialog","aria-labelledby":"dialogHeader"}),this.$dialogScroll=t.mk("div","dialogScroll"),this.$dialogWrap=t.mk("div","dialogWrap"),this.$dialogContent.append(c),this.$dialogButtons=t.mk("div","dialogButtons").appendTo(this.$dialogContent),this.$dialogBox.append(this.$dialogHeader).append(this.$dialogContent),this.$dialogOverlay.append(this.$dialogScroll.append(this.$dialogWrap.append(this.$dialogBox))).appendTo($body),$body.addClass("dialog-open"),this.$dialogOverlay.siblings().prop("inert",!0);if(u||(this.$dialogHeader.attr("class",r.color?r.color+"-bg":""),this.$dialogContent.attr("class",r.color?r.color+"-border":"")),!u&&r.buttons&&t.each(r.buttons,function(i,o){var n=t.mk("input").attr({type:"button",class:r.color?r.color+"-bg":e});o.form&&1===(h=t("#"+o.form)).length&&(n.on("click",function(){h.find("input[type=submit]").first().trigger("click")}),h.prepend(t.mk("input").attr("type","submit").hide().on("focus",function(t){t.preventDefault(),s.$dialogButtons.children().first().focus()}))),n.val(o.label).on("click",function(e){e.preventDefault(),t.callCallback(o.action,[e])}),s.$dialogButtons.append(n)}),window.withinMobileBreakpoint()||this._setFocus(),$w.trigger("dialog-opened"),Time.Update(),t.callCallback(r.callback,[h]),d){var v=this.$dialogContent.children(":not(#dialogButtons)").last();u&&(v=v.children(".notice").last()),this.$dialogOverlay.stop().animate({scrollTop:"+="+(v.position().top+parseFloat(v.css("margin-top"),10)+parseFloat(v.css("border-top-width"),10))},"fast")}}},{key:"fail",value:function(){var t=arguments.length>0&&arguments[0]!==e?arguments[0]:n.fail,i=arguments.length>1&&arguments[1]!==e?arguments[1]:a.fail,o=arguments.length>2&&arguments[2]!==e&&arguments[2];this._display({type:"fail",title:t,content:i,buttons:[this._CloseButton],forceNew:o})}},{key:"success",value:function(){var t=arguments.length>0&&arguments[0]!==e?arguments[0]:n.success,i=arguments.length>1&&arguments[1]!==e?arguments[1]:a.success,o=arguments.length>2&&arguments[2]!==e&&arguments[2],l=arguments.length>3&&arguments[3]!==e?arguments[3]:e;this._display({type:"success",title:t,content:i,buttons:o?[this._CloseButton]:e,callback:l})}},{key:"wait",value:function(){var i=arguments.length>0&&arguments[0]!==e?arguments[0]:n.wait,o=arguments.length>1&&arguments[1]!==e?arguments[1]:a.wait,l=arguments.length>2&&arguments[2]!==e&&arguments[2],s=arguments.length>3&&arguments[3]!==e?arguments[3]:e;this._display({type:"wait",title:i,content:t.capitalize(o)+"&hellip;",forceNew:l,callback:s})}},{key:"request",value:function(){var t=arguments.length>0&&arguments[0]!==e?arguments[0]:n.request,i=arguments.length>1&&arguments[1]!==e?arguments[1]:a.request,o=arguments.length>2&&arguments[2]!==e?arguments[2]:"Submit",r=arguments.length>3&&arguments[3]!==e?arguments[3]:e;"function"==typeof o&&void 0===r&&(r=o,o=e);var d=[],c=void 0;if(i instanceof jQuery)c=i.attr("id");else if("string"==typeof i){var u=i.match(/<form\sid=["']([^"']+)["']/);u&&(c=u[1])}!1!==o?(c&&d.push(new s(o,{submit:!0,form:c})),d.push(new s("Cancel",{action:l}))):d.push(new s("Close",{action:l})),this._display({type:"request",title:t,content:i,buttons:d,callback:r})}},{key:"confirm",value:function(){var i=arguments.length>0&&arguments[0]!==e?arguments[0]:n.confirm,o=arguments.length>1&&arguments[1]!==e?arguments[1]:a.confirm,r=this,d=arguments.length>2&&arguments[2]!==e?arguments[2]:["Eeyup","Nope"],c=arguments.length>3&&arguments[3]!==e?arguments[3]:e;void 0===c&&(c="function"==typeof d?d:l),t.isArray(d)||(d=["Eeyup","Nope"]);var u=[new s(d[0],{action:function(){c(!0)}}),new s(d[1],{action:function(){c(!1),r._CloseButton.action()}})];this._display({type:"confirm",title:i,content:o,buttons:u})}},{key:"info",value:function(){var t=arguments.length>0&&arguments[0]!==e?arguments[0]:n.info,i=arguments.length>1&&arguments[1]!==e?arguments[1]:a.info,o=arguments.length>2&&arguments[2]!==e?arguments[2]:e;this._display({type:"info",title:t,content:i,buttons:[this._CloseButton],callback:o})}},{key:"segway",value:function(){var i=arguments.length>0&&arguments[0]!==e?arguments[0]:n.reload,o=arguments.length>1&&arguments[1]!==e?arguments[1]:a.reload,l=arguments.length>2&&arguments[2]!==e?arguments[2]:"Reload",r=arguments.length>3&&arguments[3]!==e?arguments[3]:e;void 0===r&&"function"==typeof l&&(r=l,l="Reload"),this._display({type:"segway",title:i,content:o,buttons:[new s(l,{action:function(){t.callCallback(r),t.Navigation.reload(!0)}})]})}},{key:"setFocusedElement",value:function(t){t instanceof jQuery&&(this._$focusedElement=t)}},{key:"_storeFocus",value:function(){if(!(void 0!==this._$focusedElement&&this._$focusedElement instanceof jQuery)){var i=t(":focus");this._$focusedElement=i.length>0?i.last():e}}},{key:"_restoreFocus",value:function(){void 0!==this._$focusedElement&&this._$focusedElement instanceof jQuery&&(this._$focusedElement.focus(),this._$focusedElement=e)}},{key:"_setFocus",value:function(){var t=this.$dialogContent.find("input,select,textarea").filter(":visible"),e=this.$dialogButtons.children();t.length>0?t.first().focus():e.length>0&&e.first().focus()}},{key:"_controlInputs",value:function(t){var e=this.$dialogContent.children(":not(#dialogButtons)").last().add(this.$dialogButtons).find("input, button, select, textarea");t?e.filter(":not(:disabled)").addClass("temp-disable").disable():e.filter(".temp-disable").removeClass("temp-disable").enable()}},{key:"close",value:function(i){if(!this.isOpen())return t.callCallback(i,!1);this.$dialogOverlay.siblings().prop("inert",!1),this.$dialogOverlay.remove(),this._open=e,this._restoreFocus(),t.callCallback(i),$body.removeClass("dialog-open")}},{key:"clearNotice",value:function(t){var e=this.$dialogContent.children(":not(#dialogButtons)").children(".notice:last-child");return!!e.length&&(!(void 0!==t&&!t.test(e.html()))&&(e.hide(),e.hasClass("info")&&this._controlInputs(!1),!0))}}]),r}();t.Dialog=new r;var d=function(){t.Dialog.isOpen()&&window.withinMobileBreakpoint()&&t.Dialog.$dialogContent.css("margin-top",t.Dialog.$dialogHeader.outerHeight())};$w.on("resize",t.throttle(200,d)).on("dialog-opened",d)}(jQuery);
//# sourceMappingURL=/js/min/dialog.js.map
