<?
use \Bitrix\Main\Config\Option,
	\Bitrix\Main\Web\Json,
	\Bitrix\Highloadblock\HighloadBlockTable,
	\Bitrix\Main\Context;
class COrwoFunctions
{
	const iServiceIblockId = 23;
	const iStoresIblockId = 12;
	public static $arCatalogPrices;
	public static $arDefOffersSort;
    public static $arDiscountsOptim;
	
	public static function getServices(&$arItem)
	{
		$arResult = array();
		$arServiceSelect = array("IBLOCK_ID", "ID", "NAME", "DATE_ACTIVE_FROM");
		//$arServiceFilter = array("IBLOCK_ID" => self::iServiceIblockId, "ACTIVE"=>"Y", "PROPERTY_ALLOW_BUY_VALUE" => 'Y', "!PROPERTY_SERVICE_PRICE" => false);
		global $arRegion;
		$arServiceFilter = array(
		    "IBLOCK_ID" => self::iServiceIblockId, "ACTIVE"=>"Y",
		    array(
		        "LOGIC" => "OR",
		        array("PROPERTY_ALLOW_BUY_VALUE" => 'Y', "!PROPERTY_SERVICE_PRICE" => false, 'PROPERTY_LINK_REGION' => $arRegion['ID']),
		        array("PROPERTY_ALLOW_BUY_VALUE" => 'Y', "!PROPERTY_SERVICE_PRICE" => false, 'PROPERTY_LINK_REGION' => false),
		        //array("ID" => 135),
		    ),
		);

		$dbService = CIBlockElement::GetList(array("sort" => "asc"), $arServiceFilter, false, false, $arServiceSelect);
		while($obService = $dbService->GetNextElement())
		{
			$arFields = array();
			$arFields = $obService->GetFields();
			$arFields['SERVICE_PRICE'] = $obService->GetProperty('SERVICE_PRICE');
			$arFields['ITEMS_FOR_ASSEMBLY'] = $obService->GetProperty('ITEMS_FOR_ASSEMBLY');
			$arFields['PRICE'] = $obService->GetProperty('PRICE');

			self::ParseFilterCondition($arFields['ITEMS_FOR_ASSEMBLY']['~VALUE']);

			if(!empty($arFields['ITEMS_FOR_ASSEMBLY']['~VALUE'])){
				$arFilter = array(
					"LOGIC" => "AND",
					array(
						"IBLOCK_ID" => $arItem["IBLOCK_ID"],
						"ACTIVE"=>"Y",
						"ID" => $arItem["ID"],
					),
					$arFields['ITEMS_FOR_ASSEMBLY']['~VALUE']
				);
				$arTmpItems = \CMaxCache::CIBLockElement_GetList(array('CACHE' => array("TAG" => \CMaxCache::GetIBlockCacheTag($arItem["IBLOCK_ID"]), "GROUP" => "ID")), $arFilter, false, false, array("ID"));

				if(!empty($arTmpItems) || $arFields['ID'] == $arItem["ID"]){
					global $USER;
					$arPrice = CCatalogProduct::GetOptimalPrice($arFields['ID'], 1, $USER->GetUserGroupArray(), 'N');
					$iPriceVal = 0;
					if($arPrice['RESULT_PRICE']['DISCOUNT_PRICE'] != 0){
						$iPriceVal = $arPrice['RESULT_PRICE']['DISCOUNT_PRICE'];
					}
					elseif($arPrice['RESULT_PRICE']['BASE_PRICE'] != 0){
						$iPriceVal = $arPrice['RESULT_PRICE']['BASE_PRICE'];
					}

					$bIsPercentPrice = false;
					if($iPriceVal == 0 && !empty($arFields['PRICE']['VALUE'])){
						if(strpos($arFields['PRICE']['VALUE'], '%') !== false){
							$bIsPercentPrice = true;
							$iPriceVal = str_replace('%', '', $arFields['PRICE']['VALUE']);
						}
						else{
							$iPriceVal = $arFields['PRICE']['VALUE'];
						}
					}

					foreach ($arItem['JS_OFFERS'] as $sOfferKey => $arOffer)
					{
						foreach ($arOffer['DISPLAY_PROPERTIES'] as $sPropKey => $arProp) {
							if($arProp['CODE'] == $arFields['SERVICE_PRICE']['VALUE']){
								$iOfferPrice = $iPriceVal;
								if($bIsPercentPrice){
									if($arOffer['PRICE']['DISCOUNT_VALUE'] != 0 && $arOffer['PRICE']['DISCOUNT_VALUE'] < $arOffer['PRICE']['VALUE']){
										$iOfferPrice = ceil($arOffer['PRICE']['DISCOUNT_VALUE'] * ($iPriceVal / 100));
									}
									else{
										$iOfferPrice = ceil($arOffer['PRICE']['VALUE'] * ($iPriceVal / 100));
									}
								}
								if($iOfferPrice != 0 && ($arOffer['DISPLAY_PROPERTIES'][$sPropKey]['VALUE'] == 0 || empty($arOffer['DISPLAY_PROPERTIES'][$sPropKey]['VALUE']))){
									$arItem['JS_OFFERS'][$sOfferKey]['DISPLAY_PROPERTIES'][$sPropKey]['VALUE'] = $iOfferPrice;
									$arItem['JS_OFFERS'][$sOfferKey]['DISPLAY_PROPERTIES'][$sPropKey]['~VALUE'] = $iOfferPrice;
									$arItem['JS_OFFERS'][$sOfferKey]['DISPLAY_PROPERTIES'][$sPropKey]['DISPLAY_VALUE'] = $iOfferPrice;
								}
							}
						}
					}
					$arResult[$arFields['SERVICE_PRICE']['VALUE']] = $arFields['ID'];
				}
			}
			else{
				$arResult[$arFields['SERVICE_PRICE']['VALUE']] = $arFields['ID'];
			}
		}

		return $arResult;
	}

	public static function getServicesItems(&$arItem, $arParams = array()){
		global $arRegion;
		$arServices = [];
		$arServices = self::getServices($arItem);		
		$arResult = [];
		if(!empty($arItem['JS_OFFERS'])){
			foreach ($arItem['JS_OFFERS'] as $sOfferKey => $arOffer) {
				foreach ($arOffer['DISPLAY_PROPERTIES'] as $sPropKey => $arProp) {
					if(!empty($arServices[$arProp['CODE']])){
				

						$arResult[$arServices[$arProp['CODE']]] = $arServices[$arProp['CODE']];
						$arProp['ID'] = $arServices[$arProp['CODE']];
						if($arItem['PROPERTIES']['SERIYA_SHKAFA']['VALUE'] == '' && !empty($arRegion['PROPERTY_REGION_TAG_COEFFICIENT_VALUE'])){
							$arProp['VALUE'] *= $arRegion['PROPERTY_REGION_TAG_COEFFICIENT_VALUE'];
							self::RoundCatalogItemPrice($arProp['VALUE']);
						}
						$arItem['JS_OFFERS'][$sOfferKey]['SERVICES'][$arProp['CODE']] = $arProp;
					}
				}
			}
		} else {
			$arResult[$arServices['PRICE_SBORKA_SHKAFA']] = $arServices['PRICE_SBORKA_SHKAFA'];
			
			// foreach ($arServices as $sServCode => $arServ) {
			// 	if($arServ == $arItem['ID']){
			// 		$arResult[$arServ] = $arServ;
			// 	}
			// }
		}
		if(!empty($arResult)){
			$arItem["SERVICES"] = $arResult;
		}
	}

