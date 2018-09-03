<?php
require_once('common/config.php');

if (!function_exists('json_decode')) {
    require_once('upgradephp/upgrade.php');
}

$date = gmdate('Y-m-d\TH:i:s\Z');
$healthcheckurl = "/v3/healthcheck?timestamp=" . $date;
$nearmeurl = "/v3/nearme/latitude/-37.82392124423254/longitude/144.9462017431463";
$stopsurl = "/v3/mode/2/line/787/stops-for-line";

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
    return "https://timetableapi.ptv.vic.gov.au" . $url . "&signature=" . $signature;
}

function getNearestPOI($poiType, $originLat, $originLong, $metresMaxWalkingDistance, $type)
{
    $baseURL = "/v3/$poiType/location/$originLat,$originLong?max_distance=$metresMaxWalkingDistance&$type";
    $signedUrl = generateURLWithDevIDAndKey($baseURL);
    $apiContent = makeHttpRequest($signedUrl);
    
    $json = json_decode($apiContent);
    
    $shortestDistance = 99999999;
    $nearestLocation = null;
    
    // no locations, we're in trouble!
    if ($json->stops == null && $json->outlets == null)
    {
        return;
    }
    
    foreach($json->stops as $location)
    {
        $thisDistance = $location->stop_distance;
        
        if ($thisDistance < $shortestDistance)
        {
            $shortestDistance = $thisDistance;
            $nearestLocation = $location;
        }
    }
    
    foreach($json->outlets as $location)
    {
        $thisDistance = $location->outlet_distance;
        
        if ($thisDistance < $shortestDistance)
        {
            $shortestDistance = $thisDistance;
            $nearestLocation = $location;
        }
    }

    $nearestLocation->distance = round($shortestDistance, 0);

    return $nearestLocation;
}

function makeHttpRequest($url)
{
    # Connect to the Web API using cURL.
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_TIMEOUT, '3'); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $xmlstr = curl_exec($ch); 
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return $xmlstr;
}

?>