<?php
require_once $vars["modLib"] ."paloSantoConsola.class.php";
require_once $vars["lib"] ."JSON.php";

// Estado inicial de la consola del Call Center. Establecer si no ha sido establecido.
if (!isset($_SESSION['callcenter']) ||
    !is_array($_SESSION['callcenter']) ||
    !isset($_SESSION['callcenter']['estado_consola']))
    $_SESSION['callcenter'] = generarEstadoInicial();

function build_callerbadges($eventinfo) {
    $callerbadges = "";
    $callerbadges .= '<span class="badge text-bg-success me-1 mb-1" data-bs-toggle="tooltip" title="Número de teléfono"><i class="fa fa-phone"></i><div class="vr mx-1"></div>' .$eventinfo[phone]. '</span>';
    $callerbadges .= '<span class="badge text-bg-success me-1 mb-1" data-bs-toggle="tooltip" title="ID de llamada"><i class="fa fa-fingerprint"></i><div class="vr mx-1"></div>' .$eventinfo[call_id]. '</span>';
    return $callerbadges;
}

function fnc_checkStatus() {
	global $MQ, $response;
	
    $MQ->initSSEWorker("call_center.agentconsole");
    
    $respuesta = array();

    ignore_user_abort(true);
    set_time_limit(0);

    $sNombrePausa = NULL;
    $iDuracionLlamada = NULL;
    $iDuracionPausa = $iDuracionPausaActual = NULL;

    @$estadoCliente = $_GET['clientstate'];
	$sAgente = $_SESSION['callcenter']['agente'];
    $sExtension = $_SESSION['callcenter']['extension'];

	$oPaloConsola = new PaloSantoConsola($sAgente);
    $estado = $oPaloConsola->estadoAgenteLogoneado($sExtension);    

    // Validación del estado del cliente:
    // onhold break_id calltype campaign_id callid
    $estadoCliente['onhold'] = isset($estadoCliente['onhold'])
        ? ($estadoCliente['onhold'] == 'true')
        : false;
    foreach (array('break_id', 'calltype', 'campaign_id', 'callid') as $k) {
        if (!isset($estadoCliente[$k]) || $estadoCliente[$k] == 'null' || $estadoCliente[$k] == '')
            $estadoCliente[$k] = NULL;
    }
    if (is_null($estadoCliente['calltype'])) {
        $estadoCliente['campaign_id'] = $estadoCliente['callid'] = NULL;
    } elseif (is_null($estadoCliente['callid'])) {
        $estadoCliente['campaign_id'] = $estadoCliente['calltype'] = NULL;
    } elseif (is_null($estadoCliente['campaign_id']) && $estadoCliente['calltype'] != 'incoming') {
        $estadoCliente['calltype'] = $estadoCliente['callid'] = NULL;
    }
    $estadoCliente['waitingcall'] = isset($estadoCliente['waitingcall'])
        ? ($estadoCliente['waitingcall'] == 'true')
        : false;

    // Respuesta inmediata si el agente ya no está logoneado
    if ($estado['estadofinal'] != 'logged-in') {
        // Respuesta inmediata si el agente ya no está logoneado
        $respuesta[] = array('event' =>  'logged-out');
        $MQ->sendEvent($respuesta, "call_center.agentconsole");
        return;
    }

    // Verificación de la consistencia del estado de break
    if (!is_null($estado['pauseinfo'])) {
        $sNombrePausa = $estado['pauseinfo']['pausename'];
        $iDuracionPausaActual = time() - strtotime($estado['pauseinfo']['pausestart']);
        $iDuracionPausa = $iDuracionPausaActual + $_SESSION['callcenter']['break_acumulado'];
    } else {
        /* Si esta condición se cumple, entonces se ha perdido el evento
         * pauseexit durante la espera en manejarSesionActiva_checkStatus().
         * Se hace la suposición de que el refresco ocurre poco después de
         * que termina el break, y que por lo tanto el error al usar time()
         * como fin del break es pequeño.
         */
        if (!is_null($_SESSION['callcenter']['break_iniciado'])) {
           $_SESSION['callcenter']['break_acumulado'] += time() - strtotime($_SESSION['callcenter']['break_iniciado']);
        }
        $_SESSION['callcenter']['break_iniciado'] = NULL;
    }
    if (!is_null($estado['pauseinfo']) &&
        (is_null($estadoCliente['break_id']) || $estadoCliente['break_id'] != $estado['pauseinfo']['pauseid'])) {
        // La consola debe de entrar en break
        $respuesta[] = array( 'event' =>  'breakenter', 'break_id' => $estado['pauseinfo']['pauseid']);
    } elseif (!is_null($estadoCliente['break_id']) && is_null($estado['pauseinfo'])) {
        // La consola debe de salir del break
        $respuesta[] = construirRespuesta_breakexit();
    }

    // Verificación de la consistencia del estado de hold
    if (!$estadoCliente['onhold'] && $estado['onhold']) {
        // La consola debe de entrar en hold
        $respuesta[] = construirRespuesta_holdenter();
    } elseif ($estadoCliente['onhold'] && !$estado['onhold']) {
        // La consola debe de salir de hold
        $respuesta[] = construirRespuesta_holdexit();
    }

    // Verificación de atención a llamada
    if (!is_null($estado['callinfo']) &&
        (is_null($estadoCliente['calltype']) ||
            $estadoCliente['calltype'] != $estado['callinfo']['calltype'] ||
            $estadoCliente['campaign_id'] != $estado['callinfo']['campaign_id'] ||
            $estadoCliente['callid'] != $estado['callinfo']['callid'])) {

        // Información sobre la llamada conectada
        $infoLlamada = $oPaloConsola->leerInfoLlamada(
            $estado['callinfo']['calltype'],
            $estado['callinfo']['campaign_id'],
            $estado['callinfo']['callid']);

        // Almacenar para regenerar formulario
        $_SESSION['callcenter']['ultimo_callid'] = $estado['callinfo']['callid'];

		// La consola empezó a atender a una llamada
		$respuesta[] = array(
			'event'                 =>  'agentlinked',
			'callid'                =>  $callinfo['callid'],
			'callnumber'            =>  $callinfo['callnumber'],
		);
		
    } elseif (!is_null($estadoCliente['calltype']) && is_null($estado['callinfo'])) {
        // La consola dejó de atender una llamada
        $respuesta[] = array('event' => 'agentunlinked',);
    }

    // Verificación de espera de llamada
    if (!is_null($estado['waitedcallinfo']) && !$estadoCliente['waitingcall']) {
        $respuesta[] = construirRespuesta_waitingenter($oPaloConsola, $estado['waitedcallinfo']);
    } elseif (is_null($estado['waitedcallinfo']) && $estadoCliente['waitingcall']) {
        $respuesta[] = construirRespuesta_waitingexit();
    }

    // Ciclo de verificación para Server-sent Events
    $iTimeoutPoll = $oPaloConsola->recomendarIntervaloEsperaAjax();
    $bReinicioSesion = FALSE;
    do {
        $oPaloConsola->desconectarEspera();

        // Se inicia espera larga con el navegador...
        session_commit();
        $iTimestampInicio = time();

        $respuestaEventos = array();

        $oPaloConsola->pingAgente();
        while (connection_status() == CONNECTION_NORMAL &&
            count($respuestaEventos) <= 0 && count($respuesta) <= 0
            && time() - $iTimestampInicio <  $iTimeoutPoll) {

            $listaEventos = $oPaloConsola->esperarEventoSesionActiva();
            if (is_null($listaEventos)) {
                // Ocurrió una excepción al esperar eventos
                @session_start();
                $respuesta[] = array('event' =>  'logged-out');

                // Eliminar la información de login
                $_SESSION['callcenter'] = generarEstadoInicial();
                $bReinicioSesion = TRUE;
                break;
            }

            foreach ($listaEventos as $evento) switch ($evento['event']) {
            case 'agentloggedout':
                // Reiniciar la sesión para poder modificar las variables
                @session_start();

                $respuesta[] = array('event' =>  'logged-out');

                // Eliminar la información de login
                $_SESSION['callcenter'] = generarEstadoInicial();
                $bReinicioSesion = TRUE;
                break;
            case 'pausestart':
                if (!(isset($evento['agent_number']) && $evento['agent_number'] == $sAgente)) break;
                unset($respuestaEventos[$evento['pause_class']]);
                switch ($evento['pause_class']) {
                case 'break':
                    if (is_null($estadoCliente['break_id']) ||
                        $estadoCliente['break_id'] != $evento['pause_type']) {
                        $sNombrePausa = $evento['pause_name'];
                        $respuestaEventos['break'] = array( 'event' =>  'breakenter', 'break_id' => $evento['pause_type']);
                    }
                    @session_start();
                    $iDuracionPausaActual = time() - strtotime($evento['pause_start']);
                    $iDuracionPausa = $iDuracionPausaActual + $_SESSION['callcenter']['break_acumulado'];
                    $_SESSION['callcenter']['break_iniciado'] = $evento['pause_start'];
                    break;
                case 'hold':
                    if (!$estadoCliente['onhold']) {
                        $respuestaEventos['hold'] = construirRespuesta_holdenter();
                    }
                    break;
                }
                break;
            case 'pauseend':
                if (!(isset($evento['agent_number']) && $evento['agent_number'] == $sAgente)) break;
                unset($respuestaEventos[$evento['pause_class']]);
                switch ($evento['pause_class']) {
                case 'break':
                    if (!is_null($estadoCliente['break_id'])) {
                        $respuestaEventos['break'] = array('event' =>  'breakexit');
                    }
                    @session_start();
                    if (!is_null($_SESSION['callcenter']['break_iniciado'])) {
                        $_SESSION['callcenter']['break_acumulado'] += $evento['pause_duration'];
                        $_SESSION['callcenter']['break_iniciado'] = NULL;
                    }
                    break;
                case 'hold':
                    if ($estadoCliente['onhold']) {
                        $respuestaEventos['hold'] = construirRespuesta_holdexit();
                    }
                    break;
                }
                break;
            case 'agentlinked':
                if (!(isset($evento['agent_number']) && $evento['agent_number'] == $sAgente)) break;
                unset($respuestaEventos['llamada']);
                /* Actualizar la interfaz si entra una nueva llamada, o si
                 * la llamada activa anterior es reemplazada. */
                if (is_null($estadoCliente['calltype']) ||
                    $estadoCliente['calltype'] != $evento['call_type'] ||
                    $estadoCliente['campaign_id'] != $evento['campaign_id'] ||
                    $estadoCliente['callid'] != $evento['call_id']) {
                    
                    $iDuracionLlamada = time() - strtotime($evento['datetime_linkstart']);
                    // TODO: solucionar problema de variables de tiempo. Está entregando 6hr de más.
                    // Solución temporal al bug:
                    $iDuracionLlamada = $iDuracionLlamada - 21600; // -6 Horas
					//$iDuracionLlamada = $iDuracionLlamada - 14400; // -2hr 6min 40seg. Servidor 911

                    // Almacenar para regenerar formulario
                    @session_start();
                    $_SESSION['callcenter']['ultimo_calltype'] = $evento['call_type'];
                    $_SESSION['callcenter']['ultimo_callid'] = $evento['call_id'];

					// La consola empezó a atender a una llamada
					$respuestaEventos['llamada'] = array(
						'event'                 =>  'agentlinked',
                        'calltype'              =>  $evento['call_type'],
						'callid'                =>  $evento['call_id'],
						'callnumber' 			=>  $evento['phone'],
                        'callerbadges'          =>  build_callerbadges($evento),
					);

                    // Si la llamada fue enlazada, entonces ya no está esperando
                    if ($estadoCliente['waitingcall']) {
                        $respuestaEventos['waitingcall'] = construirRespuesta_waitingexit();
                    }
                }
                break;
            case 'agentunlinked':
                if (!(isset($evento['agent_number']) && $evento['agent_number'] == $sAgente)) break;
                unset($respuestaEventos['llamada']);
                    $respuestaEventos['llamada'] = array('event' => 'agentunlinked');
                break;
            }
        } // while(...)

        // Sólo debe haber hasta un evento de llamada, de break, de hold
        if (isset($respuestaEventos['break'])) $respuesta[] = $respuestaEventos['break'];
        if (isset($respuestaEventos['hold'])) $respuesta[] = $respuestaEventos['hold'];
        if (isset($respuestaEventos['llamada'])) $respuesta[] = $respuestaEventos['llamada'];

        // Acumular todos los eventos que no deben de ser únicos
        if (isset($respuestaEventos['other'])) $respuesta = array_merge($respuesta, $respuestaEventos['other']);

        // Agregar los textos a cambiar en la interfaz
        $sDescInicial = describirEstadoBarra($estadoCliente);
        foreach ($respuesta as $evento) switch ($evento['event']) {
        case 'holdenter':
            $estadoCliente['onhold'] = TRUE;
            break;
        case 'holdexit':
            $estadoCliente['onhold'] = FALSE;
            break;
        case 'breakenter':
            $estadoCliente['break_id'] = $evento['break_id'];
            break;
        case 'breakexit':
            $estadoCliente['break_id'] = NULL;
            break;
        case 'agentlinked':
            $estadoCliente['calltype'] = $evento['calltype'];
            $estadoCliente['callid'] = $evento['callid'];
            break;
        case 'agentunlinked':
            $estadoCliente['calltype'] = NULL;
            $estadoCliente['campaign_id'] = NULL;
            $estadoCliente['callid'] = NULL;
            break;
        default:
            break;
        }
        $sDescFinal = describirEstadoBarra($estadoCliente);
        $iPosEvento = count($respuesta) - 1;
        if ($iPosEvento >= 0 && $sDescInicial != $sDescFinal) switch ($sDescFinal) {
        case 'llamada':
            $respuesta[$iPosEvento]['timer_seconds'] = $iDuracionLlamada;
            break;
        case 'break':
            $respuesta[$iPosEvento]['breakName'] = $sNombrePausa;
            $respuesta[$iPosEvento]['timer_seconds'] = $iDuracionPausa;
            break;
        case 'ocioso':
            $respuesta[$iPosEvento]['timer_seconds'] = '';
            break;
        }
        
        $MQ->syncSSEWorker();
        if (!$MQ->workerStop) $MQ->sendEvent($respuesta, "call_center.agentconsole");

        $respuesta = array();

    } while(!$MQ->workerStop && !$bReinicioSesion);
    $oPaloConsola->desconectarTodo();
}

