<?php
session_name("cw911");
@session_start();

require_once __DIR__.'/vendor/autoload.php';

//ESTABLECER VARIABLES GLOBALES
$vars["mods"] = "./modules/";
$vars["lib"] = "./lib/";

//IMPORTAR MÓDULOS
require_once $vars["lib"] ."Core.php";
require_once $vars["lib"] ."DB.php";
require_once $vars["lib"] ."MQ.php";

//CONECTAR A LA BASE DE DATOS
$DB = new DataSource();
if (@$DB->err) {echo "Core DBERR";}

//INICIALIZAR COLA DE MENSAJES
$MQ = new MQ();

//Función SPA: Variables AJAX
$response["status"] = "OK";

//Función SPA: Respuesta AJAX
//  Algunos módulos pueden requerir respuestas que no sean JSON...
//  Esta función se utiliza solo para respuestas en JSON y se usa cuando no quedan tareas pendientes.
function send() {
	global $response;
	echo json_encode($response);
	exit();
}

//Función SPA: Mensajes
//  Los tipos de mensaje son:
//    0: Info
//    1: Success
//    2: Warning
//    3: Error
function msg($message, $msgType=0) {
	global $response;
	if (!@$_GET["noErr"]) $response["msgStack"][] = array("text" => $message, "type" => $msgType);
}

//Función SPA: Errores
function err($errCode, $errMsg=null) {
	global $response;
	$response["status"] = "ERR";
	$response["errStack"][] = $errCode;
	if ($errMsg) msg($errMsg, 3);
}

//AUTENTICAR
function auth() {
	global $response, $DB;

	$query = "select * FROM users WHERE User = ?";
	$paramType = "s";
	$paramArray = array(filter_var($_POST["user_name"]));
	$memberResult = $DB->select($query, $paramType, $paramArray);
	if(!empty($memberResult)) {
		if (password_verify($_POST["password"], $memberResult[0]["Password"])) {
			$_SESSION["logged"] = true;
			$_SESSION["uid"] = $memberResult[0]["ID"];
			$_SESSION["ual"] = $memberResult[0]["accesslevel"];
			
			$attrResult = $DB->getUserData($memberResult[0]["ID"], array("fname", "flname"));
			$_SESSION["uName"] = trim(@$attrResult["fname"]["value"] ." ". @$attrResult["flname"]["value"]) ?: $memberResult[0]["User"];
		}
	}
	
	if (!@$_SESSION["logged"]) {err("not-logged", "Autenticación incorrecta"); send();}
	ob_start(); require "core/page.main.php"; $response["modPage"]=ob_get_clean();
    send();
}

//Llamada de Función de módulo
function getFunction($fnc) {
	global $vars;
	$fncScript = explode (".", $fnc)[0];
	$fncName = @explode (".", $fnc)[1];
	
	if (file_exists($vars["modFnc"] .$fncScript. ".php")) { require $vars["modFnc"] .$fncScript. ".php";
	} else { err("function-script-not-found", "Función no encontrada"); send(); }
	if (function_exists("fnc_" .$fncName)) { call_user_func("fnc_" .$fncName);
	} else { err("function-not-found", "Función no encontrada"); send(); }
}

//CARGAR MODULO
function getModule() {
	global $DB, $response, $vars;

	if ($_GET['module'] == "core.homepage" || $DB->Authorize()) {
		ob_start(); require $vars["mod"] ."index.php"; $modContent = ob_get_clean();
		$modConf = parse_ini_file($vars["mod"] ."menu.ini");

		$response["modIcon"] = $modConf["icon"];
		$response["modTitle"] = $modConf["name"];
		$response["modContent"] = $modContent;
	} else {
		err("not-authorized", "Acceso limitado.");
	}
	
	send();
}

//Obtener datos del usuario si está logoneado
if(@$_SESSION["logged"]) $userdata = $DB->getUserData(-1);

//Si la solicitud requiere de un módulo, hacer las siguientes tareas:
if (!empty($_GET['module'])) {
	if ($_GET['module'] == "homepage") {
		if (@$userdata["homepage"]["value"]) {
			$_GET['module'] = $userdata["homepage"]["value"];
		} else {
			$_GET['module'] = "core.homepage";
		}
	}
	$vars["modBaseName"] = 		@explode (".", $_GET['module'])[0];
	$vars["modSectionName"] = 	@explode (".", $_GET['module'])[1];
	$vars["modName"] = 			str_replace(DIRECTORY_SEPARATOR, ".", $_GET['module']);
	$vars["modBase"] = 			$vars["mods"]. explode (".", $_GET['module'])[0] .DIRECTORY_SEPARATOR;
	$vars["mod"] = 				$vars["mods"]. str_replace(".", DIRECTORY_SEPARATOR, $_GET['module']) .DIRECTORY_SEPARATOR;
	$vars["modLib"] = 			$vars["modBase"] . "lib" . DIRECTORY_SEPARATOR;
	$vars["modFnc"] = 			$vars["modBase"]. "function" .DIRECTORY_SEPARATOR;

	//Cargar estado, configuracion y datos del módulo
	$vars["modCFG"] = $DB->getModCFG($vars["modBaseName"]);
}

//Si no hay parámetros REQ, entonces la solicitud no es AJAX, por lo que se envía la página
if (empty($_GET["req"])) {require "./core/mainpage.php"; exit();}

//Comprobar estado de sesión / Manejar sesión
if(!@$_SESSION["logged"] && $_GET["req"]!="login") {err("not-logged", "No se ha iniciado sesión"); send();} //Bloquear la solicitud si no ha iniciado sesión
if ($_GET["req"] == "login") auth(); //Proceso de inicio de sesión
if ($_GET["req"] == "logout") { //Cerrar sesión
	session_destroy();
	$MQ->close_sse_session();
	$response["modTitle"]="Iniciar Sesión";
	ob_start(); require "core/page.login.php"; $response["modPage"]=ob_get_clean(); 
	send();
}

//Evaluar solicitud.
	switch ($_GET["req"]) { //Evaluar el tipo de solicitud
		case "module":
			getModule();
			break;
		case "function":
			getFunction(@$_GET['function']);
			break;
		case "mod-cfg":
			if ($DB->Authorize()) {
				if (!$DB->setModCFG($vars["modBaseName"], array_merge($vars["modCFG"], json_decode($_POST["cfg"], true)))) {
					err("db-err", $DB->error);
				}
			} else {
				err("not-authorized", "Acceso limitado.");
			}
			send();
		case "SSE":
			$MQ->SSE();
		case "signalSSE":
			$MQ->signalSSE(@$_GET['target'], @$_GET['signal']);
			break;
		default:
			err("unknown-request"); send();
	}
?>