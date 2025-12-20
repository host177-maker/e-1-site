<?php
/**
 *  @var array $dataFilterRule
 *  @var array $gridTags
 */

use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;
?>
<?
$gridRows = [];
foreach ($gridTags['ROWS'] as $row) {
    $gridRow = $row;

    $gridRow['ACTIVE'] = $row['ACTIVE'] === 'Y' ? Loc::getMessage('SMARTSEO_GRID_TAG_VALUE_Y') : Loc::getMessage('SMARTSEO_GRID_TAG_VALUE_N');

    $gridRow['TYPE'] = '';
    switch ($row['TYPE']) {
        case \Aspro\Max\Smartseo\Models\SmartseoFilterTagTable::TYLE_SELF_SECTION:
            $gridRow['TYPE'] = \Aspro\Max\Smartseo\Models\SmartseoFilterTagTable::getTypeParams($row['TYPE']);

            break;
         case \Aspro\Max\Smartseo\Models\SmartseoFilterTagTable::TYPE_SECTION:
            $html = '';
            $html .= '<span class="aspro-smartseo__grid-text__note">'
                .  \Aspro\Max\Smartseo\Models\SmartseoFilterTagTable::getTypeParams($row['TYPE'])
                . '</span>';
            $html .= '[' . $row['SECTION_ID'] . '] ' . $row['SECTION_NAME'];

            $gridRow['TYPE'] = $html;

            break;

         case \Aspro\Max\Smartseo\Models\SmartseoFilterTagTable::TYPR_FILTER_CONDITION:
            $html = '';
            $html .= '<span class="aspro-smartseo__grid-text__note">'
                .  \Aspro\Max\Smartseo\Models\SmartseoFilterTagTable::getTypeParams($row['TYPE'])
                . '</span>';

            if($row['PARENT_FILTER_CONDITION_NAME']) {
                $html .= '[' . $row['PARENT_FILTER_CONDITION_ID'] . '] ' . $row['PARENT_FILTER_CONDITION_NAME'];
            } else {
               $html .= Loc::getMessage('SMARTSEO_GRID_TAG_VALUE_CONDITION_DEFAULT', [
                    '#ID#' => $row['PARENT_FILTER_CONDITION_ID']
                ]);
            }

            $gridRow['TYPE'] = $html;

            break;

        default:
            break;
    }

    $gridRow['FILTER_CONDITION'] = '';
    if($row['FILTER_CONDITION_NAME']) {
        $gridRow['FILTER_CONDITION'] = '[' . $row['FILTER_CONDITION_ID'] . '] ' . $row['FILTER_CONDITION_NAME'];
    } else {
        $gridRow['FILTER_CONDITION'] = Loc::getMessage('SMARTSEO_GRID_TAG_VALUE_CONDITION_DEFAULT', [
            '#ID#' => $row['FILTER_CONDITION_ID']
        ]);
    }

    $data = [];
    foreach ($gridTags['COLUMNS'] as $column) {
        $data[$column['id']] = $gridRow[$column['field']];
    }

    $gridRows[] = [
        'data' => $data,
        'actions' => [
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_TAG_ACTION_EDIT'),
                'default' => true,
                'onclick' => vsprintf("contextMenuGridTags.actionEdit(%d, '%s')", [
                    'ID' => $row['ID'],
                    'NAME' => $row['FILTER_CONDITION_NAME'] ?: Loc::getMessage('SMARTSEO_GRID_TAG_VALUE_CONDITION_DEFAULT', [
                        '#ID#' => $row['FILTER_CONDITION_ID'],
                    ])
                ])
            ],
            [
                'text' => $row['ACTIVE'] == 'Y'
                    ? Loc::getMessage('SMARTSEO_GRID_TAG_ACTION_DEACTIVATE')
                    : Loc::getMessage('SMARTSEO_GRID_TAG_ACTION_ACTIVATE'),
                'default' => true,
                'onclick' => $row['ACTIVE'] == 'Y'
                    ? "contextMenuGridTags.actionDeactivate($row[ID])"
                    : "contextMenuGridTags.actionActivate($row[ID])"
            ],
            [
              'delimiter' => true,
            ],
            [
                'text' => Loc::getMessage('SMARTSEO_GRID_TAG_ACTION_DELETE'),
                'default' => true,
                'onclick' => "contextMenuGridTags.actionDelete($row[ID])"
            ],
        ]
    ];
}
?>
<?
$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => $gridTags['GRID_ID'],
    'COLUMNS' => $gridTags['COLUMNS'],
    'ROWS' => $gridRows,
    'FOOTER' => [
        'TOTAL_ROWS_COUNT' => $gridTags['TOTAL_ROWS_COUNT'],
    ],
    'SHOW_ROW_CHECKBOXES' => false,
    'NAV_OBJECT' => $gridTags['NAV_OBJECT'],
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
    var phpObjectGridTags = <?= CUtil::PhpToJSObject([
        'gridId' => $gridTags['GRID_ID'],
        'filterRuleId' => $dataFilterRule['ID'],
        'urls' => [
            'ACTION_ACTIVATE' => Helper::url('filter_tag/activate', ['sessid' => bitrix_sessid()]),
            'ACTION_DEACTIVATE' => Helper::url('filter_tag/deactivate', ['sessid' => bitrix_sessid()]),
            'ACTION_DELETE' => Helper::url('filter_tag/delete', ['sessid' => bitrix_sessid()]),
            'DETAIL_PAGE' => Helper::url('filter_tag/detail', ['sessid' => bitrix_sessid()]),
        ]
    ]) ?>;

    BX.message({
        SMARTSEO_POPUP_TAG_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_TAG_BTN_DELETE') ?>',
        SMARTSEO_POPUP_TAG_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_POPUP_TAG_BTN_CANCEL') ?>',
        SMARTSEO_POPUP_TAG_BTN_CLOSE: '<?= Loc::getMessage('SMARTSEO_POPUP_TAG_BTN_CLOSE') ?>',
        SMARTSEO_POPUP_TAG_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_TAG_MESSAGE_DELETE') ?>',
        SMARTSEO_GRID_TAG_NEW: '<?= Loc::getMessage('SMARTSEO_GRID_TAG_NEW') ?>',
    });

    <? if($activeTab == 'filter_rule_tags') : ?>
    BX.ready(function ()
    {
      var gridObject = BX.Main.gridManager.getById('<?= $gridTags['GRID_ID'] ?>');

      if (gridObject.hasOwnProperty('instance')) {
        gridObject.instance.reloadTable(null, null);
      }
    })
    <? endif ?>
</script>