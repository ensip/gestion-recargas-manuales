<?php

function alertaDesactivada($empresa) {
	
	$sql = "select valor from runtime_control where clave = 'sms_cubacel_recargas_manual' and valor = 0";

	if ($empresa == 'Ensip') {
		$con = getConn();
		$res = $con->query($sql);
		//echo "ensip: " . $res->num_rows;
		return ($res->num_rows > 0) ? true : false;
	}
	if ($empresa == 'Jyctel') {
		$con = getConn(DB_prepagos);
		$res = $con->query($sql);
		//echo "jyc: " . $res->num_rows;
		return ($res->num_rows > 0) ? true : false;
	}
	return false;
}

function estadosExportar($id = '') {
	$estados = array(
		3 => 'Pendiente',
		1 => 'Hecha',
		2 => 'Error',
		4 => 'Otros'
	);

	return (!empty($id) && isset($estados[$id]) ? $estados[$id] : $estados);
}

/*
 *	para jyctel
 * */
function extraerDatosId($id) {
	$did = explode('-', $id);
	$datos['id_contrato'] = (isset($did[0]) ? $did[0] : 0);
	$datos['id_recarga'] = (isset($did[1]) ? $did[1] : 0);

	return $datos;
}

function getCantPendientes($empresa) {
	if ($empresa == 'ensip') {
		$con = getConn();

		$sql = "select id from ".PREFIX_TABLE."recargas_pendientes where numMobil not like '5300000000' and `check` = " . ESTADO_REC_BUSQUEDA_ENSIP;
		$res = $con->query($sql);
		$cant_preventas = $res->num_rows;
	
		$sql = "select id from ".PREFIX_TABLE."recargas_pendientes_no_preventa where numMobil not like '5300000000' and `check` = " . ESTADO_REC_BUSQUEDA_ENSIP;
		$res = $con->query($sql);

		$cant_pendientes = $res->num_rows;

		return $cant_preventas + $cant_pendientes;
	}
	if ($empresa == 'jyctel') {
		$con = getConn(DB_prepagos);
		$sql = "select recargas from ".PREFIX_TABLE_PREPAGOS."contratos where `check` = " . ESTADO_REC_BUSQUEDA_JYCTEL . " and preventa = 0";
		$res = $con->query($sql);
		$cant_recargas = 0;
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_object()) {
				if ($recargas = unserialize($row->recargas)) {
					foreach ($recargas as $recarga) {
						if (!isset($recarga['status'])) {
							$cant_recargas ++;
						}
					}
				}
			}
		}
		return $cant_recargas;
	}
	return 0;
}

function getInfoRecargaListado ($empresa, $datos) {
	if ($empresa == 'ensip'){
		$info = $datos->id;
	}
	if ($empresa == 'jyctel') {
		$info = sprintf("%s-%s", $datos->id, $datos->id_recarga);
	}
	return $info;
}
function getRecargayPromos($cols = 'recarga_doble') {

	$con = getConn();
	$sql = "select " . $cols . " from ".PREFIX_TABLE."recarga_y_promos ";
	$res = $con->query($sql);

	$row = null;
	if ($res->num_rows > 0) {
		$row = $res->fetch_object();
	}
	return $row;
}

function mli_put($con, $data) {
	return $con->real_escape_string($data);
}

function obtenerUsdByDenomination($denom_recarga) {

	$pn = new PreciosNuevos();
	$denominations = $pn->getArrayDenominations();

	foreach ($denominations as $usd_milliar => $denom) {

		if ($denom == $denom_recarga) {
			return $usd_milliar / 100;
		}
	}
	return 0;
}

function obtenerCupByUsd($cuc) {
	$cup = (new PreciosNuevos())->convertMontoToCUP($cuc * 100);
	return $cup;
}

function NotificacionSMS($empresa) {

	$sql = "update runtime_control set valor = 1 where clave = 'sms_cubacel_recargas_manual'";

	if ($empresa == 'ensip') {
		$con = getConn();
	}
	if ($empresa == 'jyctel') {
		$con = getConn(DB_prepagos);
	}
	return $con->query($sql);
}
