/* global DocReady,$content,$body,$w,$footer,$header,$navbar,moment,Chart,Time,ace,IntersectionObserver */
$(function(){
	'use strict';

	let SEASON = window.SEASON,
		EPISODE = window.EPISODE,
		EpID = 'S'+SEASON+'E'+EPISODE;

	window._HighlightHash = function (e){
		$('.highlight').removeClass('highlight');

		let $anchor = $(location.hash);
		if (!$anchor.length)
			return false;
		$anchor.addClass('highlight');

		setTimeout(function(){
			$.scrollTo($anchor.offset().top - $navbar.outerHeight() - 10, 500, function(){
				if (typeof e === 'object' && e.type === 'load')
					$.Dialog.close();
			});
		}, 1);
	};
	$w.on('hashchange', window._HighlightHash);

	let $voting = $('#voting');
	$voting.on('click','.rate', function(e){
		e.preventDefault();

		let makeStar = function(v){
				return $.mk('label').append(
					$.mk('input').attr({
						type: 'radio',
						name: 'vote',
						value: v,
					}),
					$.mk('span')
				).on('mouseenter mouseleave', function(e){
					let $this = $(this),
						$checked = $this.parent().find('input:checked'),
						$parent = $checked.parent(),
						$strongRating = $this.closest('div').next().children('strong');

					switch (e.type){
						case "mouseleave":
							if ($parent.length === 0){
								$this.siblings().addBack().find('.typcn').attr('class', '');
								$strongRating.text('?');
								break;
							}
							$this = $parent;
						/* falls through */
						case "mouseenter":
							$this.prevAll().addBack().children('span').attr('class','active');
							$this.nextAll().children('span').attr('class','');
							$strongRating.text($this.children('input').attr('value'));
						break;
					}

					$this.siblings().addBack().removeClass('selected');
					$parent.addClass('selected');
				});
			},
			$VoteForm = $.mk('form').attr('id','star-rating').append(
				$.mk('p').text("Rate the episode on a scale of 1 to 5. This cannot be changed later."),
				$.mk('div').attr('class','rate').append(
					makeStar(1),
					makeStar(2),
					makeStar(3),
					makeStar(4),
					makeStar(5)
				),
				$.mk('p').css('font-size','1.1em').append('Your rating: <strong>?</strong>/5')
			),
			$voteButton = $voting.children('.rate');

		$.Dialog.request('Rating '+EpID,$VoteForm,'Rate', function($form){
			$form.on('submit', function(e){
				e.preventDefault();

				let data = $form.mkData();

				if (typeof data.vote === 'undefined')
					return $.Dialog.fail(false, 'Please choose a rating by clicking on one of the muffins');

				$.Dialog.wait(false, 'Submitting your rating');

				$.post(`/episode/vote/${EpID}`,data,$.mkAjaxHandler(function(){
					if (!this.status) return $.Dialog.fail(false, this.message);

					let $section = $voteButton.closest('section');
					$section.children('h2').nextAll().remove();
					$section.append(this.newhtml);
					$voting.bindDetails();
					$.Dialog.close();
				}));
			});
		});
	});

	$voting.find('time').data('dyntime-beforeupdate',function(diff){
		if (diff.past !== true) return;

		if (!$voting.children('.rate').length){
			$.post(`/episode/vote/${EpID}?html`,$.mkAjaxHandler(function(){
				if (!this.status) return $.Dialog.fail('Display voting buttons',this.message);

				$voting.children('h2').nextAll().remove();
				$voting.append(this.html);
				$voting.bindDetails();
			}));
			$(this).removeData('dyntime-beforeupdate');
			return false;
		}
	});

	$.fn.bindDetails = function(){
		$(this).find('a.detail').on('click', function(e){
			e.preventDefault();
			e.stopPropagation();

			$.Dialog.wait('Voting details','Getting vote distribution information');

			$.post(`/episode/vote/${EpID}?detail`, $.mkAjaxHandler(function(){
				if (!this.status) return $.Dialog.fail(false, this.message);

				let $chart = $.mk('canvas'),
					ctx = $chart.get(0).getContext("2d"),
					$tooltip = $.mk('p').attr('class','tooltip');
				$.Dialog.info(false, [
					$.mk('p').text("Here’s a chart showing how the votes are distributed. Mouse over the different segments to see the exact number of votes."),
					$.mk('div').attr('id','vote-distrib').append($chart, $tooltip)
				]);
				                   //-- 0 ---,--- 1 ---,--- 2 ---,--- 3 ---,--- 4 ---,--- 5 ---
				let LegendColors = [undefined,"#FF5454","#FFB554","#FFFF54","#8CD446","#4DC742"],
					data = this.data,
					totalVotes = 0;

				data.datasets[0].backgroundColor = [];
				data.datasets[0].hoverBackgroundColor = [];
				data.datasets[0].borderWidth = [];
				data.datasets[0].hoverBorderColor = [];
				$.each(data.datasets[0].data,function(k,v){
					let bgcolor = LegendColors[parseInt(data.labels[k], 10)];
					data.datasets[0].backgroundColor.push(bgcolor);
					let lighter = $.RGBAColor.parse(bgcolor),
						mult = 1.06;
					lighter.red = Math.round(Math.min(255, lighter.red * mult));
					lighter.green = Math.round(Math.min(255, lighter.green * mult));
					lighter.blue = Math.round(Math.min(255, lighter.blue * mult));
					data.datasets[0].hoverBackgroundColor.push(lighter.toHex());
					data.datasets[0].borderWidth.push(2);
					data.datasets[0].hoverBorderColor.push(`rgba(${lighter.red},${lighter.green},${lighter.blue},0.9)`);
					totalVotes += parseInt(v, 10);
				});

				if (totalVotes === 0){
					$chart.remove();
					$tooltip.text('There are no votes for this episode yet');
					return;
				}

				new Chart(ctx,{
					type: 'pie',
					data: data,
					options: {
						titleFontColor: '#000',
						bodyFontColor: '#000',
						animation: {
							easing: 'easeInOutExpo',
						},
						legend: {
							display: false,
						},
						tooltips: {
							callbacks: {
								title: function(tooltip,data){
									let value = parseInt(data.labels[tooltip[0].index], 10);
									return `${value} muffin${value!==1?'s':''}`;
								},
								label: function(tooltip,data){
									let voteCount = parseInt(data.datasets[tooltip.datasetIndex].data[tooltip.index],10),
										votePerc = Math.round((voteCount/totalVotes)*1000)/10;
									return `${voteCount} user${voteCount!==1?'s':''} (${votePerc}%)`;
								}
							}
						}
					}
				});
			}));
		});
	};
	$voting.bindDetails();

	$.fn.rebindFluidbox = function(){
		$(this).find('.screencap > a:not(.fluidbox--initialized)')
			.fluidboxThis();
	};
	$._getLiTypeId = function($li){
		let ident = $li.attr('id').split('-');
		return {
			id: ident[1],
			type: ident[0]+'s',
		};
	};
	$.fn.rebindHandlers = function(isLi){
		let $collection = isLi ? this : this.find('li[id]');
		$collection.trigger('bind-more-handlers');
		this.closest('section').rebindFluidbox();
		return this;
	};
	let additionalHandlerAttacher = function(){
		let $li = $(this),
			ident = $._getLiTypeId($li),
			type = ident.type,
			id = ident.id,
			$actions = $li.find('.actions').children();

		$li.rebindFluidbox();

		$actions.filter('.share').on('click',function(){
			let $button = $(this),
				url = `${window.location.href.replace(/([^:/]\/).*$/,'$1')}s/${$button.parents('li').attr('id').replace(/^(re[qs])[^-]+?-(\d+)$/,'$1/$2')}`;

			$.Dialog.info(`Sharing ${type.replace(/s$/,'')} #${id}`, $.mk('div').attr('class','align-center').append(
				'Use the link below to link to this post directly:',
				$.mk('div').attr('class','share-link').text(url),
				$.mk('button').attr('class','blue typcn typcn-clipboard').text('Copy to clipboard').on('click', function(e){
					$.copy(url,e);
				})
			),function(){
				$('#dialogContent').find('.share-link').select();
			});
		});
	};
	$('#requests, #reservations')
		.on('pls-update',function(_, callback, silent){
			let $section = $(this),
				type = $section.attr('id'),
				Type = $.capitalize(type),
				typeWithS = type.replace(/([^s])$/,'$1s');
			if (silent !== true)
				$.Dialog.wait($.Dialog.isOpen() ? false : Type, 'Updating list of '+typeWithS, true);
			$.ajax(`/episode/postlist/${EpID}?section=${typeWithS}`,{
				method: "POST",
				success: $.mkAjaxHandler(function(){
					if (!this.status) return $.Dialog.fail(false, this.message);

					let $newChilds = $(this.render).filter('section').children();
					$section.empty().append($newChilds).rebindHandlers();
					$section.find('.post-form').attr('data-type',type).formBind();
					$section.find('h2 > button').enable();
					Time.Update();
					window._HighlightHash();
					if (typeof callback === 'function') callback();
					else if (silent !== true) $.Dialog.close();
				}),
			});
		})
		.on('bind-more-handlers','li[id]',additionalHandlerAttacher)
		.find('li[id]').each(additionalHandlerAttacher);

	$.fn.formBind = function (){
		let $form = $(this);
		if (!$form.length)
			return;
		let $formImgCheck = $form.find('.check-img'),
			$submitBtn = $form.find('button.submit'),
			$formImgPreview = $form.find('.img-preview'),
			$formDescInput = $form.find('input[name=label]'),
			$formImgInput = $form.find('input[name=image_url]'),
			$formLabelInput = $form.find('input[name=label]'),
			$notice = $formImgPreview.children('.notice'),
			noticeHTML = $notice.html(),
			$previewIMG = $formImgPreview.children('img'),
			type = $form.attr('data-type').replace(/s$/,''), Type = $.capitalize(type);

		if ($previewIMG.length === 0) $previewIMG = $(new Image()).appendTo($formImgPreview);
		$(`#${type}-btn`).on('click',function(){
			if ($form.hasClass('hidden')){
				$form.removeClass('hidden');
				$formDescInput.focus();
				$.scrollTo($form.offset().top - $navbar.outerHeight() - 10, 500);
			}
		});
		if (type === 'reservation') $('#add-reservation-btn').on('click',function(){
			let $AddReservationForm = $.mk('form','add-reservation').html(
				`<div class="notice info">This feature should only be used when the vector was made before the episode was displayed here, and you just want to link the finished vector under the newly posted episode OR if this was a request, but the original image (screencap) is no longer available, only the finished vector.</div>
				<div class="notice warn">If you already posted the reservation, use the <strong class="typcn typcn-attachment">I'm done</strong> button to mark it as finished instead of adding it here.</div>
				<label>
					<span>Deviation URL</span>
					<input type="text" name="deviation">
				</label>`
			);
			$.Dialog.request('Add a reservation',$AddReservationForm,'Finish', function($form){
				$form.on('submit', function(e){
					e.preventDefault();

					let deviation = $form.find('[name=deviation]').val();

					if (typeof deviation !== 'string' || deviation.length === 0)
						return $.Dialog.fail(false, 'Please enter a deviation URL');

					$.Dialog.wait(false, 'Adding reservation');

					$.post('/post/add-reservation',{deviation:deviation,epid:EpID},$.mkAjaxHandler(function(){
						if (!this.status) return $.Dialog.fail(false, this.message);

						$.Dialog.success(false, this.message);
						const id = this.id;
						$(`#${type}s`).trigger('pls-update', [function(){
							$.Dialog.close();
							window.location.hash = '#'+id;
						}]);
					}));
				});
			});
		});
		$formImgInput.on('keyup change paste',imgCheckDisabler);
		let outgoing =  /^https?:\/\/www\.deviantart\.com\/users\/outgoing\?/;
		function imgCheckDisabler(disable){
			let prevurl = $formImgInput.data('prev-url'),
				samevalue = typeof prevurl === 'string' && prevurl.trim() === $formImgInput.val().trim();
			const checkDisabled = disable === true || samevalue;
			$formImgCheck.attr('disabled', checkDisabled);
			$submitBtn.attr('disabled', !checkDisabled);
			if (checkDisabled)
				$formImgCheck.attr('title', 'You need to change the URL before checking again.');
			else $formImgCheck.removeAttr('title');

			if (disable.type === 'keyup'){
				let val = $formImgInput.val();
				if (outgoing.test(val))
					$formImgInput.val($formImgInput.val().replace(outgoing,''));
			}
		}
		let CHECK_BTN = '<strong class="typcn typcn-arrow-repeat" style="display:inline-block">Check image</strong>';
		function checkImage(){
			let url = $formImgInput.val(),
				title = Type+' process';

			$formImgCheck.removeClass('red');
			imgCheckDisabler(true);
			$.Dialog.wait(title,'Checking image');

			$.post('/post/check-image', { image_url: url }, $.mkAjaxHandler(function(){
				let data = this;
				if (!data.status){
					$notice.children('p:not(.keep)').remove();
					$notice.prepend($.mk('p').attr('class','color-red').html(data.message)).show();
					$previewIMG.hide();
					$formImgCheck.enable();
					if (typeof $formImgInput.data('prev-url') === 'string')
						$submitBtn.enable();
					else $submitBtn.disable();
					return $.Dialog.close();
				}

				function load(data, attempts){
					$.Dialog.wait(title,'Checking image availability');

					$previewIMG.attr('src',data.preview).show().off('load error').on('load',function(){
						$notice.children('p:not(.keep)').remove();

						$formImgInput.data('prev-url', url);

						if (!!data.title && !$formLabelInput.val().trim())
							$.Dialog.confirm(
								'Confirm '+type+' title',
								'The image you just checked had the following title:<br><br><p class="align-center"><strong>'+data.title+'</strong></p>'+
								'<br>Would you like to use this as the '+type+'’s description?<br>Keep in mind that it should describe the thing(s) '+
								(type==='request'?'being requested':'you plan to vector')+'.'+
								'<p>This dialog will not appear if you give your '+type+' a description before clicking the '+CHECK_BTN+' button.</p>',
								function(sure){
									if (!sure) return $form.find('input[name=label]').focus();
									$formLabelInput.val(data.title);
									$.Dialog.close();
								}
							);
						else $.Dialog.close(function(){
							$form.find('input[name=label]').focus();
						});
					}).on('error',function(){
						if (attempts < 1){
							$.Dialog.wait("Can’t load image",'Image could not be loaded, retrying in 2 seconds');
							setTimeout(function(){
								load(data, attempts+1);
							}, 2000);
							return;
						}
						$.Dialog.fail(title,"There was an error while attempting to load the image. Make sure the URL is correct and try again!");
						$formImgCheck.enable();
						if (typeof $formImgInput.data('prev-url') === 'string')
							$submitBtn.enable();
						else $submitBtn.disable();
					});
				}
				load(data, 0);
			}));
		}
		$formImgCheck.on('click', function(e){
			e.preventDefault();

			checkImage();
		});
		$form.on('submit',function(e, screwchanges, sanityCheck){
			e.preventDefault();
			let title = Type+' process';

			if (typeof $formImgInput.data('prev-url') === 'undefined')
				return $.Dialog.fail(title, 'Please click the '+CHECK_BTN+' button before submitting your '+type+'!');

			if (!screwchanges && $formImgInput.data('prev-url') !== $formImgInput.val())
				return $.Dialog.confirm(
					title,
					'You modified the image URL without clicking the '+CHECK_BTN+' button.<br>Do you want to continue with the last checked URL?',
					function(sure){
						if (!sure) return;

						$form.triggerHandler('submit',[true]);
					}
				);

			if (!sanityCheck && type === 'request'){
				let label = $formDescInput.val(),
					$type = $form.find('select');

				if (label.indexOf('character') > -1 && $type.val() !== 'chr')
					return $.Dialog.confirm(title, 'Your request label contains the word "character", but the request type isn’t set to Character.<br>Are you sure you\'re not requesting one (or more) character(s)?',['Let me change the type', 'Carray on'], function(sure){
						if (!sure) return $form.triggerHandler('submit',[screwchanges, true]);

						$.Dialog.close(function(){
							$type.focus();
						});
					});
			}

			let data = $form.mkData({
				what: type,
				episode: EPISODE,
				season: SEASON,
				image_url: $formImgInput.data('prev-url'),
			});

			(function submit(){
				$.Dialog.wait(title,'Submitting '+type);

				$.post('/post/add',data,$.mkAjaxHandler(function(){
					if (!this.status){
						if (!this.canforce)
							return $.Dialog.fail(false, this.message);
						return $.Dialog.confirm(false, this.message, ['Go ahead','Nevermind'], function(sure){
							if (!sure) return;

							data.allow_nonmember = true;
							submit();
						});
					}

					$.Dialog.success(false, Type+' posted');

					const id = this.id;
					$(`#${type}s`).trigger('pls-update', [function(){
						$.Dialog.close();
						$.Dialog.confirm(Type+' posted','Would you like to view it or make another?',['View','Make another'],function(view){
							$.Dialog.close();

							if (view) return;

							$(`#${type}-btn`).trigger('click');
						});
						window.location.hash = '#'+id;
					}]);
				}));
			})();
		}).on('reset',function(){
			$formImgCheck.attr('disabled', false).addClass('red');
			$notice.html(noticeHTML).show();
			$previewIMG.hide();
			$formImgInput.removeData('prev-url');
			$form.addClass('hidden');
		});
	};

	$.each(['requests','reservations'], function(_, el){
		$('#'+el)
			.trigger('bind-more-handlers')
			.find('.post-form').attr('data-type',el).formBind();
	});


	const deviationIO = new IntersectionObserver(entries => {
		entries.forEach(entry => {
			if (!entry.isIntersecting)
				return;

			const el = entry.target;
			deviationIO.unobserve(el);

			const
				postid = el.dataset.post.replace('-','/'),
				viewonly = el.dataset.viewonly;

			$.get(`/post/lazyload/${postid}`,{viewonly},$.mkAjaxHandler(function(){
				if (!this.status) return $.Dialog.fail('Cannot load '+postid.replace('/',' #'), this.message);

				$.loadImages(this.html).then(function($el){
					$(el).closest('.image').replaceWith($el);
				});
			}));
		});
	});
	const screencapIO = new IntersectionObserver(entries => {
		entries.forEach(entry => {
			if (!entry.isIntersecting)
				return;

			const el = entry.target;
			screencapIO.unobserve(el);

			const
				$link = $.mk('a'),
				image = new Image();
			image.src = el.dataset.src;
			$link.attr('href', el.dataset.href).append(image);

			$(image).on('load', function(){
				$(el).closest('.image').html($link);
				$link.closest('li').rebindFluidbox();
			});
		});
	});
	const avatarIO = new IntersectionObserver(entries => {
		entries.forEach(entry => {
			if (!entry.isIntersecting)
				return;

			const el = entry.target;
			avatarIO.unobserve(el);

			const image = new Image();
			image.src = el.dataset.src;
			image.classList = 'avatar';
			$(image).on('load', function(){
				$(el).replaceWith(image);
			});
		});
	});

	$('.post-deviation-promise').each((_, el) => deviationIO.observe(el));
	$('.post-image-promise').each((_, el) => screencapIO.observe(el));
	$('.user-avatar-promise').each((_, el) => avatarIO.observe(el));

	let postHashRegex = /^#(request|reservation)-\d+$/,
		showdialog = location.hash.length > 1 && postHashRegex.test(location.hash);

	if (showdialog)
		$.Dialog.wait('Scroll post into view', 'Waiting for images to load');
	directLinkHandler();

	let reloading = {};
	$.fn.reloadLi = function(log = true, callback = undefined){
		let $li = this,
			_idAttr = $li.attr('id');
		if (typeof _idAttr !== 'string' || $li.hasClass('admin-break'))
			return this;
		if (reloading[_idAttr] === true)
			return this;
		reloading[_idAttr] = true;

		let _idAttrArr = _idAttr.split('-'),
			type =_idAttrArr[0],
			id = _idAttrArr[1];

		if (log)
			console.log(`[POST-FIX] Attemting to reload ${type} #${id}`);
		$.post(`/post/reload/${type}/${id}`,{cache:log},$.mkAjaxHandler(function(){
			reloading[_idAttr] = false;
			if (!this.status) return;
			if (this.broken === true){
				$li.remove();
				console.log(`[POST-FIX] Hid (broken) ${type} #${id}`);
				return;
			}

			const $newli = $(this.li);
			$li = $('#'+$newli.attr('id'));

			if ($li.hasClass('highlight') || $newli.is(location.hash))
				$newli.addClass('highlight');
			$li.replaceWith($newli);
			$newli.rebindFluidbox();
			Time.Update();
			$newli.rebindHandlers(true);
			if (!$newli.parent().is(this.section))
				$newli.appendTo(this.section);
			$newli.parent().reorderPosts();

			if (log)
				console.log(`[POST-FIX] Reloaded ${type} #${id}`);
			$.callCallback(callback);
		}));

		return this;
	};
	$.fn.reorderPosts = function(){
		let $parent = this;
		$parent.children().sort(function(a,b){
			const
				$a = $(a),
				$b = $(b),
				$aFinAt = $a.find('.finish-date time'),
				$bFinAt = $b.find('.finish-date time');
			let diff;
			if ($aFinAt.length && $bFinAt.length)
				diff = (new Date($aFinAt.attr('datetime'))).getTime() - (new Date($bFinAt.attr('datetime'))).getTime();
			else diff = (new Date($a.find('.post-date time').attr('datetime'))).getTime() - (new Date($b.find('.post-date time').attr('datetime'))).getTime();
			if (diff === 0)
				return parseInt($a.attr('id').replace('/\D/g',''), 10) - parseInt($b.attr('id').replace('/\D/g',''), 10);
			return diff;
		}).appendTo($parent);
	};

	function directLinkHandler(){
		let $imgs = $content.find('img[src]'),
			total = $imgs.length, loaded = 0;

		if (!total)
			return $.Dialog.close();

		let $progress;
		if (showdialog){
			$progress = $.mk('progress').attr({max:total,value:0}).css({display:'block',width:'100%',marginTop:'5px'});
			$('#dialogContent').children('div:not([id])').last().addClass('align-center').append($progress);
		}
		$content.imagesLoaded()
			.progress(function(_, image){
				if (image.isLoaded){
					loaded++;
					if (showdialog)
						$progress.attr('value', loaded);
				}
				else if (image.img.src){
					// Attempt to re-load the post to fix image link
					let $li = $(image.img).closest('li[id]');
					if ($li.length === 1)
						$li.reloadLi();
					total--;
					if (showdialog)
						$progress.attr('max', total);
				}
			})
			.always(function(){
				let found = window._HighlightHash({type:'load'});
				if (found === false && showdialog){
					const title = 'Scroll post into view';
					// Attempt to find the post as a last resort, it might be on a different episode page
					$.post('/post/locate/'+location.hash.substring(1).replace('-','/'),{SEASON,EPISODE},$.mkAjaxHandler(function(){
						if (!this.status) return $.Dialog.info(title, this.message);

						if (this.refresh){
							$(`#${this.refresh}s`).triggerHandler('pls-update');
							return;
						}

						const castle = this.castle;

						const $contents =
							$(`<p>Looks like the post you were linked to is in another castle. Want to follow the path?</p>
							<div id="post-road-sign">
								<div class="sign-wrap">
									<div class="sign-inner">
										<span class="sign-text"></span>
										<span class="sign-arrow">\u2794</span>
									</div>
								</div>
								<div class="sign-pole"></div>
							</div>
							<div class="notice info">If you're seeing this message after clicking a link within the site please <a class="send-feedback">let us know</a>.</div>`);

						$contents.find('.sign-text').text(castle.name);

						$.Dialog.close(function(){
							$.Dialog.confirm(title, $contents, ['Take me there','Stay here'], sure => {
								if (!sure) return;

								$.Dialog.wait(false, 'Quicksaving');

								$.Navigation.visit(castle.url);
							});
						});
					}));
				}
			});
	}

	function bindVideoButtons(){
		let $embedWrap,
			$episode = $('.episode'),
			$showPlayers = $episode.find('.showplayers').on('scroll-video-into-view',function(){
				let hh = $header.outerHeight();
				$.scrollTo($embedWrap.offset().top - (($w.height() - $footer.outerHeight() - hh - $embedWrap.outerHeight()) / 2) - hh, 500);
			}),
			$playerActions = $showPlayers.parent(),
			$partSwitch;
		if ($showPlayers.length){
			let $reportBroken = $episode.find('.reportbroken');
			$showPlayers.on('click', function(e){
				e.preventDefault();

				if (typeof $embedWrap === 'undefined'){
					$.Dialog.wait($showPlayers.text());

					$.post(`/episode/video-embeds/${EpID}`, $.mkAjaxHandler(function(){
						if (!this.status) return $.Dialog.fail(false, this.message);

						if (this.parts === 2){
							$partSwitch = $.mk('button').attr('class','blue typcn typcn-media-fast-forward').text('Part 2').on('click',function(){
								$(this).toggleHtml(['Part 1', 'Part 2']);
								$embedWrap.children().toggleClass('hidden');
							});
							$playerActions.append($partSwitch);
						}
						$embedWrap = $.mk('div').attr('class','resp-embed-wrap').html(this.html).insertAfter($playerActions);
						$showPlayers
							.removeClass('typcn-eye green')
							.addClass('typcn-eye-outline blue')
							.text('Hide on-site player')
							.triggerHandler('scroll-video-into-view');
						$.Dialog.close();
					}));
				}
				else {
					let show = $showPlayers.hasClass('typcn-eye');
					$embedWrap[show?'show':'hide']();
					if ($partSwitch instanceof jQuery)
						$partSwitch.attr('disabled', !show);
					$showPlayers.toggleClass('typcn-eye typcn-eye-outline').toggleHtml(['Show on-site player','Hide on-site player']);

					if (show)
						$showPlayers.triggerHandler('scroll-video-into-view');
				}
			});
			$reportBroken.on('click',function(e){
				e.preventDefault();

				$.Dialog.confirm('Report broken video','<p>Have any of the linked videos been removed from their respective platform?<p><p>Please note that availability checking is automatic, bad video quality or sound issues cannot be detected this way. You should <a class="send-feedback">tell us</a> directly if that is the case.</p>',['Send report','Nevermind'],function(sure){
					if (!sure) return;

					$.Dialog.wait(false, 'Sending report');

					$.post(`/episode/broken-videos/${EpID}`, $.mkAjaxHandler(function(){
						if (!this.status) return $.Dialog.fail(false, this.message);

						if (typeof this.epsection !== 'undefined'){
							if (this.epsection.length > 0){
								$episode.html(this.epsection);
								bindVideoButtons();
							}
							else $episode.remove();
						}

						$.Dialog.success(false, this.message, true);
					}));
				});
			});
		}
	}
	window.bindVideoButtons = bindVideoButtons;
	bindVideoButtons();

	$.WS.recvPostUpdates(true);
});
