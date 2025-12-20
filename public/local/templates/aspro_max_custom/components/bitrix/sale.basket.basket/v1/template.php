<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\UI\Extension::load("ui.fonts.ruble");

/**
 * @var array $arParams
 * @var array $arResult
 * @var string $templateFolder
 * @var string $templateName
 * @var CMain $APPLICATION
 * @var CBitrixBasketComponent $component
 * @var CBitrixComponentTemplate $this
 * @var array $giftParameters
 */

$documentRoot = Main\Application::getDocumentRoot();

$arGetBlueProductIds = array();
foreach ($arResult['ITEMS']['AnDelCanBuy'] as $item) {
	$arGetBlueProductIds[] = CCatalogSku::GetProductInfo($item['PRODUCT_ID'])['ID'];
}
$sGetBlueProductIds = implode(',', $arGetBlueProductIds);
if (empty($arParams['TEMPLATE_THEME'])) {
	$arParams['TEMPLATE_THEME'] = Main\ModuleManager::isModuleInstalled('bitrix.eshop') ? 'site' : 'blue';
}

if ($arParams['TEMPLATE_THEME'] === 'site') {
	$templateId = Main\Config\Option::get('main', 'wizard_template_id', 'eshop_bootstrap', $component->getSiteId());
	$templateId = preg_match('/^eshop_adapt/', $templateId) ? 'eshop_adapt' : $templateId;
	$arParams['TEMPLATE_THEME'] = Main\Config\Option::get('main', 'wizard_' . $templateId . '_theme_id', 'blue', $component->getSiteId());
}

if (!empty($arParams['TEMPLATE_THEME'])) {
	if (!is_file($documentRoot . '/bitrix/css/main/themes/' . $arParams['TEMPLATE_THEME'] . '/style.css')) {
		$arParams['TEMPLATE_THEME'] = 'blue';
	}
}

if (!isset($arParams['DISPLAY_MODE']) || !in_array($arParams['DISPLAY_MODE'], array('extended', 'compact'))) {
	$arParams['DISPLAY_MODE'] = 'extended';
}

$arParams['USE_DYNAMIC_SCROLL'] = isset($arParams['USE_DYNAMIC_SCROLL']) && $arParams['USE_DYNAMIC_SCROLL'] === 'N' ? 'N' : 'Y';
$arParams['SHOW_FILTER'] = isset($arParams['SHOW_FILTER']) && $arParams['SHOW_FILTER'] === 'N' ? 'N' : 'Y';

$arParams['PRICE_DISPLAY_MODE'] = isset($arParams['PRICE_DISPLAY_MODE']) && $arParams['PRICE_DISPLAY_MODE'] === 'N' ? 'N' : 'Y';

if (!isset($arParams['TOTAL_BLOCK_DISPLAY']) || !is_array($arParams['TOTAL_BLOCK_DISPLAY'])) {
	$arParams['TOTAL_BLOCK_DISPLAY'] = array('top');
}

if (empty($arParams['PRODUCT_BLOCKS_ORDER'])) {
	$arParams['PRODUCT_BLOCKS_ORDER'] = 'props,sku,columns';
}

if (is_string($arParams['PRODUCT_BLOCKS_ORDER'])) {
	$arParams['PRODUCT_BLOCKS_ORDER'] = explode(',', $arParams['PRODUCT_BLOCKS_ORDER']);
}

$arParams['USE_PRICE_ANIMATION'] = isset($arParams['USE_PRICE_ANIMATION']) && $arParams['USE_PRICE_ANIMATION'] === 'N' ? 'N' : 'Y';
$arParams['EMPTY_BASKET_HINT_PATH'] = isset($arParams['EMPTY_BASKET_HINT_PATH']) ? (string)$arParams['EMPTY_BASKET_HINT_PATH'] : '/';
$arParams['USE_ENHANCED_ECOMMERCE'] = isset($arParams['USE_ENHANCED_ECOMMERCE']) && $arParams['USE_ENHANCED_ECOMMERCE'] === 'Y' ? 'Y' : 'N';
$arParams['DATA_LAYER_NAME'] = isset($arParams['DATA_LAYER_NAME']) ? trim($arParams['DATA_LAYER_NAME']) : 'dataLayer';
$arParams['BRAND_PROPERTY'] = isset($arParams['BRAND_PROPERTY']) ? trim($arParams['BRAND_PROPERTY']) : '';

