var timerHide = false;
window.array = [];

if(!funcDefined('showToggles'))
{
	showToggles = function()
	{
		new DG.OnOffSwitchAuto({
	        cls:'.block-item.active .custom-switch',
	        textOn:"",
	        height:33,
	        heightTrack:16,
	        textOff:"",
	        trackColorOff:"f5f5f5",
	        listener:function(name, checked){
	        	if(window.array.indexOf(name) == -1) {

		        	window.array.push(name);
		        	setTimeout(function(){
		        		window.array.splice(window.array.indexOf(name), 1);
		        	}, 500);
		        	var bNested = ($('input[name='+name+']').closest('.values').length && !$('input[name='+name+']').closest('.subs').length);
		        	if(checked)
						$('input[name='+name+']').val('Y');

					else
						$('input[name='+name+']').val('N');

					if(bNested)
					{
						var ajax_btn = $('<div class="btn-ajax-block animation-opacity"></div>'),
							option_wrapper = $('input[name='+name+']').closest('.option-wrapper'),
							pos = BX.pos(option_wrapper[0], true),
							current_index = $('input[name='+name+']').closest('.inner-wrapper').data('key'),
							div_class = name.replace(current_index+'_',''),
							top = 0;

						ajax_btn.html($('.values > .apply-block').html());
						option_wrapper.toggleClass('disabled');
						top = pos.top+$('.style-switcher .header').actual('outerHeight');
						ajax_btn.css('top',top);
						if($('.btn-ajax-block').length)
							$('.btn-ajax-block').remove();
						ajax_btn.appendTo($('.style-switcher'));
						ajax_btn.addClass('opacity1');

						if(checked)
						{
							/*if(div_class == 'INSTAGRAMM')
							{
								if(!$('.instagram_ajax .instagram').length)
								{
									$('.instagram_ajax').removeClass('loaded');
									$.ajax({
										type:"POST",
										url:arMaxOptions['SITE_DIR']+"include/mainpage/comp_instagramm.php",
										data:{'SHOW_INSTAGRAM':'Y', 'AJAX_REQUEST_INSTAGRAM':'Y'},
										success:function(html){
											$('.instagram_ajax').html(html);
										}
									})
								}
							}
							else*/ if(div_class == 'WITH_LEFT_BLOCK')
							{
								$('.wrapper_inner.front').removeClass('wide_page');
								$('.wrapper1.front_page').addClass('with_left_block');
								$('.wrapper_inner.front .container_inner > .right_block').removeClass('wide_Y').addClass('wide_N');
								$('.wrapper_inner.front .container_inner > .left_block').removeClass('hidden');

								if(typeof window['stickySidebar'] !== 'undefined')
								{
									window['stickySidebar'].updateSticky();
								}

							}
							$('.drag-block[data-class='+div_class.toLowerCase()+'_drag]').removeClass('hidden');
							$('.templates_block .item.'+name+'').removeClass('hidden');

							InitFlexSlider();
							$(window).resize();

							if(div_class == 'BIG_BANNER_INDEX')
							{
								$('.wrapper1').addClass('long_banner');
								$(window).resize();
							}
							if(div_class == 'MAPS' && (typeof map !== 'undefined'))
							{
								setTimeout(function(){
									map.setBounds(clusterer.getBounds(), {
										zoomMargin: 40,
										// checkZoomRange: true
									});
								}, 200)
							}
						}
						else
						{
							$('.drag-block[data-class='+div_class.toLowerCase()+'_drag]').addClass('hidden');
							$('.templates_block .item.'+name+'').addClass('hidden');

							if(div_class == 'WITH_LEFT_BLOCK')
							{
								$('.wrapper_inner.front').addClass('wide_page');
								$('.wrapper1.front_page').removeClass('with_left_block');
								$('.wrapper_inner.front .container_inner > .right_block').removeClass('wide_N wide_').addClass('wide_Y');
								$('.wrapper_inner.front .container_inner > .left_block').addClass('hidden');

								$(window).resize();
							}

							if(div_class == 'BIG_BANNER_INDEX')
							{
								$('.wrapper1').removeClass('long_banner');
							}
						}

						var eventdata = {action:'jsLoadBlock'};
						BX.onCustomEvent('onCompleteAction', [eventdata]);

						//save option
						$.post(
							arMaxOptions['SITE_DIR']+"ajax/options_save_mainpage.php",
							{
								VALUE: $('input[name='+name+']').val(),
								NAME: name
							}
						);
					}

					setTimeout(function(){
						if(!bNested)
							$('form[name=style-switcher]').submit();
					},200);
				}
				else
				{
					return false;
				}
	        }
	    });
	}
}

