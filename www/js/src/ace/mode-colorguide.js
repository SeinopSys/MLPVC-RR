/* global ace */
ace.define("ace/mode/colorguide",["require","exports","ace/mode/colorguide_highlight_rules","ace/mode/text","ace/lib/oop"], (require, exports) => {
	"use strict";
	let oop = require("../lib/oop");

	function Mode(){
		this.HighlightRules = function() {
			this.$rules = {
				start: [
					{
					    token: "comment.line.double-slash",
					    regex: /^\/\/.+$/,
					},
					{
						token: "hex",
						regex: /^#(?:[a-f\d]{6}|[a-f\d]{3})/,
						next: "colorname",
					},
					{
						token: "colorlink",
						regex: /^@\d+/,
						next: "colorname",
					},
					{ caseInsensitive: true },

				],
				colorname: [
					{
						token: "colorname",
						regex: /\s*[ -~]{3,30}\s*/,
						next: "colorid_start",
					},
					{
						token: "invalid",
						regex: /\s*$/,
						next: "invalid",
					},
				],
				colorid_start: [
					{
					    token: "colorid_start",
					    regex: /ID:/,
					    next: "colorid",
					},
					{
						token: "meta",
						regex: /\s*/,
					    next: "start",
					}
				],
				colorid: [
					{
						token: "colorid",
						regex: /\d+$/,
					    next: "start",
					}
				],
				invalid: [
					{
						token: "invalid",
						regex: /[\s\S]*/,
					},
				]
			};
		};
		oop.inherits(this.HighlightRules, require("./text_highlight_rules").TextHighlightRules);
	}
	oop.inherits(Mode, require("./text").Mode);

	Mode.prototype.getNextLineIndent = () => '';
	Mode.prototype.$id = "ace/mode/colorguide";

	exports.Mode = Mode;
});
