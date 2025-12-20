<?

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

Loader::registerAutoLoadClasses(null, 
	array(
		'COrwoWysiwygEditorUserField' => '/local/php_interface/include/classes/COrwoWysiwygEditorUserField.php',
		'COrwoFindOfferUserField' => '/local/php_interface/include/classes/COrwoFindOfferUserField.php',
		'CMaxCustom' => '/local/php_interface/include/classes/CMaxCustom.php',
		'COrwoFunctions' => '/local/php_interface/include/classes/COrwoFunctions.php',
		'COrwoEvents' => '/local/php_interface/include/classes/COrwoEvents.php',
		'CAsproEvents' => '/local/php_interface/include/classes/CAsproEvents.php',
		'COrwoSeviceProp' => '/local/php_interface/include/classes/COrwoSeviceProp.php',
		'CAsproMaxItemCustom' => '/local/php_interface/include/classes/CAsproMaxItemCustom.php',
		'CAsproMaxSkuCustom' => '/local/php_interface/include/classes/CAsproMaxSkuCustom.php',
		'COrwoSubdomain' => '/local/php_interface/include/classes/COrwoSubdomain.php',
		'\COrwoSeoTemplate\OnTemplateGetFunctionClass' => '/local/php_interface/include/classes/COrwoSeoTemplate.php',
		'COrwoBitrix24' => '/local/php_interface/include/classes/COrwoBitrix24.php',
        'COrwo1CSaleExport' => '/local/php_interface/include/classes/COrwo1CSaleExport.php',
        'COrwo1CSaleOrderLoader' => '/local/php_interface/include/classes/COrwo1CSaleOrderLoader.php',
        'BuyRestriction' => '/local/php_interface/include/classes/BuyRestriction.php',
	)
);

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler('aspro.max', "OnAsproGetBuyBlockElement", array("CAsproEvents", "OnGetBuyBlockElement"));
$eventManager->addEventHandler('aspro.max', "OnAsproShowStickers", array("CAsproEvents", "OnAsproShowStickersHandler"));
$eventManager->addEventHandler('aspro.max', "OnAsproShowSideFormLinkIcons", array("CAsproEvents", "OnAsproShowSideFormLinkIconsHandler"));
$eventManager->addEventHandler('aspro.max', "OnAsproRegionalityGetCurrentRegion", array("CAsproEvents", "OnAsproRegionalityGetCurrentRegionHandler"), false, 10000);
$eventManager->addEventHandler('aspro.max', "OnAsproShowDiscountCounter", array("CAsproEvents", "OnAsproShowDiscountCounterHandler"));
$eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList', array('COrwoSeviceProp', 'OnIBlockPropertyBuildList'));
$eventManager->addEventHandler('iblock', 'OnTemplateGetFunctionClass', array("\COrwoSeoTemplate\OnTemplateGetFunctionClass", "eventHandler"));
$eventManager->addEventHandler("main", "OnProlog", array("COrwoEvents", "OnPrologHandler"));
$eventManager->addEventHandler("main", "OnEpilog", array("COrwoEvents", "OnEpilogHandler"));
$eventManager->addEventHandler("main", "OnBuildGlobalMenu", array("COrwoEvents", "OnBuildGlobalMenuHandler"));
$eventManager->addEventHandler('main', 'OnUserTypeBuildList', array('COrwoWysiwygEditorUserField', 'GetUserTypeDescription'));
$eventManager->addEventHandler('main', 'OnUserTypeBuildList', array('COrwoFindOfferUserField', 'GetUserTypeDescription'));
$eventManager->addEventHandler('sale', 'onSaleDeliveryHandlersClassNamesBuildList', array("COrwoEvents", "AddCustomDeliveryServices"));
$eventManager->addEventHandler('sale', 'onSaleDeliveryExtraServicesClassNamesBuildList', array("COrwoEvents", "AddCustomDeliveryExtraServices"));
$eventManager->addEventHandler('sale', 'OnSaleComponentOrderResultPrepared', array("COrwoEvents", "OnSaleComponentOrderResultPreparedHandler"));
$eventManager->addEventHandler('sale', 'OnSaleOrderSaved', array("COrwoEvents", "OnSaleOrderSavedHandler"));
$eventManager->addEventHandler('sale', 'OnSaleOrderBeforeSaved', array("COrwoEvents", "saleOrderBeforeSaved"));
$eventManager->addEventHandler('form', 'onAfterResultAdd', array("COrwoEvents", "OnAfterResultAddHandler"));
$eventManager->addEventHandler('form', 'onBeforeResultAdd', array("COrwoEvents", "OnBeforeResultAddHandler"));

$eventManager->addEventHandler('main', 'OnBeforeEventAdd', array("COrwoBitrix24", "OnFormAddB24"));

//добавляем пересчет скидок, убираем в корзине скидки которые без купонов и не связаны с купонами
$eventManager->addEventHandler('catalog', 'OnGetDiscountResult', array("\E_1\Prices", "OnGetUserDiscount"));


function pageH1(){
	global $APPLICATION;
	$pageH1 = '';
	if($APPLICATION->GetProperty("HIDEH1") !== 'Y'){
		$pageH1 = '<h1 id="pagetitle">' . $APPLICATION->GetTitle(false) . '</h1>';
	}
	return $pageH1;
}

utmSetCookies();
function utmSetCookies() {
    if($_GET['utm_source']) {
        setcookie("UTM_SOURCE_COOKIE", $_GET['utm_source'], time()+86400*14, '/');
    }
    if($_GET['utm_medium']) {
        setcookie("UTM_MEDIUM_COOKIE", $_GET['utm_medium'], time()+86400*14, '/');
    }
    if($_GET['utm_campaign']) {
        setcookie("UTM_CAMPAIGN_COOKIE", $_GET['utm_campaign'], time()+86400*14, '/');
    }
    if($_GET['utm_content']) {
        setcookie("UTM_CONTENT_COOKIE", $_GET['utm_content'], time()+86400*14, '/');
    }
    if($_GET['utm_term']) {
        setcookie("UTM_TERM_COOKIE", $_GET['utm_term'], time()+86400*14, '/');
    }
    $sCodeUTMBanners = (\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_UTM_BANNERY_HOME_CODE', 'utm_banner'));
    if (!empty($sCodeUTMBanners) && !empty($_GET[$sCodeUTMBanners])) {
        setcookie("E1_SS_UTM_BANNERY_HOME_CODE", $_GET[$sCodeUTMBanners], time()+86400*14, '/');
    }
}
function getPageUrl() {
	global $APPLICATION;
	$oid = '';
	if($_GET['oid']) {
		$oid = '?oid='.$_GET['oid'];
	}
    $pageUrl = $APPLICATION->GetCurPage().$oid;
    setcookie("PAGE_URL", $pageUrl, time()+86400*14, '/');
}

// генератор кодов для акции марафон удачи
include_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/codegen.php");

if (!function_exists('custom_mail') && COption::GetOptionString("webprostor.smtp", "USE_MODULE") == "Y")
{
    function custom_mail($to, $subject, $message, $additional_headers='', $additional_parameters='')
    {
        if(CModule::IncludeModule("webprostor.smtp"))
        {
            $smtp = new CWebprostorSmtp("s1");
            if (strpos($to, 'buyer') === false && strpos($to, 'reklama@e-1.ru') === false) {
                $result = $smtp->SendMail($to, $subject, $message, $additional_headers, $additional_parameters);
            }else {
                $result = false; 
            }

            if($result)
                return true;
            else
                return false;
        }
    }
}
?>