	public static function GetStores($arFilter){
		$arRegionsItemFilter = [
		    'IBLOCK_ID' => self::iStoresIblockId,
		    'INCLUDE_SUBSECTIONS' => 'Y',
		    'ACTIVE' => 'Y',
		    'ACTIVE_DATE' => 'Y'
		];

		if(!empty($arFilter)){
			$arRegionsItemFilter = array_merge($arRegionsItemFilter, $arFilter);
		}

		$arRegionsItemSelect = ['ID', 'NAME', 'IBLOCK_ID', 'DETAIL_PAGE_URL', 'PREVIEW_TEXT', 'IBLOCK_SECTION_ID', 'PROPERTY_MAP', 'PROPERTY_PHONE', 'PROPERTY_SCHEDULE', 'PROPERTY_METRO', 'PROPERTY_EMAIL', 'PROPERTY_ADDRESS', 'PROPERTY_LINK_REGION'];
		$arRegions = CMaxCache::CIblockElement_GetList(array("CACHE" => array("TAG" => CMaxCache::GetIBlockCacheTag(self::iStoresIblockId))), $arRegionsItemFilter, false, false, $arRegionsItemSelect);
		return $arRegions;
	}

	public static function GetApplicationsStats(){
		$bIncludedIblockModule = (\Bitrix\Main\Loader::includeModule("iblock"));
		$bIncludedAsproModule = (\Bitrix\Main\Loader::includeModule("aspro.max"));
		if($bIncludedIblockModule && $bIncludedAsproModule){
			$sApplicationsFileUrl = 'http://www.e1com.ru/stat.txt';
			$sApplicationsFile = file($sApplicationsFileUrl);

			$iCitiesIblockId = \CMaxRegionality::getRegionIBlockID();
			$arCitiesFilter = array('IBLOCK_ID' => $iCitiesIblockId);
			$arFileResult = array();
			$obElement = new CIBlockElement;

			$arNameReplace = array('Санкт Петербург' => 'Санкт-Петербург');
			foreach ($sApplicationsFile as $sApplicationsFileLine) {
			    //$sApplicationsFileLine = iconv("windows-1251//TRANSLIT", 'utf-8', $sApplicationsFileLine);
			    $cols = explode(";", $sApplicationsFileLine);
			    if ($cols[0] == 'city') {
			        $cols[1] = trim(str_replace(array('г. ', ' г.', ' г'), '', $cols[1]));
			        if(isset($arNameReplace[$cols[1]]) && !empty($arNameReplace[$cols[1]])){
			        	$cols[1] = $arNameReplace[$cols[1]];
			        }
			        $arFileResult['CITIES'][$cols[1]] = $cols[2];
			        $arCitiesFilter['NAME'][] = $cols[1];
			    } else {
			        $arFileResult['STATS'][trim($cols[0])] = array('name' => trim($cols[1]), 'value' => trim($cols[2]));
			    }
			}

			$arCities = array();
			$arCitiesSelect  = array('ID', 'NAME');
			$dbCitiesElement = CIBlockElement::GetList(false, $arCitiesFilter, false, false, $arCitiesSelect);
			while ($arCitiesElement = $dbCitiesElement->Fetch()) {
				$arCities[$arCitiesElement['NAME']] = $arCitiesElement['ID'];
			}

			$arFileResult['STATS']['date'] = date('d.m.Y H:i:s');
			$arFileResult['STATS'] = json_encode($arFileResult['STATS']);

			foreach ($arFileResult['CITIES'] as $key => $value) {
				if(isset($arCities[$key]) && !empty($arCities[$key])){
					$arUpdateProps = array('REGION_TAG_DELIVERY_TIME' => $value, 'REGION_TAG_APPLICATIONS_STATS' => array('VALUE' => array('TYPE' => 'TEXT', 'TEXT' => $arFileResult['STATS'])));
					CIBlockElement::SetPropertyValuesEx($arCities[$key], $iCitiesIblockId, $arUpdateProps);
				}
			}
		}
		return "COrwoFunctions::GetApplicationsStats();";
	}

