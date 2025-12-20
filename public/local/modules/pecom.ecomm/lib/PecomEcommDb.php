<?php

namespace Pec\Delivery;

class PecomEcommDb
{
    protected static $tableName = "pecom_ecomm";

    public static function AddNewOrder(int $orderId) {
        if (!$orderId) return;
        global $DB;
        $strSql =
            "INSERT INTO " . self::$tableName . "(ORDER_ID) ".
            "VALUES(". $orderId .")";
        $DB->Query($strSql);
    }

    public static function AddOrderWidgetData1(int $orderId, string $widgetData) {
        global $DB;
        $strSql = "UPDATE " . self::$tableName . " SET WIDGET='" . $widgetData . "' WHERE ORDER_ID=" . $orderId;
        $DB->Query($strSql);
    }

    public static function AddOrderData(int $orderId, string $field, string $data) {
        global $DB;
        $strSql = "UPDATE " . self::$tableName . " SET " . $field . "='" . $data . "' WHERE ORDER_ID=" . $orderId;
        $DB->Query($strSql);
    }

    public static function GetOrderIdsByPecStatus(array $statuses) {
        global $DB;
        $strSql =
        $strSql = "SELECT ORDER_ID FROM " . self::$tableName . " WHERE PEC_STATUS IN (" . implode(',', $statuses) . ")";
        print_r($strSql);die;
        $DB->Query($strSql);
    }

    public static function GetOrderIds($startOrderId = 0) {
        global $DB;
        $strSql =
        $strSql = "SELECT ORDER_ID, PEC_ID, STATUS FROM " . self::$tableName . ' WHERE PEC_ID > "" AND ORDER_ID >= ' . $startOrderId;
        $res = $DB->Query($strSql);
        $result = [];
        while ($arr = $res->Fetch()) {
            $status = unserialize($arr['STATUS'], ['allowed_classes' => false]);
            $result[$arr['ORDER_ID']] = ['pecId' => $arr['PEC_ID'], 'status' => $status['code']];
        }
        return $result;
    }

    public static function GetOrderDataArray(int $orderId, string $field) {
        global $DB;
        $strSql = "SELECT " . $field . " FROM " . self::$tableName . " WHERE ORDER_ID=" . $orderId;
        $res = $DB->Query($strSql);
        if($arr = $res->Fetch())
            return unserialize($arr[$field], ['allowed_classes' => false]);
        else return false;
    }

    public static function GetOrderData(int $orderId, string $field) {
        global $DB;
        $strSql = "SELECT " . $field . " FROM " . self::$tableName . " WHERE ORDER_ID=" . $orderId;
        $res = $DB->Query($strSql);
        if($arr = $res->Fetch())
            return $arr[$field];
        else return false;
    }
}