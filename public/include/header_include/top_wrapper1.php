<?
global $APPLICATION, $arRegion, $arSite, $arTheme, $bIndexBot;
?>

<?CMax::ShowPageType('mega_menu');?>

<?CMax::get_banners_position('TOP_HEADER');?>

<div class="header_wrap visible-lg visible-md title-v<?=$arTheme["PAGE_TITLE"]["VALUE"];?><?=($isIndex ? ' index' : '')?> <?$APPLICATION->AddBufferContent(array('CMax', 'getBannerClass'))?>">
	<header id="header">
		<?CMax::ShowPageType('header');?>
	</header>
</div>
<?CMax::get_banners_position('TOP_UNDERHEADER');?>

<?if($arTheme["TOP_MENU_FIXED"]["VALUE"] == 'Y'):?>
	<div id="headerfixed">
		<?CMax::ShowPageType('header_fixed');?>
	</div>
<?endif;?>

<div id="mobileheader" class="visible-xs visible-sm">
	<?CMax::ShowPageType('header_mobile');?>
	<div id="mobilemenu" class="<?=($arTheme["HEADER_MOBILE_MENU_OPEN"]["VALUE"] == '1' ? 'leftside':'dropdown')?>">
		<?CMax::ShowPageType('header_mobile_menu');?>
	</div>
</div>
    <div class="search_header">
        <div id="anmarto-search-widget-container">
            <?php $APPLICATION->IncludeFile('/include/header_include/searchbooster.php'); ?>
        </div>
    </div>
    <style>
        .search_wrap.pull-left{
            width: 450px;
            visibility: visible !important;
            opacity: 0 !important;
        }
        .wrapper1 .search_header{
            z-index: 100;
        }
        @media (max-width: 991.98px) {
            .search_header {
                position: relative !important;
                width: 100% !important;
                left: 0 !important;
                top: 0 !important;
                height: auto !important;
            }
        }
        #anmarto-search-widget-container{
            align-items: center;
            display: block;
            justify-content: center;
            flex-grow: 1;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            positionSearchHeader();
        });
        function positionSearchHeader() {
            var searchWrapper = document.querySelector('.search-wrapper');
            var searchHeader = document.querySelector('.search_header');
            if (!searchWrapper || !searchHeader) return;

            // Скрыть search-wrapper
            searchWrapper.style.opacity = 0;

            if (window.innerWidth < 992) {
                // Мобильный режим
                searchHeader.style.position = 'relative';
                searchHeader.style.width = '100%';
                searchHeader.style.left = '';
                searchHeader.style.top = '';
                searchHeader.style.height = '';
            } else {
                // Десктоп
                var rect = searchWrapper.getBoundingClientRect();
                var scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

                searchHeader.style.position = 'absolute';
                searchHeader.style.top = (rect.top + scrollTop) + 'px';
                searchHeader.style.left = (rect.left + scrollLeft) + 'px';
                searchHeader.style.width = rect.width + 'px';
                searchHeader.style.height = rect.height + 'px';
                searchHeader.style.zIndex = 1000;
            }
        }

        positionSearchHeader();
        window.addEventListener('resize', positionSearchHeader);
        window.addEventListener('scroll', positionSearchHeader);
    </script>
<div id="mobilefilter" class="scrollbar-filter"></div>

<?$APPLICATION->ShowViewContent('section_bnr_top_content');?>

<?include_once('top_wrapper1_custom.php');?>