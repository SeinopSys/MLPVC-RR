/* global $w,$d,$head,$body,$header,$sidebar,$sbToggle,$main,$footer,console,prompt,HandleNav,getTimeDiff,one,createTimeStr,PRINTABLE_ASCII_REGEX */
(function($){
	'use strict';
	// document.createElement shortcut
	var mk = function(){ return document.createElement.apply(document,arguments) };
	window.mk = function(){return mk.apply(window,arguments)};

	// $(document.createElement) shortcut
	$.mk = function(){ return $(document.createElement.apply(document,arguments)) };

	// Convert relative URL to absolute
	$.urlToAbsolute = function(url){
		var a = mk('a');
		a.href = url;
		return a.href;
	};

	// Globalize common elements
	window.$w = $(window);
	window.$d = $(document);
	window.CommonElements = function(){
		window.$header = $('header');
		window.$sbToggle = $('.sidebar-toggle');
		window.$main = $('#main');
		window.$content = $('#content');
		window.$sidebar = $('#sidebar');
		window.$footer = $('footer');
		window.$body = $('body');
		window.$head = $('head');
		window.$navbar = $header.find('nav');
	};
	window.CommonElements();

	// Common key codes for easy reference
	window.Key = {
		Enter: 13,
		Space: 32,
		LeftArrow: 37,
		RightArrow: 39,
		Tab: 9,
	};
	$.isKey = function(Key, e){
		return e.keyCode === Key;
	};

	// Make the first letter of the first or all word(s) uppercase
	$.capitalize = function(str, all){
		if (all) return str.replace(/((?:^|\s)[a-z])/g, function(match){
			return match.toUpperCase();
		});
		else return str.length === 1 ? str.toUpperCase() : str[0].toUpperCase()+str.substring(1);
	};

	// Array.includes (ES7) polyfill
	if (typeof Array.prototype.includes !== 'function')
		Array.prototype.includes = function(elem){ return this.indexOf(elem) !== -1 };

	$.pad = function(str, char, len, dir){
		if (typeof str !== 'string')
			str = ''+str;

		if (typeof char !== 'string')
			char = '0';
		if (typeof len !== 'number' || !isFinite(len) || isNaN(len))
			len = 2;
		if (typeof dir !== 'boolean')
			dir = true;

		while (str.length < len)
			str = dir ? char+str : str+char;

		return str;
	};

	// Create AJAX response handling function
	$w.on('ajaxerror',function(){
		var details = '';

		if (arguments.length > 1){
			var data = [].slice.call(arguments, 1);
			if (data[0] === 'abort')
				return;
			details = ' Details:<pre><code>' + data.join('\n').replace(/</g,'&lt;') + '</code></pre>';
		}
		$.Dialog.fail(false,'There was an error while processing your request.'+details+' You may find additional details in the browser\'s console.');
	});
	$.mkAjaxHandler = function(f){
		return function(data){
			if (typeof data !== 'object'){
				//noinspection SSBasedInspection
				console.log(data);
				$w.trigger('ajaxerror');
				return;
			}

			if (typeof f === 'function') f.call(data);
		};
	};

	// Checks if a variable is a function and if yes, runs it
	// If no, returns default value (undefined or value of def)
	$.callCallback = function(func, params, def){
		if (typeof params !== 'object' || !$.isArray(params)){
			def = params;
			params = [];
		}
		if (typeof func !== 'function')
			return def;

		return func.apply(window, params);
	};

	// Convert .serializeArray() result to object
	$.fn.mkData = function(obj){
		var tempdata = $(this).serializeArray(), data = {};
		$.each(tempdata,function(i,el){
			data[el.name] = el.value;
		});
		if (typeof obj === 'object')
			$.extend(data, obj);
		return data;
	};

	// Get CSRF token from cookies
	$.getCSRFToken = function(){
		var n = document.cookie.match(/CSRF_TOKEN=([a-z\d]+)/i);
		if (n && n.length)
			return n[1];
		else throw new Error('Missing CSRF_TOKEN');
	};
	$.ajaxPrefilter(function(event, origEvent){
		if ((origEvent.type||event.type).toUpperCase() !== 'POST')
			return;

		var t = $.getCSRFToken();
		if (typeof event.data === "undefined")
			event.data = "";
		if (typeof event.data === "string"){
			var r = event.data.length > 0 ? event.data.split("&") : [];
			r.push("CSRF_TOKEN=" + t);
			event.data = r.join("&");
		}
		else event.data.CSRF_TOKEN = t;
	});
	$.ajaxSetup({
		dataType: "json",
		error: function(){
			$w.triggerHandler('ajaxerror',[].slice.call(arguments, 1));
		},
		statusCode: {
			401: function(){
				$.Dialog.fail(undefined, "Cross-site Request Forgery attack detected. Please notify the site administartors.");
			},
			404: function(){
				$.Dialog.fail(undefined, "Error 404: The requested endpoint could not be found");
			},
			500: function(){
				$.Dialog.fail(false, 'The request failed due to an internal server error. If this persists, please open an issue on GitHub using the link in the footer!');
			}
		}
	});

	// Copy any text to clipboard
	// Must be called from within an event handler
	$.copy = function(text){
		if (!document.queryCommandSupported('copy')){
			prompt('Copy with Ctrl+C, close with Enter', text);
			return true;
		}

		var $helper = $.mk('textarea'),
			success = false;
		$helper
			.css({
				opacity: 0,
				width: 0,
				height: 0,
				position: 'fixed',
				left: '-10px',
				top: '50%',
				display: 'block',
			})
			.text(text)
			.appendTo('body')
			.focus();
		$helper.get(0).select();

		try {
			success = document.execCommand('copy');
		} catch(e){}

		if (!success)
			$.Dialog.fail('Copy to clipboard', 'Copying text to clipboard failed!');
		setTimeout(function(){
			$helper.remove();
		}, 1);
	};

	// Convert HEX to RGB
	$.hex2rgb = function(hexstr){
		return {
			r: parseInt(hexstr.substring(1, 3), 16),
			g: parseInt(hexstr.substring(3, 5), 16),
			b: parseInt(hexstr.substring(5, 7), 16)
		};
	};

	window.URL = function(url){
		var a = document.createElement('a'),
			me = {};
		a.href = url;
		$.each(['hash','host','hostname','href','origin','pathname','port','protocol','search'],function(_,el){
			me[el] = a[el];
		});
		me.pathString = me.pathname.replace(/^([^\/].*)$/,'/$1')+me.search+me.hash;
		return me;
	};
})(jQuery);

