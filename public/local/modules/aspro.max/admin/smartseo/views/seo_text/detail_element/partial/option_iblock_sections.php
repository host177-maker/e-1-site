<?php
/**
 * @var array $listIblockSections
 */
use Aspro\Max\Smartseo,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(Smartseo\General\Smartseo::getModulePath() . 'admin/index.php');

?>
<option disabled><?= Loc::getMessage('SMARTSEO_AI__PLACEHOLDER__CHOOSE_IBLOCK_SECTIONS') ?></option>
<? foreach ($listIblockSections as $section) : ?>
<option value="<?= $section['ID'] ?>"><?= str_repeat(' . ', (int) $section['DEPTH_LEVEL']) ?> <?= $section['NAME'] ?></option>
<? endforeach ?>

