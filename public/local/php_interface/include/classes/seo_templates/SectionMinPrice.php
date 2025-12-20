<?
namespace COrwoSeoTemplate;
\Bitrix\Main\Loader::includeModule('iblock');
class SectionMinPrice extends \Bitrix\Iblock\Template\Functions\FunctionBase
{
    public function onPrepareParameters(\Bitrix\Iblock\Template\Entity\Base $entity, array $parameters)
    {
        $arguments = [];
        // get section ID
        $this->data['id'] = $entity->getId();
        foreach ($parameters as $parameter) {
            $arguments[] = $parameter->process($entity);
        }
        return $arguments;
    }
    
    public function calculate($parameters)
    {
        $priceGroup = ''; // base or number
        \Bitrix\Main\Loader::includeModule("catalog");
        \Bitrix\Main\Loader::includeModule('currency');
        \Bitrix\Main\Loader::includeModule('iblock');

        /**
         * For the future. To add features
         */
        $arFunction = [
            'RAW' => 'isRawCurrency',
            'IS_AVAILABLE' => 'isAvailableProduct',
        ];
        foreach ($arFunction as $function) {
            ${$function} = false; // example $isRawCurrency == false
        }
        /**
         * Check the received template for functions
         */
        foreach ($parameters as $param) {
            $param = ToUpper($param); // Upper bitrix function
            if (stripos($param, 'GROUP_') !== false) {
                $priceGroup = str_ireplace('GROUP_', '', $param); // price group
            } elseif (array_key_exists($param, $arFunction)) {
                ${$arFunction[$param]} = true; // example $isRawCurrency == true
            } else {
                $paramSectionID = (int) $param;
            }
        }
        
        $sectionID = (!empty($paramSectionID) ? $paramSectionID : $this->data['id']);

        // get section
        $section =  \Bitrix\Iblock\SectionTable::getList([
            'filter' => ['ID' => $sectionID],
            'select' => ['LEFT_MARGIN', 'RIGHT_MARGIN', 'IBLOCK_ID', 'ID']
        ])->fetchRaw();
        // get subsection
        $subSections = \Bitrix\Iblock\SectionTable::getList([
            'filter' => [
                '>=LEFT_MARGIN' => $section['LEFT_MARGIN'],
                '<=RIGHT_MARGIN' => $section['RIGHT_MARGIN'],
                '=IBLOCK_ID'  => $section['IBLOCK_ID'],
            ],
            'select' => ['ID']
        ]);
        while ($section = $subSections->fetch()) {
            $arSectionsID[] = $section['ID'];
        }

        $arElementsFilter = array("SECTION_ID" => $arSectionsID, "INCLUDE_SUBSECTIONS" => "Y");
        $arElementsSelect = array("IBLOCK_ID", "ID", "NAME");
        $arElementsOrder = array();
        \COrwoFunctions::MakeElementFilterInRegion($arCounterFilter, $arPreFilter, $arElementsFilter, $arParams);
        $obElements = \CIBlockElement::GetList($arElementsOrder, $arElementsFilter, false, false, $arElementsSelect);
        while($rsElements = $obElements->GetNextElement())
        {
            $arFields = $rsElements->GetFields();
            $arElementsID[] = (int) $arFields['ID'];
        }

        // get sku product
        $arSkuList = [];
        $arSkuList = \CCatalogSku::getOffersList($arElementsID, $section['IBLOCK_ID'], array('ACTIVE' => 'Y'), array('ID'));
        if (!empty($arSkuList)) {
            $arSkuIDs = [];
            foreach ($arSkuList as $value) {
                $arSkuIDs = array_merge($arSkuIDs, array_keys($value));
            }
        }

        // merge elements
        if (!empty($arSkuIDs)) {
            $arElementsID = array_merge($arElementsID, $arSkuIDs);
        }
         

        // get max price element
        $filterPrice = ['=ID' => $arElementsID, 'ACTIVE' => 'Y'];

        // if param 'IS_AVAILABLE' active
        // if ($isAvailableProduct === true) {
        //     $filterPrice['=ProductTable.AVAILABLE'] = 'Y';
        // }
        
        // if (!empty($priceGroup)) {
        //     $filterPrice['PriceTable.CATALOG_GROUP_ID'] = $priceGroup;
        // }
        // else{
        //     $filterPrice['PriceTable.CATALOG_GROUP_ID'] = 235;
        // }

        $arElementsFilter = $filterPrice;
        $arElementsSelect = array("IBLOCK_ID", "ID", "NAME");
        $arElementsOrder = array();

        if(empty($priceGroup)){
            $priceGroup = 235;
        }

        if (!empty($priceGroup)) {
            $arElementsSelect[] = 'PRICE_' . $priceGroup;
            $arElementsFilter['>PRICE_' . $priceGroup] = 0;
            $arElementsOrder['PRICE_' . $priceGroup] = 'asc';
        }

        $rawPrice = '';
        $obElements = \CIBlockElement::GetList($arElementsOrder, $arElementsFilter, false, array("nTopCount" => 1), $arElementsSelect);
        if($rsElements = $obElements->GetNextElement())
        {
            $arFields = $rsElements->GetFields();

            $rawPrice = $arFields['PRICE_' . $priceGroup];
        }

        if (!empty($rawPrice)) {
            /**
             * Functions over the min price
             */
            // $rawPrice = (int) reset($arItem);

            // if ($isRawCurrency === false) {
            //     $minPriceSection = html_entity_decode(\CCurrencyLang::CurrencyFormat($rawPrice, \Bitrix\Currency\CurrencyManager::getBaseCurrency()));
            // } else {
            //     $minPriceSection = html_entity_decode($rawPrice);
            // }

            $minPriceSection = html_entity_decode(\CCurrencyLang::CurrencyFormat($rawPrice, \Bitrix\Currency\CurrencyManager::getBaseCurrency()));
        }

        //$minPriceSection = 111111111;
        return $minPriceSection;
    }
}
