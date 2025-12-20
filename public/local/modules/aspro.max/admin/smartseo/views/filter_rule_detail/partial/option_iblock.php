<?php
/**
 * @var array $listIblocks
 */
use Bitrix\Main\Localization\Loc;
?>
<option disabled selected><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_IBLOCK') ?></option>
<? foreach ($listIblocks as $iblock) : ?>
<option value="<?= $iblock['ID'] ?>">[<?= $iblock['ID'] ?>] <?= $iblock['NAME'] ?></option>
<? endforeach ?>

