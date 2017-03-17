<?php
include_once('common/util.php');
global $config;

$originLat = (double) $_GET['lat'];
$originLong = (double) $_GET['lng'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1, user-scalable=no, width=device-width">
    <link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.css">
    <link rel="stylesheet" href="../css/style.css" />
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $config['googleapi'] ?>&sensor=false"></script>
    <script type="text/javascript" src="../js/home.js"></script>
    <script type="text/javascript" src="../js/mapr.js"></script>
    <script type="text/javascript">
        google.maps.event.addDomListener(window, 'load', function() { initialiseMap(<?php echo $originLat; ?>, <?php echo $originLong; ?>, 'home'); } );
    </script>
    <link rel="shortcut icon" type="image/png" href="../favicon.png" />
  </head>
  <body>
    <div data-role="page" id="home" class="map-page" data-theme="b">
        <div data-role="header">
            <a href="../" data-rel="back" data-role="button" data-icon="carat-l">Back</a>
            <h1>You're at home</h1>
        </div>
        
        <div data-role="content" id="content">
            <div id="map-canvas" style="height:100%"></div>
            <div style="display:none">
                <div id="originContent"><div class="infoWindow">
                    <h1>Welcome!</h1>
                    <p>You're currently at <span id="originLat"></span>, <span id="originLng"></span></p>
                    <p><a href="#" data-role="button" onclick="google.maps.event.trigger(nearestTramMarker, 'click')">Which way to the tram stop?</a></p>
                </div></div>
                <div id="nearestTramContent"><div class="infoWindow">
                    <h1>Your nearest tram stop</h1>
                    <p><span id="nearestName"></span> is your nearest tram stop - it's only <strong id="nearestDistance"></strong> away, which is a <strong id="nearestDuration"></strong> walk down the street.</p>
                    <p><a href="#" data-role="button" onclick="google.maps.event.trigger(ticketMachineMarker, 'click')">So where can I buy a ticket?</a></p>
                </div></div>
                <div id="ticketMachineContent"><div class="infoWindow">
                    <h1>Buying a ticket</h1>
                    <p>Unfortunatly because you can't buy a ticket on the tram, you'll have to visit your nearest myki retailer: <span id="ticketMachineName"></span>.</p>
                    <p>It's only <strong id="ticketDistance"></strong> down the road, which is a <strong id="ticketDuration"></strong> walk.</p>
                    <p><a href="#" data-role="button" onclick="google.maps.event.trigger(tramWithTicketMarker, 'click')">So - back to the tram with my new ticket!</a></p>
                </div></div>
                <div id="tramWithTicketContent"><div class="infoWindow">
                    <h1>Back to the tram</h1>
                    <p>Now that you have your ticket, you're ready to ride!</p>
                    <p>Your nearest tram stop is <span id="tramName"></span> - <strong id="tramDistance"></strong> down the road, which is a <strong id="tramDuration"></strong> walk.</p>
                    <p><a href="#popupFinal" data-role="button" data-rel="popup" data-position-to="window" data-transition="fade" onclick="closeFinalLightbox()">What a waste!</a></p>
                </div></div>
                <div id="errorContent"><div class="infoWindow">
                    <h1>No trams?</h1>
                    <p>Sorry - there aren't any tram stops near your current location! Drag me somewhere else, so we can find a tram.</p>
                </div></div>
                <div id="searchStartContent"><div class="infoWindow">
                    <h1>Where are you?</h1>
                    <p>Drag me to your current location, so we can find your nearest tram.</p>
                </div></div>
                <div id="searchEndContent"><div class="infoWindow">
                    <h1>Are you here?</h1>
                    <p>If not, drag the target to your actual location.</p>
                    <p><a href="#" data-role="button" onclick="initialiseForMarker()">I'm here</a></p>
                </div></div>
            </div>
            <div data-role="popup" id="popupFinal" data-overlay-theme="a" data-theme="d" data-corners="false">
                <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
                <h1>Sheesh!</h1>
                <p>Quite the journey to buy a ticket, wasn't it?</p>
                <p>If you could buy a ticket on the tram, it would have taken you only <strong id="nearestDurationLB"></strong> to walk the <strong id="nearestDistanceLB"></strong> to your nearest stop and board a tram.</p>
                <p>Instead, you had to walk <strong id="ticketDistanceLB"></strong> to a myki retailer, and then <strong id="tramDistanceLB"></strong> back to the tram stop.</p>
                <p>All up, that is an extra <strong id="extraDistanceLB"></strong> walk - or <strong id="extraTimeLB"></strong> you had to waste because of a lack of onboard ticket sales.</p>
                <a href="../" data-role="button" data-icon="home" data-ajax="false">Home</a>
            </div>
        </div>
        
        <div data-role="footer" data-position="fixed">
            <h4>How far is myki making you walk?</h4>
        </div>
    </div>
        
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="https://code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.js"></script>
  </body>
</html>