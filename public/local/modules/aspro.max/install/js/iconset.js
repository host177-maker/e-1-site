if(typeof window.JIconset === 'undefined'){
	window.JIconset = function(rand, code, arConfig){
		this.rand = rand;
		this.code = code;
		this.config = {};

		this.popup = false;
		this.block = false;
		this.form = false;
		this.selectButton = false;

		this.eventListners = [];

		if(typeof arConfig === 'object'){
			this.config = arConfig;

			this.init();
		}
	}

	window.JIconset.prototype = {
		init: function(){
			var that = this;

			BX.ready(function(){
				if(that.block = BX('iconset-' + that.rand)){
					that.block.iconset = that;

					if(that.popup = that.block.closest('.bx-core-window')){
						that.selectButton = that.popup.querySelector('.adm-btn-save[id=iconset_button--select]');
						if(!that.popup.querySelector('.iconset_item--selected')){
							that.disableSelectButton();
						}
					}

					if(that.form = that.block.querySelector('.iconset_form')){
						that.formUrl = that.form.getAttribute('action') || location.href
					}

					that.bindEvents();
				}
			});
		},

		bindEvents: function(){
			var that = this;

			if(
				typeof that.onDocClick !== 'function' &&
				that.block
			){
				// live click on document
				that.onDocClick = function(e){
					if(!e){
						e = window.event;
					}

					var target = e.target || e.srcElement;

					var block = target.closest('#iconset-' + that.rand);
					if(block){
						// is click on delete button
						var buttonDelete = target.closest('.iconset_btn--delete');
						if(buttonDelete){
							var icon = buttonDelete.closest('.iconset_item');
							if(icon){
								var id = BX.data(buttonDelete.closest('.iconset_item'), 'id');
								var value = BX.data(buttonDelete.closest('.iconset_item'), 'value');
								that.deleteIcon(id, value, function(bSuccess){
									if(bSuccess){
										BX.addClass(icon, 'iconset_item--deleted');

										// wait animation 0.5s
										setTimeout(function(){
											BX.remove(icon);
										}, 490);

										if(that.popup){
											if(!that.popup.querySelector('.iconset_item--selected')){
												that.disableSelectButton();
											}
										}
									}
								});
							}
						}
						else{
							// is click on item
							var item = target.closest('.iconset_item');
							if(item){
								var items = Array.prototype.slice.call(item.parentNode.children);
								for(var i in items){
									if(items[i] === item){
										BX.addClass(items[i], 'iconset_item--selected');
									}
									else{
										BX.removeClass(items[i], 'iconset_item--selected');
									}
								}

								that.enableSelectButton();
							}
							else{
								// is click on load button
								var buttonLoad = target.closest('.iconset_btn--load');
								if(buttonLoad){
									if(that.form){
										that.form.dispatchEvent(new Event('submit', {bubbles: true}));
									}
								}
								else{
									var subtabs = target.closest('.adm-detail-subtabs');
									if(subtabs){
										var addIconTab = that.block.querySelector('.adm-detail-subtabs[id=view_tab_iconset_add_icon]');
										if(addIconTab){
											if(BX.hasClass(addIconTab, 'adm-detail-subtab-active')){
												that.disableSelectButton();
											}
											else{
												that.enableSelectButton();
											}
										}
									}
								}
							}
						}
					}
				}
				document.addEventListener('click', that.onDocClick);

				// sumbmit form
				if(that.form){
					that.onFormSubmit = function(e){
						if(!e){
							e = window.event;
						}

						var errorMessage = '';
						var fd = new FormData();

						fd.set('code', that.code);
						fd.set('value', that.form.querySelector('input[name=value]').value);
						fd.set('action', 'add_icon');

						var fileInputs = Array.prototype.slice.call(that.form.querySelectorAll('input[name=iconset_new]'));
						var fileInput = fileInputs.length ? fileInputs[fileInputs.length - 1] : null;
						if(fileInput){
							var bFile = fileInput.getAttribute('type') === 'file';

							if(bFile){
								var files = fileInput.files || [fileInput.value];
								var filename = files.length ? (files[0].name || files[0]) : '';
								var p = Math.max(filename.lastIndexOf('/'), filename.lastIndexOf('\\'));
    							if(p > 0){
							        filename = filename.substring(p + 1, filename.length);
    							}
							}
							else{
								var filename = fileInput.value;
							}

							var filename = bFile ? (files.length ? files[0].name : '') : fileInput.value;

							if(filename.length){
								if(that.config.validation_pattern){
									if(filename.match(new RegExp(that.config.validation_pattern, 'i')) === null){
										errorMessage = BX.message('ICONSET_ERROR_VALIDATION_FILE_BAD_NAME');
									}
								}
							}
							else{
								errorMessage = BX.message('ICONSET_ERROR_VALIDATION_FILE_EMPTY_VALUE');
								if(!bFile){
									// wait error animation 0.3s
									setTimeout(function(){
										that.resetFileInput();
									}, 300);
								}
							}

							if(!errorMessage.length){
								fd.append(
									bFile ? 'icon_file' : 'icon_path',
									bFile ? files[0] : fileInput.value
								);
							}
						}

						if(errorMessage.length){
							that.showError(errorMessage);
						}
						else{
							that.addIcon(fd, function(id){
								if(id){
									that.getIcon(id, function(response){
										if(response){
											var child = BX.create({
												tag: 'div',
												html: response,
											});
											child = child.querySelector('.iconset_item');
											if(child){
												that.openItemsTab();

												var wrap = that.block.querySelector('.iconset_wrap');
												if(wrap){
													wrap.scrollTop = 0;

													var emptyItem = wrap.querySelector('.iconset_item--empty');
													if(emptyItem){
														BX.insertAfter(child, emptyItem);
													}
													else{
											        	BX.prepend(child, wrap);
													}

													// wait animation 0.5s
										        	setTimeout(function(){
											        	BX.removeClass(child, 'iconset_item--added');
										        	}, 1000);
												}
											}
											else{
												that.showError(BX.message('ICONSET_ERROR_REQUEST'));
											}
										}
									});
								}
							});
						}
					}
				}
				that.form.addEventListener('submit', that.onFormSubmit);
			}
		},

		unbindEvents: function(){
			if(typeof this.onDocClick === 'function'){
				document.removeEventListener('click', this.onDocClick);
			}

			if(
				typeof this.onFormSubmit === 'function' &&
				this.form
			){
				this.form.removeEventListener('submit', this.onFormSubmit);
			}
		},

		showError: function(errorMessage){
			var bForm = this.block.querySelector('.adm-detail-subtab-active[id=view_tab_iconset_add_icon]') && this.form;
			var prependTo = bForm ? this.block.querySelector('div[id=iconset_add_icon]') : this.block.querySelector('div[id=iconset_items]');
			var error = BX.create({
				tag: 'div',
				attrs: {
					class: 'adm-info-message-wrap adm-info-message-red iconset_error',
				},
				style: {
					display: 'none',
				},
				html: '<div class="adm-info-message"><div class="adm-info-message-title"></div><div class="adm-info-message-icon"></div></div>',
			});

			BX.prepend(error, prependTo.querySelector('.adm-detail-content-item-block-view-tab'));
			if(error){
				error.querySelector('.adm-info-message-title').textContent = errorMessage;
				error.style.display = 'block';
				BX.addClass(error, 'iconset_error--visible');

				setTimeout(function(){
					BX.removeClass(error, 'iconset_error--visible');

					// wait animation 0.3s
					setTimeout(function(){
						BX.remove(error);
					}, 300);
				}, 2300);
			}
		},

		showLoader: function(){
			BX.addClass(this.form.closest('.iconset'), 'iconset--sending');
		},

		hideLoader: function(){
			BX.removeClass(this.form.closest('.iconset'), 'iconset--sending');
		},

		enableSelectButton: function(){
			if(this.selectButton){
				this.selectButton.removeAttribute('disabled');
			}
		},

		disableSelectButton: function(){
			if(this.selectButton){
				this.selectButton.setAttribute('disabled', '');
			}
		},

		openItemsTab: function(){
			var tab = this.block.querySelector('.adm-detail-subtabs[id=view_tab_iconset_items]');
			if(tab){
				tab.dispatchEvent(new Event('click', {bubbles: true}));
			}
		},

		openFormTab: function(){
			if(this.form){
				var tab = this.block.querySelector('.adm-detail-subtabs[id=view_tab_iconset_add_icon]');
				if(tab){
					tab.dispatchEvent(new Event('click', {bubbles: true}));
				}
			}
		},

		resetFileInput: function(){
			if(!this.form){
				return;
			}

			var openers = Array.prototype.slice.call(this.form.querySelectorAll('.add-file-popup-btn'));
			var opener = openers.length ? openers[openers.length - 1] : null;
			if(opener && typeof opener.OPENER === 'object'){
				var menu = opener.OPENER.GetMenu();
				if(typeof menu === 'object'){
					if(menu.DIV){
						var menuItem = menu.DIV.querySelector('.adm-menu-delete') ? menu.DIV.querySelector('.adm-menu-delete').parentNode : null;
						if(menuItem){
							menu.DIV.style.visibility = 'hidden';
							menu.Show();
							var interval = setInterval(function(){
								menuItem.dispatchEvent(new Event('click', {bubbles: true}));
								if(!opener.closest('.iconset')){
									menu.DIV.style.visibility = '';
									clearInterval(interval);
								}
							}, 100);
						}
					}
				}
			}
		},

		addIcon: function(data, callback){
			if(this.form){
				var that = this;

				if(data instanceof FormData){
					data.set('action', 'add_icon');
				}
				else{
					data.action = 'add_icon';
				}

				that.showLoader();
				BX.ajax({
			        url: that.formUrl,
			        method: 'POST',
			        data: data,
			        dataType: 'html',
			        processData: false,
			        preparePost: false,
			        emulateOnload: false,
			        start: true,
			        async: true,
			        cache: false,
			        onsuccess: function(response){
			        	errorMessage = '';

			        	try{
							var result = JSON.parse(response);

							if(result.error.length){
								errorMessage = result.error;
							}
							else if(!result.id){
								errorMessage = BX.message('ICONSET_ERROR_REQUEST');
							}
						}
						catch(e){
							errorMessage = BX.message('ICONSET_ERROR_REQUEST');
						}

						that.resetFileInput();
						that.hideLoader();

						if(errorMessage.length){
							that.showError(errorMessage);
							if(typeof callback === 'function'){
				        		callback(false);
				        	}
						}
						else{
							if(typeof callback === 'function'){
				        		callback(result.id);
				        	}
						}
			        },
			        onfailure: function(error){
			        	that.hideLoader();
			        	that.showError(BX.message('ICONSET_ERROR_REQUEST'));

			        	if(typeof callback === 'function'){
			        		callback(false);
			        	}
			        }
			    });
			}
			else{
				if(typeof callback === 'function'){
	        		callback(false);
	        	}
			}
		},

		deleteIcon: function(id, value, callback){
			var that = this;

			if(confirm(BX.message('ICONSET_CONFIRM_DELETE'))){
				var iconset_values = Array.prototype.slice.call(document.querySelectorAll('.iconset_value'));
				if(iconset_values){
					for(var i in iconset_values){
						var input = iconset_values[i].querySelector('input');
						if(input && input.value === value){
							this.showError(BX.message('ICONSET_ERROR_DELETE_ICON_IS_SAVED_AS_OPTION_VALUE'));

							if(typeof callback === 'function'){
				        		callback(false);
				        	}

				        	return;
						}
					}
				}

				that.showLoader();
				BX.ajax({
			        url: that.formUrl,
			        method: 'POST',
			        data: {
			        	action: 'delete_icon',
			        	code: that.code,
			        	id: id
			        },
			        dataType: 'html',
			        processData: false,
			        preparePost: true,
			        emulateOnload: false,
			        start: true,
			        async: true,
			        cache: false,
			        onsuccess: function(response){
						errorMessage = '';

			        	try{
							var result = JSON.parse(response);

							if(result.error.length){
								errorMessage = result.error;
							}
						}
						catch(e){
							errorMessage = BX.message('ICONSET_ERROR_REQUEST');
						}

						that.hideLoader();

						if(errorMessage.length){
							that.showError(errorMessage);
							if(typeof callback === 'function'){
				        		callback(false);
				        	}
						}
						else{
							if(typeof callback === 'function'){
				        		callback(true);
				        	}
						}
			        },
			        onfailure: function(error){
			        	that.hideLoader();
			        	that.showError(BX.message('ICONSET_ERROR_REQUEST'));

			        	if(typeof callback === 'function'){
			        		callback(false);
			        	}
			        }
				});
			}
		},

		getIcon: function(id, callback){
			var that = this;

			that.showLoader();
			BX.ajax({
		        url: that.formUrl,
		        method: 'POST',
		        data: {
		        	action: 'get_icon',
		        	code: that.code,
		        	id: id
		        },
		        dataType: 'html',
		        processData: false,
		        preparePost: true,
		        emulateOnload: false,
		        start: true,
		        async: true,
		        cache: false,
		        onsuccess: function(response){
					that.hideLoader();

		        	if(typeof callback === 'function'){
		        		callback(response);
		        	}
		        },
		        onfailure: function(error){
		        	that.hideLoader();
		        	that.showError(BX.message('ICONSET_ERROR_REQUEST'));

		        	if(typeof callback === 'function'){
		        		callback(false);
		        	}
		        }
			});
		}
	}

	// click on iconset icon value
	window.JIconset._onValueClick = function(e){
		if(!e){
			e = window.event;
		}

		BX.PreventDefault(e);

		var icon = this;
		if(icon){
			if(BX.hasClass(icon, 'iconset_value--readonly')){
				return false;
			}

			var code = BX.data(icon, 'code');
			var input = icon.parentNode.querySelector('input');
			var value = input.value;
			var iconsetUrl = '/bitrix/admin/aspro.max_iconset.php';
			var iconsetDialog = new BX.CAdminDialog({
				content_url: iconsetUrl,
				content_post: {
					code: code,
					value: value,
				},
				title: BX.message('ICONSET_POPUP_TITLE'),
				draggable: true,
				resizable: true,
				width: 300,
				min_width: 300,
				height: 142,
				min_height: 142,
				buttons: [
					{
						title: BX.message('ICONSET_POPUP_SELECT'),
						id: 'iconset_button--select',
						name: 'select',
						className: 'adm-btn-save',
						action: function(){
							var selected = this.parentWindow.DIV.querySelector('.iconset_item--selected');
							if(selected){
								var value = BX.data(selected, 'value');
								var content = selected.querySelector('.iconset_item_middle').innerHTML.trim();
								icon.querySelector('.iconset_value_wrap').innerHTML = content;
								icon.querySelector('input').value = value;
							}
							this.parentWindow.Close();
						}
					},
					{
						title: BX.message('ICONSET_POPUP_CLOSE'),
						id: 'iconset_button--cancel',
						name: 'close',
						action: function(){
							this.parentWindow.Close();
						}
					}
				]
			});

			// unset popup height on show
			BX.addCustomEvent(iconsetDialog, 'onWindowRegister', function(){
				BX.WindowManager.Get().DIV.querySelector('.bx-core-adm-dialog-content').style.height = '';
			});

			// unbind popup iconset events
			BX.addCustomEvent(iconsetDialog, 'onWindowClose', function(){
				var iconset = BX.WindowManager.Get().DIV.querySelector('.iconset');
				if(iconset){
					if(typeof iconset.iconset === 'object' && iconset.iconset){
						iconset.iconset.unbindEvents();
					}
				}
			});

			// show dialog
			iconsetDialog.Show();
		}
	}

	BX.ready(function(){
		BX.bindDelegate(document.body, 'click', {class: 'iconset_value'}, window.JIconset._onValueClick);
	});
}
