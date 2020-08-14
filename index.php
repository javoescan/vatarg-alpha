<?php

// EXAMPLE
// [callsign] => 
// [cid] => 
// [realname] =>
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
$aeroparqueDepartures = ["LANDA3B", "BIVAM3B", "ATOVO3B", "DORVO7", "PTA7.GBE", "PTA7.KOVUK", "PTA7.TEDAR",
"EZE8.URINO", "EZE8.GBE", "EZE8.TORUL", "EZE8.ASADA", "PAL8.NEPIS", "PAL8.TORUL", "PAL8.GBE", "KUKEN7", "SURBO7"];
$ezeizaDepartures = ["LANDA2A", "BIVAM2A", "ATOVO2A", "PTA6B.DORVO", "PTA6B.ESLAN", "PTA6B.KOVUK", "PTA6B.TEDAR", 
"PTA6A.DORVO", "PTA6A.ESLAN", "PTA6A.KOVUK", "PTA6A.TEDAR","GBE6", "TORUL1.TORUL", "TORUL1.URINO", "TORUL1.ASADA"];

$jsonSrc = file_get_contents("http://cluster.data.vatsim.net/vatsim-data.json");
$json = json_decode($jsonSrc, true);

$flights = array_filter($json["clients"], function($connection) {
	return $connection["clienttype"] === "PILOT";
});

$argFlights = array_filter($flights, function($flight) {
	$departure = getPrefixAirport($flight["planned_depairport"]);
    return $departure === "SA";
});

$bairesDepartures = array_filter($argFlights, function($flight) use ($bairesAirports) {
	$departure = $flight["planned_depairport"];
	$latitude = round($flight["latitude"], 0);
	$longitude = round($flight["longitude"], 0);
	$isOnTheGround = $latitude > -36 && $latitude < -32 && $longitude > -60 && $longitude < -56;
	return in_array($departure, $bairesAirports) && $isOnTheGround && $flight["groundspeed"] < 40;
});

$storeData = [];

$localFile = file_get_contents("data.txt");
$storedData = json_decode($localFile, true);
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
  		<input class="form-control text-center" id="search-input" type="text" placeholder="Filtrar" />
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
						$departureTime = formatDepartureTime($flight["planned_deptime"]);
						$transponder = getTransponder($flight);
						$flightLevel = formatFlightLevel($flight["planned_altitude"]);
						$departure = getDeparture($flight);
						$initialClimb = getInitialClimb($flight);

						if (!isset($storeData[$flight["callsign"]])) {
							$storeData[$flight["callsign"]] = [
								"transponder" => $transponder,
							];
						}

						echo "<tr class='clickable-row'>";
						echo "<td>" . $flight["callsign"] . "</td>";
						echo "<td>" . $departureTime . "z</td>";
						echo "<td>" . $flight["planned_depairport"] . "</td>";
						echo "<td>" . $flight["planned_destairport"] . "</td>";
						echo "<td>" . $flightLevel . "</td>";
						echo "<td>" . $departure . "</td>";
						echo "<td>" . $initialClimb . "</td>";
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
					localStorage.setItem('filter', value);
					$("#flights tr").filter(function() {
						$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
					});
				});
				$('#flights').on('click', '.clickable-row', function(event) {
					$(this).addClass('active').siblings().removeClass('active');
				});
				document.getElementById("search-input").value = localStorage.getItem('filter') || '';
				$("#search-input").keyup();
				setInterval(() => {
					location.reload();
				}, 60000);
			});
		</script>
	</body>
</html>

<?php // HELPERS
function getTransponder($flight) {
	global $storedData;
	if (is_array($storedData) && isset($storedData[$flight["callsign"]])) {
		return $storedData[$flight["callsign"]]["transponder"];
	}
	$arrival = getPrefixAirport($flight["planned_destairport"]);
	if ($arrival === "SA") {
		return getNationalTransponder($flight);
	} else {
		return getInternationalTransponder($flight);
	}
}

function getNationalTransponder($flight) {
	global $storedData;
	do {
		$transponder = rand(1500, 1777);
	} while (
		strpos($transponder, "8") !== FALSE ||
		strpos($transponder, "9") !== FALSE ||
		strlen($transponder) !== 4 ||
		is_array($storedData) && array_filter($storedData, function($flight){
			return ($flight["transponder"] == $transponder);
		})
	);
	return $transponder;
}

function getInternationalTransponder($flight) {
	global $storedData;
	do {
		$transponder = rand(300, 577);
	} while (
		strpos($transponder, "8") !== FALSE ||
		strpos($transponder, "9") !== FALSE ||
		strlen($transponder) !== 3 ||
		is_array($storedData) && array_filter($storedData, function($flight){
			return ($flight["transponder"] == $transponder);
		})
	);
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

function formatDepartureTime($time) {
	switch(strlen($time)) {
		case 3:
			return "0" . $time;
		case 2:
			return "00" . $time;
		default:
			return $time;
	}
}

function getDeparture($flight) {
	global $aeroparqueDepartures;
	global $ezeizaDepartures;
	switch($flight["planned_depairport"]) {
		case "SABE":
			return getSID($flight["planned_route"], $aeroparqueDepartures);
		case "SAEZ":
			return getSID($flight["planned_route"], $ezeizaDepartures);
		case "SADP":
			return getPalomarDeparture($flight);
		case "SADF":
			return "RWY HDG";
		case "SADM":
			return "RWY HDG";
		default:
			return formatFlightLevel($flight["planned_altitude"]);
	}
}

function getPalomarDeparture($flight) {
	if (
		strpos($flight["planned_route"], "ATOVO") !== FALSE ||
		strpos($flight["planned_route"], "LANDA") !== FALSE ||
		strpos($flight["planned_route"], "BIVAM") !== FALSE ||
		strpos($flight["planned_route"], "KUKEN") !== FALSE ||
		strpos($flight["planned_route"], "PAPIX") !== FALSE ||
		strpos($flight["planned_route"], "DORVO") !== FALSE ||
		strpos($flight["planned_route"], "GBE") !== FALSE
	) {
		return "H080";
	}
	return "R280";
}

function getSID($route, $departures) {
	$sid = "";
	foreach ($departures as $departure) {
		$matchesSid = strpos($route, substr($departure, 0, 3)) !== FALSE;
		$matchesTransition = !$matchesSid && strpos($route, substr($departure, strpos($departure, ".") + 1, 3)) !== FALSE;
		if ($matchesSid || $matchesTransition) {
			$sid = $departure;
			break;
		}
	}
	if (strpos($sid, "EZE8") !== FALSE) {
		return str_replace("EZE8", "PAL8", $sid);
	}
	return $sid;
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
		case "SADF":
			return "A015";
		case "SADM":
			return "A020";
		default:
			return formatFlightLevel($flight["planned_altitude"]);
	}
}

$encodedString = json_encode($storeData);
file_put_contents('data.txt', $encodedString);
