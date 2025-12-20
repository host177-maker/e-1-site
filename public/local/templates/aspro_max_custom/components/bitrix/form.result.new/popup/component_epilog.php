<?global $USER;?>
<?Bitrix\Main\Page\Frame::getInstance()->startDynamicWithID("form-block".$arParams["WEB_FORM_ID"]);?>
<?if($USER->IsAuthorized()):?>
	<?
	$dbRes = CUser::GetList(($by = "id"), ($order = "asc"), array("ID" => $USER->GetID()), array("FIELDS" => array("ID", "PERSONAL_PHONE")));
	$arUser = $dbRes->Fetch();
	?>
	<script type="text/javascript">
	$(document).ready(function() {
		try{
			$('.form.<?=$arResult["arForm"]["SID"]?> input[data-sid=CLIENT_NAME], .form.<?=$arResult["arForm"]["SID"]?> input[data-sid=FIO], .form.<?=$arResult["arForm"]["SID"]?> input[data-sid=NAME]').val('<?=$USER->GetFullName()?>');
			$('.form.<?=$arResult["arForm"]["SID"]?> input[data-sid=PHONE]').val('<?=$arUser['PERSONAL_PHONE']?>');
			$('.form.<?=$arResult["arForm"]["SID"]?> input[data-sid=EMAIL]').val('<?=$USER->GetEmail()?>');
		}
		catch(e){
		}
	});
	</script>
<?endif;?>
<script type="text/javascript">
$(document).ready(function() {
	$('.form.<?=$arResult["arForm"]["SID"]?> input[data-sid="PRODUCT_NAME"]').attr('value', $('h1').text());
	$('.form.<?=$arResult["arForm"]["SID"]?> input[data-sid="PAGE_URL"]').attr('value', window.location.href);
	$('body span[data-param-form_id="ASK"]').on('click', function (e) {
		setTimeout(() => {
			//$('body form[name="ASK"] textarea[data-sid="QUESTION"]').hide();
			//$('body form[name="ASK"] label[data-type="QUESTION"]').hide();
			$('body form[name="ASK"] textarea[data-sid="QUESTION"]').removeAttr('required');
		}, 2500);
	})
	//$('body form[name="ASK"] textarea[data-sid="QUESTION"]').hide();
	//$('body form[name="ASK"] label[data-type="QUESTION"]').hide();
	$('body form[name="ASK"] select[data-sid="TOPIC"]').attr('required', 'required')
	$('body form[name="ASK"] select[data-sid="TOPIC"]').on('change', function (e) {
		//заказать шкаф
		if (this.querySelector('option[value="'+this.value+'"]').dataset.param == 'TOPIC_ONE') {
			$('body form[name="ASK"] textarea[data-sid="QUESTION"]').removeAttr('required');
			//$('body form[name="ASK"] textarea[data-sid="QUESTION"]').hide();
			//$('body form[name="ASK"] label[data-type="QUESTION"]').hide();
		}//у меня вопрос
		else if (this.querySelector('option[value="'+this.value+'"]').dataset.param == 'TOPIC_TWO') {
			$('body form[name="ASK"] textarea[data-sid="QUESTION"]').attr('required', 'required')
			//$('body form[name="ASK"] textarea[data-sid="QUESTION"]').show();
			//$('body form[name="ASK"] label[data-type="QUESTION"]').show();
		} else {
			//$('body form[name="ASK"] textarea[data-sid="QUESTION"]').hide();
			//$('body form[name="ASK"] label[data-type="QUESTION"]').hide();
			$('body form[name="ASK"] textarea[data-sid="QUESTION"]').removeAttr('required');
			$('body form[name="ASK"] textarea[data-sid="QUESTION"]').removeAttr('required');
		}
    })
});
</script>
<?Bitrix\Main\Page\Frame::getInstance()->finishDynamicWithID("form-block".$arParams["WEB_FORM_ID"], "");?>