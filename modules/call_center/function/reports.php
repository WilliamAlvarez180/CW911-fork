<?php
//error_reporting(E_ERROR);
require_once $vars["lib"]."DB.php";
require_once $vars["lib"]."ssp.class.php";

function fnc_load()
{
	global $response;
	$ds = new DataSource();
	if (!@$ds->err) { //Break if any problem connecting to database.
		$stmt = "SELECT * FROM reports WHERE id='" .$_GET["rep_id"]."'";
		$loadResult = $ds->select($stmt);
		if(!empty($loadResult)) {
			$response["data"] = (array)json_decode($loadResult[0]["data"]);
			unset($loadResult[0]["id"]);
			unset($loadResult[0]["data"]);
			foreach ( $loadResult[0] AS $k => $v ) $response["data"][$k] = $v;
		}
		if (@$ds->err) {
			err("db-err", $ds->error);
		}
	}
	send();
}

function fnc_save()
{
	global $response;
	$ds = new DataSource();
	if (!@$_POST["rep_id"]) {send();}
	if (!@$ds->err) { //Break if any problem connecting to database.
		$stmt = "INSERT INTO reports (id, data) VALUES ('".$_POST["rep_id"]."', '".$_POST["report"]."') ON DUPLICATE KEY UPDATE data = '" .$_POST["report"]."'";
		$saveResult = $ds->execute($stmt);
		if (@$ds->err) {
			$response["errCount"]++;
			$response["message"] = $ds->error;
		}
	}
	send();
}

function fnc_getReportsDT ()
{
	global $response, $DB;
	$userList = array();

	$columns = array(
		array( 'db' => 'id', 		'dt' => "id" ),
		array( 'db' => 'desc', 		'dt' => "desc" ),
		array( 'db' => 'status', 	'dt' => "status" ),
		array( 'db' => 'data',  	'dt' => "content" )
	);
	
	// TODO: Quitar apenas esté lista la librería SSP para MySQLi
	$sql_details = array(
		'user' => 'root',
		'pass' => 'Sistemas.VEN911',
		'db'   => 'cw911',
		'host' => 'localhost'
		// ,'charset' => 'utf8' // Depending on your PHP and MySQL config, you may need this
	);
	
	$response = array_merge($response, SSP::simple($_GET, $sql_details, "reports", "ID", $columns)); send();
}	
?>