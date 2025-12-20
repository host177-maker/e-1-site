BX.addCustomEvent('onAjaxSuccess', addblock);

var pecomEcomm = {
    widget: {
        // url: 'http://pecom-iframe-master.loc',
        // url: 'https://pec-widget.axbit.ru',
        url: 'https://calc.pecom.ru/iframe/e-store-calculator',
        loadTimeout: 10,
        isLoad: false,
        isLoadFail: false,
        isFirstLoad: true,
    },
    cost: '',
    days: '',
    addressSelected: 0,
    addressFieldNode: '',
    callbackFunction: function(data) {
        this.widget.isLoad = true;
        console.log('call data', data);

        $('#pec_address_txt').html(data.toAddress);
        $(this.addressFieldNode).prop('readonly', true);
        console.log('addressFieldNode', this.addressFieldNode);
        // $('#bx-soa-properties textarea[autocomplete="address"]').val(data.toAddress);
        // $('#bx-soa-properties-hidden textarea[autocomplete="address"]').val(data.toAddress);

        this.cost = data.price + ' ₽';
        $('#pec_price').val(this.cost);
        $('#pec_price_txt').html(this.cost);

        // BX.Sale.OrderAjaxComponent.result.DELIVERY[deliveryId].PRICE_FORMATED = this.cost;

        this.days = data.term.days;
        $('#pec_days').val(this.days);
        $('#pec_days_txt').html(this.days);

        $('#pec_to_address').val(data.toAddress);
        $('#pec_to_uid').val(data.toDepartmentUid);
        $('#pec_to_type').val(data.toAddressType);

        $('#pec_widget_data').val(JSON.stringify(data));

        this.addressSelected = data.isFirstRequest ? 0 : 1;
        $('#pec_address_selected').val(this.addressSelected);

        if ($('#pec_cost-delivery_error').length) {
            $('#pec_cost-delivery_error').val('');
        }

        this.replacePecCostBloks();
        this.isWidgetReload = true;
        $('#bx-soa-order-form #pec_cost_delivery_error').val('');
        BX.Sale.OrderAjaxComponent.endLoader();
        BX.Sale.OrderAjaxComponent.sendRequest();
    },
    callbackError(error) {
        console.log('message error', error);
        BX.Sale.OrderAjaxComponent.endLoader();
        if (!this.widget.isLoad) {
            // pecomEcomm.widget.isLoadFail = true;
            this.widget.isLoad = true;
            $('#bx-soa-order-form').append('<input type="hidden" id="pec_cost_delivery_error" name="pec_cost_delivery_error" value="Y">');
            BX.Sale.OrderAjaxComponent.sendRequest();
        }
    },
    hidePecCostBloks: function() {
        $('.bx-soa-pp-price').html('');
        // $('.bx-soa-pp-list-description').html('');
        $('.bx-soa-price-free').html('');
        $('.bx-soa-pp-company.bx-selected .bx-soa-pp-delivery-cost').html('');
        $('.bx-soa-cart-total .bx-soa-cart-d').hide();
        $('.bx-soa-cart-total:first .bx-soa-cart-d:first').show();
        $('.bx-soa-cart-total:last .bx-soa-cart-d:first').show();
    },
    replacePecCostBloks: function() {
        $('.bx-soa-pp-price').html(this.cost);
        $('.bx-soa-price-free').html(this.cost);
        $('.bx-soa-pp-company.bx-selected .bx-soa-pp-delivery-cost').html(this.cost);
        // $('.bx-soa-pp-list-description:first').html(this.cost);
        // $('.bx-soa-pp-list-description:last').html(this.days);
        $('.bx-soa-cart-total .bx-soa-cart-d').show();
    },
    isChecked: false,
    params: {},
    isWidgetReload: false,
};

