/* global DocReady,$w */
DocReady.push(function(){
	'use strict';

	function fulfuillPromises(){
		$('.deviation-promise:not(.loading)').each(function(){
			const $this = $(this);
			if (!$this.isInViewport())
				return;

			const favme = $this.attr('data-favme');

			$this.addClass('loading');

			$.get('/deviation/'+favme,$.mkAjaxHandler(function(){
				if (!this.status) return $.Dialog.fail('Cannot load deviation '+favme, this.message);

				$this.replaceWith(this.html);
			}));
		});
	}

	$('#contribs').on('page-switch',function(){
		fulfuillPromises();
	});
	window._contribScroll = $.throttle(400,function(){
		fulfuillPromises();
	});
	$w.on('mousewheel scroll',window._contribScroll);
	fulfuillPromises();
},function(){
	'use strict';
	$w.off('mousewheel scroll',window._contribScroll);
	delete window._contribScroll;
});
