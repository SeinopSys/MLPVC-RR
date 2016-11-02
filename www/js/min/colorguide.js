"use strict";DocReady.push(function(){function t(){l=$("#toggle-copy-hash"),l.length&&l.off("display-update").on("display-update",function(){c=!$.LocalStorage.get("leavehash"),l.attr("class","blue typcn typcn-"+(c?"tick":"times")).text("Copy # with "+a+" codes: "+(c?"En":"Dis")+"abled")}).trigger("display-update").off("click").on("click",function(t){t.preventDefault(),c?$.LocalStorage.set("leavehash",1):$.LocalStorage.remove("leavehash"),l.triggerHandler("display-update")})}function e(){var t=$(".tags").children("span.tag");t.each(function(){var t=$(this),e="Click to quick search",o=t.attr("title"),i=t.attr("class").match(/typ\-([a-z]+)(?:\s|$)/);if(i=i?" qtip-tag-"+i[1]:"",!o){var a=t.text().trim();o=/^s\d+e\d+(-\d+)?$/i.test(a)?a.toUpperCase():$.capitalize(t.text().trim(),!0)}o&&t.qtip({content:{text:e,title:o},position:{my:"bottom center",at:"top center",viewport:!0},style:{classes:"qtip-tag"+i}})}),t.css("cursor","pointer").off("click").on("click",function(t){t.preventDefault();var e=this.innerHTML.trim();d.length?(d.find('input[name="q"]').val(e),d.triggerHandler("submit")):$.Navigation.visit("/cg"+(r?"/eqg":"")+("/1?q="+e.replace(/ /g,"+")))}),$("ul.colors").children("li").find(".valid-color").each(function(){var t=$(this);t.hasAttr("data-hasqtip")&&t.data("qtip").destroy();var e="Click to copy HEX "+a+" code to clipboard<br>Shift+Click to view RGB values",o=t.attr("title");return t.is(":empty")&&(e="No color to copy"),t.qtip({content:{text:e,title:o},position:{my:"bottom center",at:"top center",viewport:!0},style:{classes:"qtip-see-thru"}}),!0}).off("mousedown touchstart").on("click",function(t){t.preventDefault();var e=$(this),o=e.html().trim();if(t.shiftKey){var i=$.hex2rgb(o),a=e.closest("li"),n=[s?$content.children("h1").text():a.parents("li").children().last().children("strong").text().trim(),a.children().first().text().replace(/:\s+$/,""),e.attr("oldtitle")];return $.Dialog.info("RGB values for color "+o,'<div class="align-center">'+n.join(" &rsaquo; ")+'<br><span style="font-size:1.2em">rgb(<code class="color-red">'+i.r+'</code>, <code class="color-green">'+i.g+'</code>, <code class="color-darkblue">'+i.b+"</code>)</span></div>")}c||(o=o.replace("#","")),$.copy(o)}).filter(":not(.ctxmenu-bound)").ctxmenu([{text:"Copy HEX "+a+" code",icon:"clipboard","default":!0,click:function(){$(this).triggerHandler("click")}},{text:"View RGB values",icon:"brush",click:function(){$(this).triggerHandler({type:"click",shiftKey:!0})}}],function(t){return"Color: "+t.attr("oldtitle")}).on("mousedown",function(t){t.shiftKey&&t.preventDefault()}),$(".cm-direction:not(.tipped)").each(function(){var t=$(this),e=t.closest("li").attr("id").substring(1),o=new Image,i=new Image,a="/cg/v/"+e+".svg?t="+parseInt((new Date).getTime()/1e3),n=t.attr("data-cm-preview");setTimeout(function(){o.src=a,i.src=n},1),t.addClass("tipped").qtip({content:{text:$.mk("span").attr("class","cm-dir-image").backgroundImageUrl(a).append($.mk("div").attr("class","img cm-dir-"+t.attr("data-cm-dir")).backgroundImageUrl(n))},position:{my:"bottom center",at:"top center",viewport:!0},style:{classes:"qtip-link"}})}),n.find("li strong > a.btn.darkblue:not(.tipped)").add(n.find("li > .sprite:not(.tipped)")).each(function(){var t=$(this),e="li"===this.parentNode.nodeName.toLowerCase();t.addClass("tipped").qtip({content:{text:"Most browsers display colors incorrectly. To ensure that the accuracy of the colors is preserved, please download or copy the image, instead of using <kbd>PrintScreen</kbd> or programs that let you grab colors directly from the browser."},position:{my:e?"left center":"top center",at:e?"right center":"bottom center",viewport:!0},style:{classes:"qtip-pick-warn"}})})}function o(){n=$(".appearance-list"),s?$(".getswatch").off("click",i).on("click",i):n.off("click",".getswatch",i).on("click",".getswatch",i),e(),t()}function i(t){t.preventDefault();var e=$(this).closest("[id^=p]"),o=e.attr("id").substring(1),i=navigator&&navigator.userAgent&&/Macintosh/i.test(navigator.userAgent)?"<kbd>⌘</kbd><kbd>F12</kbd>":"<kbd>Ctrl</kbd><kbd>F12</kbd>",a=$.mk("div").html("<div class='hidden section ai'>\n\t\t\t\t\t<h4>How to import swatches to Adobe Illustrator</h4>\n\t\t\t\t\t<ul>\n\t\t\t\t\t\t<li>Because Illustator uses a proprietary format for swatch files, you must download a script <a href='/dist/Import Swatches from JSON.jsx?v=1.3' download='Import Swatches from JSON.jsx' class='btn typcn typcn-download'>by clicking here</a> to be able to import them from our site. Once you downloaded it, place it in an easy to find location, because you'll need to use it every time you want to import colors.<br><small>If you place it in <code>&hellip;\\Adobe\\Adobe Illustrator *\\Presets\\*\\Scripts</code> it'll be available as one of the options in the Scripts submenu.</small></li>\n\t\t\t\t\t\t<li>Once you have the script, <a href=\"/cg/v/"+o+'.json" class="btn blue typcn typcn-download">click here</a> to download the <code>.json</code> file that you\'ll need to use for the import.</li>\n\t\t\t\t\t\t<li>Now that you have the 2 files, open Illustrator, create/open a document, then go to <strong>File &rsaquo; Scripts &rsaquo; Other Script</strong> (or press '+i+') then find the file with the <code>.jsx</code> extension (the one you first downloaded). A dialog will appear telling you what to do next.</li>\n\t\t\t\t\t</ul>\n\t\t\t\t\t<div class="responsive-embed">\n\t\t\t\t\t\t<iframe src="https://www.youtube.com/embed/oobQZ2xiDB8" allowfullscreen async defer></iframe>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\'hidden section inkscape\'>\n\t\t\t\t\t<h4>How to import swatches to Inkscape</h4>\n\t\t\t\t\t<p>Download <a href=\'/cg/v/'+o+".gpl' class='btn blue typcn typcn-download'>this file</a> and place it in the <code>&hellip;\\Inkscape<wbr>\\<wbr>share<wbr>\\<wbr>palettes</code> folder. If you don't plan on using the other swatches, deleting them should make your newly imported swatch easier to find.</p>\n\t\t\t\t\t<p>You will most likely have to restart Inkscape for the swatch to show up in the <em>Swatches</em> (<kbd>F6</kbd>) tool window's menu.</p>\n\t\t\t\t\t<div class=\"responsive-embed\">\n\t\t\t\t\t\t<iframe src=\"https://www.youtube.com/embed/zmaJhbIKQqM\" allowfullscreen async defer></iframe>\n\t\t\t\t\t</div>\n\t\t\t\t</div>"),n=$.mk("select").attr("required",!0).html('<option value="" selected style="display:none">Choose one</option><option value="inkscape">Inkscape</option><option value="ai">Adobe Illustrator</option>').on("change",function(){var t=$(this),e=t.val(),o=t.parent().next().children().hide();e&&o.filter("."+e).show()}),r=$.mk("form").attr("id","swatch-save").append($.mk("label").attr("class","align-center").append("<span>Choose your drawing program:</span>",n),a);$.Dialog.info("Download swatch file",r)}var a=(window.Color,window.color),n=$(".appearance-list"),r=window.EQG,s=!!window.AppearancePage,c=!$.LocalStorage.get("leavehash"),l=void 0;window.copyHashToggler=function(){t()},window.copyHashEnabled=function(){return c};var d=$("#search-form");window.tooltips=function(){e()},n.filter("#list").on("page-switch",o),$d.on("paginate-refresh",o),o(),d.on("submit",function(t,e){t.preventDefault();var o=$(this),i=o.find("input[name=q]"),a=i.val(),n=0!==a.trim().length&&o.serialize();o.find("button[type=reset]").attr("disabled",n===!1),e||(n!==!1?$.Dialog.wait("Navigation","Searching for <code>"+a.replace(/</g,"&lt;")+"</code>"):$.Dialog.success("Navigation","Search terms cleared")),$.toPage.call({query:n,gofast:e},window.location.pathname.replace(/\d+($|\?)/,"1$1"),!0,!0,!1,function(){return n!==!1?/^Page \d+/.test(document.title)?a+" - "+document.title:document.title.replace(/^.*( - Page \d+)/,a+"$1"):document.title.replace(/^.* - (Page \d+)/,"$1")})}).on("reset",function(t){t.preventDefault();var e=$(this);e.find("input[name=q]").val(""),e.triggerHandler("submit")}).on("click",".sanic-button",function(){d.triggerHandler("submit",[!0])})},function(){$(".qtip").each(function(){var t=$(this);t.data("qtip").destroy(),t.remove()})});
//# sourceMappingURL=/js/min/colorguide.js.map
