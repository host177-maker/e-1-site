<?php

namespace E_1\EventHandler;

class SendMail
{
    /**
     * @param $WEB_FORM_ID
     * @param $arFields
     * @param $arrVALUES
     *
     * @return void
     */
    public static function sendDataToMail($WEB_FORM_ID, &$arFields, &$arrVALUES)
    {
        if ($WEB_FORM_ID == 3) {
            /*
             * Заказать шкаф - ID: 130
             * Узнать статус заказа - ID: 131
             * Сообщить о браке - ID: 132
             *
             * form_text_11 - Имя
             * form_dropdown_TOPIC - Тема
             * form_text_12 - номер телефона
             * form_hidden_102 - страница на которой был юзер
             *
             * */

            $messengTo = "С формы \"Заказать звонок\" заявка!  Имя:" . $arrVALUES["form_text_11"] . ". Номер телефона:" . $arrVALUES["form_text_12"] . ". Выбранная тема:"
                         . \Cosmos\Config::getInstance()->getParam(
                    "TOPIC"
                )[$arrVALUES["form_dropdown_TOPIC"]] . ". Страница с которой была отравлена заявка: " . $arrVALUES["form_hidden_102"] . " .";

            if ($arrVALUES["form_dropdown_TOPIC"] == 130) {
                // отправка писем по условию
                \Bitrix\Main\Mail\Event::Send(
                    [
                        "EVENT_NAME" => "ORDER_CALL",
                        "LID" => "s1",
                        "C_FIELDS" => [
                            "DEFAULT_EMAIL_FROM" => \COption::GetOptionString('cosmos.settings', 'topic_email_one'),
                            "MESSAGE" => $messengTo,
                            "USER_ID" => 1,
                        ],
                    ]
                );
            } else {
                if ($arrVALUES["form_dropdown_TOPIC"] == 131 || $arrVALUES["form_dropdown_TOPIC"] == 132) {
                    // отправка писем по условию
                    \Bitrix\Main\Mail\Event::Send(
                        [
                            "EVENT_NAME" => "ORDER_CALL",
                            "LID" => "s1",
                            "C_FIELDS" => [
                                "DEFAULT_EMAIL_FROM" => \COption::GetOptionString('cosmos.settings', 'topic_email_two'),
                                "MESSAGE" => $messengTo,
                                "USER_ID" => 1,
                            ],
                        ]
                    );
                }
            }
        }

        if ($WEB_FORM_ID == 1) {
            $CFormField = new \CFormField();
            $CFormAnswer = new \CFormAnswer();

            $answers = array();
            $by = 's_sort';
            $order = 'asc';
            $filler = array('SID' => 'TOPIC');
            $result = $CFormField->GetList($WEB_FORM_ID, 'ALL', $by, $order, $filler, $isFiltered);
            if ($row = $result->Fetch()) {
                $result = $CFormAnswer->GetList($row['ID'], $by, $order, array(), $isFiltered);
                while ($row = $result->Fetch()) {
                    $answers[$row['VALUE']] = array(
                        'id' => (int)$row['ID'],
                        'value' => $row['VALUE'],
                        'message' => $row['MESSAGE'],
                    );
                }
            }

            $topic = '';
            foreach ($answers as $answer) {
                if ($answer['id'] === (int)$arrVALUES['form_dropdown_TOPIC']) {
                    $topic = $answer['message'];
                }
            }

            $message = 'С формы "Задать вопрос" заявка! <br>';
            $message .= 'Имя: ' . $arrVALUES['form_text_1'] . '<br>';
            $message .= 'Номер телефона: ' . $arrVALUES['form_text_2'] . '<br>';
            $message .= 'Email: ' . $arrVALUES['form_email_3'] . '<br>';
            $message .= 'Выбранная тема: ' . $topic . '<br>';
            $message .= 'Вопрос: ' . $arrVALUES['form_textarea_4'] . '<br>';
            $message .= 'Страница с которой была отравлена заявка: ' . $arrVALUES['form_hidden_100'] . '<br>';

            if ((int)$arrVALUES['form_dropdown_TOPIC'] === $answers['TOPIC_ONE']['id']) {
                $emailTo = \COption::GetOptionString('cosmos.settings', 'ask_topic_email_one');
            }

            if (
                (int)$arrVALUES['form_dropdown_TOPIC'] === $answers['TOPIC_TWO']['id'] ||
                (int)$arrVALUES['form_dropdown_TOPIC'] === $answers['TOPIC_TREE']['id']
            ) {
                $emailTo = \COption::GetOptionString('cosmos.settings', 'ask_topic_email_two');
            }

            if (!empty($emailTo)) {
                \Bitrix\Main\Mail\Event::Send(
                    [
                        'EVENT_NAME' => 'ASK_QUESTION',
                        'LID' => 's1',
                        'C_FIELDS' => [
                            'EMAIL_TO' => $emailTo,
                            'MESSAGE' => $message,
                        ],
                    ]
                );
            }
        }
    }
}
