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

$pageTitle = Loc::getMessage('SMARTSEO_INDEX__TITLE__SEO_TEXT');

$APPLICATION->setTitle($pageTitle);

while ($row = $rsData->Fetch()) {
    $rowGrid = $adminUiList->AddRow("$row[ROW_TYPE]$row[ID]", $row, false, Loc::getMessage('IBLIST_A_EDIT'));

    $rowGrid->AddInputField('NAME', ['size' => 20]);
    $rowGrid->AddInputField('SORT', ['size' => 3]);

    if (!$row['NAME']) {
        $row['NAME'] = Loc::getMessage('SMARTSEO_AI__ST_NOT_NAME', [
            '#ID#' => $row['ID'],
        ]);
    }

    if($row['NAME']) {
        if($row['TYPE'] == Smartseo\Models\SmartseoSeoTextTable::TYPE_SECTION) {
            $link = Helper::url('seo_text_section/detail', ['id' => $row['ID']]);
        } else {
            $link = Helper::url('seo_text_element/detail', ['id' => $row['ID']]);
        }

        $rowGrid->AddViewField('NAME', '<a href="' . $link . '" onclick="">' . htmlspecialcharsbx($row['NAME']) . '</a>');
    }

    if ($row['IBLOCK_ID']) {
        $link = "iblock_list_admin.php?IBLOCK_ID={$row['IBLOCK_ID']}&type={$row['IBLOCK_TYPE_ID']}&lang=ru&find_section_section=0&SECTION_ID=0";
        $value = $row['REF_IBLOCK_NAME'] . ' [<a href="' . $link . '" onclick="">' . $row['IBLOCK_ID'] . '</a>]';
        $rowGrid->AddViewField('IBLOCK', $value);
    }

    if ($row['IBLOCK_TYPE_ID']) {
        $rowGrid->AddViewField('IBLOCK_TYPE', $row['IBLOCK_TYPE_ID']);
    }

    if ($row['IBLOCK_SECTIONS']) {
        $_tmp = array_map(function($section) use ($row) {
            $link = "iblock_list_admin.php?IBLOCK_ID={$row['IBLOCK_ID']}&type={$row['IBLOCK_TYPE_ID']}&lang=ru&find_section_section={$section['ID']}&SECTION_ID={$section['ID']}";
            $viewField = str_repeat(' . ', $section['DEPTH_LEVEL']) . $section['NAME'] . ' [<a href="' . $link . '" onclick="">' . $section['ID'] . '</a>]';
            return $viewField;
        }, $row['IBLOCK_SECTIONS']);

        $value = '<div class="aspro-smartseo__grid__iblock-sections-value">'
            . implode('<br>', $_tmp)
            . '</div>';
    } else {
        $value = Loc::getMessage('SMARTSEO_GRID__ALL_SECTION');
    }

    $rowGrid->AddViewField('IBLOCK_SECTIONS', $value);

    if ($row['TYPE'] == Smartseo\Models\SmartseoSeoTextTable::TYPE_SECTION) {
        $rowGrid->AddViewField('TYPE', Loc::getMessage('SMARTSEO_GRID__VALUE__TYPE_SECTION'));
    } elseif ($row['TYPE'] == Smartseo\Models\SmartseoSeoTextTable::TYPE_ELEMENT) {
        $rowGrid->AddViewField('TYPE', Loc::getMessage('SMARTSEO_GRID__VALUE__TYPE_ELEMENT'));
    }

    $actions = [
        [
            'TEXT' => Loc::getMessage('SMARTSEO_AI__ACTION__EDIT'),
            'ACTION' => $row['TYPE'] === Smartseo\Models\SmartseoSeoTextTable::TYPE_SECTION
                ? $adminUiList->ActionRedirect(Helper::url('seo_text_section/detail', [
                    'id' => $row['ID']
                  ]))
                : $adminUiList->ActionRedirect(Helper::url('seo_text_element/detail', [
                    'id' => $row['ID']
                  ])),
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
    var phpObjectSeoText = <?=
        CUtil::PhpToJSObject([
            'urls' => [
                'ACTION_COPY' => Helper::url('seo_text/copy', ['sessid' => bitrix_sessid()]),
                'ACTION_DELETE' => Helper::url('seo_text/delete', ['sessid' => bitrix_sessid()]),
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
