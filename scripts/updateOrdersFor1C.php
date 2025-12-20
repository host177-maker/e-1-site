<?
set_time_limit(0);
ignore_user_abort(true);

define("ADMIN_SECTION", false);
define("BX_CAT_CRON", true);
define("NO_AGENT_STATISTIC", "Y");
define('NO_AGENT_CHECK', true);
define("DisableEventsCheck", true);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
ini_set('memory_limit', '2048M');
$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(__FILE__)) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Main\Config\Option,
    \Bitrix\Main\Web\Json,
    \Bitrix\Highloadblock\HighloadBlockTable,
    \Bitrix\Main\Context;

use Bitrix\Sale;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main;
use Bitrix\Sale\Order;
use Bitrix\Sale\Cashbox;

global $APPLICATION;
$arOrders = [];
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");
$aCosmosConfigIblock = \Cosmos\Config::getInstance()->getParam('IBLOCK');

$arProp = array("ID", "XML_ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "IBLOCK_SECTION_ID");
$strPathDir = dirname(dirname(__FILE__)) . "/scripts";

// Выведем даты всех заказов текущего пользователя за текущий месяц, отсортированные по дате заказа
$arFilter = Array(
    'PERSON_TYPE_ID' => 2,
);
 $db_sales = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter);
 while ($ar_sales = $db_sales->Fetch())
 {
    $arOrders[] = $ar_sales;
 }

 foreach ($arOrders as $key => $arOrder) {
    $obOrder = Sale\Order::load(intval($arOrder['ID']));
    $propertyCollection = $obOrder->getPropertyCollection();
    $address = $companyAdr = '';
    foreach ($propertyCollection as $property) {
        if ($property->getField('CODE') == "ADDRESS") $address = $property->getValue();
        if ($property->getField('CODE') == "COMPANY_ADR") $companyAdr = $property->getValue();
    }//var_dump($companyAdr, strlen($companyAdr) <= 0);
    if (!empty($address) && mb_strlen($companyAdr) <= 0) {
        $checkAdress = str_replace(',','', $address);
        if (!empty($checkAdress) && $address !== ', ,  ') {
            foreach ($propertyCollection as $property) {
                if ($property->getField('CODE') == "COMPANY_ADR") $property->setValue($address);
            }
            $result = $obOrder->save(); // и сохраняем
            if ($result->isSuccess()) {
                echo "\e[1;32m Успешно обновлен заказ \"" . $arOrder['ID'] . "\" \e[0m\n";
            } else {
                echo implode(', ', $result->getErrorMessages());
            }
        }
    }
 }

echo "\e[1;32m Скрипт завершен \e[0m\n";
