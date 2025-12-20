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

global $APPLICATION;

\Bitrix\Main\UI\Extension::load('ui.notification');
\Bitrix\Main\UI\Extension::load('ui.hint');

$APPLICATION->AddHeadScript('/bitrix/js/' . Smartseo\General\Smartseo::MODULE_ID . '/smartseo/src/core_custom.js');
$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/detail.js');
$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/detail_conditions.js');

$elementTitle = '';

if($data['ID'] && $data['NAME']) {
    $elementTitle = htmlspecialchars($data['NAME']);
}

if($data['ID'] && !$data['NAME']) {
    $elementTitle = Loc::getMessage('SMARTSEO__ELEMENT__DEFAULT_NAME', [
        '#ID#' => $data['ID'],
    ]);
}

$pageTitle = Loc::getMessage('SMARTSEO_INDEX__TITLE__NOINDEX_RULE')
  . ': ' . ($data['ID'] ? $elementTitle : Loc::getMessage('SMARTSEO__PAGE__TITLE'))
  . ': ' . ($data['ID'] ? Loc::getMessage('SMARTSEO_AI__ACTION__EDITING') : Loc::getMessage('SMARTSEO_AI__ACTION__ADDING'));

$APPLICATION->setTitle($pageTitle);

$adminContextMenu = new CAdminUiContextMenu(array_filter([
      [
          'TEXT' => Loc::getMessage('SMARTSEO__MENU__ADD'),
          'TITLE' => Loc::getMessage('SMARTSEO__MENU__ADD'),
          'LINK' => Helper::url('noindex_rule_detail/detail'),
          'ICON' => 'edit',
      ],
      $data ? [
        'TEXT' => Loc::getMessage('SMARTSEO__MENU__COPY'),
        'TITLE' => Loc::getMessage('SMARTSEO__MENU__COPY'),
        'LINK' => '',
        'ICON' => 'copy',
        'ONCLICK' => "contextMenuHandler.copy($data[ID])"
      ] : [],
      $data ? [
        'TEXT' => Loc::getMessage('SMARTSEO__MENU__DELETE'),
        'TITLE' => Loc::getMessage('SMARTSEO__MENU__DELETE'),
        'LINK' => '',
        'ICON' => 'delete',
        'ONCLICK' => "contextMenuHandler.delete($data[ID])"
      ] : [],
  ]));

$adminTabControl = new CAdminTabControl('noindex_rule__section_tab_control', [
    [
        'DIV' => 'noundex_rule_main',
        'TAB' => Loc::getMessage('SMARTSEO__TAB__GENERAL_NAME'),
        'TITLE' => Loc::getMessage('SMARTSEO__TAB__GENERAL_TITLE'),
        'ICON' => '',
    ],
  ], true, true);

?>

<div id="noindex_rule_detail" class="aspro-smartseo__form-detail">
  <div class="aspro-smartseo__form-detail__toolbar">
    <div class="aspro-smartseo__form-detail__col">
      <a href="<?= Helper::url('noindex_rules/list') ?>" class="ui-btn ui-btn-light-border">
        <?= Loc::getMessage('SMARTSEO_AI__ACTION__BACK_LIST') ?>
      </a>
    </div>
    <div class="aspro-smartseo__form-detail__col">
      <? $adminContextMenu->Show() ?>
    </div>
  </div>

  <? if ($data) : ?>
  <? include $this->getViewPath() . 'detail/_top_info.php'; ?>
  <? endif ?>

  <div seo-text-form-role="alert" class="ui-alert ui-alert-danger ui-alert-icon-success aspro-ui-form__alert" style="display: none;">
    <span class="ui-alert-message" form-role="alert-body"></span>
  </div>

  <div class="aspro-smartseo__form-detail__body">
    <? $adminTabControl->Begin() ?>

    <? $adminTabControl->BeginNextTab() ?>

    <tr>
      <td colspan="2">
        <? include $this->getViewPath() . 'detail/_form.php'; ?>
      </td>
    </tr>

    <tr>
      <td colspan="2">
         <? include $this->getViewPath() . 'detail/_conditions.php'; ?>
      </td>
    </tr>

    <? $adminTabControl->Buttons() ?>

    <div class="aspro-smartseo__form-detail__buttons">
      <button seo-text-form-role="save" data-action="save" class="ui-btn ui-btn-success" title="<?= Loc::getMessage('SMARTSEO_AI__HINT__SAVE') ?>">
        <?= Loc::getMessage('SMARTSEO_AI__BTN__SAVE') ?>
      </button>
      <button seo-text-form-role="apply" data-action="apply" class="ui-btn ui-btn-primary-dark" title="<?= Loc::getMessage('SMARTSEO_AI__HINT__APPLY') ?>">
        <?= Loc::getMessage('SMARTSEO_AI__BTN__APPLY') ?>
      </button>

      <a seo-text-form-role="cancel" data-action="cancel" href="<?= Helper::url('noindex_rules/list') ?>" class="ui-btn ui-btn-light" title="<?= Loc::getMessage('SMARTSEO_AI__HINT__CANCEL') ?>">
        <?= Loc::getMessage('SMARTSEO_AI__BTN__CANCEL') ?>
      </a>
    </div>

    <? $adminTabControl->End() ?>
  </div>
</div>

<script>
    var phpObjectNoindexRule = <?=
    CUtil::PhpToJSObject([
        'urls' => [
            'FIELD_OPTION_IBLOCK_TYPE' => Helper::url('noindex_rule_detail/get_option_iblock_type', ['sessid' => bitrix_sessid()]),
            'FIELD_OPTION_IBLOCK' => Helper::url('noindex_rule_detail/get_option_iblock', ['sessid' => bitrix_sessid()]),
            'FIELD_OPTION_IBLOCK_SECTIONS' => Helper::url('noindex_rule_detail/get_option_iblock_sections', ['sessid' => bitrix_sessid()]),
            'FIELD_VALUE_URL_TEMPLATE' => Helper::url('noindex_rule_detail/get_value_url_template', ['sessid' => bitrix_sessid()]),
            'DELETE' => Helper::url('noindex_rule_detail/delete', ['sessid' => bitrix_sessid()]),
            'COPY' => Helper::url('noindex_rule_detail/copy', ['sessid' => bitrix_sessid()]),
        ],
    ]);
    ?>

    BX.message({
        SMARTSEO_POPUP_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_AI__BTN__DELETE') ?>',
        SMARTSEO_POPUP_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_AI__BTN__CANCEL') ?>',
        SMARTSEO_POPUP_BTN_CLOSE: '<?= Loc::getMessage('SMARTSEO_AI__BTN__CLOSE') ?>',
        SMARTSEO_POPUP_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_AI__MESSAGE__DELETE') ?>',
        SMARTSEO_GRID_CONDITION_NEW_TAB: '<?= Loc::getMessage('SMARTSEO__GRID_CONDITION__NEW_TAB') ?>'
    });
</script>