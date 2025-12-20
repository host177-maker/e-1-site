<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Марафон удачи");

$APPLICATION->SetPageProperty("description", "Наша Компания предлагает вашему вниманию большой ассортимент шкафов купе по низким ценам. Сделать индивидуальный заказ или выбрать из имеющихся образцов – решать Вам. Только высококачественная продукция по низкой цене! Наш телефон: (861) 290-85-10");

$APPLICATION->AddHeadScript('/everyday/js/maskedinput.js');
$APPLICATION->AddHeadScript('/everyday/js/everyday.js');
//$APPLICATION->SetAdditionalCss('/everyday/css/bootstrap.min.css');
$APPLICATION->SetAdditionalCss('/everyday/css/styles.css');

include_once($_SERVER["DOCUMENT_ROOT"] . "/everyday/classes/vkOAuth.php");

$vkOAuth = new vkOAuth();

$user = array();
if (isset($_COOKIE['user'])) {
	$user = unserialize($_COOKIE['user'], ['allowed_classes' => false]);
}

if (empty($user)) {
	if ($user = $vkOAuth->action()) {
		setcookie('user', serialize($user), time() + 7776000, '/'); //90 дней
	}
}
?><div class="everyday">
	<div class="container" id="action">
		<div class="block block_orange" id="step1" title="Код PHP: &lt;?= (isset($user) &amp;&amp; !empty($user)) || ($_GET['step'] == '2') || ($_GET['step'] == '3') ? 'style=&quot;di...">
			<?= (isset($user) && !empty($user)) || ($_GET['step'] == '2') || ($_GET['step'] == '3') ? 'style="display: none;"' : '' ?><span class="bxhtmled-surrogate-inner"><span class="bxhtmled-right-side-item-icon"></span><span class="bxhtmled-comp-lable" unselectable="on" spellcheck="false"></span></span>
			<div class="row">
 <img src="images/new-banner.jpg" style="width: 100%; margin-bottom: 35px; padding: 0 1rem;" class="img-fluid" alt="">
				<div class="col-md-12">
					<h1 class="titleh1 block__title center">Е1 возвращает деньги за шкаф</h1>
					<p class="title__paragraph">
						 Купите шкаф-купе Е1 в интернет-магазине, фирменном салоне или салоне нашего партнера и примите участие в акции!
					</p>
				</div>
				<div class="row">
					<div class="col-md-12 center">
						<ul class="everyday-list">
							<div class="row no-gutters">
								<div class="col-md-1">
									<span class="list__el__sub"></span>
								</div>
								<div class="col-md-10">
									<li class="list__el">Еженедельно разыгрываются 5 денежных призов. Для регистрации используйте номер телефона, указанный в договоре покупки. Участвуют номера, зарегистрированные на сайте Е1 в течение недели.</li>
								</div>

							</div>
						</ul>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12 center">
					<h3 class="title block__title">Четыре шага для участия в акции</h3>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3 step-block">
					<div class="step step_1">
						<p class="step__title">
							 Зарегистрируйтесь
						</p>
						<p>
							 Авторизуйтесь через Вконтакте и заполните форму участника
						</p>
						<div class="step__arrow">
						</div>
					</div>
				</div>
				<div class="col-md-3 step-block">
					<div class="step step_2">
						<p class="step__title">
							 Вступите в нашу группу
						</p>
						<p>
							 Станьте участником группы Е1 Вконтакте <a target="_blank" href="https://vk.com/e_odin" class="everyday__link">vk.com/e_odin</a>
						</p>
						<div class="step__arrow">
						</div>
					</div>
				</div>
				<div class="col-md-3 step-block">
					<div class="step step_3">
						<p class="step__title">
							 Сделайте репост
						</p>
						<p>
							 Сделайте репост записи об акции, который закреплен на стене группы Вк
						</p>
						<div class="step__arrow">
						</div>
					</div>
				</div>
				<div class="col-md-3 step-block">
					<div class="step step_4">
						<p class="step__title">
							 Дождитесь итогов
						</p>
						<p>
							 <?php echo getNextMonday(); ?> мы объявим победителей
						</p>
						<div class="step__arrow">
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12 center">
 <a id="registr_akciya" href="<?= $vkOAuth->getLink() ?>" class="btn new-vk-btn">Зарегистрироваться и участвовать</a>
				</div>
				<div class="col-md-12 center">
 <a href="/sale/vozvrashchaem_dengi_za_shkaf/" class="everyday__link">Подробные условия акции</a>
				</div>
			</div>
			<div class="clearfix">
			</div>
		</div>
	</div>
</div>
<br><?php
		include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");

		function getNextMonday()
		{
			$result = '';

			for ($i = 1; $i <= 8; $i++) {
				if (date('w', strtotime('+' . $i . ' day')) == 1) {
					$result .= date('d', strtotime('+' . $i . ' day'));
					switch (date('m', strtotime('+' . $i . ' day'))) {
						case 1:
							$result .= '&nbsp;января&nbsp;';
							break;
						case 2:
							$result .= '&nbsp;февраля&nbsp;';
							break;
						case 3:
							$result .= '&nbsp;марта&nbsp;';
							break;
						case 4:
							$result .= '&nbsp;апреля&nbsp;';
							break;
						case 5:
							$result .= '&nbsp;мая&nbsp;';
							break;
						case 6:
							$result .= '&nbsp;июня&nbsp;';
							break;
						case 7:
							$result .= '&nbsp;июля&nbsp;';
							break;
						case 8:
							$result .= '&nbsp;августа&nbsp;';
							break;
						case 9:
							$result .= '&nbsp;сентября&nbsp;';
							break;
						case 10:
							$result .= '&nbsp;октября&nbsp;';
							break;
						case 11:
							$result .= '&nbsp;ноября&nbsp;';
							break;
						case 12:
							$result .= '&nbsp;декабря&nbsp;';
							break;
					}
					$result .= date('Y года', strtotime('+' . $i . ' day'));
					break;
				}
			}
			return $result;
		}
		?>