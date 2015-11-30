<?php

date_default_timezone_set('America/Sao_Paulo');

require_once '../include/classes/FindMyiPhone.php';
require_once '../include/config.php';

$device = new Device();
$device->connectDB();

try {

	$FindMyiPhone = new FindMyiPhone('YOUREMAIL', 'YOURPASSWORD');

	if (count($FindMyiPhone->devices) > 0) {

		for ($i = 0; $i <= count($FindMyiPhone->devices) -1; $i++) {

			$location = $FindMyiPhone->devices[$i]->location;
			$status = true;
			$id = $FindMyiPhone->devices[$i]->id;
			$name = $FindMyiPhone->devices[$i]->name;
			$locationEnabled = $FindMyiPhone->devices[$i]->locationEnabled;
			$modelDisplayName = $FindMyiPhone->devices[$i]->modelDisplayName;
			$deviceDisplayName = $FindMyiPhone->devices[$i]->deviceDisplayName;
			$batteryLevel = $FindMyiPhone->devices[$i]->batteryLevel;
			$batteryStatus = $FindMyiPhone->devices[$i]->batteryStatus;

			//location
			if ($location != "") {
				$status = true;
				$longitude = $FindMyiPhone->devices[$i]->location->longitude;
				$latitude = $FindMyiPhone->devices[$i]->location->latitude;
				$timeStamp = date('Y-m-d H:i:s', $FindMyiPhone->devices[$i]->location->timeStamp/1000);
				$positionType = $FindMyiPhone->devices[$i]->location->positionType;
				$searchTimeStamp = $device->searchTimeStamp($timeStamp);

				if ($searchTimeStamp == false) {
					$device->newLocation($id, $longitude, $latitude, $timeStamp, $positionType);
					echo "Nova Localizacao para o device: $name \n";
				};
			}else {
				$status = false;
			};

			$searchDevice = $device->searchDevice($id);
			if (count($searchDevice) <= 0) {
				$device->newDevice($id, $name, $status, $locationEnabled, $modelDisplayName, $deviceDisplayName, $batteryLevel, $batteryStatus);
				echo "Novo Device: $name \n";
			}else{
				$device->updateDevice($name, $status, $locationEnabled, $batteryLevel, $batteryStatus, $id);
				echo "Device Atualizado: $name \n";
			};
		};
    };

} catch (exception $e) {
	echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
};

?>
