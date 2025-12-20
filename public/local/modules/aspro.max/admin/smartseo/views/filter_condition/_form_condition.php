<?php

/**
 *  @var string $aliasCondition
 *  @var string $aliasSitemap
 *  @var string $dataCondition
 *  @var array $dataSitemap
 *  @var int $filterRuleId
 *  @var int $filterRuleIblockId
 *  @var array $listTypeGenerate
 *  @var array $listTypeGenerate
 *  @var array $listSitemap
 *  @var array $listChangefreq
 *  @var array $listPriority
 *  @var string $defaultChangefreq
 *  @var float $defaultPriority
 *
 *  @var bollean $isCatalogModule
 */
use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

$_suffix = $this->getUnique();

$conditionTree = new Smartseo\Condition\ConditionTree();
$conditionTree
  ->addControlBuild(new Smartseo\Condition\Controls\GroupBuildControls())
  ->addControlBuild(new Smartseo\Condition\Controls\IblockPropertyBuildControls($filterRuleIblockId));

if($isCatalogModule) {
    $conditionTree->addControlBuild(new Smartseo\Condition\Controls\CatalogGroupBuildControls());
}

$conditionTree->init(
  BT_COND_MODE_DEFAULT, Smartseo\Condition\ConditionTree::BT_COND_BUILD_SMARTSEO, [
    'FORM_NAME' => 'form_condition_' . $_suffix,
    'PREFIX' => $aliasCondition . '[CONDITION]',
    'CONT_ID' => 'condition_tree_' . $_suffix,
    'JS_NAME' => 'conditionTreeObject' . $_suffix,
  ]
);

