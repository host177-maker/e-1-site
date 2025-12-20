<?php
/**
 * @var \CAdminUiList $adminUiList
 * @var \Aspro\Max\Smartseo\Admin\UI\SitemapController::getFilterFields $filterFields
 * @var \CDBResult $rsData
 */

use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

global $APPLICATION;

\Bitrix\Main\UI\Extension::load('ui.notification');

$pageTitle = Loc::getMessage('SMARTSEO_INDEX__TITLE__SITEMAP');

$APPLICATION->AddHeadScript($this->getPathSelfScripts() . '/list.js');

$APPLICATION->setTitle($pageTitle);

while ($row = $rsData->Fetch()) {
    $_gridRow = $adminUiList->AddRow("$row[TYPE]$row[ID]", $row, false, Loc::getMessage('IBLIST_A_EDIT'));

    $_gridRow->AddInputField('NAME', ['size' => 20]);
    $_gridRow->AddCheckField('ACTIVE');

    $_link = Helper::url('sitemap_detail/detail', ['id' => $row['ID'], 'site_id' => $row['SITE_ID']]);

    if (!$row['NAME']) {
        $row['NAME'] = Loc::getMessage('SMARTSEO_GRID_DATA_DEFAUL_NAME', [
            '#ID#' => $row['ID'],
        ]);
    }
    $_gridRow->AddViewField('NAME', '<a href="' . $_link . '" onclick="">' . htmlspecialcharsbx($row['NAME']) . '</a>');

    $_gridRow->AddViewField('ACTIVE', $row['ACTIVE'] == 'Y' ? Loc::getMessage('SMARTSEO_GRID_DATA_Y') : Loc::getMessage('SMARTSEO_GRID_DATA_N'));
    $_gridRow->AddViewField('SITEMAP_ADDRESS', $row['PROTOCOL'] . implode('/', [$row['DOMAIN'], $row['FILE_NAME']]));

    if(!$row['DATE_LAST_LAUNCH']) {
        $_gridRow->AddViewField('DATE_LAST_LAUNCH', Loc::getMessage('SMARTSEO_GRID_DATE_LAST_LAUNCH_NEVER'));
    }

    $_actions = [
        [
            'TEXT' => Loc::getMessage('SMARTSEO_GRID_ACTION_EDIT'),
            'ACTION' => sprintf("contextMenuGrid.actionEdit('%s')", Helper::url('sitemap_detail/detail', [
                'id' => $row['ID'],
                'site_id' => $row['SITE_ID'],
            ])),
            'DEFAULT' => true,
        ],
        [
            'TEXT' => Loc::getMessage('SMARTSEO_GRID_ACTION_GENERATE'),
            'ACTION' => "contextMenuGrid.actionGenerate($row[ID])",
            'DEFAULT' => true,
        ],
        [
            'TEXT' => Loc::getMessage('SMARTSEO_GRID_ACTION_DELETE'),
            'ACTION' => "contextMenuGrid.actionDelete($row[ID])",
            'DEFAULT' => true,
        ]
    ];

    $_gridRow->AddActions($_actions);
}

$adminUiList->AddGroupActionTable([
    'activate' => Loc::getMessage('SMARTSEO_GRID_ACTION_ACTIVATE'),
    'deactivate' => Loc::getMessage('SMARTSEO_GRID_ACTION_DEACTIVATE'),
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
    var phpObjectSitemap = <?=
        CUtil::PhpToJSObject([
            'urls' => [
                'ACTION_ACTIVATE' => Helper::url('sitemap/activate', ['sessid' => bitrix_sessid()]),
                'ACTION_DEACTIVATE' => Helper::url('sitemap/deactivate', ['sessid' => bitrix_sessid()]),
                'ACTION_DELETE' => Helper::url('sitemap/delete', ['sessid' => bitrix_sessid()]),
                'ACTION_GENERATE_SITEMAP' => Helper::url('sitemap/generate', ['sessid' => bitrix_sessid()]),
            ],
        ]);
    ?>

    BX.message({
        SMARTSEO_POPUP_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_BTN_DELETE') ?>',
        SMARTSEO_POPUP_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_POPUP_BTN_CANCEL') ?>',
        SMARTSEO_POPUP_BTN_CLOSE: '<?= Loc::getMessage('SMARTSEO_POPUP_BTN_CLOSE') ?>',
        SMARTSEO_POPUP_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_POPUP_MESSAGE_DELETE') ?>',
        SMARTSEO_POPUP_MESSAGE_GENERATE_SUCCESS: '<?= Loc::getMessage('SMARTSEO_POPUP_MESSAGE_GENERATE_SUCCESS') ?>',
    });
</script>
