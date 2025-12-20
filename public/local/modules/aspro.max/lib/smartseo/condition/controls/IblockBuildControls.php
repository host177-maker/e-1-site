<?php

namespace Aspro\Max\Smartseo\Condition\Controls;

class IblockBuildControls extends \CCatalogCondCtrlIBlockFields implements BuildControlsInterface
{

    public function getBuild()
    {
       return [
            'COMPLEX' => 'Y',
            'SORT' => 200,
            'CONTROLS' => static::GetControls(),
            'GetControlShow' => [self::class, 'GetControlShow'],
            'GetConditionShow' => [self::class, 'GetConditionShow'],
            'IsGroup' => [self::class, 'IsGroup'],
            'Parse' => [self::class, 'Parse'],
            'Generate' => [self::class, 'Generate'],
            'ApplyValues' => [self::class, 'ApplyValues'],
            'InitParams' => [self::class, 'InitParams'],
        ];
    }
}
