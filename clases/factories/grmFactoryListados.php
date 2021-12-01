<?php

class FactoryListadosRecargas {

	private $empresa = '';
	function __construct($empresa) {
		$this->empresa = $empresa;
	}
	public function get() {
		
		$class = 'Listados'. ucfirst($this->empresa);
		if (class_exists($class)) {
			$listado = new $class();
			return $listado->get();
		} else {
			return false;
		}
	}
}