/*
 * La barra de color de la interfaz debe terminar en uno de tres estados:
 * llamada, break, ocioso.
 */
function describirEstadoBarra($estado)
{
    if (!is_null($estado['calltype']))
        return 'llamada';
    if ($estado['waitingcall'])
        return 'esperando';
    if (!is_null($estado['break_id']))
        return 'break';
    return 'ocioso';
}

/* Procedimiento para generar el estado inicial de la información del agente en
 * la sesión PHP.  */
function generarEstadoInicial()
{
    return array(
        /*  Estado de la consola. Los valores posibles son
            logged-out  No hay agente logoneado
            logging     Agente intenta autenticarse con la llamada
            logged-in   Agente fue autenticado y está logoneado en consola
         */
        'estado_consola'    =>  'logged-out',

        /* El número del agente que se logonea. P.ej. 8000 para el agente 8000.
         * En estado logout el agente es NULL.
         */
        'agente'            =>  NULL,

        /* El nombre del agente */
        'agente_nombre'     =>  NULL,

        /* El número de la extensión  e IP interna que se logonea al agente. En estado
           logout la extensión y su IP son NULL
         */
        'extension'         =>  NULL,
		'ext-ip'			=>  NULL,

        /* El último tipo de llamada y el último ID de llamada atendida. Esto
         * permite que se pueda guardar un formulario de una llamada que ya
         * ha terminado */
        'ultimo_calltype'       =>  NULL,
        'ultimo_callid'         =>  NULL,
        'ultimo_callsurvey'     =>  NULL,
        'ultimo_campaignform'   =>  NULL,

        /* Se lleva la cuenta de la duración, en segundos, de los breaks que se
         * han iniciado y terminado durante la sesión. El posible break en curso
         * no se cuenta en break_acumulado. Pero el hecho de que hay un break
         * en curso se registra en break_iniciado por si se refresca la interfaz
         * y se encuentra que el break ha terminado. */
        'break_acumulado'       =>  0,
        'break_iniciado'        =>  NULL,
    );
}


