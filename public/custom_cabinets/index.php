<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "Шкафы на заказ");
$APPLICATION->SetPageProperty("title", "Шкафы на заказ");
$APPLICATION->SetTitle("Шкафы на заказ");

//фотки для слайдера
$sliderImages = glob($_SERVER["DOCUMENT_ROOT"] . SITE_TEMPLATE_PATH . "/images/custom_cabinets/slider-mages/*.jpg");
$sliderImages = array_map(fn ($path) => str_replace($_SERVER["DOCUMENT_ROOT"], '', $path), $sliderImages);

?>
<div class="custom-cabinets">

    <div class="hero">
        <? $APPLICATION->IncludeComponent(
            "bitrix:main.include",
            "",
            array(
                "AREA_FILE_SHOW" => "file",
                "PATH" => $APPLICATION->GetCurDir() . '/includes/hero.php'
            )
        ); ?>
    </div>

    <div class="button-block mb-60">
        <button class="btn btn-default btn-lg" type="button" data-event="jqm" data-param-form_id="CALLBACK_FREE" data-name="callback_free">
            БЕСПЛАТНАЯ КОНСУЛЬТАЦИЯ СПЕЦИАЛИСТА
        </button>
    </div>

    <div class="why-us mb-60">
        <? $APPLICATION->IncludeComponent(
            "bitrix:main.include",
            "",
            array(
                "AREA_FILE_SHOW" => "file",
                "PATH" => $APPLICATION->GetCurDir() . '/includes/why.php'
            )
        ); ?>
    </div>

    <div class="our-works mb-60">
        <?
        /**
         * 
         * пути картинок для слайдера:
         * - относительный /local/templates/aspro_max_custom/images/custom_cabinets/slider-mages/
         * - в админке https://www.e-1.ru/bitrix/admin/fileman_admin.php?PAGEN_1=1&SIZEN_1=20&lang=ru&site=s1&path=%2Flocal%2Ftemplates%2Faspro_max_custom%2Fimages%2Fcustom_cabinets%2Fslider-mages&show_perms_for=0
         * 
         */
        ?>
        <h2>Наши работы</h2>
        <div class="our-works__swiper swiper">
            <div class="swiper-wrapper">
                <? foreach ($sliderImages as $sliderImage) : ?>
                    <div class="swiper-slide">
                        <img src="<?= $sliderImage ?>" alt="" />
                    </div>
                <? endforeach; ?>
            </div>
            <div class="swiper-button-prev_works swiper-button-prev swiper-prev"></div>
            <div class="swiper-button-next_works swiper-button-next swiper-next"></div>
        </div>
    </div>

    <div class="work-stages mb-60">
        <? $APPLICATION->IncludeComponent(
            "bitrix:main.include",
            "",
            array(
                "AREA_FILE_SHOW" => "file",
                "PATH" => $APPLICATION->GetCurDir() . '/includes/work-stages-title.php'
            )
        ); ?>
        <div class="work-stages__wrapper">
            <div class="work-stages__items js-work-stages__items">
                <? $APPLICATION->IncludeComponent(
                    "bitrix:main.include",
                    "",
                    array(
                        "AREA_FILE_SHOW" => "file",
                        "PATH" => $APPLICATION->GetCurDir() . '/includes/work-stages.php'
                    )
                ); ?>
            </div>
            <div class="work-stages__image js-work-stages__image">
                <? $APPLICATION->IncludeComponent(
                    "bitrix:main.include",
                    "",
                    array(
                        "AREA_FILE_SHOW" => "file",
                        "PATH" => $APPLICATION->GetCurDir() . '/includes/work-stages-img.php'
                    )
                ); ?>
            </div>
        </div>
    </div>

    <div class="button-block mb-60">
        <button class="btn btn-default btn-lg" data-event="jqm" data-param-form_id="FREE_DESIGN_PROJECT_ORDER_2" data-name="free_design_project_order_2" type="button">
            Заказать бесплатный дизайн-проект
        </button>
    </div>

    <div class="cabinets-features mb-60">
        <? $APPLICATION->IncludeComponent(
            "bitrix:main.include",
            "",
            array(
                "AREA_FILE_SHOW" => "file",
                "PATH" => $APPLICATION->GetCurDir() . '/includes/features-title.php'
            )
        ); ?>
        <div class="cabinets-features__items">
            <? $APPLICATION->IncludeComponent(
                "bitrix:main.include",
                "",
                array(
                    "AREA_FILE_SHOW" => "file",
                    "PATH" => $APPLICATION->GetCurDir() . '/includes/features.php'
                )
            ); ?>
        </div>
        <div class="cabinets-features__mobile-items">
            <div class="cabinets-features__swiper swiper">
                <div class="swiper-wrapper">
                    <? $APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => $APPLICATION->GetCurDir() . '/includes/features-mobile.php'
                        )
                    ); ?>
                </div>
                <div class="swiper-button-prev_features swiper-button-prev swiper-prev"></div>
                <div class="swiper-button-next_features swiper-button-next swiper-next"></div>
            </div>
        </div>
    </div>

    <div class="button-block button-block__bottom mb-60">
        <button class="btn btn-default btn-lg" data-event="jqm" data-param-form_id="ORDER_CUSTOM_CABINET" data-name="order_custom_cabinet" type="button">
            сделать заказ
        </button>
    </div>

    <div class="page-notes">
        <div class="page-note">*для удаленных регионов срок изготовления и доставки может быть увеличен до 45 дней</div>
    </div>

</div>

<link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH ?>/css/custom-cabinets.css" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    (() => {
        const swiper = new Swiper('.our-works__swiper', {
            loop: true,
            grabCursor: true,
            spaceBetween: 20,
            navigation: {
                nextEl: '.swiper-button-next_works',
                prevEl: '.swiper-button-prev_works',
            },
        });
    })();
</script>
<script>
    (() => {
        const swiper = new Swiper('.cabinets-features__swiper', {
            loop: true,
            autoHeight: true,
            spaceBetween: 10,
            navigation: {
                nextEl: '.swiper-button-next_features',
                prevEl: '.swiper-button-prev_features',
            },
        });
    })();
</script>

<script>
    (() => {
        const resizeImage = () => {
            let workStages = document.querySelector('.js-work-stages__items');
            let image = document.querySelector('.js-work-stages__image');
            let height = workStages?.clientHeight;

            image.style.maxHeight = height + 'px';
        }
        resizeImage();

        let throttle = null;
        window.addEventListener('resize', () => {
            clearTimeout(throttle);
            throttle = setTimeout(() => {
                resizeImage();
            }, 500);
        });
    })();
</script>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>