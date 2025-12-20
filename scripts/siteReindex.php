<?php
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(__FILE__)) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
set_time_limit(0);

while (ob_get_level()) {
    ob_end_flush();
}

if (CModule::IncludeModule("search")) {
    echo "\e[1;32m[" . date("d.m.Y H:i:s") . " Начало переиндексации сайта" . " \e[0m\n";

    $time_start = time();
    $progress = array();
    $max_execution_time = 10000;

    while (is_array($progress)) {
        $progress = CSearch::ReIndexAll(true, $max_execution_time, $progress);
    }

    $total_time = time() - $time_start;

    echo 'reindex finished. total time: ' . $total_time . ' seconds, indexed elements: ' . $progress . "\r\n";
    echo "\e[1;32m[" . date("d.m.Y H:i:s") . " Конец переиндексации сайта" . " \e[0m\n";
}
