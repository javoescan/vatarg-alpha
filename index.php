<?php

// EXAMPLE
// [callsign] => ARG1538
// [cid] => 1135557
// [realname] => Jose Manuel Perez Guerrero SAEZ
// [clienttype] => PILOT
// [frequency] => 
// [latitude] => -34.55429
// [longitude] => -58.4227
// [altitude] => 29
// [groundspeed] => 10
// [planned_aircraft] => B738
// [planned_tascruise] => 457
// [planned_depairport] => SABE
// [planned_altitude] => 36000
// [planned_destairport] => SACO
// [server] => USA-EAST
// [protrevision] => 100
// [rating] => 1
// [transponder] => 2000
// [facilitytype] => 0
// [visualrange] => 0
// [planned_revision] => 2
// [planned_flighttype] => I
// [planned_deptime] => 1713
// [planned_actdeptime] => 0
// [planned_hrsenroute] => 1
// [planned_minenroute] => 17
// [planned_hrsfuel] => 3
// [planned_minfuel] => 15
// [planned_altairport] => SAAR
// [planned_remarks] => CARTAS A BORDO /v/
// [planned_route] => ATOVO3B ATOVO UW5 ASISA ASISA1X
// [planned_depairport_lat] => 0
// [planned_depairport_lon] => 0
// [planned_destairport_lat] => 0
// [planned_destairport_lon] => 0
// [atis_message] => 
// [time_last_atis_received] => 2020-08-08T17:37:28.7228325Z
// [time_logon] => 2020-08-08T17:37:28.7228324Z
// [heading] => 308
// [qnh_i_hg] => 29.91
// [qnh_mb] => 1013

$bairesAirports = ["SAEZ", "SABE", "SADF", "SADP", "SADM"];

$jsonSrc = file_get_contents("http://cluster.data.vatsim.net/vatsim-data.json");
$json = json_decode($jsonSrc, true);

$flights = array_filter($json["clients"], function($connection) {
	return $connection["clienttype"] === "PILOT";
});

$argFlights = array_filter($flights, function($flight) {
	$departure = getPrefixAirport($flight["planned_depairport"]);
	//$destination = getPrefixAirport($flight["planned_destairport"]);
    return $departure === "SA" /* || $destination === "SA" */;
});

$bairesDepartures = array_filter($argFlights, function($flight) use ($bairesAirports) {
	$departure = $flight["planned_depairport"];
	return in_array($departure, $bairesAirports);
});

$storeData = [];
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>VATARG Alpha</title>
		<link rel="shortcut icon" type="image/png" href="./favicon.png">
		<link rel="stylesheet" href="./css/bootstrap.min.css">
		<link rel="stylesheet" href="./css/styles.css">
		<script src="./js/jquery-3.5.1.slim.min.js"></script>
		<script src="./js/bootstrap.min.js"></script>
	</head>
	<body>
		<h2 class="text-center title">VATSIM Argentina Alpha System</h2>
  		<input class="form-control text-center" id="search-input" type="text" placeholder="Filtrar">
		<table class="table table-striped">
			<thead class="thead-dark">
				<tr>
					<th scope="col">Callsign</th>
					<th scope="col">ETD</th>
					<th scope="col">Departure</th>
					<th scope="col">Arrival</th>
					<th scope="col">Altitude</th>
					<th scope="col">SID</th>
					<th scope="col">Initial Climb</th>
					<th scope="col">Transponder</th>
				</tr>
			</thead>
			<tbody id="flights">
				<?php
					foreach ($bairesDepartures as $flight) {
						$transponder = getTransponder($flight);
						$storeData[$flight["callsign"]] = [
							"transponder" => $transponder,
						];
						echo "<tr class='clickable-row'>";
						echo "<td>" . $flight["callsign"] . "</td>";
						echo "<td>" . $flight["planned_deptime"] . "z</td>";
						echo "<td>" . $flight["planned_depairport"] . "</td>";
						echo "<td>" . $flight["planned_destairport"] . "</td>";
						echo "<td>" . formatFlightLevel($flight["planned_altitude"]) . "</td>";
						echo "<td>" . getSID($flight["planned_route"]) . "</td>";
						echo "<td>" . getInitialClimb($flight) . "</td>";
						echo "<td>" . $transponder . "</td>";
						echo "</tr>";
					}
				?>
			</tbody>
		</table>
		<script>
			$(document).ready(function(){
				$("#search-input").on("keyup", function() {
					var value = $(this).val().toLowerCase();
					$("#flights tr").filter(function() {
						$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
					});
				});
				$('#flights').on('click', '.clickable-row', function(event) {
					$(this).addClass('active').siblings().removeClass('active');
				});
			});
		</script>
	</body>
</html>

<?php // HELPERS
function getTransponder($flight) {
	$departure = getPrefixAirport($flight["planned_depairport"]);
	if ($departure === "SA") {
		return getNationalTransponder($flight);
	} else {
		return getInternationalTransponder($flight);
	}
}

function getNationalTransponder($flight) {
	do {
		$transponder = rand(1500, 1777);
	} while (strpos($transponder, "8") !== FALSE || strpos($transponder, "9") !== FALSE || strlen($transponder) !== 4);
	return $transponder;
}

function getInternationalTransponder($flight) {
	do {
		$transponder = rand(300, 577);
	} while (strpos($transponder, "8") !== FALSE || strpos($transponder, "9") !== FALSE || strlen($transponder) !== 3);
	return "0" . $transponder;
}

function formatFlightLevel($flightLevel) {
	if (strpos($flightLevel, "FL") !== FALSE) {
		return "F" . substr($flightLevel, 2, 3);
	} else if (strpos($flightLevel, "F") === FALSE) {
		if (strlen($flightLevel) === 4) {
			if ((int)$flightLevel < 3000) {
				return "A0" . substr($flightLevel, 0, 2);
			}
			return "F0" . substr($flightLevel, 0, 2);
		} else {
			return "F" . substr($flightLevel, 0, 3);
		}
	}
    return $flightLevel;
}

function getSID($route) {
	$arr = explode(' ',trim($route));
	return $arr[0];
}

function getPrefixAirport($airport) {
	return substr($airport, 0, 2);
}

function getInitialClimb($flight) {
	switch($flight["planned_depairport"]) {
		case "SABE":
			return "F060";
		case "SAEZ":
			return "F050";
		case "SADP":
			return "A030";
		default:
			return formatFlightLevel($flight["planned_altitude"]);
	}
}

$encodedString = json_encode($storeData);
file_put_contents('data.txt', $encodedString);
