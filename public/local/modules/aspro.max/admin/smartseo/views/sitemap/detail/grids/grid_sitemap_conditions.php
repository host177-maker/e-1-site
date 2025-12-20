<?php
/**
 *  @var string $gridCondition
 */

use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;

?>
<?
$gridRows = [];
foreach ($gridCondition['ROWS'] as $row) {
    $gridRow = $row;

    $gridRow['ACTIVE'] = $row['ACTIVE'] === 'Y' ? Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_Y') : Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_N');

    $filterRuleValue = '';
    if ($row['REF_RULE_NAME']) {
        $filterRuleValue = '[' . $row['FILTER_RULE_ID'] . '] ' . $row['REF_RULE_NAME'];
    } else {
        $filterRuleValue = Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_RULE_DEFAULT', [
            '#ID#' => $row['FILTER_RULE_ID'],
        ]);
    }

    if($row['REF_RULE_ACTIVE'] === 'N') {
        $filterRuleValue .= '<span class="aspro-smartseo__alert-grid__yellow">';
        $filterRuleValue .= Loc::getMessage('SMARTSEO_GRID_CONDITION_RULE_N');
        $filterRuleValue .= '</span>';
    }

    $link = Helper::url('filter_rule_detail/detail', ['id' => $row['FILTER_RULE_ID']]);
    $gridRow['FILTER_RULE'] = '<a href="' . $link . '" target="_blank" onclick="">' . $filterRuleValue . '</a>';

    if ($row['REF_CONDITION_NAME']) {
        $gridRow['FILTER_CONDITION'] = '[' . $row['FILTER_CONDITION_ID'] . '] ' . $row['REF_CONDITION_NAME'];
    } else {
        $gridRow['FILTER_CONDITION'] = Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_CONDITION_DEFAULT', [
            '#ID#' => $row['FILTER_CONDITION_ID'],
        ]);
    }

    if($row['REF_CONDITION_ACTIVE'] === 'N') {
        $gridRow['FILTER_CONDITION'] .= '<span class="aspro-smartseo__alert-grid__yellow">';
        $gridRow['FILTER_CONDITION'] .= Loc::getMessage('SMARTSEO_GRID_CONDITION_CONDITION_N');
        $gridRow['FILTER_CONDITION'] .= '</span>';
    }

    $data = [];
    foreach ($gridCondition['COLUMNS'] as $column) {
        $data[$column['id']] = $gridRow[$column['field']];
    }

    $gridRows[] = [
        'data' => $data,
        'actions' => [
            [
                'text' => $row['ACTIVE'] == 'Y'
                    ? Loc::getMessage('SMARTSEO_GRID_CONDITION_ACTION_DEACTIVATE')
                    : Loc::getMessage('SMARTSEO_GRID_CONDITION_ACTION_ACTIVATE'),
                'default' => true,
                'onclick' => $row['ACTIVE'] == 'Y'
                    ? "contextMenuGridCondition.actionDeactivate($row[ID])"
                    : "contextMenuGridCondition.actionActivate($row[ID])"
            ],
            [
              'delimiter' => true,
            ],
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_CONDITION_ACTION_DELETE'),
                'default' => true,
                'onclick' => "contextMenuGridCondition.actionDelete($row[ID])"
            ],
        ]
    ];
}
?>
<?
$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $gridCondition['GRID_ID'],
    'COLUMNS' => $gridCondition['COLUMNS'],
    'ROWS' => $gridRows,
    'FOOTER' => [
        'TOTAL_ROWS_COUNT' => $gridCondition['TOTAL_ROWS_COUNT'],
    ],
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $gridCondition['NAV_OBJECT'],
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
    var phpObjectGridSitemapCondition = <?= CUtil::PhpToJSObject([
        'gridId' => $gridCondition['GRID_ID'],
        'urls' => [
            'ACTION_ACTIVATE' => Helper::url('filter_sitemap/activate', ['sessid' => bitrix_sessid()]),
            'ACTION_DEACTIVATE' => Helper::url('filter_sitemap/deactivate', ['sessid' => bitrix_sessid()]),
            'ACTION_DELETE' => Helper::url('filter_sitemap/delete', ['sessid' => bitrix_sessid()]),
            'DETAIL_PAGE' => Helper::url('filter_sitemap/detail', ['sessid' => bitrix_sessid()]),
        ]
    ]) ?>;

    BX.ready(function ()
    {
      var gridObject = BX.Main.gridManager.getById('<?= $gridCondition['GRID_ID'] ?>');

      if (gridObject.hasOwnProperty('instance')) {
            gridObject.instance.reloadTable(null, null);
        }
    })

     BX.message({
        SMARTSEO_POPUP_CONDITION_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_CONDITION_BTN_DELETE') ?>',
        SMARTSEO_POPUP_CONDITION_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_POPUP_CONDITION_BTN_CANCEL') ?>',
        SMARTSEO_POPUP_CONDITION_BTN_CLOSE: '<?= Loc::getMessage('SMARTSEO_POPUP_CONDITION_BTN_CLOSE') ?>',
        SMARTSEO_POPUP_CONDITION_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_CONDITION_MESSAGE_DELETE') ?>',
    });
</script>