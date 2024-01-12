<?php 
	global $userdata; 
	if (@$userdata["callcenter_enabled"]["value"] and @$userdata["callcenter_id"]["value"]){ 
?>
		<div class="card border collapse show" id="agentConsoleLogin">
			<div class="card-header"><strong class="float-left">Iniciar sesión de agente</strong></div>
			<div class="card-body">
				<form action="?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.loginAgent" method="post" id="agentLogin">							
					<div class="row">
						<div class="form-group col-md-6">
							<label class="form-label col-form-label" for="exts" >Extensión:</label>
							<div class="input-group">
								<span class="input-group-text"><i class="fa fa-phone"></i></span>
								<select placeholder="Seleccione la extensión a usar..." required name="ext" id="exts" class="form-select"></select>
								<button type="button" id="btn_extsettings " class="btn btn-outline-secondary"><i class="fa fa-gear"></i></button>
							</div>
						</div>
						<div class="form-group col-md-6">
							<label class="form-label col-form-label" for="callnumber">ID de Agente:</label>
							<div class="input-group">
								<span class="input-group-text"><i class="fa fa-user"></i></span>
								<span class="input-group-text"><?php echo @explode("/", $userdata["callcenter_id"]["value"])[1] ?></span>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="card-footer">					
				<button type="submit" form="agentLogin" class="float-end"><i class="fa fa-sign-in"></i>&nbsp; Conectar</button>
			</div>
		</div>
		
		<div class="card border border-secondary collapse" id="agentConsolePanel" tabindex="-1">
			<div class="card-header bg-secondary">
				<strong class="text-light float-start">No hay llamada activa</strong>
				<span class="badge rounded-pill bg-light text-dark float-end collapse" id="agentConsole-cronometro">00:00:00</span>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="btn-toolbar" role="toolbar">
						<div class="btn-group" role="group">
							<button type="button" id="btn_hangup" class="btn btn-outline-danger btn-sm" disabled><i class="fa fa-phone"></i>&nbsp;Colgar</button>
							<button type="button" id="btn_transfer" class="btn btn-outline-secondary btn-sm" disabled><i class="fa fa-route"></i>&nbsp; Transferir</button>
							<button type="button" id="btn_break" class="btn btn-outline-warning btn-sm"><i class="fa fa-pause"></i>&nbsp; <span>Receso</span></button>
						</div>
						<button type="button" id="btn_logout" class="btn btn-outline-danger btn-sm"><i class="fa fa-sign-out"></i>&nbsp;Cerrar sesión</button>
						<div class="btn-group" role="group">
							<button type="button" id="btn_mute" class="btn btn-outline-danger btn-sm ajaxAction noPreloader" href="?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.remotePhone&SIPEvent=key&SIPAction=MUTE"><i class="fa fa-microphone-slash"></i>&nbsp;Silenciar</button>
							<button type="button" id="btn_spkr" class="btn btn-outline-secondary btn-sm ajaxAction noPreloader" href="?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.remotePhone&SIPEvent=key&SIPAction=SPEAKER"><i class="fa fa-volume-high"></i>&nbsp;Altavoz</button>
						</div>
					</div>
				</div>
				<div class="row"><div class="d-flex flex-wrap mt-2 collapse" id="agentconsole_callerbadges"></div></div>
			</div>
			
			<div class="card-footer">					
				<a data-bs-toggle="tooltip"  title="Atajos de teclado para esta sección." href="#" id="agentConsoleKbdStatus" class="p-1 border-secondary rounded text-secondary"><i class="fa fa-keyboard"></i></a>
			</div>
		</div>
		<div div class="modal" tabindex="-1" id="callcenter-select-break">
						<div class="modal-dialog modal-dialog-centered">
							<div class="modal-content">
								<div class="modal-header"><h1 class="modal-title fs-5">Seleccionar clase de receso</h1></div>
								<div class="modal-body">
									<select id="break_select" class="form-select"></select>
								</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-primary">Aplicar</button>
								<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
							</div>
							</div>
						</div>
					</div>
				
		<div class="card border border-secondary" id="agentConsoleTools" tabindex="-1">
							<div class="card-header">
								<div class="nav nav-pills" id="toolTabs" role="tablist">
									<button class="nav-link" id="tool-tab-btn-01" data-bs-target="#tool-tab-01" data-bs-toggle="pill" role="tab" aria-selected="true">Registro de llamadas</button>
									<button class="nav-link" id="tool-tab-btn-02" data-bs-target="#tool-tab-02" data-bs-toggle="pill" role="tab" aria-selected="true">Contactos 911</button>
									<button class="nav-link" id="tool-tab-btn-03" data-bs-target="#tool-tab-03" data-bs-toggle="pill" role="tab" aria-selected="false">Consulta CI</button>
									<button class="nav-link active" id="tool-tab-btn-04" data-bs-target="#tool-tab-04" data-bs-toggle="pill" role="tab" aria-selected="false">Panel de reportes</button>
								</div>
							</div>
                            <div class="card-body"><div class="tab-content w-100" id="toolContent">
								<div class="tab-pane fade" id="tool-tab-01" role="tabpanel" aria-labelledby="tool-tab-btn-01">
									<table class="table table-sm table-hover ov-h" id="callRecord" data-order='[[ 0, "asc" ]]'>
										<thead>
											<tr>
												<th class="serial">ID</th>
												<th>Fecha y Hora</th>
												<th>Número telefónico</th>
												<th>Duración</th>
												<th>Tiempo de espera</th>
												<th>Clasificación</th>
											</tr>
										</thead>
									</table>
								</div>
								<div class="tab-pane fade" id="tool-tab-02" role="tabpanel" aria-labelledby="tool-tab-btn-02">

								</div>
								<div class="tab-pane fade" id="tool-tab-03" role="tabpanel" aria-labelledby="tool-tab-btn-03">

								</div>
								<div class="tab-pane fade active show" id="tool-tab-04" role="tabpanel" aria-labelledby="tool-tab-btn-04">
									<div class="nav nav-pills mb-3" id="reportTabs_btn" role="tablist">
										<button class="nav-link active" id="reports-search-tab-btn" data-bs-target="#reports-search-tab" data-bs-toggle="pill" role="tab" aria-selected="false">Buscar</button>
									</div>
									<hr>
									<div class="tab-content" id="reportTabs_content">
										<div class="tab-pane fade active show" id="reports-search-tab" role="tabpanel">
											<button type="button" id="btn_create_report" class="btn btn-outline-primary btn-sm"><i class="fa fa-plus"></i>&nbsp; Nuevo reporte</button>
											<table class="table table-sm table-hover ov-h" id="reportsTable" data-order='[[ 0, "desc" ]]'>
												<thead>
													<tr>
														<th class="serial">id</th>
														<th>desc</th>
														<th>status</th>
														<th>content</th>
													</tr>
												</thead>
											</table>
										</div>
									</div>
								</div>
							</div></div>
                        </div>
                </div>
	<script>
		var reportClasses = [];
		var reportInvolved = [];
	
		/* El siguiente objeto es el estado de la interfaz del CallCenter. Al comparar
		 * este objeto con los cambios de estado producto de eventos del ECCP, se
		 * consigue detectar los cambios requeridos a la interfaz sin tener que recurrir
		 * a llamadas repetidas al servidor.*/
		var agentStatus =
		{
			call_number:	0,	// Si != null, número de teléfono que se está atendiendo
			onhold:		false,	// VERDADERO si el sistema está en hold
			break_id:	null,	// Si != null, el ID del break en que está el agente
			callid:		null,	// Si != null, ID de llamada que se está atendiendo
		};

		/* Al cargar la página, o al recibir un evento AJAX, si timer_seconds tiene un
		 * valor distinto de null, se inicia fechaInicio a la fecha y hora actual menos
		 * el valor en segundos indicado por timer_seconds. En cualquier momento futuro,
		 * el valor correcto del contador es la fecha actual menos la almacenada en
		 * fechaInicio. */
		var fechaInicio = null;
		var timer = null;

		// Objeto EventSource, si está soportado por el navegador
		var evtSource = null;
		
		var AgentLoggedOut=false;	

		
		//Inicializar el constructor de tabs para reportes
		var reports = (function() {
			function reports() {
				var _reports = this;
				//Variable de prefijo
				this.idPrefix="01-";
				//URL de destino para los forms
				this.formURL = "?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=reports";
				//Eventos
				$("#tabsList").on( "show.bs.tab", function(event) {
					_reports.chgstatus($(event.target).data("changed"));
				});
				
				this.chgstatus = function(chg, mod=2) {
					if (typeof mod === "boolean") { //Si es booleano (2 no es Booleano!)
						$('#agentConsoleReports #tabsList .active').data("changed", mod);
						chg = mod;
					}
					if (chg) {
						$("#reportSaveStatus")
							.removeClass("text-success border border-success")
							.addClass("text-secondary border-secondary");
					} else {
						$("#reportSaveStatus")
							.removeClass("text-secondary border-secondary")
							.addClass("text-success border border-success");
					}
				}
				
				this.save = function() {
					var form = $('#agentConsoleReports .tab-content .active form');
					var dataObject = {};
					$.each(form.serializeArray(), function(_, item) {
						if (dataObject.hasOwnProperty(item.name)) {
							dataObject[item.name] = [].concat(dataObject[item.name], item.value);
						} else {
							dataObject[item.name] = item.value;
						}
					});
					$.post(this.formURL +".save", {rep_id: form.data("rep_id"), report: JSON.stringify(dataObject)}, 
					function(response) {
						if (response['status'] == 'OK') {
							_reports.chgstatus(false, false);
						} else {
							notify(response['message'], "error");
						}
					}, 'json')
					.fail(function() {
						notify('Error al comprobar estado de inicio de sesión de agente!', "error");
					});
				}
				
				this.load = function() {
					var selector = '#agentConsoleReports .tab-content .active form';
					$.get(this.formURL +".load&rep_id="+ $(selector).data("rep_id"), 
					function(response) {
						if (response['status'] == 'OK') {
							$(selector).trigger("reset");
							$.each(response['data'], function(item, val) {
								if ($(selector+" #"+item).hasClass("selectized")) {
									$(selector+" #"+item).selectize()[0].selectize.setValue(val);
								} else {
									$(selector+" #"+item).val(val);
								}
							});
							_reports.chgstatus(false, false);
						} else {
							notify(response['message'], "error");
						}
					}, 'json')
					.fail(function() {
						if (!s) notify('Error al comprobar estado de inicio de sesión de agente!', "error");
					});
				}
				
				this.close = function() {
					$('#agentConsoleReports .tab-content .active').remove();
					$('#agentConsoleReports .nav .nav-link.active').remove();
				}
				
				this.a = function(itemData) {
					//Devolver un elemento HTML basado en los argumentos
					const item = document.createElement(itemData.itemType);
					if (itemData.classes) item.classList.add(...itemData.classes);
					if (itemData.text) item.innerText = itemData.text;
					if (itemData.id) item.setAttribute('id', itemData.id);
					if (itemData.for) item.setAttribute('for', itemData.for);
					if (itemData.type) item.setAttribute('type', itemData.type);
					if (itemData.name) item.setAttribute('name', itemData.name);
					if (itemData.readonly) item.setAttribute('readonly', "");
					if (itemData.multiple) item.setAttribute('multiple', "");
					if (itemData.value) item.setAttribute('value', itemData.value);
					
					return item;
				}
				
				this.f = function(fieldData) {
					// Devolver una fila con 2 columnas: la etiqueta y el objeto
					return $(
						this.a({ itemType: "div", classes: ["form-group", "row"] }))
							.append(this.a({ itemType: "label", for: fieldData.id, text: fieldData.label, classes: ["form-label", "col-sm-2", "col-form-label"] }))
							.append($(this.a({ itemType: "div", classes: ["col-sm-10"] }))
								.append(this.a(fieldData))
							);
				}
				
				this.drawReport = function (d) {
					data = JSON.parse(d["content"]);
					
					if (d["status"] == 0) {
						cardClass = 'border-secondary';
						statusBadge=$("<span>").addClass("badge rounded-pill me-1 bg-secondary").text("Abierto")
					} else if (d["status"] == 1) {
						cardClass = 'border-primary';
						statusBadge=$("<span>").addClass("badge rounded-pill me-1 bg-primary").text("Aprobado")
					} else if (d["status"] == 2) {
						cardClass = 'border-danger';
						statusBadge=$("<span>").addClass("badge rounded-pill me-1 bg-danger").text("Denegado")
					} else if (d["status"] == 3) {
						cardClass = 'border-primary';
						statusBadge=$("<span>").addClass("badge rounded-pill me-1 bg-primary").text("Atendido")
					}
					
					classBadges="";
					if (typeof data["classifications"] != "string") {
						$.each(data["classifications"], (function (c) {
							classBadges=classBadges+$("<span>")
								.addClass("badge me-1 mb-1 rounded-pill bg-secondary")
								.html('<i class="fa fa-circle-info"></i><div class="vr mx-1"></div>'+reportClasses[c].label)
								[0].outerHTML
						}));
					} else {
						classBadges=$("<span>").addClass("badge rounded-pill me-1 mb-1 bg-secondary")
							.html('<i class="fa fa-circle-info"></i><div class="vr mx-1"></div>'+reportClasses[data.classifications].label)
							[0].outerHTML
					}
					
					content = $("<div>")
						.append($("<div>").addClass("d-flex justify-content-between")
							.append($("<span>").text(d["desc"]))
							.append(statusBadge)
						)
						.append($("<div>")
							.append($("<span>").addClass("badge me-1 rounded-pill bg-secondary")
								.html('<i class="fa fa-fingerprint"></i><div class="vr mx-1"></div>'+d["id"]))
							.append(classBadges)
						);
					
					return $("<div>").addClass("card mb-1 border border-2 "+cardClass).append($("<div>").addClass("card-body p-1").append(content));
				}
				
				this.open = function(rep_id=null) {
					//Abrir o Crear (según sea necesario) un tab de reporte
					
					//Comprobar si ya existe el tab ID. de ser el caso, mostrar y no hacer nada
					if ($("#report-tab-btn-" + rep_id).length) {
						$("#report-tab-btn-" + rep_id).tab("show");
						return;
					}

					//Botón del tab
					const tabButton = $('<li>').addClass("nav-item")
						.append($('<button>').addClass("nav-link")
							.attr('id', "report-tab-btn-" + rep_id)
							.attr("data-bs-target", "#report-tab-" + rep_id)
							.attr("data-bs-toggle", "tab")
							.attr("type", "button")
							.attr("role", "tab")
							.text('<i class="fas fa-cog"></i>'+rep_id)
						);
					
					//Contenido del tab
					const tabContent = document.createElement('div');
					tabContent.classList.add('tab-pane', 'fade');
					tabContent.setAttribute('id', "report-tab-" + rep_id);
					
					//Form
					const tabForm = document.createElement('form')
					tabForm.setAttribute("method" , "post");
					tabForm.setAttribute("id" , "report-form-" + rep_id);
					$(tabForm).data("rep_id", rep_id);
					tabContent.appendChild(tabForm);
					
					//Insertar tab en la página
					$("#reportTabs_btn").append(tabButton);
					$("#reportTabs_content").append(tabContent);
					
					//* Panel de reporte 
					//Insertar items
					const s = document.querySelector("#report-form-" + rep_id)
					$(s)
						.append(this.f({ itemType: "input", readonly: true, value: "23/6/2023 - 11:31pm", label: "Fecha", type: "text", id: 'datetime', classes: ["form-control-plaintext"] }))
						.append(this.f({ itemType: "input", readonly: true, value: "04163463017", label: "Teléfono", type: "text", id: 'callnumber', classes: ["form-control-plaintext"] }))
						.append(this.f({ itemType: "input", readonly: true, value: "98", label: "ID Agente", type: "text", id: 'agent', classes: ["form-control-plaintext"]	}))
						.append($("<hr>"))
						.append(this.f({ itemType: "input", label: "Municipio", type: "text", id: 'municipio', classes: ["form-control"] }))
						.append(this.f({ itemType: "input", label: "Parroquia", type: "text", id: 'parroquia', classes: ["form-control"] }))
						.append(this.f({ itemType: "input", label: "Direccion", type: "text", id: 'direccion', classes: ["form-control"] }))
						.append(this.f({ itemType: "input", label: "Punto de referencia", type: "text", id: 'puntoreferencia', classes: ["form-control"] }))
						.append($("<hr>"))
						.append(this.f({ itemType: "select", label: 'Tipos De Solicitud', multiple: true, id: 'classifications', name: "classifications", classes: ["form-control"] }))
						.append(this.f({ itemType: "select", label: 'Involucrados', multiple: true, id: 'involved', name: "involved", classes: ["form-control"] }))
						.append(this.f({ itemType: "textarea", type: "text", label: 'Descripción', id: 'description', name: "description", classes: ["form-control"] }))
						.append(this.f({ itemType: "textarea", type: "text", label: 'Seguimiento', id: 'followup', name: "followup", classes: ["form-control"] }))
						.append($("<hr>"))
						.append(this.f({ itemType: "input", label: "C.PAZ", type: "text", id: 'C.PAZ', classes: ["form-control"] }))
						.append(this.f({ itemType: "input", label: "Responsable Que Recibe", type: "text", id: 'responsable que recibe', classes: ["form-control"] }))
						.append(this.f({ itemType: "input", label: "Unidad Movil", type: "text", id: 'unidad movil ', classes: ["form-control"] }))
						
	;
					
					//Inicializar objetos
					$("#report-form-" + rep_id + " #classifications").selectize({
						plugins: ["remove_button"],
						valueField: 'id',
						labelField: 'label',
						searchField: 'label',
						options: reportClasses
					})
					
					$("#report-form-" + rep_id + " #involved").selectize({
						plugins: ["remove_button"],
						valueField: 'id',
						labelField: 'label',
						searchField: 'label',
						options: reportInvolved
					})
					
					//Establecer manejadores de eventos
					$("#report-form-" + rep_id + " :input").change(function() {
						$("#report-tab-btn-" + rep_id).data("changed", true);
						_reports.chgstatus(true);
					});
					
					//Mostrar tab recién creado
					$("#report-tab-btn-" + rep_id).tab("show");
					this.load();
				}
			}
			return new reports;
		})();

		$(window).on("unload", function() {
			if (evtSource != null) {
				evtSource.close();
				evtSource = null;
			}
		});

		// Actualizar el estado del agente en la interfaz
		function update_agent_state(nuevoEstado)
		{
			agentStatus.onhold = nuevoEstado.onhold;
			agentStatus.break_id = nuevoEstado.break_id;
			agentStatus.callid = nuevoEstado.callid;
			agentStatus.waitingcall = nuevoEstado.waitingcall;
			$('#agentstatus_callnumber').text(nuevoEstado.callnumber);
			$('#agentstatus_callid').text(nuevoEstado.callid);
			
			$('#agentconsole_callerbadges').html(nuevoEstado.callerbadges);
			SPA.updTooltips();

			iniciar_cronometro((nuevoEstado.timer_seconds !== '') ? nuevoEstado.timer_seconds : null);
		}

		// Inicializar el cronómetro con el valor de segundos indicado
		function iniciar_cronometro(timer_seconds)
		{
			// Anular el estado anterior
			if (timer != null) {
				clearTimeout(timer);
				timer = null;
				$('#agentConsole-cronometro').collapse('hide');
			}
			fechaInicio = null;
			$('#agentConsole-cronometro').text('00:00:00');

			// Iniciar el estado nuevo, si es válido
			if (timer_seconds != null) {
				fechaInicio = new Date();
				fechaInicio.setTime(fechaInicio.getTime() - timer_seconds * 1000);
				timer = setTimeout(actualizar_cronometro, 1);
				$('#agentConsole-cronometro').collapse('show');
			}
		}

		// Cada 500 ms se llama a esta función para actualizar el cronómetro
		function actualizar_cronometro()
		{
			var fechaDiff = new Date();
			var msec = fechaDiff.getTime() - fechaInicio.getTime();
			var tiempo = [0, 0, 0];
			tiempo[0] = (msec - (msec % 1000)) / 1000;
			tiempo[1] = (tiempo[0] - (tiempo[0] % 60)) / 60;
			tiempo[0] %= 60;
			tiempo[2] = (tiempo[1] - (tiempo[1] % 60)) / 60;
			tiempo[1] %= 60;
			var i = 0;
			for (i = 0; i < 3; i++) { if (tiempo[i] <= 9) tiempo[i] = "0" + tiempo[i]; }
			$('#agentConsole-cronometro').text(tiempo[2] + ':' + tiempo[1] + ':' + tiempo[0]);
			timer = setTimeout(actualizar_cronometro, 500);
		}
			$('#callcenter-select-break button.btn-primary').click(function() {
				do_break();
				$('#callcenter-select-break').modal("hide");
			});
			$('#callcenter-select-break').on('shown.bs.modal', function () {
				$('#callcenter-select-break #break_select').focus();
			});
			$('#callcenter-select-break').on('hidden.bs.modal', function () {
				$('#agentConsolePanel').focus();
			});
		
		// El siguiente código se ejecuta al presionar el botón de colgado
		function do_hangup()
		{
			$('#btn_hangup').button('disable');
			SPA.get('?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.hangup', {success: function (respuesta) {
				if (respuesta['action'] == 'error') {
					notify(respuesta['message'], "error");
					if (agentStatus.campaign_id != null || agentStatus.waitingcall)
						$('#btn_hangup').button('enable');
				}
			}});
		}

		// Función que verifica si se ha completado el proceso de login
		function do_checklogin(s)
		{	
			preloader.show({text:"Iniciando sesión de agente..."});
			url = '?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.checkLogin';
			if (s) url = '?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.checkLogin&noErr=true';
			SPA.post(url, {}, {
				success: function (respuesta) {
					if (respuesta['action'] == 'wait') {
						// Todavía no se termina proceso login, se espera
						setTimeout('do_checklogin()', 1);
					}
					if (respuesta['action'] != 'wait' && respuesta['message'] == 'logged-in') {
						// Login de agente ha tenido éxito, se refresca para mostrar formulario
						$('#agentConsoleLogin').collapse('hide')
						$('#agentConsolePanel').collapse('show')
						setTimeout(do_checkstatus, 1);
					}
					preloader.hide();
				},
				fail: function() {
					preloader.hide();
				}
				});
		}

		// El siguiente código se ejecuta al presionar el botón de fin de sesión
		function do_logout()
		{
			$.get('?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.logoutAgent', function (respuesta) {		
				if (respuesta['action'] == 'error') {
					SPA.notify(respuesta['message'], "error");
				}
			}, 'json')
			.fail(function() {
				SPA.notify('Failed to connect to server to run request!', "error");
			});
		}

		function do_break()
		{
			$.post('?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.break', {
				breakid:	$('#break_select').val()
			},
			function (respuesta) {
				
				if (respuesta['action'] == 'error') {
					SPA.notify(respuesta['message'], "error");
				}

			}, 'json')
			.fail(function() {
				SPA.notify('Failed to connect to server to run request!', "error");
			});
		}

		function do_unbreak()
		{
			// Botón está en estado de quitar break
			$.get('?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.unbreak',
			function (respuesta) {
				
				if (respuesta['action'] == 'error') {
					mostrar_mensaje_error(respuesta['message']);
				}

				// El cambio de estado de la interfaz se delega a la revisión
				// periódica del estado del agente.
				// TODO: definir evento agentbreakenter y agentbreakexit
			}, 'json')
			.fail(function() {
				mostrar_mensaje_error('Failed to connect to server to run request!');
			});
		}

		function do_transfer()
		{
			$.post('index.php?menu=' + module_name + '&rawmode=yes', {
				menu:		module_name,
				rawmode:	'yes',
				action:		'transfer',
				extension:	$('#transfer_extension').val(),
				atxfer: 	$('#transfer_type_attended').is(':checked')
			},
			function (respuesta) {
				
				if (respuesta['action'] == 'error') {
					mostrar_mensaje_error(respuesta['message']);
				}

				// El cambio de estado de la interfaz se delega a la revisión
				// periódica del estado del agente.
			}, 'json')
			.fail(function() {
				mostrar_mensaje_error('Failed to connect to server to run request!');
			});
		}

		function do_ping() {
			$.get('?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.ping', function (respuesta) {
				setTimeout(do_ping, ($.parseJSON(respuesta)['gc_maxlifetime'] / 2) * 1000);
			});
		}

		function do_checkstatus()
		{
			SPA.AddSSEListener("call_center.agentconsole", function(m) { manejarRespuestaStatus(m); });
			SPA.get('?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.checkStatus');
			SPA.onUnload(function() {
				SPA.get('?req=signalSSE&target=<?php echo $vars["modBaseName"]; ?>.monitor&signal=stop');
			});

			// Iniciar el ping de inmediato
			setTimeout(do_ping, 1);
		}

		function manejarRespuestaStatus(respuesta)
		{
			for (var i in respuesta) {
				if (respuesta[i].timer_seconds != null) {
					if (respuesta[i].timer_seconds !== '') {
						iniciar_cronometro(respuesta[i].timer_seconds);
					} else {
						iniciar_cronometro(null);
					}
				}

				switch (respuesta[i].event) {
				case 'logged-out':
					AgentLoggedOut=true;
					$("#agentconsole_callerbadges").collapse('hide')
					$('#agentConsoleLogin').collapse('show');
					$('#agentConsolePanel').collapse('hide');
					SPA.notify("Sesión de Consola de Agente finalizada.");
					return;
				case 'breakenter':
					// El agente ha entrado en break
					$("#agentConsolePanel")
						.removeClass("border-secondary" ).addClass( "border-warning" )
						.children(".card-header").removeClass( "bg-secondary" ).addClass( "bg-warning" )
						.children(".text-light.float-start").text("En receso - " + respuesta[i].breakName);
					agentStatus.break_id = respuesta[i].break_id;
					$('#btn_break')
						.removeClass('btn-outline-warning')
						.addClass('btn-outline-success unbreak')
						.children("span").text("Fin receso");
					$('#btn_break svg').removeClass("fa-pause").addClass("fa-play");
					break;
				case 'breakexit':
					// El agente ha salido del break
					agentStatus.break_id = null;
					$("#agentConsolePanel")
						.removeClass("border-warning" ).addClass( "border-secondary" )
						.children(".card-header").removeClass( "bg-warning" ).addClass( "bg-secondary" )
						.children(".text-light.float-start").text("No hay llamada activa");
					$('#btn_break')
						.removeClass('btn-outline-success unbreak')
						.addClass('btn-outline-warning')
						.children("span").text("Receso");
						$('#btn_break svg').removeClass("fa-play").addClass("fa-pause");
					break;
				case 'agentlinked':
					// El agente ha recibido una llamada
					agentStatus.callid = respuesta[i].callid;
					update_agent_state(respuesta[i])
					$('#btn_hangup').button('enable');
					$('#btn_transfer').button('enable');
					
					$("#agentConsolePanel")
						.removeClass( "border-secondary" ).addClass( "border-success" )
						.children(".card-header").removeClass( "bg-secondary" ).addClass( "bg-success" )
						.children(".text-light.float-start").text( "Conectado a llamada" );
					$("#agentconsole_callerbadges").collapse('show')

					break;
				case 'agentunlinked':
					// El agente se ha desconectado de la llamada
					agentStatus.callid = null;

					$('#btn_hangup').button('disable');
					$('#btn_transfer').button('disable');
					$('#btn_newReport').button('disable');
					$('#issabel-callcenter-cronometro').text('00:00:00');
					
					$("#agentConsolePanel")
						.removeClass( "border-success" ).addClass( "border-secondary" )
						.children(".card-header").removeClass( "bg-success" ).addClass( "bg-secondary" )
						.children(".text-light.float-start").text( "No hay llamada activa" );
					break;
				}
			}
		}
	
	SPA.whenReady(function () {
		$.extend( $.fn.dataTable.defaults, {"autoWidth": false, language: {url: "assets/lang/datatables-es.json"}});
		reportsTable = $("#reportsTable").DataTable({
			"retrieve": true,
			"info": false,
			"lengthChange": false,
			"columns": [
				{ "data": ["id"],
					"visible": false,
				},
				{ "data": ["desc"] },
				{ "data": ["status"] },
				{ "data": ["content"] },
			  ],
			"ajax": {
				"url": "?req=function&module=<?php echo $vars['modBaseName'];?>&function=reports.getReportsDT",
				//"dataSrc": "DataTable"
			},
			createdRow: function(row, data, dataIndex) { $(row).html(reports.drawReport(data)); },
			searchPanes: {
				panes: [
					{
						header: 'Estatus',
						targets: ["desc"],
						orderable: false,
						options: [
							{
								label: 'Abierto',
								value: function(rowData, rowIdx) {
									return rowData["status"] === '0';
								}
							},
							{
								label: 'Aprobado',
								value: function(rowData, rowIdx) {
									return rowData["status"] === '1';
								}
							},
							{
								label: 'Denegado',
								value: function(rowData, rowIdx) {
									return rowData[2] === 'Accountant';
								}
							},
							{
								label: 'Recibido',
								value: function(rowData, rowIdx) {
									return rowData[2] === 'Accountant';
								}
							},
							{
								label: 'Atendido',
								value: function(rowData, rowIdx) {
									return rowData[2] === 'Accountant';
								}
							},
						]
					}
				],
			},
			dom: 'lfrtip' //P'
		});
		$("#reportsTable thead").remove();
		$('#reportsTable tbody').on('click', "tr", function () {
			var data = reportsTable.row(this).data();
			reports.open(data.id);
			console.log(data);
		});
		jQuery('#agentLogin').ajaxForm({
			beforeSubmit: function(arr, $form, options) {preloader.show({text:"Iniciando sesión de agente..."})},
			success: function(responseJSON, statusText) {
				const responseArr = JSON.parse(responseJSON);
				if(responseArr['status'] == "OK") {
					setTimeout('do_checklogin()', 1);
				} else {
					SPA.notify(responseArr['message'], "error");
					preloader.hide();
				}
			}
		});
		
		$("#agentConsolePanel").focusin(function(){
			$("#agentConsoleKbdStatus").removeClass( "text-secondary border-secondary" ).addClass( "text-success border border-success" );
		});
		
		$("#agentConsolePanel").focusout(function(){
			$("#agentConsoleKbdStatus").removeClass( "text-success border-success border" ).addClass( "text-secondary border-secondary" );
		});
		
		//Inicializar Selectize.js y tooltips de Bootstrap
		jQuery(".selectize").selectize();
		var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
		var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
			return new bootstrap.Tooltip(tooltipTriggerEl, {trigger: 'hover'})
		});
		
		//Inicializar atajos de teclado
		$("#agentConsolePanel").on( "keypress", function(evt) {
			//console.log(evt.originalEvent);
			switch (evt.originalEvent.code) {
				case "KeyQ":
					$("#btn_hangup:enabled").click(); break;
				case "KeyW":
					$("#btn_transfer:enabled").click(); break;
				case "KeyE":
					$("#btn_break:enabled").click(); break;
				case "KeyR":
					$("#btn_logout:enabled").click(); break;
				case "KeyM":
					$("#btn_mute:enabled").click(); break;
				case "KeyN":
					$("#btn_newReport:enabled").click(); break;
			}
		});
		
		//Obtener opciones de formulario
			$.get('?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=agentconsole.getFormData', function (respuesta) {		
				if (respuesta['action'] == 'error') {
					SPA.notify(respuesta['message'], "error");
				}
				$.each(respuesta['exts'], function(ext, extname) {$('#exts').append($('<option/>').attr("value", ext).text(extname));});
				$.each(respuesta['breaks'], function(breakid, breakname) {$('#break_select').append($('<option/>').attr("value", breakid).text(breakname));});
				reportClasses = respuesta['reportClasses'];
				reportInvolved = respuesta['reportInvolved'];
			}, 'json')
			.fail(function() {
				SPA.notify('Failed to connect to server to run request!', "error");
			});
			
			$("#loadReport").click(function() {reportTabs.load();});
			$("#saveReport").click(function() {reportTabs.save();});
			$("#closeReport").click(function() {reportTabs.close();});
			
			//Handler para el evento ModContent
			jQuery(document).one("ModContent", function (e) {
				if (evtSource != null) {
					evtSource.close();
					evtSource = null;
				}
			});
			$('#btn_hangup').button().removeClass("ui-button ui-corner-all ui-widget");
			$('#btn_hangup').click(do_hangup);
			
			$('#btn_logout').click(do_logout);

			// El siguiente código se ejecuta al hacer click en el botón de break
			$('#btn_break').click(function() {
				if ($('#btn_break').hasClass('unbreak')) {
					do_unbreak();
				} else {
					// Botón está en estado de elegir break
					$("#callcenter-select-break").modal("show")
				}
			});

			$('#btn_transfer').button().removeClass("ui-button ui-corner-all ui-widget");
			$('#btn_transfer').click(function() {
				$('#issabel-callcenter-seleccion-transfer').dialog('open');
			});

			do_checklogin(true);
	});
    </script>
<?php } else { ?>
				<div class="row">
					<div class="card border collapse show" id="agentConsoleLogin">
						<div class="card-header"><strong class="float-left">Acceso limitado</strong></div>
						<div class="card-body">
							<?php if (!@$userdata["callcenter_id"]["value"]) { ?><span>Esta cuenta de usuario no tiene un ID de Agente asignado</span>
							<?php } else { ?><span>El acceso a la Consola de Agente está deshabilitado temporalmente para esta cuenta de usuario</span>
							<?php } ?>
						</div>
					</div>	
				</div>	
<?php } ?>