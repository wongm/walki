<?php

include_once("common/util.php");

$metresMaxWalkingDistance = 1500;
$metresMaxTicketMachineDistance = 2500;

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

$poiTickets = 'outlets';
$poiTrams = 'stops';

// nearest tram
$locationNearestTram = getNearestPOI($poiTrams, $originLat, $originLong, $metresMaxWalkingDistance, 'route_types=1');

if ($locationNearestTram != null)
{
    $contentNearestTram = trim($locationNearestTram->stop_name);

    switch ($type)
    {
        // how far to the ticket machine from the tram?
        case "tram":
            $locationTicketMachine = getNearestPOI($poiTickets, $locationNearestTram->stop_latitude, $locationNearestTram->stop_longitude, $metresMaxWalkingDistance);
            break;
        // how far do I need to walk to the ticket machine from home?
        case "home":
            $locationTicketMachine = getNearestPOI($poiTickets, $originLat, $originLong, $metresMaxWalkingDistance);
            break;
    }
    $contentTicketMachine = "$locationTicketMachine->outlet_business at " . trim($locationTicketMachine->outlet_name) . ", $locationTicketMachine->outlet_suburb";

    // where is the nearest tram stop after the ticket machine?
    $locationTramWithTicket = getNearestPOI($poiTrams, $locationTicketMachine->outlet_latitude, $locationTicketMachine->outlet_longitude, $metresMaxTicketMachineDistance, 'route_types=1');
    $contentTramWithTicket = trim($locationTramWithTicket->stop_name);
    
    switch ($type)
    {
        case "tram":
            $current = array(
                "name" => $contentNearestTram,
                "lat" => $locationNearestTram->stop_latitude,
                "lng" => $locationNearestTram->stop_longitude,
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
                "lat" => $locationNearestTram->stop_latitude,
                "lng" => $locationNearestTram->stop_longitude,
            );
            break;
    }
    
    $jsondata = array(
        "type" => $type,
        "current" => $current,
        "nearestTram" => $nearestTram,
        "ticketMachine" => array(
            "name" => $contentTicketMachine,
            "lat" => $locationTicketMachine->outlet_latitude,
            "lng" => $locationTicketMachine->outlet_longitude,
            ),
        "tramWithTicket" => array(
            "name" => $contentTramWithTicket,
            "lat" => $locationTramWithTicket->stop_latitude,
            "lng" => $locationTramWithTicket->stop_longitude,
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