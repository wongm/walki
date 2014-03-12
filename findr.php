<?php
include_once('util.php');


$originLat = $_GET['lat'];
$originLong = $_GET['long'];

if (strlen($originLat) < 1)
{
	$originLat = -37.7796654;
}
if (strlen($originLong) < 1)
{
	$originLong = 144.917969;
}

// get the bounds
$boundsOrigin = getOffsetLocationBounds($originLat, $originLong, 500);

// display bounds
$url = "http://maps.googleapis.com/maps/api/staticmap?size=600x600&maptype=roadmap&markers=color:blue%7Clabel:T%7C$bounds->lat1,$bounds->long1&markers=color:green%7Clabel:G%7C$lat,$long&markers=color:red%7Clabel:B%7C$bounds->lat2,$bounds->long2&sensor=false";

//echo "<img src=\"$url\" />";


$poiTickets = 100;
$poiTrams = 1;

$markerOrigin = "&markers=color:blue%7Clabel:O%7C$originLat,$originLong";

// nearest tram
//$locationNearestTram = getNearestPOI($poiTrams, $originLat, $originLong, $boundsOrigin);
$markerNearestTram = "&markers=color:green%7Clabel:T%7C$locationNearestTram->lat,$locationNearestTram->lon";

// how far do I need to walk to the ticket machine from home?
$locationTicketMachine = getNearestPOI($poiTickets, $originLat, $originLong, $boundsOrigin);
$markerTicketMachine = "&markers=color:red%7Clabel:M%7C$locationTicketMachine->lat,$locationTicketMachine->lon";

// where is the nearest tram stop after the ticket machine?
$boundsTicketMachine = getOffsetLocationBounds($locationTicketMachine->lat, $locationTicketMachine->lon, 500);
$locationTramWithTicket = getNearestPOI($poiTrams, $locationTicketMachine->lat, $locationTicketMachine->lon, $boundsTicketMachine);
$markerTramWithTicket = "&markers=color:orange%7Clabel:T%7C$locationTramWithTicket->lat,$locationTramWithTicket->lon";

// display them
$url = "http://maps.googleapis.com/maps/api/staticmap?size=600x600&maptype=roadmap&sensor=false$markerNearestTram$markerTicketMachine$markerTramWithTicket$markerOrigin";
?>
<h1>How far to buy a tram ticket?</h1>

<h2>Nearest tram stop</h2>
<p>It's a <?php echo 1?></p>
<p></p>


<img src="<?php echo $url ?>" />
<?php


?>