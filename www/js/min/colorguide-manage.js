"use strict";function _classCallCheck(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function _possibleConstructorReturn(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}function _inherits(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}}),e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}var _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol?"symbol":typeof t};DocReady.push(function(){function t(t,e,n){var i="Create new tag",o=t.closest(".tags"),s=o.closest("[id^=p]"),l=s.attr("id").substring(1),c=u?$content.children("h1").text():o.siblings("strong").text().trim();$.Dialog.request(i,b.clone(!0,!0),"Create",function(t){t.children(".edit-only").replaceWith($.mk("label").append($.mk("input").attr({type:"checkbox",name:"addto"}).val(l).prop("checked","string"==typeof e),' Add this tag to the appearance "'+c+'" after creation')),"string"==typeof n&&"undefined"!=typeof r[n]&&t.find("input[name=type][value="+n+"]").prop("checked",!0).trigger("change"),"string"==typeof e&&t.find("input[name=name]").val(e),t.on("submit",function(e){e.preventDefault();var n=t.mkData();$.Dialog.wait(!1,"Creating tag"),n.addto&&u&&(n.APPEARANCE_PAGE=!0),$.post("/cg/maketag"+d,n,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);if(this.tags&&(o.children("[data-hasqtip]").qtip("destroy",!0),o.html(this.tags),window.tooltips(),a()),this.needupdate===!0){var t=$(this.eps);f.replaceWith(t),f=t}$._tagAutocompleteCache={},$.Dialog.close()}))})})}function e(t,e){var n=this,i=void 0,o=void 0;"undefined"!=typeof e&&(e instanceof jQuery?(o=e.attr("id").substring(2),i=e.parents("[id^=p]").attr("id").substring(1)):i=e),$.Dialog.request(t,k.clone(!0,!0),"Save",function(t){var r=t.find("input[name=label]"),s=t.find("input[name=major]"),l=t.find("input[name=reason]"),c="object"===("undefined"==typeof n?"undefined":_typeof(n))&&n.label&&n.Colors;c&&(r.val(n.label),t.data("color_values",n.Colors).trigger("render-color-inputs")),t.on("submit",function(n){n.preventDefault();try{t.trigger("save-color-inputs",[!0])}catch(p){if(!(p instanceof g))throw p;var m=t.find(".clrs").data("editor");return m.gotoLine(p.lineNumber),m.navigateLineEnd(),$.Dialog.fail(!1,p.message),void m.focus()}var f={label:r.val(),Colors:t.data("color_values")};return c||(f.ponyid=i),0===f.Colors.length?$.Dialog.fail(!1,"You need to have at least 1 valid color"):(f.Colors=JSON.stringify(f.Colors),s.is(":checked")&&(f.major=!0,f.reason=l.val()),u&&(f.APPEARANCE_PAGE=!0),$.Dialog.wait(!1,"Saving changes"),void $.post("/cg/"+(c?"set":"make")+"cg"+(c?"/"+o:"")+d,f,$.mkAjaxHandler(function(){var t=this;return this.status?void(this.cg||this.cgs?!function(){var n=$("#p"+i);if(t.cg?(e.children("[data-hasqtip]").qtip("destroy",!0),e.html(t.cg),t.update&&e.parents("li").find(".update").html(t.update)):t.cgs&&(t.update&&n.find(".update").html(t.update),n.find("ul.colors").html(t.cgs)),!u&&t.notes){var o=n.find(".notes");try{o.find(".cm-direction").qtip("destroy",!0)}catch(r){}o.html(t.notes)}window.tooltips(),a(),t.update&&Time.Update();var s=$("#pony-cm");u&&s.length&&t.cm_img?!function(){$.Dialog.success(!1,"Color group updated"),$.Dialog.wait(!1,"Updating cutie mark orientation image");var e=new Image;e.src=t.cm_img,$(e).on("load error",function(){s.backgroundImageUrl(e.src),$.Dialog.close()})}():$.Dialog.close()}():$.Dialog.close()):$.Dialog.fail(!1,this.message)})))})})}function a(){P.children("span:not(.ctxmenu-bound)").ctxmenu([{text:"Edit tag",icon:"pencil",click:function(){var t=$(this),e=t.text().trim(),a=t.attr("class").match(/id-(\d+)(?:\s|$)/)[1],n="Editing tag: "+e;$.Dialog.wait(n,"Retrieveing tag details from server"),$.post("/cg/gettag/"+a+d,$.mkAjaxHandler(function(){var t=this;this.status?$.Dialog.request(n,b.clone(!0,!0).data("tag",t),"Save",function(e){e.find("input[name=type][value="+t.type+"]").prop("checked",!0),e.find("input[type=text][name], textarea[name]").each(function(){var e=$(this);e.val(t[e.attr("name")])}),e.on("submit",function(t){t.preventDefault();var n=e.mkData();$.Dialog.wait(!1,"Saving changes"),$.post("/cg/settag/"+a+d,n,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var t=this,e=$(".id-"+t.tid);e.qtip("destroy",!0),t.title?e.attr("title",t.title):e.removeAttr("title"),e.text(t.name).data("ctxmenu-items").eq(0).text("Tag: "+t.name),e.each(function(){/typ-[a-z]+/.test(this.className)?this.className=this.className.replace(/typ-[a-z]+/,t.type?"typ-"+t.type:""):t.type&&(this.className+=" typ-"+t.type),$(this)[t.synonym_of?"addClass":"removeClass"]("synonym").parent().reorderTags()}),window.tooltips(),$.Dialog.close()}))})}):$.Dialog.fail(n,this.message)}))}},{text:"Remove tag",icon:"minus",click:function(){var t=$(this),e=t.attr("class").match(/id-(\d+)(?:\s|$)/);if(!e)return!1;e=e[1];var a=t.closest("[id^=p]").attr("id").replace(/\D/g,""),n=t.text().trim(),i="Remove tag: "+n;$.Dialog.confirm(i,"The tag <strong>"+n+"</strong> will be removed from this appearance.<br>Are you sure?",["Remove it","Nope"],function(n){if(n){var o={tag:e};$.Dialog.wait(i,"Removing tag"),u&&(o.APPEARANCE_PAGE=!0),$.post("/cg/untag/"+a+d,o,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(i,this.message);if(this.needupdate===!0){var a=$(this.eps);f.replaceWith(a),f=a}t.qtip("destroy",!0),t.remove(),$(".tag.synonym").filter("[data-syn-of="+e+"]").remove(),$.Dialog.close()}))}})}},{text:"Delete tag",icon:"trash",click:function(){var t=$(this),e=t.text().trim(),a=t.attr("class").match(/id-(\d+)(?:\s|$)/)[1],n="Detele tag: "+e;$.Dialog.confirm(n,"Deleting this tag will also remove it from every appearance where it's been used.<br>Are you sure?",["Delete it","Nope"],function(e){if(e){var i={};u&&(i.APPEARANCE_PAGE=t.closest("[id^=p]").attr("id").substring(1)),function o(t){$.Dialog.wait(n,"Sending removal request"),$.post("/cg/deltag/"+a+d,t,$.mkAjaxHandler(function(){if(this.status){if(this.needupdate===!0){var e=$(this.eps);f.replaceWith(e),f=e}var i=$(".id-"+a);i.qtip("destroy",!0),i.remove(),$._tagAutocompleteCache={},$.Dialog.close()}else this.confirm?$.Dialog.confirm(!1,this.message,["NUKE TAG","Nevermind"],function(e){e&&(t.sanitycheck=!0,o(t))}):$.Dialog.fail(n,this.message)}))}(i)}})}},$.ctxmenu.separator,{text:"Create new tag",icon:"plus",click:function(){$.ctxmenu.triggerItem($(this).parent(),1)}}],function(t){return"Tag: "+t.text().trim()});var n=[Key.Enter,Key.Comma];P.children(".addtag").each(function(){var e=$(this),i=e.closest("[id^=p]").attr("id").substring(1);e.autocomplete({minLength:3},[{name:"tags",display:"name",source:function(t,e){if("undefined"==typeof $._tagAutocompleteCache)$._tagAutocompleteCache={};else if("undefined"!=typeof $._tagAutocompleteCache[t])return e($._tagAutocompleteCache[t]);$.get("/cg/gettags?s="+encodeURIComponent(t),$.mkAjaxHandler(function(){e($._tagAutocompleteCache[t]=this)}))},templates:{suggestion:Handlebars.compile('<span class="tag id-{{tid}} {{type}} {{#if synonym_of}}synonym{{else}}monospace{{/if}}">{{name}} <span class="uses">{{#if synonym_of}}<span class="typcn typcn-flow-children"></span>{{synonym_target}}{{else}}{{uses}}{{/if}}</span></span>')}}]),e.on("keydown",function(o){if(n.includes(o.keyCode)){var r=function(){o.preventDefault();var n=e.val().trim(),r=e.parents(".tags"),s=r.children(".tag"),l="Adding tag: "+n;if(s.filter(function(){return this.innerHTML.trim()===n}).length>0)return{v:$.Dialog.fail(l,"This appearance already has this tag")};$.Dialog.setFocusedElement(e.attr("disabled",!0)),e.parent().addClass("loading"),e.autocomplete("val",n);var c={tag_name:n};u&&(c.APPEARANCE_PAGE=!0),$.post("/cg/tag/"+i+d,c,$.mkAjaxHandler(function(){var i=this;if(e.removeAttr("disabled").parent().removeClass("loading"),this.status){if(this.needupdate===!0){var o=$(this.eps);f.replaceWith(o),f=o}r.children("[data-hasqtip]").qtip("destroy",!0),r.children(".tag").remove(),r.append($(this.tags).filter("span")),window.tooltips(),a(),$._tagAutocompleteCache={},e.autocomplete("val","").focus()}else if("string"==typeof this.cancreate){var s=function(){var a=i.cancreate,o=i.typehint;return l=l.replace(n,a),{v:$.Dialog.confirm(l,i.message,function(n){n&&t(e,a,o)})}}();if("object"===("undefined"==typeof s?"undefined":_typeof(s)))return s.v}else $.Dialog.fail(l,this.message)}))}();if("object"===("undefined"==typeof r?"undefined":_typeof(r)))return r.v}}),e.nextAll(".aa-menu").on("click",".tag",function(){e.trigger({type:"keydown",keyCode:Key.Enter})})}),s=$("ul.colors").attr("data-color",o),s.filter(":not(.ctxmenu-bound)").ctxmenu([{text:"Re-order "+o+" groups",icon:"arrow-unsorted",click:function(){var t=$(this),e=t.closest("[id^=p]"),n=e.attr("id").substring(1),i=u?$content.children("h1").text():e.children().last().children("strong").text().trim(),r="Re-order "+o+" groups on appearance: "+i;$.Dialog.wait(r,"Retrieving color group list from server"),$.post("/cg/getcgs/"+n+d,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(this.message);var e=$.mk("form","cg-reorder"),i=$.mk("ol");$.each(this.cgs,function(t,e){i.append($.mk("li").attr("data-id",e.groupid).text(e.label))}),e.append($.mk("div").attr("class","cgs").append('<p class="align-center">Drag to re-arrange</p>',i)),new Sortable(i.get(0),{ghostClass:"moving",scroll:!1,animation:150}),$.Dialog.request(r,e,"Save",function(e){e.on("submit",function(i){i.preventDefault();var o={cgs:[]},r=e.children(".cgs");return r.length?(r.find("ol").children().each(function(){o.cgs.push($(this).attr("data-id"))}),o.cgs=o.cgs.join(","),$.Dialog.wait(!1,"Saving changes"),u&&(o.APPEARANCE_PAGE=!0),void $.post("/cg/setcgs/"+n+d,o,$.mkAjaxHandler(function(){return this.status?(t.html(this.cgs),window.tooltips(),a(),void $.Dialog.close()):$.Dialog.fail(null,this.message)}))):$.Dialog.fail(!1,"There are no color groups to re-order")})})}))}},{text:"Create new group",icon:"folder-add",click:function(){e("Create "+o+" group",$(this).closest("[id^=p]").attr("id").substring(1))}},{text:"Apply template (if empty)",icon:"document-add",click:function(){var t=$(this).closest("[id^=p]").attr("id").substring(1);$.Dialog.confirm("Apply template on appearance","Add common color groups to this appearance?<br>Note: This will only work if there are no color groups currently present.",function(e){e&&($.Dialog.wait(!1,"Applying template"),$.post("/cg/applytemplate/"+t+d,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var e=$("#p"+t);e.find("ul.colors").html(this.cgs),window.tooltips(),a(),$.Dialog.close()})))})}}],i+" groups"),s.children("li").filter(":not(.ctxmenu-bound)").ctxmenu([{text:"Edit "+o+" group",icon:"pencil",click:function(){var t=$(this),a=t.closest("li"),n=a.attr("id").substring(2),i=t.children().first().text().replace(/:\s?$/,""),r="Editing "+o+" group: "+i;$.Dialog.wait(r,"Retrieving "+o+" group details from server"),$.post("/cg/getcg/"+n+d,$.mkAjaxHandler(function(){return this.status?void e.call(this,r,a):$.Dialog.fail(r,this.message)}))}},{text:"Delete "+o+" group",icon:"trash",click:function(){var t=$(this).closest("li"),e=t.attr("id").substring(2),a=t.children().first().text().replace(/:\s?$/,""),n="Delete "+o+" group: "+a;$.Dialog.confirm(n,"By deleting this "+o+" group, all "+o+"s within will be removed too.<br>Are you sure?",function(a){a&&($.Dialog.wait(n,"Sending removal request"),$.post("/cg/delcg/"+e+d,$.mkAjaxHandler(function(){this.status?(t.children("[data-hasqtip]").qtip("destroy",!0),t.remove(),$.Dialog.close()):$.Dialog.fail(n,this.message)})))})}},$.ctxmenu.separator,{text:"Re-order "+o+" groups",icon:"arrow-unsorted",click:function(){$.ctxmenu.triggerItem($(this).parent(),1)}},{text:"Create new group",icon:"folder-add",click:function(){$.ctxmenu.triggerItem($(this).parent(),2)}}],function(t){return i+" group: "+t.children().first().text().trim().replace(":","")});var r=s.children("li").find(".valid-color");$.ctxmenu.addItems(r.filter(".ctxmenu-bound"),$.ctxmenu.separator,{text:"Edit "+o+" group",icon:"pencil",click:function(){$.ctxmenu.triggerItem($(this).parent().closest(".ctxmenu-bound"),1)}},{text:"Delete "+o+" group",icon:"trash",click:function(){$.ctxmenu.triggerItem($(this).parent().closest(".ctxmenu-bound"),2)}},$.ctxmenu.separator,{text:"Re-order "+o+" groups",icon:"arrow-unsorted",click:function(){$.ctxmenu.triggerItem($(this).parent().closest(".ctxmenu-bound"),3)}},{text:"Create new group",icon:"folder-add",click:function(){$.ctxmenu.triggerItem($(this).parent().closest(".ctxmenu-bound"),4)}}),$(".upload-wrap").filter(":not(.ctxmenu-bound)").each(function(){var t=$(this),e=t.closest("li");e.length||(e=$content.children("[id^=p]"));var a=e.attr("id").substring(1);!function(t,e){var a=void 0,n=void 0,i=function(){a=t.find("img").attr("src"),n=a.indexOf("blank-pixel.png")===-1,t[n?"removeClass":"addClass"]("nosprite"),$.ctxmenu.setDefault(t,n?1:3)};t.uploadZone({requestKey:"sprite",title:"Upload sprite",accept:"image/png",target:"/cg/setsprite/"+e}).on("uz-uploadstart",function(){$.Dialog.close()}).on("uz-uploadfinish",function(){i()}).ctxmenu([{text:"Open image in new tab",icon:"arrow-forward",click:function(){window.open(t.find("img").attr("src"),"_blank")}},{text:"Copy image URL",icon:"clipboard",click:function(){$.copy($.toAbsoluteURL(t.find("img").attr("src")))}},{text:"Check sprite colors",icon:"adjust-contrast",click:function(){$.Navigation.visit("/cg/sprite/"+e)}},{text:"Upload new sprite",icon:"upload",click:function(){var a="Upload sprite image",n=t.find('input[type="file"]');$.Dialog.request(a,m.clone(),"Download image",function(t){var i=t.find("input[name=image_url]");t.find("a").on("click",function(t){t.preventDefault(),t.stopPropagation(),n.trigger("click",[!0])}),t.on("submit",function(t){t.preventDefault();var o=i.val();$.Dialog.wait(a,"Downloading external image to the server"),$.post("/cg/setsprite/"+e+d,{image_url:o},$.mkAjaxHandler(function(){this.status?n.trigger("set-image",[this.path]):$.Dialog.fail(a,this.message)}))})})}},{text:"Remove sprite image",icon:"times",click:function(){$.Dialog.confirm("Remove sprite image","Are you sure you want to <strong>permanently delete</strong> the sprite image from the server?",["Wipe it","Nope"],function(a){a&&($.Dialog.wait(!1,"Removing image"),$.post("/cg/delsprite/"+e,$.mkAjaxHandler(function(){return this.status?(t.find("img").attr("src",this.sprite),i(),void $.Dialog.close()):$.Dialog.fail(!1,this.message)})))})}}],"Sprite image").attr("title",c?" ":"").on("click",function(e,a){return a===!0||(e.preventDefault(),void $.ctxmenu.runDefault(t))}),i()}(t,a)})}function n(){$("button.edit:not(.bound)").addClass("bound").on("click",function(){var t=$(this),e=t.closest("[id^=p]"),a=e.attr("id").substring(1),n=u?$content.children("h1").text():t.parent().text().trim(),i="Editing appearance: "+n;$.Dialog.wait(i,"Retrieving appearance details from server"),$.post("/cg/get/"+a+d,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var e=this;e.ponyID=a,y(t,i,e)}))}).next(".delete").on("click",function(){var t=$(this),e=t.closest("[id^=p]"),a=e.attr("id").substring(1),n=u?$content.children("h1").text():t.parent().text().trim(),i="Deleting appearance: "+n;$.Dialog.confirm(i,"Deleting this appearance will remove <strong>ALL</strong> of its color groups, the colors within them, and the sprite file, if any.<br>Delete anyway?",function(t){t&&($.Dialog.wait(i,"Sending removal request"),$.post("/cg/delete/"+a+d,$.mkAjaxHandler(function(){if(this.status){e.remove(),$.Dialog.success(i,this.message);var t=window.location.pathname;0===h.children().length&&(t=t.replace(/(\d+)$/,function(t){return t>1?t-1:t})),u?($.Dialog.wait("Navigation","Loading page 1"),$.Navigation.visit("/cg/1",function(){$.Dialog.close()})):$.toPage(t,!0,!0)}else $.Dialog.fail(i,this.message)})))})}),P=$(".tags").ctxmenu([{text:"Create new tag",icon:"plus",click:function(){t($(this))}}],"Tags"),a()}var i=window.Color,o=window.color,r=window.TAG_TYPES_ASSOC,s=void 0,l=window.HEX_COLOR_PATTERN,c="WebkitAppearance"in document.documentElement.style,p=window.EQG,d=p?"?eqg":"",u=!!window.AppearancePage,g=function(t,e,a){this.message="Parse error on line "+e+' (shown below)\n\t\t\t\t<pre style="font-size:16px"><code>'+t.replace(/</g,"&lt;")+"</code></pre>"+(a&&!a[2]?"The color name is missing from this line.":"Please check for any errors before continuing."),this.lineNumber=e},m=$.mk("form","sprite-upload").html('<p class="align-center"><a href="#upload">Click here to upload a file</a> (max. '+window.MAX_SIZE+') or enter a URL below.</p>\n\t\t<label><input type="text" name="image_url" placeholder="External image URL" required></label>\n\t\t<p class="align-center">The URL will be checked against the supported provider list, and if an image is found, it\'ll be downloaded to the server and set as this appearance\'s sprite image.</p>'),f=void 0;u&&(f=$("#ep-appearances")),$.fn.reorderTags=function(){return this.each(function(){$(this).children(".tag").sort(function(t,e){var a=/^.*typ-([a-z]+).*$/;return t=[t.className.replace(a,"$1"),t.innerHTML.trim()],e=[e.className.replace(a,"$1"),e.innerHTML.trim()],t[0]===e[0]?t[1].localeCompare(e[1]):t[0].localeCompare(e[0])}).appendTo(this)})};var h=$(".appearance-list"),v=$.mk("form","pony-editor").append('<label>\n\t\t\t\t\t<span>Name (4-70 chars.)</span>\n\t\t\t\t\t<input type="text" name="label" placeholder="Enter a name" pattern="'+PRINTABLE_ASCII_PATTERN.replace("+","{4,70}")+'" required maxlength="70">\n\t\t\t\t</label>\n\t\t\t\t<div class="label">\n\t\t\t\t\t<span>Additional notes (1000 chars. max, optional)</span>\n\t\t\t\t\t<div class="ace_editor"></div>\n\t\t\t\t</div>\n\t\t\t\t<label>\n\t\t\t\t\t<span>Link to cutie mark (optional)</span>\n\t\t\t\t\t<input type="text" name="cm_favme" placeholder="DeviantArt submission URL">\n\t\t\t\t</label>\n\t\t\t\t<div class="align-center">\n\t\t\t\t\t<p>Cutie mark orientation</p>\n\t\t\t\t\t<div class="radio-group">\n\t\t\t\t\t\t<label><input type="radio" name="cm_dir" value="ht" required disabled><span>Head-Tail</span></label>\n\t\t\t\t\t\t<label><input type="radio" name="cm_dir" value="th" required disabled><span>Tail-Head</span></label>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<label>\n\t\t\t\t\t<span>Link to CM preview image (optional)</span>\n\t\t\t\t\t<input type="text" name="cm_preview" placeholder="Separate preview image">\n\t\t\t\t</label>\n\t\t\t\t<p class="notice info">The preview of the linked CM above will be used if the preview field is left empty.</p>'),y=function(t,e,a){var n=!!a,i=t.parents("[id^=p]"),o=i.find(".notes"),r=void 0;if(u){if(!n)return;r=$content.children("h1")}else r=t.siblings().first();$.Dialog.request(e,v.clone(!0,!0),"Save",function(t){var i=t.find("input[name=cm_favme]").on("change blur paste keyup",function(){var t=0===this.value.trim().length,e=$(this).parent().next();e.find("input").attr("disabled",t),e.next().find("input").attr("disabled",t)}),s=void 0,l=void 0;$.getAceEditor(!1,"html",function(e){try{var i=t.find(".ace_editor").get(0),o=ace.edit(i);l=$.aceInit(o,e),l.setMode(e),l.setUseWrapMode(!0),n&&a.notes&&l.setValue(a.notes)}catch(r){console.error(r)}}),n?(s=a.ponyID,t.find("input[name=label]").val(a.label),a.cm_favme&&i.val(a.cm_favme),a.cm_preview&&t.find("input[name=cm_preview]").val(a.cm_preview),a.cm_dir&&t.find("input[name=cm_dir]").enable().filter("[value="+a.cm_dir+"]").prop("checked",!0),t.append($.mk("div").attr("class","align-center").append($.mk("button").attr("class","blue typcn typcn-image").text("Update rendered image").on("click",function(t){t.preventDefault(),$.Dialog.close(),$.Dialog.wait("Clear appearance image cache","Clearing cache"),$.post("/cg/clearrendercache/"+s,$.mkAjaxHandler(function(){return this.status?void $.Dialog.success(!1,this.message,!0):$.Dialog.fail(!1,this.message)}))})))):t.append("<label><input type='checkbox' name='template'> Pre-fill with common color groups</label>"),i.triggerHandler("change"),t.on("submit",function(a){a.preventDefault();var i=t.mkData();i.notes=l.getValue(),$.Dialog.wait(!1,"Saving changes"),u&&(i.APPEARANCE_PAGE=!0),$.post("/cg/"+(n?"set/"+s:"make")+d,i,$.mkAjaxHandler(function(){return this.status?(i=this,void(n?u?($.Dialog.wait(!1,"Reloading page",!0),$.Navigation.reload(function(){$.Dialog.close()})):(r.text(i.label),i.newurl&&r.attr("href",function(t,e){return e.replace(/\/[^\/]+$/,"/"+i.newurl)}),o.html(this.notes),window.tooltips(),$.Dialog.close()):($.Dialog.success(e,"Appearance added"),$.Dialog.wait(e,"Loading appearance page"),$.Navigation.visit("/cg/v/"+i.id,function(){i.info?$.Dialog.info(e,i.info):$.Dialog.close()})))):$.Dialog.fail(!1,this.message)}))})})};$("#new-appearance-btn").on("click",function(){y($(this),"Add new "+(p?"Character":"Pony"))});var b=$.mk("form","edit-tag");b.append('<label><span>Tag name (3-30 chars.)</span><input type="text" name="name" required pattern="^.{3,30}$" maxlength="30"></label>');var x='<div class=\'type-selector\'>\n\t\t\t<label>\n\t\t\t\t<input type="radio" name="type" value="" checked>\n\t\t\t\t<span class="tag">Typeless</span>\n\t\t\t</label>';$.each(r,function(t,e){x+='<label>\n\t\t\t\t<input type="radio" name="type" value="'+t+'">\n\t\t\t\t<span class="tag typ-'+t+'">'+e+"</span>\n\t\t\t</label>"}),x+="</div>",b.append('<div class="align-center">\n\t\t\t<span>Tag type</span><br>\n\t\t\t'+x+'\n\t\t</div>\n\t\t<label>\n\t\t\t<span>Tag description (max 255 chars., optional)</span>\n\t\t\t<textarea name="title" maxlength="255"></textarea>\n\t\t</label>',$.mk("div").attr("class","align-center edit-only").append($.mk("button").attr("class","blue typcn typcn-flow-merge merge").html("Merge&hellip;"),$.mk("button").attr("class","blue typcn typcn-flow-children synon").html("Synonymize&hellip;")).on("click","button",function(t){t.preventDefault();var e=$(this).closest("form"),n=e.data("tag"),i=n.name,o=n.tid,r=this.className.split(" ").pop();$.Dialog.close(function(){window.CGTagEditing(i,o,r,function(t){var e=$(".tag.id-"+o),n=void 0;if(e.length)switch(t){case"synon":n=this.target,e.addClass("synonym");var i=e.eq(0).clone().removeClass("ctxmenu-bound"),r=new w(n),s=e.add($(".tag.id-"+n.tid)).closest(".tags");s.filter(function(){return 0===$(this).children(".id-"+o).length}).append(i).reorderTags(),s.filter(function(){return 0===$(this).children(".id-"+n.tid).length}).append(r).reorderTags(),window.tooltips(),a();break;case"unsynon":this.keep_tagged?e.removeClass("synonym"):e.remove();break;case"merge":n=this.target,e.each(function(){var t=$(this);0===t.siblings(".id-"+n.tid).length?t.replaceWith(new w(n)):t.remove()}),window.tooltips(),a()}$.Dialog.close()})})}));var w=function(t){function e(t){var a,n;return _classCallCheck(this,e),n=(a=_possibleConstructorReturn(this,Object.getPrototypeOf(e).call(this,'<span class="tag id-'+t.tid+(t.type?" typ-"+t.type:"")+(t.synonym_of?" synonym":"")+'" data-syn-of="'+t.synonym_of+'">'))).attr("title",t.title).text(t.name),_possibleConstructorReturn(a,n)}return _inherits(e,t),e}(jQuery),k=$.mk("form","cg-editor"),D=$.mk("input").attr({"class":"clri",autocomplete:"off",spellcheck:"false"}).patternAttr(l).on("keyup change input",function(t,e){var a=$(this),n=a.prev(),i=("string"==typeof e?e:this.value).trim(),o=l.test(i);o?n.removeClass("invalid").css("background-color",i.replace(l,"#$1")):n.addClass("invalid"),a.next().attr("required",o)}).on("paste blur keyup",function(t){var e=this,a=function(){var a=$.hexpand(e.value);l.test(a)&&!function(){a=a.replace(l,"#$1").toUpperCase();var n=$(e),i=$.hex2rgb(a);switch($.each(i,function(t,e){e<=3?i[t]=0:e>=252&&(i[t]=255)}),a=$.rgb2hex(i),t.type){case"paste":n.next().focus();case"blur":n.val(a)}n.trigger("change",[a]).patternAttr(SHORT_HEX_COLOR_PATTERN.test(e.value)?SHORT_HEX_COLOR_PATTERN:l)}()};"paste"===t.type?setTimeout(a,10):a()}),A=$.mk("input").attr({"class":"clrl",pattern:PRINTABLE_ASCII_PATTERN.replace("+","{3,30}")}),_=$.mk("div").attr("class","clra").append($.mk("span").attr("class","typcn typcn-minus remove red").on("click",function(){$(this).closest(".clr").remove()})).append($.mk("span").attr("class","typcn typcn-arrow-move move blue")),C=function(t){var e=D.clone(!0,!0),a=A.clone(),n=_.clone(!0,!0),i=$.mk("div").attr("class","clr");return"object"===("undefined"==typeof t?"undefined":_typeof(t))&&(t.hex&&e.val(t.hex),t.label&&a.val(t.label)),i.append("<span class='clrp'></span>",e,a,n),e.trigger("change"),i},E=$.mk("button").attr("class","typcn typcn-plus green add-color").text("Add new color").on("click",function(t){t.preventDefault();var e=$(this).parents("#cg-editor"),a=e.children(".clrs");if(a.length||e.append(a=$.mk("div").attr("class","clrs")),a.hasClass("ace_editor")){var n=a.data("editor");n.clearSelection(),n.navigateLineEnd();var i=n.getCursorPosition(),o=i.row+1,r=0===i.column,s=window.copyHashEnabled();r||o++,n.insert((r?"":"\n")+(s?"#":"")+"\tColor Name"),n.gotoLine(o,Number(s)),n.focus()}else{var l=C();a.append(l),l.find(".clri").focus()}}),T=function(t){for(var e=[],a=t.split("\n"),n=0,i=a.length;n<i;n++){var o=a[n];if(!/^(\/\/.*)?$/.test(o)){var r=o.trim().match(/^#([a-f\d]{6}|[a-f\d]{3})(?:\s*([a-z\d][ -~]{2,29}))?$/i);if(!r||!r[2])throw new g(o,n+1,r);e.push({hex:$.hexpand(r[1]),label:r[2]})}}return e},R=$.mk("button").attr("class","typcn typcn-document-text darkblue").text("Plain text editor").on("click",function(t){t.preventDefault();var e=$(this),a=e.parents("#cg-editor");e.disable();try{a.trigger("save-color-inputs")}catch(n){if(!(n instanceof g))throw n;var i=a.find(".clrs").data("editor");return i.gotoLine(n.lineNumber),i.navigateLineEnd(),$.Dialog.fail(!1,n.message),i.focus(),void e.enable()}e.toggleClass("typcn-document-text typcn-pencil").toggleHtml(["Plain text editor","Interactive editor"]).enable(),$.Dialog.clearNotice(/Parse error on line \d+ \(shown below\)/)});k.append('<label>\n\t\t\t<span>Group name (2-30 chars.)</span>\n\t\t\t<input type="text" name="label" pattern="'+PRINTABLE_ASCII_PATTERN.replace("+","{2,30}")+'" required>\n\t\t</label>',$.mk("label").append($.mk("input").attr({type:"checkbox",name:"major"}).on("click change",function(){$(this).parent().next()[this.checked?"show":"hide"]().children("input").attr("disabled",!this.checked)}),"<span>This is a major change</span>"),"<label style=\"display:none\">\n\t\t\t<span>Change reason (1-255 chars.)</span>\n\t\t\t<input type='text' name='reason' pattern=\""+PRINTABLE_ASCII_PATTERN.replace("+","{1,255}")+'" required disabled>\n\t\t</label>\n\t\t<p class="align-center">The # symbol is optional, rows with invalid '+o+"s will be ignored. Each color must have a short (3-30 chars.) description of its intended use.</p>",$.mk("div").attr("class","btn-group").append(E,R),"<div class='clrs'/>").on("render-color-inputs",function(){var t=$(this),e=t.data("color_values"),a=t.children(".clrs").empty();$.each(e,function(t,e){a.append(C(e))}),a.data("sortable",new Sortable(a.get(0),{handle:".move",ghostClass:"moving",scroll:!1,animation:150}))}).on("save-color-inputs",function(t,e){var a=$(this),n=a.children(".clrs"),i=n.hasClass("ace_editor"),o=void 0;if(i){if(o=n.data("editor"),a.data("color_values",T(o.getValue())),e)return;o.destroy(),n.empty().removeClass("ace_editor ace-colorguide").removeData("editor").unbind(),a.trigger("render-color-inputs")}else{var r=function(){var t=[];if(a.find(".clr").each(function(){var e=$(this),a=e.children(".clri"),n=$.hexpand(a.val()).toUpperCase();l.test(n)&&t.push({hex:n.replace(l,"#$1"),label:e.children(".clrl").val()})}),a.data("color_values",t),e)return{v:void 0};var i=["// One color per line","// e.g. #012ABC Fill"];$.each(t,function(t,e){var a=[];"object"===("undefined"==typeof e?"undefined":_typeof(e))&&(a.push(e.hex?e.hex:"#_____"),e.label&&a.push(e.label)),i.push(a.join("\t"))});var r=n.data("sortable");"undefined"!=typeof r&&r.destroy(),n.unbind().hide().text(i.join("\n")+"\n"),$.getAceEditor(!1,"colorguide",function(t){n.show(),o=ace.edit(n[0]);var e=$.aceInit(o,t);e.setTabSize(8),e.setMode(t),o.navigateFileEnd(),o.focus(),n.data("editor",o)})}();if("object"===("undefined"==typeof r?"undefined":_typeof(r)))return r.v}});var P=void 0;window.ctxmenus=function(){a()},h.on("page-switch",n),n(),$(".cg-export").on("click",function(){$.mk("form").attr({method:"POST",action:"/cg/export",target:"_blank"}).html($.mk("input").attr("name","CSRF_TOKEN").val($.getCSRFToken())).submit()})});
//# sourceMappingURL=/js/min/colorguide-manage.js.map
