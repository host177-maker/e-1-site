<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?\Bitrix\Main\Loader::includeModule('iblock');
$arTabs = $arShowProp = array();
global $USER;

$arResult["SHOW_SLIDER_PROP"] = false;
if(strlen($arParams["FILTER_NAME"])<=0 || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"]))
{
	$arrFilter = array();
}
else
{
	$arrFilter = $GLOBALS[$arParams["FILTER_NAME"]];
	if(!is_array($arrFilter))
		$arrFilter = array();
}

$arFilter = array("ACTIVE" => "Y", "IBLOCK_ID" => $arParams["IBLOCK_ID"]);
if($arParams["SECTION_ID"])
	$arFilter[]=array("SECTION_ID" => $arParams["SECTION_ID"], "INCLUDE_SUBSECTIONS" => "Y");
elseif($arParams["SECTION_CODE"])
	$arFilter[]=array("SECTION_CODE" => $arParams["SECTION_CODE"], "INCLUDE_SUBSECTIONS" => "Y");

global $arTheme, $bShowCatalogTab;

if(!isset($arTheme["INDEX_TYPE"]["SUB_PARAMS"][$arTheme["INDEX_TYPE"]["VALUE"]]["CATALOG_TAB"]["VALUE"]))
	$bShowCatalogTab = true;

$bCatalogIndex = $bShowCatalogTab;
$arParams["SET_SKU_TITLE"] = (CMax::GetFrontParametrValue("CHANGE_TITLE_ITEM") == "Y" ? "Y" : "");
$arParams["SHOW_PROPS"] = (CMax::GetFrontParametrValue("SHOW_PROPS_BLOCK") == "Y" ? "Y" : "N");
$arParams["DISPLAY_TYPE"] = "block";
$arParams["TYPE_SKU"] = "TYPE_1";
$arParams["MAX_SCU_COUNT_VIEW"] = CMax::GetFrontParametrValue("MAX_SCU_COUNT_VIEW");
$arParams["USE_CUSTOM_RESIZE_LIST"] = CMax::GetFrontParametrValue("USE_CUSTOM_RESIZE_LIST");
$arParams["IS_COMPACT_SLIDER"] = CMax::GetFrontParametrValue("MOBILE_CATALOG_LIST_ELEMENTS_COMPACT") == 'Y' && CMax::GetFrontParametrValue("MOBILE_COMPACT_LIST_ELEMENTS") == 'slider';
$arParams["CHECK_REQUEST_BLOCK"] = CMax::checkRequestBlock('catalog_tab');
$arParams["USE_FAST_VIEW"] = CMax::GetFrontParametrValue('USE_FAST_VIEW_PAGE_DETAIL');
$arParams["DISPLAY_WISH_BUTTONS"] = CMax::GetFrontParametrValue('CATALOG_DELAY');

$arParams["USE_PERMISSIONS"] = $arParams["USE_PERMISSIONS"]=="Y";
if(!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);

$bUSER_HAVE_ACCESS = !$arParams["USE_PERMISSIONS"];
if($arParams["USE_PERMISSIONS"] && isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]))
{
	$arUserGroupArray = $USER->GetUserGroupArray();
	foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
	{
		if(in_array($PERM, $arUserGroupArray))
		{
			$bUSER_HAVE_ACCESS = true;
			break;
		}
	}
}

