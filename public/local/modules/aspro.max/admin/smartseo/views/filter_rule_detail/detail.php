<?php
/**
 *  @var string $activeTab
 *  @var string $aliasFilterRule
 *  @var string $aliasSeoFilterRule
 *  @var array $dataFilterRule
 *  @var array $dataSeoFilterRule
 *  @var array $listSites
 *  @var array $listIblockTypes
 *  @var array $listIblocks
 *  @var array $listIblockSections
 *  @var array $listSections
 *
 *  @var array $gridConditions
 *  @var array $gridUrls
 *  @var array $gridSitemap
 *  @var array $gridTags
 *  @var array $gridSearch
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

\CJSCore::Init(['core_condtree']);
\Bitrix\Main\UI\Extension::load('ui.notification');
\Bitrix\Main\UI\Extension::load('ui.hint');

$APPLICATION->AddHeadScript('/bitrix/js/' . Smartseo\General\Smartseo::MODULE_ID . '/smartseo/src/core_custom.js');

$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/main.js');
$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/filter_conditions.js');
$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/filter_urls.js');
$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/filter_sitemap.js');
$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/filter_tags.js');
$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/filter_search.js');

$elementTitle = '';

if($dataFilterRule['ID'] && $dataFilterRule['NAME']) {
    $elementTitle = htmlspecialchars($dataFilterRule['NAME']);
}
if($dataFilterRule['ID'] && !$dataFilterRule['NAME']) {
    $elementTitle = Loc::getMessage('SMARTSEO_ELEMENT_DEFAULT_NAME', [
        '#ID#' => $dataFilterRule['ID'],
    ]);
}

$pageTitle = Loc::getMessage('SMARTSEO_INDEX__TITLE__FILTER_RULE')
  . ': ' . ($dataFilterRule['ID'] ? $elementTitle : Loc::getMessage('SMARTSEO_PAGE_TITLE'))
  . ': ' . ($dataFilterRule['ID'] ? Loc::getMessage('SMARTSEO_ACTION_TYPE_EDIT') : Loc::getMessage('SMARTSEO_ACTION_TYPE_ADD'));

$APPLICATION->setTitle($pageTitle);

$adminContextMenu = new CAdminUiContextMenu(array_filter([
    [
        'TEXT' => Loc::getMessage('SMARTSEO_MENU_ADD'),
        'TITLE' => Loc::getMessage('SMARTSEO_MENU_ADD'),
        'LINK' => Helper::url('filter_rule_detail/detail', ['parent_section_id' => $parentSectionId]),
        'ICON' => 'edit',
    ],
    $dataFilterRule ? [
        'TEXT' => Loc::getMessage('SMARTSEO_MENU_DELETE'),
        'TITLE' => Loc::getMessage('SMARTSEO_MENU_DELETE'),
        'LINK' => '',
        'ICON' => 'delete',
        'ONCLICK' => "contextMenuHandler.delete($dataFilterRule[ID])"
    ] : [],
  ]));

if($activeTab) {
    $_REQUEST['filter_rule_tab_control_active_tab'] = $activeTab;
}
$adminTabControl = new CAdminTabControl('filter_rule_tab_control', [
    [
        'DIV' => 'filter_rule_main',
        'TAB' => Loc::getMessage('SMARTSEO_TAB_GENERAL_NAME'),
        'TITLE' => Loc::getMessage('SMARTSEO_TAB_GENERAL_TITLE'),
        'ICON' => '',
        'ONSELECT' => "filter_rule_tab_control.onSelectTab('filter_rule_main')",
    ],
    [
        'DIV' => 'filter_rule_seo',
        'TAB' => Loc::getMessage('SMARTSEO_TAB_SEO_NAME'),
        'TITLE' => Loc::getMessage('SMARTSEO_TAB_SEO_TITLE'),
        'ONSELECT' => "filter_rule_tab_control.onSelectTab('filter_rule_seo')",
    ],
    [
        'DIV' => 'filter_rule_url',
        'TAB' => Loc::getMessage('SMARTSEO_TAB_URL_NAME'),
        'TITLE' => Loc::getMessage('SMARTSEO_TAB_URL_TITLE'),
        'ONSELECT' =>
            "filter_rule_tab_control.onSelectTab('filter_rule_url');"
            . "filter_rule_tab_control.gridReload('" . $gridUrls['GRID_ID'] . "');",

    ],
    [
        'DIV' => 'filter_rule_sitemap',
        'TAB' => Loc::getMessage('SMARTSEO_TAB_SITEMAP_NAME'),
        'TITLE' => Loc::getMessage('SMARTSEO_TAB_SITEMAP_TITLE'),
        'ONSELECT' =>
            "filter_rule_tab_control.onSelectTab('filter_rule_sitemap');"
            . "filter_rule_tab_control.gridReload('" . $gridSitemap['GRID_ID'] . "');",
    ],
    [
        'DIV' => 'filter_rule_tags',
        'TAB' => Loc::getMessage('SMARTSEO_TAB_TAGS_NAME'),
        'TITLE' => Loc::getMessage('SMARTSEO_TAB_TAGS_TITLE'),
        'ONSELECT' =>
            "filter_rule_tab_control.onSelectTab('filter_rule_tags');"
            . "filter_rule_tab_control.gridReload('" . $gridTags['GRID_ID'] . "');",
    ],
    [
        'DIV' => 'filter_rule_search',
        'TAB' => Loc::getMessage('SMARTSEO_TAB_SEARCH_NAME'),
        'TITLE' => Loc::getMessage('SMARTSEO_TAB_SEARCH_TITLE'),
        'ONSELECT' =>
            "filter_rule_tab_control.onSelectTab('filter_rule_search');"
            . "filter_rule_tab_control.gridReload('" . $gridSearch['GRID_ID'] . "');",
    ],
  ], true, true);

?>
<div id="filter_rule_detail" class="aspro-smartseo__form-detail">
  <div class="aspro-smartseo__form-detail__chain">
    <? include $this->getViewPath() . '_chain.php'; ?>
  </div>

  <div class="aspro-smartseo__form-detail__toolbar">
    <div class="aspro-smartseo__form-detail__col">
      <a href="<?= Helper::url('filter_rules/list', ['section_id' => $parentSectionId]) ?>" class="ui-btn ui-btn-light-border">
        <?= Loc::getMessage('SMARTSEO_ACTION_BACK_LIST') ?>
      </a>
    </div>
    <div class="aspro-smartseo__form-detail__col">
      <? $adminContextMenu->Show() ?>
    </div>
  </div>

  <? if($dataFilterRule) : ?>
    <? include $this->getViewPath() . '_top_info.php'; ?>
  <? endif ?>

  <div filter-rule-form-role="alert" class="ui-alert ui-alert-danger ui-alert-icon-danger aspro-ui-form__alert" style="display: none;">
    <span class="ui-alert-message" form-role="alert-body"></span>
  </div>

  <div class="aspro-smartseo__form-detail__body">
    <? $adminTabControl->Begin() ?>

    <? $adminTabControl->BeginNextTab() ?>

    <tr>
      <td colspan="2">
        <? include $this->getViewPath() . '_form_filter_rule.php'; ?>
        <? include $this->getViewPath() . '_tabs_conditions.php'; ?>
      </td>
    </tr>

    <? $adminTabControl->BeginNextTab() ?>

    <tr>
      <td colspan="2">
        <? include $this->getViewPath() . '_form_filter_rule_seo.php'; ?>
      </td>
    </tr>

    <? $adminTabControl->BeginNextTab() ?>

    <? if ($dataFilterRule) : ?>
        <tr>
          <td colspan="2">
            <? include $this->getViewPath() . '_tabs_urls.php'; ?>
          </td>
        </tr>
    <? endif ?>

    <? $adminTabControl->BeginNextTab() ?>

    <? if ($dataFilterRule) : ?>
        <tr>
          <td colspan="2">
            <? include $this->getViewPath() . '_tabs_sitemap.php'; ?>
          </td>
        </tr>
    <? endif ?>

    <? $adminTabControl->BeginNextTab() ?>

    <? if ($dataFilterRule) : ?>
        <tr>
          <td colspan="2">
            <? include $this->getViewPath() . '_tabs_tags.php'; ?>
          </td>
        </tr>
    <? endif ?>

    <? $adminTabControl->BeginNextTab() ?>

    <? if ($dataFilterRule) : ?>
        <tr>
          <td colspan="2">
            <? include $this->getViewPath() . '_tabs_search.php'; ?>
          </td>
        </tr>
    <? endif ?>

    <? $adminTabControl->Buttons() ?>

    <div class="aspro-smartseo__form-detail__buttons">
      <button filter-rule-form-role="save" data-action="save" class="ui-btn ui-btn-success" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_SAVE') ?>">
        <?= Loc::getMessage('SMARTSEO_FORM_BTN_SAVE') ?>
      </button>
      <button filter-rule-form-role="apply" data-action="apply" class="ui-btn ui-btn-primary-dark" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_APPLY') ?>">
        <?= Loc::getMessage('SMARTSEO_FORM_BTN_APPLY') ?>
      </button>
      <a filter-rule-form-role="cancel" data-action="cancel" href="<?= Helper::url('filter_rules/list', ['section_id' => $parentSectionId]) ?>" class="ui-btn ui-btn-light" title="<?= Loc::getMessage('SMARTSEO_FORM_HINT_CANCEL') ?>">
        <?= Loc::getMessage('SMARTSEO_FORM_BTN_CANCEL') ?>
      </a>
    </div>

    <? $adminTabControl->End() ?>
  </div>
</div>

<script>
    var phpObjectFilterRule = <?= CUtil::PhpToJSObject([
        'urls' => [
            'FIELD_OPTION_IBLOCK_TYPE' => Helper::url('filter_rule_detail/get_option_iblock_type'),
            'FIELD_OPTION_IBLOCK' => Helper::url('filter_rule_detail/get_option_iblock'),
            'FIELD_OPTION_IBLOCK_SECTIONS' => Helper::url('filter_rule_detail/get_option_iblock_sections'),
            'FIELD_VALUE_NAME' => Helper::url('filter_rule_detail/get_value_name'),
            'MENU_SEO_PROPERTY' => Helper::url('filter_rule_detail/get_menu_seo_property', ['filter_rule' => $dataFilterRule['ID']]),
            'SAMPLE_SEO_PROPERTY' => Helper::url('filter_rule_detail/get_sample_seo_property', ['filter_rule' => $dataFilterRule['ID']]),
            'DELETE' => Helper::url('filter_rule_detail/delete', ['sessid' => bitrix_sessid()]),
            'COPY' => Helper::url('filter_rule_detail/copy', ['sessid' => bitrix_sessid()]),
        ],
        'dataFilterRule' => $dataFilterRule,
        'aliasSeoFilterRule' => $aliasSeoFilterRule,
        'activeTab' => $activeTab,
    ]) ?>;

    BX.message({
        SMARTSEO_CONDITIONS_NEW: '<?= Loc::getMessage('SMARTSEO_CONDITIONS_NEW') ?>',
        SMARTSEO_POPUP_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_BTN_DELETE') ?>',
        SMARTSEO_POPUP_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_POPUP_BTN_CANCEL') ?>',
        SMARTSEO_POPUP_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_MESSAGE_DELETE') ?>',
    });
</script>