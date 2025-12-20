<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Query\Join,
    Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\TextField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoSeoTextPropertyTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> SEO_TEXT_ID int mandatory
 * <li> ENTITY_ID int mandatory
 * <li> ENTITY_TYPE string(2) optional default 'SF'
 * <li> NAME string(255) optional
 * <li> CODE string(255) mandatory
 * <li> TEXT text mandatory
 * </ul>
 *
 * @package Bitrix\Aspro
 **/

class SmartseoSeoTextPropertyTable extends DataManager
{
    const DEFAULT_CACHE_TTL = 0;

    const ENTITY_TYPE_SECTION_FIELD = 'SF';
    const ENTITY_TYPE_SECTION_PROPERTY = 'SP';
    const ENTITY_TYPE_ELEMENT_FIELD = 'EF';
    const ENTITY_TYPE_ELEMENT_PROPERTY = 'EP';


	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_aspro_max_smartseo_seo_text_property';
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
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_PROPERTY_ENTITY_ID_FIELD')
				]
			),
			new IntegerField(
				'SEO_TEXT_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_PROPERTY_ENTITY_SEO_TEXT_ID_FIELD')
				]
			),
			new IntegerField(
				'ENTITY_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_PROPERTY_ENTITY_ENTITY_ID_FIELD')
				]
			),
			new StringField(
				'ENTITY_TYPE',
				[
					'default' => 'SF',
					'validation' => [__CLASS__, 'validateEntityType'],
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_PROPERTY_ENTITY_ENTITY_TYPE_FIELD')
				]
			),
			new StringField(
				'CODE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateCode'],
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_PROPERTY_ENTITY_CODE_FIELD')
				]
			),
            new StringField(
				'NAME',
				[
					'validation' => [__CLASS__, 'validateName'],
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_PROPERTY_ENTITY_NAME_FIELD')
				]
			),
			new TextField(
				'TEXT',
				[
					'required' => true,
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_PROPERTY_ENTITY_TEXT_FIELD')
				]
			),
            (new Reference('SEO_TEXT', SmartseoSeoTextTable::class, Join::on('this.SEO_TEXT_ID', 'ref.ID')))
                ->configureJoinType('left'),
		];
	}

    public static function getMapSectionFields()
    {

        return [
            'DESCRIPTION' => [
                'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_PROPERTY_DESCRIPTION'),
                'sort' => 1,
            ],
        ];
    }

    public static function getMapElementFields()
    {
        return [
            'PREVIEW_TEXT' => [
                'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_PROPERTY_PREVIEW_TEXT'),
                'sort' => 1,
            ],
            'DETAIL_TEXT' => [
                'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_PROPERTY_DETAIL_TEXT'),
                'sort' => 2,
            ],
        ];
    }

    public static function getEntityTypeList()
    {
        return [
            self::ENTITY_TYPE_ELEMENT_FIELD,
            self::ENTITY_TYPE_ELEMENT_PROPERTY,
            self::ENTITY_TYPE_SECTION_FIELD,
            self::ENTITY_TYPE_SECTION_PROPERTY,
        ];
    }

    /**
	 * Returns validators for ENTITY_TYPE field.
	 *
	 * @return array
	 */
	public static function validateEntityType()
	{
		return [
			new LengthValidator(null, 2),
            function($value) {
                if(!in_array($value, self::getEntityTypeList())) {
                    return Loc::getMessage('SMARTSEO_SEO_TEXT_VALIDATE_ENTITY_TYPE', [
                        '#ENTITY_TYPE#' => $value,
                    ]);
                }

                return true;
            }
		];
	}

	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateCode()
	{
		return [
			new LengthValidator(null, 255),
		];
	}

    /**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return [
			new LengthValidator(null, 255),
		];
	}

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

    public static function getAllSectionFields($iblockId = null, $isLoadValues = false)
    {
        $result = [];

        foreach (self::getMapSectionFields() as $code => $field) {
            $result[$code] = [
                'ENTITY_ID' => null,
                'ENTITY_TYPE' => self::ENTITY_TYPE_SECTION_FIELD,
                'CODE' => $code,
                'NAME' => $field['title']
            ];
        }

        if($iblockId) {
            foreach (self::getSectionProperties($iblockId) as $field) {
                $result[$field['CODE']] = [
                    'ENTITY_ID' => $field['ID'],
                    'ENTITY_TYPE' => self::ENTITY_TYPE_SECTION_PROPERTY,
                    'CODE' => $field['CODE'],
                    'NAME' => $field['NAME']
                ];
            }
        }

        return $result;
    }

    public static function getAllElementnFields($iblockId = null)
    {
        $result = [];

        foreach (self::getMapElementFields() as $code => $field) {
            $result[$code] = [
                'ENTITY_ID' => null,
                'ENTITY_TYPE' => self::ENTITY_TYPE_ELEMENT_FIELD,
                'CODE' => $code,
                'NAME' => $field['title']
            ];
        }

        if($iblockId) {
            foreach (self::getElementProperties($iblockId) as $field) {
                $result[$field['CODE']] = [
                    'ENTITY_ID' => $field['ID'],
                    'ENTITY_TYPE' => self::ENTITY_TYPE_ELEMENT_PROPERTY,
                    'CODE' => $field['CODE'],
                    'NAME' => $field['NAME']
                ];
            }
        }

        return $result;
    }

    public static function getSectionProperties($iblockId, array $ignorePropertyIds = [])
    {
        if (empty($iblockId)) {
            return [];
        }

        $rows = \Bitrix\Main\UserFieldTable::getList(array_filter([
              'select' => [
                  'ID',
                  'LANG_NAME' => 'LANG.EDIT_FORM_LABEL',
                  'FIELD_NAME',
                  'USER_TYPE_ID'
              ],
              'filter' => array_filter([
                  'ENTITY_ID' => 'IBLOCK_' . $iblockId . '_SECTION',
                  'USER_TYPE_ID' => 'string',
                  '!ID' => $ignorePropertyIds,
              ]),
              'runtime' => [
                  new \Bitrix\Main\Entity\ReferenceField(
                    'LANG', FieldLangTable::class, \Bitrix\Main\ORM\Query\Join::on('this.ID', 'ref.USER_FIELD_ID')
                      ->where('ref.LANGUAGE_ID', 'ru')
                  )
              ],
              'cache' => self::getCacheTtl(),
          ]))->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'ID' => $row['ID'],
                'CODE' => $row['FIELD_NAME'],
                'NAME' => $row['LANG_NAME'] ?: $row['FIELD_NAME'],
            ];
        }

        return $result;
    }

    public static function getElementProperties($iblockId, array $ignorePropertyIds = [])
    {
         $rows = \Bitrix\Iblock\PropertyTable::getList(array_filter([
              'select' => [
                  'ID',
                  'IBLOCK_ID',
                  'NAME',
                  'CODE',
                  'SORT',
              ],
              'filter' => array_filter([
                  'IBLOCK_ID' => $iblockId,
                  '!ID' => $ignorePropertyIds,
                  'PROPERTY_TYPE' => 'S',
                  'USER_TYPE' => 'HTML',
                  'MULTIPLE' => 'N',
              ]),
              'order' => [
                  'NAME' => 'ASC',
              ],
          ]))->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'ID' => $row['ID'],
                'CODE' => $row['CODE'],
                'IBLOCK_ID' => $row['IBLOCK_ID'],
                'NAME' => $row['NAME'],
            ];
        }

        return $result;
    }

    public static function getSectionProperty($iblockId, $propertyId)
    {
        $row = \Bitrix\Main\UserFieldTable::getRow(array_filter([
              'select' => [
                  'LANG_NAME' => 'LANG.EDIT_FORM_LABEL',
                  'FIELD_NAME',
                  'ID',
                  'USER_TYPE_ID'
              ],
              'filter' => [
                  'ID' => $propertyId,
                  'ENTITY_ID' => 'IBLOCK_' . $iblockId . '_SECTION',
                  'USER_TYPE_ID' => 'string',
              ],
              'runtime' => [
                  new \Bitrix\Main\Entity\ReferenceField(
                    'LANG', FieldLangTable::class, \Bitrix\Main\ORM\Query\Join::on('this.ID', 'ref.USER_FIELD_ID')
                      ->where('ref.LANGUAGE_ID', 'ru')
                  )
              ],
              'cache' => self::getCacheTtl(),
          ]));

        if(!$row) {
            return [];
        }

        return [
            'ID' => $row['ID'],
            'IBLOCK_ID' => $iblockId,
            'CODE' => $row['FIELD_NAME'],
            'NAME' => $row['LANG_NAME'] ?: $row['FIELD_NAME'],
        ];
    }

    public static function getElementProperty($iblockId, $propertyId)
    {
        $row = \Bitrix\Iblock\PropertyTable::getRow(array_filter([
              'select' => [
                  'ID',
                  'IBLOCK_ID',
                  'NAME',
                  'CODE',
                  'SORT',
              ],
              'filter' => array_filter([
                  'IBLOCK_ID' => $iblockId,
                  'ID' => $propertyId,
                  'PROPERTY_TYPE' => 'S',
                  'USER_TYPE' => 'HTML'
              ]),
              'order' => [
                  'NAME' => 'ASC',
              ],
              'cache' => [
                'ttl' => self::getCacheTtl(),
              ]
          ]));

        if(!$row) {
            return [];
        }

        return [
            'ID' => $row['ID'],
            'IBLOCK_ID' => $iblockId,
            'CODE' => $row['CODE'],
            'NAME' => $row['NAME'],
        ];
    }
}