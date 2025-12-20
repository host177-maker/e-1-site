<?php

namespace Aspro\Max\Smartseo\Template\Functions;

class FunctionUpperFirst extends \Bitrix\Iblock\Template\Functions\FunctionBase
{
    public function calculate(array $inputParameters)
    {
        $parameters = $this->parametersToArray($inputParameters);

        $text = array_shift($parameters);

        $firstSymbol = mb_strtoupper(mb_substr($text, 0, 1));

        return $firstSymbol . mb_substr($text, 1);
    }
}
