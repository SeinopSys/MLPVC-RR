"use strict";$(function(){var t=$("#content").children("table").children("tbody");t.on("updatetimes",function(){t.children().children(":last-child").children("time.nodt").each(function(){this.innerHTML=moment($(this).attr("datetime")).format("D-MMMM-YYYY H:mm:ss").replace(/:00$/,"")})}).trigger("updatetimes")});
//# sourceMappingURL=/js/min/pages/show/index.js.map
