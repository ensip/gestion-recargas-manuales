<?php
include_once('/data/www/ensip/admin/inc/funciones.inc.php');


if (!defined('ESTADO_CONTRATO_HECHO')) {
	define('ESTADO_CONTRATO_HECHO', 1);
}
if (!defined('ESTADO_PENDIENTE_MANUAL')) {
	define('ESTADO_PENDIENTE_MANUAL', 9);
}
if (!defined('ESTADO_REC_BUSQUEDA_ENSIP')) {
	define('ESTADO_REC_BUSQUEDA_ENSIP', 3);
}
if (!defined('ESTADO_REC_BUSQUEDA_JYCTEL')) {
	define('ESTADO_REC_BUSQUEDA_JYCTEL', 9);
}
if (!defined('grmTEST')) {
	define('grmTEST', 0);
}
if (!defined('PREFIX_TABLE')) {
	if (grmTEST == 1) {
		define('PREFIX_TABLE', 'test.');
	} else {
		define('PREFIX_TABLE', '');
	}
}
if (!defined('PREFIX_TABLE_PREPAGOS')) {
	if (grmTEST == 1) {
		define('PREFIX_TABLE_PREPAGOS', 'test.');
	} else {
		define('PREFIX_TABLE_PREPAGOS', '');
	}
}
if (!defined('PREFIX_TABLE_JYCTEL')) {
	if (grmTEST == 1) {
		define('PREFIX_TABLE_JYCTEL', 'test.');
	} else {
		define('PREFIX_TABLE_JYCTEL', '');
	}
}
if (!defined('PROVIDER_MANUAL')) {
	define('PROVIDER_MANUAL', 'manual_cuba');
}
if (!defined('grmTEST')) {
	define('grmTEST', 0);
}

$empresas = array(
	'ensip' => array(
		'nombre' => 'Ensip',
		'id' => 'ensip'
	),
	'jyctel' => array(
		'nombre' => 'Jyctel',
		'id' => 'jyctel'
	)
);
function includeCrudEnsip() {
	$cruds = array(
		'grmMobileLogs.php',
		'grmRecargasPendientesNoPreventa.php'
	);	

	foreach ($cruds as $crud) {
		$path = __dir__ . '/../clases/crud/' . $crud;

		if (is_file($path)) {
			include_once($path);
		}
	}
}

function includeCrudJyctel() {
	$cruds = array(
		'grmFactonline.php',
		'grmPrepagos.php'
	);

	foreach ($cruds as $crud) {
		$path = __dir__ . '/../clases/crud/' . $crud;

		if (is_file($path)) {
			include_once($path);
		}
	}
}

function includeFactoryExportar() {
	syslog(LOG_INFO, __FILE__ . ': including ' . __METHOD__ );

	global $empresas;

	include_once( __dir__ . '/../clases/factories/grmFactoryExportar.php');
	include_once( __dir__ . '/../clases/exportar/exportar_empresa.php');

	foreach ($empresas as $empresa) {

		$crud_empresa = 'includeCrud' . $empresa['nombre'];
		$crud_empresa();

		$path = __dir__ . "/../clases/exportar/exportar_" . $empresa['id'] . ".php";

		if (is_file($path)) {
			syslog(LOG_INFO, __FILE__ . ':' . __METHOD__ . ':including:'.$path);
			include_once($path);
		}
	}
}

function includeFactoryActualizar() {
	syslog(LOG_INFO, __FILE__ . ': including ' . __METHOD__ );

	global $empresas;

	include_once( '../clases/factories/grmFactoryActualizar.php');

	foreach ($empresas as $empresa) {

		$crud_empresa = 'includeCrud' . $empresa['nombre'];
		$crud_empresa();

		$path = __dir__ . "/../clases/actualizar/actualizar_" . $empresa['id'] . ".php";

		if (is_file($path)) {
			syslog(LOG_INFO, __FILE__ . ':' . __METHOD__ . ':including:'.$path);
			include_once($path);
		}
	}
}

function includeFactoryImportarRecarga() {
	syslog(LOG_INFO, __FILE__ . ': including ' . __METHOD__ );

	global $empresas;

	include_once( __dir__ . '/../clases/factories/grmFactoryImportarRecarga.php');

	foreach ($empresas as $empresa) {

		$crud_empresa = 'includeCrud' . $empresa['nombre'];
		$crud_empresa();

		$path = __dir__ . "/../clases/importar/importar_" . $empresa['id'] . ".php";

		if (is_file($path)) {
			syslog(LOG_INFO, __FILE__ . ':' . __METHOD__ . ':including:'.$path);
			include_once($path);
		}
	}
}
function includeFactoryListados() {
	syslog(LOG_INFO, __FILE__ . ': including ' . __METHOD__ );
	global $empresas;

	include(__dir__ . '/../clases/grmTablas.php');
	
	include_once( __dir__ . '/../clases/factories/grmFactoryListados.php');

	foreach ($empresas as $empresa) {
		$path = __dir__ . "/../clases/listados/listados_" . $empresa['id'] . ".php";

		if (is_file($path)) {
			syslog(LOG_INFO, __FILE__ . ':' . __METHOD__ . ':including:'.$path);
			include_once($path);
		}
	}
}

function includeFuncionesGenerales() {
	syslog(LOG_INFO, __FILE__ . ':' . __METHOD__);
	$includes = array(
		'inc/funciones.generales.php',
		'clases/grmFormatValues.php'
	);
	foreach ($includes as $include) {
		$path = __dir__ . "/../".$include;
		if (is_file($path)) {
			syslog(LOG_INFO, __FILE__ . ':' . __METHOD__ . ':including:'.$path);
			include_once($path);
		}
	}
}

function includeFuncionesJyctel() {
	syslog(LOG_INFO, __FILE__ . ':' . __METHOD__);
	$includes_jyctel = array(
		'inc/funciones.jyctel.php'
	);
	foreach ($includes_jyctel as $include) {
		$path = "../".$include;
		if (is_file($path)) {
			syslog(LOG_INFO, __FILE__ . ':' . __METHOD__ . ':including:'.$path);
			include_once($path);
		}
	}
}

