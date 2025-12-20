<?php
/**
 * @author Shmakov Fedot
 * @date 21.12.2022
 */
use Cosmos\Config;
use \Bitrix\Main\Config\Option;

defined('NO_AGENT_CHECK') || define('NO_AGENT_CHECK', true);
defined('NO_KEEP_STATISTIC') || define('NO_KEEP_STATISTIC', "Y");
defined('NO_AGENT_STATISTIC') || define('NO_AGENT_STATISTIC', "Y");
defined('NOT_CHECK_PERMISSIONS') || define('NOT_CHECK_PERMISSIONS', true);

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__))) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");


$arFields = Array(
    "E1_SS_FOOTER_PHONE" => "8-800-100-12-11",
);
$sResult = Option::set('e1.site.settings', 'E1_SS_MODAL_ORDER_TITLE', 'Уведомление');
$sResult1 = Option::set('e1.site.settings', 'E1_SS_MODAL_ORDER_TEXT', 'Уважаемый посетитель, город доставки отличается от вашего города, и цена доставки разная.
Пожалуйста смените город, чтобы оформить заказ');
$sResult2 = Option::set('e1.site.settings', 'E1_SS_MODAL_ORDER_BUTTON_YES', 'Сменить город');
$sResult3 = Option::set('e1.site.settings', 'E1_SS_MODAL_ORDER_BUTTON_NO', 'Оформить заказ');

echo "\e[1;32m Настройки сайта успешно обновлены \e[0m\n";

