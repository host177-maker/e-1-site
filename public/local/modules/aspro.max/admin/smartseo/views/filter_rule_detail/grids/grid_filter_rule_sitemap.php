<?php
/**
 *  @var array $dataFilterRule
 *  @var array $gridSitemap
 */

use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;
?>
<?
$gridRows = [];
foreach ($gridSitemap['ROWS'] as $row) {
    $gridRow = $row;

    $gridRow['ACTIVE'] = $row['ACTIVE'] === 'Y' ? Loc::getMessage('SMARTSEO_GRID_SITEMAP_VALUE_Y') : Loc::getMessage('SMARTSEO_GRID_SITEMAP_VALUE_N');

    $sitemapValue = '';
    if($row['REF_SITEMAP_NAME']) {
        $sitemapValue = '[' . $row['SITEMAP_ID'] . '] ' . $row['REF_SITEMAP_NAME'];
    } else {
        $sitemapValue = Loc::getMessage('SMARTSEO_GRID_SITEMAP_VALUE_SITEMAP_DEFAULT', [
            '#ID#' => $row['SITEMAP_ID']
        ]);
    }
    $link = Helper::url('sitemap_detail/detail', ['id' => $row['SITEMAP_ID'], 'site_id' => $row['REF_SITEMAP_SITE_ID']]);
    $sitemapValue = '<a href="' . $link . '" target="_blank" onclick="">' . htmlspecialcharsbx($sitemapValue . ' (' . $row['REF_SITEMAP_SITE_ID'] .')') . '</a>';

    if($row['REF_SITEMAP_ACTIVE'] === 'N') {
        $sitemapValue .= '<span class="aspro-smartseo__alert-grid__yellow">';
        $sitemapValue .= Loc::getMessage('SMARTSEO_GRID_SITEMAP_SITEMAP_N');
        $sitemapValue .= '</span>';
    }

    $gridRow['SITEMAP'] = $sitemapValue;

    if ($row['REF_CONDITION_NAME']) {
        $gridRow['FILTER_CONDITION'] = '[' . $row['FILTER_CONDITION_ID'] . '] ' . $row['REF_CONDITION_NAME'];
    } else {
        $gridRow['FILTER_CONDITION'] = Loc::getMessage('SMARTSEO_GRID_SITEMAP_VALUE_CONDITION_DEFAULT', [
            '#ID#' => $row['FILTER_CONDITION_ID'],
        ]);
    }

    if($row['REF_SITEMAP_ACTIVE'] === 'N') {
        $row['FILTER_SITEMAP'] .= '<span class="aspro-smartseo__alert-grid__yellow">';
        $row['FILTER_SITEMAP'] .= Loc::getMessage('SMARTSEO_GRID_SITEMAP_SITEMAP_N');
        $row['FILTER_SITEMAP'] .= '</span>';
    }

    $data = [];
    foreach ($gridSitemap['COLUMNS'] as $column) {
        $data[$column['id']] = $gridRow[$column['field']];
    }

    $gridRows[] = [
        'data' => $data,
        'actions' => [
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_SITEMAP_ACTION_EDIT'),
                'default' => true,
                'onclick' => vsprintf("contextMenuGridSitemap.actionEdit(%d, '%s')", [
                    'ID' => $row['ID'],
                    'NAME' =>
                      htmlspecialchars($row['FILTER_CONDITION'], ENT_QUOTES) ?: Loc::getMessage('SMARTSEO_GRID_SITEMAP_DEFAULT_TAB_NAME', [
                        '#ID#' => $row['ID'],
                      ])
                ])
            ],
            [
                'text' => $row['ACTIVE'] == 'Y'
                    ? Loc::getMessage('SMARTSEO_GRID_SITEMAP_ACTION_DEACTIVATE')
                    : Loc::getMessage('SMARTSEO_GRID_SITEMAP_ACTION_ACTIVATE'),
                'default' => true,
                'onclick' => $row['ACTIVE'] == 'Y'
                    ? "contextMenuGridSitemap.actionDeactivate($row[ID])"
                    : "contextMenuGridSitemap.actionActivate($row[ID])"
            ],
            [
              'delimiter' => true,
            ],
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_SITEMAP_ACTION_DELETE'),
                'default' => true,
                'onclick' => "contextMenuGridSitemap.actionDelete($row[ID])"
            ],
        ]
    ];
}
?>
<?
$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $gridSitemap['GRID_ID'],
    'COLUMNS' => $gridSitemap['COLUMNS'],
    'ROWS' => $gridRows,
    'FOOTER' => [
        'TOTAL_ROWS_COUNT' => $gridSitemap['TOTAL_ROWS_COUNT'],
    ],
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $gridSitemap['NAV_OBJECT'],
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' => [
        ['NAME' => '10', 'VALUE' => '10'],
        ['NAME' => '20', 'VALUE' => '20'],
        ['NAME' => '50', 'VALUE' => '50'],
        ['NAME' => '100', 'VALUE' => '100']
    ],
    'AJAX_OPTION_JUMP' => 'N',
    'SHOW_CHECK_ALL_CHECKBOXES' => false,
    'SHOW_ROW_ACTIONS_MENU' => true,
    'SHOW_GRID_SETTINGS_MENU' => true,
    'SHOW_NAVIGATION_PANEL' => true,
    'SHOW_PAGINATION' => true,
    'SHOW_SELECTED_COUNTER' => false,
    'SHOW_TOTAL_COUNTER' => true,
    'SHOW_PAGESIZE' => true,
    'SHOW_ACTION_PANEL' => true,
    'ALLOW_COLUMNS_SORT' => true,
    'ALLOW_COLUMNS_RESIZE' => false,
    'ALLOW_HORIZONTAL_SCROLL' => true,
    'ALLOW_SORT' => true,
    'ALLOW_PIN_HEADER' => false,
    'AJAX_OPTION_HISTORY' => 'N',
]);
?>
<script>
    var phpObjectGridSitemap = <?= CUtil::PhpToJSObject([
        'gridId' => $gridSitemap['GRID_ID'],
        'filterRuleId' => $dataFilterRule['ID'],
        'urls' => [
            'ACTION_ACTIVATE' => Helper::url('filter_sitemap/activate', ['sessid' => bitrix_sessid()]),
            'ACTION_DEACTIVATE' => Helper::url('filter_sitemap/deactivate', ['sessid' => bitrix_sessid()]),
            'ACTION_DELETE' => Helper::url('filter_sitemap/delete', ['sessid' => bitrix_sessid()]),
            'DETAIL_PAGE' => Helper::url('filter_sitemap/detail', ['sessid' => bitrix_sessid()]),
        ]
    ]) ?>;

    BX.message({
        SMARTSEO_POPUP_SITEMAP_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_SITEMAP_BTN_DELETE') ?>',
        SMARTSEO_POPUP_SITEMAP_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_POPUP_SITEMAP_BTN_CANCEL') ?>',
        SMARTSEO_POPUP_SITEMAP_BTN_CLOSE: '<?= Loc::getMessage('SMARTSEO_POPUP_SITEMAP_BTN_CLOSE') ?>',
        SMARTSEO_POPUP_SITEMAP_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_SITEMAP_MESSAGE_DELETE') ?>',
        SMARTSEO_GRID_SITEMAP_NEW: '<?= Loc::getMessage('SMARTSEO_GRID_SITEMAP_NEW') ?>',
    });

    <? if($activeTab == 'filter_rule_sitemap') : ?>
    BX.ready(function ()
    {
      var gridObject = BX.Main.gridManager.getById('<?= $gridSitemap['GRID_ID'] ?>');

      if (gridObject.hasOwnProperty('instance')) {
        gridObject.instance.reloadTable(null, null);
      }
    })
    <? endif ?>
</script>