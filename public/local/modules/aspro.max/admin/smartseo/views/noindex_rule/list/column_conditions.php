<?php

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

?>
<div class="aspro-smartseo__grid-text__note aspro-ui-util--mb10">
   <?= Loc::getMessage('SMARTSEO__GRID__CONDITION__LABEL') ?>:
</div>
<?
    $index = 0;
?>
<? foreach ($row['CONDITIONS'] as $condition) : ?>
<? $index++ ?>
<div class="aspro-ui-util--mb10">
    <? if($condition['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_COUNT) : ?>
        <?= $index ?>. <?= Loc::getMessage('SMARTSEO__GRID__CONDITION__EXCEPTION_BY_COUNT', [
            '#VALUE#' => $condition['VALUE'],
        ]) ?>
    <? endif ?>

    <? if($condition['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_VALUES) : ?>
        <?= $index ?>. <?= Loc::getMessage('SMARTSEO__GRID__CONDITION__EXCEPTION_BY_VALUES', [
            '#VALUE#' => $condition['VALUE'],
        ]) ?>
    <? endif ?>

    <? if($condition['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_PROPERTIES) : ?>
        <?= $index ?>. <?= Loc::getMessage('SMARTSEO__GRID__CONDITION__EXCEPTION_BY_PROPERTIES') ?>
        <div>
            <? foreach ($condition['PROPERTIES'] as $property) : ?>
                <div class="aspro-smartseo__condition__item aspro-smartseo__condition__item--inline aspro-smartseo__condition__item--sm">
                  <div class="aspro-smartseo__condition__name">
                    <?= $property['PROPERTY_NAME'] ?> [<?= $property['PROPERTY_ID'] ?>]
                  </div>
                </div>
            <? endforeach ?>
        </div>
    <? endif ?>
</div>
<? endforeach; ?>


