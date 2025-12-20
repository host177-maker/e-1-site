<form action="<?echo $APPLICATION->GetCurPage()?>" name="form1">
<?=bitrix_sessid_post()?>
<input type="hidden" name="lang" value="<?echo LANG?>">
<input type="hidden" name="id" value="zamit.sitemap">
<input type="hidden" name="install" value="Y">
<input type="hidden" name="step" value="2">
	<?if (CModule::IncludeModule('cluster')):?>
	<p><?echo GetMessage("SITEMAP_INSTALL_DATABASE")?><select name="DATABASE">
		<option value=""><?echo GetMessage("SITEMAP_MAIN_DATABASE")?></option><?php
        $rsDBNodes = CClusterDBNode::GetListForModuleInstall();
        while ($arDBNode = $rsDBNodes->Fetch()):
        ?><option value="<?echo $arDBNode["ID"]?>"><?echo htmlspecialchars($arDBNode["NAME"])?></option><?php
        endwhile;
        ?></select></p>
	<br>
	<?endif;?>
	<input type="submit" name="inst" value="<?= GetMessage("MOD_INSTALL")?>">
</form>