<?php
require_once $vars["modLib"] ."paloSantoConsola.class.php";
require_once $vars["lib"] ."JSON.php";

function crearRespuestaVacia()
{
    return array(
        'statuscount'   =>  array('update' => array()),
        'activecalls'   =>  array('add' => array(), 'update' => array(), 'remove' => array()),
        'agents'        =>  array('add' => array(), 'update' => array(), 'remove' => array()),
        'log'           =>  array(),
    );
}

function esRespuestaVacia(&$respuesta)
{
	return count($respuesta['statuscount']['update']) == 0
        && count($respuesta['activecalls']['add']) == 0
        && count($respuesta['activecalls']['update']) == 0
        && count($respuesta['activecalls']['remove']) == 0
        && count($respuesta['agents']['add']) == 0
        && count($respuesta['agents']['update']) == 0
        && count($respuesta['agents']['remove']) == 0
        && count($respuesta['log']) == 0;
}

function esEventoParaCampania(&$estadoCliente, &$evento)
{
    return ($estadoCliente['campaigntype'] == 'incomingqueue')
        ? ( $estadoCliente['campaignid'] == $evento['queue'] &&
            is_null($evento['campaign_id']))
        : ( $estadoCliente['campaignid'] == $evento['campaign_id'] &&
            $estadoCliente['campaigntype'] == $evento['call_type']);
}

function formatoAgente($agent)
{
    $sEtiquetaStatus = $agent['status'];
    $sFechaHoy = date('Y-m-d');
    $sDesde = '-';
    switch ($agent['status']) {
    case 'paused':
        // Prioridad de pausa: hold, break, agendada
        if ($agent['onhold']) {
            $sEtiquetaStatus = 'En espera';
            // TODO: desde cuándo está en hold?
        } elseif (!is_null($agent['pauseinfo'])) {
            $sDesde = $agent['pauseinfo']['pausestart'];
            $sEtiquetaStatus .= ':'.$agent['pauseinfo']['pausename'];
        }
        // TODO: exponer pausa de agendamiento
        break;
    case 'oncall':
        $sDesde = $agent['callinfo']['linkstart'];
        break;
    }
    if (strpos($sDesde, $sFechaHoy) === 0)
        $sDesde = substr($sDesde, strlen($sFechaHoy) + 1);
    return array(
        'agent'         =>  $agent['agentchannel'],
        'status'        =>  $sEtiquetaStatus,
        'callnumber'    =>  is_null(@$agent['callinfo']['callnumber']) ? '-' : $agent['callinfo']['callnumber'],
        'trunk'         =>  is_null(@$agent['callinfo']['trunk']) ? '-' : $agent['callinfo']['trunk'],
        'desde'         =>  $sDesde,
    );
}

function agregarContadorLlamada($new_status, &$estadoCliente, &$respuesta)
{
    $k = strtolower($new_status);
    if ($k == 'dialing') $k = 'placing';
    if (isset($estadoCliente['statuscount'][$k])) {
        $estadoCliente['statuscount'][$k]++;
        $respuesta['statuscount']['update'][$k] = $estadoCliente['statuscount'][$k];
    }
}

function restarContadorLlamada($old_status, &$estadoCliente, &$respuesta)
{
    $k = strtolower($old_status);
    if ($k == 'dialing') $k = 'placing';
    if (isset($estadoCliente['statuscount'][$k]) && $estadoCliente['statuscount'][$k] > 0) {
        $estadoCliente['statuscount'][$k]--;
        $respuesta['statuscount']['update'][$k] = $estadoCliente['statuscount'][$k];
    }
}

function formatoLlamadaNoConectada($activecall)
{
    $sFechaHoy = date('Y-m-d');
    $sDesde = (!is_null($activecall['queuestart']))
        ? $activecall['queuestart'] : $activecall['dialstart'];
    if (strpos($sDesde, $sFechaHoy) === 0)
        $sDesde = substr($sDesde, strlen($sFechaHoy) + 1);
    $sEstado = ($activecall['callstatus'] == 'placing' && !is_null($activecall['trunk']))
        ? 'dialing' : $activecall['callstatus'];
    return array(
        'callid'        =>  $activecall['callid'],
        'callnumber'    =>  $activecall['callnumber'],
        'trunk'         =>  $activecall['trunk'],
        'callstatus'    =>  $sEstado,
        'desde'         =>  $sDesde,
    );
}

