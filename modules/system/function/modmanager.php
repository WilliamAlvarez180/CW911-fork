<?php
//error_reporting(E_ERROR);
require_once $vars["lib"]."DB.php";

function fnc_reconfigure()
{
	global $response, $DB, $vars;
	$files = array_diff(scandir($vars["mods"]), array('..', '.'));
	$menus = [];
    foreach ($files as $key => $value) {
        $path = realpath($vars["mods"] . DIRECTORY_SEPARATOR . $value);
        if (is_dir($path)) {
            if (file_exists($path . DIRECTORY_SEPARATOR . "module.json")) {
				$modconf = json_decode(file_get_contents($path . DIRECTORY_SEPARATOR . "module.json"), true);
				$response["DS"] = $modconf;
				$menus = array_merge($menus, $modconf["menu"]);
			}
        }
	}
	//$response["SS"] = $menus;
	$addResult = $DB->execute("UPDATE `menu` SET `data`='". json_encode($menus) ."' WHERE `id`='global'");
	if (@$DB->err) {
		err("db-err", $DB->error);
	}
	send();
}
?>