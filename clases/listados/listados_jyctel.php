<?php

class ListadosJyctel {
	private $cantidad = 0;
	private $excluir_nauta = 1;
	private $exportar = 0;
	private $filtros = '';
	private $prefix_table = PREFIX_TABLE_PREPAGOS;

	public function __construct($exportar = 0) {

		$this->exportar = $exportar;
	}

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
	
	private function esRecargaProveedorManual($id_contrato) {
		
		$con = getConn(DB_factura);
		$sql = "select * from mobile_logs where pago = 'IDC_" . $id_contrato . "' and plataforma = 'manual_cuba'";
		$res = $con->query($sql);

		if ($res->num_rows > 0) {
			return true;
		}
		return false;
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
	public function getCantidad() {
		return $this->cantidad;
	}
	private function getWhere() {
		
		$where = "recargas not like 'a:0:%' and `preventa` = '0' ";
		if (empty($this->filtros)) {
			$where .= "and `check`= " . ESTADO_REC_BUSQUEDA_JYCTEL;
		} else {
			$where .= $this->filtros;
		}
		//$where = "id=409615 ";
		syslog(LOG_INFO, __FILE__ . ':'. __method__ . ':FILTROS->'.$where);
		return $where;
	}

	private function getContratosRecargas() {

		$con = getConn(DB_prepagos);

		$contratos = array();

		$sql = "SELECT id,recargas,fecha,num_pedido,`check` FROM ".$this->prefix_table."contratos WHERE " . $this->getWhere() . " order by fecha desc " . (grmTEST == 1 ? "limit 5" : '');
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
			
			if ($this->exportar) {
				if (!$this->esRecargaProveedorManual($contrato->id)) {
					continue;
				}
			}
	
			if ($recargas_contrato = unserialize($contrato->recargas)) {
				
				foreach ($recargas_contrato as $key => $value) {

					$celular = getNumber($value);

					if (!$this->exportar && !$this->esPosibleListarRecarga(
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
			$this->setCantidad($i);
		}
		return $recargas;
	}
	
	private function setCantidad ($cantidad) {
		$this->cantidad = $cantidad;
	}

	public function setFiltros($filtros) {
		$this->filtros = $filtros;
	}
}
