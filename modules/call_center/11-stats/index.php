				<div class="row">
                    <div class="col">
						<div class="card border collapse show" id="agentConsolePBXConnection">
							<div class="card-header"><strong class="float-left">Ajustes de conexión al PBX</strong></div>
							<div class="card-body"></div>
							<div class="card-footer">					
								<button type="submit" form="PBXConnectionForm" class="float-end"><i class="fa fa-floppy-disk"></i>&nbsp; Guardar</button>
							</div>
						</div>
                    </div><!-- /# column -->
                </div>
	<script id="<?php echo $vars["modName"]; ?>-JS">
		jQuery(document).one("AssetsLoaded", function (e) {
			//Form
			const form = document.createElement('form')
			form.setAttribute("method" , "post");
			form.setAttribute("id" , "PBXConnectionForm");
			$(form)
				.append(formCreator.f({ itemType: "input", value: "<?php echo @$vars["modCFG"]["config"]["PBXHost"]; ?>", label: "IP del Servidor PBX", type: "text", id: 'PBXHost', name: 'PBXHost', classes: ["form-control"] }))
				.append(formCreator.f({ itemType: "input", value: "<?php echo @$vars["modCFG"]["config"]["AMPDBEngine"]; ?>", label: "Motor DB de AMP", type: "text", id: 'AMPDBEngine', name: 'AMPDBEngine', classes: ["form-control"] }))
				.append(formCreator.f({ itemType: "input", value: "<?php echo @$vars["modCFG"]["config"]["AMPDBUser"]; ?>", label: "Usuario de AMP", type: "text", id: 'AMPDBUser', name: 'AMPDBUser', classes: ["form-control"] }))
				.append(formCreator.f({ itemType: "input", value: "<?php echo @$vars["modCFG"]["config"]["AMPDBPass"]; ?>", label: "Clave de AMP", type: "text", id: 'AMPDBPass', name: 'AMPDBPass', classes: ["form-control"] }))
				.append(formCreator.f({ itemType: "input", value: "<?php echo @$vars["modCFG"]["config"]["ECCPUser"]; ?>", label: "Usuario de ECCP", type: "text", id: 'ECCPUser', name: 'ECCPUser', classes: ["form-control"] }))
				.append(formCreator.f({ itemType: "input", value: "<?php echo @$vars["modCFG"]["config"]["ECCPPass"]; ?>", label: "Clave de ECCP", type: "text", id: 'ECCPPass', name: 'ECCPPass', classes: ["form-control"] }));
			$("#agentConsolePBXConnection .card-body").append(form);
			$("#PBXConnectionForm").validate({
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
					$.post("?req=mod-cfg&module=<?php echo $vars['modBaseName'];?>&action=update", {cfg: JSON.stringify({"config":dataObject})},
						function(response) {
							if (response['status'] == 'OK') {
								notify("Ajustes establecidos", "success");
							} else {
								notify(response['message'], "error");
							}
						}, 'json')
						.fail(function() {
							notify("Error inesperado", "error");
						});
				},
				rules: {
					PBXHost: {
						required: true
					},
					AMPDBEngine: "required",
					AMPDBUser: "required",
					AMPDBPass: "required",
					ECCPUser: "required",
					ECCPPass: "required"
				},
				messages: {
					username: {
						required: "Debe ingresar un nombre de usuario",
					},
					password: {
						required: "Debe ingresar una contraseña",
						minlength: jQuery.validator.format("La contraseña debe tener al menos {0} caracteres"),
						maxlength: jQuery.validator.format("La contraseña debe tener máximo {0} caracteres")
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