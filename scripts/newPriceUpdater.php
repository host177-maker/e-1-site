<?php
set_time_limit(0);
ignore_user_abort(true);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
ini_set('memory_limit', '4096M');

use \Absteam\PriceUpdater;

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(__FILE__)) . "/public";
$strPathDir               = dirname(dirname(__FILE__)) . "/scripts";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");

PriceUpdater::Update(); // добавить любую строчку в параметр, для пересчета всего каталога
