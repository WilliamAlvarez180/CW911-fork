<?php
require_once $vars["modLib"] ."paloSantoConsola.class.php";
$oPaloConsola = new PaloSantoConsola();
$agents = $oPaloConsola->listarAgentes(); 
?>
					<div class="col-lg-12">
                        <div class="card border border-secondary collapse show" id="userListCard">
                            <div class="card-body">
                                <table class="table table-sm table-hover ov-h" id="userList" data-order='[[ 0, "asc" ]]'>
                                    <thead>
                                        <tr>
                                            <th class="serial">ID</th>
                                            <th>Usuario</th>
                                            <th>Nombre</th>
                                            <th>Cédula</th>
                                            <th>Agente</th>
                                            <th>ID Agente</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
						<div class="card border border-secondary collapse" id="userSettingsCard">
                            <div class="card-header">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_back"><i class="fa fa-arrow-left"></i>&nbsp;Volver</button>
							</div>
                            <div class="card-body"></div>
							<div class="card-footer"><button type="submit" form="userSettingsForm" class="float-end"><i class="fa fa-floppy-disk"></i>&nbsp; Guardar</button></div>
                        </div>
					</div>
                    
                    <script>                            
                        SPA.whenReady( function (e) {
                            $.extend( $.fn.dataTable.defaults, {"autoWidth": false, language: {url: "assets/lang/datatables-es.json"}});
                            userList = $("#userList").DataTable({
                                "retrieve": true,
                                "info": false,
								"lengthChange": false,
                                "columns": [
                                    { "data": ["userid"],
                                        "visible": false,
                                    },
                                    { "data": ["user"] },
                                    { "data": ["username"] },
                                    { "data": ["ic"] },
                                    { "data": ["agent"],
                                        "render": function ( data, type, row, meta ) {
                                            if (data) {
												return "<span class='badge bg-success'>Habilitado</span>";
											} else {
												return "<span class='badge bg-danger'>Deshabilitado</span>";
											}
                                        },
                                    },
									{ "data": ["agentID"] },
                                  ],
                                "ajax": {
                                    "url": "?req=function&module=<?php echo $vars['modBaseName'];?>&function=usermanager.getUserlistDT",
                                    "dataSrc": "DataTable"
                                }
                            });
							$('#userList tbody').on('click', "tr", function () {
								var data = userList.row(this).data();
								$("#userSettingsForm #userID").prop("value", data["userid"]);
								$("#userSettingsForm #agentEnabled").prop("checked", data["agent"]);
								$("#userSettingsForm #agentID").prop("value", data["agentID"]);
								$("#userListCard").collapse("hide");
								$("#userSettingsCard").collapse("show");
							});
							
							// Establecer manejadores de eventos para los botones
							$("#userSettingsCard #btn_back").on( "click", function () {
								$("#userListCard").collapse("show"); $("#userSettingsCard").collapse("hide");
							});
							
							//Crear y poblar Form para ajustes de usuario
							const form = document.createElement('form')
							form.setAttribute("id" , "userSettingsForm");
							$(form)
								.append(formCreator.a({ itemType: "input", value: "", type: "hidden", id: 'userID', name: 'userID' }))
								.append(formCreator.sw({ value: "", label: "Habilitado para Centro de Llamadas", id: 'agentEnabled', name: 'agentEnabled' }))
								.append(formCreator.f({ itemType: "select", value: "", label: "ID de Agente", id: 'agentID', name: 'agentID', classes: ["form-control"] }));
							$("#userSettingsCard > div.card-body").append(form);
							$('#agentID').append($('<option/>').attr("value", "null").text("Ninguno"));
							$.each(<?php echo json_encode($agents);?>, function(id, name) {$('#agentID').append($('<option/>').attr("value", id).text(name));});
							
							// Establecer manejadores de eventos para los formularios
							$("#userSettingsForm").validate({
                                debug: false,
                                submitHandler: function(form) {
									var dataObject = {};
									$.each($(form).serializeArray(), function(_, item) {
										if (dataObject.hasOwnProperty(item.name)) {
											dataObject[item.name] = [].concat(dataObject[item.name], item.value);
										} else {
											dataObject[item.name] = item.value;
										}
									});
									$.post("?req=function&module=<?php echo $vars['modBaseName'];?>&function=usermanager.setUserCFG", {cfg: JSON.stringify(dataObject)},
										function(response) {
											if (response['status'] == 'OK') {
												userList.ajax.reload();
												SPA.notify("Ajustes establecidos", "success");
												$("#userSettingsCard #btn_back").click();
											} else {
												SPA.notify(response['message'], "error");
											}
										}, 'json')
										.fail(function() {
											SPA.notify("Error inesperado", "error");
										});
								},
                                rules: {
                                    agentID: {
                                        required: true
                                    }
                                },
                                messages: {
                                    agentID: {
                                        required: "Debe seleccionar un ID de Agente",
                                    }
                                },
                                validClass: "is-valid",
                                errorClass: "is-invalid",
                                errorPlacement: function(error, element) {
                                    error.insertAfter(element).addClass("invalid-feedback"); // La misma función interna pero con un addClass
                                }
                            });
                        });
                    </script>