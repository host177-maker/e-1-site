<?
//global $isWidePage;
//$isWidePage = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("description", "У нас можно заказать гардеробную в #REGION_NAME_DECLINE_PP# по индивидуальному проекту недорого от производителя. Мебельная фабрика E1. Фирменные салоны Е1 в 20 регионах России, доставка мебели от 3-х дней, гарантия до 10 лет.");
$APPLICATION->SetPageProperty("title", "Заказать гардеробную в #REGION_NAME_DECLINE_PP# по индивидуальному проекту недорого");
//$APPLICATION->SetTitle("Гардеробные в #REGION_NAME_DECLINE_PP#");
?>
<div class="wardrobes">
    <div class="maxwidth-theme">
        <h1 hidden>Гардеробные</h1>
        <div class="wardrobes__types">
            <div class="wardrobes__type wardrobes__type_top">
                <div class="wardrobes__type-info">
                    <div class="wardrobes__type-title">Модульные гардеробные</div>
                    <div class="wardrobes__type-desc">Гардеробные системы Локер - это универсальное решение из готовых
                        модулей
                    </div>
                    <div>
                        <a href="#module-systems" animated class="btn btn-default btn-lg">ПОДРОБНЕЕ</a>
                    </div>
                </div>
                <div class="wardrobes__type-image">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/images/wardrobes/type-1.jpg" alt="Модульные гардеробные">
                </div>
            </div>
            <div class="wardrobes__type wardrobes__type_bottom">
                <div class="wardrobes__type-info">
                    <div class="wardrobes__type-title">Гардеробные на заказ</div>
                    <div class="wardrobes__type-desc">Подберем и изготовим гардеробную по вашим индивидуальным
                        размерам
                    </div>
                    <div>
                        <a href="#custom-systems" class="btn btn-default btn-lg">ПОДРОБНЕЕ</a>
                    </div>
                </div>
                <div class="wardrobes__type-image">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/images/wardrobes/type-2.jpg" alt="Гардеробные на заказ">
                </div>
            </div>
            <div class="wardrobes__type wardrobes__type_top">
                <div class="wardrobes__type-info">
                    <div class="wardrobes__type-title">двери-купе и раздвижные&nbsp;системы</div>
                    <div class="wardrobes__type-desc">Раздвижные системы являются органичным решением как для
                        встроенного шкафа, так и для отдельной комнаты
                    </div>
                    <div>
                        <a href="#sliding-systems" class="btn btn-default btn-lg">ПОДРОБНЕЕ</a>
                    </div>
                </div>
                <div class="wardrobes__type-image">
                    <img src="<?= SITE_TEMPLATE_PATH ?>/images/wardrobes/type-3.png"
                        alt="Двери-купе и раздвижные системы">
                </div>
            </div>
        </div>
        <div class="wardrobes__why-us mb-70">
            <? $APPLICATION->IncludeComponent(
                "bitrix:main.include",
                "",
                array(
                    "AREA_FILE_SHOW" => "file",
                    "PATH" => $APPLICATION->GetCurDir() . '/includes/why.php'
                )
            ); ?>
        </div>
    </div>
    <div class="gray-bg">
        <div class="maxwidth-theme">
            <div class="wardrobes__ready-systems pt-50">
                <h2>Готовые гардеробные системы и модули серии Локер</h2>
                <div class="swiper ready-systems__swiper">
                    <div class="swiper-wrapper">
                        <?php

                        $APPLICATION->IncludeComponent(
                            "absteam:catalog.section",
                            'wardrobes',
                            [
                                "IBLOCK_ID" => 48,
                                "IBLOCK_TYPE" => "1c_catalog",
                                "PRICE_CODE" => 'BASE',
                                "TOP_DEPTH" => "4",
                                "NO_MARGIN" => "Y",
                                "INCLUDE_SUBSECTIONS" => "Y",
                                "ADD_URL_TEMPLATE" => "/basket/",
                                "BASKET_URL" => "/basket/",
                                'ELEMENT_ID' => [845924, 845923, 1442298, 1463690, 1463689, 784413, 845926, 784414]
                            ]
                        );
                        ?>
                    </div>
                </div>
                <div class="swiper-button-prev_ready swiper-button-prev swiper-prev"></div>
                <div class="swiper-button-next_ready swiper-button-next swiper-next"></div>
            </div>
            <div class="wardrobes__module-systems" id="module-systems">
                <video
                    src="<?= SITE_TEMPLATE_PATH ?>/images/wardrobes/module.mp4"
                    controls></video>
                <div class="wardrobes__module-systems-text">Модульная гардеробная Локер - это универсальное решение, которое подстроится под любое пространство. Вы можете выбрать готовое решение гардеробной или собрать свою собственную из модулей различных размеров, декоров и комплектаций. Выберите свое наполнение, размерный ряд и внешний вид гардеробной и получите ее всего за неделю после оформления заказа!</div>
                <div class="wardrobes__module-systems-button button-center">
                    <div
                        class="btn btn-default btn-lg"
                        data-event="jqm"
                        data-param-form_id="FREE_DESIGN_PROJECT_ORDER"
                        data-name="free_design_project_order">
                        Заказать бесплатный дизайн-проект
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="maxwidth-theme maxwidth-theme_wardrobes">
        <div class="wardrobes__custom mb-60" id="custom-systems">
            <h2>Особенности гардеробных на заказ</h2>
            <div class="wardrobes__custom-features">
                <button class="wardrobes__feature-btn active js-tab-btn" type="button" data-target="sizes">Размеры и декоры</button>
                <button class="wardrobes__feature-btn js-tab-btn" type="button" data-target="materials">Материалы</button>
                <button class="wardrobes__feature-btn js-tab-btn" type="button" data-target="equipment">Комплектация</button>
            </div>
            <div class="wardrobes__features-content">
                <div class="wardrobes__feature-content active js-tab-content" data-content="sizes">
                    <div class="wardrobes__feature-info">
                        <div class="wardrobes__feature-title">Размеры и декоры</div>
                        <div>
                            <p>Мы сделаем гардеробную систему по Вашим размерам: начиная от 1 м и не ограничивая себя более в цифрах.</p>
                            <p>Заказные гардеробные представлены в 10 различных декорах ЛДСП, что позволяет изготовить ее в тон различным интерьерам.</p>
                        </div>
                        <div
                            class="btn btn-default btn-lg"
                            data-event="jqm"
                            data-param-form_id="WANT_DRESSING_ROOM"
                            data-name="want_dressing_room">
                            Хочу заказать гардеробную
                        </div>
                    </div>
                    <div class="wardrobes__feature-image">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/images/wardrobes/feature-1.png" alt="">
                    </div>
                </div>
                <div class="wardrobes__feature-content js-tab-content" data-content="materials">
                    <div class="wardrobes__feature-info">
                        <div class="wardrobes__feature-title">Материалы</div>
                        <div>
                            <ul class="disc-list">
                                <li>Безопасная ЛДСП европейского класса экологичности Е1</li>
                                <li>Нагрузка на полки до 20 кг</li>
                                <li>Нагрузка на штангу до 60 кг</li>
                                <li>Ударостойкая кромка ПВХ 2 мм</li>
                                <li>Защита торцов шкафа от сколов и повреждений</li>
                                <li>Полки закреплены усиленными металлическими стяжками - Hettich</li>
                            </ul>
                        </div>
                        <div
                            class="btn btn-default btn-lg"
                            data-event="jqm"
                            data-param-form_id="WANT_DRESSING_ROOM"
                            data-name="want_dressing_room">
                            Хочу заказать гардеробную
                        </div>
                    </div>
                    <div class="wardrobes__feature-image">
                        <img src="<?= SITE_TEMPLATE_PATH ?>/images/wardrobes/feature-2.png" alt="">
                    </div>
                </div>
                <div class="wardrobes__feature-content js-tab-content" data-content="equipment">
                    <div class="wardrobes__feature-info">
                        <div class="wardrobes__feature-title">Комплектация</div>
                        <div>
                            <p>В гардеробной возможно выбрать индивидуальную систему наполнения
                                с различной шириной секций и разными аксессуарами:</p>
                            <ul>
                                <li>встроенным модулем с выдвижными ящиками,</li>
                                <li>выдвижными полками с корзинами,</li>
                                <li>выдвижными вешалками для брюк,</li>
                                <li>выдвижными полками с корзинами для обуви.</li>
                            </ul>
                        </div>
                        <div
                            class="btn btn-default btn-lg"
                            data-event="jqm"
                            data-param-form_id="WANT_DRESSING_ROOM"
                            data-name="want_dressing_room">
                            Хочу заказать гардеробную
                        </div>
                    </div>
                    <div class="wardrobes__feature-image">
                        <figure>
                            <svg
                                version="1.1"
                                xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink"
                                viewBox="0 0 200 106"
                                width="100%"
                                height="100%">
                                <image
                                    width="200"
                                    height="106"
                                    preserveAspectRatio="xMinYMin slice"
                                    xlink:href="<?= SITE_TEMPLATE_PATH ?>/images/wardrobes/feature-3.png" />
                                <g class="3 image-map-item js-image-map-item" opacity="1">
                                    <image
                                        class="map-image js-map-image"
                                        x="46"
                                        y="55"
                                        width="48"
                                        height="48"
                                        opacity="0"
                                        xlink:href="<?= SITE_TEMPLATE_PATH ?>/images/wardrobes/feature-3_3.png" />
                                    <a xlink:href="#" class="image-button js-image-button" style="transform-origin: 101px 77px">
                                        <circle cx="101" cy="77" r="4" fill="#62BB46" />
                                        <text x="101" y="79" font-size="6" fill="#FFFFFF" text-anchor="middle">+</text>
                                    </a>
                                </g>
                                <g class="2 image-map-item js-image-map-item" opacity="1">
                                    <image
                                        class="map-image js-map-image"
                                        x="38"
                                        y="37"
                                        width="48"
                                        height="48"
                                        opacity="0"
                                        xlink:href="<?= SITE_TEMPLATE_PATH ?>/images/wardrobes/feature-3_2.png" />
                                    <a xlink:href="#" class="image-button js-image-button" style="transform-origin: 93px 60px">
                                        <circle cx="93" cy="60" r="4" fill="#62BB46" />
                                        <text x="93" y="62" font-size="6" fill="#FFFFFF" text-anchor="middle">+</text>
                                    </a>
                                </g>
                                <g class="1 image-map-item js-image-map-item" opacity="1">
                                    <image
                                        class="map-image js-map-image"
                                        x="30"
                                        y="8"
                                        width="48"
                                        height="48"
                                        opacity="0"
                                        xlink:href="<?= SITE_TEMPLATE_PATH ?>/images/wardrobes/feature-3_1.png" />
                                    <a xlink:href="#" class="image-button js-image-button" style="transform-origin: 85px 33px">
                                        <circle cx="85" cy="33" r="4" fill="#62BB46" />
                                        <text x="85" y="35" font-size="6" fill="#FFFFFF" text-anchor="middle">+</text>
                                    </a>
                                </g>
                            </svg>
                        </figure>
                    </div>
                </div>
            </div>
        </div>
        <div class="wardrobes__sliding-systems mb-60" id="sliding-systems">
            <h2>Двери-купе и раздвижные системы</h2>
            <div class="wardrobes__sliding-systems-inner">
                <h3 class="desktop-only">Раздвижные системы</h3>
                <div class="wardrobes__sliding-systems-note desktop-only">Широкая палитра цветов ЛДСП: от светлых до темных и древесных цветов. Мы можем изготовить двери-купе с различными фасадами: зеркало, цветное стекло или стекло с декором, фотопечать, фотовставка, экокожа и многое другое. Профиль раздвижных систем доступен в 5 цветах: черный, белый, бронза, золото и серебро. Выберите свой уникальный дизайн раздвижных систем, который будет радовать Вас долгое время!</div>
                <div class="swiper sliding-systems__swiper">
                    <div class="swiper-wrapper">
                        <?php
                        $arFilter = [
                            'SECTION_CODE' => 'garderobnye-razdvizhnye-sistemy'
                        ];
                        ?>
                        <? $APPLICATION->IncludeComponent(
                            "bitrix:news.list",
                            "photo_wardrobes1",
                            array(
                                "ACTIVE_DATE_FORMAT" => "d.m.Y",
                                "ADD_SECTIONS_CHAIN" => "N",
                                "AJAX_MODE" => "N",
                                "AJAX_OPTION_ADDITIONAL" => "",
                                "AJAX_OPTION_HISTORY" => "N",
                                "AJAX_OPTION_JUMP" => "N",
                                "AJAX_OPTION_STYLE" => "N",
                                "CACHE_FILTER" => "N",
                                "CACHE_GROUPS" => "N",
                                "CACHE_TIME" => "36000000",
                                "CACHE_TYPE" => "A",
                                "CHECK_DATES" => "Y",
                                "DETAIL_URL" => "",
                                "DISPLAY_BOTTOM_PAGER" => "N",
                                "DISPLAY_DATE" => "N",
                                "DISPLAY_NAME" => "N",
                                "DISPLAY_PICTURE" => "Y",
                                "DISPLAY_PREVIEW_TEXT" => "N",
                                "DISPLAY_TOP_PAGER" => "N",
                                "FIELD_CODE" => array("PREVIEW_PICTURE", ""),
                                "FILTER_NAME" => "arFilter",
                                "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                                "IBLOCK_ID" => "60",
                                "IBLOCK_TYPE" => "aspro_max_content",
                                "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                                "INCLUDE_SUBSECTIONS" => "Y",
                                "MESSAGE_404" => "",
                                "NEWS_COUNT" => "20",
                                "PAGER_BASE_LINK_ENABLE" => "N",
                                "PAGER_DESC_NUMBERING" => "N",
                                "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                                "PAGER_SHOW_ALL" => "N",
                                "PAGER_SHOW_ALWAYS" => "N",
                                "PAGER_TEMPLATE" => ".default",
                                "PAGER_TITLE" => "Новости",
                                "PARENT_SECTION" => "",
                                "PARENT_SECTION_CODE" => "",
                                "PREVIEW_TRUNCATE_LEN" => "",
                                "PROPERTY_CODE" => array("", ""),
                                "SET_BROWSER_TITLE" => "N",
                                "SET_LAST_MODIFIED" => "N",
                                "SET_META_DESCRIPTION" => "N",
                                "SET_META_KEYWORDS" => "N",
                                "SET_STATUS_404" => "N",
                                "SET_TITLE" => "N",
                                "SHOW_404" => "N",
                                "SORT_BY1" => "ACTIVE_FROM",
                                "SORT_BY2" => "SORT",
                                "SORT_ORDER1" => "DESC",
                                "SORT_ORDER2" => "ASC",
                                "STRICT_SECTION_CHECK" => "N"
                            )
                        ); ?>
                    </div>
                    <div class="swiper-button-prev_sliding swiper-button-prev swiper-prev"></div>
                    <div class="swiper-button-next_sliding swiper-button-next swiper-next"></div>
                </div>
                <h3 class="mobile-only">Раздвижные системы</h3>
                <div class="wardrobes__sliding-systems-note mobile-only">Широкая палитра цветов ЛДСП: от светлых до темных и древесных цветов. Мы можем изготовить двери-купе с различными фасадами: зеркало, цветное стекло или стекло с декором, фотопечать, фотовставка, экокожа и многое другое. Профиль раздвижных систем доступен в 5 цветах: черный, белый, бронза, золото и серебро. Выберите свой уникальный дизайн раздвижных систем, который будет радовать Вас долгое время!</div>
                <div class="button-center">
                    <div class="btn btn-default btn-lg"
                        data-event="jqm"
                        data-param-form_id="WANT_SLIDING_SYSTEM"
                        data-name="want_sliding_system">Заказать раздвижную систему</div>
                </div>
            </div>
        </div>
    </div>
    <div class="maxwidth-theme">
        <div class="wardrobes__our-works mb-60">
            <h2>Готовые проекты</h2>
            <div class="swiper our-works__swiper">
                <div class="swiper-wrapper">
                    <?php
                    $arFilter = [
                        'SECTION_CODE' => 'garderobnye-gotovye-proekty'
                    ];
                    ?>
                    <? $APPLICATION->IncludeComponent(
                        "bitrix:news.list",
                        "photo_wardrobes1",
                        array(
                            "ACTIVE_DATE_FORMAT" => "d.m.Y",
                            "ADD_SECTIONS_CHAIN" => "N",
                            "AJAX_MODE" => "N",
                            "AJAX_OPTION_ADDITIONAL" => "",
                            "AJAX_OPTION_HISTORY" => "N",
                            "AJAX_OPTION_JUMP" => "N",
                            "AJAX_OPTION_STYLE" => "N",
                            "CACHE_FILTER" => "N",
                            "CACHE_GROUPS" => "N",
                            "CACHE_TIME" => "36000000",
                            "CACHE_TYPE" => "A",
                            "CHECK_DATES" => "Y",
                            "DETAIL_URL" => "",
                            "DISPLAY_BOTTOM_PAGER" => "N",
                            "DISPLAY_DATE" => "N",
                            "DISPLAY_NAME" => "N",
                            "DISPLAY_PICTURE" => "Y",
                            "DISPLAY_PREVIEW_TEXT" => "N",
                            "DISPLAY_TOP_PAGER" => "N",
                            "FIELD_CODE" => array("PREVIEW_PICTURE", ""),
                            "FILTER_NAME" => "arFilter",
                            "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                            "IBLOCK_ID" => "60",
                            "IBLOCK_TYPE" => "aspro_max_content",
                            "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                            "INCLUDE_SUBSECTIONS" => "Y",
                            "MESSAGE_404" => "",
                            "NEWS_COUNT" => "20",
                            "PAGER_BASE_LINK_ENABLE" => "N",
                            "PAGER_DESC_NUMBERING" => "N",
                            "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                            "PAGER_SHOW_ALL" => "N",
                            "PAGER_SHOW_ALWAYS" => "N",
                            "PAGER_TEMPLATE" => ".default",
                            "PAGER_TITLE" => "Новости",
                            "PARENT_SECTION" => "",
                            "PARENT_SECTION_CODE" => "",
                            "PREVIEW_TRUNCATE_LEN" => "",
                            "PROPERTY_CODE" => array("", ""),
                            "SET_BROWSER_TITLE" => "N",
                            "SET_LAST_MODIFIED" => "N",
                            "SET_META_DESCRIPTION" => "N",
                            "SET_META_KEYWORDS" => "N",
                            "SET_STATUS_404" => "N",
                            "SET_TITLE" => "N",
                            "SHOW_404" => "N",
                            "SORT_BY1" => "ACTIVE_FROM",
                            "SORT_BY2" => "SORT",
                            "SORT_ORDER1" => "DESC",
                            "SORT_ORDER2" => "ASC",
                            "STRICT_SECTION_CHECK" => "N"
                        )
                    ); ?>
                </div>
                <div class="swiper-button-prev_works swiper-button-prev swiper-prev"></div>
                <div class="swiper-button-next_works swiper-button-next swiper-next"></div>
            </div>
            <div class="button-center">
                <button
                    class="btn btn-default btn-lg"
                    type="button" data-event="jqm"
                    data-param-form_id="WHICH_DRESSING_ROOM_NEED"
                    data-name="which_dressing_room_need">
                    Бесплатная консультация специалиста
                </button>
            </div>
        </div>
        <div class="work-stages mb-60">
            <h2>Этапы работы</h2>
            <div class="work-stages__wrapper">
                <div class="work-stages__items work-stages__items_desktop js-work-stages__items">
                    <? $APPLICATION->IncludeComponent(
                        "bitrix:main.include",
                        "",
                        array(
                            "AREA_FILE_SHOW" => "file",
                            "PATH" => $APPLICATION->GetCurDir() . '/includes/work-stages.php'
                        )
                    ); ?>
                </div>
                <div class="work-stages__items_mobile">
                    <div class="swiper work-stages__swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="work-stages__item">
                                    <div class="work-stages__item-number">01</div>
                                    <div class="work-stages__item-label">Создание дизайн-проекта</div>
                                    <div class="work-stages__item-text">Вместе с Вами выберем дизайн, ответим на все
                                        вопросы, подберем
                                        цвета и декоры, а после создадим проект, учитывающий каждую деталь и все
                                        особенности интерьера.
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="work-stages__item">
                                    <div class="work-stages__item-number">02</div>
                                    <div class="work-stages__item-label">Выезд на замер</div>
                                    <div class="work-stages__item-text">Сделаем точные замеры Вашего пространства и
                                        учтем все
                                        особенности помещения: это гарантия безупречной установки шкафа.
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="work-stages__item">
                                    <div class="work-stages__item-number">03</div>
                                    <div class="work-stages__item-label">Доставка и сборка</div>
                                    <div class="work-stages__item-text">Когда вам удобно произвести доставку и сборку
                                        мебели у Вас дома?
                                        Бережно доставим, поднимем в квартиру и аккуратно соберем мебель в удобное для
                                        Вас время.
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="work-stages__item">
                                    <div class="work-stages__item-number">04</div>
                                    <div class="work-stages__item-label">Гарантийное обслуживание</div>
                                    <div class="work-stages__item-text">Мы сможем помочь вам в любом вопросе по
                                        эксплуатации мебели в
                                        течение пяти лет, а также оказывать Вам постгарантийное обслуживание
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-button-prev_stages swiper-button-prev swiper-prev"></div>
                        <div class="swiper-button-next_stages swiper-button-next swiper-next"></div>
                    </div>
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
        <div class="page-note">*для удаленных регионов срок изготовления и доставки может быть увеличен до 45 дней</div>
    </div>
