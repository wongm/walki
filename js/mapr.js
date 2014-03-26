var map;
var infoWindow = new google.maps.InfoWindow();

var originContent, nearestTramContent, ticketMachineContent, tramWithTicketContent;    
var originMarker, nearestTramMarker, ticketMachineMarker, tramWithTicketMarker;
var nearestDistance, ticketDistance, tramDistance, nearestDuration, ticketDuration, tramDuration;

function displayFailure(xhr, result) {
    alert('fail');
}

function loadMap(xhr, stopResults) {
    
    if (stopResults.error) {
        displayFailure();
    }
        
    var mapOptions = {
        center: originLatlng,
    };
    
    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);
    
    var bounds = new google.maps.LatLngBounds();
    var originIcon;
    
    var originLatlng = new google.maps.LatLng(stopResults.current.lat, stopResults.current.lng);
    var ticketMachineLatlng = new google.maps.LatLng(stopResults.ticketMachine.lat, stopResults.ticketMachine.lng);
    var tramWithTicketLatlng = new google.maps.LatLng(stopResults.tramWithTicket.lat, stopResults.tramWithTicket.lng);
    var nearestTramLatlng = originLatlng;
    
    switch (stopResults.type)
    {
        case 'home':
            nearestTramLatlng = new google.maps.LatLng(stopResults.nearestTram.lat, stopResults.nearestTram.lng);
            originIcon = 'http://maps.google.com/mapfiles/kml/pal3/icon56.png';
            break;
        case 'tram':
            originIcon = 'http://maps.google.com/mapfiles/kml/pal3/icon20.png';
            break;
    }
    
    bounds.extend(originLatlng);
    bounds.extend(nearestTramLatlng);
    bounds.extend(ticketMachineLatlng);
    bounds.extend(tramWithTicketLatlng);
    
    var directionsService = new google.maps.DirectionsService();  
    var directionsDisplay = new google.maps.DirectionsRenderer();      
    directionsDisplay.setMap(map);
    
    if (stopResults.type == 'home')
    {
        var requestDirect = {
            origin:originLatlng,
            destination:nearestTramLatlng,
            travelMode: google.maps.TravelMode.WALKING
        };
        directionsService.route(requestDirect, function(directionResult, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                renderDirections(directionResult);
                
                nearestTramMarker = new google.maps.Marker({
                    position: new google.maps.LatLng(directionResult.routes[0].legs[0].end_location.k, directionResult.routes[0].legs[0].end_location.A),
                    map: map,
                    icon: 'http://maps.google.com/mapfiles/ms/micons/green.png',
                    title: 'Nearest tram stop'
                });
                
                nearestDistance = directionResult.routes[0].legs[0].distance;
                nearestDuration = directionResult.routes[0].legs[0].duration;
                
                document.getElementById('nearestName').innerHTML = stopResults.nearestTram.name;
                document.getElementById('nearestDistance').innerHTML = formatDistance(nearestDistance);
                document.getElementById('nearestDuration').innerHTML = nearestDuration.text;
                
                google.maps.event.addListener(nearestTramMarker, 'click', function() {
                    infoWindow.setContent(document.getElementById('nearestTramContent').innerHTML);
                    infoWindow.open(map, this);
                });
                
                setupFinalLightboxContent();
            }
        });
    }
    else
    {
        nearestDistance = new Object();
        nearestDistance.value = 0;
        nearestDuration = new Object();
        nearestDuration.value = 0;
    }
        
  
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
    directionsService.route(requestMyki, function(directionResult, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            renderDirections(directionResult);
            
            originMarker = new google.maps.Marker({
                position: new google.maps.LatLng(directionResult.routes[0].legs[0].start_location.k, directionResult.routes[0].legs[0].start_location.A),
                map: map,
                icon: originIcon,
                title: 'You are here'
            });
            
            switch (stopResults.type)
            {
                case 'home':
                    document.getElementById('originLat').innerHTML = originMarker.position.lat().toFixed(6);
                    document.getElementById('originLng').innerHTML = originMarker.position.lng().toFixed(6);
                    break;
                case 'tram':
                    document.getElementById('originName').innerHTML = stopResults.current.name;    
                    break;
            }
            
            google.maps.event.addListener(originMarker, 'click', function() {
                infoWindow.setContent(document.getElementById('originName').innerHTML);
                infoWindow.open(map, this);
            });
            
            infoWindow.setContent(document.getElementById('originContent').innerHTML);
            infoWindow.open(map, originMarker);
            
            ticketMachineMarker = new google.maps.Marker({
                position: new google.maps.LatLng(directionResult.routes[0].legs[0].end_location.k, directionResult.routes[0].legs[0].end_location.A),
                map: map,
                icon: 'http://maps.google.com/mapfiles/ms/micons/orange.png',
                title: 'Nearest myki machine'
            });
            
            ticketDistance = directionResult.routes[0].legs[0].distance;
            ticketDuration = directionResult.routes[0].legs[0].duration;
            
            document.getElementById('ticketMachineName').innerHTML = stopResults.ticketMachine.name;
            document.getElementById('ticketDistance').innerHTML = formatDistance(ticketDistance);
            document.getElementById('ticketDuration').innerHTML = ticketDuration.text;
            
            google.maps.event.addListener(ticketMachineMarker, 'click', function() {
                infoWindow.setContent(document.getElementById('ticketMachineContent').innerHTML);
                   infoWindow.open(map, this);
            });
            
            tramWithTicketMarker = new google.maps.Marker({
                position: new google.maps.LatLng(directionResult.routes[0].legs[1].end_location.k, directionResult.routes[0].legs[1].end_location.A),
                map: map,
                icon: 'http://maps.google.com/mapfiles/ms/micons/red.png',
                title: 'Nearest tram stop'
            });
            
            tramDistance = directionResult.routes[0].legs[1].distance;
            tramDuration = directionResult.routes[0].legs[1].duration;
            
            document.getElementById('tramName').innerHTML = stopResults.tramWithTicket.name;
            document.getElementById('tramDistance').innerHTML = formatDistance(tramDistance);
            document.getElementById('tramDuration').innerHTML = tramDuration.text;
            
            google.maps.event.addListener(tramWithTicketMarker, 'click', function() {
                infoWindow.setContent(document.getElementById('tramWithTicketContent').innerHTML);
                   infoWindow.open(map, this);
            });
            
            setupFinalLightboxContent();
        }
    });
    
    map.fitBounds(bounds);
}

function formatDistance(distance)
{
    if (distance.value < 1000) {
        return distance.value + ' metres';
    }
    return distance.text;
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
            extraDistance = (extraDistance / 1000).toFixed(2) + ' km';
        }
        
        if (document.getElementById('nearestDurationLB')) {
            document.getElementById('nearestDurationLB').innerHTML = nearestDuration.text;
            document.getElementById('nearestDistanceLB').innerHTML = formatDistance(nearestDistance);
        }
        
        document.getElementById('ticketDistanceLB').innerHTML = formatDistance(ticketDistance);
        document.getElementById('tramDistanceLB').innerHTML = formatDistance(tramDistance);
        document.getElementById('extraDistanceLB').innerHTML = extraDistance;
        document.getElementById('extraTimeLB').innerHTML = extraTime + ' minutes';
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