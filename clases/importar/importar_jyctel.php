<?php
include(__dir__ . '/../actualizar/actualizar_jyctel.php');
include(__dir__ . '/../../inc/funciones.jyctel.php');

class ImportarRecargaJyctel {
	
	private function getDataFromArray($recarga, $campo) {
		
		if ($campo == 'id_contrato' ) {
			return $recarga[0];
		}
	}

	public function importar($recarga) {
		
		$id_contrato = $this->getDataFromArray($recarga, 'id_contrato');

		$recargas = unserialize(getContrato($id_contrato, 'recargas'));
		
		$id_recarga = '-1';
		if (is_array($recargas)) {
			$id_recarga = key($recargas);
		}
		if ($id_recarga == '-1') {
			return array('error' => 'Datos exportados incorrectos');
		}

		$datos = new StdClass();
		$datos->id = $id_contrato;
		$datos->id_recarga = $id_recarga;

		$info_recarga = array(
			'id' => getInfoRecargaListado ('jyctel', $datos),
			'estado' => 1
		); 
		
		$ae = new ActualizarJyctel($info_recarga); 	
		$res = $ae->update();
		
		if ($res) {
			return true;
		} else {
			return array ('error' => 'Error al importar jyctel');
 		}
	}	
}
