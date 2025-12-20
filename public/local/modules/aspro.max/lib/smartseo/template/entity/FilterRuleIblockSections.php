<?php

namespace Aspro\Max\Smartseo\Template\Entity;

use Aspro\Max\Smartseo,
    Bitrix\Iblock;

class FilterRuleIblockSections extends Iblock\Template\Entity\Base
{

    /** @var \Bitrix\Iblock\Template\Entity\SectionProperty */
    protected $property = null;

    /** @var \Bitrix\Iblock\Template\Entity\Section */
    protected $parent = null;

    /** @var \Bitrix\Iblock\Template\Entity\SectionPath */
    protected $sectionPath = null;
    private $limit = 1;

    public function __construct($filterRuleId, $limit = 1)
    {
        parent::__construct($filterRuleId);

        $this->limit = $limit;

        $this->fieldMap = [
            'code' => 'CODE',
            'name' => 'NAME',
            'previewtext' => 'DESCRIPTION',
            'detailtext' => 'DESCRIPTION',
            'ID' => 'ID',
            'IBLOCK_ID' => 'IBLOCK_ID',
            'IBLOCK_SECTION_ID' => 'IBLOCK_SECTION_ID',
        ];
    }

    public function resolve($entity)
    {
        switch ($entity) {
            case 'property':
                if (!$this->property && $this->loadFromDatabase()) {
                    if ($this->fields['IBLOCK_ID'] > 0) {
                        $this->property = new Iblock\Template\Entity\SectionProperty($this->fields['ID']);
                        $this->property->setIblockId($this->fields['IBLOCK_ID']);
                    }
                }

                if ($this->property) {
                    return $this->property;
                }

                break;

            case 'parent':
                if (!$this->parent && $this->loadFromDatabase()) {
                    if ($this->fields['IBLOCK_SECTION_ID'] > 0) {
                        $this->parent = new Iblock\Template\Entity\Section($this->fields['IBLOCK_SECTION_ID']);
                    }
                }

                if ($this->parent) {
                    return $this->parent;
                }

                break;

            case 'sections':
                if (!$this->sectionPath && $this->loadFromDatabase()) {
                    if ($this->fields['IBLOCK_SECTION_ID'] > 0) {
                        $this->sectionPath = new Iblock\Template\Entity\SectionPath($this->fields['IBLOCK_SECTION_ID']);
                    }
                }

                if ($this->sectionPath) {
                    return $this->sectionPath;
                }

                break;

            default:
                break;
        }

        return parent::resolve($entity);
    }

    public function setFields(array $fields)
    {
        if($fields['SECTIONS']) {
            foreach ($fields['SECTIONS'] as $section) {
                $this->fields['CODE'][] = $section['CODE'];
                $this->fields['NAME'][] = $section['NAME'];
                $this->fields['DESCRIPTION'][] = $section['DESCRIPTION'];
            }
        }
    }

    protected function loadFromDatabase()
    {
        if (isset($this->fields)) {
            return is_array($this->fields);
        }

        $rows = Smartseo\Models\SmartseoFilterIblockSectionsTable::getList([
              'select' => [
                  'REF_SECTION_ID' => 'SECTION.ID',
                  'REF_SECTION_NAME' => 'SECTION.NAME',
                  'REF_SECTION_CODE' => 'SECTION.CODE',
                  'REF_SECTION_DESCRIPTION' => 'SECTION.DESCRIPTION',
                  'REF_SECTION_IBLOCK_ID' => 'SECTION.IBLOCK_ID',
                  'REF_SECTION_IBLOCK_SECTION_ID' => 'SECTION.IBLOCK_SECTION_ID'
              ],
              'filter' => [
                  'FILTER_RULE_ID' => $this->id
              ],
              'limit' => $this->limit,
              'order' => [
                'SECTION.LEFT_MARGIN' => 'ASC',
              ],
              'cache' => [
                  'ttl' => Smartseo\Models\SmartseoFilterIblockSectionsTable::DEFAULT_CACHE_TTL,
              ],
          ])->fetchAll();

        for ($i = 0; $i <= count($rows) - 1; $i++) {
            foreach ($rows[$i] as $fieldName => $fieldValue) {
                $_field = preg_replace('[^(REF_SECTION_)]i', '', $fieldName);
                $this->fields[$_field] = $fieldValue;
            }
        }

        return is_array($this->fields);
    }

}