function fnc_loginAgent()
{
	global $response, $userdata;
	$oPaloConsola = new PaloSantoConsola();
	
    // Acción AJAX para iniciar el login de agente
    $bCallback = false; //in_array($_GET['callback'), array('true', 'checked')); //disabled
    if ($bCallback) {
        $sAgente = $_GET['ext_callback'];
        $sPasswordCallback = $_GET['pass_callback'];
        $regs = NULL;
        $sExtension = (preg_match('|^(\w+)/(\d+)$|', $sAgente, $regs)) ? $regs[2]: NULL;
    } else {
        $sAgente = $userdata["callcenter_id"]["value"];
        $sExtension = $_POST['ext'];
        $sPasswordCallback = NULL;
    }

    $bContinuar = TRUE;

    // Verificar que la extensión y el agente son válidos en el sistema
    if ($bContinuar) {
        $listaExtensiones = $oPaloConsola->listarExtensiones();
        $listaAgentes = $oPaloConsola->listarAgentes();
        if (!in_array($sAgente, array_keys($listaAgentes))) {
            $bContinuar = FALSE;
            err('invalid-agent', "ID de Agente inválido");
        } elseif (!in_array($sExtension, array_keys($listaExtensiones))) {
            $bContinuar = FALSE;
            $response['status'] = FALSE;
            $response["message"] = 'invalid-ext';
        }
    }

    // Verificar si el número de agente no está ya ocupado por otra extensión
    if ($bContinuar) {
        $oPaloConsola->desconectarTodo();
        $oPaloConsola = new PaloSantoConsola($sAgente);

        $estado = (!$bCallback || $oPaloConsola->autenticar($sAgente, $sPasswordCallback))
            ? $oPaloConsola->estadoAgenteLogoneado($sExtension)
            : array('estadofinal' => 'error');
        switch ($estado['estadofinal']) {
        case 'error':
        case 'mismatch':
            $response['status'] = FALSE;
            $response["message"] = 'Cannot start agent login - '.$oPaloConsola->errMsg;
            break;
        case 'logged-out':
            // No hay canal de login. Se inicia login a través de Originate para el caso de Agent/xxx
            $bExito = $oPaloConsola->loginAgente($sExtension);
            if (!$bExito) {
                $response['status'] = FALSE;
                $response["message"] = 'Cannot start agent login - '.$oPaloConsola->errMsg;
                break;
            }
            // En caso de éxito, se cuela al siguiente caso
        case 'logging':
        case 'logged-in':
            // Ya está logoneado este agente. Se procede directamente a espera
            $_SESSION['callcenter']['estado_consola'] = 'logging';
            $_SESSION['callcenter']['agente'] = $sAgente;
            $_SESSION['callcenter']['agente_nombre'] = $listaAgentes[$sAgente];
            $_SESSION['callcenter']['extension'] = $sExtension;
            $response["message"] = 'logging';
			//Obtener IP de la Extensión.
			exec("/usr/sbin/asterisk -rx 'sip show peer ".$sExtension."'", $result);
			foreach (preg_grep('/Addr->IP/', $result) as $result_addr) {$_SESSION['callcenter']['ext-ip'] = trim(explode(':', $result_addr)[1]);}

            if ($estado['estadofinal'] != 'logged-in') {
                // Esperar hasta 1 segundo para evento de fallo de login.
                $sEstado = $oPaloConsola->esperarResultadoLogin();
                if ($sEstado == 'logged-in') {
                    /* El agente ha podido logonearse. Se delega el cambio de
                     * estado_consola a logged-in a la verificación de
                     * manejarLogin_checkLogin() */
				} elseif ($sEstado == 'logged-out') {
                    // El procedimiento de login ha fallado, sin causa conocida
                    $_SESSION['callcenter'] = generarEstadoInicial();
                    $response['status'] = FALSE;
                    $response["message"] = 'Agent log-in failed!';
                } elseif ($sEstado == 'error') {
                    // Ocurre un error al consultar el estado del agente
                    $_SESSION['callcenter'] = generarEstadoInicial();
                    $response['status'] = FALSE;
                    $response["message"] = 'Agent log-in failed!'.' - '.$oPaloConsola->errMsg;
                }
            }
            break;
        }
    }

    $oPaloConsola->desconectarTodo();
    
    send();
}

