<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\TextField,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoNoindexUrlTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NOINDEX_RULE_ID int mandatory
 * <li> URL text mandatory
 * <li> IBLOCK_ID int optional
 * <li> SECTION_ID int optional
 * </ul>
 *
 * @package Bitrix\Aspro
 **/

class SmartseoNoindexUrlTable extends DataManager
{
    const DEFAULT_CACHE_TTL = 0;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_aspro_max_smartseo_noindex_url';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_URL_ENTITY_ID_FIELD')
				]
			),
			new IntegerField(
				'NOINDEX_RULE_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_URL_ENTITY_NOINDEX_RULE_ID_FIELD')
				]
			),
			new TextField(
				'URL',
				[
					'required' => true,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_URL_ENTITY_URL_FIELD')
				]
			),
			new IntegerField(
				'IBLOCK_ID',
				[
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_URL_ENTITY_IBLOCK_ID_FIELD')
				]
			),
			new IntegerField(
				'SECTION_ID',
				[
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_URL_ENTITY_SECTION_ID_FIELD')
				]
			),
            (new Reference('NOINDEX_RULE', SmartseoNoindexRuleTable::class, Join::on('this.NOINDEX_RULE_ID', 'ref.ID')))
                ->configureJoinType('left'),
		];
	}

    public static function getDataByUrl($url, $siteId = null)
    {
        $row = self::getRow([
            'select' => [
                'NOINDEX_RULE_ID',
                'IBLOCK_ID',
                'SECTION_ID',
                'URL',
            ],
            'filter' => [
                'NOINDEX_RULE.ACTIVE' => 'Y',
                'NOINDEX_RULE.SITE_ID' => $siteId,
                'URL' => $url . '%',
            ],
            'cache' => [
                'ttl' => self::getCacheTtl(),
            ]
        ]);

        return $row;
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

    public static function deleteAllUrls($filterConditionId)
    {

        $sql = 'DELETE FROM ' . self::getTableName()
          . ' WHERE NOINDEX_RULE_ID = ' . (int) $filterConditionId;

        $connection = \Bitrix\Main\Application::getConnection();

        $connection->queryExecute($sql);
    }
}