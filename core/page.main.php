<?php require "page.main.menu.php"; ?>
<?php require "page.main.header.php"; ?>
    <!-- Right Panel -->
        <!-- Content -->
		<main class="col ps-md-2 pt-2" style="height: calc(100vh - 47px);">
		<div class="breadcrumbs">
			<div class="row m-0">
				<div class="col-sm-12">
					<div class="page-header border-primary border-bottom border-top border-1 rounded-3 hstack gap-3">
						<h1 class="text-secondary"><b><div id="page-title"><i class="fa-fade menu-icon fas fa-home " style="--fa-animation-duration: 2s;"></i>&nbsp;Inicio</div></b></h1>
						<div class="vr ms-auto my-1"></div>
						<div class="btn-group">
							<button class="btn btn-sm btn-outline-primary"><i class="fa fa-gear"></i></button>
							<button class="btn btn-sm btn-outline-primary"><i class="fa fa-circle-question"></i></button>
						</div>
					</div>
				</div>								
			</div>
		</div>
		<div id="content-sticky-top" class="sticky-top"></div>
        <div class="content" id="content">
			<script>SPA.startSSE();SPA.ajaxAction("?req=module&module=homepage");</script>
        </div>
		<div class="clearfix"></div>
		<footer class="d-flex flex-wrap justify-content-between align-items-center py-2 mx-2 border-top">
			<div class="col-md-4 d-flex align-items-center">
				<span class="mb-3 me-1 mb-md-0 text-secondary text-decoration-none lh-1"><i class="fa fa-code"></i></span>
				<span class="mb-3 mb-md-0 text-body-secondary">Diseñado para CCCT VEN911</span>
			</div>

			<ul class="nav col-md-4 justify-content-end list-unstyled d-flex">
				<li class="ms-2"><a class="text-body-secondary" href="mailto:enderjimenez2500@gmail.com"><i class="fa-brands fa-google"></i></a></li>
				<li class="ms-2"><a class="text-body-secondary" href="https://facebook.com/TheCracker25"><i class="fa-brands fa-facebook"></i></a></li>
				<li class="ms-2"><a class="text-body-secondary" href="https://twitter.com/Ender_J25"><i class="fa-brands fa-twitter"></i></a></li>
				<li class="ms-2"><a class="text-body-secondary" href="https://github.com/EnderJ25"><i class="fa-brands fa-github"></i></a></li>
				<li class="ms-1"><span class="text-secondary">© 2023 Coordinación Tecnología Apure</span></li>
			</ul>
		</footer>
		<div id="content-sticky-bottom" class="sticky-bottom"></div>
		</main>
	</div>
</div>
	
	<script><?php require "body.main.js"; ?></script>