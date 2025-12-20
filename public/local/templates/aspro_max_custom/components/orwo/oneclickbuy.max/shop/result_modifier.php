<?php

use Bitrix\Sale;

//формирование объекта для ecommerce
$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
$arEcomData = [];
$price = $basket->getPrice();
if ($price) {
    $arEcomData['price'] = $price;
}
foreach ($basket as $index => $basketItem) {
    $ecomItem = [
        'item_name' => $basketItem->getField('NAME'),
        'item_id' => $basketItem->getField('ID'),
        'price' => $basketItem->getField('PRICE'),
        'index' => $index + 1,
        'quantity' => intval($basketItem->getField('QUANTITY')),
    ];
    if (!empty($ecomItem)) {
        $arEcomData['items'][] = $ecomItem;
    }
}
if (!empty($arEcomData)) {
    $arResult['ECOM_DATA'] = $arEcomData;
}