if ($arParams['USE_GIFTS'] === 'Y') {
	$arParams['GIFTS_BLOCK_TITLE'] = isset($arParams['GIFTS_BLOCK_TITLE']) ? trim((string)$arParams['GIFTS_BLOCK_TITLE']) : Loc::getMessage('SBB_GIFTS_BLOCK_TITLE');

	CBitrixComponent::includeComponentClass('bitrix:sale.products.gift.basket');

	$giftParameters = array(
		'SHOW_PRICE_COUNT' => 1,
		'PRODUCT_SUBSCRIPTION' => 'N',
		'PRODUCT_ID_VARIABLE' => 'id',
		'USE_PRODUCT_QUANTITY' => 'N',
		'ACTION_VARIABLE' => 'actionGift',
		'ADD_PROPERTIES_TO_BASKET' => 'Y',
		'PARTIAL_PRODUCT_PROPERTIES' => 'Y',

		'BASKET_URL' => $APPLICATION->GetCurPage(),
		'APPLIED_DISCOUNT_LIST' => $arResult['APPLIED_DISCOUNT_LIST'],
		'FULL_DISCOUNT_LIST' => $arResult['FULL_DISCOUNT_LIST'],

		'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
		'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_SHOW_VALUE'],
		'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],

		'BLOCK_TITLE' => $arParams['GIFTS_BLOCK_TITLE'],
		'HIDE_BLOCK_TITLE' => $arParams['GIFTS_HIDE_BLOCK_TITLE'],
		'TEXT_LABEL_GIFT' => $arParams['GIFTS_TEXT_LABEL_GIFT'],

		'DETAIL_URL' => isset($arParams['GIFTS_DETAIL_URL']) ? $arParams['GIFTS_DETAIL_URL'] : null,
		'PRODUCT_QUANTITY_VARIABLE' => $arParams['GIFTS_PRODUCT_QUANTITY_VARIABLE'],
		'PRODUCT_PROPS_VARIABLE' => $arParams['GIFTS_PRODUCT_PROPS_VARIABLE'],
		'SHOW_OLD_PRICE' => $arParams['GIFTS_SHOW_OLD_PRICE'],
		'SHOW_DISCOUNT_PERCENT' => $arParams['GIFTS_SHOW_DISCOUNT_PERCENT'],
		'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
		'MESS_BTN_BUY' => $arParams['GIFTS_MESS_BTN_BUY'],
		'MESS_BTN_DETAIL' => $arParams['GIFTS_MESS_BTN_DETAIL'],
		'CONVERT_CURRENCY' => $arParams['GIFTS_CONVERT_CURRENCY'],
		'HIDE_NOT_AVAILABLE' => $arParams['GIFTS_HIDE_NOT_AVAILABLE'],

		'PRODUCT_ROW_VARIANTS' => '',
		'PAGE_ELEMENT_COUNT' => 0,
		'DEFERRED_PRODUCT_ROW_VARIANTS' => \Bitrix\Main\Web\Json::encode(
			SaleProductsGiftBasketComponent::predictRowVariants(
				$arParams['GIFTS_PAGE_ELEMENT_COUNT'],
				$arParams['GIFTS_PAGE_ELEMENT_COUNT']
			)
		),
		'DEFERRED_PAGE_ELEMENT_COUNT' => $arParams['GIFTS_PAGE_ELEMENT_COUNT'],

		'ADD_TO_BASKET_ACTION' => 'BUY',
		'PRODUCT_DISPLAY_MODE' => 'Y',
		'PRODUCT_BLOCKS_ORDER' => isset($arParams['GIFTS_PRODUCT_BLOCKS_ORDER']) ? $arParams['GIFTS_PRODUCT_BLOCKS_ORDER'] : '',
		'SHOW_SLIDER' => isset($arParams['GIFTS_SHOW_SLIDER']) ? $arParams['GIFTS_SHOW_SLIDER'] : '',
		'SLIDER_INTERVAL' => isset($arParams['GIFTS_SLIDER_INTERVAL']) ? $arParams['GIFTS_SLIDER_INTERVAL'] : '',
		'SLIDER_PROGRESS' => isset($arParams['GIFTS_SLIDER_PROGRESS']) ? $arParams['GIFTS_SLIDER_PROGRESS'] : '',
		'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],

		'USE_ENHANCED_ECOMMERCE' => $arParams['USE_ENHANCED_ECOMMERCE'],
		'DATA_LAYER_NAME' => $arParams['DATA_LAYER_NAME'],
		'BRAND_PROPERTY' => $arParams['BRAND_PROPERTY']
	);
}

\CJSCore::Init(array('fx', 'popup', 'ajax'));

$this->addExternalCss('/bitrix/css/main/bootstrap.css');
$this->addExternalCss($templateFolder . '/themes/' . $arParams['TEMPLATE_THEME'] . '/style.css');

$this->addExternalJs($templateFolder . '/js/mustache.js');
$this->addExternalJs($templateFolder . '/js/action-pool.js');
$this->addExternalJs($templateFolder . '/js/filter.js');
$this->addExternalJs($templateFolder . '/js/component.js');

$mobileColumns = isset($arParams['COLUMNS_LIST_MOBILE'])
	? $arParams['COLUMNS_LIST_MOBILE']
	: $arParams['COLUMNS_LIST'];
$mobileColumns = array_fill_keys($mobileColumns, true);

