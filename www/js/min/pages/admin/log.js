"use strict";$(function(){var n=!1,t=$("#filter-form");t.on("reset",function(e){e.preventDefault(),t.find('[name="type"]').val(""),t.find('[name="by"]').val(""),t.trigger("submit")});var e=$("#logs");e.find("tbody").off("page-switch").on("click",".expand-section",function(){var e=$(this);if(e.hasClass("typcn-minus"))e.toggleClass("typcn-minus typcn-plus").next().stop().slideUp();else if(1===e.next().length)e.toggleClass("typcn-minus typcn-plus").next().stop().slideDown();else{if(n)return!1;n=!0,e.removeClass("typcn-minus typcn-plus").addClass("typcn-refresh");var t=parseInt(e.closest("td").siblings().first().text()),s=function(){e.addClass("typcn-times color-red").css("cursor","not-allowed").off("click")};$.post("/admin/logs/details/"+t,$.mkAjaxHandler(function(){if(!this.status)return!0===this.unclickable&&e.replaceWith(e.text().trim()),$.Dialog.fail("Log entry details",this.message),s();var i=$.mk("div").attr("class","expandable-section").css("display","none");$.each(this.details,function(e,t){var s=void 0,n=$.mk("strong").html(t[0]+": ");"string"==typeof t[2]&&n.addClass("color-"+t[2]),null===t[1]?s=$.mk("em").addClass("color-darkblue").text("empty"):"boolean"==typeof t[1]?s=$.mk("span").addClass("color-"+(t[1]?"green":"red")).text(t[1]?"yes":"no"):$.isArray(t[1])?(s=void 0,n.html(n.html().replace(/:\s$/,""))):s=t[1],i.append($.mk("div").append(n,s))}),i.insertAfter(e).slideDown(),Time.Update(),e.addClass("typcn-minus color-darkblue")})).always(function(){n=!1,e.removeClass("typcn-refresh")}).fail(s)}}).on("click",".server-init",function(){t.find('[name="by"]').val($(this).text().trim()),t.trigger("submit")}).on("click",".search-ip",function(){var e=$(this);t.find('[name="by"]').val(e.hasClass("your-ip")?"my IP":e.siblings(".address").text().trim()),t.trigger("submit")}).on("click",".search-user",function(){var e=$(this);t.find('[name="by"]').val(e.hasClass("your-name")?"me":e.siblings(".name").text().trim()),t.trigger("submit")}).on("click",".dynt-el",function(){if(650<=$w.width())return!0;var e=$(this).parent(),t=e.parent(),s=t.children(".ip");s.children("a").length&&(s=s.clone(!0,!0)).children(".self").html(function(){return $(this).text()});var n=s.contents(),i=$.mk("span").attr("class","modal-ip").append("<br><b>Initiator:</b> ",n.eq(0));1<n.length&&i.append("<br><b>IP Address:</b> "+n.get(2).textContent),$.Dialog.info("Hidden details of entry #"+t.children(".entryid").text(),$.mk("div").append("<b>Timestamp:</b> "+e.children("time").html().trim().replace(/<br>/," "),i))});var r=[{className:"darkblue",showins:!0,showdel:!0,title:"diff"},{className:"green",showins:!0,showdel:!1,title:"new"},{className:"red",showins:!1,showdel:!0,title:"old"}];e.on("click contextmenu",".btn.view-switch",function(e){var t="contextmenu"===e.type;if(t&&e.shiftKey)return!0;e.preventDefault();for(var s=$(e.target),n=s.next(),i=s.attr("class").match(/\b(darkblue|green|red)\b/)[1],a=void 0,l=0;l<r.length;l++)r[l].className===i&&(a=r[l+(t?-1:1)]);void 0===a&&(a=r[t?r.length-1:0]),n.find("ins")[a.showins?"show":"hide"](),n.find("del")[a.showdel?"show":"hide"](),n[a.showins&&a.showdel?"removeClass":"addClass"]("no-colors"),n[0===n.contents().filter(function(){return!/^(del|ins)$/.test(this.nodeName.toLowerCase())||"none"!==this.style.display}).length?"addClass":"removeClass"]("empty"),s.removeClass(i).addClass(a.className).text(a.title)})});
//# sourceMappingURL=/js/min/pages/admin/log.js.map
