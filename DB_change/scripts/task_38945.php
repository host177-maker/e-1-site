<?php

/**
 * @date 28.07.2023
 * @author Litvishko Aleksey
 * @see #38945
 */

define("NOT_CHECK_PERMISSIONS", true);

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__, 3) . '/public';

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

global $USER;
$USER->Authorize(1);

if (!\Bitrix\Main\Loader::IncludeModule('form')) {
    echo 'Не удалось подключить модуль form' . PHP_EOL;
    die;
}

$CFormField = new CFormField();

$webFormId = 1;

$questions = array(
    array(
        'FORM_ID' => $webFormId,
        'ACTIVE' => 'Y',
        'TITLE' => 'Тема',
        'TITLE_TYPE' => 'text',
        'SID' => 'TOPIC',
        'C_SORT' => 150,
        'ADDITIONAL' => 'N',
        'REQUIRED' => 'Y',
        'IN_RESULTS_TABLE' => 'N',
        'IN_EXCEL_TABLE' => 'Y',
        'FILTER_TITLE' => 'Тема',
        'RESULTS_TABLE_TITLE' => 'Тема',
        'arIMAGE' => array(),
        'arFILTER_ANSWER_TEXT' => array('dropdown'),
        'arANSWER' => array(
            array(
                'MESSAGE' => 'Заказать шкаф',
                'VALUE' => 'TOPIC_ONE',
                'FIELD_TYPE' => 'dropdown',
                'ACTIVE' => 'Y',
                'C_SORT' => 100,
            ),
            array(
                'MESSAGE' => 'Узнать статус заказа',
                'VALUE' => 'TOPIC_TWO',
                'FIELD_TYPE' => 'dropdown',
                'ACTIVE' => 'Y',
                'C_SORT' => 200,
            ),
            array(
                'MESSAGE' => 'Сообщить о браке',
                'VALUE' => 'TOPIC_TREE',
                'FIELD_TYPE' => 'dropdown',
                'ACTIVE' => 'Y',
                'C_SORT' => 300,
            ),
        ),
    ),
);
foreach ($questions as $question) {
    $by = 's_sort';
    $order = 'asc';
    $filler = array('SID' => $question['SID']);
    $result = $CFormField->GetList($question['FORM_ID'], 'ALL', $by, $order, $filler, $isFiltered);
    if ($row = $result->Fetch()) {
        echo 'Вопрос ' . $question['TITLE'] . ' уже существует' . PHP_EOL;
    } else {
        $id = $CFormField->Set($question);
        if ($id > 0) {
            echo 'Добавлен вопрос ' . $question['TITLE'] . PHP_EOL;
        } else {
            global $strError;

            echo 'Не удалось добавить вопрос ' . $question['TITLE'] . PHP_EOL;
            echo 'Ошибка: ' . $strError . PHP_EOL;
        }
    }
}

global $APPLICATION;

$CEventType = new CEventType();
$eventTypes = array(
    array(
        'LID' => 'ru',
        'EVENT_NAME' => 'ASK_QUESTION',
        'NAME' => 'Задать вопрос',
        'DESCRIPTION' => '',
    ),
    array(
        'LID' => 'en',
        'EVENT_NAME' => 'ASK_QUESTION',
        'NAME' => 'Задать вопрос',
        'DESCRIPTION' => '',
    ),
);
foreach ($eventTypes as $fields) {
    $filter = array('TYPE_ID' => $fields['EVENT_NAME'], 'LID' => $fields['LID']);
    $result = CEventType::GetList($filter);
    if ($row = $result->fetch()) {
        echo "\e[1;33m Тип почтового события \"" . $fields["NAME"] . "\" уже существует \e[0m\n";
    } elseif ($CEventType->Add($fields)) {
        echo "\e[1;32m Успешно добавлен тип почтового события \"" . $fields["NAME"] . "\" \e[0m\n";
    } else {
        echo "\e[1;31m Не удалось добавить тип почтового события \"" . $fields["NAME"] . "\" \e[0m\n";
        if ($exception = $APPLICATION->GetException()) {
            echo "\e[1;31m Error: " . $exception->GetString() . " \e[0m\n";
        }
    }
}

$CEventMessage = new CEventMessage();
$eventMessages = array(
    array(
        'EVENT_NAME' => 'ASK_QUESTION',
        'LID' => array('s1'),
        'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
        'EMAIL_TO' => '#EMAIL_TO#',
        'SUBJECT' => 'Вопрос с сайта #SITE_NAME#',
        'MESSAGE' => '#MESSAGE#',
        'BODY_TYPE' => 'html',
        'LANGUAGE_ID' => 'ru',
    ),
);
foreach ($eventMessages as $fields) {
    $by = 'id';
    $order = 'asc';
    $filter = array('TYPE_ID' => $fields['EVENT_NAME'], 'SUBJECT' => $fields['SUBJECT']);
    $result = CEventMessage::GetList($by, $order, $filter);
    if ($row = $result->fetch()) {
        echo "\e[1;33m Почтовый шаблон \"" . $fields["SUBJECT"] . "\" уже существует \e[0m\n";
    } elseif ($CEventMessage->Add($fields)) {
        echo "\e[1;32m Успешно добавлен почтовый шаблон \"" . $fields["SUBJECT"] . "\" \e[0m\n";
    } else {
        echo "\e[1;31m Не удалось добавить почтовый шаблон \"" . $fields["SUBJECT"] . "\" \e[0m\n";
        if ($exception = $APPLICATION->GetException()) {
            echo "\e[1;31m Error: " . $exception->GetString() . " \e[0m\n";
        }
    }
}
