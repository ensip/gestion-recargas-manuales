<?php

class ExportarEnsip extends ExportarEmpresa {

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
			
			$this->filtros .= ' and';
			if (isset($fechas['inicial'])) {
				$this->filtros .= sprintf(" fecha >= '%s'", $fechas['inicial'] . ' 00:00:01' );
			} else {
				$this->filtros .= sprintf(" fecha >= '%s'", date('Y-m-d H:i:s'));
			}
			if (isset($fechas['final'])) {
				$this->filtros .= sprintf(" and fecha <= '%s'", $fechas['final'] . ' 23:59:59' );
			} else {
				$this->filtros .= sprintf(" and fecha <= '%s'", date('Y-m-d H:i:s'));
			}
		}

		if (isset($filtros['estado'])) {
			
			$this->filtros .= ' and `check` ' . $this->formatCheck($filtros['estado']);
		}

		if ($filtros['estado'] == 1 || $filtros['estado'] == 2) {
			$this->filtros .= " and token in (select token from ".PREFIX_TABLE."mobile_logs where plataforma like '%manual_cuba%' )";
		}
		
		$this->filtros .= " order by fecha desc";	

		if (!$hay_filtros_fechas) {
			$this->filtros .= " limit 0";
		}
	}

	private function formatCheck($select_estado_recargas) {

		$check = "=" . (int)$select_estado_recargas;
		if ($select_estado_recargas == 4) {
			$check = "not in (1,2,3)";
		}
		return $check;
	}

	protected function getData() {

		$con = getConn();

		$this->empresa = 'ensip';

		$sql = "select id,id_usuario,numMobil,cuc,eur,`check` as estado,fecha,token from ".PREFIX_TABLE."recargas_pendientes_no_preventa where " . $this->filtros;
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
				if (isset($row['estado'])) {
					switch ($row['estado']) {
					case 2:
						$recs_exportar['estado'] = 'Error';
						break;
					case 1:
						$recs_exportar['estado'] = 'Hecha';
						break;
					case 3:
						$recs_exportar['estado'] = 'Pendiente';
						break;
					default:
						$recs_exportar['estado'] = 'Otros (' . $recs_exportar['estado'] . ')';
						break;
					}
				}
				if (isset($row['eur'])) {
					$recs_exportar['eur'] = str_replace('.',',',$row['eur']);
				}
				if (isset($row['cuc'])) {
					$recs_exportar['cuc'] = $pn->convertMontoToCUP($row['cuc'] * 100);
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
