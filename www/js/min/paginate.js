"use strict";!function(){function t(t){if(void 0===t){var a=window.location.pathname.split("/"),e=1;if(a.length>1){var n=a[a.length-1];isNaN(n)||(e=parseInt(n,10))}return{pageNumber:e,maxPages:null}}return{pageNumber:parseInt(t.children("li").children("strong").text(),10),maxPages:parseInt(t.children(":not(.spec)").last().text(),10)}}function a(){i.removeClass("loading").find(".loading").removeClass("loading")}var e=/Page \d+/g,n=location.pathname.replace(/\/\d+$/,"")+"/",i=$(".pagination"),o="Navigation",r=$.mk("form").attr("id","goto-page").html('<label>\n\t\t\t\t<span>Enter page number</span>\n\t\t\t\t<input type="number" min="1" step="1">\n\t\t\t</label>').on("submit",function(t){t.preventDefault();var a=parseInt($(this).find("input:visible").val(),10);a=isNaN(a)?1:Math.max(1,a),$.Dialog.wait("Navigation","Loading page "+a),$.toPage(n+a).then(function(){$.Dialog.close()})});$d.off("paginate-refresh").on("paginate-refresh",function(){n=location.pathname.replace(/\/\d+$/,"")+"/",i=$(".pagination"),o="Navigation",i.off("click").on("click","a",function(a){a.preventDefault(),a.stopPropagation(),$("#ctxmenu").hide();var e=$(this);if(void 0===e.attr("href")){var n=t(e.closest(".pagination"));return console.log(n),$.Dialog.request("Navigation",r.clone(!0,!0),"Go to page",function(t){t.find("input:visible").attr("max",n.maxPages).val(n.pageNumber).get(0).select()})}e.closest("li").addClass("loading"),$.toPage(this.pathname)}),$w.off("nav-popstate").on("nav-popstate",function(t,a,e){var n=a,i=[!1,!0,void 0,!0];void 0!==a.baseurl&&$.Navigation._lastLoadedPathname.replace(/\/\d+($|\?)/,"$1")!==a.baseurl?e(location.pathname+location.search+location.hash,function(){$.toPage.apply(n,i)}):$.toPage.apply(n,i)}),$.toPage=function(r,l,s,p,g){r||(r=location.pathname);var c=parseInt(r.replace(/^.*\/(\d+)(?:\?.*)?$/,"$1"),10),u=this.state||{};if(isNaN(c))return $.Dialog.fail(o,"Could not get page number to go to");var h=t();if(!s&&(h.pageNumber===c||h.pageNumber===u.page)&&location.pathname===r)return!l&&$.Dialog.fail(o,"You are already on page "+h.pageNumber);var d={paginate:!0},f=[],m=this.query,v="string"==typeof m;return v?f=f.concat(m.split("&")):location.search.length>1&&(f=f.concat(location.search.substring(1).split("&"))),f.length&&($.each(f,function(t,a){0!==(a=a.replace(/\+/g," ").split("="))[1].length&&(d[decodeURIComponent(a[0])]=decodeURIComponent(a[1]))}),f=void 0),this.btnl?d.btnl=!0:i.addClass("loading"),r+=location.hash,new Promise(function(t,l){$.get(r,d,$.mkAjaxHandler(function(){if(!this.status)return a(),$.Dialog.fail(o,this.message);if(this.goto)return a(),$.Navigation.visit(this.goto,function(){$.Dialog.close()});c=parseInt(this.page,10);var r=$navbar.find("li.active").children().last();e.test(r.text())&&r.html(function(){this.innerHTML.replace(e,"Page "+c)}),"function"==typeof g&&(document.title=g(c)),document.title=document.title.replace(e,"Page "+c),$navbar.find("li.active").children().last().html(function(){return this.innerHTML.replace(e,"Page "+c)});var l=this.request_uri||n+c+(location.search.length>1?location.search:""),s=[{paginate:!0,page:c,baseurl:this.request_uri.replace(/\/\d+($|\?)/,"$1")},"",l];v&&(s[0].query=m),"function"==typeof window.ga&&window.ga("send",{hitType:"pageview",page:l,title:document.title}),(!0===p||u.page!==c&&!isNaN(c)||v)&&(history.replaceState.apply(history,s),$.WS.navigate()),i.filter('[data-for="'+this.for+'"]').html(this.pagination);var h=jQuery.Event("page-switch");$(this.update).html(this.output).trigger(h),Time.Update(),a(),t()})).fail(function(){a(),l()})})}})}();
//# sourceMappingURL=/js/min/paginate.js.map
