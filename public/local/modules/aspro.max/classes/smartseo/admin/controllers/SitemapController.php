<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\App\Controller,
    Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SitemapController extends Controller
{

    use BitrixCoreEntity;

    public function getViewFolderName()
    {
        return 'sitemap';
    }

    public function actionList()
    {
        $order = [
            'by' => 'ID',
            'order' => 'DESC',
        ];

        if ($this->request->get('by') && $this->request->get('order')) {
            $order = [
                'by' => $this->request->get('by'),
                'order' => $this->request->get('order'),
            ];
        }

        $ui = new Smartseo\Admin\UI\SitemapAdminUI();

        $adminUiList = new \CAdminUiList($ui->getGridId(), new \CAdminUiSorting($ui->getGridId(), $order['by'], $order['order']));

        $adminUiList->AddAdminContextMenu($ui->getContextMenu(), $isShowExcel = false);

        $this->listenEditActionAdminUiList($adminUiList);

        $this->listenGroupActionAdminUiList($adminUiList);

        $adminUiList->AddHeaders($ui->getGridColumns());

        $filter = [];

        $adminUiList->AddFilter($ui->getFilterFields(), $filter);

        $rsData = new \CAdminUiResult(
          $this->getRsData([
              $order['by'] => $order['order']
            ], $filter), $ui->getGridId()
        );

        $rsData->NavStart();

        $adminUiList->SetNavigationParams($rsData);

        $this->render('list', [
            'adminUiList' => $adminUiList,
            'filterFields' => $ui->getFilterFields(),
            'rsData' => $rsData,
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
            'message' => 'Sitemap deactivated successfully'
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
            'message' => 'Sitemap activated successfully'
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
            'message' => 'Sitemap deleted successfully'
        ]);
    }

    public function actionGenerate()
    {
        if (!$this->validateParamsForMenuAction()) {
            echo Json::encode([
                'result' => false,
                'message' => $this->getErrors(),
            ]);
        }

        $id = $this->request->get('id');

        if (!$this->generateSitemapFile($id)) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        echo Json::encode([
            'result' => true,
            'message' => 'Sitemap generate successfully'
        ]);
    }

    private function getRsData($order = [], $filter = [])
    {
        $elements = $this->getElements($order, $filter);

        $result = new \CDBResult;

        $result->InitFromArray($elements);

        return $result;
    }

    private function getElements($order = [], $filter = [])
    {
        if ($filter['NAME']) {
            $filter['NAME'] = '%' . $filter['NAME'] . '%';
        }

        $rsRows = Smartseo\Models\SmartseoSitemapTable::getList([
              'select' => [
                  '*'
              ],
              'order' => $order,
              'filter' => $filter,
              'cache' => [
                  'ttl' => Smartseo\Models\SmartseoSitemapTable::getCacheTtl(),
              ]
        ]);

        $elements = [];

        while ($_row = $rsRows->fetch()) {
            $elements['E' . $_row['ID']] = $_row;
            $elements['E' . $_row['ID']]['TYPE'] = 'E';
        }

        return $elements;
    }

    private function listenGroupActionAdminUiList(\CAdminUiList &$adminUiList)
    {
        if (!$action = $this->request->get('action_button_' . $adminUiList->table_id)) {
            return;
        }

        if (!$mixidResultIds = $adminUiList->GroupAction()) {
            return;
        }

        $elementIds = [];
        $sectionIds = [];

        foreach ($mixidResultIds as $mixidId) {
            $_type = substr($mixidId, 0, 1);
            $_id = (int) substr($mixidId, 1);

            switch ($_type) {
                case 'E' :
                    $elementIds[] = $_id;
                    break;
            }
        }

        $elementCollection = null;
        if ($elementIds) {
            $elementCollection = Smartseo\Models\SmartseoSitemapTable::getList([
                  'filter' => [
                      'ID' => $elementIds
                  ]
              ])->fetchCollection();
        }

        switch ($action) {
            case 'deactivate' :
                $this->deactivateCollection($elementCollection);
                break;
            case 'activate' :
                $this->activateCollection($elementCollection);
                break;
            case 'delete' :
                $this->deleteElementByIds($elementIds);
                break;
        }

        if ($this->hasErrors()) {
            foreach ($this->getErrors() as $errorMessage) {
                $adminUiList->AddGroupError($errorMessage);
            }
        }
    }

    private function listenEditActionAdminUiList(\CAdminUiList &$adminUiList)
    {
        if ($adminUiList->EditAction()) {
            $mixidResults = $this->request->get('FIELDS');

            foreach ($mixidResults as $mixidId => $mixidData) {
                if (!$adminUiList->IsUpdated($mixidId)) {
                    continue;
                }

                $_type = substr($mixidId, 0, 1);
                $_id = (int) substr($mixidId, 1);

                switch ($_type) {
                    case 'E' :
                        Smartseo\Models\SmartseoSitemapTable::update($_id, array_filter($mixidData, function($code) {
                              return Smartseo\Models\SmartseoSitemapTable::hasMapField($code);
                          }, ARRAY_FILTER_USE_KEY));
                        break;
                }
            }
        }
    }

    private function deactivate($id)
    {
        if (!Smartseo\Models\SmartseoSitemapTable::getByPrimary($id)) {
            $this->addError('Sitemap not found');

            return false;
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoSitemap::wakeUp($id);
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
        if (!Smartseo\Models\SmartseoSitemapTable::getByPrimary($id)) {
            $this->addError('Sitemap not found');

            return false;
        }

        $entity = \Aspro\Max\Smartseo\Models\EO_SmartseoSitemap::wakeUp($id);
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
        if (!Smartseo\Models\SmartseoSitemapTable::getByPrimary($id)) {
            $this->addError('Sitemap not found');

            return false;
        }

        $sitemapEngine = new Smartseo\Engines\SitemapEngine($id);

        if ($sitemapEngine->hasErrors()) {
            $this->setErrors($sitemapEngine->getErrors());

            return false;
        }

        $result = Smartseo\Models\SmartseoSitemapTable::delete($id);

        if (!$result->isSuccess()) {
            $this->setErrors($result->getErrorMessages());

            return false;
        }

        $sitemapEngine->deleteSitemap();

        return true;
    }

    private function generateSitemapFile($sitemapId)
    {
        $sitemapEngine = new Smartseo\Engines\SitemapEngine($sitemapId);

        if(!$sitemapEngine->update() || $sitemapEngine->hasErrors()) {
            throw new \Exception(implode('<br>', $sitemapEngine->getErrors()));

            return false;
        }

        return $sitemapEngine->getResult();
    }

    private function deactivateCollection($collection)
    {
        if (!$collection) {
            return;
        }

        foreach ($collection as $item) {
            $item->setActive('N');
        }

        $collection->save();
    }

    private function activateCollection($collection)
    {
        if (!$collection) {
            return;
        }

        foreach ($collection as $item) {
            $item->setActive('Y');
        }

        $collection->save();
    }

    private function deleteElementByIds($elementIds)
    {
        global $DB;

        if (!$elementIds) {
            return;
        }

        try {
            $DB->StartTransaction();

            foreach ($elementIds as $elementId) {
                $sitemapEngine = new Smartseo\Engines\SitemapEngine($elementId);

                $result = Smartseo\Models\SmartseoSitemapTable::delete($elementId);

                if (!$result->isSuccess()) {
                    $this->setErrors($result->getErrorMessages());
                }

                $sitemapEngine->deleteSitemap();
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }
    }

    private function validateParamsForMenuAction()
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

}
