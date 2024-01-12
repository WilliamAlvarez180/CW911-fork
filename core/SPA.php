<?php
	function getRes($dir, $type) {
		$files = array_diff(scandir($dir), array('..', '.'));
		foreach ($files as $key => $value) {
			$path = $dir . "/" . $value;
			if (!is_dir($path) && (pathinfo($path, PATHINFO_EXTENSION))) {
				echo "\"" . $path . "\"" . ",";
			}
		}
	}
?>

<style><?php include("SPA.css"); ?></style>
<script type="text/javascript" id="spaJS"><?php include("SPA.js"); ?></script>