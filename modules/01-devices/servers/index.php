               <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
				            <div class="card-header">
                                <strong class="card-title">Administración de encendido</strong>
                            </div>
                            <div class="card-body">
								<strong>Servidor 1</strong>
								<div>
									<button type="button" class="btn btn-success" onclick="powerControl(1,'pwrOn');"><i class="fa fa-bolt"></i>&nbsp; Encender</button>
									<button type="button" class="btn btn-secondary" onclick="powerControl(1,'Reboot');"><i class="fa fa-refresh"></i>&nbsp; Reiniciar</button>
									<button type="button" class="btn btn-danger" onclick="powerControl(1,'pwrOff');"><i class="fa fa-power-off"></i>&nbsp; Apagar</button>
								</div>
								<hr>
								<strong>Servidor 2</strong>
								<div>
									<button type="button" class="btn btn-success" onclick="powerControl(2,'pwrOn');"><i class="fa fa-bolt"></i>&nbsp; Encender</button>
									<button type="button" class="btn btn-secondary" onclick="powerControl(2,'Reboot');"><i class="fa fa-refresh"></i>&nbsp; Reiniciar</button>
									<button type="button" class="btn btn-danger" onclick="powerControl(2,'pwrOff');"><i class="fa fa-power-off"></i>&nbsp; Apagar</button>
								</div>
								<hr>
								<strong>Servidor 3</strong>
								<div>
									<button type="button" class="btn btn-success" onclick="powerControl(3,'pwrOn');"><i class="fa fa-bolt"></i>&nbsp; Encender</button>
									<button type="button" class="btn btn-secondary" onclick="powerControl(3,'Reboot');"><i class="fa fa-refresh"></i>&nbsp; Reiniciar</button>
									<button type="button" class="btn btn-danger" onclick="powerControl(3,'pwrOff');"><i class="fa fa-power-off"></i>&nbsp; Apagar</button>
								</div>
                            </div>
                        </div>
                    </div>
                </div>