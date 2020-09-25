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

$jsonSrc = file_get_contents("http://cluster.data.vatsim.net/vatsim-data.json");
$json = json_decode($jsonSrc, true);

if (!isset($_GET["fir"]) || $_GET["fir"] === "") {
    header("Location: index.php");
}
$fir = $_GET["fir"];
$data = json_decode(file_get_contents("./data/" . $fir  . ".json"), true);
$transponders = $data["transponders"];
$fir = strtoupper($fir);

if (isset($_GET["airport"]) && $_GET["airport"] !== "") {
    $airport = strtoupper($_GET["airport"]);
    $data = array($airport => $data[$airport]);
}

$flights = array_filter($json["clients"], function($connection) {
	return $connection["clienttype"] === "PILOT";
});

$argFlights = array_filter($flights, function($flight) {
	$departure = getPrefixAirport($flight["planned_depairport"]);
    return $departure === "SA";
});

$departures = array_filter($argFlights, function($flight) use ($data) {
	$departure = $flight["planned_depairport"];
	if (!array_key_exists($departure, $data)) {
		return false;
	}
	$airport = $data[$departure];
	$latitude = round($flight["latitude"], 0);
	$longitude = round($flight["longitude"], 0);
	$isOnTheGround =
		$latitude >= $airport['latitude'] - 1 &&
		$latitude <= $airport['latitude'] + 1 &&
		$longitude >= $airport['longitude'] - 1 &&
		$longitude <= $airport['longitude'] + 1 &&
		$flight["groundspeed"] < 40;
	return $isOnTheGround;
});

usort($departures, function($a, $b) {
    return strcmp($a["planned_deptime"], $b["planned_deptime"]);
});

$storeData = [];

$localFile = file_get_contents("flights_data.json");
$storedData = json_decode($localFile, true);

$activeRunwaysFile = file_get_contents("active_runways.json");
$activeRunways = json_decode($activeRunwaysFile, true);

