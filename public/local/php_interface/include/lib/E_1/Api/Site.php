<?php

namespace E_1\Api;

use Composer\Package\Loader\ValidatingArrayLoader;
use Cosmos\Config;
use Cosmos\HLblock;

use Bitrix\Main\Application,
    Bitrix\Main\Loader,
    Bitrix\Sale\Order;
use CUser, CFile;
use Bitrix\Main\Config\Option;

class Site extends Base
{

    public function get_types()
    {
        $result = [];
        $resultTypes = \Bitrix\Sale\PersonType::getList(/*['filter'=>['ID'=>$id]]*/)->fetchAll();
        if (!empty($resultTypes)) {
            foreach ($resultTypes as $k => $arValues) {
                $result['data'][] = [
                    'id' => $arValues['ID'],
                    'title' => $arValues['NAME'],
                ];
            }
        }
        return $result;
    }

    //устанавливает (меняет) город, при вводе адреса
    public function setCookieRegion($arRegionId = '')
    {
        global $_COOKIE;
        if (!empty($arRegionId)) {
            $_COOKIE['current_region'] = $arRegionId;
            //удалим cookie и установим новую
            unset($_COOKIE['current_region']);
            setcookie("current_region", "", time() - 3600, '/');
            setcookie("current_region", "", time() - 3600, '/');
            setcookie("current_region", "", 1);
            $arFilter = array("ID" => $arRegionId, 'IBLOCK_ID' => \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_regions'), 'ACTIVE' => 'Y');
            $resCity = \CIBlockElement::GetList(array(), $arFilter, false, false, ['ID', 'CODE', 'NAME', 'PROPERTY_REGION_TAG_NEAREST_REGION', 'PROPERTY_DEFAULT', 'PROPERTY_MAIN_DOMAIN'])->Fetch();
            $domain = '';
            if (!empty($resCity) && !empty($resCity['PROPERTY_MAIN_DOMAIN_VALUE'])) {
                $domain = (!empty($resCity['PROPERTY_DEFAULT_VALUE'])) ? '.' . $resCity['PROPERTY_MAIN_DOMAIN_VALUE'] : '.' . $resCity['PROPERTY_MAIN_DOMAIN_VALUE'];
            } elseif (!empty($resCity['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE'])) {
                $arFilter = array("ID" => $arRegionId, 'PROPERTY_REGION_TAG_NEAREST_REGION' => $resCity['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE'], 'IBLOCK_ID' => \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_regions'), 'ACTIVE' => 'Y');
                $resCity = \CIBlockElement::GetList(array(), $arFilter, false, false, ['ID', 'CODE', 'NAME', 'PROPERTY_REGION_TAG_NEAREST_REGION', 'PROPERTY_DEFAULT', 'PROPERTY_MAIN_DOMAIN'])->Fetch();
                if (!empty($resCity['PROPERTY_MAIN_DOMAIN_VALUE'])) {
                    $domain = (!empty($resCity['PROPERTY_DEFAULT_VALUE'])) ? '.' . $resCity['PROPERTY_MAIN_DOMAIN_VALUE'] : '.' . $resCity['PROPERTY_MAIN_DOMAIN_VALUE'];
                }
            }
            //установка новой cookie
            if (!empty($domain)) {
                $cookieResult = setcookie("current_region", $arRegionId, 0, '/', $domain, false, false); // запомнить выбор в cookie сразу же
                $cookieResult = setcookie("current_region", $arRegionId, 0, '/', '.e-1.ru', false, false); // запомнить выбор в cookie сразу же
                $cookieResult = setcookie("current_region", $arRegionId, 0, '/', $_SERVER['HTTP_HOST'], false, false); // для региона подставляем домен текущего города
            } else {
                $cookieResult = setcookie("current_region", $arRegionId, 0, '/');
                $cookieResult = setcookie("current_region", $arRegionId, 0, '/', '.e-1.ru', false, false); // запомнить выбор в cookie сразу же
                $cookieResult = setcookie("current_region", $arRegionId, 0, '/', $_SERVER['HTTP_HOST'], false, false); // для региона подставляем домен текущего города
            }
            //setcookie("current_region", $arRegionId, 0, '/'); // запомнить выбор в cookie сразу же
            $arResultOrder['data']['region_name_max'] = \CMaxRegionality::getCurrentRegion()['NAME'];

            return true;
        }
        return false;
    }

    //устанавливает(пересчитывает) корзину для региона
    public function setBasketForRegion()
    {
        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(\CSaleBasket::GetBasketUserID()), \Bitrix\Main\Context::getCurrent()->getSite())->getOrderableItems();
        $orderPrice = 0;
        $basket->refreshData(array('PRICE', 'COUPONS', 'BASE_PRICE'));
        global $arRegion;
        foreach ($basket as $obBasketItem) {
            $iProductId = $obBasketItem->getProductId();
            $iProductQuantity = $obBasketItem->getQuantity();
            $ibBasketItemId = $obBasketItem->getId();

            $basketPropertyCollection = $obBasketItem->getPropertyCollection();
            if ($arRegion['PROPERTY_SHOW_PRODUCT_BUILDING_VALUE'] == 'Y' && isset($basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']) && !empty($basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']) && $basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']['CODE'] === 'ASPRO_BUY_PRODUCT_ID') {
                \CSaleBasket::Delete($ibBasketItemId);
                continue;
            }


            if (!empty($iProductId) && !empty($iProductQuantity) && !empty($ibBasketItemId)) {
                $arResult = \COrwoFunctions::GetPriceByOfferId($iProductId);
                $fakeDiscount = \E_1\Prices::getDiscountById($iProductId);
                $realDiscount = $obBasketItem->getField('DISCOUNT_PRICE_PERCENT');
                if ($realDiscount) {
                    $totalDiscount = (int)($fakeDiscount + ((100 - $fakeDiscount) * ($realDiscount / 100)));
                } else {
                    $totalDiscount = (int)$fakeDiscount;
                }
                global $USER;
                $arDiscounts = \CCatalogDiscount::GetDiscountByProduct(
                    $iProductId,
                    $USER->GetUserGroupArray(),
                    "N",
                    [],
                    SITE_ID
                );
                //получаем id скидки , $iDiscountOptim - это общая переменая 
                $iDiscountOptim = $iDiscountOptimSumm = 0;
                foreach ($arDiscounts as $key => $arDiscount) {
                    if (empty($arDiscount['COUPON']) && $arDiscount['ACTIVE'] === 'Y') {
                        $iDiscountOptim += $arDiscount['VALUE'];
                    } else {
                        $iDiscountOptimSumm += $arDiscount['VALUE'];
                    }
                }
                $basketItemPriceReal = (!empty($arResult['OFFER']['MIN_PRICE']["DISCOUNT_VALUE"])) ? floatval($arResult['OFFER']['MIN_PRICE']["DISCOUNT_VALUE"]) : floatval($arResult['ELEMENT']['MIN_PRICE']["DISCOUNT_VALUE"]);
                //подставляем для расчета скидки нужную цену (актуально для оптим)
                if (!empty($basketItemPriceReal) && $arResult['ELEMENT']["PROPERTIES"]["SERIYA_SHKAFA"]["VALUE"] === 'Оптим') {
                    $intProcentSale = (!empty($arResult['OFFER']['MIN_PRICE']["PROCENT_SALE"])) ? floatval($arResult['OFFER']['MIN_PRICE']["PROCENT_SALE"]) : floatval($arResult['ELEMENT']['MIN_PRICE']["PROCENT_SALE"]);
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
                    $obBasketItem->setFields([
                        'PRICE' => $priceSale,
                    ]);

                    $obBasketItem->save();
                } else {
                    if (!empty($arResult['OFFER']['MIN_PRICE']['DISCOUNT_VALUE'])) {
                        $basePrice = $basketItemPriceReal * (100 / (100 - $totalDiscount));
                        $oldPrice = $basePrice;
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
                                'PRICE' => $sPriceForCoupon,
                            ]);
                        } else {
                            $obBasketItem->setFields([
                                'PRICE' => $priceSale,
                            ]);
                        }

                        $obBasketItem->save();
                    }
                }
            }
            $orderPrice += $priceSale;
        }
    }

    /**
     * Метод получает корзину пользователя
     * $couponCheck - когда из другого метода получаем, доп проверка
     */

    public function get_info($couponCheck = false)
    {
        global $USER, $arRegion, $_COOKIE;
        $arResultOrder['data'] = [
            'goodsCount' => null,
            'discount' => null,
            'totalGoods' => null,
            'total' => null,
            'assembly' => null,
        ];
        $arRegionHome = [];
        //учитываем регион, если он есть
        if (!empty($_REQUEST['city'])) {
            $arRegionHome = $this->get_region_for_city($_REQUEST['city']);
        }
        //делаем подмену данных доставки, берем  например Домодедово, Московская область, возврат город Москва
        if (!empty($_REQUEST['region'])) {
            $arRegionHome = $this->get_region($_REQUEST['region']);
        }
        if (isset($arRegionHome) && !empty($arRegionHome) /*&& $arRegionHome['CODE'] !==$arRegion['CODE']*/) {
            $arRegion["PROPERTY_SHOW_DELIVERY_BUSINESS_LINE_VALUE"] = $arRegionHome["PROPERTY_SHOW_DELIVERY_BUSINESS_LINE_VALUE"];
            $arRegion['ID'] = $arRegionHome['ID'];
            $arRegion['CODE'] = $arRegionHome['CODE'];
            $arRegion['NAME'] = $arRegionHome['NAME'];
            $arRegion['PROPERTY_SHOW_PRODUCT_BUILDING_VALUE'] = $arRegionHome['PROPERTY_SHOW_PRODUCT_BUILDING_VALUE'];
            $arResultRegionCookie = $this->setCookieRegion($arRegion['ID']);
            $arResultOrder['data']['region_name_COOKIE_SET'] = $arResultRegionCookie;
            //если город поменялся, тогда и корзину тоже меняем
            if ($arResultRegionCookie) {
                $arResultRegionCookie = $this->setBasketForRegion();
            }
        }
        \Bitrix\Main\Loader::includeModule("catalog");
        //$basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(\CSaleBasket::GetBasketUserID()), \Bitrix\Main\Context::getCurrent()->getSite())->getOrderableItems();
        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(
            \CSaleBasket::GetBasketUserID(),
            "s1"
        )->getOrderableItems();
        $priceAssembly = 0;
        foreach ($basket as $basketItem) {
            $basketPropertyCollection = $basketItem->getPropertyCollection();
            //для сборки генерируем рандом id, чтобы были отличия в id товара и id сборки и цена считалась верной
            if (isset($basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']) && !empty($basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']) && $basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']['CODE'] === 'ASPRO_BUY_PRODUCT_ID') {
                $productIDService = $basketItem->getField('PRODUCT_ID') . random_int(100, 1000);
                $arBasketIds[] = $productIDService;
                $arProductBasket[$productIDService] = $productIDService;
                $productBasePrice = $basketItem->getField('PRICE');
            } else {
                $arBasketIds[] = $basketItem->getField('PRODUCT_ID');
                $arProductBasket[$basketItem->getField('ID')] = $basketItem->getField('PRODUCT_ID');
                $productIDService = $basketItem->getField('PRODUCT_ID');
                $productBasePrice = $basketItem->getField('BASE_PRICE');
            }
            if (!empty($productIDService)) {
                $arResult["BASKET_LIST"][$productIDService] = array(
                    "ID" => $basketItem->getField('ID'),
                    "PRICE" => $basketItem->getField('PRICE'),
                    "BASE_PRICE" => $productBasePrice,
                    "OLD_PRICE" => ($basketItem->getField('BASE_PRICE') == $basketItem->getField('PRICE')) ? null : $basketItem->getField('BASE_PRICE'),
                    "OLD_PRICE_1" => $basketItem->getField('PRICE'), //$basketItem->getField('BASE_PRICE'),
                    "COUNT" => $basketItem->getQuantity(),
                    "DISCOUNT" => $basketItem->getDiscountPrice(),
                    "PRODUCT_ID" => $productIDService,
                );
                $basketPropertyCollection = $basketItem->getPropertyCollection();
                if (isset($basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']) && !empty($basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']) && $basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']['CODE'] === 'ASPRO_BUY_PRODUCT_ID') {
                    $arResultOrder['data']['services'] = true;
                }
            }
        }
        //custom Скидка, проверяем если она активна
        $sIsCustomBasketSale = Option::get('e1.site.settings', 'E1_SS_CUSTOM_SALE_BASKET', 'N');
        if ($sIsCustomBasketSale !== 'Y') {
            $arProductsBasket = $arProductsOffers = $arProductPrice = [];
            foreach ($arResult["BASKET_LIST"] as $key => $item) {
                $productId = \CCatalogSKU::GetProductInfo($item['PRODUCT_ID'])['ID'];
                $arProductsBasket[$productId] = $arProductsBasket[$productId] + 1;
                $arProductsOffers[] = $item['PRODUCT_ID'];
                $arProductPrice[] = $arResult["BASKET_LIST"][$key]['PRICE'];
            }
        }

        //скидка
        $discounts = \Bitrix\Sale\Discount::buildFromBasket($basket, new \Bitrix\Sale\Discount\Context\Fuser($basket->getFUserId(true)));
        if (isset($discounts)) {
            $discounts->calculate();
            $result = $discounts->getApplyResult(true); // цены товаров с учетом скидки
        }
        $arResult["BASKET_SUM"] = $arResult["BASKET_DISCOUNT"] = $arResult["BASKET_VAT_RATE"] =  $allWeight = 0;
        foreach ($result['PRICES']['BASKET'] as $idProduct => $arBasketPrice) {
            if (isset($arResult["BASKET_LIST"][$arProductBasket[$idProduct]]) && !empty($arResult["BASKET_LIST"][$arProductBasket[$idProduct]])) {
                $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["PRICE"] = ceil($arBasketPrice["PRICE"] * 100) / 100;
                $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["DISCOUNT"] = ceil($arBasketPrice["DISCOUNT"] * 100) / 100;
                $allWeight += $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["COUNT"] * $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["WEIGHT"];
                $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["DISCOUNT_RESULT_RUB"] = $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["COUNT"] * $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["DISCOUNT"];
            }

            $productId = \CCatalogSKU::GetProductInfo((int)$arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["PRODUCT_ID"]);
            if (!is_array($productId)) {
                $priceAssembly += (int) $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["PRICE"];
            }

            $basketItemPriceRealCalc = \COrwoFunctions::GetPriceByOfferId($arResult["BASKET_LIST"][$arProductBasket[$idProduct]]['PRODUCT_ID']);
            $intProcentSale = (!empty($basketItemPriceRealCalc['OFFER']['MIN_PRICE']["PROCENT_SALE"])) ? floatval($basketItemPriceRealCalc['OFFER']['MIN_PRICE']["PROCENT_SALE"]) : floatval($basketItemPriceRealCalc['ELEMENT']['MIN_PRICE']["PROCENT_SALE"]);

            $basePrice = $basketItemPriceRealCalc['OFFER']['MIN_PRICE']["VALUE"];
            $priceSale = $basketItemPriceRealCalc['OFFER']['MIN_PRICE']["VALUE"] - $basketItemPriceRealCalc['OFFER']['MIN_PRICE']["DISCOUNT_DIFF"];
            $discountSumm =  $basketItemPriceRealCalc['OFFER']['MIN_PRICE']["DISCOUNT_DIFF"];

            $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["BASE_PRICE"] = $basePrice;
            $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["PRICE"] = $priceSale;
            $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["DISCOUNT"] = $discountSumm;
            $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["DISCOUNT_RESULT_RUB"] = $discountSumm;
            $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]['OPTIM'] = true;
            $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["DISCOUNT_OPTIM"] = $discountSumm;
            $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]['DISCOUNT_PRICE_PERCENT'] = $intProcentSale;

            $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]['D']['OFFER'] = $basketItemPriceRealCalc['OFFER'];
        }

        //считаем сумму с учетом скидок
        foreach ($arResult["BASKET_LIST"] as $key => $arBasket) {
            if (empty($arBasket["BASE_PRICE"])) {
                unset($arResult["BASKET_LIST"][$key]);
                continue;
            }
            $arResult["BASKET_SUM"] += $arBasket["BASE_PRICE"];
            $arResult["BASKET_DISCOUNT"] += $arBasket["DISCOUNT_RESULT_RUB"] * $arBasket["COUNT"];
        }
        $arResult["BASKET_SUM"] = ceil($arResult["BASKET_SUM"] * 100) / 100;

        $arResult['DELIVERIES'] = $this->get_deliveries()["data"];
        //записываем для удобства расчета, чтобы можно было взять по id
        foreach ($arResult['DELIVERIES'] as $kDelivery => $arDelivery) {
            $arResult['DELIVERIES_ID'][$arDelivery['id']] = $arDelivery;
        }

        //получаем примерную цену с доставкой
        if (!empty($_REQUEST['delivery_id']) && !empty($_REQUEST['city'])) {
            //нужно учесть город!!!
            $arResult['DELIVERY_PRICE'] = $arResult['DELIVERIES_ID'][$_REQUEST['delivery_id']]["price"];

            if (!empty($arResult['DELIVERIES_ID'][$_REQUEST['delivery_id']]['types'])) {
                $arResultOrder['data']['riseFloor'] = $arResult['DELIVERIES_ID'][$_REQUEST['delivery_id']]['types'][0]['additionalPrice'];
                $arResultOrder['data']['elevatorPrice'] = $arResult['DELIVERIES_ID'][$_REQUEST['delivery_id']]['types'][0]['additionalOptions'][0]['additionalPrice'];
            }
        } else {
            $arResult['DELIVERY_PRICE'] = $arResult['DELIVERIES'][0]["price"];
        }

        $arResultOrder['data']['goodsCount'] = count($arResult["BASKET_LIST"]);
        $arResultOrder['data']['discount'] = round($arResult["BASKET_DISCOUNT"]);
        $arResultOrder['data']['totalGoods'] = round($arResult["BASKET_SUM"]);

        if ((int) $arResult["BASKET_DISCOUNT"] > 0) {
            $arResultOrder['data']['total'] = $arResult["BASKET_SUM"] - $arResult["BASKET_DISCOUNT"];
        } else {
            $arResultOrder['data']['total'] = $arResult["BASKET_SUM"]; ///+ $arResult['DELIVERY_PRICE'];
        }
        //добавил пересчет как на промо, здесь есть товары, которые имеют одинаковый продукт ID (услуга сборка шкафа)
        $sum = $discountPrice = 0;
        // массив объектов \Bitrix\Sale\BasketItem
        $basketItems = $basket->getBasketItems();
        /** @var \Bitrix\Sale\BasketItem $basketItem */
        foreach ($arResult["BASKET_LIST"] as $idProduct => $arBasketPrice) {
            $sum += $arBasketPrice["BASE_PRICE"] * $arBasketPrice["COUNT"];
            $discountPrice += $arResult["BASKET_LIST"][$arProductBasket[$idProduct]]["DISCOUNT_RESULT_RUB"];
        }

        //если цена сборки больше 0, то устанавливаем ее
        if ($priceAssembly > 0) {
            $arResultOrder['data']['assembly'] = $priceAssembly;
            $arResultOrder['data']['assemblyPathPriceSumm'] = $arResult['DELIVERIES_ID'][$_REQUEST['delivery_id']]['assembly_path_price_summ'];
        }
        if ($discountPrice > 0) {
            $arResult["BASKET_DISCOUNT"] = $discountPrice;
            $arResultOrder['data']['discount'] = round($arResult["BASKET_DISCOUNT"]);
        }
        if (!empty($arResult["BASKET_LIST"])) {
            $arResult["BASKET_LIST"] = array_values($arResult["BASKET_LIST"]);
        }
        //$arResultOrder['result'] = $arResult;
        $arResultOrder['data']['goodsCount'] = count($basketItems);
        $arResultOrder['data']['totalGoods'] = round($sum);
        $arResultOrder['data']['total'] = $sum;

        if ((int)$arResult["BASKET_DISCOUNT"] > 0) {
            $arResultOrder['data']['total'] = round($sum - $arResult["BASKET_DISCOUNT"]);
        } else {
            $arResultOrder['data']['total'] = round($sum); ///+ $arResult['DELIVERY_PRICE'];
        }
        $arResultOrder['data']['basket_listInfo'] = $arResult["BASKET_LIST"];
        return $arResultOrder;
    }

    //получение информации о пользователе (физ и юр лицо)
    public function get_personal()
    {
        $arSelectCode = ['EMAIL', 'PHONE', 'FIO', 'NAME', 'COMMENT'];
        $arSelectCode2 = ['DADATA', 'COMPANY', 'INN', 'CHECKING_ACCOUNT', 'BIK', 'CONTACT_PERSON', 'PHONE', 'EMAIL', 'COMMENT', 'REFERAL_CODE'];
        $aFilter = array(
            "PERSON_TYPE_ID" => (!empty($_REQUEST['param'])) ? $_REQUEST['param'] : "1",
            "CODE" => ($_REQUEST['param'] === '1') ? $arSelectCode : $arSelectCode2,
        );
        global $USER;
        // Данные из последнего заказа
        if ($USER->IsAuthorized()) {
            $arResult['JS_DATA']['CURRENT_USER'] = \CUser::GetByID($USER->GetID())->fetch();
            $arResult['USER_FIELD'] = [
                'NAME' => $USER->GetFullName(),
                'EMAIL' => $arResult['JS_DATA']['CURRENT_USER']['EMAIL'],
                'PHONE' => $arResult['JS_DATA']['CURRENT_USER']['PERSONAL_PHONE'],
            ];
            $dbRes = \Bitrix\Sale\Order::getList([
                'select' => ['ID'],
                'filter' => [
                    'USER_ID' => $USER->GetID(),
                ],
                'order' => ['ID' => 'DESC'],
                'limit' => 1,
            ]);

            if ($arRes = $dbRes->fetch()) {
                $order = \Bitrix\Sale\Order::load($arRes['ID']);
                $propertyCollection = $order->getPropertyCollection();

                foreach ($propertyCollection as $property) {
                    if (!$arResult['JS_DATA']['ORDER_PROP_LIST'][$property->getField('CODE')]['VALUE'][0]) {
                        $arResult['JS_DATA']['ORDER_PROP_LIST'][$property->getField('CODE')]['VALUE'][0] = $property->getField('VALUE');
                    }
                }
            }
        }
        $oDbRes = \CSaleOrderProps::GetList(array('SORT' => 'ASC'), $aFilter, false, false, []);
        while ($aDbRes = $oDbRes->fetch()) {
            $arFields = [
                'title' => $aDbRes["NAME"],
                'id' => $aDbRes["ID"],
                'code' => $aDbRes["CODE"],
                'name' => 'PROPS[' . $aDbRes["CODE"] . ']',
                'value' => null,
                'isRequired' => ($aDbRes["REQUIED"] === 'Y') ? true : false,
            ];
            if ($aDbRes["CODE"] === 'COMMENT') {
                //$arResult['comment'] = $arFields;
            } else $arResult['data']['inputs'][$aDbRes["ID"]] = $arFields;
            switch ($aDbRes["CODE"]) {
                case 'DADATA':
                    $arResult['data']['inputs'][$aDbRes["ID"]]['type'] = 'dadata';
                    $arResult['data']['inputs'][$aDbRes["ID"]]['placeholder'] = "Введите данные для автоматического заполнения";
                    break;
                case 'COMPANY':
                    $arResult['data']['inputs'][$aDbRes["ID"]]['type'] = 'text';
                    $arResult['data']['inputs'][$aDbRes["ID"]]['placeholder'] = "ООО «Компания»";
                    break;
                case 'INN':
                    $arResult['data']['inputs'][$aDbRes["ID"]]['type'] = 'number';
                    $arResult['data']['inputs'][$aDbRes["ID"]]['placeholder'] = "0000000000";
                    break;
                case 'CHECKING_ACCOUNT':
                    $arResult['data']['inputs'][$aDbRes["ID"]]['type'] = 'number';
                    $arResult['data']['inputs'][$aDbRes["ID"]]['placeholder'] = "0000 0000 0000 0000 0000";
                    break;
                case 'BIK':
                    $arResult['data']['inputs'][$aDbRes["ID"]]['type'] = 'number';
                    $arResult['data']['inputs'][$aDbRes["ID"]]['placeholder'] = "00 00 00 000";
                    break;
                case 'EMAIL':
                    $arResult['data']['inputs'][$aDbRes["ID"]]['type'] = 'email';
                    $arResult['data']['inputs'][$aDbRes["ID"]]['placeholder'] = 'email@mail.ru';
                    break;
                case 'PHONE':
                    $arResult['data']['inputs'][$aDbRes["ID"]]['type'] = 'tel';
                    $arResult['data']['inputs'][$aDbRes["ID"]]['placeholder'] = '+7 (999) 999-99-99';
                    break;
                case 'FIO':
                    $arResult['data']['inputs'][$aDbRes["ID"]]['type'] = 'text';
                    $arResult['data']['inputs'][$aDbRes["ID"]]['placeholder'] = 'Иванов Иван Иванович';
                    break;
            }
            if (isset($arResult['JS_DATA']['ORDER_PROP_LIST'][$aDbRes["CODE"]]['VALUE'][0]) && !empty($arResult['JS_DATA']['ORDER_PROP_LIST'][$aDbRes["CODE"]]['VALUE'][0])) {
                if ($aDbRes["CODE"] !== 'COMMENT') {
                    $arResult['data']['inputs'][$aDbRes["ID"]]["VALUE"] = $arResult['JS_DATA']['ORDER_PROP_LIST'][$aDbRes["CODE"]]['VALUE'][0];
                } else {
                    $arResult['comment'] = $arResult['JS_DATA']['ORDER_PROP_LIST'][$aDbRes["CODE"]]['VALUE'][0];
                }
            } else {
                $arResult['data']['inputs'][$aDbRes["ID"]]["VALUE"] = $arResult['USER_FIELD'][$aDbRes["CODE"]];
            }
        }

        if (!empty($arResult['data']['inputs'])) {
            $arResult['data']['inputs'] = array_values($arResult['data']['inputs']);
        }

        return $arResult;
    }

    public static function get_address()
    {
        global $USER, $arRegion;
        $result = [
            'data' => [
                'address' => null,
                'legal_address' => null,
                'floor' => null,
                'apartment' => null,
                'city' => 'г ' . $arRegion['NAME'],
            ]
        ];
        if (!$USER->IsAuthorized()) {
            return $result;
        }

        $data = [];

        $orderList = \Bitrix\Sale\Order::loadByFilter([
            'filter' => [
                'USER_ID' => $USER->GetID(),
            ],
            'order' => [
                'ID' => 'DESC',
            ],
            'limit' => 1,
        ]);

        /** @var Sale\Order $order */
        $order = reset($orderList);

        if ($order) {
            $propertyCollection = $order->getPropertyCollection();

            foreach ($propertyCollection as $property) {
                $data[$property->getField('CODE')] = $property->getField('VALUE');
            }
        }

        if (!empty($data)) {
            if (!empty($data['ADDRESS'])) {
                $result['data']['address'] = $result['data']['legal_address'] = $data['ADDRESS'];
            }
            $result['data']['apartment'] = $data['APARTMENT'];
        }

        return $result;
    }

    public function get_region($regionName = '')
    {
        ///$regionName = 'Ленинградская область';
        $arResult = '';
        if ($regionName == '') $regionName = htmlspecialchars($_REQUEST['cityName']);

        file_put_contents(
            $_SERVER['DOCUMENT_ROOT'] . "/cityrequest.log",
            "\n\n" . print_r([date('d-m-Y H:s'), 'Город ', $regionName], true) . "\n",
            FILE_APPEND
        );

        if (!empty($regionName)) {
            $arResultSection = \CIBlockSection::GetList(array("SORT" => "­­ASC"), array("IBLOCK_ID" => \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_regions'), "NAME" => $regionName), false, []);
            $idSection = '';
            if ($arSection = $arResultSection->Fetch()) {
                $idSection = $arSection['ID'];
            }

            if (!empty($idSection)) {
                $arSelect = array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "CODE", 'PROPERTY_REGION_TAG_DELIVERY_REGION', 'PROPERTY_SHOW_DELIVERY_BUSINESS_LINE', 'PROPERTY_HIDE_RISE_TO_FLOOR');
                $arFilter = array("IBLOCK_ID" => \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_regions'), "SECTION_ID" => $idSection, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "PROPERTY_CITY_HOME_VALUE" => 'Y');
                $res = \CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);

                if ($arRegionCity = $res->Fetch()) {
                    $arResult = $arRegionCity;
                }
            }
        }
        return $arResult;
    }

    public function get_region_for_city($cityCode = '')
    {
        $arResult = [];
        global $arRegion;
        $arFilter = array("CODE" => $cityCode, 'IBLOCK_ID' => \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_regions'), 'ACTIVE' => 'Y');
        $resCity = \CIBlockElement::GetList(array(), $arFilter, false, false, ['ID', 'CODE', 'NAME', 'PROPERTY_REGION_TAG_DELIVERY_REGION', 'PROPERTY_SHOW_DELIVERY_BUSINESS_LINE', 'PROPERTY_HIDE_RISE_TO_FLOOR', 'PROPERTY_DELIVERY_PRICE_FOR_CITY', 'PROPERTY_SHOW_PRODUCT_BUILDING',])->Fetch();
        if (!empty($resCity)) {
            $arRegion["PROPERTY_REGION_TAG_DELIVERY_REGION_ENUM_ID"] = $resCity["PROPERTY_REGION_TAG_DELIVERY_REGION_ENUM_ID"];
            $arRegion["PROPERTY_SHOW_DELIVERY_BUSINESS_LINE_VALUE"] = $resCity["PROPERTY_SHOW_DELIVERY_BUSINESS_LINE_VALUE"];
            $arRegion['ID'] = $resCity['ID'];
            $arRegion['CODE'] = $resCity['CODE'];
            $arRegion['NAME'] = $resCity['NAME'];
            $arRegion['PROPERTY_HIDE_RISE_TO_FLOOR_VALUE'] = $resCity['PROPERTY_HIDE_RISE_TO_FLOOR_VALUE'];
            $arRegion['PROPERTY_DELIVERY_PRICE_FOR_CITY_VALUE'] = $resCity['PROPERTY_DELIVERY_PRICE_FOR_CITY_VALUE'];
            $arRegion['PROPERTY_SHOW_PRODUCT_BUILDING_VALUE'] = $resCity['PROPERTY_SHOW_PRODUCT_BUILDING_VALUE'];
            $arResult = $arRegion;
        }
        return $arResult;
    }


    public function get_deliveries()
    {
        global $USER, $arRegion;
        $arRegionHome = [];
        // получаем километраж для рассчта доставки
        if (!empty($_REQUEST['path_km'])) {
            $pathKm = intval($_REQUEST['path_km']);
        }
        //учитываем регион, если он есть
        if (!empty($_REQUEST['city'])) {
            $arRegionHome = $this->get_region_for_city($_REQUEST['city']);
        }
        //делаем подмену данных доставки, берем  например Домодедово, Московская область, возврат город Москва
        if (!empty($_REQUEST['region'])) {
            $arRegionHome = $this->get_region($_REQUEST['region']);
        }
        $arResult['HIDE_RISE_TO_FLOOR'] = ($arRegion['PROPERTY_HIDE_RISE_TO_FLOOR_VALUE'] === 'Y') ? true : false;
        if (isset($arRegionHome) && !empty($arRegionHome)) {
            $iRegionPrice = $arRegionHome["PROPERTY_REGION_TAG_DELIVERY_REGION_ENUM_ID"];
            $arRegion["PROPERTY_SHOW_DELIVERY_BUSINESS_LINE_VALUE"] = $arRegionHome["PROPERTY_SHOW_DELIVERY_BUSINESS_LINE_VALUE"];
            $arRegion['ID'] = $arRegionHome['ID'];
            $arRegion['CODE'] = $arRegionHome['CODE'];
            $arRegion['NAME'] = $arRegionHome['NAME'];
            $arRegion['HIDE_RISE_TO_FLOOR'] = ($arRegionHome['PROPERTY_HIDE_RISE_TO_FLOOR_VALUE'] == 'Y') ? true : false;
            $arResultRegionCookie = $this->setCookieRegion($arRegion['ID']);
            $arResult['region_name_COOKIE_SET'] = $arResultRegionCookie;
            //если город поменялся, тогда и корзину тоже меняем
            if ($arResultRegionCookie) {
                $arResultRegionCookie = $this->setBasketForRegion();
            }
            $arRegion["PROPERTY_DELIVERY_PRICE_FOR_CITY_VALUE"] = $arRegionHome['PROPERTY_DELIVERY_PRICE_FOR_CITY_VALUE'];
        }
        $arResult['regionName'] = $arRegion['NAME'];
        $arResult['regionId'] = $arRegion['ID'];
        $arResult['regionPrice0'] = $iRegionPrice;
        $arResult['regionHome'] = $arRegionHome;
        $arResult['requestCity'] = $_REQUEST['city'];
        $arResult['requestRegion'] = $_REQUEST['region'];
        $requestCode = $this->data->getValues()['postal_code'];
        $deliveries = \Bitrix\Sale\Delivery\Services\Table::getList(array(
            'order' => array('SORT' => 'ASC'),
            'filter' => array('ACTIVE' => 'Y'),
        ))->fetchAll();
        ///$delivery = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();

        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(\CSaleBasket::GetBasketUserID()), \Bitrix\Main\Context::getCurrent()->getSite())->getOrderableItems();
        //$basket->refreshData(array('PRICE', 'COUPONS', 'BASE_PRICE'));
        $priceAssembly = 0;
        foreach ($basket as $basketItem) {
            $products[$basketItem->getField('PRODUCT_ID')] = array(
                "QUANTITY" => $basketItem->getQuantity(),
                "PRODUCT_ID" => $basketItem->getField('PRODUCT_ID'),
            );

            $productId = \CCatalogSKU::GetProductInfo((int)$basketItem->getField('PRODUCT_ID'));
            if (!is_array($productId)) {
                $priceAssembly += (int) $basketItem->getField('PRICE');
            }
        }


        $basket = \Bitrix\Sale\Basket::create(\Bitrix\Main\Context::getCurrent()->getSite());
        foreach ($products as $product) {
            $item = $basket->createItem("catalog", $product["PRODUCT_ID"]);
            unset($product["PRODUCT_ID"]);
            $item->setFields($product);
        }

        $order = \Bitrix\Sale\Order::create(\Bitrix\Main\Context::getCurrent()->getSite(), $USER->GetID());
        $order->setPersonTypeId(1);
        $order->setBasket($basket);

        $orderProperties = $order->getPropertyCollection();
        $orderDeliveryLocation = $orderProperties->getDeliveryLocation();
        $orderDeliveryLocation->setValue($requestCode); // В какой город "доставляем" (куда доставлять).

        $arParams = array("replace_space" => "-", "replace_other" => "-");
        foreach ($deliveries as $key => $result) {
            $arDeliveries[$result['ID']] = $result;
        }
        foreach ($arDeliveries as $idDelivery => $arDelivery) {
            if (isset($arDelivery["PARENT_ID"]) && (int)$arDelivery["PARENT_ID"] > 0) {
                $arDeliveries[$arDelivery["PARENT_ID"]]['PROFILES']['AVAILABLE'] = 'Y';
                $arDeliveries[$arDelivery["PARENT_ID"]]['PROFILES']['LIST'][$arDelivery["ID"]] = [
                    'NAME' => $arDelivery["NAME"],
                    'ID' => $arDelivery["ID"],
                    'PRICE' => $arDelivery["PRICE"],
                    'OWN_NAME' => $arDelivery["NAME"],
                    'DESCRIPTION' => $arDelivery["DESCRIPTION"],
                    'FIELD_NAME' => 'DELIVERY_ID',
                    'LOGOTIP_SRC' => CFile::GetPath($arDelivery["LOGOTIP"]),
                ];
                unset($arDeliveries[$idDelivery]);
            }
        }
        if (!isset($iRegionPrice) && empty($iRegionPrice)) {
            $property_enums = \CIBlockPropertyEnum::GetList(
                array("ID" => "ASC", "SORT" => "ASC"),
                array("IBLOCK_ID" => \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_regions'), "CODE" => "REGION_TAG_DELIVERY_REGION", "XML_ID" => $arRegion['PROPERTY_REGION_TAG_DELIVERY_REGION_VALUE_XML_ID'])
            );
            if ($enum_fields = $property_enums->GetNext()) {
                $iRegionPrice = $enum_fields["ID"];
            }
        }
        foreach ($arDeliveries as $idDelivery => $result) {
            if ($result["NAME"] === "Без доставки") continue;
            $arResult['data'][$idDelivery]["id"] = $result["ID"];
            $arResult['data'][$idDelivery]["title"] = $result["NAME"];
            $arResult['data'][$idDelivery]['profiles'] = $result['PROFILES'];
            //обнуляем ключи, чтобы на фронте работало
            if (!empty($arResult['data'][$idDelivery]['profiles'])) {
                $arResult['data'][$idDelivery]['profiles']['LIST'] = array_values($arResult['data'][$idDelivery]['profiles']['LIST']);
            }
            $arResult['data'][$idDelivery]["logo"] = CFile::GetPath($result["LOGOTIP"]);
            $arResult['data'][$idDelivery]["description"] = $result["DESCRIPTION"];
            $arResult['data'][$idDelivery]["sort"] = $result["SORT"];
            $arResult["data"][$idDelivery]["types"] = $arResult["data"][$idDelivery]["deliveryTime"] = null;
            $arResult["data"][$idDelivery]["code"] = $result["CODE"];
            if ($result["CODE"] == 'CUSTOM') {
                $arResult["data"][$idDelivery]["price"] = (!empty($arRegion["PROPERTY_DELIVERY_PRICE_FOR_CITY_VALUE"])) ? $arRegion["PROPERTY_DELIVERY_PRICE_FOR_CITY_VALUE"] : $result["CONFIG"]["MAIN"]["PRICE"];
            } else {
                $arResult["data"][$idDelivery]["price"] = $result["CONFIG"]["MAIN"]["PRICE"];
            }
            if (!empty($result["CONFIG"]["MAIN"]["PERIOD"]["FROM"]) && $result["CONFIG"]["MAIN"]["PERIOD"]["TO"]) {
                $arResult["data"][$idDelivery]["deliveryTime"] = 'Примерное время доставки ' . $result["CONFIG"]["MAIN"]["PERIOD"]["FROM"] . '-' . $result["CONFIG"]["MAIN"]["PERIOD"]["TO"] . ' дней';
            }
            if (!empty($iRegionPrice)) {
                if (isset($result["CONFIG"]["MAIN"]["PRICE_" . $iRegionPrice]) && !empty($result["CONFIG"]["MAIN"]["PRICE_" . $iRegionPrice])) {
                    $arResult["data"][$idDelivery]["price"] = $result["CONFIG"]["MAIN"]["PRICE"] = $result["CONFIG"]["MAIN"]["PRICE_" . $iRegionPrice];
                }
                //подменим цену, если задана в регионах в свойстве
                if ($result["CODE"] == 'CUSTOM') {
                    $arResult["data"][$idDelivery]["price"] = (!empty($arRegion["PROPERTY_DELIVERY_PRICE_FOR_CITY_VALUE"])) ? $arRegion["PROPERTY_DELIVERY_PRICE_FOR_CITY_VALUE"] : $result["CONFIG"]["MAIN"]["PRICE"];
                }

                $extraServices = \Bitrix\Sale\Delivery\ExtraServices\Manager::getExtraServicesList($result['ID']);

                if (!empty($extraServices)) {
                    foreach ($extraServices as $key => $arExtra) {
                        $arExtraServices[$arExtra['CODE']] = $arExtra;
                    }
                    //выбираем услугу доставки с нужным кодом
                    //Стоимость подъёма на этаж
                    $arPriceLiftingCost = $arExtraServices['LIFTING_COST_' . $iRegionPrice];
                    //Подъем на лифте
                    $arPriceLift = $arExtraServices['LIFT_PRICE_' . $iRegionPrice];
                    $arResult["data"][$idDelivery]['types'][] = [
                        'id' => $arPriceLiftingCost['ID'],
                        'title' => $arPriceLiftingCost['NAME'],
                        'additionalPrice' => $arPriceLiftingCost["PARAMS"]["PRICE"],
                        'additionalOptions' => [
                            [
                                'id' => $arPriceLift['ID'],
                                'title' => $arPriceLift['NAME'],
                                'default' => false,
                                'additionalPrice' => $arPriceLift["PARAMS"]["PRICE"],
                            ],
                        ]
                    ];

                    // дополним ценой за 1 км
                    $arResult["data"][$idDelivery]['path_km'] = (int) $pathKm;
                    $arResult["data"][$idDelivery]['path_price'] = (int) $arExtraServices['PRICE_KM_' . $iRegionPrice]["PARAMS"]["PRICE"] ?? 0;
                    $arResult["data"][$idDelivery]['path_price_sum'] = (int) $arExtraServices['PRICE_KM_' . $iRegionPrice]["PARAMS"]["PRICE"] * $pathKm ?? 0;
                    $arResult["data"][$idDelivery]['assembly_path_price'] = (int) $arExtraServices['ASSEMBLE_KM_' . $iRegionPrice]["PARAMS"]["PRICE"] ?? 0;

                    if ($priceAssembly > 0) {
                        $arResult["data"][$idDelivery]['assembly_path_price_summ'] = (int) $arExtraServices['ASSEMBLE_KM_' . $iRegionPrice]["PARAMS"]["PRICE"] * $pathKm ?? 0;
                    }
                }
            }
            $arResult["data"][$idDelivery]["price_text"] = 'Доставка ' . CurrencyFormat($result["CONFIG"]["MAIN"]["PRICE"], $result["CURRENCY"]);

            $arStoresFilter = array();
            if (!empty($arRegion['ID'])) {
                $arStoresFilter['PROPERTY_LINK_REGION'] = $arRegion['ID'];
            }

            $arStores = \COrwoFunctions::GetStores($arStoresFilter);
            if (!empty($arStores) && $result["CODE"] == 'SAM' || !empty($arStores) && $result["NAME"] == 'Самовывоз') {
                foreach ($arStores as $kStore => $arStore) {
                    $arMapCoord = explode(',', $arStore["PROPERTY_MAP_VALUE"]);
                    $arResult['data'][$idDelivery]['deliveries'][] = [
                        "id" => $arStore['ID'],
                        "address" => $arStore["PROPERTY_ADDRESS_VALUE"],
                        "schedule" => $arStore["PROPERTY_SCHEDULE_VALUE"]['TEXT'],
                        "phone" => $arStore["PROPERTY_PHONE_VALUE"],
                        "coordinates" => [
                            'latio' => $arMapCoord[0],
                            'ratio' => $arMapCoord[1],
                        ]
                    ];
                }
            } else {
                // если нет пунктов самовывоза, то не выводим его
                if ($result["CODE"] == 'SAM' || $result["NAME"] == 'Самовывоз') {
                    unset($arResult["data"][$idDelivery]);
                }
            }
            //если у нас склады, то их добавляем
            if ($result["CODE"] == 'STORES') {
                //методо получает склады, в которых уже стоит галочка Отображать на карте
                $arWareHouses = \COrwoFunctions::GetWareHouses();

                if (!empty($arWareHouses)) {
                    foreach ($arWareHouses as $key => $value) {
                        //$arCoords = explode(',', $value['PROPERTY_MAP_VALUE']);
                        $arResult['data'][$idDelivery]['deliveries'][] = [
                            'id' => $value['ID'],
                            'title' => $value['TITLE'],
                            'address' => $value['ADDRESS'],
                            'description' => $value['DESCRIPTION'],
                            'phone' => $value['PHONE'],
                            'schedule' => $value['SCHEDULE'],
                            "coordinates" => [
                                'latio' => $value['GPS_N'],
                                'ratio' => $value['GPS_S'],
                            ]
                        ];
                    }
                }
            }

            $shipmentCollection = $order->getShipmentCollection();

            $delivery = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($result["ID"]);
        }
        //если у нас включена опция в регионе, отображать только Деловые линии, то убираем остальные доставки
        if (
            $arRegion["PROPERTY_SHOW_DELIVERY_BUSINESS_LINE_VALUE"] === 'Y' ||
            $arRegion["PROPERTY_SHOW_PICKUP_VALUE"] === 'Y' ||
            $arRegion["PROPERTY_SHOW_PICKUP_SALON_VALUE"] === 'Y' ||
            $arRegion["PROPERTY_SHOW_OWN_DELIVERY_VALUE"] === 'Y'
        ) {
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
            /*if ($arRegion["PROPERTY_SHOW_PICKUP_SALON_VALUE"] !== 'Y') {
            $deliveryId = \Cosmos\Config::getInstance()->getParam("PICKUP_SALON")["ID"];
            $deliveryName = \Cosmos\Config::getInstance()->getParam("PICKUP_SALON")["NAME"];
            foreach ($arResult["data"] as $key => $arDeliery) {
                if ($arDeliery['id'] === $deliveryId || $arDeliery['title'] === $deliveryName) {
                    unset($arResult["data"][$key]);
                }
            }
        }*/
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
        if (!empty($arResult["data"])) {
            $arResult["data"] = array_values($arResult["data"]);
        }
        $arResult['regionPrice'] = $iRegionPrice;
        return $arResult;
    }

    //получение городов
    public function get_cities()
    {
        $dbItems = \Bitrix\Iblock\ElementTable::getList(array(
            'order' => array('SORT' => 'ASC'), // сортировка
            'select' => array('ID', 'NAME', 'CODE', 'IBLOCK_ID', 'SORT', 'TAGS'), // выбираемые поля, без свойств. Свойства можно получать на старом ядре \CIBlockElement::getProperty
            'filter' => array('IBLOCK_ID' => \Cosmos\Config::getInstance()->getIblockIdByCode('aspro_max_regions'), 'ACTIVE' => 'Y'), // фильтр только по полям элемента, свойства (PROPERTY) использовать нельзя
        ))->fetchAll();
        $result = [];
        if (!empty($dbItems)) {
            foreach ($dbItems as $key => $arItem) {
                $result['data'][] = [
                    'title' => $arItem['NAME'],
                    'code' => $arItem['CODE'],
                ];
            }
        }
        return $result;
    }

    public function get_payments()
    {
        $paySystemResult = \Bitrix\Sale\PaySystem\Manager::getList(array(
            'order' => array('SORT' => 'ASC'),
            'filter' => array(
                'ACTIVE' => 'Y',
            )
        ))->fetchAll();
        if (!empty($paySystemResult)) {
            foreach ($paySystemResult as $key => $arPaySystem) {
                $arFields = [
                    'id' => $arPaySystem["ID"],
                    'pay_system_id' => $arPaySystem["PAY_SYSTEM_ID"],
                    'title' => $arPaySystem["NAME"],
                    'description' => strip_tags($arPaySystem["DESCRIPTION"]),
                    'short_name' => $arPaySystem["PSA_NAME"],
                    'picture' => CFile::GetPath($arPaySystem["LOGOTIP"])
                ];
                $arResult['data'][$arPaySystem['PAY_SYSTEM_ID']] = $arFields;
            }
        }
        if (!empty($arResult["data"])) {
            $arResult["data"] = array_values($arResult["data"]);
        }
        return $arResult;
    }

    public function getCityCode()
    {
        global $USER;
        $arResult = null;
        $requestCode = $this->data->getValues()['city'];
        $res = \Bitrix\Sale\Location\LocationTable::getList(array(
            'filter' => array('=NAME.NAME' => $requestCode, '=NAME.LANGUAGE_ID' => LANGUAGE_ID, 'TYPE_CODE' => array("REGION", "CITY"),),
            'select' => array('*', 'CODE' => 'CODE', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE', 'REGION_ID' => 'REGION_ID',)
        ));
        if ($item = $res->fetch()) {
            $arResult = $item["CODE"];
        }

        return $arResult;
    }

    public function promo()
    {
        global $USER, $arRegion;
        $arResultOrder = [];
        $arResultOrder["success"] = true;
        $arResultOrder["status"] = [
            "accepted" => false,
            "description" => "",
        ];
        $arResultOrder['data'] = [
            'goodsCount' => null,
            'discount' => null,
            'totalGoods' => null,
            'total' => null,

        ];
        $arRegionHome = [];
        //учитываем регион, если он есть
        if (!empty($_REQUEST['city'])) {
            $arRegionHome = $this->get_region_for_city($_REQUEST['city']);
        }
        //делаем подмену данных доставки, берем  например Домодедово, Московская область, возврат город Москва
        if (!empty($_REQUEST['region'])) {
            $arRegionHome = $this->get_region($_REQUEST['region']);
        }
        //только сюда приходит новый город, поэтому делаем обновление корзины здесь
        if (isset($arRegionHome) && !empty($arRegionHome) && $arRegion['CODE'] !== $arRegionHome['CODE']) {
            $iRegionPrice = $arRegionHome["PROPERTY_REGION_TAG_DELIVERY_REGION_ENUM_ID"];
            $arRegion["PROPERTY_SHOW_DELIVERY_BUSINESS_LINE_VALUE"] = $arRegionHome["PROPERTY_SHOW_DELIVERY_BUSINESS_LINE_VALUE"];
            $arRegion['ID'] = $arRegionHome['ID'];
            $arRegion['CODE'] = $arRegionHome['CODE'];
            $arRegion['NAME'] = $arRegionHome['NAME'];
            $arResultRegionCookie = $this->setCookieRegion($arRegion['ID']);

            $arResult['region_name_COOKIE_SET'] = $arResultRegionCookie;
            //если город поменялся, тогда и корзину тоже меняем
            if ($arResultRegionCookie) {
                $arResultRegionCookie = $this->setBasketForRegion();
            }
        }
        $siteId = \Bitrix\Main\Context::getCurrent()->getSite();
        $currencyCode = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
        if (!empty($_REQUEST['param'])) {
            //получаем корзину
            $basket = \Bitrix\Sale\Basket::loadItemsForFUser(\Bitrix\Sale\Fuser::getId(\CSaleBasket::GetBasketUserID()), \Bitrix\Main\Context::getCurrent()->getSite());
            if (!$basket->getPrice()) {
                $arResultOrder["status"]["description"] = "Корзина пуста";
            } else {
                if (\CModule::IncludeModule("sale") && \CModule::IncludeModule("catalog")) {
                    $coupon = $_REQUEST['param']; // номер купона
                    $couponinfo = \Bitrix\Sale\DiscountCouponsManager::getData($coupon, true);  // получаем информацио о купоне
                    //список купонов
                    $countCouponActive = 0;
                    $couponList = \Bitrix\Sale\DiscountCouponsManager::get();
                    foreach ($couponList as $kCoupon => $arCouponList) {
                        if ($arCouponList["DISCOUNT_ACTIVE"] == 'Y') {
                            $countCouponActive++;
                        }
                    }
                    $couponActive = false;
                    if ($countCouponActive > 0) {
                        $arResultOrder["status"]['coupons'] = implode(',', array_keys($couponList));
                        if (in_array($coupon, array_keys($couponList)) && $couponList[$coupon]["DISCOUNT_ACTIVE"] === 'Y') {
                            $arResultOrder["status"]["accepted"] = true;
                            $couponActive = true;
                            $arResultOrder["status"]["description"] = 'Купон уже активирован';
                            $discountPrice = 0;
                            // массив объектов \Bitrix\Sale\BasketItem
                            $basketItems = $basket->getBasketItems();
                            $discounts = \Bitrix\Sale\Discount::buildFromBasket($basket, new \Bitrix\Sale\Discount\Context\Fuser($basket->getFUserId(true)));
                            $r = $discounts->calculate();
                            $order = \Bitrix\Sale\Order::create($siteId, $USER->GetID());
                            $order->setField('CURRENCY', $currencyCode);
                            $arBasketDiscounts = $discounts->getApplyResult(true);

                            /** @var \Bitrix\Sale\BasketItem $basketItem */
                            foreach ($basketItems as $basketItem) {
                                // чтоб в этом цикле их вывести
                                $basketCode = $basketItem->getBasketCode();

                                if (isset($arBasketDiscounts["PRICES"]['BASKET'][$basketCode])) {
                                    //при купонах оставляем цену, какая и была, так как купон применяется 2 раза, уходим от этого
                                    $sum += $basketItem->getField('BASE_PRICE') * $basketItem->getQuantity();
                                    $discountPrice += $basketItem->getField('DISCOUNT_PRICE');
                                    //ниже код, по стандарту, оставил на всякий случай
                                    ///$sum += $arBasketDiscounts["PRICES"]['BASKET'][$basketCode]["PRICE"]*$basketItem->getQuantity();
                                } else {
                                    $sum += $basketItem->getFinalPrice();
                                    $discountPrice += $basketItem->getField('DISCOUNT_PRICE');
                                }
                            }
                            if (!empty($arBasketDiscounts['PRICES']['BASKET'])) {
                                $discountPrice = 0;
                                foreach ($arBasketDiscounts['PRICES']['BASKET'] as $idBasket => $arDiscountProduct) {
                                    $discountPrice += $arDiscountProduct['DISCOUNT'];
                                }
                            }
                            $arResultOrder["status"]["accepted"] = ($countCouponActive > 0 && isset($couponList[$coupon]) && !empty($couponList[$coupon])) ? true : false;
                            $arResultOrder['data'] = [
                                'goodsCount' => count($basketItems),
                                'discount' => $discountPrice,
                                'totalGoods' => $sum,
                                'total' => $sum,
                            ];
                            //здесь не прибавляем стоимость товаров и скидки, чтобы цена была одинаковая и верная
                            $arResultOrder['data']['totalGoods'] = round($sum);
                            $arResultOrder['data']['total'] = $sum - $discountPrice;
                            $basketInfo = $this->get_info();
                            $arResultOrder['data'] = $basketInfo['data'];
                            return $arResultOrder;
                        }
                    }
                    if ($couponinfo['ACTIVE'] == "Y" && !$couponActive /*&& $coupon !== 'undefined'*/) {
                        $addCoupon = \Bitrix\Sale\DiscountCouponsManager::add($coupon); // true - купон есть / false - его нет
                        if ($addCoupon) {
                            $basket = \Bitrix\Sale\Basket::loadItemsForFUser(
                                \CSaleBasket::GetBasketUserID(),
                                "s1"
                            )->getOrderableItems();

                            $discounts = \Bitrix\Sale\Discount::buildFromBasket($basket, new \Bitrix\Sale\Discount\Context\Fuser($basket->getFUserId(true)));
                            $r = $discounts->calculate();
                            $order = \Bitrix\Sale\Order::create($siteId, $USER->GetID());
                            $order->setField('CURRENCY', $currencyCode);
                            $arBasketDiscounts = $discounts->getApplyResult(true);
                            $order->setBasket($basket);
                            if ($r->isSuccess() && ($discountData = $r->getData()) && !empty($discountData) && is_array($discountData)) {
                                /** @var Result $r */
                                $r = $order->applyDiscount($discountData);
                                if (!$r->isSuccess()) {
                                    $arResultOrder["status"]["description"] = 'Ошибка применения промокода';
                                } else {
                                    $basketInfo = $this->get_info();
                                    $sum = $discountPrice = 0;
                                    // массив объектов \Bitrix\Sale\BasketItem
                                    $basketItems = $basket->getBasketItems();

                                    /** @var \Bitrix\Sale\BasketItem $basketItem */
                                    foreach ($basketItems as $basketItem) {
                                        // чтоб в этом цикле их вывести
                                        $basketCode = $basketItem->getBasketCode();

                                        if (isset($arBasketDiscounts["PRICES"]['BASKET'][$basketCode])) {
                                            //при купонах оставляем цену, какая и была, так как купон применяется 2 раза, уходим от этого
                                            $sum += $basketItem->getField('BASE_PRICE') * $basketItem->getQuantity();
                                            $discountPrice += $basketItem->getField('DISCOUNT_PRICE');
                                            //ниже код, по стандарту, оставил на всякий случай
                                            ///$sum += $arBasketDiscounts["PRICES"]['BASKET'][$basketCode]["PRICE"]*$basketItem->getQuantity();
                                        } else {
                                            $sum += $basketItem->getFinalPrice();
                                            $discountPrice += $basketItem->getField('DISCOUNT_PRICE');
                                        }
                                    }
                                    $arResult['DELIVERIES'] = $this->get_deliveries()["data"];
                                    //записываем для удобства расчета, чтобы можно было взять по id
                                    foreach ($arResult['DELIVERIES'] as $kDelivery => $arDelivery) {
                                        $arResult['DELIVERIES_ID'][$arDelivery['id']] = $arDelivery;
                                    }
                                    //получаем примерную цену с доставкой
                                    if (!empty($_REQUEST['delivery_id'])) {
                                        //нужно учесть город!!!
                                        $arResult['DELIVERY_PRICE'] = $arResult['DELIVERIES_ID'][$_REQUEST['delivery_id']]["price"];
                                    } else {
                                        $arResult['DELIVERY_PRICE'] = $arResult['DELIVERIES'][0]["price"];
                                    }
                                    $arResultOrder['data'] = [
                                        'goodsCount' => count($basketItems),
                                        'discount' => $discountPrice,
                                        'totalGoods' => $sum,
                                        'total' => $sum,
                                    ];
                                    if ((int)$discountPrice > 0) {
                                        $arResultOrder['data']['totalGoods'] = round($sum + $discountPrice);
                                    } else {
                                        $arResultOrder['data']['totalGoods'] = round($sum);
                                    }
                                    //делаем замену
                                    $arResultOrder['data'] = $basketInfo['data'];
                                    $couponList = \Bitrix\Sale\DiscountCouponsManager::get();
                                    foreach ($couponList as $kCoupon => $arCouponList) {
                                        if ($arCouponList["DISCOUNT_ACTIVE"] == 'Y') {
                                            $countCouponActive++;
                                        }
                                    }
                                    $arResultOrder["status"]["description"] = (isset($couponList[$coupon]) && !empty($couponList[$coupon])) ? "Промо-код активирован" : 'Неверный промокод';
                                    $arResultOrder["status"]["accepted"] = ($countCouponActive > 0 && isset($couponList[$coupon]) && !empty($couponList[$coupon])) ? true : false;

                                    return $arResultOrder;
                                }
                            }
                        } else {
                            $arResultOrder["status"]["description"] = "Промо-код уже активирован";
                        }
                    } else if (!$couponinfo['ACTIVE']) {
                        $arResultOrder["status"]["description"] = "Такого промо-кода нет, промокод не активен";
                    } else {
                        $arResultOrder["status"]["description"] = "Ошибка активации промо-кода, некорректный промокод";
                    }
                }
            }
        } else {
            //здесь нужно проверить, был ли применени промокод
            $arResultOrder["status"]["description"] = "Код купона не введен";
            //список купонов
            $countCouponActive = 0;
            $couponList = \Bitrix\Sale\DiscountCouponsManager::get();
            foreach ($couponList as $kCoupon => $arCouponList) {
                if ($arCouponList["DISCOUNT_ACTIVE"] == 'Y') {
                    $countCouponActive++;
                }
            }
            if ($countCouponActive > 0) {
                $arResultOrder["status"]['coupons'] = implode(',', array_keys($couponList));
            }
        }

        $sum = $discountPrice = 0;
        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(
            \CSaleBasket::GetBasketUserID(),
            "s1"
        )->getOrderableItems();
        $arBasketDiscounts = [];
        if (!empty($_REQUEST['param']) && $_REQUEST['param'] !== 'undefined') {
            $discounts = \Bitrix\Sale\Discount::buildFromBasket($basket, new \Bitrix\Sale\Discount\Context\Fuser($basket->getFUserId(true)));
            if (isset($discounts)) {
                $r = $discounts->calculate();
                $arBasketDiscounts = $discounts->getApplyResult(true);
            } else {
                $arBasketDiscounts = [];
            }
        }
        $order = \Bitrix\Sale\Order::create($siteId, $USER->GetID());
        $order->setField('CURRENCY', $currencyCode);
        // массив объектов \Bitrix\Sale\BasketItem
        $basketItems = $basket->getBasketItems();
        foreach ($basketItems as $basketItem) {
            // чтоб в этом цикле их вывести
            $basketCode = $basketItem->getBasketCode();

            if (isset($arBasketDiscounts["PRICES"]['BASKET'][$basketCode])) {
                //при купонах оставляем цену, какая и была, так как купон применяется 2 раза, уходим от этого
                $sum += $basketItem->getField('BASE_PRICE') * $basketItem->getQuantity();
                $discountPrice += $basketItem->getField('DISCOUNT_PRICE');
            } else {
                // $sum += $basketItem->getFinalPrice();
                $sum += $basketItem->getFinalPrice() * $basketItem->getQuantity();
                $discountPrice += $basketItem->getField('DISCOUNT_PRICE');
            }
        }
        $arResult['DELIVERIES'] = $this->get_deliveries()["data"];
        //записываем для удобства расчета, чтобы можно было взять по id
        foreach ($arResult['DELIVERIES'] as $kDelivery => $arDelivery) {
            $arResult['DELIVERIES_ID'][$arDelivery['id']] = $arDelivery;
        }
        //получаем примерную цену с доставкой
        if (!empty($_REQUEST['delivery_id'])) {
            //нужно учесть город!!!
            $arResult['DELIVERY_PRICE'] = $arResult['DELIVERIES_ID'][$_REQUEST['delivery_id']]["price"];
        } else {
            $arResult['DELIVERY_PRICE'] = $arResult['DELIVERIES'][0]["price"];
        }
        $arResultOrder["status"]["description"] = ($countCouponActive > 0 && isset($couponList[$coupon]) && !empty($couponList[$coupon])) ? "Промо-код активирован" : "Неверный промокод";
        $arResultOrder["status"]["accepted"] = ($countCouponActive > 0 && isset($couponList[$coupon]) && !empty($couponList[$coupon])) ? true : false;
        $arResultOrder['data'] = [
            'goodsCount' => count($basketItems),
            'discount' => $discountPrice,
            'totalGoods' => round($sum),
            'total' => ($sum + $arResult['DELIVERY_PRICE']),
        ];
        //так как цена здесь получается обновленной, для города, подставляем ее, передаем что промо применен
        if (isset($arRegionHome) && !empty($arRegionHome) && !(($countCouponActive > 0 && isset($couponList[$coupon]) && !empty($couponList[$coupon])))) {
            $arResultOrder["status"]["accepted"] = ($coupon !== 'undefined') ? false : true;
            $arResultOrder["data"]["total"] = $sum;
            //здесь при применении промокода считает скидка не верно, чтобы не писать код заново и подсчет, запускаем метод get_info
            $arGetInfoBasket = $this->get_info()["data"];

            $arResultOrder['basket_info'] = $arGetInfoBasket;
            if ($discountPrice > 0) {
                $arResultOrder["data"]["discount"] = $discountPrice;
            }
            if (intval($arResultOrder['basket_info']['assembly']) > 0) {
                $arResultOrder["data"]["assembly"] = $arResultOrder['basket_info']['assembly'];
                $arResultOrder['data']['assemblyPathPriceSumm'] = $arResult['DELIVERIES_ID'][$_REQUEST['delivery_id']]['assembly_path_price_summ'];
            }
            //подставляем из метода, так как подсчет скидки там верный
            if ($arResultOrder['basket_info']['discount'] > 0) {
                $arResultOrder["data"]["discount"] = $arResultOrder['basket_info']['discount'];
                $arResultOrder["data"]["totalGoods"] = round($arResultOrder['basket_info']['totalGoods']);
                //также меняем результат вычислений в этом случае
                $arResultOrder["data"]["total"] = $arResultOrder['basket_info']['total'];
            }
        } else {
            $arGetInfoBasket = $this->get_info()["data"];
            if ($arResultOrder['basket_info']['discount'] > 0) {
                $arResultOrder["data"]["discount"] = $arResultOrder['basket_info']['discount'];
                //также меняем результат вычислений в этом случае
                $arResultOrder['basket_info']['totalGoods'] += $arResultOrder['basket_info']['discount'];
                $arResultOrder["data"]["total"] = $arResultOrder['basket_info']['totalGoods'] - $arResultOrder['basket_info']['discount'];
            }
            $arResultOrder['data'] = $arGetInfoBasket;
        }
        return $arResultOrder;
    }
}
