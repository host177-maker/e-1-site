<?php
/**
 *  @var string $alias
 *  @var array $data
 *  @var array $dataSample
 *  @var string $gridId
 *  @var int $filterRuleId
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

$_suffix = $this->getUnique();
?>
<form id="form_search_<?= $_suffix ?>" method="POST" action="<?= Helper::url('filter_search/update') ?>">
  <?= bitrix_sessid_post() ?>
  <input page-role="form-field-ID" type="hidden" name="<?= $alias ?>[ID]" value="<?= $data['ID'] ?>">
  <input page-role="form-field-REINDEX" type="hidden" name="reindex" value="N">

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
        <td width="40%" class="adm-detail-content-cell-l">
          <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_FILTER_CONDITION') ?>:</b>
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select required page-role="form-field-FILTER_CONDITION_ID" required name="<?= $alias ?>[FILTER_CONDITION_ID]" class="aspro-smartseo__form-control__select">
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

      <tr class="heading" ><td colspan="2"><?= Loc::getMessage('SMARTSEO_FORM_GROUP_SEARCH_CONTENT') ?></td></tr>

      <tr>
        <td width="100%" colspan="2">
          <div class="aspro-smartseo__form-control aspro-smartseo__form-control--w100">
            <div page-role="control-engine-template" data-state="true">
              <div class="aspro-smartseo__form-control__label">
                <span><b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_TITLE_TEMPLATE') ?>:</b></span>
                <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
              </div>
              <div control-role="input-wrapper">
                <textarea required control-role="input" name="<?= $alias ?>[TITLE_TEMPLATE]" cols="55" rows="1" class="aspro-smartseo__form-control__textarea"><?= $data['TITLE_TEMPLATE'] ?></textarea>
              </div>
              <div control-role="sample" class="aspro-smartseo__form-control__note">
                <?= $dataSample['TITLE_TEMPLATE'] ?: '' ?>
              </div>
            </div>
          </div>
        </td>
      </tr>

      <tr>
        <td width="100%" colspan="2">
          <div class="aspro-smartseo__form-control aspro-smartseo__form-control--w100">
            <div page-role="control-engine-template" data-state="true">
              <div class="aspro-smartseo__form-control__label">
                <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_BODY_TEMPLATE') ?>:</span>
                <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
              </div>
              <div control-role="input-wrapper">
                <textarea control-role="input" name="<?= $alias ?>[BODY_TEMPLATE]" cols="55" rows="4" class="aspro-smartseo__form-control__textarea"><?= $data['BODY_TEMPLATE'] ?></textarea>
              </div>
              <div control-role="sample" class="aspro-smartseo__form-control__note">
                <?= $dataSample['BODY_TEMPLATE'] ?: '' ?>
              </div>
            </div>
          </div>
        </td>
      </tr>

    </tbody>
  </table>
</form>