if (isset($airport)) {
	if (isset($activeRunways[$airport])) {
		$activeDepRunway = $activeRunways[$airport]["dep"];
		$activeArrRunway = $activeRunways[$airport]["arr"];
	} else {
		$activeDepRunway = array_key_first($data[$airport]["departures"]);
		$activeArrRunway = array_key_first ($data[$airport]["departures"]);
	}
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>VATARG Alpha</title>
		<link rel="shortcut icon" type="image/png" href="./img/favicon.png">
		<link rel="stylesheet" href="./css/bootstrap.min.css">
		<link rel="stylesheet" href="./css/styles.css?v=<?=time()?>">
		<script src="./js/jquery-3.5.1.slim.min.js"></script>
		<script src="./js/bootstrap.min.js"></script>
	</head>
	<body class="d-none">
        <div class="title-container">
            <div class="controller-name">
                <a class="back-btn d-inline" href="./index.php">
                    <img src="./img/back.png" class="back-btn-image" alt="back-button">
    			</a>
                <h4 class="pl-2 ml-2 text-white name d-inline border-left">Controller name</h4> 
            </div>
            <h2 class="text-center title">VATSIM Argentina Alpha System 2.0</h2>
            <h4 class="mr-3 logout-text"><a href="#" class="text-white" id="logout">Logout</a></h4>
        </div>
		<?php if (count($departures)) { ?>
		<div class="selectors">
			<?php if (isset($airport)) {
				?>
					<div class="runways-selector">
						<div class="runway-selector">
							<h5>DEP</h5>
							<?php
								foreach ($data[$airport]["departures"] as $runway => $_) {
									?>
										<div>
											<button 
												class="btn btn-light runway-btn <?= $runway == $activeDepRunway ? 'runway-active' : ''?>"
												onclick="updateRunway('<?=$runway?>', 0)"
											>
												<?=$runway?>
											</button>
										</div>
									<?php 
								}
							?>
						</div>
						<div class="runway-selector">
							<h5>ARR</h5>
							<?php
								foreach ($data[$airport]["departures"] as $runway => $_) {
									?>
										<div>
											<button
												class="btn btn-light runway-btn <?= $runway == $activeArrRunway ? 'runway-active' : ''?>"
												onclick="updateRunway('<?=$runway?>', 1)"
											>
												<?=$runway?>
											</button>
										</div>
									<?php 
								}
							?>
						</div>
					</div>
				<?php
				}
			?>
			<div class="search-container">
				<input class="form-control text-center" id="search-input" type="text" placeholder="Filtrar" />
			</div>
		</div>
		<table class="table table-striped">
			<thead class="thead-dark">
				<tr>
					<th scope="col">Callsign</th>
					<th scope="col">ETD</th>
					<th scope="col">Departure</th>
					<th scope="col">Arrival</th>
					<th scope="col">Altitude</th>
					<th scope="col">Departure</th>
					<th scope="col">Initial Climb</th>
					<th scope="col">Transponder</th>
				</tr>
			</thead>
			<tbody id="flights">
				<?php
					foreach ($departures as $flight) {
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
				}, 120000);
			});
			<?php if (isset($airport)) { ?>
			function updateRunway(runway, type) {
				$.ajax({
					url: "update_runway.php",
					type: 'POST',
					data: {
						airport: '<?=$airport?>',
						runway: runway,
						type: type,
					},
					success: function(result){
						location.reload();
					},
				});
			}
			<?php } ?>
		</script>
		<?php } else { ?>
            <h2 class="text-center no-results">No flights here &#128532</h2>
		<?php } ?>
        <script>
            $("#logout").click(function(e){
				e.preventDefault();
				$.ajax({url: "modulo/logout.php"});
				window.localStorage.clear(); 
				window.location.href = "index.php"; 
            });
            $.ajax({
				url: "modulo/is_logged.php",
				crossDomain: true,
				dataType: 'text',
				async: false,
				success: function(response){
					var Storage=window.localStorage.getItem('token');
					if(response=='false' && Storage === null){
						window.location.href = "index.php"; 
					}else{
						if(Storage!==response){
							window.localStorage.clear();
							window.location.href = "index.php";
						}
					}
				}
            });
			$(document).ready(function(){
			    $('body').removeClass('d-none');
			    userStorage=window.localStorage.getItem('user'); 
                user=JSON.parse(userStorage); 
                $('.name').text(user.data.personal.name_full); 
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
	return generateTransponder($flight);
}

function generateTransponder($flight) {
	global $storedData;
	global $transponders;
	$transpondersRange = [];
	if ($flight["planned_flighttype"] === "V") {
		$transpondersRange = $transponders["vfr"];
	} else if (getPrefixAirport($flight["planned_destairport"]) === "SA") {
		$transpondersRange = $transponders["nat"];
	} else {
		$transpondersRange = $transponders["int"];
	}
	do {
		$transponder = rand($transpondersRange[0], $transpondersRange[1]);
	} while (
		strpos($transponder, "8") !== FALSE ||
		strpos($transponder, "9") !== FALSE ||
		is_array($storedData) && array_filter($storedData, function($flight){
			return ($flight["transponder"] == $transponder);
		})
	);
	if (strlen($transponder) === 3) {
		$transponder = "0" . $transponder;
	} else if (strlen($transponder) === 3) {
		$transponder = "00" . $transponder;
	}
	return $transponder;
}

function formatFlightLevel($flightLevel) {
	if (strpos($flightLevel, "FL") !== FALSE) {
		return "F" . substr($flightLevel, 2, 3);
	} else if (strpos($flightLevel, "F") === FALSE) {
		if (strlen($flightLevel) === 4) {
			if ((int)$flightLevel <= 3000) {
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
	switch($flight["planned_depairport"]) {
		case "SADP":
			return getPalomarDeparture($flight);
		case "SADF":
			return "RWY HDG";
		case "SADM":
			return "RWY HDG";
		default:
			return getSID($flight["planned_depairport"], $flight["planned_route"]);
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

function getSID($departure, $route) {
	global $data;
	global $activeDepRunway;
	global $activeRunways;
	$sid = "";
	$airport = $data[$departure];
	if (isset($activeDepRunway)) {
		$departures = array_values($airport["departures"][$activeDepRunway]);
	} else if (isset($activeRunways[$departure])) {
		$departures = array_values($airport["departures"][$activeRunways[$departure]["dep"]]);
	} else if (array_values($airport["departures"])[0] !== null) {
		$departures = array_values($airport["departures"])[0];
	}
	
	$arr = explode(' ', trim($route));
	$plannedDeparture = $arr[0];
	if (in_array($plannedDeparture, $departures)) {
		$sid = $plannedDeparture;
	} else {
		foreach ($departures as $departure) {
			$matchesSid = strpos($route, substr($departure, 0, 3)) !== FALSE;
			$matchesTransition = !$matchesSid && strpos($route, substr($departure, strpos($departure, ".") + 1, 3)) !== FALSE;
			if ($matchesSid || $matchesTransition) {
				$sid = $departure;
				break;
			}
		}
	}
	if (strpos($sid, "EZE8") !== FALSE) {
		return str_replace("EZE8", "PAL8", $sid);
	}
	if ($sid === "") {
		$sid = "BY ATC";
	}
	return $sid;
}

function getPrefixAirport($airport) {
	return substr($airport, 0, 2);
}

function getInitialClimb($flight) {
	global $data;
	$airport = $data[$flight["planned_depairport"]];
	return $airport["initClimb"] !== null ? $airport["initClimb"] : "BY ATC";
}

$encodedString = json_encode($storeData);
file_put_contents('flights_data.json', $encodedString);
