<?php

namespace E_1;

use Bitrix\Main\Config\Option;

class Prices
{
    private static $discountsCustom = null;

    /**
     * Метод служит для получения связей ТП - продукта , возвращает массив привязок
     */
    public static function OnBasketItemsLinksHandler()
    {
        global $USER;
        if (!is_object($USER)) {
            $USER = new \CUser();
        }
        $userId = $USER->GetID();
        $strGroups = $USER->GetUserGroupString();
        $arUserGroups = explode(',', $strGroups);

        $arResult = [];

        \Bitrix\Main\Loader::IncludeModule('iblock');
        \Bitrix\Main\Loader::IncludeModule('catalog');
        \Bitrix\Main\Loader::IncludeModule('sale');
        $arProductsLinks = [];
        $fuser = \Bitrix\Sale\Fuser::getId();
        $basket = \Bitrix\Sale\Basket::loadItemsForFUser($fuser, \Bitrix\Main\Context::getCurrent()->getSite());
        //если корзина пуста, ничего не делаем
        if ($basket->getPrice()) {
            foreach ($basket as $item) {
                $aElementId[] = $item->getProductId();
            }

            //получаем элементы в Кастомных правилах
            $arSelect = [
                "ID",
                "IBLOCK_ID",
                "NAME",
                "PREVIEW_PICTURE",
                "DETAIL_PICTURE",
                "DETAIL_PAGE_URL",
            ];
            $arProducts = $arProductOfferLink = [];
            foreach ($aElementId as $key => $idProduct) {
                $mxResult = \CCatalogSku::GetProductInfo(
                    $idProduct
                );
                if (is_array($mxResult)) {
                    $arProducts[$idProduct] = $mxResult['ID']; //product keys
                    $arProductOfferLink[$mxResult['ID']] = $idProduct; //offers keys
                } else {
                    $arProducts[$idProduct] = $idProduct;
                    $arProductOfferLink[$idProduct] = $idProduct;
                }
                $arResult['PRODUCTS'] = $arProducts;
                $arResult['OFFERS'] = $arProductOfferLink;
            }
        }
        return $arResult;
    }