$jsTemplates = new Main\IO\Directory($documentRoot . $templateFolder . '/js-templates');
/** @var Main\IO\File $jsTemplate */
foreach ($jsTemplates->getChildren() as $jsTemplate) {
	include($jsTemplate->getPath());
}

$displayModeClass = $arParams['DISPLAY_MODE'] === 'compact' ? ' basket-items-list-wrapper-compact' : '';

if (empty($arResult['ERROR_MESSAGE'])) {
	if ($arParams['USE_GIFTS'] === 'Y' && $arParams['GIFTS_PLACE'] === 'TOP') {
?>
		<div data-entity="parent-container">
			<div class="catalog-block-header" data-entity="header" data-showed="false" style="display: none; opacity: 0;">
				<?= $arParams['GIFTS_BLOCK_TITLE'] ?>
			</div>
			<?
			$APPLICATION->IncludeComponent(
				'bitrix:sale.products.gift.basket',
				'.default',
				$giftParameters,
				$component
			);
			?>
		</div>
	<?
	}

	if ($arResult['BASKET_ITEM_MAX_COUNT_EXCEEDED']) {
	?>
		<div id="basket-item-message">
			<?= Loc::getMessage('SBB_BASKET_ITEM_MAX_COUNT_EXCEEDED', array('#PATH#' => $arParams['PATH_TO_BASKET'])) ?>
		</div>
	<?
	}
	?>
	<div id="basket-root" class="bx-basket bx-<?= $arParams['TEMPLATE_THEME'] ?> bx-step-opacity" style="opacity: 0;">
		<?
		if (
			$arParams['BASKET_WITH_ORDER_INTEGRATION'] !== 'Y'
			&& in_array('top', $arParams['TOTAL_BLOCK_DISPLAY'])
			&& $arParams['DELAYED_BASKET'] != 'Y'
		) {
		?>
			<div class="row">
				<div class="col-xs-12" data-entity="basket-total-block"></div>
			</div>

			<div class="row">
				<div class="split-div"></div>
				<style>
					.ya-pay-widget,
					.split-div {
						width: 500px !important;
					}
				</style>
			</div>
		<?
		}
		?>

		<div class="row">
			<div class="col-xs-12">
				<div class="alert alert-warning alert-dismissable" id="basket-warning" style="display: none;">
					<span class="close" data-entity="basket-items-warning-notification-close">&times;</span>
					<div data-entity="basket-general-warnings"></div>
					<div data-entity="basket-item-warnings">
						<?= Loc::getMessage('SBB_BASKET_ITEM_WARNING') ?>
					</div>
				</div>
			</div>
		</div>

		<div class="row" id="main">
			<div class="col-xs-12">
				<div class="basket-items-list-wrapper basket-items-list-wrapper-height-fixed basket-items-list-wrapper-light<?= $displayModeClass ?>" id="basket-items-list-wrapper">
					<div class="basket-items-list-header" data-entity="basket-items-list-header">
						<div class="basket-items-search-field" data-entity="basket-filter">
							<div class="form has-feedback">
								<input type="text" class="form-control" placeholder="<?= Loc::getMessage('SBB_BASKET_FILTER') ?>" data-entity="basket-filter-input">
								<span class="form-control-feedback basket-clear" data-entity="basket-filter-clear-btn"></span>
							</div>
						</div>
						<div class="basket-items-list-header-filter">
							<a href="javascript:void(0)" class="basket-items-list-header-filter-item active" data-entity="basket-items-count" data-filter="all" style="display: none;"></a>
							<a href="javascript:void(0)" class="basket-items-list-header-filter-item" data-entity="basket-items-count" data-filter="similar" style="display: none;"></a>
							<a href="javascript:void(0)" class="basket-items-list-header-filter-item" data-entity="basket-items-count" data-filter="warning" style="display: none;"></a>
							<a href="javascript:void(0)" class="basket-items-list-header-filter-item" data-entity="basket-items-count" data-filter="delayed" style="display: none;"></a>
							<a href="javascript:void(0)" class="basket-items-list-header-filter-item" data-entity="basket-items-count" data-filter="not-available" style="display: none;"></a>
						</div>
					</div>
					<div class="basket-items-list-container" id="basket-items-list-container">
						<div class="basket-items-list-overlay" id="basket-items-list-overlay" style="display: none;"></div>
						<div class="basket-items-list" id="basket-item-list">
							<div class="basket-search-not-found" id="basket-item-list-empty-result" style="display: none;">
								<div class="basket-search-not-found-icon"></div>
								<div class="basket-search-not-found-text">
									<?= Loc::getMessage('SBB_FILTER_EMPTY_RESULT') ?>
								</div>
							</div>
							<table class="basket-items-list-table" id="basket-item-table"></table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?
		if (
			$arParams['BASKET_WITH_ORDER_INTEGRATION'] !== 'Y'
			&& in_array('bottom', $arParams['TOTAL_BLOCK_DISPLAY'])
			&& $arParams['DELAYED_BASKET'] != 'Y'
		) {
		?>
			<div class="row">
				<div class="col-xs-12" data-entity="basket-total-block"></div>

				<div class="split-div col-xs-12"></div>
				<style>
					.split-div {
						width: 100% !important;
						margin-top: -16px;
					}
				</style>
			</div>
		<?
		}
		?>
	</div>
	<?
	if (!empty($arResult['CURRENCIES']) && Main\Loader::includeModule('currency')) {
		CJSCore::Init('currency');

	?>
		<script>
			BX.Currency.setCurrencies(<?= CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true) ?>);
		</script>
	<?
	}

	$signer = new \Bitrix\Main\Security\Sign\Signer;
	$signedTemplate = $signer->sign($templateName, 'sale.basket.basket');
	$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.basket.basket');
	$messages = Loc::loadLanguageFile(__FILE__);
	?>
	<script>
		BX.message(<?= CUtil::PhpToJSObject($messages) ?>);
		BX.Sale.BasketComponent.init({
			result: <?= CUtil::PhpToJSObject($arResult, false, false, true) ?>,
			params: <?= CUtil::PhpToJSObject($arParams) ?>,
			template: '<?= CUtil::JSEscape($signedTemplate) ?>',
			signedParamsString: '<?= CUtil::JSEscape($signedParams) ?>',
			siteId: '<?= CUtil::JSEscape($component->getSiteId()) ?>',
			siteTemplateId: '<?= CUtil::JSEscape($component->getSiteTemplateId()) ?>',
			templateFolder: '<?= CUtil::JSEscape($templateFolder) ?>'
		});
	</script>
	<?
	if ($arParams['USE_GIFTS'] === 'Y' && $arParams['GIFTS_PLACE'] === 'BOTTOM') {
	?>
		<div data-entity="parent-container">
			<div class="catalog-block-header" data-entity="header" data-showed="false" style="display: none; opacity: 0;">
				<?= $arParams['GIFTS_BLOCK_TITLE'] ?>
			</div>
			<?
			$APPLICATION->IncludeComponent(
				'bitrix:sale.products.gift.basket',
				'.default',
				$giftParameters,
				$component
			);
			?>
		</div>
	<?
	}
	?>

	<? //if($USER->IsAdmin() || $_GET['test'] == 'y'):
	?>
	<div class="dont-forget-buy-block full-window-width-block">
		<div class="maxwidth-theme">
			<? include 'recom_products_component_params.php'; ?>
			<?
			$GLOBALS['arRecomProductsFilter']['SECTION_ID'] = 354;
			$GLOBALS['arRecomProductsFilter']['INCLUDE_SUBSECTIONS'] = 'Y';
			?>
			<? $APPLICATION->IncludeComponent(
				"bitrix:catalog.section",
				"catalog_slider",
				array(
					"SLIDE_ITEMS" => "Y",
					"IBLOCK_TYPE" => $arRecomProductsComponentParams["IBLOCK_TYPE"],
					"IBLOCK_ID" => $arRecomProductsComponentParams["IBLOCK_ID"],
					"USE_REGION" => ($GLOBALS['arRegion'] ? "Y" : "N"),
					"STORES" => $arRecomProductsComponentParams['STORES'],
					"SHOW_BIG_BLOCK" => 'N',
					"IS_CATALOG_PAGE" => 'N',
					"SHOW_UNABLE_SKU_PROPS" => $arRecomProductsComponentParams["SHOW_UNABLE_SKU_PROPS"],
					"ALT_TITLE_GET" => $arRecomProductsComponentParams["ALT_TITLE_GET"],
					"SEF_URL_TEMPLATES" => $arRecomProductsComponentParams["SEF_URL_TEMPLATES"],
					"SHOW_COUNTER_LIST" => $arRecomProductsComponentParams["SHOW_COUNTER_LIST"],
					"SECTION_ID" => '',
					"SECTION_CODE" => '',
					"AJAX_REQUEST" => $isAjax,
					"ELEMENT_SORT_FIELD" => $sort,
					"ELEMENT_SORT_ORDER" => $sort_order,
					"SHOW_DISCOUNT_TIME_EACH_SKU" => $arRecomProductsComponentParams["SHOW_DISCOUNT_TIME_EACH_SKU"],
					"ELEMENT_SORT_FIELD2" => $arRecomProductsComponentParams["ELEMENT_SORT_FIELD2"],
					"ELEMENT_SORT_ORDER2" => $arRecomProductsComponentParams["ELEMENT_SORT_ORDER2"],
					"FILTER_NAME" => 'arRecomProductsFilter',
					"INCLUDE_SUBSECTIONS" => "Y",
					"SHOW_ALL_WO_SECTION" => "Y",
					"PAGE_ELEMENT_COUNT" => '10',
					"LINE_ELEMENT_COUNT" => '1',
					"SET_LINE_ELEMENT_COUNT" => $bSetElementsLineRow,
					"DISPLAY_TYPE" => $display,
					"TYPE_SKU" => ($typeSKU ? $typeSKU : $arTheme["TYPE_SKU"]["VALUE"]),
					"SET_SKU_TITLE" => ((($typeSKU == "TYPE_1" || $arTheme["TYPE_SKU"]["VALUE"] == "TYPE_1") && $arTheme["CHANGE_TITLE_ITEM"]["VALUE"] == "Y") ? "Y" : ""),
					"PROPERTY_CODE" => $arRecomProductsComponentParams["LIST_PROPERTY_CODE"],
					"SHOW_ARTICLE_SKU" => $arRecomProductsComponentParams["SHOW_ARTICLE_SKU"],
					"SHOW_MEASURE_WITH_RATIO" => $arRecomProductsComponentParams["SHOW_MEASURE_WITH_RATIO"],
					"MAX_SCU_COUNT_VIEW" => $arTheme['MAX_SCU_COUNT_VIEW']['VALUE'],
					"OFFERS_FIELD_CODE" => $arRecomProductsComponentParams["LIST_OFFERS_FIELD_CODE"],
					"OFFERS_PROPERTY_CODE" => $arRecomProductsComponentParams["LIST_OFFERS_PROPERTY_CODE"],
					"OFFERS_SORT_FIELD" => $arRecomProductsComponentParams["OFFERS_SORT_FIELD"],
					"OFFERS_SORT_ORDER" => $arRecomProductsComponentParams["OFFERS_SORT_ORDER"],
					"OFFERS_SORT_FIELD2" => $arRecomProductsComponentParams["OFFERS_SORT_FIELD2"],
					"OFFERS_SORT_ORDER2" => $arRecomProductsComponentParams["OFFERS_SORT_ORDER2"],
					'OFFER_TREE_PROPS' => $arRecomProductsComponentParams['OFFER_TREE_PROPS'],
					'OFFER_SHOW_PREVIEW_PICTURE_PROPS' => $arRecomProductsComponentParams['OFFER_SHOW_PREVIEW_PICTURE_PROPS'],
					"OFFERS_LIMIT" => $arRecomProductsComponentParams["LIST_OFFERS_LIMIT"],
					"SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
					"DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
					"BASKET_URL" => $arRecomProductsComponentParams["BASKET_URL"],
					"ACTION_VARIABLE" => $arRecomProductsComponentParams["ACTION_VARIABLE"],
					"PRODUCT_ID_VARIABLE" => $arRecomProductsComponentParams["PRODUCT_ID_VARIABLE"],
					"PRODUCT_QUANTITY_VARIABLE" => "quantity",
					"PRODUCT_PROPS_VARIABLE" => "prop",
					"MAX_GALLERY_ITEMS" => $arRecomProductsComponentParams["MAX_GALLERY_ITEMS"],
					"SHOW_GALLERY" => $arRecomProductsComponentParams["SHOW_GALLERY"],
					"SHOW_PROPS" => (CMax::GetFrontParametrValue("SHOW_PROPS_BLOCK") == "Y" ? "Y" : "N"),
					'SHOW_POPUP_PRICE' => (CMax::GetFrontParametrValue('SHOW_POPUP_PRICE') == 'Y' ? "Y" : "N"),
					"TYPE_VIEW_BASKET_BTN" => "TYPE_2",
					"COMPONENT_TEMPLATE" => "catalog_block",
					'TYPE_VIEW_CATALOG_LIST' => CMax::GetFrontParametrValue('TYPE_VIEW_CATALOG_LIST'),
					'SHOW_STORES_POPUP' => (CMax::GetFrontParametrValue('STORES_SOURCE') == 'STORES' && $arRecomProductsComponentParams['STORES']),
					"SECTION_ID_VARIABLE" => $arRecomProductsComponentParams["SECTION_ID_VARIABLE"],
					//"SET_LAST_MODIFIED" => $arRecomProductsComponentParams["SET_LAST_MODIFIED"],
					//"AJAX_MODE" => $arRecomProductsComponentParams["AJAX_MODE"],
					//"AJAX_OPTION_JUMP" => $arRecomProductsComponentParams["AJAX_OPTION_JUMP"],
					//"AJAX_OPTION_STYLE" => $arRecomProductsComponentParams["AJAX_OPTION_STYLE"],
					//"AJAX_OPTION_HISTORY" => $arRecomProductsComponentParams["AJAX_OPTION_HISTORY"],
					"CACHE_TYPE" => $arRecomProductsComponentParams["CACHE_TYPE"],
					"CACHE_TIME" => $arRecomProductsComponentParams["CACHE_TIME"],
					"CACHE_GROUPS" => $arRecomProductsComponentParams["CACHE_GROUPS"],
					"CACHE_FILTER" => $arRecomProductsComponentParams["CACHE_FILTER"],
					//"META_KEYWORDS" => $arRecomProductsComponentParams["LIST_META_KEYWORDS"],
					//"META_DESCRIPTION" => $arRecomProductsComponentParams["LIST_META_DESCRIPTION"],
					//"BROWSER_TITLE" => $arRecomProductsComponentParams["LIST_BROWSER_TITLE"],
					//"ADD_SECTIONS_CHAIN" => ($iSectionsCount && $arRecomProductsComponentParams['INCLUDE_SUBSECTIONS'] == "N") ? 'N' : $arRecomProductsComponentParams["ADD_SECTIONS_CHAIN"],
					"HIDE_NOT_AVAILABLE" => $arRecomProductsComponentParams["HIDE_NOT_AVAILABLE"],
					'HIDE_NOT_AVAILABLE_OFFERS' => $arRecomProductsComponentParams["HIDE_NOT_AVAILABLE_OFFERS"],
					"DISPLAY_COMPARE" => CMax::GetFrontParametrValue('CATALOG_COMPARE'),
					"USE_FAST_VIEW" => CMax::GetFrontParametrValue('USE_FAST_VIEW_PAGE_DETAIL'),
					"MANY_BUY_CATALOG_SECTIONS" => CMax::GetFrontParametrValue('MANY_BUY_CATALOG_SECTIONS'),
					"SET_TITLE" => 'N',
					"SET_STATUS_404" => 'N',
					"SHOW_404" => 'N',
					"PRICE_CODE" => (reset($GLOBALS['arRegion']['LIST_PRICES']) != 'component') ? array_keys($GLOBALS['arRegion']['LIST_PRICES']) : '',
					"USE_PRICE_COUNT" => $arRecomProductsComponentParams["USE_PRICE_COUNT"],
					"SHOW_PRICE_COUNT" => $arRecomProductsComponentParams["SHOW_PRICE_COUNT"],
					"PRICE_VAT_INCLUDE" => $arRecomProductsComponentParams["PRICE_VAT_INCLUDE"],
					"USE_PRODUCT_QUANTITY" => $arRecomProductsComponentParams["USE_PRODUCT_QUANTITY"],
					"OFFERS_CART_PROPERTIES" => $arRecomProductsComponentParams["OFFERS_CART_PROPERTIES"],
					"DISPLAY_TOP_PAGER" => 'N',
					"DISPLAY_BOTTOM_PAGER" => 'N',
					//"PAGER_TITLE" => $arRecomProductsComponentParams["PAGER_TITLE"],
					//"PAGER_SHOW_ALWAYS" => $arRecomProductsComponentParams["PAGER_SHOW_ALWAYS"],
					//"PAGER_TEMPLATE" => $arRecomProductsComponentParams["PAGER_TEMPLATE"],
					//"PAGER_DESC_NUMBERING" => $arRecomProductsComponentParams["PAGER_DESC_NUMBERING"],
					//"PAGER_DESC_NUMBERING_CACHE_TIME" => $arRecomProductsComponentParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
					//"PAGER_SHOW_ALL" => $arRecomProductsComponentParams["PAGER_SHOW_ALL"],
					"AJAX_OPTION_ADDITIONAL" => "",
					"ADD_CHAIN_ITEM" => "N",
					"SHOW_QUANTITY" => $arRecomProductsComponentParams["SHOW_QUANTITY"],
					"ADD_DETAIL_TO_SLIDER" => $arRecomProductsComponentParams["DETAIL_ADD_DETAIL_TO_SLIDER"],
					"OFFER_ADD_PICT_PROP" => $arRecomProductsComponentParams["OFFER_ADD_PICT_PROP"],
					"SHOW_QUANTITY_COUNT" => $arRecomProductsComponentParams["SHOW_QUANTITY_COUNT"],
					"SHOW_DISCOUNT_PERCENT_NUMBER" => $arRecomProductsComponentParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
					"SHOW_DISCOUNT_PERCENT" => $arRecomProductsComponentParams["SHOW_DISCOUNT_PERCENT"],
					"SHOW_DISCOUNT_TIME" => $arRecomProductsComponentParams["SHOW_DISCOUNT_TIME"],
					"SHOW_ONE_CLICK_BUY" => $arRecomProductsComponentParams["SHOW_ONE_CLICK_BUY"],
					"SHOW_OLD_PRICE" => $arRecomProductsComponentParams["SHOW_OLD_PRICE"],
					"CONVERT_CURRENCY" => $arRecomProductsComponentParams["CONVERT_CURRENCY"],
					"CURRENCY_ID" => $arRecomProductsComponentParams["CURRENCY_ID"],
					"USE_STORE" => $arRecomProductsComponentParams["USE_STORE"],
					"MAX_AMOUNT" => $arRecomProductsComponentParams["MAX_AMOUNT"],
					"MIN_AMOUNT" => $arRecomProductsComponentParams["MIN_AMOUNT"],
					"USE_MIN_AMOUNT" => $arRecomProductsComponentParams["USE_MIN_AMOUNT"],
					"USE_ONLY_MAX_AMOUNT" => $arRecomProductsComponentParams["USE_ONLY_MAX_AMOUNT"],
					"DISPLAY_WISH_BUTTONS" => $arRecomProductsComponentParams["DISPLAY_WISH_BUTTONS"],
					"LIST_DISPLAY_POPUP_IMAGE" => $arRecomProductsComponentParams["LIST_DISPLAY_POPUP_IMAGE"],
					"DEFAULT_COUNT" => $arRecomProductsComponentParams["DEFAULT_COUNT"],
					"SHOW_MEASURE" => $arRecomProductsComponentParams["SHOW_MEASURE"],
					"SHOW_HINTS" => $arRecomProductsComponentParams["SHOW_HINTS"],
					"USE_CUSTOM_RESIZE_LIST" => $arTheme['USE_CUSTOM_RESIZE_LIST']['VALUE'],
					"OFFER_HIDE_NAME_PROPS" => $arRecomProductsComponentParams["OFFER_HIDE_NAME_PROPS"],
					"SHOW_SECTIONS_LIST_PREVIEW" => $arRecomProductsComponentParams["SHOW_SECTIONS_LIST_PREVIEW"],
					"SECTIONS_LIST_PREVIEW_PROPERTY" => $arRecomProductsComponentParams["SECTIONS_LIST_PREVIEW_PROPERTY"],
					"SHOW_SECTION_LIST_PICTURES" => $arRecomProductsComponentParams["SHOW_SECTION_LIST_PICTURES"],
					"USE_MAIN_ELEMENT_SECTION" => $arRecomProductsComponentParams["USE_MAIN_ELEMENT_SECTION"],
					"ADD_PROPERTIES_TO_BASKET" => (isset($arRecomProductsComponentParams["ADD_PROPERTIES_TO_BASKET"]) ? $arRecomProductsComponentParams["ADD_PROPERTIES_TO_BASKET"] : ''),
					"PARTIAL_PRODUCT_PROPERTIES" => (isset($arRecomProductsComponentParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arRecomProductsComponentParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
					"PRODUCT_PROPERTIES" => $arRecomProductsComponentParams["PRODUCT_PROPERTIES"],
					"SALE_STIKER" => $arRecomProductsComponentParams["SALE_STIKER"],
					"STIKERS_PROP" => $arRecomProductsComponentParams["STIKERS_PROP"],
					"SHOW_RATING" => $arRecomProductsComponentParams["SHOW_RATING"],
					"REVIEWS_VIEW" => (isset($arTheme['REVIEWS_VIEW']['VALUE']) && $arTheme['REVIEWS_VIEW']['VALUE'] == 'EXTENDED') || (!isset($arTheme['REVIEWS_VIEW']['VALUE']) && isset($arTheme['REVIEWS_VIEW']) && $arTheme['REVIEWS_VIEW'] ==  'EXTENDED'),
					"ADD_PICT_PROP" => $arRecomProductsComponentParams["ADD_PICT_PROP"],
					"IBINHERIT_TEMPLATES" => $arSeoItem ? $arIBInheritTemplates : array(),
					"FIELDS" => $arRecomProductsComponentParams['FIELDS'],
					"USER_FIELDS" => $arRecomProductsComponentParams['USER_FIELDS'],
					"SECTION_COUNT_ELEMENTS" => $arRecomProductsComponentParams["SECTION_COUNT_ELEMENTS"],
					"PAGE_TYPE" => 'slider',
					"BLOCK_TITLE" => 'Не забудьте купить',
					'SLIDER_PARAMS_STRING' => '{"nav": true, "margin": 15, "autoplay" : false,  "autoplayTimeout" : "3000", "smartSpeed":1000, "loop": true, "responsiveClass": true, "responsive":{"0":{"items": 2},"600":{"items": 2},"768":{"items": 3},"1200":{"items": 5}}}'
				)
			); ?>
		</div>
	</div>
	<script>
		fullWindowWidthBlockHandler();
	</script>
	<? //endif;
	?>

	<? //FB страница корзины
	$arrFbItems = [];
	$sumPriceItems = 0;
	foreach ($arResult["GRID"]["ROWS"] as $item) {
		if ($item["IBLOCK_ID"] == 48) {
			$arrFbItems[] = array(
				'id' => $item["PRODUCT_ID"],
				'product_id' => $item["PRODUCT_ID"],
				'quantity' => $item["QUANTITY"],
				'price' => $item['PRICE']
			);
			$sumPriceItems = $sumPriceItems + $item["SUM_FULL_PRICE"];
		} elseif ($item["IBLOCK_ID"] == 49) {
			$mxResult = CCatalogSku::GetProductInfo(
				$item["PRODUCT_ID"]
			);
			if (is_array($mxResult)) {
				$arrFbItems[] = array(
					'id' => $item["PRODUCT_ID"],
					'product_id' => $mxResult["ID"],
					'quantity' => $item["QUANTITY"],
					'price' => $item['PRICE']
				);
			}
			$sumPriceItems = $sumPriceItems + $item["SUM_FULL_PRICE"];
		}
	}

	if (!empty($arResult['allSum'])) {
		$sumPriceItems = $arResult['allSum'];
	}
	?>
	<script>
		window.productPageView = true;
		var arrFbItems = [];
		var itemsIds = [];
		var arrVkItems = [];
		var getblueItems = '';
		var arrSlonItems = [];
		<?
		foreach ($arrFbItems as $k => $item) { ?>
			var fbItem = {
				//id: '<?= $item["product_id"] ?>',//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
				id: '<?= $item["id"] ?>',
				quantity: <?= $item["quantity"] ?>,
			};
			arrFbItems.push(fbItem);
			var vkItem = {
				//"id": '<?= $item["product_id"] ?>'//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
				"id": '<?= $item["id"] ?>'
			};
			arrVkItems.push(vkItem);
			//var item_id = '<?= $item["product_id"] ?>';//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
			var item_id = '<?= $item["id"] ?>';
			itemsIds.push(item_id);
			<? if ($k > 0) { ?>
				//getblueItems += ',<?= $item["product_id"] ?>';//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
				getblueItems += ',<?= $item["id"] ?>';
			<? } else { ?>
				//getblueItems += '<?= $item["product_id"] ?>';//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
				getblueItems += '<?= $item["id"] ?>';
			<? } ?>

			var slonItem = {
				//id: '<?= $item["product_id"] ?>',//заменить на $item["id"], если в фиде будут передаваться id торговых предложений
				id: '<?= $item["id"] ?>',
				quantity: <?= $item["quantity"] ?>,
				price: <?= $item['price']; ?>
			};
			arrSlonItems.push(slonItem);

		<? } ?>
		//закомментил фейсбук, ломает скрипт
		// fbq('track', 'InitiateCheckout', {
		// 	value: <?= $sumPriceItems ?>,
		// 	currency: 'RUB',
		// 	contents: arrFbItems,
		// });
		//mail.ru страница корзины
		var _tmr = window._tmr || [];
		_tmr.push({
			id: '2828139',
			type: 'itemView',
			productid: itemsIds,
			pagetype: 'cart',
			list: '1',
			totalvalue: '<?= $sumPriceItems ?>'
		});
		//mgid страница корзины
		/*(function() {
			var d = document, w = window;
			w.MgSensorData = w.MgSensorData || [];
			w.MgSensorData.push({
				cid:280113,
				lng:"us",
				nosafari:true,
				eid: itemsIds,
				partner: 578829,
				project: "a.mgid.com",
				goods:[0]
			});
			var l = "a.mgid.com";
			var n = d.getElementsByTagName("script")[0];
			var s = d.createElement("script");
			s.type = "text/javascript";
			s.async = true;
			var dt = !Date.now?new Date().valueOf():Date.now();
			s.src = "//" + l + "/mgsensor.js?d=" + dt;
			n.parentNode.insertBefore(s, n);
		})();*/
		//VK страница корзины
		window.vkAsyncInit = function() {
			window.VK.Retargeting.Init('VK-RTRG-333568-aT9Ur');
			const eventParams = {
				"products": arrVkItems,
				"total_price": "<?= $sumPriceItems ?>"
			};
			window.VK.Retargeting.ProductEvent(2842, "add_to_cart", eventParams);
		}

		//getblue страница корзины
		window.blue_q = window.blue_q || [];
		window.blue_q.push({
			event: "setCampaignId",
			value: "00817ED8-9D92-C9B7-14D7CD0015C22D53"
		}, {
			event: "setProductId",
			value: "<?= $sGetBlueProductIds ?>"
		}, {
			event: "setPageType",
			value: "basket"
		});
		// ГдеСлон
		window.gdeslon_q = window.gdeslon_q || [];
		window.gdeslon_q.push({
			page_type: "basket",
			products: arrSlonItems,
			merchant_id: "100062",
			deduplication: gdeSlonUtm(),
			<? if (!empty($GLOBALS['USER']->GetID())) : ?>user_id: "<?= $GLOBALS['USER']->GetID(); ?>"
		<? endif; ?>
		});
	</script>
<?
} elseif ($arResult['EMPTY_BASKET']) {
	include(Main\Application::getDocumentRoot() . $templateFolder . '/empty.php');
} else {
	ShowError($arResult['ERROR_MESSAGE']);
}

// подключаем Яндекс Пэй 
Bitrix\Main\Page\Asset::getInstance()->addString('<script src="https://pay.yandex.ru/sdk/v1/pay.js?v=' . time() . '"></script>');
