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

function getOffsetLocationBounds($lat, $long, $difference)
{
	// http://gis.stackexchange.com/questions/2951/algorithm-for-offsetting-a-latitude-longitude-by-some-amount-of-meters
	
	//Earth's radius, sphere
	$radius = 6378137;
	
	//Coordinate offsets in radians
	$dLat = $difference / $radius;
	$dLong = -$difference / ($radius * Cos(Pi() * ($lat / 180)));
	
	//OffsetPosition, decimal degrees
	$bounds->lat1 = $lat + $dLat * 180 / Pi();
	$bounds->long1 = $long + $dLong * 180 / Pi();
	
	//Coordinate offsets in radians
	$dLat = -$difference / $radius;
	$dLong = $difference / ($radius * Cos(Pi() * ($lat / 180)));
	
	//OffsetPosition, decimal degrees
	$bounds->lat2 = $lat + $dLat * 180 / Pi();
	$bounds->long2 = $long + $dLong * 180 / Pi();
	
	return $bounds;
}

function calculateDistance($lat1, $long1, $lat2, $long2, $unit) {

  $theta = $long1 - $long2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
}

function getNearestPOI($poiType, $originLat, $originLong, $bounds)
{
	$griddepth = 3;
	$limit = 100;
	
	$baseURL = "/v2/poi/$poiType/lat1/$bounds->lat1/long1/$bounds->long1/lat2/$bounds->lat2/long2/$bounds->long2/griddepth/$griddepth/limit/$limit";
	
	$signedUrl = generateURLWithDevIDAndKey($baseURL);
	$apiContent = makeHttpRequest($signedUrl);
	
	require_once('upgradephp/upgrade.php');
	
	$json = json_decode($apiContent);
	
	$shortestDistance = 99999999;
	$nearestLocation = null;
	
	// no locations, we're in trouble!
	if ($json->locations == null)
	{
		return;
	}
	
	foreach($json->locations as $location)
	{
		$thisDistance = calculateDistance($originLat, $originLong, $location->lat, $location->lon, 'k');
		
		if ($thisDistance < $shortestDistance)
		{
			$shortestDistance = $thisDistance;
			$nearestLocation = $location;
		}
	}
	
	$nearestLocation->distance = round($shortestDistance * 1000, 0);

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