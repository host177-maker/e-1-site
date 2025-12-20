<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main,
    Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
    Bitrix\Main\ORM\Fields\Relations\Reference,
    Bitrix\Main\ORM\Fields\Relations\OneToMany,
    Bitrix\Main\ORM\Query\Join,
	Bitrix\Main\ORM\Fields\DatetimeField,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
    Bitrix\Main\ORM\Fields\BooleanField,
    Bitrix\Main\ORM\Fields\TextField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoSeoTextTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(255) optional
 * <li> TYPE string(1) optional default 'S'
 * <li> SITE_ID string(4) mandatory
 * <li> IBLOCK_TYPE_ID string(255) mandatory
 * <li> IBLOCK_ID int mandatory
 * <li> CONDITION_TREE text optional
 * <li> DATE_CREATE datetime optional
 * <li> DATE_CHANGE datetime optional
 * <li> DATE_LAST_RUN datetime optional
 * <li> SORT int optional default 500
 * <li> CREATED_BY int optional
 * <li> MODIFIED_BY int optional
 * <li> REWRITE bool ('N', 'Y') optional default 'N'
 * <li> SECTION_ID int optional
 * </ul>
 *
 * @package Bitrix\Aspro
 **/

class SmartseoSeoTextTable extends DataManager
{
    const DEFAULT_CACHE_TTL = 0;