</div>

<link rel="stylesheet" href="<?= SITE_TEMPLATE_PATH ?>/css/wardrobes.css" />

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    (() => {
        const swiper = new Swiper('.ready-systems__swiper', {
            loop: true,
            breakpoints: {
                200: {
                    slidesPerView: 'auto',
                },
                320: {
                    spaceBetween: 18,
                    slidesPerView: 'auto',
                },
                992: {
                    spaceBetween: 25,
                    slidesPerView: 4
                },
            },
            navigation: {
                nextEl: '.swiper-button-next_ready',
                prevEl: '.swiper-button-prev_ready',
            },
        });
    })();
</script>
<script>
    (() => {
        const swiper = new Swiper('.sliding-systems__swiper', {
            loop: true,
            breakpoints: {
                320: {
                    spaceBetween: 18,
                    slidesPerView: 'auto'
                },
                992: {
                    spaceBetween: 25
                },
                1200: {
                    spaceBetween: 60,
                    slidesPerView: 3,
                }
            },
            navigation: {
                nextEl: '.swiper-button-next_sliding',
                prevEl: '.swiper-button-prev_sliding',
            },
        });
    })();
</script>
<script>
    (() => {
        const swiper = new Swiper('.work-stages__swiper', {
            spaceBetween: 20,
            slidesPerView: 'auto',
            navigation: {
                nextEl: '.swiper-button-next_stages',
                prevEl: '.swiper-button-prev_stages',
            },
        });
    })();
