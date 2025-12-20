<?
/**
 * @var array $chainSections
 */
use Aspro\Max\Smartseo\Admin\Helper,
    Bitrix\Main\Localization\Loc;

$adminUiChain = new CAdminChain('smartseo_filter_rules_chain', true);

$adminUiChain->AddItem([
    'TEXT' => htmlspecialcharsbx(Loc::getMessage('SMARTSEO_INDEX__TITLE__FILTER_RULE')),
    'LINK' => $chainSections ? Helper::url('filter_rules/list') : '',
    'ONCLICK' => '',
    'ID' => 'filter_nav_chain',
    'MENU_URL' => Helper::url('filter_nav_chain/get_menu', [
        'depth_level' => 1
    ]),
]);

$i = 1;
$lastIndex = count($chainSections);
foreach ($chainSections as $chainItem) {
    if ($i == $lastIndex) {
        $adminUiChain->AddItem([
            'TEXT' => htmlspecialcharsbx($chainItem['NAME']),
            'LINK' => Helper::url('filter_rules/list', ['section_id' => $chainItem['ID']]),
        ]);
    } else {
        $adminUiChain->AddItem([
            'TEXT' => htmlspecialcharsbx($chainItem['NAME']),
            'LINK' => Helper::url('filter_rules/list', ['section_id' => $chainItem['ID']]),
            'ONCLICK' => '',
            'ID' => 'filter_nav_chain_' . $chainItem['NAME'],
            'MENU_URL' => Helper::url('filter_nav_chain/get_menu', [
                'depth_level' => $chainItem['DEPTH_LEVEL'] + 1,
                'section_id' => $chainItem['ID']
            ]),
        ]);
    }

    $i++;
}

?>
<div class="adm-navchain"<?= ($adminUiChain->id ? ' id="' . $adminUiChain->id . '"' : '') . ($adminUiChain->bVisible == false ? ' style="display:none;"' : '') ?>>
  <? $countAdminChain = count($adminUiChain->items) - 1; ?>
  <? $adminChainScripts = ''; ?>
  <? foreach ($adminUiChain->items as $n => $item) : ?>
      <?
      $_className = !empty($item['CLASS']) ? ' ' . htmlspecialcharsbx($item['CLASS']) : '';
      $_text = htmlspecialcharsbx(htmlspecialcharsback($item['TEXT']));

      ?>
      <? if (!empty($item['LINK'])) : ?>
          <? $_link = htmlspecialcharsbx($item['LINK'], ENT_COMPAT, false); ?>
          <a class="adm-navchain-item"
             href="<?= $_link ?>"
             <? $item['ONCLICK'] ? 'onclick="' . htmlspecialcharsbx($item['ONCLICK']) . '"' : '' ?>>
            <span class="adm-navchain-item-text <?= $_className ?>">
              <?= $_text ?>
            </span>
          </a>
      <? elseif (!empty($item['MENU_URL'])) : ?>
          <a href="javascript:void(0)" class="adm-navchain-item" id="bx_admin_chain_item_<?= $item['ID'] ?>">
            <span class="adm-navchain-item-text <?= $_className ?>">
              <?= $_text ?>
            </span>
          </a>
          <?
          $adminChainScripts .= 'new BX.COpener(' . CUtil::PhpToJsObject([
                'DIV' => 'bx_admin_chain_item_' . $item['ID'],
                'ACTIVE_CLASS' => 'adm-navchain-item-active',
                'MENU_URL' => $item['MENU_URL']
            ]) . ');';

          ?>
      <? else : ?>
          <span class="adm-navchain-item adm-navchain-item-empty <?= $_className ?>"><span class="adm-navchain-item-text"><?= $_text ?></span></span>
      <? endif ?>

      <? if ($n < $countAdminChain) : ?>
          <? if ($item['MENU_URL']) : ?>
              <span class="adm-navchain-item" id="bx_admin_chain_delimiter_<?= $item['ID'] ?>"><span class="adm-navchain-delimiter"></span></span>
              <?
              $adminChainScripts .= 'new BX.COpener(' . CUtil::PhpToJsObject([
                    'DIV' => 'bx_admin_chain_delimiter_' . $item['ID'],
                    'ACTIVE_CLASS' => 'adm-navchain-item-active',
                    'MENU_URL' => $item['MENU_URL']
                ]) . ');';

              ?>
          <? else : ?>
              <span class="adm-navchain-delimiter"></span>
          <? endif ?>
      <? endif ?>
  <? endforeach ?>
</div>
<script type="text/javascript">
<?= $adminChainScripts ?>
</script>