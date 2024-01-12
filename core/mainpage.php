<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]>      <html class="no-js" lang=""> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>cw911</title>
    <meta name="description" content="cw911">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="favicon.ico">
    <link rel="shortcut icon" href="favicon.ico">
	
	<script>const loadStart = Date.now();// Start measuring load time</script> 
	<script src="assets/js/init/jquery.min.js"></script>
	<script src="assets/js/init/jquery.color-2.2.0.min.js"></script>
	<link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
	
	<?php require "SPA.php";?>

</head>

<body>
	<div id="preloader" style="background-color: #4d595f;">
		<div id="status">
			<div class="spinner"></div>
		</div>
		<img id="loadlogo" src="assets/img/logo-white.png">
		<div class="Loading"><span id="loadBar" style="width: 0%;"></span><span id="loadText">Cargando...</span></div>
		<img id="footerlogo" src="assets/img/VEN-APURE.png" style="position: absolute; left: 50%; bottom: 20px; width: 400px; margin: 0 0 0 -200px;">
	</div>
	<div id="notifications" style="position: fixed; min-height: 200px; top: 10px; right: 5px; z-index: 2000;"></div>
	<div id="c-body">
		<?php if(@$_SESSION["logged"]) {require "page.main.php";} else {require "page.login.php";} ?>
	</div>
</body>
</html>
