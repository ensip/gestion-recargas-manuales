<?php
includeFuncionesGenerales();
includeFuncionesJyctel();

class FactoryActualizar {
	public static function actualizar($post) {
		
		$empresa = (isset($post['empresa']) ? $post['empresa'] : '');
		$tipo_recarga = (isset($post['recarga']['tipo_recarga']) ? $post['recarga']['tipo_recarga'] : '');

		if (empty($empresa)) {
			return array('error' => 'Empresa vacía');
		}
		if ($tipo_recarga != 'normal') {
			$empresa .= ucfirst($tipo_recarga);
		}
		$recarga = (isset($post['recarga']) ? $post['recarga'] : '');
		if (empty($recarga)) {
			return array('error' => 'Recarga vacía');
		}
	
		$class = 'Actualizar'. ucfirst($empresa);
		
		if (class_exists($class)) {
			$obj = new $class($recarga);
			//print json_encode(array('result' => 1, 'text' => 'ok-preve'));
			return $obj->update();

		} else {
			return array('error' => 'Error con empresa');
		}
	}
}
