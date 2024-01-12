<?php
//error_reporting(E_ERROR);

function api_IC()
{
	global $response;
	$CI = $_GET["IC"];
	//$nac = $_POST["nac"];
	ini_set('default_socket_timeout', 10); // 10Sec timeout
	
	$CNE = file_get_contents('http://200.11.144.25/web/registro_electoral/ce.php?nacionalidad=V&cedula=' . $CI);
	
	if ($CNE) {
		// Crear un objeto DOMDocument y cargar HTML para procesarlo con XPath
		libxml_use_internal_errors(true);
		$doc = new DOMDocument();
		$doc->loadHTML($CNE);
		$xpath = new DOMXPath($doc);
	} else {
		$response['errCount']++;
		$response['message'] = 'Error al conectar a CNE';
	}

	if (!empty($doc)) { //la tabla con los datos se encuentra en /body/table/tr/td/table/tr[5]/td/table/tr[2]/td/table[1]
		$response['icName'] = $xpath->query('/html/body/table/tr/td/table/tr[5]/td/table/tr[2]/td/table[1]/tr[2]/td[2]/b')->item(0)->textContent;
	}
}

//Evaluar la solicitud de API
switch ($_GET['action']) {
	case "IC":
        api_IC(); 
        break;
}
?>