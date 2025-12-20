<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use Aspro\Max\Smartseo\Admin\App\Controller,
    Aspro\Max\Smartseo\Admin\Traits\FilterChainSectionTree,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

class FilterNavChainController extends Controller
{

    use FilterChainSectionTree;

    public function actionGetMenu($depth_level = 1, $section_id = 0)
    {
        if (!$this->request->isAjaxRequest()) {
            throw new \Exception(Loc::getMessage('SMARTSEO_INDEX__ERROR__NOT_AJAX'));
        }

        $treeSections = $this->getTreeSections($depth_level, $section_id);

        echo Json::encode($this->getMenuByTreeSections($treeSections));
    }

    protected function getMenuByTreeSections($treeSections)
    {
        $result = [];

        foreach ($treeSections as $treeSection) {
            $result[] = array_filter([
                'LINK' => Helper::url('filter_rules/list', ['section_id' => $treeSection['ID']]),
                'TEXT' => $treeSection['NAME'],
                'MENU' => $treeSection['CHILD']
                    ? $this->getMenuByTreeSections($treeSection['CHILD'])
                    : []
            ]);
        }

        if (!$result) {
            return [
                ['TEXT' => Loc::getMessage('SMARTSEO_INDEX__NOT_DATA')],
            ];
        }

        return $result;
    }

}
