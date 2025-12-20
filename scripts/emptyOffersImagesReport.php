<?php

const NO_AGENT_CHECK = true;
const DisableEventsCheck = true;
const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
const STOP_STATISTICS = true;
const PERFMON_STOP = true;
const SM_SAFE_MODE = true;

use \Bitrix\Main\Loader;

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__, 2) . '/public';

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

ini_set('display_errors', 1);
ini_set('max_execution_time', 0);

/**
 * @return array
 */
function getOffersToReport(): array
{
    $offers = array();

    $offersIblockId = \Cosmos\Config::getInstance()->getIblockIdByCode('offers');

    $filter = array('IBLOCK_ID' => $offersIblockId);
    $select = array('IBLOCK_ID', 'ID', 'XML_ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE');
    $result = CIBlockElement::GetList(array(), $filter, false, false, $select);
    while ($row = $result->Fetch()) {
        $offer = array(
            'id' => (int)$row['ID'],
            'xmlId' => $row['XML_ID'],
            'isEmptyPreviewPicture' => empty($row['PREVIEW_PICTURE']),
            'isEmptyDetailPicture' => empty($row['DETAIL_PICTURE']),
        );

        if ($offer['isEmptyPreviewPicture'] || $offer['isEmptyDetailPicture']) {
            $offers[] = $offer;
        }
    }

    return $offers;
}

/**
 * @param string $path
 *
 * @return bool
 */
function createDir(string $path): bool
{
    if (file_exists($path)) {
        return true;
    }

    return mkdir($path, 0777, true);
}

$path = $_SERVER['DOCUMENT_ROOT'] . '/upload/reports/emptyOffersImages/';

$fileName = date('d.m.Y-H:i:s') . '.csv';

$headers = array('ID', 'Внешний код', 'Изображение анонса', 'Детальное изображения');

if (!createDir($path)) {
    echo 'Не удалось создать директорию для отчетов ' . $path . PHP_EOL;
    exit;
}

if (!Loader::includeModule('iblock')) {
    echo 'Не удалось подключить модуль iblock' . PHP_EOL;
    exit;
}

$offers = getOffersToReport();

if (empty($offers)) {
    echo 'Не найдено торговых предложений без изображений' . PHP_EOL;
    exit;
}

$fp = fopen($path . $fileName, 'w');

fputcsv($fp, $headers);

foreach ($offers as $offer) {
    fputcsv(
        $fp,
        array(
            $offer['id'],
            $offer['xmlId'],
            $offer['isEmptyPreviewPicture'] ? 'Нет' : '',
            $offer['isEmptyDetailPicture'] ? 'Нет' : '',
        ),
    );
}

fclose($fp);

echo 'Отчет ' . $path . $fileName . ' создан' . PHP_EOL;
exit;