function formatoLogCampania($entradaLog)
{
    $listaMsg = array(
        'Placing'   =>  'LOG_FMT_PLACING',
        'Dialing'   =>  'LOG_FMT_DIALING',
        'Ringing'   =>  'LOG_FMT_RINGING',
        'OnQueue'   =>  'Llamada a  '.$entradaLog["phone"].'  conectada a cola  '.$entradaLog["queue"].' , esperando agente ...',
        'Success'   =>  'Llamada a '.$entradaLog["phone"].' asignada a agente '.$entradaLog["agentchannel"],
        'Hangup'    =>  "Llamada corta a " .$entradaLog["phone"]. " desconectada del agente " .$entradaLog["agentchannel"]. ", duración fue {duration}",
        'OnHold'    =>  'LOG_FMT_ONHOLD',
        'OffHold'   =>  'LOG_FMT_OFFHOLD',
        'Failure'   =>  'LOG_FMT_FAILURE',
        'Abandoned' =>  'Llamada a '.$entradaLog["phone"].' en cola '.$entradaLog["queue"].' se desconectó y no ha sido asignada',
        'ShortCall' =>  'LOG_FMT_SHORTCALL',
        'NoAnswer'  =>  'LOG_FMT_NOANSWER',
    );
    $sMensaje = $listaMsg[$entradaLog['new_status']];
    foreach ($entradaLog as $k => $v) {
        if ($k == 'duration') $v = sprintf('%02d:%02d:%02d',
                ($v - ($v % 3600)) / 3600,
                (($v - ($v % 60)) / 60) % 60,
                $v % 60);
        $sMensaje = str_replace('{'.$k.'}', $v, $sMensaje);
    }

    return array(
        'id'        =>  $entradaLog['id'],
        'timestamp' =>  $entradaLog['datetime_entry'],
        'mensaje'   =>  $sMensaje,
    );
}

function fnc_getCampaigns()
{
    global $response;
    $oPaloConsola = new PaloSantoConsola();
    $listaCampanias = $oPaloConsola->leerListaCampanias();
    if (!is_array($listaCampanias)) err($oPaloConsola->errMsg);
    $listaColas = $oPaloConsola->leerListaColasEntrantes();
    if (!is_array($listaColas)) err($oPaloConsola->errMsg);
    if (is_array($listaCampanias) && is_array($listaColas)) {
        foreach ($listaColas as $q) {
        	$listaCampanias[] = array(
                'id'        =>  $q['queue'],
                'type'      =>  'Entrante',
                'name'      =>  $q['queue'],
                'status'    =>  $q['status'],
            );
        }


        /* Para la visualización se requiere que primero se muestren las campañas
         * activas, con el ID mayor primero (probablemente la campaña más reciente)
         * seguido de las campañas inactivas, y luego las terminadas */
        if (!function_exists('manejarMonitoreo_getCampaigns_sort')) {
            function manejarMonitoreo_getCampaigns_sort($a, $b)
            {
            	if ($a['type'] != $b['type']) {
            		if ($a['type'] == 'incomingqueue') return 1;
                    if ($b['type'] == 'incomingqueue') return -1;
            	}
                if ($a['status'] != $b['status'])
                    return strcmp($a['status'], $b['status']);
                return $b['id'] - $a['id'];
            }
        }
        usort($listaCampanias, 'manejarMonitoreo_getCampaigns_sort');
        $respuesta = array();
        foreach ($listaCampanias as $c) {
            $respuesta[] = array(
                'id_campaign'   => $c['id'],
                'desc_campaign' => '('.$c['type'].') '.$c['name'],
                'type'          =>  $c['type'],
                'status'        =>  $c['status'],
            );
        }
    }
    
    $response["campaigns"] = $respuesta;
    send();
}

