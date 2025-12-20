<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "Партнерство");
$APPLICATION->SetPageProperty("title", "Партнерство с мебельной компанией E1.");
$APPLICATION->SetTitle("Стань партнером Е1");

$FORM_SID = "FRANCHISE";
$rsForm = \CForm::GetBySID($FORM_SID);
$arForm = $rsForm->Fetch();
?>

<div class="partnership">
    <div class="maxwidth-theme">
        <section class="partners-options">
            <div class="partners-options__item">
                <? $APPLICATION->IncludeComponent(
                    "bitrix:main.include",
                    "",
                    array(
                        "AREA_FILE_SHOW" => "file",
                        "PATH" => $APPLICATION->GetCurDir() . '/includes/wholesale.php'
                    )
                ); ?>
            </div>
            <div class="partners-options__item">
                <? $APPLICATION->IncludeComponent(
                    "bitrix:main.include",
                    "",
                    array(
                        "AREA_FILE_SHOW" => "file",
                        "PATH" => $APPLICATION->GetCurDir() . '/includes/franchise.php'
                    )
                ); ?>
            </div>
            <div class="partners-options__item">
                <? $APPLICATION->IncludeComponent(
                    "bitrix:main.include",
                    "",
                    array(
                        "AREA_FILE_SHOW" => "file",
                        "PATH" => $APPLICATION->GetCurDir() . '/includes/export.php'
                    )
                ); ?>
            </div>
            <div class="become-partner-btn">
                <a href="#form_partnership" class="btn btn-default has-ripple" style="scroll-behavior: smooth">Стать партнером</a>
            </div>
        </section>
    </div>
    <div class="maxwidth-theme">
        <section class="logistics">
            <div class="logistics__inner">
                <? $APPLICATION->IncludeComponent(
                    "bitrix:main.include",
                    "",
                    array(
                        "AREA_FILE_SHOW" => "file",
                        "PATH" => $APPLICATION->GetCurDir() . '/includes/network.php'
                    )
                ); ?>
            </div>
            <div class="logistics__regions">
                <? $APPLICATION->IncludeComponent(
                    "bitrix:main.include",
                    "",
                    array(
                        "AREA_FILE_SHOW" => "file",
                        "PATH" => $APPLICATION->GetCurDir() . '/includes/stores.php'
                    )
                ); ?>
            </div>
        </section>
    </div>
    <div class="maxwidth-theme">
        <section class="why-us">
            <? $APPLICATION->IncludeComponent(
                "bitrix:main.include",
                "",
                array(
                    "AREA_FILE_SHOW" => "file",
                    "PATH" => $APPLICATION->GetCurDir() . '/includes/whye1.php'
                )
            ); ?>
        </section>
    </div>
    <section class="steps-to-success">
        <? $APPLICATION->IncludeComponent(
            "bitrix:main.include",
            "",
            array(
                "AREA_FILE_SHOW" => "file",
                "PATH" => $APPLICATION->GetCurDir() . '/includes/fivesteps.php'
            )
        ); ?>
    </section>

    <section id="form_partnership" class="section__form maxwidth-theme">
        <h2 class="block-title">Обратная связь</h2>
        <div class="section__form-description">Оставьте заявку и получите консультацию нашего специалиста</div>
        <? $APPLICATION->IncludeComponent(
            "bitrix:form.result.new",
            "partnership",
            array(
                "CACHE_TIME" => "3600",
                "CACHE_TYPE" => "A",
                "CHAIN_ITEM_LINK" => "",
                "CHAIN_ITEM_TEXT" => "",
                "IGNORE_CUSTOM_TEMPLATE" => "N",
                "LIST_URL" => "",
                "EDIT_URL" => "",
                "SEF_MODE" => "N",
                "SUCCESS_URL" => "",
                "USE_EXTENDED_ERRORS" => "N",
                "WEB_FORM_ID" => $arForm['ID'],
                "COMPONENT_TEMPLATE" => "franchise",
                "SEF_FOLDER" => "",
                "VARIABLE_ALIASES" => array(
                    "WEB_FORM_ID" => "WEB_FORM_ID",
                    "RESULT_ID" => "RESULT_ID",
                ),
                "AJAX_MODE" => "Y",
                "AJAX_OPTION_SHADOW" => "N",
                "AJAX_OPTION_JUMP" => "N",
                "AJAX_OPTION_STYLE" => "Y",
                "AJAX_OPTION_HISTORY" => "N",
            ),
            false
        ); ?>
    </section>

    <script>
        // ГдеСлон
        window.gdeslon_q = window.gdeslon_q || [];
        window.gdeslon_q.push({
            page_type: "list",
            merchant_id: "100062",
            category_id: "franchise",
            <? if (!empty($GLOBALS['USER']->GetID())) : ?>user_id: "<?= $GLOBALS['USER']->GetID(); ?>"
        <? endif; ?>
        });
    </script>
    <script>
        //show all regions btn
        const openBtns = document.querySelectorAll('.js-show-all-btn');
        openBtns?.forEach(button => {
            button.addEventListener('click', () => {
                const target = button.getAttribute('data-target');
                const spoiler = document.querySelector(`[data-entity="${target}"]`);
                spoiler.classList.toggle('open');

                if (spoiler.classList.contains('open')) {
                    button.textContent = 'Скрыть';
                } else {
                    button.textContent = 'Полный список...';
                }
            });
        });
    </script>
</div>
<link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH ?>/css/partnership.css" />
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>