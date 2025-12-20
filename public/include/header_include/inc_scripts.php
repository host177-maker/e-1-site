<?global $APPLICATION;?>
<script type="text/javascript">!function(){var t=document.createElement("script");t.type="text/javascript",t.async=!0,t.src="https://vk.com/js/api/openapi.js?160",t.onload=function(){VK.Retargeting.Init("VK-RTRG-333568-aT9Ur"),VK.Retargeting.Hit()},document.head.appendChild(t)}();</script><noscript><img src="https://vk.com/rtrg?p=VK-RTRG-333568-aT9Ur" style="position:fixed; left:-999px;" alt=""/></noscript>

<?/*<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '130197989161029');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=130197989161029&ev=PageView&noscript=1"
/></noscript>*/?>

<?/*<script type="text/javascript" src="//event.getblue.io/js/blue-tag.min.js" async="true" data-type="getblue"></script>*/?>
<?//=$APPLICATION->AddBufferContent(['COrwoFunctions', 'ShowAddidgitalJsScripts']);?>
<script type="text/javascript"  data-skip-moving="true">
window.productPageView = false;
window.blue_q = window.blue_q || [];
/* $(document).ready(function(){
  if(!window.productPageView){
    window.blue_q.push(
      {event: "setCampaignId", value: "00817ED8-9D92-C9B7-14D7CD0015C22D53"}
      ,{event: "setPageType", value: "visit"}
    );
  }
})*/;
<?/*window.blue_q = window.blue_q || [];
window.blue_q.push(
	{event: "setCampaignId", value: "00817ED8-9D92-C9B7-14D7CD0015C22D53"}
	,{event: "setPageType", value: "visit"}
);*/?>
</script>
<script type="text/javascript" data-skip-moving=true>
var __cs = __cs || [];
__cs.push(["setCsAccount", "bMgsWU1yiUUbmG3XBPYxBdYV2Gv4rYGd"]);
</script>
<?/* закомментил comagic */?>
<?/*<script type="text/javascript" async src="https://app.comagic.ru/static/cs.min.js" data-skip-moving="true"></script>*/?>

