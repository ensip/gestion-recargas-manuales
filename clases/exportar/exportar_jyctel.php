<?php

include(__dir__ . '/../listados/listados_jyctel.php');
include(__dir__ . '/../grmFormatValues.php');
include(__dir__ . '/../../inc/funciones.jyctel.php');


class ExportarJyctel extends ExportarEmpresa {

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
		
		$this->empresa = 'jyctel';
		
		$listado = new ListadosJyctel();
		$recargas = $listado->get();

		$new_recargas = array();
		if (!empty($recargas)) {
			
			$this->setNumRows(count($recargas));

			foreach ($recargas as $idx => $recarga) {
				foreach ($recarga as $key => $value) {
					$format_value = grmFormatValues::format($key, $value, 'jyctel');
					$new_recargas[$idx][$key] = $format_value; 
				}
				$new_recargas[$idx]['precio'] = obtener_precio_recarga($recarga->id); 
			}
		}
		if (!empty($new_recargas)) {
			$this->csv = $this->prepareDataToExport($new_recargas);
		}
	}
	private function prepareDataToExport($recs) {

		$string_campos = array(
			'id', 'id_recarga', 'celular', 'cantidad_recarga', 'precio', 'estado_recarga', 'estado_contrato', 'fecha', 'token_contrato'
		);	

		$campos = array();

		foreach ($string_campos as $key => $campo) {
			$campos[$key] = array('key' => $campo);
		}

		$csv = "id;id-recarga;numMobil;cup;precio;estado recarga;estado contrato;fecha;token;empresa\n";

		foreach ($recs as $rec) {
			foreach ($campos as $campo) {
				$campo_recarga = $campo['key'];
				if (isset($rec[$campo_recarga])) {
					$csv .= $rec[$campo_recarga] .";";
				}
			}
			$csv .= $this->empresa . ";";
			$csv .= "\n";
		}
		return $csv;
	}
	protected function setNumRows($num_rows) {
		$this->cantidad = $num_rows;
	}

}
