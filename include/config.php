<?php

class Device{

	public $notFound = "404.html";
	public $dbLink;

	public function connectDB(){
		try {
			$host='localhost';
			$dbname='findmyiphone';
			$user='root';
			$pass='yourpassword';
			$driver = array(PDO :: MYSQL_ATTR_INIT_COMMAND => 'SET NAMES `utf8mb4`');
			$DBH = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass,$driver);

		}catch(PDOException $e) {
			echo $e->getMessage('test');
		}
		$this->dbLink=$DBH;
		$this->dbLink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function searchDevice($device_id){
		if(!$this->dbLink) {
			die('erro de conexao');
		}else{
			$query = $this->dbLink->prepare("SELECT * FROM devices WHERE device_id=?");
			$query->execute(array($device_id));
			$dados = array();
			while($row = $query->fetch()) {
				$dados[] = $row;
			}
			return $dados;
		}
	}

	public function searchAllDevices(){
		if(!$this->dbLink) {
			die('erro de conexao');
		}else{
			$query = $this->dbLink->prepare("SELECT * FROM devices");
			$query->execute(array());
			$dados = array();
			while($row = $query->fetch()) {
				$dados[] = $row;
			}
			return $dados;
		}
	}

	public function searchAllLocationFromDevice($device_id,$dateStart,$dateFinish){
		if(!$this->dbLink) {
			die('erro de conexao');
		}else{
			$query = $this->dbLink->prepare("SELECT * FROM location WHERE device_id=? AND time_stamp BETWEEN ? AND ? ORDER BY time_stamp ASC");
			$query->execute(array($device_id,$dateStart,$dateFinish));
			$dados = array();
			while($row = $query->fetch()) {
				$dados[] = $row;
			}
			return $dados;
		}
	}

	public function searchTimeStamp($time_stamp){
		if(!$this->dbLink) {
			die('erro de conexao');
		}else{
			$query = $this->dbLink->prepare("SELECT COUNT(*) as count FROM location WHERE time_stamp=?");
			$query->execute(array($time_stamp));
			$row = $query->fetch();
			if($row['count'] > 0){
				return true;
			}else{
				return false;
			}
		}
	}

	public function newDevice($device_id, $device_name,$status,$location_enabled,$model_display_name,$device_display_name,$battery_level,$battery_status){
		$data = array($device_id, $device_name,$status,$location_enabled,$model_display_name,$device_display_name,$battery_level,$battery_status);
		$query = $this->dbLink->prepare("INSERT INTO devices (device_id, device_name, status, location_enabled, model_display_name,device_display_name,battery_level,battery_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
		$query->execute($data);
	}

	public function newLocation($device_id, $longitude, $latitude, $time_stamp, $position_type){
		$data = array($device_id, $longitude, $latitude, $time_stamp, $position_type);
		$query = $this->dbLink->prepare("INSERT INTO location (device_id, longitude, latitude, time_stamp, position_type) VALUES (?, ?, ?, ?, ?)");
		$query->execute($data);
	}

	public function updateDevice($device_name, $status, $location_enabled, $battery_level, $battery_status, $device_id){
		$data = array($device_name, $status, $location_enabled, $battery_level, $battery_status, $device_id);
		$query = $this->dbLink->prepare("UPDATE devices SET device_name=?, status=?, location_enabled=?, battery_level=?, battery_status=? WHERE device_id=? ");
		$query->execute($data);
	}

}
?>
