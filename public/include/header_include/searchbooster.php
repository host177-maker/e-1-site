<?php
 $anmartoWidgetCode = 'VUx6rvY7Pw';
 $anmartoRemoteUrl = 'https://search.anmarto.com/s/' . $anmartoWidgetCode . '.js';
 $anmartoProxyFlag = 'anmarto_sb_proxy';

 if (isset($_GET[$anmartoProxyFlag]) && (string)$_GET[$anmartoProxyFlag] === '1') {
     header('Content-Type: application/javascript; charset=UTF-8');
     header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
     header('Pragma: no-cache');

     $anmartoContext = stream_context_create([
         'http' => [
             'timeout' => 5,
             'header' => "User-Agent: anmarto-widget-proxy\r\n",
         ],
         'https' => [
             'timeout' => 5,
             'header' => "User-Agent: anmarto-widget-proxy\r\n",
         ],
     ]);

     $anmartoJs = @file_get_contents($anmartoRemoteUrl, false, $anmartoContext);
     if (!is_string($anmartoJs) || $anmartoJs === '') {
         echo "(function(){try{console.error('Anmarto widget: failed to load remote script');}catch(e){}})();\n";
         exit;
     }

     $anmartoJs = str_replace('</script', '<\\/script', $anmartoJs);
     echo "(function(){\n" .
         "var __anmartoOld$ = window.$;\n" .
         "var __anmartoOldjQuery = window.jQuery;\n" .
         "try{\n" .
         $anmartoJs . "\n" .
         "try {\n" .
         "  if (typeof Jt === 'function') {\n" .
         "    try { window.removeEventListener('load', Jt); } catch(e) {}\n" .
         "    try { document.removeEventListener('DOMContentLoaded', Jt); } catch(e) {}\n" .
         "    try { Jt(); } catch(e) {}\n" .
         "  }\n" .
         "} catch(e) {}\n" .
         "} finally {\n" .
         "if (window.$ !== __anmartoOld$) window.$ = __anmartoOld$;\n" .
         "if (window.jQuery !== __anmartoOldjQuery) window.jQuery = __anmartoOldjQuery;\n" .
         "}\n" .
         "})();\n";
     exit;
 }

 $anmartoScriptSrc = '/include/header_include/searchbooster.php?' . $anmartoProxyFlag . '=1';
 ?>
 <div class="an-search--widget"
      style="font-family: Arial, sans-serif; font-weight: 300; font-size: 16px; color: rgb(0, 0, 0);">
     <div id="anmarto-search-widget-container"><noscript>Please enable JavaScript</noscript></div>
     <?php if ($anmartoScriptSrc): ?>
         <script src="<?=htmlspecialchars((string)$anmartoScriptSrc, ENT_QUOTES)?>" async></script>
     <?php endif; ?>
 </div>

<style>
    .an-search--widget {
        position:relative;
        align-self:start;
        width:100%!important;
        height:100%!important;
        max-width:100%!important;
        padding:0;
        margin:0;
        box-sizing:border-box
    }
    .an-search--widget * {
        padding:0;
        margin:0;
        box-sizing:border-box
    }
    .an-search--widget img {
        max-width:100%;
        display:block
    }
    .an-search--widget a {
        text-decoration:none;
        cursor:pointer;
        color:inherit
    }
    .an-search--widget button,
    .an-search--widget input,
    .an-search--widget a {
        font-family:inherit
    }
    .an-search--widget li {
        list-style:none
    }
    .an-search--widget li:before,
    .an-search--widget li:after {
        display:none!important
    }
    .an-search--widget button {
        cursor:pointer
    }
    .an-search--widget__search-container {
        padding:8px
    }
    .an-search--widget__search-container input {
        flex-grow:1;
        width:100%;
        height:43px;
        padding-left:12px;
        padding-right:42px;
        border:none;
        outline:none;
        font-family:inherit;
        font-size:inherit;
        font-weight:inherit;
        color:inherit
    }
    .an-search--widget__search-container input::placeholder {
        color:var(--placeholderColor)
    }
    .an-search--widget__search-inner {
        display:flex;
        align-items:center
    }
    .an-search--widget__input-wrapper {
        position:relative;
        flex-grow:1;
        display:flex;
        align-items:center;
        gap:5px;
        border-width:2px;
        border-style:solid;
        background-color:#fff
    }
    .an-search--widget__input-wrapper .an-search--widget__clear-btn {
        position:absolute;
        top:50%;
        right:12px;
        transform:translateY(-50%)
    }
    .an-search--widget__clear-btn {
        padding:0;
        border:none;
        background-color:transparent;
        flex-shrink:0;
        display:flex;
        align-items:center;
        justify-content:center;
        width:20px;
        height:20px;
        cursor:pointer
    }
    .an-search--widget__results-page-link {
        padding:9px 16px
    }
    .an-search--widget__search-filters {
        display:flex;
        align-items:center;
        gap:9px;
        flex-wrap:wrap;
        padding:10px 12px 8px
    }
    .an-search--widget__search-filters button {
        padding:6px 12px;
        background-color:#f5f7fa;
        border-radius:8px
    }
    .an-search--widget__results {
        position:absolute;
        top:calc(100% + 16px);
        left:0;
        right:0;
        display:grid;
        gap:10px;
        max-height:50vh;
        overflow-y:auto;
        padding:16px;
        scrollbar-width:thin;
        scrollbar-color:#888 #f1f1f1
    }
    .an-search--widget__results ul {
        display:grid;
        grid-template-columns:100%;
        gap:10px
    }
    .an-search--widget__result-link {
        display:flex;
        align-items:center;
        justify-content:space-between;
        min-height:30px;
        gap:20px;
        padding:4px 10px 4px 4px;
        border-radius:12px
    }
    .an-search--widget__result-link .an-search--widget__clear-btn {
        display:none
    }
    @media (any-hover: hover) {
        .an-search--widget__result-link:hover {
            background-color:#f5f7fa
        }
        .an-search--widget__result-link:hover .an-search--widget__clear-btn {
            display:flex
        }
    }
    .an-search--widget__result-link_history {
        cursor:pointer
    }
    .an-search--widget__result-icon {
        flex-shrink:0;
        display:flex;
        align-items:center;
        justify-content:center;
        width:44px;
        height:44px;
        border-radius:8px;
        overflow:hidden;
        background-color:#f5f7fa
    }
    .an-search--widget__result-icon svg,
    .an-search--widget__result-icon img {
        object-fit:contain
    }
    .an-search--widget__result-text {
        margin-right:auto
    }
</style>