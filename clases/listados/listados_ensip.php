<?php

class ListadosEnsip {
	private $excluir_nauta = 1;

	public function get() {
		syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ );

		if ($this->checkIfSonManuales()) { //ahora mismo check if recarga doble or not

			return $this->getRecargas();
		} else {
			return array();
		}
	}
	private function checkIfSonManuales() {
		$con = getConn();
		$sql = "select recarga_doble from recarga_y_promos and recarga_doble = 1";
		$res = $con->query($sql);
		if ($res->num_rows > 0) {
			return false;
	       	} else {
			return true;
		}
	}
	private function getRecargas() {
		$con = getConn();

		$sql = "select id,numMobil as celular,cuc as cantidad_recarga,fecha,`check` as estado_recarga,token from ".PREFIX_TABLE."recargas_pendientes_no_preventa where " . $this->getWhere() . " order by fecha desc";
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
