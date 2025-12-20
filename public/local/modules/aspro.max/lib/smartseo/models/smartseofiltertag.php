<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\BooleanField,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\TextField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Fields\Relations\OneToMany,
    Bitrix\Main\ORM\Query\Join;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoFilterTagTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> FILTER_CONDITION_ID int mandatory
 * <li> PARENT_FILTER_CONDITION_ID int optional
 * <li> SECTION_ID int optional
 * <li> ACTIVE bool ('N', 'Y') optional default 'Y'
 * <li> TEMPLATE string(255) mandatory
 * <li> TYPE string(2) optional default 'SL'
 * <li> RELATED_PROPERTY string mandatory
 * <li> DEPTH_LEVEL int mandatory
 * </ul>
 *
 * @package Bitrix\Aspro
 **/

class SmartseoFilterTagTable extends DataManager
{
    const DEFAULT_CACHE_TTL = 0;

    const TYLE_SELF_SECTION = 'SL';
    const TYPE_SECTION = 'SC';
    const TYPR_FILTER_CONDITION = 'FC';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_aspro_max_smartseo_filter_tag';
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
					'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ENTITY_ID_FIELD')
				]
			),
			new IntegerField(
				'FILTER_CONDITION_ID',
				[
					'required' => true,
                    'validation' => [__CLASS__, 'validateFilterCondition'],
					'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ENTITY_FILTER_CONDITION_ID_FIELD')
				]
			),
			new IntegerField(
				'PARENT_FILTER_CONDITION_ID',
				[
                    'validation' => [__CLASS__, 'validateParentFilterCondition'],
					'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ENTITY_PARENT_FILTER_CONDITION_ID_FIELD')
				]
			),
			new IntegerField(
				'SECTION_ID',
				[
                    'validation' => [__CLASS__, 'validateSection'],
					'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ENTITY_SECTION_ID_FIELD')
				]
			),
			new BooleanField(
				'ACTIVE',
				[
					'values' => array('N', 'Y'),
					'default' => 'Y',
					'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ENTITY_ACTIVE_FIELD')
				]
			),
			new StringField(
				'TEMPLATE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateTemplate'],
					'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ENTITY_TEMPLATE_FIELD')
				]
			),
			new StringField(
				'TYPE',
				[
					'default' => 'SL',
					'validation' => [__CLASS__, 'validateType'],
					'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ENTITY_TYPE_FIELD')
				]
			),
            new TextField(
				'RELATED_PROPERTY',
				[
					'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ENTITY_RELATED_PROPERTY_FIELD'),
				]
			),
             new IntegerField(
				'DEPTH_LEVEL',
				[
					'title' => Loc::getMessage('SMARTSEO_FILTER_TAG_ENTITY_DEPTH_LEVEL_FIELD'),
                    'default' => 0,
				]
			),
            (new Reference('FILTER_CONDITION', SmartseoFilterConditionTable::class, Join::on('this.FILTER_CONDITION_ID', 'ref.ID')))
                ->configureJoinType('left'),
            (new Reference('PARENT_FILTER_CONDITION', SmartseoFilterConditionTable::class, Join::on('this.PARENT_FILTER_CONDITION_ID', 'ref.ID')))
                ->configureJoinType('left'),
            (new Reference('SECTION', \Bitrix\Iblock\SectionTable::class, Join::on('this.SECTION_ID', 'ref.ID')))
                ->configureJoinType('left'),
		];
	}

    public static function getTypeParams($type = null)
    {
        $result = [
            self::TYLE_SELF_SECTION => Loc::getMessage('SMARTSEO_FILTER_TAG_TYPE_SELF'),
            self::TYPE_SECTION => Loc::getMessage('SMARTSEO_FILTER_TAG_TYPE_SECTION'),
            self::TYPR_FILTER_CONDITION => Loc::getMessage('SMARTSEO_FILTER_TAG_TYPE_FILTER_CONDITION'),

        ];

        if($type) {
            return $result[$type];
        }

        return $result;
    }

	/**
	 * Returns validators for TEMPLATE field.
	 *
	 * @return array
	 */
	public static function validateTemplate()
	{
		return [
			new LengthValidator(null, 255),
		];
	}

    /**
	 * Returns validators for FILTER_CONDITION_ID field.
	 *
	 * @return array
	 */
	public static function validateFilterCondition()
	{
		return [
			function($value, $primary, $fields) {
                if(empty($value)) {
                    return Loc::getMessage('SMARTSEO_FILTER_TAG_VALIDATE_PARENT_FILTER_CONDITION');
                }

                return true;
            }
		];
	}

    /**
	 * Returns validators for PARENT_FILTER_CONDITION_ID field.
	 *
	 * @return array
	 */
	public static function validateParentFilterCondition()
	{
		return [
			function($value, $primary, $fields) {
                if($fields['TYPE'] == self::TYPR_FILTER_CONDITION && !$value) {
                    return Loc::getMessage('SMARTSEO_FILTER_SITEMAP_VALIDATE_FILTER_CONDITION');
                }

                return true;
            }
		];
	}

    /**
	 * Returns validators for SECTION field.
	 *
	 * @return array
	 */
	public static function validateSection()
	{
		return [
			function($value, $primary, $fields) {
                if($fields['TYPE'] == self::TYPE_SECTION && !$value) {
                    return Loc::getMessage('SMARTSEO_FILTER_TAG_VALIDATE_SECTION');
                }

                return true;
            }
		];
	}

	/**
	 * Returns validators for TYPE field.
	 *
	 * @return array
	 */
	public static function validateType()
	{
		return [
			new LengthValidator(null, 2),
            function($value, $primary, $fields) {
                if(!in_array($value, [self::TYLE_SELF_SECTION, self::TYPE_SECTION, self::TYPR_FILTER_CONDITION])) {
                    return Loc::getMessage('SMARTSEO_FILTER_SITEMAP_VALIDATE_TYPE', [
                        '#TYPE#' => $value,
                    ]);
                }
                return true;
            }
		];
	}

    /**
     * @inheritdoc
     */
    public static function getRowById($id) {
        $data = parent::getRowById($id);

        if(!$data) {
            return [];
        }

        if($data['RELATED_PROPERTY']) {
            $data['RELATED_PROPERTY'] = unserialize($data['RELATED_PROPERTY'], ['allowed_classes' => false]);
        }

        return $data;
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }
}