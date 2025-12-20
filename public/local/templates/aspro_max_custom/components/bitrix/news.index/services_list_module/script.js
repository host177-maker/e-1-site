$( document ).ready(function() {
    $("body").on( "click", ".basket-delete-module", function() {
        let idProduct = $(this).parents('.services-items').find('.services-item-module').data('item');
        let itemDataDom = $(this).parents('.services-item-module');
        let itemData = [];
        itemData.PRODUCT_ID = itemDataDom.data('item');
        if (typeof(idProduct) != 'undefined') {
            $.ajax({
                url: arAsproOptions['SITE_DIR'] + 'ajax/item.php?delete_assembly=' + idProduct,
                type: 'post',
                success: function(html) {
                    updateBasketInfoServices(true);
                    setTimeout(()=>{
                        getActualBasketInAssemblyModule(null, null, null, true, idProduct, itemData.PRODUCT_ID);
                    },2500)
                }
            });
        }
    });
    $(document).on( "click", '.services-item-module div[data-entity="add_services_module_as"]', function() {
        let idProduct = $(this).find('[data-entity="id_service"]').data('href');
        let itemDataDom = $(this).parents('.services-item-module');
        let itemData = [];
        itemData.PRODUCT_ID = $(this).attr('data-id');
        itemData.OFFER = $(this).attr('data-offer');
        itemData.PRICE = $(this).attr('data-price');
        itemData.IBLOCK_SERVICE_ID = itemDataDom.data('iblockid');
        itemData.QUANTITY = itemDataDom.data('quantity');
        if (typeof(idProduct) != 'undefined') {
            //отключаем клик на чекбокс, чтоб запрос не задвоился при многократном нажатии
            $(this).css('pointer-events', 'none');
            // var services = $('.assembly-module[data-id="' + itemData.OFFER + '"]');
            var services = $('.basket-services-list-item-container[data-offer="' + itemData.OFFER + '"]').find(
                '.services-item[data-item_id="4095"]'
            );

            if (services.length) {
                $.ajax({
                    url: arAsproOptions['SITE_DIR'] + 'ajax/item.php?delete_assembly=' + idProduct,
                    data: {
                        parentId : itemData.OFFER,
                        iblockid: itemData.IBLOCK_SERVICE_ID,
                        quantity: itemData.QUANTITY,
                        module: true,
                        propOffer: itemData.OFFER
                    },
                    type: 'post',
                    success: function(html) {
                        updateBasketInfoServices(true);
                        setTimeout(()=>{
                            getActualBasketInAssemblyModule(null, null, null, true, idProduct, itemData.PRODUCT_ID);
                        },2500)
                    }
                });
            } else {
                
                console.log('service.list.template', itemData);
                
                $.ajax({
                    url: arAsproOptions['SITE_DIR'] + 'ajax/item.php?add_service=' + idProduct,
                    type: 'post',
                    data: {
                        parentId : itemData.OFFER,
                        iblockid: itemData.IBLOCK_SERVICE_ID,
                        quantity: itemData.QUANTITY,
                        module: true,
                        propOffer: itemData.OFFER,
                        priceModule: itemData.PRICE
                    },
                    success: function(html) {
                        updateBasketInfoServices(true);
                        setTimeout(()=>{
                            getActualBasketInAssemblyModule(null, null, null, false, idProduct, itemData.OFFER);
                        },2500)
                    }
                });
            }
        }
    } );
});