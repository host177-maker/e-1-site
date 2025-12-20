<?php
/**
 *  @var array $dataFilterRule
 *  @var array $gridUrls
 */

use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;
?>
<?
$gridRows = [];
foreach ($gridUrls['ROWS'] as $row) {
    $gridRow = $row;

    if ($row['REF_FILTER_CONDITION_ACTIVE'] === 'N') {
        $gridRow['ACTIVE'] = Loc::getMessage('SMARTSEO_GRID_URL_VALUE_N_PARENT');
    } else {
        $gridRow['ACTIVE'] = $row['ACTIVE'] === 'Y' ? Loc::getMessage('SMARTSEO_GRID_URL_VALUE_Y') : Loc::getMessage('SMARTSEO_GRID_URL_VALUE_N');
    }

    $gridRow['SECTION'] = $row['REF_SECTION_NAME'] . ' [' . $row['SECTION_ID'] . ']';

    $gridRow['FILTER_CONDITION'] = $row['REF_CONDITION_NAME'] ? '[' . $row['FILTER_CONDITION_ID'] . '] ' . $row['REF_CONDITION_NAME']
        : Loc::getMessage('SMARTSEO_GRID_URL_VALUE_CONDITION_DEFAULT', [
        '#ID#' => $row['FILTER_CONDITION_ID']
    ]);

    if($row['PROPERTIES']) {
        $propertyDisplayValues = [];
        foreach ($row['PROPERTIES'] as $property) {
            $propertyDisplayValues[] = implode(', ', $property['VALUES']['DISPLAY']);
        }

        $row['NAME'] = $row['REF_SECTION_NAME'] . ' - ' . implode(', ', $propertyDisplayValues);

        ob_start();
        include(__DIR__ . '/' . $gridUrls['GRID_ID'] . '/column_properties.php');
        $gridRow['PROPERTIES'] = ob_get_contents();
        ob_end_clean();
    }

    $data = [];
    foreach ($gridUrls['COLUMNS'] as $column) {
        $data[$column['id']] = $gridRow[$column['field']];
    }

    $gridRows[] = [
        'data' => $data,
        'actions' => [
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_URL_ACTION_EDIT'),
                'default' => true,
                'onclick' => vsprintf("contextMenuGridUrl.actionEdit(%d, '%s')", [
                    'ID' => $row['ID'],
                    'NAME' => $row['NAME']
                ])
            ],
            [
                'text' => $row['ACTIVE'] == 'Y'
                    ? Loc::getMessage('SMARTSEO_GRID_URL_ACTION_DEACTIVATE')
                    : Loc::getMessage('SMARTSEO_GRID_URL_ACTION_ACTIVATE'),
                'default' => true,
                'onclick' => $row['ACTIVE'] == 'Y'
                    ? "contextMenuGridUrl.actionDeactivate($row[ID])"
                    : "contextMenuGridUrl.actionActivate($row[ID])"
            ],
            [
              'delimiter' => true,
            ],
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_URL_ACTION_OPEN_ON_SITE'),
                'default' => true,
                'onclick' => "contextMenuGridUrl.actionOpenOnSite('$row[NEW_URL]');"
            ],

            [
              'delimiter' => true,
            ],
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_URL_ACTION_DELETE'),
                'default' => true,
                'onclick' => "contextMenuGridUrl.actionDelete($row[ID])"
            ],
        ]
    ];
}
?>
<div class="aspro-smartseo__form-detail__wrapper-filter-inline">
  <?
  $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
      'FILTER_ID' => $gridUrls['FILTER_ID'],
      'GRID_ID' => $gridUrls['GRID_ID'],
      'FILTER' => $gridUrls['FILTER_FIELDS'],
      'ENABLE_LIVE_SEARCH' => true,
      'ENABLE_LABEL' => true
  ]);
  ?>

</div>
<?
$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $gridUrls['GRID_ID'],
    'COLUMNS' => $gridUrls['COLUMNS'],
    'ROWS' => $gridRows,
    'FOOTER' => [
        'TOTAL_ROWS_COUNT' => $gridUrls['TOTAL_ROWS_COUNT'],
    ],
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $gridUrls['NAV_OBJECT'],
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
    var phpObjectGridConditionUrls = <?= CUtil::PhpToJSObject([
    'gridId' => $gridUrls['GRID_ID'],
    'filterRuleId' => $dataFilterRule['ID'],
    'urls' => [
        'ACTION_ACTIVATE' => Helper::url('filter_url/activate', ['sessid' => bitrix_sessid()]),
        'ACTION_DEACTIVATE' => Helper::url('filter_url/deactivate', ['sessid' => bitrix_sessid()]),
        'ACTION_DELETE' => Helper::url('filter_url/delete', ['sessid' => bitrix_sessid()]),
        'DETAIL_PAGE' => Helper::url('filter_url/detail', ['sessid' => bitrix_sessid()]),
        'FILTER_ENTITY_FILTER_CONDITION' => Helper::url('custom_entity_filter/get_inner_url_filter_condition', ['sessid' => bitrix_sessid()]),
        'FILTER_ENTITY_SECTIONS' => Helper::url('custom_entity_filter/get_inner_url_sections', ['sessid' => bitrix_sessid()]),
    ]
    ]) ?>;

    BX.message({
        SMARTSEO_GRID_URL_TAB_TITLE: '<?= Loc::getMessage('SMARTSEO_GRID_URL_TAB_TITLE') ?>',
        SMARTSEO_POPUP_URL_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_URL_BTN_DELETE') ?>',
        SMARTSEO_POPUP_URL_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_POPUP_URL_BTN_CANCEL') ?>',
        SMARTSEO_POPUP_URL_BTN_CLOSE: '<?= Loc::getMessage('SMARTSEO_POPUP_URL_BTN_CLOSE') ?>',
        SMARTSEO_POPUP_URL_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_URL_MESSAGE_DELETE') ?>',
    });

    <? if($activeTab == 'filter_rule_url') : ?>
    BX.ready(function ()
    {
      var gridObject = BX.Main.gridManager.getById('<?= $gridUrls['GRID_ID'] ?>');

      if (gridObject.hasOwnProperty('instance')) {
        gridObject.instance.reloadTable(null, null);
      }
    })
    <? endif ?>
</script>