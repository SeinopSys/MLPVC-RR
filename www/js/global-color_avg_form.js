/* global $ColorAvgForm */
// Color Average form
window.$ColorAvgForm = (function($){
	'use strict';

	return $.mk('form').attr('id','color-avg-form').on('added',function(){
		var $form = $(this).on('submit',function(e){
				e.preventDefault();

				$.Dialog.close();
			}),
			defaultInputCount = 10,
			calcAvg = function(){
				var count = 0,
					rAvg = 0,
					gAvg = 0,
					bAvg = 0;

				$form.find('.input-group-3').each(function(){
					var $allInputs = $(this).children(),
						r = $allInputs.eq(0).val(),
						g = $allInputs.eq(1).val(),
						b = $allInputs.eq(2).val();

					if (r.length && g.length && b.length){
						var row = {
							r: parseInt(r, 10),
							g: parseInt(g, 10),
							b: parseInt(b, 10),
						};


						if (
							!isNaN(row.r) && row.r >= 0 && row.r <= 255 &&
							!isNaN(row.g) && row.g >= 0 && row.g <= 255 &&
							!isNaN(row.b) && row.b >= 0 && row.b <= 255
						){
							count++;
							rAvg += parseInt(row.r, 10);
							gAvg += parseInt(row.g, 10);
							bAvg += parseInt(row.b, 10);
						}
					}
					else $allInputs.attr('required', (r.length + g.length + b.length) > 0);
				});

				if (count){
					rAvg = Math.round(rAvg / count);
					gAvg = Math.round(gAvg / count);
					bAvg = Math.round(bAvg / count);
				}

				$AvgRedTD.text(rAvg);
				$AvgGreenTD.text(gAvg);
				$AvgBlueTD.text(bAvg);

				var hex = $.rgb2hex({ r:rAvg, g:gAvg, b:bAvg });
				$AvgColorPreview.css('background-color',hex);
				$AvgHexTD.text(hex);
			},
			_$baseInput = $("<input type='text' pattern='^([1-9]?\\d|1\\d{2}|2[0-4]\\d|25[0-5])$' maxlength='3'  class='align-center'>"),
			$ColorAvgInputRow = $.mk('div').attr('class','input-group-3').append(
				_$baseInput.clone().attr('placeholder','Red'),
				_$baseInput.clone().attr('placeholder','Green'),
				_$baseInput.clone().attr('placeholder','Blue'),
				$("<input type='text' pattern='^#?([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$' maxlength='7' placeholder='HEX' class='align-center color-ui' spellcheck='false'>").on('change blur',function(e){
					e.stopPropagation();

					var $el = $(this);
					if (!$el.is(':valid') || $el.val().trim().length === 0)
						return;

					var $sib = $el.siblings(),
						hexstr = $.hexpand($el.val().toUpperCase()),
						rgb = $.hex2rgb(hexstr);

					$el.val(hexstr);
					$sib.eq(0).val(rgb.r);
					$sib.eq(1).val(rgb.g);
					$sib.eq(2).val(rgb.b).triggerHandler('change');
				})
			),
			$inputsDiv = $.mk('div').attr('class','inputs'),
			resetInputs = function(){
				$inputsDiv.empty();
				for (var i=0; i < defaultInputCount; i++)
					$inputsDiv.append($ColorAvgInputRow.clone(true,true));

				calcAvg();
			},
			$AvgColorPreview = $.mk('span').css({
				position: 'absolute',
				top: 0,
				left: 0,
				width: '100%',
				height: '100%',
				display: 'block',
			}).html('&nbsp;'),
			$AvgColorPreviewTD = $.mk('td').attr('rowspan','2').css({
				width: '25%',
				position: 'relative',
			}).append($AvgColorPreview),
			$AvgRedTD =  $.mk('td').attr('class','color-red'),
			$AvgGreenTD =  $.mk('td').attr('class','color-green'),
			$AvgBlueTD =  $.mk('td').attr('class','color-darkblue'),
			$AvgHexTD =  $.mk('td').attr('colspan','3');

		$ColorAvgInputRow.children().on('paste',function(){
			var $this = $(this);
			setTimeout(function(){
				if (!$this.is(':valid'))
					return;

				$this.val($this.val().trim()).triggerHandler('change');
				var $next = $this.index() < 2 ? $this.next() : $this.parent().next().children().first();

				if ($next.length)
					$next.focus();
			},1);
		}).on('change keyup blur',calcAvg);

		$form.append(
			$inputsDiv,
			$.mk('div').attr('class', 'btn-group').append(
				$.mk('button').attr('class','green typcn typcn-plus').text('Add row').on('click',function(e){
					e.preventDefault();

					$inputsDiv.append($ColorAvgInputRow.clone(true,true));
					//calcAvg();
				}),
				$.mk('button').attr('class','orange typcn typcn-times').text('Reset form').on('click',function(e){
					e.preventDefault();

					resetInputs();
				})
			),
			$.mk('table').attr({
				'class':'align-center',
				style: 'display:table;width:100%;font-family:"Source Code Pro","Consolas",monospace;font-size:1.3em;border-collapse:collapse'
			}).append(
				$.mk('tr').append(
					$AvgColorPreviewTD,
					$AvgRedTD,
					$AvgGreenTD,
					$AvgBlueTD
				),
				$.mk('tr').append($AvgHexTD)
			).find('td').css('border','1px solid black').end()
		);

		resetInputs();
	});
})(jQuery);
