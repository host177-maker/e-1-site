<?php

use Absteam\Form\Result;

class COrwoBitrix24
{
    public static function sendRest1($type, $array)
    {
        $webhookURL = \Cosmos\Config::getInstance()->getParam('e1_crm_bx24')['crm_webhook_url'];
        $queryUrl   = $webhookURL . $type;
        $queryData  = http_build_query($array);
        $curl       = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST           => 1,
            CURLOPT_HEADER         => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => $queryUrl,
            CURLOPT_POSTFIELDS     => $queryData,
        ]);

        $result = curl_exec($curl);
        curl_close($curl);
        $result = json_decode($result, 1);

        // $logInfo = "========================================\n";
        // $logInfo .= "TIME: " . date("Y-m-d H:i:s") . "\n";
        // $logInfo .= "METHOD: POST\n";
        // $logInfo .= "HOOK: " . $queryUrl . "\n";
        // $logInfo .= "QUERY: " . urldecode(str_replace('+', '%20', $queryData)) . "\n";
        // $logInfo .= "JSON: " . json_encode($array, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        // $logInfo .= "========================================\n";
        // $file = file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/b24_1.log', $logInfo, FILE_APPEND);

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/.logs/requestParams.log', print_r($array, true), FILE_APPEND);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/.logs/response.log', print_r($result, true),FILE_APPEND);

        return $result['result'];
    }

    public static function OnFormAddB24($event, $lid, array $arFields)
    {
        $form = 'Неопределенная форма';
        $name = $phone = $email = $city = $comment = $comment_b24 = null;
        $page = '/';
        if (isset($_COOKIE['current_region'])) {
            if (\Bitrix\Main\Loader::includeModule('iblock')) {
                $res = CIBlockElement::getList([], ['IBLOCK_ID' => 5, 'ID' => $_COOKIE['current_region']], false, false, ['NAME']);
                if ($ar = $res->Fetch()) {
                    $city = $ar['NAME'];
                }
            }
        }
        if ($event === 'SALE_STATUS_CHANGED_N' && isset($arFields['ORDER_ID']) && !empty($arFields['ORDER_ID'])) {
            $event = 'NEW_ORDER';
            // определяем ID заказа
            $OrderId = $arFields['ORDER_ID'];
            if ($OrderId > 0) :
                // получаем данные о заказе
                \Bitrix\Main\Loader::includeModule('sale');
                $OrderDetail = \Bitrix\Sale\Order::load($OrderId);
                // извлекаем параметр уведомления у пользователя, который сделал заказ
                $OrderByUser        = $OrderDetail->getUserId();
                $propertyCollection = $OrderDetail->getPropertyCollection();
                $paymentCollection = $OrderDetail->getPaymentCollection();
                $shipmentmentCollection = $OrderDetail->getShipmentCollection();

                foreach ($shipmentmentCollection as $shipment) {
                    if ($shipment->isSystem())
                        continue;

                    //Получение доп. услуги - подъем на этаж и т.д.
                    // $shipment_id = $shipment->getId();
                    // $stores = \Bitrix\Sale\Delivery\ExtraServices\Manager::getExtraServicesList($shipment_id);

                    // $extra = $shipment->getExtraServices(); //массив значений доп.услуги, типа array([10]=>'Y')
                    //     if(!empty($extra)){
                    //         foreach($extra as $key => $value){
                    //             if($value == 'Y')
                    //                 $arFields['EXTRA_SERVICE'][] = $stores[$key]['NAME']; // получаем наименование доп. услуги
                    //         }
                    //     }

                    $arFields['DELIVERY'] = $shipment->getField('DELIVERY_NAME');
                    
                }
                foreach ($paymentCollection as $property) {
                    $arFields['PAY'] = $property->getPaySystem()->getField('NAME');
                }

                foreach ($propertyCollection as $property) {
                    $arResult['JS_DATA']['ORDER_PROP_LIST'][$property->getField('CODE')]['VALUE'][0] = $property->getField('VALUE');
                    //$arr[$property->getField('CODE')][] = $property->getField('VALUE');
                    //$arFields['ORDER_FIO']  = $propertyCollection->getPayerName()->getField('VALUE');
                }
                $user = \Bitrix\Main\UserTable::getRowById($OrderByUser);
                $arFields['ORDER_FIO'] = $user['LAST_NAME'].' '.$user['NAME'].' '.$user['SECOND_NAME'];
                $arFields['DELIVERY']  .= ($arResult['JS_DATA']['ORDER_PROP_LIST']['PUNKT_SAM']['VALUE'][0]) ? ': '.  $arResult['JS_DATA']['ORDER_PROP_LIST']['PUNKT_SAM']['VALUE'][0] : '';
                $arFields['ADDRESS']    = ($arResult['JS_DATA']['ORDER_PROP_LIST']['ADDRESS']['VALUE'][0]) ? 'Адрес: ' . $arResult['JS_DATA']['ORDER_PROP_LIST']['ADDRESS']['VALUE'][0] : '';
                if($arResult['JS_DATA']['ORDER_PROP_LIST']['LIFTING']['VALUE'][0] && $arResult['JS_DATA']['ORDER_PROP_LIST']['LIFTING']['VALUE'][0] == 'Подъём на этаж' ){
                    $arResult['JS_DATA']['ORDER_PROP_LIST']['LIFTING']['VALUE'][0] = $arResult['JS_DATA']['ORDER_PROP_LIST']['LIFTING']['VALUE'][0] . ' - ' . $arResult['JS_DATA']['ORDER_PROP_LIST']['OWN_DELIVERY_INFO']['VALUE'][0];
                }
                $arFields['LIFTING']    = ($arResult['JS_DATA']['ORDER_PROP_LIST']['LIFTING']['VALUE'][0]) ? $arResult['JS_DATA']['ORDER_PROP_LIST']['LIFTING']['VALUE'][0] : '';
            endif;
            $arFields['PHONE']       = $arResult['JS_DATA']['ORDER_PROP_LIST']['PHONE']['VALUE'][0];
            $arFields['EMAIL_BUYER'] = $arResult['JS_DATA']['ORDER_PROP_LIST']['EMAIL']['VALUE'][0];

            $arrFbItems       = '';
            $productPriceSumm = 0;
            //Заберем данные заказа
            $dbBasketItems = \CSaleBasket::GetList(
                ['NAME' => 'ASC', 'ID' => 'ASC'],
                ['LID' => SITE_ID, 'ORDER_ID' => $arFields['ORDER_ID']],
                false,
                false,
                ['PRODUCT_ID', 'NAME', 'PRICE', 'BASE_PRICE', 'QUANTITY']
            );
            while ($arItem = $dbBasketItems->Fetch()) {
                if ($arItem['BASE_PRICE'] != 0) {
                    $mxResult = \CCatalogSku::GetProductInfo(
                        $arItem['PRODUCT_ID']
                    );
                    if (is_array($mxResult)) {
                        $productInfo = [
                            'id'         => $arItem['PRODUCT_ID'],
                            'product_id' => $mxResult['ID'],
                            'quantity'   => $arItem['QUANTITY'],
                            'name'       => $arItem['NAME'],
                            'price'      => $arItem['PRICE'],
                            'full_ptice' => ((float) $arItem['PRICE'] * $arItem['QUANTITY']),
                        ];
                    } else {
                        $productInfo = [
                            'id'         => $arItem['PRODUCT_ID'],
                            'product_id' => $arItem['PRODUCT_ID'],
                            'quantity'   => $arItem['QUANTITY'],
                            'name'       => $arItem['NAME'],
                            'price'      => $arItem['PRICE'],
                            'full_ptice' => ((float) $arItem['PRICE'] * $arItem['QUANTITY']),
                        ];
                    }
                    $productInfoStr   = 'product_id=' . $arItem['PRODUCT_ID'] . ' quantity=' . $arItem['QUANTITY'] . ' name ' . $arItem['NAME'];
                    $productPriceSumm = (int) $productPriceSumm + ((int) $arItem['PRICE'] * (int) $arItem['QUANTITY']);
                    //$productObj = json_encode($productInfo);
                    $arrFbItems .= $productInfoStr;
                }
            }

            $arFields['ORDER_ITEMS'] = $arrFbItems;

            //$basket = \Bitrix\Sale\Basket::loadItemsForOrder($arFields['ORDER_ID']);
            //$arFields['ORDER_PRICE'] = $basket->getPrice();
            $arFields['ORDER_PRICE'] = $OrderDetail->getPrice(); // Сумма заказа
            $arFields['COMMENT']     = $OrderDetail->getField('USER_DESCRIPTION'); //комментарии пользователя
            $arFields['PAGE_URL']    = '/order/';
        }

        // Извлекаем файлы из результатов формы
        $formFiles = '';
        if (!empty($arFields['FILE']) && (int)$arFields['RS_RESULT_ID'] > 0) {
            $formFiles = implode(PHP_EOL, array_map(
                static fn(array $item) => sprintf('%s (%s)', $item['fileName'], $item['url']),
                (new Result($arFields['RS_RESULT_ID']))->getFieldValues()
            ));
        }

        switch ($event) {
            case 'FORM_FILLING_CALLBACK':
                //Заказать звонок
                $sourceId       = 44;
                $form           = 'Заказ звонка';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $topic          = array_key_exists('TOPIC', $arFields) ? substr($arFields['TOPIC'], 0, -18) : 'Null';
                $additional     = ($topic == 'Заказать шкаф') ? 6721 : 6723;

                break;
            case 'FORM_FILLING_CALLBACK_FREE':
                //Бесплатная консультация специалиста
                $sourceId       = 44;
                $form           = 'Бесплатная консультация специалиста';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6711;

                break;

            case 'FEEDBACK_FORM':
                // Форма в меню Поставщикам
                $sourceId       = 44;
                $form           = 'Форма поставщикам';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $comment_b24    = array_key_exists('DEPARTMENT', $arFields) ? "Департамент: {$arFields['DEPARTMENT']}<br>" . PHP_EOL : null;
                $comment_b24    .= array_key_exists('TEXT', $arFields) ? $arFields['TEXT'] . '<br>' : null;
                $additional     = 6657;

                break;
                
            case 'FORM_FILLING_ORDER_CUSTOM_CABINET':
                //Сделать заказ
                $sourceId       = 44;
                $form           = 'Сделать заказ (шкафы на заказ)';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6745;

                break;

            case 'FORM_FILLING_ASK':
                $sourceId       = 44;
                $form           = 'Задать вопрос';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email          = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment        = array_key_exists('QUESTION', $arFields) ? $arFields['QUESTION'] : null;
                $comment_b24    = array_key_exists('QUESTION', $arFields) ? $arFields['QUESTION'] . '<br>' : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $topic          = array_key_exists('TOPIC', $arFields) ? substr($arFields['TOPIC'], 0, -18) : 'Null';
                $additional     = ($topic == 'Консультация по продукту') ? 6713 : 6715;

                break;
            case 'FORM_FILLING_ASK_STAFF':
                $sourceId       = 44;
                $form           = 'Задать вопрос (консультация по продукту)';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email          = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment        = array_key_exists('QUESTION', $arFields) ? $arFields['QUESTION'] : null;
                $comment_b24    = array_key_exists('QUESTION', $arFields) ? $arFields['QUESTION'] . '<br>' : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6717;

                break;

            case 'FORM_FILLING_WARDROBE_ORDER':
                $sourceId = 44;
                $form     = 'Заказ шкафа по своим размерам';
                $name     = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone    = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $comment  = array_key_exists('DIMENSIONS', $arFields) ? "Габариты (ШхВхГ, мм): {$arFields['DIMENSIONS']}" . PHP_EOL : null;
                $comment .= array_key_exists('PAGE_URL', $arFields) ? "Со страницы:{$arFields['PAGE_URL']}" . PHP_EOL : null;
                $comment_b24 = array_key_exists('DIMENSIONS', $arFields) ? "Габариты (ШхВхГ, мм): {$arFields['DIMENSIONS']}<br>" . PHP_EOL : null;
                $comment_b24 .= array_key_exists('PAGE_URL', $arFields) ? "Со страницы:{$arFields['PAGE_URL']}<br>" . PHP_EOL : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6887;

                break;
            case 'FORM_FILLING_WARDROBE_ORDER_NEW':
                $sourceId       = 44;
                $form           = 'Подобрать шкаф';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $comment        = array_key_exists('PAGE_URL', $arFields) ? "Со страницы:{$arFields['PAGE_URL']}" . PHP_EOL : null;
                $comment_b24    = array_key_exists('PAGE_URL', $arFields) ? "Со страницы:{$arFields['PAGE_URL']}<br>" . PHP_EOL : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6737;

                break;
            case 'FORM_FILLING_FEEDBACK_KKAP7':
                $sourceId    = 44;
                $form        = 'Оформить рассрочку';
                $name        = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone       = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email       = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment     = array_key_exists('POST', $arFields) ? $arFields['POST'] : null;
                $comment_b24 = array_key_exists('POST', $arFields) ? $arFields['POST'] . '<br>' : null;
                $page        = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional  = 6747;

                break;

            case 'FORM_FILLING_FREE_DESIGN_PROJECT_ORDER':
                // Заказать бесплатный дизайн-проект
                $sourceId       = 44;
                $form           = 'Заказ дизайн-проекта (гардеробные)';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email          = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment        = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}" : null;
                $comment_b24    = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}<br>" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6719;

                break;
            case 'FORM_FILLING_DRESSING_ROOM_CALCULATE':
                // $sourceId = 29;
                $sourceId    = 805;
                $form        = 'Рассчитать стоимость гардеробной';
                $name        = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone       = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email       = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment     = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}" : null;
                $comment_b24 = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}<br>" : null;
                $page        = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;

                break;
            case 'FORM_FILLING_WANT_DRESSING_ROOM':
                // $sourceId = 31;
                $sourceId       = 44;
                $form           = 'Хочу заказать гардеробную';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email          = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment        = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}" : null;
                $comment_b24    = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}<br>" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6749;

                break;
            case 'FORM_FILLING_WANT_SLIDING_SYSTEM':
                $sourceId       = 44;
                $form           = 'Заказать раздвижную систему (гардеробные)';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email          = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment        = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}" : null;
                $comment_b24    = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}<br>" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6725;

                break;
            case 'FORM_FILLING_WHICH_DRESSING_ROOM_NEED':
                // $sourceId = 30;
                $sourceId = 44;
                $form     = 'Бесплатная консультация специалиста (гардеробные)';
                $name     = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone    = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email    = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment  = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}" : null;
                $comment .= array_key_exists('FILE', $arFields) ? "Файлы: $formFiles" : null;
                $comment_b24 = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}<br>" : null;
                $comment_b24 .= array_key_exists('FILE', $arFields) ? "Файлы: $formFiles<br>" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6727;

                break;
            case 'FORM_FILLING_FREE_DESIGN_PROJECT_ORDER_2':
                $sourceId       = 34;
                $form           = 'Заказ дизайн-проекта';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email          = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment        = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}" : null;
                $comment_b24    = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}<br>" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6719;

                break;
            case 'FORM_FILLING_BUILT_IN_WARDROBE_CALCULATE':
                $sourceId       = 32;
                $form           = 'Рассчитать стоимость встроенного шкафа';
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email          = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment        = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}" : null;
                $comment_b24    = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}<br>" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6871;

                break;
            case 'FORM_FILLING_WANT_BUILT_IN_WARDROBE':
                $sourceId       = 35;
                $form           = 'Хочу заказать встроенный шкаф';
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email          = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment        = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}" : null;
                $comment_b24    = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}<br>" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6877;

                break;

            case 'FORM_FILLING_FEEDBACK':
                $sourceId       = 44;
                $form           = 'Связь с директором';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email          = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment        = array_key_exists('POST', $arFields) ? $arFields['POST'] : null;
                $comment_b24    = array_key_exists('POST', $arFields) ? $arFields['POST'] . '<br>' : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6743;

                break;

            case 'FORM_FILLING_GIVE_FEEDBACK':
                $sourceId    = 44;
                $form        = 'Оставить отзыв';
                $name        = array_key_exists('NAME', $arFields) ? $arFields['NAME'] : null;
                $phone       = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email       = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment     = array_key_exists('AGREEMENT', $arFields) ? "№ Заказа: {$arFields['AGREEMENT']}<br>" : null;
                $comment .= array_key_exists('RATING', $arFields) ? "Оценка: {$arFields['RATING']}<br>" : null;
                $comment .= array_key_exists('REVIEW_TEXT', $arFields) ? "Текст отзыва: {$arFields['REVIEW_TEXT']}<br>" : null;
                $comment .= array_key_exists('FILE', $arFields) ? "Файлы: $formFiles" : null;
                $comment_b24  = array_key_exists('AGREEMENT', $arFields) ? "№ Заказа: {$arFields['AGREEMENT']}<br>" : null;
                $comment_b24 .= array_key_exists('RATING', $arFields) ? "Оценка: {$arFields['RATING']}<br>" : null;
                $comment_b24 .= array_key_exists('REVIEW_TEXT', $arFields) ? "Текст отзыва: {$arFields['REVIEW_TEXT']}<br>" : null;
                $comment_b24 .= array_key_exists('FILE', $arFields) ? "Файлы: $formFiles" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6733;

                break;

            case 'FORM_FILLING_REVIEW':
                $sourceId    = 44;
                $form        = 'Оставить отзыв';
                $name        = array_key_exists('NAME', $arFields) ? $arFields['NAME'] : null;
                $phone       = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email       = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment .= array_key_exists('RATING', $arFields) ? "Оценка: {$arFields['RATING']}<br>" : null;
                $comment .= array_key_exists('REVIEW_TEXT', $arFields) ? "Текст отзыва: {$arFields['REVIEW_TEXT']}<br>" : null;
                $comment .= array_key_exists('FILE', $arFields) ? "Файлы: $formFiles" : null;
                $comment_b24  = array_key_exists('RATING', $arFields) ? "Оценка: {$arFields['RATING']}<br>" : null;
                $comment_b24 .= array_key_exists('REVIEW_TEXT', $arFields) ? "Текст отзыва: {$arFields['REVIEW_TEXT']}<br>" : null;
                $comment_b24 .= array_key_exists('FILE', $arFields) ? "Файлы: $formFiles" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6733;

                break;

            case 'FORM_FILLING_FEEDBACK_VACANCY':
                $sourceId    = 44;
                $form        = 'Оставить заявку';
                $name        = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone       = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $comment     = array_key_exists('TARGET_VACANCY', $arFields) ? "Желаемая должность: {$arFields['TARGET_VACANCY']}<br>" : null;
                $comment .= array_key_exists('RESUME_FILE', $arFields) ? "Резюме: {$arFields['RESUME_FILE']}" : null;
                $comment .= array_key_exists('CITY_FORM', $arFields) ? "Город: {$arFields['CITY_FORM']}<br>" : null;
                $comment_b24    = array_key_exists('TARGET_VACANCY', $arFields) ? "Желаемая должность: {$arFields['TARGET_VACANCY']}<br>" : null;
                $comment_b24 .= array_key_exists('RESUME_FILE', $arFields) ? "Резюме: {$arFields['RESUME_FILE']}" : null;
                $comment_b24 .= array_key_exists('CITY_FORM', $arFields) ? "Город: {$arFields['CITY_FORM']}<br>" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6731;

                break;

            case 'FORM_FILLING_FRANCHISE':
                $sourceId       = 44;
                $form           = 'Франшиза';
                $name           = array_key_exists('FIO', $arFields) ? $arFields['FIO'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email          = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment_b24    = array_key_exists('CITY', $arFields) ? $arFields['CITY'] . '<br>' : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6735;

                break;

            case 'FORM_FILLING_SELECTION_WARDROBE':
                $sourceId       = 44;
                $form           = 'Заказать подбор';
                $name           = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone          = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email          = array_key_exists('CLIENT_EMAIL', $arFields) ? $arFields['CLIENT_EMAIL'] : null;
                $comment        = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}<br>" : null;
                $comment_b24    = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}<br>" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6883;

                break;

            case 'NEW_ONE_CLICK_BUY':
                // $sourceId = 12;
                $sourceId = 44;
                $form     = 'Купить в 1 клик';
                $name     = array_key_exists('CLIENT_NAME', $arFields) ? $arFields['CLIENT_NAME'] : null;
                $phone    = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email    = array_key_exists('EMAIL_BUYER', $arFields) ? $arFields['EMAIL_BUYER'] : null;
                $comment  = array_key_exists('ORDER_ITEMS', $arFields) ? $arFields['ORDER_ITEMS'] : null;
                $comment .= array_key_exists('ORDER_PRICE', $arFields) ? "Сумма: {$arFields['ORDER_PRICE']}" : null;
                $comment .= array_key_exists('COMMENT', $arFields) ? "Комментарий: {$arFields['COMMENT']}" : null;
                $comment_b24 = array_key_exists('ORDER_ITEMS', $arFields) ? $arFields['ORDER_ITEMS'] . '<br>' : 'product_id=1 quantity=1 ';
                $comment_b24 .= array_key_exists('ORDER_PRICE', $arFields) ? "Сумма: {$arFields['ORDER_PRICE']}<br>" : null;
                $comment_b24 .= array_key_exists('COMMENT', $arFields) ? "Комментарий: {$arFields['COMMENT']}<br>" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6729;

                break;
            case 'FORM_FILLING_WHICH_BUILT_IN_WARDROBE_NEED':
                $sourceId = 33;
                $form     = 'Знаете какой встроенный шкаф вам нужен?';
                $phone    = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email    = array_key_exists('EMAIL_RAW', $arFields) ? $arFields['EMAIL_RAW'] : null;
                $comment  = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}" : null;
                $comment .= array_key_exists('FILE', $arFields) ? "Файлы: $formFiles" : null;
                $comment_b24 = array_key_exists('PAGE_URL', $arFields) ? "Адрес страницы: {$arFields['PAGE_URL']}<br>" : null;
                $comment_b24 .= array_key_exists('FILE', $arFields) ? "Файлы: $formFiles<br>" : null;
                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $additional     = 6885;

                break;
            case 'NEW_ORDER':
                $sourceId = 44;
                $form     = 'Оформление заказа через корзину';
                $name     = array_key_exists('ORDER_FIO', $arFields) ? $arFields['ORDER_FIO'] : null;
                $phone    = array_key_exists('PHONE', $arFields) ? $arFields['PHONE'] : null;
                $email    = array_key_exists('EMAIL_BUYER', $arFields) ? $arFields['EMAIL_BUYER'] : null;
                $comment  = array_key_exists('ORDER_ITEMS', $arFields) ? $arFields['ORDER_ITEMS'] : null;
                $comment .= array_key_exists('ORDER_PRICE', $arFields) ? "Сумма: {$arFields['ORDER_PRICE']}" : null;
                $comment .= array_key_exists('COMMENT', $arFields) ? "Комментарий: {$arFields['COMMENT']}" : null;
                $comment_b24 = array_key_exists('ORDER_ITEMS', $arFields) ? $arFields['ORDER_ITEMS'] . '<br>' : null;
                $comment_b24 .= array_key_exists('ORDER_PRICE', $arFields) ? "Сумма: {$arFields['ORDER_PRICE']}<br>" : null;
                $comment_b24 .= array_key_exists('COMMENT', $arFields) ? "Комментарий: {$arFields['COMMENT']}<br>" : null;
                $comment_b24 .= array_key_exists('ADDRESS', $arFields) ? $arFields['ADDRESS'].'<br>' : null;
                $comment_b24 .= array_key_exists('PAY', $arFields) ? "Форма оплаты: {$arFields['PAY']}<br>" : null;
                $comment_b24 .= array_key_exists('DELIVERY', $arFields) ? "Доставка: {$arFields['DELIVERY']}<br>" : null;
                $comment_b24 .= array_key_exists('LIFTING', $arFields) ? "Способ подъёма: {$arFields['LIFTING']}<br>" : null;

                $page           = array_key_exists('PAGE_URL', $arFields) ? $arFields['PAGE_URL'] : null;
                $sumOrder       = array_key_exists('ORDER_PRICE', $arFields) ? (int) $arFields['ORDER_PRICE'] : null;
                $additional     = 6739;

                break;
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/.logs/b24.log', print_r($comment_b24, true), FILE_APPEND);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/.logs/b24_fields.log', print_r($arFields, true), FILE_APPEND);

        if ($sourceId) {
            $arrayDeal = [
                'fields' => [
                    'TITLE'              => "Форма на сайте '{$form}'",
                    'NAME'               => $name,
                    'SOURCE_ID'          => $sourceId,
                    'SOURCE_DESCRIPTION' => 'Адрес страницы: ' . $page,
                    'STATUS_ID'          => 'NEW',
                    'CONTACT_ID'         => isset($contactId) ? $contactId : null,
                    'COMMENTS'           => $comment_b24,
                    'CURRENCY_ID'        => 'RUB',
                    'OPPORTUNITY'        => (isset($sumOrder) && $sumOrder > 0) ? $sumOrder : 0,
                    'PHONE'              => [
                        'n0' => [
                            'VALUE'      => $phone,
                            'VALUE_TYPE' => 'WORK',
                        ]
                    ],
                    'EMAIL'              => [
                        'n0' => [
                            'VALUE'      => $email,
                            'VALUE_TYPE' => 'WORK',
                        ]
                    ],
                    'UF_CRM_1692872695'  => isset($_COOKIE['E1_SS_UTM_BANNERY_HOME_CODE']) ? $_COOKIE['E1_SS_UTM_BANNERY_HOME_CODE'] : '',
                    'UF_CRM_1610615481'  => isset($contactId) ? $contactId : null,
                    'UF_CRM_1690176368'  => $topic,
                    'UF_CRM_1613465695'  => $city,
                    'UF_CRM_1607007809'  => isset($_COOKIE['roistat_visit']) ? $_COOKIE['roistat_visit'] : null,
                    'UF_CRM_1750683577'  => $additional,
                    'UTM_SOURCE'         => isset($_COOKIE['UTM_SOURCE_COOKIE']) ? $_COOKIE['UTM_SOURCE_COOKIE'] : null,
                    'UTM_MEDIUM'         => isset($_COOKIE['UTM_MEDIUM_COOKIE']) ? $_COOKIE['UTM_MEDIUM_COOKIE'] : null,
                    'UTM_CAMPAIGN'       => isset($_COOKIE['UTM_CAMPAIGN_COOKIE']) ? $_COOKIE['UTM_CAMPAIGN_COOKIE'] : null,
                    'UTM_CONTENT'        => isset($_COOKIE['UTM_CONTENT_COOKIE']) ? $_COOKIE['UTM_CONTENT_COOKIE'] : null,
                    'UTM_TERM'           => isset($_COOKIE['UTM_TERM_COOKIE']) ? $_COOKIE['UTM_TERM_COOKIE'] : null
                ]
            ];
        }

        if ($arrayDeal) {
            //file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/b24.log', print_r($arrayDeal, true), FILE_APPEND);
            self::sendRest1('crm.lead.add', $arrayDeal);
            // self::sendRest1('crm.deal.add', $arrayDeal);
        }

        if (mb_strlen($phone) || mb_strlen($email)) {
            $roistatData = [
                'roistat'           => isset($_COOKIE['roistat_visit']) ? $_COOKIE['roistat_visit'] : null,
                'key'               => 'ZjlhNzRiZDBjZTQxYzNjOTYyNTgzYjY2MWUwZmJjMTc6MTc3MDgz',
                'title'             => "Форма на сайте '{$form}'",
                'comment'           => $comment,
                'name'              => $name,
                'phone'             => $phone,
                'email'             => $email,
                'is_skip_sending'   => '1',
                'UF_CRM_1610615481' => isset($contactId) ? $contactId : null,
                'fields'            => [
                    'SOURCE_DESCRIPTION'   => 'Адрес страницы: ' . $page,
                    'UF_CRM_1610615481'    => isset($contactId) ? $contactId : null,
                    'UF_CRM_1689155778234' => $topic,
                    'UF_CRM_1613465695'    => $city,
                    'UF_CRM_1750683577'    => $additional,
                    'UTM_SOURCE'           => '{utmSource}',
                    'UTM_MEDIUM'           => '{utmMedium}',
                    'UTM_CAMPAIGN'         => '{utmCampaign}',
                    'UTM_TERM'             => '{utmTerm}',
                    'UTM_CONTENT'          => '{utmContent}'
                ]
            ];
            file_get_contents('https://cloud.roistat.com/api/proxy/1.0/leads/add?' . http_build_query($roistatData));
        }
    }
}
