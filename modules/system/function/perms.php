<?php
//error_reporting(E_ERROR);

function fnc_getAccessLevelsDT()
{
	global $response, $DB;
	$stmt = $DB->select("SELECT `ID`, `name`, `desc` from accesslevels");
	if (@$DB->err) {
		err("db-err", $DB->error);
	}
	$response["DataTable"] = $stmt;
	send();
}

function fnc_getPermsTree()
{
	global $DB, $response;
	$result = $DB->select("SELECT `structure` from modules");
	if (@$DB->err) {
		err("db-err", $DB->error);
	}
	$result2 = $DB->select("SELECT `perms` from accesslevels WHERE ID=" .$_GET["levelID"]);
	if (@$DB->err) {
		err("db-err", $DB->error);
	}
	$stmt = [];
	$permsBool = [];
	foreach ($result as $key => $value) {
		$stmt = array_merge($stmt, json_decode($result[$key]["structure"], true));
	}
	$permsBool = json_decode($result2[0]["perms"], true);
	if (!$permsBool == null) $stmt = array_merge_recursive($stmt, $permsBool);
	
	$response["permsTree"] = $stmt;
	send();
}

function fnc_setAccessLevel()
{
	global $response, $DB;

	$query = "UPDATE accesslevels SET perms='". $_POST["ALPerms"] ."' WHERE ID=". $_POST["ALID"];
	$stmt = $DB->execute($query);

	if (@$DB->err) {
		err("db-err", $DB->error);
	}
	msg("Permisos actualizados", 1);
	send();
}
?>