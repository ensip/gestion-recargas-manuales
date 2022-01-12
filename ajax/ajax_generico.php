<?php

include_once(__dir__ . '/../inc/conf.inc.php');
includeFuncionesGenerales();

if (isset($_POST['alerta_empresa'])) {
	
	if (notificacionSMS($_POST['alerta_empresa'])) {
		$res = array('res' => $_POST['alerta_empresa']);
	} else {
		$res = array('error' => true);
	}

	print json_encode($res);
}

