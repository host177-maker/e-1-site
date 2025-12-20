<?php
set_time_limit(0);
ignore_user_abort(true);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
ini_set('memory_limit', '2048M');

use Bitrix\Sale\Location;

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(__FILE__)) . "/public";;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$cityName = "Ростов-на-Дону";

$parameters = array();
$parameters['filter']['=NAME.NAME'] = $cityName;
$parameters['limit'] = 1;
$parameters['select'] = array('CODE');

$locationRes = Location\LocationTable::getList($parameters)->fetch();

var_dump($locationRes);
