<?php
/**
 *  @var array $dataFilterRule
 *  @var array $gridConditions
 *  @var array $gridUrls
 */

use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;

?>
<?
$gridRows = [];
foreach ($gridConditions['ROWS'] as $row) {
    $gridRow = $row;

    if($row['REF_SITEMAP_ID']) {
        $link = Helper::url('sitemap_detail/detail', ['id' => $row['REF_SITEMAP_ID'], 'site_id' => $row['REF_SITEMAP_SITE_ID']]);
        $name = '';

        if($row['REF_SITEMAP_NAME']) {
            $name = htmlspecialcharsbx($row['REF_SITEMAP_NAME']);
        } else {
            $name = Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_SITEMAP_DEFAULT', [
                '#ID#' => $row['REF_SITEMAP_ID']
            ]);
        }

        $row['SITEMAP'] = '<a href="' . $link . '" target="_blank" onclick="">' . $name . '</a>';
    } else {
        $row['SITEMAP'] = Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_SITEMAP_NO_RELATED');
    }

    if($row['CONDITION_PROPERTY']) {
        ob_start();
        include(__DIR__ . '/' . $gridConditions['GRID_ID'] . '/column_properties.php');
        $gridRow['CONDITION'] = ob_get_contents();
        ob_end_clean();
    }

    ob_start();
    include(__DIR__ . '/' . $gridConditions['GRID_ID'] . '/column_info.php');
    $gridRow['INFO'] = ob_get_contents();
    ob_end_clean();

    $data = [];
    foreach ($gridConditions['COLUMNS'] as $column) {
        $data[$column['id']] = $gridRow[$column['field']];
    }

    $gridRows[] = [
        'data' => $data,
        'actions' => [
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_CONDITION_ACTION_EDIT'),
                'default' => true,
                'onclick' => vsprintf("contextMenuGridCondition.actionEdit(%d, '%s')", [
                    'ID' => $row['ID'],
                    'NAME' =>
                      htmlspecialchars($row['NAME'], ENT_QUOTES) ?: Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_NAME_DEFAULT', [
                            '#ID#' => $row['ID']
                      ])
                ])
            ],
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
                'text' => Loc::getMessage('SMARTSEO_GRID_CONDITION_ACTION_COPY'),
                'default' => true,
                'onclick' => "contextMenuGridCondition.actionCopy($row[ID])"
            ],
            [
              'delimiter' => true,
            ],
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_CONDITION_ACTION_GENERATE_URLS'),
                'default' => true,
                'onclick' => "contextMenuGridCondition.actionGenerateUrls($row[ID])"
            ],
            [
                'text' => $row['URL_CLOSE_INDEXING'] == 'Y'
                    ? Loc::getMessage('SMARTSEO_GRID_CONDITION_ACTION_OPEN_INDEXING')
                    : Loc::getMessage('SMARTSEO_GRID_CONDITION_ACTION_CLOSE_INDEXING'),
                'default' => true,
                'onclick' => $row['URL_CLOSE_INDEXING'] == 'Y'
                    ? "contextMenuGridCondition.actionOpenUrlIndexing($row[ID])"
                    : "contextMenuGridCondition.actionCloseUrlIndexing($row[ID])"
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
    'GRID_ID' => $gridConditions['GRID_ID'],
    'COLUMNS' => $gridConditions['COLUMNS'],
    'ROWS' => $gridRows,
    'FOOTER' => [
        'TOTAL_ROWS_COUNT' => $gridConditions['TOTAL_ROWS_COUNT'],
    ],
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $gridConditions['NAV_OBJECT'],
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
    'SHOW_GRID_SETTINGS_MENU' => false,
    'SHOW_NAVIGATION_PANEL' => true,
    'SHOW_PAGINATION' => true,
    'SHOW_SELECTED_COUNTER' => false,
    'SHOW_TOTAL_COUNTER' => true,
    'SHOW_PAGESIZE' => true,
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
        'gridId' => $gridConditions['GRID_ID'],
        'gridUrlId' => $gridUrls['GRID_ID'],
        'filterRuleId' => $dataFilterRule['ID'],
        'urls' => [
           'ACTION_DEACTIVATE' => Helper::url('filter_condition/deactivate', ['sessid' => bitrix_sessid()]),
           'ACTION_ACTIVATE' => Helper::url('filter_condition/activate', ['sessid' => bitrix_sessid()]),
           'ACTION_DELETE' => Helper::url('filter_condition/delete', ['sessid' => bitrix_sessid()]),
           'ACTION_COPY' => Helper::url('filter_condition/copy', ['sessid' => bitrix_sessid()]),
           'ACTION_CLOSE_URL_INDEXING' => Helper::url('filter_condition/close_url_indexing', ['sessid' => bitrix_sessid()]),
           'ACTION_OPEN_URL_INDEXING' => Helper::url('filter_condition/open_url_indexing', ['sessid' => bitrix_sessid()]),
           'ACTION_ACTIVATE_URL_STRICT_COMPLIANCE' => Helper::url('filter_condition/activate_url_strict_compliance', ['sessid' => bitrix_sessid()]),
           'ACTION_DEACTIVATE_URL_STRICT_COMPLIANCE' => Helper::url('filter_condition/deactivate_url_strict_compliance', ['sessid' => bitrix_sessid()]),
           'ACTION_GENERATE_URLS' => Helper::url('filter_condition/generate_urls', ['sessid' => bitrix_sessid()]),
           'DETAIL_PAGE' => Helper::url('filter_condition/detail', ['sessid' => bitrix_sessid()]),
        ]
    ]) ?>;

    BX.message({
        SMARTSEO_POPUP_CONDITION_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_CONDITION_BTN_DELETE') ?>',
        SMARTSEO_POPUP_CONDITION_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_POPUP_CONDITION_BTN_CANCEL') ?>',
        SMARTSEO_POPUP_CONDITION_BTN_CLOSE: '<?= Loc::getMessage('SMARTSEO_POPUP_CONDITION_BTN_CLOSE') ?>',
        SMARTSEO_POPUP_CONDITION_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_CONDITION_MESSAGE_DELETE') ?>',
        SMARTSEO_GRID_CONDITIONS_NEW: '<?= Loc::getMessage('SMARTSEO_GRID_CONDITIONS_NEW') ?>',
        SMARTSEO_GRID_CONDITION_MESSAGE_GENERATE_SUCCESS: '<?= Loc::getMessage('SMARTSEO_GRID_CONDITION_MESSAGE_GENERATE_SUCCESS') ?>',
        SMARTSEO_GRID_CONDITION_VALUE_NAME_DEFAULT: '<?= Loc::getMessage('SMARTSEO_GRID_CONDITION_VALUE_NAME_DEFAULT') ?>',
    });

    BX.ready(function ()
    {
      var gridObject = BX.Main.gridManager.getById('<?= $gridConditions['GRID_ID'] ?>');

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
                let elGrid = document.getElementById('<?= $gridConditions['GRID_ID'] ?>');
                let elementHints = elGrid.querySelectorAll('[data-ext="hint"]');
                for (var i = 0; i < elementHints.length; i++) {
                  BX.UI.Hint.init(elementHints[i]);
                }
            });
        }, timeout);
      }
    })
</script>