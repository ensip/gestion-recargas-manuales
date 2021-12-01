<?php

function obtenerEmpresa($idata) {
	
	return (isset($idata[9]) ? $idata[9] : 0);
}

$res = array();

if (isset($_POST['subir'])) {
	includeFactoryImportarRecarga();
	
	if ($_FILES['archivo']['error'] == UPLOAD_ERR_OK && is_uploaded_file($_FILES['archivo']['tmp_name'])) { 


		$empresa = ($_POST['empresa_importar'] ? $_POST['empresa_importar'] : '');
		if(empty($empresa)) {
			$res = array('error' => 'Falta empresa');
		}
		if (empty($res)) {
			$handle = @fopen($_FILES['archivo']['tmp_name'], "r");
			
			while (($data = fgets($handle, 4096)) !== false) {
				if (strpos($data, 'cup') === false ) { //para evitar la cabecera
					$idata = explode(';', $data);
					
					if ($empresa != obtenerEmpresa($idata)) {
						$res = array('error' => 'Empresa no concuerda con escogida');
					} else {
						if (isset($idata[0]) && $idata[0] > 0) {
							$res = grmFactoryImportarRecarga::call($empresa, $idata);
						}
					}
				}
			}
		}
	}
}
if (!empty($res)) {
	if (isset($res['error'])) {
		$resultado_importar = '<h5><span class="badge badge-danger">'.$res['error'].'</span></h5>';
	} else {
		$resultado_importar = '<h5><span class="badge badge-success">Importado con éxito</span></h5>';
	}
}