<script type="text/javascript" data-skip-moving="true">
if(!window.gdeslon_q||window.gdeslon_q instanceof Array){var hasPerformance="undefined"!==typeof performance&&"function"===typeof performance.now;var perf=hasPerformance?performance.now():null;var oldQueue=window.gdeslon_q||[];window.gdeslon_q=function(){var _exceptions=[],_state={},appendScript=function(url){var gss=document.createElement("script");gss.type="text/javascript";gss.async=true;gss.src=url;var s=document.getElementsByTagName("script")[0];s.parentNode.insertBefore(gss,s)},serializeObject=function(obj){return Object.keys(obj).map(function(key){return encodeURIComponent(key)+"="+encodeURIComponent(obj[key])}).join("&")},deserializeObject=function(str,pairsSeparator,keyValueSeparator){var result={},pairs,pair,key,value,i,l;if(!keyValueSeparator){keyValueSeparator="="}if(!str){return result}pairs=str.split(pairsSeparator);for(i=0,l=pairs.length;i<l;i++){pair=pairs[i].replace(/^\s+|\s+$/g,"").split(keyValueSeparator);try{key=decodeURIComponent(pair[0]);value=decodeURIComponent(pair[1]);result[key]=value}catch(e){console.log(e.message)}}return result},location=function(){return document.location}(),domain=function(){var domain=location.hostname||location.host.split(":")[0];var domainParts=domain.split(".");var l=domainParts.length;if(l>1){domain=domainParts[l-2]+"."+domainParts[l-1]}return domain}(),queryParams=function(){return deserializeObject(location.search.slice(1),"&")}(),cookieTtl=function(){var cookieTtl=parseInt(queryParams._gs_cttl,10);if(!cookieTtl||isNaN(cookieTtl)){cookieTtl=180}return cookieTtl}(),writeCookie=function(name,value,ttlSeconds){if(!(name&&value)){return}value=encodeURIComponent(value);var ttl=ttlSeconds||cookieTtl*24*60*60;var date=new Date;date.setTime(date.getTime()+ttl*1e3);var expires="; expires="+date.toUTCString();var domainParam="domain="+domain+"; ";document.cookie=name+"="+value+expires+"; "+domainParam+"path=/;"},cookies=function(key){return deserializeObject(document.cookie,";")[key]},token=function(){return cookies("gdeslon.ru.__arc_token")},affiliate_id=function(){return cookies("gdeslon.ru.__arc_aid")},track_domain=function(){return cookies("gdeslon.ru.__arc_domain")||"gdeslon.ru"},pixel_domain=function(){return cookies("gdeslon.ru.__arc_gsp_domain")||"gdeslon.ru"},gs_uid=function(){return cookies("gdeslon.ru.user_id")},processor=function(){_state.pushStartedAt=Date.now();var pixel=[];var track=[];if(arguments.length===0){return}var obj=arguments[0];var shouldInvokeTrack=false;Object.keys(obj).forEach(function(key){var val=obj[key];var same="";switch(key){case"page_type":pixel.mode=val;break;case"merchant_id":pixel.mid=val;track.merchant_id=val;break;case"category_id":pixel.cat_id=val;track.cat_id=val;break;case"products":if(!val||val.constructor!==Array)break;same=val.map(function(l){var repeats=[];for(var i=0;i<parseFloat(l.quantity);i++){repeats.push(l.id+":"+parseFloat(l.price))}return repeats.join(",")});pixel.codes=same;track.codes=same;break;case"user_id":pixel.muid=val;track.muid=val;break;default:pixel[key]=val;track[key]=val;break}});if(obj.page_type==="thanks"){if(obj.hasOwnProperty("deduplication")){if(Object.prototype.toString.call(obj.deduplication)==="[object String]"){var trueArr=["gdeslon_cpa","gdeslon","gde slon","","undefined","null","true","1"];shouldInvokeTrack=trueArr.indexOf(obj.deduplication.toLowerCase())>-1}else{shouldInvokeTrack=true}}else{shouldInvokeTrack=true}}pixel.perf=parseInt(perf,10);track.perf=pixel.perf;pixel.gs_uid=gs_uid();track.gs_uid=pixel.gs_uid;pixel._t=Date.now();track._t=Date.now();pixel.source=window.location.href;var url="//"+pixel_domain()+"/gsp.js?"+serializeObject(pixel);appendScript(url);if(shouldInvokeTrack){_state.shouldInvokeTrack=true;track.affiliate_id=affiliate_id();track.token=token();url="//"+track_domain()+"/purchase.js?"+serializeObject(track);appendScript(url)}else{_state.shouldInvokeTrack=false}_state.pushFinishedAt=Date.now()},_push=function(){try{return processor.apply(null,arguments)}catch(c){_exceptions.push(c);var url="https://gdeslon.ru/error.js?"+serializeObject({message:c.message});appendScript(url)}};if(queryParams.gsaid){writeCookie("gdeslon.ru.__arc_aid",queryParams.gsaid)}if(queryParams._gs_ref){writeCookie("gdeslon.ru.__arc_token",queryParams._gs_ref)}if(queryParams._gs_vm){writeCookie("gdeslon.ru.__arc_domain",queryParams._gs_vm)}if(queryParams._gs_ld){writeCookie("gdeslon.ru.__arc_gsp_domain",queryParams._gs_ld)}return{push:_push,exceptions:_exceptions,state:_state}}();window.gdeslon_q.push.apply(null,oldQueue)}
/* Отслеживание utm_source gdeslon */
function setCookie(name, value, time) {
    var expires = "";
    if (time) {
        var date = new Date();
        date.setTime(date.getTime() + time);
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
function gdeSlonUtm(){
  const getParam = (param) => {
  const urlParams = new URL(window.location.toString()).searchParams
    return urlParams.get(param) || ''
  }
  const utm = getParam('utm_source');
  const cookieUtm = getCookie('utm_source_gdeslon');
  if (!cookieUtm && utm){
    setCookie('utm_source_gdeslon', utm, 3600);
  }
  else if (!cookieUtm){
    setCookie('utm_source_gdeslon', '<?=$_COOKIE["UTM_SOURCE_COOKIE"];?>', 3600);
  } 
  if(utm && cookieUtm != utm){
    setCookie('utm_source_gdeslon', utm, 3600);
  }
  
return getCookie('utm_source_gdeslon');
}
console.log('footer' + gdeSlonUtm())
</script>
<?/*
<script data-skip-moving="true">
    !function (w, d, t) {
        w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++
        )ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script");n.type="text/javascript",n.async=!0,n.src=i+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};

        ttq.load('C8GAIVU5JLPVEHN4LARG');
        ttq.page();
    }(window, document, 'ttq');
</script>
*/?>
<script type="text/javascript">window._ab_id_=155231</script>
<script src="https://cdn.botfaqtor.ru/one.js"></script>