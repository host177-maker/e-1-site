<?php
/**
 *  @var array $row
 */

use Bitrix\Main\Localization\Loc;

$_logicAllLabel = '';
$_logicAllClass = '';

switch ($row['CONDITION_GROUP']['All']) {
    case 'AND':
        $_logicAllLabel = Loc::getMessage('SMARTSEO_GRID_CONDITION_LOGIC_AND');
        break;

    case 'OR':
        $_logicAllLabel = Loc::getMessage('SMARTSEO_GRID_CONDITION_LOGIC_OR');
        $_logicAllClass = 'bg--gray';
        break;
}

if($row['CONDITION_GROUP']['True'] === 'False') {
    $_logicAllLabel .= ' ' . Loc::getMessage('SMARTSEO_GRID_CONDITION_LOGIC_FALSE');
}

$index = 0;
$lastIndex = count($row['CONDITION_PROPERTY']) - 1;
$lastIblockId = array_pop(array_column($row['CONDITION_PROPERTY'], 'IBLOCK_ID'));
$isOneProperty = $lastIndex < 1;

?>
<div class="aspro-smartseo__wrapper-condition <?= $isOneProperty ? 'aspro-smartseo__wrapper-condition--no-line' : '' ?>">
  <? if(!$isOneProperty) : ?>
  <div class="aspro-smartseo__condition__logic-all <?= $_logicAllClass ?>">
      <?= $_logicAllLabel ?>
  </div>
  <? endif ?>

  <?
  $tempIblockId = null;
  ?>
  <? foreach ($row['CONDITION_PROPERTY'] as $property) : ?>
      <? if (!$tempIblockId || $tempIblockId != $property['GROUP_ID']) : ?>
          <? if ($tempIblockId) : ?>
            </div><!-- .aspro-smartseo__condition -->
        <? endif ?>
        <div class="aspro-smartseo__condition__label
        <?= $tempIblockId ? 'aspro-smartseo__condition__label--mt' : '' ?>
             "><?= $property['GROUP_NAME'] ?>:</div>
        <div class="aspro-smartseo__condition
        <?= !$tempIblockId ? 'aspro-smartseo__condition--first' : '' ?>
        <?= $property['GROUP_ID'] == $lastIblockId ? 'aspro-smartseo__condition--last' : '' ?>"
             >

          <? $tempIblockId = $property['GROUP_ID'] ?>
      <? endif ?>

      <div class="aspro-smartseo__condition__item">
        <div class="aspro-smartseo__condition__name">
          <?= $property['NAME'] ?>
        </div>
        <div class="aspro-smartseo__condition__value">
          <? foreach ($property['CONDITIONS'] as $key => $condition) : ?>
          <? $_values = array_filter($condition['VALUES']); ?>
          <div class="aspro-smartseo__condition__logic-group__values">
            <span class="aspro-smartseo__condition__logic-group__label"><?= $condition['LOGIC_LABEL'] ?></span>
            <span>
                <? if($_values) : ?>
                    <?= implode(', ', $condition['DISPLAY_VALUES']) ?>
                <? else : ?>
                    <?= Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_ALL_VALUES') ?>
                <? endif ?>
            </span>
          </div>
          <? endforeach ?>
        </div>
      </div>

      <? if ($lastIndex == $index) : ?>
        </div><!-- .aspro-smartseo__condition -->
    <? endif ?>

    <? $index++ ?>
<? endforeach ?>
