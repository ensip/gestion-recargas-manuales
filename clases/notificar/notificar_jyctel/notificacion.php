<?php
includeFuncionesJyctel();

class Notificacion {
	public function __construct($datos_notificacion){
		$this->datos_notificacion = $datos_notificacion; //struct con los datos que se necesita para generar sms
		$this->medio['sms'] = new NotificacionSms($datos_notificacion);
		$this->medio['email'] = new NotificacionEmail($datos_notificacion);
	}

	public function notificar($medio = '') {

		if (!empty($medio) && isset($this->medio[$medio])) {
			
			$medio = $this->medio[$medio];
			$res = $medio->notificar();

		} else {
			foreach ($this->medio as $medio) {
				$res = $medio->notificar();
			}
		}
		return $res;
	}
}
