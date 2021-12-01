<?php
includeFuncionesGenerales();
includeFuncionesJyctel();

class grmFactoryImportarRecarga {
	public static function call($empresa, $recarga) {

		$class = 'ImportarRecarga'. ucfirst($empresa);
		if (class_exists($class)) {
			$obj = new $class();
			return $obj->importar($recarga);
		} else {
			return array('error' => 'Error con empresa');
		}
	}
}
