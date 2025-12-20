<?php

namespace Aspro\Max\Smartseo\Admin\Traits;

use Aspro\Max\Smartseo;

trait BitrixCoreEntity
{

    public function getSiteList($order = [], $filter = [], $select = [], $group = [], $params = [])
    {
        return \Bitrix\Main\SiteTable::getList(array_filter([
              'select' => $select ?: ['LID', 'SORT', 'DEF', 'ACTIVE', 'NAME', 'SITE_NAME'],
              'order' => $order ?: ['DEF' => 'DESC', 'SORT' => 'DESC', 'NAME' => 'ASC'],
              'filter' => $filter ?: [],
              'group' => $group ?: [],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ],
          ]))->fetchAll();
    }

    public function getSiteRow($order = [], $filter = [], $select = [], $group = [], $params = [])
    {
        return \Bitrix\Main\SiteTable::getRow(array_filter([
              'select' => $select ?: ['LID', 'SORT', 'DEF', 'ACTIVE', 'NAME', 'SITE_NAME'],
              'order' => $order ?: ['DEF' => 'DESC', 'SORT' => 'DESC', 'NAME' => 'ASC'],
              'filter' => $filter ?: [],
              'group' => $group ?: [],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ],
          ]));
    }

    public function getIblockList($order = [], $filter = [], $select = [], $group = [], $params = [])
    {
        return \Bitrix\Iblock\IblockTable::getList(array_filter([
              'select' => $select ?: ['ID', 'CODE', 'NAME', 'ACTIVE', 'SORT'],
              'order' => $order ?: ['SORT' => 'DESC', 'NAME' => 'ASC'],
              'filter' => $filter ?: [],
              'group' => $group ?: [],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ]
          ]))->fetchAll();
    }

    public function getIblockRow($filter = [], $select = [], $params = [])
    {
        return \Bitrix\Iblock\IblockTable::getRow(array_filter([
              'select' => $select ?: ['ID', 'CODE', 'NAME', 'ACTIVE', 'SORT'],
              'filter' => $filter ?: [],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ]
          ]));
    }

    public function getIblockTypeLanguageList($order = [], $filter = [], $select = [], $group = [], $params = [])
    {
        return \Bitrix\Iblock\TypeLanguageTable::getList(array_filter([
              'select' => $select ?: [],
              'order' => $order ?: [],
              'filter' => $filter ?: [],
              'group' => $group ?: [],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ],
          ]))->fetchAll();
    }

    public function getIblockTypeList($order = [], $filter = [], $select = [], $group = [], $params = [])
    {
        return \Bitrix\Iblock\TypeTable::getList(array_filter([
              'select' => ['ID', 'NAME' => 'LANG_MESSAGE.NAME'],
              'order' => $order ?: [],
              'filter' => $filter ?: ['LANG_MESSAGE.LANGUAGE_ID' => 'ru'],
              'group' => $group ?: [],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ],
          ]))->fetchAll();
    }

    public function getIblockSectionList($order = [], $filter = [], $select = [], $group = [], $params = [])
    {
        return \Bitrix\Iblock\SectionTable::getList(array_filter([
              'select' => $select ?: ['ID', 'CODE', 'NAME', 'ACTIVE', 'IBLOCK_ID', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'DEPTH_LEVEL'],
              'order' => $order ?: [],
              'filter' => $filter ?: [],
              'group' => $group ?: [],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ],
              'limit' => $params['limit']
          ]))->fetchAll();
    }

    public function getCatalogIblockList($order = [], $filter = [], $select = [], $group = [], $params = [])
    {
        if(!class_exists('\Bitrix\Catalog\CatalogIblockTable')) {
            return false;
        }

        return \Bitrix\Catalog\CatalogIblockTable::getList(array_filter([
              'select' => $select ?: ['IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID'],
              'order' => $order ?: [],
              'filter' => $filter ?: [],
              'group' => $group ?: [],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ],
          ]))->fetchAll();
    }

    public function getCatalogIblockRow($filter = [], $select = [], $params = [])
    {
        if(!class_exists('\Bitrix\Catalog\CatalogIblockTable')) {
            return false;
        }

        return \Bitrix\Catalog\CatalogIblockTable::getRow(array_filter([
              'select' => $select ?: ['IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID'],
              'order' => $order ?: [],
              'filter' => $filter ?: [],
              'group' => $group ?: [],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ],
          ]));
    }

    public function getIblockSiteList($order = [], $filter = [], $select = [], $group = [], $params = [])
    {
        return \Bitrix\Iblock\IblockSiteTable::getList(array_filter([
              'select' => $select ?: ['IBLOCK_ID', 'SITE_ID'],
              'order' => $order ?: [],
              'filter' => $filter ?: [],
              'group' => $group ?: [],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ],
          ]))->fetchAll();
    }

    public function getIblockPropertyList($order = [], $filter = [], $select = [], $group = [], $params = [])
    {
        return \Bitrix\Iblock\PropertyTable::getList(array_filter([
              'select' => $select ?: ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'ACTIVE', 'PROPERTY_TYPE', 'DEFAULT_VALUE'],
              'order' => $order ?: [],
              'filter' => $filter ?: [],
              'group' => $group ?: [],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ],
          ]))->fetchAll();
    }

    public function getUserFieldList($order = [], $filter = [], $select = [], $group = [], $params = [])
    {
        return \Bitrix\Main\UserFieldTable::getList(array_filter([
              'select' => $select ?: ['ID', 'ENTITY_ID', 'FIELD_NAME', 'USER_TYPE_ID', 'XML_ID', 'LANG_NAME' => 'LANG.EDIT_FORM_LABEL'],
              'order' => $order ?: [],
              'filter' => $filter ?: [],
              'group' => $group ?: [],
              'runtime' => [
                  new \Bitrix\Main\Entity\ReferenceField(
                    'LANG', Smartseo\Models\FieldLangTable::class, \Bitrix\Main\ORM\Query\Join::on('this.ID', 'ref.USER_FIELD_ID')
                      ->where('ref.LANGUAGE_ID', 'ru')
                  )
              ],
              'cache' => $params['cache'] ?: [
                'ttl' => 86400,
              ],
          ]))->fetchAll();
    }

}
