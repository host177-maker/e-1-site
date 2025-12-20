<? define("STATISTIC_SKIP_ACTIVITY_CHECK", "true"); ?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if($_REQUEST['section']){
    echo json_encode(['show' => \BuyRestriction::checkBasketForSection($_REQUEST['section']) ? 1 : 0]);
}