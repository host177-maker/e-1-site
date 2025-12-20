<?php
/**
 * @var \CAdminUiList $adminUiList
 * @var \Aspro\Max\Smartseo\Admin\UI\FilterRulesAdminUI::getFilterFields $filterFields
 * @var \CDBResult $rsData
 * @var array $chainSections
 */
use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;


global $APPLICATION;

$APPLICATION->setTitle(Loc::getMessage('SMARTSEO_INDEX__TITLE__FILTER_RULE'));

$APPLICATION->AddHeadScript('/bitrix/js/' . Smartseo\General\Smartseo::MODULE_ID . '/smartseo/filter_rules.js');

while ($row = $rsData->Fetch()) {
    $_gridRow = $adminUiList->AddRow("$row[TYPE]$row[ID]", $row, false, Loc::getMessage('IBLIST_A_EDIT'));

    $_gridRow->AddInputField('NAME', ['size' => 20]);

    if($row['ACTIVE']) {
        $_gridRow->AddCheckField('ACTIVE');
    }

    $_gridRow->AddCheckField('URL_CLOSE_INDEXING');
    $_gridRow->AddInputField('SORT', ['size' => 3]);

    if ($row['TYPE'] === 'S') {
        $_link = Helper::url('filter_rules/list', ['section_id' => $row['ID']]);
        $_viewField = '<a href="' . $_link . '" onclick="" class="adm-list-table-icon-link">'
          . '<span class="adm-submenu-item-link-icon adm-list-table-icon iblock-section-icon"></span>'
          . '<span class="adm-list-table-link">'
          . htmlspecialcharsbx($row['NAME'])
          . '</span>'
          . '</a>';

        if ($row['DESCRIPTION']) {
            $_viewField .= '<div class="aspro-smartseo__grid__description-value">'
              . $row['DESCRIPTION']
              . '</div>';
        }
        $_gridRow->AddViewField('NAME', $_viewField);
    } else {
        $row['NAME'] = $row['NAME'] ?: Loc::getMessage('SMARTSEO_AI__FR_NOT_NAME', [
            '#ID#' => $row['ID'],
        ]);
        $_link = Helper::url('filter_rule_detail/detail', ['id' => $row['ID'], 'parent_section_id' => $row['SECTION_ID']]);
        $_gridRow->AddViewField('NAME', '<a href="' . $_link . '" onclick="">' . htmlspecialcharsbx($row['NAME']) . '</a>');
    }

    if($row['ACTIVE']) {
        $_gridRow->AddViewField('ACTIVE', $row['ACTIVE'] == 'Y' ? Loc::getMessage('SMARTSEO_AI__YES') : Loc::getMessage('SMARTSEO_AI__NOT'));
    }

    if ($row['URL_CLOSE_INDEXING']) {
        $_gridRow->AddViewField('URL_CLOSE_INDEXING', $row['URL_CLOSE_INDEXING'] == 'Y' ? Loc::getMessage('SMARTSEO_AI__YES') : Loc::getMessage('SMARTSEO_AI__NOT'));
    } else {
        $_gridRow->AddViewField('URL_CLOSE_INDEXING', '');
    }

    $_gridRow->AddViewField('IBLOCK_TYPE_NAME', $row['IBLOCK_TYPE_ID']);

    if ($row['IBLOCK_ID']) {
        $_link = "iblock_list_admin.php?IBLOCK_ID={$row['IBLOCK_ID']}&type={$row['IBLOCK_TYPE_ID']}&lang=ru&find_section_section=0&SECTION_ID=0";
        $_viewField = $row['REF_IBLOCK_NAME'] . ' [<a href="' . $_link . '" onclick="">' . $row['IBLOCK_ID'] . '</a>]';
        $_gridRow->AddViewField('IBLOCK_NAME', $_viewField);
    }

    if ($row['IBLOCK_SECTIONS']) {
        $_tmp = array_map(function($section) use ($row) {
            $link = "iblock_list_admin.php?IBLOCK_ID={$row['IBLOCK_ID']}&type={$row['IBLOCK_TYPE_ID']}&lang=ru&find_section_section={$section['ID']}&SECTION_ID={$section['ID']}";
            $viewField = str_repeat(' . ', $section['DEPTH_LEVEL']) . $section['NAME'] . ' [<a href="' . $link . '" onclick="">' . $section['ID'] . '</a>]';
            return $viewField;
        }, $row['IBLOCK_SECTIONS']);

        $_viewField = '<div class="aspro-smartseo__grid__iblock-sections-value">'
            . implode('<br>', $_tmp)
            . '</div>';

        $_gridRow->AddViewField('IBLOCK_SECTIONS', $_viewField);
    }

    $_actions = [
        [
            'TEXT' => Loc::getMessage('SMARTSEO_AI__ACTION__EDIT'),
            'ACTION' => $row['TYPE'] === 'E'
                ? $adminUiList->ActionRedirect(Helper::url('filter_rule_detail/detail', [
                    'id' => $row['ID'], 'parent_section_id' => $row['SECTION_ID']
                  ]))
                : $adminUiList->ActionRedirect(Helper::url('filter_section/detail', [
                    'id' => $row['ID']
                  ])),
            'DEFAULT' => true,
        ],
        $row['TYPE'] === 'E' ? [
            'TEXT' => $row['ACTIVE'] == 'Y' ? Loc::getMessage('SMARTSEO_AI__ACTION__DEACTIVATE') : Loc::getMessage('SMARTSEO_AI__ACTION__ACTIVATE'),
            'ACTION' => $adminUiList->ActionDoGroup("$row[TYPE]$row[ID]", $row['ACTIVE'] == 'Y' ? 'deactivate' : 'activate', ''),
            'DEFAULT' => true,
        ] : [],
        $row['TYPE'] === 'E' ? [
          'TEXT' => $row['URL_CLOSE_INDEXING'] == 'Y' ? Loc::getMessage('SMARTSEO_AI__ACTION__DEACTIVATE_INDEXING') : Loc::getMessage('SMARTSEO_AI__ACTION__ACTIVATE_INDEXING'),
          'ACTION' => $adminUiList->ActionDoGroup("$row[TYPE]$row[ID]", $row['URL_CLOSE_INDEXING'] == 'Y' ? 'deactivate_indexing' : 'activate_indexing', ''),
          'DEFAULT' => true,
        ] : [],
        [
            'TEXT' => Loc::getMessage('SMARTSEO_AI__ACTION__DELETE'),
            'ACTION' =>
                'contextMenuHandler.delete(function(){' . $adminUiList->ActionDoGroup("$row[TYPE]$row[ID]", 'delete', '') . '})',
            'DEFAULT' => true,
        ]
    ];

    $_gridRow->AddActions($_actions);
}

