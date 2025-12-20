<?php
/**
 * @var array $listIblockTypes
 */
use Bitrix\Main\Localization\Loc;
?>
<option disabled selected><?= Loc::getMessage('SMARTSEO_FORM_PLACEHOLDER_IBLOCK_TYPE') ?></option>
<? foreach ($listIblockTypes as $iblockType) : ?>
<option value="<?= $iblockType['ID'] ?>"><?= $iblockType['NAME'] ?></option>
<? endforeach ?>

