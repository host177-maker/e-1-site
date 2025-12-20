<?php
//формирование объекта для ecommerce
if ($arParams['ELEMENT_ID']) {
    if (CModule::IncludeModule("iblock")) {
        $arEcomData = [];
        $res = CIBlockElement::GetByID($arParams['ELEMENT_ID']);
        $price = CPrice::GetBasePrice($arParams['ELEMENT_ID']);
        $arEcomData['price'] = (int)$price['PRICE'];
        $arEcomData['currency'] = $price['CURRENCY'];
        if ($ar_res = $res->GetNext()) {
            $ecomItem = [
                'item_name' => $ar_res['NAME'],
                'item_id' => $ar_res['ID'],
                'price' => (int)$price['PRICE'],
                'quantity' => 1,
            ];
            if (!empty($ecomItem)) {
                $arEcomData['items'] = $ecomItem;
            }
        }
        if (!empty($arEcomData)) {
            $arResult['ECOM_DATA'] = $arEcomData;
        }
    }
}