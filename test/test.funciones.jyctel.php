<?php
session_start();
include_once('/data/www/ensip/admin/inc/funciones.inc.php');
include_once('inc/conf.inc.php');
include_once('funciones.jyctel.php');
include('clases/grmFactoryActualizar.php');

$id_contrato = 410119;//410119
$id_recarga = 1;
/*
$recarga = getRecargaContrato($id_contrato, $id_recarga);
if (!isset($recarga['ConfirmId'])) {
	$recarga['ConfirmId'] = time();
}
//print_r($recarga);
$celular = getNumber($recarga);

$data = array(
	'id_contrato' => $id_contrato,
	'celular' => $celular,
	'id_recarga_hecha' => $id_recarga
);
//print_r($data);
$pre = new grmPrepagos($data);
//$res_update_contrato = $pre->update_contrato(1, $recarga, time());
$estado = 1;
$fo = new grmFactonline($data, $estado);
//insert recargas_contratos_hechas , id_contrato, id_recarga
//$fo->insert_recargas_contratos_hechas();
$res = $fo->insert_mobile_logs($recarga);
var_dump($res);

 */
