					<div class="col-lg-12">
                        <div class="card border border-secondary collapse show" id="ALListCard">
                            <div class="card-header">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_add"><i class="fa fa-plus"></i>&nbsp;Agregar nivel</button>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-hover ov-h" id="accessLevelList" data-order='[[ 1, "asc" ]]'>
                                    <thead>
                                        <tr>
                                            <th class="serial">ID</th>
                                            <th>Nombre</th>
                                            <th>Descripción</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
						<div class="card border border-secondary collapse" id="ALDetailsCard">
                            <div class="card-header">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_back"><i class="fa fa-arrow-left"></i>&nbsp;Volver</button>
								<button type="button" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash-can"></i>&nbsp;Eliminar nivel de acceso</button>
                            </div>
                            <div class="card-body">
									<div class="accordion accordion-flush" id="ALDetailsAccordion"></div>
                            </div>
							<div class="card-footer">					
								<button id="btn_AL_submit" class="float-end"><i class="fa fa-floppy-disk"></i>&nbsp; Guardar</button>
							</div>
                        </div>
						<div class="card border border-secondary collapse" id="modAddCard">
                            <div class="card-header">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btn_back_ma"><i class="fa fa-arrow-left"></i>&nbsp;Volver</button>
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
                    <script id="AccessLevelJS">
						function buildPermsObject(inputs) {
						  var obj = {};
						  inputs.each(function() {
							if ($(this).hasClass('accordion-collapse')) {
								var name = $(this).attr('data-ng-name');
							} else {
								var name = $(this).attr('name');
							}
							if ($(this)[0].role == "switch") {
								var value = $(this).is(':checked');
							} else {	
								var value = $(this).val();
							}
							
							// Obtener los inputs hijos
							var children = $(this).children('div').find('input');
							
							// Si hay inputs hijos, construir el árbol de objetos recursivamente
							if (children.length > 0) {
							  obj[name] = buildPermsObject(children);
							} else {
							  if ($(this).attr('data-ng-role') == "submenu-switch") {
								obj["enabled"] = value;
							  } else {
								if (!obj.hasOwnProperty('items'))obj["items"] = {};
								obj.items[name] = {enabled: value};
							  }
							}
						  });
						  return obj;
						}
						function buildpermsTree(key, submenuData, modenabled) {
							console.log(modenabled);
							var $submenu = $("<div>", {id: key})
								.attr("data-ng-name", key.substr(3))
								.attr("data-bs-parent", "#ALDetailsAccordion")
								.addClass("accordion-collapse collapse");
							$submenu.append($("<div>").addClass("ms-2 mt-2 form-check form-switch")
								.append($("<input>", {id: "flexSwitchCheck"+ key, name: key.substr(3), type: "checkbox", role: "switch"})
									.attr("data-ng-role", "submenu-switch")
									.addClass("form-check-input")
									.prop('checked', modenabled)
									.change(function (event) {console.log(event);})
								)
								.append($("<label>", {for: "flexSwitchCheck"+ key, text: "Habilitar módulo"}).addClass("form-check-label"))
							).append($("<hr>"));
							$.each(submenuData, function(key, val) {
							var $submenuItem;

							if (val.type === "item") {
								$submenuItem = $("<div>").addClass("ms-2 my-1 form-check form-switch")
									.append($("<input>", {id: "flexSwitchCheck"+ key, name: key, type: "checkbox", role: "switch"}).addClass("form-check-input").prop('checked', val.enabled))
									.append($("<i>").addClass("me-1 fas fa-"+val.icon))
									.append($("<label>", {for: "flexSwitchCheck"+ key, text: val.name}).addClass("form-check-label"));
							} else if (val.type === "submenu") {
								$submenuItem = $("<li>")
									.append($("<a>")
										.append(
											$("<i>", { href: "#AL-" +key }).addClass("fas fa-plus me-3")
											.attr("data-bs-toggle", "collapse")
										)
										.append($("<i>").addClass("fas fa-"+val.icon))
										.append($("<span>", {text: val.name}).addClass("ms-1"))
										.append(
											$("<div>").addClass("form-check form-switch").append(
												$("<input>").addClass("form-check-input")
											)
										)
									)
									.append(buildSubMenu(key, val.items));
							}
							$submenu.append($submenuItem);
							});

							return $submenu;
						}
								
						function loadModPerms(level) {
							SPA.get('?req=function&module=<?php echo $vars["modBaseName"]; ?>&function=perms.getPermsTree&levelID=' + level, {success: function (respuesta) {
								ALID = level;
								perms = respuesta["permsTree"];
								var permsTree = $("<div>");
								$.each(perms, function(key, val) {
									var $menuItem;
									if (val.type === "item") {
										$menuItem = $("<li>").addClass("nav-item mb-1")
										.append($("<a>")
											.addClass("nav-link")
											.append($("<div>").addClass("form-check form-switch")
												.append($("<input>").addClass("form-check-input"))
												.append($("<label>", {for: val.name}).addClass("form-check-label")
													.append($("<i>").addClass("fas fa-"+val.icon))
													.append($("<span>", {text: val.name}).addClass("ms-1"))
												)
											)
										);
									} else if (val.type === "submenu") {
										$menuItem = $("<div>")
										.addClass("accordion-item")
										.append($("<h2>")
											.addClass("accordion-header")
											.append($("<button>", {"data-bs-toggle": "collapse", "data-bs-target": "#AL-" +key})
												.addClass("accordion-button")
												.append($("<i>").addClass("me-1 fas fa-"+val.icon))
												.append($("<span>", {text: val.name}))
											)
										)
										.append(buildpermsTree("AL-" + key ,val.items, val.enabled));
									}
									permsTree.append($menuItem);
								});
								$("#ALDetailsCard #ALDetailsAccordion").html(permsTree);
							}});
						}
						
                        SPA.whenReady(function (e) {
                            $.extend( $.fn.dataTable.defaults, {"autoWidth": false, language: {url: "assets/lang/datatables-es.json"}});
                            accessLevelList = $("#accessLevelList").DataTable({
                                "retrieve": true,
                                "info": false,
								"lengthChange": false,
                                "columns": [
                                    { "data": ["ID"],
                                        "visible": false
                                    },
                                    { "data": ["name"] },
                                    { "data": ["desc"] },
                                  ],
                                "ajax": {
                                    "url": "?req=function&module=<?php echo $vars['modBaseName'];?>&function=perms.getAccessLevelsDT",
                                    "dataSrc": "DataTable"
                                }
                            });
							$('#accessLevelList tbody').on('click', "tr", function () {
								var data = accessLevelList.row(this).data();
								loadModPerms(data["ID"]);
								$("#ALListCard").collapse("hide");
								$("#ALDetailsCard").collapse("show");
							});
							
							// Establecer manejadores de eventos para los botones
							$("#ALListCard #btn_add").on( "click", function () {
								$("#ALListCard").collapse("hide"); $("#userAddCard").collapse("show"); 
							});
							$("#ALDetailsCard #btn_back").on( "click", function () {
								$("#ALListCard").collapse("show"); $("#ALDetailsCard").collapse("hide");
							});
							$("#btn_AL_submit").on( "click", function () {
								var ALData = buildPermsObject($('#ALDetailsAccordion div.accordion-collapse'));
								SPA.post("?req=function&module=<?php echo $vars['modBaseName'];?>&function=perms.setAccessLevel", {ALID: ALID, ALPerms: JSON.stringify(ALData)}, {});
							});
						});
                    </script>