<?php

class ActualizarJyctel {

	private $datos_id = array();
	private $estado_recarga = 0;
	private $id_contrato = -1;
	private $id_recarga = -1;
	private $info_recarga = array();

	/*
	 *	info_recarga : array {id, estado}
	 * */
	public function __construct($info_recarga) {
		$this->info_recarga = $info_recarga;
		$this->sacarDatos();
		$this->setIdRecarga();
		$this->setIdContrato();
	}
	private function sacarDatos() {
		$this->setNewStatus();
		$this->datos_id = extraerDatosId($this->info_recarga['id']); //id_contrato_id_recarga
	}
	private function setIdContrato() {
		if (isset($this->datos_id['id_contrato'])) {
			$this->id_contrato = $this->datos_id['id_contrato'];
		}
	}
	private function setIdRecarga() {
		if (isset($this->datos_id['id_recarga'])) {
			$this->id_recarga = $this->datos_id['id_recarga'];
		}
	}
	private function setNewStatus() {
		if (isset($this->info_recarga['estado'])) {
			$this->estado_recarga = $this->info_recarga['estado'];
		}
	}
	public function update() {
		
		if ($this->id_recarga == '-1') {
			return array('error' => 'Id recarga no encontrado');
		}
		if ($this->id_contrato == '-1') {
			return array('error' => 'Id contrato no encontrado');
		}
		
		//inc/funciones.jyctel return array datos recarga from contratos
		$recarga = getRecargaContrato($this->id_contrato, $this->id_recarga); 

		if (empty($recarga)) {
			return array('error' => 'Datos contrato no validos');
		}
		$celular = getNumber($recarga); //inc/funciones.jyctel

		$confirmId = time();

		$data = array(
			'id_contrato' => $this->id_contrato,
			'celular' => $celular,
			'id_recarga_hecha' => $this->id_recarga
		);
		
		$res_update = 0;

		if (!isRechargeDone($data)) { //funciones.jyctel.php
			$recargas = array();
			$pre = new grmPrepagos($data);
			$fo = new grmFactonline($data);
			
			$cant_insertadas = $fo->getRecargasInsertadas();

			$res_update_contrato = $pre->update_contrato($this->estado_recarga, $recarga, $confirmId, $cant_insertadas); //OK PROBADO	 
				
			if ($res_update_contrato) {

				$fo->set_estado_recarga($this->estado_recarga);	
				$fo->insert_recargas_contratos_hechas();

				//insert mobile_logs con todos los campos
				if (!isset($recarga['ConfirmId'])) {
					$recarga['ConfirmId'] = $confirmId;
				}
				$res_update = $fo->insert_mobile_logs($recarga);

			} else {
				return array('error' => 'Registro recarga guardada no actualizado');
			}
			
		}

		$log = "actualizar recarga id : " .$this->id_recarga . " [".$celular."] con estado:".$this->estado_recarga . " res_insertar : ".($res_update ? 'ok' : 'ko')."";
		syslog(LOG_INFO, __FILE__ . ':' . __method__ . ':' . $log);
		
		if ($res_update == 'ok') {
			notificacionSMS('jyctel');
		}

		return $res_update;
	}
}
