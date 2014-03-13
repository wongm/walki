<?php

include_once('util.php');

$metresMaxWalkingDistance = 1500;

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
$boundsOrigin = getOffsetLocationBounds($originLat, $originLong, $metresMaxWalkingDistance);

$poiTickets = 100;
$poiTrams = 1;

$markerOrigin = "&markers=color:blue%7Clabel:H%7C$originLat,$originLong";

// nearest tram
$locationNearestTram = getNearestPOI($poiTrams, $originLat, $originLong, $boundsOrigin);

if ($locationNearestTram != null)
{
	$markerNearestTram = "&markers=color:green%7Clabel:T%7C$locationNearestTram->lat,$locationNearestTram->lon";
	$pathNearestTram = "&path=color:green|weight:5|$originLat,$originLong|$locationNearestTram->lat,$locationNearestTram->lon";
	
	// how far do I need to walk to the ticket machine from home?
	$locationTicketMachine = getNearestPOI($poiTickets, $originLat, $originLong, $boundsOrigin);
	$markerTicketMachine = "&markers=color:red%7Clabel:M%7C$locationTicketMachine->lat,$locationTicketMachine->lon";
	$pathTicketMachine = "&path=color:red|weight:5|$originLat,$originLong|$locationTicketMachine->lat,$locationTicketMachine->lon";
	
	// where is the nearest tram stop after the ticket machine?
	$boundsTicketMachine = getOffsetLocationBounds($locationTicketMachine->lat, $locationTicketMachine->lon, $metresMaxWalkingDistance);
	$locationTramWithTicket = getNearestPOI($poiTrams, $locationTicketMachine->lat, $locationTicketMachine->lon, $boundsTicketMachine);
	$markerTramWithTicket = "&markers=color:red%7Clabel:T%7C$locationTramWithTicket->lat,$locationTramWithTicket->lon";
	$pathTramWithTicket = "&path=color:red|weight:5|$locationTicketMachine->lat,$locationTicketMachine->lon|$locationTramWithTicket->lat,$locationTramWithTicket->lon";
	
	$extraMykiDistance = (($locationTicketMachine->distance + $locationTramWithTicket->distance) - $locationNearestTram->distance);
}

global $config;

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map-canvas { height: 100% }
    </style>
    <script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?key=<?php echo $config['googleapi'] ?>&sensor=false">
    </script>
    <script type="text/javascript">
      function initialize() {
        var mapOptions = {
          center: new google.maps.LatLng(<?php echo $originLat; ?>,<?php echo $originLong; ?>),
          zoom: 16
        };
        var map = new google.maps.Map(document.getElementById("map-canvas"),
            mapOptions);
            
        var homeLatlng = new google.maps.LatLng(<?php echo $originLat; ?>,<?php echo $originLong; ?>);
        var homeMarker = new google.maps.Marker({
	    	position: homeLatlng,
	    	map: map,
	    	title:"You are here"
		});
            
<?php




?>

      }
      google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
<?php

?>
<h1>How far to buy a tram ticket?</h1>

<p>You're currently at <?php echo $originLat ?>, <?php echo $originLong ?>.</p>

<?php
if ($locationNearestTram != null)
{
?>
<h2>Nearest tram stop</h2>
<p>It's a <?php echo $locationNearestTram->distance ?> metre walk to the nearest tram stop: <?php echo trim($locationNearestTram->location_name) ?>.</p>


<h2>Forgotten your ticket?</h2>
<p>It's a <?php echo $locationTicketMachine->distance ?> metre walk to the nearest myki machine: <?php echo $locationTicketMachine->business_name ?> at <?php echo trim($locationTicketMachine->location_name) ?>, <?php echo $locationTicketMachine->suburb ?>.</p>
<p>You then need to walk <?php echo $locationTramWithTicket->distance ?> metres to the nearest tram stop: <?php echo trim($locationTramWithTicket->location_name) ?>.</p>

<p>All you, you've had to walk an extra <?php echo $extraMykiDistance ?> metres because you can't buy a ticket on a tram!</p>
<?php

}
else
{
?>
<h2>Oops!</h2>

<p>You don't have any tram stops nearby!</p>
<?php
}

?>
    <div id="map-canvas"/>
  </body>
</html>