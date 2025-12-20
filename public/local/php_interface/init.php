<?php

define('APPLICATION_PATH', dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR);
include_once APPLICATION_PATH . join(DIRECTORY_SEPARATOR, ['local', 'php_interface', '']) . 'autoload.php';
@include_once 'defines-local.php';
@include_once 'defines.php';

include_once ROOT_PATH . join(DIRECTORY_SEPARATOR, ['vendor', '']) . 'autoload.php';
include 'include/orwo-init.php';
include 'include/paykeeper.php';
include 'include/clear-coupon.php';
include 'include/functions.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

$capsule = new Capsule();
global $DB;

$connection = [
    'driver'    => 'mysql',
    'host'      => $DB->DBHost,
    'database'  => $DB->DBName,
    'username'  => $DB->DBLogin,
    'password'  => $DB->DBPassword,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
];
if (file_exists('/var/lib/mysqld/mysqld.sock')) {
    $connection['unix_socket'] = '/var/lib/mysqld/mysqld.sock';
} elseif (file_exists('/var/run/mysqld/mysqld.sock')) {
    $connection['unix_socket'] = '/var/run/mysqld/mysqld.sock';
}

$capsule->addConnection($connection);
$capsule->setEventDispatcher(new Dispatcher(new Container()));
$capsule->setAsGlobal();
$capsule->bootEloquent();

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler('main', 'OnBeforeEventAdd', ['\E_1\EventHandler\CrmSendForm', 'crmSendDataForm']);
$eventManager->addEventHandler('search', 'BeforeIndex', ['\E_1\EventHandler\Index', 'BeforeIndex']);
$eventManager->addEventHandler('sale', 'OnOrderNewSendEmail', ['\E_1\EventHandler\OnOrderNewSendPayLog', 'OnOrderNewSendEmail']);
$eventManager->addEventHandler('sale', 'OnOrderPaySendEmail', ['\E_1\EventHandler\OnOrderNewSendPayLog', 'OnAfterOrderPaid']);
$eventManager->addEventHandler('sale', 'OnOrderStatusSendEmail', ['\E_1\EventHandler\OnOrderNewSendPayLog', 'OnOrderStatusSendEmail']);
$eventManager->addEventHandler('sale', 'OnSaleComponentOrderJsData', ['\E_1\EventHandler\OnOrderDelivery', 'OnSaleComponentOrderJsDataHandler']);
$eventManager->addEventHandler('sale', 'OnSaleComponentOrderOneStepProcess', ['\E_1\Prices', 'orderAjaxResultModify']);
$eventManager->addEventHandler('sale', 'OnSaleOrderBeforeSaved', ['BuyRestriction', 'checkOrderRestriction']);
$eventManager->addEventHandler('form', 'onBeforeResultAdd', ['\E_1\EventHandler\SendMail', 'sendDataToMail']);
//переделанный метод для обновления цены в корзине без Custom Price
$eventManager->addEventHandler('sale', 'OnSaleBasketItemRefreshData', ['COrwoEvents', 'OnSaleBasketItemRefreshDataNoCustomPriceHandler']);
//перед сохранением теряется цена, дело в событии
$eventManager->addEventHandler('sale', 'OnSaleBasketBeforeSaved', ['COrwoEvents', 'OnSaleBasketBeforeSavedNoCustomPriceHandler']);

$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', ["\E_1\EventHandler\IblockElement", 'ResizeUploadedPhoto']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', ["\E_1\EventHandler\IblockElement", 'ResizeUploadedPhoto']);

$eventManager->AddEventHandler('sale', 'OnSalePayOrder', 'sendOrderToPayKeeper');
$eventManager->addEventHandler('main', 'OnBeforeProlog', ["\E_1\EventHandler\NewUrlRedirect", 'OnBeforePrologHandler']);
//здесь кастом для цен нашел, задает цену (правила работы с корзиной), мало ли пригодится
// $eventManager->addEventHandlerCompatible(
//     "sale",
//     "OnCondSaleActionsControlBuildList",
//     ["\E_1\EventHandler\SaleActionCustomPrice", "GetControlDescr"]
// );

//Кастомный тип свойства для привязки к типу Список
$eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList', ['\E_1\EventHandler\PropertyListLink', 'GetIBlockPropertyDescription']);

$eventManager->addEventHandler('main', 'OnPageStart', ['\Absteam\Handlers', 'OnPageStartHandler']);
$eventManager->addEventHandler('main', 'OnBeforeProlog', ['\Absteam\Handlers', 'BeforePrologHandler']);
$eventManager->addEventHandler('main', 'OnEpilog', ['\Absteam\Handlers', 'EpilogHandler']);
// $eventManager->addEventHandler('main', "OnAfterEpilog", ['\Absteam\Handlers', 'AfterEpilogHandler']);

$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementUpdate', ["\Absteam\Handlers", 'OnBeforeIBlockElementUpdateHandler']);
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementAdd', ["\Absteam\Handlers", 'OnBeforeIBlockElementUpdateHandler']);

$eventManager->addEventHandler(
    'rest',
    'OnRestServiceBuildDescription',
    ['\E_1\RestApi\GetSkuList', 'addCustomRestMethods']
);

$eventManager->addEventHandler('main', 'OnAdminListDisplay', ["\Absteam\Handlers", 'OnAdminListDisplayHandler']);
$eventManager->addEventHandler('main', 'OnBeforeProlog', ["\Absteam\Handlers", 'OnBeforeProlog_OnAdminListDisplayHandler']);
$eventManager->addEventHandler('main', 'OnEndBufferContent', ["\Absteam\Handlers", 'OnEndBufferContentHandler']);

$GLOBALS['timestart'] = microtime(true);

function debugTime()
{
    global $timeStart;
    $time_elapsed_secs = microtime(true) - $GLOBALS['timestart'];
    $GLOBALS['clockwork']->requestProcessed();
    dd('Time: ' . round($time_elapsed_secs * 1000, 4) . " ms, Queries: {$GLOBALS['countQuery']}");
}
