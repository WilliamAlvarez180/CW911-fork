<style>
	body {
		font-family: Arial;
		color: #333;
		font-size: 0.95em;
	}
	
	#frmLogin {
		 margin: 0;
		 position: absolute; 
		 top: 50%; 
		 left: 50%;
		 transform: translate(-50%, -50%);
	}

	.form-head {
		color: #191919;
		font-weight: normal;
		font-weight: 400;
		margin: 0;
		text-align: center;
		font-size: 1.8em;
	}

	.error-message {
		padding: 7px 10px;
		background: #fff1f2;
		border: #ffd5da 1px solid;
		color: #d6001c;
		border-radius: 4px;
		margin: 30px 10px 10px 10px;
	}

	.login-table {
		background: #ffffff;
		border-spacing: initial;
		margin: 15px auto;
		word-break: break-word;
		table-layout: auto;
		line-height: 1.8em;
		color: #333;
		border-radius: 4px;
		padding: 30px;
		width: 380px;
		border: 1px solid;
		border-color: #e5e6e9 #dfe0e4 #d0d1d5;
	}

	.login-table .label {
		color: #888888;
	}

	.login-table .field-column {
		padding: 15px 10px;
	}

	.input-box {
		padding: 13px;
		border: #CCC 1px solid;
		border-radius: 4px;
		width: 100%;
	}

	.btnLogin {
		padding: 13px;
		background-color: #5d9cec;
		color: #f5f7fa;
		cursor: pointer;
		border-radius: 4px;
		width: 100%;
		border: #5791da 1px solid;
		font-size: 1.1em;
	}
	
	.error-info {
		color: #FF0000;
		margin-left: 10px;
	}
	
	a.logout-button {
		color: #09f;
	}
	
	#particles-js {
		position: fixed;
		width: 100%;
		height: 100%;
		z-index: -1;
	}
</style>
<body>
	<div id="particles-js"></div>
    <div>
        <form action="?req=login" method="post" id="frmLogin" onSubmit="return validate();">
            <div class="login-table">
			<img src="assets/img/logo.png" style="width:100%;">

                <div class="form-head">Iniciar Sesión</div>
                <?php 
                if(isset($_GET["code"])) {
                ?>
                <div class="error-message sufee-alert alert with-close alert-danger alert-dismissible fade show">
                    <span class="badge badge-pill badge-danger">Error</span>
                    <?php  echo "Primero debe iniciar sesión"; ?>
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span></button>
                </div>
                <?php } ?>
                <div class="field-column">
                    <div>
                        <label for="user_name">Usuario</label><span id="user_info" class="error-info"></span>
                    </div>
                    <div>
                        <input name="user_name" id="user_name" type="text"
                            class="input-box">
                    </div>
                </div>
                <div class="field-column">
                    <div>
                        <label for="password">Contraseña</label><span id="password_info" class="error-info"></span>
                    </div>
                    <div>
                        <input name="password" id="password" type="password"
                            class="input-box">
                    </div>
                </div>
                <div class=field-column>
                    <div>
                        <input type="submit" class="btnLogin"></span>
                    </div>
                </div>
				<h5 style="text-align: center; margin: 5px auto;">Coordinación tecnología Apure - Diseñado para CCCT Ven911</h5>
            </div>
        </form>
    </div>
    <script>
    function validate() {
        var $valid = true;
        document.getElementById("user_info").innerHTML = "";
        document.getElementById("password_info").innerHTML = "";
        
        var userName = document.getElementById("user_name").value;
        var password = document.getElementById("password").value;
        if(userName == "") 
        {
            document.getElementById("user_info").innerHTML = "Campo requerido";
        	$valid = false;
        }
        if(password == "") 
        {
        	document.getElementById("password_info").innerHTML = "Campo requerido";
            $valid = false;
        }
        return $valid;
    }
	SPA.whenReady(function () {
		particlesJS("particles-js", {
			  "particles": {
				"number": {
				  "value": 100,
				  "density": {
					"enable": true,
					"value_area": 850
				  }
				},
				"color": {
				  "value": "#ffffff"
				},
				"shape": {
				  "type": "circle",
				  "stroke": {
					"width": 0.3,
					"color": "#121220"
				  }
				},
				"opacity": {
				  "value": 0.48927153781200905,
				  "random": false,
				  "anim": {
					"enable": false,
					"speed": 0.5,
					"opacity_min": 0,
					"sync": false
				  }
				},
				"size": {
				  "value": 6,
				  "random": true,
				  "anim": {
					"enable": false,
					"speed": 4,
					"size_min": 0,
					"sync": false
				  }
				},
				"line_linked": {
				  "enable": true,
				  "distance": 150,
				  "color": "#1c7af1",
				  "opacity": 0.9,
				  "width": 0.6
				},
				"move": {
				  "enable": true,
				  "speed": 0.6,
				  "direction": "none",
				  "random": true,
				  "straight": false,
				  "out_mode": "out",
				  "bounce": false,
				  "attract": {
					"enable": false,
					"rotateX": 600,
					"rotateY": 1200
				  }
				}
			  },
			  "interactivity": {
				"detect_on": "canvas",
				"events": {
				  "onhover": {
					"enable": false,
					"mode": "repulse"
				  },
				  "onclick": {
					"enable": true,
					"mode": "push"
				  },
				  "resize": true
				},
				"modes": {
				  "repulse": {
					"distance": 200,
					"duration": .01
				  },
				  "push": {
					"particles_nb": 1
				  },
				  "remove": {
					"particles_nb": 2
				  }
				}
			  },
			  "retina_detect": false
		});
		
		$('#frmLogin').ajaxForm({dataType: 'json',
			beforeSubmit: function() {preloader.show({});},
			success: function(data) {
				SPA.handleJSON(data);
				if(data.status == "OK") {
					preloader.hide();
				} else if (data.status=="ERR") {
                  preloader.hide();
				}
			},
			error: function (data) {
				SPA.notify("Error inesperado", 3);
				preloader.hide();
			}
		});
	});
	//notify("Error de conexión. Revisa el acceso de red al sistema", "error");
    </script>
</body>