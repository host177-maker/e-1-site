<?php

namespace Aspro\Max\Smartseo\Models;

use Aspro\Max\Smartseo\Entity as SmartseoEntity,
    Aspro\Max\Smartseo\Admin\Settings\SettingSmartseo,
    Bitrix\Main,
    Bitrix\Main\Localization\Loc,
	Bitrix\Main\ORM\Data\DataManager,
	Bitrix\Main\ORM\Fields\BooleanField,
	Bitrix\Main\ORM\Fields\DatetimeField,
	Bitrix\Main\ORM\Fields\IntegerField,
	Bitrix\Main\ORM\Fields\StringField,
	Bitrix\Main\ORM\Fields\Validators\LengthValidator;

Loc::loadMessages(__FILE__);

/**
 * Class SmartseoSitemapTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE bool optional default 'Y'
 * <li> SITE_ID string(4) mandatory
 * <li> NAME string(255) optional
 * <li> DATE_CREATE datetime optional
 * <li> DATE_CHANGE datetime optional
 * <li> DATE_LAST_LAUNCH datetime optional
 * <li> PROTOCOL string(8) mandatory
 * <li> DOMAIN string(255) mandatory
 * <li> SITEMAP_FILE string(255) mandatory
 * <li> SITEMAP_LAST_FILE string(255) mandatory
 * <li> SITEMAP_URL string(255) mandatory
 * <li> SITEMAP_LAST_URL string(255) mandatory
 * <li> IN_ROBOTS bool optional default 'N'
 * <li> IN_INDEX_SITEMAP bool optional default 'N'
 * <li> INDEX_SITEMAP_FILE string(255) optional
 * <li> UPDATE_SITEMAP_INDEX bool ('N', 'Y') optional default 'Y'
 * <li> UPDATE_SITEMAP_FILE bool ('N', 'Y') optional default 'Y'
 * </ul>
 *
 * @package Bitrix\Aspro
 * */
class SmartseoSitemapTable extends Main\Entity\DataManager
{

