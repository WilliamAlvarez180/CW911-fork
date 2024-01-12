<?php
error_reporting(E_ERROR);
global $vars;
require_once $vars["lib"]."DB.php";

$ds = new DataSource();

if (!@$ds->err) { //Break if any problem connecting to database.
$query = "select * FROM users WHERE User = ?";
$paramType = "s";
$paramArray = array(filter_var($_POST["user_name"]));
$memberResult = $ds->select($query, $paramType, $paramArray);
if(!empty($memberResult)) {
	if (password_verify($_POST["password"], $memberResult[0]["Password"])) {
		$_SESSION["logged"] = true;
		$_SESSION["uid"] = $memberResult[0]["ID"];
		$_SESSION["ual"] = $memberResult[0]["accesslevel"];
		
		$attrResult = $ds->getUserData($memberResult[0]["ID"], array("fname", "flname"));
		$_SESSION["uName"] = trim(@$attrResult["fname"]["value"] ." ". @$attrResult["flname"]["value"]) ?: $memberResult[0]["User"];
	}
}
} 
?>