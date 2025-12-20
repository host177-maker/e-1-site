<?php
use
    Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

\CJSCore::Init(['core_condtree']);

$conditionTree = new Smartseo\Condition\ConditionTree();
$conditionTree
  ->addControlBuild(new Smartseo\Condition\Controls\Group2BuildControls())
  ->addControlBuild(new Smartseo\Condition\Controls\IblockPropertyBuildControls(26));

$conditionTree->init(
  BT_COND_MODE_DEFAULT, Smartseo\Condition\ConditionTree::BT_COND_BUILD_SMARTSEO, [
    'FORM_NAME' => 'form',
    'PREFIX' => 'CONDITION',
    'CONT_ID' => 'condition_tre',
    'JS_NAME' => 'conditionTreeObject',
  ]
);
$_condition = null;
if($_POST['CONDITION']) {
    $_condition = $conditionTree->Parse($_POST['CONDITION']);

    $parse = new Smartseo\Condition\ConditionParseHandler();

    $parseresult = $parse->parseCondition($_condition, []);

    echo '<pre>';
    var_dump($parseresult);
    echo '</pre>';
}

?>
<form id="form" method="post">
    <div id="condition_tre"></div>
    <?
        echo $conditionTree->showTreeConditions($_condition ?: []);
    ?>
    <button type="submit">Отправить</button>
</form>
