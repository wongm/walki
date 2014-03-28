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
    <div data-role="page" id="tram" class="map-page" data-theme="b">
        <div data-role="header">
            <a href="." data-rel="back" data-role="button" data-icon="carat-l">Back</a>
            <h1>You're on a tram</h1>
        </div>
        
        <div data-role="content" id="content">
            <div id="map-canvas" style="height:100%"></div>
        </div>
        
        <div data-role="footer" data-position="fixed">
            <h4>Test All The Things</h4>
        </div>
    </div>
    
    <div id="light" class="white_content">
        <h1>Sheesh!</h1>
        <p>Quite the journey to top up your myki, wasn't it?</p>
        <p>You've just had to had to walk <strong id="ticketDistanceLB"></strong> to a myki retailer, and then <strong id="tramDistanceLB"></strong> back to a tram.</p>
        <p>All up, that is an extra <strong id="extraDistanceLB"></strong> walk - or <strong id="extraTimeLB"></strong> you had to waste because you can't top up on your myki on a tram.</p>
    </div>
    
    <div id="fade" class="black_overlay"></div>
    
    <div style="display:none">
        <div id="originContent"><div class="infoWindow">
            <h1>Welcome!</h1>
            <p>You just tried to touch on, but your myki is running on empty.</p>
            <p>Bail out at stop <span id="originName"></span>, before the Metcops catch you!</p>
            <p><a href="#" onclick="google.maps.event.trigger(ticketMachineMarker, 'click')">So where can I top up?</a></p>
        </div></div>
        <div id="ticketMachineContent"><div class="infoWindow">
            <h1>Buying a ticket</h1>
            <p>Because you can't buy a ticket on the tram, you'll have to visit your nearest myki retailer: <span id="ticketMachineName"></span>.</p>
            <p>It's only <strong id="ticketDistance"></strong> down the road, which is a <strong id="ticketDuration"></strong> walk.</p>
            <p><a href="#" onclick="google.maps.event.trigger(tramWithTicketMarker, 'click')">So - back to the tram!</a></p>
        </div></div>
        <div id="tramWithTicketContent"><div class="infoWindow">
            <h1>Back to the tram</h1>
            <p>Now that you've topped up your myki, you can jump back on the tram!</p>
            <p>Your nearest tram stop is <span id="tramName"></span> - <strong id="tramDistance"></strong> down the road, which is a <strong id="tramDuration"></strong> walk.</p>
            <p><a href="#" onclick="displayFinalLightbox()">What a waste!</a></p>
        </div></div>    
    </div>
    
    <script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="http://code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.js"></script>
  </body>
</html>