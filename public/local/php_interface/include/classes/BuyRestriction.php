<?php

use Bitrix\Main\EventResult;
use Bitrix\Sale\ResultError;

class BuyRestriction
{
    public const RESTRICTION_SECTION_PROPERTY = "UF_RESTRICTION";
    public const RESTRICTION_CART_PROPERTY = "RESTRICTION";

    /**
     * @param $elementID
     * @param $sectionID
     * @param $iblockID
     * @return false|mixed
     * проверка есть ли ограничения на покупку товара по ID
     */
    public static function isRestrictedGoods($elementID, $sectionID = 0, $iblockID = 0)
    {
        if (!$sectionID) {
            $sectionID = self::getSection($elementID);            
        }

        return self::getSectionRestriction($sectionID, $iblockID);
    }

    /**
     * @param $sectionID
     * @param $iblockID
     * @return false|mixed
     * получение ограничений раздела
     */
    public static function getSectionRestriction($sectionID, $iblockID = 0){
        if(!$iblockID){
            $res = \CIBlock::GetList([], ['CODE' => '1c_catalog'], false);
            if($ar = $res->GetNext()){
                $iblockID = $ar['ID'];
            } else {
                return false;
            }
        }

        $res = \CIBlockSection::GetList([], ['ID' => $sectionID, 'IBLOCK_ID' => $iblockID], false, ['ID', 'IBLOCK_ID', self::RESTRICTION_SECTION_PROPERTY]);
        if($ar = $res->GetNext()){
            if($ar[self::RESTRICTION_SECTION_PROPERTY]){
                return $ar[self::RESTRICTION_SECTION_PROPERTY];
            }
        }
        return false;
    }

    /**
     * @param $sectionID
     * @return array
     * получение списка родительских розделов
     */
    public static function getParentSections($sectionID)
    {
        $result = [];
        $nav = \CIBlockSection::GetNavChain(false, $sectionID, ['ID', 'NAME', 'DEPTH_LEVEL']);
        while ($v = $nav->GetNext()) {
            if ($v['ID']) {
                $result[] = $v['ID'];
            }
        }
        return $result;
    }

    /**
     * @param $elementID
     * @return false|mixed
     * получение раздела товара
     */
    public static function getSection($elementID)
    {
        $res = \CIBlockElement::GetList([], ['ID' => $elementID], false, false, ['ID', 'IBLOCK_SECTION_ID', 'PROPERTY_CML2_LINK']);
        if ($ar = $res->GetNext()) {
            if ($ar['PROPERTY_CML2_LINK_VALUE']) {
                return self::getSection($ar['PROPERTY_CML2_LINK_VALUE']);
            }
            return $ar['IBLOCK_SECTION_ID'];
        }

        return false;
    }

    /**
     * @param \Bitrix\Sale\Basket|null $basket
     * @return bool
     * проверка корзины на возможность покупки
     */
    public static function checkRestrictionInBasket(\Bitrix\Sale\Basket $basket = null){
        if(is_null($basket)){
            $fuser = \Bitrix\Sale\Fuser::getId();
            $basket = \Bitrix\Sale\Basket::loadItemsForFUser($fuser, \Bitrix\Main\Context::getCurrent()->getSite());
        }

        $restricted = false;
        foreach ($basket as $item) {
            $basketPropertyCollection = $item->getPropertyCollection()->getPropertyValues();
            if($basketPropertyCollection[self::RESTRICTION_CART_PROPERTY]['VALUE']){
                $restricted = unserialize($basketPropertyCollection[self::RESTRICTION_CART_PROPERTY]['VALUE'], ['allowed_classes' => false]);
            }
        }

        if($restricted){
            return self::checkRestriction($restricted, $basket);
        }

        return false;
    }

    /**
     * @param $restricted
     * @param \Bitrix\Sale\Basket|null $basket
     * @return bool
     * проверка корзины на ограничения. возвращает true если условия ограничений не выполнены, false - если выполнены и можно пропускать дальше
     */
    public static function checkRestriction($restricted, \Bitrix\Sale\Basket $basket = null){
        if(is_null($basket)){
            $fuser = \Bitrix\Sale\Fuser::getId();
            $basket = \Bitrix\Sale\Basket::loadItemsForFUser($fuser, \Bitrix\Main\Context::getCurrent()->getSite());
        }

        /** @var \Bitrix\Sale\BasketItem $item */
        foreach ($basket as $item) {
            $productID = $item->getProductId();
            $section = self::getSection($productID);
            $parent =  self::getParentSections($section);
            if(array_intersect($restricted, $parent)){
                return false;
            }
        }

        return true;
    }

    /**
     * @param $sectionID
     * @param $iblockID
     * @return bool
     * проверка нужно ли показывать уведомление в разделе
     */
    public static function checkBasketForSection($sectionID, $iblockID = 0){
        $restriction = self::getSectionRestriction($sectionID, $iblockID);
        if($restriction){
            return self::checkRestriction($restriction);
        }

        return true;
    }

    /**
     * @param \Bitrix\Main\Event $event
     * @return void
     * обработчик события сохранения заказа, проверяет возможность оформить заказ
     */
    public static function checkOrderRestriction(\Bitrix\Main\Event $event){
        /** @var \Bitrix\Sale\Order $order */
        $order = $event->getParameter("ENTITY");
        $basket = $order->getBasket();
        if(self::checkRestrictionInBasket($basket)){
            $event->addResult(new EventResult(EventResult::ERROR, ResultError::create(new \Bitrix\Main\Error('Покупка возможна только при условии приобретения шкафа.'))));
        }
    }

}