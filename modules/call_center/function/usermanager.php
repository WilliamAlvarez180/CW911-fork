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
				$userListDT[] = array(
					"userid" => $k,
					"user" => $v["username"],
					"username" => trim(@$v["fname"] ." ". @$v["sname"] ." ". @$v["flname"] ." ". @$v["slname"]) ?: null,
					"ic" => @$v["IC"],
					"agent" => boolval(@$v["callcenter_enabled"]),
					"agentID" => @$v["callcenter_id"]
				);
			}
			$response["DataTable"] = $userListDT;
		}
	} 
	send();
}

function fnc_setUserCFG() 
{
	global $response;
	$ds = new DataSource();
	
	$cfg = json_decode($_POST["cfg"], true);
	if (!@$ds->err) { //Break if any problem connecting to database.
		$ds->setUserData($cfg["userID"], "callcenter_enabled", (@$cfg["agentEnabled"]? 1 : 0));
		$ds->setUserData($cfg["userID"], "callcenter_id", (@$cfg["agentID"]?: null));
		if (@$ds->err) {
			err("db-err", $ds->error);
		}
	}
	send();
}
?>