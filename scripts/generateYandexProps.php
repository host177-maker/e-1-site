<?php

declare(strict_types=1);

set_time_limit(0);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
ini_set('memory_limit', '4096M');

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__) . '/public';

use Bitrix\Iblock\Iblock;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

CModule::IncludeModule('iblock');

const CATALOG_IB = 48; // ID ИБ каталога
const OFFERS_IB  = 49; // ID ИБ торговых предложений

$cnt    = 0;
$result = getData();

// заполнеяем поля для сохранения в свойствах ИБ
foreach ($result as $key => $value) {
    $result[$key] = array_map(function ($item) use (&$result, $key, &$cnt) {
        $prefix = match($item['SECTION_NAME']) {
            '2-х дверные шкафы-купе', '3-х дверные шкафы-купе', 'Шкафы-купе с зеркалом', 'Шкафы-купе с фотопечатью' => 'Шкаф-купе',
            'Шкафы распашные' => 'Распашной шкаф',
            'Угловые шкафы'   => 'Угловой шкаф',
            default           => null,
        };
        $item['typePrefix'] = $prefix;

        $models         = getModelsList($item);
        $item['model']  = generateModel($models, ['prefix' => $prefix, 'vendor' => 'Мебельная фабрика «Е1»']);

        // Дополнительные строки для формирования name
        $additionalStrings = [
            'Гарантия до 10 лет',
            'Рассрочка 6 мес',
            'Кэшбек',
            'Сборка',
            'Доставка',
            'Фурнитура Hettich',
            'Защита от сколов и повреждений',
            'Бесшумные двери',
            'Противоосколочные зеркала',
            'Ударостойкая ПВХ кромка',
            'Индивидуальный дизайн'
        ];
        shuffle($additionalStrings);

        $item['name']  = $item['typePrefix'];
        for ($i = 0, $n = count($additionalStrings); $i < $n; $i++) {
            if (mb_strlen($item['name'] . '. ' . $additionalStrings[$i]) < 56) {
                $item['name'] .= '. ' . $additionalStrings[$i];
            }
        }

        // формируем description
        $singularSectionName = match($item['SECTION_NAME']) {
            '2-х дверные шкафы-купе'   => '2-х дверный шкаф-купе. ',
            '3-х дверные шкафы-купе'   => '3-х дверный шкаф-купе. ',
            'Шкафы-купе с зеркалом'    => 'Шкаф-купе с зеркалом. ',
            'Шкафы-купе с фотопечатью' => 'Шкаф-купе с фотопечатью. ',
            'Шкафы распашные'          => 'Распашной шкаф. ',
            'Угловые шкафы'            => 'Угловой шкаф. ',
            default                    => null,
        };

        $item['description'] = $singularSectionName . match($item['SECTION_NAME']) {
            '2-х дверные шкафы-купе', '3-х дверные шкафы-купе', 'Шкафы-купе с зеркалом', 'Шкафы-купе с фотопечатью' => 'Ударостойкий. Безопасные зеркала. Бесшумные двери.',
            'Шкафы распашные' => '3 системы открывания. Нагрузка на полки: до 15 кг',
            'Угловые шкафы'   => '3 системы открывания. Ударостойкий. Безопасные зеркала.',
            default           => '',
        };

        $result[$key] = $item;

        // сохраняем свойства в ИБ
        $properties = [
            'YANDEX_TYPE_PREFIX' => $result[$key]['typePrefix'],
            'YANDEX_MODEL'       => $result[$key]['model'],
            'YANDEX_NAME'        => $result[$key]['name'],
            'YANDEX_DESCRIPTION' => $result[$key]['description'],
        ];

        CIBlockElement::SetPropertyValuesEx($item['OFFER_ID'], OFFERS_IB, $properties);

        ob_clean();
        // \033[0G - перемещает курсор в начало строки
        // \033[K - очищает текущую строку
        echo "\033[0G\033[KOffer ID: " . $item['OFFER_ID'];
        ob_flush();

        $cnt++;

        return $result[$key];
    }, $result[$key]);
}

echo "\n";
ob_flush();

dump('Итого элементов: ' . $cnt);

