function checkPosition(evt) {
	if(navigator.geolocation) {
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
			window.location.href = type + '.php?lat=' + pos.coords.latitude + '&long=' + pos.coords.longitude;
			return;
	}
}

function positionDenied(type) {
	window.location.href = type + '.php';
	return;
}

function hardLoadLink(evt) {
	window.location.href = evt.target.href;
	return;
}