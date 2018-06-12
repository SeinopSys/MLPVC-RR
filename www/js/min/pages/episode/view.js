"use strict";var _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};!function(){var m=window.SEASON,b=window.EPISODE,y="S"+m+"E"+b;window._HighlightHash=function(t){$(".highlight").removeClass("highlight");var e=$(location.hash);if(!e.length)return!1;e.addClass("highlight"),setTimeout(function(){$.scrollTo(e.offset().top-$navbar.outerHeight()-10,500,function(){"object"===(void 0===t?"undefined":_typeof(t))&&"load"===t.type&&$.Dialog.close()})},1)},$w.on("hashchange",window._HighlightHash);var a=$("#voting");a.on("click",".rate",function(t){t.preventDefault();var e=function(t){return $.mk("label").append($.mk("input").attr({type:"radio",name:"vote",value:t}),$.mk("span")).on("mouseenter mouseleave",function(t){var e=$(this),i=e.parent().find("input:checked").parent(),n=e.closest("div").next().children("strong");switch(t.type){case"mouseleave":if(0===i.length){e.siblings().addBack().find(".typcn").attr("class",""),n.text("?");break}e=i;case"mouseenter":e.prevAll().addBack().children("span").attr("class","active"),e.nextAll().children("span").attr("class",""),n.text(e.children("input").attr("value"))}e.siblings().addBack().removeClass("selected"),i.addClass("selected")})},i=$.mk("form").attr("id","star-rating").append($.mk("p").text("Rate the episode on a scale of 1 to 5. This cannot be changed later."),$.mk("div").attr("class","rate").append(e(1),e(2),e(3),e(4),e(5)),$.mk("p").css("font-size","1.1em").append("Your rating: <strong>?</strong>/5")),n=a.children(".rate");$.Dialog.request("Rating "+y,i,"Rate",function(i){i.on("submit",function(t){t.preventDefault();var e=i.mkData();if(void 0===e.vote)return $.Dialog.fail(!1,"Please choose a rating by clicking on one of the muffins");$.Dialog.wait(!1,"Submitting your rating"),$.API.post("/episode/"+y+"/vote",e,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var t=n.closest("section");t.children("h2").nextAll().remove(),t.append(this.newhtml),a.bindDetails(),$.Dialog.close()}))})})}),a.find("time").data("dyntime-beforeupdate",function(t){if(!0===t.past)return a.children(".rate").length?void 0:($.API.get("/episode/"+y+"vote?html",$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail("Display voting buttons",this.message);a.children("h2").nextAll().remove(),a.append(this.html),a.bindDetails()})),$(this).removeData("dyntime-beforeupdate"),!1)}),$.fn.bindDetails=function(){$(this).find("a.detail").on("click",function(t){t.preventDefault(),t.stopPropagation(),$.Dialog.wait("Voting details","Getting vote distribution information"),$.API.get("/episode/"+y+"/vote",$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var a=$.mk("ul"),o=0;$.each(this.data,function(t,e){var i=1==+t?"":"s",n=1==+e?"":"s";a.append("<li><strong>"+t+" muffin"+i+":</strong> "+e+" vote"+n+"</li>"),o+=+e});var i=$.mk("div").attr("class","bars");$.each(this.data,function(t,e){i.append('<div class="bar type-'+t+'" style="width:'+$.roundTo(e/o*100,2)+'%"></div>')}),$.Dialog.info(!1,[$.mk("p").text("Here's how the votes are distributed:"),$.mk("div").attr("id","vote-distrib").append(a,i)])}))})},a.bindDetails(),$.fn.rebindFluidbox=function(){$(this).find(".screencap > a:not(.fluidbox--initialized)").fluidboxThis()},$._getLiTypeId=function(t){return{id:parseInt(t.attr("id").split("-").pop(),10),type:t.attr("data-type")}},$.fn.rebindHandlers=function(t){t||this.find("li[id]");return this.closest("section").rebindFluidbox(),this};var t=$(".posts");t.on("click","li[id] .share",function(t){t.preventDefault();var e=$(this).closest("li"),i=$._getLiTypeId(e).id,n=window.location.href.replace(/([^:/]\/).*$/,"$1")+"s/"+i.toString(36),a=$.mk("div").attr("class","align-center").append("Use the link below to link to this post directly:",$.mk("div").attr("class","share-link").text(n),$.mk("button").attr("class","blue typcn typcn-clipboard").text("Copy to clipboard").on("click",function(t){$.copy(n,t)}));$.Dialog.info("Sharing post #"+i,a,function(){a.find(".share-link").select()})}).on("pls-update",function(t,e,i){var n=$(this),a=n.attr("id"),o=$.capitalize(a);!0!==i&&$.Dialog.wait(!$.Dialog.isOpen()&&o,"Updating list of "+a,!0),$.API.get("/episode/"+y+"/posts",{section:a},$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var t=$(this.render).filter("section").children();n.empty().append(t).rebindHandlers(),n.find(".post-form").formBind(),n.find("h2 > button").enable(),Time.Update(),window._HighlightHash(),"function"==typeof e?e():!0!==i&&$.Dialog.close()}))}),t.find("li[id]").each(function(){$(this).rebindFluidbox()}),$.fn.formBind=function(){var r=$(this);if(r.length){var o=r.find(".check-img"),s=r.find("button.submit"),t=r.find(".img-preview"),l=r.find("input[name=label]"),d=r.find("input[name=image_url]"),c=r.find("input[name=label]"),u=t.children(".notice"),e=u.html(),h=r.attr("data-kind"),f=$.capitalize(h),p=t.children("img");0===p.length&&(p=$(new Image).appendTo(t)),$("#"+h+"-btn").on("click",function(){r.hasClass("hidden")&&r.removeClass("hidden"),$.scrollTo(r.offset().top-$navbar.outerHeight()-10,500,function(){l.focus()})}),"reservation"===h&&$("#add-reservation-btn").on("click",function(){var n=$.mk("form","add-reservation").html('<div class="notice info">This feature should only be used when the vector was made before the episode was displayed here, and you just want to link the finished vector under the newly posted episode OR if this was a request, but the original image (screencap) is no longer available, only the finished vector.</div>\n\t\t\t\t<div class="notice warn">If you already posted the reservation, use the <strong class="typcn typcn-attachment">I\'m done</strong> button to mark it as finished instead of adding it here.</div>\n\t\t\t\t<label>\n\t\t\t\t\t<span>Deviation URL</span>\n\t\t\t\t\t<input type="text" name="deviation">\n\t\t\t\t</label>');$.Dialog.request("Add a reservation",n,"Finish",function(){n.on("submit",function(t){t.preventDefault();var e=n.find("[name=deviation]").val();if("string"!=typeof e||0===e.length)return $.Dialog.fail(!1,"Please enter a deviation URL");$.Dialog.wait(!1,"Adding reservation");var i={deviation:e,epid:y};$.API.post("/post/reservation",i,$.mkAjaxHandler(function(){var t=this;if(!this.status)return $.Dialog.fail(!1,this.message);$.Dialog.success(!1,this.message),r.closest(".posts").trigger("pls-update",[function(){$.Dialog.close(),window.location.hash="#"+t.id}])}))})})}),d.on("keyup change paste",i);var g=/^https?:\/\/www\.deviantart\.com\/users\/outgoing\?/,v='<strong class="typcn typcn-arrow-repeat" style="display:inline-block">Check image</strong>';o.on("click",function(t){var n,a;t.preventDefault(),n=d.val(),a=f+" process",o.removeClass("red"),i(!0),$.Dialog.wait(a,"Checking image"),$.API.post("/post/check-image",{image_url:n},$.mkAjaxHandler(function(){if(!this.status)return u.children("p:not(.keep)").remove(),u.prepend($.mk("p").attr("class","color-red").html(this.message)).show(),p.hide(),o.enable(),"string"==typeof d.data("prev-url")?s.enable():s.disable(),$.Dialog.close();!function t(e,i){$.Dialog.wait(a,"Checking image availability"),p.attr("src",e.preview).show().off("load error").on("load",function(){u.children("p:not(.keep)").remove(),d.data("prev-url",n),e.title&&!c.val().trim()?$.Dialog.confirm("Confirm "+h+" title",'The image you just checked had the following title:<br><br><p class="align-center"><strong>'+e.title+"</strong></p><br>Would you like to use this as the "+h+"'s description?<br>Keep in mind that it should describe the thing(s) "+("request"===h?"being requested":"you plan to vector")+".<p>This dialog will not appear if you give your "+h+" a description before clicking the "+v+" button.</p>",function(t){if(!t)return r.find("input[name=label]").focus();c.val(e.title),$.Dialog.close()}):$.Dialog.close(function(){r.find("input[name=label]").focus()})}).on("error",function(){if(i<1)return $.Dialog.wait("Can't load image","Image could not be loaded, retrying in 2 seconds"),void setTimeout(function(){t(e,i+1)},2e3);$.Dialog.fail(a,"There was an error while attempting to load the image. Make sure the URL is correct and try again!"),o.enable(),"string"==typeof d.data("prev-url")?s.enable():s.disable()})}(this,0)}))}),r.on("submit",function(t,e,i){t.preventDefault();var n=f+" process";if(void 0===d.data("prev-url"))return $.Dialog.fail(n,"Please click the "+v+" button before submitting your "+h+"!");if(!e&&d.data("prev-url")!==d.val())return $.Dialog.confirm(n,"You modified the image URL without clicking the "+v+" button.<br>Do you want to continue with the last checked URL?",function(t){t&&r.triggerHandler("submit",[!0])});if(!i&&"request"===h){var a=l.val(),o=r.find("select");if(-1<a.indexOf("character")&&"chr"!==o.val())return $.Dialog.confirm(n,"Your request label contains the word \"character\", but the request type isn't set to Character.<br>Are you sure you're not requesting one (or more) character(s)?",["Let me change the type","Carry on"],function(t){if(!t)return r.triggerHandler("submit",[e,!0]);$.Dialog.close(function(){o.focus()})})}var s=r.mkData({kind:h,episode:b,season:m,image_url:d.data("prev-url")});!function e(){$.Dialog.wait(n,"Submitting post"),$.API.post("/post",s,$.mkAjaxHandler(function(){if(!this.status)return this.canforce?$.Dialog.confirm(!1,this.message,["Go ahead","Never mind"],function(t){t&&(s.allow_nonmember=!0,e())}):$.Dialog.fail(!1,this.message);$.Dialog.success(!1,f+" posted");var t=this.id;$("#"+h+"s").trigger("pls-update",[function(){$.Dialog.close(),$.Dialog.confirm(f+" posted","Would you like to view it or make another?",["View","Make another"],function(t){$.Dialog.close(),t||$("#"+h+"-btn").trigger("click")}),window.location.hash="#"+t}])}))}()}).on("reset",function(){o.attr("disabled",!1).addClass("red"),u.html(e).show(),p.hide(),d.removeData("prev-url"),r.addClass("hidden")})}function i(t){var e=d.data("prev-url"),i="string"==typeof e&&e.trim()===d.val().trim(),n=!0===t||i;if(o.attr("disabled",n),s.attr("disabled",!n),n?o.attr("title","You need to change the URL before checking again."):o.removeAttr("title"),"keyup"===t.type){var a=d.val();g.test(a)&&d.val(d.val().replace(g,""))}}},t.find(".post-form").each(function(){$(this).formBind()});var o=new IntersectionObserver(function(t){t.forEach(function(t){if(t.isIntersecting){var i=t.target;o.unobserve(i);var n=i.dataset.postId,e=i.dataset.viewonly;$.API.get("/post/"+n+"/lazyload",{viewonly:e},$.mkAjaxHandler(function(){var e=$(i);if(!this.status)return e.trigger("error"),$.Dialog.fail("Cannot load post "+n,this.message);$.loadImages(this.html).then(function(t){e.trigger(t.e).closest(".image").replaceWith(t.$el)})}))}})}),s=new IntersectionObserver(function(t){t.forEach(function(t){if(t.isIntersecting){var e=t.target;s.unobserve(e);var i=$.mk("a"),n=new Image;n.src=e.dataset.src,i.attr("href",e.dataset.href).append(n);var a=$(e);$(n).on("load",function(t){$(e).trigger(t).closest(".image").html(i),i.closest("li").rebindFluidbox()}).on("error",function(t){a.trigger(t)})}})}),r=new IntersectionObserver(function(t){t.forEach(function(t){if(t.isIntersecting){var e=t.target;r.unobserve(e);var i=new Image;i.src=e.dataset.src,i.classList="avatar";var n=$(e);$(i).on("load",function(t){n.trigger(t).replaceWith(i)}).on("error",function(t){n.trigger(t)})}})});$(".post-deviation-promise").each(function(t,e){return o.observe(e)}),$(".post-image-promise").each(function(t,e){return s.observe(e)}),$(".user-avatar-promise").each(function(t,e){return r.observe(e)}),window.linkedPostURL&&history.replaceState({},null,window.linkedPostURL);var e=1<location.hash.length&&/^#post-\d+$/.test(location.hash);!function(){if(!1===window._HighlightHash({type:"load"})&&e){var i="Scroll post into view",t=location.hash.replace(/\D/g,"");$.API.post("/post/"+t+"/locate",{SEASON:m,EPISODE:b},$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.info(i,this.message);if(this.refresh)$("#"+this.refresh+"s").triggerHandler("pls-update");else{var e=this.castle,t=$('<p>Looks like the post you were linked to is in another castle. Want to follow the path?</p>\n\t\t\t\t\t<div id="post-road-sign">\n\t\t\t\t\t\t<div class="sign-wrap">\n\t\t\t\t\t\t\t<div class="sign-inner">\n\t\t\t\t\t\t\t\t<span class="sign-text"></span>\n\t\t\t\t\t\t\t\t<span class="sign-arrow">➔</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class="sign-pole"></div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class="notice info">If you\'re seeing this message after clicking a link within the site please <a class="send-feedback">let us know</a>.</div>');t.find(".sign-text").text(e.name),$.Dialog.close(function(){$.Dialog.confirm(i,t,["Take me there","Stay here"],function(t){t&&($.Dialog.wait(!1,"Quicksaving"),$.Navigation.visit(e.url))})})}}))}}();var l={};function d(){var i=void 0,e=$(".episode"),n=e.find(".showplayers").on("scroll-video-into-view",function(){var t=$header.outerHeight();$.scrollTo(i.offset().top-($w.height()-$footer.outerHeight()-t-i.outerHeight())/2-t,500)}),a=n.parent(),o=void 0;if(n.length){var t=e.find(".reportbroken");n.on("click",function(t){if(t.preventDefault(),void 0===i)$.Dialog.wait(n.text()),$.API.get("/episode/"+y+"/video-embeds",$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);2===this.parts&&(o=$.mk("button").attr("class","blue typcn typcn-media-fast-forward").text("Part 2").on("click",function(){$(this).toggleHtml(["Part 1","Part 2"]),i.children().toggleClass("hidden")}),a.append(o)),i=$.mk("div").attr("class","resp-embed-wrap").html(this.html).insertAfter(a),n.removeClass("typcn-eye green").addClass("typcn-eye-outline blue").text("Hide on-site player").triggerHandler("scroll-video-into-view"),$.Dialog.close()}));else{var e=n.hasClass("typcn-eye");i[e?"show":"hide"](),o instanceof jQuery&&o.attr("disabled",!e),n.toggleClass("typcn-eye typcn-eye-outline").toggleHtml(["Show on-site player","Hide on-site player"]),e&&n.triggerHandler("scroll-video-into-view")}}),t.on("click",function(t){t.preventDefault(),$.Dialog.confirm("Report broken video",'<p>Have any of the linked videos been removed from their respective platform?<p><p>Please note that availability checking is automatic, bad video quality or sound issues cannot be detected this way. You should <a class="send-feedback">tell us</a> directly if that is the case.</p>',["Send report","Never mind"],function(t){t&&($.Dialog.wait(!1,"Sending report"),$.API.post("/episode/"+y+"/broken-videos",$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);void 0!==this.epsection&&(0<this.epsection.length?(e.html(this.epsection),d()):e.remove()),$.Dialog.success(!1,this.message,!0)})))})})}}$.fn.reloadLi=function(){var e=!(0<arguments.length&&void 0!==arguments[0])||arguments[0],i=1<arguments.length&&void 0!==arguments[1]?arguments[1]:void 0,n=this,a=n.attr("id");if("string"!=typeof a||n.hasClass("admin-break"))return this;if(!0===l[a])return this;l[a]=!0;var o=a.split("-")[1];return e&&console.log("[POST-FIX] Attempting to reload post #"+o),$.API.get("/post/"+o+"/reload",{cache:e},$.mkAjaxHandler(function(){if(l[a]=!1,this.status){if(!0===this.broken)return n.remove(),void console.log("[POST-FIX] Hid (broken) post #"+o);var t=$(this.li);(n=$("#"+t.attr("id"))).find(".fluidbox--opened").fluidbox("close"),n.find(".fluidbox--initialized").fluidbox("destroy"),(n.hasClass("highlight")||t.is(location.hash))&&t.addClass("highlight"),n.replaceWith(t),t.rebindFluidbox(),Time.Update(),t.rebindHandlers(!0),t.parent().is(this.section)||t.appendTo(this.section),t.parent().reorderPosts(),e&&console.log("[POST-FIX] Reloaded post #"+o),$.callCallback(i)}})),this},$.fn.reorderPosts=function(){this.children().sort(function(t,e){var i=$(t),n=$(e),a=i.find(".finish-date time"),o=n.find(".finish-date time"),s=void 0;return 0===(s=a.length&&o.length?new Date(a.attr("datetime")).getTime()-new Date(o.attr("datetime")).getTime():new Date(i.find(".post-date time").attr("datetime")).getTime()-new Date(n.find(".post-date time").attr("datetime")).getTime())?parseInt(i.attr("id").replace("/D/g",""),10)-parseInt(n.attr("id").replace("/D/g",""),10):s}).appendTo(this)},(window.bindVideoButtons=d)(),$.WS.recvPostUpdates(!0)}();
//# sourceMappingURL=/js/min/pages/episode/view.js.map
