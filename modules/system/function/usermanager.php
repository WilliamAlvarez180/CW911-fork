<?php
//error_reporting(E_ERROR);
require_once $vars["lib"]."DB.php";

function fnc_getUserlistDT() 
{
	global $response;
	$ds = new DataSource();
	$userList = array();

	if (!@$ds->err) { //Break if any problem connecting to database.
		$idResult = $ds->select("SELECT ID, User FROM users");
		if(!empty($idResult)) {
			foreach ($idResult as $k => $v) {
				$userList[$v["ID"]]["username"] = $v["User"];
			}
			$attrResult = $ds->select("SELECT * FROM userdata");
			foreach ($attrResult as $k => $v) {
				$userList[$v["ID"]][$v["attr"]] = $v["val"];
			}
			foreach ($userList as $k => $v) {
				$userListDT[] = array($k, $v["username"], trim(@$v["fname"] ." ". @$v["sname"] ." ". @$v["flname"] ." ". @$v["slname"]) ?: null, @$v["IC"], @$v["worker_position"], @$v["worker_status"]);
			}
			$response["DataTable"] = $userListDT;
		}
	}
	send();
}

function fnc_getUserDetailsDT() 
{
	global $response;
	$ds = new DataSource();

	if (!@$ds->err) { //Break if any problem connecting to database.
		$attrResult = $ds->getUserData($_GET["uid"]);
		if (!@$ds->err) { //Break if any problem
			foreach ($attrResult as $k => $v) {
				$userListDT[] = [$v["def"], $v["proc"]];
			}
			$response["DataTable"] = $userListDT;
		} else {
			err("db-err", $ds->error);
		}
	}
	send();
}

function fnc_addUser() 
{
	global $response;
	$ds = new DataSource();

	if (!@$ds->err) { //Break if any problem connecting to database.
		$addResult = $ds->execute("INSERT INTO `users` (`User`, `Password`, `accesslevel`) VALUES ('". $_POST["username"] ."', '". password_hash($_POST["password"], PASSWORD_BCRYPT) ."', '". $_POST["accesslevel"] ."')");
		if (@$ds->err) {
			err("db-err", $ds->error);
		}
	}
	send();
}
?>