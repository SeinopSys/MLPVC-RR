"use strict";DocReady.push(function(){function e(e,t,i){switch(e){case"p_vectorapp":if(0===i.length&&0!==t.length){var a="app-"+t;$("."+a).removeClass(a),$(".title h1 .vectorapp-logo").remove(),$.Dialog.close()}else $.Dialog.wait(!1,"Reloading page"),$.Navigation.reload(function(){$.Dialog.close()});break;case"p_hidediscord":var o=$sidebar.find(".welcome .discord-join");i?o.length&&o.remove():o.length||$sidebar.find(".welcome .buttons").append('<a class="btn typcn discord-join" href="http://fav.me/d9zt1wv" target="_blank">Join Discord</a>'),$.Dialog.close();break;case"p_disable_ga":if(i)return $.Dialog.wait(!1,"Performing a hard reload to remove user ID from the tracking code"),window.location.reload();$.Dialog.close();break;case"p_hidepcg":$.Dialog.wait("Navigation","Reloading page"),$.Navigation.reload(function(){$.Dialog.close()});break;default:$.Dialog.close()}}$(".personal-cg-say-what").on("click",function(e){e.preventDefault(),$.Dialog.info("About Personal Color Guides","<p>We are forever grateful to our members who help others out by fulfilling their requests on our website. As a means of giving back, we're introducing Personal Color Guides. This is a place where you can store and share colors for any of your OCs, similar to our <a href=\"/cg/\">Official Color Guide</a>.</p>\n\t\t\t<p><em>&ldquo;So where’s the catch?&rdquo;</em> &mdash; you might ask. Everyone starts with 0 slots*, which they can increase by fulfilling requests on our website, then submitting them to the club and getting them approved. You'll get your first slot after you've fulfilled 10 requests, all of which got approved by our staff to the club gallery. After that, you will be granted an additional slot for every 10 requests you finish and we approve.</p>\n\t\t\t<p><small>* Staff members get an honorary slot for free</small></p>\n\t\t\t<br>\n\t\t\t<p><strong>However</strong>, there are a few things to keep in mind:</p>\n\t\t\t<ul>\n\t\t\t\t<li>You may only add characters made by you, for you, or characters you've purchased to your Personal Color Guide. If we're asked to remove someone else’s character from your guide we'll certainly comply.</li>\n\t\t\t\t<li>Finished requests only count toward additional slots after they have been submitted to the group and have been accepted to the gallery. This is indicated by a tick symbol (<span class=\"color-green typcn typcn-tick\"></span>) on the post throughout the site.</li>\n\t\t\t\t<li>Do not attempt to abuse the system in any way. Exploiting any bugs you may encounter instead of <a class=\"send-feedback\">reporting them</a> will be sanctioned.</li>\n\t\t\t</ul>")});var t=$(".pending-reservations");t.length&&(t.on("click","button.cancel",function(){var e=$(this),i=e.prev();$.Dialog.confirm("Cancel reservation","Are you sure you want to cancel this reservation?",function(a){if(a){$.Dialog.wait(!1,"Cancelling reservation");var o=i.prop("hash").substring(1).split("-");$.post("/post/unreserve/"+o.join("/"),{FROM_PROFILE:!0},$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var i=this.pendingReservations;e.closest("li").fadeOut(1e3,function(){$(this).remove(),i&&(t.html($(i).children()),Time.Update())}),$.Dialog.close()}))}})}),t.on("click","button.fix",function(){var e=$(this).next().prop("hash").substring(1).split("-"),t=e[0],i=e[1],a=$.mk("form").attr("id","img-update-form").append($.mk("label").append($.mk("span").text("New image URL"),$.mk("input").attr({type:"text",maxlength:255,pattern:"^.{2,255}$",name:"image_url",required:!0,autocomplete:"off",spellcheck:"false"})));$.Dialog.request("Update image of "+t+" #"+i,a,"Update",function(e){e.on("submit",function(a){a.preventDefault();var o=e.mkData();$.Dialog.wait(!1,"Replacing image"),$.post("/post/set-image/"+t+"/"+i,o,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);$.Dialog.success(!1,"Image has been updated"),$.Dialog.wait(!1,"Reloading page"),$.Navigation.reload(function(){$.Dialog.close()})}))})})}));var i=$("#signout"),a=$(".session-list"),o=$content.children(".briefing").find(".username").text().trim(),n=o===$sidebar.children(".welcome").find(".un").text().trim();a.find("button.remove").off("click").on("click",function(e){e.preventDefault();var t="Deleting session",a=$(this).closest("li"),s=a.children(".browser").text().trim(),r=a.children(".platform"),l=r.length?" on <em>"+r.children("strong").text().trim()+"</em>":"";if(0===a.index()&&-1!==a.children().last().text().indexOf("Current"))return i.triggerHandler("click");var c=a.attr("id").replace(/\D/g,"");if(void 0===c||isNaN(c)||!isFinite(c))return $.Dialog.fail(t,"Could not locate Session ID, please reload the page and try again.");$.Dialog.confirm(t,(n?"You":o)+" will be signed out of <em>"+s+"</em>"+l+".<br>Continue?",function(e){e&&($.Dialog.wait(t,"Signing out of "+s+l),$.post("/user/sessiondel/"+c,$.mkAjaxHandler(function(){return this.status?0!==a.siblings().length?(a.remove(),$.Dialog.close()):($.Dialog.wait(!1,"Reloading page",!0),void $.Navigation.reload(function(){$.Dialog.close()})):$.Dialog.fail(t,this.message)})))})}),a.find("button.useragent").on("click",function(e){e.preventDefault();var t=$(this);$.Dialog.info("User Agent string for session #"+t.parents("li").attr("id").substring(8),"<code>"+t.data("agent")+"</code>")}),$("#signout-everywhere").on("click",function(){$.Dialog.confirm("Sign out from ALL sessions","This will invalidate ALL sessions. Continue?",function(e){e&&($.Dialog.wait(!1,"Signing out"),$.post("/da-auth/signout?everywhere",{username:o},$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);$.Dialog.wait(!1,"Reloading page",!0),$.Navigation.reload(function(){$.Dialog.close()})})))})}),$("#unlink").on("click",function(e){e.preventDefault();var t="Unlink account & sign out";$.Dialog.confirm(t,"Are you sure you want to unlink your account?",function(e){e&&($.Dialog.wait(t,"Removing account link"),$.post("/da-auth/signout?unlink",$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);$.Dialog.wait(!1,"Reloading page",!0),$.Navigation.reload(function(){$.Dialog.close()})})))})});var s=$(".awaiting-approval");s.length&&$.post("/user/awaiting-approval/"+o,$.mkAjaxHandler(function(){if(!this.status)return s.html("<div class='notice fail'>This section failed to load</div>");s.hide().html(this.html).slideDown(300).on("click","button.check",function(e){e.preventDefault();var t=$(this).parents("li").attr("id").split("-"),i=t[0],a=t[1];$.Dialog.wait("Deviation acceptance status","Checking"),$.post("/post/lock/"+i+"/"+a,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var e=this.message;$.Dialog.wait(!1,"Reloading page"),$.Navigation.reload(function(){$.Dialog.success(!1,e,!0)})}))})}));var r=$("#settings").find("form").on("submit",function(t){t.preventDefault();var i=$(this),a=i.attr("action"),o=i.mkData(),n=i.find('[name="value"]'),s=n.data("orig");$.Dialog.wait("Saving setting","Please wait"),$.post(a,o,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);n.is("[type=number]")?n.val(this.value):n.is("[type=checkbox]")&&(this.value=Boolean(this.value),n.prop("checked",this.value)),n.data("orig",this.value).triggerHandler("change"),e(a.split("/").pop(),s,this.value)}))}).children("label");r.children("input[type=number], select").each(function(){var e=$(this);e.data("orig",e.val().trim()).on("keydown keyup change",function(){var e=$(this);e.siblings(".save").attr("disabled",e.val().trim()===e.data("orig"))})}),r.children("input[type=checkbox]").each(function(){var e=$(this);e.data("orig",e.prop("checked")).on("keydown keyup change",function(){var e=$(this);e.siblings(".save").attr("disabled",e.prop("checked")===e.data("orig"))})})});
//# sourceMappingURL=/js/min/user.js.map
