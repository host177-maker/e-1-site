<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
use \Bitrix\Main\Context;
$request = Context::getCurrent()->getRequest();
$arPostValues = $request->getPostList()->toArray();
?>
<a href="#" class="close jqmClose"><?=CMax::showIconSvg('', SITE_TEMPLATE_PATH.'/images/svg/Close.svg')?></a>
<div class="form <?=$arResult["arForm"]["SID"]?>  ">
	<!--noindex-->
	<div class="form_head">
		<?if($arResult["isFormTitle"] == "Y"):?>
			<h2><?=$arResult["FORM_TITLE"]?></h2>
		<?endif;?>
		<?if($arResult["isFormDescription"] == "Y"):?>
			<div class="form_desc"><?=$arResult["FORM_DESCRIPTION"]?></div>
		<?endif;?>
	</div>
	<?if(strlen($arResult["FORM_NOTE"])){?>
		<div class="form_result <?=($arResult["isFormErrors"] == "Y" ? 'error' : 'success')?>">
			<?if($arResult["isFormErrors"] == "Y"):?>
				<?=$arResult["FORM_ERRORS_TEXT"]?>
			<?else:?>
				<?=CMax::showIconSvg(' colored', SITE_TEMPLATE_PATH.'/images/svg/success.svg')?>
				<span class="success_text" style="display: inline-block">
					<?$successNoteFile = SITE_DIR."include/form/success_{$arResult["arForm"]["SID"]}.php";?>
					<?if(file_exists($_SERVER["DOCUMENT_ROOT"].$successNoteFile)):?>
					<?$APPLICATION->IncludeFile($successNoteFile, array(), array("MODE" => "html", "NAME" => "Form success note"));?>
					<?else:?>
						<?=GetMessage("FORM_SUCCESS");?>
					<?endif;?>
				</span>
				<script>
					if(arMaxOptions['THEME']['USE_FORMS_GOALS'] !== 'NONE')
					{
						var eventdata = {goal: 'goal_webform_success' + (arMaxOptions['THEME']['USE_FORMS_GOALS'] === 'COMMON' ? '' : '_<?=$arResult["arForm"]["ID"]?>')};
						BX.onCustomEvent('onCounterGoals', [eventdata]);
					}
					$(window).scroll();
				</script>
				<?php
				// Функция для получения значения GA eventAction по ID формы
				function getGaEventAction(int $formId): ?string
				{
					return match ($formId) {
						1 => '21_1_c_question_on_footer',
						2 => 'ASK_STAFF',
						3 => 'callback_head',
						4 => 'CHEAPER',
						5 => 'FEEDBACK',
						6 => 'PROJECTS',
						7 => 'RESUME',
						8 => 'REVIEW',
						9 => 'TOORDER',
						10 => 'SERVICES',
						11 => 'SEND_GIFT',
						12 => '39_1_c_my_size',
						13 => 'GIVE_FEEDBACK',
						14 => 'rassrochka',
						15 => 'calculate_built_in',
						16 => 'The_client_knows_which_cabinet_he_needs',
						17 => 'ordered_a_design_project',
						18 => 'I_want_to_order_a_wardrobe',
						19 => 'calculate_built_in',
						20 => 'lead_int',
						21 => 'The_client_knows_which_cabinet_he_needs',
						22 => 'I_want_to_order_a_wardrobe',
						23 => 'NO_MY_CITY',
						24 => 'FEEDBACK_VACANCY',
						25 => 'FRANCHISE',
						26 => 'WARDROBE_ORDER_NEW',
						27 => 'CALLBACK_FREE',
						28 => 'ORDER_CUSTOM_CABINET',
						29 => 'WANT_SLIDING_SYSTEM',
						30 => 'SELECTION_WARDROBE',
						default => null,
					};
				}

				// Получаем eventAction по ID формы
				$eventAction = getGaEventAction((int)$arResult["arForm"]["ID"]);

				// Формируем массив для GA, если eventAction найден
				$arrGaEvents = $eventAction ? [
					'event' => 'eventTarget',
					'eventCategory' => 'target',
					'eventAction' => $eventAction,
				] : [];
				?>
				

				<?//START Передаем цели в CoMagic
				switch ($arResult["arForm"]["ID"]) {
					case '17':
					case '20':
						$arrCMEvents = array('eventCategory'=> 'target', 'eventAction' => 'ordered_a_design_project');
						break;
					case '16':
					case '21':
						$arrCMEvents = array('eventCategory'=> 'target', 'eventAction' => 'The_client_knows_which_cabinet_he_needs');
						break;
					case '18':
					case '22':
						$arrCMEvents = array('eventCategory'=> 'target', 'eventAction' => 'I_want_to_order_a_wardrobe');
						break;
					case '15':
					case '19':
						$arrCMEvents = array('eventCategory'=> 'target', 'eventAction' => 'calculate_built_in');
						break;
					case '3':
						$arrCMEvents = array('eventCategory'=> 'target', 'eventAction' => 'callback_head');
						break;
					case '12':
						$arrCMEvents = array('eventCategory'=> 'target', 'eventAction' => '39_1_c_my_size');
						break;
					case '1':
						$arrCMEvents = array('eventCategory'=> 'target', 'eventAction' => '21_1_c_question_on_footer');
						break;
				}
				?>
				<?if($arrCMEvents):?>
					<script>
						//console.log('<?=$arrCMEvents["eventCategory"]?>:<?=$arrCMEvents["eventAction"]?>');
						//Comagic.trackEvent('<?=$arrCMEvents["eventCategory"]?>', '<?=$arrCMEvents["eventAction"]?>');
						</script>
				<?endif;?>
				<script>
                <?
                $nubmerOrder =  (!empty(\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_SYSTEM_NUMBER_ZVONOK'))) ? (int)\Bitrix\Main\Config\Option::get('e1.site.settings', 'E1_SS_SYSTEM_NUMBER_ZVONOK') : '';
                $nubmerOrder++;
                \Bitrix\Main\Config\Option::set('e1.site.settings', 'E1_SS_SYSTEM_NUMBER_ZVONOK', $nubmerOrder);
                $arAnswer = CFormResult::GetDataByID($_REQUEST['RESULT_ID'], array(), $arResult2, $arAnswer2);
                ?>
				// ГдеСлон
				window.gdeslon_q = window.gdeslon_q || [];
				window.gdeslon_q.push({
					page_type:  "thanks",
					merchant_id: "100062",
					order_id: '<?=$arAnswer['PHONE'][0]['USER_TEXT'].'_'.time()?>',
					category_id: '<?=$arResult["arForm"]["ID"]?>',
					<?if(!empty($GLOBALS['USER']->GetID())):?>user_id: "<?=$GLOBALS['USER']->GetID();?>"<?endif;?>
				});
				console.log('<?=$arAnswer['PHONE'][0]['USER_TEXT'].'_'.time()?>','ГдеСлон popup');//служит для проверки
				</script>
				<?//END Передаем цели в CM?>

				<?/*$arComagicSendData = COrwoFunctions::GetComagicSendData($arResult, $_REQUEST, 'webform');?>
				<?if(!empty($arComagicSendData)):?>
					<script>
						var comagicSendData = <?=CUtil::PhpToJSObject($arComagicSendData);?>;
						Comagic.addOfflineRequest(comagicSendData);
						console.log('comagicSendData');
						console.log(comagicSendData);
					</script>
				<?endif;*/?>
				<?if($arrGaEvents):?>
					<script>
						console.log('<?=$arrGaEvents["eventAction"]?>');
						dataLayer.push({
							'event': '<?=$arrGaEvents["event"]?>',
							'eventCategory': '<?=$arrGaEvents["eventCategory"]?>',
							'eventAction': '<?=$arrGaEvents["eventAction"]?>',
							'eventLabel': '',
						});
					</script>
				<?endif;?>
				<?//END Передаем цели в GA?>
				<div class="close-btn-wrapper">
					<div class="btn btn-default btn-lg jqmClose"><?=GetMessage('FORM_CLOSE')?></div>
				</div>
			<?endif;?>
		</div>
	<?}else{?>
		<?if($arResult["isFormErrors"] == "Y"):?>
			<div class="form_body error"><?=$arResult["FORM_ERRORS_TEXT"]?></div>
		<?endif;?>
		<?=$arResult["FORM_HEADER"]?>
		<?=bitrix_sessid_post();?>
		<div class="form_body">
			<?if(is_array($arResult["QUESTIONS"])):?>
				<?foreach($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion):?>
					<?CMaxCustom::drawFormField($FIELD_SID, $arQuestion);?>
				<?endforeach;?>
			<?endif;?>
			<div class="clearboth"></div>
			<?$bHiddenCaptcha = (isset($arParams["HIDDEN_CAPTCHA"]) ? $arParams["HIDDEN_CAPTCHA"] : COption::GetOptionString("aspro.max", "HIDDEN_CAPTCHA", "Y"));?>
			<?if($arResult["isUseCaptcha"] == "Y"):?>
				<div class="form-control captcha-row clearfix">
					<label><span><?=GetMessage("FORM_CAPRCHE_TITLE")?>&nbsp;<span class="star">*</span></span></label>
					<div class="captcha_image">
						<img src="/bitrix/tools/captcha.php?captcha_sid=<?=htmlspecialcharsbx($arResult["CAPTCHACode"])?>" border="0" />
						<input type="hidden" name="captcha_sid" value="<?=htmlspecialcharsbx($arResult["CAPTCHACode"])?>" />
						<div class="captcha_reload"></div>
					</div>
					<div class="captcha_input">
						<input type="text" class="inputtext captcha" name="captcha_word" size="30" maxlength="50" value="" required />
					</div>
				</div>
			<?elseif($bHiddenCaptcha == "Y"):?>
				<textarea name="nspm" style="display:none;"></textarea>
			<?endif;?>
			<div class="clearboth"></div>
		</div>
		<div class="form_footer">
			<?$bShowLicenses = (isset($arParams["SHOW_LICENCE"]) ? $arParams["SHOW_LICENCE"] : COption::GetOptionString("aspro.max", "SHOW_LICENCE", "Y"));?>
			<?if($bShowLicenses == "Y"):?>
				<div class="licence_block filter onoff label_block">
					<input type="checkbox" id="licenses_popup" name="licenses_popup" <?=(COption::GetOptionString("aspro.max", "LICENCE_CHECKED", "N") == "Y" ? "checked" : "");?> required value="Y">
					<label for="licenses_popup">
						<?$APPLICATION->IncludeFile(SITE_DIR."include/licenses_text.php", Array(), Array("MODE" => "html", "NAME" => "LICENSES")); ?>
					</label>
				</div>
			<?endif;?>
			<button type="submit" class="btn btn-lg btn-default"><span><?=$arResult["arForm"]["BUTTON"]?></span></button>
			<input type="hidden" class="btn btn-default" value="<?=$arResult["arForm"]["BUTTON"]?>" name="web_form_submit">
		</div>
		<?=$arResult["FORM_FOOTER"]?>
		<?if($arResult["isFormErrors"] != "Y"):?>
			<script>
				//Comagic.openSiteRequestPanel();
				//console.log('Comagic.openSiteRequestPanel');
			</script>
		<?endif;?>
	<?}?>
	<!--/noindex-->
	<script type="text/javascript">
	$(document).ready(function(){

		$('form[name="<?=$arResult["arForm"]["VARNAME"]?>"]').validate({
			highlight: function( element ){
				$(element).parent().addClass('error');
			},
			unhighlight: function( element ){
				$(element).parent().removeClass('error');
			},
			submitHandler: function( form ){
				if( $('form[name="<?=$arResult["arForm"]["VARNAME"]?>"]').valid() ){
					setTimeout(function() {
						$(form).find('button[type="submit"]').attr("disabled", "disabled");
					}, 500);
					var eventdata = {type: 'form_submit', form: form, form_name: '<?=$arResult["arForm"]["VARNAME"]?>'};
					BX.onCustomEvent('onSubmitForm', [eventdata]);
				}
			},
			errorPlacement: function( error, element ){
				error.insertBefore(element);
			},
			messages:{
		      licenses_popup: {
		        required : BX.message('JS_REQUIRED_LICENSES')
		      }
			}
		});

		if(arMaxOptions['THEME']['PHONE_MASK'].length){
			var base_mask = arMaxOptions['THEME']['PHONE_MASK'].replace( /(\d)/g, '_' );
			$('form[name=<?=$arResult["arForm"]["VARNAME"]?>] input.phone').inputmask('mask', {'mask': arMaxOptions['THEME']['PHONE_MASK'] });
			$('form[name=<?=$arResult["arForm"]["VARNAME"]?>] input.phone').blur(function(){
				if( $(this).val() == base_mask || $(this).val() == '' ){
					if( $(this).hasClass('required') ){
						$(this).parent().find('label.error').html(BX.message('JS_REQUIRED'));
					}
				}
			});
		}

		$('input[type=file]').uniform({fileButtonHtml: BX.message('JS_FILE_BUTTON_NAME'), fileDefaultHtml: BX.message('JS_FILE_DEFAULT')});
		$(document).on('change', 'input[type=file]', function(){
			if($(this).val())
			{
				$(this).closest('.uploader').addClass('files_add');
			}
			else
			{
				$(this).closest('.uploader').removeClass('files_add');
			}
		})
		$('.form .add_file').on('click', function(){
			var index = $(this).closest('.input').find('input[type=file]').length+1;

			$(this).closest('.form-group').find('.input').append('<input type="file" id="POPUP_FILE" name="FILE_n'+index+'"   class="inputfile" value="" />');
			//$('<input type="file" id="POPUP_FILE" name="FILE_n'+index+'"   class="inputfile" value="" />').closest()($(this));
			$('input[type=file]').uniform({fileButtonHtml: BX.message('JS_FILE_BUTTON_NAME'), fileDefaultHtml: BX.message('JS_FILE_DEFAULT')});
		});

		$('.form .add_file').on('click', function(){
			var index = $(this).closest('.input').find('input[type=file]').length+1;

			$(this).closest('.form-group').find('.input').append('<input type="file" id="POPUP_FILE" name="FILE_n'+index+'"   class="inputfile" value="" />');
			//$('<input type="file" id="POPUP_FILE" name="FILE_n'+index+'"   class="inputfile" value="" />').closest()($(this));
			$('input[type=file]').uniform({fileButtonHtml: BX.message('JS_FILE_BUTTON_NAME'), fileDefaultHtml: BX.message('JS_FILE_DEFAULT')});
		});

		$('.form .add_text').on('click', function(){
			var input = $(this).closest('.form-group').find('input[type=text]').first(),
				index = $(this).closest('.form-group').find('input[type=text]').length,
				name = input.attr('id').split('POPUP_')[1];

			$(this).closest('.form-group').find('.input').append('<input type="text" id="POPUP_'+name+'" name="'+name+'['+index+']"  class="form-control " value="" />');
		});
			
		// $('.popup').jqmAddClose('a.jqmClose');
		$('.jqmClose').on('click', function(e){
			e.preventDefault();
			$(this).closest('.jqmWindow').jqmHide();
		})
		$('.popup').jqmAddClose('button[name="web_form_reset"]');
	});
	</script>
</div>