function powerControl(system, mode) {
	switch (mode) {
		case "pwrOn":
			jQuery.get("sysControl.php?system=" + system + "&action=pwrOn")
			break;
		case "Reboot":
			if (confirm("Está seguro de Reiniciar?")) jQuery.get("sysControl.php?system=" + system + "&action=Reboot")
			break;
		case "pwrOff":
			if (confirm("Está seguro de Apagar?")) jQuery.get("sysControl.php?system=" + system + "&action=pwrOff")
			break;
	}
}