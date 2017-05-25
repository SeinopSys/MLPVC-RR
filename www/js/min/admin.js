'use strict';DocReady.push(function(){'use strict';(function(){function a(a){a=a.replace(/<img[^>]+>/g,'').match(b);var d={};$.each(a,function(a,b){var e=b.match(c);e&&'undefined'==typeof d[e[1]]&&(d[e[1]]=!0)});var e=Object.keys(d);return e?void($.Dialog.wait('Bulk approve posts','Attempting to approve '+e.length+' post'+(1===e.length?'':'s')),$.post('/admin/mass-approve',{ids:e.join(',')},$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var a=this.message,b=function(){a?$.Dialog.success(!1,a,!0):$.Dialog.close()};this.reload?($.Dialog.wait(!1,'Reloading page'),$.Navigation.reload(b)):b()}))):$.Dialog.fail('No deviations found on the pasted page.')}$('#bulk-how').on('click',function(){$.Dialog.info('How to approve posts in bulk','<p>This tool is easier to use than you would think. Here\'s how it works:</p>\n\t\t\t\t<ol>\n\t\t\t\t\t<li>\n\t\t\t\t\t\tIf you have the group watched, visit <a href="http://www.deviantart.com/notifications/#view=groupdeviations%3A17450764" target="_blank" rel=\'noopener\'>this link</a><br>\n\t\t\t\t\t\tIf not, go to the <a href="http://mlp-vectorclub.deviantart.com/messages/?log_type=1&instigator_module_type=0&instigator_roleid=1276365&instigator_username=&bpp_status=4&display_order=desc" target="_blank" rel=\'noopener\'>Processed Deviations queue</a>\n\t\t\t\t\t</li>\n\t\t\t\t\t<li>Once there, press <kbd>Ctrl</kbd><kbd>A</kbd> (which will select the entire page)</li>\n\t\t\t\t\t<li>Now press <kbd>Ctrl</kbd><kbd>C</kbd> (copying the selected content)</li>\n\t\t\t\t\t<li>Return to this page and click into the box below (you should see a blinking cursor afterwards)</li>\n\t\t\t\t\t<li>Hit <kbd>Ctrl</kbd><kbd>V</kbd></li> (to paste what you just copied)\n\t\t\t\t\t<li>Repeat these steps if there are multiple pages of results.</li>\n\t\t\t\t</ol>\n\t\t\t\t<p>The script will look for any deviation links in the HTML code of the page, which it then sends over to the server to mark them as approved if they were used to finish posts on the site.</p>')}),$('.mass-approve').children('.textarea').on('paste',function(b){var c,d,e,f=this;if(b.originalEvent.clipboardData&&b.originalEvent.clipboardData.types&&b.originalEvent.clipboardData.getData&&(c=b.originalEvent.clipboardData.types,c instanceof DOMStringList&&c.contains('text/html')||c.indexOf&&-1!==c.indexOf('text/html')))return d=b.originalEvent.clipboardData.getData('text/html'),a(d),b.stopPropagation(),b.preventDefault(),!1;for(e=document.createDocumentFragment();0<f.childNodes.length;)e.appendChild(f.childNodes[0]);return function b(c,d){if(c.childNodes&&0<c.childNodes.length){var e=c.innerHTML;c.innerHTML='',c.appendChild(d),a(e)}else setTimeout(function(){b(c,d)},20)}(f,e),!0});var b=/(?:[A-Za-z\-\d]+\.)?deviantart\.com\/art\/(?:[A-Za-z\-\d]+-)?(\d+)/g,c=/\/(?:[A-Za-z\-\d]+-)?(\d+)$/})();var a=$('.recent-posts'),b=!1;window._AdminRecentScroll=function(){if(!b&&a.isInViewport()){var c=a.children('div');c.is(':empty')&&(b=!0,$.post('/admin/recent-posts',$.mkAjaxHandler(function(){return this.status?void c.html(this.html):c.append('<div class="notice fail align-center">This section failed to load.</div>')})))}},$w.on('scroll',window._AdminRecentScroll),window._AdminRecentScroll()},function(){'use strict';$w.off('scroll',window._AdminRecentScroll)});
//# sourceMappingURL=/js/min/admin.js.map