	public static function PrepareCatalogItemArray(&$arResult, $sType, &$arParams) 
	{
		if($arResult['SHOW_PROPERTIES']['GLAVNAYA_STRANITSA']){
			unset($arResult['SHOW_PROPERTIES']['GLAVNAYA_STRANITSA']);
		}

		if(!isset($arParams['SHOW_OLD_PRICE'])){
			$arParams['SHOW_OLD_PRICE'] = 'Y';
		}
		if(!isset($arParams['SHOW_DISCOUNT_PERCENT'])){
			$arParams['SHOW_DISCOUNT_PERCENT'] = 'Y';
		}

		if($sType == 'list'){
			foreach ($arResult['ITEMS'] as $key => $arItem)
			{
				foreach ($arItem['OFFERS'] as $keyOffer => $arOffer)
				{
					self::ChangeCatalogItemPrice($arItem, $arOffer, $arParams);

					$arItem['OFFERS'][$keyOffer] = $arOffer;
					$arItem["PRICES"] = $arOffer["PRICES"];
					$arItem["MIN_PRICE"] = $arOffer["MIN_PRICE"];
				}
				
				$arResult['ITEMS'][$key] = $arItem;
			}
		}
		elseif($sType == 'compare' ){
			foreach ($arResult['ITEMS'] as $key => $arItem)
			{
				$sOfferKey = $arItem['OFFER_FIELDS']['ID'];
				$arItem['OFFERS'][$sOfferKey] = $arItem['OFFER_FIELDS'];
				$arItem['OFFERS'][$sOfferKey]['PROPERTIES'] = $arItem['OFFER_PROPERTIES'];
				$arItem['OFFERS'][$sOfferKey]['PRICES'] = $arItem['PRICES'];
				$arItem['OFFERS'][$sOfferKey]['MIN_PRICE'] = $arItem['MIN_PRICE'];
				foreach ($arItem['OFFERS'] as $keyOffer => $arOffer)
				{
					self::ChangeCatalogItemPrice($arItem, $arOffer, $arParams);

					$arItem["PRICES"] = $arOffer["PRICES"];
					$arItem["MIN_PRICE"] = $arOffer["MIN_PRICE"];
					$arItem['OFFERS'][$keyOffer] = $arOffer;
				}
				$arResult['ITEMS'][$key] = $arItem;
			}
		} elseif($sType == 'detail') {
			foreach ($arResult['OFFERS'] as $keyOffer => $arOffer)
			{
				self::ChangeCatalogItemPrice($arResult, $arOffer, $arParams);

				if(empty($arOffer['DETAIL_PICTURE']) && !empty($arResult['DETAIL_PICTURE'])){
					$arOffer['DETAIL_PICTURE'] = $arResult['DETAIL_PICTURE'];
				}

				if(empty($arOffer['PREVIEW_PICTURE']) && !empty($arResult['PREVIEW_PICTURE'])){
					$arOffer['PREVIEW_PICTURE'] = $arResult['PREVIEW_PICTURE'];
				}
				$arResult['OFFERS'][$keyOffer] = $arOffer;
				$arResult["PRICES"] = $arOffer["PRICES"];
				$arResult["MIN_PRICE"] = $arOffer["MIN_PRICE"];
			}

			if($arParams["SET_SKU_TITLE"] == "Y"){
				$arResult["NAME"] = $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] = ((isset($arResult["OFFERS"][$arResult["OFFER_ID_SELECTED"]]['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arResult["OFFERS"][$arResult["OFFER_ID_SELECTED"]]['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arResult["OFFERS"][$arResult["OFFER_ID_SELECTED"]]['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arResult["OFFERS"][$arResult["OFFER_ID_SELECTED"]]['NAME']);
			}
		}
	}

	public static function ChangeCatalogItemName(&$arItem, &$arOffer, $arParams) 
	{
			$obHLBlockEntityDataClass = self::GetHLBlockEntity(13);
			$obOfferNameReplace = $obHLBlockEntityDataClass::getList(array(
			   'select' => array("*"),
			   'order' => array('ID' => 'ASC'),
			   'limit' => '1',
			   'filter' => array('UF_ACTIVE' => 1, 'UF_OFFER' => $arItem['ID']) 
			));
			if($arOfferNameReplace = $obOfferNameReplace->fetch()){
				$arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] = $arOfferNameReplace['UF_NAME'];
			}
	}

    public static function GetDiscountsOptim()
    {
        if (empty(self::$arDiscountsOptim)) {
            $iDiscountOptimIblockId = \Cosmos\Config::getInstance()->getIblockIdByCode('discount_optim');
            $arSelect = Array("ID", "NAME", "PROPERTY_SKIDKA", "PROPERTY_SKIDKA_RAZDELY", "PROPERTY_SKIDKA_TOVARY", "PROPERTY_OFF_DECOR_SKIDKA");
            $arFilter = Array("IBLOCK_ID" => IntVal($iDiscountOptimIblockId), "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
            $res = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
            $aResDiscounts = [];
            while ($arFields = $res->GetNext()) {
                $item_id = $arFields['PROPERTY_SKIDKA_TOVARY_VALUE'];
                $section_id = $arFields['PROPERTY_SKIDKA_RAZDELY_VALUE'];
                $discount = abs($arFields['PROPERTY_SKIDKA_VALUE']);
                $decor_off = $arFields['PROPERTY_OFF_DECOR_SKIDKA_VALUE'];

                // записываем наибольшую не пустую скидку
                if (!empty($item_id) && $aResDiscounts['ITEMS'][$item_id]['DISCOUNT'] < $discount) {
                    $aResDiscounts['ITEMS'][$item_id]['DISCOUNT'] = $discount;
                    $aResDiscounts['ITEMS'][$item_id]['OFF_DECOR_DISCOUNT'] = $decor_off;
                }
                if (!empty($section_id) && $aResDiscounts['SECTIONS'][$section_id]['DISCOUNT'] < $discount) {
                    $aResDiscounts['SECTIONS'][$section_id]['DISCOUNT'] = $discount;
                    $aResDiscounts['SECTIONS'][$section_id]['OFF_DECOR_DISCOUNT'] = $decor_off;
                }
            }
            self::$arDiscountsOptim = $aResDiscounts;
        }
    }

    public static function ChangePriceOptim(&$arItem, &$arOffer)
    {
        $aDiscountsOptim = self::$arDiscountsOptim;

        $search_item = [];
        // ищем среди элементов
        if (!empty($aDiscountsOptim['ITEMS'][$arItem['ID']]['DISCOUNT'])) {
            $search_item = $aDiscountsOptim['ITEMS'][$arItem['ID']];
        }
        // ищем среди разделов, если не нашли среди элементов или скидка раздела больше
        if (
            !empty($aDiscountsOptim['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]['DISCOUNT'])
            && (empty($search_item) || $aDiscountsOptim['SECTIONS'][$arItem['IBLOCK_SECTION_ID']]['DISCOUNT'] > $search_item['DISCOUNT'])
        ) {
            $search_item = $aDiscountsOptim['SECTIONS'][$arItem['IBLOCK_SECTION_ID']];
        }

        if ($search_item) {
            // отключаем декоративную скидку
            if ($search_item['OFF_DECOR_DISCOUNT'] == "Y") {
                $arItem['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'] = 0;
                $arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'] = 0;
				//записываем дальше, чтобы при пересчете с правилом корзины учесть отключение декоративной скидки
                $arOffer['PROPERTIES']['OFF_DECOR_DISCOUNT']['VALUE'] = $arItem['PROPERTIES']['OFF_DECOR_DISCOUNT']['VALUE'] = 'Y';
            }
            // подставляем настоящую скидку
            foreach ($arOffer["PRICES"] as $key => &$value) {
                $value['DISCOUNT_DIFF_PERCENT'] += $search_item['DISCOUNT'];
            }
        }
    }

	public static function ChangeCatalogItemPrice(&$arItem, &$arOffer, $arParams) 
	{
		global $arRegion, $USER;

        // Процент скидки = [реальная скидка] + [декоративная скидка]
        $discountPercent = (float)$arOffer['MIN_PRICE']['DISCOUNT_DIFF_PERCENT']
            + (float)($arItem['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'] ?? 0);

        // Цена без скидки = [цена с реальной скидкой] / (1 - [процент скидки] / 100)
        $fakePrice = $discountPercent < 100
            ? round((float)$arOffer['MIN_PRICE']['DISCOUNT_VALUE'] / (1 - $discountPercent / 100))
            : $arOffer['MIN_PRICE']['VALUE'];

		if(!empty($arItem['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'])){
			$arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'] = $arItem['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'];
		}

		if($arItem['PROPERTIES']['SERIYA_SHKAFA']['VALUE'] == 'Оптим'){

            // получаем скидки на Оптиму из инфоблока
            self::GetDiscountsOptim();
            // измняем цены для Оптимы
            self::ChangePriceOptim($arItem, $arOffer);

			$iDiscountValue = 0;
			$bPriceIncrease = true;

			$sResultPriceCode = 'BASE';
			$iDiscountPriceValue = $iPriceValue = 0;
			foreach ($arOffer["PRICES"] as $key => $value) {
				if($value['DISCOUNT_DIFF_PERCENT'] != 0){
					//$arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'] += $value['DISCOUNT_DIFF_PERCENT'];
					$iDiscountValue = intval($value['DISCOUNT_DIFF_PERCENT']);
					//$bPriceIncrease = false;
				}

				if($key == self::$arCatalogPrices['DISCOUNT_PRICE_CODE']){
					$iDiscountPriceValue = $value['VALUE'];
					$sResultPriceCode = $key;
				}
				if($key == self::$arCatalogPrices['PRICE_CODE']){
					$iPriceValue = $value['VALUE'];
				}
			}

			
			$iCalcDiscountPriceValue = ($iDiscountPriceValue + floatval($arOffer['PROPERTIES']['BASE_COST_LAYOUT']['VALUE']))
														* $arRegion['PROPERTY_REGION_TAG_COEFFICIENT_VALUE'];
														
			self::RoundCatalogItemPrice($iCalcDiscountPriceValue);

			$iDiscountPriceValue = $iCalcDiscountPriceValue;
			$iPriceValue = $iDiscountPriceValue;
			if(!empty($arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'])){
				if($bPriceIncrease){
					$iPriceValue = $iDiscountPriceValue * 100 / (100-intval($arOffer["PROPERTIES"]["PROTSENT_SKIDKI"]["VALUE"]));
				}
				else{
					$iDiscountPriceValue = $iPriceValue * (100-intval($arOffer["PROPERTIES"]["PROTSENT_SKIDKI"]["VALUE"]))/100;
				}
			}

			if($iDiscountValue != 0){
				$iDiscountPriceValue = $iDiscountPriceValue * (100-$iDiscountValue)/100;
			}

			$iPriceValue = ceil($iPriceValue);
			$iDiscountPriceValue = ceil($iDiscountPriceValue);

			$arOffer["PRICES"] = array();

			$arOffer["PRICES"][$sResultPriceCode] = array(
				"MIN_PRICE" => "Y",
				"VALUE" => $iPriceValue,
				"DISCOUNT_VALUE" => $iDiscountPriceValue,
				"MIN_PRICE" => "Y",
				"CURRENCY" => "RUB",
				"PRINT_VALUE" => CurrencyFormat($iPriceValue, 'RUB'),
				"PRINT_DISCOUNT_VALUE" => CurrencyFormat($iDiscountPriceValue, 'RUB'),
				"DISCOUNT_DIFF" => $iPriceValue - $iDiscountPriceValue,
				"PRINT_DISCOUNT_DIFF" => CurrencyFormat($iPriceValue - $iDiscountPriceValue, 'RUB'),
				"CAN_ACCESS" => "Y",
				"PROCENT_SALE" => $iDiscountValue
			);

			$arOffer["MIN_PRICE"] = $arOffer["PRICES"][$sResultPriceCode];
		} else {
			$arPrices = $arOffer["PRICES"] ?? [];
			
			if(count($arPrices) > 1) {
				$sDiscountPriceKey = $sPriceKey = '';
				foreach ($arPrices as $key => $value) {
					if($key == self::$arCatalogPrices['DISCOUNT_PRICE_CODE']){
						$sDiscountPriceKey = $key;
					}
					elseif($key == self::$arCatalogPrices['PRICE_CODE']){
						$sPriceKey = $key;
					}
				}
				if(!empty($sDiscountPriceKey) && !empty($arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'])){
					$iDiscountPriceValue = $arPrices[$sDiscountPriceKey]["VALUE"];

	            	$arPrices[$sDiscountPriceKey]["DISCOUNT_VALUE"] = $iDiscountPriceValue;
	            	$arPrices[$sDiscountPriceKey]["PRINT_DISCOUNT_VALUE"] = $arPrices[$sDiscountPriceKey]["PRINT_VALUE"];

	            	$arPrices[$sDiscountPriceKey]["VALUE"] = ceil($iDiscountPriceValue * 100 / (100-intval($arOffer["PROPERTIES"]["PROTSENT_SKIDKI"]["VALUE"])));
	            	$arPrices[$sDiscountPriceKey]["PRINT_VALUE"] = CurrencyFormat($arPrices[$sDiscountPriceKey]["VALUE"], $arPrices[$sDiscountPriceKey]["CURRENCY"]);

					if($arPrices[$sDiscountPriceKey]['DISCOUNT_DIFF_PERCENT'] != 0){
						$arPrices[$sDiscountPriceKey]["DISCOUNT_VALUE"] = ceil($arPrices[$sDiscountPriceKey]["DISCOUNT_VALUE"] * (100-intval($arPrices[$sDiscountPriceKey]['DISCOUNT_DIFF_PERCENT']))/100);
						$arPrices[$sDiscountPriceKey]["PRINT_DISCOUNT_VALUE"] = CurrencyFormat($arPrices[$sDiscountPriceKey]["DISCOUNT_VALUE"], $arPrices[$sDiscountPriceKey]["CURRENCY"]);
					}
	            	$arPrices[$sDiscountPriceKey]["DISCOUNT_DIFF"] = ceil($arPrices[$sDiscountPriceKey]["VALUE"] - $arPrices[$sDiscountPriceKey]["DISCOUNT_VALUE"]);
	            	$arPrices[$sDiscountPriceKey]["PRINT_DISCOUNT_DIFF"] = CurrencyFormat($arPrices[$sDiscountPriceKey]["DISCOUNT_DIFF"], $arPrices[$sDiscountPriceKey]["CURRENCY"]);
	            	$arPrices[$sDiscountPriceKey]["DISCOUNT_DIFF_PERCENT"] = $arPrices[$sDiscountPriceKey]["PERCENT"] = round(($arPrices[$sDiscountPriceKey]["DISCOUNT_DIFF"] / $arPrices[$sDiscountPriceKey]["VALUE"])*100, 2);
	            	$arOffer["MIN_PRICE"] = $arPrices[$sDiscountPriceKey];
	            	$arPrices[$sDiscountPriceKey]["MIN_PRICE"] = "Y";
	            	unset($arPrices[$sPriceKey]);
				}
				elseif(!empty($arPrices[$sDiscountPriceKey]["VALUE"]) && $arPrices[$sDiscountPriceKey]['DISCOUNT_DIFF_PERCENT'] != 0){
					unset($arPrices[$sPriceKey]);
					$arOffer["MIN_PRICE"] = $arPrices[$sPriceKey];
				}
		        elseif(!empty($arPrices[$sDiscountPriceKey]["VALUE"]) && !empty($arPrices[$sPriceKey]["VALUE"])){
		            if(is_array($arPrices[$sDiscountPriceKey]) && is_array($arPrices[$sPriceKey])){
		            	if($arPrices[$sDiscountPriceKey]["VALUE"] < $arPrices[$sPriceKey]["VALUE"] && $arPrices[$sDiscountPriceKey]["VALUE"] != 0){
			            	$arPrices[$sPriceKey]["DISCOUNT_VALUE"] = ceil($arPrices[$sDiscountPriceKey]["VALUE"]);
			            	$arPrices[$sPriceKey]["PRINT_DISCOUNT_VALUE"] = $arPrices[$sDiscountPriceKey]["PRINT_VALUE"];
			            	$arPrices[$sPriceKey]["DISCOUNT_DIFF"] = ceil($arPrices[$sPriceKey]["VALUE"]) - ceil($arPrices[$sDiscountPriceKey]["VALUE"]);
			            	$arPrices[$sPriceKey]["PRINT_DISCOUNT_DIFF"] = CurrencyFormat($arPrices[$sPriceKey]["DISCOUNT_DIFF"], $arPrices[$sPriceKey]["CURRENCY"]);
			            	$arPrices[$sPriceKey]["DISCOUNT_DIFF_PERCENT"] = $arPrices[$sPriceKey]["PERCENT"] = round(($arPrices[$sPriceKey]["DISCOUNT_DIFF"] / ceil($arPrices[$sPriceKey]["VALUE"]))*100, 2);
			            	$arPrices[$sPriceKey]["MIN_PRICE"] = "Y";
			            	$arPrices[$sPriceKey]["OLD_NAME_LANG"] = $arPrices[$sPriceKey]["NAME_LANG"];
			            	$arPrices[$sPriceKey]["NAME_LANG"] = $arPrices[$sDiscountPriceKey]["NAME_LANG"];
		            	}
		            	if($arPrices[$sDiscountPriceKey]["VALUE"] == 0){
			            	$arPrices[$sPriceKey]["MIN_PRICE"] = "Y";
			            	//$arItem["MIN_PRICE"] = $arPrices[$sPriceKey];
		            	}

						// if($arOffer['PROPERTIES']['BASIC_CONFIGURATION']['VALUE'] == 'Да'){
						// 	$arItem['BASIC_CONFIGURATION_PRICE'] = (($arPrices[$sDiscountPriceKey]["VALUE"] < $arPrices[$sPriceKey]["VALUE"]) ? $arPrices[$sDiscountPriceKey]["VALUE"] : $arPrices[$sPriceKey]["VALUE"]);
						// }
		            	unset($arPrices[$sDiscountPriceKey]);
		            }
		            $arOffer["MIN_PRICE"] = $arPrices[$sPriceKey];
		            $arOffer["ITEM_PRICES"] = $arPrices;
		        }
		        $arItem["PRICES"] = $arPrices;
			} elseif(count($arPrices) == 1) {
				$arOffer["MIN_PRICE"] = current($arPrices);

				if(!empty($arOffer["MIN_PRICE"]) && !empty($arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'])){
					$iDiscountPriceValue = $arOffer["MIN_PRICE"]["VALUE"];
	            	$arOffer["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"] = $arOffer["MIN_PRICE"]["PRINT_VALUE"];
	            	$arOffer["MIN_PRICE"]["VALUE"] = ceil($iDiscountPriceValue * 100 / (100-intval($arOffer["PROPERTIES"]["PROTSENT_SKIDKI"]["VALUE"])));
	            	$arOffer["MIN_PRICE"]["PRINT_VALUE"] = CurrencyFormat($arOffer["MIN_PRICE"]["VALUE"], $arOffer["MIN_PRICE"]["CURRENCY"]);
	            	$arOffer["MIN_PRICE"]["DISCOUNT_DIFF"] = ceil($arOffer["MIN_PRICE"]["VALUE"] - $arOffer["MIN_PRICE"]["DISCOUNT_VALUE"]);
	            	$arOffer["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"] = CurrencyFormat($arOffer["MIN_PRICE"]["DISCOUNT_DIFF"], $arOffer["MIN_PRICE"]["CURRENCY"]);
	            	$arOffer["MIN_PRICE"]["DISCOUNT_DIFF_PERCENT"] = $arOffer["MIN_PRICE"]["PERCENT"] = intval($arOffer["MIN_PRICE"]["DISCOUNT_DIFF_PERCENT"]) > 0 ? intval($arOffer["MIN_PRICE"]["DISCOUNT_DIFF_PERCENT"]) : round(($arOffer["MIN_PRICE"]["DISCOUNT_DIFF"] / $arOffer["MIN_PRICE"]["VALUE"])*100, 2);
					$sPriceKey = key($arPrices);
	            	$arPrices[$sPriceKey] = $arOffer["MIN_PRICE"];
	            }
			}
			$arOffer["PRICES"] = $arPrices;
		}

		if(count($arOffer["PRICES"]) == 1 && empty($arOffer["MIN_PRICE"])){
			$arOffer["MIN_PRICE"] = current($arOffer["PRICES"]);
			if(!empty($arOffer["MIN_PRICE"]) && !empty($arOffer['PROPERTIES']['PROTSENT_SKIDKI']['VALUE'])){
				$iDiscountPriceValue = $arOffer["MIN_PRICE"]["VALUE"];
            	$arOffer["MIN_PRICE"]["DISCOUNT_VALUE"] = $iDiscountPriceValue;
            	$arOffer["MIN_PRICE"]["PRINT_DISCOUNT_VALUE"] = $arOffer["MIN_PRICE"]["PRINT_VALUE"];
            	$arOffer["MIN_PRICE"]["VALUE"] = ceil($iDiscountPriceValue * 100 / (100-intval($arOffer["PROPERTIES"]["PROTSENT_SKIDKI"]["VALUE"])));
            	$arOffer["MIN_PRICE"]["PRINT_VALUE"] = CurrencyFormat($arOffer["MIN_PRICE"]["VALUE"], $arOffer["MIN_PRICE"]["CURRENCY"]);

            	$arOffer["MIN_PRICE"]["DISCOUNT_DIFF"] = ceil($arOffer["MIN_PRICE"]["VALUE"] - $arOffer["MIN_PRICE"]["DISCOUNT_VALUE"]);
            	$arOffer["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"] = CurrencyFormat($arOffer["MIN_PRICE"]["DISCOUNT_DIFF"], $arOffer["MIN_PRICE"]["CURRENCY"]);
            	$arOffer["MIN_PRICE"]["DISCOUNT_DIFF_PERCENT"] = $arOffer["MIN_PRICE"]["PERCENT"] = round(($arOffer["MIN_PRICE"]["DISCOUNT_DIFF"] / $arOffer["MIN_PRICE"]["VALUE"])*100, 2);
            }
		}

        // Устанавливаем цены
        $arOffer["MIN_PRICE"]["VALUE"] = $fakePrice;
        $arOffer["MIN_PRICE"]["PRINT_VALUE"] = CurrencyFormat(
            $arOffer["MIN_PRICE"]["VALUE"],
            $arOffer["MIN_PRICE"]["CURRENCY"]
        );
        $arOffer["MIN_PRICE"]["DISCOUNT_DIFF"] = ceil(
            $arOffer["MIN_PRICE"]["VALUE"] - $arOffer["MIN_PRICE"]["DISCOUNT_VALUE"]
        );
        $arOffer["MIN_PRICE"]["PRINT_DISCOUNT_DIFF"] = CurrencyFormat(
            $arOffer["MIN_PRICE"]["DISCOUNT_DIFF"],
            $arOffer["MIN_PRICE"]["CURRENCY"]
        );
        $arOffer["MIN_PRICE"]["DISCOUNT_DIFF_PERCENT"] = $arOffer["MIN_PRICE"]["PERCENT"] = $discountPercent;

        if (!empty($arOffer["MIN_PRICE"]) && count($arOffer["PRICES"]) === 1) {
            $offset = key($arOffer["PRICES"]);
            $arOffer['PRICES'][$offset] = $arOffer['MIN_PRICE'];
        }

		$iMinProductPrice = ($arItem['MIN_PRICE']['DISCOUNT_VALUE'] < $arItem['MIN_PRICE']['VALUE']) ? $arItem['MIN_PRICE']['DISCOUNT_VALUE'] : $arItem['MIN_PRICE']['VALUE'];
		$iMinOfferPrice = ($arOffer['MIN_PRICE']['DISCOUNT_VALUE'] < $arOffer['MIN_PRICE']['VALUE']) ? $arOffer['MIN_PRICE']['DISCOUNT_VALUE'] : $arOffer['MIN_PRICE']['VALUE'];
		if(($iMinOfferPrice < $iMinProductPrice || empty($iMinProductPrice)) && !empty($iMinOfferPrice)){
			$arItem['MIN_PRICE'] = $arOffer['MIN_PRICE'];
			$arItem['OFFER_ID_SELECTED'] = $arOffer['ID'];
			$arItem['BASIC_CONFIGURATION_PRICE'] = $iMinOfferPrice;
		}
	}

	public static function GetPriceByOfferId($ID) 
	{
		global $arRegion, $USER;
		$arResult = array();
		//file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/classes/arRegion.txt', print_r($arRegion, true));
		$iBasePriceId = Option::get('e1.site.settings', 'E1_SS_BASE_PRICE', '1', 's1');
		\COrwoEvents::disableHandler();

		if(!empty($arRegion['PROPERTY_REGION_TAG_COEFFICIENT_VALUE']) && !empty($iBasePriceId)){
			$dbOffer = \CIBlockElement::GetByID($ID);
			if($obOffer = $dbOffer->GetNextElement()){
				$rsOffer = $obOffer->GetFields();
				$sSettingCatalogId = Option::get("aspro.max", "CATALOG_IBLOCK_ID", '27', 's1');
				$arSettingCatalogInfo = CCatalogSku::GetInfoByIBlock($sSettingCatalogId);
				//file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/classes/arSettingCatalogInfo.txt', print_r($arSettingCatalogInfo, true));

				if($rsOffer['IBLOCK_ID'] == $arSettingCatalogInfo['IBLOCK_ID']){
					$rsOffer['PROPERTIES']['CML2_LINK'] = $obOffer->GetProperty('CML2_LINK');
				}
				elseif($rsOffer['IBLOCK_ID'] == $arSettingCatalogInfo['PRODUCT_IBLOCK_ID']){
					$rsOffer['PROPERTIES']['CML2_LINK']['VALUE'] = $rsOffer['ID'];
				}

				if(!empty($rsOffer['PROPERTIES']['CML2_LINK']['VALUE'])){
					$dbElement = \CIBlockElement::GetByID($rsOffer['PROPERTIES']['CML2_LINK']['VALUE']);
					if($obElement = $dbElement->GetNextElement()){
						$rsElement = $obElement->GetFields();
						$rsElement['PROPERTIES']['PROTSENT_SKIDKI'] = $obElement->GetProperty('PROTSENT_SKIDKI');
						$rsElement['PROPERTIES']['SERIYA_SHKAFA'] = $obElement->GetProperty('SERIYA_SHKAFA');
						$rsElement['PROPERTIES']['BASE_COST_LAYOUT'] = $obElement->GetProperty('BASE_COST_LAYOUT');
					}

					$arPriceGroups = array();
					$arPriceGroups = explode(',', Option::get('e1.site.settings', 'E1_SS_DEFAULT_PRICE', '235,236', 's1'));
					//$arPrice = \CCatalogProduct::GetOptimalPrice($rsOffer['ID'], 1, $USER->GetUserGroupArray(), "N", array() , "s1");

					if(!empty($arRegion['LIST_PRICES'])){
						$arPriceGroups = array();
						foreach ($arRegion['LIST_PRICES'] as $sLPKey => $arListPrice) {
							$arPriceGroups[] = $arListPrice['ID'];
						}
					}

					$rsGetPrice = \Bitrix\Catalog\Model\Price::getList([
					    "filter" => [
					        "PRODUCT_ID" => $rsOffer['ID'],
					        "CATALOG_GROUP_ID" => $arPriceGroups
					    ]
					]);

					while ($arGetPrice = $rsGetPrice->fetch()) {
						if(!empty($arGetPrice['ID'])){
							$dbPriceType = \CCatalogGroup::GetList(
						        array("SORT" => "ASC"),
						        array("ID" => $arGetPrice['CATALOG_GROUP_ID'])
						    );
							if ($arPriceType = $dbPriceType->Fetch())
							{
								if(!empty($arPriceType['NAME'])){
							        $arDiscounts = CCatalogDiscount::GetDiscountByProduct($arGetPrice['PRODUCT_ID'], $USER->GetUserGroupArray(), "N");
							        if(is_array($arDiscounts) && sizeof($arDiscounts) > 0) {
							            $arGetPrice['DISCOUNT_PRICE'] = CCatalogProduct::CountPriceWithDiscount($arGetPrice['PRICE'], $arGetPrice['CURRENCY'], $arDiscounts);
							            $arGetPrice['DISCOUNT'] = $arGetPrice['PRICE'] - $arGetPrice['DISCOUNT_PRICE'];
							            $arGetPrice['PERCENT'] = round((($arGetPrice['DISCOUNT'] / $arGetPrice['PRICE']) * 100), 2);
							        }

							        if($arGetPrice['DISCOUNT_PRICE'] == 0 && $arGetPrice['PRICE'] != 0){
							        	$arGetPrice['DISCOUNT_PRICE'] = $arGetPrice['PRICE'];
							        }

									$rsOffer["PRICES"][$arPriceType['NAME']] = array(
										"VALUE" => $arGetPrice['PRICE'],
										"DISCOUNT_VALUE" => $arGetPrice['DISCOUNT_PRICE'],
										"CURRENCY" => $arGetPrice['CURRENCY'],
										"PRINT_VALUE" => CurrencyFormat($arGetPrice['PRICE'], $arGetPrice['CURRENCY']),
										"PRINT_DISCOUNT_VALUE" => CurrencyFormat($arGetPrice['DISCOUNT_PRICE'], $arGetPrice['CURRENCY']),
										"DISCOUNT_DIFF" => $arGetPrice['DISCOUNT'],
										"PRINT_DISCOUNT_DIFF" => CurrencyFormat($arGetPrice['DISCOUNT'], $arGetPrice['CURRENCY']),
										"DISCOUNT_DIFF_PERCENT" => $arGetPrice['PERCENT'],
										"CAN_ACCESS" => "Y"
									);
									//$iCalcDiscountPriceValue = ($iDiscountPriceValue + $arOffer['PROPERTIES']['BASE_COST_LAYOUT']['VALUE']) * $arRegion['PROPERTY_REGION_TAG_COEFFICIENT_VALUE'];
									///self::ChangeCatalogItemPrice($rsElement, $rsOffer, $arParams);
									
									//file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/classes/rsOffer.txt', print_r($rsOffer, true));
									//file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/classes/rsElement.txt', print_r($rsElement, true));
								}
							}
						}
					}
					\COrwoFunctions::ChangeCatalogItemPrice($rsElement, $rsOffer, []);
					$arResult['ELEMENT'] = $rsElement;
					$arResult['OFFER'] = $rsOffer;
					$arResult['BASE_PRICE_ID'] = $iBasePriceId;
				}
					
			}
		}
		\COrwoEvents::enableHandler();
	
		return $arResult;
	}

	public static function CatalogDataChangeAgent()
	{
		if(\CModule::IncludeModule("iblock") && \CModule::IncludeModule("catalog")){
			$IBLOCK_ID = 49;		

			$arElementsSelect = array("IBLOCK_ID", "ID", "NAME", "EXTERNAL_ID");
			$arElementsFilter = array("IBLOCK_ID" => $IBLOCK_ID, "ACTIVE" => "Y");

			$iCountItemsFromFile = \CIBlockElement::GetList(array("IBLOCK_ID"), $arElementsFilter, array(), false, array('ID', 'NAME', "IBLOCK_ID"));

			$iItemsPerPage = 1; //заменяю на 1, было 500
			$sCurHour = intval(date("H", strtotime("now")));
			//$sCurHour = intval(date("H", strtotime("now"))) + 1;
			$iMinuteKoef = 0;
			if(($sCurHour % 2) != 0){
				$iMinuteKoef = 60;
			}
			$sCurMinute = intval(date("i")) + $iMinuteKoef;

			$iNumPage = intval($sCurMinute);
			$iStartParseKey = $iItemsPerPage * ($iNumPage - 1);
			$iEndParseKey = $iItemsPerPage * $iNumPage;

			if($iStartParseKey <= $iCountItemsFromFile){
				$dbElements = \CIBlockElement::GetList(array("id" => "asc"), $arElementsFilter, false, array("iNumPage" => $iNumPage, "nPageSize" => $iItemsPerPage), $arElementsSelect);
				while($obElements = $dbElements->GetNextElement())
				{
					$arElements = $obElements->GetFields();
					if(!empty($arElements["ID"])){
						\COrwoEvents::OnPriceChangeHandler($arElements);
						sleep(14);//отложить на 14 сек
					}
				}
				file_put_contents(
					$_SERVER['DOCUMENT_ROOT'] . "/../logs/AGENT_" . date('d-m-Y') . ".log",
					"\n\n" . date('Y-m-d H:i:s') . "\n " . print_r([time(), date('d-m-Y H:s'), 'Запуск обновление цен, CatalogDataChangeAgent' => date('Y-m-d H:i:s') ], true) . "\n\n",
					FILE_APPEND
				); 
			}
			else{
				$dbAgent = \CAgent::GetList(array("ID" => "DESC"), array("NAME" => "\COrwoFunctions::CatalogDataChangeAgent();"));
				if($arAgent = $dbAgent->GetNext()){
				    if(!empty($arAgent['ID']) && $arAgent['NAME'] == '\COrwoFunctions::CatalogDataChangeAgent();'){
				    	\CAgent::Update($arAgent['ID'], array("ACTIVE" => "N")); 
						file_put_contents(
							$_SERVER['DOCUMENT_ROOT'] . "/../logs/AGENT_" . date('d-m-Y') . ".log",
							"\n\n" . date('Y-m-d H:i:s') . "\n " . print_r([time(), date('d-m-Y H:s'), 'Деактивация агента, CatalogDataChangeAgent' => date('Y-m-d H:i:s') ], true) . "\n\n",
							FILE_APPEND
						); 
				    }
				}
			}
		}
		return '\COrwoFunctions::CatalogDataChangeAgent();';
	}

	public static function MakeElementFilterInRegion(&$arCounterFilter, &$arPreFilter, &$arCatalogFilter, &$arParams) 
	{
		global $arRegion;

		if($arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE'] == '[]'){
			$arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE'] = '';
		}

		if(!empty($arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE'])){
			self::ParseFilterCondition($arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE']);

			if(empty($arParams['PREFILTER_NAME'])){
				$arParams['PREFILTER_NAME'] = 'arPreFilter';
			}

			if(is_array($arCounterFilter)){
                if(is_string($arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE'])){
                    $arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE'] = json_decode($arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE'], true);
                }
				$arCounterFilter = array_merge($arCounterFilter, $arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE']);
			}
			elseif(empty($arCounterFilter)){
				$arCounterFilter = $arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE'];
			}

			if(is_array($arPreFilter)){
				$GLOBALS[$arParams['PREFILTER_NAME']] = array_merge($GLOBALS[$arParams['PREFILTER_NAME']], $arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE']);
			}
			elseif(empty($arPreFilter)){
				$GLOBALS[$arParams['PREFILTER_NAME']] = $arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE'];
			}

			if(is_array($arCatalogFilter) && is_array($arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE'][0])){
				$arCatalogFilter = array_merge($arCatalogFilter, $arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE'][0]);
			}
			elseif(empty($arCatalogFilter)){
				$arCatalogFilter = $arRegion['PROPERTY_REGION_TAG_OFFERS_FILTER_VALUE'];
			}

			if(!empty($arRegion['LIST_PRICES'])){
				$arPriceFilter = ["LOGIC" => "OR"];
				foreach ($arRegion['LIST_PRICES'] as $arRegionPrice) {
					$arPriceFilter[] = ['>PRICE_' . $arRegionPrice['ID'] => 0];
				}

				// if(!empty($arPriceFilter)){
				// 	$arCatalogFilter = array_merge($arCatalogFilter, $arPriceFilter);
				// 	if(is_array($arPreFilter)){
				// 		$GLOBALS[$arParams['PREFILTER_NAME']] = array_merge($GLOBALS[$arParams['PREFILTER_NAME']], $arPriceFilter);
				// 	}
				// 	elseif(empty($arPreFilter)){
				// 		$GLOBALS[$arParams['PREFILTER_NAME']] = $arPriceFilter;
				// 	}
				// }

				// echo '<div style="display:none; font-size: 16px;" class="myDump $arPriceFilter"><pre>';
				// 	print_r($arCatalogFilter);
				// echo '</pre></div>';

				// echo '<div style="display:none; font-size: 16px;" class="myDump $arPriceFilter"><pre>';
				// 	print_r($GLOBALS[$arParams['PREFILTER_NAME']]);
				// echo '</pre></div>';

				   	// [
				    //     "LOGIC" => "OR",
				    //     array("<PROPERTY_RADIUS" => 50),
				    //     array(">=PROPERTY_RADIUS" => 50, "!=PROPERTY_CONDITION" => "Y"),
				    // ],


				// echo '<div style="display:none; font-size: 16px;" class="myDump $arCatalogFilter"><pre>';
				// 	print_r($arCatalogFilter);
				// echo '</pre></div>';
			}


		}
	}

	public static function PrepareSmartFilterArray(&$arResult, $arParams) 
	{
		if($arResult['ITEMS'])
		{
			foreach($arResult["ITEMS"] as $key => $arItem)
			{
				if(isset($arItem['PRICE']) && $arItem['PRICE'])
				{
					if($key != self::$arCatalogPrices['DISCOUNT_PRICE_CODE']){
						unset($arResult["ITEMS"][$key]);
					}
					else{
						$arResult["ITEMS"][$key]["NAME"] = '';
					}
				}
				else{
					// foreach($arItem["VALUES"] as $valKey => $arValue){
					// 	if($arValue['URL_ID'] = '339b0964-d3fd-11eb-bba5-0cc47ad88f51'){
					// 		$arResult["ITEMS"][$key]["VALUES"][$valKey]['URL_ID'] = '11111111';
					// 	}
					// }
				}
			}
		}
	}

	public static function GetCatalogPricesCodes() 
	{
		global $arRegion;
		if(empty($arRegion["LIST_PRICES"]) && \Bitrix\Main\Loader::includeModule("catalog")){
			$arPriceGroups = explode(',', Option::get('e1.site.settings', 'E1_SS_DEFAULT_PRICE'));
			$dbPriceType = CCatalogGroup::GetList(
		        array("SORT" => "ASC"),
		        array("ID" => $arPriceGroups)
		    );
			while ($arPriceType = $dbPriceType->Fetch())
			{
				$arRegion["LIST_PRICES"][$arPriceType["NAME"]] = $arPriceType;
			}
		}

		foreach ($arRegion["LIST_PRICES"] as $key => $arRegionPrice) {
			if(preg_match('/^REG\d_RETAIL_SALE$/', $key) || preg_match('/^Розница.+Скид$/', $key)){
				self::$arCatalogPrices['DISCOUNT_PRICE_CODE'] = $key;
			}
			elseif(preg_match('/^REG\d_RETAIL$/', $key) || preg_match('/^Розница.+$/', $key)){
				self::$arCatalogPrices['PRICE_CODE'] = $key;
			}
		}
	}

	public static function GetDefOffersSort()
	{
		global $arRegion;
		self::$arDefOffersSort['OFFERS_SORT_FIELD'] = 'PRICE_235';
		self::$arDefOffersSort["OFFERS_SORT_ORDER"] = "asc";

		self::$arDefOffersSort["OFFERS_SORT_FIELD2"] = "id";
		self::$arDefOffersSort["OFFERS_SORT_ORDER2"] = "asc";

		if(!empty($arRegion["PROPERTY_SORT_REGION_PRICE_VALUE"])){
			self::$arDefOffersSort["OFFERS_SORT_FIELD2"] = "PRICE_".$arRegion["PROPERTY_SORT_REGION_PRICE_VALUE"];
			self::$arDefOffersSort["OFFERS_SORT_ORDER2"] = "asc";
		}
	}

	public static function RoundCatalogItemPrice(&$price, $esta = false) {
		//return round($price / 100) * 100 - 10;
		if ($esta) {
			$price = round($price / 100) * 100 - 10;
		} else{
			$price = round($price / 100) * 100;
		} 
	}

	public static function SetCatalogDetailMeta(&$arResult, $arParams) 
	{
		if(!empty($arResult['PROPERTIES']['TIP_SHKAFA']['VALUE'])){
			$sTipShkafa = str_replace('дверные', 'дверные ', $arResult['PROPERTIES']['TIP_SHKAFA']['VALUE']);
		}

		$sH1 = (!empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arResult['NAME']);

		$arResult['META_TAGS']['BROWSER_TITLE'] = $sH1 . ' – купить {IN_CITY} на заказ, фабрика E1 | ' . $sTipShkafa . 'шкаф-купе – цены';
		$arResult['META_TAGS']['DESCRIPTION'] = $sH1 . ' – купить недорого {IN_CITY} от производителя мебельной фабрики Е1. Для вас в каталоге ☑ стильные и функциональные шкафы и гардеробные, ☑ доставка от 3 дней, ☑ гарантия до 10 лет! ' . $sH1 . ' по цене от ' . $arResult['MIN_PRICE']['VALUE'] . ' руб. – выбирайте подходящий размер, цвет и внутреннее наполнение в нашем онлайн-конструкторе.';
		//$arResult['META_TAGS']['ELEMENT_CHAIN'] = $arResult["NAME"];
	}

	public static function GetCatalogItemMinPrice(&$arResult) 
	{
		foreach ($arResult['OFFERS'] as $keyOffer => $arOffer)
		{
			if($arOffer['PROPERTIES']['BASIC_CONFIGURATION']['VALUE'] != 'Да'){
				$arResult['OFFERS'][$keyOffer]['DISPLAY_PROPERTIES']['BASE_COST_DIMENSIONS']['NAME'] = 'К базовой стоимости';
				$arResult['OFFERS'][$keyOffer]['DISPLAY_PROPERTIES']['BASE_COST_DIMENSIONS']['CODE'] = 'BASE_COST_DIMENSIONS';
				$iMinOfferPrice = ($arOffer['MIN_PRICE']['DISCOUNT_VALUE'] < $arOffer['MIN_PRICE']['VALUE']) ? $arOffer['MIN_PRICE']['DISCOUNT_VALUE'] : $arOffer['MIN_PRICE']['VALUE'];
				$arResult['OFFERS'][$keyOffer]['DISPLAY_PROPERTIES']['BASE_COST_DIMENSIONS']['DISPLAY_VALUE'] = $iMinOfferPrice - $arResult['BASIC_CONFIGURATION_PRICE'];
			}
		}
	}

	public static function ParseFilterCondition(&$arCondition) 
	{
		//if($arFields['ITEMS_FOR_ASSEMBLY']['~VALUE'] != '[]'){
		if($arCondition != '[]'){
			try{
				$arTmpGoods = Json::decode($arCondition);
			}
			catch(\Exception $e){
				$arTmpGoods = array();
			}

			if(
				array_key_exists('CHILDREN', $arTmpGoods) &&
				$arTmpGoods['CHILDREN']
			){
				$cond = new \CMaxCondition();
				try{
					$arExGoodsFilter = $cond->parseCondition($arTmpGoods, $arParams);
				}
				catch(\Exception $e){
					$arExGoodsFilter = array();
				}
				$arCondition = $arExGoodsFilter;
			}
		}
	}

	public static function GetComagicSendData($arResult, $arRequest, $sDataType) 
	{

		//$arRequest['WEB_FORM_ID'] = 3;
		//$arRequest['RESULT_ID'] = 1373;

		$arComagicSendData = [];

//{name: myName, email: myEmail, phone: myPhone, message: myMessage, is_sale: mySale, sale_cost: myCost, group_id: 12345, form_name: formName}
		if($sDataType == 'webform'){
			if(!empty($arRequest['WEB_FORM_ID']) && !empty($arRequest['RESULT_ID'])){

				$arComagicFields = [
					'name' => ['CLIENT_NAME', 'NAME'],
					'email' => ['EMAIL'],
					'phone' => ['PHONE'],
					'message' => ['QUESTION', 'POST', 'REVIEW_TEXT', 'MESSAGE', 'RESUME_TEXT'],
				];

				\CForm::GetResultAnswerArray(
					$arRequest['WEB_FORM_ID'],
					$arrColumns,
					$arrAnswers,
					$arrAnswersVarname,
					array("RESULT_ID" => $arRequest['RESULT_ID'])
				);

				if(!empty($arrAnswersVarname[$arRequest['RESULT_ID']])){
					foreach ($arrAnswersVarname[$arRequest['RESULT_ID']] as $sFormSid => $arAnswer) {
						if(!empty($arAnswer[0]['USER_TEXT'])){
							foreach ($arComagicFields as $sCFKey => $arComagicField) {
								foreach ($arComagicField as $sComagicFieldValue) {
									if($sFormSid == $sComagicFieldValue){
										$arComagicSendData[$sCFKey] = $arAnswer[0]['USER_TEXT'];
									}
								}
							}
						}
					}
					if(!empty($arComagicSendData) && !empty($arResult['arForm']['NAME'])){
						$arComagicSendData['form_name'] = $arResult['arForm']['NAME'];
					}
				}
			}
		}
		elseif($sDataType == 'oneclickbuy'){
			$arComagicSendData['name'] = $arRequest['ONE_CLICK_BUY']['FIO'];
			$arComagicSendData['email'] = $arRequest['ONE_CLICK_BUY']['EMAIL'];
			$arComagicSendData['phone'] = $arRequest['ONE_CLICK_BUY']['PHONE'];
			$arComagicSendData['message'] = $arRequest['ONE_CLICK_BUY']['COMMENT'];
			$arComagicSendData['is_sale'] = true;
			$arComagicSendData['sale_cost'] = $arResult['prod_summ'];
		}
		elseif($sDataType == 'order' && !empty($arResult['ORDER']['ID'])){
			$dbOrderProps = CSaleOrderPropsValue::GetOrderProps($arResult['ORDER']['ID']);
			while ($rsOrderProps = $dbOrderProps->Fetch())
			{
				$arResult['ORDER']['PROPS'][$rsOrderProps['CODE']] = $rsOrderProps;
			}
			$arComagicSendData['name'] = $arResult['ORDER']['PROPS']['FIO']['VALUE'];
			$arComagicSendData['email'] = $arResult['ORDER']['PROPS']['EMAIL']['VALUE'];
			$arComagicSendData['phone'] = $arResult['ORDER']['PROPS']['PHONE']['VALUE'];
			$arComagicSendData['message'] = $arResult['ORDER']['USER_DESCRIPTION'];
			$arComagicSendData['is_sale'] = true;
			$arComagicSendData['sale_cost'] = $arResult['ORDER']['PRICE'];
		}

		return $arComagicSendData;
	}

	public static function GetHLBlockEntity($iHLBlockId) 
	{
		if(CModule::IncludeModule('highloadblock')){
		    $obHLBlock = HighloadBlockTable::getById($iHLBlockId)->fetch();
		    $obHLBlockEntity = HighloadBlockTable::compileEntity($obHLBlock);
		    $obHLBlockEntityDataClass = $obHLBlockEntity->getDataClass();
		    return $obHLBlockEntityDataClass;
		}
	}

	public static function GetWordEnding($str, $arr = array()) 
	{
	    $iskl=array("11","12","13","14");
	    $num=array(
	        array("1"),
	        array("2","3","4"),
	        array("5","6","7","8","9","0")
	        );
	    $str=(string)$str;
	    if (in_array(substr($str, -2),$iskl)){
	        $curIndex=2;
	    } else {
	        foreach ($num as $key=>$val) {
	            if (in_array(substr($str, -1),$val)){
	                $curIndex=$key;
	                break;
	            }
	        }
	    }
	    return $arr[$curIndex];
	}

	public static function ShowAddidgitalJsScripts(){
		global $APPLICATION;
		$sJsString = '';
		if( $APPLICATION->GetProperty("HIDE_ADDIDGITAL_JS") != 'Y' ){
			$sJsString = '<script type="text/javascript" src="//event.getblue.io/js/blue-tag.min.js" async="true" data-type="getblue"></script>';
		}
		return $sJsString;
	}

	public static function IsMainPage(){
		$request = Context::getCurrent()->getRequest();
		$sCurPage = $request->getRequestedPageDirectory();
		if(empty($sCurPage)){
			return true;
		}
		return false;
	}

	public static function GetWareHouses() {
		$res = CCatalogStore::GetList(array("ADDRESS" => "ASC"), array("ACTIVE" => "Y", "UF_SHOW" => 1), false, false);
		while($wareHouse = $res->fetch()) {
			$wareHouses[] = $wareHouse;
		}
		return $wareHouses;
	}
}
?>