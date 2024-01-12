        <!-- Header-->
        <header id="header" class="header border-bottom sticky-top bg-white">
		<div class="container-fluid d-grid align-items-center" style="grid-template-columns: 1fr 2fr;">
            <div class="d-flex flex-row">
				<div class="btn-group" role="group">
					<button type="button" class="btn btn-outline-secondary" data-bs-target="#sidebar" data-bs-toggle="offcanvas"><i class="fa fa-bars"></i></a>
					<button type="button" class="btn btn-outline-secondary" data-bs-toggle="fullscreen"><i class="fas fa-expand"></i></button>
				</div>
			</div>
			
            <div class="d-flex flex-row-reverse">
                <div class="header-menu">
                    <div class="header-left">				
                        <div class="dropdown for-notification">
                            <button class="btn dropdown-toggle" type="button" id="notifications" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-bell"></i>
                                <span class="count bg-primary">3</span>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="notifications">
                                <a class="dropdown-item dropdown-item-danger d-flex gap-2 align-items-center" href="#">
                                    <i class="fa fa-fw fa-check"></i> Servidor #1 funcionando.
                                </a>
                                <a class="dropdown-item dropdown-item-danger d-flex gap-2 align-items-center" href="#">
                                    <i class="fa fa-fw fa-info"></i> Servidor #2 apagado.
                                </a>
                                <a class="dropdown-item dropdown-item-danger d-flex gap-2 align-items-center" href="#">
                                    <i class="fa fa-fw fa-warning"></i> Servidor #3 sobrecalentado.
                                </a>
                            </div>
                        </div>
					
					</div>
					<div class="user-area">
						<div class="dropdown">
							<a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
								<?php if (file_exists("assets/img/user/" .$_SESSION["uid"]. ".png")) { echo('<img class="user-avatar rounded-circle" src="assets/img/user/' .$_SESSION["uid"]. '.png">');
								} else { echo('<img class="user-avatar rounded-circle" src="assets/img/user/default.png">'); }?>
							</a>
							<ul class="dropdown-menu p-2">
								<li><h6 class="dropdown-header"><b><?php echo ($_SESSION["uName"]); ?></b></h6></li>
								<li><hr class="dropdown-divider"></li>
								<li><a class="dropdown-item ajaxAction rounded-2 text-danger" href="?req=logout"><i class="fa fa-sign-out"></i>&nbsp;Cerrar Sesión</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			</div>
        </header>
        <!-- /#header -->