"use strict";
/*!
 * Timezone data string taken from:
 * http://momentjs.com/downloads/moment-timezone-with-data.js
 * version 0.4.1 by Tim Wood, licensed MIT
 */$(function(){var t=$("#content").find("table"),e=window.SEASON,i=window.EPISODE;moment.tz.add("America/Los_Angeles|PST PDT PWT PPT|80 70 70 70|010102301010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010101010|-261q0 1nX0 11B0 1nX0 SgN0 8x10 iy0 5Wp0 1Vb0 3dB0 WL0 1qN0 11z0 1o10 11z0 1o10 11z0 1o10 11z0 1o10 11z0 1qN0 11z0 1o10 11z0 1o10 11z0 1o10 11z0 1o10 11z0 1qN0 WL0 1qN0 1cL0 1cN0 1cL0 1cN0 1cL0 1cN0 1fz0 1a10 1fz0 1cN0 1cL0 1cN0 1cL0 1cN0 1cL0 1cN0 1cL0 1cN0 1fz0 1cN0 1cL0 1cN0 1cL0 s10 1Vz0 LB0 1BX0 1cN0 1fz0 1a10 1fz0 1cN0 1cL0 1cN0 1cL0 1cN0 1cL0 1cN0 1cL0 1cN0 1fz0 1a10 1fz0 1cN0 1cL0 1cN0 1cL0 1cN0 1cL0 14p0 1lb0 14p0 1nX0 11B0 1nX0 11B0 1nX0 14p0 1lb0 14p0 1lb0 14p0 1nX0 11B0 1nX0 11B0 1nX0 14p0 1lb0 14p0 1lb0 14p0 1lb0 14p0 1nX0 11B0 1nX0 11B0 1nX0 14p0 1lb0 14p0 1lb0 14p0 1nX0 11B0 1nX0 11B0 1nX0 Rd0 1zb0 Op0 1zb0 Op0 1zb0 Rd0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Rd0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Rd0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Rd0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Rd0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0 Op0 1zb0");var n=function(t){return moment.tz(t,"America/Los_Angeles").set({day:"Saturday",h:8,m:30,s:0}).local()},a=n(new Date),o=$.momentToYMD(a),s=$.momentToHM(a),l=a.format("dddd"),r=window.EP_TITLE_REGEX;function d(t){var e=$.mk("form").attr("id",t).append('<div class="label episode-only">\n\t\t\t\t<span>Season, Episode & Overall #</span>\n\t\t\t\t<div class=input-group-3>\n\t\t\t\t\t<input type="number" min="1" max="9" name="season" placeholder="Season #" required>\n\t\t\t\t\t<input type="number" min="1" max="26" name="episode" placeholder="Episode #" required>\n\t\t\t\t\t<input type="number" min="1" max="255" name="no" placeholder="Overall #" required>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class="label movie-only">\n\t\t\t\t<span>Overall movie number</span>\n\t\t\t\t<input type="number" min="1" max="26" name="episode" placeholder="Overall #" required>\n\t\t\t</div>\n\t\t\t<input class="movie-only" type="hidden" name="season" value="0">',$.mk("label").append("<span>Title (5-35 chars.)</span>",$.mk("input").attr({type:"text",minlength:5,name:"title",placeholder:"Title",autocomplete:"off",required:!0}).patternAttr(r)),'<div class="notice info align-center movie-only">\n\t\t\t\t<p>Include "Equestria Girls: " if applicable. Prefixes don’t count towards the character limit.</p>\n\t\t\t</div>\n\t\t\t<div class="label">\n\t\t\t\t<span>Air date & time</span>\n\t\t\t\t<div class="input-group-2">\n\t\t\t\t\t<input type="date" name="airdate" placeholder="YYYY-MM-DD" required>\n\t\t\t\t\t<input type="time" name="airtime" placeholder="HH:MM" required>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class="notice info align-center button-here">\n\t\t\t\t<p>Specify the <span class="episode-only">episode</span><span class="movie-only">movie</span>’s air date and time in <strong>your computer’s timezone</strong>.</p>\n\t\t\t</div>\n\t\t\t<label class="episode-only"><input type="checkbox" name="twoparter"> Has two parts</label>\n\t\t\t<div class="notice info align-center episode-only">\n\t\t\t\t<p>If this is checked, only specify the episode number of the first part</p>\n\t\t\t</div>\n\t\t\t<div class="label">\n\t\t\t\t<span>Notes (optional, 1000 chars. max)</span>\n\t\t\t\t<div class="ace_editor"></div>\n\t\t\t</div>');return $.mk("button").attr("class","episode-only").text("Set time to "+s+" this "+l).on("click",function(t){t.preventDefault(),$(this).parents("form").find('input[name="airdate"]').val(o).next().val(s)}).appendTo(e.children(".button-here")),e}var p=new d("addep"),c=new d("editep");function m(t){t.preventDefault();var n=$(this),a="edit-ep"===n.attr("id")?e?"S"+e+"E"+i:"Movie#"+i:n.closest("tr").attr("data-epid"),o=/^Movie/.test(a);$.Dialog.wait("Editing "+a,"Getting "+(o?"movie":"episode")+" details from server"),o&&(a="S0E"+a.split("#")[1]),$.post("/episode/get/"+a,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var t=c.clone(!0,!0);t.find(o?".episode-only":".movie-only").remove(),o||t.find("input[name=twoparter]").prop("checked",!!this.ep.twoparter),delete this.ep.twoparter;var e=moment(this.ep.airs);this.ep.airdate=$.momentToYMD(e),this.ep.airtime=$.momentToHM(e);var i=this.epid;delete this.epid;var n=this.ep.notes;delete this.ep.notes,$.each(this.ep,function(e,i){t.find("input[name="+e+"]").val(i)}),$.Dialog.request(!1,t,"Save",function(t){var e=void 0;$.getAceEditor(!1,"html",function(i){try{var a=t.find(".ace_editor").get(0),o=ace.edit(a);(e=$.aceInit(o,i)).setMode(i),e.setUseWrapMode(!0),n&&e.setValue(n)}catch(t){console.error(t)}}),t.on("submit",function(t){t.preventDefault();var n=$(this).mkData(),a=$.mkMoment(n.airdate,n.airtime);delete n.airdate,delete n.airtime,n.airs=a.toISOString(),n.notes=e.getValue(),$.Dialog.wait(!1,"Saving changes"),$.post("/episode/set/"+i,n,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);$.Dialog.wait(!1,"Updating page",!0),$.Navigation.reload()}))})})}))}$("#add-episode, #add-movie").on("click",function(t){t.preventDefault();var e=/movie/.test(this.id),i=p.clone(!0,!0);i.find(e?".episode-only":".movie-only").remove(),e||i.prepend($.mk("div").attr("class","align-center").html($.mk("button").attr("class","typcn typcn-flash blue").text("Pre-fill based on last added").on("click",function(t){var e=$(t.target),i=e.closest("form");e.disable(),$.post("/episode/prefill",$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var t=n(this.airday);$.each({airdate:$.momentToYMD(t),airtime:$.momentToHM(t),episode:this.episode,season:this.season,no:this.no},function(t,e){i.find("[name="+t+"]").val(e)})})).always(function(){e.enable()})}))),$.Dialog.request("Add "+(e?"Movie":"Episode"),i,"Add",function(t){var i=void 0;$.getAceEditor(!1,"html",function(e){try{var n=t.find(".ace_editor").get(0),a=ace.edit(n);(i=$.aceInit(a,e)).setMode(e),i.setUseWrapMode(!0)}catch(t){console.error(t)}}),t.on("submit",function(n){n.preventDefault();var a=t.find("input[name=airdate]").attr("disabled",!0).val(),o=t.find("input[name=airtime]").attr("disabled",!0).val(),s=$.mkMoment(a,o).toISOString(),l=$(this).mkData({airs:s});l.notes=i.getValue(),$.Dialog.wait(!1,"Adding "+(e?"movie":"episode")+" to database"),$.post("/episode/add",l,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);$.Dialog.wait(!1,"Opening "+(e?"movie":"episode")+" page",!0),$.Navigation.visit(this.url)}))})})}),$content.on("click","#edit-ep",m),t.on("click",".edit-episode",m).on("click",".delete-episode",function(t){t.preventDefault();var e=$(this).closest("tr").data("epid"),i=/^Movie/.test(e);$.Dialog.confirm("Deleting "+e,"<p>This will remove <strong>ALL</strong><ul><li>requests</li><li>reservations</li><li>video links</li><li>and votes</li></ul>associated with the "+(i?"movie":"episode")+", too.</p><p>Are you sure you want to delete it?</p>",function(t){t&&($.Dialog.wait(!1,"Removing episode"),$.post("/episode/delete/"+e,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);$.Navigation.reload(!0)})))})})});
//# sourceMappingURL=/js/min/pages/show/index-manage.js.map
