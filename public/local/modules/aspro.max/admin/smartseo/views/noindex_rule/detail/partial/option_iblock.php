<?php
/**
 * @var array $listIblocks
 */
use Aspro\Max\Smartseo,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(Smartseo\General\Smartseo::getModulePath() . 'admin/index.php');

?>
<option disabled selected><?= Loc::getMessage('SMARTSEO_AI__PLACEHOLDER__CHOOSE_IBLOCK') ?></option>
<? foreach ($listIblocks as $iblock) : ?>
<option value="<?= $iblock['ID'] ?>">[<?= $iblock['ID'] ?>] <?= $iblock['NAME'] ?></option>
<? endforeach ?>

