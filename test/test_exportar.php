<?php
include_once(__dir__ . '/../inc/conf.inc.php');
includeFactoryExportar();

$_POST['empresa'] = 'jyctel';
$_POST['estado_recargas'] = 1;
$_POST['fecha_exportar_inicial'] = date('Y-m-01 H:i:s');
$_POST['fecha_exportar_final'] = date('Y-m-d H:i:s');
$csv = FactoryExportar::exportar($_POST);
print_r($csv);

