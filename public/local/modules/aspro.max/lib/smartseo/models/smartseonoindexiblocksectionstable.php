<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoNoindexIblockSectionsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NOINDEX_RULE_ID int mandatory
 * <li> SECTION_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Aspro
 **/

class SmartseoNoindexIblockSectionsTable extends DataManager
{
    const DEFAULT_CACHE_TTL = 0;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_aspro_max_smartseo_noindex_iblock_sections';
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
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_IBLOCK_SECTIONS_ENTITY_ID_FIELD')
				]
			),
			new IntegerField(
				'NOINDEX_RULE_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_IBLOCK_SECTIONS_ENTITY_NOINDEX_RULE_ID_FIELD')
				]
			),
			new IntegerField(
				'SECTION_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('SMARTSEO_NOINDEX_IBLOCK_SECTIONS_ENTITY_SECTION_ID_FIELD')
				]
			),
            (new Reference('NOINDEX_RULE', SmartseoNoindexRuleTable::class, Join::on('this.NOINDEX_RULE_ID', 'ref.ID')))
                ->configureJoinType('left'),
            (new Reference('SECTION', \Bitrix\Iblock\SectionTable::class, Join::on('this.SECTION_ID', 'ref.ID')))
                ->configureJoinType('left'),
		];
	}

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }
}