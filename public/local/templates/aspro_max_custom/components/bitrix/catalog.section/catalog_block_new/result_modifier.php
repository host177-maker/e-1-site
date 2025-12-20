<?
use Bitrix\Main\Type\Collection;
use Bitrix\Currency\CurrencyTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
CModule::IncludeModule("blog");
// расчет рейтинга
$arIds = $arIdsPost = $arCommentsRating = $valueRating = [];
foreach ($arResult['ITEMS'] as $key => $arItem)
{ 
	$arIds[] = $arItem['PROPERTIES']["BLOG_POST_ID"]["VALUE"];
	$arIdsPost[$arItem['PROPERTIES']["BLOG_POST_ID"]["VALUE"]] = $arItem["ID"];
}
$commentsCount = $commentsRating = $commentsCountRaiting =  0;
$resBlog = CBlogComment::GetList(array("ID"=>"DESC"), array('POST_ID' => $arIds, 'PARENT_ID' => false, 'PUBLISH_STATUS' => 'P'), false, false, array('ID', 'UF_ASPRO_COM_RATING','BLOG_ID','POST_ID', 'AUTHOR_NAME'));
while( $comment = $resBlog->Fetch() ) {
	$arCommentsRating[$comment['POST_ID']][] = $comment;
}
foreach($arCommentsRating as $postId => $arComment){
	foreach ($arComment as $numbComment => $arCommentValue) {
		$commentsCountRaiting++;
		$commentsRating += $arCommentValue['UF_ASPRO_COM_RATING'];
	}
	$valueRating[$postId] = round( $commentsRating/$commentsCountRaiting, 1 );
	$commentsCount = $commentsRating = $commentsCountRaiting =  0;
}

foreach($arResult['ITEMS'] as $key => $arItem){
	if (isset($valueRating[$arItem['PROPERTIES']["BLOG_POST_ID"]["VALUE"]]) && !empty($valueRating[$arItem['PROPERTIES']["BLOG_POST_ID"]["VALUE"]])) {
		$arResult['ITEMS'][$key]['RESULT_RATING'] = $valueRating[$arItem['PROPERTIES']["BLOG_POST_ID"]["VALUE"]];
	} else {
		$arResult['ITEMS'][$key]['RESULT_RATING'] = 0;
	}
}
// расчет рейтинга end

//start e1 fix
COrwoFunctions::PrepareCatalogItemArray($arResult, 'list', $arParams);
//end e1 fix

$arDefaultParams = array(
	'TYPE_SKU' => 'N',
	'FILTER_HIT_PROP' => 'block',
	'OFFER_TREE_PROPS' => array('-'),
);
$arParams = array_merge($arDefaultParams, $arParams);

if(isset($arParams['STORES'])) {
	foreach($arParams['STORES'] as $key => $store) {
		if(!$store) {
			unset($arParams['STORES'][$key]);
		}
	}
}

if ('TYPE_1' != $arParams['TYPE_SKU'] )
	$arParams['TYPE_SKU'] = 'N';

if ('TYPE_1' == $arParams['TYPE_SKU'] && $arParams['DISPLAY_TYPE'] !='table' )
{
	if (!is_array($arParams['OFFER_TREE_PROPS']))
		$arParams['OFFER_TREE_PROPS'] = array($arParams['OFFER_TREE_PROPS']);
	foreach ($arParams['OFFER_TREE_PROPS'] as $key => $value)
	{
		$value = (string)$value;
		if ('' == $value || '-' == $value)
			unset($arParams['OFFER_TREE_PROPS'][$key]);
	}
	if(empty($arParams['OFFER_TREE_PROPS']) && isset($arParams['OFFERS_CART_PROPERTIES']) && is_array($arParams['OFFERS_CART_PROPERTIES']))
	{
		$arParams['OFFER_TREE_PROPS'] = $arParams['OFFERS_CART_PROPERTIES'];
		foreach ($arParams['OFFER_TREE_PROPS'] as $key => $value)
		{
			$value = (string)$value;
			if ('' == $value || '-' == $value)
				unset($arParams['OFFER_TREE_PROPS'][$key]);
		}
	}
}
else
{
	$arParams['OFFER_TREE_PROPS'] = array();
}

