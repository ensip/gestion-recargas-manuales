<?php

class ListadosJyctel {
	private $excluir_nauta = 1;
	private $prefix_table = PREFIX_TABLE_PREPAGOS;

	private function checkNumber($number) {
		
		if (strpos($number, 'nauta') !== false) {
			return true;
		} else {
			return false;
		}
	}

	private function esPosibleListarRecarga($data) {

		$celular = $data['celular'];

		if (!empty($celular)) {
			if ($this->excluir_nauta) {
				if ($this->checkNumber($celular)) {
					return false;
				}
			}
		} 

		if (isRechargeDone($data)) { //funciones.jyctel.php
			syslog(LOG_INFO, __FILE__ . ': isRechargeDone true');
			return false;
		}

		return true;
	}

	public function get() {
		syslog(LOG_INFO, __FILE__ . ':'.__CLASS__ );
		$contratos = $this->getContratosRecargas();

		$recargas = array();
		
		if (!empty($contratos)) {
			$recargas = $this->getRecargas($contratos);
		}
		return $recargas;
	}

	private function getWhere() {

		$where = "recargas not like 'a:0:%' and `preventa` = '0' and `check`= " . ESTADO_REC_BUSQUEDA_JYCTEL;
		//$where = "id=409615 ";

		return $where;
	}

	private function getContratosRecargas() {
		$con = getConn(DB_prepagos);

		$contratos = array();

		$sql = "SELECT id,recargas,fecha,num_pedido,`check` FROM ".$this->prefix_table."contratos WHERE " . $this->getWhere() . " order by fecha desc ".
			(grmTEST == 1 ? "limit 5" : '');
		$res = $con->query($sql);
		
		syslog(LOG_INFO, __FILE__ . ':'. __method__ . ':'.$sql.':'.$res->num_rows);
		
		if ($res->num_rows > 0) {
			while ($row = $res->fetch_object()) {
				$contratos[] = $row;
			}
		}
		return $contratos;
	}

	private function getRecargas($contratos) {
		
		$recargas = array();
		$i = 0;
		foreach ($contratos as $contrato) {
			
			if ($recargas_contrato = unserialize($contrato->recargas)) {
				
				foreach ($recargas_contrato as $key => $value) {

					$celular = getNumber($value);

					if (!$this->esPosibleListarRecarga(
						array(
							'id_contrato' => $contrato->id, 
							'celular' => $celular,
							'id_recarga_hecha' => $key
						))
					) {
						continue;
					}

					$stdClass = new StdClass();

					$stdClass->id = $contrato->id;

					if (!empty($celular)) {
						$stdClass->celular = $celular;
					}
					$stdClass->cantidad_recarga = $value['monto'];
					$stdClass->fecha = $contrato->fecha;
					
					if (isset($value['status'])) {
						$stdClass->estado_recarga = $value['status'];
					} else {
						$stdClass->estado_recarga = '';
					}

					$stdClass->estado_contrato = $contrato->check;
					$stdClass->token_contrato = $contrato->num_pedido;
					$stdClass->id_recarga = $key;

					$recargas[$i] = $stdClass;
					
					$i ++;
				}
			}
		}
		if (!empty($recargas)) {
			$recargas = (object)$recargas;
		}
		//syslog(LOG_INFO, __FILE__ . ':'. __method__ . ':'.$sql.':'.$res->num_rows);
			
		return $recargas;
	}
}
