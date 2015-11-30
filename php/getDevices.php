<?php

date_default_timezone_set('America/Sao_Paulo');
require_once '../include/config.php';

class JSONResponse {
    public $dados = array();
}

$retorno = new JSONResponse();

$device = new Device();
$device->connectDB();

try {

	$retorno = $device->searchAllDevices();

} catch (exception $e) {
	echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
};

echo json_encode($retorno);

?>
