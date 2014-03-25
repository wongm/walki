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
	$contentNearestTram = "<div class=\"lightbox\">$locationNearestTram->distance metre walk to the nearest tram stop:<br> " . trim($locationNearestTram->location_name) . "</div>";

	// how far do I need to walk to the ticket machine from home?
	$locationTicketMachine = getNearestPOI($poiTickets, $originLat, $originLong, $boundsOrigin);
	$markerTicketMachine = "&markers=color:red%7Clabel:M%7C$locationTicketMachine->lat,$locationTicketMachine->lon";
	$pathTicketMachine = "&path=color:red|weight:5|$originLat,$originLong|$locationTicketMachine->lat,$locationTicketMachine->lon";
	$contentTicketMachine = "<div class=\"lightbox\">It\'s a $locationTicketMachine->distance metre walk to the nearest myki machine:<br> $locationTicketMachine->business_name at " . trim($locationTicketMachine->location_name) . ", $locationTicketMachine->suburb</div>";

	// where is the nearest tram stop after the ticket machine?
	$boundsTicketMachine = getOffsetLocationBounds($locationTicketMachine->lat, $locationTicketMachine->lon, $metresMaxWalkingDistance);
	$locationTramWithTicket = getNearestPOI($poiTrams, $locationTicketMachine->lat, $locationTicketMachine->lon, $boundsTicketMachine);
	$markerTramWithTicket = "&markers=color:red%7Clabel:T%7C$locationTramWithTicket->lat,$locationTramWithTicket->lon";
	$pathTramWithTicket = "&path=color:red|weight:5|$locationTicketMachine->lat,$locationTicketMachine->lon|$locationTramWithTicket->lat,$locationTramWithTicket->lon";
	$contentTramWithTicket = "<div class=\"lightbox\">You then need to walk $locationTramWithTicket->distance metres to the nearest tram stop:<br> " . trim($locationTramWithTicket->location_name) . "</div>";

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
      #map-canvas, #map-canvas2 { height: 100% }
      .lightbox { height: 200px; }
    </style>
    <script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?key=<?php echo $config['googleapi'] ?>&sensor=false">
    </script>
    <script type="text/javascript">
    var map;
    
    var originContent, nearestTramContent, ticketMachineContent, tramWithTicketContent;    
    var originMarker, nearestTramMarker, ticketMachineMarker, tramWithTicketMarker;
    
    function initialize() {
        
        var originLatlng = new google.maps.LatLng(<?php echo $originLat; ?>,<?php echo $originLong; ?>);
        var directionsService = new google.maps.DirectionsService();
        var mapOptions = {
            center: originLatlng,
        };
        map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
<?php

if ($locationNearestTram != null)
{
?>
        var nearestTramLatlng = new google.maps.LatLng(<?php echo $locationNearestTram->lat; ?>,<?php echo $locationNearestTram->lon; ?>);
        var ticketMachineLatlng = new google.maps.LatLng(<?php echo $locationTicketMachine->lat; ?>,<?php echo $locationTicketMachine->lon; ?>);
        var tramWithTicketLatlng = new google.maps.LatLng(<?php echo $locationTramWithTicket->lat; ?>,<?php echo $locationTramWithTicket->lon; ?>);
        
        var bounds = new google.maps.LatLngBounds();
		bounds.extend(originLatlng);
		bounds.extend(nearestTramLatlng);
		bounds.extend(ticketMachineLatlng);
		bounds.extend(tramWithTicketLatlng);
        
        var directionsDisplay = new google.maps.DirectionsRenderer();	  
        directionsDisplay.setMap(map);
	       
		var infoWindow = new google.maps.InfoWindow();
		
        var requestDirect = {
            origin:originLatlng,
            destination:nearestTramLatlng,
            travelMode: google.maps.TravelMode.WALKING
        };
        directionsService.route(requestDirect, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                renderDirections(result);
                
                originMarker = new google.maps.Marker({
                    position: new google.maps.LatLng(result.routes[0].legs[0].start_location.k,result.routes[0].legs[0].start_location.A),
                    map: map,
                    icon: 'http://maps.google.com/mapfiles/kml/pal3/icon56.png',
                    title:"You are here"
                });
                
                originContent = getOriginContent(originMarker.position);
                
                google.maps.event.addListener(originMarker, 'click', function() {
                    infoWindow.setContent(originContent);
                	infoWindow.open(map, this);
                });
		        
                infoWindow.setContent(originContent);
                infoWindow.open(map, originMarker);
		        
                nearestTramMarker = new google.maps.Marker({
                    position: new google.maps.LatLng(result.routes[0].legs[0].end_location.k,result.routes[0].legs[0].end_location.A),
                    map: map,
                    icon: 'http://maps.google.com/mapfiles/ms/micons/green.png',
                    title:"Nearest tram stop"
                });
                
                var distance = formatDistance(result.routes[0].legs[0].distance);
                var duration = result.routes[0].legs[0].duration.text;
                nearestTramContent = getNearestTramContent(nearestTramMarker.position, distance, duration);
                
                google.maps.event.addListener(nearestTramMarker, 'click', function() {
                    infoWindow.setContent(nearestTramContent);
                	infoWindow.open(map, this);
                });
	        }
	    });
	  
        var requestMyki = {
            origin:originLatlng,
            destination:tramWithTicketLatlng,
            waypoints: [
            {
                location:ticketMachineLatlng,
                stopover:true
            }],
            travelMode: google.maps.TravelMode.WALKING
        };
        directionsService.route(requestMyki, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                renderDirections(result);
                
                ticketMachineMarker = new google.maps.Marker({
        	    	position: new google.maps.LatLng(result.routes[0].legs[0].end_location.k,result.routes[0].legs[0].end_location.A),
        	    	map: map,
        	    	icon: 'http://maps.google.com/mapfiles/ms/micons/orange.png',
        	    	title:"Nearest myki machine"
        		});
        		
                var ticketDistance = formatDistance(result.routes[0].legs[0].distance);
                var ticketDuration = result.routes[0].legs[0].duration.text;
        		ticketMachineContent = getTicketMachineContent(ticketMachineMarker.position, ticketDistance, ticketDuration);
        		
        		google.maps.event.addListener(ticketMachineMarker, 'click', function() {
            		infoWindow.setContent(ticketMachineContent);
           			infoWindow.open(map, this);
        		});
                tramWithTicketMarker = new google.maps.Marker({
        	    	position: new google.maps.LatLng(result.routes[0].legs[1].end_location.k,result.routes[0].legs[1].end_location.A),
        	    	map: map,
        	    	icon: 'http://maps.google.com/mapfiles/ms/micons/red.png',
        	    	title:"Nearest tram stop"
        		});
        		
                var tramDistance = formatDistance(result.routes[0].legs[1].distance);
                var tramDuration = result.routes[0].legs[1].duration.text;
        		tramWithTicketContent = getTramWithTicketContent(tramWithTicketMarker.position, tramDistance, tramDuration);
        		
        		google.maps.event.addListener(tramWithTicketMarker, 'click', function() {
            		infoWindow.setContent(tramWithTicketContent);
           			infoWindow.open(map, this);
        		});
		    }
        });
        
        map.fitBounds(bounds);
	  
