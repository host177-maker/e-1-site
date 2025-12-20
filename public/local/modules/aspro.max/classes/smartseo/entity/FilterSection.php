<?php

namespace Aspro\Max\Smartseo\Entity;

use Aspro\Max\Smartseo;

class FilterSection extends Smartseo\Models\EO_SmartseoFilterSection
{
    public function setParentId($value) {
        parent::setParentId($value);

        $parentFilterSection = static::wakeUp($value);

        $parentFilterSection->fillDepthLevel();
        if($parentFilterSection) {
            $this->setDepthLevel($parentFilterSection->getDepthLevel() + 1);
        }
    }
}