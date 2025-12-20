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
 * Class SmartseoFilterSectionTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> NAME string(255) optional
 * <li> DESCRIPTION string optional
 * <li> DATE_CREATE datetime optional
 * <li> DATE_CHANGE datetime optional
 * <li> SORT int optional default 500
 * <li> PARENT_ID int optional
 * <li> DEPTH_LEVEL int mandatory
 * </ul>
 *
 * @package Aspro\Max\Smartseo
 * */
class SmartseoFilterSectionTable extends Main\Entity\DataManager
{

    const DEFAULT_CACHE_TTL = 0;

    public static function getObjectClass()
    {
        return SmartseoEntity\FilterSection::class;
    }

    public static function getCollectionClass()
    {
        return SmartseoEntity\FilterSections::class;
    }

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_aspro_max_smartseo_filter_section';
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
                'title' => Loc::getMessage('SMARTSEO_FILTER_SECTION_ENTITY_ID_FIELD'),
            ),
            'ACTIVE' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_SECTION_ENTITY_ACTIVE_FIELD'),
                'default_value' => 'Y',
            ),
            'NAME' => array(
                'data_type' => 'string',
                'validation' => array(__CLASS__, 'validateName'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_SECTION_ENTITY_NAME_FIELD'),
            ),
            'DESCRIPTION' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('SMARTSEO_FILTER_SECTION_ENTITY_DESCRIPTION_FIELD'),
            ),
            'DATE_CREATE' => array(
                'data_type' => 'datetime',
                'title' => Loc::getMessage('SMARTSEO_FILTER_SECTION_ENTITY_DATE_CREATE_FIELD'),
                'default_value' => new Main\Type\DateTime,
            ),
            'DATE_CHANGE' => array(
                'data_type' => 'datetime',
                'title' => Loc::getMessage('SMARTSEO_FILTER_SECTION_ENTITY_DATE_CHANGE_FIELD'),
                'default_value' => new Main\Type\DateTime,
            ),
            'SORT' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_SECTION_ENTITY_SORT_FIELD'),
                'default_value' => 500,
            ),
            'PARENT_ID' => array(
                'data_type' => 'integer',
                'validation' => array(__CLASS__, 'validateParent'),
                'title' => Loc::getMessage('SMARTSEO_FILTER_SECTION_ENTITY_PARENT_ID_FIELD'),
            ),
            'DEPTH_LEVEL' => array(
                'data_type' => 'integer',
                'title' => Loc::getMessage('SMARTSEO_FILTER_SECTION_ENTITY_DEPTH_LEVEL_FIELD'),
                'default_value' => 0,
            ),
              (new Reference('PARENT', SmartseoFilterSectionTable::class, Join::on('this.PARENT_ID', 'ref.ID')))
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

    /**
     * Returns validators for PARENT field.
     *
     * @return array
     */
    public static function validateParent()
    {

        return array(
            function ($value, $primary) {
                if (!$primary['ID']) {
                    return true;
                }

                $self = static::getRowById($primary['ID']);

                $sections = static::getList([
                      'select' => ['ID', 'NAME', 'PARENT_ID', 'DEPTH_LEVEL'],
                      'order' => ['DEPTH_LEVEL' => 'ASC', 'PARENT_ID' => 'ASC'],
                      'filter' => array_filter([
                          '>=DEPTH_LEVEL' => $self['DEPTH_LEVEL'],
                      ]),
                      'cache' => [
                          'ttl' => static::DEFAULT_CACHE_TTL,
                      ]
                  ])->fetchAll();

                $chain = [$primary['ID']];

                foreach ($sections as $section) {
                    if (in_array($section['PARENT_ID'], $chain)) {
                        $chain[] = $section['ID'];
                    }
                }

                if (in_array($value, $chain)) {
                    return Loc::getMessage('SMARTSEO_FILTER_SECTION_VALIDATE_PARENT_ID');
                }

                return true;
            }
        );
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

    public static function OnAfterUpdate(Main\Entity\Event $event)
    {
        $result = new Main\Entity\EventResult;

        static::cascadeUpdateDepthLevel($event, $result);

        return $result;
    }

    public static function OnAfterDelete(Main\Entity\Event $event)
    {
        $result = new Main\Entity\EventResult;

        static::cascadeDeleteElements($event, $result);

        static::cascadeDeleteSections($event, $result);

        return $result;
    }

    private static function cascadeDeleteSections(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID']) {
            return;
        }

        $childSections = static::getList([
              'select' => ['ID'],
              'filter' => [
                  'PARENT_ID' => $data['ID'],
              ]
          ])->fetchAll();

        foreach ($childSections as $section) {
            try {
                static::delete($section['ID']);
            } catch (Exception $e) {
                $result->addError(new Main\Entity\FieldError(
                  $event->getEntity()->getField('ID'), static::getTableName() . '[' . $section['ID'] . '] - ' . $e->getMessage()
                ));
            }
        }
    }

    private static function cascadeDeleteElements(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID']) {
            return;
        }

        $elements = SmartseoFilterRuleTable::getList([
              'select' => ['ID'],
              'filter' => [
                  'SECTION_ID' => $data['ID']
              ]
          ])->fetchAll();

        foreach ($elements as $element) {
            try {
                SmartseoFilterRuleTable::delete($element['ID']);
            } catch (Exception $e) {
                $result->addError(new Main\Entity\FieldError(
                  $event->getEntity()->getField('ID'), SmartseoFilterRuleTable::getTableName() . '[' . $element['ID'] . '] - ' . $e->getMessage()
                ));
            }
        }
    }

    private static function cascadeUpdateDepthLevel(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');
        $self = static::getRowById($data['ID']);

        $childSections = static::getList([
              'select' => ['ID'],
              'filter' => [
                  'PARENT_ID' => $data['ID'],
              ]
          ])->fetchAll();

        foreach ($childSections as $section) {
            try {
                static::update($section['ID'], ['DEPTH_LEVEL' => $self['DEPTH_LEVEL'] + 1]);
            } catch (Exception $e) {
                $result->addError(new Main\Entity\FieldError(
                  $event->getEntity()->getField('ID'), static::getTableName() . '[' . $section['ID'] . '] - ' . $e->getMessage()
                ));
            }
        }
    }

}
