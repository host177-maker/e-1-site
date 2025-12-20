<div class="custom-popup">
    <div class="custom-popup__container">
        <div class="popup__content">
            <div class="popup__content-header">
                <div class="popup__content-inner">
                    <div class="popup__content-header-title"><?= (!empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_ORDER_TITLE'))) ? \Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_ORDER_TITLE'): 'Уведомление' ?></div>

                </div>
                <div class="close">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 1L14.9998 14.9998M14.9999 1L1.00006 14.9998" stroke="#555555" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
            <div class="popup__content-main">
                <div class="popup__content-inner">
                    <div class="opportunity-list">
                    <?= (!empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_ORDER_TEXT'))) ? \Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_ORDER_TEXT'): 'Уважаемый посетитель, город доставки отличается от вашего города, и цена доставки разная.
Пожалуйста смените город, чтобы оформить заказ' ?>
                    </div>
                    <div id="bx-popup-order-save-block-buttons" class="opportunity-btns">
                        <a href="#" class="btn btn-default opportunity-btn"><?= (!empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_ORDER_BUTTON_YES'))) ? \Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_ORDER_BUTTON_YES'): 'Сменить город' ?></a>
                        <a href="#" class="btn  opportunity-btn check"><?= (!empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_ORDER_BUTTON_NO'))) ? \Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_ORDER_BUTTON_NO'): 'Оформить заказ' ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>