/* globals $w,$d,$content,DocReady,HandleNav */
DocReady.push(function Colorguide(){
	'use strict';
	//noinspection JSUnusedLocalSymbols
	var Color = window.Color, color = window.color, $list = $('.appearance-list'), EQG = window.EQG, AppearancePage = !!window.AppearancePage;

	var copyHash = !localStorage.getItem('leavehash'), $toggler;
	function copyHashToggler(){
		$toggler = $('#toggle-copy-hash');
		if (!$toggler.length)
			return;
		$toggler.off('display-update').on('display-update',function(){
			copyHash = !localStorage.getItem('leavehash');
			$toggler
				.attr('class','blue typcn typcn-'+(copyHash ? 'tick' : 'times'))
				.text('Copy # with '+color+' codes: '+(copyHash ? 'En':'Dis')+'abled');
		}).trigger('display-update').off('click').on('click',function(e){
			e.preventDefault();

			if (copyHash) localStorage.setItem('leavehash', 1);
			else localStorage.removeItem('leavehash');

			$toggler.triggerHandler('display-update');
		});
	}
	window.copyHashToggler = function(){copyHashToggler()};
	window.copyHashEnabled = function(){ return copyHash };

	var $SearchForm = $('#search-form');

	function tooltips(){
		var isGuest = false,
			$tags = $('.tags').children('span.tag');
		$tags.each(function(){
			var $this = $(this),
				text = 'Click to quick search',
				title = $this.attr('title'),
				tagstyle = $this.attr('class').match(/typ\-([a-z]+)(?:\s|$)/);

			tagstyle = !tagstyle ? '' : ' qtip-tag-'+tagstyle[1];

			if (!title){
				var titletext = $this.text().trim();
				title = /^s\d+e\d+(-\d+)?$/i.test(titletext)
					? titletext.toUpperCase()
					: $.capitalize($this.text().trim(), true);
			}

			if (title){
				$this.qtip({
					content: {
						text: text,
						title: title
					},
					position: {my: 'bottom center', at: 'top center', viewport: true},
					style: {classes: 'qtip-tag' + tagstyle}
				});
			}
		});
		$tags.css('cursor','pointer').off('click').on('click',function(e){
			e.preventDefault();

			var query = this.innerHTML.trim();
			if ($SearchForm.length){
				$SearchForm.find('input[name="q"]').val(query);
				$SearchForm.triggerHandler('submit');
			}
			else $.Navigation.visit('/cg'+(EQG?'/eqg':'')+'/1?q='+query.replace(/ /g,'+'));
		});
		$('ul.colors').children('li').find('.valid-color').each(function(){
			var $this = $(this),
				text = 'Click to copy HEX '+color+' code to clipboard<br>Shift+Click to view RGB values',
				title = $this.attr('title');

			if ($this.is(':empty'))
				text = 'No color to copy';

			$this.qtip({
				content: {
					text: text,
					title: title
				},
				position: { my: 'bottom center', at: 'top center', viewport: true },
				style: { classes: 'qtip-see-thru' }
			});
		}).off('click mousedown').on('click',function(e){
			e.preventDefault();
			var $this = $(this),
				copy = $this.html().trim();
			if (e.shiftKey){
				var rgb = $.hex2rgb(copy),
					$cg = $this.closest('li'),
					path = [
						(
							!AppearancePage
							? $cg.parents('li').children().last().children('strong').text().trim()
							: $content.children('h1').text()
						),
						$cg.children().first().text().replace(/:\s+$/,''),
						$this.attr('oldtitle'),
					];
				return $.Dialog.info('RGB values for color ' + copy, '<div class="align-center">'+path.join(' &rsaquo; ')+'<br><span style="font-size:1.2em">rgb(<code class="color-red">'+rgb.r+'</code>, <code class="color-green">'+rgb.g+'</code>, <code class="color-darkblue">'+rgb.b+'</code>)</span></div>');
			}
			if (!copyHash) copy = copy.replace('#','');
			$.copy(copy);
		}).filter(':not(.ctxmenu-bound)').ctxmenu(
			[
				{text: "Copy HEX "+color+" code", icon: 'clipboard', 'default': true, click: function(){
					$(this).triggerHandler('click');
				}},
				{text: "View RGB values", icon: 'brush', click: function(){
					$(this).triggerHandler({
						type: 'click',
						shiftKey: true,
					});
				}},
			],
			function($el){ return 'Color: '+$el.attr('oldtitle') }
		).on('mousedown', function(e){
		    if (e.shiftKey)
		        e.preventDefault();
		});
		$('.cm-direction:not(.tipped)').each(function(){
			var $this = $(this),
				ponyID = $this.closest('li').attr('id').substring(1),
				base = new Image(),
				cm = new Image(),
				base_img = '/cg/v/'+ponyID+'.svg?t='+(parseInt(new Date().getTime()/1000)),
				cm_img = $this.attr('data-cm-preview');
			setTimeout(function(){
				base.src = base_img;
				cm.src = cm_img;
			}, 1);
			$this.addClass('tipped').qtip({
				content: {
					text: $.mk('span').attr('class', 'cm-dir-image').backgroundImageUrl(base_img).append(
						$.mk('div').attr('class', 'img cm-dir-'+$this.attr('data-cm-dir')).backgroundImageUrl(cm_img)
					)
				},
				position: { my: 'bottom center', at: 'top center', viewport: true },
				style: { classes: 'qtip-link' }
			});
		});
	}
	window.tooltips = function(){tooltips()};

	function Navigation(){
		$list = $('.appearance-list');
		tooltips();
		copyHashToggler();
	}
	$list.filter('#list').on('page-switch', Navigation);
	$d.on('paginate-refresh', Navigation);
	Navigation();

	$SearchForm.on('submit',function(e){
		e.preventDefault();

		var $this = $(this),
			$query = $this.find('input[name=q]'),
			orig_query = $query.val(),
			query = orig_query.trim().length === 0 ? false : $this.serialize();
		$this.find('button[type=reset]').attr('disabled', query === false);

		if (query !== false)
			$.Dialog.wait('Navigation', 'Searching for <code>'+orig_query.replace(/</g,'&lt;')+'</code>');
		else $.Dialog.success('Navigation', 'Search terms cleared');

		$.toPage.call({query:query}, window.location.pathname.replace(/\d+$/,'1'), true, true, false, function(){
			if (query !== false)
				return /^Page \d+/.test(document.title)
					? orig_query+' - '+document.title
					: document.title.replace(/^.*( - Page \d+)/, orig_query+'$1');
			else return document.title.replace(/^.* - (Page \d+)/, '$1');
		});
	}).on('reset',function(e){
		e.preventDefault();

		var $this = $(this);
		$this.find('input[name=q]').val('');
		$this.triggerHandler('submit');
	});
}, function(){
	'use strict';
	$('.qtip').each(function(){
		var $this = $(this);
		$this.data('qtip').destroy();
		$this.remove();
	});
});
