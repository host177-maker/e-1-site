<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if(!empty($arParams['CURRENT_SECTION'])){
    $rsParentSection = CIBlockSection::GetList(
        Array('name' => 'asc'),
        $arParams['CURRENT_SECTION']
    );
    while ($arParentSection = $rsParentSection->Fetch())
    {
        $arFilter = array('IBLOCK_ID' => $arParentSection['IBLOCK_ID'],'ACTIVE' => 'Y','>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'],'<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],'>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL'], 'UF_IS_TAG' => 1);//выб разделы, которые выводятся как тег
        $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'),$arFilter);
        while ($arSect = $rsSect->GetNext())
        {
            $arSectRes["PROPERTIES"]["LINK"]["VALUE"] = $arSect["SECTION_PAGE_URL"];
            $arSectRes["PROPERTIES"]["NAME"]["VALUE"] = $arSect["NAME"];
            $arSectRes["ID"] = $arSect["ID"];
            if (!empty( $arSectRes["PROPERTIES"]["LINK"]["VALUE"]) && !empty($arSectRes["PROPERTIES"]["NAME"]["VALUE"])) {
                $arResult['ITEMS'][] = $arSectRes;
            }
        }
    }
}