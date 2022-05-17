<?php

class ActualizarEnsip {

	protected $estado_recarga = 0;
	protected $id_recarga = 0;
	protected $info_recarga = array();

	/*
	 *	info_recarga : array {id, estado}
	 * */
	public function __construct($info_recarga) {
		$this->info_recarga = $info_recarga;
		$this->sacarDatos();
	}
	protected function sacarDatos() {
		$this->setNewStatus();
		$this->setIdRecarga();
	}
	protected function setIdRecarga() {
		if (isset($this->info_recarga['id'])) {
			$this->id_recarga = $this->info_recarga['id'];
		}
	}
	protected function setNewStatus() {
		if (isset($this->info_recarga['estado'])) {
			$this->estado_recarga = $this->info_recarga['estado'];
		}
	}
	/*
	 *	return true : array (error:errorstr)
	 * */
	public function update() {
		
		if ($this->id_recarga == '-1') {
			return array('error' => 'Id recarga no encontrado');
		}

		$res_update = 0;
		
		$rpnv = new grmRecargasPendientesNoPreventa($this->id_recarga);
		if ($datos_pendiente = $rpnv->esRecargaPendiente()) {	
			if ($rpnv->updateByRegistro($this->estado_recarga)) {
				$res_update = (new grmMobileLogs($datos_pendiente, $this->estado_recarga, PROGRAMADA_ENSIP))->insert_mobile_logs();
			} else {
				return array('error' => 'Registro recarga guardada no actualizado');
			}
		}

		$log = "actualizar recarga id : " .$this->id_recarga . " con estado:".$this->estado_recarga . " res_insertar : ".($res_update ? 'ok' : 'ko')."";
		syslog(LOG_INFO, __FILE__ . ':' . __method__ . ':' . $log);

		if ($res_update == 'ok') {
			NotificacionSMS('ensip');
		}

		return $res_update;
	}
}
