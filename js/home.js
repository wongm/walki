var defaultLat = -37.814107;
var defaultLng = 144.96328;

function checkPosition(evt) {
    if(navigator.geolocation) {  
        $.mobile.loading('show', {
            text: 'Finding your location',
            textVisible: true,
            theme: 'b',
        });
        var timeout = setTimeout(function() { positionDenied(evt.target.id); }, 10000);
        navigator.geolocation.getCurrentPosition( 
            function(pos) { clearTimeout(timeout); showPosition(pos, evt.target.id) }, 
            function() { clearTimeout(timeout); positionDenied(evt.target.id) } 
        );
    } else {
        positionDenied(evt.target.id);
    }
    return false;
}

function showPosition(pos, type) {
    switch (type) {
        case 'home':
        case 'tram':
            if (pos.coords.latitude == defaultLat && pos.coords.longitude == defaultLng) {
                window.location.href = type + '/find';
            } else {
                window.location.href = type + '/' + pos.coords.latitude + ',' + pos.coords.longitude;
            }
            return false;
    }
}

function positionDenied(type) {
    window.location.href = type + '/find';
    return false;
}

function hardLoadLink(evt) {
    window.location.href = evt.target.href;
    return false;
}