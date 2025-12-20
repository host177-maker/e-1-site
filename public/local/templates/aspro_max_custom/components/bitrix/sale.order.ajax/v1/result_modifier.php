<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */

global $arRegion;
//получаем из региона включена ли опция выводить одну доставку Деловые линии
if ($arRegion["PROPERTY_SHOW_DELIVERY_BUSINESS_LINE_VALUE"] === 'Y' ||
    $arRegion["PROPERTY_SHOW_PICKUP_VALUE"] === 'Y' ||
    $arRegion["PROPERTY_SHOW_OWN_DELIVERY_VALUE"] === 'Y') {
    if ($arRegion["PROPERTY_SHOW_DELIVERY_BUSINESS_LINE_VALUE"] !== 'Y') {
        $deliveryId = \Cosmos\Config::getInstance()->getParam("DELIVERY_BUSINESS_LINE")["ID"];
        $deliveryName = \Cosmos\Config::getInstance()->getParam("DELIVERY_BUSINESS_LINE")["NAME"];
        foreach ($arResult["data"] as $key => $arDeliery) {
            if ($arDeliery['id'] === $deliveryId || $arDeliery['title'] === $deliveryName) {
                unset($arResult["data"][$key]);
            }
        }
    }
    if ($arRegion["PROPERTY_SHOW_PICKUP_VALUE"] !== 'Y') {
        $deliveryId = \Cosmos\Config::getInstance()->getParam("PICKUP")["ID"];
        $deliveryName = \Cosmos\Config::getInstance()->getParam("PICKUP")["NAME"];
        foreach ($arResult["data"] as $key => $arDeliery) {
            if ($arDeliery['id'] === $deliveryId || $arDeliery['title'] === $deliveryName) {
                unset($arResult["data"][$key]);
            }
        }
    }
    if ($arRegion["PROPERTY_SHOW_OWN_DELIVERY_VALUE"] !== 'Y') {
        $deliveryId = \Cosmos\Config::getInstance()->getParam("OWN_DELIVERY")["ID"];
        $deliveryName = \Cosmos\Config::getInstance()->getParam("OWN_DELIVERY")["NAME"];
        foreach ($arResult["data"] as $key => $arDeliery) {
            if ($arDeliery['id'] === $deliveryId || $arDeliery['title'] === $deliveryName) {
                unset($arResult["data"][$key]);
            }
        }
    }
}
//подставляем город
if (!empty($arRegion["NAME"])) {
    foreach ($arResult['JS_DATA']["ORDER_PROP"]["properties"] as $kProp => $arProp) {
        if ($arProp["CODE"] === "CITY_NEW") {
            $arResult['JS_DATA']["ORDER_PROP"]["properties"][$kProp]["VALUE"] = $arRegion["NAME"];
            $arResult['CITY'] = $arRegion["NAME"];
        }
        //удаляем из js оформления заказа поле ADDRESS, но заполним его для 1С в событии сохранения заказа
        if ($arProp["CODE"] === "ADDRESS") {
            unset($arResult['JS_DATA']["ORDER_PROP"]["properties"][$kProp]);
        }
        //обнуляем значения для свойcтв при повторном заказе, чтобы был только город, а улица, дом, пустые(одинаковые значения у полей, везде улица, из прошлого заказа берется)
        if ($arProp["CODE"] === "STREET" || $arProp["CODE"] === "HOUSE"  || $arProp["CODE"] === "HOUSING" || $arProp["CODE"] === "APARTMENT") {
            if (!empty($arResult['JS_DATA']["ORDER_PROP"]["properties"][$kProp]["VALUE"])) {
                $arResult['JS_DATA']["ORDER_PROP"]["properties"][$kProp]["VALUE"] = '';
            }
        }
    }
}

$component = $this->__component;
$component::scaleImages($arResult['JS_DATA'], $arParams['SERVICES_IMAGES_SCALING']);