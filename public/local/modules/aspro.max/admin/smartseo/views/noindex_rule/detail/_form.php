<?php
/**
 * @var string $alias
 * @var array $data
 * @var array $listSites
 * @var array $listIblockTypes
 * @var array $listIblocks
 * @var array $listIblockSections
 */
use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

?>
<form id="form_noindex_rule_detail" method="POST" action="<?= Helper::url('noindex_rule_detail/update') ?>">
  <?= bitrix_sessid_post() ?>
  <input page-role="form-field-ID" type="hidden" name="<?= $alias ?>[ID]" value="<?= $data['ID'] ?>">

  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
            <?= Loc::getMessage('SMARTSEO__FORM_ENTITY__ACTIVE') ?>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input id="<?= $alias ?>_ACTIVE" class="adm-designed-checkbox" type="checkbox" name="<?= $alias ?>[ACTIVE]" <?= $data['ACTIVE'] !== 'N' ? 'checked' : '' ?>  value="Y" >
            <label class="adm-designed-checkbox-label" for="<?= $alias ?>_ACTIVE" title="<?= Loc::getMessage('SMARTSEO__FORM_ENTITY__ACTIVE') ?>"></label>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
            <?= Loc::getMessage('SMARTSEO__FORM_ENTITY__NAME') ?>:
        </td>
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
          <b><?= Loc::getMessage('SMARTSEO__FORM_ENTITY__SITE_ID') ?></b>:
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
          <b><?= Loc::getMessage('SMARTSEO__FORM_ENTITY__IBLOCK_TYPE_ID') ?></b>:
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
          <b><?= Loc::getMessage('SMARTSEO__FORM_ENTITY__IBLOCK_ID') ?></b>:
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
          <b><?= Loc::getMessage('SMARTSEO__FORM_ENTITY__IBLOCK_SECTIONS') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div>
              <div class="aspro-smartseo__form-control">
                <div class="aspro-smartseo__form-control__checkbox">
                    <input data-field="iblock_section_all" id="<?= $alias ?>_IBLOCK_SECTION_ALL" class="adm-designed-checkbox" type="checkbox" name="<?= $alias ?>[IBLOCK_SECTION_ALL]" <?= $data['IBLOCK_SECTION_ALL'] === 'Y' ? 'checked' : '' ?>  value="Y" >
                    <label class="adm-designed-checkbox-label" for="<?= $alias ?>_IBLOCK_SECTION_ALL" title="<?= Loc::getMessage('SMARTSEO__FORM_ENTITY__IBLOCK_SECTION_ALL') ?>">
                      <?= Loc::getMessage('SMARTSEO__FORM_ENTITY__IBLOCK_SECTION_ALL') ?>
                    </label>
                </div>
              </div>
          </div>
          <div>
            <div class="aspro-smartseo__form-control">
              <select data-field="iblock_sections" <?= $data['IBLOCK_SECTION_ALL'] === 'Y' ? 'disabled' : 'required' ?> multiple size="8" name="<?= $alias ?>[IBLOCK_SECTIONS][]" class="aspro-smartseo__form-control__select">
                <option disabled><?= Loc::getMessage('SMARTSEO_AI__PLACEHOLDER__CHOOSE_IBLOCK_SECTIONS') ?></option>
                <? foreach ($listIblockSections as $item) : ?>
                <option <?= in_array($item['ID'], $data['IBLOCK_SECTIONS']) ? 'selected' : '' ?> value="<?= $item['ID'] ?>">
                  <?= str_repeat(' . ', (int) $item['DEPTH_LEVEL']) ?> <?= $item['NAME'] ?>
                </option>
                <? endforeach ?>
              </select>
              <span class="aspro-smartseo__loading"></span>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
            <b><?= Loc::getMessage('SMARTSEO__FORM_ENTITY__URL_TEMPLATE') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input data-field="url_template" control-role="input" type="text" required name="<?= $alias ?>[URL_TEMPLATE]" class="aspro-smartseo__form-control__input" value="<?= $data['URL_TEMPLATE'] ?>" />
            <span class="aspro-smartseo__loading"></span>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</form>