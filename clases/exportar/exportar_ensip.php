<?php

class ExportarEnsip extends ExportarEmpresa {
	
	public $empresa = 'ensip';	

	/*
	 * 	estado : 1: hecha, 2:error, 3: pendiente, 4:otros
	 *	Filtros: fechas[inicial|final]
	 *
	 * */	
	protected function setFiltros($filtros) {
		$previo_and = 0;

		$hay_filtros_fechas = 0;
		if (isset($filtros['fechas'])) {
			$fechas = $filtros['fechas'];
			$hay_filtros_fechas = 1;
			
			$this->filtros_string .= ' and';
			if (isset($fechas['inicial'])) {
				$this->filtros_string .= sprintf(" fecha >= '%s'", $fechas['inicial'] . ' 00:00:01' );
			} else {
				$this->filtros_string .= sprintf(" fecha >= '%s'", date('Y-m-d H:i:s'));
			}
			if (isset($fechas['final'])) {
				$this->filtros_string .= sprintf(" and fecha <= '%s'", $fechas['final'] . ' 23:59:59' );
			} else {
				$this->filtros_string .= sprintf(" and fecha <= '%s'", date('Y-m-d H:i:s'));
			}
		}

		$filtro_estado = '';
		if (isset($filtros['estado'])) {

			$filtro_estado = ' and `check` ' . $this->formatCheck($filtros['estado']);
 			//$this->filtros_string .= ' and `check` ' . $this->formatCheck($filtros['estado']);
		}

		if ($filtros['estado'] == 1 || $filtros['estado'] == 2) {
			//$this->filtros_string .= " and token in (select token from ".PREFIX_TABLE."mobile_logs where plataforma like '%manual_cuba%' )";
			$this->filtros_string .= " and plataforma like '%manual_cuba%'";
			if (isset($filtros['estado'])) {
				$filtro_estado = ' and `ResultId` = ' . ($filtros['estado'] == 2 ? '0' : '1')  ;

			}

		}
		if (!empty($filtro_estado)) {
			$this->filtros_string .= $filtro_estado;
		}

		$this->filtros_string .= " order by fecha desc";	

		if (!$hay_filtros_fechas) {
			$this->filtros_string .= " limit 0";
		}
		$this->filtros = $filtros;

	}

	private function formatCheck($select_estado_recargas) {

		$check = "=" . (int)$select_estado_recargas;
		if ($select_estado_recargas == 4) {
			$check = "not in (1,2,3)";
		}
		return $check;
	}

	private function formatCup($eur, $fecha) {

		$cup = '';

		if ( ($fecha >= '2022-01-31 00:00:00' && $fecha <= '2022-02-06 10:00:00') || 
			//($fecha >= '2022-02-24 10:00:00' && $fecha <= '2022-03-07 00:00:00')	
			($fecha >= '2022-02-24 10:00:00')	
		) {
			if ($eur >= 9 && $eur<= 13) { //11
				$cup = 500;
			}
			if ($eur >= 18 && $eur<= 23) { //22
				$cup = 1000;
			}
			if ($eur >= 37 && $eur<= 43) { //44
				$cup = 2000;
			}
			if ($eur >= 57 && $eur<= 63) { //66
				$cup = 3000;
			}
		}
		if ($fecha >= '2022-03-21 06:00:00') {
			if ($eur >= 9 && $eur<= 13) { //11
				$cup = 500;
			}
			if ($eur >= 18 && $eur<= 23) { //22
				$cup = 1500;
			}
			if ($eur >= 37 && $eur<= 43) { //44
				$cup = 3000;
			}
			if ($eur >= 57 && $eur<= 63) { //66
				$cup = 6000;
			}
		}
		return $cup;
	}

	private function formatEstado($estado) {

		switch ($estado) {
			case 2:
				$formatted = 'Error';
				break;
			case 1:
				$formatted = 'Hecha';
				break;
			case 3:
				$formatted = 'Pendiente';
				break;
			default:
				$formatted = 'Otros (' . $estado . ')';
				break;
		}
		return $formatted;
	}

	protected function getData($filtros) {

		$con = getConn();
		$this->setFiltros($filtros);

		if ($this->filtros['estado'] == 1 || $this->filtros['estado'] == 2) {
			$sql = "select id, user_id as id_usuario, mobNumber as numMobil, to_send as cuc, amount as eur, ".$this->filtros['estado']." as estado, fecha, token ".
				",(select to_send from recargas_pendientes_no_preventa where token = ml.token and forzar_proveedor = 'noriel' and TRANSACTIONID = '' limit 1) as rec_rec ".
				"from ".
				PREFIX_TABLE."mobile_logs ml where " .$this->filtros_string;
		} else {
			$sql = "select id,id_usuario,numMobil,cuc,to_send,eur,`check` as estado,fecha,token from ".PREFIX_TABLE."recargas_pendientes_no_preventa where " . $this->filtros_string;
		}
		
		//syslog (LOG_INFO, __method__ . ':'.$sql);

		$res = $con->query($sql);
		
		if (isset($con->errno) and $con->errno > 0) {
			return $con->error;
		}
		$this->setNumRows($res->num_rows);
		
		if ($res->num_rows > 0) {
			$pn = new PreciosNuevos();

			while ($row = $res->fetch_assoc()) {
				//$this->csv .= serialize($row) . "\n";
				$recs_exportar = $row;
				unset($recs_exportar['rec_rec']);

				if (isset($row['estado'])) {
					$recs_exportar['estado'] = $this->formatEstado($row['estado']);

				}
				if (isset($row['eur'])) {
					$recs_exportar['eur'] = str_replace('.',',',$row['eur']);
				}
				if (isset($row['rec_rec']) && !empty($row['rec_rec'])) {
					$cup = $row['rec_rec'];
					$recs_exportar['cuc'] = $cup;
				} else {
					if (isset($row['cuc'])) {

						syslog (LOG_INFO, __method__ . ':no-hay-res_rec : ' .serialize($row));
						if (isset($row['to_send']) && (!empty($to_send) && $to_send > 0)) {
							$cup = $to_send;
						} else {
							$cup = $this->formatCup($row['eur'], $row['fecha']);
							if (empty($cup)) {
								$cup = $pn->convertMontoToCUP($row['cuc'] * 100);
							}					
						}

						$recs_exportar['cuc'] = $cup;
					}
				}

				foreach ($recs_exportar as $key => $val) {
					$this->csv .= "{$val};";
				}

				$this->csv .= $this->empresa . ";";
				$this->csv .= "\r\n";
			}
		}
	}

	protected function setNumRows($num_rows) {
		$this->cantidad = $num_rows;
	}
}
