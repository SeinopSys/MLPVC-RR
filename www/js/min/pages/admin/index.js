"use strict";$(function(){!function(){$("#bulk-how").on("click",function(){$.Dialog.info("How to approve posts in bulk",'<p>This tool is easier to use than you would think. Here\'s how it works:</p>\n\t\t\t\t<ol>\n\t\t\t\t\t<li>\n\t\t\t\t\t\tIf you have the group watched, visit <a href="http://www.deviantart.com/notifications/#view=groupdeviations%3A17450764" target="_blank" rel=\'noopener\'>this link</a><br>\n\t\t\t\t\t\tIf not, go to the <a href="http://mlp-vectorclub.deviantart.com/messages/?log_type=1&instigator_module_type=0&instigator_roleid=1276365&instigator_username=&bpp_status=4&display_order=desc" target="_blank" rel=\'noopener\'>Processed Deviations queue</a>\n\t\t\t\t\t</li>\n\t\t\t\t\t<li>Once there, press <kbd>Ctrl</kbd><kbd>A</kbd> (which will select the entire page)</li>\n\t\t\t\t\t<li>Now press <kbd>Ctrl</kbd><kbd>C</kbd> (copying the selected content)</li>\n\t\t\t\t\t<li>Return to this page and click into the box below (you should see a blinking cursor afterwards)</li>\n\t\t\t\t\t<li>Hit <kbd>Ctrl</kbd><kbd>V</kbd></li> (to paste what you just copied)\n\t\t\t\t\t<li>Repeat these steps if there are multiple pages of results.</li>\n\t\t\t\t</ol>\n\t\t\t\t<p>The script will look for any deviation links in the HTML code of the page, which it then sends over to the server to mark them as approved if they were used to finish posts on the site.</p>')}),$(".mass-approve").children(".textarea").on("paste",function(t){var e=void 0,a=void 0,i=this;if(t.originalEvent.clipboardData&&t.originalEvent.clipboardData.types&&t.originalEvent.clipboardData.getData&&((e=t.originalEvent.clipboardData.types)instanceof DOMStringList&&e.contains("text/html")||e.indexOf&&-1!==e.indexOf("text/html")))return o(t.originalEvent.clipboardData.getData("text/html")),t.stopPropagation(),t.preventDefault(),!1;for(a=document.createDocumentFragment();i.childNodes.length>0;)a.appendChild(i.childNodes[0]);return function t(e,a){if(e.childNodes&&e.childNodes.length>0){var i=e.innerHTML;e.innerHTML="",e.appendChild(a),o(i)}else setTimeout(function(){t(e,a)},20)}(i,a),!0});var t=$(".recent-posts ul"),e=/(?:[A-Za-z\-\d]+\.)?deviantart\.com\/art\/(?:[A-Za-z\-\d]+-)?(\d+)|fav\.me\/d([a-z\d]{6,})/g,a=/\/(?:[A-Za-z\-\d]+-)?(\d+)$/,n=/fav\.me\/d([a-z\d]{6,})/;function o(o){o=o.replace(/<img[^>]+>/g,"").match(e);var s={};$.each(o,function(t,e){var i=e.match(a);if(i&&void 0===s[i[1]])s[i[1]]=!0;else if(i=e.match(n)){var o=parseInt(i[1],36);void 0===s[o]&&(s[o]=!0)}});var r=Object.keys(s);if(!r)return $.Dialog.fail("No deviations found on the pasted page.");$.Dialog.wait("Bulk approve posts","Attempting to approve "+r.length+" post"+(1!==r.length?"s":"")),$.post("/admin/mass-approve",{ids:r.join(",")},$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);this.html&&(t.html(this.html),i()),this.message?$.Dialog.success(!1,this.message,!0):$.Dialog.close()}))}}();var t=new IntersectionObserver(function(e){e.forEach(function(e){if(e.isIntersecting){var a=e.target;t.unobserve(a);var i=a.dataset.post.replace("-","/"),n=a.dataset.viewonly;$.get("/post/lazyload/"+i,{viewonly:n},$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail("Cannot load "+i.replace("/"," #"),this.message);$.loadImages(this.html).then(function(t){$(a).closest(".image").replaceWith(t)})}))}})}),e=new IntersectionObserver(function(e){e.forEach(function(e){if(e.isIntersecting){var a=e.target;t.unobserve(a);var i=$.mk("a"),n=new Image;n.src=a.dataset.src,i.attr("href",a.dataset.href).append(n),$(n).on("load",function(){$(a).closest(".image").html(i)})}})}),a=new IntersectionObserver(function(t){t.forEach(function(t){if(t.isIntersecting){var e=t.target;a.unobserve(e);var i=new Image;i.src=e.dataset.src,i.classList="avatar",$(i).on("load",function(){$(e).replaceWith(i)})}})});function i(){$(".post-deviation-promise").each(function(e,a){return t.observe(a)}),$(".post-image-promise").each(function(t,a){return e.observe(a)}),$(".user-avatar-promise").each(function(t,e){return a.observe(e)})}i()});
//# sourceMappingURL=/js/min/pages/admin/index.js.map
