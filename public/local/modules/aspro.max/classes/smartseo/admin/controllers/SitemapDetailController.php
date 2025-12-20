<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use
    Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\App\Controller,
    Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Aspro\Max\Smartseo\Admin\Helper,
    Aspro\Max\Smartseo\Admin\Grids as GridSmartseo,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class SitemapDetailController extends Controller
{
    const ALIAS = 'SITEMAP';

    use BitrixCoreEntity;

    public function getViewFolderName()
    {
        return 'sitemap';
    }

    public function actionDetail($id = null, $site_id)
    {
        $dataSite = $this->getDataSite($site_id);

        if(!$dataSite) {
           throw new \Exception('Site not found');
        }

        $dataSitemap = null;

        if ($id && (!$dataSitemap = Smartseo\Models\SmartseoSitemapTable::getRowById($id))) {
            throw new \Exception('Sitemap not found');
        }

        if($this->listenGridActions($dataSitemap)) {
            return;
        }

        $futureId = Smartseo\Models\SmartseoSitemapTable::getMaxID() + 1;
        $defaultSitemapFile = Smartseo\Models\SmartseoSitemapTable::DEFAULT_FOLDER_SITEMAP . 'sitemap-' . $futureId . '.xml';
        $defaultMainSitemapFile = Smartseo\Models\SmartseoSitemapTable::DEFAULT_INDEX_SITEMAP_FILE;

        $this->render('detail', [
            'dataSitemap' => $dataSitemap,
            'dataSite' => $dataSite,
            'alias' => self::ALIAS,
            'defaultProtocol' => $this->getProtocol(),
            'defaultSitemapFile' => $defaultSitemapFile,
            'defaultMainSitemapFile' => $defaultMainSitemapFile,
            'gridCondition' => GridSmartseo\SitemapConditionGrid::getInstance($dataSitemap['ID'])->getComponentParams(),
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

            if (!$data['ID']) {
                $sitemapId = $this->addSitemap($data);
            } else {
                $sitemapId = $this->updateSitemap($data['ID'], $data);
            }

            if($this->request->get('generate') === 'Y') {
                $this->generateSitemapFile($sitemapId);
            }

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        $redirectUrl = '';
        if ($this->request->get('action') == 'apply') {
            $redirectUrl = Helper::url('sitemap_detail/detail', [
                'id' => $sitemapId,
                'site_id' => $data['SITE_ID'],

            ]);
        } else {
            $redirectUrl = Helper::url('sitemap/list');
        }

        echo Json::encode(array_filter([
            'result' => true,
            'message' => 'Sitemap saved successfully',
            'redirect' => $redirectUrl,
        ]));
    }

    public function actionDelete()
    {
        if (!check_bitrix_sessid()) {
            echo Json::encode([
                'result' => false,
                'message' => 'Bitrix session not found',
            ]);

            return;
        }

        if(!$sitemapId = $this->request->get('id')) {
            echo Json::encode([
                'result' => false,
                'message' => 'Element ID expected'
            ]);

            return;
        }

        if (!$sitemap = Smartseo\Models\SmartseoSitemapTable::getRowById($sitemapId)) {
            throw new \Exception('Sitemap not found');
        }

        global $DB;

        try {
            $DB->StartTransaction();

            $this->deleteSitemap($sitemap['ID']);

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        echo Json::encode(array_filter([
            'result' => true,
            'massage' => 'Sitemap deleted successfully',
            'redirect' => Helper::url('sitemap/list'),
        ]));
    }

    private function getDataSite($siteId)
    {
        return $this->getSiteRow([], [
            '=LID' => $siteId,
        ], [
            'LID',
            'NAME',
            'SERVER_NAME',
            'DIR',
        ]);
    }

    private function getProtocol()
    {
        $isHttps = !empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS']);
        return $isHttps ? 'https://' : 'http://';
    }

    private function updateSitemap($id, array $data)
    {
        if (!Smartseo\Models\SmartseoSitemapTable::getByPrimary($id)) {
            throw new \Exception('Sitemap not found');
        }

        $sitemap = \Aspro\Max\Smartseo\Models\EO_SmartseoSitemap::wakeUp($id);

        $sitemap->setSiteId($data['SITE_ID']);
        $sitemap->setActive('Y');
        $sitemap->setName($data['NAME']);
        $sitemap->setProtocol($data['PROTOCOL']);
        $sitemap->setDomain($data['DOMAIN']);
        $sitemap->setSitemapFile($data['SITEMAP_FILE']);
        $sitemap->setInRobots($data['IN_ROBOTS']);
        $sitemap->setInIndexSitemap($data['IN_INDEX_SITEMAP']);
        $sitemap->setUpdateSitemapIndex($data['UPDATE_SITEMAP_INDEX']);
        $sitemap->setUpdateSitemapFile($data['UPDATE_SITEMAP_FILE']);

        if($data['IN_INDEX_SITEMAP'] && $data['IN_INDEX_SITEMAP'] === 'Y') {
            $sitemap->setIndexSitemapFile($data['INDEX_SITEMAP_FILE']);
        }

        $sitemap->setDateChange(new \Bitrix\Main\Type\DateTime());

        $result = $sitemap->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $sitemapId = $result->getId();

        return $sitemapId;
    }

    private function addSitemap(array $data)
    {
        $sitemap = new \Aspro\Max\Smartseo\Models\EO_SmartseoSitemap();
        $sitemap->setSiteId($data['SITE_ID']);
        $sitemap->setActive('Y');
        $sitemap->setName($data['NAME']);
        $sitemap->setProtocol($data['PROTOCOL']);
        $sitemap->setDomain($data['DOMAIN']);
        $sitemap->setSitemapFile($data['SITEMAP_FILE']);
        $sitemap->setInRobots($data['IN_ROBOTS']);
        $sitemap->setInIndexSitemap($data['IN_INDEX_SITEMAP']);
        $sitemap->setUpdateSitemapIndex($data['UPDATE_SITEMAP_INDEX']);
        $sitemap->setUpdateSitemapFile($data['UPDATE_SITEMAP_FILE']);

        if($data['IN_INDEX_SITEMAP'] && $data['IN_INDEX_SITEMAP'] === 'Y') {
            $sitemap->setIndexSitemapFile($data['INDEX_SITEMAP_FILE']);
        }

        $sitemap->setDateChange(new \Bitrix\Main\Type\DateTime());
        $sitemap->setDateCreate(new \Bitrix\Main\Type\DateTime());

        $result = $sitemap->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $sitemapId = $result->getId();

        return $sitemapId;
    }

    private function deleteSitemap($id)
    {
        $sitemapEngine = new Smartseo\Engines\SitemapEngine($id);

        if($sitemapEngine->hasErrors()) {
            throw new \Exception($sitemapEngine->getErrors());
        }

        $result = Smartseo\Models\SmartseoSitemapTable::delete($id);

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        $sitemapEngine->deleteSitemap();
    }

    private function generateSitemapFile($sitemapId)
    {
        $sitemapEngine = new Smartseo\Engines\SitemapEngine($sitemapId);

        if(!$sitemapEngine->update()) {
            throw new \Exception(implode('<br>', $sitemapEngine->getErrors()));

            return false;
        }

        return $sitemapEngine->getResult();
    }

    private function listenGridActions($dataSitemap = [])
    {
        if ($this->request->get('grid_action') && $this->request->get('grid_id')) {
            $gridId = $this->request['grid_id'];
            $gridAction = $this->request->get('grid_action');
            $gridSort = [];

            if($this->request->get('by') && $this->request->get('order')) {
               $gridSort[$this->request->get('by')] = $this->request->get('order');
            }

            $currentPage = false;
            if($gridAction == 'pagination') {
                $currentPage = 1;
                if($this->request->get($gridId)) {
                    $currentPage = (int)preg_replace('/page-/', '', $this->request->get($gridId));
                }
            }

            $result = [];
            switch ($gridId) {
                case 'grid_sitemap_conditions' :
                    $grid = new GridSmartseo\SitemapConditionGrid($dataSitemap['ID']);
                    $grid->addSort($gridSort);
                    $grid->addFilter();
                    $grid->setCurrentPage($currentPage);

                    $result = [
                        'gridCondition' => $grid->getComponentParams(true),
                    ];

                    break;

                default:
                    break;
            }

            $this->render('detail/grids/' . $gridId, $result);

            return true;
        }

        return false;
    }


}
