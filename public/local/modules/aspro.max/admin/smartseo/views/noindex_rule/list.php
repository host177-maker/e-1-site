<?php
/**
 * @var \CAdminUiList $adminUiList
 * @var \Aspro\Max\Smartseo\Admin\UI\::getFilterFields $filterFields
 * @var \CDBResult $rsData
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

\Bitrix\Main\UI\Extension::load('ui.notification');

$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/list.js');

$pageTitle = Loc::getMessage('SMARTSEO_INDEX__TITLE__NOINDEX_RULE');

$APPLICATION->setTitle($pageTitle);

while ($row = $rsData->Fetch()) {
    $rowGrid = $adminUiList->AddRow("$row[ROW_TYPE]$row[ID]", $row, false, Loc::getMessage('IBLIST_A_EDIT'));

    $rowGrid->AddInputField('NAME', ['size' => 20]);
    $rowGrid->AddInputField('SORT', ['size' => 3]);
    $rowGrid->AddCheckField('ACTIVE');

    $rowGrid->AddViewField('ACTIVE', $row['ACTIVE'] === 'Y' ? Loc::getMessage('SMARTSEO_AI__YES') : Loc::getMessage('SMARTSEO_AI__NOT'));

    if (!$row['NAME']) {
        $row['NAME'] = Loc::getMessage('SMARTSEO_AI__NI_NOT_NAME', [
            '#ID#' => $row['ID'],
        ]);
    }

    if($row['NAME']) {
        $link = Helper::url('noindex_rule_detail/detail', ['id' => $row['ID']]);
        $rowGrid->AddViewField('NAME', '<a href="' . $link . '" onclick="">' . htmlspecialcharsbx($row['NAME']) . '</a>');
    }

    if ($row['IBLOCK_ID']) {
        $link = "iblock_list_admin.php?IBLOCK_ID={$row['IBLOCK_ID']}&type={$row['IBLOCK_TYPE_ID']}&lang=ru&find_section_section=0&SECTION_ID=0";
        $value = $row['REF_IBLOCK_NAME'] . ' [<a href="' . $link . '" onclick="">' . $row['IBLOCK_ID'] . '</a>]';
        $rowGrid->AddViewField('IBLOCK', $value);
    } else {
        $rowGrid->AddViewField('IBLOCK', Loc::getMessage('SMARTSEO_GRID__ALL_IBLOCK'));
    }

    if($row['IBLOCK_TYPE_ID']) {
        $rowGrid->AddViewField('IBLOCK_TYPE', $row['IBLOCK_TYPE_ID']);
    }

    if ($row['IBLOCK_SECTIONS'] && $row['IBLOCK_SECTION_ALL'] == 'N') {
        $_tmp = array_map(function($section) use ($row) {
            $link = "iblock_list_admin.php?IBLOCK_ID={$row['IBLOCK_ID']}&type={$row['IBLOCK_TYPE_ID']}&lang=ru&find_section_section={$section['ID']}&SECTION_ID={$section['ID']}";
            $viewField = str_repeat(' . ', $section['DEPTH_LEVEL']) . $section['NAME'] . ' [<a href="' . $link . '" onclick="">' . $section['ID'] . '</a>]';
            return $viewField;
        }, $row['IBLOCK_SECTIONS']);

        $value = '<div class="aspro-smartseo__grid__iblock-sections-value">'
            . implode('<br>', $_tmp)
            . '</div>';
        $rowGrid->AddViewField('IBLOCK_SECTIONS', $value);
    } else {
        $rowGrid->AddViewField('IBLOCK_SECTIONS', Loc::getMessage('SMARTSEO__GRID__ALL_SECTION'));
    }

    if ($row['CONDITIONS']) {
        ob_start();
        include(__DIR__ . '/list/column_conditions.php');
        $html = ob_get_contents();
        ob_end_clean();

        $rowGrid->AddViewField('CONDITIONS', $html);
    }

    $actions = [
        [
            'TEXT' => Loc::getMessage('SMARTSEO_AI__ACTION__EDIT'),
            'ACTION' => $adminUiList->ActionRedirect(Helper::url('noindex_rule_detail/detail', [
                  'id' => $row['ID']
            ])),
            'DEFAULT' => true,
        ],
        [
            'TEXT' => $row['ACTIVE'] === 'Y'
                ? Loc::getMessage('SMARTSEO_AI__ACTION__DEACTIVATE')
                : Loc::getMessage('SMARTSEO_AI__ACTION__ACTIVATE'),
            'ACTION' => $row['ACTIVE'] === 'Y'
                ? "contextMenuGrid.actionDeactivate($row[ID])"
                : "contextMenuGrid.actionActivate($row[ID])",
            'DEFAULT' => true,
        ],
        [
            'TEXT' => Loc::getMessage('SMARTSEO_AI__ACTION__COPY'),
            'ACTION' => "contextMenuGrid.actionCopy($row[ID])",
            'DEFAULT' => true,
        ],
        [
            'TEXT' => Loc::getMessage('SMARTSEO_AI__ACTION__DELETE'),
            'ACTION' => "contextMenuGrid.actionDelete($row[ID])",
            'DEFAULT' => true,
        ]
    ];

    $rowGrid->AddActions($actions);
}

$adminUiList->AddGroupActionTable([
    'activate' => Loc::getMessage('SMARTSEO_AI__ACTION__ACTIVATE'),
    'deactivate' => Loc::getMessage('SMARTSEO_AI__ACTION__DEACTIVATE'),
    'delete' => '',
    'edit' => '',
]);

?>

<div class="aspro-smartseo__list__wrapper-filter">
  <? $adminUiList->DisplayFilter($filterFields); ?>
</div>

<div class="aspro-smartseo__list__wrapper-grid">
  <? $adminUiList->DisplayList() ?>
</div>

<script>
    var phpObjectNoindexRule = <?=
        CUtil::PhpToJSObject([
            'urls' => [
                'ACTION_DEACTIVATE' => Helper::url('noindex_rules/deactivate', ['sessid' => bitrix_sessid()]),
                'ACTION_ACTIVATE' => Helper::url('noindex_rules/activate', ['sessid' => bitrix_sessid()]),
                'ACTION_COPY' => Helper::url('noindex_rules/copy', ['sessid' => bitrix_sessid()]),
                'ACTION_DELETE' => Helper::url('noindex_rules/delete', ['sessid' => bitrix_sessid()]),
            ],
        ]);
    ?>

    BX.message({
        SMARTSEO_POPUP_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_AI__BTN__DELETE') ?>',
        SMARTSEO_POPUP_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_AI__BTN__CANCEL') ?>',
        SMARTSEO_POPUP_BTN_CLOSE: '<?= Loc::getMessage('SMARTSEO_AI__BTN__CLOSE') ?>',
        SMARTSEO_POPUP_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_AI__MESSAGE__DELETE') ?>',
    });
</script>