    /**
     * Метод получает скидки которые заданы в инфоблоке
     */
    public static function GetDiscountsCustom()
    {
        if (empty(self::$discountsCustom)) {
            $iDiscountOptimIblockId = \Cosmos\Config::getInstance()->getIblockIdByCode('discount_custom');
            $arSelect = array("ID", "NAME", "PROPERTY_SKIDKA", "PROPERTY_SKIDKA_RAZDELY", "PROPERTY_SKIDKA_TOVARY", "PROPERTY_OFF_DECOR_SKIDKA", 'PROPERTY_COUNT');
            $arFilter = array("IBLOCK_ID" => IntVal($iDiscountOptimIblockId), "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
            $res = \CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
            $aResDiscounts = [];
            while ($arFields = $res->Fetch()) {
                $item_id = $arFields['PROPERTY_SKIDKA_TOVARY_VALUE'];
                $section_id = $arFields['PROPERTY_SKIDKA_RAZDELY_VALUE'];
                $discount = abs($arFields['PROPERTY_SKIDKA_VALUE']);
                $count = (int) $arFields['PROPERTY_COUNT_VALUE'];

                // записываем наибольшую не пустую скидку
                if (!empty($item_id) && $aResDiscounts['ITEMS'][$item_id]['DISCOUNT'] < $discount) {
                    $aResDiscounts['ITEMS'][] = [
                        'DISCOUNT' => $discount,
                        'COUNT' => $count,
                        'PRODUCT_ID' => (int)$item_id,
                    ];
                    //$aResDiscounts['ITEMS'][$item_id]['DISCOUNT'] = $discount;
                    //$aResDiscounts['ITEMS'][$item_id]['COUNT'] = $count;
                }
            }
            self::$discountsCustom = $aResDiscounts;
            return self::$discountsCustom;
        }
        return self::$discountsCustom;
    }


    public static function getDiscountById($id)
    {
        $arSelect = array("ID", "IBLOCK_ID", "NAME", "PROPERTY_PROTSENT_SKIDKI");
        $arFilter = array("IBLOCK_ID" => \Cosmos\Config::getInstance()->getIblockIdByCode('1c_catalog'), '=ID' => $id);
        $res = \CIBlockElement::GetList(array(), $arFilter, false, array("nPageSize" => 50), $arSelect);

        if ($ob = $res->fetch()) {
            return $ob['PROPERTY_PROTSENT_SKIDKI_VALUE'];
        } else {
            return 0;
        }
    }

    public static function calculatePrice($price, $discountValue)
    {
        $oldPrice = ($price / (100 - $discountValue)) * 100;
        return $oldPrice;
    }


    //цены итоговая в корзине
    public static function resultPricesModify($result)
    {
        $sIsCustomBasketSale = Option::get('e1.site.settings', 'E1_SS_CUSTOM_SALE_BASKET', 'N');
        if ($sIsCustomBasketSale !== 'Y') {
            $arSaleCustom = self::GetDiscountsCustom(); //получаем скидки кастомные из инфоблока
            $iCountBasket = self::getCountBasket(); //количество товара в корзине
            $arProductsBasket = $arProductsOffers = $arProductPrice = [];
            foreach ($result['GRID']['ROWS'] as $key => $item) {
                if (!isset($result['GRID']['ROWS'][$key]["PROPS_ALL"]["ASPRO_BUY_PRODUCT_ID"]) && $result['GRID']['ROWS'][$key]["PROPS_ALL"]["ASPRO_BUY_PRODUCT_ID"]['CODE'] != 'ASPRO_BUY_PRODUCT_ID') {
                    $productId = \CCatalogSKU::GetProductInfo($item['PRODUCT_ID'])['ID'];
                    $arProductsBasket[$productId] = $arProductsBasket[$productId] + 1;
                    $arProductsOffers[] = $item['PRODUCT_ID'];
                    $arProductPrice[] = $result['GRID']['ROWS'][$key]['PRICE'];
                }
            }
            $arPriceMin = !empty($arProductPrice)?min($arProductPrice):[];
            $arKeyBasket = is_array(array_keys($arProductPrice, $arPriceMin)) ? array_keys($arProductPrice, $arPriceMin)[0] : array_keys($arProductPrice, $arPriceMin);
        }
        $iCount = 0;
        $priceWithoutDiscount = $priceAll = $priceAllSum = 0;
        foreach ($result['GRID']['ROWS'] as $key => $item) {
            $productId = \CCatalogSKU::GetProductInfo($item['PRODUCT_ID'])['ID'];
            $fakeDiscount = self::getDiscountById($productId);
            $realDiscount = $item['DISCOUNT_PRICE_PERCENT'];
            if ($realDiscount) {
                $discount = (int)($fakeDiscount + ((100 - $fakeDiscount) * ($realDiscount / 100)));
            } else {
                $discount = (int)$fakeDiscount;
            }
            //рассчитываем цену которая считается специальной функцией (на всем сайте)
            $basketItemPriceRealCalc = \COrwoFunctions::GetPriceByOfferId($item['PRODUCT_ID']);
            $basketItemPriceReal = (!empty($basketItemPriceRealCalc['OFFER']['MIN_PRICE']["DISCOUNT_VALUE"])) ? floatval($basketItemPriceRealCalc['OFFER']['MIN_PRICE']["DISCOUNT_VALUE"]) : floatval($basketItemPriceRealCalc['ELEMENT']['MIN_PRICE']["DISCOUNT_VALUE"]);
            //подставляем для расчета скидки нужную цену (актуально для оптим)
            if (!empty($basketItemPriceReal) && $basketItemPriceRealCalc['ELEMENT']["PROPERTIES"]["SERIYA_SHKAFA"]["VALUE"] === 'Оптим') {
                
                //цена изначальная
                $basePrice = $basketItemPriceReal * (100 / (100 - $discount));
                $priceSale = $basketItemPriceReal * ((100 -$realDiscount) / 100);
                $oldPrice = $basePrice;

                $result['GRID']['ROWS'][$key]['FULL_PRICE'] = $item['FULL_PRICE'] = $basePrice;
                $result['GRID']['ROWS'][$key]['PRICE'] = $item['PRICE'] = $item['~PRICE'] = round($priceSale);
                $result['GRID']['ROWS'][$key]['PRICE_FORMATED'] = CurrencyFormat(round($item['PRICE']), 'RUB');
                $result['GRID']['ROWS'][$key]['SUMM'] = $result['GRID']['ROWS'][$key]['PRICE'] * $item['QUANTITY'];
                $result['GRID']['ROWS'][$key]['SUMM_FORMATED'] = CurrencyFormat(round($result['GRID']['ROWS'][$key]['SUMM']), 'RUB');
                $result['D'][$key]['discount'] = $discount;
                $result['D'][$key]['realDiscount'] = $realDiscount;
                $result['D'][$key]['basePrice'] = $basePrice;
                $result['D'][$key]['priceSale'] = $priceSale;
                $result['D'][$key]['basketItemPriceReal'] = $basketItemPriceReal;
                $result['D'][$key]['QUANTITY'] = $item['QUANTITY'];

            } else {
                global $USER;
                $arDiscounts = \CCatalogDiscount::GetDiscountByProduct(
                    $productId,
                    $USER->GetUserGroupArray(),
                    "N",
                    [],
                    SITE_ID
                ) ?: [];
                //получаем id скидки
                $iDiscountOptim = 0;
                foreach ($arDiscounts as $k => $arDiscount) {
                    if (!empty($arDiscount['COUPON']) && $arDiscount['ACTIVE'] === 'Y') {
                        $iDiscountOptim += $arDiscount['VALUE'];
                    }
                }
                if ($iDiscountOptim > 0) {
                    $priceSale =  $result['GRID']['ROWS'][$key]['PRICE'] * (100 / (100 - $iDiscountOptim));
                    //возвращает цену до промокода
                    //$oldPrice = $priceSale* (100/(100-$iDiscountOptim));
                    //возврат базовой цены
                    $basePrice = $priceSale / ((100 - $discount) / 100);
                    $oldPrice = $basePrice;
                    //корректировка для Прайм
                    if ($iDiscountOptim > 0 && $discount > 0) {
                        $priceSale = $basePrice * ((100 - ($discount)) / 100);
                        $priceSale = $priceSale * ((100 - ($iDiscountOptim)) / 100);
                        $discountPercent = ($discount) ? $discount : $result['GRID']['ROWS'][$key]['DISCOUNT_PRICE_PERCENT'];
                        $basepriceNew = round($priceSale / ((((100 - $discountPercent) / 100))));
                        if ($basepriceNew > 0) {
                            $oldPrice = $basepriceNew;
                        }
                    }
                    $result['GRID']['ROWS'][$key]['PRICE'] = $item['PRICE'] = round($priceSale);
                    $result['GRID']['ROWS'][$key]['PRICE_FORMATED'] =  $item['PRICE_FORMATED'] = CurrencyFormat(round($result['GRID']['ROWS'][$key]['PRICE']), 'RUB');
                    $result['GRID']['ROWS'][$key]["SUM_FULL_PRICE"] = $item["SUM_VALUE"] = $item['SUM_FULL_PRICE'] = round($item['PRICE'] * $item['QUANTITY']);
                    $result['GRID']['ROWS'][$key]["SUM"] = $item['SUM'] = CurrencyFormat(round($item['SUM_FULL_PRICE']), 'RUB');
                    $result['GRID']['ROWS'][$key]["SUMM_FORMATED"] = $item['SUMM_FORMATED'] = CurrencyFormat(round($item['SUM_FULL_PRICE']), 'RUB');

                    //для скидки + промокоды
                    if ($discount > 50) {
                        $oldPrice = self::calculatePrice($item['FULL_PRICE'], $fakeDiscount);
                        $result['GRID']['ROWS'][$key]["DISCOUNT_PRICE_PERCENT"] = $discount;
                        $result['GRID']['ROWS'][$key]['PRICE'] = $item['PRICE'] = $result['GRID']['ROWS'][$key]["~PRICE"] = $oldPrice * ((100 - $result['GRID']['ROWS'][$key]["DISCOUNT_PRICE_PERCENT"]) / 100);
                        $result['GRID']['ROWS'][$key]['PRICE_FORMATED'] =  $item['PRICE_FORMATED'] = CurrencyFormat(round($result['GRID']['ROWS'][$key]['PRICE']), 'RUB');
                        $result['GRID']['ROWS'][$key]['SUM_FULL_PRICE'] =  $result['GRID']['ROWS'][$key]["SUM_VALUE"] = round($result['GRID']['ROWS'][$key]['PRICE'] * $result['GRID']['ROWS'][$key]['QUANTITY']);
                        $result['GRID']['ROWS'][$key]["SUM"] = $item['SUM'] = CurrencyFormat(round($result['GRID']['ROWS'][$key]['SUM_FULL_PRICE']), 'RUB');
                        $result['GRID']['ROWS'][$key]["SUMM_FORMATED"] = $item['SUMM_FORMATED'] = CurrencyFormat(round($result['GRID']['ROWS'][$key]['PRICE'] * $result['GRID']['ROWS'][$key]['QUANTITY']), 'RUB');
                    }
                } else {
                    $oldPrice = self::calculatePrice($item['FULL_PRICE'], $fakeDiscount);
                    //кейс кастом скидки, маркетинговые
                    //проверяем товары и делаем скидку
                    if ($sIsCustomBasketSale !== 'Y') {
                        //вначале проверим на существование в массиве значений
                        if (
                            $arProductsOffers[$arKeyBasket] == $item['PRODUCT_ID']
                            && in_array(data_get($arProductsBasket, $productId, []), array_column($arSaleCustom['ITEMS'] ?: [], 'COUNT'))
                            && in_array($iCountBasket, array_column($arSaleCustom['ITEMS'], 'COUNT'))
                            && $iCount == $arKeyBasket
                        ) {
                            //найдем по количеству нужный ключ массива
                            $iSearchKeyProduct = array_search($iCountBasket, array_column($arSaleCustom['ITEMS'], 'COUNT'));
                            //проверим чтобы все сходилось
                            if (isset($arSaleCustom['ITEMS'][$iSearchKeyProduct]['PRODUCT_ID']) && $arSaleCustom['ITEMS'][$iSearchKeyProduct]['PRODUCT_ID'] == $productId && $iCountBasket == $arSaleCustom['ITEMS'][$iSearchKeyProduct]['COUNT'] && $arProductsBasket[$productId] == $arSaleCustom['ITEMS'][$iSearchKeyProduct]['COUNT']) {

                                $procentSale = $arSaleCustom['ITEMS'][$iSearchKeyProduct]['DISCOUNT'] + $result['GRID']['ROWS'][$key]['DISCOUNT_PRICE_PERCENT'];
                                $result['GRID']['ROWS'][$key]['PRICE'] = $item['PRICE'] =  $result['GRID']['ROWS'][$key]["~PRICE"] = $result['GRID']['ROWS'][$key]["BASE_PRICE"] * ((100 - ($procentSale)) / 100);
                                $result['GRID']['ROWS'][$key]['PRICE_FORMATED'] =  $item['PRICE_FORMATED'] = CurrencyFormat(round($result['GRID']['ROWS'][$key]['PRICE']), 'RUB');
                                $result['GRID']['ROWS'][$key]['SUM_FULL_PRICE'] =  $result['GRID']['ROWS'][$key]["SUM_VALUE"] = round($result['GRID']['ROWS'][$key]['PRICE'] * $result['GRID']['ROWS'][$key]['QUANTITY']);
                                $result['GRID']['ROWS'][$key]["SUM"] = $item['SUM'] = CurrencyFormat(round($result['GRID']['ROWS'][$key]['SUM_FULL_PRICE']), 'RUB');
                                $result['GRID']['ROWS'][$key]["SUMM_FORMATED"] = $item['SUMM_FORMATED'] = CurrencyFormat(round($result['GRID']['ROWS'][$key]['PRICE'] * $result['GRID']['ROWS'][$key]['QUANTITY']), 'RUB');
                            }
                        }
                    }
                }
                if (!isset($result['GRID']['ROWS'][$key]["PROPS_ALL"]["ASPRO_BUY_PRODUCT_ID"]) && $result['GRID']['ROWS'][$key]["PROPS_ALL"]["ASPRO_BUY_PRODUCT_ID"]['CODE'] != 'ASPRO_BUY_PRODUCT_ID') {
                    $iCount++;
                }
            }
            $priceAll += $item['FULL_PRICE'];
            $priceAllSum += $item['PRICE'] * $item['QUANTITY'];
            $result['GRID']['ROWS'][$key]['FULL_PRICE_FORMATED'] = CurrencyFormat(round($oldPrice), 'RUB');
            if ($discount) {
                $result['GRID']['ROWS'][$key]['DISCOUNT_PRICE_PERCENT'] = $discount;
            }
            $priceWithoutDiscount += $oldPrice * $item['QUANTITY'];
        }
        $result['allSum'] = ($priceAllSum > 0) ? round($priceAllSum) : round($result['allSum']);
        $result['PRICE_WITHOUT_DISCOUNT'] = CurrencyFormat(round($priceWithoutDiscount), 'RUB');
        $result['DISCOUNT_PRICE_ALL'] = CurrencyFormat(round($priceAll), 'RUB');
        $result['DISCOUNT_PRICE_FORMATED'] = CurrencyFormat(round($priceWithoutDiscount - $result['allSum']), 'RUB');
        $result["allSum_FORMATED"] = CurrencyFormat(round($result['allSum']), 'RUB');
        return $result;
    }

    /**
     * получает количество товаров в корзине
     */

    public static function getCountBasket()
    {
        \CModule::IncludeModule("sale");
        //return \CSaleBasket::GetList(false, array("FUSER_ID" => \CSaleBasket::GetBasketUserID(),"LID" => SITE_ID,"ORDER_ID" => "NULL"),false,false,array("ID" ))->SelectedRowsCount();
        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(
            \CSaleBasket::GetBasketUserID(),
            "s1"
        )->getOrderableItems();
        $basketCount = 0; //считаем товары без услуг (без сборки), чтобы проверить количество товаров на соот. количеству
        foreach ($basket as $basketItem) {
            $basketPropertyCollection = $basketItem->getPropertyCollection();
            if (isset($basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']) && !empty($basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']) && $basketPropertyCollection->getPropertyValues()['ASPRO_BUY_PRODUCT_ID']['CODE'] === 'ASPRO_BUY_PRODUCT_ID') {
            } else {
                $basketCount++;
            }
        }
        return $basketCount;
    }

