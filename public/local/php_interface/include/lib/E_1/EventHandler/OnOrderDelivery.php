<?

namespace E_1\EventHandler;

class OnOrderDelivery
{
    /**
     * Метод устанавивает город при обновлении на странице заказа
     * @param array
     * @return array
     */

    public static function OnSaleComponentOrderJsDataHandler(&$arResult, &$arParams)
    {
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
        
        if(!empty($arRegion["NAME"])) {
            foreach ($arResult['JS_DATA']["ORDER_PROP"]["properties"] as $kProp => $arProp) {
                if($arProp["CODE"] === "CITY_NEW"){
                    //$arResult['JS_DATA']["ORDER_PROP"]["properties"][$kProp]["VALUE"] = $arRegion["NAME"];
                }
            }
        }
    }
}