function fnc_remotePhone() {
	global $response;
	
	$opts = array(
		'http'=>array(
			'method'=>"GET"
		)
	);
	$context = stream_context_create($opts);
	
	switch ($_GET['SIPEvent']) {
		case "key":
			$sAction = "key=".$_GET['SIPAction'];
			$break;
		case "dtmf":
			$sAction = "key_dtmf=".urlencode($_GET['SIPAction']);
			$break;
	}
	
	@file_get_contents('http://'.$_SESSION['callcenter']['ext-ip']."/command.htm?".$sAction, false, $context);
    send();
}

function fnc_checkLogin() {
	global $response;

    $bContinuar = TRUE;

    // Verificación rápida para saber si el canal es correcto
    $sAgente = $_SESSION['callcenter']['agente'];
    $sExtension = $_SESSION['callcenter']['extension'];
    $oPaloConsola = new PaloSantoConsola($sAgente);

    if ($bContinuar) {
        $estado = $oPaloConsola->estadoAgenteLogoneado($sExtension);
        switch ($estado['estadofinal']) {
        case 'error':
        case 'mismatch':
            // Otra extensión ya ocupa el login del agente indicado, o error
            $_SESSION['callcenter'] = generarEstadoInicial();
            err("mismatch", 'Cannot start agent login - '.$oPaloConsola->errMsg);
            $bContinuar = FALSE;
            break;
        case 'logged-out':
            // No se encuentra evidencia de que se empezara el login
            $_SESSION['callcenter'] = generarEstadoInicial();
            err('logged-out', 'El proceso de inicio de sesion de agente no ha iniciado');
            $bContinuar = FALSE;
            break;
        case 'logging':
            $_SESSION['callcenter']['estado_consola'] = 'logging';
            $response['action'] = 'wait';
            $response['message'] = 'logging-in';
            break;
        case 'logged-in':
            // El agente ha podido logonearse. Se procede a mostrar el formulario
            $_SESSION['callcenter']['estado_consola'] = 'logged-in';
            $response['message'] = 'logged-in';
            $bContinuar = FALSE;
            break;
        }

    }

    if ($bContinuar && $response['action'] == 'wait') {
        $iTimeoutPoll = $oPaloConsola->recomendarIntervaloEsperaAjax();
        $oPaloConsola->desconectarEspera();

        // Se inicia espera larga con el navegador...
        session_commit();
        set_time_limit(0);
        $iTimestampInicio = time();

        while ($bContinuar && time() - $iTimestampInicio <  $iTimeoutPoll) {

            // Verificar si el agente ya está en línea
            $sEstado = $oPaloConsola->esperarResultadoLogin();
            if ($sEstado == 'logged-in') {
                // Reiniciar la sesión para poder modificar las variables
                session_start();

                // El agente ha podido logonearse. Se procede a mostrar el formulario
                $_SESSION['callcenter']['estado_consola'] = 'logged-in';
                $response['message'] = 'logged-in';
                $bContinuar = FALSE;

            } elseif ($sEstado == 'logged-out') {
                // Reiniciar la sesión para poder modificar las variables
                session_start();

                // El procedimiento de login ha fallado, sin causa conocida
                $_SESSION['callcenter'] = generarEstadoInicial();
                $response['details'] = 'Agent log-in terminated.';
                $response['message'] = 'logged-out';
                $bContinuar = FALSE;
            } elseif ($sEstado == 'error') {
                // Reiniciar la sesión para poder modificar las variables
                session_start();

                // Ocurre un error al consultar el estado del agente
                $_SESSION['callcenter'] = generarEstadoInicial();
                $response['action'] = 'error';
                $response['message'] = 'Agent log-in failed! - '.$oPaloConsola->errMsg;
                $bContinuar = FALSE;
            }
        }
    }

    $oPaloConsola->desconectarTodo();
    send();
}

