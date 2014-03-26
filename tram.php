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

global $config;

?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="css/style.css" />
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $config['googleapi'] ?>&sensor=false"></script>
    <script type="text/javascript" src="js/core.js"></script>
    <script type="text/javascript" src="js/mapr.js"></script>
    <script type="text/javascript">
        function initialize() {            
            var url = 'json.php?lat=<?php echo $originLat; ?>&long=<?php echo $originLong; ?>&type=tram';            
            ajax(url, {
                onSuccess: loadMap,
                onFailure: displayFailure,
                responseType: 'json',
            });
        }    
        google.maps.event.addDomListener(window, 'load', initialize);
    </script>
  </head>
  <body>
    <div id="map-canvas"></div>
    <div id="light" class="white_content">
        <h1>Sheesh!</h1>
        <p>Quite the journey to buy a ticket, wasn't it?</p>
        <p>Instead, you had to walk <strong id="ticketDistanceLB"></strong> to a myki retailer, and then <strong id="tramDistanceLB"></strong> back to the tram stop.</p>
        <p>All up, that is an extra <strong id="extraDistanceLB"></strong> walk - or <strong id="extraTimeLB"></strong> you had to waste because of a lack of onboard ticket sales.</p>
    </div>
    <div id="fade" class="black_overlay"></div>
    <div style="display:none">
        <div id="originContent"><div class="infoWindow">
            <h1>Welcome!</h1>
            <p>You're currently at <span id="originName"></span></p>
            <p><a href="#" onclick="google.maps.event.trigger(ticketMachineMarker, 'click')">So where can I buy a ticket?</a></p>
        </div></div>
        <div id="ticketMachineContent"><div class="infoWindow">
            <h1>Buying a ticket</h1>
            <p>Unfortunatly because you can't buy a ticket on the tram, you'll have to visit your nearest myki retailer: <span id="ticketMachineName"></span>.</p>
            <p>It's only <strong id="ticketDistance"></strong> down the road, which is a <strong id="ticketDuration"></strong> walk.</p>
            <p><a href="#" onclick="google.maps.event.trigger(tramWithTicketMarker, 'click')">So - back to the tram with my new ticket!</a></p>
        </div></div>
        <div id="tramWithTicketContent"><div class="infoWindow">
            <h1>Back to the tram</h1>
            <p>Now that you have your ticket, you're ready to ride!</p>
            <p>Your nearest tram stop is <span id="tramName"></span> - <strong id="tramDistance"></strong> down the road, which is a <strong id="tramDuration"></strong> walk.</p>
            <p><a href="#" onclick="displayFinalLightbox()">What a waste!</a></p>
        </div></div>    
    </div>
  </body>
</html>