function addblock(a,b,c) {
    if (!a.hasOwnProperty('order')) return;

    if (pecomEcomm.widget.isLoadFail) {
        console.log('widget load is fail');
        hideHtmlBlockDeliveryPec();
        hidePecPickup();
        return;
    }

    console.log('ajax', a, b, c);

    for (var key in a.order.ORDER_PROP.properties) {
        if (a.order.ORDER_PROP.properties[key].CODE == 'ADDRESS') {
            pecomEcomm.addressFieldNode = '#soa-property-' + a.order.ORDER_PROP.properties[key].ID;
        }
    }

    if (document.querySelector('pecomEcomm-delivery-widget')) return;

    if (!a.hasOwnProperty('order')) return;

    console.log('a.order', a.order);
    var isDeliveryPec = false;
    a.order.DELIVERY.forEach(function (item) {
        console.log('item', item);
        if (item.hasOwnProperty('CHECKED') && item.CHECKED == 'Y'
            && item.hasOwnProperty('IS_PEC_DELIVERY') && item.IS_PEC_DELIVERY) {
            isDeliveryPec = true;
        }
    });
    if (!isDeliveryPec) {
        hideHtmlBlockDeliveryPec();
        hidePecPickup();
    } else {
        // $('#bx-soa-pickup').show().addClass('bx-active').find('.bx-soa-section-title').html('АДрес доставки');
        // $('#bx-soa-pickup').find('.bx-soa-section-content').html('Widget');
        addHtmlBlockDeliveryPec();
    }
    pecomEcomm.isWidgetReload = false;
}

function hidePecPickup($addressTo, $weight, $volume, $declaredAmount) {
    $('#pecWidjetOrig').hide();
    $(pecomEcomm.addressFieldNode).prop('readonly', false);
    $(pecomEcomm.addressFieldNode).prop('readonly', false);
}

