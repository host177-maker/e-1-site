<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? $this->setFrameMode(true); ?>
<?
$showElements = true;
global $iScuBlockCounter;
$arSkuTemplate = CMaxCustom::GetSKUPropsArray($arResult['SKU_PROPS'], $arResult["SKU_IBLOCK_ID"], "list", 'N', "N", array(), "N");
$arElementId = [];
?>

<? //параметры вызова компонента catalog.element для Аксессуаров
$arElementParams = array(
	//"USE_REGION" => ($arRegion ? "Y" : "N"),
	"USE_PREDICTION" => 'Y', $arParams['USE_DETAIL_PREDICTION'],
	"SECTION_TIZERS" => 0,
	"HELP_TEXT" => $arSection["UF_HELP_TEXT"],
	"ALT_TITLE_GET" => 'NORMAL',
	"GRUPPER_PROPS" => 'NOT',
	"USE_CUSTOM_RESIZE" => 'N',
	"SHOW_DISCOUNT_TIME_EACH_SKU" => 'N',
	"SHOW_UNABLE_SKU_PROPS" => 'Y',
	"SHOW_ARTICLE_SKU" => 'Y',
	"SHOW_MEASURE_WITH_RATIO" => 'N',
	"STORES_FILTER" =>  "TITLE",
	"STORES_FILTER_ORDER" => ($arParams["STORES_FILTER_ORDER"] ? $arParams["STORES_FILTER_ORDER"] : "SORT_ASC"),
	"BUNDLE_ITEMS_COUNT" => 3,
	"WIDE_BLOCK" => 'N',
	"PICTURE_RATIO" => (isset($sViewPictureDetail) ? $sViewPictureDetail : ''),
	"DETAIL_DOCS_PROP" => '-',
	"SHOW_DISCOUNT_TIME" => 'Y',
	"TYPE_SKU" => 'TYPE_1',
	"SET_SKU_TITLE" => "",
	"SEF_URL_TEMPLATES" => array(
		'sections' => '',
		'section' => '#SECTION_CODE_PATH#/',
		'element' => '#SECTION_CODE_PATH#/#ELEMENT_ID#/',
		'compare' => 'compare.php?action=#ACTION_CODE#',
		'smart_filter' => '#SECTION_CODE_PATH#/filter/#SMART_FILTER_PATH#/'
	),
	"IBLOCK_REVIEWS_TYPE" => $arParams["IBLOCK_REVIEWS_TYPE"],
	"IBLOCK_REVIEWS_ID" => $arParams["IBLOCK_REVIEWS_ID"],
	"SHOW_ONE_CLICK_BUY" => 'Y',
	"SEF_MODE_BRAND_SECTIONS" => $arParams["SEF_MODE_BRAND_SECTIONS"],
	"SEF_MODE_BRAND_ELEMENT" => $arParams["SEF_MODE_BRAND_ELEMENT"],
	"DISPLAY_COMPARE" => 'N', //CMax::GetFrontParametrValue('CATALOG_COMPARE'),
	"IBLOCK_TYPE" => '1c_catalog',
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"PROPERTY_CODE" => array(
		0 => 'IN_INTERIOR',
		1 => 'VIDEO_YOUTUBE',
		2 => 'INSTRUCTIONS',
		3 => 'MORE_PHOTO',
		4 => 'SERIYA_SHKAFA',
		7 => 'ACCESSORIES_PARAMS'
	),
	"META_KEYWORDS" => '-',
	"META_DESCRIPTION" => '-',
	"BROWSER_TITLE" => '-',
	"BASKET_URL" => '/basket/',
	'OFFER_SHOW_PREVIEW_PICTURE_PROPS' => array(),
	"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
	"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
	"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
	"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
	"SET_TITLE" => $arParams["SET_TITLE"],
	"SHOW_CHEAPER_FORM" => 'Y',
	"SET_CANONICAL_URL" => $arParams["DETAIL_SET_CANONICAL_URL"],
	"SET_LAST_MODIFIED" => "Y",
	"SET_STATUS_404" => $arParams["SET_STATUS_404"],
	"MESSAGE_404" => $arParams["MESSAGE_404"],
	"SHOW_404" => $arParams["SHOW_404"],
	"FILE_404" => $arParams["FILE_404"],
	"SORT_REGION_PRICE" => 'BASE',
	"PRICE_CODE" => $arParams['PRICE_CODE'],
	"USE_PRICE_COUNT" => 'Y', //$arParams["USE_PRICE_COUNT"],
	"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
	"USE_RATIO_IN_RANGES" => $arParams["USE_RATIO_IN_RANGES"],
	"PRICE_VAT_INCLUDE" => 'Y',
	"PRICE_VAT_SHOW_VALUE" => 'N', //$arParams["PRICE_VAT_SHOW_VALUE"],
	"LINK_IBLOCK_TYPE" => $arParams["LINK_IBLOCK_TYPE"],
	"LINK_IBLOCK_ID" => $arParams["LINK_IBLOCK_ID"],
	"LINK_PROPERTY_SID" => $arParams["LINK_PROPERTY_SID"],
	"LINK_ELEMENTS_URL" => 'link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#',
	"USE_ALSO_BUY" => 'Y', //$arParams["USE_ALSO_BUY"],
	'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'],
	'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'],
	"OFFERS_CART_PROPERTIES" => array(
		0 => 'PRICE_UM',
		1 => 'PRICE_VP',
		2 => 'PRICE_SBORKA_SHKAFA'
	),
	"OFFERS_FIELD_CODE" => array(
		0 => 'NAME',
		1 => 'PREVIEW_PICTURE',
		2 => 'DETAIL_PICTURE',
		3 => 'SHOW_COUNTER',
		4 => 'DETAIL_PAGE_URL',
		5 => '',
	),
	"OFFERS_PROPERTY_CODE" => array(
		0 => 'BASIC_CONFIGURATION',
		1 => 'HEIGHT',
		2 => 'WIDTH',
		3 => 'DEPTH',
		4 => 'BASE_COST_DIMENSIONS',
		5 => 'PRICE_UM',
		6 => 'PRICE_VP',
		7 => 'COLOR_REF',
		8 => 'BASE_COST_COLOR',
		9 => 'HOUSING_LAYOUT',
		10 => 'BASE_COST_LAYOUT',
		11 => 'PRICE_SBORKA_SHKAFA',
		12 => 'TSVET_PROFILYA',
		13 => '',
	),
	"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
	"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
	"OFFERS_SORT_FIELD2" => 'PRICE_238',
	"OFFERS_SORT_ORDER2" => 'asc',
	"LINKED_ELEMENT_TAB_SORT_FIELD" => $arParams["LINKED_ELEMENT_TAB_SORT_FIELD"],
	"LINKED_ELEMENT_TAB_SORT_ORDER" => $arParams["LINKED_ELEMENT_TAB_SORT_ORDER"],
	"LINKED_ELEMENT_TAB_SORT_FIELD2" => $arParams["LINKED_ELEMENT_TAB_SORT_FIELD2"],
	"LINKED_ELEMENT_TAB_SORT_ORDER2" => $arParams["LINKED_ELEMENT_TAB_SORT_ORDER2"],
	"SKU_DETAIL_ID" => 'oid',
	"SKU_DISPLAY_LOCATION" => $arParams["SKU_DISPLAY_LOCATION"],
	"SECTION_URL" => '/catalog/#SECTION_CODE_PATH#/',
	"DETAIL_URL" => '/catalog/#SECTION_CODE_PATH#/#ELEMENT_ID#/',
	"ADD_SECTIONS_CHAIN" => 'N',
	"ADD_ELEMENT_CHAIN" => 'N',
	"USE_STORE" => 'N',
	"USE_STORE_PHONE" => 'N',
	"USE_STORE_SCHEDULE" => 'N',
	"USE_MIN_AMOUNT" => 'N',
	"MIN_AMOUNT" => 10,
	"STORE_PATH" => '/contacts/stores/#store_id#/',
	"MAIN_TITLE" => 'Наличие на складах',
	"USE_PRODUCT_QUANTITY" => 'N',
	"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
	"BLOG_URL" => $arParams["BLOG_URL"],
	"SHOW_SEND_GIFT" => $arParams['SHOW_SEND_GIFT'],
	"RECOMEND_COUNT" => $arParams["RECOMEND_COUNT"],
	"SEND_GIFT_FORM_NAME" => $arParams['SEND_GIFT_FORM_NAME'],

	"IBLOCK_LINK_SALE_ID" => $arParams["IBLOCK_STOCK_ID"],
	"IBLOCK_LINK_NEWS_ID" => $arParams["IBLOCK_LINK_NEWS_ID"],
	"IBLOCK_SERVICES_ID" => $arParams["IBLOCK_SERVICES_ID"],
	"IBLOCK_LINK_REVIEWS_ID" => $arParams["IBLOCK_LINK_REVIEWS_ID"],
	"IBLOCK_LINK_BLOG_ID" => $arParams["BLOG_IBLOCK_ID"],
	"IBLOCK_TIZERS_ID" => $arParams["IBLOCK_TIZERS_ID"],
	"IBLOCK_LINK_STAFF_ID" => $arParams["STAFF_IBLOCK_ID"],
	"IBLOCK_LINK_VACANCY_ID" => $arParams["VACANCY_IBLOCK_ID"],

	"SEF_MODE_STOCK_SECTIONS" => $arParams["SEF_MODE_STOCK_SECTIONS"],
	"SHOW_QUANTITY" => 'Y',
	"SHOW_QUANTITY_COUNT" => 'Y',
	"CONVERT_CURRENCY" => $arParams["CONVERT_CURRENCY"],
	"CURRENCY_ID" => $arParams["CURRENCY_ID"],
	'HIDE_NOT_AVAILABLE' => 'Y', //$arParams["HIDE_NOT_AVAILABLE"],
	'HIDE_NOT_AVAILABLE_OFFERS' => 'Y', //$arParams["HIDE_NOT_AVAILABLE_OFFERS"],
	'SHOW_DEACTIVATED' => 'N',
	"USE_ELEMENT_COUNTER" => 'Y',
	"STAFF_VIEW_TYPE" => ($arParams["STAFF_VIEW_TYPE"] ? $arParams["STAFF_VIEW_TYPE"] : "staff_block"),
	'STRICT_SECTION_CHECK' => '',
	'RELATIVE_QUANTITY_FACTOR' => (isset($arParams['RELATIVE_QUANTITY_FACTOR']) ? $arParams['RELATIVE_QUANTITY_FACTOR'] : ''),

	"USE_RATING" => $arParams["USE_RATING"],
	"USE_REVIEW" => $arParams["USE_REVIEW"],
	"REVIEWS_VIEW" => $arTheme["REVIEWS_VIEW"]["VALUE"],
	"FORUM_ID" => $arParams["FORUM_ID"],
	"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
	"MAX_AMOUNT" => $arParams["MAX_AMOUNT"],
	"USE_ONLY_MAX_AMOUNT" => $arParams["USE_ONLY_MAX_AMOUNT"],
	"DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
	"DEFAULT_COUNT" => $arParams["DEFAULT_COUNT"],
	"SHOW_BRAND_PICTURE" => $arParams["SHOW_BRAND_PICTURE"],
	"PROPERTIES_DISPLAY_LOCATION" => 'TAB', //$arParams["PROPERTIES_DISPLAY_LOCATION"],
	"PROPERTIES_DISPLAY_TYPE" => 'TABLE', //$arParams["PROPERTIES_DISPLAY_TYPE"],
	"SHOW_ADDITIONAL_TAB" => $arParams["SHOW_ADDITIONAL_TAB"],
	"SHOW_ASK_BLOCK" => $arParams["SHOW_ASK_BLOCK"],
	"ASK_FORM_ID" => $arParams["ASK_FORM_ID"],
	"SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
	"SHOW_HINTS" => $arParams["SHOW_HINTS"],
	"OFFER_HIDE_NAME_PROPS" => '', //$arParams["OFFER_HIDE_NAME_PROPS"],
	"SHOW_KIT_PARTS" => 'Y', //$arParams["SHOW_KIT_PARTS"],
	"SHOW_KIT_PARTS_PRICES" => 'Y', //$arParams["SHOW_KIT_PARTS_PRICES"],
	"SHOW_DISCOUNT_PERCENT_NUMBER" => 'Y', //$arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
	"SHOW_DISCOUNT_PERCENT" => 'Y', //$arParams["SHOW_DISCOUNT_PERCENT"],
	"SHOW_OLD_PRICE" => 'Y', //$arParams["SHOW_OLD_PRICE"],
	'OFFER_TREE_PROPS' => array(
		0 => 'HEIGHT',
		1 => 'WIDTH',
		2 => 'DEPTH',
		3 => 'COLOR_REF',
		4 => 'HOUSING_LAYOUT',
		5 => 'VARIANT_ISPOLNENIYA',
		6 => 'TSVET_PROFILYA',
	), //$arParams['OFFER_TREE_PROPS'],
	'ADD_DETAIL_TO_SLIDER' => (isset($arParams['DETAIL_ADD_DETAIL_TO_SLIDER']) ? $arParams['DETAIL_ADD_DETAIL_TO_SLIDER'] : ''),
	"SHOW_EMPTY_STORE" => 'N', //$arParams['SHOW_EMPTY_STORE'],
	"SHOW_GENERAL_STORE_INFORMATION" => 'N', //$arParams['SHOW_GENERAL_STORE_INFORMATION'],
	"USER_FIELDS" => array(
		0 => '',
		1 => 'UF_CATALOG_ICON',
		2 => 'UF_IS_TAG',
	),
	"FIELDS" => array(
		0 => '',
		1 => ''
	), //$arParams['FIELDS'],
	"STORES" => array(
		//0 => 575,
	),
	"BIG_DATA_RCM_TYPE" => $arParams['BIG_DATA_RCM_TYPE'],
	"USE_BIG_DATA" => $arParams['USE_BIG_DATA'],
	"USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
	"PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
	"ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
	"PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
	"SALE_STIKER" => $arParams["SALE_STIKER"],
	"STIKERS_PROP" => $arParams["STIKERS_PROP"],
	"SHOW_RATING" => $arParams["SHOW_RATING"],

	"MAX_GALLERY_ITEMS" => $arParams["MAX_GALLERY_ITEMS"],
	"SHOW_GALLERY" => $arParams["SHOW_GALLERY"],
	"SHOW_PROPS" => (CMax::GetFrontParametrValue("SHOW_PROPS_BLOCK") == "Y" ? "Y" : "N"),
	'SHOW_POPUP_PRICE' => (CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' ? "Y" : "N"),

	"OFFERS_LIMIT" => 0,

	'SHOW_BASIS_PRICE' => (isset($arParams['DETAIL_SHOW_BASIS_PRICE']) ? $arParams['DETAIL_SHOW_BASIS_PRICE'] : 'Y'),
	"DETAIL_PICTURE_MODE" => (isset($arTheme["DETAIL_PICTURE_MODE"]["VALUE"]) ? $arTheme["DETAIL_PICTURE_MODE"]["VALUE"] : 'POPUP'),

	'DISABLE_INIT_JS_IN_COMPONENT' => (isset($arParams['DISABLE_INIT_JS_IN_COMPONENT']) ? $arParams['DISABLE_INIT_JS_IN_COMPONENT'] : ''),
	'COMPATIBLE_MODE' => (isset($arParams['COMPATIBLE_MODE']) ? $arParams['COMPATIBLE_MODE'] : ''),
	'SET_VIEWED_IN_COMPONENT' => (isset($arParams['DETAIL_SET_VIEWED_IN_COMPONENT']) ? $arParams['DETAIL_SET_VIEWED_IN_COMPONENT'] : ''),

	'SHOW_SLIDER' => (isset($arParams['DETAIL_SHOW_SLIDER']) ? $arParams['DETAIL_SHOW_SLIDER'] : ''),
	'SLIDER_INTERVAL' => (isset($arParams['DETAIL_SLIDER_INTERVAL']) ? $arParams['DETAIL_SLIDER_INTERVAL'] : ''),
	'SLIDER_PROGRESS' => (isset($arParams['DETAIL_SLIDER_PROGRESS']) ? $arParams['DETAIL_SLIDER_PROGRESS'] : ''),
	'USE_ENHANCED_ECOMMERCE' => (isset($arParams['USE_ENHANCED_ECOMMERCE']) ? $arParams['USE_ENHANCED_ECOMMERCE'] : ''),
	'DATA_LAYER_NAME' => (isset($arParams['DATA_LAYER_NAME']) ? $arParams['DATA_LAYER_NAME'] : ''),
	'GALLERY_THUMB_POSITION' => CMax::GetFrontParametrValue('CATALOG_PAGE_DETAIL_THUMBS'),


	"USE_GIFTS_DETAIL" => $arParams['USE_GIFTS_DETAIL'] ?: 'Y',
	"USE_GIFTS_MAIN_PR_SECTION_LIST" => $arParams['USE_GIFTS_MAIN_PR_SECTION_LIST'] ?: 'Y',
	"GIFTS_SHOW_DISCOUNT_PERCENT" => $arParams['GIFTS_SHOW_DISCOUNT_PERCENT'],
	"GIFTS_SHOW_OLD_PRICE" => $arParams['GIFTS_SHOW_OLD_PRICE'],
	"GIFTS_DETAIL_PAGE_ELEMENT_COUNT" => $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'],
	"GIFTS_DETAIL_HIDE_BLOCK_TITLE" => $arParams['GIFTS_DETAIL_HIDE_BLOCK_TITLE'],
	"GIFTS_DETAIL_TEXT_LABEL_GIFT" => $arParams['GIFTS_DETAIL_TEXT_LABEL_GIFT'],
	"GIFTS_DETAIL_BLOCK_TITLE" => $arParams["GIFTS_DETAIL_BLOCK_TITLE"],
	"GIFTS_SHOW_NAME" => $arParams['GIFTS_SHOW_NAME'],
	"GIFTS_SHOW_IMAGE" => $arParams['GIFTS_SHOW_IMAGE'],
	"GIFTS_MESS_BTN_BUY" => $arParams['GIFTS_MESS_BTN_BUY'],
	"VISIBLE_PROP_COUNT" => $arParams['VISIBLE_PROP_COUNT'],

	"GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT" => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'],
	"GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE" => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE'],

	"TAB_OFFERS_NAME" => ($arParams["TAB_OFFERS_NAME"] ? $arParams["TAB_OFFERS_NAME"] : GetMessage("OFFER_PRICES")),
	"TAB_VIDEO_NAME" => ($arParams["TAB_VIDEO_NAME"] ? $arParams["TAB_VIDEO_NAME"] : GetMessage("VIDEO_TAB")),
	"TAB_BUY_SERVICES_NAME" => ($arParams["TAB_BUY_SERVICES_NAME"] ? $arParams["TAB_BUY_SERVICES_NAME"] : GetMessage("BUY_SERVICES_TAB")),
	"TAB_REVIEW_NAME" => ($arParams["TAB_REVIEW_NAME"] ? $arParams["TAB_REVIEW_NAME"] : GetMessage("REVIEW_TAB")),
	"TAB_FAQ_NAME" => $arParams["TAB_FAQ_NAME"],
	"TAB_STOCK_NAME" => ($arParams["TAB_STOCK_NAME"] ? $arParams["TAB_STOCK_NAME"] : GetMessage("STORES_TAB")),
	"TAB_NEWS_NAME" => ($arParams["TAB_NEWS_NAME"] ? $arParams["TAB_NEWS_NAME"] : GetMessage("TAB_NEWS_NAME")),
	"TAB_DOPS_NAME" => ($arParams["TAB_DOPS_NAME"] ? $arParams["TAB_DOPS_NAME"] : GetMessage("ADDITIONAL_TAB")),
	"TAB_STAFF_NAME" => ($arParams["TAB_STAFF_NAME"] ? $arParams["TAB_STAFF_NAME"] : GetMessage("TAB_STAFF_NAME")),
	"TAB_VACANCY_NAME" => ($arParams["TAB_VACANCY_NAME"] ? $arParams["TAB_VACANCY_NAME"] : GetMessage("TAB_VACANCY_NAME")),
	"TAB_BLOG_NAME" => ($arParams["BLOCK_BLOG_NAME"] ? $arParams["BLOCK_BLOG_NAME"] : GetMessage("TAB_BLOG_NAME")),
	"BLOCK_SERVICES_NAME" => ($arParams["BLOCK_SERVICES_NAME"] ? $arParams["BLOCK_SERVICES_NAME"] : GetMessage("SERVICES_TITLE")),
	"BLOCK_DOCS_NAME" => ($arParams["BLOCK_DOCS_NAME"] ? $arParams["BLOCK_DOCS_NAME"] : GetMessage("DOCUMENTS_TITLE")),
	"CHEAPER_FORM_NAME" => $arParams["CHEAPER_FORM_NAME"],
	"USE_ADDITIONAL_GALLERY" => $arParams["USE_ADDITIONAL_GALLERY"],
	"ADDITIONAL_GALLERY_TYPE" => $arParams["ADDITIONAL_GALLERY_TYPE"],
	"ADDITIONAL_GALLERY_PROPERTY_CODE" => $arParams["ADDITIONAL_GALLERY_PROPERTY_CODE"],
	"ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE" => $arParams["ADDITIONAL_GALLERY_OFFERS_PROPERTY_CODE"],
	"BLOCK_ADDITIONAL_GALLERY_NAME" => ($arParams["BLOCK_ADDITIONAL_GALLERY_NAME"] ? $arParams["BLOCK_ADDITIONAL_GALLERY_NAME"] : GetMessage("BLOCK_ADDITIONAL_GALLERY_NAME")),

	"T_KOMPLECT" => $arParams["TAB_KOMPLECT_NAME"],
	"T_NABOR" => $arParams["TAB_NABOR_NAME"],
	"T_DESC" => $arParams["TAB_DESCR_NAME"],
	"T_CHARACTERISTICS" => $arParams["TAB_CHAR_NAME"],

	"DETAIL_LINKED_GOODS_SLIDER" => $arParams["DETAIL_LINKED_GOODS_SLIDER"],
	"DETAIL_LINKED_GOODS_TABS" => $arParams["DETAIL_LINKED_GOODS_TABS"],

	"DETAIL_ASSOCIATED_TITLE" => $arParams["DETAIL_ASSOCIATED_TITLE"],
	"DETAIL_EXPANDABLES_TITLE" => $arParams["DETAIL_EXPANDABLES_TITLE"],

	"LINKED_FILTER_BY_PROP" => $arAllValues,
	"LINKED_FILTER_BY_FILTER" => $arTab,
	"BIG_DATA_TEMPLATE" => $_SERVER["DOCUMENT_ROOT"] . $this->__folder . '/page_blocks/' . $sViewBigDataExtTemplate . '.php',
	"TITLE_SLIDER" => $arParams['TITLE_SLIDER'],
	"LINKED_BLOG" => (isset($linkedArticles) ? $linkedArticles : ''),

	"SHOW_PAYMENT" => (isset($arParams["SHOW_PAYMENT"]) ? $arParams["SHOW_PAYMENT"] : "Y"),
	"SHOW_DELIVERY" => (isset($arParams["SHOW_DELIVERY"]) ? $arParams["SHOW_DELIVERY"] : "Y"),
	"SHOW_HOW_BUY" => (isset($arParams["SHOW_HOW_BUY"]) ? $arParams["SHOW_HOW_BUY"] : "Y"),
	"TITLE_HOW_BUY" => ($arParams["TITLE_HOW_BUY"] ? $arParams["TITLE_HOW_BUY"] : GetMessage("TITLE_HOW_BUY")),
	"TITLE_DELIVERY" => ($arParams["TITLE_DELIVERY"] ? $arParams["TITLE_DELIVERY"] : GetMessage("TITLE_DELIVERY")),
	"TITLE_PAYMENT" => ($arParams["TITLE_PAYMENT"] ? $arParams["TITLE_PAYMENT"] : GetMessage("TITLE_PAYMENT")),

	"CALCULATE_DELIVERY" => $arTheme["CALCULATE_DELIVERY"]["VALUE"],
	"EXPRESSION_FOR_CALCULATE_DELIVERY" => $arTheme["EXPRESSION_FOR_CALCULATE_DELIVERY"]["VALUE"],
	"DISPLAY_ELEMENT_SLIDER" => $arParams['DISPLAY_ELEMENT_SLIDER'],

	"DETAIL_USE_COMMENTS" => (isset($arParams['DETAIL_USE_COMMENTS']) ? $arParams['DETAIL_USE_COMMENTS'] : 'N'),
	"COMMENTS_COUNT" => (isset($arParams['COMMENTS_COUNT']) ? $arParams['COMMENTS_COUNT'] : '5'),
	"DETAIL_BLOG_EMAIL_NOTIFY" => (isset($arParams['DETAIL_BLOG_EMAIL_NOTIFY']) ? $arParams['DETAIL_BLOG_EMAIL_NOTIFY'] : 'Y'),
	"MAX_IMAGE_SIZE" => (isset($arParams['MAX_IMAGE_SIZE']) ? $arParams['MAX_IMAGE_SIZE'] : '0.5'),

	"DETAIL_BLOCKS_ORDER" => 'complect,nabor,offers,tabs,services,news,blog,staff,vacancy,gifts,goods', //($arParams["DETAIL_BLOCKS_ORDER"] ? $arParams["DETAIL_BLOCKS_ORDER"] : 'complect,nabor,offers,tabs,services,news,blog,staff,vacancy,goods'),
	"DETAIL_BLOCKS_TAB_ORDER" => 'desc,char,instructions,stores,video,reviews,buy,payment,delivery,custom_tab,ar,3d', //($arParams["DETAIL_BLOCKS_TAB_ORDER"] ? $arParams["DETAIL_BLOCKS_TAB_ORDER"] : 'desc,char,buy,payment,delivery,video,stores,reviews,custom_tab,buy_services'),
	"DETAIL_BLOCKS_ALL_ORDER" => 'complect,goods,nabor,offers,desc,char,buy,payment,delivery,video,stores,custom_tab,services,news,blog,reviews,staff,vacancy,gifts', //($arParams["DETAIL_BLOCKS_ALL_ORDER"] ? $arParams["DETAIL_BLOCKS_ALL_ORDER"] : 'complect,nabor,offers,desc,char,buy,payment,delivery,video,stores,custom_tab,services,news,reviews,blog,staff,vacancy,goods,buy_services'),
	"COUNT_SERVICES_IN_ANNOUNCE" => (isset($arParams["COUNT_SERVICES_IN_ANNOUNCE"]) ? $arParams["COUNT_SERVICES_IN_ANNOUNCE"] : '2'),
	"SHOW_ALL_SERVICES_IN_SLIDE" => (isset($arParams["SHOW_ALL_SERVICES_IN_SLIDE"]) ? $arParams["SHOW_ALL_SERVICES_IN_SLIDE"] : 'N'),
	"FILTER_NAME" => $arParams["FILTER_NAME"],
	"AJAX_JS" => $bTemplateParamAjaxJs,
	"CLEAR_TEMPLATE_DATA_OFFERS" => 1,
	"CURRENT_BASE_PAGE" => $sCurrentBasePage
);
if (!empty($arResult["ITEMS"])) {
?>
	<div class="equipment-block-body">
		<? if ($arResult['NAME']) : ?>
			<p class="product-param-choose-title equipment-block-title"><span><?= $iScuBlockCounter; ?>. </span><?= $arResult['NAME']; ?></p>
		<? endif; ?>
		<div class="equipment-block-summ-cost"><span>+ 0 ₽</span>к базовой стоимости</div>

		<? if ($arResult["ITEMS"] && $showElements) : ?>
			<ul class="equipment-block-template">
				<? foreach ($arResult["ITEMS"] as $key => $arItem) : ?>
					<? $arElementId[$arItem['ID']] = $arItem['ID'];
					if (!empty($arItem['PROPERTIES']['IMAGE_ACCESSORY']['VALUE'])) {
						$arItem['PREVIEW_PICTURE']['SRC'] = \CFile::GetPath($arItem['PROPERTIES']['IMAGE_ACCESSORY']['VALUE']);
					}
					?>
					<?
					$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
					$this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));
					?>
					<li data-item="<?= $arItem['ID']; ?>" <? //data-item - при клике вставляеся js оффер, чтобы добавить в корзину
															?> data-product="<?= $arItem['ID']; ?>" data-iblockid="<?= \Cosmos\Config::getInstance()->getIblockIdByCode('offers'); ?>" data-price="<?= $arItem['PRICE']; ?>" data-code="<?= $arItem['PROPERTIES']['SERVICE_PRICE']['VALUE']; ?>">

						<span class="cnt1">
							<span data-lazyload="" class="cnt_item lazyloaded" style="background-image:url('<?= $arItem['PREVIEW_PICTURE']['SRC']; ?>');" data-obgi="url('<?= $arItem['DETAIL_PICTURE']['SRC']; ?>')" title="<?= $arItem['NAME']; ?>"></span>
						</span><i title="<?= $arItem['NAME']; ?>"></i>
					</li>
				<? endforeach; ?>
			</ul>
			<?
			$arSizes = [];
			$arPropActive = array_keys($arResult['OFFERS_PROP']);
			?>
			<div class="catalog_detail">
				<div class="sku_props_accessuar">
					<div class="bx_item_detail_size">

					</div>
				</div>

			</div>
	</div>
<? endif; ?>

<? $iScuBlockCounter++; ?>
<div class="product-chars-body equipment-variants">
	<?
	foreach ($arResult["ITEMS"] as $key => $arItem) {
		$arElementParams["ELEMENT_ID"] = $arItem['ID'];
		$ElementID = $APPLICATION->IncludeComponent(
			"bitrix:catalog.element",
			"e1_equipment",
			$arElementParams,
			false
		);
	}
	?>
</div>

<?
} ?>