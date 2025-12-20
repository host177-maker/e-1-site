<?php
/**
 * @var string $alias
 * @var array $data
 * @var inr $noindexRuleId
 * @var string $gridConditionId
 * @var array $listConditionTypes
 * @var array $listProperties
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

$_suffix = $this->getUnique();

$selectedProperties = [];
if($data['PROPERTIES']) {
   $selectedProperties = array_column($data['PROPERTIES'], 'PROPERTY_UNIQUE');
}

?>
<form id="form_noindex_condition_<?= $_suffix ?>" method="POST" action="<?= Helper::url('noindex_condition/update') ?>">
  <?= bitrix_sessid_post() ?>
  <input page-role="form-field-ID" type="hidden" name="<?= $alias ?>[ID]" value="<?= $data['ID'] ?>">
  <input type="hidden" name="<?= $alias ?>[NOINDEX_RULE_ID]" value="<?= $noindexRuleId ?>">

  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO__FORM_ENTITY__ACTIVE') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input id="<?= $alias ?>_ACTIVE_<?= $_suffix ?>" class="adm-designed-checkbox" type="checkbox"
                name="<?= $alias ?>[ACTIVE]"
                <?= $data['ACTIVE'] !== 'N' ? 'checked' : '' ?>  value="Y">
            <label class="adm-designed-checkbox-label" for="<?= $alias ?>_ACTIVE_<?= $_suffix ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>"></label>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <?= Loc::getMessage('SMARTSEO__FORM_ENTITY__SORT') ?>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
              <input type="text" name="<?= $alias ?>[SORT]" size="7" maxlength="10" value="<?= $data['SORT'] ?: 500 ?>">
          </div>
        </td>
      </tr>
      <tr>
        <td colspan="2"><div class="aspro-smartseo__form-detail__separator"></div></td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <b><?= Loc::getMessage('SMARTSEO__FORM_ENTITY__ACTION') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select required  class="aspro-smartseo__form-control__select">
              <option selected><?= Loc::getMessage('SMARTSEO__FORM_VALUE__CONDITION_NOINDEX') ?></option>
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
         <b><?= Loc::getMessage('SMARTSEO__FORM_ENTITY__CONDITION') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select page-role="form-field-TYPE" required name="<?= $alias ?>[TYPE]" class="aspro-smartseo__form-control__select">
              <option disabled selected><?= Loc::getMessage('SMARTSEO_AI__PLACEHOLDER__CHOOSE_CONDITION') ?></option>
              <? foreach ($listConditionTypes as $code => $label) : ?>
              <option <?= $data['TYPE'] == $code ? 'selected' : '' ?> value="<?= $code ?>">
                <?= Loc::getMessage('SMARTSEO__FORM_VALUE__' . $code) ?>
              </option>
              <? endforeach ?>
            </select>
            <span class="aspro-smartseo__loading"></span>
          </div>
        </td>
      </tr>
      <?
        $_isVisible = false;
        if($data['TYPE']
          &&  (
                $data['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_COUNT
                || $data['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_VALUES
              )
          ) {
           $_isVisible = true;
        }
      ?>
      <tr page-role="wrapper-value-control" style="<?= $_isVisible ? '' : 'display: none' ?>">
        <td width="40%" class="adm-detail-content-cell-l">
          <b><?= Loc::getMessage('SMARTSEO__FORM_LABEL__MORE') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
           <input type="text" name="<?= $alias ?>[VALUE]" size="7" maxlength="3" value="<?= $data['VALUE'] ?: 0 ?>">
          </div>
        </td>
      </tr>
       <?
        $_isVisible = false;
        if($data['TYPE'] && $data['TYPE'] == Smartseo\Models\SmartseoNoindexConditionTable::TYPE_EXCEPTION_BY_PROPERTIES) {
           $_isVisible = true;
        }
      ?>
      <tr page-role="wrapper-properties-control" style="<?= $_isVisible ? '' : 'display: none' ?>">
        <td width="40%" class="adm-detail-content-cell-l">
          <b><?= Loc::getMessage('SMARTSEO__FORM_ENTITY__PROPERTIES') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select data-field="properties" multiple size="8" name="<?= $alias ?>[PROPERTIES][]" class="aspro-smartseo__form-control__select">
              <option disabled><?= Loc::getMessage('SMARTSEO_AI__PLACEHOLDER__CHOOSE_PROPERTIES') ?></option>
              <? $optgroup = ''; ?>
              <? foreach ($listProperties as $item) : ?>
                <?
                    $optgroupLabel = '';
                ?>

                <? if (!$optgroup || $optgroup != $item['PROPERTY_IBLOCK_ID']) : ?>
                    <?
                        if($item['GROUP'] == 'IBLOCK' || $item['GROUP'] == 'SKU_IBLOCK') {
                            $optgroupLabel = Loc::getMessage('SMARTSEO__FORM_LABEL__IBLOCK_PROPERTY', [
                                '#IBLOCK_NAME#' => $item['PROPERTY_IBLOCK_NAME'],
                                '#IBLOCK_ID#' => $item['PROPERTY_IBLOCK_ID'],
                            ]);
                        }
                        if($item['GROUP'] == 'PRICE') {
                            $optgroupLabel = Loc::getMessage('SMARTSEO__FORM_LABEL__CATALOG_GROUP');
                       }
                    ?>
                <? endif ?>

                <? if (!$optgroup) : ?>
                  <optgroup label="<?= $optgroupLabel ?>">
                <? elseif($optgroup != $item['GROUP']) : ?>
                  </optgroup>
                  <optgroup label="<?= $optgroupLabel ?>">
                <? endif ?>
                <option <?= in_array($item['PROPERTY_UNIQUE'], $selectedProperties) ? 'selected' : '' ?> value="<?= $item['GROUP'] . '_' .$item['PROPERTY_ID'] ?>">
                  <?= $item['PROPERTY_NAME'] ?>
                </option>
                <? $optgroup = $item['GROUP'] ?>
              <? endforeach ?>
              </optgroup>
            </select>
            <span class="aspro-smartseo__loading"></span>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</form>