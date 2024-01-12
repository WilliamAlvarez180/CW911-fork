<?php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exception\AMQPBasicCancelException;
use PhpAmqpLib\Exception\AMQPChannelClosedException;
use PhpAmqpLib\Exception\AMQPConnectionBlockedException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPNoDataException;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPTimeoutException;

class MQ
{
    const HOST = 'localhost';
    const PORT = 5672;
    const USERNAME = 'guest';
    const PASSWORD = 'guest';

    private $conn, $chan;

    function __construct()
    {
        $this->SID = session_id();
        $this->consumerTag = $this->SID."-".random_string(10);
        $this->SSEqueue = "SSE-usr".@$_SESSION["uid"]."-".$this->consumerTag;
        //Obtener ID de Cola del navegador.
        if (!empty(@$_GET["QID"])&&$_GET["QID"]!="null") {
            $this->consumerTag = $this->SID."-".@explode("-", $_GET["QID"])[3];
            $this->SSEqueue = $_GET["QID"];
        }
        $this->conn = new AMQPStreamConnection(self::HOST, self::PORT, self::USERNAME, self::PASSWORD);
        $this->chan = $this->conn->channel();
        $this->chan->exchange_declare("SSE", AMQPExchangeType::DIRECT, false, true, false);
        
        if (!$this->conn) {$this->err=true;}
        
        //Registrar función de cierre
        register_shutdown_function([$this, 'close']);
    }
    
    public function close()
    {
        if (@$this->chan->is_consuming()) {
            $this->chan->basic_cancel($this->consumerTag, false, true);
            $this->chan->close();
        }
        if ((!@$this->wchan===null)&&@$this->wchan->is_consuming()) {
            $this->wchan->basic_cancel($this->consumerTag, false, true);
            $this->wchan->close();
        }
        $this->conn->close();
    }

