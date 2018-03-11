"use strict";!function(){var e={requestKey:"file",title:"Image upload",accept:"image/*",target:"",helper:!0};$.fn.uploadZone=function(n){var o=(n=$.extend(!0,{},e,n)).title,i=$(this).first(),s=$.mk("input").attr({type:"file",name:n.requestKey,accept:n.accept}),l=void 0;return n.helper&&(l=$.mk("div").addClass("helper")),s.on("set-image",function(e,a){var t=function(){i.removeClass("uploading"),a.path?s.prev().attr("href",a.path).children("img").fadeTo(200,0,function(){var e=$(this);i.addClass("loading"),e.attr("src",a.path).on("load",function(){i.removeClass("loading"),e.fadeTo(200,1)}),i.trigger("uz-uploadfinish",[a])}):i.trigger("uz-uploadfinish",[a])};!0===a.keep_dialog?t():$.Dialog.close(t)}),s.on("dragenter dragleave",function(e){e.stopPropagation(),e.preventDefault(),i["dragenter"===e.type?"addClass":"removeClass"]("drop")}),s.on("change drop",function(e){var a=e.target.files||e.originalEvent.dataTransfer.files;if(void 0===a[0]||!(a[0]instanceof File))return!0;i.trigger("uz-uploadstart").removeClass("drop").addClass("uploading");var t=new FormData;t.append(n.requestKey,a[0]),t.append("CSRF_TOKEN",$.getCSRFToken());var r={url:n.target,type:"POST",contentType:!1,processData:!1,cache:!1,data:t,success:$.mkAjaxHandler(function(){this.status?s.trigger("set-image",[this]):($.Dialog.fail(o,this.message),i.trigger("uz-uploadfinish"))}),complete:function(){i.removeClass("uploading"),n.helper&&l.removeAttr("data-progress"),s.val("")}};n.helper&&(r.xhr=function(){var e=$.ajaxSettings.xhr();return e.upload&&e.upload.addEventListener("progress",function(e){if(!e.lengthComputable||!n.helper)return!0;var a=e.loaded||e.position,t=e.total;l.attr("data-progress",Math.round(a/t*100))},!1),e}),$.ajax(r)}),i.append(s),n.helper&&i.append(l),i}}(jQuery);
//# sourceMappingURL=/js/min/jquery.uploadzone.js.map
