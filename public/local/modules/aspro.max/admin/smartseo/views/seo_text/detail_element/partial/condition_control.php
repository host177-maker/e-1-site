<?php
/**
 *  @var string $alias
 *  @var array $iblockId
 *  @var array $condition
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

$conditionTree = new Smartseo\Condition\ConditionTree();
$conditionTree
  ->addControlBuild(new Smartseo\Condition\Controls\GroupBuildControls())
  ->addControlBuild(new Smartseo\Condition\Controls\IblockPropertyBuildControls($iblockId, [
      'ONLY_PROPERTY_SMART_FILTER' => 'N',
      'SHOW_PROPERTY_SKU' => 'N',
  ]));

$conditionTree->init(
  BT_COND_MODE_DEFAULT, Smartseo\Condition\ConditionTree::BT_COND_BUILD_SMARTSEO, [
    'FORM_NAME' => 'form_seo_text_detail',
    'PREFIX' => $alias . '[CONDITION]',
    'CONT_ID' => 'condition_tree',
    'JS_NAME' => 'conditionTreeObject',
  ]
);
?>
<div id="condition_tree"></div>
<?
  $_condition = $condition ? unserialize($condition, ['allowed_classes' => false]) : null;
  echo  $conditionTree->showTreeConditions($_condition ?: []);
?>