<?php

namespace Aspro\Max\Smartseo\Template\Functions;

class Fabric
{
    public static function getFunctionClass($event)
    {

        $parameters = $event->getParameters();
        $functionClass = $parameters[0];
        if (is_string($functionClass)) {
            switch ($functionClass) {
                case 'aspro_morphy':
                    return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, '\\Aspro\\Max\\Smartseo\\Template\\Functions\\FunctionMorphology');
                case 'aspro_upperf':
                    return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::SUCCESS, '\\Aspro\\Max\\Smartseo\\Template\\Functions\\FunctionUpperFirst');
            }
        }
    }
}
