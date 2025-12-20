<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\App\Controller,
    Aspro\Max\Smartseo\Admin\Grids as GridSmartseo,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterSitemapController extends Controller
{

    const ALIAS = 'FILTER_SITEMAP';

    public function getViewFolderName()
    {
        return 'filter_sitemap';
    }

    public function actionDetail($id = null, $filter_rule_id)
    {
        if(!$filterRule = Smartseo\Models\SmartseoFilterRuleTable::getRowById($filter_rule_id)) {
            throw new \Exception('Parent element not found');
        }

        $data = null;
        if ($id && (!$data = Smartseo\Models\SmartseoFilterSitemapTable::getRowById($id))) {
            throw new \Exception('Relation not found');
        }

        $this->render('detail', [
            'alias' => self::ALIAS,
            'data' => $data,
            'listSitemap' => $this->getListSitemap($filterRule['SITE_ID']),
            'listFilterCondition' => $this->getListFilterCondition($filterRule['ID'], $data['FILTER_CONDITION_ID']),
            'listChangefreq' => Smartseo\Models\SmartseoFilterSitemapTable::getChangefreqParams(),
            'listPriority' => Smartseo\Models\SmartseoFilterSitemapTable::getPriorityParams(),
            'defaultChangefreq' => Smartseo\Models\SmartseoFilterSitemapTable::getMap()['CHANGEFREQ']['default_value'],
            'defaultPriority' => Smartseo\Models\SmartseoFilterSitemapTable::getMap()['PRIORITY']['default_value'],
            'gridId' => GridSmartseo\FilterRuleSitemapGrid::getInstance($filterRule['ID'])->getGridId(),
        ]);
    }

    public function actionUpdate()
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

            if(!$data['SITEMAP_ID'] && $data['FILTER_CONDITION_ID']) {
                $data['SITEMAP_ID'] = $this->addSitemap($data['FILTER_CONDITION_ID']);
            }

            if (!$data['ID']) {
                $id = $this->add($data);
            } else {
                $id = $this->update($data['ID'], $data);
            }

            if ($this->hasErrors()) {
                throw new \Exception(implode('<br>', $this->getErrors()));
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSitemap::wakeUp($id);
        $entity->fill(['FILTER_CONDITION', 'FILTER_CONDITION_ID']);

        echo Json::encode([
            'result' => true,
            'fields' => [
                'ID' => $id,
                'NAME' => $entity->getFilterCondition()->getName()
                    ? '[' . $entity->getFilterConditionId() . '] ' . $entity->getFilterCondition()->getName()
                    : '',
                'FILTER_CONDITION_ID' => $entity->getFilterConditionId(),
            ],
            'action' => $this->request->get('action'),
            'message' => 'Relation saved successfully',
        ]);
    }

    public function actionDeactivate()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->deactivate($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Relation deactivated successfully'
        ]);
    }

    public function actionActivate()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->activate($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Relation activated successfully'
        ]);
    }

    public function actionDelete()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->delete($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Relation deleted successfully'
        ]);
    }

    protected function validateParamsForMenuAction()
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        if (!check_bitrix_sessid()) {
            $this->addError('Bitrix session not found');

            return false;
        }

        if (!$this->request->get('id')) {
            $this->addError('ID param expected');

            return false;
        }

        return true;
    }

    private function getListSitemap($siteId = null)
    {
         $rows = Smartseo\Models\SmartseoSitemapTable::getList([
             'select' => [
                 'ID',
                 'NAME',
                 'SITE_ID',
             ],
             'filter' => array_filter([
               'SITE_ID' => $siteId
             ]),
             'order' => [
                 'ID' => 'ASC',
             ]
         ])->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[$row['ID']] = $row['NAME'] ? $row['NAME'] . ' (' . $row['SITE_ID'] . ')' : '';
        }

        return $result;
    }

    private function getListFilterCondition($filterRuleId, $currentFilterConditionId = null)
    {
        $filterSitemapRows = Smartseo\Models\SmartseoFilterSitemapTable::getList([
              'select' => [
                  'FILTER_CONDITION_ID',
              ],
              'filter' => array_filter([
                  'FILTER_CONDITION.FILTER_RULE.ID' => $filterRuleId,
                  '!FILTER_CONDITION_ID' => $currentFilterConditionId,
              ])
        ])->fetchAll();

        $existingIds = array_column($filterSitemapRows, 'FILTER_CONDITION_ID');

        $rows = Smartseo\Models\SmartseoFilterConditionTable::getList([
              'select' => [
                  'ID',
                  'NAME',
              ],
              'filter' => [
                  'FILTER_RULE.ID' => $filterRuleId,
                  '!ID' => $existingIds,
              ],
              'order' => [
                  'SORT' => 'ASC',
                  'ID' => 'ASC',
              ]
          ])->fetchAll();

        return array_column($rows, 'NAME', 'ID');
    }

    private function deactivate($id)
    {
        if (!Smartseo\Models\SmartseoFilterSitemapTable::getByPrimary($id)) {
            $this->addError('Relation not found');

            return false;
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSitemap::wakeUp($id);
        $entity->setActive('N');

        $result = $entity->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function activate($id)
    {
        if (!Smartseo\Models\SmartseoFilterSitemapTable::getByPrimary($id)) {
            $this->addError('Relation not found');

            return false;
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSitemap::wakeUp($id);
        $entity->setActive('Y');

        $result = $entity->save();

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        return $result->getId();
    }

    private function delete($id)
    {
        if (!Smartseo\Models\SmartseoFilterSitemapTable::getByPrimary($id)) {
            $this->addError('Relation not found');

            return false;
        }

        $result = Smartseo\Models\SmartseoFilterSitemapTable::delete($id);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());
        }

        return true;
    }

    private function update($id, array $data)
    {
        if (!Smartseo\Models\SmartseoFilterSitemapTable::getByPrimary($id)) {
            throw new \Exception('Element not found');
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSitemap::wakeUp($id);
        $entity->setActive($data['ACTIVE']);
        $entity->setFilterConditionId($data['FILTER_CONDITION_ID']);
        $entity->setSitemapId($data['SITEMAP_ID']);
        $entity->setChangefreq($data['CHANGEFREQ']);
        $entity->setPriority($data['PRIORITY']);

        $result = $entity->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $id = $result->getId();

        return $id;
    }

    private function add(array $data)
    {
        $entity = new \Aspro\Max\Smartseo\Models\EO_SmartseoFilterSitemap();
        $entity->setActive($data['ACTIVE']);
        $entity->setFilterConditionId($data['FILTER_CONDITION_ID']);
        $entity->setSitemapId($data['SITEMAP_ID']);
        $entity->setChangefreq($data['CHANGEFREQ']);
        $entity->setPriority($data['PRIORITY']);

        $result = $entity->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $id = $result->getId();

        return $id;
    }

    private function addSitemap($filterConditionId) {
        $dataScope = Smartseo\Models\SmartseoFilterConditionTable::getRow([
            'select' => [
              'REF_SITE_ID' => 'FILTER_RULE.SITE_ID',
              'REF_SERVER_NAME' => 'FILTER_RULE.SITE.SERVER_NAME',
            ],
            'filter' => [
                'ID' => $filterConditionId,
            ]
        ]);

        $isHttps = !empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']);
        $futureId = Smartseo\Models\SmartseoSitemapTable::getMaxID() + 1;

        $protocol = $isHttps ? 'https://' : 'http://';
        $sitemapFile = Smartseo\Models\SmartseoSitemapTable::DEFAULT_FOLDER_SITEMAP . 'sitemap_' . $futureId . '.xml';

        $sitemap = new \Aspro\Max\Smartseo\Models\EO_SmartseoSitemap();
        $sitemap->setSiteId($dataScope['REF_SITE_ID']);
        $sitemap->setActive('Y');
        $sitemap->setProtocol($protocol);
        $sitemap->setDomain($dataScope['REF_SERVER_NAME']);
        $sitemap->setSitemapFile($sitemapFile);
        $sitemap->setInRobots('N');
        $sitemap->setInIndexSitemap('N');
        $sitemap->setUpdateSitemapIndex('N');
        $sitemap->setUpdateSitemapFile('Y');

        $result = $sitemap->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $sitemapId = $result->getId();

        return $sitemapId;
    }

}
