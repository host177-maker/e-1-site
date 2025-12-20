<?
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
define('BX_NO_ACCELERATOR_RESET', true);
define('CHK_EVENT', true);
define('BX_WITH_ON_AFTER_EPILOG', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(0);
@ignore_user_abort(true);

$start = microtime(true);
$intNumber = mt_rand();
CAgent::CheckAgents();
$textAgent = 'Время выполнения checkAgents: '.round(microtime(true) - $start, 4).' сек.';
file_put_contents(
    $_SERVER['DOCUMENT_ROOT'] . "/../logs/SCRIPT_CRON_" . date('d-m-Y') . ".log",
    "\n\n" . date('Y-m-d H:i:s') . "\n " . print_r([time(), date('d-m-Y H:s'), 'Number' => $intNumber, 'TIME_AGENTS' => $textAgent, 'TIME_SCRIPTS' => $textLog, 'TIME_EVENTS' => $textEvents ], true) . "\n\n",
    FILE_APPEND
);
define("BX_CRONTAB_SUPPORT", true);
define("BX_CRONTAB", true);
CEvent::CheckEvents();
$textEvents = 'Время выполнения checkEvents: '.round(microtime(true) - $start, 4).' сек.';
file_put_contents(
    $_SERVER['DOCUMENT_ROOT'] . "/../logs/SCRIPT_CRON_" . date('d-m-Y') . ".log",
    "\n\n" . date('Y-m-d H:i:s') . "\n " . print_r([time(), date('d-m-Y H:s'), 'Number' => $intNumber, 'TIME_AGENTS' => $textAgent, 'TIME_SCRIPTS' => $textLog, 'TIME_EVENTS' => $textEvents ], true) . "\n\n",
    FILE_APPEND
);
if(CModule::IncludeModule('sender'))
{
    \Bitrix\Sender\MailingManager::checkPeriod(false);
    \Bitrix\Sender\MailingManager::checkSend();
}

require($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/tools/backup.php");
CMain::FinalActions();
$textLog = 'Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек.';
file_put_contents(
    $_SERVER['DOCUMENT_ROOT'] . "/../logs/SCRIPT_CRON_" . date('d-m-Y') . ".log",
    "\n\n" . date('Y-m-d H:i:s') . "\n " . print_r([time(), date('d-m-Y H:s'), 'Number' => $intNumber, 'TIME_AGENTS' => $textAgent, 'TIME_SCRIPTS' => $textLog, 'TIME_EVENTS' => $textEvents ], true) . "\n\n",
    FILE_APPEND
);
?>