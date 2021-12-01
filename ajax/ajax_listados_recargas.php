<?php
include(__dir__ . '/../inc/conf.inc.php');

includeFuncionesGenerales();
includeFuncionesJyctel();
includeFactoryListados();

if (isset($_REQUEST['listado_recargas'])) {
	$listados = new FactoryListadosRecargas($_REQUEST['empresa']);
	$recargas = $listados->get(); //array of objects
	if (!$recargas || empty($recargas)) {
		$error = json_encode(array('error' => 'no-recargas'));
		echo $error;
	} else {
		$tabla_rec = new tablaRecargas($_REQUEST['empresa'], $recargas);
		$tabla = $tabla_rec->format();
		echo json_encode($tabla);

	}
}
