/* global ace */
ace.define("ace/mode/colorguide",["require","exports","ace/mode/colorguide_highlight_rules","ace/mode/folding/coffee","ace/range","ace/mode/text","ace/lib/oop"], (require, exports) => {
	"use strict";
	let oop = require("../lib/oop");

	function Mode(){
		this.HighlightRules = function() {
			this.$rules = {
				"start": [
					{
					    token: "comment.line",
					    regex: /^\/\/.+$/,
					},
					{
					    token: "invalid",
					    regex: /^(?:[^\/]+\/{2}.*|\/(?:[^\/]?.*)?)$/,
					},
					{
					    token: "invalid",
					    regex: /^\s*[^#].*$/,
					},
					{
					    token: "color",
					    regex: /^\s*#(?:[a-f\d]{6}|[a-f\d]{3})\s+/,
					},
					{
					    token: "invalid",
					    regex: /^\s*#\S*[^a-f\d\s]\S*?(\s|$)/,
					},
					{
					    token: "invalid",
					    regex: /^\s*#(?:[a-f\d]{1,5}|[a-f\d]{4,5}|[a-f\d]{7,})?\S?/,
					},
					{
					    token: "colorname",
					    regex: /\s*[a-z\d][ -~]{2,29}\s*$/,
					},
					{
					    token: "invalid",
					    regex: /\s*.*[^ -~].*\s*$/,
					},
					{
					    token: "invalid",
					    regex: /\s*(?:.{1,2}|.{30,})\s*$/,
					},
					{ caseInsensitive: true },

				],
				"color": [
					{
						token: "constant.other",
						regex: /^\s*#(?:[a-f\d]{6}|[a-f\d]{3})/,
					},
				],
				"colorname": [
					{
						token: "string.unquoted",
						regex: /[^\s#][ -~]{2,29}\s*$/,
					},
				],
			};
		};
		oop.inherits(this.HighlightRules, require("./text_highlight_rules").TextHighlightRules);

		// TODO Figure this out
		/* var WorkerClient = require("ace/worker/worker_client").WorkerClient;
		this.createWorker = function(session) {
		    var worker = new WorkerClient(["ace"], "ace/worker/colorguide_worker", "WorkerModule");
		    worker.attachToDocument(session.getDocument());

		    worker.on("lint", function(results) {
		        session.setAnnotations(results.data);
		    });

		    worker.on("terminate", function() {
		        session.clearAnnotations();
		    });

		    return worker;
		}; */
	}
	oop.inherits(Mode, require("./text").Mode);

	Mode.prototype.getNextLineIndent = () => '';
	Mode.prototype.$id = "ace/mode/colorguide";

	exports.Mode = Mode;
});
