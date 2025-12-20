<?php

use Bitrix\Main\Context;
use Bitrix\Sale;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\PaySystem;
use Bitrix\Main\Config\Option;
use Bitrix\Sale\Location;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$data = [
    'success' => false,
    'message' => '',
];

$oRequest = Context::getCurrent()->getRequest();
$basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Context::getCurrent()->getSite());
if (!$basket->getPrice()) {
    $data['message'] = 'Корзина пуста';
    die(json_encode($data));
}

if (!$oRequest->getPost('PAYMENT_ID')) {
    $data['message'] = 'Выберите способ оплаты';
    die(json_encode($data));
}

// Получаем пользователя
global $USER;

if ($USER->IsAuthorized()) {
    $userId = $USER->GetID();
} else {
    $defaultGroup = COption::GetOptionString('main', 'new_user_registration_def_group', '');
    $arPolicy     = CUser::GetGroupPolicy([]);

    $passwordMinLength = 12; //intval($arPolicy["PASSWORD_LENGTH"]);

    $passwordChars = [
        'abcdefghijklnmopqrstuvwxyz',
        'ABCDEFGHIJKLNMOPQRSTUVWXYZ',
        '0123456789',
        ",.<>/?;:'\"[]{}\|`~!@#\$%^&*()-_+=",
    ];

    $autoPassword = randString($passwordMinLength + 2, $passwordChars);

    $nameParts = explode(' ', $oRequest->getPost('FIO'));

    $autoLogin = $oRequest->getPost('EMAIL');

    if (!check_email($autoLogin)) {
        $data['message'] = 'Некорректный e-mail';
        die(json_encode($data));
    }

    $dbUserLogin = CUser::GetByLogin($autoLogin)->Fetch();
    if ($dbUserLogin){
        $userId = $dbUserLogin['ID'];
    } else {
        $arFields = [
            'LOGIN'            => $autoLogin,
            'PASSWORD'         => $autoPassword,
            'PASSWORD_CONFIRM' => $autoPassword,
            'GROUP_ID'         => $defaultGroup,
            'LID'              => Context::getCurrent()->getSite(),
            'EMAIL'            => $oRequest->getPost('EMAIL'),
            'PERSONAL_PHONE'   => $oRequest->getPost('PHONE'),
            'NAME'             => $nameParts[1],
            'LAST_NAME'        => $nameParts[0],
            'SECOND_NAME'      => $nameParts[2],
        ];

        $user   = new CUser();
        $userId = $user->Add($arFields);

        if (!$userId) {
            $data['message'] = $user->LAST_ERROR;
            die(json_encode($data));
        }
    }
}

// Создаём новый заказ
$order = Sale\Order::create(Context::getCurrent()->getSite(), $userId, CurrencyManager::getBaseCurrency());
//если у нас есть INN, то юр лицо
if (!empty($oRequest->getPost('INN'))) {
    $order->setPersonTypeId(2);
} else {
    $order->setPersonTypeId(1);
}
$order->setBasket($basket);

// Создаём одну отгрузку и устанавливаем способ доставки "Без доставки" (служебный)
$shipmentCollection = $order->getShipmentCollection();
$shipment           = $shipmentCollection->createItem();
$currentDelivery    = !empty($oRequest->getPost('EXTRASERVICES_ID')) ? $oRequest->getPost('EXTRASERVICES_ID') : $oRequest->getPost('DELIVERY_ID');
$service            = Delivery\Services\Manager::getById($currentDelivery);
$shipment->setFields([
    'DELIVERY_ID'   => $service['ID'],
    'DELIVERY_NAME' => $service['NAME'],
]);
if (!empty($oRequest->getPost('CUSTOM_PRICE_DELIVERY'))) {
    $deliveryData = [
        'DELIVERY_ID'           => $service['ID'],
        'DELIVERY_NAME'         => $service['NAME'],
        'ALLOW_DELIVERY'        => 'Y',
        'PRICE_DELIVERY'        => $oRequest->getPost('CUSTOM_PRICE_DELIVERY'),
        'CUSTOM_PRICE_DELIVERY' => 'Y'
    ];
    $shipment->setFields($deliveryData);
}

$shipmentItemCollection = $shipment->getShipmentItemCollection();