function fnc_checkStatusSSE()
{
    global $MQ;
    
    $MQ->initSSEWorker("call_center.monitor");
    $respuesta = crearRespuestaVacia();
    $estadoCliente = crearRespuestaVacia();
    $sTipoCampania = 'incomingqueue';
    $sIdCampania = "911";

    ignore_user_abort(true);
    set_time_limit(0);

    $oPaloConsola = new PaloSantoConsola();


    $infoCampania = $oPaloConsola->leerInfoCampania($sTipoCampania, $sIdCampania);
    if (!is_array($infoCampania)) {
        $respuesta['error'] = $oPaloConsola->errMsg;
        $MQ->sendEvent($respuesta, "call_center.monitor");
        $oPaloConsola->desconectarTodo();
        return;
    }
    $estadoCampania = $oPaloConsola->leerEstadoCampania($sTipoCampania, $sIdCampania);
    if (!is_array($estadoCampania)) {
        $respuesta['status'] = 'error';
        $respuesta['message'] = $oPaloConsola->errMsg;
    }
    
    $respuesta['campaigndata'] = array(
        'startdate'                 =>
            is_null($infoCampania['startdate'])
            ? 'N/A' : $infoCampania['startdate'],
        'enddate'                   =>
            is_null($infoCampania['enddate'])
            ? 'N/A' : $infoCampania['enddate'],
        'working_time_starttime'    =>
            is_null($infoCampania['working_time_starttime'])
            ? 'N/A' : $infoCampania['working_time_starttime'],
        'working_time_endtime'      =>
            is_null($infoCampania['working_time_endtime'])
            ? 'N/A' : $infoCampania['working_time_endtime'],
        'queue'                     =>  $infoCampania['queue'],
        'retries'                   =>
            is_null($infoCampania['retries'])
            ? 'N/A' : (int)$infoCampania['retries'],
    );
    
    // Traducción de estado de las llamadas no conectadas
    $estadoCampaniaLlamadas = array();
    foreach ($estadoCampania['activecalls'] as $activecall) {
        $estadoCampaniaLlamadas[] = formatoLlamadaNoConectada($activecall);
    }

    // Traducción de estado de los agentes
    $estadoCampaniaAgentes = array();
    foreach ($estadoCampania['agents'] as $agent) {
        $estadoCampaniaAgentes[] = formatoAgente($agent);
    }

    // Se arma la respuesta JSON y el estado final del cliente
    $respuesta = array_merge($respuesta, crearRespuestaVacia());
    $respuesta['statuscount']['update'] = $estadoCampania['statuscount'];
    $respuesta['stats']['update'] = $estadoCampania['stats'];
    $respuesta['activecalls']['add'] = $estadoCampaniaLlamadas;
    $respuesta['agents']['add'] = $estadoCampaniaAgentes;
    $estadoCliente = array(
        'campaignid'    =>  $sIdCampania,
        'campaigntype'  =>  $sTipoCampania,
        'queue'         =>  $infoCampania['queue'],
        'statuscount'   =>  $estadoCampania['statuscount'],
        'activecalls'   =>  $estadoCampania['activecalls'],
        'agents'        =>  $estadoCampania['agents'],
        'stats'         =>  $estadoCampania['stats'],
    );

    $MQ->sendEvent($respuesta, "call_center.monitor");

    // Acumular inmediatamente las filas que son distintas en estado
    $respuesta = crearRespuestaVacia();

    unset($infoCampania);

    $oPaloConsola->escucharProgresoLlamada(TRUE);
    $iTimeoutPoll = $oPaloConsola->recomendarIntervaloEsperaAjax();

    do {
        $oPaloConsola->desconectarEspera();

        // Se inicia espera larga con el navegador...
        $iTimestampInicio = time();

        while (!$MQ->workerStop && esRespuestaVacia($respuesta)
            && time() - $iTimestampInicio <  $iTimeoutPoll) {

            session_commit();
            $listaEventos = $oPaloConsola->esperarEventoSesionActiva();
            if (is_null($listaEventos)) {
                $respuesta['error'] = $oPaloConsola->errMsg;
                $MQ->sendEvent($respuesta, "call_center.monitor");
                $oPaloConsola->desconectarTodo();
                return;
            }
            @session_start();

            /* Si el navegador elige otra campaña mientras se espera la primera
             * campaña, entonces esta espera es inválida, y el navegador ya ha
             * iniciado otra sesión comet. */
            if (@isset($_SESSION[$module_name]) &&
                !($estadoCliente['campaigntype'] === $_SESSION[$module_name]['estadoCliente']['campaigntype'] &&
                 $estadoCliente['campaignid'] === $_SESSION[$module_name]['estadoCliente']['campaignid'])) {
                $respuesta['estadoClienteHash'] = 'invalidated';
                $MQ->sendEvent($respuesta, "call_center.monitor");
                $oPaloConsola->desconectarTodo();
                return;
            }

            $iTimestampActual = time();
            foreach ($listaEventos as $evento) {
                $sCanalAgente = isset($evento['agent_number']) ? $evento['agent_number'] : NULL;
                switch ($evento['event']) {
                case 'agentloggedin':
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                    	/* Se ha logoneado agente que atiende a esta campaña.
                         * ATENCIÓN: sólo se setean suficientes campos para la
                         * visualización. Otros campos quedan con sus valores
                         * antiguos, si tenían */
                        $estadoCliente['agents'][$sCanalAgente]['status'] = 'online';
                        $estadoCliente['agents'][$sCanalAgente]['pauseinfo'] = NULL;
                        $estadoCliente['agents'][$sCanalAgente]['callinfo'] = NULL;

                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                    }
                    break;
                case 'agentloggedout':
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                        /* Se ha deslogoneado agente que atiende a esta campaña.
                         * ATENCIÓN: sólo se setean suficientes campos para la
                         * visualización. Otros campos quedan con sus valores
                         * antiguos, si tenían */
                        $estadoCliente['agents'][$sCanalAgente]['status'] = 'offline';
                        $estadoCliente['agents'][$sCanalAgente]['pauseinfo'] = NULL;
                        $estadoCliente['agents'][$sCanalAgente]['callinfo'] = NULL;

                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                    }
                    break;
                case 'callprogress':
                    if (esEventoParaCampania($estadoCliente, $evento)) {
                    	// Llamada corresponde a cola monitoreada
                        $callid = $evento['call_id'];

                        // Para llamadas entrantes, cada llamada en cola aumenta el total
                        if ($evento['call_type'] == 'incoming' && $evento['new_status'] == 'OnQueue') {
                        	agregarContadorLlamada('total', $estadoCliente, $respuesta);
                        }

                        if (in_array($evento['new_status'], array('Failure', 'Abandoned', 'NoAnswer'))) {
                            if (isset($estadoCliente['activecalls'][$callid])) {
                                restarContadorLlamada($estadoCliente['activecalls'][$callid]['callstatus'], $estadoCliente, $respuesta);
                                agregarContadorLlamada($evento['new_status'], $estadoCliente, $respuesta);

                                // Quitar de las llamadas que esperan un agente
                                $respuesta['activecalls']['remove'][] = array('callid' => $callid);
                                unset($estadoCliente['activecalls'][$callid]);
                            }
                        } elseif (in_array($evento['new_status'], array('OnHold', 'OffHold'))) {
                        	// Se supone que una llamada en hold ya fue asignada a un agente
                        } else {
                            if (isset($estadoCliente['activecalls'][$callid])) {
                                restarContadorLlamada($estadoCliente['activecalls'][$callid]['callstatus'], $estadoCliente, $respuesta);

                                $estadoCliente['activecalls'][$callid]['callstatus'] = $evento['new_status'];
                                $estadoCliente['activecalls'][$callid]['trunk'] = $evento['trunk'];
                                if ($evento['new_status'] == 'OnQueue')
                                    $estadoCliente['activecalls'][$callid]['queuestart'] = $evento['datetime_entry'];
                                $respuesta['activecalls']['update'][] =
                                    formatoLlamadaNoConectada($estadoCliente['activecalls'][$callid]);
                            } else {
                            	// Valores sólo para satisfacer formato
                                $estadoCliente['activecalls'][$callid] = array(
                                    'callid'        =>  $callid,
                                    'callnumber'    =>  $evento['phone'],
                                    'callstatus'    =>  $evento['new_status'],
                                    'dialstart'     =>  $evento['datetime_entry'],
                                    'dialend'       =>  NULL,
                                    'queuestart'    =>  $evento['datetime_entry'],
                                    'trunk'         =>  $evento['trunk'],
                                );
                                $respuesta['activecalls']['add'][] =
                                    formatoLlamadaNoConectada($estadoCliente['activecalls'][$callid]);
                            }

                            agregarContadorLlamada($evento['new_status'], $estadoCliente, $respuesta);
                        }

                        $respuesta['log'][] = formatoLogCampania(array(
                            'id'                =>  $evento['id'],
                            'new_status'        =>  $evento['new_status'],
                            'datetime_entry'    =>  $evento['datetime_entry'],
                            'campaign_type'     =>  $evento['call_type'],
                            'campaign_id'       =>  $evento['campaign_id'],
                            'call_id'           =>  $evento['call_id'],
                            'retry'             =>  $evento['retry'],
                            'uniqueid'          =>  $evento['uniqueid'],
                            'trunk'             =>  $evento['trunk'],
                            'phone'             =>  $evento['phone'],
                            'queue'             =>  $evento['queue'],
                            'agentchannel'      =>  $sCanalAgente,
                            'duration'          =>  NULL,
                        ));
                    }
                    break;
                case 'pausestart':
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                        switch ($evento['pause_class']) {
                        case 'break':
                            $estadoCliente['agents'][$sCanalAgente]['pauseinfo'] = array(
                                'pauseid'   =>  $evento['pause_type'],
                                'pausename' =>  $evento['pause_name'],
                                'pausestart'=>  $evento['pause_start'],
                            );
                            break;
                        case 'hold':
                            $estadoCliente['agents'][$sCanalAgente]['onhold'] = TRUE;
                            // TODO: desde cuándo empieza la pausa hold?
                            break;
                        // TODO: pausa de llamada agendada
                        }
                        if ($estadoCliente['agents'][$sCanalAgente]['status'] != 'oncall')
                            $estadoCliente['agents'][$sCanalAgente]['status'] = 'paused';
                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                    }
                    break;
                case 'pauseend':
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                        switch ($evento['pause_class']) {
                        case 'break':
                            $estadoCliente['agents'][$sCanalAgente]['pauseinfo'] = NULL;
                            break;
                        case 'hold':
                            $estadoCliente['agents'][$sCanalAgente]['onhold'] = FALSE;
                            // TODO: anular inicio de pausa hold
                            break;
                        }
                        if ($estadoCliente['agents'][$sCanalAgente]['status'] != 'oncall') {
                            $estadoCliente['agents'][$sCanalAgente]['status'] = (
                                !is_null($estadoCliente['agents'][$sCanalAgente]['pauseinfo']) ||
                                $estadoCliente['agents'][$sCanalAgente]['onhold'])
                            ? 'paused' : 'online';
                        }

                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                    }
                    break;
                case 'agentlinked':
                    // Si la llamada estaba en lista activa, quitarla
                    $callid = $evento['call_id'];
                    if (isset($estadoCliente['activecalls'][$callid])) {
                        restarContadorLlamada($estadoCliente['activecalls'][$callid]['callstatus'], $estadoCliente, $respuesta);
                        $respuesta['activecalls']['remove'][] = array('callid' => $estadoCliente['activecalls'][$callid]['callid']);
                        unset($estadoCliente['activecalls'][$callid]);
                    }

                    // Si el agente es uno de los de la campaña, modificar
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                        $estadoCliente['agents'][$sCanalAgente]['status'] = 'oncall';
                        $estadoCliente['agents'][$sCanalAgente]['callinfo'] = array(
                            'callnumber'    =>  $evento['phone'],
                            'linkstart'     =>  $evento['datetime_linkstart'],
                            'trunk'         =>  $evento['trunk'],
                            'callid'        =>  $evento['call_id'],

                            // Campos que (todavía) no se usan
                            'calltype'      =>  $evento['call_type'],
                            'campaign_id'   =>  $evento['campaign_id'],
                            'queuenumber'   =>  $evento['queue'],
                            'remote_channel'=>  $evento['remote_channel'],
                            'status'        =>  $evento['status'],
                            'queuestart'    =>  (is_null($evento['datetime_join']) || $evento['datetime_join'] == '') ? NULL : $evento['datetime_join'],
                            'dialstart'     =>  $evento['datetime_originate'],
                            'dialend'       =>  $evento['datetime_originateresponse'],
                        );

                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                    }

                    if (esEventoParaCampania($estadoCliente, $evento)) {
                        $respuesta['log'][] = formatoLogCampania(array(
                            'id'                =>  $evento['campaignlog_id'],
                            'new_status'        =>  'Success',
                            'datetime_entry'    =>  $evento['datetime_linkstart'],
                            'campaign_type'     =>  $evento['call_type'],
                            'campaign_id'       =>  $evento['campaign_id'],
                            'call_id'           =>  $evento['call_id'],
                            'retry'             =>  $evento['retries'],
                            'uniqueid'          =>  $evento['uniqueid'],
                            'trunk'             =>  $evento['trunk'],
                            'phone'             =>  $evento['phone'],
                            'queue'             =>  $evento['queue'],
                            'agentchannel'      =>  $sCanalAgente,
                            'duration'          =>  NULL,
                        ));

                        agregarContadorLlamada('Success', $estadoCliente, $respuesta);
                    }
                    break;
                case 'agentunlinked':
                    // Si el agente es uno de los de la campaña, modificar
                    if (isset($estadoCliente['agents'][$sCanalAgente])) {
                        /* Es posible que se reciba un evento agentunlinked luego
                         * del evento agentloggedout si el agente se desconecta con
                         * una llamada activa. */
                        if ($estadoCliente['agents'][$sCanalAgente]['status'] != 'offline') {
                            $estadoCliente['agents'][$sCanalAgente]['status'] = (
                                !is_null($estadoCliente['agents'][$sCanalAgente]['pauseinfo']) ||
                                $estadoCliente['agents'][$sCanalAgente]['onhold'])
                            ? 'paused' : 'online';
                        }
                        $estadoCliente['agents'][$sCanalAgente]['callinfo'] = NULL;

                        $respuesta['agents']['update'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                    }

                    if (esEventoParaCampania($estadoCliente, $evento)) {
                        $respuesta['log'][] = formatoLogCampania(array(
                            'id'                =>  $evento['campaignlog_id'],
                            'new_status'        =>  $evento['shortcall'] ? 'ShortCall' : 'Hangup',
                            'datetime_entry'    =>  $evento['datetime_linkend'],
                            'campaign_type'     =>  $evento['call_type'],
                            'campaign_id'       =>  $evento['campaign_id'],
                            'call_id'           =>  $evento['call_id'],
                            'retry'             =>  NULL,
                            'uniqueid'          =>  NULL,
                            'trunk'             =>  NULL,
                            'phone'             =>  $evento['phone'],
                            'queue'             =>  NULL,
                            'agentchannel'      =>  $sCanalAgente,
                            'duration'          =>  $evento['duration'],
                        ));

                        if ($evento['call_type'] == 'incoming') {
                        	restarContadorLlamada('Success', $estadoCliente, $respuesta);
                            agregarContadorLlamada('Finished', $estadoCliente, $respuesta);
                            agregarContadorLlamada('Total', $estadoCliente, $respuesta);
                            $respuesta['duration'] = $evento['duration'];
                        } else {
                        	if ($evento['shortcall']) {
                        		restarContadorLlamada('Success', $estadoCliente, $respuesta);
                                agregarContadorLlamada('ShortCall', $estadoCliente, $respuesta);
                        	} else {
                        		// Se actualiza Finished para actualizar estadísticas
                                agregarContadorLlamada('Finished', $estadoCliente, $respuesta);
                                $respuesta['duration'] = $evento['duration'];
                        	}
                        }
                        if (isset($respuesta['duration'])) {
                        	$estadoCliente['stats']['total_sec'] += $respuesta['duration'];
                            if ($estadoCliente['stats']['max_duration'] < $respuesta['duration'])
                                $estadoCliente['stats']['max_duration'] = $respuesta['duration'];
                        }
                    }
                    break;
                case 'queuemembership':
                    if (in_array($estadoCliente['queue'], $evento['queues']) &&
                        !isset($estadoCliente['agents'][$sCanalAgente])) {
                        // Este nuevo agente acaba de ingresar a la cola de campañas
                        $estadoCliente['agents'][$sCanalAgente] = array_merge(
                            array('agentchannel' => $sCanalAgente), $evento);
                        unset($estadoCliente['agents'][$sCanalAgente]['queues']);

                        $respuesta['agents']['add'][] = formatoAgente($estadoCliente['agents'][$sCanalAgente]);
                    } elseif (!in_array($estadoCliente['queue'], $evento['queues']) &&
                        isset($estadoCliente['agents'][$sCanalAgente])) {

                        // El agente mencionado deja de pertenecer a la cola de campañas
                        $respuesta['agents']['remove'][] = array('agent' => $estadoCliente['agents'][$sCanalAgente]['agentchannel']);
                        unset($estadoCliente['agents'][$sCanalAgente]);
                    }
                    break;
                }
            }
        }
        
        $MQ->syncSSEWorker();
        if (!$MQ->workerStop) $MQ->sendEvent($respuesta, "call_center.monitor");

        $respuesta = crearRespuestaVacia();

    } while (!$MQ->workerStop);
    $oPaloConsola->desconectarTodo();
}

?>