function addHtmlBlockDeliveryPec() {

    $(pecomEcomm.addressFieldNode).prop('readonly', true);
    $(pecomEcomm.addressFieldNode).prop('readonly', true);

    var urlPecWidget = 'https://calc.pecom.ru/iframe/e-store-calculator';
    // var urlPecWidget = 'https://pec-widget.axbit.ru/calculator';
    // var urlPecWidget = 'http://pecom-iframe-master.loc/calculator';

    $.ajax({
        url: '/bitrix/js/pecom.ecomm/ajax.php',
        type: 'post',
        data: {method: 'orderParams', sessid: BX.bitrix_sessid()},
        async: false,
        success: function (data) {
            pecomEcomm.params = JSON.parse(data);
            console.log('pecomEcomm.params', pecomEcomm.params);

        }
    })

    var fromAddress = pecomEcomm.params.FROM_ADDRESS, fromDepartment;
    var fromType = pecomEcomm.params.FROM_TYPE == 'pzz' ? 0 : 1;
    // if (fromType == 'pzz') {
    // fromDepartment = fromAddress;
    // fromAddress = '';
    // }

    var address = pecomEcomm.params.ADDRESS;
    var price = pecomEcomm.params.PRICE;
    var volume = pecomEcomm.params.VOLUME;
    var weight = pecomEcomm.params.WEIGHT;
    var selfPack = pecomEcomm.params.SELF_PACK;
    var costOut = pecomEcomm.params.PEC_COST_OUT;
    var pecPrice = '';//pecomEcomm.params.PEC_PRICE;
    var pecDays = '';//pecomEcomm.params.PEC_DAYS;

    console.log('costOut', costOut);
    console.log('pecomEcomm', pecomEcomm);
    if (1 || costOut) {
        if (!pecomEcomm.cost) {
            pecomEcomm.hidePecCostBloks();
        } else {
            pecomEcomm.replacePecCostBloks();
        }
    }

    var transportationType = '';
    switch (pecomEcomm.params.transportationType) {
        case 'avia': transportationType = '&transportation-type=avia'; break;
        case 'easyway': transportationType = '&transportation-type=easyway'; break;
    }

    var txt = `
        <div id="bx-soa-pickupPEK-hidden" class="bx-soa-section">
        <div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
        <div class="bx-soa-section-title col-sm-9">
        <span class="bx-soa-section-title-count"></span>Адрес доставки
        </div>
        <div class="col-xs-12 col-sm-3 text-right"><a href class="bx-soa-editstep">изменить</a></div>
        </div>
        
        <div class="bx-soa-section-content">
        
        <div style="clear: both;"></div>
        
        </div>
        </div>
    `;
    var txt_h = `
        <div id="bx-soa-pickupPEK" class="bx-soa-section bx-active" data-visited="true">
        <div class="bx-soa-section-title-container d-flex justify-content-between align-items-center flex-nowrap">
        <div class="bx-soa-section-title col-sm-9">
        <span class="bx-soa-section-title-count"></span>Адрес доставки
        </div>
        <div class="col-xs-12 col-sm-3 text-right"><a href class="bx-soa-editstep">изменить</a></div>
        </div>
        
        <div class="bx-soa-section-content">
        <input type="hidden" id="pec_address" name="pec_address" value="${address}">
        <input type="hidden" id="pec_type" name="pec_type" value="">
        <input type="hidden" id="pec_price" name="pec_price" value="${pecPrice}">
        <input type="hidden" id="pec_days" name="pec_days" value="${pecDays}">
        <input type="hidden" id="pec_to_address" name="pec_to_address" value="">
        <input type="hidden" id="pec_widget_data" name="pec_widget_data" value="">
        <input type="hidden" id="pec_to_uid" name="pec_to_uid" value="">
        <input type="hidden" id="pec_to_type" name="pec_to_type" value="">
        <input type="hidden" id="pec_address_selected" name="pec_address_selected" value="${pecomEcomm.addressSelected}">
        
        <div class="pec__hidden-block">
        <span>Адрес: <span id="pec_address_txt"> </span></span><br>
        <span>Стоимость: <span id="pec_price_txt">${pecPrice}</span></span><br>
        <span>Срок: от <span id="pec_days_txt">${pecDays}</span></span>
        </div>
        <div class="pec__show-block" style="display:none;">
        <iframe id="pecWidjetOrig" src="${urlPecWidget}?address-from=${fromAddress}&intake=${fromType}&address-to=${address}&delivery=1&weight=${weight}&volume=${volume}&declared-amount=${price}&packing-rigid=${selfPack}${transportationType}&auto-run=1" width="100%" height="552" frameborder="0"></iframe>
        </div>
        
        <div style="clear: both;"></div>
        
        </div>
        </div>
    `;

    console.log('iframe src', `ddress-to=${address}&delivery=1&weight=${weight}&volume=${volume}&declared-amount=${price}&packing-rigid=${selfPack}${transportationType}&auto-run=1`);

    if ($('#bx-soa-pickupPEK').length && pecomEcomm.isWidgetReload == false && !pecomEcomm.addressSelected) {
        //$('#pecWidjetOrig').attr('src', `${urlPecWidget}?address-from=Россия, Москва&intake=1&address-to=${address}&delivery=1&weight=${weight}&volume=${volume}&declared-amount=${price}&packing-rigid=${selfPack}${transportationType}&auto-run=1`);
        //BX.Sale.OrderAjaxComponent.startLoader();
    }
    if ($('#bx-soa-pickupPEK-hidden').length) {
        // $('#bx-soa-pickupPEK-hidden').remove();
    }
    if (!$('#bx-soa-pickupPEK').length) {
        $('#bx-soa-delivery').after(txt_h);
        // this.pecBlockNode = $('#bx-soa-pickupPEK');
        // this.pecHiddenBlockNode =  $('#bx-soa-pickupPEK-hidden');
        // BX.Sale.OrderAjaxComponent.getBlockFooter(node.querySelector('#bx-soa-pickupPEK-hidden .bx-soa-section-content'));
        initBlockPec($('#bx-soa-pickupPEK-hidden .bx-soa-section-content'));
        console.log('start loader');
        BX.Sale.OrderAjaxComponent.startLoader();
    }
    if (!$('#bx-soa-pickupPEK-hidden').length) {
        $('#bx-soa-delivery-hidden').after(txt);
    }

    //$('.bx-soa-pp-delivery-cost').html('');
    // $('.bx-soa-pp-list-description').html('');
    // $('.bx-soa-pp-list-description').html('');

    if (pecomEcomm.widget.isFirstLoad) {
        // Adding an event which wating for a message from the map that it's ready
        var widgetListener = window.addEventListener('message', (event) => {
            console.log('message pecomEcomm.widget.isLoadFail', pecomEcomm.widget.isLoadFail);
            if (pecomEcomm.widget.isLoadFail || !event.data.hasOwnProperty('pecDelivery')) {
                return;
            }
            if (event.data.pecDelivery.hasOwnProperty('result')) {
                pecomEcomm.callbackFunction(event.data.pecDelivery.result);
            }
            if (event.data.pecDelivery.hasOwnProperty('error')) {
                // alert(event.data.pecomEcomm.error);
                pecomEcomm.callbackError(event.data.pecDelivery.error);
            }
        });
        setTimeout(function () {
            if (!pecomEcomm.widget.isLoad && !pecomEcomm.widget.isLoadFail) {
                pecomEcomm.widget.isLoadFail = true;
                widgetListener = null;
                pecomEcomm.callbackError('срок загрузки виджета вышел');
            }
        }, pecomEcomm.widget.loadTimeout * 1000);

        pecomEcomm.widget.isFirstLoad = false;
    }

    if (!$('#bx-soa-pickupPEK').hasClass('bx-active')) {
        $('#bx-soa-pickupPEK').addClass('bx-active');
        $('#bx-soa-pickupPEK').show();
    }
}

