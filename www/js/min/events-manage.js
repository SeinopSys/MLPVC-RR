"use strict";DocReady.push(function(){var t=window.PRINTABLE_ASCII_PATTERN,e=window.EVENT_TYPES,n=Boolean(window.EventPage),a=$.mk("select").attr({name:"type",required:!0}).append('<option value="" style="display:none">(choose event type)</option>').on("change",function(){var t=$(this),e="contest"===t.val();t.parent().siblings(".who-vote")[e?"removeClass":"addClass"]("hidden").find("select")[e?"enable":"disable"]("hidden")}),i=$.mk("optgroup").attr("label","Available types").appendTo(a);$.each(e,function(t,e){i.append('<option value="'+t+'">'+e+"</option>")});var o=$.mk("form","event-editor").append($.mk("label").append("<span>Event name (2-64 chars.)</span>",$.mk("input").attr({type:"text",name:"name",minlength:2,maxlength:64,required:!0}).patternAttr(t)),'<div class="label">\n\t\t\t\t<span>Description (1-3000 chars.)<br>Uses <a href="https://help.github.com/articles/basic-writing-and-formatting-syntax/" target="_blank">Markdown</a> formatting</span>\n\t\t\t\t<div class="ace_editor"></div>\n\t\t\t</div>',$.mk("label").append("<span>Event type (cannot ba changed later)</span>",a),$.mk("label").attr("class","who-vote hidden").append("<span>Who can vote on the entries?</span>",'<select name="vote_role" required>\n\t\t\t\t\t<optgroup label="Roles">\n\t\t\t\t\t\t<option value="user" selected>Any DeviantArt User</option>\n\t\t\t\t\t\t<option value="member">Club Members</option>\n\t\t\t\t\t\t<option value="staff">Staff Members</option>\n\t\t\t\t\t</optgroup>\n\t\t\t\t</select>'),$.mk("div").attr("class","label").append("<span>Start date & time</span>",$.mk("div").attr("class","input-group-2").append('<input type="date" name="start_date">','<input type="time" name="start_time">')),$.mk("div").attr("class","notice info align-center").html("Leave <q>Start date & time</q> blank if you want the event to start immediately after you press Add. Always specify times in your computer's timezone."),$.mk("div").attr("class","label").append("<span>End date & time</span>",$.mk("div").attr("class","input-group-2").append('<input type="date" name="end_date" required>','<input type="time" name="end_time" required>')),$.mk("div").attr("class","label").append("<span>Who can enter & how many times?</span>",$.mk("div").attr("class","input-group-2").append('<select name="entry_role" required>\n\t\t\t\t\t\t<optgroup label="Role in the group">\n\t\t\t\t\t\t\t<option value="user" selected>Any DeviantArt User</option>\n\t\t\t\t\t\t\t<option value="member">Club Members</option>\n\t\t\t\t\t\t\t<option value="staff">Staff Members</option>\n\t\t\t\t\t\t</optgroup>\n\t\t\t\t\t\t<optgroup label="Special">\n\t\t\t\t\t\t\t<option value="spec_discord">Discord Server Members</option>\n\t\t\t\t\t\t\t<option value="spec_illustrator">Illustrator Users</option>\n\t\t\t\t\t\t\t<option value="spec_inkscape">Inkscape Users</option>\n\t\t\t\t\t\t\t<option value="spec_ponyscape">Ponyscape Users</option>\n\t\t\t\t\t\t</optgroup>\n\t\t\t\t\t</select>','<input type="text" name="max_entries" pattern="^(0*[1-9]\\d*|[Uu]nlimited|0)$" list="max_entries-list" value="1">\n\t\t\t\t\t<datalist id="max_entries-list" required>\n\t\t\t\t\t\t<option value="Unlimited">\n\t\t\t\t\t\t<option value="1">\n\t\t\t\t\t</datalist>')),$.mk("div").attr("class","notice info align-center").html("Enter <q>0</q> or <q>Unlimited</q> to remove the number of entries cap.")),s=function(t,e,a){var i=!!a,s=void 0;if(n){if(!i)return;s=$content.children("h1")}else s=t.siblings().first();$.Dialog.request(e,o.clone(!0,!0),"Save",function(t){var o=void 0,r=void 0;if($.getAceEditor(!1,"markdown",function(e){try{var n=t.find(".ace_editor").get(0),o=ace.edit(n);r=$.aceInit(o,e),r.setMode(e),r.setUseWrapMode(!0),i&&a.desc_src&&r.setValue(a.desc_src)}catch(t){console.error(t)}}),i){if(o=a.eventID,t.find("input[name=name]").val(a.name),t.find("[name=type]").parent().remove(),t.find("[name=entry_role]").val(a.entry_role),t.find("[name=max_entries]").val(a.max_entries?a.max_entries:"Unlimited"),a.starts_at){var l=moment(a.starts_at);t.find('input[name="start_date"]').val($.momentToYMD(l)),t.find('input[name="start_time"]').val($.momentToHM(l))}if(a.ends_at){var d=moment(a.ends_at);t.find('input[name="end_date"]').val($.momentToYMD(d)),t.find('input[name="end_time"]').val($.momentToHM(d))}}t.on("submit",function(a){a.preventDefault();var l=t.mkData();if(l.description=r.getValue(),l.start_date&&l.start_time){var d=$.mkMoment(l.start_date,l.start_time);l.starts_at=d.toISOString()}var p=$.mkMoment(l.end_date,l.end_time);l.ends_at=p.toISOString(),delete l.start_date,delete l.start_time,delete l.end_date,delete l.end_time,$.Dialog.wait(!1,"Saving changes"),n&&(l.EVENT_PAGE=!0),$.post("/event/"+(i?"set/"+o:"/add"),l,$.mkAjaxHandler(function(){return this.status?(l=this,void(i?n?($.Dialog.wait(!1,"Reloading page",!0),$.Navigation.reload(function(){$.Dialog.close()})):(s.text(l.name),l.newurl&&s.attr("href",function(t,e){return e.replace(/\/[^\/]+$/,"/"+l.newurl)}),$.Dialog.close()):($.Dialog.success(e,"Event added"),$.Dialog.wait(e,"Loading event page"),$.Navigation.visit(l.goto,function(){l.info?$.Dialog.info(e,l.info):$.Dialog.close()})))):$.Dialog.fail(!1,this.message)}))})})};$("#add-event").on("click",function(t){t.preventDefault(),s($(this),"Add new event")}),$content.on("click","[id^=event-] .edit-event",function(t){t.preventDefault();var e=$(this),n=e.closest("[id^=event-]"),a=n.attr("id").split("-")[1],i="Editing event #"+a;$.Dialog.wait(i,"Retrieving event details from server"),$.post("/event/get/"+a,$.mkAjaxHandler(function(){if(!this.status)return $.Dialog.fail(!1,this.message);var t=this;t.eventID=a,s(e,i,t)}))}),$content.on("click","[id^=event-] .delete-event",function(t){t.preventDefault();var e=$(this).closest("[id^=event-]"),a=e.attr("id").split("-")[1],i=n?$content.children("h1").text():e.find(".event-name").html();$.Dialog.confirm("Delete event #"+a,'Are you <strong class="color-red"><em>ABSOLUTELY</em></strong> sure you want to delete &ldquo;'+i+"&rdquo; along with all submissions?",function(t){t&&($.Dialog.wait(!1),$.post("/event/del/"+a,$.mkAjaxHandler(function(){return this.status?n?($.Dialog.wait("Navigation","Loading page 1"),void $.Navigation.visit("/events/1",function(){$.Dialog.close()})):($.Dialog.wait(!1,"Reloading page",!0),void $.Navigation.reload(function(){$.Dialog.close()})):$.Dialog.fail(!1,this.message)})))})})});
//# sourceMappingURL=/js/min/events-manage.js.map