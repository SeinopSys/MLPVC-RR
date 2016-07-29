/* global DocReady,$content,SHORT_HEX_COLOR_PATTERN */
DocReady.push(function ColorguideSpriteedit(){
	'use strict';

	let AppearanceColors = window.AppearanceColors,
		SpriteColorList = window.SpriteColorList,
		$Table = $.mk('table').appendTo($('#input-cont').empty()),
		$SVG = $('#svg-cont').children(),
		AppearanceColorObject = {},
		AppearanceColorIterator = 1,
		mapColor = function(key, val){
			let result = [];
			for (let i = 0,l = AppearanceColors.length; i<l; i++){
				let el = AppearanceColors[i];
				if (el[key] === val)
					result.push(el);
			}
			if (!result.length)
				result.push({
					label: '(unrecognized color)',
					hex: '',
				});
			return result;
		};

	$.each(AppearanceColors, (_, color) => {
		AppearanceColorObject[color.label] = AppearanceColorIterator++;
	});

	$SVG.children().each(function(){
		let $rect = $(this);
		$rect.addClass($.yiq($rect.attr('stroke')) > (0xFF/2) ? 'bright' : 'dark');
	});

	$.each(SpriteColorList, function(index, actual){
		let matchingColors = mapColor('hex', actual), labels = [];
		$.each(matchingColors, (_, color) => {
			labels.push(`<li>${color.label}</li>`);
		});
		$Table.append(
			$.mk('tr').html(
				`<td class="color-preview" style="background-color:${actual}"></td>
				<td class="label"><ul>${labels.join('')}</ul></td>
				<td class="color">${actual}</td>`
			).on('mouseenter',function(){
				$SVG.children().filter(`[stroke="${actual}"]`).addClass('highlight');
			}).on('mouseleave',function(){
				$SVG.find('.highlight').removeClass('highlight');
			})
		);
	});
	$Table.children('tr').sort(function(a,b){
		let at = AppearanceColorObject[$(a).children('td.label li').first().text()] || -1,
			bt = AppearanceColorObject[$(b).children('td.label li').first().text()] || -1;

		return at === bt ? 0 : (at < bt ? -1 : 1);
	}).prependTo($Table);
});
