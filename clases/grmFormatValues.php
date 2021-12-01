<?php

class grmFormatValues {
	
	public static function format($key, $value, $empresa) {

			
		if ($key == 'cantidad_recarga') {
			return self::cantidadRecarga($value, $empresa);
		}
		if ($key == 'celular') {
			return self::celular($value);
		}
		if ($key == 'estado_contrato') {
			return self::estadoContrato($value);
		}
		if ($key == 'estado_recarga') {
			$estado = self::estadoRecarga($value);//es posible que no se use
			return $estado;
		}
		return $value;
	}

	public static function cantidadRecarga($value, $empresa) {
		if ($empresa == 'ensip') {
			$value = $precios_nuevos = (new PreciosNuevos())->convertMontoToCUP($value * 100, 'usd');
		}
		if ($empresa == 'jyctel') {
			$pn = new PreciosNuevos();
			$value = $pn->convertCucToCup($value);
		}

		return $value;
	}
	private static function celular($value) {
		if (strlen($value) == 8) {
			return '53' . $value;
		} else 
			return $value;
	}

	public static function estadoContrato($value) {
		switch ($value) {
		case 0:
			$estado = 'Sin verificar';
			break;
		case 1:
			$estado = 'Hecho';
			break;
		case 2:
			$estado = 'Error';
			break;
		case 3:
			$estado = 'Pendiente';
			break;
		case 9:
			$estado = 'Pendiente manual';
			break;

		default:
			$estado = $value;
			break;
		}
		return $estado;
	}

	public static function estadoRecarga($value) {
		switch ($value) {
		case 1:
			$estado = 'Hecha';
			break;
		case 2:
			$estado = 'Error';
			break;
		case 3:
			$estado = 'Pendiente';
			break;
		case '' :
			$estado = 'Pendiente';
			break;
		default:
			$estado = $value;
			break;
		}
		return $estado;
	}
}
