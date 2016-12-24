"use strict";var _typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol?"symbol":typeof e};DocReady.push(function(){function e(){"undefined"!=typeof l&&l.filter(".red").triggerHandler("click")}function t(){var e=$content.find("img[src]"),t=e.length,i=0;if(!t)return $.Dialog.close();var n=void 0;b&&($.Dialog.wait("Scroll post into view","Waiting for page to load"),n=$.mk("progress").attr({max:t,value:0}).css({display:"block",width:"100%",marginTop:"5px"}),$("#dialogContent").children("div:not([id])").last().addClass("align-center").append(n)),$content.imagesLoaded().progress(function(e,o){if(o.isLoaded)i++,b&&n.attr("value",i);else if(o.img.src){var a=$(o.img).closest("li[id]");1===a.length&&a.reloadLi(),t--,b&&n.attr("max",t)}}).always(function(){var e=window._HighlightHash({type:"load"});e===!1&&b&&$.Dialog.info("Scroll post into view","The "+location.hash.replace(m,"$1")+" you were linked to has either been deleted or didn't exist in the first place. Sorry.<div class='align-center'><span class='sideways-smiley-face'>:\\</div>")})}function i(){var e=void 0,t=$(".episode"),n=t.find(".showplayers").on("scroll-video-into-view",function(){var t=$header.outerHeight();$.scrollTo(e.offset().top-($w.height()-$footer.outerHeight()-t-e.outerHeight())/2-t,500)}),o=n.parent(),r=void 0;if(n.length){var s=t.find(".reportbroken");n.on("click",function(t){if(t.preventDefault(),"undefined"==typeof e)$.Dialog.wait(n.text()),$.post("/episode/videos/"+a,$.mkAjaxHandler(function(){return this.status?(2===this[0]&&(r=$.mk("button").attr("class","blue typcn typcn-media-fast-forward").text("Part 2").on("click",function(){$(this).toggleHtml(["Part 1","Part 2"]),e.children().toggle()}),o.append(r)),e=$.mk("div").attr("class","resp-embed-wrap").html(this[1]).insertAfter(o),n.removeClass("typcn-eye green").addClass("typcn-eye-outline blue").text("Hide on-site player").triggerHandler("scroll-video-into-view"),void $.Dialog.close()):$.Dialog.fail(!1,this.message)}));else{var i=n.hasClass("typcn-eye");e[i?"show":"hide"](),r instanceof jQuery&&r.attr("disabled",!i),n.toggleClass("typcn-eye typcn-eye-outline").toggleHtml(["Show on-site player","Hide on-site player"]),i&&n.triggerHandler("scroll-video-into-view")}}),s.on("click",function(e){e.preventDefault(),$.Dialog.confirm("Report broken video",'<p>Have any of the linked videos been removed from their respective platform?<p><p>Please note that availability checking is automatic, bad video quality or sound issues cannot be detected this way. You should <a class="send-feedback">tell us</a> directly if that is the case.</p>',["Send report","Nevermind"],function(e){e&&($.Dialog.wait(!1,"Sending report"),$.post("/episode/brokenvideos/"+a,$.mkAjaxHandler(function(){return this.status?("undefined"!=typeof this.epsection&&(this.epsection.length>0?(t.html(this.epsection),i()):t.remove()),void $.Dialog.success(!1,this.message,!0)):$.Dialog.fail(!1,this.message)})))})})}}var n=window.SEASON,o=window.EPISODE,a="S"+n+"E"+o,r=$("#live-update"),s=r.length,l=void 0,d=function(e,t){$("#reservations, #requests").trigger("pls-update",[e,t,!0])},c=void 0;window._HighlightHash=function(e){$(".highlight").removeClass("highlight");var t=$(location.hash);return!!t.length&&(t.addClass("highlight"),void setTimeout(function(){$.scrollTo(t.offset().top-$navbar.outerHeight()-10,500,function(){"object"===("undefined"==typeof e?"undefined":_typeof(e))&&"load"===e.type&&$.Dialog.close()})},1))},$w.on("hashchange",window._HighlightHash),s&&!function(){var t=void 0,i=30,n=function(){"undefined"!=typeof window._rlinterval&&(clearInterval(window._rlinterval),window._rlinterval=void 0)},o=r.find(".timer"),a=r.find("button.reload").on("click",function(t){t.preventDefault(),n();var i=function(t){o.html("&hellip;").css("color",""),a.disable().html("Reloading&hellip;");var i=0,n=2,r=function(o){return o===!1?e():(i++,void(i<n||(window._HighlightHash(),c(),t&&$.Dialog.close())))};d(r,!0)};$(".post-form").filter(":visible").length>0?$.Dialog.confirm("Reloading posts","You are in the process of posting a request/reservation. Reloading the posts will clear your progress.<br><br>Continue reloading?",function(e){e&&($.Dialog.wait(!1,"Updating posts"),i(!0))}):i()}),s=function(){var e=Math.round((t.getTime()-(new Date).getTime())/1e3)*-1,n=e>i?255:e/i*255;o.text(i-e+"s").css("color","rgb(255,"+(255-n/2)+","+(255-n)+")"),e>=i&&a.triggerHandler("click")};c=function(){a.html("Reload now").enable(),n(),l.hasClass("green")||(t=new Date,o.text(i+"s").css("color",""),window._rlinterval=setInterval(s,1e3))},l=r.find("button.disable").on("click",function(e){e.preventDefault();var t=l.hasClass("red");l.toggleHtml(["Enable","Disable"]).toggleClass("red green typcn-times typcn-tick"),t?o.parent().hide().next().show():o.parent().show().next().hide(),c()}),t=new Date,s(),window._rlinterval=setInterval(s,1e3),$w.on("dialog-opened",e)}();var u=$("#voting");u.on("click",".rate",function(e){e.preventDefault();var t=function(e){return $.mk("label").append($.mk("input").attr({type:"radio",name:"vote",value:e}),$.mk("span")).on("mouseenter mouseleave",function(e){var t=$(this),i=t.parent().find("input:checked"),n=i.parent(),o=t.closest("div").next().children("strong");switch(e.type){case"mouseleave":if(0===n.length){t.siblings().addBack().find(".typcn").attr("class",""),o.text("?");break}t=n;case"mouseenter":t.prevAll().addBack().children("span").attr("class","active"),t.nextAll().children("span").attr("class",""),o.text(t.children("input").attr("value"))}t.siblings().addBack().removeClass("selected"),n.addClass("selected")})},i=$.mk("form").attr("id","star-rating").append($.mk("p").text("Rate the episode on a scale of 1 to 5. This cannot be changed later."),$.mk("div").attr("class","rate").append(t(1),t(2),t(3),t(4),t(5)),$.mk("p").css("font-size","1.1em").append("Your rating: <strong>?</strong>/5")),n=u.children(".rate");$.Dialog.request("Rating "+a,i,"Rate",function(e){e.on("submit",function(t){t.preventDefault();var i=e.mkData();return"undefined"==typeof i.vote?$.Dialog.fail(!1,"Please choose a rating by clicking on one of the muffins"):($.Dialog.wait(!1,"Submitting your rating"),void $.post("/episode/vote/"+a,i,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var e=n.closest("section");e.children("h2").nextAll().remove(),e.append(this.newhtml),u.bindDetails(),$.Dialog.close()})))})})}),u.find("time").data("dyntime-beforeupdate",function(e){if(e.past===!0)return u.children(".rate").length?void 0:($.post("/episode/vote/"+a+"?html",$.mkAjaxHandler(function(){return this.status?(u.children("h2").nextAll().remove(),u.append(this.html),void u.bindDetails()):$.Dialog.fail("Display voting buttons",this.message)})),$(this).removeData("dyntime-beforeupdate"),!1)}),$.fn.bindDetails=function(){$(this).find("a.detail").on("click",function(e){e.preventDefault(),e.stopPropagation(),$.Dialog.wait("Voting details","Getting vote distribution information"),$.post("/episode/vote/"+a+"?detail",$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var e=$.mk("canvas"),t=e.get(0).getContext("2d"),i=$.mk("p").attr("class","tooltip");$.Dialog.info(!1,[$.mk("p").text("Here's a chart showing how the votes are distributed. Mouse over the different segments to see the exact number of votes."),$.mk("div").attr("id","vote-distrib").append(e,i)]);var n=[void 0,"#FF5454","#FFB554","#FFFF54","#8CD446","#4DC742"],o=this.data,a=0;return o.datasets[0].backgroundColor=[],o.datasets[0].hoverBackgroundColor=[],o.datasets[0].borderWidth=[],o.datasets[0].hoverBorderColor=[],$.each(o.datasets[0].data,function(e,t){var i=n[parseInt(o.labels[e],10)];o.datasets[0].backgroundColor.push(i);var r=$.hex2rgb(i),s=1.06;r.r=Math.round(Math.min(255,r.r*s)),r.g=Math.round(Math.min(255,r.g*s)),r.b=Math.round(Math.min(255,r.b*s)),o.datasets[0].hoverBackgroundColor.push($.rgb2hex(r)),o.datasets[0].borderWidth.push(2),o.datasets[0].hoverBorderColor.push("rgba("+r.r+","+r.g+","+r.b+",0.9)"),a+=parseInt(t,10)}),0===a?(e.remove(),void i.text("There are no votes for this episode yet")):void new Chart(t,{type:"pie",data:o,options:{titleFontColor:"#000",bodyFontColor:"#000",animation:{easing:"easeInOutExpo"},legend:{display:!1},tooltips:{callbacks:{title:function(e,t){var i=parseInt(t.labels[e[0].index],10);return i+" muffin"+(1!==i?"s":"")},label:function(e,t){var i=parseInt(t.datasets[e.datasetIndex].data[e.index],10),n=Math.round(i/a*1e3)/10;return i+" user"+(1!==i?"s":"")+" ("+n+"%)"}}}}})}))})},u.bindDetails(),$.fn.rebindFluidbox=function(){$(this).find(".screencap > a:not(.fluidbox--initialized)").fluidboxThis().on("openstart.fluidbox",function(){e()})},$._getLiTypeId=function(e){var t=e.attr("id").split("-");return{id:t[1],type:t[0]+"s"}},$.fn.rebindHandlers=function(e){var t=e?this:this.find("li[id]");return t.each(function(){var e=$(this),t=$._getLiTypeId(e);e.trigger("bind-more-handlers",[t.id,t.type])}),this.closest("section").rebindFluidbox(),this};var h=function(){var e=$(this),t=$._getLiTypeId(e),i=t.type,n=t.id,o=e.find(".actions").children();e.rebindFluidbox(),o.filter(".share").on("click",function(){var e=$(this),t=window.location.href.replace(/([^:\/]\/).*$/,"$1")+"s/"+e.parents("li").attr("id").replace(/^(re[qs])[^-]+?-(\d+)$/,"$1/$2");$.Dialog.info("Sharing "+i.replace(/s$/,"")+" #"+n,$.mk("div").attr("class","align-center").append("Use the link below to link to this post directly:",$.mk("div").attr("class","share-link").text(t),$.mk("button").attr("class","blue typcn typcn-clipboard").text("Copy to clipboard").on("click",function(e){$.copy(t,e)})),function(){$("#dialogContent").find(".share-link").select()})})};$("#requests, #reservations").on("pls-update",function(e,t,i,n){if(s&&!n)return d(t,i);var o=$(this),r=o.attr("id"),l=$.capitalize(r),c=r.replace(/([^s])$/,"$1s"),u=function(){return"function"==typeof t&&i===!0?t(!1):void window.location.reload()};i!==!0&&$.Dialog.wait(!$.Dialog.isOpen()&&l,"Updating list of "+c,!0),$.ajax("/episode/"+c+"/"+a,{method:"POST",success:$.mkAjaxHandler(function(){if(!this.status)return u();var e=$(this.render).filter("section").children();o.empty().append(e).rebindHandlers(),o.find(".post-form").attr("data-type",r).formBind(),o.find("h2 > button").enable(),Time.Update(),"function"==typeof t?t():i!==!0&&$.Dialog.close()}),error:u})}).on("bind-more-handlers","li[id]",h).find("li[id]").each(h),$.fn.formBind=function(){function t(e){var t=c.data("prev-url"),i="string"==typeof t&&t.trim()===c.val().trim();if(s.attr("disabled",e===!0||i),e===!0||i?s.attr("title","You need to change the URL before chacking again."):s.removeAttr("title"),"keyup"===e.type){var n=c.val();m.test(n)&&c.val(c.val().replace(m,""))}}function i(){var e=c.val(),i=v+" process";s.removeClass("red"),t(!0),$.Dialog.wait(i,"Checking image"),$.post("/post",{image_url:e},$.mkAjaxHandler(function(){function t(n,o){$.Dialog.wait(i,"Checking image availability"),p.attr("src",n.preview).show().off("load error").on("load",function(){h.children("p:not(.keep)").remove(),c.data("prev-url",e),n.title&&!u.val().trim()?$.Dialog.confirm("Confirm "+g+" title",'The image you just checked had the following title:<br><br><p class="align-center"><strong>'+n.title+"</strong></p><br>Would you like to use this as the "+g+"'s description?<br>Keep in mind that it should describe the thing(s) "+("request"===g?"being requested":"you plan to vector")+".<p>This dialog will not appear if you give your "+g+" a description before clicking the "+b+" button.</p>",function(e){return e?(u.val(n.title),void $.Dialog.close()):r.find("input[name=label]").focus()}):$.Dialog.close(function(){r.find("input[name=label]").focus()})}).on("error",function(){return o<1?($.Dialog.wait("Can't load image","Image could not be loaded, retrying in 2 seconds"),void setTimeout(function(){t(n,o+1)},2e3)):($.Dialog.fail(i,"There was an error while attempting to load the image. Make sure the URL is correct and try again!"),void s.enable())})}var n=this;return n.status?void t(n,0):(h.children("p:not(.keep)").remove(),h.prepend($.mk("p").attr("class","color-red").html(n.message)).show(),p.hide(),s.enable(),$.Dialog.close())}))}var r=$(this);if(r.length){var s=r.find(".check-img"),l=r.find(".img-preview"),d=r.find("[name=label]"),c=r.find("[name=image_url]"),u=r.find("[name=label]"),h=l.children(".notice"),f=h.html(),p=l.children("img"),g=r.attr("data-type").replace(/s$/,""),v=$.capitalize(g);0===p.length&&(p=$(new Image).appendTo(l)),$("#"+g+"-btn").on("click",function(){e(),r.is(":visible")||(r.show(),d.focus(),$.scrollTo(r.offset().top-$navbar.outerHeight()-10,500))}),"reservation"===g&&$("#add-reservation-btn").on("click",function(){var e=$.mk("form","add-reservation").html('<div class="notice info">This feature should only be used when the vector was made before the episode was displayed here, and all you want to do is link your already-made vector under the newly posted episode.</div>\n\t\t\t\t<div class="notice warn">If you already posted the reservation, use the <strong class="typcn typcn-attachment">I\'m done</strong> button to mark it as finished instead of adding it here.</div>\n\t\t\t\t<label>\n\t\t\t\t\t<span>Deviation URL</span>\n\t\t\t\t\t<input type="text" name="deviation">\n\t\t\t\t</label>');$.Dialog.request("Add a reservation",e,"Finish",function(e){e.on("submit",function(t){t.preventDefault();var i=e.find("[name=deviation]").val();return"string"!=typeof i||0===i.length?$.Dialog.fail(!1,"Please enter a deviation URL"):($.Dialog.wait(!1,"Adding reservation"),void $.post("/post/add-reservation",{deviation:i,epid:a},$.mkAjaxHandler(function(){return this.status?($.Dialog.success(!1,this.message),void $("#"+g+"s").trigger("pls-update")):$.Dialog.fail(!1,this.message)})))})})}),c.on("keyup change paste",t);var m=/^https?:\/\/www\.deviantart\.com\/users\/outgoing\?/,b='<strong class="typcn typcn-arrow-repeat" style="display:inline-block">Check image</strong>';s.on("click",function(e){e.preventDefault(),i()}),r.on("submit",function(e,t,i){e.preventDefault();var a=v+" process";if("undefined"==typeof c.data("prev-url"))return $.Dialog.fail(a,"Please click the "+b+" button before submitting your "+g+"!");if(!t&&c.data("prev-url")!==c.val())return $.Dialog.confirm(a,"You modified the image URL without clicking the "+b+" button.<br>Do you want to continue with the last checked URL?",function(e){e&&r.triggerHandler("submit",[!0])});if(!i&&"request"===g){var s=function(){var e=d.val(),i=r.find("select");if(e.indexOf("character")>-1&&"chr"!==i.val())return{v:$.Dialog.confirm(a,"Your request label contains the word \"character\", but the request type isn't set to Character.<br>Are you sure you're not requesting one (or more) character(s)?",["Let me change the type","Carray on"],function(e){return e?void $.Dialog.close(function(){i.focus()}):r.triggerHandler("submit",[t,!0])})}}();if("object"===("undefined"==typeof s?"undefined":_typeof(s)))return s.v}var l=r.mkData({what:g,episode:o,season:n,image_url:c.data("prev-url")});!function e(){$.Dialog.wait(a,"Submitting "+g),$.post("/post",l,$.mkAjaxHandler(function(){if(!this.status)return this.canforce?$.Dialog.confirm(!1,this.message,["Go ahead","Nevermind"],function(t){t&&(l.allow_nonmember=!0,e())}):$.Dialog.fail(!1,this.message);$.Dialog.success(!1,v+" posted");var t=this.id;$("#"+g+"s").trigger("pls-update",[function(){$.Dialog.close(),$("#"+g+"-"+t).find("em.post-date").children("a").triggerHandler("click")}])}))}()}).on("reset",function(){s.attr("disabled",!1).addClass("red"),h.html(f).show(),p.hide(),c.removeData("prev-url"),$(this).hide()})}};var f=["requests","reservations"],p=0,g={},v=function e(i,n){$.each(f,function(i,o){!function(i){var o=$("#"+i);g[i]!==!0&&($.isInViewport(o.get(0))||n)&&(g[i]=!0,console.log("[DYN-POSTS] Loading %s section (force=%s)",i,n),o.trigger("pls-update",[function(){f.splice(f.indexOf(i),1),console.log("[DYN-POSTS] Loaded %s section",i),2===++p&&($w.off("scroll",e),t(!0))},!0,!0]))}(o)})};$w.on("scroll touchmove",$.throttle(250,v)),v();var m=/^#(request|reservation)-\d+$/,b=location.hash.length>1&&m.test(location.hash);b&&($.Dialog.wait("Scroll post into view","Preloading all posts, please wait"),v(null,!0));var y={};$.fn.reloadLi=function(){var e=$(this),t=e.attr("id").split("-"),i=t[0],n=t[1];y[t]||(y[t]=!0,console.log("[POST-FIX] Attemting to reload "+i+" #"+n),$.post("/post/reload-"+i+"/"+n,$.mkAjaxHandler(function(){if(this.status){var t=$(this.li);e.hasClass("highlight")&&t.addClass("highlight"),e.replaceWith(t),t.rebindFluidbox(),Time.Update(),t.rebindHandlers(!0),console.log("[POST-FIX] Reloaded "+i+" #"+n)}})))},window.bindVideoButtons=i,i()},function(){$body.removeClass("fluidbox-open"),$(".fluidbox--opened").fluidbox("close"),"number"==typeof window._rlinterval&&clearInterval(window._rlinterval),$w.off("hashchange",window._HighlightHash),delete window._HighlightHash,delete $.fn.reloadLi});
//# sourceMappingURL=/js/min/episode.js.map
