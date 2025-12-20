<?php
use E_1\TablesCleaner;

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
ini_set('memory_limit', '2048M');
$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(__FILE__)) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

TablesCleaner::truncateCaptchaTable();
TablesCleaner::clearViewedProductsTable();
