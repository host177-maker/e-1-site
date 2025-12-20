<?php

namespace Aspro\Max\Smartseo\Admin\Controllers;

use Aspro\Max\Smartseo\Models\SmartseoFilterSectionTable,
    Aspro\Max\Smartseo\Entity\FilterSection,
    Aspro\Max\Smartseo\Admin\App\Controller,
    Aspro\Max\Smartseo\Admin\Traits\FilterChainSectionTree,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Web\Json,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterSectionController extends Controller
{

    const DATA_FORM_NAME = 'FILTER_SECTION';

    use FilterChainSectionTree;

    public function getViewFolderName()
    {
        return 'filter_section';
    }

    public function actionDetail($id = null, $parent_section_id = null)
    {
        $filterSection = null;

        if ($id && (!$filterSection = SmartseoFilterSectionTable::getRowById($id))) {
            throw new \Exception('Section not found');
        }

        $this->render('detail', [
            'dataFormName' => self::DATA_FORM_NAME,
            'dataFilterSection' => $filterSection,
            'parentSectionId' => $filterSection['PARENT_ID']
              ?: $parent_section_id,
            'chainSections' => $this->getChainSections($filterSection['PARENT_ID']
              ?: $parent_section_id),
            'listSections' => $this->getSectionList(),
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

        if (!$this->request->get(self::DATA_FORM_NAME) || !is_array($this->request->get(self::DATA_FORM_NAME))) {
            echo Json::encode([
                'result' => false,
                'message' => 'No update data found'
            ]);
            return;
        }

        $data = $this->request->get(self::DATA_FORM_NAME);

        if (!$data['ID']) {
            $id = $this->addSection($data);
        } else {
            $id = $this->updateSection($data['ID'], $data);
        }

        if($this->hasErrors()) {
            throw new \Exception(implode('<br>', $this->getErrors()));
        }

        $redirectUrl = '';
        if ($this->request->get('action') == 'apply') {
            $redirectUrl = Helper::url('filter_section/detail', [
              'id' => $id,
            ]);
        } else {
            $redirectUrl = Helper::url('filter_rules/list', [
              'section_id' => $data['PARENT_ID'],
            ]);
        }

        echo Json::encode(array_filter([
            'result' => true,
            'massage' => 'Section saved successfully',
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

        if(!$sectionId = $this->request->get('id')) {
            echo Json::encode([
                'result' => false,
                'message' => 'Section ID expected'
            ]);

            return;
        }

        if (!$section = SmartseoFilterSectionTable::getRowById($sectionId)) {
            throw new \Exception('Section not found');
        }

        global $DB;

        try {
            $DB->StartTransaction();

            $this->deleteSection($section['ID']);

            $DB->Commit();
        } catch (Exception $e) {
            $DB->Rollback();
        }

        echo Json::encode(array_filter([
            'result' => true,
            'massage' => 'Section deleted successfully',
            'redirect' => Helper::url('filter_rules/list', [
                'section_id' => $section['PARENT_ID'],
            ]),
        ]));
    }

    private function getSectionList()
    {
        $treeSections = $this->getTreeSections();

        return $this->getRowsByTreeSections($treeSections);
    }

    private function addSection(array $data)
    {
        if (!SmartseoFilterSectionTable::getByPrimary($data['PARENT_ID'])) {
            $this->addError('Parent section not found');

            return false;
        }

        $newFilterSection = new FilterSection();
        $newFilterSection->setName($data['NAME']);
        $newFilterSection->setDescription($data['DESCRIPTION']);
        $newFilterSection->setSort($data['SORT']);
        $newFilterSection->setParentId($data['PARENT_ID']);

        $result = $newFilterSection->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        return $result->getId();
    }

    private function updateSection($id, array $data)
    {
        if (!SmartseoFilterSectionTable::getByPrimary($id)) {
            $this->addError('Section not found');

            return false;
        }

        $filterSection = FilterSection::wakeUp($id);
        $filterSection->setName($data['NAME']);
        $filterSection->setDescription($data['DESCRIPTION']);
        $filterSection->setDateChange(new \Bitrix\Main\Type\DateTime());
        $filterSection->setSort($data['SORT']);
        $filterSection->setParentId($data['PARENT_ID']);

        $result = $filterSection->save();

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }

        return $result->getId();
    }

    private function deleteSection($id)
    {
        $result = SmartseoFilterSectionTable::delete($id);

        if (!$result->isSuccess()) {
            throw new \Exception(implode('<br>', $result->getErrorMessages()));
        }
    }

}
