<?php
include(__dir__ . '/../inc/conf.inc.php');
includeNotificarJyctel();
includeCrudJyctel();

if (isset($_POST['notificar_recargas'])) {
	$recargas = $_POST['notificar_recargas'];
	$empresa = $_POST['empresa'];
	if ($empresa == 'jyctel'){
		
		$contratos_recargas = [];
		foreach ($recargas as $recarga) {
			$id_contrato = $recarga['contrato'];
			$contratos_recargas[$id_contrato][] = array( 'celular' => $recarga['celular']);
		}
		$not = new Notificacion($contratos_recargas);
		$notificaciones = array(
			'sms',
			'email'
		);
		foreach ($notificaciones as $notificacion) {
			$not->notificar($notificacion); //sms,email
		}
	}
}
