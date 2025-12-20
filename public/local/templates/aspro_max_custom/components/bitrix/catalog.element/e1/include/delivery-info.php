<?if(!empty($arRegion)):?>
	<p>
        <span class="product-pickup-btn"><i>Самовывоз</i></span>
	</p>
	<p>
        <span class="product-delivery-btn"><i>В <?=CITY_NAME_PP;?> доступна: Доставка <?=$templateData['QUANTITY_DATA']['IS_FOUND']?'от 24 часов':'от 14 дней'?></i></span>
    </p>
<?endif;?>