$(document).ready(function() {
	var timerDynamicLeftSide = false;

	// selected thematic is current value by default
	var selectedThematic = arMaxOptions.THEMATICS.VALUE;

	// select current thematic value
	var restoreThematics = function(){
		selectedThematic = arMaxOptions.THEMATICS.VALUE;
		selectThematic(selectedThematic, false);
	}

	// select thematic
	var selectThematic = function(thematic, bShowPresets){
		var $thematic = $thematic = $('.presets .thematik .item[data-code=' + thematic + ']');
		if($thematic.length){
			if(typeof arMaxOptions.THEMATICS.LIST[thematic] === 'object'){
				// thematic found

				// save selected value
				selectedThematic = thematic;

				// mark as current
				$thematic.addClass('active').siblings().removeClass('active');

				// set "-" on presets`s subtab
				$('.presets .presets_subtabs .presets_subtab .desc').html('&mdash;');

				// set thematic title on thematics`s subtab
				$('.presets .presets_subtabs .presets_subtab:first .desc').text(arMaxOptions.THEMATICS.LIST[thematic].TITLE);

				// hide all presets
				$('.presets .presets_block .conf .item').addClass('hidden');

				// show add new preset in preset editor
				$('.presets .presets_block .conf .item .js-addpreset').closest('.item').removeClass('hidden'); //

				if(typeof BX.admin !== 'object'){
					// if user is not admin, than hide APPLY buttons of all presets
					$('.presets .presets_block .conf .apply_conf_block').addClass('hidden');
				}

				// unmark current preset
				$('.presets .presets_block .conf .item .preset-block.current').removeClass('current');

				for(var i in arMaxOptions.THEMATICS.LIST[thematic].PRESETS.LIST){
					//each thematic`s preset

					var preset = arMaxOptions.THEMATICS.LIST[thematic].PRESETS.LIST[i];
					var $presetBlock = $('.presets .presets_block .conf .item .preset-block[data-id=' + preset + ']');

					if($presetBlock.length){
						if(typeof arMaxOptions.PRESETS.LIST[preset] === 'object'){
							// show preset
							$presetBlock.closest('.item').removeClass('hidden');

							// if selected thematic is without URL, than hide APPLY buttons of it`s presets
							if(arMaxOptions.THEMATICS.LIST[thematic].URL.length){
								// show APPLY button
								$presetBlock.find('.apply_conf_block').removeClass('hidden');
							}

							if(arMaxOptions.THEMATICS.VALUE == thematic){
								// selected current thematic

								// show APPLY button
								$presetBlock.find('.apply_conf_block').removeClass('hidden');

								if(arMaxOptions.PRESETS.VALUE == preset){
									// current preset

									// mark as current preset
									$presetBlock.addClass('current');

									// set preset title on presets`s subtab
									$('.presets .presets_subtabs .presets_subtab:last .desc').text(arMaxOptions.PRESETS.LIST[preset].TITLE);
								}
							}
						}
					}
				}

				if(typeof bShowPresets !== 'undefined' && bShowPresets){
					// open presets list
					$('.presets .presets_subtabs .presets_subtab').last().trigger('click');
				}

				return;
			}
		}
	}

	// select preset
	var selectPreset = function(preset){
		var $preset = $('.style-switcher .presets .preset-block[data-id=' + preset + ']');

		if($preset.length){
			if(
				// selected preset is current already or editing
				$preset.hasClass('current') ||
				$preset.hasClass('editing')
			){
				return;
			}

			if(typeof arMaxOptions.PRESETS.LIST[preset] === 'object'){
				// is dev
				var bDev = location.hostname.indexOf('dev.aspro.ru') !== -1;

				// is demo
				var bDemo = !bDev && typeof arMaxOptions.THEMATICS.LIST[selectedThematic] === 'object' && arMaxOptions.THEMATICS.LIST[selectedThematic].URL.length;

				// is selected thematic not current
				var bPrepareWizard = !bDev && !bDemo && (selectedThematic != arMaxOptions.THEMATICS.VALUE);

				if(bPrepareWizard){
					// install new thematic
					prepareWizard(selectedThematic, preset);
				}
				else{
					// unmark current preset
					$('.style-switcher .presets .preset-block.current').removeClass('current');

					// mark as current
					$preset.addClass('current');

					// set preset title on presets`s subtab
					$preset.closest('.presets').find('.presets_subtab.active .desc').text($preset.find('.info .title').text());

					// apply preset configuration
					setConfiguration(selectedThematic, preset);
				}
			}
		}
	}

	var setConfiguration = function(thematic, preset){
		if(typeof arMaxOptions.THEMATICS.LIST[thematic] === 'object'){
			if(typeof arMaxOptions.PRESETS.LIST[preset] === 'object'){
				// is dev
				var bDev = location.hostname.indexOf('dev.aspro.ru') !== -1;

				// is demo
				var bDemo = !bDev && arMaxOptions.THEMATICS.LIST[thematic].URL.length;

				if(bDemo && thematic !== arMaxOptions.THEMATICS.VALUE){
					location.href = arMaxOptions.THEMATICS.LIST[thematic].URL + '?aspro_preset=' + preset;
				}
				else{
					// order of main page blocks
					var order = [];

					// list of options to send
					var options = {
						'backurl': arMaxOptions['SITE_DIR'],
						'THEMATIC': thematic
					};

					var serialize = $('form[name=style-switcher]').serializeArray();
					for(j in serialize){
						// add each form option value
						options[serialize[j].name] = serialize[j].value;
					}

					if(typeof arMaxOptions.PRESETS.LIST[preset]['OPTIONS'] === 'object'){
						for(j in arMaxOptions.PRESETS.LIST[preset]['OPTIONS']){
							// change value of each preset option in options list

							var val = arMaxOptions.PRESETS.LIST[preset]['OPTIONS'][j];
							if(typeof val !== 'object'){
								options[j] = val;
							}
							else{
								if(typeof val.VALUE !== 'undefined'){
									options[j] = val.VALUE;

									if(typeof val.ADDITIONAL_OPTIONS === 'object'){
										for(z in val.ADDITIONAL_OPTIONS){
											var addoption = val.ADDITIONAL_OPTIONS[z];
											if(typeof addoption === 'object'){
												for(addoptioncode in addoption){
													if(typeof addoption[addoptioncode] !== 'undefined'){
														options[addoptioncode + '_' + z] = addoption[addoptioncode];
													}
												}
											}
										}
									}

									if(typeof val.SUB_PARAMS === 'object'){
										for(z in val.SUB_PARAMS){
											var subval = val.SUB_PARAMS[z];
											if(typeof subval !== 'object'){
												options[val.VALUE + '_' + z] = subval;
											}
											else{
												if(typeof subval.VALUE !== 'undefined'){
													options[val.VALUE + '_' + z] = subval.VALUE;

													if(typeof subval.TEMPLATE !== 'undefined'){
														options[val.VALUE + '_' + z + '_TEMPLATE'] = subval.TEMPLATE;

														if(typeof subval.ADDITIONAL_OPTIONS !== 'undefined'){
															for(addoptioncode in subval.ADDITIONAL_OPTIONS){
																options[val.VALUE + '_' + z + '_' + addoptioncode + '_' + subval.TEMPLATE] = subval.ADDITIONAL_OPTIONS[addoptioncode];
															}
														}
													}

													if(typeof subval.FON !== 'undefined'){
														options['fon' + val.VALUE + z] = subval.FON;
													}
												}
											}
										}
									}

									if(typeof val.DEPENDENT_PARAMS === 'object'){
										for(z in val.DEPENDENT_PARAMS){
											var depval = val.DEPENDENT_PARAMS[z];
											if(typeof depval !== 'object'){
												options[z] = depval;
											}
										}
									}

									if(typeof val.ORDER === 'string'){
										order.push({
											NAME: 'SORT_ORDER_' + j + '_' + val.VALUE,
											VALUE: val.ORDER
										});

										options['SORT_ORDER_' + j + '_' + val.VALUE] = val.ORDER;
									}
								}
							}
						}
					}

					if(typeof arMaxOptions.THEMATICS.LIST[thematic]['OPTIONS'] === 'object'){
						for(j in arMaxOptions.THEMATICS.LIST[thematic]['OPTIONS']){
							// change value of each thematic option in options list

							var val = arMaxOptions.THEMATICS.LIST[thematic]['OPTIONS'][j];
							if(typeof val !== 'object'){
								options[j] = val;
							}
							else{
								if(typeof val.VALUE !== 'undefined'){
									options[j] = val.VALUE;

									if(typeof val.ADDITIONAL_OPTIONS === 'object'){
										for(z in val.ADDITIONAL_OPTIONS){
											var addoption = val.ADDITIONAL_OPTIONS[z];
											if(typeof addoption === 'object'){
												for(addoptioncode in addoption){
													if(typeof addoption[addoptioncode] !== 'undefined'){
														options[addoptioncode + '_' + z] = addoption[addoptioncode];
													}
												}
											}
										}
									}

									if(typeof val.SUB_PARAMS === 'object'){
										for(z in val.SUB_PARAMS){
											var subval = val.SUB_PARAMS[z];
											if(typeof subval !== 'object'){
												options[val.VALUE + '_' + z] = subval;
											}
											else{
												if(typeof subval.VALUE !== 'undefined'){
													options[val.VALUE + '_' + z] = subval.VALUE;

													if(typeof subval.TEMPLATE !== 'undefined'){
														options[val.VALUE + '_' + z + '_TEMPLATE'] = subval.TEMPLATE;

														if(typeof subval.ADDITIONAL_OPTIONS !== 'undefined'){
															for(addoptioncode in subval.ADDITIONAL_OPTIONS){
																options[val.VALUE + '_' + z + '_' + addoptioncode + '_' + subval.TEMPLATE] = subval.ADDITIONAL_OPTIONS[addoptioncode];
															}
														}
													}

													if(typeof subval.FON !== 'undefined'){
														options['fon' + val.VALUE + z] = subval.FON;
													}
												}
											}
										}
									}

									if(typeof val.DEPENDENT_PARAMS === 'object'){
										for(z in val.DEPENDENT_PARAMS){
											var depval = val.DEPENDENT_PARAMS[z];
											if(typeof depval !== 'object'){
												options[z] = depval;
											}
										}
									}

									if(typeof val.ORDER === 'string'){
										order.push({
											NAME: 'SORT_ORDER_' + j + '_' + val.VALUE,
											VALUE: val.ORDER
										});

										options['SORT_ORDER_' + j + '_' + val.VALUE] = val.ORDER;
									}
								}
							}
						}
					}

					function _sendOptions(){
						$.ajax({
							type: 'POST',
							data: options,
							success: function(){
								// close switcher
								$('.style-switcher .presets_action').trigger('click');

								// go to main page
								location.href = arMaxOptions['SITE_DIR'];
							}
						});
					}

					function _sendOrder(){
						if(order.length){
							var sort = order.pop();
							$.ajax({
								url: arMaxOptions['SITE_DIR'] + 'ajax/options_save_mainpage.php',
								type: 'POST',
								data: sort,
								success: function(){
									_sendOrder();
								}
							});
						}
						else{
							_sendOptions();
						}
					}

					// send each order array and than send options array
					_sendOrder();
				}
			}
		}
	}

	// show prepare wizard page, can redefine
	if(typeof prepareWizard === 'undefined'){
		prepareWizard = function(thematic, preset){
			if(typeof arMaxOptions.THEMATICS.LIST[thematic] === 'object'){
				if(typeof arMaxOptions.PRESETS.LIST[preset] === 'object'){
					$.ajax({
						url: $('.style-switcher .contents.wizard').data('script'),
						type: 'POST',
						data: {
							action: 'getform',
							thematic: thematic,
							preset: preset
						},
						success: function(response){
							// put response to content
							$('.style-switcher .contents.wizard').html(response);

							// show prepare wizard page
							$('.style-switcher .contents.wizard').addClass('active');
						}
					});
				}
			}
		}
	}

	/* get updates */
	$.ajax({
		url:'https://aspro.ru/demo/updates/index.php',
		type:'POST',
		data:{
			'AJAX_FORM': 'Y',
		},
		success:function(html){
			$('.section-block.updates_tab').removeClass('hidden');
			$('.right-block .inner-content .contents.updates .body_block').html(html);

			$('.style-switcher .contents.updates .right-block .body_block').mCustomScrollbarDeferred({
				mouseWheel: {
					scrollAmount: 150,
					preventDefault: true
				}
			})
		},
		error:function(jqXhr){
			console.log(jqXhr);
		}
	})
	/**/

	HideHintBlock = function(bHideOverlay)
	{
		if(typeof bHideOverlay === 'undefined' || bHideOverlay){
			HideOverlay();
		}
		$.cookie('clickedSwitcher', 'Y', {path: '/'});
		if($('.hint-theme').length)
		{
			$('.hint-theme').fadeIn(300, function(){
				$('.hint-theme').remove();
			});
		}
	}

	$('.style-switcher .presets .presets_block').mCustomScrollbar({
		mouseWheel: {
			scrollAmount: 150,
			preventDefault: true
		},
		callbacks:{
			onScroll: function(){
				var topPositionPresets = $('.style-switcher .presets .mCSB_container').css('top').replace(/"/g, '');
				topPositionPresets = parseInt(topPositionPresets);
				topPositionPresets = isNaN(topPositionPresets) ? 0 : topPositionPresets;
				$.cookie('STYLE_SWITCHER_SCROLL_PRESET_POSITION', topPositionPresets, {path: arMaxOptions['SITE_DIR']});
			}
		},
		setTop: (function() {
			try {
				return (typeof($.cookie('STYLE_SWITCHER_SCROLL_PRESET_POSITION')) !== 'undefined' ? $.cookie('STYLE_SWITCHER_SCROLL_PRESET_POSITION')+'px' : 0);
			} catch(error){
				console.log(error);
				return 0;
			}
		}()),
	});

	$('.style-switcher .left-block').mCustomScrollbar({
		mouseWheel: {
			scrollAmount: 150,
			preventDefault: true
		},
		callbacks:{
			onScroll: function(){
				var topPositionLeftBlock = $('.style-switcher .left-block .mCSB_container').css('top').replace(/"/g, '');
				topPositionLeftBlock = parseInt(topPositionLeftBlock);
				topPositionLeftBlock = isNaN(topPositionLeftBlock) ? 0 : topPositionLeftBlock;
				$.cookie('STYLE_SWITCHER_SCROLL_LEFT_POSITION', topPositionLeftBlock, {path: arMaxOptions['SITE_DIR']});
			}
		},
		setTop: (function() {
			try {
				return (typeof($.cookie('STYLE_SWITCHER_SCROLL_LEFT_POSITION')) !== 'undefined' ? $.cookie('STYLE_SWITCHER_SCROLL_LEFT_POSITION')+'px' : 0);
			} catch(error){
				console.log(error);
				return 0;
			}
		}()),
	});

	$('.style-switcher .contents.parametrs .right-block').mCustomScrollbar({
		mouseWheel: {
			scrollAmount: 150,
			preventDefault: true,
		},
		callbacks:{
			onScroll: function(){
				var topPositionRightBlock = $('.style-switcher .parametrs .right-block .mCSB_container').css('top').replace(/"/g, '');
				topPositionRightBlock = parseInt(topPositionRightBlock);
				topPositionRightBlock = isNaN(topPositionRightBlock) ? 0 : topPositionRightBlock;
				$.cookie('STYLE_SWITCHER_SCROLL_RIGHT_POSITION', topPositionRightBlock, {path: arMaxOptions['SITE_DIR']});

				InitLazyLoad();
			}
		},
		setTop: (function() {
			try {
				return (typeof($.cookie('STYLE_SWITCHER_SCROLL_RIGHT_POSITION')) !== 'undefined' ? $.cookie('STYLE_SWITCHER_SCROLL_RIGHT_POSITION')+'px' : 0);
			} catch(error){
				console.log(error);
				return 0;
			}
		}()),
	});

	$('.style-switcher .contents.demos .right-block').mCustomScrollbarDeferred({
		mouseWheel: {
			scrollAmount: 150,
			preventDefault: true,
		},
	});

	$('.style-switcher .item input[type=checkbox]').on('change', function(){
		var _this =  $(this);
		if(_this.is(':checked'))
			_this.val('Y');
		else
			_this.val('N');
		if(typeof _this.data('dynamic') === undefined)
		{
			$('form[name=style-switcher]').submit();
		}
		else
		{
			$('.'+_this.data('index_block')).toggleClass('grey_block');
			//save option
			$.post(
				arMaxOptions['SITE_DIR']+"ajax/options_save_mainpage.php",
				{
					VALUE: _this.val(),
					NAME: _this.attr('name')
				}
			);
		}
	})

	/* close search block */
	$("html, body").on('mousedown', function(e){
		if(typeof e.target.className == 'string' && e.target.className.indexOf('adm') < 0)
		{
			e.stopPropagation();
			if(!$(e.target).closest('.style-switcher .dynamic_left_side').length){
				$('.style-switcher .dynamic_left_side').removeClass('active');
			}

			if(!$(e.target).closest('.style-switcher .contents.wizard').length){
				$('.style-switcher .contents.wizard').removeClass('active');
			}
		}
	});

	$('.dynamic_left_side').find('*').on('mousedown', function(e){
		e.stopPropagation();
	});

	$('.sup-params .values .subtitle').click(function(){
		var _this = $(this),
			wrapper = _this.closest('.option-wrapper');
		if(wrapper.find('.template_block > .item').is(':visible'))
			$.removeCookie('STYLE_SWITCHER_TEMPLATE'+wrapper.index(), {path: arMaxOptions['SITE_DIR']});
		else
			$.cookie('STYLE_SWITCHER_TEMPLATE'+wrapper.index(), 'Y', {path: arMaxOptions['SITE_DIR']});

		wrapper.find('.template_block .item').slideToggle();
	});

	$('.presets .presets_subtabs .presets_subtab').on('click', function(){
		var _this = $(this);
		_this.siblings().removeClass('active');
		_this.addClass('active');

		$('.presets .presets_block .options').removeClass('active');
		_this.closest('.presets').find('.options:eq('+_this.index()+')').addClass('active');

		// $('.dynamic_left_side .cl').click();
		if(_this.index() == 0){
			restoreThematics();
		}

		$.cookie('STYLE_SWITCHER_CONFIG_BLOCK', _this.index(), {path: arMaxOptions['SITE_DIR']});
	})

	$(document).on('click', '.presets .thematik .item', function(){
		var thematic = $(this).data('code');
		selectThematic(thematic, true);
	})

	$(document).on('click', '.style-switcher .presets .preset-block .apply_conf_block', function(e){
		var preset = $(this).closest('.preset-block').data('id');
		selectPreset(preset);
	});

	$('.style-switcher .can_save .save_btn').on('click', function(){
		var _this = $(this);

		if(timerHide){
			clearTimeout(timerHide);
			timerHide = false;
		}

		$.ajax({
			type:"POST",
			url:arMaxOptions['SITE_DIR']+"ajax/options_save.php",
			data:{'SAVE_OPTIONS':'Y'},
			dataType:"json",
			success:function(response){
				if("STATUS" in response)
				{
					if(!$('.save_config_status').length)
						$('<div class="save_config_status"><span></span></div>').appendTo(_this.parent());
					if(response.STATUS === 'OK')
						$('.save_config_status').addClass('success');
					else
						$('.save_config_status').addClass('error');

					$('.save_config_status span').text(BX.message(response.MESSAGE));

					$('.save_config_status').slideDown(200);
					timerHide = setTimeout(function(){
						// here delayed functions in event
						$('.save_config_status').slideUp(200, function(){
							$(this).remove();
							$('.action_block.can_save').remove();
						})
					}, 1000);
				}
			}
		})
	})

	showToggles(); //replace checkbox in custom toggle

	if($.cookie('styleSwitcherType') === 'presets'){
		$('.style-switcher .presets').addClass('active');
	}

	if($.cookie('styleSwitcher') === 'open'){
		$('.style-switcher').addClass('active');

		if($.cookie('styleSwitcherType') === 'presets'){
			$('.style-switcher .switch.presets_action').addClass('active');
		}
		else{
			$('.style-switcher .switch').addClass('active');
		}
	}

	//sort order for main page
	$('.refresh-block.sup-params .values .inner-wrapper').each(function(){
		var _th = $(this),
			sort_block = _th[0];
		Sortable.create(sort_block,{
			handle: '.drag',
			animation: 150,
			forceFallback: true,
			filter: '.no_drag',
			// Element dragging started
			onStart: function (/**Event*/evt){
				evt.oldIndex;  // element index within parent
				window.getSelection().removeAllRanges();

				$(evt.item).find('.template_block').addClass('hidden');
			},
			// Element dragging ended
			onEnd: function (/**Event*/evt){
				$(evt.item).find('.template_block').removeClass('hidden');
			},
			onMove: function (evt) {
				return evt.related.className.indexOf('no_drag') === -1;
			},
			// Changed sorting within list
			onUpdate: function (/**Event*/evt){
				var itemEl = evt.item;  // dragged HTMLElement
				var order = [],
					current_type = _th.data('key'),
					name = 'SORT_ORDER_INDEX_TYPE_'+current_type;
				$(itemEl).find('.template_block').removeClass('hidden');

				_th.find('.option-wrapper').each(function(){
					order.push($(this).find('.blocks input[type="checkbox"]').attr('name').replace(current_type+'_', ''));
					$('div[data-class="'+$(this).find('.blocks input[type="checkbox"]').attr('name').toLowerCase().replace(current_type+'_', '')+'_drag"]').attr('data-order', $(this).index()+1);
				})

				$('input[name='+name+']').val(order.join(','));

				//save option
				$.post(
					arMaxOptions['SITE_DIR']+"ajax/options_save_mainpage.php",
					{
						VALUE: order.join(','),
						NAME: name
					}
				);

				var eventdata = {action:'jsLoadBlock'};
				BX.onCustomEvent('onCompleteAction', [eventdata]);
			},
		});
	})

	if($('.base_color_custom input[type=hidden]').length)
	{
		$('.base_color_custom input[type=hidden]').each(function(){
			var _this = $(this),
				parent = $(this).closest('.base_color_custom');
			_this.spectrum({
				preferredFormat: 'hex',
				showButtons: true,
				showInput: true,
				showPalette: false,
				appendTo: parent,
				chooseText: BX.message('CUSTOM_COLOR_CHOOSE'),
				cancelText: BX.message('CUSTOM_COLOR_CANCEL'),
				containerClassName: 'custom_picker_container',
				replacerClassName: 'custom_picker_replacer',
				clickoutFiresChange: false,
				move: function(color) {
					var colorCode = color.toHexString();
					/*parent.find('span span.vals').text(colorCode);
					parent.find('span.animation-all').attr('style', 'border-color:' + colorCode);
					*/
					parent.find('span span.bg').attr('style', 'background:' + colorCode);
				},
				hide: function(color) {
					var colorCode = color.toHexString();
					/*parent.find('span span.vals').text(colorCode);
					parent.find('span.animation-all').attr('style', 'border-color:' + colorCode);
					*/
					parent.find('span span.bg').attr('style', 'background:' + colorCode);
				},
				change: function(color) {
					var colorCode = color.toHexString();
					parent.addClass('current').siblings().removeClass('current');

					parent.find('span span.vals').text(colorCode);
					parent.find('span.animation-all').attr('style', 'border-color:' + colorCode);

					$('form[name=style-switcher] input[name=' + parent.find('.click_block').data('option-id') + ']').val(parent.find('.click_block').data('option-value'));
					$('form[name=style-switcher]').submit();
				}
			});
		})
	}

	$('.base_color_custom').click(function(e) {
		e.preventDefault();
		$('input[name='+$(this).data('name')+']').spectrum('toggle');
		return false;
	});

	if($('.base_color.current').length)
	{
		$('.base_color.current').each(function(){
			var color_block = $(this).closest('.options').find('.base_color_custom'),
				curcolor = $(this).data('color');
			if(curcolor != undefined && curcolor.length)
			{
				$('input[name='+color_block.data('name')+']').spectrum('set', curcolor);
				color_block.find('span span').attr('style', 'background:' + curcolor);
			}
		})
	}
	$('.style-switcher .switch,.style-switcher .presets_action').click(function(e){
		e.preventDefault();

		var styleswitcher = $(this).closest('.style-switcher');
		var presets = styleswitcher.find('.presets');
		var parametrs = styleswitcher.find('.parametrs');
		var bSwitchPresets = $(this).hasClass('presets_action');

		styleswitcher.find('.section-block').removeClass('active');

		try {
			if(typeof($.cookie('STYLE_SWITCHER_SCROLL_RIGHT_POSITION')) !== 'undefined') {
				//var rightPosition = isNaN(parseInt($.cookie('STYLE_SWITCHER_SCROLL_RIGHT_POSITION'))) ? 0 : $.cookie('STYLE_SWITCHER_SCROLL_RIGHT_POSITION');
				$('.style-switcher .contents.parametrs .right-block').mCustomScrollbar('scrollTo', $.cookie('STYLE_SWITCHER_SCROLL_RIGHT_POSITION'));
			}

			if(typeof($.cookie('STYLE_SWITCHER_SCROLL_LEFT_POSITION')) !== 'undefined') {
				//var leftPosition = isNaN(parseInt($.cookie('STYLE_SWITCHER_SCROLL_LEFT_POSITION'))) ? 0 : $.cookie('STYLE_SWITCHER_SCROLL_LEFT_POSITION');
				$('.style-switcher .left-block').mCustomScrollbar('scrollTo', $.cookie('STYLE_SWITCHER_SCROLL_LEFT_POSITION'));
			}

			if(typeof($.cookie('STYLE_SWITCHER_SCROLL_PRESET_POSITION')) !== 'undefined') {
				//var presetPosition = isNaN(parseInt($.cookie('STYLE_SWITCHER_SCROLL_PRESET_POSITION'))) ? 0 : $.cookie('STYLE_SWITCHER_SCROLL_PRESET_POSITION');
				$('.style-switcher .presets .presets_block').mCustomScrollbar('scrollTo', $.cookie('STYLE_SWITCHER_SCROLL_PRESET_POSITION'));
			}
		} catch(error) {
			console.log(error);
		}

		if(styleswitcher.hasClass('active')){
			restoreThematics();

			// current switch type
			var typeSwitcher = $.cookie('styleSwitcherType');

			// change switcher bgcolor
			styleswitcher.find('.switch').removeClass('active');
			styleswitcher.find('.presets_action').removeClass('active');

			if((bSwitchPresets && typeSwitcher === 'presets') || (!bSwitchPresets && typeSwitcher === 'parametrs')){
				HideHintBlock(true);

				// remove switcher type
				$.removeCookie('styleSwitcherType', {path: '/'});

				// save switcher as hidden
				$.removeCookie('styleSwitcher', {path: '/'});

				// hide switcher with transition
				styleswitcher.addClass('closes');
				setTimeout(function(){
					styleswitcher.removeClass('active');
				}, 300)
			}
			else{
				HideHintBlock(false);

				// save switcher type
				$.cookie('styleSwitcherType', (bSwitchPresets ? 'presets' : 'parametrs'), {path: '/'});

				// hide switcher title
				styleswitcher.find('.header .title').hide();

				// set presets visible or hidden with transition and change switcher bgcolor
				if(bSwitchPresets){
					// styleswitcher.find('.header .title.title-presets').show();
					$('.section-block.presets_tab').addClass('active');
					presets.addClass('active');
					parametrs.removeClass('active');
				}
				else if($(this).hasClass('demo_action'))
				{
					$('.section-block.demos_tab').removeClass('hidden').addClass('active');
					$('.inner-content .contents').removeClass('active');
					$('.inner-content .contents.demos').removeClass('hidden').addClass('active');

					$.removeCookie('styleSwitcherType', {path: '/'});
					$.removeCookie('styleSwitcher', {path: '/'});
				}
				else{
					// styleswitcher.find('.header .title.title-parametrs').show();
					$('.section-block.parametrs_tab').addClass('active');
					presets.removeClass('active');
					parametrs.addClass('active');
				}
				$(this).addClass('active');
			}
		}
		else{
			HideHintBlock(true);

			// change switcher bgcolor
			$(this).addClass('active');

			// save switcher type
			$.cookie('styleSwitcherType', (bSwitchPresets ? 'presets' : 'parametrs'), {path: '/'});

			// save switcher as open
			$.cookie('styleSwitcher', 'open', {path: '/'});

			// set presets visible or hidden immediately before adding .active to .style-switcher
			if(bSwitchPresets){
				// styleswitcher.find('.header .title.title-presets').show();
				$('.section-block.presets_tab').addClass('active');
				presets.addClass('active');
				parametrs.removeClass('active');
			}
			else if($(this).hasClass('demo_action'))
			{
				$('.section-block.demos_tab').removeClass('hidden').addClass('active');
				$('.inner-content .contents').removeClass('active');
				$('.inner-content .contents.demos').removeClass('hidden').addClass('active');

				$.removeCookie('styleSwitcherType', {path: '/'});
				$.removeCookie('styleSwitcher', {path: '/'});
			}
			else{
				// styleswitcher.find('.header .title.title-parametrs').show();
				$('.section-block.parametrs_tab').addClass('active');
				presets.removeClass('active');
				parametrs.addClass('active');
			}

			// show overlay
			ShowOverlay();

			// show switcher with transition
			styleswitcher.removeClass('closes').addClass('active');

			var lazyCounter = setInterval(function(){
				if( $('.style-switcher').hasClass('active') ) {
					InitLazyLoad();
					clearInterval(lazyCounter);
				}
			}, 500);

		}

		InitLazyLoad();

	});

	$('.item.groups-tab a[data-toggle="tab"].linked').on('shown.bs.tab', function(e){
		var _this = $(this);

		$.cookie('styleSwitcherTabs'+_this.closest('.tabs').data('parent'), _this.parent().index(), {path: '/'});

		setTimeout(function(){
			InitLazyLoad();
		}, 250)
	})

	$(document).on('click', '.close-overlay', function(){
		HideHintBlock()
	})

	$('.close_block').click(function(){
		$('.jqmOverlay').trigger('click');
	})

	$(document).on('click', '.jqmOverlay', function(){
		var styleswitcher = $('.style-switcher');

		if(!$('.hint-theme').length){
			HideOverlay();
		}

		styleswitcher.each(function(){
			var _this = $(this);
			_this.addClass('closes');

			setTimeout(function(){
				_this.removeClass('active');
			},300);

			$('.form_demo-switcher').animate({
				left: '-' + $('.form_demo-switcher').outerWidth() + 'px'
			}, 100).removeClass('active abs');
		})

		$('.style-switcher .switch,.style-switcher .presets_action').removeClass('active');

		restoreThematics();

		$.removeCookie('styleSwitcherType', {path: '/'});
		$.removeCookie('styleSwitcher', {path: '/'});
	})

	$('.style-switcher .section-block').on('click', function(){
		$(this).siblings().removeClass('active');
		$(this).addClass('active');


		$('.style-switcher .right-block .contents').removeClass('active');
		$('.style-switcher .right-block .contents.' + $(this).data('type')).addClass('active');

		$.cookie('styleSwitcherType', $(this).data('type'), {path: '/'});

		// save switcher as open
		$.cookie('styleSwitcher', 'open', {path: '/'});

		if($(this).hasClass('demos_tab') || $(this).hasClass('updates_tab'))
		{
			$.removeCookie('styleSwitcherType', {path: '/'});
			$.removeCookie('styleSwitcher', {path: '/'});
		}

		setTimeout(function(){
			InitLazyLoad();
		}, 250)
	})

	$('.style-switcher .subsection-block').on('click', function(){
		$(this).siblings().removeClass('active');
		$(this).addClass('active');

		$('.style-switcher .right-block .contents .content-body .block-item').removeClass('active');
		$('.style-switcher .right-block .contents .content-body .block-item:eq('+$(this).index()+')').addClass('active');

		$.cookie('styleSwitcherSubType', $(this).index(), {path: '/'});

		//replace checkbox in custom toggle
		if(!$(this).hasClass('toggle_initied') && !$(this).hasClass('presets_tab'))
			showToggles();
		$(this).addClass('toggle_initied');

		setTimeout(function(){
			InitLazyLoad();
		}, 250)
	})

	$('.style-switcher .reset').click(function(e){
		$('form[name=style-switcher]').append('<input type="hidden" name="THEME" value="default" />');
		$('form[name=style-switcher]').submit();

		$.removeCookie('styleSwitcherTabsCatalog', {path: '/'});
	});

	$(document).on('click', '.style-switcher .apply', function(){
		$('form[name=style-switcher]').submit();
	})

	$('.style-switcher .sup-params.options .block-title').click(function(){
		$(this).next().slideToggle();
		setTimeout(function(){
			InitLazyLoad();
		}, 250)
	})

	$(document).on('click', '.style-switcher .preview_conf_block .btn', function(){
		var _this = $(this);

		if($('.dynamic_left_side').length)
			$('.dynamic_left_side').remove();

		$('<div class="dynamic_left_side"><div class="items_inner"><div class="titles_block"></div></div></div>').appendTo(_this.closest('.contents.presets .presets_block'));
		$('.dynamic_left_side .titles_block').html(
			'<div class="title">'+_this.closest('.preset-block').find('.info .title').text()+'</div>'+
			'<div class="blocks_wrapper">'+
				'<div class="cl" title="'+BX.message('FANCY_CLOSE')+'">'+$('.close_block .closes').html()+'</div>'+
				(_this.closest('.preset-block').find('.apply_conf_block').hasClass('hidden') ? '' : '<div class="ch" data-id="'+_this.closest('.preset-block').data('id')+'">'+_this.closest('.preset-block').find('.apply_conf_block').html()+'</div>') +
			'</div>'
		)

		$('<div class="desc">'+_this.closest('.preset-block').find('.info .description').text()+'</div>').appendTo($('.dynamic_left_side .items_inner'));
		if(_this.closest('.preset-block').find('.info .description').data('img'))
			$('<div class="img"><img src="'+_this.closest('.preset-block').find('.info .description').data('img')+'" /></div>').appendTo($('.dynamic_left_side .items_inner'));

		$('.dynamic_left_side').mCustomScrollbar({
			mouseWheel: {
				scrollAmount: 150,
				preventDefault: true
			}
		})
		if(timerDynamicLeftSide)
		{
			clearTimeout(timerDynamicLeftSide);
			timerDynamicLeftSide = false;
		}
		timerDynamicLeftSide = setTimeout(function(){
			$('.dynamic_left_side').addClass('active');
		}, 100)
	})

	$('.style-switcher .ext_hint_title').click(function(){
		var _this = $(this);

		if($('.dynamic_left_side').length)
			$('.dynamic_left_side').remove();

		$('<div class="dynamic_left_side"><div class="items_inner"></div></div>').appendTo(_this.closest('.contents.parametrs > .right-block'));
		$('<div class="cl" title="'+BX.message('FANCY_CLOSE')+'">'+$('.close_block .closes').html()+'</div>').appendTo($('.dynamic_left_side'));

		$('.ext_hint_desc').find('iframe').attr('src', $('.ext_hint_desc').find('iframe').data('src'));

		$('.dynamic_left_side .items_inner').html(_this.siblings('.ext_hint_desc').html());

		$('.dynamic_left_side').mCustomScrollbar({
			mouseWheel: {
				scrollAmount: 150,
				preventDefault: true
			}
		})
		if(timerDynamicLeftSide)
		{
			clearTimeout(timerDynamicLeftSide);
			timerDynamicLeftSide = false;
		}
		timerDynamicLeftSide = setTimeout(function(){
			$('.dynamic_left_side').addClass('active');
		}, 100)
	})

	$(document).on('click', '.dynamic_left_side .ch .btn', function(e){
		var preset = $(this).parent().data('id');
		selectPreset(preset);
		$('.dynamic_left_side').removeClass('active');
	})

	$(document).on('click', '.dynamic_left_side .cl', function(e){
		$('.dynamic_left_side').removeClass('active');
	})

	$('.style-switcher .options > .link-item,.style-switcher .options > div:not(.base_color_custom) .link-item,.style-switcher .options > div:not(.base_color_custom) .click_block').click(function(e){
		var _this = $(this);
		var bMulti = _this.data('type') == 'multi';
		var bCurrent = _this.hasClass('current');

		if(!bMulti && bCurrent)
			return;


		if(bMulti) {
			_this.toggleClass('current');
		} else {
			if(!_this.closest('.subs').length)
				_this.closest('.options').find('.link-item').removeClass('current');

			_this.siblings().removeClass('current');
			_this.addClass('current');
		}


		if(bMulti) {
			var input = $('form[name=style-switcher] input[name=' + _this.data('option-id') + ']');
			var inputVal = input.val();

			if(!inputVal) {
				input.val(_this.data('option-value'));
			} else {
				inputVal = inputVal.split(',');
				if(bCurrent) {
					inputVal.splice( inputVal.indexOf(_this.data('option-value')), 1 );
				} else {
					inputVal.push(_this.data('option-value'));
				}
				inputVal = inputVal.join();
				input.val(inputVal);
			}
		} else {
			$('form[name=style-switcher] input[name=' + _this.data('option-id') + ']').val(_this.data('option-value'));
		}

		if(_this.closest('.sup-params').length)
			$.removeCookie('styleSwitcher', {path: '/'});

		if(typeof($(this).data('option-type')) != 'undefined') // set cookie for scroll block
			$.cookie('scroll_block', $(this).data('option-type'));

		if(typeof($(this).data('option-url')) != 'undefined') // set action form for redirect
			$('form[name=style-switcher]').prepend('<input type="hidden" name="backurl" value='+$(this).data('option-url')+' />');

		if(_this.closest('.options').hasClass('refresh-block'))
		{
			if(!_this.closest('.options').hasClass('sup-params'))
				var index = _this.index()-1;
			_this.closest('.item').find('.sup-params.options').removeClass('active');
			_this.closest('.item').find('.sup-params.options.s_'+_this.data('option-value')+'').addClass('active');
			$('form[name=style-switcher]').submit();
		}
		else
		{
			$('form[name=style-switcher]').submit();
		}
	});

	$('.tooltip-link').on('shown.bs.tooltip', function (e) {
		var tooltip_block = $(this).next(),
			wihdow_height = $(window).height(),
			scroll = $(this).closest('form').scrollTop(),
			pos = BX.pos($(this)[0], true),
			pos_tooltip = BX.pos(tooltip_block[0], true),
			pos_item_wrapper = BX.pos($(this).closest('.item')[0], true);

		if(!$(this).closest('.item').next().length && pos_tooltip.bottom > pos_item_wrapper.bottom)
		{
			tooltip_block.removeClass('bottom').addClass('top');
			tooltip_block.css({'top':(pos.top-tooltip_block.actual('outerHeight'))});
		}
	})
});