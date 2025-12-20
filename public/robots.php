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

	header('Content-Type: text/plain');
	header('Content-Length: '.filesize($file));
	header("Last-Modified: $tsstring");
    header("ETag: \"{$etag}\"");

	//readfile($file);


	$sRobotsContent = file_get_contents($file);

	if($hostname != 'www.e-1.ru') {
		$sRobotsContent = str_replace('www.e-1.ru', $hostname, $sRobotsContent);
	}

	echo $sRobotsContent;


	return true;
}

$robotsHost = dirname(__FILE__) . "/aspro_regions/robots/robots_{$hostname}.txt";
$robotsDefault = dirname(__FILE__) . "/robots.txt";

//if(!echoTextFile($robotsHost) && !echoTextFile($robotsDefault)) 
if(!echoTextFile($robotsDefault, $hostname)) 
{
	header('HTTP/1.0 404 Not Found');
}
