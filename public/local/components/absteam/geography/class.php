<?php

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;

/**
 *
 */
class AbsteamGeographyComponent extends CBitrixComponent
{

    /**
     * @return void
     */
    public function executeComponent(): void
    {
        $this->arResult = $this->getResult();

        $this->includeComponentTemplate();
    }

    /**
     * @return array
     */
    private function getResult(): array
    {
        Loader::includeModule('iblock');

        $items = ElementTable::getList([
            'order' => [
                'NAME' => 'ASC',
            ],
            'filter' => [
                'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
                'ACTIVE' => 'Y',
                '!CODE' => false,
            ],
            'select' => [
                'ID',
                'NAME',
                'CODE',
            ],
            'cache' => [
                'ttl' => 3600,
            ],
        ])->fetchCollection();

        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'code' => $item->getCode(),
                'url' => $this->getUrl($item->getCode()),
            ];
        }

        return $result;
    }

    /**
     * @param string $code
     * @return string
     */
    private function getUrl(string $code): string
    {
        if ($code === 'moskva') {
            return '/';
        }

        return "/$code/";
    }

}