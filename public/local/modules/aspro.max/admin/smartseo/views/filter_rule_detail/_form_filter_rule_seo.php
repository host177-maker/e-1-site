<?php
/**
 *  @var array $dataSeoFilterRule
 */

use Bitrix\Main\Localization\Loc,
    Aspro\Max\Smartseo\Admin\Helper;
?>
<form id="form_seo_property_filter_rule" method="POST" action="<?= Helper::url('filter_rule_detail/update') ?>">
  <table class="adm-detail-content-table edit-table">
    <tbody>
      <tr>
        <td width="100%">
          <div class="aspro-smartseo__form-control__fieldset">
            <span class="aspro-smartseo__form-control__legend">
              <?= Loc::getMessage('SMARTSEO_FORM_SEO_LEGEND_META_TAGS') ?>
            </span>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_META_TITLE') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <textarea control-role="input" name="META_TITLE" cols="55" rows="1" class="aspro-smartseo__form-control__textarea"><?= $dataSeoFilterRule['META_TITLE']['TEMPLATE'] ?></textarea>

                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeoFilterRule['META_TITLE']['SAMPLE'] ?></div>
              </div>
            </div>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_META_KEYWORDS') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <textarea control-role="input" name="META_KEYWORDS" cols="55" rows="1" class="aspro-smartseo__form-control__textarea"><?= $dataSeoFilterRule['META_KEYWORDS']['TEMPLATE'] ?></textarea>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeoFilterRule['META_KEYWORDS']['SAMPLE'] ?></div>
              </div>
            </div>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_META_DESCRIPTION') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <textarea control-role="input" name="META_DESCRIPTION" cols="55" rows="1" class="aspro-smartseo__form-control__textarea"><?= $dataSeoFilterRule['META_DESCRIPTION']['TEMPLATE'] ?></textarea>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeoFilterRule['META_DESCRIPTION']['SAMPLE'] ?></div>
              </div>
            </div>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_PAGE_TITLE') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <textarea control-role="input" name="PAGE_TITLE" cols="55" rows="1" class="aspro-smartseo__form-control__textarea"><?= $dataSeoFilterRule['PAGE_TITLE']['TEMPLATE'] ?></textarea>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeoFilterRule['PAGE_TITLE']['SAMPLE'] ?></div>
              </div>
            </div>
          </div>
          <div class="aspro-smartseo__form-control__fieldset">
            <span class="aspro-smartseo__form-control__legend">
              <?= Loc::getMessage('SMARTSEO_FORM_SEO_LEGEND_INCLUDED') ?>
            </span>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_TOP_DESCRIPTION') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu aspro-smartseo__form-control__menu--textarea"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <?
                CFileMan::AddHTMLEditorFrame(
                  'TOP_DESCRIPTION', $dataSeoFilterRule['TOP_DESCRIPTION']['TEMPLATE'], false, 'html', [
                    'height' => 120,
                    'width' => '100%'
                  ], 'N', 0, '', 'control-role="input"'
                )
                ?>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeoFilterRule['TOP_DESCRIPTION']['SAMPLE'] ?></div>
              </div>
            </div>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
                <div page-role="control-engine-template">
                  <div class="aspro-smartseo__form-control__label">
                    <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_BOTTOM_DESCRIPTION') ?>:</span>
                    <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu aspro-smartseo__form-control__menu--textarea"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                  </div>
                  <?
                  CFileMan::AddHTMLEditorFrame(
                    'BOTTOM_DESCRIPTION', $dataSeoFilterRule['BOTTOM_DESCRIPTION']['TEMPLATE'], false, 'html', [
                      'height' => 120,
                      'width' => '100%'
                    ], 'N', 0, '', 'control-role="input"'
                  )
                  ?>
                  <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeoFilterRule['BOTTOM_DESCRIPTION']['SAMPLE'] ?></div>
                </div>
              </div>
            </div>
            <div class="aspro-smartseo__form-control aspro-smartseo__form-control--mb aspro-smartseo__form-control--w100">
              <div page-role="control-engine-template">
                <div class="aspro-smartseo__form-control__label">
                  <span><?= Loc::getMessage('SMARTSEO_FORM_SEO_ENTITY_ADDITIONAL_DESCRIPTION') ?>:</span>
                  <span control-role="menu" class="ui-btn aspro-smartseo__form-control__menu aspro-smartseo__form-control__menu--textarea"><?= Loc::getMessage('SMARTSEO_INDEX__MENU_DOTS') ?></span>
                </div>
                <?
                CFileMan::AddHTMLEditorFrame(
                  'ADDITIONAL_DESCRIPTION', $dataSeoFilterRule['ADDITIONAL_DESCRIPTION']['TEMPLATE'], false, 'html', [
                    'height' => 120,
                    'width' => '100%'
                  ], 'N', 0, '', 'control-role="input"'
                )
                ?>
                <div control-role="sample" class="aspro-smartseo__form-control__note"><?= $dataSeoFilterRule['ADDITIONAL_DESCRIPTION']['SAMPLE'] ?></div>
              </div>
            </div>
          </div>
        </td>
      </tr>
    </tbody>
  </table>
</form>