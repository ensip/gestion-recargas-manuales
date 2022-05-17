<?php

class ListadosEnsip {
	private $excluir_nauta = 1;

	public function get() {

		syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ . ':start-getting-recharges' );

		$recargas = $this->getRecargas();
		syslog(LOG_INFO, __FILE__ . ':' . __METHOD__ . ':'.serialize($recargas));
		$rowryp = getRecargayPromos();
		$ver_preventa = 0;
		if (!is_null($rowryp)) {
			if ($rowryp->recarga_doble) {
				$ver_preventa = 1;
			}
		}
		
		if ($ver_preventa) {
			$recargas = $this->getPreventas($recargas);
			syslog(LOG_INFO, __FILE__ . ':' . __METHOD__ . ':'.serialize($recargas));
			//array_push($recargas,$preventas);
		}
		//print_r($recargas);	
		return $recargas;
	}
	private function getPreventas($recargas) {

		$con = getConn();

		$sql = "select id,numMobil as celular, to_send as cantidad_recarga,fecha,`check` as estado_recarga, 'Preventa' as tipo_recarga,token ".
			"from ".PREFIX_TABLE."recargas_pendientes ".
			"where " . $this->getWhere() . " and forzar_proveedor = 'noriel' and numMobil not like '5300000000' order by fecha desc";

		$res = $con->query($sql);
		syslog(LOG_INFO, __FILE__ . ':' . __METHOD__ . ':'.$sql . ':cant'.$res->num_rows);
		
		$cant = count($recargas) + 1;
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_object()) {
				$recargas[$cant] = $row;
				$cant ++;
			}
		}
		return $recargas;
	}

	private function getRecargas() {
		$con = getConn();
		
		$sql = "select id,numMobil as celular, to_send as cantidad_recarga,fecha,`check` as estado_recarga, 'Normal' as tipo_recarga,token ".
			"from ".PREFIX_TABLE."recargas_pendientes_no_preventa ".
			"where " . $this->getWhere() . " and forzar_proveedor = 'noriel' and numMobil not like '5300000000' order by fecha desc";

		syslog(LOG_INFO, __FILE__ . ':' . __METHOD__ . ':'.$sql);
		$res = $con->query($sql);
		
		$recargas = array();
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_object()) {
				$recargas[] = $row;
			}
		}
		return $recargas;
	}	

	private function getWhere() {

		$where = "";
		if ($this->excluir_nauta) {
			$where = "numMobil not like '%nauta%' ";
		}

		$where .= "and `check` = " . ESTADO_REC_BUSQUEDA_ENSIP;

		return $where;
	}
}
