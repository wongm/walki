<?php
include_once('util.php');
global $config;

$originLat = (double) $_GET['lat'];
$originLong = (double) $_GET['lng'];
?>
<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1, user-scalable=no, width=device-width">
    <link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.css">
    <link rel="stylesheet" href="/css/style.css" />
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo $config['googleapi'] ?>&sensor=false"></script>
    <script type="text/javascript" src="/js/mapr.js"></script>
    <script type="text/javascript">
        google.maps.event.addDomListener(window, 'load', function() { initialiseMap(<?php echo $originLat; ?>, <?php echo $originLong; ?>, 'tram'); } );
    </script>
  </head>
  <body>
    <div data-role="page" id="tram" class="map-page" data-theme="b">
        <div data-role="header">
            <a href="/" data-rel="back" data-role="button" data-icon="carat-l">Back</a>
            <h1>You're on a tram</h1>
        </div>
        
        <div data-role="content" id="content">
            <div id="map-canvas" style="height:100%"></div>
            <div style="display:none">
                <div id="originContent"><div class="infoWindow">
                    <h1>Welcome!</h1>
                    <p>You just tried to touch on, but your myki is running on empty.</p>
                    <p>Bail out at stop <span id="originName"></span>, before the Metcops catch you!</p>
                    <p><a href="#" data-role="button" onclick="google.maps.event.trigger(ticketMachineMarker, 'click')">So where can I top up?</a></p>
                </div></div>
                <div id="ticketMachineContent"><div class="infoWindow">
                    <h1>Buying a ticket</h1>
                    <p>Because you can't buy a ticket on the tram, you'll have to visit your nearest myki retailer: <span id="ticketMachineName"></span>.</p>
                    <p>It's only <strong id="ticketDistance"></strong> down the road, which is a <strong id="ticketDuration"></strong> walk.</p>
                    <p><a href="#" data-role="button" onclick="google.maps.event.trigger(tramWithTicketMarker, 'click')">So - back to the tram!</a></p>
                </div></div>
                <div id="tramWithTicketContent"><div class="infoWindow">
                    <h1>Back to the tram</h1>
                    <p>Now that you've topped up your myki, you can jump back on the tram!</p>
                    <p>Your nearest tram stop is <span id="tramName"></span> - <strong id="tramDistance"></strong> down the road, which is a <strong id="tramDuration"></strong> walk.</p>
                    <p><a href="#popupFinal" data-role="button" data-rel="popup" data-position-to="window" data-transition="fade" onclick="closeFinalLightbox()">What a waste!</a></p>
                </div></div>
                <div id="errorContent"><div class="infoWindow">
                    <h1>No trams?</h1>
                    <p>Sorry - there aren't any tram stops near your current location!</p>
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
                <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a><h1>Sheesh!</h1>
                <p>Quite the journey to top up your myki, wasn't it?</p>
                <p>You've just had to had to walk <strong id="ticketDistanceLB"></strong> to a myki retailer, and then <strong id="tramDistanceLB"></strong> back to a tram.</p>
                <p>All up, that is an extra <strong id="extraDistanceLB"></strong> walk - or <strong id="extraTimeLB"></strong> you had to waste because you can't top up on your myki on a tram.</p>
                <a href="/" data-role="button" data-icon="home" onclick="goHome()">Home</a>
            </div>
        </div>
        
        <div data-role="footer" data-position="fixed">
            <h4>How far is myki making you walk?</h4>
        </div>
    </div>
    
    <script src="http://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="http://code.jquery.com/mobile/1.4.0/jquery.mobile-1.4.0.min.js"></script>
  </body>
</html>