<?php

include(__dir__ . '/../listados/listados_jyctel.php');
include(__dir__ . '/../grmFormatValues.php');
include(__dir__ . '/../../inc/funciones.jyctel.php');


class ExportarJyctel extends ExportarEmpresa {

	public $empresa = 'jyctel';	

	/*
	 *	los filtros son sobre los contratos y recargas hechas con el proveedor manual
	 * */
	protected function setFiltros($filtros) {
		$previo_and = 0;

		if (isset($filtros['id_contrato'])) {
			
			$this->filtros_string = ' `id` = ' . $filtros['id_contrato'];
		} else {

			$hay_filtros_fechas = 0;
			if (isset($filtros['fechas'])) {
				$fechas = $filtros['fechas'];
				$hay_filtros_fechas = 1;
				
				$this->filtros_string = ' and';
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


			if (isset($filtros['estado'])) {
				
				$this->filtros_string .= ' and `check` ' . $this->formatCheck($filtros['estado']);
			}

			if (!$hay_filtros_fechas) {
				$this->filtros_string .= " limit 0";
			}
		}

		$this->filtros = $filtros;
	}

	/*
	 *	los filtros son sobre las recargas hechas con el proveedor manual
	 * */
	protected function setFiltrosRecargas($filtros) {

		$previo_and = 0;
		$hay_filtros_fechas = 0;
		
		$fecha_inicial = sprintf(" and created >= UNIX_TIMESTAMP('%s')", date('Y-m-d H:i:s'));
		$fecha_final = sprintf(" and created <= UNIX_TIMESTAMP('%s')", date('Y-m-d H:i:s'));

		if (isset($filtros['fechas'])) {
			$fechas = $filtros['fechas'];
			$hay_filtros_fechas = 1;
			
			if (isset($fechas['inicial'])) {
				$fecha_inicial = sprintf(" and created >= UNIX_TIMESTAMP('%s')", $fechas['inicial'] . ' 00:00:01' );
			} 	
			if (isset($fechas['final'])) {
				$fecha_final = sprintf(" and created <= UNIX_TIMESTAMP('%s')", $fechas['final'] . ' 23:59:59' );
			} 
		}
		
		$this->filtros_string .= $fecha_inicial;
		$this->filtros_string .= $fecha_final;

		if (isset($filtros['estado'])) {
			
			$this->filtros_string .= ' and `status` = ' . (int)$filtros['estado'];
		}

		$this->filtros = $filtros;
	}

	private function formatCheck($select_estado_recargas) {

		$estado_recargas = (int)$select_estado_recargas;

		if ($estado_recargas == 3) {
			$estado_recargas = (int)ESTADO_REC_BUSQUEDA_JYCTEL; 
		}

		$check = "=" . $estado_recargas;
		if ($select_estado_recargas == 4) {
			$check = "not in (".ESTADO_CONTRATO_HECHO.",".ESTADO_CONTRATO_ERROR.",".ESTADO_REC_BUSQUEDA_JYCTEL.")";
		}
		return $check;
	}	

	protected function getData($filtros) {
		
		$exportar = 1;
		$listado = new ListadosJyctel($exportar);
		$listado->loadDb();
		
		if ($this->filtros['estado'] == ESTADO_CONTRATO_HECHO && $this->filtros['estado'] != ESTADO_CONTRATO_ERROR) {
			$this->setFiltrosRecargas($filtros);
			
			//$contratos = $listado->get_from_mobile_logs();
			$recargas = array();
			$recargas_contratos = $listado->getRecargasMobileLogs($this->filtros_string);
			syslog(LOG_INFO, __METHOD__ . ':'.time() . ':REC_CONT:'.serialize($recargas_contratos));
			
			
			foreach ($recargas_contratos as $recarga) {
				syslog(LOG_INFO, __METHOD__ . ':'.time() . ':REC:'.serialize($recarga));
				$filtros['id_contrato'] = $recarga->id_contrato;
				$this->setFiltros($filtros);
				$listado->setBusquedaPorContrato(1);
				$listado->setFiltros($this->filtros_string);
				$contrato = $listado->getContratosRecargas();

				if (!is_null($contrato)) {
					foreach ($contrato as $c) {
						$recarga->fecha = $c->fecha;
						$recarga->estado_contrato = $c->check;
						$recarga->token_contrato = $c->num_pedido;
						$recarga->ofertas = $c->ofertas;
					}
				}

				$recargas[$i] =  $recarga;
				$i ++;
			}
		
			syslog(LOG_INFO, __METHOD__ . ':'.time() . ':'.count($recargas));
			$listado->setCantidad(count($recargas));
			
		} else {
			$this->setFiltros($filtros);
			$listado->setFiltros($this->filtros_string);
			$recargas = $listado->get();
		}
		
		$new_recargas = array();
		if (!empty($recargas)) {
			
			$this->setNumRows($listado->getCantidad());

			foreach ($recargas as $idx => $recarga) {
				foreach ($recarga as $key => $value) {
					$format_value = grmFormatValues::format($key, $value, 'jyctel');
					$new_recargas[$idx][$key] = $format_value; 
				}
				if ($this->filtros['estado'] != ESTADO_CONTRATO_HECHO && $this->filtros['estado'] != ESTADO_CONTRATO_ERROR) {
					$new_recargas[$idx]['precio'] = obtener_precio_recarga($recarga->id); 
				} else {
					$new_recargas[$idx]['precio'] = $recarga->amount; 
					
					$pn = new PreciosNuevos();
					$new_recargas[$idx]['cantidad_recarga'] = $pn->convertMontoToCUP($recarga->cantidad_recarga * 100);

				}
			}
		}
		if (!empty($new_recargas)) {
			$this->csv = $this->prepareDataToExport($new_recargas);
		}
	}
	private function prepareDataToExport($recs) {

		$string_campos = array(
			'id_contrato', 'celular', 'cantidad_recarga', 'precio', 'estado_recarga', 'estado_contrato', 'fecha', 'ofertas', 'token_contrato', 'id_recarga', 'empresa'
		);	

		$campos = array();

		foreach ($string_campos as $key => $campo) {
			
			//syslog(LOG_INFO, __FILE__ . ':'. __method__ . ':'.$key.':'.$campo);
			
			$campos[$key] = array('key' => $campo);
		}
		
		//syslog(LOG_INFO, __FILE__ . ':'. __method__ . ':' . var_export($recs, true));

		$csv = "ID contrato;numMobil;cup;precio;estado recarga;estado contrato;fecha;ofertas;token;id recarga;empresa\n";

		foreach ($recs as $rec) {
			foreach ($campos as $campo) {
				$campo_recarga = $campo['key'];
				if (isset($rec[$campo_recarga])) {
					$csv .= rtrim($rec[$campo_recarga], " \t\n\r\0\x0B") . ";";
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
