(function ($, undefined) {
	function $makeDiv(id){ return $.mk('div').attr('id', id) }
	var colors = {
			fail: 'red',
			success: 'green',
			wait: 'blue',
			request: 'yellow',
			confirm: 'orange',
			info: 'darkblue'
		},
		defaultTitles = {
			fail: 'Error',
			success: 'Success',
			wait: 'Sending request',
			request: 'Input required',
			confirm: 'Confirmation',
			info: 'Info',
		},
		defaultContent = {
			fail: 'There was an issue while processing the request.',
			success: 'Whatever you just did, it was completed successfully.',
			request: 'The request did not require any additional info.',
			confirm: 'Are you sure?',
			info: 'No message provided.',
		},
		$dialogOverlay = $('#dialogOverlay'),
		$dialogContent = $('#dialogContent'),
		$dialogHeader = $('#dialogHeader'),
		$dialogBox = $('#dialogBox'),
		$dialogButtons = $('#dialogButtons');
	
	$.Dialog = {
		open: $dialogContent.length,
		fail: function(title,content,callback){
			$.Dialog.display('fail',title,content,{
				'Close': {
					'action': function(){
						$.Dialog.close();
					}
				}
			},callback);
		},
		success: function(title,content,closeBtn,callback){
			$.Dialog.display('success',title,content,(closeBtn === true ? {
				'Close': {
					'action': function(){
						$.Dialog.close();
					}
				}
			} : undefined), callback);
		},
		wait: function(title,additional_info,callback){
			if (typeof additional_info === 'function' && callback === 'undefined'){
				callback = additional_info;
			}
			if (typeof additional_info !== 'string' || additional_info.length < 2) additional_info = 'Sending request';
			$.Dialog.display('wait',title,additional_info[0].toUpperCase()+additional_info.substring(1)+'&hellip;',callback);
		},
		request: function(title,content,formid,caption,callback){
			if (typeof caption === 'function' && typeof callback === 'undefined'){
				callback = caption;
				caption = 'Elküld';
			}
			var obj = {};
			obj[caption] = {
				'submit': true,
				'form': formid,
			};
			obj['Cancel'] = {
				'action': function(){
					$.Dialog.close();
				},
			};

			$.Dialog.display('request',title,content,obj, callback);
		},
		confirm: function(title,content,btnTextArray,handlerFunc){
			if (typeof btnTextArray === 'function' && typeof handlerFunc === 'undefined')
				handlerFunc = btnTextArray;
			
			if (typeof handlerFunc !== 'function') handlerFunc = new Function();
			
			if (!$.isArray(btnTextArray)) btnTextArray = ['Eeyup','Nope'];
			var buttonsObj = {};
			buttonsObj[btnTextArray[0]] = {'action': function(){ handlerFunc(true) }};
			buttonsObj[btnTextArray[1]] = {'action': function(){ handlerFunc(false); $.Dialog.close() }};
			$.Dialog.display('confirm',title,content,buttonsObj);
		},
		info: function(title,content,callback){
			$.Dialog.display('info',title,content,{
				'Close': {
					'action': function(){
						$.Dialog.close();
					}
				}
			},callback);
		},
		_storeFocus: function(){
			if (typeof $.Dialog._focusedElement !== 'undefined' && $.Dialog._focusedElement instanceof jQuery)
				return;
			var $focus = $(':focus');
			if ($focus.length > 0) $.Dialog._focusedElement = $focus.last();
			else $.Dialog._focusedElement = undefined;
		},
		_restoreFocus: function(){
			if (typeof $.Dialog._focusedElement !== 'undefined' && $.Dialog._focusedElement instanceof jQuery)
				$.Dialog._focusedElement.focus();
		},
		_setFocus: function(){
			var $inputs = $('#dialogContent').find('input,select,textarea').filter(':visible'),
				$actions = $('#dialogButtons').children();
			if ($inputs.length > 0) $inputs.first().focus();
			else if ($actions.length > 0) $actions.first().focus();
		},
		display: function (type,title,content,buttons,params,callback) {
			if (typeof type !== 'string' || typeof colors[type] === 'undefined') throw new TypeError('Invalid dialog type: '+typeof type);

			function run(append){
				var $contentAdd = $makeDiv().addClass(params.color);
				if (append){
					$dialogOverlay = $('#dialogOverlay');
					$dialogBox = $('#dialogBox');
					$dialogHeader = $('#dialogHeader');
					if (typeof params.title === 'string')
						$dialogHeader.text(params.title);
					$dialogContent = $('#dialogContent');
					$dialogButtons = $('#dialogButtons');
					$dialogContent.children(':not(:last-child)').find('input, select, textarea').attr('disabled',true);
					$dialogContent.append($contentAdd.html(params.content));

					if (params.buttons){
						if ($dialogButtons.length === 0)
							$dialogButtons = $makeDiv('dialogButtons');
						$dialogButtons.appendTo($dialogContent);
					}
					$dialogButtons.empty();
				}
				else {
					$.Dialog._storeFocus();

					$dialogOverlay = $makeDiv('dialogOverlay');
					$dialogHeader = $makeDiv('dialogHeader').text(params.title||defaultTitles[type]);
					$dialogContent = $makeDiv('dialogContent');
					$dialogBox = $makeDiv('dialogBox');

					$dialogContent.append($contentAdd.html(params.content));
					$dialogButtons = $makeDiv('dialogButtons').appendTo($dialogContent);
					$dialogBox.append($dialogHeader).append($dialogContent);
					$dialogOverlay.append($dialogBox).appendTo($body);

					$body.addClass('dialog-open');
				}
				
				$dialogHeader.attr('class',params.color+'-bg');

				var $form = false;
				if (params.buttons) $.each(params.buttons, function (name, obj) {
					var $button = $.mk('input').attr('type','button');
					$button.attr('class',params.color+'-bg');
					if (obj.form){
						$form = $('#'+obj.form);
						if ($form.length == 1){
							$button.on('click', function(){
								$form.find('input[type=submit]').trigger('click');
							});
							$form.prepend($.mk('input').attr('type','submit').hide());
						}
					}
					$button.val(name).on('keydown', function (e) {
						if ([13, 32].indexOf(e.keyCode) !== -1){
							e.preventDefault();
							
							$button.trigger('click');
						}
						else if ([9, 37, 39].indexOf(e.keyCode) !== -1){
							e.preventDefault();
							
							var $dBc = $dialogButtons.children(),
								$focused = $dBc.filter(':focus'),
								$inputs = $dialogContent.find(':input');

							if (e.keyCode === 37) e.shiftKey = true;
								
							if ($focused.length){
								if (!e.shiftKey){
									if ($focused.next().length) $focused.next().focus();
									else if (e.keyCode === 9) $inputs.add($dBc).filter(':visible').first().focus();
								}
								else {
									if ($focused.prev().length) $focused.prev().focus();
									else if (e.keyCode === 9) ($inputs.length > 0 ? $inputs : $dBc).filter(':visible').last().focus();
								}
							}
							else $inputs.add($dBc)[!e.shiftKey ? 'first' : 'last']().focus();
						}
					}).on('click', function (e) {
						e.preventDefault();
						
						if (typeof obj.action === 'function') obj.action(e);

						if (obj.type === 'close') $.Dialog.close(typeof obj.callback === 'function' ? obj.callback : undefined);
					});
					$dialogButtons.append($button);
				});

				$.Dialog.center();
				$.Dialog._setFocus();

				if (typeof callback === 'function') callback($form);
			}
			
			if (typeof buttons == "function" && typeof params == "undefined" && typeof callback == 'undefined')
				callback = buttons;
			else if (typeof buttons == "object" && typeof params == "function" && typeof callback == 'undefined')
				callback = params;
			if (typeof params == "undefined") params = {};
			
			if (typeof title === 'undefined') title = defaultTitles[type];
			else if (title === false) title = undefined;
			if (typeof content === 'undefined') content = defaultContent[type];
			params = {
				type: type,
				title: title,
				content: content,
				buttons: buttons,
				color: colors[type]
			};

			if ($.Dialog.open)
				run(true);
			else {
				$.Dialog.open = params;
				run();
			}
		},
		close: function (callback) {
			if (typeof $.Dialog.open === "undefined") return typeof callback == 'function' ? callback() : false;

			$('#dialogOverlay').remove();
			$.Dialog.open = void(0);
			$.Dialog._restoreFocus();
			if (typeof callback == 'function') callback();

			$body.removeClass('dialog-open');
		},
		center: function(){
			if (typeof $.Dialog.open === 'undefined') return;

			var overlay = {w: $dialogOverlay.width(), h: $dialogOverlay.height()},
				dialog = {w: $dialogBox.outerWidth(true), h: $dialogBox.outerHeight(true)};
			$dialogBox.css("top", Math.max((overlay.h - dialog.h) / 2, 0));
			$dialogBox.css("left", Math.max((overlay.w - dialog.w) / 2, 0));
		}
	};

	$w.on('resize', $.Dialog.center);
	$body.on('keydown',function(e){
		if (e.keyCode === 9 && typeof $.Dialog.open !== 'undefined'){
			var $this = $(e.target),
				$inputs = $('#dialogContent').find(':input'),
				idx = $this.index('#dialogContent :input');

			if (e.shiftKey && idx === 0){
				e.preventDefault();
				$('#dialogButtons').find(':last').focus();
			}
			else if ($inputs.filter(':focus').length !== 1){
				e.preventDefault();
				$inputs.first().focus();
			}
		}
	});
})(jQuery);
