<?php

class ActualizarEnsipPreventa extends ActualizarEnsip {

	/*
	 *	info_recarga : array {id, estado}
	 * */
	public function __construct($info_recarga) {
		parent::__construct($info_recarga);
	}
	
	public function update() {

		if ($this->id_recarga == '-1' || !$this->id_recarga) {
			return array('error' => 'Id recarga no encontrado');
		}

		$res_update = 0;

		$rp = new grmRecargasPendientes($this->id_recarga);
		if ($datos_pendiente = $rp->esRecargaPendiente()) {	
			if ($rp->updateByRegistro($this->estado_recarga)) {
				$res_update = (new grmMobileLogs($datos_pendiente, $this->estado_recarga, PREVENTA_ENSIP))->insert_mobile_logs();
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
