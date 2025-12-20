<?php

/**
 *  @var string $alias
 *  @var array $data
 *  @var array $listFilterCondition
 *  @var array $listIblockSections
 *  @var array $listTypes
 *  @var array $listIdenticalProperty
 *  @var array $listTags
 */
use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

$_suffix = $this->getUnique();
?>
<form id="form_tag_<?= $_suffix ?>" method="POST" action="<?= Helper::url('filter_tag/update') ?>">
  <?= bitrix_sessid_post() ?>
  <input page-role="form-field-ID" type="hidden" name="<?= $alias ?>[ID]" value="<?= $data['ID'] ?>">

  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>: </td>
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
        <td colspan="2">
          <div class="aspro-smartseo__form-detail__separator">
            <div class="aspro-smartseo__form-detail__separator__legend">
              <span><?= Loc::getMessage('SMARTSEO_FORM_GROUP_OUTPUT_TAGS') ?></span>
            </div>
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_TYPE') ?>:</b></td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select page-role="form-field-TYPE" required name="<?= $alias ?>[TYPE]" class="aspro-smartseo__form-control__select">
              <? foreach ($listTypes as $key => $value) : ?>
                  <?
                  $_selected = $data['TYPE'] == $key;
                  ?>
                  <option <?= $_selected ? 'selected' : '' ?> value="<?= $key ?>">
                    <?= $value ?>
                  </option>
              <? endforeach ?>
            </select>
          </div>
        </td>
      </tr>
      <tr page-role="parent-filter-condition-wrapper" style="<?= $data['TYPE'] === 'FC' ? 'display: table-row;' : 'display: none;' ?>">
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_PARENT_FILTER_CONDITION') ?>:</td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select page-role="form-field-PARENT_FILTER_CONDITION_ID" required name="<?= $alias ?>[PARENT_FILTER_CONDITION_ID]" class="aspro-smartseo__form-control__select">
              <option disabled <?= !$data['PARENT_FILTER_CONDITION_ID'] ? 'selected' : '' ?>><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_FILTER_CONDITION') ?></option>
              <? foreach ($listFilterCondition as $key => $value) : ?>
                  <?
                  $_selected = $data['PARENT_FILTER_CONDITION_ID'] == $key;
                  ?>
                  <option <?= $_selected ? 'selected' : '' ?> value="<?= $key ?>">
                    <?=
                    $value ? '[' . $key . '] ' . $value : Loc::getMessage('SMARTSEO_FORM_VALUE_CONDITION_DEFAULT', ['#ID#' => $key])
                    ?>
                  </option>
              <? endforeach ?>
            </select>
          </div>
        </td>
      </tr>
      <tr page-role="section-wrapper" style="<?= $data['TYPE'] === 'SC' ? 'display: table-row;' : 'display: none;' ?>">
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SECTION') ?>:</td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select required name="<?= $alias ?>[SECTION_ID]" class="aspro-smartseo__form-control__select">
              <option disabled <?= !$data['SECTION_ID'] ? 'selected' : '' ?>><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_FILTER_SECTION') ?></option>
              <? foreach ($listIblockSections as $key => $item) : ?>
                  <?
                  $_selected = $data['SECTION_ID'] == $item['ID'];
                  ?>
                  <option <?= $_selected ? 'selected' : '' ?> value="<?= $item['ID'] ?>">
                    <?= str_repeat(' . ', (int) $item['DEPTH_LEVEL']) ?> <?= $item['NAME'] ?>
                  </option>
              <? endforeach ?>
            </select>
          </div>
        </td>
      </tr>

      <tr>
        <td colspan="2">
          <div class="aspro-smartseo__form-detail__separator">
            <div class="aspro-smartseo__form-detail__separator__legend">
              <span><?= Loc::getMessage('SMARTSEO_FORM_GROUP_SETTING_TAGS') ?></span>
            </div>
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_FILTER_CONDITION') ?>:</b></td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select required page-role="form-field-FILTER_CONDITION_ID"  name="<?= $alias ?>[FILTER_CONDITION_ID]" class="aspro-smartseo__form-control__select">
              <option disabled <?= !$data ? 'selected' : '' ?>><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_FILTER_CONDITION') ?></option>
              <? foreach ($listFilterCondition as $key => $value) : ?>
                  <?
                  $_selected = $data['FILTER_CONDITION_ID'] == $key;
                  ?>
                  <option <?= $_selected ? 'selected' : '' ?> value="<?= $key ?>">
                    <?=
                    $value ? '[' . $key . '] ' . $value : Loc::getMessage('SMARTSEO_FORM_VALUE_CONDITION_DEFAULT', ['#ID#' => $key])
                    ?>
                  </option>
              <? endforeach ?>
            </select>
          </div>
        </td>
      </tr>

      <tr page-role="parent-filter-condition-wrapper" style="<?= $data['TYPE'] === 'FC' ? 'display: table-row;' : 'display: none;' ?>">
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_RELATED_PROPERTY') ?>:</td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div page-role="form-wrapper-RELATED_PROPERTY" class="aspro-smartseo__form-control">
            <? if(!$listIdenticalProperty) : ?>
                <span style="font-size: 10px;"><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_NOT_RELATED_PROPERTY') ?></span>
            <? endif ?>

            <? foreach ($listIdenticalProperty as $propertyId => $propertyName) : ?>
            <?
                $_selected = in_array($propertyId, $data['RELATED_PROPERTY']);
            ?>
            <div class="aspro-smartseo__form-control__checkbox">
                <input id="<?= $alias ?>_RELATED_PROPERTY_<?= $propertyId . $_suffix ?>" class="adm-designed-checkbox" type="checkbox"
                   name="<?= $alias ?>[RELATED_PROPERTY][]"
                   <?= $_selected ? 'checked' : '' ?>
                   value="<?= $propertyId ?>">
                <label class="adm-designed-checkbox-label" for="<?= $alias ?>_RELATED_PROPERTY_<?= $propertyId . $_suffix ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_RELATED_PROPERTY') ?>">
                  <?= $propertyName ?>
                </label>
            </div>
            <? endforeach ?>
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%" class="adm-detail-content-cell-l adm-detail-content-cell-l--align-top">
          <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_TEMPLATE') ?>:</b>
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control aspro-smartseo__form-control--w100">
            <div page-role="control-engine-template">
              <div class="aspro-smartseo__form-control__textarea-menu-wrapper aspro-smartseo__form-control__textarea-menu-wrapper--w100">
                <div control-role="input-wrapper">
                  <textarea required control-role="input" rows="1" class="aspro-smartseo__form-control__input"
                    type="text" name="<?= $alias ?>[TEMPLATE]"><?= $data['TEMPLATE'] ? htmlspecialchars($data['TEMPLATE']) : '' ?></textarea>
                </div>
                <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
              </div>
              <div control-role="sample" class="aspro-smartseo__form-control__note">
                  <? include $this->getViewPath() . '_sample_tags.php'; ?>
              </div>
            </div>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</form>