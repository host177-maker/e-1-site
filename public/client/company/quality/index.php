<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle("Качество сервиса");
$APPLICATION->SetPageProperty('description', 'Компания «Е1» обеспечивает высокоточное производство мебели с автоматизацией процессов. Узнайте о нашей прозрачной статистике розничных заказов и качестве работы.');

if (\Bitrix\Main\Loader::includeModule("iblock") && \Bitrix\Main\Loader::includeModule("aspro.max")) {
	$sApplicationsFileUrl = 'http://www.e1com.ru/stat.txt';
	$sApplicationsFile = file($sApplicationsFileUrl);

	$iCitiesIblockId = \CMaxRegionality::getRegionIBlockID();
	$arCitiesFilter = array('IBLOCK_ID' => $iCitiesIblockId);
	$arFileResult = array();
	$obElement = new CIBlockElement;

	$arNameReplace = array('Санкт Петербург' => 'Санкт-Петербург');
	foreach ($sApplicationsFile as $sApplicationsFileLine) {
		$cols = explode(";", $sApplicationsFileLine);
		if ($cols[0] == 'city') {
			$cols[1] = trim(str_replace(array('г. ', ' г.', ' г'), '', $cols[1]));
			if (isset($arNameReplace[$cols[1]]) && !empty($arNameReplace[$cols[1]])) {
				$cols[1] = $arNameReplace[$cols[1]];
			}
			$arFileResult['CITIES'][$cols[1]] = $cols[2];
			$arCitiesFilter['NAME'][] = $cols[1];
		} else {
			$arFileResult['STATS'][trim($cols[0])] = array('name' => trim($cols[1]), 'value' => trim($cols[2]));
		}
	}

	$arCities = array();
	$arCitiesSelect  = array('ID', 'NAME');
	$dbCitiesElement = CIBlockElement::GetList(false, $arCitiesFilter, false, false, $arCitiesSelect);
	while ($arCitiesElement = $dbCitiesElement->Fetch()) {
		$arCities[$arCitiesElement['NAME']] = $arCitiesElement['ID'];
	}

	$arFileResult['STATS']['date'] = date('d.m.Y H:i:s');
	$arFileResult['STATS'] = json_encode($arFileResult['STATS']);

	foreach ($arFileResult['CITIES'] as $key => $value) {
		if (isset($arCities[$key]) && !empty($arCities[$key])) {
			$arUpdateProps = array('REGION_TAG_DELIVERY_TIME' => $value, 'REGION_TAG_APPLICATIONS_STATS' => array('VALUE' => array('TYPE' => 'TEXT', 'TEXT' => $arFileResult['STATS'])));
			CIBlockElement::SetPropertyValuesEx($arCities[$key], $iCitiesIblockId, $arUpdateProps);
		}
	}
}

$arRegion['STATS'] = json_decode($arFileResult['STATS'], true);
$sClaimsDefectsValue = '';
$sClaimsDefectsValue = round($arRegion['STATS']['count_bad']['value'] / $arRegion['STATS']['count_not_shipped']['value'] * 100, 2);
?>

<p>В «Е1» мы сделали ставку на высокотехнологичное производство и можем ответственно заявить — мы наладили все максимально чётко. Все заказы автоматически передаются на станки и рабочие места исполнителей на мебельном производстве. Так мы <b>практически исключили ошибки</b> при изготовлении заказов. Ниже представлены текущие показатели нашей работы по розничным заказам. И сегодня «Е1» — одна из немногих мебельных компаний в РФ, предоставляющих такую информацию онлайн.</p>

<h3>Статистика по работе служб «Е1» на <?= $arRegion['STATS']['date'] ?></h3>

<div class="statistics">
	<p><b>Производство</b></p>
	<ul>
		<? if (!empty($arRegion['STATS']['count_not_shipped']['value'])) : ?>
			<li>
				<span>
					<span>Общее число заказов в работе:</span>
				</span>
				<span class="right"><b><?= $arRegion['STATS']['count_not_shipped']['value'] ?> шт.</b></span>
			</li>
		<? endif; ?>
		<? if (!empty($arRegion['STATS']['count_bad']['value'])) : ?>
			<li>
				<span>
					<span>Общее количество рекламаций и брака в работе:</span>
				</span>
				<span class="right"><b><?= $sClaimsDefectsValue ?>% (<?= $arRegion['STATS']['count_bad']['value'] ?> шт)</b></span>
			</li>
		<? endif; ?>
		<? if (!empty($arRegion['STATS']['plan_day']['value'])) : ?>
			<li>
				<span>
					<span>Среднее время отгрузки продукции с производства по графику:</span>
				</span>
                <?
                $arRegion['STATS']['plan_day']['value'] = str_replace(',', '.',$arRegion['STATS']['plan_day']['value']);
                ?>
				<span class="right"><b><?= $arRegion['STATS']['plan_day']['value']; ?> <?= COrwoFunctions::GetWordEnding(ceil($arRegion['STATS']['plan_day']['value']), array("день", "дня", "дней")); ?></b></span>
			</li>
		<? endif; ?>
		<? if (!empty($arRegion['STATS']['real_day']['value'])) : ?>
			<li>
				<span>
					<span>фактически:</span>
				</span>
                <?
                $arRegion['STATS']['real_day']['value'] = str_replace(',', '.',$arRegion['STATS']['real_day']['value']);
                ?>
				<span class="right"><b><?= $arRegion['STATS']['real_day']['value']; ?> <?= COrwoFunctions::GetWordEnding(ceil($arRegion['STATS']['real_day']['value']), array("день", "дня", "дней")); ?></b></span>
			</li>
		<? endif; ?>
	</ul>
	<p>
		<b>Срок доставки</b>
	</p>

	<ul>
		<li>
			<span>
				<span>За последний месяц с момента заказа:</span>
			</span>
			<span class="right">
				<b>
					<? if (!empty($arRegion['PROPERTY_REGION_TAG_DELIVERY_TIME_VALUE'])) : ?>
                        <?
                        $arRegion['PROPERTY_REGION_TAG_DELIVERY_TIME_VALUE'] = str_replace(',', '.',$arRegion['PROPERTY_REGION_TAG_DELIVERY_TIME_VALUE']);
                        ?>
						<?= $arRegion['PROPERTY_REGION_TAG_DELIVERY_TIME_VALUE'] ?> <?= COrwoFunctions::GetWordEnding(ceil($arRegion['PROPERTY_REGION_TAG_DELIVERY_TIME_VALUE']), array("день", "дня", "дней")) ?>
					<? else : ?>
						Нет данных
					<? endif ?>
				</b>
			</span>
		</li>
	</ul>
</div>

<p>Вы можете проверить состояние вашего заказа. Для этого необходимо перейти в <a href="/auth/">личный кабинет</a> и авторизоваться.</p>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>