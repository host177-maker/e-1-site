<?php

namespace E_1\EventHandler;

class OnOrderNewSendPayLog
{

    public static function OnOrderNewSendEmail($orderID, &$eventName, &$arFields) {
        $arPaySystem = self::OnGetOrderPaySystemData($orderID);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/upload/newOrder_" . __FUNCTION__ . ".log", "\n\n" . date('Y-m-d H:i:s') . "\n " . print_r([time(),$arPaySystem, $arFields], true) . "\n\n", FILE_APPEND);
        return true;
    }

    public static function OnAfterOrderPaid($orderID, &$eventName, &$arFields){
        $arPaySystem = self::OnGetOrderPaySystemData($orderID);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/upload/payOrder_" . __FUNCTION__ . ".log", "\n\n" . date('Y-m-d H:i:s') . "\n " . print_r([time(),$arPaySystem, $arFields], true) . "\n\n", FILE_APPEND);
        return true;
    }

    public static function OnOrderStatusSendEmail($orderID, &$eventName, &$arFields, $val){
        $arPaySystem = self::OnGetOrderPaySystemData($orderID);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/upload/changeOrder_" . __FUNCTION__ . ".log", "\n\n" . date('Y-m-d H:i:s') . "\n " . print_r([time(),$arPaySystem, $arFields], true) . "\n\n", FILE_APPEND);
        return true;
    }

    public static function OnGetOrderPaySystemData($orderID) {
        $obOrder =  \Bitrix\Sale\Order::load($orderID);
	    $obPaymentCollection = $obOrder->getPaymentCollection();

        foreach ($obPaymentCollection as $payment) {
            $arFields["ORDER_PAY_SUM"] = $payment->getSum(); // сумма к оплате
            $arFields["ORDER_PAY_STATUS"] = ($payment->isPaid()) ? 'оплачена' : 'не оплачена'; // true, если оплачена
            $arFields["ORDER_PAY_RETURN"] = ($payment->isReturn()) ? 'возвращена' : 'не возвращена'; // true, если возвращена
            $arFields["ORDER_PAY_SYSTEM_OBJ"] = $payment->getPaySystem(); // платежная система (объект Sale\PaySystem\Service)
            $arFields["ORDER_PAY_SYSTEM_ID"] = $payment->getPaymentSystemId(); // ID платежной системы
            $arFields["ORDER_PAY_SYSTEM_NAME"] = $payment->getPaymentSystemName(); // название платежной системы
            $arFields["ORDER_PAY_INTERNAL_ACCOUNT"] = $payment->isInner(); // true, если это оплата с внутреннего счета
        }
        return $arFields;
    }

}