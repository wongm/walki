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
    
    $jsondata = array(
        "nearestTram" => array(
            "content" => $contentNearestTram,
            "lat" => $locationNearestTram->lat,
            "lng" => $locationNearestTram->lon,
            ),
        "ticketMachine" => array(
            "content" => $contentTicketMachine,
            "lat" => $locationTicketMachine->lat,
            "lng" => $locationTicketMachine->lon,
            ),
        "tramWithTicket" => array(
            "content" => $contentTramWithTicket,
            "lat" => $locationTramWithTicket->lat,
            "lng" => $locationTramWithTicket->lon,
            )
    );
}
else
{
    $jsondata = array("error" => "No tram stops found", "code" => 1);
}
    
header('Content-Type: application/json');
echo json_encode(array("feed" => $jsondata));
?>