    public function sendEvent($messageBody, $method, $dest=0)
    {
        //sendEvent envía paquetes vía MQ, que son distribuidos a Instancias SSE según la ruta asignada por $dest
        //$dest puede ser Cola, Sesión, Usuario, Todos, o una ruta personalizada
        //El destino predeterminado es Cola actual.
        if ($dest==0) $dest=$this->SSEqueue;           //Enviar a Instancia SSE actual
        if ($dest==1) $dest=$this->SID;                 //Enviar a Sesión actual
        if ($dest==2) $dest="usr".@$_SESSION["uid"];    //Enviar a Usuario actual
        if ($dest==3) $dest="usr".@$_SESSION["uid"];    //Enviar a Todos
        
        $message = new AMQPMessage(json_encode(array('body' => $messageBody, 'func' => $method)), array('content_type' => 'text/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
        $this->chan->basic_publish($message, "SSE", $dest);
        return true;
    }
    
    public function msgEvent($msg, $type=0, $dest=0) 
    {
        $this->sendEvent(array('command'=>"message", 'msg'=>$msg, 'type'=>$type), "SPA", $dest);
    }
    
    public function close_sse_session() 
    {
        $this->sendEvent(array('command'=>"stopWorker", 'target'=>"ALL"), "SystemSSE"); //Señal de parar todos los trabajadores SSE
        $this->sendEvent(array('command'=>"shutdown"), "SystemSSE", 1);
    }
    
    public function initSSEWorker($workerName=null)
    {
        $this->wchan = $this->conn->channel();
        $this->workerStop = false;
        $this->workerName = $workerName;
        $this->workerQueue = "SSEWorker-" .$this->SID. "-". $workerName ."-".random_string(10);
        $this->wchan->queue_declare($this->workerQueue, false, false, false, true);
        
        //Enlazar con las claves de ruta:
        $this->wchan->queue_bind($this->workerQueue, "SSE", $this->SSEqueue);         //ID de Instancia SSE
        
        //Manejador de mensajes
        $callback = function ($message) {
            $msg = json_decode($message->body, true);
            //Mensajes con método SystemSSE son señales de control.
            if ($msg['func']=="SystemSSE") {
                if ($msg['body']["command"]=="stopWorker" &&(
                    $msg['body']["target"]=="ALL" ||                //Parar todos los trabajadores SSE
                    $msg['body']["target"]==$this->workerName ||    //Parar todos los trabajadores SSE con el nombre $this->workerName
                    $msg['body']["target"]==$this->workerQueue )    //Parar este trabajador SSE
                ) { 
                    $this->workerStop=true; } //Acción a tomar cuando se cierra la Instancia SSE
                return;
            }
        };
        $this->wchan->basic_consume($this->workerQueue, $this->consumerTag, true, true, false, false, $callback);
        
        $maximumPoll = 1.0;
        $timeout = $this->conn->getReadTimeout();
        $heartBeat = $this->conn->getHeartbeat();
        if ($heartBeat > 2) {
            $timeout = min($timeout, floor($heartBeat / 2));
        }
        $this->wtimeout = max(min($timeout, $maximumPoll), 1);
    }
    
    public function syncSSEWorker ()
    {
        try {
            $this->wchan->wait(null, false, $this->wtimeout);
        } catch (AMQPTimeoutException $exception) {
            // something might be wrong, try to send heartbeat which involves select+write
            $this->conn->checkHeartBeat();
        } catch (AMQPNoDataException $exception) {
        }
    }
    
    public function signalSSE($target, $signal)
    {
        if ($signal=="stop") $this->sendEvent(array('command'=>"stopWorker", 'target'=>$target), "SystemSSE"); //Señal de parar
    }
    
    public function SSE()
    {
        session_commit();
        ignore_user_abort(true);
        set_time_limit(0);
        Header('Content-Type: text/event-stream');
        $this->printflush("retry: 5000\n");
        
        $this->chan->queue_declare($this->SSEqueue, false, false, false, true);
        
        if(!@$_SESSION["logged"]) {
            $this->sendJSON(array('func'=>"SPA", 'data'=>array('command'=>"message", 'msg'=>"No se ha iniciado sesión. Cerrando SSE...", 'type'=>3)));
            return;
        }
        
        //Enlazar con las claves de ruta:
        $this->chan->queue_bind($this->SSEqueue, "SSE", $this->SID);              //ID de Sesión
        $this->chan->queue_bind($this->SSEqueue, "SSE", "usr".@$_SESSION["uid"]); //ID de Usuario
        $this->chan->queue_bind($this->SSEqueue, "SSE", $this->SSEqueue);         //ID de Instancia SSE
        $this->chan->queue_bind($this->SSEqueue, "SSE", "ALL");                   //Todos
        
        //Notificar al servidor el ID de Cola
        $this->sendJSON(array('func'=>"SPA", 'data'=>array('command'=>"SetQID", 'QID'=>$this->SSEqueue)));

        $callback = function ($message) {
            $msg = json_decode($message->body, true);
            //Mensajes con método SystemSSE son señales de control.
            if ($msg['func']=="SystemSSE") {
                if ($msg['body']["command"]=="shutdown") { $this->sendJSON(array('func'=>"SPA", 'data'=>array('command'=>"shutdown"))); exit(); } //Acción a tomar cuando se cierra sesión
                return;
            }
            $this->sendJSON(array('func'=>$msg['func'], 'data'=>$msg['body']));
        };
        $this->chan->basic_consume($this->SSEqueue, $this->consumerTag, true, true, false, false, $callback);

        $maximumPoll = 10.0;
        $timeout = $this->conn->getReadTimeout();
        $heartBeat = $this->conn->getHeartbeat();
        if ($heartBeat > 2) {
            $timeout = min($timeout, floor($heartBeat / 2));
        }
        $timeout = max(min($timeout, $maximumPoll), 1);
        while (connection_status() == CONNECTION_NORMAL && ($this->chan->is_consuming() || !empty($this->chan->method_queue))) {
            try {
                $this->chan->wait(null, false, $timeout);
            } catch (AMQPTimeoutException $exception) {
                // something might be wrong, try to send heartbeat which involves select+write
                $this->conn->checkHeartBeat();
                $this->sendJSON("ping");
                continue;
            } catch (AMQPNoDataException $exception) {
                continue;
            }
        }
        $this->sendEvent(array('command'=>"stopWorker", 'target'=>"ALL"), "SystemSSE");
    }
    
    private function sendJSON($msg)
    {
        $r = json_encode($msg);
        $this->printflush("data: $r\n\n");
    }

    private function printflush($s)
    {
        print $s;
        ob_flush();
        flush();
    }
}
?>