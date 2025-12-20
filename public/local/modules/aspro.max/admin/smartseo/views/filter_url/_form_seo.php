<?php
/**
 *  @var array $dataSeo
 */

use Bitrix\Main\Localization\Loc,
    Aspro\Max\Smartseo\Admin\Helper;

\Bitrix\Main\Loader::includeModule('fileman');

$_suffix = $this->getUnique();
$_typeEntitySeo = \Aspro\Max\Smartseo\Models\SmartseoSeoTemplateTable::ENTITY_TYPE_FILTER_URL;

?>
<form id="form_url_seo_<?= $_suffix ?>" method="POST" action="<?= Helper::url('filter_rule_detail/update') ?>">
  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="100%">
          <div class="aspro-smartseo__form-control__fieldset">
            <span class="aspro-smartseo__form-control__legend">
              <?= Loc::getMessage('SMARTSEO_FORM_SEO_LEGEND_META_TAGS') ?>
            </span>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template" data-state="<?= $dataSeo['META_TITLE']['ENTITY_TYPE'] != $_typeEntitySeo ? 'false' : '' ?>">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_META_TITLE') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <div control-role="input-wrapper">
                    <textarea control-role="input" name="META_TITLE" cols="55" rows="1" class="aspro-smartseo__form-control__textarea"><?= $dataSeo['META_TITLE']['TEMPLATE'] ?></textarea>
                </div>
                <div class="aspro-smartseo__form-control__checkbox">
                    <? $_selectorId = uniqid('meta_title_') ?>
                    <input control-role="edit-checkbox" id="<?= $_selectorId ?>" type="checkbox" class="adm-designed-checkbox" value="Y" >
                    <label control-role="edit-checkbox-label" class="adm-designed-checkbox-label" for="<?= $_selectorId ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?>">
                      <span><?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?></span>
                    </label>
                </div>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeo['META_TITLE']['SAMPLE'] ?></div>
              </div>
            </div>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template" data-state="<?= $dataSeo['META_KEYWORDS']['ENTITY_TYPE'] != $_typeEntitySeo ? 'false' : '' ?>">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_META_KEYWORDS') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <div control-role="input-wrapper">
                    <textarea control-role="input" name="META_KEYWORDS" cols="55" rows="1" class="aspro-smartseo__form-control__textarea"><?= $dataSeo['META_KEYWORDS']['TEMPLATE'] ?></textarea>
                </div>
                <div class="aspro-smartseo__form-control__checkbox">
                    <? $_selectorId = uniqid('meta_keywords_') ?>
                    <input control-role="edit-checkbox" id="<?= $_selectorId ?>" type="checkbox" class="adm-designed-checkbox" value="Y" >
                    <label control-role="edit-checkbox-label" class="adm-designed-checkbox-label" for="<?= $_selectorId ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?>">
                      <span><?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?></span>
                    </label>
                </div>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeo['META_KEYWORDS']['SAMPLE'] ?></div>
              </div>
            </div>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template" data-state="<?= $dataSeo['META_DESCRIPTION']['ENTITY_TYPE'] != $_typeEntitySeo ? 'false' : '' ?>">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_META_DESCRIPTION') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <div control-role="input-wrapper">
                    <textarea control-role="input" name="META_DESCRIPTION" cols="55" rows="1" class="aspro-smartseo__form-control__textarea"><?= $dataSeo['META_DESCRIPTION']['TEMPLATE'] ?></textarea>
                </div>
                <div class="aspro-smartseo__form-control__checkbox">
                    <? $_selectorId = uniqid('meta_description_') ?>
                    <input control-role="edit-checkbox" id="<?= $_selectorId ?>" type="checkbox" class="adm-designed-checkbox" value="Y" >
                    <label control-role="edit-checkbox-label" class="adm-designed-checkbox-label" for="<?= $_selectorId ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?>">
                      <span><?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?></span>
                    </label>
                </div>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeo['META_DESCRIPTION']['SAMPLE'] ?></div>
              </div>
            </div>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template" data-state="<?= $dataSeo['PAGE_TITLE']['ENTITY_TYPE'] != $_typeEntitySeo ? 'false' : '' ?>">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_PAGE_TITLE') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <div control-role="input-wrapper">
                    <textarea control-role="input" name="PAGE_TITLE" cols="55" rows="1" class="aspro-smartseo__form-control__textarea"><?= $dataSeo['PAGE_TITLE']['TEMPLATE'] ?></textarea>
                </div>
                <div class="aspro-smartseo__form-control__checkbox">
                    <? $_selectorId = uniqid('page_title_') ?>
                    <input control-role="edit-checkbox" id="<?= $_selectorId ?>" type="checkbox" class="adm-designed-checkbox" value="Y" >
                    <label ccontrol-role="edit-checkbox-label" class="adm-designed-checkbox-label" for="<?= $_selectorId ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?>">
                      <span><?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?></span>
                    </label>
                </div>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeo['PAGE_TITLE']['SAMPLE'] ?></div>
              </div>
            </div>

            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template" data-state="<?= $dataSeo['BREADCRUMB_PAGE']['ENTITY_TYPE'] != $_typeEntitySeo ? 'false' : '' ?>">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_PAGE_BREADCRUMB_PAGE') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <div control-role="input-wrapper">
                    <textarea control-role="input" name="BREADCRUMB_PAGE" cols="55" rows="1" class="aspro-smartseo__form-control__textarea"><?= $dataSeo['BREADCRUMB_PAGE']['TEMPLATE'] ?></textarea>
                </div>
                <div class="aspro-smartseo__form-control__checkbox">
                    <? $_selectorId = uniqid('breadcrumb_page_') ?>
                    <input control-role="edit-checkbox" id="<?= $_selectorId ?>" type="checkbox" class="adm-designed-checkbox" value="Y" >
                    <label ccontrol-role="edit-checkbox-label" class="adm-designed-checkbox-label" for="<?= $_selectorId ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?>">
                      <span><?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?></span>
                    </label>
                </div>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeo['BREADCRUMB_PAGE']['SAMPLE'] ?></div>
              </div>
            </div>
          </div>
          <div class="aspro-smartseo__form-control__fieldset">
            <span class="aspro-smartseo__form-control__legend">
              <?= Loc::getMessage('SMARTSEO_FORM_SEO_LEGEND_INCLUDED') ?>
            </span>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template" data-state="<?= $dataSeo['TOP_DESCRIPTION']['ENTITY_TYPE'] != $_typeEntitySeo ? 'false' : '' ?>">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_TOP_DESCRIPTION') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu aspro-smartseo__form-control__menu--textarea"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <div control-role="input-wrapper">
                    <?
                    \CFileMan::AddHTMLEditorFrame(
                      'TOP_DESCRIPTION_suffix_' . $_suffix, $dataSeo['TOP_DESCRIPTION']['TEMPLATE'], false, 'html', [
                        'height' => 120,
                        'width' => '100%'
                      ], 'N', 0, '', 'control-role="input"'
                    )
                    ?>
                </div>
                <div class="aspro-smartseo__form-control__checkbox">
                    <? $_selectorId = uniqid('top_description_') ?>
                    <input control-role="edit-checkbox" id="<?= $_selectorId ?>" type="checkbox" class="adm-designed-checkbox" value="Y" >
                    <label control-role="edit-checkbox-label" class="adm-designed-checkbox-label" for="<?= $_selectorId ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?>">
                      <span><?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?></span>
                    </label>
                </div>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeo['TOP_DESCRIPTION']['SAMPLE'] ?></div>
              </div>
            </div>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
                <div page-role="control-engine-template" data-state="<?= $dataSeo['BOTTOM_DESCRIPTION']['ENTITY_TYPE'] != $_typeEntitySeo ? 'false' : '' ?>">
                  <div class="aspro-smartseo__form-control__label">
                    <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_BOTTOM_DESCRIPTION') ?>:</span>
                    <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu aspro-smartseo__form-control__menu--textarea"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                  </div>
                  <div control-role="input-wrapper">
                    <?
                    \CFileMan::AddHTMLEditorFrame(
                      'BOTTOM_DESCRIPTION_suffix_' . $_suffix, $dataSeo['BOTTOM_DESCRIPTION']['TEMPLATE'], false, 'html', [
                        'height' => 120,
                        'width' => '100%'
                      ], 'N', 0, '', 'control-role="input"'
                    )
                    ?>
                  </div>
                  <div class="aspro-smartseo__form-control__checkbox">
                    <? $_selectorId = uniqid('bottom_description_') ?>
                    <input control-role="edit-checkbox" id="<?= $_selectorId ?>" type="checkbox" class="adm-designed-checkbox" value="Y" >
                    <label control-role="edit-checkbox-label" class="adm-designed-checkbox-label" for="<?= $_selectorId ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?>">
                      <span><?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?></span>
                    </label>
                  </div>
                  <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeo['BOTTOM_DESCRIPTION']['SAMPLE'] ?></div>
                </div>
              </div>
            </div>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template" data-state="<?= $dataSeo['ADDITIONAL_DESCRIPTION']['ENTITY_TYPE'] != $_typeEntitySeo ? 'false' : '' ?>">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_ADDITIONAL_DESCRIPTION') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu aspro-smartseo__form-control__menu--textarea"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <div control-role="input-wrapper">
                    <?
                    \CFileMan::AddHTMLEditorFrame(
                      'ADDITIONAL_DESCRIPTION_suffix_' . $_suffix, $dataSeo['ADDITIONAL_DESCRIPTION']['TEMPLATE'], false, 'html', [
                        'height' => 120,
                        'width' => '100%'
                      ], 'N', 0, '', 'control-role="input"'
                    )
                    ?>
                </div>
                <div class="aspro-smartseo__form-control__checkbox">
                  <? $_selectorId = uniqid('additional_description_') ?>
                  <input control-role="edit-checkbox" id="<?= $_selectorId ?>" type="checkbox" class="adm-designed-checkbox" value="Y" >
                  <label control-role="edit-checkbox-label" class="adm-designed-checkbox-label" for="<?= $_selectorId ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?>">
                    <span><?= Loc::getMessage('SMARTSEO_FORM_LABEL_EDIT_THIS') ?></span>
                  </label>
                </div>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeo['ADDITIONAL_DESCRIPTION']['SAMPLE'] ?></div>
              </div>
            </div>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</form>