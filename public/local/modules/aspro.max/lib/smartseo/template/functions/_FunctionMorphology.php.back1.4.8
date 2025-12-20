<?php

namespace Aspro\Max\Smartseo\Template\Functions;

class FunctionMorphology extends \Bitrix\Iblock\Template\Functions\FunctionBase
{

    public function calculate(array $parameters)
    {
        $result = $this->parametersToArray($parameters);

        $case = null;
        $grammaticalNumber = null;
        $gender = null;

        $morphy = \Aspro\Max\Smartseo\Morphy\Morphology::getInstance();

        foreach ($result as $key => $grammem) {
            if ($morphy->isCase($grammem)) {
                $case = mb_strtoupper($grammem);
                unset($result[$key]);
                continue;
            }

            if ($morphy->isGender($grammem)) {
                $gender = mb_strtoupper($grammem);
                unset($result[$key]);
                continue;
            }

            if ($morphy->isGrammaticalNumber($grammem)) {
                $grammaticalNumber = mb_strtoupper($grammem);
                unset($result[$key]);
                continue;
            }
        }

        $words = $result;

        if (!$words) {
            return '';
        }

        $result = null;

        foreach ($words as $word) {
            $wordGender = null;
            $collocationWords = explode(' ', $word);

            if (!$gender && count($collocationWords) > 1) {
                $lastWord = $collocationWords[count($collocationWords) - 1];
                $wordGender = $morphy->getGenderByWord($lastWord);
            }

            $castWords = null;
            foreach ($collocationWords as $word) {
                $castWords[] = $morphy->castWord($word, array_filter(array_unique([$grammaticalNumber, $gender, $wordGender, $case])));
            }

            $result[] = $castWords ? implode(' ', $castWords) : $word;
        }

        return $result;
    }
}