if(CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y')
	$arParams['SHOW_POPUP_PRICE'] = 'Y';

$arParams['TYPE_VIEW_BASKET_BTN'] = CMax::GetFrontParametrValue('TYPE_VIEW_BASKET_BTN');
$arParams['REVIEWS_VIEW'] = CMax::GetFrontParametrValue('REVIEWS_VIEW') ==  'EXTENDED';

if($arParams['OFFER_TREE_PROPS'])
{
	$keys = array_search('ARTICLE', $arParams['OFFER_TREE_PROPS']);
	if(false !== $keys)
		unset($arParams['OFFER_TREE_PROPS'][$keys]);
}


if(!in_array('DETAIL_PAGE_URL', $arParams['OFFERS_FIELD_CODE']))
	$arParams['OFFERS_FIELD_CODE'][] = 'DETAIL_PAGE_URL';
if(!in_array('NAME', $arParams['OFFERS_FIELD_CODE']))
	$arParams['OFFERS_FIELD_CODE'][] = 'NAME';

$arParams["IS_AJAX"] = CMax::checkAjaxRequest();

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

if($arParams['IS_AJAX'] == 'Y')
{
	// $APPLICATION->ShowCss();
	// $APPLICATION->ShowHeadScripts();
	$APPLICATION->ShowAjaxHead();

	// not load core.js in CJSCore:Init()
	CJSCore::markExtensionLoaded('core');

	// not load main.popup.bundle.js, ui.font.opensans.css
	$arParams["DISABLE_INIT_JS_IN_COMPONENT"] = "Y";
}

if($bCatalogIndex)
{
	if($arParams["IS_AJAX"] != 'Y')
	{
		$this->IncludeComponentTemplate();
	}
	else
	{
		$arShowProp = CMaxCache::CIBlockPropertyEnum_GetList(Array("sort" => "asc", "id" => "desc", "CACHE" => array("TAG" => CMaxCache::GetPropertyCacheTag($arParams["TABS_CODE"]))), Array("ACTIVE" => "Y", "IBLOCK_ID" => $arParams["IBLOCK_ID"], "CODE" => $arParams["TABS_CODE"]));

		if($arShowProp)
		{
			if($arParams['STORES'])
			{
				foreach($arParams['STORES'] as $key => $store)
				{
					if(!$store)
						unset($arParams['STORES'][$key]);
				}
			}
			$arFilterStores = array();
			global $arRegion;
			if(CMax::GetFrontParametrValue('USE_REGIONALITY') == 'Y')
				$arParams['USE_REGION'] = 'Y';

			$arRegion = CMaxRegionality::getCurrentRegion();
			if($arRegion && $arParams['USE_REGION'] == 'Y')
			{
				if(CMax::GetFrontParametrValue('REGIONALITY_FILTER_ITEM') == 'Y' && CMax::GetFrontParametrValue('REGIONALITY_FILTER_CATALOG') == 'Y'){
					$arFilter['PROPERTY_LINK_REGION'] = $arRegion['ID'];
					CMax::makeElementFilterInRegion($arFilter, $arRegion['ID']);
				}
				
				if($arRegion['LIST_PRICES'])
				{
					if(reset($arRegion['LIST_PRICES']) != 'component')
					{
						$arParams['PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
						$arParams['~PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
					}
				}
				if($arRegion['LIST_STORES'])
				{
					if(reset($arRegion['LIST_STORES']) != 'component')
					{
						$arParams['STORES'] = $arRegion['LIST_STORES'];
						$arParams['~STORES'] = $arRegion['LIST_STORES'];
					}

					if($arParams["HIDE_NOT_AVAILABLE"] == "Y")
					{
						
						/*
						$arTmpFilter["LOGIC"] = "OR";
						foreach($arParams['STORES'] as $storeID)
						{
							$arTmpFilter[] = array(">CATALOG_STORE_AMOUNT_".$storeID => 0);
						}
						$arFilterStores[] = $arTmpFilter;
						*/
						
						if(CMax::checkVersionModule('18.6.200', 'iblock')){
							$arTmpFilter["LOGIC"] = "OR";
							$arTmpFilter[] = array('TYPE' => array('2','3'));//complects and offers
							$arTmpFilter[] = array(
								'STORE_NUMBER' => $arParams['STORES'],
								'>STORE_AMOUNT' => 0,
							);						
						}
						else{
							if(count($arParams['STORES']) > 1){
								$arTmpFilter = array('LOGIC' => 'OR');
								foreach($arParams['STORES'] as $storeID)
								{
									$arTmpFilter[] = array(">CATALOG_STORE_AMOUNT_".$storeID => 0);
								}
							}
							else{
								foreach($arParams['STORES'] as $storeID)
								{
									$arTmpFilter = array(">CATALOG_STORE_AMOUNT_".$storeID => 0);
								}
							}
						}
						$arFilterStores[] = $arTmpFilter;
						

					}
				}
			}

			foreach($arShowProp as $key => $prop)
			{
				$arItems = array();
				$arFilterProp = array("PROPERTY_".$arParams["TABS_CODE"]."_VALUE" => array($prop));

				$arItems = CMaxCache::CIBLockElement_GetList(array('CACHE' => array("MULTI" => "N", "TAG" => CMaxCache::GetIBlockCacheTag($arParams["IBLOCK_ID"]))), array_merge($arFilter, $arrFilter, $arFilterStores, $arFilterProp), false, array("nTopCount" => 1), array("ID"));
				if($arItems)
				{
					$arTabs[$key] = array(
						"CODE" => $key,
						"TITLE" => $prop,
						"FILTER" => array_merge($arFilter, $arrFilter, $arFilterStores, $arFilterProp)
					);
					$arResult["SHOW_SLIDER_PROP"] = true;
				}
			}
		}
		else
		{
			return;
		}
		$arParams["PROP_CODE"] = $arParams["TABS_CODE"];
		$arResult["TABS"] = $arTabs;

		$arTransferParams = array(
			"SHOW_ABSENT" => $arParams["SHOW_ABSENT"],
			"HIDE_NOT_AVAILABLE_OFFERS" => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],
			"PRICE_CODE" => $arParams["PRICE_CODE"],
			"OFFER_TREE_PROPS" => $arParams["OFFER_TREE_PROPS"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
			"CURRENCY_ID" => $arParams["CURRENCY_ID"],
			"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
			"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
			"OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
			"OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
			"LIST_OFFERS_LIMIT" => $arParams["OFFERS_LIMIT"],
			"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
			"LIST_OFFERS_PROPERTY_CODE" => $arParams["OFFERS_PROPERTY_CODE"],
			"SHOW_DISCOUNT_TIME" => $arParams["SHOW_DISCOUNT_TIME"],
			"SHOW_COUNTER_LIST" => $arParams["SHOW_COUNTER_LIST"],
			"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
			"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
			"SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
			"SHOW_OLD_PRICE" => $arParams["SHOW_OLD_PRICE"],
			"SHOW_DISCOUNT_PERCENT" => $arParams["SHOW_DISCOUNT_PERCENT"],
			"SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
			"USE_REGION" => $arParams["USE_REGION"],
			"STORES" => $arParams["STORES"],
			"DEFAULT_COUNT" => $arParams["DEFAULT_COUNT"],
			"BASKET_URL" => $arParams["BASKET_URL"],
			"OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
			"PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
			"PARTIAL_PRODUCT_PROPERTIES" => $arParams["PARTIAL_PRODUCT_PROPERTIES"],
			"ADD_PROPERTIES_TO_BASKET" => ($arParams["ADD_PROPERTIES_TO_BASKET"] != "N" ? "Y" : "N"),
			"SHOW_DISCOUNT_TIME_EACH_SKU" => $arParams["SHOW_DISCOUNT_TIME_EACH_SKU"],
			"SHOW_ARTICLE_SKU" => $arParams["SHOW_ARTICLE_SKU"],
			"OFFER_ADD_PICT_PROP" => ($arParams["ADD_PROPERTIES_TO_BASKET"] != "N" ? "Y" : "N"),
			"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
			"SHOW_ONE_CLICK_BUY" => $arParams["SHOW_ONE_CLICK_BUY"],
			"DISPLAY_COMPARE" => $arParams["DISPLAY_COMPARE"],
			"DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
			"MAX_GALLERY_ITEMS" => $arParams["MAX_GALLERY_ITEMS"],
			"SHOW_GALLERY" => $arParams["SHOW_GALLERY"],
			"SHOW_PROPS" => $arParams["SHOW_PROPS"],
			"SHOW_POPUP_PRICE" => CMax::GetFrontParametrValue('SHOW_POPUP_PRICE'),
			"ADD_PICT_PROP" => $arParams["OFFER_ADD_PICT_PROP"],
			"ADD_DETAIL_TO_SLIDER" => $arParams["ADD_DETAIL_TO_SLIDER"],
			"DISPLAY_COMPARE" => CMax::GetFrontParametrValue('CATALOG_COMPARE'),
		);
		?>
		<div class="js_wrapper_items" data-params='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arTransferParams, false))?>'>
			<?$this->IncludeComponentTemplate('ajax');?>
		</div>
	<?}?>
<?}
else
	return;?>