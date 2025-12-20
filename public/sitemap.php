<?
$arHost = explode( ":", $_SERVER["HTTP_HOST"]);
$_SERVER["HTTP_HOST"] = $arHost[0];
$hostname = $_SERVER['HTTP_HOST'];

function echoTextFile($file, $hostname) {
	if (! file_exists($file)) return false;
	if (! is_readable($file)) return false;

	$timestamp = filemtime($file);
	$tsstring = gmdate('D, d M Y H:i:s ', $timestamp) . 'GMT';
	$etag = md5($file . $timestamp);

	header('Content-Type: application/xml');
	header("Last-Modified: $tsstring");
    header("ETag: \"{$etag}\"");

    $sSitemapContent = file_get_contents($file);
	$obSitemapXml = simplexml_load_string($sSitemapContent);

	foreach ($obSitemapXml->sitemap as $arSitemap) {
		$sSitemapPart = str_replace(array('https://e-1.ru'), '', $arSitemap->loc->__toString());
		$sSitemapPartContent = file_get_contents($_SERVER["DOCUMENT_ROOT"] . $sSitemapPart);
		$sSitemapPartContent = str_replace(array('<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', '</urlset>'), '', $sSitemapPartContent);
		$sSitemapContent = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $sSitemapContent);
		$sSitemapContent = trim($sSitemapContent);
		$sSitemapContent = preg_replace('/\s+/', ' ', $sSitemapContent);
		$sSitemapContent =  preg_replace('/[^\S\r\n]+/', ' ', $sSitemapContent);
		$sSitemapContent = str_replace('<sitemap><loc>' . $arSitemap->loc->__toString() . '</loc><lastmod>' . $arSitemap->lastmod->__toString() . '</lastmod><changefreq>' . $arSitemap->changefreq->__toString() . '</changefreq><priority>' . $arSitemap->priority->__toString() . '</priority></sitemap>', $sSitemapPartContent, $sSitemapContent);
	}

	$sSitemapContent = str_replace(
		array('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', '</sitemapindex>'), 
		array('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', '</urlset>'), 
		$sSitemapContent
	);
	echo $sSitemapContent;
	return true;
}

$sitemapDefault = dirname(__FILE__) . "/sitemap.xml";

if(!echoTextFile($sitemapDefault, $hostname)) 
{
	header('HTTP/1.0 404 Not Found');
}