function generateModel(array $models, array $checkingData): string
{
    $model = '';
    if (empty($models)) {
        $model = ' от производителя';
    } else {
        do {
            $model = implode(' ', $models);
            array_pop($models);
        } while (mb_strlen($checkingData['prefix'] . ' ' . $model . '. ' . $checkingData['vendor']) > 56 && !empty($models));
    }

    return $model;
}

function getData(): array
{
    $catalogEntity  = Iblock::wakeUp(CATALOG_IB)->getEntityDataClass();
    $offersEntity   = Iblock::wakeUp(OFFERS_IB)->getEntityDataClass();

    $pCml2Link      = \CIBlockProperty::GetList([], ['IBLOCK_ID' => OFFERS_IB, 'CODE' => 'CML2_LINK'])->Fetch();
    $linkPropertyId = $pCml2Link['ID'];

    $query = $catalogEntity::query()
        ->setSelect([
            'ID',
            'SECTION_NAME'     => 'IBLOCK_SECTION.NAME',
            'OFFER_ID'         => 'PROPERTY_LINK.IBLOCK_ELEMENT_ID',
            'OFFER_NAME'       => 'PRODUCT.NAME',
        ])
        ->setFilter([
            // 'ID'     => [1646573, /*1646574, 1646575, 1646576*/], // временно, для тестов
            'ACTIVE' => 'Y'
        ])
        ->registerRuntimeField(
            'PROPERTY_LINK',
            [
                'data_type' => 'Bitrix\Iblock\ElementPropertyTable',
                'reference' => [
                    '=this.ID'                => 'ref.VALUE',
                    '=ref.IBLOCK_PROPERTY_ID' => new \Bitrix\Main\DB\SqlExpression('?i', $linkPropertyId)
                ],
                'join_type' => 'inner'
            ]
        )
        ->registerRuntimeField(
            'PRODUCT',
            [
                'data_type' => $offersEntity,
                'reference' => [
                    '=ref.ID'     => 'this.PROPERTY_LINK.IBLOCK_ELEMENT_ID',
                    '=ref.ACTIVE' => new \Bitrix\Main\DB\SqlExpression('?s', 'Y'),
                ],
                'join_type' => 'inner',
            ]
        );

    $query->exec();

    $collection = collect($query->fetchAll());

    return $collection->groupBy('ID')->toArray();
}
/**
 * Функция формирует и возвращает массив моделей в зависимости от вхождения подстрок в имя товара
 *
 * @param array $item Массив данных товара, должен содержать ключ 'OFFER_NAME'
 * @return array Массив найденных моделей товара
 * @throws InvalidArgumentException Если входные данные некорректны
 */
function getModelsList(array $item): array
{
    if (empty($item['OFFER_NAME']) || !is_string($item['OFFER_NAME'])) {
        throw new InvalidArgumentException('Некорректные входные данные: ожидается массив с ключом OFFER_NAME');
    }

    $cacheKey = 'model_' . md5($item['OFFER_NAME']);

    static $cache = [];
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $name   = mb_strtolower($item['OFFER_NAME']);
    $models = [];

    // Массив правил для проверки [подстрока/регулярка => значение модели]
    $rules = [
        '/2-х\s*дверный/ui' => '2-х дверный',
        '/3-х\s*дверный/ui' => '3-х дверный',
        'дсп'               => 'из ЛДСП',
        'стекл'             => 'со стеклом',
        'печат'             => 'с фотопечатью',
        'зеркал'            => 'с зеркалом',
        '/2\s*секции/ui'    => '2 секции',
        '/3\s*секции/ui'    => '3 секции',
        '/4\s*секции/ui'    => '4 секции'
    ];

    foreach ($rules as $pattern => $model) {
        // Проверяем, является ли паттерн регулярным выражением (по наличию /)
        if ($pattern[0] === '/') {
            if (preg_match($pattern, $name)) {
                $models[] = $model;
            }
        } else {
            if (mb_strpos($name, $pattern) !== false) {
                $models[] = $model;
            }
        }
    }

    $cache[$cacheKey] = $models;

    return $models;
}
