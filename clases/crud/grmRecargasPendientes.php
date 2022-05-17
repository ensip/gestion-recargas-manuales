<?php

class grmRecargasPendientes {

	private $id_registro = 0;
	private $prefix_table = PREFIX_TABLE;
	private $table = 'recargas_pendientes';

	public function __construct($id_registro) {
		$this->id_registro = $id_registro;
	}

	public function esRecargaPendiente() {
		$con = getConn();

		$sql = "select * from " .$this->prefix_table.$this->table. " where id = " . (int)$this->id_registro . " and `check` = " . ESTADO_REC_BUSQUEDA_ENSIP;
		$res = $con->query($sql);
		syslog(LOG_INFO, __FILE__ . ':' .__method__ .':'.$sql);

		if ($res->num_rows > 0) {
			$row = $res->fetch_object();
			return $row;
		}
		return false;
	}

	public function updateByRegistro($estado) {
		$con = getConn();

		$sql = "update " .$this->prefix_table.$this->table. " set `check`=".$estado." where id = '".(int)$this->id_registro."' limit 1";
		syslog(LOG_INFO, __FILE__ . ':' .__method__ .':'.$sql);
		return $con->query($sql);
	}	
}	
