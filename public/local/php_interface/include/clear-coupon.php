<?php
AddEventHandler("sale", "OnSaleOrderSaved", Array("clearCupon", "OnSaleOrderSavedHandler"));
class clearCupon
{
    static function OnSaleOrderSavedHandler()
    {
        \Bitrix\Main\Loader::includeModule('sale');
        \Bitrix\Sale\DiscountCouponsManager::init();
        \Bitrix\Sale\DiscountCouponsManager::clear(true);
        \Bitrix\Sale\DiscountCouponsManager::clearApply(true);
    }
}