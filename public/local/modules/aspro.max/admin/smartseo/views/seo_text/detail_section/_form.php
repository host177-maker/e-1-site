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
?>
<form id="form_seo_text_detail" method="POST" action="<?= Helper::url('seo_text_section/update') ?>">
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
          <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_IBLOCK_SECTIONS') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select data-field="iblock_sections" required multiple size="8" name="<?= $alias ?>[IBLOCK_SECTIONS][]" class="aspro-smartseo__form-control__select">
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