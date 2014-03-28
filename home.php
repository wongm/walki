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
    <meta name="viewport" content="initial-scale=1, user-scalable=no, width=device-width">
    <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.css">
    <link rel="stylesheet" href="css/style.css" />
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $config['googleapi'] ?>&sensor=false"></script>
    <script type="text/javascript" src="js/core.js"></script>
    <script type="text/javascript" src="js/mapr.js"></script>
    <script type="text/javascript">
        function initialize() {
            $.mobile.loading('show');
            var url = 'json.php?lat=<?php echo $originLat; ?>&long=<?php echo $originLong; ?>&type=home';            
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
    <div data-role="page" id="home" class="map-page" data-theme="b">
        <div data-role="header">
            <a href="." data-role="button" data-icon="carat-l">Back</a>
            <h1>You're at home</h1>
        </div><!-- /header -->
        
        <div data-role="content" id="content">
            <div id="map-canvas" style="height:100%"></div>
        </div>
        
        <div data-role="footer" data-position="fixed">
            <h4>Test All The Things</h4>
        </div><!-- /footer -->
    </div><!-- /page -->
        
    <div id="light" class="white_content">
        <h1>Sheesh!</h1>
        <p>Quite the journey to buy a ticket, wasn't it?</p>
        <p>If you could buy a ticket on the tram, it would have taken you only <strong id="nearestDurationLB"></strong> to walk the <strong id="nearestDistanceLB"></strong> to your nearest stop and board a tram.</p>
        <p>Instead, you had to walk <strong id="ticketDistanceLB"></strong> to a myki retailer, and then <strong id="tramDistanceLB"></strong> back to the tram stop.</p>
        <p>All up, that is an extra <strong id="extraDistanceLB"></strong> walk - or <strong id="extraTimeLB"></strong> you had to waste because of a lack of onboard ticket sales.</p>
    </div>
    
    <div id="fade" class="black_overlay"></div>
    
    <div style="display:none">
        <div id="originContent"><div class="infoWindow">
            <h1>Welcome!</h1>
            <p>You're currently at <span id="originLat"></span>, <span id="originLng"></span></p>
            <p><a href="#" onclick="google.maps.event.trigger(nearestTramMarker, 'click')">Which way to the tram stop?</a></p>
        </div></div>
        <div id="nearestTramContent"><div class="infoWindow">
            <h1>Your nearest tram stop</h1>
            <p><span id="nearestName"></span> is your nearest tram stop - it's only <strong id="nearestDistance"></strong> away, which is a <strong id="nearestDuration"></strong> walk down the street.</p>
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
    
    <script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="http://code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.js"></script>
  </body>
</html>