function fnc_logoutAgent()
{
	global $response;

    $sAgente = $_SESSION['callcenter']['agente'];
    $oPaloConsola = new PaloSantoConsola($sAgente);
	
    $response['action']='logged-out';
    $bExito = $oPaloConsola->logoutAgente();
    if (!$bExito) {
        $response['action'] = 'error';
        $response['message'] = 'Error while logging out agent. - '.$oPaloConsola->errMsg;
    }

    // Se asume que el único error posible en logout es que el agente ya
    // esté deslogoneado.
    $_SESSION['callcenter']['estado_consola'] = 'logged-out';
    $_SESSION['callcenter']['agente'] = NULL;
    $_SESSION['callcenter']['agente_nombre'] = NULL;
    $_SESSION['callcenter']['extension'] = NULL;
    
    send();
}

function fnc_hangup()
{
	global $response;

    $sAgente = $_SESSION['callcenter']['agente'];
    $oPaloConsola = new PaloSantoConsola($sAgente);
	
    $bExito = $oPaloConsola->colgarLlamada();
    if (!$bExito) {
        err('hangup-error', $oPaloConsola->errMsg);
    }
    send();
}

function fnc_break($break=true)
{
	global $response;

    $sAgente = $_SESSION['callcenter']['agente'];
    $oPaloConsola = new PaloSantoConsola($sAgente);
	
    if ($break) { $bExito = $oPaloConsola->iniciarBreak($_POST["breakid"]);
    } else {$bExito = $oPaloConsola->terminarBreak();}
    
    if (!$bExito) {
        $response['action'] = 'error';
        $response['message'] = 'Error while starting break - '.$oPaloConsola->errMsg;
    }
    send();
}

