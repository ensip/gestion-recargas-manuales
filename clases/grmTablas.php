<?php

class camposTablasRecargas {
	const CAMPOS_ENSIP = array(
		'id',
		'celular', 
		'cantidad_recarga', 
		'fecha', 
		'estado_recarga',
		'token', 
	);
	const CAMPOS_JYCTEL = array(
		'id',
		'celular',
		'cantidad_recarga', 
		'fecha',
		'estado_recarga',
		'estado_contrato',
		'token_contrato',
	);

	public static function getCampos($empresa) {
		//$const = 'CAMPOS_' . strtoupper($empresa);

		if ($empresa == 'ensip') {
			return self::CAMPOS_ENSIP;
		}
		if ($empresa == 'jyctel') {
			return self::CAMPOS_JYCTEL;
		}

		return false;
	}
}

class tablaRecargas {
	private $empresa = '';
	private $recargas = array();

	function __construct($empresa, $recargas) {
		$this->empresa = $empresa;
		$this->recargas = $recargas;
		$this->campos = camposTablasRecargas::getCampos($empresa);
	}

	private function crearTabla() {

		$th = '<thead class="thead-dark">';
		$th .= "<th class='py-0'>i</th>";
		
		/* TODO$th .= '<th class="py-0">'.
			'<input type="checkbox" name="checkAll" id="checkAll" value="" title="Seleccionar todos" onclick="checkAll(this)">'.
			//'&nbsp;<label for="checkAll">Seleccionar Todos</label>'.
			'</th>';
		 */
		$td = '';
		
		$i = 0;

		foreach ($this->recargas as $recargas) {
			$td .= '<tr id="'.$i.'">';
			//TODO$td .= '<td class="p-1"><input type="checkbox" name="checkAll" class="checkSingle" onclick="checkSingle(this)" value="" title="Seleccionar para exportar"></td>';
			$td .= "<td class='py-0'>".($i + 1)."</td>";

			foreach ($recargas as $key => $valor) {
				if ($i == 0) {
					if (in_array($key, $this->campos)) {
						$th .= "<th class='py-0'>".$this->formatColumn($key)."</th>";
					}
				}
				

				if (in_array($key, $this->campos)) {
					$td .= "<td class='py-0 ".$key."_".$i."' >" . $this->formatValue($key, $valor) . "</td>";
			
					if ($key == 'estado_recarga') {
						$td .=  '<input type="hidden" value="'.$value.'" name="estado_original">';
					}


				}	
			}
			$id_recharge = $this->getIdentifier($recargas);

			$td .= '<td class="form-inline select_estado_recarga_'.$i.'">
					<select class="browser-default custom-select custom-select-sm selected_recharge"" id="'.$i.'" onchange="selectNewState(this)">
						<option value="">Estado Nuevo</option>
						<option value="1">Hecha</option>
						<option value="2">Error</option>

					</select>'.
					'<input type="hidden" name="selected_'.$i.'" value="0" >'.
					'<input type="hidden" name="id_recharge_' . $i . '" value="' . $id_recharge . '" >'.
				'</td>';
			$td .= '</tr>';
			$i ++;
		}
		//$th .= '<th class="py-0">Actualizar Estado</th>';
		
		$th .= '<th class="py-0">
			<div class="form-inline"><select class="browser-default custom-select custom-select-sm select_recharges" onchange="selectNewsStates()">
				<option value="">Seleccionar Estado</option>
				<option value="1">Realizadas</option>
			</select></div>'.
			'</th>';
		
		$th .= '</thead>';
		
		$table = '<div class="table-responsive">'.
			'<table class="table table-sm table-striped table-hover table_recharges" id="table_recharges_'.$this->empresa.'">'.
			$th . 
			'<tbody>'.$td.'</tbody>'.
			'</table>'.
			'</div>';
		return $table;
	}

	public function format() {

		$tabla = $this->crearTabla();

		$div = '<div class="card m-0">';
		$div .= "<div class='card-header text-center p-1 m-0'><h6 class=\"m-0\">" . strtoupper("Recargas ".$this->empresa) . "</h6></div>";
		$div .= '<div class="card-body p-1">'.
			$tabla.
			'<div class="bg-light"><hr>'.
			'<div class="results"></div>'.
			//'<input type="button" class="btn btn-sm btn-light ml-2" value="EXPORTAR" id="exportar_listado" onclick="" disabled>'.
			'<input type="button" class="btn btn-sm btn-light ml-2" value="ACTUALIZAR RECARGAS" id="update_estado" onclick="changeEstado();" disabled>'.
			'<input type="hidden" value="'.$this->empresa.'" id="empresa">'.
			'</div>'.
			'</div>';
		$div .= '</div>';
		return $div;
	}
	
	private function formatColumn($th) {
		return ucfirst(str_replace('_', ' ', $th));
	}

	private function formatValue($key, $value) {
		$format_value = grmFormatValues::format($key, $value, $this->empresa);

		return $format_value;
	}

	private function getIdentifier($recharge) {

		return getInfoRecargaListado ($this->empresa, $recharge);
	}
}
