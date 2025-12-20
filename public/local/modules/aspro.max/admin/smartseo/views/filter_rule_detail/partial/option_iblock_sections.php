<?php
/**
 * @var array $listIblockSections
 */
use Bitrix\Main\Localization\Loc;
?>
<option disabled><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_IBLOCK_SECTIONS') ?></option>
<? foreach ($listIblockSections as $section) : ?>
<option value="<?= $section['ID'] ?>"><?= str_repeat(' . ', (int) $section['DEPTH_LEVEL']) ?> <?= $section['NAME'] ?></option>
<? endforeach ?>

