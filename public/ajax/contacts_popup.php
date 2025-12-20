<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;

use Cosmos\Config;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");


$arSelect = array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_*"); //IBLOCK_ID и ID обязательно должны быть указаны, см. описание arSelectFields выше
$arFilter = array("IBLOCK_ID" => Config::getInstance()->getIblockIdByCode("partnership"), "ACTIVE" => "Y");
$res      = CIBlockElement::GetList(array(), $arFilter, false, $arSelect);
while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetProperties();
}
$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();
if ($request->isPost()) {
    $sendSUPPLIERSform = $request->getPostList()->toArray();
    if (empty($sendSUPPLIERSform) || empty($sendSUPPLIERSform['CLIENT_NAME']) || empty($sendSUPPLIERSform['PHONE']) || empty($sendSUPPLIERSform['DEPARTMENT']) || empty($sendSUPPLIERSform['AGREE']) || empty($sendSUPPLIERSform['TEXT'])) {
        echo json_encode(['status' => 'error']);
    } else {
        if (in_array($sendSUPPLIERSform['DEPARTMENT'], array_column($arFields, 'VALUE')) && $sendSUPPLIERSform['AGREE'] === "on") {
            $sendBuyform = CEvent::Send(
                "FEEDBACK_FORM",
                SITE_ID,
                [
                    'AUTHOR'     => $sendSUPPLIERSform['CLIENT_NAME'],
                    'PHONE'      => $sendSUPPLIERSform['PHONE'],
                    'EMAIL_TO'   => $sendSUPPLIERSform['DEPARTMENT'],
                    'DEPARTMENT' => $sendSUPPLIERSform['DEPARTMENT'],
                    'TEXT'       => $sendSUPPLIERSform['TEXT'],
                ],
                "N"
            );
            if ($sendBuyform) {
                echo json_encode(['status' => 'ok']);
            } else {
                echo json_encode(['status' => 'Email sending error']);
            }
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
    exit;
}
?>

<div class="popup_cooperation">

    <svg class="close jqmClose" width="24" height="24" viewBox="0 0 24 24" fill="none"
        xmlns="http://www.w3.org/2000/svg">
        <path d="M5 5L18.5 18.5" stroke="#B8B8B8" stroke-width="2" />
        <path d="M18.5 5L5 18.5" stroke="#B8B8B8" stroke-width="2" />
    </svg>
    <div class="popup_cooperation-title">Поставщикам</div>
    <div class="popup_cooperation-container" id="html-container">
        <form name="SUPPLIERS" action="/ajax/contacts_popup.php" method="POST">

            <div class="form_body">
                <div class="form-control">
                    <label for="CLIENT_NAME"><span>Ваше имя&nbsp;<span class="star">*</span></span></label>
                    <input type="text" class="form-control inputtext" name="CLIENT_NAME" required>

                    <label for="PHONE"><span>Телефон&nbsp;<span class="star">*</span></span></label>
                    <input type="tel" class="form-control phone" id="partner_phone" name="PHONE" required>

                    <label for="DEPARTMENT"><span>Отдел&nbsp;<span class="star">*</span></span></label>
                    <select class="form-control" noselect="" name="DEPARTMENT" id="DEPARTMENT" required>
                        <option value="">Выбрать отдел...</option>
                        <option value="<?= $arFields['ADVERTISEMENT']['VALUE'] ?>">Реклама</option>
                        <option value="<?= $arFields['REAL_ESTATE_RENT']['VALUE'] ?>">Аренда недвижимости</option>
                        <option value="<?= $arFields['PURCHASES']['VALUE'] ?>">Закупки</option>
                        <option value="<?= $arFields['LOGISTICS']['VALUE'] ?>">Логистика</option>
                        <option value="<?= $arFields['WHOLESALE']['VALUE'] ?>">Опт</option>
                    </select>
                </div>
                <div class="form-control">
                    <label data-type="TEXT"><span>Сообщение&nbsp;<span class="star">*</span></span></label>
                    <textarea required name="TEXT" cols="40" rows="10" aria-required="true"></textarea>
                </div>
            </div>
            <div class="form_footer">
                <div class="licence_block filter onoff label_block">
                    <input class="form-control" type="checkbox" name="AGREE" required checked aria-checked="true">
                    <label for="AGREE">
                        Я согласен на <a href="/include/licenses_detail.php" target="_blank">обработку персональных
                            данных</a> </label>
                </div>
                <button type="submit" class="btn btn-lg btn-default"><span>Отправить</span></button>
            </div>

        </form>
    </div>
</div>


<script>
    const mask = arAsproOptions['THEME']['PHONE_MASK'];
    $('#partner_phone').inputmask('mask', {
        mask: mask
    });

    $(document).ready(() => {
        $('form[name="SUPPLIERS"]').on('submit', (event) => {
            event.preventDefault();
            const data = new FormData(event.target);
            fetch($(event.target).attr('action'), {
                body: data,
                method: 'POST'
            }).then(function(response) {
                return response.json();
            }).then(function(response) {
                if (response.status === 'ok') {
                    $('#html-container').html('<span class="success">Спасибо, мы вам обязательно перезвоним в самое ближайшее время!</span>');
                    ecommerceOnFormSubmit('for_suppliers');
                } else {
                    $('#html-container').append('<span class="alert">Упс, что-то пошло не так. Попробуйте еще раз.</span>');
                    console.error(response);
                }
            });
        });
    });
</script>