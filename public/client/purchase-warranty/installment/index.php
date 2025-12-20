<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Рассрочка");
$APPLICATION->SetPageProperty('description', 'Оформите рассрочку на шкаф в три простых шага: сделайте заказ, заполните заявку на рассрочку и получите одобрение от банка. Мы поможем сделать вашу покупку удобной!');
?><div class="instalments-section">
	<div class="d-flex flex-column align-items-center justify-content-center instalments-section__banner">
		<div class="instalments-section__banner-header">
			 Забери шкаф сейчас, плати потом!
		</div>
		<div class="instalments-section__banner-text">
			 В рассрочку без переплат*
		</div>
		<div class="instalments-section__banner-filter">
		</div>
	</div>
	<div class="instalments-section__conteiner">
		<div class="d-flex justify-content-center">
			<h2>На любой шкаф от компании «Е1» можно оформить онлайн-рассрочку:</h2>
		</div>
		<div class="d-flex flex-wrap row fow instalments-section__list">
			<div class="col-xs-12 col-md-6 col-lg-4 instalments-section__list-item">
				<div class="d-flex flex-column align-items-center justify-content-center instalments-section__list-wrapper">
					<div class="d-flex align-items-center justify-content-center d-flex align-items-center justify-content-center instalments-section__list-img">
 <img src="/upload/images/cond_1.png">
					</div>
					<div class="d-flex align-items-center justify-content-center instalments-section__list-text">
						 СРОК РАССРОЧКИ – 6 МЕС
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-md-6 col-lg-4 instalments-section__list-item">
				<div class="d-flex flex-column align-items-center justify-content-center instalments-section__list-wrapper">
					<div class="d-flex align-items-center justify-content-center instalments-section__list-img">
 <img src="/upload/images/cond_2.png">
					</div>
					<div class="d-flex align-items-center justify-content-center instalments-section__list-text">
						 0% – ПЕРЕПЛАТА
					</div>
				</div>
			</div>
			<div class="col-xs-12 col-md-6 col-lg-4 instalments-section__list-item">
				<div class="d-flex flex-column align-items-center justify-content-center d-flex flex-column align-items-center justify-content-center instalments-section__list-wrapper">
					<div class="d-flex align-items-center justify-content-center instalments-section__list-img">
 <img src="/upload/images/cond_3.png">
					</div>
					<div class="d-flex align-items-center justify-content-center instalments-section__list-text">
						 ВЫПЛАТЫ ЕЖЕМЕСЯЧНО<br>
						 РАВНЫМИ ЧАСТЯМИ
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="instalments-section__conteiner">
		<div class="d-flex justify-content-center">
			<h2>Как оформить рассрочку?</h2>
		</div>
		<div class="row instalments-section__steps ml-0">
			<div class="col-xs-12 col-lg-7 steps-list">
				<div class="steps-list__item">
					<div class="d-flex align-items-center steps-list__title">
						Шаг 1
					</div>
					<div class="steps-list__text">
						<p>
							Закажите шкаф обычным путем, нажав "купить" на карточке товара. Вам перезвонит менеджер. Во время разговора с ним скажите о намерении получить рассрочку.
						</p>
						<p>
							Менеджер вышлет ссылку на заполнение заявки на рассрочку. Любым удобным способом - почта, sms, whatsapp и т.д.
						</p>
					</div>
				</div>
				<div class="steps-list__item">
					<div class="d-flex align-items-center steps-list__title">
						Шаг 2
					</div>
					<div class="steps-list__text">
						<p>
							После заполнения данных, отправьте заявку на рассрочку и дождитесь ответа от банка. Весь процесс займет не более 15 минут.
						</p>
					</div>
				</div>
				<div class="steps-list__item">
					<div class="d-flex align-items-center steps-list__title">
						Шаг 3
					</div>
					<div class="steps-list__text">
						<p>
							После получения одобрения от банка, с Вами свяжется менеджер Е1 и оформит заказ.
						</p>
					</div>
				</div>
			</div>
			<div class="col-xs-5 instalments-section__steps-img">
 <img src="/upload/medialibrary/861/wardrobe.png">
			</div>
		</div>
	</div>
	<div class="instalments-section__conteiner">
		<div class="instalments-section__items">
			<div class="d-flex justify-content-center">
				<h2><br>
				</h2>
			</div>
			 <?global $sectionsFilter;
			 $sectionsFilter = array( 'UF_IS_TAG' => 0);
			$APPLICATION->IncludeComponent(
	"bitrix:catalog.section.list", 
	"sections_compact", 
	array(
		"ADD_SECTIONS_CHAIN" => "Y",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"COUNT_ELEMENTS" => "Y",
		"COUNT_ELEMENTS_FILTER" => "CNT_ACTIVE",
		"FILTER_NAME" => "sectionsFilter",
		"IBLOCK_ID" => "48",
		"IBLOCK_TYPE" => "1c_catalog",
		"SECTION_CODE" => "shkafy_kupe",
		"SECTION_FIELDS" => array(
			0 => "",
			1 => "",
		),
		"SECTION_ID" => $_REQUEST["SECTION_ID"],
		"SECTION_URL" => "",
		"SECTION_USER_FIELDS" => array(
			0 => "UF_IS_TAG",
			1 => "",
		),
		"SHOW_PARENT_NAME" => "Y",
		"TOP_DEPTH" => "1",
		"NO_MARGIN" => "Y",
		"VIEW_MODE" => "LINE",
		"COMPONENT_TEMPLATE" => "sections_compact",
		"SHOW_SECTION_LIST_PICTURES" => "Y",
		"GRID_CLASS" => "col-md-4 col-xs-6 col-xxs-12",
		"BIG_IMG" => "Y"
	),
	false
);?>
		</div>
	</div>
	<div class="instalments-section__conteiner">
		<div class="instalments-section__form">
			 <?$APPLICATION->IncludeComponent(
	"bitrix:form.result.new",
	"inline",
	Array(
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_SHADOW" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"CACHE_TIME" => "3600",
		"CACHE_TYPE" => "A",
		"CHAIN_ITEM_LINK" => "",
		"CHAIN_ITEM_TEXT" => "",
		"COMPONENT_TEMPLATE" => "inline",
		"EDIT_URL" => "",
		"IGNORE_CUSTOM_TEMPLATE" => "N",
		"LIST_URL" => "",
		"SEF_MODE" => "N",
		"SUCCESS_URL" => "",
		"USE_EXTENDED_ERRORS" => "N",
		"VARIABLE_ALIASES" => array("WEB_FORM_ID"=>"WEB_FORM_ID","RESULT_ID"=>"RESULT_ID",),
		"WEB_FORM_ID" => "14"
	)
);?>
		</div>
	</div>
</div>
<br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>