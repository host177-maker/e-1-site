<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?use \Bitrix\Main\Web\Json,
Bitrix\Main\Localization\Loc;?>

<?
$showElements = true;
?>

<?if($arResult["ITEMS"] && $showElements):?>
        <?foreach($arResult["ITEMS"] as $key => $arItem):?>
            <?$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));
        ?>

            <div id="additional-gallery-wrap">
                <div class="ordered-block">
                    <div class="additional-gallery-our-work <?=($templateData['OFFERS_INFO']['OFFERS'] && 'TYPE_1' === $arParams['TYPE_SKU'] ? ' hidden' : '')?>">
                        <?//small gallery?>
                        <div class="small-gallery-block"<?//=($bShowSmallGallery ? '' : ' style="display:none;"');?>>
                            <div class="row flexbox flexbox--row">
                                <?foreach($arItem['PROPERTIES']['FILE']['VALUE'] as $i => $arPhoto):?>
                                    <?
                                    if (!empty($arPhoto)) {
                                        $resize_image = CFile::ResizeImageGet($arPhoto,
                                        Array("width" => 200, "height" => 200),
                                        BX_RESIZE_IMAGE_EXACT, false);
                                        $resImageFull = CFile::ResizeImageGet($arPhoto,
                                        Array("width" => 1000, "height" => 1000),
                                        BX_RESIZE_IMAGE_EXACT, false);
                                        ?>
                                        <div class="col-md-4 col-sm-4 col-xs-6 small-gallery-item">
                                            <div class="item">
                                                <div class="wrap"><a href="<?=$resImageFull['src']?>" class="fancyOurWork" data-fancybox="small-gallery" target="_blank" title="<?=$arItem['NAME']?>">
                                                    <img data-src="<?=$resize_image['src']?>" src="<?=\Aspro\Functions\CAsproMax::showBlankImg($resize_image['src']);?>" class="lazy img-responsive inline" title="<?=$arItem['NAME']?>" alt="<?=$arItem['NAME']?>" /></a>
                                                </div>
                                            </div>
                                        </div>
                                    <? } ?>
                                <?endforeach;?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?endforeach;?>
<?endif;?>

<?$iScuBlockCounter++;?>