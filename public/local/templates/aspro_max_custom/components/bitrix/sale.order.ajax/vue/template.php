<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();

use Bitrix\Main;
use Bitrix\Main\Page\Asset;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var SaleOrderAjax $component
 * @var string $templateFolder
 */

$context      = Main\Application::getInstance()->getContext();
$request      = $context->getRequest();
$signer       = new Main\Security\Sign\Signer;
$signedParams = $signer->sign(base64_encode(serialize($arParams)), 'sale.order.ajax');
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/yandexpaypay/widget/build/widget.js')) {
    Asset::getInstance()->addString('<script src="/bitrix/js/yandexpaypay/widget/build/widget.js?' . time() . '"></script>', true);
}
if ($request->get('ORDER_ID') <> '') {
    include(Main\Application::getDocumentRoot() . $templateFolder . '/confirm.php');
} elseif ($arParams['DISABLE_BASKET_REDIRECT'] === 'Y' && $arResult['SHOW_EMPTY_BASKET']) {
    include(Main\Application::getDocumentRoot() . $templateFolder . '/empty.php');
} else {
    Asset::getInstance()->addString('<script>let orderInfo = ' . CUtil::PhpToJSObject($arResult['JS_DATA']) . ';</script>');
    Asset::getInstance()->addString('<script>let signedParamsString =  "' . CUtil::JSEscape($signedParams) . '";
    let siteID = "' . CUtil::JSEscape($component->getSiteId()) . '";
    let sessid = "' . CUtil::JSEscape(bitrix_sessid()) . '";
    let locations = "' . CUtil::PhpToJSObject($arResult['LOCATIONS']) . '";
    let ajaxUrl = "' . CUtil::JSEscape($component->getPath() . '/ajax.php') . '";</script>');

    Asset::getInstance()->addString('<script src="https://pay.yandex.ru/sdk/v1/pay.js?v=' . time() . '"></script>');
    Asset::getInstance()->addString('<script type="module" crossorigin src="/order/assets/index.js?v=' . Absteam\Helper::getAssetHash('/order/assets/index.js') . '"></script>');

    echo '<div id="app"></div>';
}
