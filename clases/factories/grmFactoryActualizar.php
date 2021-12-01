<?php
includeFuncionesGenerales();
includeFuncionesJyctel();

class FactoryActualizar {
	public static function actualizar($post) {

		$empresa = (isset($post['empresa']) ? $post['empresa'] : '');
		if (empty($empresa)) {
			return array('error' => 'Empresa vacía');
		}
		$recarga = (isset($post['recarga']) ? $post['recarga'] : '');
		if (empty($recarga)) {
			return array('error' => 'Recarga vacía');
		}
	
		$class = 'Actualizar'. ucfirst($empresa);
		if (class_exists($class)) {
			$obj = new $class($recarga);
			return $obj->update();
		} else {
			return array('error' => 'Error con empresa');
		}
	}
}