    //цена в оформлении заказа
    public static function orderAjaxResultModify(&$arResult, $arUserResult, $arParams)
    {
        $sIsCustomBasketSale = Option::get('e1.site.settings', 'E1_SS_CUSTOM_SALE_BASKET', 'N');
        if ($sIsCustomBasketSale !== 'Y') {
            $arSaleCustom = self::GetDiscountsCustom(); //получаем скидки кастомные из инфоблока
            $iCountBasket = self::getCountBasket(); //количество товара в корзине
            $arProductsBasket = $arProductsOffers = $arProductPrice = [];
            foreach ($arResult['JS_DATA']['GRID']['ROWS'] as $key => $item) {
                if (!isset($arResult['JS_DATA']['GRID']['ROWS'][$key]["PROPS_ALL"]["ASPRO_BUY_PRODUCT_ID"]) && $arResult['JS_DATA']['GRID']['ROWS'][$key]["PROPS_ALL"]["ASPRO_BUY_PRODUCT_ID"]['CODE'] != 'ASPRO_BUY_PRODUCT_ID') {
                    $productId = \CCatalogSKU::GetProductInfo($item['data']['PRODUCT_ID'])['ID'];
                    $arProductsBasket[$productId] = $arProductsBasket[$productId] + 1;
                    $arProductsOffers[] = $item['data']['PRODUCT_ID'];
                    $arProductPrice[] = $arResult['JS_DATA']['GRID']['ROWS'][$key]['PRICE'];
                }
            }
            $arPriceMin = min($arProductPrice);
            $arKeyBasket = is_array(array_keys($arProductPrice, $arPriceMin)) ? array_keys($arProductPrice, $arPriceMin)[0] : array_keys($arProductPrice, $arPriceMin);
        }
        $iCount = 0;
        $totalOldPrice = $priceAllSum = 0;
        foreach ($arResult['JS_DATA']['GRID']['ROWS'] as $key => $row) {
            $productId = \CCatalogSKU::GetProductInfo($row['data']['PRODUCT_ID'])['ID'];
            $fakeDiscount = self::getDiscountById($productId);
            $realDiscount = $row['data']['DISCOUNT_PRICE_PERCENT'];
            if ($realDiscount) {
                $discount = (int)($fakeDiscount + ((100 - $fakeDiscount) * ($realDiscount / 100)));
            } else {
                $discount = (int)$fakeDiscount;
            }
            global $USER;
            $arDiscounts = \CCatalogDiscount::GetDiscountByProduct(
                $productId,
                $USER->GetUserGroupArray(),
                "N",
                [],
                SITE_ID
            ) ?: [];
            //получаем id скидки
            $iDiscountOptim = 0;
            $idDiscounts = array_keys($arDiscounts);
            foreach ($arDiscounts as $k => $arDiscount) {
                if (empty($arDiscount['COUPON']) && $arDiscount['ACTIVE'] === 'Y') {
                    $iDiscountOptim += $arDiscount['VALUE'];
                }
            }
            //рассчитываем цену которая считается специальной функцией (на всем сайте)
            $basketItemPriceRealCalc = \COrwoFunctions::GetPriceByOfferId($row['data']['PRODUCT_ID']);
            $basketItemPriceReal = (!empty($basketItemPriceRealCalc['OFFER']['MIN_PRICE']["DISCOUNT_VALUE"])) ? floatval($basketItemPriceRealCalc['OFFER']['MIN_PRICE']["DISCOUNT_VALUE"]) : floatval($basketItemPriceRealCalc['ELEMENT']['MIN_PRICE']["DISCOUNT_VALUE"]);
            //подставляем для расчета скидки нужную цену (актуально для оптим)
            /**/
            if (!empty($basketItemPriceReal) && $basketItemPriceRealCalc['ELEMENT']["PROPERTIES"]["SERIYA_SHKAFA"]["VALUE"] === 'Оптим') {
                $intProcentSale = (!empty($basketItemPriceRealCalc['OFFER']['MIN_PRICE']["PROCENT_SALE"])) ? floatval($basketItemPriceRealCalc['OFFER']['MIN_PRICE']["PROCENT_SALE"]) : floatval($basketItemPriceRealCalc['ELEMENT']['MIN_PRICE']["PROCENT_SALE"]);
                if ($intProcentSale) {
                    $totalDiscountReal = $intProcentSale; //(int)($totalDiscount + ((100 - $totalDiscount) * ($intProcentSale / 100)));// 
                } else {
                    $totalDiscountReal = $intProcentSale; //(int)$totalDiscount;
                }

                //расчет скидки
                $basePrice = $basketItemPriceReal * (100 / (100 - $totalDiscountReal));
                if ($iDiscountOptim > 0) {
                    $priceSale = $basePrice * ((100 - ($totalDiscountReal + $totalDiscount)) / 100);
                    //магия для оптимы, прибавка скидки,которая будет уменьшена правилом корзины
                    //$priceSale = $priceSale* (100/(100-$iDiscountOptim));
                } else {
                    $priceSale = $basePrice * ((100 - ($totalDiscountReal + $discount)) / 100);
                }
                //если у нас задана в инфоблоке отключение декоративной скидки, то учитываем это
                if ($basketItemPriceRealCalc['OFFER']['PROPERTIES']['OFF_DECOR_DISCOUNT']['VALUE'] === 'Y') {
                    $priceSale = $basePrice * ((100 - ($totalDiscountReal)) / 100);
                    //прибавляем скидку от правил корзины, если есть
                    if ($arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]["DISCOUNT_PRICE_PERCENT"] > 0) {
                        $priceSale = $basePrice * ((100 - ($totalDiscountReal + $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]["DISCOUNT_PRICE_PERCENT"])) / 100);
                    }
                    $procentSale = $totalDiscountReal + $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]["DISCOUNT_PRICE_PERCENT"];
                }
                $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]['FULL_PRICE'] = $row['data']['FULL_PRICE'] = $row['data']['BASE_PRICE'] = $basePrice;
                $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['PRICE'] = $row['data']['PRICE'] = round($priceSale);
                $oldPrice = $basePrice;
                $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['PRICE_FORMATED'] = $row['data']['PRICE_FORMATED'] = CurrencyFormat(round($arResult['JS_DATA']['GRID']['ROWS'][$key]['data']['PRICE']), 'RUB');
                $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']["SUM_FULL_PRICE"] = $row['data'][$key]["SUM_VALUE"] = $row['data']["SUM_VALUE"] = $row['data']['SUM_FULL_PRICE'] = $row['data']['PRICE'] * $row['data']['QUANTITY'];
                $arResult['JS_DATA']['GRID']['ROWS'][$key]['data']["SUM"] = $row['data']['SUM'] = CurrencyFormat(round($row['data']['SUM_FULL_PRICE']), 'RUB');
            } else {
                $basePrice = $basketItemPriceReal * (100 / (100 - $discount));
                $oldPrice = $basePrice;
                if ($iDiscountOptim > 0) {
                    $priceSale = $basePrice * ((100 - ($discount)) / 100);
                    // прибавка скидки,которая будет уменьшена правилом корзины
                    //$priceSale = $priceSale* (100/(100-$iDiscountOptim));
                } else {
                    $priceSale = $basePrice * ((100 - ($discount)) / 100);
                }
                //проверяем товары и делаем скидку
                if ($sIsCustomBasketSale !== 'Y') {
                    //вначале проверим на существование в массиве значений
                    if (
                        $arProductsOffers[$arKeyBasket] == $row['data']['PRODUCT_ID']
                        && in_array($arProductsBasket[$productId], array_column($arSaleCustom['ITEMS'] ?: [], 'COUNT'))
                        && in_array($iCountBasket, array_column($arSaleCustom['ITEMS'] ?: [], 'COUNT'))
                        && $iCount == $arKeyBasket) {
                        //найдем по количеству нужный ключ массива
                        $iSearchKeyProduct = array_search($iCountBasket, array_column($arSaleCustom['ITEMS'], 'COUNT'));
                        //проверим чтобы все сходилось
                        if (isset($arSaleCustom['ITEMS'][$iSearchKeyProduct]['PRODUCT_ID']) && $arSaleCustom['ITEMS'][$iSearchKeyProduct]['PRODUCT_ID'] == $productId && $iCountBasket == $arSaleCustom['ITEMS'][$iSearchKeyProduct]['COUNT'] && $arProductsBasket[$productId] == $arSaleCustom['ITEMS'][$iSearchKeyProduct]['COUNT']) {

                            $procentSale = $arSaleCustom['ITEMS'][$iSearchKeyProduct]['DISCOUNT'] + $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]["DISCOUNT_PRICE_PERCENT"];
                            $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]['PRICE'] = $row['data']['PRICE'] =  $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]["~PRICE"] = $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]["BASE_PRICE"] * ((100 - ($procentSale)) / 100);
                            $result['GRID']['ROWS'][$key]['PRICE_FORMATED'] =  $row['data']['PRICE_FORMATED'] = CurrencyFormat(round($arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]['PRICE']), 'RUB');
                            $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]['SUM_FULL_PRICE'] =  $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]["SUM_VALUE"] = $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]['PRICE'];
                            $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]["SUM"] = $row['data']['SUM'] = CurrencyFormat(round($arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]['SUM_FULL_PRICE']), 'RUB');
                            $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]["SUMM_FORMATED"] = $row['data']['SUMM_FORMATED'] = CurrencyFormat(round($arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]['PRICE'] * $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]['QUANTITY']), 'RUB');
                        }
                    }
                }
            }
            if (!isset($arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]["PROPS_ALL"]["ASPRO_BUY_PRODUCT_ID"]) && $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]["PROPS_ALL"]["ASPRO_BUY_PRODUCT_ID"]['CODE'] != 'ASPRO_BUY_PRODUCT_ID') {
                $iCount++;
            }
            $priceAllSum += $row['data']['PRICE'] * $row['data']['QUANTITY'];
            $totalOldPrice += round($oldPrice) * $row['data']['QUANTITY'];
            $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]['BASE_PRICE_FORMATED'] = CurrencyFormat(round($oldPrice), 'RUB');
            $arResult['JS_DATA']['GRID']['ROWS'][$key]["data"]['SUM_BASE_FORMATED'] = CurrencyFormat(round($oldPrice * $row['data']['QUANTITY']), 'RUB');
        }

        $arResult['JS_DATA']["TOTAL"]["PRICE_WITHOUT_DISCOUNT"] = CurrencyFormat($totalOldPrice, 'RUB');
        $arResult['JS_DATA']["TOTAL"]["PRICE_WITHOUT_DISCOUNT_VALUE"] = $totalOldPrice;
        $arResult['JS_DATA']["TOTAL"]["ORDER_PRICE"] = ($priceAllSum > 0) ? $priceAllSum : $arResult['JS_DATA']["TOTAL"]["ORDER_PRICE"];
        $arResult['JS_DATA']["TOTAL"]["ORDER_PRICE_FORMATED"] = CurrencyFormat($arResult['JS_DATA']["TOTAL"]["ORDER_PRICE"], 'RUB');
        $arResult['JS_DATA']["TOTAL"]['DISCOUNT_PRICE'] = round($arResult['JS_DATA']["TOTAL"]["PRICE_WITHOUT_DISCOUNT_VALUE"] - $arResult['JS_DATA']["TOTAL"]["ORDER_PRICE"]);
        $arResult['JS_DATA']["TOTAL"]['DISCOUNT_PRICE_FORMATED'] = CurrencyFormat(round($arResult['JS_DATA']["TOTAL"]["PRICE_WITHOUT_DISCOUNT_VALUE"] - $arResult['JS_DATA']["TOTAL"]["ORDER_PRICE"]), 'RUB');
        $arResult['JS_DATA']["TOTAL"]["ORDER_TOTAL_PRICE"] = $arResult['JS_DATA']["TOTAL"]["DELIVERY_PRICE"] + $arResult['JS_DATA']["TOTAL"]["ORDER_PRICE"];
        $arResult['JS_DATA']["TOTAL"]["ORDER_TOTAL_PRICE_FORMATED"] = CurrencyFormat(round($arResult['JS_DATA']["TOTAL"]["DELIVERY_PRICE"] + $arResult['JS_DATA']["TOTAL"]["ORDER_PRICE"]), 'RUB');
    }

    //метод будет убирать скидку если она без купонов

    public static function OnGetUserDiscount(&$arResult) {
        global $APPLICATION;
        global $USER;
        $context = \Bitrix\Main\Application::getInstance()->getContext();
        $request = $context->getRequest();
        //убираем в корзине скидку чтобы было одинаково
        //$request->isAjaxRequest() - для малой корзины убираем
        if (
            $APPLICATION->GetCurPage(false) === '/basket/'
            || $APPLICATION->GetCurPage(false) === '/order/order.php'
            || (
                $request->isAjaxRequest()
                && (
                    strpos($_SERVER['REQUEST_URI'], '/ajax/showBasketHover.php') !== false
                    || strpos($_SERVER['REQUEST_URI'], '/ajax/show_basket_actual.php') !== false
                    || strpos($_SERVER['REQUEST_URI'], '/sale.basket.basket/ajax.php') !== false
                    || strpos($_SERVER['REQUEST_URI'], '/sale.order.ajax/ajax.php') !== false)
            )
        ) {
            foreach ($arResult as $id => $arSaleBasket) {
                if (empty($arSaleBasket['COUPON'])) {
                    unset($arResult[$id]);
                }
            }
        }
    }
}
