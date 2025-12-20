<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Entity as SmartseoEntity,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoFilterIblockSectionsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FILTER_RULE_ID int mandatory
 * <li> SECTION_ID int mandatory
 * </ul>
 *
 * @package Aspro\Max\Smartseo
 **/

class SmartseoFilterIblockSectionsTable extends Main\Entity\DataManager
{
    const DEFAULT_CACHE_TTL = 0;

    public static function getObjectClass()
    {
        return SmartseoEntity\FilterIblockSection::class;
    }

    public static function getCollectionClass()
    {
        return SmartseoEntity\FilterIblockSections::class;
    }

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_aspro_max_smartseo_filter_iblock_sections';
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
				'title' => Loc::getMessage('SMARTSEO_FILTER_IBLOCK_SECTIONS_ENTITY_ID_FIELD'),
			),
			'FILTER_RULE_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SMARTSEO_FILTER_IBLOCK_SECTIONS_ENTITY_FILTER_RULE_ID_FIELD'),
			),
			'SECTION_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SMARTSEO_FILTER_IBLOCK_SECTIONS_ENTITY_SECTION_ID_FIELD'),
			),
            (new Reference('FILTER_RULE', SmartseoFilterRuleTable::class, Join::on('this.FILTER_RULE_ID', 'ref.ID')))
                ->configureJoinType('left'),
            (new Reference('SECTION', \Bitrix\Iblock\SectionTable::class, Join::on('this.SECTION_ID', 'ref.ID')))
                ->configureJoinType('left'),
		);
	}

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }
}