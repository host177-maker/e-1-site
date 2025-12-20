<?php
/**
 *  @var array $dataFilterRule
 *  @var string $aliasFilterRule
 *  @var array $listSites
 *  @var array $listIblockTypes
 *  @var array $listIblocks
 *  @var array $listIblockSections
 *  @var array $listSections
 */

use Bitrix\Main\Localization\Loc,
  Aspro\Max\Smartseo\Admin\Helper;

?>
<form id="form_filter_rule" method="POST" action="<?= Helper::url('filter_rule_detail/update') ?>" name="">
  <?= bitrix_sessid_post() ?>
  <input type="hidden" name="<?= $aliasFilterRule ?>[ID]" value="<?= $dataFilterRule['ID'] ?>">
  <input id="<?= $aliasFilterRule ?>_IBLOCK_INCLUDE_SUBSECTIONS" type="hidden" name="<?= $aliasFilterRule ?>[IBLOCK_INCLUDE_SUBSECTIONS]" value="Y">

  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input id="<?= $aliasFilterRule ?>_ACTIVE" class="adm-designed-checkbox" type="checkbox" name="<?= $aliasFilterRule ?>[ACTIVE]" <?= $dataFilterRule['ACTIVE'] !== 'N' ? 'checked' : '' ?>  value="Y" >
            <label class="adm-designed-checkbox-label" for="<?= $aliasFilterRule ?>_ACTIVE" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_ACTIVE') ?>">
            </label>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_NAME') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <input data-field="name" type="text" name="<?= $aliasFilterRule ?>[NAME]" value="<?= htmlspecialchars($dataFilterRule['NAME']) ?>"
                   maxlength="255" class="aspro-smartseo__form-control__input">
            <span class="aspro-smartseo__loading"></span>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SECTION_ID') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select name="<?= $aliasFilterRule ?>[SECTION_ID]" class="aspro-smartseo__form-control__select">
              <option value="0"><?= Loc::getMessage('SMARTSEO_FORM_SECTION_DEFAULT') ?></option>
              <? foreach ($listSections as $section) : ?>
                  <option <?= $parentSectionId == $section['ID'] ? 'selected' : '' ?> value="<?= $section['ID'] ?>">
                    <?= str_repeat(' . ', (int) $section['DEPTH_LEVEL']) . $section['NAME'] ?>
                  </option>
              <? endforeach ?>
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_SORT') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <input type="text" name="<?= $aliasFilterRule ?>[SORT]" size="7" maxlength="10" value="<?= $dataFilterRule['SORT'] ?: 500 ?>">
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_FIELDSET_URL') ?>: </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <div class="aspro-smartseo__form-control__checkbox">
              <input id="<?= $aliasFilterRule ?>_URL_CLOSE_INDEXING" type="checkbox" class="adm-designed-checkbox"
                     name="<?= $aliasFilterRule ?>[URL_CLOSE_INDEXING]" value="Y"
                     <?= $dataFilterRule['URL_CLOSE_INDEXING'] === 'Y' ? 'checked' : '' ?>
                     >
              <label class="adm-designed-checkbox-label" for="<?= $aliasFilterRule ?>_URL_CLOSE_INDEXING" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_URL_CLOSE_INDEXING') ?>">
                <span><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_URL_CLOSE_INDEXING') ?></span>
              </label>
            </div>
          </div>
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
            <select data-field="site" required name="<?= $aliasFilterRule ?>[SITE_ID]" class="aspro-smartseo__form-control__select">
              <option disabled selected><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_SITE_ID') ?></option>
              <? foreach ($listSites as $item) : ?>
              <option <?= $dataFilterRule['SITE_ID'] == $item['LID'] ? 'selected' : '' ?> value="<?= $item['LID'] ?>">
                <?= $item['NAME'] ?>
              </option>
              <? endforeach ?>
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <td width="40%" class="adm-detail-content-cell-l">
          <b><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_IBLOCK_TYPE') ?></b>:
        </td>
        <td width="60%" class="adm-detail-content-cell-r">
          <div class="aspro-smartseo__form-control">
            <select data-field="iblock_type" required name="<?= $aliasFilterRule ?>[IBLOCK_TYPE_ID]" class="aspro-smartseo__form-control__select">
              <option disabled selected><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_IBLOCK_TYPE') ?></option>
              <? foreach ($listIblockTypes as $item) : ?>
              <option <?= $dataFilterRule['IBLOCK_TYPE_ID'] == $item['ID'] ? 'selected' : '' ?> value="<?= $item['ID'] ?>">
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
            <select data-field="iblock" required name="<?= $aliasFilterRule ?>[IBLOCK_ID]" class="aspro-smartseo__form-control__select">
              <option disabled selected><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_IBLOCK_ID') ?></option>
              <? foreach ($listIblocks as $item) : ?>
              <option <?= $dataFilterRule['IBLOCK_ID'] == $item['ID'] ? 'selected' : '' ?> value="<?= $item['ID'] ?>">
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
            <select data-field="iblock_sections" required multiple size="8" name="<?= $aliasFilterRule ?>[IBLOCK_SECTIONS][]" class="aspro-smartseo__form-control__select">
              <option disabled><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_IBLOCK_SECTIONS') ?></option>
              <? foreach ($listIblockSections as $item) : ?>
              <option <?= in_array($item['ID'], $dataFilterRule['IBLOCK_SECTIONS']) ? 'selected' : '' ?> value="<?= $item['ID'] ?>">
                <?= str_repeat(' . ', (int) $item['DEPTH_LEVEL']) ?> <?= $item['NAME'] ?>
              </option>
              <? endforeach ?>
            </select>
            <span class="aspro-smartseo__loading"></span>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</form>