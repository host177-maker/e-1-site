<?php
/**
 * @var string $alias
 * @var array $data
 * @var array $listSites
 * @var array $listIblockTypes
 * @var array $listIblocks
 * @var array $listIblockSections
 * @var array $listConditionTypes
 * @var array $gridCondition
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

Loc::loadMessages($this->getViewPath() . '/detail.php');

?>
<?
$gridRows = [];
foreach ($gridCondition['ROWS'] as $row) {
    $gridRow = $row;

    $gridRow['ACTIVE'] = $row['ACTIVE'] == 'Y' ? Loc::getMessage('SMARTSEO_AI__YES') : Loc::getMessage('SMARTSEO_AI__NOT');

    ob_start();
    include $this->getViewPath() . 'detail/partial/column_grid_condition.php';
    $gridRow['CONDITION'] = ob_get_contents();
    ob_end_clean();

    ob_start();
    include $this->getViewPath() . 'detail/partial/column_grid_value.php';
    $gridRow['VALUE'] = ob_get_contents();
    ob_end_clean();

    $data = [];
    foreach ($gridCondition['COLUMNS'] as $column) {
        $data[$column['id']] = $gridRow[$column['field']];
    }

    $gridRows[] = [
        'data' => $data,
        'actions' => [
            [
                'text' => Loc::getMessage('SMARTSEO_AI__ACTION__EDIT'),
                'default' => true,
                'onclick' => vsprintf("contextMenuGridCondition.actionEdit(%d, '%s')", [
                    'ID' => $row['ID'],
                    'NAME' => Loc::getMessage('SMARTSEO__TAB__CONDITION', [
                        '#ID#' => $row['ID'],
                        '#CONDITION#' => $listConditionTypes[$row['TYPE']],

                    ]),
                ])
            ],
            [
                'text' => $row['ACTIVE'] == 'Y'
                    ? Loc::getMessage('SMARTSEO_AI__ACTION__DEACTIVATE')
                    : Loc::getMessage('SMARTSEO_AI__ACTION__ACTIVATE'),
                'default' => true,
                'onclick' => $row['ACTIVE'] == 'Y'
                    ? "contextMenuGridCondition.actionDeactivate($row[ID])"
                    : "contextMenuGridCondition.actionActivate($row[ID])"
            ],
            [
              'delimiter' => true,
            ],
            [
                'text' => Loc::getMessage('SMARTSEO_AI__ACTION__DELETE'),
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
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $gridCondition['NAV_OBJECT'],
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' => [
        ['NAME' => '20', 'VALUE' => '20'],
        ['NAME' => '50', 'VALUE' => '50'],
        ['NAME' => '100', 'VALUE' => '100']
    ],
    'AJAX_OPTION_JUMP' => 'N',
    'SHOW_CHECK_ALL_CHECKBOXES' => false,
    'SHOW_ROW_ACTIONS_MENU' => true,
    'SHOW_GRID_SETTINGS_MENU' => true,
    'SHOW_NAVIGATION_PANEL' => false,
    'SHOW_PAGINATION' => false,
    'SHOW_SELECTED_COUNTER' => false,
    'SHOW_TOTAL_COUNTER' => false,
    'SHOW_PAGESIZE' => false,
    'SHOW_ACTION_PANEL' => true,
    'ALLOW_COLUMNS_SORT' => false,
    'ALLOW_COLUMNS_RESIZE' => false,
    'ALLOW_HORIZONTAL_SCROLL' => true,
    'ALLOW_SORT' => false,
    'ALLOW_PIN_HEADER' => false,
    'AJAX_OPTION_HISTORY' => 'N',
]);
?>
<script>
    var phpObjectGridConditions = <?= CUtil::PhpToJSObject([
        'gridId' => $gridCondition['GRID_ID'],
        'noindexRuleId' => $data['ID'],
        'urls' => [
           'ACTION_DEACTIVATE' => Helper::url('noindex_condition/deactivate', ['sessid' => bitrix_sessid()]),
           'ACTION_ACTIVATE' => Helper::url('noindex_condition/activate', ['sessid' => bitrix_sessid()]),
           'ACTION_DELETE' => Helper::url('noindex_condition/delete', ['sessid' => bitrix_sessid()]),
           'DETAIL_PAGE' => Helper::url('noindex_condition/detail', ['sessid' => bitrix_sessid()]),
        ]
    ]) ?>;

    BX.ready(function ()
    {
      var gridObject = BX.Main.gridManager.getById('<?= $gridCondition['GRID_ID'] ?>');

      if (gridObject.hasOwnProperty('instance')) {

        let timeout = 0;

        if(BX.browser.IsMac()) {
            timeout = 1000;
        }

        setTimeout(function(){
            gridObject.instance.reloadTable(null, null, function(){
                if(typeof BX.UI.Hint !== 'object') {
                    return;
                }
                let elGrid = document.getElementById('<?= $gridCondition['GRID_ID'] ?>');
                let elementHints = elGrid.querySelectorAll('[data-ext="hint"]');
                for (var i = 0; i < elementHints.length; i++) {
                  BX.UI.Hint.init(elementHints[i]);
                }
            });
        }, timeout);
      }
    })
</script>