$data['ecom_purchase'] = [];
$orderPrice            = 0;
//custom Скидка, проверяем если она активна
$sIsCustomBasketSale = Option::get('e1.site.settings', 'E1_SS_CUSTOM_SALE_BASKET', 'N');
if ($sIsCustomBasketSale !== 'Y') {
    $arSaleCustom     = \E_1\Prices::GetDiscountsCustom(); //получаем скидки кастомные из инфоблока
    $iCountBasket     = \E_1\Prices::getCountBasket(); //количество товара в корзине
    $arProductsBasket = $arProductsOffers = $arProductPrice = [];

    foreach ($order->getBasket() as $item) {
        $iProductId                   = (int) $item->getProductId();
        $productId                    = \CCatalogSKU::GetProductInfo($iProductId)['ID'];
        $arProductsBasket[$productId] = $arProductsBasket[$productId] + 1;
        $arProductsOffers[]           = $iProductId;
        $arProductPrice[]             = $item->getField('PRICE');
    }
    $arPriceMin  = min($arProductPrice);
    $arKeyBasket = is_array(array_keys($arProductPrice, $arPriceMin)) ? array_keys($arProductPrice, $arPriceMin)[0] : array_keys($arProductPrice, $arPriceMin);
}
$iCount = 0;

foreach ($order->getBasket() as $obBasketItem) {
    $iProductId       = $obBasketItem->getProductId();
    $iProductQuantity = $obBasketItem->getQuantity();
    $ibBasketItemId   = $obBasketItem->getId();

    if (!empty($iProductId) && !empty($iProductQuantity) && !empty($ibBasketItemId)) {
        $arResult     = \COrwoFunctions::GetPriceByOfferId($iProductId);
        $fakeDiscount =  \E_1\Prices::getDiscountById($iProductId);
        $realDiscount = $obBasketItem->getField('DISCOUNT_PRICE_PERCENT');
        if ($realDiscount) {
            $totalDiscount = (int) ($fakeDiscount + ((100 - $fakeDiscount) * ($realDiscount / 100)));
        } else {
            $totalDiscount = (int) $fakeDiscount;
        }
        global $USER;
        $arDiscounts = \CCatalogDiscount::GetDiscountByProduct(
            $iProductId,
            $USER->GetUserGroupArray(),
            'N',
            [],
            SITE_ID
        );
        //получаем id скидки , $iDiscountOptim - это общая переменая
        $iDiscountOptim = $iDiscountOptimSumm = 0;
        $idDiscounts    = array_keys($arDiscounts);
        foreach ($arDiscounts as $key => $arDiscount) {
            if (empty($arDiscount['COUPON']) && $arDiscount['ACTIVE'] === 'Y') {
                $iDiscountOptim += $arDiscount['VALUE'];
            } else {
                $iDiscountOptimSumm += $arDiscount['VALUE'];
            }
        }
        $basketItemPriceReal = (!empty($arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'])) ? floatval($arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE']) : floatval($arResult['ELEMENT']['MIN_PRICE']['DISCOUNT_VALUE']);
        //подставляем для расчета скидки нужную цену (актуально для оптим)
        if (!empty($basketItemPriceReal) && $arResult['ELEMENT']['PROPERTIES']['SERIYA_SHKAFA']['VALUE'] === 'Оптим') {
            $intProcentSale    = (!empty($arResult['OFFER']['MIN_PRICE']['PROCENT_SALE'])) ? floatval($arResult['OFFER']['MIN_PRICE']['PROCENT_SALE']) : floatval($arResult['ELEMENT']['MIN_PRICE']['PROCENT_SALE']);
            $totalDiscountReal = $intProcentSale;

            //цена изначальная
            $basePrice = $basketItemPriceReal * (100 / (100 - $totalDiscountReal));
            if ($iDiscountOptim > 0) {
                $priceSale = $basePrice * ((100 - ($totalDiscountReal + $totalDiscount)) / 100);
                //магия для оптимы, прибавка скидки,которая будет уменьшена правилом корзины
                $priceSale = $priceSale * (100 / (100 - $iDiscountOptim));
            } else {
                $priceSale = $basePrice * ((100 - ($totalDiscountReal + $totalDiscount)) / 100);
            }
            //добавление логирования
            if (!empty(\Cosmos\Config::getInstance()->getParam('LOGGING')['VUE_ORDER'])) {
                file_put_contents(
                    $_SERVER['DOCUMENT_ROOT'] . '/VUE_SAVE_OPTIM.log',
                    "\n\n" . date('Y-m-d H:i:s') . "\n " . print_r([time(), '$intProcentSale' => $intProcentSale, '$totalDiscountReal' => $totalDiscountReal, '$basePrice' => $basePrice, '$iDiscountOptim' => $iDiscountOptim, '$priceSale' => $priceSale, '$iDiscountOptimSumm' => $iDiscountOptimSumm, 'arDiscouns' => $arDiscounts, 'NEW_SAVE'], true) . "\n\n",
                    FILE_APPEND
                );
            }
            $obBasketItem->setFields([
                'PRICE'      => $priceSale,
                'BASE_PRICE' => $priceSale,
            ]);

            $obBasketItem->save();
        } else {
            if (!empty($arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'])) {
                $basePrice = $basketItemPriceReal * (100 / (100 - $totalDiscount));
                $oldPrice  = $basePrice;
                if ($iDiscountOptim > 0) {
                    $priceSale = $basePrice * ((100 - ($totalDiscount)) / 100);
                    // прибавка скидки,которая будет уменьшена правилом корзины
                    $priceSale = $priceSale * (100 / (100 - $iDiscountOptim));
                } else {
                    $priceSale = $basePrice * ((100 - ($totalDiscount)) / 100);
                }
                if ($iDiscountOptim > 0 && $iDiscountOptimSumm > 0) {
                    //здесь цена без купона, он применяется самим Битрикс
                    $sPriceForCoupon = $basePrice / ((100 - $iDiscountOptimSumm) / 100);
                    $sPriceForCoupon = $sPriceForCoupon / ((100 - $iDiscountOptim) / 100);
                    $obBasketItem->setFields([
                        'PRICE'      => $sPriceForCoupon,
                        'BASE_PRICE' => $sPriceForCoupon,
                    ]);
                } else {
                    //проверяем товары и делаем скидку
                    if ($sIsCustomBasketSale !== 'Y') {
                        $productId = \CCatalogSKU::GetProductInfo($iProductId)['ID'];
                        //вначале проверим на существование в массиве значений
                        if (
                            $arProductsOffers[$arKeyBasket] == $iProductId
                            && in_array($arProductsBasket[$productId], array_column($arSaleCustom['ITEMS'] ?: [], 'COUNT'))
                            && in_array($iCountBasket, array_column($arSaleCustom['ITEMS'] ?: [], 'COUNT'))
                            && $iCount == $arKeyBasket
                        ) {
                            //найдем по количеству нужный ключ массива
                            $iSearchKeyProduct = array_search($iCountBasket, array_column($arSaleCustom['ITEMS'], 'COUNT'));
                            //проверим чтобы все сходилось
                            if (isset($arSaleCustom['ITEMS'][$iSearchKeyProduct]['PRODUCT_ID']) && $arSaleCustom['ITEMS'][$iSearchKeyProduct]['PRODUCT_ID'] == $productId && $iCountBasket == $arSaleCustom['ITEMS'][$iSearchKeyProduct]['COUNT'] && $arProductsBasket[$productId] == $arSaleCustom['ITEMS'][$iSearchKeyProduct]['COUNT']) {
                                $procentSale = $arSaleCustom['ITEMS'][$iSearchKeyProduct]['DISCOUNT'];
                                $priceSale   = $priceSale * ((100 - ($procentSale)) / 100);
                            }
                        }
                    }

                    $obBasketItem->setFields([
                        'PRICE'      => $priceSale, //$arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'],
                        'BASE_PRICE' => $priceSale, //$arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'],
                    ]);
                }
                if (!empty(\Cosmos\Config::getInstance()->getParam('LOGGING')['VUE_ORDER'])) {
                    file_put_contents(
                        $_SERVER['DOCUMENT_ROOT'] . '/../logs/VUE_SAVE_OPTIM.log',
                        "\n\n" . date('Y-m-d H:i:s') . "\n " . print_r([time(), '$basePrice' => $basePrice, '$iDiscountOptim' => $iDiscountOptim, '$priceSale' => $priceSale, '$iDiscountOptimSumm' => $iDiscountOptimSumm, '$oldPrice' => $oldPrice, '$sPriceForCoupon' => $sPriceForCoupon, 'CUSTOM_PRICE' => $obBasketItem->getField('CUSTOM_PRICE'), 'arDiscouns' => $arDiscounts, 'NEW_SAVE'], true) . "\n\n",
                        FILE_APPEND
                    );
                }
                $obBasketItem->save();
            }
        }
    }
    $orderPrice += $priceSale;
    $iCount++;
}

$propertyCollection = $order->getPropertyCollection();

foreach ($propertyCollection as $property) {
    $arResult['JS_DATA']['ORDER_PROP_LIST'][$property->getField('CODE')]['VALUE'][0] = $property->getField('VALUE');
}
$order->refreshData();
$arFields['ORDER_PRICE'] = $order->getPrice(); // Сумма заказа
//добавление логирования
if (!empty(\Cosmos\Config::getInstance()->getParam('LOGGING')['VUE_ORDER'])) {
    file_put_contents(
        $_SERVER['DOCUMENT_ROOT'] . '/../logs/VUE_SAVE_OPTIM.log',
        "\n\n" . date('Y-m-d H:i:s') . "\n " . print_r([time(), $oRequest->getPost('CUSTOM_PRICE_DELIVERY'), '$ORDER_PROP_LIST' => $arResult['JS_DATA']['ORDER_PROP_LIST'], 'ORDER_PRICE' => $arFields['ORDER_PRICE'], $_REQUEST, $sDeliveryPrice, ], true) . "\n\n",
        FILE_APPEND
    );
}

// Добавляем товары корзины в отгрузку
foreach ($order->getBasket() as $item) {
    $shipmentItem = $shipmentItemCollection->createItem($item);
    $shipmentItem->setQuantity($item->getQuantity());
}
// Разрешаем отгрузку
$shipmentCollection = $order->getShipmentCollection();
foreach ($shipmentCollection as $shipment) {
    if (!$shipment->isSystem()) {
        $shipment->allowDelivery();
    }
}
// Создаём оплату
$paymentCollection = $order->getPaymentCollection();
$payment           = $paymentCollection->createItem();
$paySystemService  = PaySystem\Manager::getObjectById($oRequest->getPost('PAYMENT_ID'));
$payment->setFields([
    'PAY_SYSTEM_ID'   => $paySystemService->getField('PAY_SYSTEM_ID'),
    'PAY_SYSTEM_NAME' => $paySystemService->getField('NAME'),
    'SUM'             => $order->getPrice()
]);

$data['ecom_purchase']['value'] = $order->getPrice();

$order->setField('USER_DESCRIPTION', $oRequest->getPost('COMMENT'));

$propertyCollection = $order->getPropertyCollection();
$properties = [
    'ADDRESS'           => 'ADDRESS',
    'NAME'              => 'NAME',
    'EMAIL'             => 'EMAIL',
    'PHONE'             => 'PHONE',
    'PUNKT_SAM'         => 'PUNKT_SAM',
    'OWN_DELIVERY_INFO' => 'FLOOR',
    'APARTMENT'         => 'APARTMENT',
    'LIFTING'           => 'LIFTING',
];
if (!empty($oRequest->getPost('INN'))) {
    $properties['COMPANY']          = 'COMPANY';
    $properties['INN']              = 'INN';
    $properties['CHECKING_ACCOUNT'] = 'CHECKING_ACCOUNT';
    $properties['BIK']              = 'BIK';
    $properties['CONTACT_PERSON']   = 'CONTACT_PERSON';
    $properties['COMPANY_ADR']      = 'COMPANY_ADR';
    $properties['REFERAL_CODE']     = 'REFERAL_CODE';
}
//добавляем сохранение времени только для доставки Скоро Готово
if ($service['CODE'] === 'GOTOVOSHOP') {
    $properties['TIME_GOTOVO'] = 'TIME_GOTOVO';
}

foreach ($properties as $code => $postCode) {
    $propValue = $propertyCollection->getItemByOrderPropertyCode($code);

    if ($propValue) {
        $propValue->setValue($oRequest->getPost($postCode));
    }
}

$propValue = $propertyCollection->getItemByOrderPropertyCode('LOCATION');

if ($propValue) {
    $cityName = $oRequest->getPost('CITY');
    if (!empty($cityName)) {
        $parameters                         = [];
        $parameters['filter']['=NAME.NAME'] = $cityName;
        $parameters['limit']                = 1;
        $parameters['select']               = ['CODE', 'NAME'];

        $locationRes = Location\LocationTable::getList($parameters)->fetch();

        if ($locationRes && !empty($locationRes['CODE'])) {
            $propValue->setValue($locationRes['CODE']);
        }
    }
}

$order->doFinalAction(true);

$result = $order->save();

if ($result->isSuccess()) {
    $data['orderId']    = $result->getId();
    $order              = \Bitrix\Sale\Order::load($data['orderId']);
    $propertyCollection = $order->getPropertyCollection();
    $address            = $companyAdr = '';
    foreach ($propertyCollection as $property) {
        if ($property->getField('CODE') == 'FIO') {
            $property->setValue($oRequest->getPost('FIO'));
        }
    }

    $data['ecom_purchase']['transaction_id'] = $result->getId();

    $order->save();
    $data['success'] = true;
} else {
    $data['message'] = implode(', ', $result->getErrorMessages());
}

if ($data['ecom_purchase']['transaction_id']) {
    \CModule::IncludeModule('sale');
    $res          = \CSaleBasket::GetList([], ['ORDER_ID' => $data['ecom_purchase']['transaction_id']]);
    $arOrderItems = [];
    while ($arItem = $res->Fetch()) {
        $arOrderItems[] = $arItem;
    }

    foreach ($arOrderItems as $arItem) {
        $ecomItem = [
            'item_name' => $arItem['NAME'],
            'item_id'   => $arItem['ID'],
            'price'     => (int) $arItem['PRICE'],
            'quantity'  => (int) $arItem['QUANTITY'],
        ];
        $data['ecom_purchase']['items'][] = $ecomItem;
    }
}

if ($data['ecom_purchase']) {
    $data['ecom_purchase_js_object'] = json_encode($data['ecom_purchase'], JSON_UNESCAPED_UNICODE);
}

die(json_encode($data));