    const TYPE_SECTION = 'S';
    const TYPE_ELEMENT = 'E';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_aspro_max_smartseo_seo_text';
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
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_ID_FIELD')
				]
			),
            new StringField(
				'NAME',
				[
					'validation' => [__CLASS__, 'validateName'],
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_NAME_FIELD')
				]
			),
			new StringField(
				'TYPE',
				[
					'default' => 'S',
					'validation' => [__CLASS__, 'validateType'],
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_TYPE_FIELD')
				]
			),
			new StringField(
				'SITE_ID',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateSiteId'],
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_SITE_ID_FIELD')
				]
			),
			new StringField(
				'IBLOCK_TYPE_ID',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateIblockTypeId'],
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_IBLOCK_TYPE_ID_FIELD')
				]
			),
			new IntegerField(
				'IBLOCK_ID',
				[
					'required' => true,
                    'validation' => [__CLASS__, 'validateIblockId'],
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_IBLOCK_ID_FIELD')
				]
			),
            new TextField(
				'CONDITION_TREE',
				[
                    'validation' => [__CLASS__, 'validateConditionTree'],
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_CONDITION_TREE_FIELD')
				]
			),
			new DatetimeField(
				'DATE_CREATE',
				[
                    'default' => new \Bitrix\Main\Type\DateTime,
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_DATE_CREATE_FIELD')
				]
			),
			new DatetimeField(
				'DATE_CHANGE',
				[
                    'default' => new \Bitrix\Main\Type\DateTime,
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_DATE_CHANGE_FIELD')
				]
			),
			new DatetimeField(
				'DATE_LAST_RUN',
				[
                    'default' => new \Bitrix\Main\Type\DateTime,
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_DATE_LAST_RUN_FIELD')
				]
			),
			new IntegerField(
				'SORT',
				[
					'default' => 500,
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_SORT_FIELD')
				]
			),
			new IntegerField(
				'CREATED_BY',
				[
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_CREATED_BY_FIELD')
				]
			),
			new IntegerField(
				'MODIFIED_BY',
				[
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_MODIFIED_BY_FIELD')
				]
			),
            new BooleanField(
				'REWRITE',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
					'title' => Loc::getMessage('SMARTSEO_SEO_TEXT_ENTITY_REWRITE_FIELD')
				]
			),
            new IntegerField(
				'SECTION_ID',
				[
                    'default' => 0,
					'title' => ''
				]
			),
            (new OneToMany('IBLOCK_SECTIONS', SmartseoSeoTextIblockSectionsTable::class, 'SEO_TEXT'))
              ->configureJoinType('left'),
            (new OneToMany('PROPERTIES', SmartseoSeoTextPropertyTable::class, 'SEO_TEXT'))
              ->configureJoinType('left'),
            (new Reference('IBLOCK_TYPE', \Bitrix\Iblock\TypeLanguageTable::class, Join::on('this.IBLOCK_TYPE_ID', 'ref.IBLOCK_TYPE_ID')))
              ->configureJoinType('left'),
            (new Reference('IBLOCK', \Bitrix\Iblock\IblockTable::class, Join::on('this.IBLOCK_ID', 'ref.ID')))
              ->configureJoinType('left'),
            (new Reference('SITE', \Bitrix\Main\SiteTable::class, Join::on('this.SITE_ID', 'ref.LID')))
              ->configureJoinType('left')
		];
	}

    public static function getMapFields() {
        $result = [];

        foreach (static::getMap() as $field) {
            $result[] = $field->getName();
        }

        return $result;
    }

    public static function hasMapField($code)
    {
        return in_array($code, self::getMapFields());
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

	/**
	 * Returns validators for TYPE field.
	 *
	 * @return array
	 */
	public static function validateType()
	{
		return [
			new LengthValidator(null, 1),
            function($value) {
                if(!in_array($value, [self::TYPE_ELEMENT, self::TYPE_SECTION])) {
                    return Loc::getMessage('SMARTSEO_SEO_TEXT_VALIDATE_TYPE', [
                        '#TYPE#' => $value,
                    ]);
                }

                return true;
            }
		];
	}

	/**
	 * Returns validators for SITE_ID field.
	 *
	 * @return array
	 */
	public static function validateSiteId()
	{
		return [
			new LengthValidator(null, 4),
		];
	}

	/**
	 * Returns validators for IBLOCK_TYPE_ID field.
	 *
	 * @return array
	 */
	public static function validateIblockTypeId()
	{
		return [
			new LengthValidator(null, 255),
		];
	}

    /**
	 * Returns validators for IBLOCK_ID field.
	 *
	 * @return array
	 */
	public static function validateIblockId()
	{
		return [
			function($value) {
                if(empty($value)) {
                    return Loc::getMessage('SMARTSEO_SEO_TEXT_VALIDATE_IBLOCK_ID');
                }

                return true;
            }
		];
	}

    /**
	 * Returns validators for IBLOCK_ID field.
	 *
	 * @return array
	 */
	public static function validateConditionTree()
	{
		return [
			function($value, $fields) {
                if($fields['TYPE'] == self::TYPE_ELEMENT && empty($value)) {
                    return Loc::getMessage('SMARTSEO_SEO_TEXT_VALIDATE_CONDITION_TREE');
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

        $rows = SmartseoSeoTextIblockSectionsTable::getList([
              'select' => ['ID', 'SECTION_ID'],
              'filter' => [
                  'SEO_TEXT_ID' => $id
              ],
              'cache' => [
                  'ttl' => SmartseoSeoTextIblockSectionsTable::getCacheTtl(),
              ]
          ])->fetchAll();

        $data['IBLOCK_SECTIONS'] = array_column($rows, 'SECTION_ID');

        return $data;
    }

    public static function getSectionProperties($seotextId = null, $onlyFilledValues = true)
    {
        $propertyValues = [];
        $iblockId = null;
        $seotextElementTemplate = null;

        if ($seotextId) {
            $seotext = self::getRowById($seotextId);
            $seotextElementTemplate = new \Aspro\Max\Smartseo\Template\Entity\SeoText($seotextId);

            $rows = SmartseoSeoTextPropertyTable::getList([
                  'select' => [
                      'CODE',
                      'TEXT',
                  ],
                  'filter' => [
                      'SEO_TEXT_ID' => $seotext['ID'],
                  ],
                  'cache' => [
                      'ttl' => SmartseoSeoTextPropertyTable::getCacheTtl(),
                  ]
              ])->fetchAll();

            $propertyValues = array_column($rows, 'TEXT', 'CODE');
            $iblockId = $seotext['IBLOCK_ID'];
        }

        $sectionFields = SmartseoSeoTextPropertyTable::getAllSectionFields($iblockId);

        $result = [];

        foreach ($sectionFields as $field) {
            $_value = '';

            if($propertyValues[$field['CODE']]) {
                $_value = $propertyValues[$field['CODE']];
            }

            if($field['ENTITY_TYPE'] == SmartseoSeoTextPropertyTable::ENTITY_TYPE_SECTION_PROPERTY
              && $onlyFilledValues && !$_value) {
                continue;
            }

            $result[$field['CODE']] = $field;
            $result[$field['CODE']]['TEXT'] = $_value;
            $result[$field['CODE']]['SAMPLE'] = '';

            if($seotextElementTemplate) {
                $result[$field['CODE']]['SAMPLE'] = \Bitrix\Main\Text\HtmlFilter::encode(
                    \Bitrix\Iblock\Template\Engine::process($seotextElementTemplate, $_value)
                );
            }
        }

        return $result;
    }

    public static function getElementProperties($seotextId = null, $onlyFilledValues = true)
    {
        $propertyValues = [];
        $iblockId = null;
        $seotextElementTemplate = null;

        if ($seotextId) {
            $seotext = self::getRowById($seotextId);
            $seotextElementTemplate = new \Aspro\Max\Smartseo\Template\Entity\SeoText($seotextId);

            $rows = SmartseoSeoTextPropertyTable::getList([
                  'select' => [
                      'CODE',
                      'TEXT',
                  ],
                  'filter' => [
                      'SEO_TEXT_ID' => $seotext['ID'],
                  ],
                  'cache' => [
                      'ttl' => SmartseoSeoTextPropertyTable::getCacheTtl(),
                  ]
              ])->fetchAll();

            $propertyValues = array_column($rows, 'TEXT', 'CODE');
            $iblockId = $seotext['IBLOCK_ID'];
        }

        $elementFields = SmartseoSeoTextPropertyTable::getAllElementnFields($iblockId);

        $result = [];

        foreach ($elementFields as $field) {
            $_value = '';

            if($propertyValues[$field['CODE']]) {
                $_value = $propertyValues[$field['CODE']];
            }

            if($field['ENTITY_TYPE'] == SmartseoSeoTextPropertyTable::ENTITY_TYPE_ELEMENT_PROPERTY
              && $onlyFilledValues && !$_value) {
                continue;
            }

            $result[$field['CODE']] = $field;
            $result[$field['CODE']]['TEXT'] = $_value;
            $result[$field['CODE']]['SAMPLE'] = '';

            if($seotextElementTemplate) {
                $result[$field['CODE']]['SAMPLE'] = \Bitrix\Main\Text\HtmlFilter::encode(
                    \Bitrix\Iblock\Template\Engine::process($seotextElementTemplate, $_value)
                );
            }
        }

        return $result;
    }

    public static function updateSectionProperties($seotextId, $data)
    {
        $sectionProperties = self::getSectionProperties($seotextId, false);

        self::updateProperties($seotextId, $sectionProperties, $data, [
            SmartseoSeoTextPropertyTable::ENTITY_TYPE_SECTION_FIELD,
            SmartseoSeoTextPropertyTable::ENTITY_TYPE_SECTION_PROPERTY
        ]);
    }

    public static function updateElementProperties($seotextId, $data)
    {
        $elementProperties = self::getElementProperties($seotextId, false);

        self::updateProperties($seotextId, $elementProperties, $data, [
            SmartseoSeoTextPropertyTable::ENTITY_TYPE_ELEMENT_FIELD,
            SmartseoSeoTextPropertyTable::ENTITY_TYPE_ELEMENT_PROPERTY
        ]);
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

    public static function OnAfterDelete(Main\Entity\Event $event)
    {
        $result = new Main\Entity\EventResult;

        static::cascadeDeleteIblockSections($event, $result);

        static::cascadeDeleteProperties($event, $result);

        return $result;
    }

    protected static function updateProperties($seotextId, $properties, $data, array $entityTypes)
    {
        $propertyCollection = SmartseoSeoTextPropertyTable::getList([
           'filter' => [
             'SEO_TEXT_ID' => $seotextId,
             '=ENTITY_TYPE' => $entityTypes,
           ]
         ])->fetchCollection();

        $modifiedData = [];
        foreach ($data as $code => $value) {
            $_code = preg_replace('/_property.*/', '', $code);

            if(!$properties[$_code]) {
                continue;
            }

            $modifiedData[$_code] = trim($value);
        }

        foreach ($propertyCollection as $propertyValue) {
            $_code = $propertyValue->getCode();

            if (isset($modifiedData[$_code])) {
                if ($modifiedData[$_code]) {
                    $propertyValue->setText($modifiedData[$_code]);
                } else {
                    SmartseoSeoTextPropertyTable::delete($propertyValue->getId());
                }

                unset($modifiedData[$_code]);
            } else {
                SmartseoSeoTextPropertyTable::delete($propertyValue->getId());
            }
        }

        if($modifiedData) {
            self::addProperties($seotextId, $properties, $modifiedData);
        }

        $result = $propertyCollection->save(true);

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

    protected static function addProperties($seotextId, $properties, $data)
    {
        $propertyCollection = new \Aspro\Max\Smartseo\Models\EO_SmartseoSeoTextProperty_Collection();

        foreach ($data as $code => $value) {
            $_code = preg_replace('/_property.*/', '', $code);

            if(!$properties[$_code]) {
                continue;
            }

            $_property = $properties[$_code];

            if(!$value) {
                continue;
            }

            $newProperty = new \Aspro\Max\Smartseo\Models\EO_SmartseoSeoTextProperty();
            $newProperty->setSeoTextId($seotextId);
            $newProperty->setText($value);
            $newProperty->setEntityId($_property['ENTITY_ID']);
            $newProperty->setEntityType($_property['ENTITY_TYPE']);
            $newProperty->setCode($_property['CODE']);
            $newProperty->setName($_property['NAME']);

            $propertyCollection[] = $newProperty;
        }

        $result = $propertyCollection->save(true);

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

    private static function cascadeDeleteIblockSections(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoSeoTextIblockSectionsTable::getTableName()
          . ' WHERE SEO_TEXT_ID = ' . (int) $data['ID'];

        try {
            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoSeoTextIblockSectionsTable::getTableName() . ' SEO_TEXT_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }

    private static function cascadeDeleteProperties(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoSeoTextPropertyTable::getTableName()
          . ' WHERE SEO_TEXT_ID = ' . (int) $data['ID'];

        try {
            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoSeoTextPropertyTable::getTableName() . ' SEO_TEXT_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }
}