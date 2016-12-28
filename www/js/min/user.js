"use strict";DocReady.push(function(){function e(e,t,i){switch(e){case"p_vectorapp":if(0===i.length&&0!==t.length){var o="app-"+t;$("."+o).removeClass(o),$(".title h1 .vectorapp-logo").remove(),$.Dialog.close()}else $.Dialog.wait(!1,"Reloading page"),$.Navigation.reload(function(){$.Dialog.close()});break;case"p_hidediscord":var a=$sidebar.find(".welcome .discord-join");i?a.length&&a.remove():a.length||$sidebar.find(".welcome .buttons").append('<a class="btn typcn discord-join" href="http://fav.me/d9zt1wv" target="_blank">Join Discord</a>'),$.Dialog.close();break;case"p_disable_ga":if(i)return $.Dialog.wait(!1,"Performing a hard reload to remove user ID from the tracking code"),window.location.reload();$.Dialog.close();break;case"p_hidepcg":$.Dialog.wait("Navigation","Reloading page"),$.Navigation.reload(function(){$.Dialog.close()});break;default:$.Dialog.close()}}$(".personal-cg-say-what").on("click",function(e){e.preventDefault(),$.Dialog.info("About Personal Color Guides","<p>We are forever grateful to our members who help others out by fulfilling their requests on our website. As a means of giving back, we're introducing Personal Color Guides. This is a place where you can store and share colors for any of your OCs, similar to our <a href=\"/cg/\">Official Color Guide</a>.</p>\n\t\t\t<p><em>&ldquo;So where's the catch?&rdquo;</em> &mdash; you might ask. Everyone starts with 0 slots, which they can increase by fulfilling requests on our website, then submitting them to the club and getting them approved. You'll get your first slot after you've fulfilled 10 requests, all of which got approved by our staff to the club gallery. After that, you will be granted an additional slot for every 10 requests you finish and we approve.</p>\n\t\t\t<p>A few things to keep in mind:</p>\n\t\t\t<ul>\n\t\t\t\t<li>You may only add characters made by you, for you, or characters you've purchased to your Personal Color Guide. If we're asked to remove someone else's character from your guide we'll certainly comply.</li>\n\t\t\t\t<li>Finished requests only count toward additional slots after they have been submitted to the group and have been accepted to the gallery. This is indicated by a tick symbol (<span class=\"color-green typcn typcn-tick\"></span>) on the post throughout the site.</li>\n\t\t\t\t<li>Do not attempt to abuse the system in any way. Exploiting any bugs you may encounter instead of <a class=\"send-feedback\">reporting them</a> will be sanctioned.</li>\n\t\t\t</ul>")});var t=$(".pending-reservations");t.length&&t.on("click","button.cancel",function(){var e=$(this),i=e.prev();$.Dialog.confirm("Cancel reservation","Are you sure you want to cancel this reservation?",function(o){if(o){$.Dialog.wait(!1,"Cancelling reservation");var a=i.prop("hash").substring(1).split("-");$.post("/post/unreserve/"+a.join("/"),{FROM_PROFILE:!0},$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var i=this.pendingReservations;e.closest("li").fadeOut(1e3,function(){$(this).remove(),i&&(t.html($(i).children()),Time.Update())}),$.Dialog.close()}))}})});var i=$("#signout"),o=$(".session-list"),a=$content.find(".title .username").text().trim(),n=a===$sidebar.children(".welcome").find(".un").text().trim();o.find("button.remove").off("click").on("click",function(e){e.preventDefault();var t="Deleting session",o=$(this),s=o.closest("li"),r=s.children(".browser").text().trim(),l=s.children(".platform"),c=l.length?" on <em>"+l.children("strong").text().trim()+"</em>":"";if(0===s.index()&&s.children().last().text().indexOf("Current")!==-1)return i.triggerHandler("click");var u=s.attr("id").replace(/\D/g,"");return"undefined"==typeof u||isNaN(u)||!isFinite(u)?$.Dialog.fail(t,"Could not locate Session ID, please reload the page and try again."):void $.Dialog.confirm(t,(n?"You":a)+" will be signed out of <em>"+r+"</em>"+c+".<br>Continue?",function(e){e&&($.Dialog.wait(t,"Signing out of "+r+c),$.post("/user/sessiondel/"+u,$.mkAjaxHandler(function(){return this.status?0!==s.siblings().length?(s.remove(),$.Dialog.close()):($.Dialog.wait(!1,"Reloading page",!0),void $.Navigation.reload(function(){$.Dialog.close()})):$.Dialog.fail(t,this.message)})))})}),o.find("button.useragent").on("click",function(e){e.preventDefault();var t=$(this);$.Dialog.info("User Agent string for session #"+t.parents("li").attr("id").substring(8),"<code>"+t.data("agent")+"</code>")}),$("#signout-everywhere").on("click",function(){$.Dialog.confirm("Sign out from ALL sessions","This will invalidate ALL sessions. Continue?",function(e){e&&($.Dialog.wait(!1,"Signing out"),$.post("/signout?everywhere",{username:a},$.mkAjaxHandler(function(){return this.status?($.Dialog.wait(!1,"Reloading page",!0),void $.Navigation.reload(function(){$.Dialog.close()})):$.Dialog.fail(!1,this.message)})))})}),$("#discord-verify").on("click",function(e){e.preventDefault(),$.Dialog.wait("Verify identity on Discord","Getting your token"),$.post("/user/discord-verify",$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var e="/verify "+this.token,t=$.mk("div").attr("class","align-center").append("Run the following command in any of the channels:",$.mk("div").attr("class","disc-verify-code").html("<code>"+e+"</code>").on("mousedown",function(e){e.preventDefault(),$(this).select()}),$.mk("button").attr("class","darkblue typcn typcn-clipboard").text("Copy command to clipboard").on("click",function(t){t.preventDefault(),$.copy(e,t)}));$.Dialog.info(!1,t)}))}),$("#unlink").on("click",function(e){e.preventDefault();var t="Unlink account & sign out";$.Dialog.confirm(t,"Are you sure you want to unlink your account?",function(e){e&&($.Dialog.wait(t,"Removing account link"),$.post("/signout?unlink",$.mkAjaxHandler(function(){return this.status?($.Dialog.wait(!1,"Reloading page",!0),void $.Navigation.reload(function(){$.Dialog.close()})):$.Dialog.fail(!1,this.message)})))})}),$("#awaiting-deviations").children("li").children(":last-child").children("button.check").on("click",function(e){e.preventDefault();var t=$(this).parents("li"),i=t.attr("id").split("-"),o=i[0],a=i[1];$.Dialog.wait("Deviation acceptance status","Checking"),$.post("/post/lock-"+o+"/"+a,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var e=this.message;$.Dialog.wait(!1,"Reloading page"),$.Navigation.reload(function(){$.Dialog.success(!1,e,!0)})}))});var s=$("#settings").find("form").on("submit",function(t){t.preventDefault();var i=$(this),o=i.attr("action"),a=i.mkData(),n=i.find('[name="value"]'),s=n.data("orig");$.Dialog.wait("Saving setting","Please wait"),$.post(o,a,$.mkAjaxHandler(function(){return this.status?(n.is("[type=number]")?n.val(this.value):n.is("[type=checkbox]")&&(this.value=Boolean(this.value),n.prop("checked",this.value)),n.data("orig",this.value).triggerHandler("change"),void e(o.split("/").pop(),s,this.value)):$.Dialog.fail(!1,this.message)}))}).children("label");s.children("input[type=number], select").each(function(){var e=$(this);e.data("orig",e.val().trim()).on("keydown keyup change",function(){var e=$(this);e.siblings(".save").attr("disabled",e.val().trim()===e.data("orig"))})}),s.children("input[type=checkbox]").each(function(){var e=$(this);e.data("orig",e.prop("checked")).on("keydown keyup change",function(){var e=$(this);e.siblings(".save").attr("disabled",e.prop("checked")===e.data("orig"))})})});
//# sourceMappingURL=/js/min/user.js.map
