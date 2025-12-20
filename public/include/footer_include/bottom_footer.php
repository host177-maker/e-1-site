<?global $APPLICATION, $arRegion, $arSite, $arTheme, $bIndexBot, $is404, $isForm, $isIndex;?>

<div class="bx_areas">
	<?CMax::ShowPageType('bottom_counter');?>
</div>
<?CMax::ShowPageType('search_title_component');?>
<?CMax::setFooterTitle();
CMax::showFooterBasket();?>
<div id="popup_iframe_wrapper"></div>
<?/*
			<div class="popup-city__selection">
                <div class="popup-city__container">
                  <div class="popup-city__content">
                    <div class="popup-city__close"></div>
                    <div class="popup-city__question">
                        <div class="popup-city__icon">
                        <svg width="21" height="29" viewBox="0 0 21 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M1.83748 9.775C1.83748 4.99083 5.71581 1.1125 10.5 1.1125C15.2841 1.1125 19.1625 4.99083 19.1625 9.775C19.1625 12.0644 18.0733 15.3075 16.4398 18.6184C14.8152 21.9115 12.6919 25.1924 10.7034 27.5393C10.5946 27.6677 10.4053 27.6677 10.2966 27.5393C8.30804 25.1924 6.18477 21.9115 4.56013 18.6184C2.92667 15.3075 1.83748 12.0644 1.83748 9.775ZM10.5 0.0625C5.13591 0.0625 0.787476 4.41093 0.787476 9.775C0.787476 12.3172 1.97014 15.7419 3.61849 19.083C5.27565 22.4419 7.44415 25.7971 9.49545 28.2181C10.0236 28.8415 10.9763 28.8415 11.5045 28.2181C13.5558 25.7971 15.7243 22.4419 17.3815 19.083C19.0298 15.7419 20.2125 12.3172 20.2125 9.775C20.2125 4.41093 15.864 0.0625 10.5 0.0625ZM6.01423 10.6101C6.01423 8.13239 8.02285 6.12378 10.5006 6.12378C12.9783 6.12378 14.987 8.13239 14.987 10.6101C14.987 13.0879 12.9783 15.0965 10.5006 15.0965C8.02285 15.0965 6.01423 13.0879 6.01423 10.6101ZM10.5006 5.07378C7.44295 5.07378 4.96423 7.55249 4.96423 10.6101C4.96423 13.6678 7.44295 16.1465 10.5006 16.1465C13.5582 16.1465 16.037 13.6678 16.037 10.6101C16.037 7.55249 13.5582 5.07378 10.5006 5.07378Z" fill="#62BB46"/>
                        </svg>
                         </div>
                         <div class="popup-city__question-text"></div>
                    </div>

                    <div class="popup-city__buttons">
                        <button class="popup-city__button-confirm">Да, верно</button>
                        <button class="popup-city__button-negative">Нет, другой</button>
                    </div>
                </div>
                </div>
			</div>*/?>


<?include_once('bottom_footer_custom.php');?>