<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if ($arParams['DELAYED_BASKET'] != 'Y') {
    $arResult['ShowDelay'] = 'N';
    foreach ($arResult['BASKET_ITEM_RENDER_DATA'] as $key => $arItem) {
        if ($arItem['DELAYED']) {
            unset($arResult['BASKET_ITEM_RENDER_DATA'][$key]);
        }
    }
} else {
    $arResult['ShowDelay'] = 'Y';
    foreach ($arResult['BASKET_ITEM_RENDER_DATA'] as $key => $arItem) {
        if (!$arItem['DELAYED']) {
            unset($arResult['BASKET_ITEM_RENDER_DATA'][$key]);
        }
    }

    $arResult['TOTAL_RENDER_DATA'] = array();
}
$arResult['RESTRICTED'] = \BuyRestriction::checkRestrictionInBasket();

//формирование массива данных для екомерса
if (!empty($arResult['BASKET_ITEM_RENDER_DATA'])) {
    unset($arItem);
    $arEcomData = [];
    foreach ($arResult['BASKET_ITEM_RENDER_DATA'] as $index => &$arItem) {
        $ecomItem = [
            'item_name' => $arItem['NAME'],
            'item_id' => $arItem['ID'],
            'price' => $arItem['PRICE'],
            'item_list_name' => 'Корзина',
            'index' => $index + 1,
            'quantity' => $arItem['MEASURE_RATIO'],
        ];
        $arItem['ECOM_ITEM_DATA'] = \CUtil::PHPToJSObject($ecomItem);
        $arItem['ECOM_ITEM_JSON'] = json_encode($ecomItem, JSON_UNESCAPED_UNICODE);
        $arEcomData[] = $ecomItem;
    }
    if (!empty($arEcomData)) {
        $arResult['ECOM_DATA'] = $arEcomData;
    }
    $this->__component->SetResultCacheKeys(array("ECOM_DATA"));

    unset($index, $arItem, $ecomItem, $arEcomData);
    $arEcomData['sum'] = $arResult['allSum'];
    $arEcomData['currency'] = $arResult['CURRENCY'];
    foreach ($arResult['BASKET_ITEM_RENDER_DATA'] as $index => &$arItem) {
        $ecomItem = [
            'item_name' => $arItem['NAME'],
            'item_id' => $arItem['ID'],
            'price' => $arItem['PRICE'],
            'item_list_name' => 'Просмотр корзины',
            'index' => $index + 1,
            'quantity' => $arItem['MEASURE_RATIO'],
        ];
        $arEcomData['items'][] = $ecomItem;
    }
    $arResult['ECOM_BASKET_DATA'] = $arEcomData;
    $this->__component->SetResultCacheKeys(array("ECOM_BASKET_DATA"));
}
?>
