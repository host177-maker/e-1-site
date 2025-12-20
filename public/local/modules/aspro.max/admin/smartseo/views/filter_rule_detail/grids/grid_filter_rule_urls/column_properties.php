<?php

/**
 *  @var array $item
 */
use Bitrix\Main\Localization\Loc;
?>

<? foreach ($row['PROPERTIES'] as $property) : ?>
    <? if(!$property['VALUES']) : ?>
        <? continue; ?>
    <? endif ?>
    <div class="aspro-smartseo__condition__item aspro-smartseo__condition__item--sm">
      <div class="aspro-smartseo__condition__name">
        <?= $property['PROPERTY_NAME'] ?> [<?= $property['PROPERTY_ID'] ?>]
      </div>
      <div class="aspro-smartseo__condition__value">
        <div class="aspro-smartseo__condition__logic-group__values">
          <? if($property['PROPERTY_TYPE'] == 'PRICE' || $property['PROPERTY_TYPE'] == 'N') : ?>
            <? if(isset($property['VALUES']['DISPLAY']['MIN'])) : ?>
                <span class="aspro-smartseo__condition__logic-group__label"><?= Loc::getMessage('SMARTSEO_GRID_URL_EGR') ?></span>
                <span><?= $property['VALUES']['DISPLAY']['MIN'] ?></span>
            <? endif ?>
            <? if(isset($property['VALUES']['DISPLAY']['MAX'])) : ?>
                <span class="aspro-smartseo__condition__logic-group__label"><?= Loc::getMessage('SMARTSEO_GRID_URL_ELS') ?></span>
                <span><?= $property['VALUES']['DISPLAY']['MAX'] ?></span>
            <? endif ?>
          <? else : ?>
          <span class="aspro-smartseo__condition__logic-group__label"><?= Loc::getMessage('SMARTSEO_GRID_URL_EQUALLY') ?></span>
          <span><?= implode(', ', $property['VALUES']['DISPLAY']) ?></span>
          <? endif ?>
        </div>
      </div>
    </div>
<? endforeach ?>