function checkPosition(evt) {
    if(navigator.geolocation) {  
        $.mobile.loading( 'show', {
            text: 'Finding your location',
            textVisible: true,
            theme: 'b',
        });    
        navigator.geolocation.getCurrentPosition( 
        
            function(pos) { showPosition(pos, evt.target.id) }, 
            function() { positionDenied(evt.target.id) } 
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
            window.location.href = '/' + type + '/' + pos.coords.latitude + ',' + pos.coords.longitude;
            return false;
    }
}

function positionDenied(type) {
    window.location.href = '/' + type + '/';
    return false;
}

function hardLoadLink(evt) {
    window.location.href = evt.target.href;
    return false;
}