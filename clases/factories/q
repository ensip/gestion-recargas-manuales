<?php
include(__dir__ . '/../inc/conf.inc.php');
includeFactoryActualizar();

/*
 * Actualizar recargas como hechas en su empresa correspondiente.
 *
 * si ensip: ya está hecho en el panel, reutilizar código
 * si jyctel: 
 * 	actualizar contrato, 
 * 	actualizar campo recargas de contrato
 * 	insertar registro en recargas_contratos_hechas
 * 	insertar registro en mobile_logs
 *
 * */

// {
//	"actualizar_recarga_manual":"1",
//	"empresa":"ensip",
//	"recarga":
//		"tr_id":"4",
//		"estado":"1",
//		"id":"30049"
//  }
//			
if (isset($_POST['actualizar_recarga_manual'])) {
	
	$res =  FactoryActualizar::actualizar($_POST);

	$result = 1;
	$text = '';
	if (isset($res['error'])) {
		$result = 0;
		$text = $res['error'];
	}		
	print json_encode(array('result' => $result, 'text' => $text));
	
}
