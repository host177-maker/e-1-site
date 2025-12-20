<?php

namespace Aspro\Max\Smartseo\Models;

use
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Aspro\Max\Smartseo\Entity as SmartseoEntity,
    Bitrix\Main,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoFilterConditionUrlTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FILTER_CONDITION_ID int mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> NAME string(255) optional
 * <li> REAL_URL string mandatory
 * <li> NEW_URL string mandatory
 * <li> IBLOCK_ID int mandatory
 * <li> SECTION_ID int mandatory
 * <li> PROPERTIES string mandatory
 * <li> ELEMENT_COUNT int optional
 * <li> STATE_MODIFIED bool optional default 'N'
 * <li> STATE_DELETED bool optional default 'N'
 * <li> HASH string(32) optional
 * </ul>
 *
 * @package Bitrix\Aspro
 * */
class SmartseoFilterConditionUrlTable extends Main\Entity\DataManager
{
    const DEFAULT_CACHE_TTL = 0;

    public static function getObjectClass()
    {
        return SmartseoEntity\FilterConditionUrl::class;
    }

    public static function getCollectionClass()
    {
        return SmartseoEntity\FilterConditionUrls::class;
    }

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_aspro_max_smartseo_filter_url';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('SMARTSEO_URL_ENTITY_ID_FIELD'),
            ),
            'FILTER_CONDITION_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_URL_ENTITY_FILTER_CONDITION_ID_FIELD'),
            ),
            'ACTIVE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_URL_ENTITY_ACTIVE_FIELD'),
                'default_value' => 'Y',
            ),
            'NAME' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateName'),
                'title' => Loc::getMessage('SMARTSEO_URL_ENTITY_NAME_FIELD'),
            ),
            'REAL_URL' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_URL_ENTITY_REAL_URL_FIELD'),
            ),
            'NEW_URL' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_URL_ENTITY_NEW_URL_FIELD'),
            ),
            'IBLOCK_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SMARTSEO_FILTER_URL_ENTITY_IBLOCK_ID_FIELD'),
			),
            'SECTION_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_URL_ENTITY_SECTION_ID_FIELD'),
            ),
            'PROPERTIES' => array(
                'data_type' => 'text',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_URL_ENTITY_PROPERTIES_FIELD'),
            ),
            'ELEMENT_COUNT' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_URL_ENTITY_ELEMENT_COUNT_FIELD'),
            ),
            'STATE_MODIFIED' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_URL_ENTITY_STATE_MODIFIED_FIELD'),
                'default_value' => 'N',
            ),
            'STATE_DELETED' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_URL_ENTITY_STATE_DELETED_FIELD'),
                'default_value' => 'N',
            ),
            'HASH' => array(
                'data_type' => 'string',
                'title' => Loc::getMessage('SMARTSEO_FILTER_URL_ENTITY_HASH_FIELD'),
            ),
            (new Reference('FILTER_CONDITION', SmartseoFilterConditionTable::class, Join::on('this.FILTER_CONDITION_ID', 'ref.ID')
              ->where('this.STATE_DELETED', 'N')))
              ->configureJoinType('left'),
            (new Reference('SECTION', \Bitrix\Iblock\SectionTable::class, Join::on('this.SECTION_ID', 'ref.ID')))
                ->configureJoinType('left'),
        );
    }

    /**
     * Returns validators for NAME field.
     *
     * @return array
     */
    public static function validateName()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

    public static function getDataScopeByUrl($url, $siteId = null)
    {
        list($onlyUrl, $onlyQuery) = explode('?', $url);

        $row = self::getRow([
            'select' => [
                'URL_ID' => 'ID',
                'URL_NAME' => 'NAME',
                'URL_ACTIVE' => 'ACTIVE',
                'URL_REAL' => 'REAL_URL',
                'URL_NEW' => 'NEW_URL',
                'URL_PROPERTIES' => 'PROPERTIES',
                'CONDITION_ID' => 'FILTER_CONDITION.ID',
                'CONDITION_ACTIVE' => 'FILTER_CONDITION.ACTIVE',
                'CONDITION_NAME' => 'FILTER_CONDITION.NAME',
                'CONDITION_URL_STRICT_COMPLIANCE' => 'FILTER_CONDITION.URL_STRICT_COMPLIANCE',
                'CONDITION_URL_CLOSE_INDEXING' => 'FILTER_CONDITION.URL_CLOSE_INDEXING',
                'CONDITION_PRIORITY' => 'FILTER_CONDITION.PRIORITY',
                'RULE_ID' => 'FILTER_CONDITION.FILTER_RULE.ID',
                'RULE_ACTIVE' => 'FILTER_CONDITION.FILTER_RULE.ACTIVE',
                'RULE_NAME' => 'FILTER_CONDITION.FILTER_RULE.NAME',
                'RULE_URL_STRICT_COMPLIANCE' => 'FILTER_CONDITION.FILTER_RULE.URL_STRICT_COMPLIANCE',
                'RULE_URL_CLOSE_INDEXING' => 'FILTER_CONDITION.FILTER_RULE.URL_CLOSE_INDEXING',
                'RULE_SITE_ID' => 'FILTER_CONDITION.FILTER_RULE.SITE_ID',
                'RULE_IBLOCK_TYPE_ID' => 'FILTER_CONDITION.FILTER_RULE.IBLOCK_TYPE_ID',
                'RULE_IBLOCK_ID' => 'FILTER_CONDITION.FILTER_RULE.IBLOCK_ID',
                'RULE_IBLOCK_INCLUDE_SUBSECTIONS' => 'FILTER_CONDITION.FILTER_RULE.IBLOCK_INCLUDE_SUBSECTIONS',
             ],
            'filter' => [
                'FILTER_CONDITION.FILTER_RULE.ACTIVE' => 'Y',
                'FILTER_CONDITION.ACTIVE' => 'Y',
                'ACTIVE' => 'Y',
                'STATE_DELETED' => 'N',
                [
                  'LOGIC' => 'OR',
                  'FILTER_CONDITION.FILTER_RULE.SECTION.ACTIVE' => 'Y',
                  'FILTER_CONDITION.FILTER_RULE.SECTION_ID' => 0,
                ],
                'FILTER_CONDITION.FILTER_RULE.SITE_ID' => $siteId,
                [
                   'LOGIC' => 'OR',
                   'REAL_URL' => $url,
                   'NEW_URL' => $onlyUrl,
                ]
            ],
            'order' => [
                'FILTER_CONDITION.PRIORITY' => 'ASC',
            ],
            'cache' => [
                'ttl' => self::getCacheTtl(),
            ]
        ]);


        if ($row) {
            $row['URL_REAL_QUERY'] = '';
            if (strpos($row['URL_REAL'], '?')) {
                list($realUrl, $realUrlQuery) = explode('?', $row['URL_REAL']);
                $row['URL_REAL_QUERY'] = $realUrlQuery;
            }
        }

        return $row;
    }

    public static function deleteNotModifiedUrls($filterConditionId)
    {

        $sql = 'DELETE FROM ' . self::getTableName()
          . ' WHERE FILTER_CONDITION_ID = ' . (int) $filterConditionId
          . ' AND STATE_MODIFIED = "N"';

        $connection = \Bitrix\Main\Application::getConnection();

        $connection->queryExecute($sql);
    }

    public static function deleteAllUrls($filterConditionId)
    {

        $sql = 'DELETE FROM ' . self::getTableName()
          . ' WHERE FILTER_CONDITION_ID = ' . (int) $filterConditionId;

        $connection = \Bitrix\Main\Application::getConnection();

        $connection->queryExecute($sql);
    }

    public static function deleteModifiedUrls($filterConditionId, array $urlIds)
    {
        if(!$urlIds) {
            return;
        }

        $sql = 'DELETE FROM ' . self::getTableName()
          . ' WHERE ID IN  (' . implode(',', $urlIds) . ')'
          . ' AND FILTER_CONDITION_ID = ' . (int) $filterConditionId
          . ' AND STATE_MODIFIED = "Y"';

        $connection = \Bitrix\Main\Application::getConnection();

        $connection->queryExecute($sql);
    }

    public static function getModifiedPairHashUrls($filterConditionId)
    {
        $rows = self::getList([
            'select' => [
                'ID',
                'HASH',
            ],
            'filter' => [
                'FILTER_CONDITION_ID' => $filterConditionId
            ]
        ])->fetchAll();

        return array_column($rows, 'ID', 'HASH');
    }

}