function hideHtmlBlockDeliveryPec() {
    if ($('#bx-soa-pickupPEK').length) {
        $('#bx-soa-pickupPEK').hide();
        $('#bx-soa-pickupPEK').removeClass('bx-active');
    }
}

function initBlockPec () {
    BX.Sale.OrderAjaxComponentExt.getBlockFooterPec($('#bx-soa-pickupPEK .bx-soa-section-content'));
    $('#bx-soa-pickupPEK .pec__show-block').hide();
}

BX.ready(function() {

    // var initParent = BX.Sale.OrderAjaxComponent.init,
    //     getBlockFooterParent = BX.Sale.OrderAjaxComponent.getBlockFooter,
    //     editOrderParent = BX.Sale.OrderAjaxComponent.editOrder
    // ;

    var showParent = BX.Sale.OrderAjaxComponent.show,
        fadeParent = BX.Sale.OrderAjaxComponent.fade;

    console.log(BX.Sale.OrderAjaxComponent.options);

    BX.namespace('BX.Sale.OrderAjaxComponentExt');

    BX.Sale.OrderAjaxComponentExt = BX.Sale.OrderAjaxComponent;

    BX.Sale.OrderAjaxComponentExt.show = function(node) {
        showParent.apply(this, arguments);
        console.log('node', node);
        if (node.id == 'bx-soa-pickupPEK') {
            this.showBlockPec();
        }
    }

    BX.Sale.OrderAjaxComponentExt.fade = function(node) {
        fadeParent.apply(this, arguments);
        if (node.id == 'bx-soa-pickupPEK') {
            this.fadeBlockPec();
        }
    }

    BX.Sale.OrderAjaxComponentExt.fadeBlockPec = function() {
        console.log('pecomEcomm block fade');
        $('#bx-soa-pickupPEK .pec__hidden-block').show();
        $('#bx-soa-pickupPEK .pec__show-block').hide(200);
    }

    BX.Sale.OrderAjaxComponentExt.showBlockPec = function() {
        console.log('pecomEcomm block show');
        $('#bx-soa-pickupPEK .pec__hidden-block').hide();
        $('#bx-soa-pickupPEK .pec__show-block').show();
        $('#pecWidjetOrig').show();
    }

    BX.Sale.OrderAjaxComponentExt.getBlockFooterPec = function (node) {
        var buttons = [];

        buttons.push(
            BX.create('button', {
                props: {
                    href: 'javascript:void(0)',
                    className: 'btn btn-outline-secondary pl-3 pr-3'
                },
                html: this.params.MESS_BACK,
                events: {
                    click: BX.proxy(this.clickPrevAction, this)
                }
            })
        );

        buttons.push(
            BX.create('button', {
                props: {href: 'javascript:void(0)', className: 'pull-right btn btn-primary pl-3 pr-3'},
                html: this.params.MESS_FURTHER,
                events: {click: BX.proxy(this.clickNextAction, this)}
            })
        );

        node.append(
            BX.create('DIV', {
                props: {className: 'row bx-soa-more pec__show-block'},
                children: [
                    BX.create('DIV', {
                        props: {className: 'bx-soa-more-btn col'},
                        children: buttons
                    })
                ]
            })
        );

    };

    if (pecomEcomm.isChecked) {
        addHtmlBlockDeliveryPec();
    }

});