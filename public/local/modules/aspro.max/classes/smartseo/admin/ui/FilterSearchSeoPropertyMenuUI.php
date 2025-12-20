<?php

namespace Aspro\Max\Smartseo\Admin\UI;

use
    Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Traits\BitrixCoreEntity,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class FilterSearchSeoPropertyMenuUI extends SeoPropertyMenuUI
{
    const CATEGORY_FILTER_CONDITION_SEO_PROPERTY = 'filter_condition_seo_property';

    private $filterRuleId = null;
    private $filterConditionId = null;

    public function getMenuItems($controlId, $onlyCategory = [])
    {
        $this->category[self::CATEGORY_FILTER_CONDITION_SEO_PROPERTY] = $this->getFilterConditionMenuCategory($controlId);

        return parent::getMenuItems($controlId, $onlyCategory);
    }

    public function setFilterRuleId($filterRuleId)
    {
        $this->filterRuleId = $filterRuleId;

        return $this;
    }

    public function setFilterConditionId($filterConditionId)
    {
        $this->filterConditionId = $filterConditionId;

        return $this;
    }

    protected function getFilterConditionMenuCategory($controlId)
    {
        $seoTemplates = Smartseo\Models\SmartseoSeoTemplateTable::getDataSeoTemplates([
            Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_RULE => $this->filterRuleId,
            Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_CONDITION => $this->filterConditionId,
        ]);

        $_menu = [];
        foreach ($seoTemplates as $seoProperty) {
            $_menu[] = [
                'TEXT' => $seoProperty['TITLE'],
                'ONCLICK' => $this->getFunctionOnClick([
                    'id' => $controlId,
                    'value' => $seoProperty['TEMPLATE'],
                ]),
            ];
        }

        $category = [
            'TEXT' => Loc::getMessage('SMARTSEO_FSSP_CATEGORY'),
            'MENU' => $_menu,
        ];

        return $category;
    }
}
