<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Entity as SmartseoEntity,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Aspro\Max\Smartseo\Template,
    Bitrix\Main,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoSeoTemplateTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> CODE string(255) mandatory
 * <li> TEMPLATE string mandatory
 * <li> ENTITY_TYPE string(2) mandatory
 * <li> ENTITY_ID int mandatory
 * </ul>
 *
 * @package Aspro\Max\Smartseo
 * */
class SmartseoSeoTemplateTable extends Main\Entity\DataManager
{
    const DEFAULT_CACHE_TTL = 0;

    const ENTITY_TYPE_FILTER_RULE = 'FR';
    const ENTITY_TYPE_FILTER_CONDITION = 'FC';
    const ENTITY_TYPE_FILTER_URL = 'FU';

    public static function getObjectClass()
    {
        return SmartseoEntity\SeoTemplate::class;
    }

    public static function getCollectionClass()
    {
        return SmartseoEntity\SeoTemplates::class;
    }

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_aspro_max_smartseo_seo_template';
    }

    public static function getMapEntityCode()
    {
        return [
            'META_TITLE' => [
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_PROPERTY_META_TITLE'),
                'sort' => 1
            ],
            'META_KEYWORDS' => [
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_PROPERTY_META_KEYWORDS'),
                'sort' => 2
            ],
            'META_DESCRIPTION' => [
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_PROPERTY_META_DESCRIPTION'),
                'sort' => 3
            ],
            'PAGE_TITLE' => [
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_PROPERTY_PAGE_TITLE'),
                'sort' => 4
            ],
            'BREADCRUMB_PAGE' => [
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_PROPERTY_BREADCRUMB_PAGE'),
                'sort' => 5
            ],
            'TOP_DESCRIPTION' => [
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_PROPERTY_TOP_DESCRIPTION'),
                'sort' => 6
            ],
            'BOTTOM_DESCRIPTION' => [
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_PROPERTY_BOTTOM_DESCRIPTION'),
                'sort' => 7
            ],
            'ADDITIONAL_DESCRIPTION' => [
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_PROPERTY_ADDITIONAL_DESCRIPTION'),
                'sort' => 8
            ]
        ];
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
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_ENTITY_ID_FIELD'),
            ),
            'CODE' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateCode'),
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_ENTITY_CODE_FIELD'),
            ),
            'TEMPLATE' => array(
                'data_type' => 'text',
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_ENTITY_TEMPLATE_FIELD'),
            ),
            'ENTITY_TYPE' => array(
                'data_type' => 'string',
                'required' => true,
                'validation' => array(__CLASS__, 'validateEntityType'),
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_ENTITY_ENTITY_TYPE_FIELD'),
            ),
            'ENTITY_ID' => array(
                'data_type' => 'integer',
                'required' => true,
                'title' => Loc::getMessage('SMARTSEO_SEO_TEMPLATE_ENTITY_ENTITY_ID_FIELD'),
            ),
        );
    }

    /**
     * Returns validators for CODE field.
     *
     * @return array
     */
    public static function validateCode()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
            function($value) {
                if(!in_array($value, array_keys(static::getMapEntityCode()))) {
                    return Loc::getMessage('SMARTSEO_SEO_TEMPLATE_VALIDATE_CODE', [
                        '#CODE#' => $value,
                    ]);
                }

                return true;
            }
        );
    }

    /**
     * Returns validators for ENTITY_TYPE field.
     *
     * @return array
     */
    public static function validateEntityType()
    {
        return array(
            new Main\Entity\Validator\Length(null, 2),
        );
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

    public static function getDataByEntity($entityId, $entityType)
    {
        $map = static::getMapEntityCode();

        $rows = self::getList([
            'select' => [
                'CODE',
                'TEMPLATE'
             ],
            'filter' => [
                'ENTITY_ID' => $entityId,
                'ENTITY_TYPE' => $entityType,
            ],
            'cache' => [
                'ttl' => self::getCacheTtl(),
            ]
        ])->fetchAll();

        $result = [];

        foreach ($rows as $row) {
            $result[$row['CODE']] = array_filter([
                'TEMPLATE' => $row['TEMPLATE'],
                'TITLE' => $map[$row['CODE']]['title']
            ]);
        }

        return $result;
    }

    public static function updateSeoTemplates($entityId, $entityType, $data)
    {
        $seoTemplates = self::getList([
           'filter' => [
             'ENTITY_ID' => $entityId,
             '=ENTITY_TYPE' => $entityType,
           ]
         ])->fetchCollection();

        $modifiedData = [];
        foreach ($data as $code => $value) {
            $_code = preg_replace('|(_suffix_*.+)|', '', $code);
            $modifiedData[$_code] = $value;
        }

        foreach ($seoTemplates as $seoTemplate) {
            $_code = $seoTemplate->getCode();

            if(isset($modifiedData[$_code])) {
                $seoTemplate->setTemplate($modifiedData[$_code]);
                unset($modifiedData[$_code]);
            } else {
                self::delete($seoTemplate->getId());
            }
        }

        if($modifiedData) {
            self::addSeoTemplates($entityId, $entityType, $modifiedData);
        }

        $result = $seoTemplates->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

    public static function addSeoTemplates($entityId, $entityType, $data)
    {
        $seoTemplateCollection = new SmartseoEntity\SeoTemplates();

        foreach ($data as $code => $value) {
            $_code = preg_replace('|(_suffix_*.+)|', '', $code);

            if(!$value) {
                continue;
            }

            $newSeoTemplate = new SmartseoEntity\SeoTemplate();
            $newSeoTemplate->setEntityId($entityId);
            $newSeoTemplate->setEntityType($entityType);
            $newSeoTemplate->setCode($_code);
            $newSeoTemplate->setTemplate($value);

            $seoTemplateCollection[] = $newSeoTemplate;
        }

        $result = $seoTemplateCollection->save(true);

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

    public static function getDataSeoTemplates(array $params = [])
    {
        if(!$params) {
            return [];
        }

        $map = static::getMapEntityCode();

        $params = array_filter($params);

        $filter = [];

        foreach ($params as $entityType => $entityId) {
            if(!in_array($entityType, self::getEntityTypes())) {

              continue;
            }

            $filter[] = [
                'ENTITY_ID' => $entityId,
                'ENTITY_TYPE' => $entityType
            ];
        }

        $rows = self::getList([
              'select' => [
                  'CODE',
                  'TEMPLATE',
                  'ENTITY_TYPE',
              ],
              'filter' => array_merge([
                  'LOGIC' => 'OR',
              ], $filter),
              'cache' => [
                  'ttl' => self::getCacheTtl(),
              ]
          ])->fetchAll();

        $allResults = [];
        foreach ($rows as $row) {
            if(!$row['TEMPLATE']) {
                continue;
            }

            $allResults[$row['ENTITY_TYPE']][$row['CODE']] = $row;
            $allResults[$row['ENTITY_TYPE']][$row['CODE']]['TITLE'] = $map[$row['CODE']]['title'];
        }

        $result = null;
        foreach (self::getEntityTypes() as $entityType) {
            if(!$allResults[$entityType]) {
                continue;
            }

            if(!$result) {
                $result = $allResults[$entityType];
            } else {
                $result = array_merge($result, $allResults[$entityType]);
            }
        }

        if($result) {
            usort($result, function($a, $b) use($map){
                return ($map[$a['CODE']]['sort'] > $map[$b['CODE']]['sort']);
            });
        }

        return $result;
    }

    private static function getEntityTypes()
    {
        return [
            self::ENTITY_TYPE_FILTER_RULE,
            self::ENTITY_TYPE_FILTER_CONDITION,
            self::ENTITY_TYPE_FILTER_URL
        ];
    }

}