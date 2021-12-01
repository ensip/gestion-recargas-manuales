<?php
include(__dir__ . '/../actualizar/actualizar_ensip.php');

class ImportarRecargaEnsip {
	private function getDataFromArray($recarga, $campo) {
		if ($campo == 'id_registro' ) {
			return $recarga[0];
		}
	}

	public function importar($recarga) {
		$info_recarga = array(
			'id' => $this->getDataFromArray($recarga, 'id_registro'),
			'estado' => 1
		); 

		$ae = new ActualizarEnsip($info_recarga); 
		$res = $ae->update();
		
		if ($res) {
			return true;
		} else {
			return array ('error' => 'Error al importar jyctel');
 		}
	}	
	
}	
