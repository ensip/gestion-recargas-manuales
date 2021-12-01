<?php

class FactoryExportar {
	public static function exportar($post) {

		$empresa = (isset($post['empresa']) ? $post['empresa'] : '');
		if (empty($empresa)) {
			return array('error' => 'Empresa vacía');
		}
		$estado = (isset($post['estado_recargas']) ? $post['estado_recargas'] : '');
		if (empty($estado)) {
			return array('error' => 'Falta el estado de las recargas');
		}
		$fechas['fecha_exportar']['inicial'] = (isset($post['fecha_exportar_inicial']) ? $post['fecha_exportar_inicial'] : '');
		if (empty($fechas['fecha_exportar']['inicial'])) {
			return array('error' => 'Fecha inicial vacia');
		}
		$fechas['fecha_exportar']['final'] = (isset($post['fecha_exportar_final']) ? $post['fecha_exportar_final'] : '');
		if (empty($fechas['fecha_exportar']['final'])) {
			return array('error' => 'Fecha final vacia');
		}
		$filtros = array(
			'estado' => $estado,
			'fechas' => $fechas['fecha_exportar']
		);
		//print_r($filtros);
		$class = 'Exportar'. ucfirst($empresa);
		//echo __FILE__ . " Exportar: ".$class . "\n";
		if (class_exists($class)) {
			$res = (new $class())->export($filtros);
		} else {
			$res = array('error' => 'Error con empresa');
		}
		return ($res);
	}
}