?>
<form id="form_condition_<?= $_suffix ?>" method="POST" action="<?= Helper::url('filter_condition/update') ?>">
  <?= bitrix_sessid_post() ?>
  <input page-role="form-field-ID" type="hidden" name="<?= $aliasCondition ?>[ID]" value="<?= $dataCondition['ID'] ?>">
  <input page-role="form-field-GENERATE" type="hidden" name="generate" value="N">
  <input type="hidden" name="<?= $aliasCondition ?>[FILTER_RULE_ID]" value="<?= $filterRuleId ?>">
  <input type="hidden" name="<?= $aliasCondition ?>[FILTER_RULE_IBLOCK_ID]" value="<?= $filterRuleIblockId ?>">

  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input id="<?= $aliasCondition ?>_ACTIVE_<?= $_suffix ?>" class="adm-designed-checkbox" type="checkbox"
                   name="<?= $aliasCondition ?>[ACTIVE]"
                   <?= $dataCondition['ACTIVE'] !== 'N' ? 'checked' : '' ?>  value="Y">
            <label class="adm-designed-checkbox-label" for="<?= $aliasCondition ?>_ACTIVE_<?= $_suffix ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>"></label>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_NAME') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input data-field="name" type="text" name="<?= $aliasCondition ?>[NAME]" value="<?= htmlspecialchars($dataCondition['NAME']) ?>"
                   maxlength="255" class="aspro-smartseo__form-control__input">
            <span class="aspro-smartseo__loading"></span>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SORT') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
           <div class="aspro-smartseo__form-control">
            <input type="text" name="<?= $aliasCondition ?>[SORT]" size="7" maxlength="10"
                   value="<?= $dataCondition['SORT'] ?: 500 ?>">
           </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
            <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_CONDITION') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div id="condition_tree_<?= $_suffix ?>"></div>
          <?
          $_condition = unserialize($dataCondition['CONDITION_TREE'], ['allowed_classes' => false]);
          echo $conditionTree->showTreeConditions($_condition ?: []);
          ?>
        </td>
      </tr>

      <tr class="heading" ><td colspan="2"><?= Loc::getMessage('SMARTSEO_FORM_GROUP_URL_PAGES') ?></td></tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_URL_CLOSE_INDEXING') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input id="<?= $aliasCondition ?>_URL_CLOSE_INDEXING_<?= $_suffix ?>" type="checkbox" class="adm-designed-checkbox"
                name="<?= $aliasCondition ?>[URL_CLOSE_INDEXING]" value="Y"
                <?= $dataCondition['URL_CLOSE_INDEXING'] === 'Y' ? 'checked' : '' ?>
                >
            <label class="adm-designed-checkbox-label" for="<?= $aliasCondition ?>_URL_CLOSE_INDEXING_<?= $_suffix ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_URL_CLOSE_INDEXING') ?>">
            </label>
          </div>
        </td>
      </tr>

      <tr>
        <td colspan="2">
          <div class="aspro-smartseo__form-detail__separator">
            <div class="aspro-smartseo__form-detail__separator__legend">
              <span><?= Loc::getMessage('SMARTSEO_FORM_GROUP_URL') ?></span>
            </div>
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_URL_TEMPLATE') ?>:</b>
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control aspro-smartseo__form-control--w100">
            <div page-role="control-engine-template">
              <div class="aspro-smartseo__form-control__input-menu-wrapper aspro-smartseo__form-control__input-menu-wrapper--w100">
              <div control-role="input-wrapper">
                <input control-role="input" type="text" required name="<?= $aliasCondition ?>[URL_TEMPLATE]" class="aspro-smartseo__form-control__input" value="<?= $dataCondition['URL_TEMPLATE'] ?>" />
              </div>
              <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
              </div>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <?= Loc::getMessage('SMARTSEO_FORM_ENTITY_URL_TYPE_GENERATE') ?>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select class="aspro-smartseo__form-control__select aspro-smartseo__form-control__select--w50" name="<?= $aliasCondition ?>[URL_TYPE_GENERATE]">
              <? foreach ($listTypeGenerate as $value => $label) : ?>
                  <option value="<?= $value ?>" <?= $dataCondition['URL_TYPE_GENERATE'] == $value ? 'selected' : ''?> ><?= $label ?></option>
              <? endforeach ?>
            </select>
          </div>
        </td>
      </tr>

      <tr>
        <td colspan="2">
          <div class="aspro-smartseo__form-detail__separator">
            <div class="aspro-smartseo__form-detail__separator__legend">
              <span><?= Loc::getMessage('SMARTSEO_FORM_GROUP_SITEMAP') ?></span>
            </div>
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SITEMAP') ?>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <select page-role="form-field-SITEMAP" name="<?= $aliasSitemap ?>[SITEMAP_ID]" class="aspro-smartseo__form-control__select">
            <option selected value="0"><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_SITEMAP') ?></option>
              <? foreach ($listSitemap as $key => $value) : ?>
                <?
                    $_selected = $dataSitemap['SITEMAP_ID'] == $key;
                ?>
                <option <?= $_selected ? 'selected' : '' ?>  value="<?= $key ?>">
                   <?=
                      $value
                      ? '[' . $key . '] ' . $value
                      : Loc::getMessage('SMARTSEO_FORM_VALUE_SITEMAP_DEFAULT', ['#ID#' => $key])
                  ?>
                </option>
              <? endforeach ?>
           </select>
        </td>
      </tr>
      <tr page-role="sitemap-wrapper-controls" style="<?= $dataSitemap ? 'display: table-row;' : 'display: none;' ?>">
        <td width="40%" class="adm-detail-content-cell-l">
          <?= Loc::getMessage('SMARTSEO_FORM_ENTITY_CHANGEFREQ') ?>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <select required name="<?= $aliasSitemap ?>[CHANGEFREQ]" class="aspro-smartseo__form-control__select aspro-smartseo__form-control__select--w50">
              <? foreach ($listChangefreq as $key => $value) : ?>
                <?
                    $_selected = false;

                    if(!$dataSitemap && $value == $defaultChangefreq) {
                        $_selected = true;
                    } else {
                        $_selected = $dataSitemap['CHANGEFREQ'] == $key;
                    }
                ?>
                <option <?= $_selected ? 'selected' : '' ?> value="<?= $key ?>">
                  <?= $value ?>
                </option>
              <? endforeach ?>
           </select>
        </td>
      </tr>
      <tr page-role="sitemap-wrapper-controls" style="<?= $dataSitemap ? 'display: table-row;' : 'display: none;' ?>">
        <td width="40%" class="adm-detail-content-cell-l">
          <?= Loc::getMessage('SMARTSEO_FORM_ENTITY_PRIORITY') ?>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <select required name="<?= $aliasSitemap ?>[PRIORITY]" class="aspro-smartseo__form-control__select aspro-smartseo__form-control__select--w50">
              <? foreach ($listPriority as $key => $value) : ?>
                <?
                    $_selected = false;

                    if(!$dataSitemap && $value == $defaultPriority) {
                        $_selected = true;
                    } else {
                        $_selected = $dataSitemap['PRIORITY'] == $key;
                    }
                ?>
                <option <?= $_selected ? 'selected' : '' ?> value="<?= $key ?>">
                  <?= $value ?>
                </option>
              <? endforeach ?>
           </select>
        </td>
      </tr>

    </tbody>
  </table>
</form>