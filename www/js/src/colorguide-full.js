/* global DocReady,Sortable */
DocReady.push(function ColorguideFull(){
	'use strict';
	let $sortBy = $('#sort-by'),
		$fullList = $('#full-list'),
		$ReorderBtn = $('#guide-reorder'),
		$ReorderCancelBtn = $('#guide-reorder-cancel');
	$sortBy.on('change',function(){
		let baseurl = $sortBy.data('baseurl'),
			val = $sortBy.val(),
			url = `${baseurl}?ajax&${val}`.replace(/&$/,''),
			stateUrl = `${baseurl}?${val}`.replace(/\?$/,'');

		$.Dialog.wait('Changing sort order');

		$.get(url, $.mkAjaxHandler(function(){
			if (!this.status) return $.Dialog.fail(false, this.message);

			$fullList.html(this.html);
			$ReorderBtn.attr('disabled', Boolean(val.length));
			history.replaceState(history.state,'',stateUrl);
			$.Dialog.close();
		}));
	});

	if (typeof window.Sortable !== 'function')
		return;

	$fullList.on('click','.sort-alpha',function(){
		var $section = $(this).closest('section'),
			$ul = $section.children('ul');
		$ul.children().sort(function(a,b){
			return $(a).text().trim().localeCompare($(b).text().trim());
		}).appendTo($ul);
	});

	$ReorderBtn.on('click',function(){
		if (!$ReorderBtn.hasClass('typcn-tick')){
			$ReorderBtn.removeClass('typcn-arrow-unsorted darkblue').addClass('typcn-tick green').html('Save');
			$fullList.addClass('sorting').children().each(function(){
				let $names = $(this).children('ul');
				$names.children().each(function(){
					let $li = $(this);
					$li.data('orig-index', $li.index());
				}).children().moveAttr('href','data-href');
				$names.data('sortable-instance', new Sortable($names.get(0), {
				    ghostClass: "moving",
				    animation: 300,
				}));
			});
			$('.sort-alpha').show();
			$ReorderCancelBtn.removeClass('hidden');
		}
		else {
			$.Dialog.wait('Re-ordering appearances');

			let list = [];
			$fullList.children().children('ul').children().each(function(){
				list.push($(this).children().attr('data-href').split('/').pop().replace(/^(\d+)\D.*$/,'$1'));
			});

			$.post('/cg/full?reorder', {list:list.join(',')}, $.mkAjaxHandler(function(){
				if (!this.status) return $.Dialog.fail(false, this.message);

				$fullList.removeClass('sorting').html(this.html);
				$ReorderBtn.removeClass('typcn-tick green').addClass('typcn-arrow-unsorted darkblue').html('Re-order');
				$.Dialog.close();
			}));
		}
	});

	$ReorderCancelBtn.on('click',function(){
		$ReorderBtn.removeClass('typcn-tick green').addClass('typcn-arrow-unsorted darkblue').html('Re-order');
		$fullList.removeClass('sorting').children().each(function(){
			let $names = $(this).children('ul');
			$names.children().sort(function(a, b){
				a = $(a).data('orig-index');
				b = $(b).data('orig-index');
				return a > b ? 1 : (a < b ? -1 : 0);
			}).appendTo($names).removeData('orig-index').children().moveAttr('data-href', 'href');
			$names.data('sortable-instance').destroy();
			$names.removeData('sortable-instance');
		});
		$('.sort-alpha').hide();
		$ReorderCancelBtn.addClass('hidden');
	});
});
