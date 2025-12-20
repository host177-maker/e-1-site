<?php
/**
 * @var array $listIblockTypes
 */
use Aspro\Max\Smartseo,
    Bitrix\Main\Localization\Loc;

Loc::loadMessages(Smartseo\General\Smartseo::getModulePath() . 'admin/index.php');

?>
<option disabled selected><?= Loc::getMessage('SMARTSEO_AI__PLACEHOLDER__CHOOSE_IBLOCK_TYPE') ?></option>
<? foreach ($listIblockTypes as $iblockType) : ?>
<option value="<?= $iblockType['ID'] ?>"><?= $iblockType['NAME'] ?></option>
<? endforeach ?>

