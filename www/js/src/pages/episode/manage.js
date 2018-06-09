/* global bindVideoButtons */
(function(){
	'use strict';

	let SEASON = window.SEASON,
		EPISODE = window.EPISODE,
		USERNAME_REGEX = window.USERNAME_REGEX,
		FULLSIZE_MATCH_REGEX = window.FULLSIZE_MATCH_REGEX,
		EpID = 'S'+SEASON+'E'+EPISODE,
		isMovie = SEASON === 0,
		What = isMovie ? 'Movie' : 'Episode',
		what = What.toLowerCase(),
		$epSection = $content.children('section.episode');

	$('#video').on('click',function(){
		$.Dialog.wait('Set video links', 'Requesting links from the server');

		$.API.get(`/episode/${EpID}/video-data`,$.mkAjaxHandler(function(){
			let data = this;

			if (!data.status) return $.Dialog.fail(false, data.message);

			let yt_input = `<input type='url' class='yt' name='yt_1' placeholder='YouTube' spellcheck='false' autocomplete='off'>`,
				dm_input = `<input type='url' class='dm' name='dm_1' placeholder='Dailymotion' spellcheck='false' autocomplete='off'>`,
				sv_input = `<input type='url' class='sv' name='sv_1' placeholder='sendvid' spellcheck='false' autocomplete='off'>`,
				mg_input = `<input type='url' class='mg' name='mg_1' placeholder='Mega' spellcheck='false' autocomplete='off'>`,
				$VidLinksForm = $.mk('form').attr('id','vidlinks').attr('class','align-center').html(
					`<p>Enter vido links below, leave any input blank to remove that video from the ${what} page.</p>
					<div class='inputs'>
						${yt_input}
						${dm_input}
						${sv_input}
						${mg_input}
					</div>`
				);
			if (data.twoparter){
				$.mk('p').html('<strong>~ Part 1 ~</strong>').insertBefore($VidLinksForm.children('input').first());
				$VidLinksForm.append(
					`<p>Check below if either link contains the full ${what} instead of just one part</p>
					<div>
						<label><input type='checkbox' name='yt_1_full'> YouTube</label>
						<label><input type='checkbox' name='dm_1_full'> Dailymotion</label>
						<label><input type='checkbox' name='sv_1_full'> sendvid</label>
						<label><input type='checkbox' name='mg_1_full'> Mega</label>
					</div>
					<p><strong>~ Part 2 ~</strong></p>
					<div class='inputs'>
						${yt_input.replace('yt_1', 'yt_2')}
						${dm_input.replace('dm_1', 'dm_2')}
						${sv_input.replace('sv_1', 'sv_2')}
						${sv_input.replace('mg_1', 'mg_2')}
					</div>`
				);
				$VidLinksForm.find('input[type="checkbox"]').on('change',function(){
					let provider = $(this).attr('name').replace(/^([a-z]+)_.*$/,'$1');
					$VidLinksForm.find('input').filter(`[name=${provider}_2]`).attr('disabled', this.checked);
				});
				if (data.fullep.length > 0)
					$.each(data.fullep,function(_,prov){
						$VidLinksForm
							.find('input[type="checkbox"]')
							.filter('[name="'+prov+'_1_full"]')
							.prop('checked', true)
							.trigger('change');
					});
			}
			if (Object.keys(data.vidlinks).length > 0){
				let $inputs = $VidLinksForm.find('input[type="url"]');
				$.each(data.vidlinks,function(k,v){
					$inputs.filter('[name='+k+']').val(v);
				});
			}
			$.Dialog.request(false,$VidLinksForm,'Save', function($form){
				$form.on('submit', function(e){
					e.preventDefault();

					let data = $form.mkData();
					$.Dialog.wait(false, 'Saving links');

					$.API.put(`/episode/${EpID}/video-data`,data,$.mkAjaxHandler(function(){
						if (!this.status) return $.Dialog.fail(false, this.message);

						if (this.epsection){
							if (!$epSection.length)
								$epSection = $.mk('section')
									.addClass('episode')
									.insertBefore($content.children('section').first());
							$epSection.html($(this.epsection).filter('section').html());
							bindVideoButtons();
						}
						else if ($epSection.length){
							$epSection.remove();
							$epSection = {length:0};
						}
						$.Dialog.close();
					}));
				});
			});
		}));
	});

	let $cgRelations = $content.children('section.appearances');
	$('#cg-relations').on('click',function(){
		$.Dialog.wait('Guide relation editor', 'Retrieving relations from server');

		$.API.get(`/episode/${EpID}/guide-relations`,$.mkAjaxHandler(function(){
			if (!this.status) return $.Dialog.fail(false, this.message);

			let data = this,
				$GuideRelationEditorForm = $.mk('form').attr('id','guide-relation-editor'),
				$selectLinked = $.mk('select').attr({name:'listed',multiple:true}),
				$selectUnlinked = $.mk('select').attr('multiple', true);

			if (data.linked && data.linked.length)
				$.each(data.linked,function(_, el){
					$selectLinked.append($.mk('option').attr('value', el.id).text(el.label));
				});
			if (data.unlinked && data.unlinked.length)
				$.each(data.unlinked,function(_, el){
					$selectUnlinked.append($.mk('option').attr('value', el.id).text(el.label));
				});

			$GuideRelationEditorForm.append(
				$.mk('div').attr('class','split-select-wrap').append(
					$.mk('div').attr('class','split-select').append("<span>Linked</span>",$selectLinked),
					$.mk('div').attr('class','buttons').append(
						$.mk('button').attr({'class':'typcn typcn-chevron-left green',title:'Link selected'}).on('click', function(e){
							e.preventDefault();

							$selectLinked.append($selectUnlinked.children(':selected').prop('selected', false)).children().sort(function(a,b){
								return a.innerHTML.localeCompare(b.innerHTML);
							}).appendTo($selectLinked);
						}),
						$.mk('button').attr({'class':'typcn typcn-chevron-right red',title:'Unlink selected'}).on('click', function(e){
							e.preventDefault();

							$selectUnlinked.append($selectLinked.children(':selected').prop('selected', false)).children().sort(function(a,b){
								return a.innerHTML.localeCompare(b.innerHTML);
							}).appendTo($selectUnlinked);
						})
					),
					$.mk('div').attr('class','split-select').append("<span>Available</span>",$selectUnlinked)
				)
			);

			$.Dialog.request(false,$GuideRelationEditorForm,'Save', function($form){
				$form.on('submit', function(e){
					e.preventDefault();

					let ids = [];
					$selectLinked.children().each(function(_, el){ ids.push(el.value) });
					$.Dialog.wait(false, 'Saving changes');

					$.API.put(`/episode/${EpID}/guide-relations`,{ids:ids.join(',')},$.mkAjaxHandler(function(){
						if (!this.status) return $.Dialog.fail(false, this.message);

						if (this.section){
							if (!$cgRelations.length)
								$cgRelations = $.mk('section')
									.addClass('appearances')
									.insertBefore($content.children('.admin'));
							$cgRelations.html($(this.section).filter('section').html());
						}
						else if ($cgRelations.length){
							$cgRelations.remove();
							$cgRelations = {length:0};
						}
						$.Dialog.close();
					}));
				});
			});
		}));
	});

	$('#edit-about_reservations, #edit-reservation_rules').on('click', function(e){
		e.preventDefault();

		let $h2 = $(this).parent(),
			$h2c = $h2.clone(),
			endpoint = this.id.split('-').pop();
		$h2c.children().remove();
		let text = $h2c.text().trim();

		$.Dialog.wait(`Editing "${text}"`,"Retrieving setting's value");
		$.post(`/setting/get/${endpoint}`,$.mkAjaxHandler(function(){
			if (!this.status) return $.Dialog.fail(false, this.message);

			let $EditorForm = $.mk('form', `${endpoint}-editor`).html(`<span>${text}</span>`),
				value = this.value;

			$.Dialog.request(false, $EditorForm, 'Save', function($form){
				const mode = 'html';
			    let editor = ace.edit($.mk('div').appendTo($form).get(0));
				editor.setShowPrintMargin(false);
			    let session = $.aceInit(editor, mode);
			    session.setMode(mode);
			    session.setUseWrapMode(true);
			    session.setValue(value);

				$form.on('submit', function(e){
					e.preventDefault();

					let data = { value: session.getValue() };
					$.Dialog.wait(false, 'Saving');

					$.post(`/setting/set/${endpoint}`, data, $.mkAjaxHandler(function(){
						if (!this.status) return $.Dialog.fail(false, this.message);

						$h2.siblings().remove();
						$h2.parent().append(this.value);
						$.Dialog.close();
					}));
				});
			});
		}));
	});

	function reservePost($li, reserveAs, id, type){
		let title = 'Reserving request',
			send = function(data){
				$.Dialog.wait(title, 'Sending reservation to the server');

				$.API.post(`/post/request/${id}/reservation`, data, $.mkAjaxHandler(function(){
					if (this.retry)
						return $.Dialog.confirm(false, this.message, function(sure){
							if (!sure) return;

							data.screwit = true;
							send(data);
						});
					else if (!this.status)
						return $.Dialog.fail(false, this.message);

					if (this.li){
						let $newli = $(this.li);
						if ($li.hasClass('highlight'))
							$newli.addClass('highlight');
						$li.replaceWith($newli);
						Time.Update();
						$newli.trigger('bind-more-handlers', [id, type]);
					}
					$.Dialog.close();
				}));
			};

		if (typeof USERNAME_REGEX === 'undefined' || !reserveAs) send({});
		else {
			let $ReserveAsForm = $.mk('form').attr('id','reserve-as').append(
				$.mk('label').append(
					"<span>Reserve as</span>",
					$.mk('input').attr({
						type: 'text',
						name: 'post_as',
						required: true,
						placeholder: 'Username',
					}).patternAttr(USERNAME_REGEX)
				),
				$.mk('label').append(
					$.mk('span').text('Reserved at'),
					$.mk('input').attr({
						type: 'datetime',
						name: 'reserved_at',
						spellcheck: false,
						autocomplete: 'off',
						placeholder: 'time()',
					})
				)
			);
			$.Dialog.request(title,$ReserveAsForm,'Reserve', function($form){
				$form.on('submit', function(e){
					e.preventDefault();

					send($form.mkData());
				});
			});
		}
	}

	let additionalHandlerAttacher = function(){
		let $li = $(this),
			ident = $._getLiTypeId($li),
			id = ident.id,
			type = ident.type.replace(/s$/,''),
			Type = $.capitalize(type);

		$li.children('button.reserve-request').off('click').on('click', function(e){
			e.preventDefault();

			reservePost($li, e.shiftKey, id, type);
		});

		let $actions = $li.find('.actions').children();
		$actions.filter('.cancel').off('click').on('click',function(){
			$.Dialog.confirm('Cancel reservation','Are you sure you want to cancel this reservation?', function(sure){
				if (!sure) return;

				$.Dialog.wait(false, 'Cancelling reservation');
				$li.addClass('deleting');

				if (type === 'request')
					$.API.delete(`/post/request/${id}/reservation`, $.mkAjaxHandler(function(){
						if (!this.status) return $.Dialog.fail(false, this.message);

						$li.removeClass('deleting').reloadLi(false);
						$.Dialog.close();
					}));
				else {
					$.API.delete(`/post/${id}/reservation`, $.mkAjaxHandler(function(){
						if (!this.status) return $.Dialog.fail(false, this.message);

						$.Dialog.close();
						return $li[window.withinMobileBreakpoint()?'slideUp':'fadeOut'](500,function(){
							$li.remove();
						});
					}));
				}
			});
		});
		$actions.filter('.finish').off('click').on('click',function(){
			let $FinishResForm = $.mk('form').attr('id', 'finish-res').append(
				$.mk('label').append(
					$.mk('span').text('Deviation URL'),
					$.mk('input').attr({
						type: 'url',
						name: 'deviation',
						spellcheck: false,
						autocomplete: 'off',
						required: true,
					})
				)
			);
			if (typeof USERNAME_REGEX !== 'undefined')
				$FinishResForm.append(
					$.mk('label').append(
						$.mk('span').text('Finished at'),
						$.mk('input').attr({
							type: 'datetime',
							name: 'finished_at',
							spellcheck: false,
							autocomplete: 'off',
							placeholder: 'time()',
						})
					)
				);
			$.Dialog.request('Complete reservation',$FinishResForm,'Finish', function($form){
				$form.on('submit', function(e){
					e.preventDefault();

					let deviation = $form.find('[name=deviation]').val();

					if (typeof deviation !== 'string' || deviation.length === 0)
						return $.Dialog.fail(false, 'Please enter a deviation URL');

					const sent_data = $form.mkData();

					(function attempt(){
						$.Dialog.wait(false, 'Marking post as finished');

						$.API.put(`/post/${id}/finish`,sent_data,$.mkAjaxHandler(function(data){
							if (data.status){
								$.Dialog.success(false, Type+' has been marked as finished');

								$(`#${type}s`).trigger('pls-update', [function(){
									if (typeof data.message === 'string' && data.message)
										$.Dialog.success(false, data.message, true);
									else $.Dialog.close();
								}]);

								return;
							}

							if (data.retry){
								$.Dialog.confirm(false, data.message, ["Continue","Cancel"], function(sure){
									if (!sure) return;
									sent_data.allow_overwrite_reserver = true;
									attempt();
								});
							}
							else $.Dialog.fail(false, data.message);
						}));
					})();
				});
			});
		});
		$actions.filter('.unfinish').off('click').on('click',function(){
			let $unFinishBtn = $(this),
				deleteOnly = $unFinishBtn.hasClass('delete-only'),
				Type = $.capitalize(type),
				what = type.replace(/s$/,'');

			$.Dialog.request((deleteOnly?'Delete':'Un-finish')+' '+what,'<form id="unbind-check"><p>Are you sure you want to '+(deleteOnly?'delete this reservation':'mark this '+what+' as unfinished')+'?</p><hr><label><input type="checkbox" name="unbind"> Unbind '+what+' from user</label></form>','Un-finish', function($form){
				let $unbind = $form.find('[name=unbind]');

				if (!deleteOnly)
					$form.prepend('<div class="notice info">By removing the "finished" flag, the post will be moved back to the "List of '+Type+'" section</div>');

				if (type === 'reservation'){
					$unbind.on('click',function(){
						$('#dialogButtons').children().first().val(this.checked ? 'Delete' : 'Un-finish');
					});
					if (deleteOnly)
						$unbind.trigger('click').off('click').on('click keydown touchstart', () => false).css('pointer-events','none').parent().hide();
					$form.append('<div class="notice warn">Because this '+(!deleteOnly?'is a reservation, unbinding it from the user will <strong>delete</strong> it permanently.':'reservation was added directly, it cannot be marked unfinished, only deleted.')+'</div>');
				}
				else
					$form.append('<div class="notice info">If this is checked, any user will be able to reserve this request again afterwards. If left unchecked, only the current reserver <em>(and Vector Inspectors)</em> will be able to mark it as finished until the reservation is cancelled.</div>');
				$w.trigger('resize');
				$form.on('submit', function(e){
					e.preventDefault();

					let unbind = $unbind.prop('checked');

					$.Dialog.wait(false, 'Removing "finished" flag'+(unbind?' & unbinding from user':''));

					$.API.delete(`/post/${id}/finish${unbind?'?unbind':''}`,$.mkAjaxHandler(function(){
						if (!this.status) return $.Dialog.fail(false, this.message);

						$.Dialog.success(false, typeof this.message !== 'undefined' ? this.message : '"finished" flag removed successfully');
						$(`#${type}s`).trigger('pls-update');
					}));
				});
			});
		});
		$actions.filter('.check').off('click').on('click', function(e){
			e.preventDefault();

			$.Dialog.wait('Submission approval status','Checking');

			$.API.post(`/post/${id}/approval`, $.mkAjaxHandler(function(){
				if (!this.status) return $.Dialog.fail(false, this.message);

				let message = this.message;
				$li.reloadLi();
				$.Dialog.success(false, message, true);
			}));
		});
		$actions.filter('.unlock').off('click').on('click', function(e){
			e.preventDefault();

			$.Dialog.confirm('Unlocking post','Are you sure you want to unlock this post?', function(sure){
				if (!sure) return;

				$.Dialog.wait(false);

				$.API.delete(`/post/${id}/approval`, $.mkAjaxHandler(function(){
					if (!this.status) return $.Dialog.fail(false, this.message);

					$(`#post-${id}`).closest('.posts').trigger('pls-update');
				}));
			});
		});
		$actions.filter('.delete').off('click').on('click',function(){
			let $this = $(this);

			$.Dialog.confirm(`Deleting request #${id}`, 'You are about to permanently delete this request.<br>Are you sure about this?', function(sure){
				if (!sure) return;

				$.Dialog.wait(false);
				$li.addClass('deleting');

				$.API.delete(`/post/request/${id}`,$.mkAjaxHandler(function(){
					if (!this.status){
						$li.removeClass('deleting');
						return $.Dialog.fail(false, this.message);
					}

					$.Dialog.close();
					$this.closest('li')[window.withinMobileBreakpoint()?'slideUp':'fadeOut'](500,function(){
						$(this).remove();
					});
				}));
			});
		});
		$actions.filter('.edit').off('click').on('click',function(){
			const
				$button = $(this),
				$li = $button.closest('li'),
				{ id, type } = $._getLiTypeId($li),
				isRequest = type === 'requests';

			$.Dialog.wait(`Editing post #${id}`, `Retrieving details`);

			$.API.get(`/post/${id}`,$.mkAjaxHandler(function(data){
				if (!data.status) return $.Dialog.fail(false, data.message);

				let $PostEditForm = $.mk('form').attr('id', 'post-edit-form').append(
					$.mk('label').append(
						$.mk('span').text(`Description (3-255 chars.${!isRequest?', optional':''})`),
						$.mk('input').attr({
							type: 'text',
							maxlength: 255,
							pattern: "^.{3,255}$",
							name: 'label',
							required: isRequest,
						})
					)
				);

				if (isRequest)
					$PostEditForm.append(
						$.mk('label').append(
							$.mk('span').text('Request type'),
							$.mk('select').attr({
								name: 'type',
								required: true,
							}).append(
								$.mk('option').attr('value','chr').text('Character'),
								$.mk('option').attr('value','obj').text('Object'),
								$.mk('option').attr('value','bg').text('Background')
							)
						)
					);

				if (typeof data.posted_at === 'string')
					$PostEditForm.append(
						$.mk('label').append(
							$.mk('span').text('Post timestamp'),
							$.mk('input').attr({
								type: 'datetime',
								name: 'posted_at',
								required: true,
								spellcheck: false,
								autocomplete: 'off',
							})
						)
					);
				if (typeof data.reserved_at === 'string')
					$PostEditForm.append(
						$.mk('label').append(
							$.mk('span').text('Reserved at'),
							$.mk('input').attr({
								type: 'datetime',
								name: 'reserved_at',
								spellcheck: false,
								autocomplete: 'off',
							})
						)
					);
				if (typeof data.finished_at === 'string')
					$PostEditForm.append(
						$.mk('label').append(
							$.mk('span').text('Finished at'),
							$.mk('input').attr({
								type: 'datetime',
								name: 'finished_at',
								spellcheck: false,
								autocomplete: 'off',
							})
						)
					);

				let show_img_update_btn = $li.children('.image').hasClass('screencap'),
					finished = $li.closest('div').attr('class') === 'finished',
					$fullsize_link = finished ? $li.children('.original') : $li.children('.image').children('a'),
					fullsize_url = $fullsize_link.attr('href'),
					show_stash_fix_btn = !finished && !FULLSIZE_MATCH_REGEX.test(fullsize_url) && /deviantart\.net\//.test(fullsize_url),
					deemed_broken = $li.children('.broken-note').length ;

				if (show_img_update_btn || show_stash_fix_btn || deemed_broken){
					$PostEditForm.append(
						$.mk('label').append(
							(
								show_img_update_btn
								? $.mk('a').text('Update Image').attr({
									'href':'#update',
									'class':'btn darkblue typcn typcn-pencil',
								}).on('click', function(e){
									e.preventDefault();

									$.Dialog.close();
									let $img = $li.children('.image').find('img'),
										$ImgUpdateForm = $.mk('form').attr('id', 'img-update-form').append(
											$.mk('div').attr('class','oldimg').append(
												$.mk('span').text('Current image'),
												$img.clone()
											),
											$.mk('label').append(
												$.mk('span').text('New image URL'),
												$.mk('input').attr({
													type: 'text',
													maxlength: 255,
													pattern: "^.{2,255}$",
													name: 'image_url',
													required: true,
													autocomplete: 'off',
													spellcheck: 'false',
												})
											)
										);
									$.Dialog.request(`Update image of post #${id}`,$ImgUpdateForm,'Update', function($form){
										$form.on('submit', function(e){
											e.preventDefault();

											let data = $form.mkData();
											$.Dialog.wait(false, 'Replacing image');

											$.API.put(`/post/${id}/image`,data,$.mkAjaxHandler(function(){
												if (!this.status) return $.Dialog.fail(false, this.message);

												$.Dialog.success(false, 'Image has been updated', true);

												if (this.li){
													let $newli = $(this.li);
													if ($li.hasClass('highlight'))
														$newli.addClass('highlight');
													$li.replaceWith($newli);
													Time.Update();
													$newli.trigger('bind-more-handlers');
												}
												else $li.reloadLi();
											}));
										});
									});
								})
								: undefined
							),
							(
								show_stash_fix_btn
								? $.mk('a').text('Sta.sh fullsize fix').attr({
									'class':'btn orange typcn typcn-spanner',
								}).on('click', function(e){
									e.preventDefault();
									$.Dialog.close();
									$.Dialog.wait('Fix Sta.sh fullsize URL','Fixing Sta.sh full size image URL');

									$.API.post(`/post/${id}/fix-stash`,$.mkAjaxHandler(function(){
										if (!this.status){
											if (this.rmdirect){
												if (!finished){
													$li.find('.post-date').children('a').first().triggerHandler('click');
													return $.Dialog.fail(false, `${this.message}<br>The post might be broken because of this, please check it for any issues.`);
												}
												$li.children('.original').remove();
											}
											return $.Dialog.fail(false, this.message);
										}

										$fullsize_link.attr('href', this.fullsize);
										$.Dialog.success(false, 'Fix successful', true);
									}));
								})
								: undefined
							),
							(
								deemed_broken
								? $.mk('a').text('Clear broken status').attr({
									'href':'#clear-broken-status',
									'class':'btn orange typcn typcn-spanner',
								}).on('click', function(e){
									e.preventDefault();
									$.Dialog.close();
									$.Dialog.wait('Clear post broken status','Checking image availability');

									$.API.get(`/post/${id}/unbreak`,$.mkAjaxHandler(function(){
										if (!this.status) return $.Dialog.fail(false, this.message);

										if (this.li){
											let $newli = $(this.li);
											if ($li.hasClass('highlight'))
												$newli.addClass('highlight');
											$li.replaceWith($newli);
											Time.Update();
											$newli.trigger('bind-more-handlers');
										}

										$.Dialog.close();
									}));
								})
								: undefined
							)
						)
					);
				}

				$.Dialog.request(false, $PostEditForm, 'Save', function($form){
					let $label = $form.find('[name=label]'),
						$type = $form.find('[name=type]'),
						$posted_at, $reserved_at, $finished_at;
					if (data.label)
						$label.val(data.label);
					if (data.type)
						$type.children('option').filter(function(){
							return this.value === data.type;
						}).attr('selected', true);
					if (typeof data.posted_at === 'string'){
						$posted_at = $form.find('[name=posted_at]');

						let posted_at = moment(data.posted_at);
						$posted_at.val(posted_at.format());
					}
					if (typeof data.reserved_at === 'string'){
						$reserved_at = $form.find('[name=reserved_at]');

						if (data.reserved_at.length){
							let reserved = moment(data.reserved_at);
							$reserved_at.val(reserved.format());
						}
					}
					if (typeof data.finished_at === 'string'){
						$finished_at = $form.find('[name=finished_at]');

						if (data.finished_at.length){
							let finished = moment(data.finished_at);
							$finished_at.val(finished.format());
						}
					}
					$form.on('submit', function(e){
						e.preventDefault();

						let data = { label: $label.val() };
						if (isRequest)
							data.type = $type.val();
						if (typeof data.posted_at === 'string'){
							data.posted_at = new Date($posted_at.val());
							if (isNaN(data.posted_at.getTime()))
								return $.Dialog.fail(false, 'Post timestamp is invalid');
							data.posted_at = data.posted_at.toISOString();
						}
						if (typeof data.reserved_at === 'string'){
							let reserved_at = $reserved_at.val();
							if (reserved_at.length){
								data.reserved_at = new Date(reserved_at);
								if (isNaN(data.reserved_at.getTime()))
									return $.Dialog.fail(false, '"Reserved at" timestamp is invalid');
								data.reserved_at = data.reserved_at.toISOString();
							}
						}
						if (typeof data.finished_at === 'string'){
							let finished_at = $finished_at.val().trim();
							if (finished_at.length){
								data.finished_at = new Date(finished_at);
								if (isNaN(data.finished_at.getTime()))
									return $.Dialog.fail(false, '"Finished at" timestamp is invalid');
								data.finished_at = data.finished_at.toISOString();
							}
						}

						$.Dialog.wait(false, 'Saving changes');

						$.API.put(`/post/${id}`,data, $.mkAjaxHandler(function(){
							if (!this.status) return $.Dialog.fail(false, this.message);

							$li.reloadLi();

							$.Dialog.close();
						}));
					});
				});
			}));
		});
		$actions.filter('.pls-transfer').off('click').on('click',function(){
			let reservedBy = $li.children('.reserver').find('.name').text();
			$.Dialog.confirm(`Take on reservation of ${type} #${id}`,
				`<p>Using this option, you can express your interest in finishing the ${type} which ${reservedBy} already reserved.</p>
				<p>They will be sent a notification letting them know you're interested and they'll be able to allow/deny the transfer of the reserver status as they see fit.</p>
				<p>Once ${reservedBy} responds to your inquiry you'll receive a notification informing you about their decision. If they agreed, the post's reservation will be transferred to you immediately.</p>
				<p><strong>Are you sure you can handle this ${type}?</strong></p>`, sure => {
					if (!sure) return;

					$.Dialog.wait(false);

					$.API.post(`/post/${id}/transfer`,$.mkAjaxHandler(function(){
						if (this.canreserve)
							return $.Dialog.confirm(false, this.message, function(sure){
								if (!sure) return;

								reservePost($li, false, id, type);
							});
						else if (!this.status)
							return $.Dialog.fail(false, this.message);

						$.Dialog.success(false, this.message, true);
					}));
				});
		});
	};
	$('#requests, #reservations')
		.on('bind-more-handlers','li[id]',additionalHandlerAttacher)
		.find('li[id]').each(additionalHandlerAttacher);
})();
