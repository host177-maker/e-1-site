<?php

namespace Aspro\Max\Smartseo\Template\Functions;

class FunctionMorphology extends \Bitrix\Iblock\Template\Functions\FunctionBase
{

    public function calculate(array $inputParameters)
    {
        $parameters = $this->parametersToArray($inputParameters);

        $case = null;
        $grammaticalNumber = null;
        $gender = null;

        $morphy = \Aspro\Max\Smartseo\Morphy\Morphology::getInstance();

        foreach ($parameters as $key => $grammem) {
            if ($morphy->isCase($grammem)) {
                $case = mb_strtoupper($grammem);
                unset($parameters[$key]);
                continue;
            }

            if ($morphy->isGender($grammem)) {
                $gender = mb_strtoupper($grammem);
                unset($parameters[$key]);
                continue;
            }

            if ($morphy->isGrammaticalNumber($grammem)) {
                $grammaticalNumber = mb_strtoupper($grammem);
                unset($parameters[$key]);
                continue;
            }
        }

        $words = $parameters;

        if (!$words) {
            return '';
        }

        $result = null;

        foreach ($words as $word) {
            $wordGender = null;
            $collocationWords = explode(' ', $word);

            $castWords = null;
            foreach ($collocationWords as $word) {
                $castWords[] = $morphy->castWord($word, array_filter(array_unique([$grammaticalNumber, $gender, $case])));
            }

            $result[] = $castWords ? implode(' ', $castWords) : $word;
        }

        return $result;
    }
}