    const DEFAULT_FOLDER_SITEMAP = 'aspro-sitemap/';
    const DEFAULT_INDEX_SITEMAP_FILE = 'sitemap.xml';
    const DEFAULT_CACHE_TTL = 0;

    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'b_aspro_max_smartseo_sitemap';
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
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_ID_FIELD')
				]
			),
			new BooleanField(
				'ACTIVE',
				[
					'values' => array('N', 'Y'),
					'default' => 'Y',
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_ACTIVE_FIELD')
				]
			),
			new StringField(
				'SITE_ID',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateSiteId'],
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_SITE_ID_FIELD')
				]
			),
			new StringField(
				'NAME',
				[
					'validation' => [__CLASS__, 'validateName'],
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_NAME_FIELD')
				]
			),
			new DatetimeField(
				'DATE_CREATE',
				[
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_DATE_CREATE_FIELD'),
                    'default' => new Main\Type\DateTime,
				]
			),
			new DatetimeField(
				'DATE_CHANGE',
				[
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_DATE_CHANGE_FIELD'),
                    'default' => new Main\Type\DateTime,
				]
			),
			new DatetimeField(
				'DATE_LAST_LAUNCH',
				[
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_DATE_LAST_LAUNCH_FIELD'),
				]
			),
			new StringField(
				'PROTOCOL',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateProtocol'],
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_PROTOCOL_FIELD')
				]
			),
			new StringField(
				'DOMAIN',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateDomain'],
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_DOMAIN_FIELD')
				]
			),
			new StringField(
				'SITEMAP_FILE',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateSitemapFile'],
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_SITEMAP_FILE_FIELD')
				]
			),
            new StringField(
				'SITEMAP_LAST_FILE',
				[
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_SITEMAP_LAST_FILE_FIELD')
				]
			),
			new StringField(
				'SITEMAP_URL',
				[
					'validation' => [__CLASS__, 'validateSitemapUrl'],
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_SITEMAP_URL_FIELD')
				]
			),
            new StringField(
				'SITEMAP_LAST_URL',
				[
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_SITEMAP_LAST_URL_FIELD')
				]
			),
			new BooleanField(
				'IN_ROBOTS',
				[
					'values' => array('N', 'Y'),
					'default' => 'N',
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_IN_ROBOTS_FIELD')
				]
			),
			new BooleanField(
				'IN_INDEX_SITEMAP',
				[
					'values' => array('N', 'Y'),
					'default' => 'N',
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_IN_INDEX_SITEMAP_FIELD')
				]
			),
			new StringField(
				'INDEX_SITEMAP_FILE',
				[
					'validation' => [__CLASS__, 'validateMainSitemapFile'],
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_INDEX_SITEMAP_FILE_FIELD')
				]
			),
            new BooleanField(
				'UPDATE_SITEMAP_INDEX',
				[
					'values' => array('N', 'Y'),
					'default' => 'Y',
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_UPDATE_SITEMAP_INDEX_FIELD')
				]
			),
			new BooleanField(
				'UPDATE_SITEMAP_FILE',
				[
					'values' => array('N', 'Y'),
					'default' => 'Y',
					'title' => Loc::getMessage('SMARTSEO_SITEMAP_ENTITY_UPDATE_SITEMAP_FILE_FIELD')
				]
			),
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
     * Returns validators for SITE_ID field.
     *
     * @return array
     */
    public static function validateSiteId()
    {
        return array(
            new Main\Entity\Validator\Length(null, 4),
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
     * Returns validators for PROTOCOL field.
     *
     * @return array
     */
    public static function validateProtocol()
    {
        return array(
            new Main\Entity\Validator\Length(null, 8),
        );
    }

    /**
     * Returns validators for DOMAIN field.
     *
     * @return array
     */
    public static function validateDomain()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

    /**
     * Returns validators for SITEMAP_FILE field.
     *
     * @return array
     */
    public static function validateSitemapFile()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
            function($file, $primary, $fields) {
                if (!preg_match('|^.+(\.xml)$|', $file)) {
                    return Loc::getMessage('SMARTSEO_SITEMAP_VALIDATE_SITEMAP_EXT');
                }

                if ($primary) {
                    return true;
                }

                $site = \Bitrix\Main\SiteTable::getRow([
                      'select' => [
                          'DIR',
                          'DOC_ROOT',
                      ],
                      'filter' => [
                          '=LID' => $fields['SITE_ID']
                      ]
                ]);

                $siteDir = implode('/', array_filter([
                    $site['DOC_ROOT'] ?: $_SERVER['DOCUMENT_ROOT'],
                    $site['DIR']])
                );

                $filePath = preg_replace('|([/]+)|s', '/', $siteDir . $file);

                if (file_exists($filePath)) {
                    return Loc::getMessage('SMARTSEO_SITEMAP_VALIDATE_SITEMAP_EXIST', [
                          '#PATH#' => $filePath,
                    ]);
                }

                return true;
            }
        );
    }

    /**
     * Returns validators for SITEMAP_URL field.
     *
     * @return array
     */
    public static function validateSitemapUrl()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
            function($fileUrl, $primary, $fields) {
                if (!preg_match('|^.+(\.xml)$|', $fileUrl)) {
                    return Loc::getMessage('SMARTSEO_SITEMAP_VALIDATE_SITEMAP_URL_EXT');
                }

                return true;
            }
        );
    }

    /**
     * Returns validators for INDEX_SITEMAP_FILE field.
     *
     * @return array
     */
    public static function validateMainSitemapFile()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
            function($file, $primary, $fields) {
                if (!preg_match('|^.+(\.xml)$|', $file)) {
                    return Loc::getMessage('SMARTSEO_SITEMAP_VALIDATE_SITEMAP_EXT');
                }

                if (!isset($fields['IN_INDEX_SITEMAP']) || $fields['IN_INDEX_SITEMAP'] === 'N') {
                    return true;
                }

                $site = \Bitrix\Main\SiteTable::getRow([
                      'select' => [
                          'DIR',
                          'DOC_ROOT',
                      ],
                      'filter' => [
                          '=LID' => $fields['SITE_ID']
                      ]
                ]);

                $siteDir = implode('/', array_filter([
                    $site['DOC_ROOT'] ?: $_SERVER['DOCUMENT_ROOT'],
                    $site['DIR']])
                );

                $filePath = preg_replace('|([/]+)|s', '/', $siteDir . $file);

                if(!self::checkedSitemapIndex($filePath)){
                    return Loc::getMessage('SMARTSEO_SITEMAP_VALIDATE_MAIN_SITEMAP_NOT_SITEMAPINDEX');
                }

                return true;
            },
        );
    }

    public static function getCacheTtl()
    {
        $ttl = SettingSmartseo::getInstance()->getCacheTable();

        return $ttl ?: self::DEFAULT_CACHE_TTL;
    }

    public static function getMaxID()
    {
        $result = static::getRow([
              'select' => [
                  new Main\Entity\ExpressionField('MAX', 'MAX(%s)', ['ID'])
              ],
        ]);

        return $result['MAX'];
    }

    public static function OnAfterDelete(Main\Entity\Event $event)
    {
        $result = new Main\Entity\EventResult;

        static::cascadeDeleteFilterSitemap($event, $result);

        return $result;
    }

    private static function cascadeDeleteFilterSitemap(Main\Entity\Event $event, Main\Entity\EventResult &$result)
    {
        $data = $event->getParameter('primary');

        if (!$data['ID'] || !intval($data['ID'])) {
            return;
        }

        $sql = 'DELETE FROM ' . SmartseoFilterSitemapTable::getTableName()
          . ' WHERE SITEMAP_ID = ' . (int) $data['ID'];

        try {

            $connection = \Bitrix\Main\Application::getConnection();
            $connection->queryExecute($sql);
        } catch (Exception $e) {
            $result->addError(new Main\Entity\FieldError(
              $event->getEntity()->getField('ID'), SmartseoFilterSitemapTable::getTableName() . ' SITEMAP_ID [' . $data['ID'] . '] - ' . $e->getMessage()
            ));
        }
    }

    private static function checkedSitemapIndex($filePath)
    {
        if (!file_exists($filePath)) {
            return true;
        }

        $xml = new \SimpleXMLElement(file_get_contents($filePath));

        if($xml->getName() == 'sitemapindex') {
            return true;
        }

        return false;
    }

}
