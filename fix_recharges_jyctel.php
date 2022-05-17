<?php
session_start();
include_once('../../inc/funciones.inc.php');
include_once('inc/conf.inc.php');

includeCrudJyctel();
includeFuncionesGenerales();
includeFuncionesJyctel();
includeFactoryListados();

function getContratosPendientes() {

	$list_jyc = new ListadosJyctel();

	$contratos_recargas = $list_jyc->getContratosRecargas();

	return $contratos_recargas;
}

function getCantMobileLogsInsertadas($id_contrato) {

	$data['id_contrato'] = $id_contrato;
	$data['id_recarga_hecha'] = null;
	$data['celular'] = '';

	$fo = new grmFactonline($data);
	$cant_mobile_logs = $fo->getMobileLogsInsertadas($data);

	return count($cant_mobile_logs);
}

function insertMobileLogs($id_contrato, $recargas) {
	foreach ($recargas as $id_recarga => $recarga) {
		
		//print_r($recarga);
		if (isset($recarga['num']) && !isset($recarga['status'])) {
			
			print_r($recarga);
			$data = array(
				'id_contrato' => $id_contrato,
				'celular' => $recarga['num'],
				'id_recarga_hecha' => $id_recarga
			);
			print_r($data);
		}
	}
}

$contratos = getContratosPendientes();

if (!empty($contratos)) {
	foreach ($contratos as $contrato) {
		if ($recargas = unserialize($contrato->recargas)) {
			$cantidad_recargas = count($recargas);
			$id_contrato = $contrato->id;

			$cant_insertadas = getCantMobileLogsInsertadas($id_contrato);

			//echo $id_contrato . ':'.$cantidad_recargas . " insertadas: " . $cant_insertadas . "\n";

			if ($cant_insertadas > 0 && $cantidad_recargas != $cant_insertadas) {
				echo "faltan : ".$id_contrato . "\n";
				//insertMobileLogs($id_contrato, $recargas);
			}
		}
	}
}

