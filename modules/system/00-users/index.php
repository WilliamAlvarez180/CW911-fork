					<div class="col-lg-12">
                        <div class="card border border-secondary collapse show" id="userListCard">
                            <div class="card-header">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add"><i class="fa fa-plus"></i>&nbsp;Agregar usuario</button>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-hover ov-h" id="userList" data-order='[[ 0, "asc" ]]'>
                                    <thead>
                                        <tr>
                                            <th class="serial">ID</th>
                                            <th>Usuario</th>
                                            <th>Nombre</th>
                                            <th>Cédula</th>
                                            <th>Cargo</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
						<div class="card border border-secondary collapse" id="userDetailsCard">
                            <div class="card-header">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_back"><i class="fa fa-arrow-left"></i>&nbsp;Volver</button>
								<button type="button" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash-can"></i>&nbsp;Eliminar usuario</button>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-hover ov-h" id="userDetailsTable">
                                    <thead>
                                        <tr>
                                            <th class="serial">Propiedad</th>
                                            <th>Valor</th>
										</tr>
									</thead>
                                </table>
                            </div>
                        </div>
						<div class="card border border-secondary collapse" id="userAddCard">
                            <div class="card-header">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_back_ua"><i class="fa fa-arrow-left"></i>&nbsp;Volver</button>
							</div>
                            <div class="card-body">
                                <form action="?req=function&module=<?php echo $vars['modBaseName'];?>&function=usermanager.addUser" method="post" id="addUser" class="row" autocomplete="off">							
									<div class="col-md-6"><label for="username" class="form-label">Nombre de usuario</label><input type="text" placeholder="Ingrese nombre de usuario..." name="username" id="username" class="form-control"></input></div>
									<div class="col-md-6">
                                        <label for="accesslevel" class="form-label">Nivel de acceso</label>
                                        <select placeholder="Seleccione un nivel..." required name="accesslevel" id="accesslevel" class="form-select">
                                            <?php
                                                require_once $vars["lib"]."DB.php";
                                                $ds = new DataSource();
                                                if (!@$ds->err) { //Break if any problem connecting to database.
                                                    $arrResult = $ds->select("SELECT  ID, name FROM accesslevels");
                                                    if(!empty($arrResult)) {			
                                                        foreach ($arrResult as $k => $v) {
                                                            echo ("<option value=" .$v["ID"]. ">" .$v["name"]. "</option>");
                                                        }
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
									<div class="col-md-6">
										<label for="password" class="form-label">Contraseña</label>
										<input type="password" placeholder="Ingrese contraseña..." name="password" id="password" class="form-control"></input>
                                    </div>
									<div class="col-md-6">
										<label for="passconfirm" class="form-label">Repetir contraseña</label>
										<input type="password" placeholder="Ingrese contraseña de nuevo..." name="passconfirm" id="passconfirm" class="form-control"></input>
                                    </div>
								</form>
                            </div>
							<div class="card-footer">					
								<button type="submit" form="addUser" class="float-end"><i class="fa fa-sign-in"></i>&nbsp; Agregar</button>
							</div>
                        </div>
                    </div>
                    
                    <script>
                        function loadUserDetails(uid) {
                            $("#userDetailsCard #userDetailsTable").remove();
							$("#userDetailsCard .dataTables_wrapper").remove();
                            $("#userDetailsCard > div.card-body").append($(document.createElement('table')).addClass("table table-sm table-hover ov-h").attr("ID", "userDetailsTable"));
                            $("#userDetailsCard > div.card-body > #userDetailsTable").DataTable({
                                "info": false,
								"lengthChange": false,
                                "filter": true,
                                "paging": false,
								"ordering": false,
                                "columns": [
                                    { "data": [0] },
                                    { "data": [1],
                                        "render": function ( data, type, row, meta ) {
                                            switch (data) {
                                            case "0": pdata="Activo"; pclass="success"; break;
                                            case "1": pdata="Reposo"; pclass="warning"; break;
                                            case "2": pdata="Activo"; break;
                                            default: pdata=null; pclass=null;
                                            }
                                            if (pdata) {
												return "<span class='badge bg-"+pclass+"'>"+pdata+"</span>";
											} else {
												return data;
											}
                                        },
                                    },
                                  ],
                                "ajax": {
                                    "url": "?req=function&module=<?php echo $vars['modBaseName'];?>&function=usermanager.getUserDetailsDT&uid="+uid,
                                    "dataSrc": "DataTable"
                                }
                            });
                        }
                            
                        SPA.whenReady(function () {
                            $.extend( $.fn.dataTable.defaults, {"autoWidth": false, language: {url: "assets/lang/datatables-es.json"}});
                            userList = $("#userList").DataTable({
                                "retrieve": true,
                                "info": false,
								"lengthChange": false,
                                "columns": [
                                    { "data": [0],
                                        "visible": false,
                                    },
                                    { "data": [1] },
                                    { "data": [2] },
                                    { "data": [3] },
                                    { "data": [4] },
                                    { "data": [5],
                                        "render": function ( data, type, row, meta ) {
                                            switch (data) {
                                            case "0": pdata="Activo"; pclass="success"; break;
                                            case "1": pdata="Reposo"; pclass="warning"; break;
                                            case "2": pdata="Activo"; break;
                                            default: pdata=null; pclass=null;
                                            }
                                            if (pdata) {
												return "<span class='badge bg-"+pclass+"'>"+pdata+"</span>";
											} else {
												return "";
											}
                                        },
                                    },
                                  ],
                                "ajax": {
                                    "url": "?req=function&module=<?php echo $vars['modBaseName'];?>&function=usermanager.getUserlistDT",
                                    "dataSrc": "DataTable"
                                }
                            });
							$('#userList tbody').on('click', "tr", function () {
								var data = userList.row(this).data();
								loadUserDetails(data[0]);
								$("#userListCard").collapse("hide");
								$("#userDetailsCard").collapse("show");
							});
							
							// Establecer manejadores de eventos para los botones
							$("#userListCard #btn_add").on( "click", function () {
								$("#userListCard").collapse("hide"); $("#userAddCard").collapse("show"); 
							});
							$("#userAddCard #btn_back_ua").on( "click", function () {
								$("#userListCard").collapse("show"); $("#userAddCard").collapse("hide");
							});
							$("#userDetailsCard #btn_back").on( "click", function () {
								$("#userListCard").collapse("show"); $("#userDetailsCard").collapse("hide");
							});
							
							// Establecer manejadores de eventos para los formularios
							$('#password, #confirm_password').on('keyup', function () {
								if ($('#password').val() == $('#confirm_password').val()) {
									$('#message').html('Matching').css('color', 'green');
								} else 
									$('#message').html('Not Matching').css('color', 'red');
							});
							$("#addUser").validate({
                                debug: false,
                                submitHandler: function(form) {
                                    $(form).ajaxSubmit({dataType: 'json',
                                        success: function(data, statusText) {
                                            if(data.status == "OK") {
                                                notify("Usuario creado", "success")
                                                $("#btn_back_ua").click();
                                                userList.ajax.reload();
                                            } else if (data.status=="ERR") {
                                                notify("Error: " +data.message, "error");
                                            }
                                        },
                                        error: function (data) {
                                            notify("Error inesperado", "error");
                                        }
                                    });
                                },
                                rules: {
                                    username: "required",
                                    password: {
                                        required: true,
                                        minlength: 8,
                                        maxlength: 20
                                    },
                                    passconfirm: {
                                        required: true,
                                        equalTo: "#password"
                                    },
                                },
                                messages: {
                                    username: {
                                        required: "Debe ingresar un nombre de usuario",
                                    },
                                    password: {
                                        required: "Debe ingresar una contraseña",
                                        minlength: jQuery.validator.format("La contraseña debe tener al menos {0} caracteres"),
                                        maxlength: jQuery.validator.format("La contraseña debe tener máximo {0} caracteres")
                                    },  
                                    passconfirm: {
                                        required: "Debe ingresar la contraseña de nuevo",
                                        equalTo: "Las contraseñas deben coincidir",
                                    },
                                },
                                validClass: "is-valid",
                                errorClass: "is-invalid",
                                errorPlacement: function(error, element) {
                                    error.insertAfter(element).addClass("invalid-feedback"); // La misma función interna pero con un addClass
                                }
                            });
                        });
                    </script>