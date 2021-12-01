<?php
include(__dir__ . '/../../inc/funciones.generales.php');

class ExportarEmpresa {

	protected $cantidad = 0;
	protected $csv = "id;id_usuario;numMobil;cup;precio;estado;fecha;token;empresa\r\n";
	protected $empresa = '';
	protected $filtros = '1';
	protected $post = array();

	public function __construct() {
		$this->post = $_POST;
	}

	public function export($filtros) {
		$this->setFiltros($filtros);
		$this->getData();

		$res = array(
			'link' => $this->exportCsv(),
			'cantidad' => $this->getCantidad(),
			'empresa' =>$this->getEmpresa()		
		);
		return $res;
	}

	/*
	 *	OK PROBADO
	 * */
	protected function exportCsv() {

		$link_exportar_recargas_guardadas = '';
		if ($this->cantidad > 0) {

			$link_exportar_recargas_guardadas = exportarFichero($this->csv, $this->nombreExportar(), 'a+', 'sin-html' );
		}

		return $link_exportar_recargas_guardadas;
	}
	private function nombreExportar() {

		$estados_exportar = estadosExportar($_POST['estado_recargas']);

		$estado_exportar = (!is_array($estados_exportar) ? $estados_exportar : $_POST['estado_recargas']);

		$nombre = sprintf("recargas_%s_manual_cuba_%s_%s.csv", $estado_exportar, $this->empresa, date('d-m-Y-His'));

		return $nombre;
	}
	private function getCantidad() {
		return $this->cantidad;
	}
	private function getEmpresa() {
		return $this->empresa;
	}
}
