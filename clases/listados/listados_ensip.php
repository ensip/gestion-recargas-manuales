<?php

class ListadosEnsip {
	private $excluir_nauta = 1;

	public function get() {
		syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ );
		return $this->getRecargas();
	}

	private function getRecargas() {
		$con = getConn();

		$sql = "select id,numMobil as celular,cuc as cantidad_recarga,fecha,`check` as estado_recarga,token from ".PREFIX_TABLE."recargas_pendientes_no_preventa where " . $this->getWhere() . " order by fecha desc";
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
