<?php

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
		}
		else
			return $row;
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
function isRechargeDone($data) {
	

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
	
	syslog (LOG_INFO, __FILE__ . ':'.__method__ .':'.json_encode($data) . ':'.$id_contrato);

	$hay_mobile_logs = $res->num_rows;

	if ($hay_mobile_logs == 1) {
		syslog (LOG_INFO, __FILE__ . ':'.__method__ .':'.$id_contrato . '; hay-mobile_logs');
		return true;
	} else {
		//check recargas_contratos_hechas
		$sql = sprintf("select id from ".PREFIX_TABLE_JYCTEL."recargas_contratos_hechas where id_c = %s and id_r = '%s' ", (int)$id_contrato, (int)$id_recarga_hecha);
		$res = $con->query($sql);
		
		$hay_recarga_hecha = $res->num_rows;
		
		if ($hay_recarga_hecha == 1) {
			syslog (LOG_INFO, __FILE__ . ':'.__method__ .':'.$id_contrato . '; hay-recargas_contratos_hechas');
			return true;
		} else {
		
			//check recargas guardadas
			$sql = sprintf("select id from ".PREFIX_TABLE_JYCTEL."recargas_pendientes_no_preventas where id_contrato = %s and id_recarga = %s and numero = '%s' and `check`=1",
				(int)$id_contrato, (int)$id_recarga_hecha, $celular);

			$res = $con->query($sql);
			
			$hay_recarga_hecha = $res->num_rows;
			
			if ($hay_recarga_hecha == 1) {
				syslog (LOG_INFO, __FILE__ . ':'.__method__ .':'.$id_contrato . '; hay-recargas_pendientes_no_preventas');
				return true;
			}
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


		
