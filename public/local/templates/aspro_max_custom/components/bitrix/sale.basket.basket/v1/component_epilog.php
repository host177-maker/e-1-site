<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if ($request->get('clear') !== null && Loader::includeModule("sale")) {
    CSaleBasket::DeleteAll(CSaleBasket::GetBasketUserID());

    $redirect = $APPLICATION->GetCurPageParam('', ['clear']);

    LocalRedirect($redirect);
}

?>

<?
if (isset($arResult['ECOM_BASKET_DATA']) && !empty($arResult['ECOM_BASKET_DATA'])) {
    ?>
    <script>
        dataLayer.push({ecommerce: null});
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            event: "view_cart",
            ecommerce: {
                value: <?=json_encode($arResult['ECOM_BASKET_DATA']['sum'])?>,
                currency: <?=json_encode($arResult['ECOM_BASKET_DATA']['currency'])?>,
                items: <?=json_encode($arResult['ECOM_BASKET_DATA']['items'], JSON_UNESCAPED_UNICODE);?>
            }
        });
    </script>
    <?
}
?>