function fnc_unbreak()
{
    fnc_break(false);
}

function fnc_ping() {
	global $response;
	
    $gc_maxlifetime = ini_get('session.gc_maxlifetime');
    if ($gc_maxlifetime == "") $gc_maxlifetime = 10 * 60;
    $gc_maxlifetime = (int)$gc_maxlifetime;

    $response['gc_maxlifetime'] = $gc_maxlifetime;
    send();
}

function fnc_getFormData() {
    global $vars, $response;
    
    $file = fopen($vars["modBase"] . "reportClasses.cfg", "r");
    $reportClasses = array();
    if ($file) while(!feof($file)) {
        $line = fgets($file);
        $parts = explode(", ", $line);
        $item = array('id' => intval($parts[0]), 'label' => $parts[1]);
        array_push($reportClasses, $item);
    }
    @fclose($file);
    $response["reportClasses"] = $reportClasses;
    
    $file = fopen($vars["modBase"] . "reportInvolved.cfg", "r");
    $reportInvolved = array();
    if ($file) while(!feof($file)) {
        $line = fgets($file);
        $parts = explode(", ", $line);
        $item = array('id' => intval($parts[0]), 'label' => $parts[1]);
        array_push($reportInvolved, $item);
    }
    @fclose($file);
    $response["reportInvolved"] = $reportInvolved;
    
    $oPaloConsola = new PaloSantoConsola();
    $response["exts"]=$oPaloConsola->listarExtensiones();
    $response["breaks"]=$oPaloConsola->listarBreaks();
    $oPaloConsola->desconectarTodo();
    
    send();
}
?>