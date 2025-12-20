<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? $frame = $this->createFrame()->begin('') ?>
<?
$bLeftAndRight = false;
$fullQuestion = false;

if (is_array($arResult["QUESTIONS"])) {
    foreach ($arResult["QUESTIONS"] as $arQuestion) {
        if ($arQuestion["STRUCTURE"][0]["FIELD_PARAM"] == 'left') {
            $bLeftAndRight = true;
            break;
        }
    }
}
if (is_array($arResult["QUESTIONS"])) {
    foreach ($arResult["QUESTIONS"] as $arQuestion) {
        if ($arQuestion["STRUCTURE"][0]["FIELD_PARAM"] == 'full') {
            $fullQuestion = true;
            break;
        }
    }
}
?>
    <div class="contact-us">
        <div class="form inline contact-us-inner <?= $arResult["arForm"]["SID"] ?> ">
            <? if ($arResult["isFormErrors"] == "Y" || strlen($arResult["FORM_NOTE"])): ?>
                <div class="form_result <?= ($arResult["isFormErrors"] == "Y" ? 'error' : 'success') ?>">
                    <? if ($arResult["isFormErrors"] == "Y"): ?>
                        <?= $arResult["FORM_ERRORS_TEXT"] ?>
                    <? else: ?>
                        <? $successNoteFile = SITE_DIR . "include/form/success_{$arResult["arForm"]["SID"]}.php"; ?>
                        <? if (file_exists($_SERVER["DOCUMENT_ROOT"] . $successNoteFile)): ?>
                            <? $APPLICATION->IncludeFile($successNoteFile, array(), array("MODE" => "html", "NAME" => "Form success note")); ?>
                        <? else: ?>
                            <?= GetMessage("FORM_SUCCESS"); ?>
                        <? endif; ?>
                        <script>
                            if (arMaxOptions['THEME']['USE_FORMS_GOALS'] !== 'NONE') {
                                var eventdata = {goal: 'goal_webform_success' + (arMaxOptions['THEME']['USE_FORMS_GOALS'] === 'COMMON' ? '' : '_<?=$arResult["arForm"]["ID"]?>')};
                                BX.onCustomEvent('onCounterGoals', [eventdata]);
                            }
                        </script>
                    <? //START Передаем цели в GA
                    switch ($arResult["arForm"]["ID"]) {
                        case '14':
                            $arrGaEvents = array('eventCategory' => 'target', 'eventAction' => 'rassrochka');
                            break;
                    }
                    ?>


                    <? //START Передаем цели в CoMagic
                    switch ($arResult["arForm"]["ID"]) {
                        case '14':
                            $arrCMEvents = array('eventCategory' => 'target', 'eventAction' => 'rassrochka');
                            break;
                    }
                    ?>
                    <? if ($arrCMEvents): ?>
                        <script>
                            //console.log('<?=$arrCMEvents["eventCategory"]?>:<?=$arrCMEvents["eventAction"]?>');
                            //Comagic.trackEvent('<?=$arrCMEvents["eventCategory"]?>', '<?=$arrCMEvents["eventAction"]?>');
                        </script>
                    <? endif; ?>
                    <?
                    $nubmerOrder = (!empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_SYSTEM_NUMBER_ZVONOK', 'N'))) ? (int)\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_SYSTEM_NUMBER_ZVONOK', 'N') : '';
                    $nubmerOrder++;
                    \Bitrix\Main\Config\Option::set('e1.site.settings', 'E1_SS_SYSTEM_NUMBER_ZVONOK', $nubmerOrder);
                    $arAnswer = CFormResult::GetDataByID($_REQUEST['RESULT_ID'], array(), $arResult2, $arAnswer2);
                    ?>
                        <script>
                            // ГдеСлон
                            window.gdeslon_q = window.gdeslon_q || [];
                            window.gdeslon_q.push({
                                page_type: "thanks",
                                merchant_id: "100062",
                                order_id: '<?=$arAnswer['PHONE'][0]['USER_TEXT'] . '_' . time()?>',
                                category_id: '<?=$arResult["arForm"]["ID"]?>',
                                <?if(!empty($GLOBALS['USER']->GetID())):?>user_id: "<?=$GLOBALS['USER']->GetID();?>"<?endif;?>
                            });
                            console.log('<?=$arAnswer['PHONE'][0]['USER_TEXT'] . '_' . time()?>', 'ГдеСлон popup');//служит для проверки для мобилки, компонент form.result inline
                        </script>
                    <? //END Передаем цели в CM?>
                    <? if ($arrGaEvents): ?>
                        <script>
                            console.log('<?=$arrGaEvents["eventAction"]?>');
                            dataLayer.push({
                                'event': 'eventTarget',
                                'eventCategory': '<?=$arrGaEvents["eventCategory"]?>',
                                'eventAction': '<?=$arrGaEvents["eventAction"]?>',
                                'eventLabel': '',
                            });
                        </script>
                    <? endif; ?>
                        <? //END Передаем цели в GA?>
                    <? endif; ?>
                </div>

            <?else:?>
            <h5>
                <p>Зафиксируйте персональную цену на 14 дней</p>
            </h5>
            <? if ($arParams['HIDE_SUCCESS'] != 'Y' || ($arParams['HIDE_SUCCESS'] == 'Y' && !strlen($arResult["FORM_NOTE"]))): ?>
                <?= $arResult["FORM_HEADER"] ?>
                <?= bitrix_sessid_post(); ?>
                <? if (is_array($arResult["QUESTIONS"])): ?>
                <? foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion): ?>
                    <?if($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden'):?>
                        <? CMax::drawFormField($FIELD_SID, $arQuestion); ?>
                    <?endif;?>
                <?endforeach;?>
                <div class="form-row">
                    <? foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion): ?>
                        <?if($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] == 'hidden') continue;?>
                        <label>
                            <? CMax::drawFormField($FIELD_SID, $arQuestion); ?>
                        </label>
                    <? endforeach; ?>
                    <button type="submit" >Оставить заявку</button>
                    <input type="hidden" class="btn btn-default" value="<?= $arResult["arForm"]["BUTTON"] ?>"
                           name="web_form_submit">
                </div>
            <? endif; ?>
                <? $bHiddenCaptcha = (isset($arParams["HIDDEN_CAPTCHA"]) ? $arParams["HIDDEN_CAPTCHA"] : COption::GetOptionString("aspro.max", "HIDDEN_CAPTCHA", "Y")); ?>
                <? if ($arResult["isUseCaptcha"] == "Y"): ?>
                <div class="form-control captcha-row clearfix">
                    <label><span><?= GetMessage("FORM_CAPRCHE_TITLE") ?>&nbsp;<span class="star">*</span></span></label>
                    <div class="captcha_image">
                        <img src="/bitrix/tools/captcha.php?captcha_sid=<?= htmlspecialcharsbx($arResult["CAPTCHACode"]) ?>"
                             border="0"/>
                        <input type="hidden" name="captcha_sid"
                               value="<?= htmlspecialcharsbx($arResult["CAPTCHACode"]) ?>"/>
                        <div class="captcha_reload"></div>
                    </div>
                    <div class="captcha_input">
                        <input type="text" class="inputtext captcha" name="captcha_word" size="30" maxlength="50"
                               value="" required/>
                    </div>
                </div>
            <? elseif ($bHiddenCaptcha == "Y"): ?>
                <textarea name="nspm" style="display:none;"></textarea>
            <? endif; ?>
            <? $bShowLicenses = (isset($arParams["SHOW_LICENCE"]) ? $arParams["SHOW_LICENCE"] : COption::GetOptionString("aspro.max", "SHOW_LICENCE", "Y")); ?>
            <? if ($bShowLicenses == "Y"): ?>
                <label class="check">
                    <input type="checkbox"
                           id="licenses_inline" <?= (COption::GetOptionString("aspro.max", "LICENCE_CHECKED", "N") == "Y" ? "checked" : ""); ?>
                           name="licenses_inline" required value="Y">
                    <? $APPLICATION->IncludeFile(SITE_DIR . "include/licenses_text.php", array(), array("MODE" => "html", "NAME" => "LICENSES")); ?>
                </label>
            <? endif; ?>
                <div class="form_footer">


                    <script type="text/javascript">
                        $(document).ready(function () {
                            $('form[name="<?=$arResult["arForm"]["VARNAME"]?>"]').validate({
                                highlight: function (element) {
                                    $(element).parent().addClass('error');
                                },
                                unhighlight: function (element) {
                                    $(element).parent().removeClass('error');
                                },
                                submitHandler: function (form) {
                                    if( $('form[name="<?=$arResult["arForm"]["VARNAME"]?>"]').valid() ){
                                        setTimeout(function() {
                                            $(form).find('button[type="submit"]').attr("disabled", "disabled");
                                        }, 500);
                                        var eventdata = {type: 'form_submit', form: form, form_name: '<?=$arResult["arForm"]["VARNAME"]?>'};
                                        BX.onCustomEvent('onSubmitForm', [eventdata]);
                                    }
                                },
                                errorPlacement: function (error, element) {
                                    error.insertBefore(element);
                                },
                                messages: {
                                    licenses_inline: {
                                        required: BX.message('JS_REQUIRED_LICENSES')
                                    }
                                }
                            });

                            if (arMaxOptions['THEME']['PHONE_MASK'].length) {
                                var base_mask = arMaxOptions['THEME']['PHONE_MASK'].replace(/(\d)/g, '_');
                                $('form[name=<?=$arResult["arForm"]["VARNAME"]?>] input.phone, form[name=<?=$arResult["arForm"]["VARNAME"]?>] input[data-sid=PHONE]').inputmask('mask', {'mask': arMaxOptions['THEME']['PHONE_MASK']});
                                $('form[name=<?=$arResult["arForm"]["VARNAME"]?>] input.phone, form[name=<?=$arResult["arForm"]["VARNAME"]?>] input[data-sid=PHONE]').blur(function () {
                                    if ($(this).val() == base_mask || $(this).val() == '') {
                                        if ($(this).hasClass('required')) {
                                            $(this).parent().find('label.error').html(BX.message('JS_REQUIRED'));
                                        }
                                    }
                                });
                            }
                        });
                    </script>
                </div>
            <?= $arResult["FORM_FOOTER"] ?>
            <? else: ?>
                <script type="text/javascript">
                    $(document).ready(function () {
                        $('body, html').animate({scrollTop: 0}, 500);
                    });
                </script>
            <? endif; ?>
            <!--/noindex-->
    <?endif;?>
        </div>
    </div>
<? $frame->end() ?>