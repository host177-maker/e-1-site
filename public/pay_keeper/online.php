<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("robots", "noindex, nofollow");

if ($request->isPost()) {
  $arForm = $request->getPostList()->toArray();
  $payment_parameters = http_build_query(array( 
    "clientid" => $arForm['name'] . $arForm['patronymic'] . $arForm['surname'],
    "orderid" => $arForm['number_zakaz'],
    "sum" => "1000",
    "client_phone" => $arForm['phone']
  ));
  $options = array(
    "http" => array(
      "method" => "POST",
      "header" =>
      "Content-type: application/x-www-form-urlencoded",
      "content" => $payment_parameters
    )
  );
  $context = stream_context_create($options);

  echo file_get_contents("https://2e-1.server.paykeeper.ru/form/inline/", FALSE, $context);
} else { ?>


  <div class="form ASK  ">
    <!--noindex-->
    <div class="form_head">
      <h2>Заполните ваши данные</h2>
    </div>

    <form name="PAY_KEEPER" action="<?= $APPLICATION->GetCurPage(false) ?>" method="POST" enctype="multipart/form-data" novalidate="novalidate">
      <input type="hidden" name="WEB_FORM_ID" value="1">
      <div class="form_body">
        <?/*<div class="form-control">
          <label><span>Имя&nbsp;<span class="star">*</span></span></label>
          <input type="text" class="inputtext" data-sid="CLIENT_NAME" required="" name="name" value="" aria-required="true">
        </div>
        <div class="form-control">
          <label><span>Фамилия&nbsp;<span class="star">*</span></span></label>
          <input type="text" class="inputtext" data-sid="CLIENT_F" required="" name="surname" value="" aria-required="true">
        </div>
        <div class="form-control">
          <label><span>Отчество&nbsp;<span class="star">*</span></span></label>
          <input type="text" class="inputtext" data-sid="CLIENT_O" required="" name="patronymic" value="" aria-required="true">
        </div>
        <div class="form-control">
          <label><span>Номер заказа&nbsp;<span class="star">*</span></span></label>
          <input type="text" class="inputtext" data-sid="number_zakaz" name="number_zakaz" required="" aria-required="true" value="<?= (!empty($_GET['id']) ? htmlspecialcharsEx($_GET['id']) : '')?>">
        </div>*/?>
        <div class="form-control">
          <label><span>Телефон&nbsp;<span class="star">*</span></span></label>
          <input type="tel" class="phone" data-sid="PHONE" required="" name="phone" value="" aria-required="true">
        </div>
        <input type="hidden" data-sid="PRODUCT_NAME" name="form_hidden_5" value="">
        <input type="hidden" data-sid="PAGE_URL" name="form_hidden_100" value="<?= $APPLICATION->GetCurPage(false) ?>">
        <div class="clearboth"></div>
      </div>
      <button type="submit" class="btn btn-lg btn-default"><span>Отправить</span></button>
      <input type="hidden" class="btn btn-default" value="Отправить" name="web_form_submit">
  </div>
</form>
<!--/noindex-->
  <script>
    $(document).ready(function() {

      $('form[name="PAY_KEEPER"]').validate({
        highlight: function(element) {
          $(element).parent().addClass('error');
        },
        unhighlight: function(element) {
          $(element).parent().removeClass('error');
        },
        submitHandler: function(form) {
          if ($('form[name="PAY_KEEPER"]').valid()) {
            setTimeout(function() {
              $(form).find('button[type="submit"]').attr("disabled", "disabled");
            }, 500);
            var eventdata = {
              type: 'form_submit',
              form: form,
              form_name: 'PAY_KEEPER'
            };
            BX.onCustomEvent('onSubmitForm', [eventdata]);
          }
        },
        errorPlacement: function(error, element) {
          error.insertBefore(element);
        },
        messages: {
          licenses_popup: {
            required: BX.message('JS_REQUIRED_LICENSES')
          }
        }
      });
    });
  </script>
</div>
<?
}
?>
<div class="page-top">
  <div>
    <img alt="pay system" src="/images/HorizontalLogos.png" />
  </div>
</div>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>