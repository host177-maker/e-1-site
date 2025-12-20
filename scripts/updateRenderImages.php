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

use \Absteam\CatalogImgUpdater;

global $APPLICATION;
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");

$sourceDir      = '/';
$destinationDir = $_SERVER["DOCUMENT_ROOT"] . "/upload/upload_img_offers";

$ftp_host     = \Cosmos\Config::getInstance()->getParam('COMMON')['ftp_host'];
$ftp_user     = \Cosmos\Config::getInstance()->getParam('COMMON')['ftp_user'];
$ftp_password = \Cosmos\Config::getInstance()->getParam('COMMON')['ftp_password'];
$imgUpdater   = new CatalogImgUpdater($ftp_host, $ftp_user, $ftp_password);

try {
    $imgUpdater->updateCatalogImages($destinationDir);
    $imgUpdater->finish($destinationDir);
    echo "\e[1;32m Скрипт завершен \e[0m\n";
    exit;
} catch (\Throwable $ex) {
    file_put_contents(
        $_SERVER['DOCUMENT_ROOT'] . "/../logs/CatalogImgUpdater_" . date('d-m-Y') . ".log",
        "\n\n" . print_r([date('d-m-Y H:m'), $ex->getMessage()], true) . "\n",
        FILE_APPEND
    );
}

echo "\e[1;32m Скрипт завершен без загрузки картинок \e[0m\n";