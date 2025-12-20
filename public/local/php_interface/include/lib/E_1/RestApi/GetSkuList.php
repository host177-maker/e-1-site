<?php

namespace E_1\RestApi;

use Exception;
use Bitrix\Main\Loader;
use Bitrix\Iblock\Iblock;
use Bitrix\Catalog\Model\Price;
use Bitrix\Highloadblock as HL;

class GetSkuList
{
    // Значения по умолчанию для лимита и страницы
    private static int $defaultLimit = 1000;
    private static int $defaultPage  = 1;

    /**
     * Регистрирует пользовательские методы REST API и указывает их обработчики.
     *
     * @return array Массив с описанием методов REST API.
     */
    public static function addCustomRestMethods(): array
    {
        return [
            'sku_list' => [
                'sku_list.get' => [
                    'callback' => [__CLASS__, 'getGetSkuList'], // Обработчик метода
                    'options'  => [], // Дополнительные опции
                ],
            ],
        ];
    }

    /**
     * Обработчик для получения списка SKU.
     *
     * @param array $query Параметры запроса (limit, page и т.д.).
     * @return array Результат выполнения метода.
     */
    public static function getGetSkuList($query): array
    {
        try {
            Loader::IncludeModule('iblock');

            // Обработка параметра limit
            $limit = self::$defaultLimit;
            if ($query['limit']) {
                $limit = self::prepareInt($query['limit']);
                if ($limit <= 0) {
                    $limit = self::$defaultLimit;
                }
                if ($limit > 100000) {
                    $limit = 100000;
                }
            }

            // Обработка параметра page
            $page = self::$defaultPage;
            if ($query['page']) {
                $page = self::prepareInt($query['page']);
                if ($page <= 0) {
                    $page = self::$defaultPage;
                }
                if ($page > 10000) {
                    $page = 10000;
                }
            }

            // Расчет смещения (offset) для пагинации
            if ($page === 1) {
                $offset = 0;
            } else {
                $offset = ($page - 1) * $limit;
            }

            // Получение данных из Highload-блоков
            $hlListColorRef      = self::hlGetList('TSVETKORPUSA'); // Цвет корпуса
            $hlListColorProfil   = self::hlGetList('TSVETPROFILYA1'); // Цвет профиля
            $hlListHousingLayout = self::hlGetList('KOMPONOVKAKORPUSA'); // Компоновка корпуса

            // Инициализация переменных для хранения данных
            $result           = [];
            $skuIds           = [];
            $productIds       = [];
            $productAndSkuIds = [];

            // Получение данных из инфоблока SKU
            $entity            = Iblock::wakeUp(49); // ID инфоблока SKU
            $entityClassName   = $entity->getEntityDataClass();
            $elementCollection = $entityClassName::getList([
                'select' => [
                    'ID',
                    'NAME',
                    'XML_ID',
                    'CML2_LINK.VALUE',
                    'HEIGHT.ITEM',
                    'WIDTH.ITEM',
                    'DEPTH.ITEM',
                    'COLOR_REF.VALUE',
                    'TSVET_PROFILYA.VALUE',
                    'HOUSING_LAYOUT.VALUE'
                ],
                // 'filter' => [
                //     'ACTIVE' => 'Y' // Только активные элементы
                // ],
                'order' => [
                    'ID' => 'ASC' // Сортировка по ID
                ],
                'offset' => $offset, // Смещение для пагинации
                'limit'  => $limit // Лимит элементов
            ])?->fetchCollection();

            // Проверка на пустую коллекцию
            if (empty($elementCollection)) {
                return [
                    'status'            => 'error',
                    'error_description' => 'Ошибка получения коллекции SKU.'
                ];
            }

            // Обработка элементов коллекции SKU
            foreach ($elementCollection as $element) {
                $skuId                                = $element->getId();
                $skuIds[$skuId]                       = $skuId;
                $skuXmlId                             = $element->getXmlId();
                $productId                            = $element->get('CML2_LINK')?->getValue();
                $productIds[$productId]               = $productId;
                $productAndSkuIds[$productId][$skuId] = $skuId;

                // Получение значений из Highload-блоков
                $colorRef = '';
                if ($element->get('COLOR_REF')) {
                    $colorRef = $element->get('COLOR_REF')?->getValue();
                    $colorRef = $colorRef ? $hlListColorRef[$colorRef] : '';
                }

                $colorProfil = '';
                if ($element->get('TSVET_PROFILYA')) {
                    $colorProfil = $element->get('TSVET_PROFILYA')?->getValue();
                    $colorProfil = $colorProfil ? $hlListColorProfil[$colorProfil] : '';
                }

                $housingLayout = '';
                if ($element->get('HOUSING_LAYOUT')) {
                    $housingLayout = $element->get('HOUSING_LAYOUT')?->getValue();
                    $housingLayout = $housingLayout ? $hlListHousingLayout[$housingLayout] : '';
                }

                // Формирование результата для каждого SKU
                $result[$skuId] = [
                    'NAME' => [
                        'NAME'  => 'Название',
                        'CODE'  => 'NAME',
                        'VALUE' => $element->getName()
                    ],
                    'HEIGHT' => [
                        'NAME'  => 'Высота, мм',
                        'CODE'  => 'HEIGHT',
                        'VALUE' => $element->get('HEIGHT')?->getItem()?->getValue()
                    ],
                    'WIDTH' => [
                        'NAME'  => 'Ширина, мм',
                        'CODE'  => 'WIDTH',
                        'VALUE' => $element->get('WIDTH')?->getItem()?->getValue()
                    ],
                    'DEPTH' => [
                        'NAME'  => 'Глубина, мм',
                        'CODE'  => 'DEPTH',
                        'VALUE' => $element->get('DEPTH')?->getItem()?->getValue()
                    ],
                    'COLOR_REF' => [
                        'NAME'  => 'Цвет корпуса',
                        'CODE'  => 'COLOR_REF',
                        'VALUE' => $colorRef
                    ],
                    'TSVET_PROFILYA' => [
                        'NAME'  => 'Цвет профиля',
                        'CODE'  => 'TSVET_PROFILYA',
                        'VALUE' => $colorProfil
                    ],
                    'HOUSING_LAYOUT' => [
                        'NAME'  => 'Компоновка корпуса',
                        'CODE'  => 'HOUSING_LAYOUT',
                        'VALUE' => $housingLayout
                    ],
                    'PRICE_MOSCOW' => [
                        'NAME'  => 'Розница Москва Скид',
                        'CODE'  => 'PRICE_MOSCOW',
                        'VALUE' => 0
                    ],
                    'PRICE_SIBERIA' => [
                        'NAME'  => 'Розница Сибирь Скид',
                        'CODE'  => 'PRICE_SIBERIA',
                        'VALUE' => 0
                    ],
                    'XML_ID' => [
                        'NAME'  => 'Внешний код ТП',
                        'CODE'  => 'XML_ID',
                        'VALUE' => $skuXmlId
                    ],
                    'PRODUCT_NAME' => [
                        'NAME'  => 'Название карточки',
                        'CODE'  => 'PRODUCT_NAME',
                        'VALUE' => ''
                    ],
                    'PRODUCT_SERIYA_SHKAFA' => [
                        'NAME'  => 'Серия',
                        'CODE'  => 'PRODUCT_SERIYA_SHKAFA',
                        'VALUE' => ''
                    ],
                    'PRODUCT_XML_ID' => [
                        'NAME'  => 'Внешний код карточки',
                        'CODE'  => 'PRODUCT_XML_ID',
                        'VALUE' => ''
                    ],
                    'PRODUCT_ADMIN_URL' => [
                        'NAME'  => 'admin_url',
                        'CODE'  => 'PRODUCT_ADMIN_URL',
                        'VALUE' => ''
                    ],
                ];
            }

            // Получение цен для SKU
            $dbPrice = Price::getList([
                'filter' => ['PRODUCT_ID' => $skuIds, 'CATALOG_GROUP_ID' => [235, 236]], // Фильтр по ID товаров и группам цен
                'select' => ['PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE'],
            ]);
            while ($priceData = $dbPrice->fetch()) {
                $skuId = $priceData['PRODUCT_ID'];
                $price = $priceData['PRICE'];

                // Заполнение цен для Москвы и Сибири
                if ($price > 0 && intval($priceData['CATALOG_GROUP_ID']) === 235) {
                    $result[$skuId]['PRICE_MOSCOW']['VALUE'] = $price;
                }

                if ($price > 0 && intval($priceData['CATALOG_GROUP_ID']) === 236) {
                    $result[$skuId]['PRICE_SIBERIA']['VALUE'] = $price;
                }
            }

            // Получение данных из инфоблока товаров
            $entityProduct            = Iblock::wakeUp(48); // ID инфоблока товаров
            $entityClassNameProduct   = $entityProduct->getEntityDataClass();
            $elementCollectionProduct = $entityClassNameProduct::getList([
                'select' => [
                    'ID',
                    'NAME',
                    'XML_ID',
                    'SERIYA_SHKAFA.ITEM',
                ],
                'filter' => [
                    'ID' => $productIds // Фильтр по ID товаров
                ],
                'order' => [
                    'ID' => 'ASC'
                ]
            ])?->fetchCollection();

            // Проверка на пустую коллекцию товаров
            if (empty($elementCollectionProduct)) {
                return [
                    'status'            => 'error',
                    'error_description' => 'Ошибка получения коллекции Товаров.'
                ];
            }

            // Обработка элементов коллекции товаров
            foreach ($elementCollectionProduct as $product) {
                $productId           = $product->getId();
                $productXmlId        = $product->getXmlId();
                $productName         = $product->getName();
                $productSeriyaShkafa = $product->get('SERIYA_SHKAFA')?->getItem()?->getValue();
                $productAdminUrl     = 'https://' . $_SERVER['SERVER_NAME'] . '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=48&type=1c_catalog&lang=ru&ID=' . $productId;

                $skuIds = $productAndSkuIds[$productId];

                // Заполнение данных о товаре для каждого SKU
                if ($skuIds) {
                    foreach ($skuIds as $skuId) {
                        $result[$skuId]['PRODUCT_NAME']['VALUE']          = $productName;
                        $result[$skuId]['PRODUCT_SERIYA_SHKAFA']['VALUE'] = $productSeriyaShkafa;
                        $result[$skuId]['PRODUCT_XML_ID']['VALUE']        = $productXmlId;
                        $result[$skuId]['PRODUCT_ADMIN_URL']['VALUE']     = $productAdminUrl;

                        unset($productAndSkuIds[$productId][$skuId]);
                    }
                }
            }

            // Возврат успешного результата
            return [
                'status'   => 'success',
                'response' => $result,
            ];
        } catch (Exception $e) {
            // Обработка исключений
            return [
                'status'            => 'error',
                'error_description' => $e->getMessage()
            ];
        }
    }

    /**
     * Получает данные из Highload-блока по имени таблицы.
     *
     * @param string $tableName Имя таблицы Highload-блока.
     * @return array Массив данных из Highload-блока.
     */
    private static function hlGetList(string $tableName): array
    {
        if (!$tableName) {
            return [];
        }

        $result = [];

        // Компиляция сущности Highload-блока
        $obEntity           = HL\HighloadBlockTable::compileEntity($tableName);
        $strEntityDataClass = $obEntity->getDataClass();

        // Получение данных из Highload-блока
        $rsData = $strEntityDataClass::getList([
            'select' => [
                'UF_XML_ID',
                'UF_NAME'
            ],
            'cache' => [
                'ttl' => 3600 // Кэширование на 1 час
            ]
        ]);

        // Формирование массива данных
        while ($arTmp = $rsData->Fetch()) {
            $result[$arTmp['UF_XML_ID']] = $arTmp['UF_NAME'];
        }

        return $result;
    }

    /**
     * Подготавливает целое число из входных данных.
     *
     * @param int $data Входные данные.
     * @return int Подготовленное целое число.
     */
    private static function prepareInt(int $data): int
    {
        if (!$data) {
            return 0;
        }

        return intval(htmlspecialchars(trim($data)));
    }
}