</script>
<script>
    (() => {
        const swiper = new Swiper('.our-works__swiper', {
            loop: true,
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
        const imageButtons = document.querySelectorAll('.js-image-button');
        const mapItems = document.querySelectorAll('.js-image-map-item');
        const mapImages = document.querySelectorAll('.js-map-image');

        imageButtons?.forEach(button => {
            let thisItem = button.closest('.js-image-map-item');
            let thisImage = thisItem.querySelector('.js-map-image');
            button.addEventListener('click', (e) => {
                e.preventDefault();
                let thisOpened = thisItem.classList.contains('active');
                mapItems?.forEach(item => {
                    item.classList.remove('active');
                });
                mapImages?.forEach(image => {
                    image.setAttribute('opacity', '0');
                });
                if (!thisOpened) {
                    thisItem.classList.add('active');
                    setTimeout(() => {
                        thisImage.setAttribute('opacity', '1');
                    }, 200);
                }
            });
        });
    })();
</script>

<script>
    (() => {
        const buyButtons = document.querySelectorAll('.js-buy-btn');

        buyButtons?.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = button.dataset.url;
            });
        });
    })();
</script>

<script>
    (() => {
        const tabButtons = document.querySelectorAll('.js-tab-btn');

        tabButtons?.forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll(`.js-tab-content`).forEach(item => {
                    item.classList.remove('active');
                });

                const target = button.getAttribute('data-target');
                const tabContent = document.querySelector(`.js-tab-content[data-content="${target}"]`);
                tabContent.classList.add('active');

                tabButtons.forEach(item => {
                    item.classList.remove('active');
                });
                button.classList.add('active');
            });
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