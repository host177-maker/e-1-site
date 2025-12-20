<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/search/prolog.php");

$module_id = 'abr.search';
global $USER, $APPLICATION;
Loader::includeModule($module_id);
Loader::includeModule('iblock');
Loader::includeModule('catalog');
Loader::includeModule('sale');

if (!$USER->IsAdmin()) {
    $APPLICATION->authForm('Nope');
}

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/options.php");

Loc::loadMessages(__FILE__);
$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
$defaultSettings = \Bitrix\Main\Config\Option::getDefaults($module_id);

$arAllOptions = [
    Loc::getMessage('abr.search_COMMON'),
    [
        "USE_ANMARTO",
        Loc::getMessage('abr.search_USE_MODULE'),
        false,
        [
            'checkbox',
            false
        ]
    ],
    Loc::getMessage('abr.search_CONNECTION'),

];
const DEFAULT_HOST = 'http://185.103.132.28:81/api';

if (empty(Option::get($module_id, 'API_HOST'))) {
    Option::set($module_id, 'API_HOST', DEFAULT_HOST);
}

$v2Options = [
//    [
//        "API_HOST",
//        Loc::getMessage('abr.search_HOST'),
//        'http://185.103.132.28:81/api/',
//        [
//            'text',
//            30
//        ]
//    ],
    [
        "TOKEN",
        Loc::getMessage('abr.search_TOKEN'),
        '',
        [
            'text',
            55
        ]
    ],
    [
        "INDEX_NAME",
        Loc::getMessage('ABR_INDEX_NAME'),
        'b_search_content_text',
        [
            'text',
            30
        ]
    ],
];


$arAllOptions = array_merge($arAllOptions, $v2Options);