/*stores product*/
$arStores=CMaxCache::CCatalogStore_GetList(array(), array("ACTIVE" => "Y"), false, false, array());
$arResult["STORES_COUNT"] = (count($arStores) && (CMax::GetFrontParametrValue("USE_STORE_QUANTITY")== "Y"));

/* hide compare link from module options */
if(CMax::GetFrontParametrValue('CATALOG_COMPARE') == 'N')
	$arParams["DISPLAY_COMPARE"] = 'N';
/**/

if (!empty($arResult['ITEMS'])){
	$arConvertParams = array();
	if ('Y' == $arParams['CONVERT_CURRENCY'])
	{
		if (!CModule::IncludeModule('currency'))
		{
			$arParams['CONVERT_CURRENCY'] = 'N';
			$arParams['CURRENCY_ID'] = '';
		}
		else
		{
			$arResultModules['currency'] = true;
			if($arResult['CURRENCY_ID'])
			{
				$arConvertParams['CURRENCY_ID'] = $arResult['CURRENCY_ID'];
			}
			else
			{
				$arCurrencyInfo = CCurrency::GetByID($arParams['CURRENCY_ID']);
				if (!(is_array($arCurrencyInfo) && !empty($arCurrencyInfo)))
				{
					$arParams['CONVERT_CURRENCY'] = 'N';
					$arParams['CURRENCY_ID'] = '';
				}
				else
				{
					$arParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
					$arConvertParams['CURRENCY_ID'] = $arCurrencyInfo['CURRENCY'];
				}
			}
		}
	}

	$arEmptyPreview = false;
	$strEmptyPreview = $this->GetFolder().'/images/no_photo.png';
	if (file_exists($_SERVER['DOCUMENT_ROOT'].$strEmptyPreview))
	{
		$arSizes = getimagesize($_SERVER['DOCUMENT_ROOT'].$strEmptyPreview);
		if (!empty($arSizes))
		{
			$arEmptyPreview = array(
				'SRC' => $strEmptyPreview,
				'WIDTH' => intval($arSizes[0]),
				'HEIGHT' => intval($arSizes[1])
			);
		}
		unset($arSizes);
	}
	unset($strEmptyPreview);

	$arSKUPropList = array();
	$arSKUPropIDs = array();
	$arSKUPropKeys = array();
	$boolSKU = false;
	$strBaseCurrency = '';
	$boolConvert = isset($arResult['CONVERT_CURRENCY']['CURRENCY_ID']);

	if ($arResult['MODULES']['catalog'])
	{
		if (!$boolConvert)
			$strBaseCurrency = CCurrency::GetBaseCurrency();

		$arSKU = CCatalogSKU::GetInfoByProductIBlock($arParams['IBLOCK_ID']);

		$boolSKU = !empty($arSKU) && is_array($arSKU);
		if ( $boolSKU && $featureProps = \Bitrix\Iblock\Model\PropertyFeature::getListPageShowPropertyCodes( $arSKU["IBLOCK_ID"], array('CODE' => 'Y') ) ) {
			$arParams['OFFER_TREE_PROPS'] = $featureProps;
		}
		if ($boolSKU && !empty($arParams['OFFER_TREE_PROPS']))
		{

			$arResult["SKU_IBLOCK_ID"]=$arSKU["IBLOCK_ID"];
			$arNeedValues = array();
			$arSKUPropIDs = array("HEIGHT", "WIDTH", "DEPTH", "COLOR_REF","HOUSING_LAYOUT","VARIANT_ISPOLNENIYA");
			if($featureProps) {
				$arParams['OFFER_TREE_PROPS'] = $arSKUPropIDs;
			}

			if (empty($arSKUPropIDs))
				$arParams['TYPE_SKU'] = 'N';
			else
				$arSKUPropKeys = array_fill_keys($arSKUPropIDs, false);
		}
	}

	$arNewItemsList = array();
	//start e1 fix
	foreach ($arSKUPropList as $skuPropKey => $arSkuProp) {
		foreach ($arSkuProp['VALUES'] as $key => $value) {
			if(!empty($value['XML_ID'])){
				$arSKUPropList[$skuPropKey]['SORT_VALUES'][$value['XML_ID']] = $value['SORT'];
			}
		}
	}
	//end e1 fix

	foreach ($arResult['ITEMS'] as $key => $arItem)
	{
		if(is_array($arItem['PROPERTIES']['CML2_ARTICLE']['VALUE']))
		{
			$arItem['PROPERTIES']['CML2_ARTICLE']['VALUE'] = reset($arItem['PROPERTIES']['CML2_ARTICLE']['VALUE']);
			$arResult['ITEMS'][$key]['PROPERTIES']['CML2_ARTICLE']['VALUE'] = $arItem['PROPERTIES']['CML2_ARTICLE']['VALUE'];
			if($arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE'])
			{
				$arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] = reset($arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE']);
				$arResult['ITEMS'][$key]['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] = $arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'];
			}
		}
		$bBigBlock = false;
		if ($arItem['PROPERTIES']['BIG_BLOCK']['VALUE'] == 'Y' && $arParams['SHOW_BIG_BLOCK'] != 'N') {
			$bBigBlock = true;
		}

		$arItem['CHECK_QUANTITY'] = false;
		if (!isset($arItem['CATALOG_MEASURE_RATIO']))
			$arItem['CATALOG_MEASURE_RATIO'] = 1;
		if (!isset($arItem['CATALOG_QUANTITY']))
			$arItem['CATALOG_QUANTITY'] = 0;
		$arItem['CATALOG_QUANTITY'] = (
			0 < $arItem['CATALOG_QUANTITY'] && is_float($arItem['CATALOG_MEASURE_RATIO'])
			? floatval($arItem['CATALOG_QUANTITY'])
			: intval($arItem['CATALOG_QUANTITY'])
		);
		$arItem['CATALOG'] = false;
		if (!isset($arItem['CATALOG_SUBSCRIPTION']) || 'Y' != $arItem['CATALOG_SUBSCRIPTION'])
			$arItem['CATALOG_SUBSCRIPTION'] = 'N';

		if ($arResult['MODULES']['catalog'])
		{
			$arItem['CATALOG'] = true;
			if (!isset($arItem['CATALOG_TYPE']))
				$arItem['CATALOG_TYPE'] = CCatalogProduct::TYPE_PRODUCT;
			if (
				(CCatalogProduct::TYPE_PRODUCT == $arItem['CATALOG_TYPE'] || CCatalogProduct::TYPE_SKU == $arItem['CATALOG_TYPE'])
				&& !empty($arItem['OFFERS'])
			)
			{
				$arItem['CATALOG_TYPE'] = CCatalogProduct::TYPE_SKU;
			}
			switch ($arItem['CATALOG_TYPE'])
			{
				case CCatalogProduct::TYPE_SET:
					$arItem['OFFERS'] = array();
					$arItem['CHECK_QUANTITY'] = ('Y' == $arItem['CATALOG_QUANTITY_TRACE'] && 'N' == $arItem['CATALOG_CAN_BUY_ZERO']);

					$arSets = CCatalogProductSet::getAllSetsByProduct($arItem["ID"], 1);
					if($arSets)
					{
						foreach($arSets as $arSet2)
						{
							foreach($arSet2["ITEMS"] as $arSet3)
							{
								$arItem['SET_ITEMS'][] = array(
									"ID" => $arSet3["ITEM_ID"],
									"QUANTITY" => $arSet3["QUANTITY"]
								);
							}
						}
					}

					break;
				case CCatalogProduct::TYPE_SKU:
					break;
				case CCatalogProduct::TYPE_PRODUCT:
				default:
					$arItem['CHECK_QUANTITY'] = ('Y' == $arItem['CATALOG_QUANTITY_TRACE'] && 'N' == $arItem['CATALOG_CAN_BUY_ZERO']);
					break;
			}
		}
		else
		{
			$arItem['CATALOG_TYPE'] = 0;
			$arItem['OFFERS'] = array();
		}


		if(($arItem['DETAIL_PICTURE'] && $arItem['PREVIEW_PICTURE']) || (!$arItem['DETAIL_PICTURE'] && $arItem['PREVIEW_PICTURE']))
			$arItem['DETAIL_PICTURE'] = $arItem['PREVIEW_PICTURE'];

		$arItem['GALLERY'] = CMax::getSliderForItemExt($arItem, $arParams['ADD_PICT_PROP'], 'Y' == $arParams['ADD_DETAIL_TO_SLIDER']);
		array_splice($arItem['GALLERY'], $arParams['MAX_GALLERY_ITEMS']);

		if ($arItem['CATALOG'] && isset($arItem['OFFERS']) && !empty($arItem['OFFERS']))
		{
			$arMatrixFields = $arSKUPropKeys;
			$arMatrix = array();

			$arNewOffers = array();
			$boolSKUDisplayProperties = false;
			$arItem['OFFERS_PROP'] = false;
			$bChangeName = true;
			$bNeedFindPicture = false;

			$arDouble = array();
			foreach ($arItem['OFFERS'] as $keyOffer => $arOffer)
			{

				$arOffer['ID'] = intval($arOffer['ID']);
				if (isset($arDouble[$arOffer['ID']]))
					continue;
				$arRow = array();
				foreach ($arSKUPropIDs as $propkey => $strOneCode)
				{
					$arCell = array(
						'VALUE' => 0,
						'SORT' => PHP_INT_MAX,
						'NA' => true
					);
					if (isset($arOffer['DISPLAY_PROPERTIES'][$strOneCode]))
					{
						$arMatrixFields[$strOneCode] = true;
						$arCell['NA'] = false;

						if ('directory' == $arSKUPropList[$strOneCode]['USER_TYPE'])
						{
							$intValue = $arSKUPropList[$strOneCode]['XML_MAP'][$arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE']];
							$arCell['VALUE'] = $intValue;
							//start e1 fix
							if(!empty($arSKUPropList[$strOneCode]['SORT_VALUES'][$arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE']])){
								$arSKUPropList[$strOneCode]['VALUES'][$arCell['VALUE']]['SORT'] = $arSKUPropList[$strOneCode]['SORT_VALUES'][$arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE']];
							}
							//end e1 fix
						}
						elseif ('L' == $arSKUPropList[$strOneCode]['PROPERTY_TYPE'])
						{
							$arCell['VALUE'] = intval($arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE_ENUM_ID']);
						}
						elseif ('E' == $arSKUPropList[$strOneCode]['PROPERTY_TYPE'])
						{
							$arCell['VALUE'] = intval($arOffer['DISPLAY_PROPERTIES'][$strOneCode]['VALUE']);
						}
						$arCell['SORT'] = $arSKUPropList[$strOneCode]['VALUES'][$arCell['VALUE']]['SORT'];
					}
					$arRow[$strOneCode] = $arCell;
				}

				//start e1 fix
				if(!isset($arRow['BASIC_CONFIGURATION'])){
					$arRow['BASIC_CONFIGURATION'] = array(
						'VALUE' => 0,
						'SORT' => PHP_INT_MAX,
						'NA' => true
					);

					if (isset($arOffer['DISPLAY_PROPERTIES']['BASIC_CONFIGURATION']))
					{
						$arMatrixFields['BASIC_CONFIGURATION'] = true;
						$arRow['BASIC_CONFIGURATION']['NA'] = false;
						//$arRow['SORT'] = $arSKUPropList[$strOneCode]['VALUES'][$arCell['VALUE']]['SORT'];
						$arRow['BASIC_CONFIGURATION']['VALUE'] = $arOffer['DISPLAY_PROPERTIES']['BASIC_CONFIGURATION']['VALUE'];
						$arRow['BASIC_CONFIGURATION']['SORT'] = $arOffer['DISPLAY_PROPERTIES']['BASIC_CONFIGURATION']['VALUE_SORT'];
					}

				}
				//end e1 fix
				$arMatrix[$keyOffer] = $arRow;

				$arOffer['OWNER_PICT'] = empty($offerPictures['PICT']);
				$arOffer['PREVIEW_PICTURE_FIELD'] = $arOffer['PREVIEW_PICTURE'];
				$arOffer['PREVIEW_PICTURE'] = false;
				$arOffer['PREVIEW_PICTURE_SECOND'] = false;
				$arOffer['SECOND_PICT'] = true;
				if (!$arOffer['OWNER_PICT'])
				{
					if (empty($offerPictures['SECOND_PICT']))
						$offerPictures['SECOND_PICT'] = $offerPictures['PICT'];
					$arOffer['PREVIEW_PICTURE'] = $offerPictures['PICT'];
					$arOffer['PREVIEW_PICTURE_SECOND'] = $offerPictures['SECOND_PICT'];
				}

				if ('' != $arParams['OFFER_ADD_PICT_PROP'] && isset($arOffer['DISPLAY_PROPERTIES'][$arParams['OFFER_ADD_PICT_PROP']]))
					unset($arOffer['DISPLAY_PROPERTIES'][$arParams['OFFER_ADD_PICT_PROP']]);

				if($arParams["USE_MAIN_ELEMENT_SECTION"] != "Y")
				{
					if($arOffer["DETAIL_PAGE_URL"])
					{
						$arTmpUrl = explode("?", $arOffer["DETAIL_PAGE_URL"]);
						$arOffer["DETAIL_PAGE_URL"] = str_replace($arTmpUrl[0], $arItem["DETAIL_PAGE_URL"], $arOffer["DETAIL_PAGE_URL"]);
					}
				}

				$arDouble[$arOffer['ID']] = true;
				$arNewOffers[$keyOffer] = $arOffer;
			}

			$arItem['OFFERS'] = $arNewOffers;

			$arUsedFields = array();
			$arSortFields = array();

			$arPropSKU = $arItem['OFFERS_PROPS_JS'] = array();

			foreach ($arSKUPropIDs as $propkey => $strOneCode)
			{
				$boolExist = $arMatrixFields[$strOneCode];
				foreach ($arMatrix as $keyOffer => $arRow)
				{
					if ($boolExist)
					{
						if (!isset($arItem['OFFERS'][$keyOffer]['TREE']))
							$arItem['OFFERS'][$keyOffer]['TREE'] = array();

						//start e1 fix
						if(isset($arRow['BASIC_CONFIGURATION'])){
							$arItem['OFFERS'][$keyOffer]['TREE']['PROP_' . $arOffer['DISPLAY_PROPERTIES']['BASIC_CONFIGURATION']['ID']] = $arMatrix[$keyOffer]['BASIC_CONFIGURATION']['VALUE'];
							$arItem['OFFERS'][$keyOffer]['SKU_SORT_BASIC_CONFIGURATION'] = $arMatrix[$keyOffer]['BASIC_CONFIGURATION']['SORT'];
							$arUsedFields['BASIC_CONFIGURATION'] = true;
							$arSortFields['SKU_SORT_BASIC_CONFIGURATION'] = SORT_NUMERIC;
							$arPropSKU['BASIC_CONFIGURATION'][$arMatrix[$keyOffer]['BASIC_CONFIGURATION']["VALUE"]] = $arSKUPropList[$strOneCode]["VALUES"][$arMatrix[$keyOffer][$strOneCode]["VALUE"]];
						}
						//end e1 fix

						$arItem['OFFERS'][$keyOffer]['TREE']['PROP_'.$arSKUPropList[$strOneCode]['ID']] = $arMatrix[$keyOffer][$strOneCode]['VALUE'];
						$arItem['OFFERS'][$keyOffer]['SKU_SORT_'.$strOneCode] = $arMatrix[$keyOffer][$strOneCode]['SORT'];
						$arUsedFields[$strOneCode] = true;
						$arSortFields['SKU_SORT_'.$strOneCode] = SORT_NUMERIC;
						$arPropSKU[$strOneCode][$arMatrix[$keyOffer][$strOneCode]["VALUE"]] = $arSKUPropList[$strOneCode]["VALUES"][$arMatrix[$keyOffer][$strOneCode]["VALUE"]];
					}
					else
					{
						unset($arMatrix[$keyOffer][$strOneCode]);
					}
				}
			}
			$arItem['OFFERS_PROP'] = $arUsedFields;
			// $arItem['OFFERS_PROP_CODES'] = (!empty($arUsedFields) ? base64_encode(serialize(array_keys($arUsedFields))) : '');
			$arItem['OFFERS_PROP_CODES'] = (!empty($arParams["OFFERS_CART_PROPERTIES"]) ? base64_encode(serialize(array_keys($arParams["OFFERS_CART_PROPERTIES"]))) : '');

			$arMatrix = array();
			$intSelected = -1;
			$arItem['MIN_PRICE'] = false;
			$arItem['MIN_BASIS_PRICE'] = false;

			if ($bBigBlock) {
				$arItem['OFFERS_PROP'] = false;
			}
			if($arItem['OFFERS_PROP'])
			{
				$bChangeName = true;
				foreach ($arItem['OFFERS'] as $keyOffer => $arOffer)
				{
					//if (empty($arItem['MIN_PRICE']))
					//{
						if ($arItem['OFFER_ID_SELECTED'] > 0)
							$foundOffer = ($arItem['OFFER_ID_SELECTED'] == $arOffer['ID']);
						else
							$foundOffer = $arOffer['CAN_BUY'];
						if ($foundOffer && $intSelected == -1)
						{
							$intSelected = $keyOffer;
							$arItem['MIN_PRICE'] = (isset($arOffer['RATIO_PRICE']) ? $arOffer['RATIO_PRICE'] : $arOffer['MIN_PRICE']);
							$arItem['MIN_BASIS_PRICE'] = $arOffer['MIN_PRICE'];
						}
						unset($foundOffer);

					$totalCount = CMax::GetTotalCount($arOffer, $arParams);
					$arOffer['IS_OFFER'] = 'Y';
					$arOffer['IBLOCK_ID'] = $arResult['IBLOCK_ID'];

					$arPriceTypeID = array();
					if($arOffer['PRICES'])
					{
						foreach($arOffer['PRICES'] as $priceKey => $arOfferPrice)
						{
							if($arOfferPrice['CAN_BUY'] == 'Y')
								$arPriceTypeID[] = $arOfferPrice['PRICE_ID'];
							if($arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']])
								$arOffer['PRICES'][$priceKey]['GROUP_NAME'] = $arOffer['CATALOG_GROUP_NAME_'.$arOfferPrice['PRICE_ID']];
						}
					}

					$arAddToBasketData = CMax::GetAddToBasketArray($arOffer, $totalCount, $arParams["DEFAULT_COUNT"], $arParams["BASKET_URL"], false, $arItemIDs["ALL_ITEM_IDS"], 'small read_more1', $arParams);
					$arAddToBasketData["HTML"] = str_replace('data-item', 'data-props="'.$arOfferProps.'" data-item', $arAddToBasketData["HTML"]);

					$arOneRow = array(
						'ID' => $arOffer['ID'],
						'NAME' => $arOffer['~NAME'],
						'TREE' => $arOffer['TREE'],
						'DISPLAY_PROPERTIES' => $arSKUProps,
						'ARTICLE' => $arSKUArticle,
						// 'PRICE' => (isset($arOffer['RATIO_PRICE']) ? $arOffer['RATIO_PRICE'] : $arOffer['MIN_PRICE']),
						//'PRICE' => $arOffer['MIN_PRICE'],
						'SHOW_DISCOUNT_TIME_EACH_SKU' => $arParams['SHOW_DISCOUNT_TIME_EACH_SKU'],
						'PRICES' => $arOffer['PRICES'],
						'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
						'SHOW_DISCOUNT_PERCENT_NUMBER' => $arParams['SHOW_DISCOUNT_PERCENT_NUMBER'],
						'SHOW_ARTICLE_SKU' => $arParams['SHOW_ARTICLE_SKU'],
						'ARTICLE_SKU' => ($arParams['SHOW_ARTICLE_SKU'] == 'Y' ? (isset($arItem['PROPERTIES']['CML2_ARTICLE']['VALUE']) && $arItem['PROPERTIES']['CML2_ARTICLE']['VALUE'] ? $arItem['PROPERTIES']['CML2_ARTICLE']['NAME'].': '.$arItem['PROPERTIES']['CML2_ARTICLE']['VALUE'] : '') : ''),
						'PRICE_MATRIX' => $sPriceMatrix,
						'PRICE_MATRIX_RAW' => $arOffer["PRICE_MATRIX"],
						//'BASIS_PRICE' => $arOffer['MIN_PRICE'],
						'OWNER_PICT' => $arOffer['OWNER_PICT'],
						'PREVIEW_PICTURE' => $arOffer['PREVIEW_PICTURE'],
						'PREVIEW_PICTURE_SECOND' => $arOffer['PREVIEW_PICTURE_SECOND'],
						'CHECK_QUANTITY' => $arOffer['CHECK_QUANTITY'],
						'MAX_QUANTITY' => $totalCount,
						'STEP_QUANTITY' => $arOffer['CATALOG_MEASURE_RATIO'],
						'QUANTITY_FLOAT' => is_double($arOffer['CATALOG_MEASURE_RATIO']),
						'MEASURE' => $arOffer['~CATALOG_MEASURE_NAME'],
						'CAN_BUY' => ($arAddToBasketData['CAN_BUY'] ? 'Y' : $arOffer['CAN_BUY']),
						'CATALOG_SUBSCRIBE' => $arOffer['CATALOG_SUBSCRIBE'],
						'AVAILIABLE' => CMax::GetQuantityArray($totalCount),
						'URL' => $arOffer['DETAIL_PAGE_URL'],
						'SHOW_MEASURE' => ($arParams["SHOW_MEASURE"]=="Y" ? "Y" : "N"),
						'SHOW_ONE_CLICK_BUY' => "N",
						'ONE_CLICK_BUY' => GetMessage("ONE_CLICK_BUY"),
						'OFFER_PROPS' => $arOfferProps,
						'NO_PHOTO' => $arEmptyPreview,
						'CONFIG' => $arAddToBasketData,
						'HTML' => $arAddToBasketData["HTML"],
						'PRODUCT_QUANTITY_VARIABLE' => $arParams["PRODUCT_QUANTITY_VARIABLE"],
						'SUBSCRIPTION' => true,
					);
					if($arOneRow["PRICE"]["DISCOUNT_DIFF"]){
						$percent=round(($arOneRow["PRICE"]["DISCOUNT_DIFF"]/$arOneRow["PRICE"]["VALUE"])*100, 2);
						$arOneRow["PRICE"]["DISCOUNT_DIFF_PERCENT_RAW"]="-".$percent."%";
					}
					$arMatrix[$keyOffer] = $arOneRow;

					if(($arOffer['DETAIL_PICTURE'] && $arOffer['PREVIEW_PICTURE']) || (!$arOffer['DETAIL_PICTURE'] && $arOffer['PREVIEW_PICTURE']))
						$arOffer['DETAIL_PICTURE'] = $arOffer['PREVIEW_PICTURE'];

				}
				if (-1 == $intSelected)
					$intSelected = 0;
				if (!$arMatrix[$intSelected]['OWNER_PICT'])
				{
					$arItem['PREVIEW_PICTURE'] = $arMatrix[$intSelected]['PREVIEW_PICTURE'];
					$arItem['PREVIEW_PICTURE_SECOND'] = $arMatrix[$intSelected]['PREVIEW_PICTURE_SECOND'];
				}
				$arItem['JS_OFFERS'] = $arMatrix;
				$arItem['OFFERS_SELECTED'] = $intSelected;
				$arItem['OFFERS_PROPS_DISPLAY'] = $boolSKUDisplayProperties;
			}
		}

		
		$arFirstSkuPicture = array();
		$bNeedFindPicture = ($arParams['SHOW_GALLERY'] == 'Y') && (CMax::GetFrontParametrValue("SHOW_FIRST_SKU_PICTURE") == "Y") && empty($arItem['GALLERY']);
		if( $bNeedFindPicture){
			foreach ($arItem['OFFERS'] as $keyOffer => $arOffer)
			{
				if(($arOffer['DETAIL_PICTURE'] && $arOffer['PREVIEW_PICTURE']) || (!$arOffer['DETAIL_PICTURE'] && $arOffer['PREVIEW_PICTURE']))
					$arOffer['DETAIL_PICTURE'] = $arOffer['PREVIEW_PICTURE'];

				$arFirstSkuPicture = CMax::getSliderForItemExt($arOffer, '', true);
				if(!empty( $arFirstSkuPicture )){
					$arItem['GALLERY'] = $arFirstSkuPicture;
					break;
				}
			}
		}

		if (!empty($arItem['DISPLAY_PROPERTIES']))
		{
			foreach ($arItem['DISPLAY_PROPERTIES'] as $propKey => $arDispProp)
			{
				if ('F' == $arDispProp['PROPERTY_TYPE'])
					unset($arItem['DISPLAY_PROPERTIES'][$propKey]);

			}
		}

		//format prices when USE_PRICE_COUNT
		$arItem = array_merge($arItem, CMax::formatPriceMatrix($arItem));

		$arItem['ARTICLE']=false;
		if (!empty($arItem['DISPLAY_PROPERTIES']))
		{
			foreach ($arItem['DISPLAY_PROPERTIES'] as $propKey => $arDispProp)
			{
				if($propKey=="CML2_ARTICLE"){
					$arItem['ARTICLE']=$arDispProp;
					unset($arItem['DISPLAY_PROPERTIES'][$propKey]);
				}
				if ('F' == $arDispProp['PROPERTY_TYPE'] || $arDispProp["CODE"] == $arParams["STIKERS_PROP"])
					unset($arItem['DISPLAY_PROPERTIES'][$propKey]);
			}
			$arItem['DISPLAY_PROPERTIES'] = CMax::PrepareItemProps($arItem['DISPLAY_PROPERTIES']);
		}

		$arItem['LAST_ELEMENT'] = 'N';

		if($arParams['IBINHERIT_TEMPLATES']){
			\Aspro\Max\Property\IBInherited::modifyItemTemplates($arParams, $arItem);
		}

		$arNewItemsList[$key] = $arItem;

		//start e1 fix
		$arResult['ITEMS_IDS'][] = $arItem['ID'];
		$arResult['ITEMS_URLS'][$arItem['ID']] = $arItem['DETAIL_PAGE_URL'];
		$arResult["SELECTED_OFFERS"][$arItem['ID']] = $arItem["OFFERS"][$arItem["OFFERS_SELECTED"]];
		//end e1 fix
	}

	$arNewItemsList[$key]['LAST_ELEMENT'] = 'Y';
	$arResult['ITEMS'] = $arNewItemsList;
	$arResult['SKU_PROPS'] = $arSKUPropList;
	$arResult['DEFAULT_PICTURE'] = $arEmptyPreview;

	unset($arNewItemsList);

	$this->__component->SetResultCacheKeys(array("ITEMS_IDS", "ITEMS_URLS", "SELECTED_OFFERS"));
}
?>