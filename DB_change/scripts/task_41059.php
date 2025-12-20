<?php
/**
 * @author Shmakov Fedot
 * @date 16.11.2023
 * @see 41059
 */

use Cosmos\Config;

defined('NO_AGENT_CHECK') || define('NO_AGENT_CHECK', true);
defined('NO_KEEP_STATISTIC') || define('NO_KEEP_STATISTIC', "Y");
defined('NO_AGENT_STATISTIC') || define('NO_AGENT_STATISTIC', "Y");
defined('NOT_CHECK_PERMISSIONS') || define('NOT_CHECK_PERMISSIONS', true);

$_SERVER["DOCUMENT_ROOT"] = dirname(dirname(dirname(__FILE__))) . "/public";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$aCosmosConfigIblock = Config::getInstance()->getParam("IBLOCK");

if (!CModule::IncludeModule("iblock")) {
    echo "Не удалось подключить модуль iblock\n";
    die;
}
if (!CModule::IncludeModule("form")) {
    echo "Веб-формы не подключены\n";
    die;
}

$iFormId = 5;
// метод CForm::Copy не хочет работать без авторизации админа
// даже если передать второй аргумент "N"
global $USER;
$USER->Authorize(1);

// скопируем веб-форму
if ($iNewFormId = CForm::Copy($iFormId, 'N')) {
    echo "Веб-форма #" . $iFormId . " успешно скопирована в новую веб-форму #" . $iNewFormId . "\n";

    $rQuestions = CFormField::GetList($iNewFormId, "N", $by="s_id", $order="desc", $arFilter, $is_filtered);
    while ($aQuestion = $rQuestions->GetNext()) {
        if (
            $aQuestion['SID'] == 'POST'
        ) {
            CFormField::Delete($aQuestion['ID'], 'N');
            $FIELD_ID = CFormField::Set(array('FORM_ID' => $iNewFormId,
                'ACTIVE' => 'Y' ,
                'TITLE' => 'Город' ,
                'TITLE_TYPE' => 'text',
                'C_SORT' => '2' ,
                'VALUE' => ' ',
                'SID' => 'CITY' ),
                false,
                'Y',
                'N'
            );
        }
        if (
            $aQuestion['SID'] == 'CLIENT_NAME'
        ) {
            CFormField::Delete($aQuestion['ID'], 'N');
            $FIELD_ID = CFormField::Set(array('FORM_ID' => $iNewFormId,
            'REQUIRED' => 'Y',
            'TITLE' => 'ФИО' ,
            'ACTIVE' => 'Y' ,
            'TITLE' => 'ФИО' ,
            'VALUE' => ' ',
            'C_SORT' => '1' ,
            'TITLE_TYPE' => 'text',
            'SID' => 'FIO' ),
                false,
                'Y',
                'N'
            );
        }
    }

    $aTemplates = CForm::SetMailTemplate($iNewFormId, 'Y', '');
    $oEventMessage = new CEventMessage;
    $aFields = array(
        "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
        "EMAIL_TO" => "franch@e-1.ru",
        "SUBJECT" => "Франшиза ",
        "MESSAGE" => "Сообщение из формы  Франшиза:
        ФИО: #FIO#
        E-mail: #EMAIL#
        Город: #CITY#
        Телефон: #PHONE#
        ",
        "BODY_TYPE" => "text"
    );

    $resultUpdate = $oEventMessage->Update($aTemplates[0], $aFields);
    if($resultUpdate) {
        echo "Веб-форма #" . $iNewFormId . " успешно обновлена\n";
        $arFields_i["NAME"] = 'Франшиза';
		$arFields_i["SID"] = 'FRANCHISE';
        $arFields_i["arMENU"] = array("ru" => "Франшиза", "en" => "Franchise");
        $ID = CAllForm::Set($arFields_i, $iNewFormId);
        if($ID) {
            echo "Веб-форма #" . $ID . " успешно обновлена\n";
        }
    }
} else {
    // выведем текст ошибки
    global $strError;
    echo "Ошибка: " . $strError . "\n";
}
