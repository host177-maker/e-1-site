<?php
/**
 * @author Baljinimaev Artem
 * @date 22.07.2023
 * @see 38790
 */
use Cosmos\Config;
use \Bitrix\Main\Config\Option;

defined('NO_AGENT_CHECK') || define('NO_AGENT_CHECK', true);
defined('NO_KEEP_STATISTIC') || define('NO_KEEP_STATISTIC', "Y");
defined('NO_AGENT_STATISTIC') || define('NO_AGENT_STATISTIC', "Y");
defined('NOT_CHECK_PERMISSIONS') || define('NOT_CHECK_PERMISSIONS', true);

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__))) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$sResult = Option::set('e1.site.settings', 'E1_SS_MODAL_WARNING_TITLE', 'На сайте идут технические работы');
$sResult1 = Option::set('e1.site.settings', 'E1_SS_MODAL_WARNING_TEXT', 'Некоторые функции сайта могут работать некорректно.');
$sResult2 = Option::set('e1.site.settings', 'E1_SS_MODAL_WARNING_BUTTON', 'Я понял');

echo "\e[1;32m Настройки сайта успешно обновлены \e[0m\n";
