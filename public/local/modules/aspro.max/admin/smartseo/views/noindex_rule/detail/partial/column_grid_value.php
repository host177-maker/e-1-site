<?php
/**
 *  @var int $noindexRuleId
 *  @var int $index
 *  @var array $row
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;
?>

<? if($row['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_COUNT
  || $row['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_VALUES) : ?>
      <span class="aspro-smartseo__condition__logic-group__label aspro-smartseo__condition__logic-group__label--ml-0">
          <?= Loc::getMessage('SMARTSEO__GRID__CONDITION__MORE') ?>
      </span>
      <span>
        <?= $row['VALUE'] ?>
      </span>
    </strong>
<? endif ?>


<? if($row['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_PROPERTIES) : ?>
    <div>
        <? foreach ($row['PROPERTIES'] as $property) : ?>
            <div class="aspro-smartseo__condition__item aspro-smartseo__condition__item--inline aspro-smartseo__condition__item--sm">
              <div class="aspro-smartseo__condition__name">
                <?= $property['PROPERTY_NAME'] ?> [<?= $property['PROPERTY_ID'] ?>]
              </div>
            </div>
        <? endforeach ?>
    </div>
<? endif ?>