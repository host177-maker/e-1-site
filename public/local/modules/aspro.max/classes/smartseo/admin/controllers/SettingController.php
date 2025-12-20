<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use
    Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\App\Controller,
    Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SettingController extends Controller
{
    const ALIAS = 'SETTING';

    use BitrixCoreEntity;

    public function getViewFolderName()
    {
        return 'settings';
    }

    public function actionGeneral()
    {
        $data = Smartseo\Models\SmartseoSettingTable::getGeneralSettings();

        $this->render('general', [
            'alias' => self::ALIAS,
            'data' => $data,
            'isCatalogModule' => $this->isCatalogModule,
        ]);
    }

    public function actionSites()
    {
        $data = Smartseo\Models\SmartseoSettingTable::getSiteSettings();

        $this->render('sites', [
            'alias' => self::ALIAS,
            'listSites' => $this->getSiteList([
                'SORT' => 'ASC'
            ], [
                'ACTIVE' => 'Y',
            ]),
            'listPropertyListOptions' => Smartseo\Models\SmartseoSettingTable::getPropertyListReplaceOptions(),
            'listPropertyElementOptions' => Smartseo\Models\SmartseoSettingTable::getPropertyElementReplaceOptions(),
            'listPropertyDirectoryOptions' => Smartseo\Models\SmartseoSettingTable::getPropertyDirectoryReplaceOptions(),
            'data' => $data
        ]);
    }

    public function actionUpdateGeneralSettings()
    {
        if (!check_bitrix_sessid()) {
            echo Json::encode([
                'result' => false,
                'message' => 'Bitrix session not found',
            ]);

            return;
        }

        if (!$this->request->get(self::ALIAS) || !is_array($this->request->get(self::ALIAS))) {
            echo Json::encode([
                'result' => false,
                'message' => 'No update data found'
            ]);
            return;
        }

        $data = $this->request->get(self::ALIAS);

        global $DB;

        try {
            $DB->StartTransaction();

            $this->updateGeneralSettings($data);

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        echo Json::encode(array_filter([
            'result' => true,
            'massage' => 'Settings saved successfully',
            'redirect' => Smartseo\Admin\Helper::url('setting/general')
        ]));
    }

    public function actionUpdateSiteSettings()
    {
        if (!check_bitrix_sessid()) {
            echo Json::encode([
                'result' => false,
                'message' => 'Bitrix session not found',
            ]);

            return;
        }

        if (!$this->request->get(self::ALIAS) || !is_array($this->request->get(self::ALIAS))) {
            echo Json::encode([
                'result' => false,
                'message' => 'No update data found'
            ]);
            return;
        }

        $data = $this->request->get(self::ALIAS);

        if(!$data['SITE_ID']) {
             echo Json::encode([
                'result' => false,
                'message' => 'Expected site value (SITE_LID)'
            ]);
            return;
        }

        global $DB;

        try {
            $DB->StartTransaction();

            $this->updateSiteSettings($data);

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        echo Json::encode(array_filter([
            'result' => true,
            'massage' => 'Settings saved successfully',
        ]));
    }

    public function actionGetMenuFilterRuleName()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if (!$controlId = $this->request->get('control')) {
            echo Json::encode([
                'result' => true,
                'menu' => [
                    ['TEXT' => Loc::getMessage('SMARTSEO_INDEX__NOT_DATA2')]
                ]
            ]);

            return;
        }

        $menuUiSetting = new Smartseo\Admin\UI\SettingMenuUI();

        echo Json::encode([
            'result' => true,
            'menu' => $menuUiSetting->getMenuItems($controlId, $menuUiSetting::CATEGORY_FILTER_RULE_NAME)
        ]);
    }


    public function actionGetMenuUrlTemplate()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if (!$controlId = $this->request->get('control')) {
            echo Json::encode([
                'result' => true,
                'menu' => [
                    ['TEXT' => Loc::getMessage('SMARTSEO_INDEX__NOT_DATA2')]
                ]
            ]);

            return;
        }

        $category = $this->request->get('category');

        $menuUiSetting = new Smartseo\Admin\UI\SettingMenuUI();

        echo Json::encode([
            'result' => true,
            'menu' => $menuUiSetting->getMenuItems($controlId, $category)
        ]);
    }

    public function actionClearCache()
    {
        try {
            Smartseo\Models\SmartseoFilterConditionTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoFilterConditionUrlTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoFilterIblockSectionsTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoFilterRuleTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoFilterSearchTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoFilterSectionTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoFilterSitemapTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoFilterTagTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoNoindexConditionTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoNoindexIblockSectionsTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoNoindexRuleTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoNoindexUrlTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoSeoTemplateTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoSeoTextIblockSectionsTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoSeoTextPropertyTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoSeoTextTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoSettingTable::getEntity()->cleanCache();
            Smartseo\Models\SmartseoSitemapTable::getEntity()->cleanCache();

            \Bitrix\Iblock\SectionTable::getEntity()->cleanCache();
            \Bitrix\Iblock\PropertyTable::getEntity()->cleanCache();

            echo Json::encode([
                'result' => true,
            ]);
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function updateGeneralSettings(array $data)
    {
        $this->modifireInputData($data, Smartseo\Models\SmartseoSettingTable::getMapModuleEntityFields());

        $settingCollection = Smartseo\Models\SmartseoSettingTable::getList([
              'select' => [
                  'CODE',
                  'VALUE'
               ],
              'filter' => [
                  '==SITE_ID' => '',
              ],
          ])->fetchCollection();

        foreach ($settingCollection as $setting) {
           $_code = $setting->getCode();

           if(isset($data[$_code])) {
               $setting->setValue($data[$_code]);
               unset($data[$_code]);
           }
        }

        if($data) {
            $this->addSettings($data);
        }

        $result = $settingCollection->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

    private function updateSiteSettings(array $data)
    {
        $this->modifireInputData($data, Smartseo\Models\SmartseoSettingTable::getMapSiteEntityFields());

        $siteId = null;
        if($data['SITE_ID']) {
            $siteId = $data['SITE_ID'];
        }

        $settingCollection = Smartseo\Models\SmartseoSettingTable::getList([
              'select' => [
                  'CODE',
                  'VALUE'
               ],
              'filter' => [
                  'SITE_ID' => $siteId,
              ],
          ])->fetchCollection();

        foreach ($settingCollection as $setting) {
           $_code = $setting->getCode();

           if(isset($data[$_code])) {
               $setting->setValue($data[$_code]);
               $setting->setSiteId($siteId);
               unset($data[$_code]);
           }
        }

        if($data) {
            $this->addSettings($data);
        }

        $result = $settingCollection->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

    private function addSettings(array $data)
    {
        $settingCollection = new \Aspro\Max\Smartseo\Models\EO_SmartseoSetting_Collection();

        $siteId = null;
        if($data['SITE_ID']) {
            $siteId = $data['SITE_ID'];
            unset($data['SITE_ID']);
        }

        foreach ($data as $code => $value) {
            $setting = new \Aspro\Max\Smartseo\Models\EO_SmartseoSetting();

            $setting->setCode($code);
            $setting->setValue($value);
            $setting->setSiteId($siteId);

            $settingCollection[] = $setting;
        }

        $result = $settingCollection->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

    private function modifireInputData(&$data, array $mapFields)
    {
        foreach ($mapFields as $code => $field) {
            if(!isset($data[$code])) {
                switch ($field['data_type']) {
                    case 'boolean':
                        $data[$code] = 'N';

                        break;

                    default:
                        break;
                }
            }

            if(isset($data[$code]) && !$data[$code]) {
                 switch ($field['data_type']) {
                    case 'integer':
                        $data[$code] = 0;

                        break;

                    default:
                        break;
                }
            }

            if(isset($data[$code]) && $data[$code]) {
                 switch ($field['data_type']) {
                    case 'integer':
                        $data[$code] = (int)$data[$code];

                        break;

                    default:
                        break;
                }
            }
        }
    }
}