function DocumentIsReady(){
	'use strict';
	$d.triggerHandler('paginate-refresh');

	// Sign in button handler
	var OAUTH_URL = window.OAUTH_URL,
		consent = localStorage.getItem('cookie_consent');

	$('#signin').off('click').on('click',function(){
		var $this = $(this),
			opener = function(sure){
				if (!sure) return;

				localStorage.setItem('cookie_consent',1);
				$this.attr('disabled', true);
				$.Dialog.wait('Sign-in process', 'Redirecting you to DeviantArt', function(){
					window.location.href = OAUTH_URL;
				});
			};

		if (!consent) $.Dialog.confirm('EU Cookie Policy Notice','In compliance with the <a href="http://ec.europa.eu/ipg/basics/legal/cookies/index_en.htm">EU Cookie Policy</a> we must inform you that our website will store cookies on your device to remember your logged in status between browser sessions.<br><br>If you would like to avoid these completly harmless pieces of information required to use this website, click "Decline" and continue browsing as a guest.<br><br>This warning will not appear again if you accept our use of persistent cookies.',['Accept','Decline'],opener);
		else opener(true);
	});

	// Sign out button handler
	$('#signout').off('click').on('click',function(){
		var title = 'Sign out';
		$.Dialog.confirm(title,'Are you sure you want to sign out?',function(sure){
			if (!sure) return;

			$.Dialog.wait(title,'Signing out');

			$.post('/signout',$.mkAjaxHandler(function(){
				if (this.status){
					var msg = this.message;
					HandleNav(location.href, function(){
						$.Dialog.success(title, msg);
						setTimeout(function(){
							$.Dialog.close();
						}, 1500);
					});
				}
				else $.Dialog.fail(title,this.message);
			}));
		});
	});

	// Feedback form
	var $FeedbackForm;
	$('.send-feedback').off('click').on('click',function(e){
		e.preventDefault();
		e.stopPropagation();

		if (typeof $FeedbackForm === 'undefined'){
			$FeedbackForm = $.mk('form').attr('id','feedback-form').append(
				$.mk('p').text("Your opinion matters. If you're having an issue with the site, or just want to say how great it is, this is the place to do do."),
				$.mk('p').html("Your message will be sent directly to the developer, and you'll be able to communicate with him using the <a href='/feedback'>Feedback</a> section of the site."),
				$.mk('label').append(
					$.mk('span').text('Subject (5-120 chars.)'),
					$.mk('input').attr({
						name: 'subject',
						maxlength: 120,
						placeholder: 'Enter subject',
						pattern: PRINTABLE_ASCII_REGEX.replace('+','{5,120}'),
						required: true,
					})
				),
				$.mk('label').append(
					$.mk('span').text('Message (10-500 chars.)'),
					$.mk('textarea').attr({
						name: 'message',
						maxlength: 500,
						placeholder: 'Enter message',
						pattern: PRINTABLE_ASCII_REGEX.replace('+','{10,500}'),
						required: true,
					})
				)
			);
		}

		$.Dialog.request('Send feedback',$FeedbackForm.clone(true,true),'feedback-form','Send',function($form){
			$form.on('submit',function(e){
				e.preventDefault();

				var data = $form.mkData();

				if (data.subject.length < 5 || data.subject.length > 120)
					return $.Dialog.fail(false, 'The subject must be between 5 and 120 characters (you entered '+data.subject.length+').');
				if (data.message.length < 10 || data.message.length > 500)
					return $.Dialog.fail(false, 'Your message must be between 10 and 500 characters (you entered '+data.message.length+').');

				$.post('/feedback',data,$.mkAjaxHandler(function(){
					if (!this.status) return $.Dialog.fail(false, this.message);

					$.Dialog.success(false, this.message, true);
				}));
			});
		});
	});

	var l = window.DocReady.length;
	if (l) for (var i = 0; i<l; i++)
		window.DocReady[i].call(window);
}
function OpenSidebarByDefault(){
	'use strict';
	return window.matchMedia('all and (min-width: 1200px)').matches;
}
var DocReadyOnce = false;
$(function(){
	'use strict';
	if (DocReadyOnce) return;
	DocReadyOnce = true;

	// Sidebar toggle handler
	var xhr = false;
	$sbToggle.off('click').on('click',function(e){
		e.preventDefault();

		if (xhr !== false) return;
		$sbToggle.trigger('sb-'+($body.hasClass('sidebar-open')?'close':'open'));
	}).on('sb-open sb-close',function(e){
		var close = e.type.substring(3) === 'close';
		$body[close ? 'removeClass' : 'addClass']('sidebar-open');
		localStorage[close ? 'setItem' : 'removeItem']('sidebar-closed', 'true');
	});
	if (localStorage.getItem('sidebar-closed') !== 'true' && OpenSidebarByDefault())
		$body.addClass('sidebar-open');

	// Upcoming Episode Countdown
	var $cd, cdtimer,
		months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	window.setCD = function(){
		var $uc = $('#upcoming');
		if (!$uc.length)
			return;

		var $lis = $uc.children('ul').children();
		if (!$lis.length)
			return $uc.remove();

		$cd = $lis.first().find('time').addClass('nodt');
		cdtimer = setInterval(cdupdate, 1000);
		cdupdate();

		$uc.find('li').each(function(){
			var $this = $(this),
				$calendar = $this.children('.calendar'),
				d = new Date($this.find('.countdown').data('airs') || $this.find('time').attr('datetime'));
			$calendar.children('.top').text(months[d.getMonth()]);
			$calendar.children('.bottom').text(d.getDate());
		});
		window.updateTimes();
	};
	window.setCD();
	function pad(n){return n<10?'0'+n:n}
	function cdupdate(){
		var cdExists = typeof $cd.parent === "function" && $cd.parent().length > 0,
			diff = {}, now, airs;
		if (cdExists){
			now = new Date();
			airs = new Date($cd.attr('datetime'));
			diff = getTimeDiff(now, airs);
		}
		if (!cdExists || diff.past){
			if (cdExists)
				$cd.parents('li').remove();
			clearInterval(cdtimer);
			return window.setCD();
		}
		var text = 'in ';
		if (diff.time < one.month){
			if (diff.week > 0)
				diff.day += diff.week * 7;
			if (diff.day > 0)
				text += diff.day+' day'+(diff.day!==1?'s':'')+' & ';
			if (diff.hour > 0)
				text += diff.hour+':';
			text += pad(diff.minute)+':'+pad(diff.second);
		}
		else text = createTimeStr(now, airs);
		$cd.text(text);
	}
	$w.on('unload',function(){
		$cd = {length:0};
	});

	// AJAX page loader
	var REWRITE_REGEX = window.REWRITE_REGEX,
		SITE_TITLE = window.SITE_TITLE;

	function LinkClick(e){
		if (e.which > 2) return true;

		var link = this;
		if (
			link.hostname !== location.hostname || (
				!REWRITE_REGEX.test(link.pathname) &&
				!/^\/@([A-Za-z\-\d]{1,20})$/.test(link.pathname)
			)
		) return true;

		if (link.pathname === location.pathname && link.search === location.search)
			return true;

		if ($(this).parents('#dialogContent').length !== 0)
			$.Dialog.close();

		e.preventDefault();
		Navigation(this.href);
	}
	$d.off('click','a[href]',LinkClick).on('click','a[href]',LinkClick);

	$w.off('popstate').on('popstate',function(e){
		var state = e.originalEvent.state;

		if (state !== null && !state['via-js'])
			return $w.trigger('nav-popstate', [state]);
		Navigation(location.href, undefined, true);
	});

	function Navigation(url, callback, block_reload){
		if (xhr !== false){
			xhr.abort();
			xhr = false;
		}

		var title = 'Navigation';
		$body.addClass('loading');
		var ajaxcall = $.ajax({
			url: url,
			data: {'via-js': true},
			success: $.mkAjaxHandler(function(){
				if (xhr !== ajaxcall) return;
				if (!this.status){
					$body.removeClass('loading');
					xhr = false;
					return $.Dialog.fail(title+' error', this.message);
				}

				url = new URL(this.responseURL+(new URL(url).hash)).pathString;
				$w.triggerHandler('unload');
				if (!OpenSidebarByDefault())
					$sbToggle.trigger('sb-close');

				var css = this.css,
					js = this.js,
					content = this.content,
					sidebar = this.sidebar,
					footer = this.footer,
					pagetitle = this.title,
					avatar = this.avatar;

				$main.empty();
				var doreload = false,
					ParsedLocation = new URL(location.href),
					reload = !block_reload && ParsedLocation.pathString === url;

				$body.children('script[src], script[data-src]').each(function(){
					var $this = $(this),
						src = $this.attr('src') || $this.attr('data-src');
					if (reload){
						if (!/js\/dialog\./.test(src))
							$this.remove();
						return true;
					}

					var pos = js.indexOf(src);

					if (pos !== -1 && !/js\/colorguide[\.\-]/.test(src))
						js.splice(pos, 1);
					else {
						if (src.indexOf('global') !== -1)
							return !(doreload = true);
						$this.remove();
					}
				});
				if (doreload !== false){
					location.href = url;
					return location.href;
				}

				$head.children('link[href], style[href]').each(function(){
					var $this = $(this),
						href = $this.attr('href'),
						pos = css.indexOf(href);

					if (pos !== -1)
						css.splice(pos, 1);
					else $this.remove();
				});

				console.clear();

				(function LoadCSS(item){
					if (item >= css.length){
						$main.addClass('pls-wait').html(content);
						$sidebar.html(sidebar);
						$footer.html(footer);
						window.updateTimes();
						window.setCD();
						var $headerNav = $header.find('nav').children();
						$headerNav.children().first().children('img').attr('src', avatar);
						$headerNav.children(':not(:first-child)').remove();
						$headerNav.append($sidebar.find('nav').children().children().clone());
						document.title = (pagetitle?pagetitle+' - ':'')+SITE_TITLE;

						window.CommonElements();
						history[ParsedLocation.pathString === url?'replaceState':'pushState']({'via-js':true},'',url);

						window.DocReady = [];

						return (function LoadJS(item){
							if (item >= js.length){
								DocumentIsReady();
								$body.removeClass('loading');
								$main.removeClass('pls-wait');

								$.callCallback(callback);
								//noinspection JSUnusedAssignment
								xhr = false;
								return;
							}

							var requrl = js[item];
							xhr = $.ajax({
								url: requrl,
								dataType: 'text',
								success:function(data){
									$body.append($.mk('script').attr('data-src', requrl).text(data));
									LoadJS(item+1);
								}
							});
						})(0);
					}

					var requrl = css[item];
					xhr = $.ajax({
						url: requrl,
						dataType: 'text',
						success: function(data){
							$head.append($.mk('style').attr('href',requrl).text(data));
							LoadCSS(item+1);
						}
					});
				})(0);
			})
		});
		xhr = ajaxcall;
	}
	window.HandleNav = function(){ Navigation.apply(window, arguments) };
	var Reload = function(callback, delay){
		var f = (typeof delay === 'number' && delay > 0)
			? function(){ setTimeout(callback, delay) }
			: callback;
		Navigation(location.pathname, f);
	};
	window.HandleNav.reload = function(){ Reload.apply(window, arguments) };

	DocumentIsReady();
});

// Remove loading animation from header on load
$w.on('load',function(){
	'use strict';
	$body.removeClass('loading');
});
