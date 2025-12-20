<?php
/**
 *  @var string $alias
 *  @var array  $data
 *  @var boolean $isCatalogModule
 */

use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/general.js');

$pageTitle = Loc::getMessage('SMARTSEO_PAGE_TITLE');

$APPLICATION->setTitle($pageTitle);

$adminTabControl = new CAdminTabControl('setting_tab_control', [
    [
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('SMARTSEO_TAB_SETTINGS_NAME'),
        'ICON' => '',
        'TITLE' => Loc::getMessage('SMARTSEO_TAB_SETTINGS_TITLE'),
    ],
  ]);
?>

<div class="aspro-smartseo__form-detail">
  <form id="general_setting_form" method="POST" action="<?= Helper::url('setting/update_general_settings') ?>" enctype="multipart/form-data" name="filter_section_form">
    <?= bitrix_sessid_post() ?>

    <div form-role="alert" class="ui-alert ui-alert-danger ui-alert-icon-danger aspro-ui-form__alert" style="display: none;">
      <span class="ui-alert-message" form-role="alert-body"></span>
    </div>

    <div class="aspro-smartseo__form-detail__body">
      <? $adminTabControl->Begin() ?>

      <? $adminTabControl->BeginNextTab() ?>

      <tr class="heading" ><td colspan="2"><?= Loc::getMessage('SMARTSEO_FORM_GROUP_CACHE') ?></td></tr>

      <tr>
        <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_CACHE_TABLE') ?>: </td>
        <td width="60%">
           <div class="aspro-smartseo__form-control">
             <input class="aspro-smartseo__form-control__input" type="text" name="<?= $alias ?>[CACHE_TABLE]" value="<?= $data['CACHE_TABLE'] ?: 0 ?>">
           </div>
        </td>
      </tr>

      <tr>
        <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_CACHE_TEMPLATE_ENTITY') ?>: </td>
        <td width="60%">
          <div class="aspro-smartseo__form-control">
             <input class="aspro-smartseo__form-control__input" type="text" name="<?= $alias ?>[CACHE_TEMPLATE_ENTITY]" value="<?= $data['CACHE_TEMPLATE_ENTITY'] ?: 0 ?>">
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_CACHE_CONDITION_CONTROL') ?>: </td>
        <td width="60%">
          <div class="aspro-smartseo__form-control">
             <input class="aspro-smartseo__form-control__input" type="text" name="<?= $alias ?>[CACHE_CONDITION_CONTROL]" value="<?= $data['CACHE_CONDITION_CONTROL'] ?: 0 ?>">
          </div>
        </td>
      </tr>

      <tr class="heading" ><td colspan="2"><?= Loc::getMessage('SMARTSEO_FORM_GROUP_FILTER_RULE') ?></td></tr>
      
      <? if($isCatalogModule) : ?>
      <tr>
        <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_FILTER_RULE_IS_ONLY_CATALOG') ?>: </td>
        <td width="60%">
          <div class="aspro-smartseo__form-control">
            <input class="aspro-smartseo__form-control__input" type="checkbox" name="<?= $alias ?>[FILTER_RULE_IS_ONLY_CATALOG]" <?= $data['FILTER_RULE_IS_ONLY_CATALOG'] == 'Y' ? 'checked' : '' ?> value="Y">
          </div>
        </td>
      </tr>
      <? endif ?>

      <tr class="heading" ><td colspan="2"><?= Loc::getMessage('SMARTSEO_FORM_GROUP_PAGE') ?></td></tr>

      <tr>
        <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_PAGE_IS_REPLACE_META_TAGS') ?>: </td>
        <td width="60%">
          <div class="aspro-smartseo__form-control">
            <input class="aspro-smartseo__form-control__input" type="checkbox" name="<?= $alias ?>[PAGE_IS_REPLACE_META_TAGS]" <?= $data['PAGE_IS_REPLACE_META_TAGS'] == 'Y' ? 'checked' : '' ?> value="Y">
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_PAGE_IS_REPLACE_TITLE') ?>: </td>
        <td width="60%">
          <div class="aspro-smartseo__form-control">
            <input class="aspro-smartseo__form-control__input" type="checkbox" name="<?= $alias ?>[PAGE_IS_REPLACE_TITLE]" <?= $data['PAGE_IS_REPLACE_TITLE'] == 'Y' ? 'checked' : '' ?> value="Y">
          </div>
        </td>
      </tr>

      <tr>
        <td width="40%"><?= Loc::getMessage('SMARTSEO_FORM_ENTITY_PAGE_IS_REPLACE_SNIPPET') ?>: </td>
        <td width="60%">
          <div class="aspro-smartseo__form-control">
            <input class="aspro-smartseo__form-control__input" type="checkbox" name="<?= $alias ?>[PAGE_IS_REPLACE_SNIPPET]" <?= $data['PAGE_IS_REPLACE_SNIPPET'] == 'Y' ? 'checked' : '' ?> value="Y">
          </div>
        </td>
      </tr>

      <? $adminTabControl->Buttons() ?>
      <div class="aspro-smartseo__form-detail__buttons">
        <button form-role="apply" data-action="apply" class="ui-btn ui-btn-primary-dark" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_APPLY') ?>">
          <?= Loc::getMessage('SMARTSEO_FORM_BTN_APPLY') ?>
        </button>
      </div>
      <? $adminTabControl->End() ?>
    </div>
  </form>
</div>
<script>
    var phpObjectGeneralSetting = <?= CUtil::PhpToJSObject([
        'urls' => [
            'MENU_FILTER_NAME' => Helper::url('setting/get_menu_filter_rule_name'),
        ],
        'dataFilterRule' => $dataFilterRule,
        'aliasSeoFilterRule' => $aliasSeoFilterRule,
    ]) ?>;
</script>
