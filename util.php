<?php
require_once('config.php');

$date = gmdate('Y-m-d\TH:i:s\Z');
$healthcheckurl = "/v2/healthcheck?timestamp=" . $date;
$nearmeurl = "/v2/nearme/latitude/-37.82392124423254/longitude/144.9462017431463";
$stopsurl = "/v2/mode/2/line/787/stops-for-line";

function generateURLWithDevIDAndKey($url)
{
    global $config;
    
	// bake out base URL
	if (strpos($url, '?') > 0)
	{
		$url .= "&";
	}
	else
	{
		$url .= "?";
	}
	$url .= "devid=" . $config['devid'];
	
	// hash everything
	$signature = strtoupper(hash_hmac("sha1", $url, $config['key'], false));
	
	// mash it all together
	return "http://timetableapi.ptv.vic.gov.au" . $url . "&signature=" . $signature;
}
?>