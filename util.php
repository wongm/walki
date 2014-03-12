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

function distance($lat1, $long1, $lat2, $long2, $unit) {

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
?>