$adminUiList->AddGroupActionTable([
    'activate' => Loc::getMessage('SMARTSEO_AI__ACTION__ACTIVATE'),
    'deactivate' => Loc::getMessage('SMARTSEO_AI__ACTION__DEACTIVATE'),
    'activate_indexing' => Loc::getMessage('SMARTSEO_AI__ACTION__ACTIVATE_INDEXING'),
    'deactivate_indexing' => Loc::getMessage('SMARTSEO_AI__ACTION__DEACTIVATE_INDEXING'),
    'delete' => '',
    'edit' => '',
]);

?>

<div class="aspro-smartseo__list__wrapper-filter">
  <? $adminUiList->DisplayFilter($filterFields); ?>
</div>

<div class="aspro-smartseo__list__wrapper-chain">
  <? include $this->getViewPath() . '_chain.php'; ?>
</div>

<div class="aspro-smartseo__list__wrapper-grid">
  <? $adminUiList->DisplayList() ?>
</div>

<script>
    var phpObjectFilterRules = <?= CUtil::PhpToJSObject([
          'messages' => [
              'popupWindow' => [
                  'BTN_DELETE' => Loc::getMessage('SMARTSEO_AI__BTN__DELETE'),
                  'BTN_CANCEL' => Loc::getMessage('SMARTSEO_AI__BTN__CANCEL'),
                  'MESSAGE_DELETE' => Loc::getMessage('SMARTSEO_AI__MESSAGE__DELETE'),
              ]
          ]
      ]);
      ?>

      BX.message({
          SMARTSEO_POPUP_BTN_DELETE: '<?= Loc::getMessage('SMARTSEO_AI__BTN__DELETE') ?>',
          SMARTSEO_POPUP_BTN_CANCEL: '<?= Loc::getMessage('SMARTSEO_AI__BTN__CANCEL') ?>',
          SMARTSEO_POPUP_MESSAGE_DELETE: '<?= Loc::getMessage('SMARTSEO_AI__MESSAGE__DELETE') ?>',
      });
</script>