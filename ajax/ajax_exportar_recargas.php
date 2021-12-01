<?php

include_once(__dir__ . '/../inc/conf.inc.php');
includeFactoryExportar();

if (isset($_POST['exportar_recargas'])) {

	unset($_POST['exportar_recargas']);
	$post_data = $_POST['post'];
	unset($_POST['post']);
	
	foreach ( $post_data as $post) {
		$_POST[$post['name']] = $post['value'];
	}
	$res = FactoryExportar::exportar($_POST);

	print json_encode($res);
}

