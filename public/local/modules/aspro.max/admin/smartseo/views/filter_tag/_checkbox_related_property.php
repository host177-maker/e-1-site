<?php
/**
 *  @var string $alias
 *  @var array $listIdenticalProperty
 *  @var array $dataRelatedProperty
 */

use Aspro\Max\Smartseo,
    Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$suffix = $this->getUnique();

?>
<? if($listIdenticalProperty) : ?>
    <? foreach ($listIdenticalProperty as $propertyId => $propertyName) : ?>
        <?
        $_selected = in_array($propertyId, $dataRelatedProperty);
        ?>
        <div class="aspro-smartseo__form-control__checkbox">
          <input id="<?= $alias ?>_RELATED_PROPERTY_<?= $propertyId . $suffix ?>" class="adm-designed-checkbox" type="checkbox"
                 name="<?= $alias ?>[RELATED_PROPERTY][]"
                 <?= $_selected ? 'checked' : '' ?>
                 value="<?= $propertyId ?>">
          <label class="adm-designed-checkbox-label" for="<?= $alias ?>_RELATED_PROPERTY_<?= $propertyId . $suffix ?>" title="<?= Loc::getMessage('SMARTSEO_FORM_ENTITY_RELATED_PROPERTY') ?>">
            <?= $propertyName ?>
          </label>
        </div>
    <? endforeach ?>
<? else : ?>
    <span style="font-size: 10px;"><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_NOT_RELATED_PROPERTY') ?></span>
<? endif ?>