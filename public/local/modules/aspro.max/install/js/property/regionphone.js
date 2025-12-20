if(typeof window.JRegionPhone === 'undefined'){
	window.JRegionPhone = function(tableId){
		this.tableId = tableId;

		this.table = false;
		this.tbody = false;

		this.eventListners = [];

		this.init();
	}

	window.JRegionPhone.prototype = {
		init: function(){
			var that = this;

			BX.ready(function(){
				if(that.table = BX(that.tableId)){
					that.table.regionphone = that;

					that.tbody = that.table.querySelector('tbody');

					that.updateControls();

					that.bindEvents();
				}
			});
		},

		bindEvents: function(){
			var that = this;

			if(
				typeof that.onButtonDeleteClick !== 'function' &&
				that.table
			){
				// click on delete button
				that.onButtonDeleteClick = function(e){
					if(!e){
						e = window.event;
					}

					BX.PreventDefault(e);

					var table = this.closest('#' + that.tableId);
					if(table){
						var item = this.closest('.aspro_property_regionphone_item');
						if(item){
							var row = item.closest('tr');
							if(row){
								if(that.isHasOneRow()){
									var inputs = Array.prototype.slice.call(row.querySelectorAll('input'));
									for(var i in inputs){
										inputs[i].value = '';
									}

									var icons_wraps = Array.prototype.slice.call(row.querySelectorAll('.iconset_value_wrap'));
									for(var i in icons_wraps){
										BX.clean(icons_wraps[i]);
									}

									that.updateControls();
								}
								else{
									var wrapper = item.querySelector('.wrapper')
									if(wrapper){
										BX.addClass(wrapper, 'no_drag');
									}

									BX.addClass(item, 'aspro_property_regionphone_item--deleted');

									// wait animation 0.5s
									setTimeout(function(){
										BX.remove(row);

										that.updateControls();
									}, 490);
								}
							}
						}
					}
				}
				BX.bindDelegate(that.table, 'click', {class: 'remove'}, that.onButtonDeleteClick);

				// click on iconset icon value
				if(typeof window.JIconset._onValueClick === 'function'){
					if(that.table.closest('.main-grid-editor-container')){
						BX.bindDelegate(that.table, 'click', {class: 'iconset_value'}, window.JIconset._onValueClick);
					}
				}

				// drag rows
				if(typeof Sortable === 'function'){
					if(that.tbody){
						Sortable.create(that.tbody, {
							handle: '.drag',
							animation: 150,
							forceFallback: true,
							filter: '.no_drag',
							onStart: function(evt){
								window.getSelection().removeAllRanges();
							},
							onMove: function(evt){
								return evt.related.querySelector('.no_drag') === null && evt.related.querySelector('.aspro_property_regionphone_item') !== null;
							},
							onUpdate: function(evt){
								try{
									var keys = [];
									var inputsNames = [];
									var rows = Array.prototype.slice.call(that.tbody.querySelectorAll('tr'));
									for(var j in rows){
										keys.push(j * 1);

										var names = [];
										var inputs = Array.prototype.slice.call(rows[j].querySelectorAll('input'));
										for(var k in inputs){
											names.push(inputs[k].getAttribute('name'));
										}
										inputsNames.push(names);
									}

									var k = evt.oldIndex;
									do{
										keys[k] = (k == evt.oldIndex ? evt.newIndex : (evt.newIndex > evt.oldIndex ? k - 1 : k + 1)) ;
										evt.newIndex > evt.oldIndex ? ++k : --k;
									}
									while(evt.newIndex > evt.oldIndex ? k <= evt.newIndex : k >= evt.newIndex);

									for(var j in rows){
										if(keys[j] != j){
											var inputs = Array.prototype.slice.call(rows[j].querySelectorAll('input'));
											for(var k in inputs){
												inputs[k].setAttribute('name', inputsNames[keys[j]][k]);
											}
										}
									}
								}
								catch(e){
									console.error(e);
								}
							}
						});
					}
				}

				// title change
				window.JRegionPhone._bindTitleChange(that.table);
			}
		},

		unbindEvents: function(){
			if(
				typeof this.onButtonDeleteClick === 'function' &&
				this.table
			){
				this.table.removeEventListener('click', this.onButtonDeleteClick);
			}
		},

		getItemsCount: function(){
			if(this.table){
				return Array.prototype.slice.call(
						this.table.querySelectorAll('tr .aspro_property_regionphone_item')
					).length -
					Array.prototype.slice.call(
						this.table.querySelectorAll('tr .aspro_property_regionphone_item>.wrapper.has_title')
					).length;
			}

			return 0;
		},

		isHasOneRow: function(){
			return this.getItemsCount() === 1;
		},

		updateControls: function(){
			if(this.table){
				if(this.isHasOneRow()){
					var rows = Array.prototype.slice.call(this.table.querySelectorAll('tr'));
					for(var i in rows){
						var item = rows[i].querySelector('.aspro_property_regionphone_item');
						if(item){
							if(!item.querySelector('.wrapper.has_title')){
								BX.addClass(item, 'aspro_property_regionphone_item--hiddendrag');

								return;
							}
						}
					}
				}
				else{
					BX.removeClass(this.table.querySelector('.aspro_property_regionphone_item--hiddendrag'), 'aspro_property_regionphone_item--hiddendrag');
				}
			}
		}
	}

	// parse email
	window.JRegionPhone._parseEmail = function(email){
		var re = /(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/;
	    var matches = email.toLowerCase().match(re);

	    return matches ? matches[0] : '';
	}

	window.JRegionPhone._setHref = function(title, href){
		if(
			title &&
			typeof title === 'object' &&
			href &&
			typeof href === 'object'
		){
			var href_value = href.value.trim();
			var bCanSet = !href_value.length || href_value.indexOf('mailto:') === 0 || href_value.indexOf('tel:') === 0;
			if(bCanSet){
				href_value = '';

				var title_value = title.value.trim();
				var email = window.JRegionPhone._parseEmail(title_value);
				if(!!email.length){
					href_value = 'mailto:' + email;
				}
				else{
					var phone = title_value.replace(/[^+0-9]/g, '');
					var bPlus = phone[0] === '+';
					phone = phone.replace(/[^0-9]/g, '');
					if(phone.length){
						phone = (bPlus ? '+' : '') + phone;
						if(phone.length){
							href_value = 'tel:' + phone;
						}
					}
				}

				href.value = href_value;
			}
		}
	}

	window.JRegionPhone._bindTitleChange = function(table){
		if(
			table &&
			typeof table === 'object'
		){
			BX.bindDelegate(table, 'keydown', {tag: 'input', attribute: {type: 'text'}}, function(e){
				if(!e){
					e = window.event;
				}

				if(e.which == 255){
					return;
				}

				if(
					this.getAttribute('name').indexOf('VALUE') !== -1 &&
					this.getAttribute('name').indexOf('PHONE') !== -1
				){
					var inner_wrapper = this.closest('.inner_wrapper');
					if(inner_wrapper){
						var inputs = Array.prototype.slice.call(inner_wrapper.querySelectorAll('input[type=text]'));
						if(inputs){
							for(var i in inputs){
								if(inputs[i].getAttribute('name').indexOf('HREF') !== -1){
									var href = inputs[i];
									var title = this;

									setTimeout(function(){
										window.JRegionPhone._setHref(title, href);
									}, 50);

									break;
								}
							}
						}
					}
				}
			});

			BX.bindDelegate(table, 'cut', {tag: 'input', attribute: {type: 'text'}}, function(e){
				if(!e){
					e = window.event;
				}

				if(
					this.getAttribute('name').indexOf('VALUE') !== -1 &&
					this.getAttribute('name').indexOf('PHONE') !== -1
				){
					var inner_wrapper = this.closest('.inner_wrapper');
					if(inner_wrapper){
						var inputs = Array.prototype.slice.call(inner_wrapper.querySelectorAll('input[type=text]'));
						if(inputs){
							for(var i in inputs){
								if(inputs[i].getAttribute('name').indexOf('HREF') !== -1){
									var href = inputs[i];
									var title = this;

									setTimeout(function(){
										window.JRegionPhone._setHref(title, href);
									}, 50);

									break;
								}
							}
						}
					}
				}
			});

			BX.bindDelegate(table, 'paste', {tag: 'input', attribute: {type: 'text'}}, function(e){
				if(!e){
					e = window.event;
				}

				if(
					this.getAttribute('name').indexOf('VALUE') !== -1 &&
					this.getAttribute('name').indexOf('PHONE') !== -1
				){
					var inner_wrapper = this.closest('.inner_wrapper');
					if(inner_wrapper){
						var inputs = Array.prototype.slice.call(inner_wrapper.querySelectorAll('input[type=text]'));
						if(inputs){
							for(var i in inputs){
								if(inputs[i].getAttribute('name').indexOf('HREF') !== -1){
									var href = inputs[i];
									var title = this;

									setTimeout(function(){
										window.JRegionPhone._setHref(title, href);
									}, 50);

									break;
								}
							}
						}
					}
				}
			});
		}
	}

	BX.ready(function(){
		// add new row
		BX.addCustomEvent(window, 'onAddNewRowBeforeInner', function(htmlObject){
			if(htmlObject && typeof htmlObject === 'object'){
				if(htmlObject['html'] && htmlObject['html'].length){
					if(htmlObject['html'].indexOf('aspro_property_regionphone_item') !== -1){
						var row = BX.create({
							tag: 'div',
							html: htmlObject['html'],
						});

						// remove inputs value
						var inputs = Array.prototype.slice.call(row.querySelectorAll('input'));
						for(var i in inputs){
							inputs[i].value = '';
							inputs[i].setAttribute('value', ''); // need!
						}

						// remove icons
						var icons_wraps = Array.prototype.slice.call(row.querySelectorAll('.iconset_value_wrap'));
						for(var i in icons_wraps){
							BX.clean(icons_wraps[i]);
						}

						htmlObject['html'] = row.innerHTML.replace('aspro_property_regionphone_item--hiddendrag', '');

						// fix bitrix bug (input name of new row is name of last exist row, without n0/n1/n2..)
						var name = htmlObject['html'].match(new RegExp('name="([^"]+)"', ''));
						if(name !== null){
							var bAdminList = name[1].match(new RegExp('^FIELDS\\[', ''));
							if(bAdminList){
								var valueId = name[1].match(new RegExp('FIELDS\\[([^\\]]*)\\]\\[(PROPERTY_\\d+)\\]\\[(n\\d+)\\]', 'i'));
								if(valueId === null){
									htmlObject['html'] = htmlObject['html'].replace(new RegExp('FIELDS\\[([^\\]]*)\\]\\[(PROPERTY_\\d+)\\]\\[([^\\]]*)\\]', 'ig'), 'FIELDS[$1][$2][n0]');
								}
							}
							else{
								var valueId = name[1].match(new RegExp('PROP\\[\\d+\\]\\[(n\\d+)\\]', 'i'));
								if(valueId === null){
									htmlObject['html'] = htmlObject['html'].replace(new RegExp('PROP\\[(\\d+)\\]\\[([^\\]]*)\\]', 'ig'), 'PROP[$1][n0]');
								}
							}
						}
						name = htmlObject['html'].match(new RegExp('name="([^"]+)"', ''));

						BX.remove(row);

						setTimeout(function(){
							var input = document.querySelector('input[name="' + name[1] + '"]');
							if(input){
								var table = input.closest('table');
								if(table){
									if(typeof table.regionphone === 'object' && table.regionphone){
										table.regionphone.updateControls();
									}
								}
							}
						}, 100);
					}
				}
			}
		});

		// edit property value from admin list
		BX.addCustomEvent(window, 'grid::thereeditedrows', function(){
			var adminRows = BX.Main.gridManager.data[0].instance.rows.getSelected();
			for(var i in adminRows){
				var adminRow = adminRows[i].node;
				if(adminRow){
					var tablesIds = [];
					var items = Array.prototype.slice.call(adminRow.querySelectorAll('.aspro_property_regionphone_item--admlistedit'));
					for(var i in items){
						var table = items[i].closest('table');
						if(table){
							if(typeof table.regionphone === 'undefined'){
								new JRegionPhone(table.id);
							}
						}
					}
				}
			}
		});
	});
}
