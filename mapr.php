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
    $contentNearestTram = trim($locationNearestTram->location_name);

    // how far do I need to walk to the ticket machine from home?
    $locationTicketMachine = getNearestPOI($poiTickets, $originLat, $originLong, $boundsOrigin);
    $contentTicketMachine = "$locationTicketMachine->business_name at " . trim($locationTicketMachine->location_name) . ", $locationTicketMachine->suburb";

    // where is the nearest tram stop after the ticket machine?
    $boundsTicketMachine = getOffsetLocationBounds($locationTicketMachine->lat, $locationTicketMachine->lon, $metresMaxWalkingDistance);
    $locationTramWithTicket = getNearestPOI($poiTrams, $locationTicketMachine->lat, $locationTicketMachine->lon, $boundsTicketMachine);
    $contentTramWithTicket = trim($locationTramWithTicket->location_name);
}

global $config;

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
        html { height: 100%; font-family: Roboto,Arial,sans-serif; }
        body { height: 100%; margin: 0; padding: 0 }
        #map-canvas, #map-canvas2 { height: 100% }
        .infoWindow h1 { 
            font-size: 20px !important;
        }
        .infoWindow { 
            min-height: 100px; 
            max-width: 400px;
            text-align: center;
        }
        .infoWindow p { 
        	font-size: 15px !important;
        }
        .infoWindow div {
            width: 100%;
        }
        .infoWindow a {
            display: block;
            bottom: 0;
            margin:10px auto;
            padding: 8px;
            width: 80%;
        }
        .infoWindow a {
        	-moz-box-shadow:inset 0px 1px 0px 0px #ffffff;
        	-webkit-box-shadow:inset 0px 1px 0px 0px #ffffff;
        	box-shadow:inset 0px 1px 0px 0px #ffffff;
        	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #ededed), color-stop(1, #dfdfdf) );
        	background:-moz-linear-gradient( center top, #ededed 5%, #dfdfdf 100% );
        	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#dfdfdf');
        	background-color:#ededed;
        	-webkit-border-top-left-radius:6px;
        	-moz-border-radius-topleft:6px;
        	border-top-left-radius:6px;
        	-webkit-border-top-right-radius:6px;
        	-moz-border-radius-topright:6px;
        	border-top-right-radius:6px;
        	-webkit-border-bottom-right-radius:6px;
        	-moz-border-radius-bottomright:6px;
        	border-bottom-right-radius:6px;
        	-webkit-border-bottom-left-radius:6px;
        	-moz-border-radius-bottomleft:6px;
        	border-bottom-left-radius:6px;
        	text-indent:0;
        	border:1px solid #dcdcdc;
        	//display:inline-block;
        	color:black;
        	//font-family:arial;
        	font-size: 15px !important;
        	font-weight:bold !important;
        	font-style:normal;
        	//height:50px;
        	//line-height:50px;
        	//width:100px;
        	text-decoration:none;
        	text-align:center;
        	text-shadow:1px 1px 0px #ffffff;
        }
        .infoWindow a:hover {
        	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #dfdfdf), color-stop(1, #ededed) );
        	background:-moz-linear-gradient( center top, #dfdfdf 5%, #ededed 100% );
        	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#dfdfdf', endColorstr='#ededed');
        	background-color:#dfdfdf;
        }
        .black_overlay{
            display: none;
            position: absolute;
            top: 0%;
            left: 0%;
            width: 100%;
            height: 100%;
            background-color: black;
            z-index:1001;
            -moz-opacity: 0.8;
            opacity:.80;
            filter: alpha(opacity=80);
        }
        .white_content {
            display: none;
            position: absolute;
            top: 25%;
            left: 25%;
            width: 50%;
            height: 400px;
            padding: 16px;
            border: 16px solid orange;
            background-color: white;
            z-index:1002;
            overflow: auto;
        }
        
    </style>
    <script type="text/javascript"
      src="https://maps.googleapis.com/maps/api/js?key=<?php echo $config['googleapi'] ?>&sensor=false">
    </script>
    <script type="text/javascript">
    var map;
    var infoWindow = new google.maps.InfoWindow();
    
    var originContent, nearestTramContent, ticketMachineContent, tramWithTicketContent;    
    var originMarker, nearestTramMarker, ticketMachineMarker, tramWithTicketMarker;
    var nearestDistance, ticketDistance, tramDistance, nearestDuration, ticketDuration, tramDuration;
    
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
                
                nearestDistance = result.routes[0].legs[0].distance;
                nearestDuration = result.routes[0].legs[0].duration;
                nearestTramContent = getNearestTramContent(nearestTramMarker.position, nearestDistance, nearestDuration);
                
                google.maps.event.addListener(nearestTramMarker, 'click', function() {
                    infoWindow.setContent(nearestTramContent);
                    infoWindow.open(map, this);
                });
                
                setupFinalLightboxContent();
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
                
                ticketDistance = result.routes[0].legs[0].distance;
                ticketDuration = result.routes[0].legs[0].duration;
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
                
                tramDistance = result.routes[0].legs[1].distance;
                tramDuration = result.routes[0].legs[1].duration;
                tramWithTicketContent = getTramWithTicketContent(tramWithTicketMarker.position, tramDistance, tramDuration);
                
                google.maps.event.addListener(tramWithTicketMarker, 'click', function() {
                    infoWindow.setContent(tramWithTicketContent);
                       infoWindow.open(map, this);
                });
                
                setupFinalLightboxContent();
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
        return '<div class="infoWindow"><h1>Welcome!</h1><p>You\'re currently at ' + position.lat().toFixed(6) + ', ' + position.lng().toFixed(6) + '</p><p><a href="#" onclick="google.maps.event.trigger(nearestTramMarker, \'click\')">Which way to the tram stop?</a></p></div>';
    }
    
    function getNearestTramContent(position, distance, duration)
    {
        return '<div class="infoWindow"><h1>Your nearest tram stop</h1><p><?php echo $contentNearestTram ?> is your nearest tram stop - it\'s only <strong>' + formatDistance(distance) + '</strong> away, which is a <strong>' + duration.text + '</strong> walk down the street.</p><p><a href="#" onclick="google.maps.event.trigger(ticketMachineMarker, \'click\')">So where can I buy a ticket?</a></p></div>';
    }
    
    function getTicketMachineContent(position, distance, duration)
    {
        return '<div class="infoWindow"><h1>Buying a ticket</h1><p>Unfortunatly because you can\'t buy a ticket on the tram, you\'ll have to visit your nearest myki retailer: <?php echo $contentTicketMachine ?>.</p><p>It\'s only <strong>' + formatDistance(distance) + '</strong> down the road, which is a <strong>' + duration.text + '</strong> walk.</p><p><a href="#" onclick="google.maps.event.trigger(tramWithTicketMarker, \'click\')">So - back to the tram with my new ticket!</a></p></div>';
    }
    
    function getTramWithTicketContent(position, distance, duration)
    {
        return '<div class="infoWindow"><h1>Back to the tram</h1><p>Now that you have your ticket, you\'re ready to ride!</p><p>Your nearest tram stop is <?php echo $contentTramWithTicket ?> - <strong>' + formatDistance(distance) + '</strong> down the road, which is a <strong>' + duration.text + '</strong> walk.</p><p><a href="#" onclick="displayFinalLightbox()">What a waste!</a></p></div>';
    }
    
    function setupFinalLightboxContent()
    {
        // check for everything being loaded from async methods
        if (nearestDistance !== undefined && ticketDistance !== undefined)
        {
            var extraTime = Math.round(((ticketDuration.value + tramDuration.value) - nearestDuration.value) / 60);
            var extraDistance = ((ticketDistance.value + tramDistance.value) - nearestDistance.value);
            if (extraDistance < 1000) {
                extraDistance += ' metres';
            } else {
                extraDistance = (extraDistance / 1000).toFixed(2) + ' kilometers';
            }
            
            document.getElementById('nearestMinutes').innerHTML = nearestDuration.text;
            document.getElementById('nearestDistance').innerHTML = formatDistance(nearestDistance);
            document.getElementById('ticketDistance').innerHTML = formatDistance(ticketDistance);
            document.getElementById('tramDistance').innerHTML = formatDistance(tramDistance);
            document.getElementById('extraDistance').innerHTML = extraDistance;
            document.getElementById('extraTime').innerHTML = extraTime + ' minutes';
        }
    }
    
    function displayFinalLightbox()
    {
        infoWindow.close();
        document.getElementById('light').style.display='block';
        document.getElementById('fade').style.display='block';
        
        document.getElementById('fade').addEventListener('click', closeFinalLightbox);
    }
    
    function closeFinalLightbox()
    {
        document.getElementById('light').style.display='none';
        document.getElementById('fade').style.display='none';
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

<p>You don't have any tram stops nearby!</p><p>
<?php
}

?>
    <div id="map-canvas"></div>
    <div id="light" class="white_content">
        <h1>Sheesh!</h1>
        <p>Quite the journey to buy a ticket, wasn't it?</p>
        <p>If you could buy a ticket on the tram, it would have taken you only <strong id="nearestMinutes"></strong> to walk the <strong id="nearestDistance"></strong> to your nearest stop and board a tram.</p>
        <p>Instead, you had to walk <strong id="ticketDistance"></strong> to a myki retailer, and then <strong id="tramDistance"></strong> back to the tram stop.</p>
        <p>All up, that is an extra <strong id="extraDistance"></strong> walk - or <strong id="extraTime"></strong> you had to waste because of a lack of onboard ticket sales.</p>
    </div>
    <div id="fade" class="black_overlay"></div>
  </body>
</html>