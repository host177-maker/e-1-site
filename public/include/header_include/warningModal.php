<div class="warning-modal" id="warning-modal">
    <div class="warning-modal__box">
        <div class="warning-modal__title"><?= !empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_WARNING_TITLE')) ? \Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_WARNING_TITLE') : 'На сайте идут технические работы'?></div>
        <div class="warning-modal__info">
            <?= !empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_WARNING_TEXT')) ? \Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_WARNING_TEXT') : 'Некоторые функции могут работать некорректно, но вы все еще можете пользоваться сайтом.'?>
        </div>
        <div class="warning-modal__bottom">
            <a href="#" class="btn" id="warning-btn_close"><?= !empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_WARNING_BUTTON')) ? \Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_MODAL_WARNING_BUTTON') : 'Ок'?></a>
        </div>
    </div>
</div>