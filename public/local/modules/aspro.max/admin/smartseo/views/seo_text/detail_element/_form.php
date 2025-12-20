<?php
/**
 * @var string $alias
 * @var string $aliasProperty
 * @var array $data
 * @var array $dataProperties
 * @var array $listSites
 * @var array $listIblockTypes
 * @var array $listIblocks
 * @var array $listIblockSections
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

$conditionTree = new Smartseo\Condition\ConditionTree();
$conditionTree
  ->addControlBuild(new Smartseo\Condition\Controls\GroupBuildControls())
  ->addControlBuild(new Smartseo\Condition\Controls\IblockPropertyBuildControls($data['IBLOCK_ID'] ?: 0, [
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
<form id="form_seo_text_detail" method="POST" action="<?= Helper::url('seo_text_element/update') ?>">
  <?= bitrix_sessid_post() ?>
  <input page-role="form-field-ID" type="hidden" name="<?= $alias ?>[ID]" value="<?= $data['ID'] ?>">
  <input page-role="form-field-SAVE-IN-TABLE" type="hidden" name="save_in_table" value="<?= $data ? 'Y' : 'N' ?>">

  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_NAME') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input data-field="name" type="text" name="<?= $alias ?>[NAME]" value="<?= htmlspecialchars($data['NAME']) ?>"
                   maxlength="255" class="aspro-smartseo__form-control__input">
            <span class="aspro-smartseo__loading"></span>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
            <?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SORT') ?>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <input type="text" name="<?= $alias ?>[SORT]" size="7" maxlength="10" value="<?= $data['SORT'] ?: 500 ?>">
        </td>
      </tr>

      <tr>
        <td colspan="2"><div class="aspro-smartseo__form-detail__separator"></div></td>
      </tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SITE_ID') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select data-field="site" required name="<?= $alias ?>[SITE_ID]" class="aspro-smartseo__form-control__select">
              <option disabled selected><?= Loc::getMessage('SMARTSEO_AI__PLACEHOLDER__CHOOSE_SITE') ?></option>
              <? foreach ($listSites as $item) : ?>
              <option <?= $data['SITE_ID'] == $item['LID'] ? 'selected' : '' ?> value="<?= $item['LID'] ?>">
                <?= $item['NAME'] ?>
              </option>
              <? endforeach ?>
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_IBLOCK_TYPE_ID') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select data-field="iblock_type" required name="<?= $alias ?>[IBLOCK_TYPE_ID]" class="aspro-smartseo__form-control__select">
              <option disabled selected><?= Loc::getMessage('SMARTSEO_AI__PLACEHOLDER__CHOOSE_IBLOCK_TYPE') ?></option>
              <? foreach ($listIblockTypes as $item) : ?>
              <option <?= $data['IBLOCK_TYPE_ID'] == $item['ID'] ? 'selected' : '' ?> value="<?= $item['ID'] ?>">
                <?= $item['NAME'] ?>
              </option>
              <? endforeach ?>
            </select>
            <span class="aspro-smartseo__loading"></span>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_IBLOCK_ID') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select data-field="iblock" required name="<?= $alias ?>[IBLOCK_ID]" class="aspro-smartseo__form-control__select">
              <option disabled selected><?= Loc::getMessage('SMARTSEO_AI__PLACEHOLDER__CHOOSE_IBLOCK') ?></option>
              <? foreach ($listIblocks as $item) : ?>
              <option <?= $data['IBLOCK_ID'] == $item['ID'] ? 'selected' : '' ?> value="<?= $item['ID'] ?>">
                [<?= $item['ID'] ?>] <?= $item['NAME'] ?>
              </option>
              <? endforeach ?>
            </select>
            <span class="aspro-smartseo__loading"></span>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <?= Loc::getMessage('SMARTSEO_FORM_ENTITY_IBLOCK_SECTIONS') ?>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select data-field="iblock_sections" multiple size="8" name="<?= $alias ?>[IBLOCK_SECTIONS][]" class="aspro-smartseo__form-control__select">
              <option disabled><?= Loc::getMessage('SMARTSEO_AI__PLACEHOLDER__CHOOSE_IBLOCK_SECTIONS') ?></option>
              <? foreach ($listIblockSections as $item) : ?>
              <option <?= in_array($item['ID'], $data['IBLOCK_SECTIONS']) ? 'selected' : '' ?> value="<?= $item['ID'] ?>">
                <?= str_repeat(' . ', (int) $item['DEPTH_LEVEL']) ?> <?= $item['NAME'] ?>
              </option>
              <? endforeach ?>
            </select>
            <span class="aspro-smartseo__loading"></span>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
            <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_CONDITION') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div page-role="condition-control-container" class="aspro-smartseo__form-control aspro-smartseo__form-control--w100">
            <div id="condition_tree"></div>
            <?
              $_condition = $data['CONDITION_TREE'] ? unserialize($data['CONDITION_TREE'], ['allowed_classes' => false]) : null;
              echo  $conditionTree->showTreeConditions($_condition ?: []);
            ?>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
            <?= Loc::getMessage('SMARTSEO_FORM_ENTITY_REWRITE') ?>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input id="<?= $alias ?>_REWRITE" class="adm-designed-checkbox" type="checkbox" name="<?= $alias ?>[REWRITE]" <?= $data['REWRITE'] === 'Y' ? 'checked' : '' ?>  value="Y" >
            <label class="adm-designed-checkbox-label" for="<?= $alias ?>_REWRITE" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_REWRITE') ?>">
            </label>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</form>