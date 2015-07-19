$(function(){
	$('ul.colors').find('span').qtip({
		position: { my: 'bottom center', at: 'top center', viewport: true },
		style: { classes: 'qtip-see-thru' }
	});
	$('.tags').children('[title]').each(function(){
		console.log(this);
		$(this).qtip({
			position: { my: 'bottom center', at: 'top center', viewport: true },
			style: { classes: 'qtip-tag qtip-tag-'+this.className }
		});
	});
});