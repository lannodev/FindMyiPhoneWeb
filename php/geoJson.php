<?php

date_default_timezone_set('America/Sao_Paulo');
require_once '../include/config.php';

class JSONResponse {
    public $dados = array();
}

//convert date
$dateStart = $_POST["dateStart"];
$allDevices = $_POST["devices"];
$dateFinish = $_POST['dateFinish'];
$date1 = str_replace('/', '-', $dateStart);
$date2 = str_replace('/', '-', $dateFinish);
$dateStart = date('Y-m-d H:i:s', strtotime($date1));
$dateFinish = date('Y-m-d H:i:s', strtotime($date2));



$retorno = new JSONResponse();

$device = new Device();
$device->connectDB();

try {

	$feature = array();
	$coordinates = array();
	$timestamp = array();
	$colors = ['#2ecc71','#2196f3','#1abc9c','#9b59b6','#e67e22','#e74c3c','#16a085','#27ae60','#2980b9','#8e44ad'];
	$countColor = 0;

	# Build GeoJSON feature collection array
	$geojson = array();

	if (count($allDevices) > 0 ) {

		for ($a = 0; $a <= count($allDevices) -1; $a++) {

			$location = $device->searchAllLocationFromDevice($allDevices[$a],$dateStart,$dateFinish);

			for ($b = 0; $b <= count($location) -1; $b++) {

				$time = strtotime($location[$b]['time_stamp']);
				array_push($coordinates, [(float)$location[$b]['longitude'],(float)$location[$b]['latitude']]);
				array_push($timestamp, $time*1000);
			}

			if (count($coordinates) > 0) {

				$devices = $device->searchDevice($allDevices[$a]);

				if ($countColor > count($colors)-1) {
					$countColor = 0;
				}

				$status = "";
				if ($devices[0]['status'] == true) {
					$status = "Online";
				}else{
					$status = "Offline";
				};

				$level = round($devices[0]['battery_level'] * 100);

				$feature = array(
				'type' => 'Feature',
				'geometry' => array(
				    'type' => 'MultiPoint',
				    # Pass Longitude and Latitude Columns here
				    'coordinates' => $coordinates,
				),
				# Pass other attribute columns here
				'properties' => array(
				    'name' => $devices[0]['device_name'],
				    'time' => $timestamp,
				    'status'=> $status,
				    'model' => $devices[0]['device_display_name'],
				    'battery_level' => "$level%",
				    'battery_status' => $devices[0]['battery_status'],
				    'color' => $colors[$countColor],
				    )
				);
				# Add feature arrays to feature collection array
				array_push($geojson, $feature);

				$coordinates = [];
				$timestamp = [];

				$countColor++;
			};
		};

		$retorno = $geojson;
	};


} catch (exception $e) {
	echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
};

echo json_encode($retorno);

?>
