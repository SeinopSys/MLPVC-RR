"use strict";DocReady.push(function(){var e=!1,t=$("#filter-form");t.on("submit",function(e){e.preventDefault();var n=t.find('[name="type"] option:selected'),i=n.val(),a=t.find('[name="by"]').val().trim(),s=""+(i.length?i.replace("of type ","")+" entries ":"")+(a.length?(i.length?"":"Entries")+" by "+a+" ":""),l=!!s.length&&t.serialize();t.find("button[type=reset]").attr("disabled",l===!1),l!==!1?$.Dialog.wait("Navigation","Looking for "+s.replace(/</g,"&lt;")):$.Dialog.success("Navigation","Search terms cleared"),$.toPage.call({query:l},window.location.pathname.replace(/\d+($|\?)/,"1$1"),!0,!0,!1,function(){return l!==!1?/^Page \d+/.test(document.title)?s+" - "+document.title:document.title.replace(/^.*( - Page \d+)/,s+"$1"):document.title.replace(/^.* - (Page \d+)/,"$1")})}).on("reset",function(e){e.preventDefault(),t.find('[name="type"]').val(""),t.find('[name="by"]').val(""),t.triggerHandler("submit")}),$("#logs").find("tbody").off("page-switch").on("page-switch",function(){$(this).children().each(function(){var n=$(this);n.find(".expand-section").off("click").on("click",function(){var t=$(this),i="Log entry details";if(t.hasClass("typcn-minus"))t.toggleClass("typcn-minus typcn-plus").next().stop().slideUp();else if(1===t.next().length)t.toggleClass("typcn-minus typcn-plus").next().stop().slideDown();else{if(e)return!1;e=!0,t.removeClass("typcn-minus typcn-plus").addClass("typcn-refresh");var a=parseInt(n.children().first().text());$.post("/admin/logs/details/"+a,$.mkAjaxHandler(function(){this.status||$.Dialog.fail(i,this.message);var e=$.mk("div").attr("class","expandable-section").css("display","none");$.each(this.details,function(t,n){"boolean"==typeof n[1]&&(n[1]='<span class="color-'+(n[1]?"green":"red")+'">'+(n[1]?"yes":"no")+"</span>");var i=/[a-z]$/i;n[0]="<strong>"+n[0]+(i.test(n[0])?":":"")+"</strong>",e.append("<p>"+n.join(" ")+"</p>")}),e.insertAfter(t).slideDown(),Time.Update(),t.addClass("typcn-minus color-darkblue")})).always(function(){e=!1,t.removeClass("typcn-refresh")}).fail(function(){t.addClass("typcn-times color-red").css("cursor","not-allowed").off("click")})}}),n.find(".server-init").off("click").on("click",function(){t.find('[name="by"]').val($(this).text().trim()),t.triggerHandler("submit")})})}).trigger("page-switch").on("click",".dynt-el",function(){var e=$w.width();if(e>=650)return!0;var t=$(this),n=t.parent(),i=n.parent(),a=i.children(".ip");a.children("a").length&&(a=a.clone(!0,!0),a.children(".self").html(function(){return $(this).text()}));var s=a.contents(),l=$.mk("span").attr("class","modal-ip").append("<br><b>Initiator:</b> ",s.eq(0));s.length>1&&l.append("<br><b>IP Address:</b> "+s.get(2).textContent),$.Dialog.info("Hidden details of entry #"+i.children(".entryid").text(),$.mk("div").append("<b>Timestamp:</b> "+n.children("time").html().trim().replace(/<br>/," "),l))})});
//# sourceMappingURL=/js/min/admin-logs.js.map