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

<? if($row['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_COUNT) : ?>
    <?= Loc::getMessage('SMARTSEO__GRID__CONDITION__LABEL') ?>
    <strong class="aspro-ui-text aspro-ui-text--color-dark-blue">
        <?= Loc::getMessage('SMARTSEO__GRID__CONDITION__EXCEPTION_BY_COUNT') ?>
    </strong>
<? endif ?>

<? if($row['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_VALUES) : ?>
    <?= Loc::getMessage('SMARTSEO__GRID__CONDITION__LABEL') ?>
    <strong class="aspro-ui-text aspro-ui-text--color-dark-blue">
        <?= Loc::getMessage('SMARTSEO__GRID__CONDITION__EXCEPTION_BY_VALUES') ?>
    </strong>
<? endif ?>

<? if($row['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_PROPERTIES) : ?>
    <?= Loc::getMessage('SMARTSEO__GRID__CONDITION__LABEL') ?>
    <strong class="aspro-ui-text aspro-ui-text--color-dark-blue">
        <?= Loc::getMessage('SMARTSEO__GRID__CONDITION__EXCEPTION_BY_PROPERTIES') ?>
    </strong>
<? endif ?>