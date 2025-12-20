<?php

namespace Aspro\Max\Smartseo\Seo;

class RobotsFile extends \Bitrix\Seo\RobotsFile
{

    public function deleteRule($rule, $section = '*', $bCheckUnique = true)
    {
        $this->load();

        $this->deleteSectionRule($section, $rule);

        $this->save();
    }

    protected function deleteSectionRule($section, $rule)
    {
        $section = ToUpper($section);

        $ruleIndex = null;
        foreach ($this->contents[$section] as $contentIndex => $contentRules) {
            if (ToUpper($contentRules[0]) == ToUpper($rule[0]) && $contentRules[1] == $rule[1]) {
                $ruleIndex = $contentIndex;

                continue;
            }
        }

        if ($ruleIndex !== null) {
            unset($this->contents[$section][$ruleIndex]);
        }
    }

}
