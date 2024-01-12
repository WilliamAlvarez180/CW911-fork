<div class="row gutters-sm">
            <div class="col-md-5 mb-3">
				<div class="card mb-3" style="overflow-y: scroll; height: calc(100vh - 120px);">
					<div class="card-body">
					  <h6 class="d-flex align-items-center mb-3">Registro de eventos</h6>
						<table class="table" id="event-list">
							<thead>
								<tr>
								  <th scope="col">Fecha</th>
								  <th scope="col">Evento</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
              <div class="card collapse">
				<h6 class="d-flex align-items-center m-3">Estadísticas de llamadas</h6>
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="mb-0">Total</h6>
                    <span class="text-secondary">320</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="mb-0">Efectiva</h6>
                    <span class="text-secondary">16</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="mb-0">Informativa</h6>
                    <span class="text-secondary">64</span>
                  </li>
                  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="mb-0">Falsa</h6>
                    <span class="text-secondary">240</span>
                  </li>
                </ul>
              </div>
			  <div class="card collapse">
                <div class="card-body">
                  <div class="d-flex flex-column align-items-center text-center">
                    <img src="/assets/img/user/9.png" alt="Admin" class="rounded-circle" width="150">
                    <div class="mt-3">
                      <h4>Ender Jimenez</h4>
                      <p class="text-secondary mb-1">Operador integral</p>
                      <p class="text-muted font-size-sm">San Fernando, Apure</p>
                      <button class="btn btn-primary mb-1">Estadísticas</button>
                      <button class="btn btn-outline-primary">Intervenir Audio</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-7">
              <div class="row gutters-sm">
                <div class="col-12 mb-3">
				<div class="card">
					<div class="d-flex justify-content-between">
						<h6 class="m-2">Estadísticas de cola</h6>
						<button class="btn btn-sm btn-primary m-1" type="button" data-bs-toggle="collapse" data-bs-target="#queue-stats-content">Expandir</button>
					</div>
					<div id="queue-stats-content" class="collapse show">
						<ul  class="list-group list-group-flush ">
						  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
							<h6 class="mb-0">Total</h6>
							<span class="text-secondary" id="queue-stats-total">0</span>
						  </li>
						  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
							<h6 class="mb-0">Conectadas</h6>
							<span class="text-secondary" id="queue-stats-success">0</span>
						  </li>
						  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
							<h6 class="mb-0">Abandonadas</h6>
							<span class="text-secondary" id="queue-stats-abandoned">0</span>
						  </li>
						  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
							<h6 class="mb-0">Finalizadas</h6>
							<span class="text-secondary" id="queue-stats-finished">0</span>
						  </li>
						</ul>
					</div>
				  </div>

				</div>
					<div class="col-12 mb-3">
						<div class="card">
							<div class="card-body">
							  <h6 class="d-flex align-items-center mb-3">Estado de Agentes<i class="material-icons text-info ms-2">Activos:</i><i class="material-icons text-info" id="agents-online">0</i></h6>
								<table class="table" id="agents-list">
								  <thead>
									<tr>
									  <th scope="col">Agente</th>
									  <th scope="col">Estado</th>
									  <th scope="col">Telf.</th>
									  <th scope="col">Desde</th>
									</tr>
								  </thead>
								  <tbody></tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="col-12 mb-3">
						<div class="card">
							<div class="card-body">
							  <h6 class="d-flex align-items-center mb-3">Cola de llamadas<i class="material-icons text-info ms-2">Por atender:</i><i class="material-icons text-info" id="queue-stats-onqueue">0</i></h6>
								<table class="table" id="onqueue-list">
								  <thead>
									<tr>
									  <th scope="col">Estado</th>
									  <th scope="col">Telf.</th>
									  <th scope="col">Desde</th>
									</tr>
								  </thead>
								  <tbody></tbody>
								</table>
							</div>
						</div>
					</div>
                </div>
              </div>
            </div>
