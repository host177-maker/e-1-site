<?php
/**
 * @author Shmakov Fedot
 * @date 26.01.2023
 * @see 35966
 */

use Cosmos\Config;

defined('NO_AGENT_CHECK') || define('NO_AGENT_CHECK', true);
defined('NO_KEEP_STATISTIC') || define('NO_KEEP_STATISTIC', "Y");
defined('NO_AGENT_STATISTIC') || define('NO_AGENT_STATISTIC', "Y");
defined('NOT_CHECK_PERMISSIONS') || define('NOT_CHECK_PERMISSIONS', true);

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__))) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!CModule::IncludeModule("iblock")) {
    echo "Не удалось подключить модуль iblock\n";
    die;
}


$idIBlock = \Cosmos\Config::getInstance()->getIblockIdByCode('1c_catalog');
$ib = new CIBlock;
$arFields = Array(
  "API_CODE" => 'catalog',
  );
  if ($ib->Update($idIBlock, $arFields)) {
    echo "\e[1;32m Успешно обновлено поле инфоблока \"" . $aFields["NAME"] . "\" \e[0m\n";
  } else {
    echo "\e[1;31m Не удалось обновить инфоблок \"" . $aFields["NAME"] . "\" \e[0m\n";
    echo "\e[1;31m Error: " . $ib->LAST_ERROR . " \e[0m\n";
  }