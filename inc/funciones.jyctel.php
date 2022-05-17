<?php
define('PROVIDER_SMS_JYCTEL', 3);
/*
 *	return array | empty array
 * */
function getContrato($id_contrato, $cols = 'recargas') {
	$con = getConn(DB_prepagos);

	$sql = sprintf("select %s from %scontratos where id = %s", $cols, PREFIX_TABLE_PREPAGOS, (int)$id_contrato);
	$res = $con->query($sql);

	if ($res->num_rows > 0) {
		$row = $res->fetch_object();
		
		if ($cols == 'recargas') {
			return $row->recargas;	

		} else if ($cols == 'Cellular') {
			return $row->Cellular;

		} else {
			return $row;
		}
	}
	return array();
}

function getMobileLogsInsertadas($id_contrato) {
	
	$con = getConn(DB_factura);
	$sql = "select * from " . PREFIX_TABLE_JYCTEL . "mobile_logs where pago like '%".$id_contrato."%'" ;
	$res = $con->query($sql);

	if ($res->num_rows > 0) {

		while ($row = $res->fetch_object()) {
			$recargas[] = $row;
		}

		return $recargas;
	}
	return array();
}

function getNumber($value) {

	if (isset($value['num'])) {
		return '53' . $value['num'];
	}
	if (isset($value['PhoneNumber'])) {
		return $value['PhoneNumber'];
	}
	return '';
}
/*
 *	return array | empty object
 * */
function getOferta($id, $cols = '*') {
	$con = getConn(DB_prepagos);

	$sql = sprintf("select %s from %scontratos_ofertas where id = %s", $cols, PREFIX_TABLE_PREPAGOS, (int)$id);
	$res = $con->query($sql);

	if ($res->num_rows > 0) {
		$row = $res->fetch_object();
		
		return $row;
		
	}

	return array();
}


function getProveedorSmsGrm() {
	$con = getConn(DB_factura);
	$sql = "select id from ".PREFIX_TABLE_JYCTEL."proveedor_sms where `STATUS` = 1 and A_CUBA = 0";
	$res = $con->query($sql);
	
	if (!isset($con->error)) {
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_object()) {
				return $row->id;
			}
		}
	}
	return PROVIDER_SMS_JYCTEL;

}

/*
 *	return serialized array | string ''
 * */
function getRecargaContrato($id_contrato, $id_recarga) {

	$rc = getContrato($id_contrato);
	
	if ($recargas = unserialize($rc)) {
		foreach ($recargas as $key => $recarga) {
			if ($key == $id_recarga) {
				return $recarga;
			}
		}
	}
	return '';
}
/*
 * *      primero miro en mobile_logs que esté hecha.
 * *      Si hay más de un resultado (recargas al mismo numero en un mismo contrato)
 * *      reviso en recargas_contratos_hechas para ver la recarga en cuestión
 * *
 * * */
function isRechargeDone($data, $cant_insertadas, $recargas_contrato) {

	$con = getConn(DB_factura);
	
	$celular = $data['celular'];
	$id_contrato = $data['id_contrato'];
	$id_recarga_hecha = $data['id_recarga_hecha'];

	$sql = "select id from ".PREFIX_TABLE_JYCTEL."mobile_logs where ".
		"mobNumber = '" . $celular . "' ".
		"and operation = 'return_payment' ".
		"and pago = 'IDC_" . $id_contrato . "' ".
		"and ResultId = 1 ".
		"and ResultStr != '' ".
		"and ConfirmId != ''";
	$res = $con->query($sql);
	
	$num_mobile_logs = $res->num_rows;
	
	syslog (LOG_INFO, __FILE__ . ':'.__method__ .':'.json_encode($data) . ':'.$id_contrato . ':cant recargas: ' . $num_mobile_logs);

	$sql = null;
	$res = null;
	if ($num_mobile_logs) {
		$sql = sprintf("select id from ".PREFIX_TABLE_JYCTEL."recargas_contratos_hechas where id_c = %s ", (int)$id_contrato);
		$res = $con->query($sql);

		if ($res->num_rows && $res->num_rows == count($recargas_contrato)) {
			syslog (LOG_INFO, __FILE__ . ':'.__method__ .':'.$id_contrato . '; num-cant_insertadas es '. count($recargas_contrato));
			return true;
		}
		// Si hay pero no es de las que tenemos, se salta el else.
	} else {
		// No tenemos recargas hechas, por lo que hay que hacerlas.
		return false;
	}

	//check recargas_contratos_hechas para saber si nos toca hacer esta.
	$sql = sprintf("select id from ".PREFIX_TABLE_JYCTEL."recargas_contratos_hechas where id_c = %s and id_r = '%s' ", (int)$id_contrato, (int)$id_recarga_hecha);
	$res = $con->query($sql);
	
	$hay_recarga_hecha = $res->num_rows;
	
	if ($hay_recarga_hecha == 1) {
		syslog (LOG_INFO, __FILE__ . ':'.__method__ .':'.$id_contrato . '; hay-recargas_contratos_hechas');
		return true;
	} else  {
	
		//check recargas guardadas - sólo no si se pudieran repetir, que en caso de Noriel no se guardan
		$sql = sprintf("select id from ".PREFIX_TABLE_JYCTEL."recargas_pendientes_no_preventas where id_contrato = %s and id_recarga = %s and numero = '%s' and `check`=1",
			(int)$id_contrato, (int)$id_recarga_hecha, $celular);

		$res = $con->query($sql);
		
		$hay_recarga_hecha = $res->num_rows;
		
		if ($hay_recarga_hecha == 1) {
			syslog (LOG_INFO, __FILE__ . ':'.__method__ .':'.$id_contrato . '; hay-recargas_pendientes_no_preventas');
			return true;
		}
	}

	return false;
}

function obtener_precio_recarga($id_contrato) {

	$info_contrato = getContrato($id_contrato, 'recargas, importe_tpv, importe_cuenta');
	$cant_recargas = count(unserialize($info_contrato->recargas));

	$importe = $info_contrato->importe_tpv + $info_contrato->importe_cuenta;

	return $precio = $importe / $cant_recargas;
}