$aTabs = [
    [
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
        "OPTIONS" => $arAllOptions,
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
    ],
    [
        "DIV" => "edit2",
        "TAB" => Loc::getMessage("MAIN_TAB_RIGHTS"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_RIGHTS"),
    ],
];

$tabControl = new \CAdminTabControl("tabControl", $aTabs);

if ($request->isPost() && $request['Update'] && check_bitrix_sessid()) {

    foreach ($aTabs as $aTab) {
        foreach ($aTab['OPTIONS'] as $arOption) {

            if (!is_array($arOption))
                continue;

            if ($arOption['note'])
                continue;

            $optionName = $arOption[0];

            $optionValue = $request->getPost($optionName);

            Option::set($module_id, $optionName, is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
            if ($optionName == 'EXCHANGE_TYPE' && $optionValue == 'SITE') {
                $now = new Bitrix\Main\Type\Datetime();
                $now->add('+5 min');
                $formattedTime = $now->toString(new \Bitrix\Main\Context\Culture(array("FORMAT_DATETIME" => "d.m.Y H:i:s")));
            }
        }
    }
    LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . htmlspecialcharsbx($request['mid']) . '&amp;lang=' . $request['lang']);
}
$tabControl->Begin(); ?>
<div class="adm-info-message-wrap" style="position: relative;">
    <div class="adm-info-message" style="margin: 0 auto; display: block;">
        <?php echo Loc::getMessage("abr.search_ATTENTION"); ?>
    </div>
</div>
<form method='post'
      action='<?= $APPLICATION->GetCurPage(); ?>?mid=<?= htmlspecialcharsbx($request['mid']) ?>&amp;lang=<?= $request['lang'] ?>'
      name='absteam_elastic_settings'>

    <?php foreach ($aTabs as $aTab):
        if ($aTab['OPTIONS']): ?>
            <?php $tabControl->BeginNextTab(); ?>
            <?php __AdmSettingsDrawList($module_id, $aTab['OPTIONS']); ?>

        <?php endif;
    endforeach; ?>
    <td>
        <input type="submit" name="Update" value="<?php echo GetMessage('MAIN_SAVE') ?>">
        <input type="reset" name="Reset" value="<?php echo GetMessage('MAIN_RESET') ?>">
        <?= bitrix_sessid_post(); ?>
    </td>
    <?
    $tabControl->BeginNextTab(); ?>
    <?php

    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights.php");
    ?>
    <td>
        <input type="submit" name="Update" value="<?php echo GetMessage('MAIN_SAVE') ?>">
        <input type="reset" name="Reset" value="<?php echo GetMessage('MAIN_RESET') ?>">
        <?= bitrix_sessid_post(); ?>
    </td>

    <?php
    $tabControl->EndTab(); ?>
</form>

<?php $tabControl->End(); ?>

<script>

    /**
     * Отрпавляет поисковый запрос к БД и обновляет таблицу
     */
    function doSearch() {
        const query = new URLSearchParams({
            q: $('#search-input').val(),
            sid: $('#SID').val(),
            module_id: $('#module').val(),
            iblock: $('#iblocks').val()
        });

        const searchTable = $('#search-table').DataTable({
            destroy: true, // пересоздавать объект при обновлении
            ajax: "/bitrix/admin/<?= $module_id ?>_ajax_search.php?" + query.toString(),
            info: true,
            paging: true,
            searching: true
        });
    }

    /**
     * Вкл/выкл выпадающего списка типов ИБ, при выборе модуля ИБ
     */
    function onSelectModule(obj) {
        if ($(obj).val() === 'iblock') {
            $('#iblocks').prop('disabled', false);
            return
        }
        $('#iblocks').prop('disabled', true);
    }


    var dTable;

    function uploadFile() {
        var input = document.querySelector('input[type="file"][name="replaces"]');
        var formData = new FormData();
        var file = input.files[0];
        formData.append('replaces', file);


        $.ajax({
            url: '/bitrix/admin/abr.search_ajax_replaces.php?type=upload',
            type: 'POST',
            data: formData,
            processData: false,  // tell jQuery not to process the data
            contentType: false,  // tell jQuery not to set contentType
            success: function (data) {
                // console.log(data);
                alert(data);
                try {
                    dTable.ajax.reload();
                } catch (e) {

                }
            }
        });

        // $.post('/bitrix/admin/abr.search_ajax_replaces.php?type=upload', formData, function (){
        //
        // });
        return false;
    }

    $(document).ready(function () {
        dTable = $('#replace-table').DataTable({
            ajax: "/bitrix/admin/abr.search_ajax_replaces.php"
        });

        var dialog, form,

            // From http://www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#e-mail-state-%28type=email%29
            emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
            replace = $("#replace"),
            word = $("#word"),
            allFields = $([]).add(replace).add(word),
            tips = $(".validateTips");

        function updateTips(t) {
            tips
                .text(t)
                .addClass("ui-state-highlight");
            setTimeout(function () {
                tips.removeClass("ui-state-highlight", 1500);
            }, 500);
        }

        function checkLength(o, n, min, max) {
            if (o.val().length > max || o.val().length < min) {
                o.addClass("ui-state-error");
                updateTips("Length of " + n + " must be between " +
                    min + " and " + max + ".");
                return false;
            } else {
                return true;
            }
        }

        function checkRegexp(o, regexp, n) {
            if (!(regexp.test(o.val()))) {
                o.addClass("ui-state-error");
                updateTips(n);
                return false;
            } else {
                return true;
            }
        }

        function addUser() {
            var valid = true;
            allFields.removeClass("ui-state-error");

            valid = valid && checkLength(word, "word", 2, 999);
            valid = valid && checkLength(replace, "replace", 2, 9999);

            console.log(word.val(), replace.val(), valid)

            if (valid) {
                $.get('/bitrix/admin/abr.search_ajax_replaces.php?type=add&word=' + word.val() + '&replace=' + replace.val(), function () {
                    dialog.dialog("close");
                    setTimeout(function () {
                        dTable.ajax.reload(null, false);
                    }, 500);
                    form.trigger('reset');
                });
            }
            return valid;
        }

        dialog = $("#dialog-form").dialog({
            autoOpen: false,
            height: 400,
            width: 350,
            modal: true,
            buttons: {
                "<?php echo Loc::getMessage("abr.search_REPLACES_SAVE"); ?>": addUser,
                "<?php echo Loc::getMessage("abr.search_REPLACES_CANCEL"); ?>": function () {
                    dialog.dialog("close");
                }
            },
            close: function () {
                form.trigger('reset');
                allFields.removeClass("ui-state-error");
            }
        });

        form = dialog.find("form").on("submit", function (event) {
            event.preventDefault();
            addUser();
        });

        $("#add-replace").button().on("click", function () {
            dialog.dialog("open");

            return false;
        });
        $("#add-replace-file").button().on("click", function () {
            dialog.dialog("open");

            return false;
        });
        $('body').on('click', '.btn-del', function () {
            if (confirm('<?php echo Loc::getMessage("abr.search_REPLACES_CONFIRM"); ?>')) {
                var id = $(this).data('id');
                $.get('/bitrix/admin/abr.search_ajax_replaces.php?type=del&id=' + id, function () {
                    setTimeout(function () {
                        dTable.ajax.reload(null, false);
                    }, 1500);
                });
            }
        })
    });
</script>
<style>
	.btn-action {
		text-decoration: none;
	}

	label,
	input {
		display: block;
	}

	input.text {
		margin-bottom: 12px;
		width: 95%;
		padding: .4em;
	}

	fieldset {
		padding: 0;
		border: 0;
		margin-top: 25px;
	}

	h1 {
		font-size: 1.2em;
		margin: .6em 0;
	}

	div#users-contain {
		width: 350px;
		margin: 20px 0;
	}

	div#users-contain table {
		margin: 1em 0;
		border-collapse: collapse;
		width: 100%;
	}

	div#users-contain table td,
	div#users-contain table th {
		border: 1px solid #eee;
		padding: .6em 10px;
		text-align: left;
	}

	.ui-dialog .ui-state-error {
		padding: .3em;
	}

	.validateTips {
		border: 1px solid transparent;
		padding: 0.3em;
	}

	#search-form-container select {
		margin-right: 20px;
	}

	#search-form-container label {
		padding-top: 4px;
		margin-right: 3px;
	}

	#search-form-container input {
		height: 25px;
		margin-top: 1px;
		margin-right: 3px;
	}

	#search-form {
		padding-top: 10px;
	}

	#module-option,
	#search-form {
		display: flex;
	}

	#search-table {
		width: 100%;
		margin-top: 10px;
	}

	.dataTables_wrapper {
		margin-top: 10px;
	}
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">