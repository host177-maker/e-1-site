<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use
    Aspro\Max\Smartseo\Admin\Helper,
    Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Aspro\Max\Smartseo\Models\SmartseoFilterRuleTable,
    Aspro\Max\Smartseo\Models\SmartseoFilterIblockSectionsTable,
    \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SitemapAdminUI extends AbstractAdminUI
{

    private $siteItems = [];

    use BitrixCoreEntity;

    function __construct(){}

    public function getGridId()
    {
        return 'grid_sitemap';
    }

    public function getFilterFields()
    {
        $result = [
            [
                'id' => 'ID',
                'name' => Loc::getMessage('SMARTSEO_SM_UI_ID_FIELD'),
                'type' => 'number',
                'default' => true
            ],
            [
                'id' => 'SITE_ID',
                'name' => Loc::getMessage('SMARTSEO_SM_UI_SITE_ID_FIELD'),
                'type' => 'list',
                'items' => $this->getSiteItems(),
                'default' => true,
            ],
            [
                'id' => 'NAME',
                'name' => Loc::getMessage('SMARTSEO_SM_UI_NAME_FIELD'),
                'quickSearch' => '',
                'filterable' => '',
                'default' => true,
            ],
        ];

        return $result;
    }

    public function getContextMenu($urlParams = [])
    {
        $siteItems = $this->getSiteItems();

        $_menus = [];

        foreach ($siteItems as $siteId => $siteName) {
            $_menus[] = [
                'TEXT' => $siteName,
                'ICON' => '',
                'LINK' => Helper::url('sitemap_detail/detail', array_merge($urlParams, [
                    'site_id' => $siteId,
                ])),
            ];
        }

        return [
            [
                'TEXT' => Loc::getMessage('SMARTSEO_SM_UI_MENU_ADD_ELEMENT'),
                'MENU' => $_menus,
                'ONCLICK' => 'this.nextElementSibling.click(); return false;'

            ],
        ];
    }

    public function getGridColumns()
    {
        return [
            [
                'id' => 'SITE_ID',
                'title' => Loc::getMessage('SMARTSEO_SM_UI_SITE_ID_FIELD'),
                'content' => Loc::getMessage('SMARTSEO_SM_UI_SITE_ID_FIELD'),
                'sort' => 'SITE_ID',
                'default' => true,
            ],
            [
                'id' => 'NAME',
                'title' => Loc::getMessage('SMARTSEO_SM_UI_NAME_FIELD'),
                'content' => Loc::getMessage('SMARTSEO_SM_UI_NAME_FIELD'),
                'sort' => 'NAME',
                'width' => 200,
                'default' => true,
            ],
            [
                'id' => 'DATE_LAST_LAUNCH',
                'title' => Loc::getMessage('SMARTSEO_SM_UI_DATE_LAST_LAUNCH_FIELD'),
                'content' => Loc::getMessage('SMARTSEO_SM_UI_DATE_LAST_LAUNCH_FIELD'),
                'sort' => 'DATE_LAST_LAUNCH',
                'default' => true,
            ],
            [
                'id' => 'DATE_CREATE',
                'title' => Loc::getMessage('SMARTSEO_SM_UI_DATE_CREATE_FIELD'),
                'content' => Loc::getMessage('SMARTSEO_SM_UI_DATE_CREATE_FIELD'),
                'sort' => 'DATE_CREATE',
                'default' => false,
            ],
            [
                'id' => 'DATE_CHANGE',
                'title' => Loc::getMessage('SMARTSEO_SM_UI_DATE_CHANGE_FIELD'),
                'content' => Loc::getMessage('SMARTSEO_SM_UI_DATE_CHANGE_FIELD'),
                'sort' => 'DATE_CHANGE',
                'default' => false,
            ],
            [
                'id' => 'SITEMAP_ADDRESS',
                'title' => Loc::getMessage('SMARTSEO_SM_UI_SITEMAP_ADDRESS'),
                'content' => Loc::getMessage('SMARTSEO_SM_UI_SITEMAP_ADDRESS'),
                'sort' => '',
                'default' => false,
                'width' => 400,
            ],
            [
                'id' => 'ID',
                'title' => Loc::getMessage('SMARTSEO_SM_UI_ID_FIELD'),
                'content' => Loc::getMessage('SMARTSEO_SM_UI_ID_FIELD'),
                'sort' => 'ID',
                'default' => false,
            ],
        ];
    }

    private function getSiteItems($siteIds = [])
    {
        if($this->siteItems) {
            return $this->siteItems;
        }

        $rows = $this->getSiteList([
            'SORT' => 'ASC',
        ], array_filter([
              'LID' => $siteIds,
              'ACTIVE' => 'Y',
          ]), [
              'LID',
              'NAME'
          ]
        );

        $result = [];
        foreach ($rows as $row) {
            $result[$row['LID']] = "[$row[LID]] $row[NAME]";
        }

        $this->siteItems = $result;

        return $result;
    }

}