</div>
	<script>
		var monitor = (function ()
		{
			function fmonitor() {
				_this = this;
				//Diccionario de Traducciones
				this.i18 = {
					offline: "Desconectado",
					online: "Conectado",
					oncall: "En llamada",
					paused: "Descanso",
					OnQueue: "En cola",
				};
				this.agentsOnline = {};
				//Función para reaccionar ante el evento SSE
				this.handleEvent = function (data) {
					
				}
				//Funciones para manejar la lista de llamadas en cola
				this.updQueue = function (callstatus, callnumber, desde, callid) {
					var tRow = $('<tr><td>'+this.i18[callstatus]+'</td><td>'+callnumber+'</td><td>'+desde+'</td></tr>').attr('id',"onqueue-"+callid);
					if ($('#onqueue-'+callid)[0]) {
						$('#onqueue-'+callid).replaceWith(tRow);
					} else {
						$('#onqueue-list > tbody:last-child').append(tRow);
					}
				}				
				this.delQueue = function (callid) {
					$('#onqueue-'+callid).remove();
				}
				//Funciones para manejar la lista de Agentes
				this.updAgent = function (agent, status, callnumber, desde) {
					agent = agent.replace('/', '');
					status = status.split(":");
					if (status[0]=="offline") {delete this.agentsOnline[agent];} else {this.agentsOnline[agent]=true;}
					if (!status[1]) {status=this.i18[status[0]];} else {status=this.i18[status[0]]+": "+status[1];}
					var tRow = $('<tr><td>'+agent+'</td><td>'+status+'</td><td>'+callnumber+'</td><td>'+desde+'</td></tr>').attr('id',"agent-"+agent);
					$('#agents-online').text(Object.keys(this.agentsOnline).length);
					if ($("#agent-"+agent)[0]) {
						$("#agent-"+agent).replaceWith(tRow);
					} else {
						$('#agents-list > tbody:last-child').append(tRow);
					}
				}				
				this.delAgent = function (agent) {
					$('#agent-'+agent).remove();
					delete this.agentsOnline[agent];
					$('#agents-online').text(Object.keys(this.agentsOnline).length);
				}
				//Funciones para manejar el registro de eventos
				this.log = function (date, event) {
					var tRow = $('<tr><td>'+date+'</td><td>'+event+'</td></tr>');
					$('#event-list > tbody:last-child').append(tRow);
				}
			}
			return new fmonitor;
		})();
		
		function manejarRespuestaStatus(respuesta) {
			// Intentar recargar la página en caso de error
			if (respuesta.error != null) {
				SPA.notify(respuesta.error, 3);
				return false;
			}
			
			if (respuesta.statuscount != null && respuesta.statuscount.update != null) {
				var upd = respuesta.statuscount.update;
				if (upd.total != null) $("#queue-stats-total").text(upd.total);
				if (upd.onqueue != null) $("#queue-stats-onqueue").text(upd.onqueue);
				if (upd.abandoned != null) $("#queue-stats-abandoned").text(upd.abandoned);
				if (upd.finished != null) $("#queue-stats-finished").text(upd.finished);
				if (upd.success != null) $("#queue-stats-success").text(upd.success);
			}
			
			// Lista de las llamadas activas sin agente asignado
			if (respuesta.activecalls != null && respuesta.activecalls.add != null)
			$.each(respuesta.activecalls.add, function (k, llamada) {
				monitor.updQueue(llamada.callstatus, llamada.callnumber, llamada.desde, llamada.callid);
				// El objeto llamada tiene lo siguiente
					// callid:		llamada.callid,
					// numero:		llamada.callnumber,
					// troncal:	llamada.trunk,
					// estado:		llamada.callstatus,
					// desde:		llamada.desde,
					// rtime:		new Date(),
					// reciente:	true
			});
				
			if (respuesta.activecalls != null && respuesta.activecalls.update != null)
			for (var i = 0; i < respuesta.activecalls.update.length; i++) {
				var llamada = respuesta.activecalls.update[i];
				var llamadaMarcando = this.llamadasMarcando.findBy('callid', llamada.callid);
				if (llamadaMarcando != null) llamadaMarcando.setProperties({
					'numero':	llamada.callnumber,
					'troncal':	llamada.trunk,
					'estado':	llamada.callstatus,
					'desde':	llamada.desde,
					'rtime':	new Date(),
					'reciente':	true
				});
			}
			if (respuesta.activecalls != null && respuesta.activecalls.remove != null)
			for (var i = 0; i < respuesta.activecalls.remove.length; i++) {
				var callid = respuesta.activecalls.remove[i].callid;
				monitor.delQueue(callid);
			}
			
			// Lista de los agentes que atienden llamada
			if (respuesta.agents != null && respuesta.agents.add != null)
			for (var i = 0; i < respuesta.agents.add.length; i++) {
				var agente = respuesta.agents.add[i];
				monitor.updAgent(agente.agent, agente.status, agente.callnumber, agente.desde);
					// troncal:	agente.trunk,
					// rtime:		new Date(),
					// reciente:	true
			}
			if (respuesta.agents != null && respuesta.agents.update != null)
			for (var i = 0; i < respuesta.agents.update.length; i++) {
				var agente = respuesta.agents.update[i];
				monitor.updAgent(agente.agent, agente.status, agente.callnumber, agente.desde);
			}
			if (respuesta.agents != null && respuesta.agents.remove != null)
			for (var i = 0; i < respuesta.agents.remove.length; i++) {
				var agentchannel = respuesta.agents.remove[i].agent;
				for (var j = 0; j < this.agentes.length; j++) {
					if (this.agentes[j].get('canal') == agentchannel) {
						this.agentes.removeAt(j);
					}
				}
			}
			
			// Registro de los eventos de la llamada
			if (respuesta.log != null)
			for (var i = 0; i < respuesta.log.length; i++) {
				monitor.log(respuesta.log[i]["timestamp"], respuesta.log[i]["mensaje"]);
			}
			
			return true;
		}
		
		SPA.whenReady(function (e) {
			SPA.AddSSEListener("call_center.monitor", function(m) { manejarRespuestaStatus(m); });
			SPA.get('?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=monitor.checkStatusSSE');
			SPA.onUnload(function() {
				SPA.get('?req=signalSSE&target=<?php echo $vars["modBaseName"]; ?>.monitor&signal=stop');
			});
		});
    </script>