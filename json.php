<?php

include_once("util.php");

$metresMaxWalkingDistance = 1500;

$originLat = $_GET["lat"];
$originLong = $_GET["long"];
$type = $_GET["type"];

if (strlen($originLat) < 1)
{
    $originLat = -37.7796654;
}
if (strlen($originLong) < 1)
{
    $originLong = 144.917969;
}

switch ($type)
{
    case "tram":
    case "home":
        continue;
    default:
        die();
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
    
    switch ($type)
    {
        case "tram":
            $current = array(
                "name" => $contentNearestTram,
                "lat" => $locationNearestTram->lat,
                "lng" => $locationNearestTram->lon,
            );
            $nearestTram = null;
            break;
        case "home":
            $current = array(
                "name" => null,
                "lat" => (double)$originLat,
                "lng" => (double)$originLong,
            );
            $nearestTram = array(
                "name" => $contentNearestTram,
                "lat" => $locationNearestTram->lat,
                "lng" => $locationNearestTram->lon,
            );
            break;
    }
    
    $jsondata = array(
        "type" => $type,
        "current" => $current,
        "nearestTram" => $nearestTram,
        "ticketMachine" => array(
            "name" => $contentTicketMachine,
            "lat" => $locationTicketMachine->lat,
            "lng" => $locationTicketMachine->lon,
            ),
        "tramWithTicket" => array(
            "name" => $contentTramWithTicket,
            "lat" => $locationTramWithTicket->lat,
            "lng" => $locationTramWithTicket->lon,
            )
    );
}
else
{
    $jsondata = array("error" => "No tram stops found", "code" => 1);
}
    
header("Content-Type: application/json");
echo json_encode($jsondata);
?>