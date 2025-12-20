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
 * Class SmartseoSeoTextIblockSectionsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> SEO_TEXT_ID int mandatory
 * <li> SECTION_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Aspro
 **/

class SmartseoSeoTextIblockSectionsTable extends DataManager
{
    const DEFAULT_CACHE_TTL = 0;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_aspro_max_smartseo_seo_text_iblock_sections';
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
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_IBLOCK_SECTIONS_ENTITY_ID_FIELD')
				]
			),
			new IntegerField(
				'SEO_TEXT_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_IBLOCK_SECTIONS_ENTITY_SEO_TEXT_ID_FIELD')
				]
			),
			new IntegerField(
				'SECTION_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_IBLOCK_SECTIONS_ENTITY_SECTION_ID_FIELD')
				]
			),
            (new Reference('SEO_TEXT', SmartseoSeoTextTable::class, Join::on('this.SEO_TEXT_ID', 'ref.ID')))
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