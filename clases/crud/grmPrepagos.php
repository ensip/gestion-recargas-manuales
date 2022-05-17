<?php

class grmPrepagos {

	private $con = null;
	private $estado_contrato = 9;
	private $id_contrato = 0;
	private $id_recarga = -1;
	private $prefix_table = PREFIX_TABLE_PREPAGOS;

	public function __construct($data) {

		$this->con = getConn(DB_prepagos);
		$this->id_contrato = $data['id_contrato'];
		$this->id_recarga = $data['id_recarga_hecha'];
	}


	/*
	 *	@return success:true, unsuccess:false
	 * */
	public function update_contrato($estado, $confirmId, $cant_insertadas, $recargas_contrato) {

		$new_recargas = array();

		foreach ($recargas_contrato as $key => $recarga) {

			$num = '';
			if (isset($recarga['num'])) {
				$num = $recarga['num'];
			}
			
			$new_recargas[$key] = $recarga;

			if ($key == $this->id_recarga && $estado != ESTADO_CONTRATO_NO_HACER) {
				unset($new_recargas[$key]['num']);
				$new_recargas[$key]['ConfirmId'] = $confirmId;
				$new_recargas[$key]['status'] = ($estado == 1 ? 'Hecha' : 'Error');
				$new_recargas[$key]['PhoneNumber'] = getNumber($recarga);
			}
		}

		//return serialize($new_recargas);	
		if (!empty($new_recargas) && is_array($new_recargas)){
			$serialized_recargas = serialize($new_recargas);
			$total_recargas_hechas = $cant_insertadas;
			$recargas_contrato = count($new_recargas);

			$estado_contrato = $estado;
			if ($estado != ESTADO_CONTRATO_NO_HACER) {
				$estado_contrato = ESTADO_PENDIENTE_MANUAL; //9

				if ($total_recargas_hechas >= $recargas_contrato) {
					$estado_contrato = ESTADO_CONTRATO_HECHO; //1
				}
			}


					
			$sql = "update ".$this->prefix_table."contratos set recargas = '".$serialized_recargas."', `check` = ".$estado_contrato." where id = ".$this->id_contrato." limit 1";
			$res = $this->con->query($sql);

			syslog(LOG_INFO, __FILE__ . ':' .__method__ .':rec: '.$this->id_recarga.' - '.$sql . ':'.$res . ':'.(!$res ? $this->con->error : 'sin-error') . " - $recargas_contrato == $total_recargas_hechas");

			sleep(1);

			return true;
			
		}

		return false;
	}
}