<?php
}
?>
    }
    
    function formatDistance(distance)
    {
        if (distance.value < 1000) {
            return distance.value + ' metres';
        }
        return distance.text;
    }
        
    function getOriginContent(position)
    {
        return '<div>You\'re currently at ' + position.lat() + ', ' + position.lng() + '<br><a href="#" onclick="google.maps.event.trigger(nearestTramMarker, \'click\')">Which way to the tram stop?</a></div>';
    }
    
    function getNearestTramContent(position, distance, duration)
    {
        return '<div>Your nearest tram stop is <?php echo $contentNearestTram ?> - it\'s only ' + distance + ' away, which is a ' + duration + ' walk.<a href="#" onclick="google.maps.event.trigger(ticketMachineMarker, \'click\')">So where can I buy a ticket?</a></div>';
    }
    
    function getTicketMachineContent(position, distance, duration)
    {
        return '<div>getTicketMachineContent</div>';
    }
    
    function getTramWithTicketContent(position, distance, duration)
    {
        return '<div>getTramWithTicketContent</div>';
    }
    
    
      
	function renderDirections(result) {
        var rendererOptions = { 
            map: map, 
            preserveViewport: true,
            suppressMarkers : true 
	    } 
		var directionsRenderer = new google.maps.DirectionsRenderer(rendererOptions);
		directionsRenderer.setDirections(result);
	}

	google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
<?php

if ($locationNearestTram == null)
{
?>
<h2>Oops!</h2>

<p>You don't have any tram stops nearby!</p>
<?php
}

?>
    <div id="map-canvas"></div>
  </body>
</html>