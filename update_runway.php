<?php

$airport = $_POST["airport"];
$runway = $_POST["runway"];
$type = $_POST["type"];

$activeRunwaysFile = file_get_contents("active_runways.json");
$activeRunways = json_decode($activeRunwaysFile, true);

$runwayType = $type == 0 ? "dep" : "arr";

echo $runwayType;

if (isset($activeRunways[$airport])) {
    $activeRunways[$airport][$runwayType] = $runway;
} else {
    $activeRunways[$airport] = [
        "dep" => $runway,
        "arr" => $runway,
    ];
}

$encodedData = json_encode($activeRunways);
file_put_contents('active_runways.json', $encodedData);