<?

use \Bitrix\Main\Config\Option,
    \Bitrix\Main\Web\Json,
    \Bitrix\Main\Localization\Loc;

class CMaxCustom extends CMax
{
    public static function GetSKUPropsArray(&$arSkuProps, $iblock_id = 0, $type_view = "list", $hide_title_props = "N", $group_iblock_id = "N", $arItem = array(), $offerShowPreviewPictureProps = array(), $max_count = false)
    {
        $arSkuTemplate = array();
        $class_title = ($hide_title_props == "Y" ? "hide_class" : "show_class");
        $class_title .= ' bx_item_section_name';
        if ($iblock_id) {
            $arPropsSku = CIBlockSectionPropertyLink::GetArray($iblock_id);
            if ($arPropsSku) {
                foreach ($arSkuProps as $key => $arProp) {
                    if ($arPropsSku[$arProp["ID"]]) {
                        $arSkuProps[$key]["DISPLAY_TYPE"] = $arPropsSku[$arProp["ID"]]["DISPLAY_TYPE"];
                    }
                }
            }
        } ?>

        <?
        $bTextViewProp = (Option::get(self::moduleID, "VIEW_TYPE_HIGHLOAD_PROP", "N", SITE_ID) == "Y");

        $arCurrentOffer = $arItem['OFFERS'][$arItem['OFFERS_SELECTED']];
        $j = 0;
        $arFilter = $arShowValues = array();

        /*get correct values*/
        foreach ($arSkuProps as $key => $arProp) {
            $strName = 'PROP_' . $arProp['ID'];
            $arShowValues = self::GetRowValues($arFilter, $strName, $arItem);

            if (in_array($arCurrentOffer['TREE'][$strName], $arShowValues ?: [])) {
                $arFilter[$strName] = $arCurrentOffer['TREE'][$strName];
            } else {
                $arFilter[$strName] = $arShowValues[0];
            }

            $arCanBuyValues = $tmpFilter = array();
            $tmpFilter = $arFilter;
            foreach ($arShowValues as $value) {
                $tmpFilter[$strName] = $value;
                if (self::GetCanBuy($tmpFilter, $arItem)) {
                    $arCanBuyValues[] = $value;
                }
            }

            $arSkuProps[$key] = self::UpdateRow($arFilter[$strName], $arShowValues, $arCanBuyValues, $arProp, $type_view);
        }
        /**/


        if ($group_iblock_id == "Y") {
            foreach ($arSkuProps as $iblockId => $skuProps) {
                $arSkuTemplate[$iblockId] = array();
                $j = 0;
                foreach ($skuProps as $key => &$arProp) {

                    if ($arProp['VALUES']) {
                        foreach ($arProp['VALUES'] as $arOneValue) {
                            if ($arOneValue['CLASS'] && $arOneValue['CLASS'] == 'active') {
                                $arProp['VALUE'] = '<span class="val">' . $arOneValue['NAME'] . '</span>';
                                break;
                            }
                        }
                    }

                    $nameWithDelimeters = $arProp['NAME'];

                    $templateRow = '';
                    $class_title .= (($arProp["HINT"] && $arProp["SHOW_HINTS"] == "Y") ? ' whint char_name' : '');
                    $hint_block = (($arProp["HINT"] && $arProp["SHOW_HINTS"] == "Y") ? '<div class="hint"><span class="icon"><i>?</i></span><div class="tooltip">' . $arProp["HINT"] . '</div></div>' : '');
                    if (($arProp["DISPLAY_TYPE"] == "P" || $arProp["DISPLAY_TYPE"] == "R") && $type_view != 'block') {
                        $templateRow .= '<div class="bx_item_detail_size" ' . $arProp['STYLE'] . ' id="#ITEM#_prop_' . $arProp['ID'] . '_cont" data-display_type="SELECT" data-id="' . $arProp['ID'] . '">' .
                            '<span class="' . $class_title . '"><span>' . htmlspecialcharsex($arProp['NAME']) . $hint_block . '<span class="sku_mdash">&mdash;</span>' . $arProp['VALUE'] . '</span></span>' .
                            '<div class="bx_size_scroller_container form-control bg"><div class="bx_size"><select id="#ITEM#_prop_' . $arProp['ID'] . '_list" class="list_values_wrapper">';
                        foreach ($arProp['VALUES'] as $arOneValue) {
                            // if($arOneValue['ID']>0){
                            $arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
                            $templateRow .= '<option ' . $arOneValue['SELECTED'] . ' ' . $arOneValue['DISABLED'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="select" data-onevalue="' . $arOneValue['ID'] . '" ';
                            if ($arProp["DISPLAY_TYPE"] == "R") {
                                $templateRow .= 'data-img_src="' . $arOneValue["PICT"]["SRC"] . '" ';
                            }

                            $templateRow .= 'title="' . $nameWithDelimeters . $arOneValue['NAME'] . '">';
                            $templateRow .= '<span class="cnt">' . $arOneValue['NAME'] . '</span>';
                            $templateRow .= '</option>';
                            // }
                        }
                        $templateRow .= '</select></div>' . '</div></div>';
                    } elseif ('TEXT' == $arProp['SHOW_MODE']) {
                        $templateRow .= '<div class="bx_item_detail_size" ' . $arProp['STYLE'] . ' id="#ITEM#_prop_' . $arProp['ID'] . '_cont" data-display_type="LI" data-id="' . $arProp['ID'] . '">' .
                            '<span class="' . $class_title . '"><span>' . htmlspecialcharsex($arProp['NAME']) . $hint_block . '<span class="sku_mdash">&mdash;</span>' . $arProp['VALUE'] . '</span></span>' .
                            '<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_' . $arProp['ID'] . '_list" class="list_values_wrapper">';
                        foreach ($arProp['VALUES'] as $arOneValue) {
                            // if($arOneValue['ID']>0){

                            $arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
                            $templateRow .= '<li class="item ' . $arOneValue['CLASS'] . '" ' . $arOneValue['STYLE'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="li" data-onevalue="' . $arOneValue['ID'] . '" title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"><i></i><span class="cnt">' . $arOneValue['NAME'] . '</span></li>';
                            // }
                        }
                        $templateRow .= '</ul></div>' . '</div></div>';
                    } elseif ('PICT' == $arProp['SHOW_MODE']) {
                        $arCurrentTree = array();
                        if ($offerShowPreviewPictureProps && is_array($offerShowPreviewPictureProps)) {
                            if (in_array($arProp['CODE'], $offerShowPreviewPictureProps)) {
                                if ($arCurrentOffer && $arCurrentOffer['TREE'])
                                    $arCurrentTree = $arCurrentOffer['TREE'];
                            }
                        }

                        $isHasPicture = true;
                        foreach ($arProp['VALUES'] as &$arOneValue) {
                            $boolOneSearch = false;
                            if ($arCurrentTree && $arOneValue['ID'] != 0) {
                                $arRowTree = $arCurrentTree;
                                $arRowTree['PROP_' . $arProp['ID']] = $arOneValue['ID'];

                                foreach ($arItem['OFFERS'] as &$arOffer) {
                                    $boolOneSearch = true;
                                    foreach ($arRowTree as $rkey => $rval) {
                                        if ($rval !== $arOffer['TREE'][$rkey]) {
                                            $boolOneSearch = false;
                                            break;
                                        }
                                    }
                                    if ($boolOneSearch) {
                                        if ($arOffer['PREVIEW_PICTURE_FIELD'] && is_array($arOffer['PREVIEW_PICTURE_FIELD']) && $arOffer['PREVIEW_PICTURE_FIELD']['SRC'])
                                            $arOneValue['NEW_PICT'] = $arOffer['PREVIEW_PICTURE_FIELD'];
                                        else
                                            $boolOneSearch = false;
                                        break;
                                    }
                                }
                                unset($arOffer);
                            }

                            if (!$boolOneSearch) {
                                //if($arOneValue['ID']>0){
                                if (!isset($arOneValue['PICT']['SRC']) || !$arOneValue['PICT']['SRC']) {
                                    if (!$bTextViewProp) {
                                        $arOneValue['PICT']['SRC'] = SITE_TEMPLATE_PATH . '/images/svg/noimage_product.svg';
                                        $arOneValue['NO_PHOTO'] = 'Y';
                                    } else {
                                        $isHasPicture = false;
                                    }
                                }
                                //}
                            }
                        }
                        unset($arOneValue);

                        if ($isHasPicture) {
                            $templateRow .= '<div class="bx_item_detail_scu" ' . $arProp['STYLE'] . ' id="#ITEM#_prop_' . $arProp['ID'] . '_cont" data-display_type="LI" data-id="' . $arProp['ID'] . '">' .
                                '<span class="' . $class_title . '"><span>' . htmlspecialcharsex($arProp['NAME']) . $hint_block . '<span class="sku_mdash">&mdash;</span>' . $arProp['VALUE'] . '</span></span>' .
                                '<div class="bx_scu_scroller_container"><div class="bx_scu"><ul id="#ITEM#_prop_' . $arProp['ID'] . '_list" class="list_values_wrapper">';
                        } else {
                            $templateRow .= '<div class="bx_item_detail_size" ' . $arProp['STYLE'] . ' id="#ITEM#_prop_' . $arProp['ID'] . '_cont" data-display_type="LI" data-id="' . $arProp['ID'] . '">' .
                                '<span class="' . $class_title . '">' . htmlspecialcharsex($arProp['NAME']) . '</span>' .
                                '<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_' . $arProp['ID'] . '_list" class="list_values_wrapper">';
                        }
                        foreach ($arProp['VALUES'] as $arOneValue) {
                            //if($arOneValue['ID']>0){
                            $arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
                            if ($isHasPicture && ($arOneValue['NEW_PICT'] || (isset($arOneValue['PICT']['SRC']) && $arOneValue['PICT']['SRC']))) {
                                $str = '<span class="cnt1"><span class="cnt_item' . ($arOneValue['NEW_PICT'] ? ' pp' : '') . '" style="background-image:url(\'' . ($arOneValue['NEW_PICT'] ? $arOneValue['NEW_PICT']['SRC'] : $arOneValue['PICT']['SRC']) . '\');" data-obgi="url(\'' . $arOneValue['PICT']['SRC'] . '\')" title="' . $arProp['NAME'] . ': ' . $arOneValue['NAME'] . '"></span></span>';
                                if (isset($arOneValue['NO_PHOTO']) && $arOneValue['NO_PHOTO'] == 'Y')
                                    $str = '<span class="cnt1 nf"><span class="cnt_item" title="' . $arProp['NAME'] . ': ' . $arOneValue['NAME'] . '"><span class="bg" style="background-image:url(\'' . $arOneValue['PICT']['SRC'] . '\');"></span></span></span>';
                                $templateRow .= '<li class="item ' . $arOneValue['CLASS'] . '" ' . $arOneValue['STYLE'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="li" data-onevalue="' . $arOneValue['ID'] . '"><i title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"></i></li>';
                            } else {
                                $templateRow .= '<li class="item ' . $arOneValue['CLASS'] . '" ' . $arOneValue['STYLE'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="li" data-onevalue="' . $arOneValue['ID'] . '" title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"><i></i><span class="cnt">' . $arOneValue['NAME'] . '</span></li>';
                            }
                            //}
                        }
                        $templateRow .= '</ul></div>' .
                            '</div></div>';
                    }
                    $arSkuTemplate[$iblockId][$arProp['CODE']] = $templateRow;
                }
            }
        } else {
            $arBlockProps = array('HEIGHT', 'WIDTH', 'DEPTH');
            global $iScuBlockCounter;
            $iScuBlockCounter = 1;
            $bShowDimensionsBlock = false;

            $sDimensionsName = array();
            foreach ($arBlockProps as $arProp) {
                if (isset($arSkuProps[$arProp])) {
                    $bShowDimensionsBlock = true;
                    $sDimensionsName[] = mb_substr($arSkuProps[$arProp]['NAME'], 0, 1);
                }
            }

            $sDimensionsName = implode('х', $sDimensionsName);

            if ($bShowDimensionsBlock) {
                //start product-param-choose-block
                $sDimensionsRow = '<div class="product-param-choose-block bx_catalog_item_scu_block" data-code="' . strtolower('DIMENSIONS') . '"><div class="product-param-choose-body"><p class="product-param-choose-title bx_catalog_item_scu_block_title"><span>' . $iScuBlockCounter . '. </span>' . 'Габариты (' . $sDimensionsName . ', мм)' . '</p>';
                $sDimensionsRow .= '<div class="bx_catalog_item_scu_block_base_cost"><span>+ 0 ₽</span>к базовой стоимости</div>';
                $sDimensionsRow .= '<div class="bx_catalog_item_scu_block_body' . $sItemScuBlockClass . '">';
                $iScuBlockCounter++;
            }

            foreach ($arSkuProps as $key => &$arProp) {
                if (in_array($key, $arBlockProps) && $bShowDimensionsBlock) {
                    $templateRow = '';
                    $class_title .= (($arProp["HINT"] && $arProp["SHOW_HINTS"] == "Y") ? ' whint char_name' : '');
                    $hint_block = (($arProp["HINT"] && $arProp["SHOW_HINTS"] == "Y") ? '<span class="hint"><span class="icon"><i>?</i></span><span class="tooltip">' . $arProp["HINT"] . '</span></span>' : '');
                    $show_more_link = false;
                    $count_more = 0;
                    $count_visible = 0;

                    $nameWithDelimeters = $arProp['NAME'];
                    if (strpos($arProp['NAME'], ':') === false) {
                        $nameWithDelimeters .= ': ';
                    }

                    if ($arProp['VALUES']) {
                        foreach ($arProp['VALUES'] as $propKey => $arOneValue) {
                            $arProp['NAME'] = htmlspecialcharsex($arProp['NAME']);

                            if ($arOneValue['CLASS'] && strpos($arOneValue['CLASS'], 'active') !== false) {
                                $arProp['VALUE'] = '<span class="val">' . $arOneValue['NAME'] . '</span>';

                                if (!$max_count)
                                    break;
                            }

                            if ($max_count && $count_visible >= $max_count && (!$arOneValue['CLASS'] || strpos($arOneValue['CLASS'], 'active') === false)) {
                                $arProp['VALUES'][$propKey]['CLASS'] .= ' scu_prop_more';
                                $show_more_link = true;
                                $count_more++;
                            }

                            if (!$arOneValue['CLASS'] || strpos($arOneValue['CLASS'], 'missing') === false) {
                                $count_visible++;
                            }
                        }
                    }

                    if ($show_more_link) {
                        $show_more_link_html = '';
                        $titles = array(
                            Loc::getMessage('SHOW_MORE_SCU_1'),
                            Loc::getMessage('SHOW_MORE_SCU_2'),
                            Loc::getMessage('SHOW_MORE_SCU_3'),
                        );
                        $more_scu_mess = Loc::getMessage('SHOW_MORE_SCU_MAIN', array('#COUNT#' => \Aspro\Functions\CAsproMax::declOfNum($count_more, $titles)));
                        $svgHTML =
                            '<svg xmlns="http://www.w3.org/2000/svg" width="4" height="7" viewBox="0 0 4 7" fill="none">'
                            . '<path d="M0.5 0.5L3.5 3.5L0.5 6.5" stroke="#333" stroke-linecap="round" stroke-linejoin="round"/>'
                            . '</svg>';
                        $show_more_link_html = '<div class="show_more_link"><a class="font_sxs colored_theme_n_hover_bg-svg-stroke" href="' . $arItem['DETAIL_PAGE_URL'] . '">' . $more_scu_mess . $svgHTML . '</a></div>';
                    }


                    if ($arProp['SHOW_MODE'] == 'TEXT') {
                        $templateRow .= '<div class="bx_item_detail_size" ' . $arProp['STYLE'] . ' id="#ITEM#_prop_' . $arProp['ID'] . '_cont" data-display_type="LI" data-id="' . $arProp['ID'] . '">' .
                            '<span class="' . $class_title . '"><span>' . ($arProp['TITLE'] ? $arProp['TITLE'] : $arProp['NAME']) . $hint_block . '</span></span>' .
                            '<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_' . $arProp['ID'] . '_list" class="list_values_wrapper" ' . ($max_count ? 'data-max-count="' . $max_count . '"' : '') . '>';
                        foreach ($arProp['VALUES'] as $arOneValue) {
                            $arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
                            if ($arProp['NAME'] === 'Ширина, мм') {
                                $templateRow .= '<li class="item ' . $arOneValue['CLASS'] . '" ' . $arOneValue['STYLE'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="li"  data-type="width" data-value="' . $arOneValue['NAME'] . '" data-onevalue="' . $arOneValue['ID'] . '" title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"><i></i><span class="cnt">' . $arOneValue['NAME'] . '</span></li>';
                            } elseif ($arProp['NAME'] === 'Глубина, мм') {
                                $templateRow .= '<li class="item ' . $arOneValue['CLASS'] . '" ' . $arOneValue['STYLE'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="li"  data-type="depth" data-value="' . $arOneValue['NAME'] . '" data-onevalue="' . $arOneValue['ID'] . '" title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"><i></i><span class="cnt">' . $arOneValue['NAME'] . '</span></li>';
                            } else {
                                $templateRow .= '<li class="item ' . $arOneValue['CLASS'] . '" ' . $arOneValue['STYLE'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="li" data-onevalue="' . $arOneValue['ID'] . '" title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"><i></i><span class="cnt">' . $arOneValue['NAME'] . '</span></li>';
                            }
                        }
                        $templateRow .= '</ul></div>' . '</div></div>';
                        if ($show_more_link) {
                            $templateRow .= $show_more_link_html;
                        }
                    }

                    $sDimensionsRow .= $templateRow;
                    //$arSkuTemplate[$arProp['CODE']] = $templateRow;
                    //unset($arSkuProps[$key]);
                }
            }

            if ($bShowDimensionsBlock) {
                $sDimensionsRow .= '</div><p class="calculate-according-dimensions" style="display: none"><span data-event="jqm" data-param-form_id="WARDROBE_ORDER" data-name="wardrobe_order">Рассчитать шкаф по своим размерам</span></p>';
                $sDimensionsRow .= '</div></div>';
                //end product-param-choose-block
                $arSkuTemplate['DIMENSIONS'] = $sDimensionsRow;
            }

            foreach ($arSkuProps as $key => &$arProp) {
                if ((!in_array($key, $arBlockProps) && $bShowDimensionsBlock) || !$bShowDimensionsBlock) {
                    $templateRow = '';
                    $class_title .= (($arProp["HINT"] && $arProp["SHOW_HINTS"] == "Y") ? ' whint char_name' : '');
                    $hint_block = (($arProp["HINT"] && $arProp["SHOW_HINTS"] == "Y") ? '<span class="hint"><span class="icon"><i>?</i></span><span class="tooltip">' . $arProp["HINT"] . '</span></span>' : '');
                    $show_more_link = false;
                    $count_more = 0;
                    $count_visible = 0;

                    $nameWithDelimeters = $arProp['NAME'];
                    if (strpos($arProp['NAME'], ':') === false) {
                        $nameWithDelimeters .= ': ';
                    }

                    if ($arProp['VALUES']) {
                        foreach ($arProp['VALUES'] as $propKey => $arOneValue) {
                            $arProp['NAME'] = htmlspecialcharsex($arProp['NAME']);

                            if ($arOneValue['CLASS'] && strpos($arOneValue['CLASS'], 'active') !== false) {
                                $arProp['VALUE'] = '<span class="val">' . $arOneValue['NAME'] . '</span>';

                                if (!$max_count)
                                    break;
                            }

                            if ($max_count && $count_visible >= $max_count && (!$arOneValue['CLASS'] || strpos($arOneValue['CLASS'], 'active') === false)) {
                                $arProp['VALUES'][$propKey]['CLASS'] .= ' scu_prop_more';
                                $show_more_link = true;
                                $count_more++;
                            }

                            if (!$arOneValue['CLASS'] || strpos($arOneValue['CLASS'], 'missing') === false) {
                                $count_visible++;
                            }
                        }
                    }

                    //$iScuBlockCounter

                    if ($show_more_link) {
                        $show_more_link_html = '';
                        $titles = array(
                            Loc::getMessage('SHOW_MORE_SCU_1'),
                            Loc::getMessage('SHOW_MORE_SCU_2'),
                            Loc::getMessage('SHOW_MORE_SCU_3'),
                        );
                        $more_scu_mess = Loc::getMessage('SHOW_MORE_SCU_MAIN', array('#COUNT#' => \Aspro\Functions\CAsproMax::declOfNum($count_more, $titles)));
                        $svgHTML =
                            '<svg xmlns="http://www.w3.org/2000/svg" width="4" height="7" viewBox="0 0 4 7" fill="none">'
                            . '<path d="M0.5 0.5L3.5 3.5L0.5 6.5" stroke="#333" stroke-linecap="round" stroke-linejoin="round"/>'
                            . '</svg>';
                        $show_more_link_html = '<div class="show_more_link"><a class="font_sxs colored_theme_n_hover_bg-svg-stroke" href="' . $arItem['DETAIL_PAGE_URL'] . '">' . $more_scu_mess . $svgHTML . '</a></div>';
                    }


                    $sItemScuBlockClass = '';

                    if ($key == 'HOUSING_LAYOUT') {
                        $sItemScuBlockClass = ' flexbox flexbox--row';
                    }

                    //start product-param-choose-block


                    $templateRow .= '<div class="product-param-choose-block bx_catalog_item_scu_block" data-code="' . strtolower($key) . '"><div class="product-param-choose-body"><p class="product-param-choose-title bx_catalog_item_scu_block_title"><span>' . $iScuBlockCounter . '. </span>' . ($arProp['TITLE'] ? $arProp['TITLE'] : $arProp['NAME']) . '</p>';
                    $templateRow .= '<div class="bx_catalog_item_scu_block_base_cost"><span></span>к базовой стоимости</div>';
                    $templateRow .= '<div class="bx_catalog_item_scu_block_body' . $sItemScuBlockClass . '">';

                    if (($arProp["DISPLAY_TYPE"] == "P" || $arProp["DISPLAY_TYPE"] == "R") && $type_view != 'block') {
                        $templateRow .= '<div class="bx_item_detail_size" ' . $arProp['STYLE'] . ' id="#ITEM#_prop_' . $arProp['ID'] . '_cont" data-display_type="SELECT" data-id="' . $arProp['ID'] . '">' .
                            '<span class="' . $class_title . '"><span>' . ($arProp['TITLE'] ? $arProp['TITLE'] : $arProp['NAME']) . $hint_block . '<span class="sku_mdash">&mdash;</span>' . $arProp['VALUE'] . '</span></span>' .
                            '<div class="bx_size_scroller_container form-control bg"><div class="bx_size"><select id="#ITEM#_prop_' . $arProp['ID'] . '_list" class="list_values_wrapper">';
                        foreach ($arProp['VALUES'] as $arOneValue) {
                            // if($arOneValue['ID']>0){
                            $arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
                            $templateRow .= '<option ' . $arOneValue['SELECTED'] . ' ' . $arOneValue['DISABLED'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="select" data-onevalue="' . $arOneValue['ID'] . '" ';
                            if ($arProp["DISPLAY_TYPE"] == "R") {
                                $templateRow .= 'data-img_src="' . $arOneValue["PICT"]["SRC"] . '" ';
                            }

                            $templateRow .= 'title="' . $nameWithDelimeters . $arOneValue['NAME'] . '">';
                            $templateRow .= '<span class="cnt">' . $arOneValue['NAME'] . '</span>';
                            $templateRow .= '</option>';
                            // }
                        }
                        $templateRow .= '</select></div>' . '</div></div>';
                    } elseif ('TEXT' == $arProp['SHOW_MODE']) {
                        $templateRow .= '<div class="bx_item_detail_size" ' . $arProp['STYLE'] . ' id="#ITEM#_prop_' . $arProp['ID'] . '_cont" data-display_type="LI" data-id="' . $arProp['ID'] . '">' .
                            //'<span class="'.$class_title.'"><span>'.($arProp['TITLE'] ? $arProp['TITLE'] : $arProp['NAME']).$hint_block.'<span class="sku_mdash">&mdash;</span>'.$arProp['VALUE'].'</span></span>'.
                            '<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_' . $arProp['ID'] . '_list" class="list_values_wrapper" ' . ($max_count ? 'data-max-count="' . $max_count . '"' : '') . '>';
                        foreach ($arProp['VALUES'] as $arOneValue) {
                            $arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);

                            $templateRow .= '<li class="item ' . $arOneValue['CLASS'] . '" ' . $arOneValue['STYLE'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="li" data-onevalue="' . $arOneValue['ID'] . '" title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"><i></i><span class="cnt">' . $arOneValue['NAME'] . '</span></li>';
                        }
                        $templateRow .= '</ul></div>' . '</div></div>';
                        if ($show_more_link) {
                            $templateRow .= $show_more_link_html;
                        }
                    } elseif ('PICT' == $arProp['SHOW_MODE']) {

                        $arCurrentTree = array();
                        $showPreviewPictureProp = false;
                        if ($offerShowPreviewPictureProps && is_array($offerShowPreviewPictureProps)) {
                            if (in_array($arProp['CODE'], $offerShowPreviewPictureProps)) {
                                $showPreviewPictureProp = true;
                                if ($arCurrentOffer && $arCurrentOffer['TREE'])
                                    $arCurrentTree = $arCurrentOffer['TREE'];
                            }
                        }

                        $isHasPicture = true;
                        foreach ($arProp['VALUES'] as &$arOneValue) {
                            $boolOneSearch = false;
                            if ($arCurrentTree && $arOneValue['ID'] != 0) {
                                $arRowTree = $arCurrentTree;
                                $arRowTree['PROP_' . $arProp['ID']] = $arOneValue['ID'];

                                foreach ($arItem['OFFERS'] as &$arOffer) {
                                    $boolOneSearch = true;
                                    foreach ($arRowTree as $rkey => $rval) {
                                        if ($rval !== $arOffer['TREE'][$rkey]) {
                                            $boolOneSearch = false;
                                            break;
                                        }
                                    }
                                    if ($boolOneSearch) {
                                        if ($arOffer['PREVIEW_PICTURE_FIELD'] && is_array($arOffer['PREVIEW_PICTURE_FIELD']) && $arOffer['PREVIEW_PICTURE_FIELD']['SRC'])
                                            $arOneValue['NEW_PICT'] = $arOffer['PREVIEW_PICTURE_FIELD'];
                                        else
                                            $boolOneSearch = false;
                                        break;
                                    }
                                }
                                unset($arOffer);
                            }

                            if (!$boolOneSearch) {
                                //if($arOneValue['ID']>0){
                                if (!isset($arOneValue['PICT']['SRC']) || !$arOneValue['PICT']['SRC']) {
                                    if (!$bTextViewProp || $showPreviewPictureProp) {
                                        $arOneValue['PICT']['SRC'] = SITE_TEMPLATE_PATH . '/images/svg/noimage_product.svg';
                                        $arOneValue['NO_PHOTO'] = 'Y';
                                    } else {
                                        $isHasPicture = false;
                                    }
                                }
                                //}
                            }

                            foreach ($arItem['OFFERS'] as &$arOffer) {
                                if ($arRowTree['PROP_' . $arProp['ID']] == $arOffer['TREE']['PROP_' . $arProp['ID']] && !$boolOneSearch) {
                                    if ($arOffer['PREVIEW_PICTURE_FIELD'] && is_array($arOffer['PREVIEW_PICTURE_FIELD']) && $arOffer['PREVIEW_PICTURE_FIELD']['SRC'])
                                        $arOneValue['NEW_PICT'] = $arOffer['PREVIEW_PICTURE_FIELD'];
                                    break;
                                }
                            }
                        }
                        unset($arOneValue);
                        if ($isHasPicture) {
                            $templateRow .= '<div class="bx_item_detail_scu" ' . $arProp['STYLE'] . ' id="#ITEM#_prop_' . $arProp['ID'] . '_cont" data-display_type="LI" data-id="' . $arProp['ID'] . '">' .
                                //'<span class="'.$class_title.'"><span>'.($arProp['TITLE'] ? $arProp['TITLE'] : $arProp['NAME']).$hint_block.'<span class="sku_mdash">&mdash;</span>'.$arProp['VALUE'].'</span></span>'.
                                '<div class="bx_scu_scroller_container"><div class="bx_scu"><ul id="#ITEM#_prop_' . $arProp['ID'] . '_list" class="list_values_wrapper" ' . ($max_count ? 'data-max-count="' . $max_count . '"' : '') . '>';
                        } else {
                            $templateRow .= '<div class="bx_item_detail_size" ' . $arProp['STYLE'] . ' id="#ITEM#_prop_' . $arProp['ID'] . '_cont" data-display_type="LI" data-id="' . $arProp['ID'] . '">' .
                                '<span class="' . $class_title . '">' . htmlspecialcharsex($arProp['NAME']) . '</span>' .
                                '<div class="bx_size_scroller_container"><div class="bx_size"><ul id="#ITEM#_prop_' . $arProp['ID'] . '_list" class="list_values_wrapper">';

                        }

                        foreach ($arProp['VALUES'] as $arOneValue) {
                            //if($arOneValue['ID']>0){
                            $arOneValue['NAME'] = htmlspecialcharsbx($arOneValue['NAME']);
                            if ($isHasPicture && ($arOneValue['NEW_PICT'] || (isset($arOneValue['PICT']['SRC']) && $arOneValue['PICT']['SRC']))) {
                                if (!empty($arOneValue['PICT']['SRC'])) {
                                    $arPrevPic = CFile::ResizeImageGet(CFile::GetFileArray($arOneValue['PICT']['ID']), array('width' => 200, 'height' => 200), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                    if (!empty($arPrevPic['src'])) {
                                        $arPrevPic['SRC'] = $arPrevPic['src'];
                                        $arOneValue['NEW_PICT'] = $arPrevPic;
                                    }
                                }
                                $str = '<span class="cnt1"><span class="cnt_item' . ($arOneValue['NEW_PICT'] ? ' pp' : '') . '" style="background-image:url(\'' . ($arOneValue['NEW_PICT'] ? $arOneValue['NEW_PICT']['SRC'] : $arOneValue['PICT']['SRC']) . '\');" data-obgi=" ' . $arOneValue['PICT']['SRC'] . ' " title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"></span></span>';
                                if (isset($arOneValue['NO_PHOTO']) && $arOneValue['NO_PHOTO'] == 'Y')
                                    $str = '<span class="cnt1 nf"><span class="cnt_item" title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"><span class="bg no-image" style="background-image:url(\'' . $arOneValue['PICT']['SRC'] . '\');"></span></span></span>';
                                if (($arProp['TITLE'] ? $arProp['TITLE'] : $arProp['NAME']) == 'Цвет корпуса' || ($arProp['TITLE'] ? $arProp['TITLE'] : $arProp['NAME']) == 'Цвет профиля') {
                                    $templateRow .= '<li class="item ' . $arOneValue['CLASS'] . '" ' . $arOneValue['STYLE'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="li" data-type="color_ref" data-value="' . $arOneValue['NAME'] . '" data-onevalue="' . $arOneValue['ID'] . '"><i title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"></i>' . $str . '<span class="line-text-color">' . $arOneValue['NAME'] . '</span></li>';
                                } else {
                                    $templateRow .= '<li class="item ' . $arOneValue['CLASS'] . '" ' . $arOneValue['STYLE'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="li" data-onevalue="' . $arOneValue['ID'] . '"><i title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"></i>' . $str . '</li>';
                                }
                            } else {
                                $templateRow .= '<li class="item ' . $arOneValue['CLASS'] . '" ' . $arOneValue['STYLE'] . ' data-treevalue="' . $arProp['ID'] . '_' . $arOneValue['ID'] . '" data-showtype="li" data-onevalue="' . $arOneValue['ID'] . '" title="' . $nameWithDelimeters . $arOneValue['NAME'] . '"><i></i><span class="cnt">' . $arOneValue['NAME'] . '</span></li>';
                            }
                            //}
                        }
                        $templateRow .= '</ul></div>' . '</div></div>';

                        $arBlockWithDetailImg = array('HOUSING_LAYOUT');

                        if (in_array($arProp['CODE'], $arBlockWithDetailImg)) {
                            $templateRow .= '<div class="bx_catalog_item_scu_img_view"></div>';
                        }

                        if ($show_more_link) {
                            $templateRow .= $show_more_link_html;
                        }
                    }

                    $templateRow .= '</div></div></div>';
                    //end product-param-choose-block

                    $arSkuTemplate[$arProp['CODE']] = $templateRow;
                    $iScuBlockCounter++;


                }
            }
        }
        unset($templateRow, $arProp);
        return $arSkuTemplate;
    }

    public static function drawFormField($FIELD_SID, $arQuestion)
    {
        ?>
        <? $arQuestion["HTML_CODE"] = str_replace('name=', 'data-sid="' . $FIELD_SID . '" name=', $arQuestion["HTML_CODE"]); ?>
        <? $arQuestion["HTML_CODE"] = str_replace('left', '', $arQuestion["HTML_CODE"]); ?>
        <? $arQuestion["HTML_CODE"] = str_replace('size="0"', '', $arQuestion["HTML_CODE"]); ?>
        <? if ($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden'):?>
        <?= $arQuestion["HTML_CODE"]; ?>
    <? else:?>
        <? $arNotMultiple = [33]; ?>
        <? if ($FIELD_SID == 'FILE') {
            foreach ($arQuestion['STRUCTURE'] as $key => $value) {
                if (!in_array($value['QUESTION_ID'], $arNotMultiple)) {
                    $arQuestion["HTML_CODE"] = str_replace('name="form_file_' . $value['ID'] . '"', 'name="' . $FIELD_SID . '[]" data-type="multiple"', $arQuestion["HTML_CODE"]);
                    //$arQuestion["HTML_CODE"] = str_replace('name="form_file_' . $value['ID'] . '"', 'name="form_file_' . $value['ID'] . '[]"', $arQuestion["HTML_CODE"]);
                }
            }
        } ?>

        <div class="form-control">
            <label
                data-type="<?= $FIELD_SID ?>"><span><?= $arQuestion["CAPTION"] ?><?= ($arQuestion["REQUIRED"] == "Y" ? '&nbsp;<span class="star">*</span>' : '') ?></span></label>
            <?
            if (strpos($arQuestion["HTML_CODE"], "class=") === false)
                $arQuestion["HTML_CODE"] = str_replace('input', 'input class=""', $arQuestion["HTML_CODE"]);

            if (is_array($arResult["FORM_ERRORS"]) && array_key_exists($FIELD_SID, $arResult['FORM_ERRORS']))
                $arQuestion["HTML_CODE"] = str_replace('class="', 'class="error ', $arQuestion["HTML_CODE"]);

            if ($arQuestion["REQUIRED"] == "Y")
                $arQuestion["HTML_CODE"] = str_replace('name=', 'required name=', $arQuestion["HTML_CODE"]);

            if ($arQuestion["STRUCTURE"][0]["FIELD_TYPE"] == "email")
                $arQuestion["HTML_CODE"] = str_replace('type="text"', 'type="email" placeholder="mail@domen.com"', $arQuestion["HTML_CODE"]);

            if ((strpos($arQuestion["HTML_CODE"], "phone") !== false) || (strpos(strToLower($FIELD_SID), "phone") !== false))
                $arQuestion["HTML_CODE"] = str_replace('type="text"', 'type="tel"', $arQuestion["HTML_CODE"]);
            ?>
            <? if ($FIELD_SID == 'RATING'):?>
                <div class="votes_block nstar big with-text">
                    <div class="ratings">
                        <div class="inner_rating">
                            <? for ($i = 1; $i <= 5; $i++):?>
                                <div class="item-rating"
                                     data-message="<?= GetMessage('RATING_MESSAGE_' . $i) ?>"><?= static::showIconSvg("star", SITE_TEMPLATE_PATH . "/images/svg/star.svg"); ?></div>
                            <?endfor; ?>
                        </div>
                    </div>
                    <div class="rating_message muted"
                         data-message="<?= GetMessage('RATING_MESSAGE_0') ?>"><?= GetMessage('RATING_MESSAGE_0') ?></div>
                    <?= str_replace('type="text"', 'type="hidden"', $arQuestion["HTML_CODE"]) ?>
                </div>
            <? else:?>
                <?= $arQuestion["HTML_CODE"] ?>
            <?endif; ?>
        </div>
    <?endif; ?>
        <?
    }

    public static function ShowBasketWithCompareLink($class_link = 'top-btn hover', $class_icon = '', $show_price = false, $class_block = '', $force_show = false, $bottom = false, $div_class = '')
    {
        ?>
        <? global $APPLICATION, $arTheme, $arBasketPrices;
        static $basket_call;
        $type_svg = '';
        if ($class_icon) {
            $tmp = explode(' ', $class_icon);
            $type_svg = '_' . $tmp[0];
        }

        $iCalledID = ++$basket_call; ?>
        <? if (($arTheme['ORDER_BASKET_VIEW']['VALUE'] == 'NORMAL' || ($arTheme['ORDER_BASKET_VIEW']['VALUE'] == 'BOTTOM' && $bottom)) || $force_show):?>
        <? if ($div_class):?>
            <div class="<?= $div_class ?>">
        <?endif; ?>
        <? Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('header-basket-with-compare-block' . $iCalledID); ?>
        <? if ($arTheme['CATALOG_COMPARE']['VALUE'] != 'N'):?>
            <? if ($class_block):?>
                <div class="<?= $class_block; ?>">
            <?endif; ?>
            <? $APPLICATION->IncludeComponent("bitrix:main.include", ".default",
                array(
                    "COMPONENT_TEMPLATE" => ".default",
                    "PATH" => SITE_DIR . "ajax/show_compare_preview_top.php",
                    "AREA_FILE_SHOW" => "file",
                    "AREA_FILE_SUFFIX" => "",
                    "AREA_FILE_RECURSIVE" => "Y",
                    "CLASS_LINK" => $class_link,
                    "CLASS_ICON" => $class_icon,
                    "FROM_MODULE" => "Y",
                    "EDIT_TEMPLATE" => "standard.php"
                ),
                false, array('HIDE_ICONS' => 'Y')
            ); ?>
            <? if ($class_block):?>
                </div>
            <?endif; ?>
        <?endif; ?>
        <? if (self::getShowBasket()):?>
            <!-- noindex -->
            <? if ($class_block):?>
                <div class="<?= $class_block; ?>">
            <?endif; ?>
            <a rel="nofollow"
               class="basket-link delay <?= $class_link; ?> <?= $class_icon; ?> <?= ($arBasketPrices['DELAY_COUNT'] ? 'basket-count' : ''); ?>"
               href="<?= $arTheme['BASKET_PAGE_URL']['VALUE']; ?>delayed/"
               title="<?//=$arBasketPrices['DELAY_SUMM_TITLE'];
               ?>">
							<span class="js-basket-block">
								<?= self::showIconSvg("wish " . $class_icon, SITE_TEMPLATE_PATH . "/images/svg/chosen.svg"); ?>
								<span class="title dark_link"><?= Loc::getMessage('JS_BASKET_DELAY_TITLE'); ?></span>
								<span class="count"><?= $arBasketPrices['DELAY_COUNT']; ?></span>
							</span>
            </a>
            <? if ($class_block):?>
                </div>
            <?endif; ?>
            <? if ($class_block):?>
                <div class="<?= $class_block; ?> <?= $arTheme['ORDER_BASKET_VIEW']['VALUE'] ? 'top_basket' : '' ?>">
            <?endif; ?>
            <a rel="nofollow"
               class="basket-link basket <?= ($show_price ? 'has_prices' : ''); ?> <?= $class_link; ?> <?= $class_icon; ?> <?= ($arBasketPrices['BASKET_COUNT'] ? 'basket-count' : ''); ?>"
               href="<?= $arTheme['BASKET_PAGE_URL']['VALUE']; ?>" title="<?//=$arBasketPrices['BASKET_SUMM_TITLE'];
            ?>">
							<span class="js-basket-block">
								<?= self::showIconSvg("basket " . $class_icon, SITE_TEMPLATE_PATH . "/images/svg/basket.svg"); ?>
                                <? if ($show_price): ?>
									<span class="wrap">
								<?endif;
                                ?>
								<span class="title dark_link"><?= Loc::getMessage('JS_BASKET_TITLE'); ?></span>
								<? if ($show_price): ?>
									<span
                                        class="prices"><?= ($arBasketPrices['BASKET_COUNT'] ? $arBasketPrices['BASKET_SUMM'] : $arBasketPrices['BASKET_SUMM_TITLE_SMALL']) ?></span>
									</span>
								<?endif;
                                ?>
								<span class="count"><?= $arBasketPrices['BASKET_COUNT']; ?></span>
							</span>
            </a>
            <span class="basket_hover_block loading_block loading_block_content"></span>

            <? if ($class_block):?>
                </div>
            <?endif; ?>
            <!-- /noindex -->
        <?endif; ?>
        <? Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('header-basket-with-compare-block' . $iCalledID, ''); ?>
        <? if ($div_class):?>
            </div>
        <?endif; ?>
    <?endif; ?>
    <?
    }

    public static function ShowMobileRegions()
    {
        global $APPLICATION, $arRegion, $arRegions;

        if ($arRegion):
            $type_regions = self::GetFrontParametrValue('REGIONALITY_TYPE');
            static $mregions_call;

            $iCalledID = ++$mregions_call;
            $arRegions = CMaxRegionality::getRegions();
            $regionID = ($arRegion ? $arRegion['ID'] : '');
            $iCountRegions = count($arRegions); ?>
            <? Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID('mobile-region-block' . $iCalledID); ?>
            <!-- noindex -->
            <? $APPLICATION->IncludeComponent(
            "bitrix:main.include",
            "",
            array(
                "AREA_FILE_SHOW" => "file",
                "PATH" => SITE_DIR . "include/top_page/search.title.catalog.php",
                "EDIT_TEMPLATE" => "include_area.php",
                'SEARCH_ICON' => 'Y'
            )
        ); ?>
            <div class="menu middle mobile_regions">
                <ul>
                    <li>
                        <? if (self::GetFrontParametrValue('REGIONALITY_SEARCH_ROW') != 'Y'): ?>
                        <a rel="nofollow" href="" class="dark-color<?= ($iCountRegions > 1 ? ' parent' : '') ?>">
                            <? else: ?>
                            <a rel="nofollow" href="" class="js_city_chooser dark-color" data-event="jqm"
                               data-name="city_chooser" data-param-url="<?= urlencode($APPLICATION->GetCurUri()); ?>"
                               data-param-form_id="city_chooser">
                                <?endif;
                                ?>
                                <?= static::showIconSvg('region_arrow', SITE_TEMPLATE_PATH . '/images/svg/location_mm.svg') ?>
                                <span><?= $arRegion['NAME']; ?></span>
                                <? if ($iCountRegions > 1):?>
                                    <span class="arrow">
									<?= static::showIconSvg("triangle", SITE_TEMPLATE_PATH . '/images/svg/trianglearrow_right.svg', '', '', true, false); ?>
								</span>
                                <?endif; ?>
                            </a>
                            <? if (self::GetFrontParametrValue('REGIONALITY_SEARCH_ROW') != 'Y'):?>
                                <? if ($iCountRegions > 1): // if more than one
                                    ?>
                                    <? $host = (CMain::IsHTTPS() ? 'https://' : 'http://');
                                    $uri = $APPLICATION->GetCurUri(); ?>
                                    <ul class="dropdown">
                                        <li class="menu_back"><a href="" class="dark-color"
                                                                 rel="nofollow"><?= static::showIconSvg('back_arrow', SITE_TEMPLATE_PATH . '/images/svg/return_mm.svg') ?><?= Loc::getMessage('MAX_T_MENU_BACK') ?></a>
                                        </li>
                                        <li class="menu_title"><?= Loc::getMessage('MAX_T_MENU_REGIONS') ?></li>

                                        <?
                                        $arMainDomain = '';
                                        $arDomainRegion = '';

                                        foreach ($arResult['REGIONS'] as $key => $value) {
                                            if ($value['PROPERTY_DEFAULT_VALUE'] == 'Y') {
                                                $arMainDomain = $value['PROPERTY_MAIN_DOMAIN_VALUE'];
                                            }
                                            if ($value['PROPERTY_MAIN_DOMAIN_VALUE'] == $_SERVER['SERVER_NAME']) {
                                                $arDomainRegion = $value;
                                            }
                                        }
                                        ?>

                                        <? foreach ($arRegions as $arItem):?>
                                            <? $href = $uri;
                                            if ($arItem['PROPERTY_MAIN_DOMAIN_VALUE']) {
                                                $href = $host . $arItem['PROPERTY_MAIN_DOMAIN_VALUE'] . $uri;
                                            } elseif (!empty($arItem['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']) && !empty($arRegions[$arItem['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']]['PROPERTY_MAIN_DOMAIN_VALUE'])) {
                                                $href = $host . $arRegions[$arItem['PROPERTY_REGION_TAG_NEAREST_REGION_VALUE']]['PROPERTY_MAIN_DOMAIN_VALUE'] . $arItem['URL'];
                                            } elseif (!empty($arMainDomain) && $arMainDomain != $arDomainRegion['PROPERTY_MAIN_DOMAIN_VALUE']) {
                                                $href = $host . $arMainDomain . $arItem['URL'];
                                            }
                                            ?>
                                            <li>
                                                <div data-href="<?= $href ?>"
                                                     class="dark-color mobile_regions_city_item"
                                                     data-id="<?= $arItem['ID']; ?>"><?= $arItem['NAME']; ?></div>
                                            </li>
                                        <?endforeach; ?>
                                    </ul>
                                <?endif; ?>
                            <?endif; ?>
                    </li>
                </ul>
            </div>
            <!-- /noindex -->
            <? Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID('mobile-region-block' . $iCalledID); ?>
        <?endif;
    }
}