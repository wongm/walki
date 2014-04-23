<?php

include_once("util.php");

$metresMaxWalkingDistance = 1500;

$originLat = (double) $_GET["lat"];
$originLong = (double) $_GET["lng"];
$type = $_GET["type"];

// default to the middle of Melbourne!
if ($originLat == 0 || !is_numeric($originLat))
{
    $originLat = -37.814107;
}
if ($originLong == 0 || !is_numeric($originLong))
{
    $originLong = 144.96328;
}

switch ($type)
{
    case "tram":
    case "home":
        continue;
    default:
        die();
}

$poiTickets = 100;
$poiTrams = 1;

// nearest tram
$boundsOrigin = getOffsetLocationBounds($originLat, $originLong, $metresMaxWalkingDistance);
$locationNearestTram = getNearestPOI($poiTrams, $originLat, $originLong, $boundsOrigin);

if ($locationNearestTram != null)
{
    $boundsNearestTram = getOffsetLocationBounds($locationNearestTram->lat, $locationNearestTram->lon, $metresMaxWalkingDistance);
    $contentNearestTram = trim($locationNearestTram->location_name);

    switch ($type)
    {
        // how far to the ticket machine from the tram?
        case "tram":
            $locationTicketMachine = getNearestPOI($poiTickets, $locationNearestTram->lat, $locationNearestTram->lon, $boundsNearestTram);
            break;
        // how far do I need to walk to the ticket machine from home?
        case "home":
            $locationTicketMachine = getNearestPOI($poiTickets, $originLat, $originLong, $boundsOrigin);
            break;
    }
    $contentTicketMachine = "$locationTicketMachine->business_name at " . trim($locationTicketMachine->location_name) . ", $locationTicketMachine->suburb";

    // where is the nearest tram stop after the ticket machine?
    $boundsTicketMachine = getOffsetLocationBounds($locationTicketMachine->lat, $locationTicketMachine->lon, $metresMaxWalkingDistance + 1000);
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
    $jsondata = array(
        "error" => "No tram stops found", 
        "code" => 1,
        "lat" => (double)$originLat,
        "lng" => (double)$originLong,
    );
}
    
header("Content-Type: application/json");
echo json_encode($jsondata);
?>