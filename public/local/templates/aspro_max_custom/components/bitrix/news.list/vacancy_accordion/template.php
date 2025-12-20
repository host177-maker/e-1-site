<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<? $this->setFrameMode(true); ?>
<? global $arRegion; ?>
<div class="actual-vacancies">
	<h4 class="actual-vacancies-title">Актуальные вакансии</h4>
	<div class="custom__dropdown actual-vacancies-dropdown">
		<div class="custom__dropdown-value">
			<span>Выберите город</span>
		</div>
		<? if (!empty($arResult['CITY'])) { ?>
			<ul class="custom__dropdown-list">
				<?
				foreach ($arResult['CITY'] as $id => $arCity) { ?>
					<li data-id="<?= $arCity['ID'] ?>"><?= $arCity['NAME'] ?></li>
				<?
				}
				?>
			</ul>
		<? } ?>
	</div>
</div>
<div class="items row accordion-type-1 vacancies-list">
	<?
	foreach ($arResult['ITEMS'] as $k => $arVacancy) { ?>
		<div class="col-md-12 vacancy-item" data-id="<?= (!empty($arVacancy["PROPERTIES"]["CITY_REGION"]["VALUE"])) ? $arVacancy["PROPERTIES"]["CITY_REGION"]["VALUE"]: 'nocity' ?>">
			<div class="item_wrap box-shadow rounded3 bordered-block  item-accordion-wrapper">
				<div class="item accordion-head colored_theme_hover_bg-block noborder wti clearfix accordion-close collapsed" id="bx_3322728009_116397" data-toggle="collapse" data-parent="#accordion495" href="#accordion<?= $arVacancy['ID'] ?>_495">
					<span class="arrow_open pull-right colored_theme_hover_bg-el"></span>
					<div class="body-info with-section ">
						<div class="top-block justify-content-between align-items-start">
							<div class="top-block__info">
								<div class="title font_mlg "><?= $arVacancy['NAME'] ?></div>
								<div class="properties muted font_upper">
									<div class="inner-wrapper">
										<? if (!empty($arResult['CITY'][$arVacancy["PROPERTIES"]["CITY_REGION"]["VALUE"]]['NAME'])) { ?>
											<div class="property  city"><?= $arResult['CITY'][$arVacancy["PROPERTIES"]["CITY_REGION"]["VALUE"]]['NAME'] ?></div>
											<span class="separator">—</span>
										<? } ?>
									</div>
									<div class="inner-wrapper">
										<? if (!empty($arVacancy["PROPERTIES"]["QUALITY"]["VALUE"])) { ?>
											<div class="property  quality"><?= $arVacancy["PROPERTIES"]["QUALITY"]["NAME"] ?>:&nbsp;<?= $arVacancy["PROPERTIES"]["QUALITY"]["VALUE"] ?></div>
											<span class="separator">—</span>
										<? } ?>
									</div>
									<div class="inner-wrapper">
										<? if (!empty($arVacancy["PROPERTIES"]["WORK_TYPE"]["VALUE"])) { ?>
											<div class="property  work_type"><?= $arVacancy["PROPERTIES"]["WORK_TYPE"]["VALUE"] ?></div>
											<span class="separator">—</span>
										<? } ?>
									</div>
								</div>
							</div>
							<div class="top-block__salary"><?= $arVacancy["PROPERTIES"]["PAY"]["VALUE"] ?></div>
						</div>
					</div>
				</div>
				<div id="accordion<?= $arVacancy['ID'] ?>_495" class="panel-collapse collapse" style="height: 0px;">
					<div class="accordion-body">
						<div class="row">
							<div class="col-md-12">
								<div class="text">
									<?= $arVacancy["PREVIEW_TEXT"] ?>
								</div>
								<div class="add_resume">
									<div class="button_wrap">
										<span><span class="btn btn-default btn-lg animate-load" data-event="jqm" data-name="resume" data-param-form_id="RESUME" data-autoload-post="<?= $arVacancy['NAME'] ?>" data-autoload-city="<?= $arResult['CITY'][$arVacancy["PROPERTIES"]["CITY_REGION"]["VALUE"]]['NAME'] ?>" data-autohide="">Отправить резюме</span></span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	<?
	}
	?>
</div>

