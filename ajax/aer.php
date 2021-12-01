<?php

$dbconn = new DbConn("ensip_jycteladm");

$link_exportar_recargas_guardadas = '';
$cantidad_recargas_para_exportar = 0;
if (isset($_POST['exportar_recargas_guardadas'])) {
	$csv = '';
	$empresa = (isset($_POST['empresa'])) ? $_POST['empresa'] : '';
	$select_estado_recargas = (isset($_POST['estado_recargas'])) ? $_POST['estado_recargas'] : '';
	$fecha_inicial_exportar = (isset($_POST['fecha_exportar']['inicial'])) ? $_POST['fecha_exportar']['inicial'] . ' 00:00:01': '';
	$fecha_final_exportar = (isset($_POST['fecha_exportar']['final'])) ? $_POST['fecha_exportar']['final'] . ' 23:59:59' : '';

	if (!empty($select_estado_recargas)) {

		$check = "=" . (int)$select_estado_recargas;
		if ($select_estado_recargas == 4) {
			$check = "not in (1,2,3)";
		}

		$sql = "select id,id_usuario,numMobil,cuc,eur,`check` as estado,fecha,token from recargas_pendientes_no_preventa where fecha >= '".$fecha_inicial_exportar."' and fecha <= '".$fecha_final_exportar."' ".
			"and `check` " . $check;
		$res_expo = getResultSQL($sql);
		$cantidad_recargas_para_exportar = $res_expo->num_rows;
		if ($res_expo->num_rows > 0) {
			
			$precios_nuevos = new PreciosNuevos();

			$csv = "id;id_usuario;numMobil;cup;precio;estado;fecha;token\r\n";

			while ($row_expo = $res_expo->fetch_assoc()) {

				$recs_exportar = $row_expo;
				if (isset($row_expo['estado'])) {
					switch ($row_expo['estado']) {
					case 2:
						$recs_exportar['estado'] = 'Error';
						break;
					case 1:
						$recs_exportar['estado'] = 'Hecha';
						break;
					case 3:
						$recs_exportar['estado'] = 'Pendiente';
						break;
					default: 
						$recs_exportar['estado'] = 'Otros (' . $recs_exportar['estado'] . ')';
						break;
					}
				}

				if (isset($row_expo['eur'])) {
					$recs_exportar['eur'] = str_replace('.',',',$recs_exportar['eur']);
				}
				if (isset($row_expo['cuc'])) {
					$recs_exportar['cuc'] = $precios_nuevos->convertMontoToCUP ($recs_exportar['cuc'] * 100);

				}

				foreach ($recs_exportar as $key => $val) {
					$csv .= "{$val};";
				}
				$csv .= "\r\n";
			}
			echo "<!--"; debug($csv);echo"-->";
		}
	}
	if (!empty($csv)) {

		$link_exportar_recargas_guardadas = exportarFichero($csv, 'recargas_guardadas_'.$_POST['estado_recargas'].'_'.time().'.csv', 'a+', 'sin-html' );
	}
}

