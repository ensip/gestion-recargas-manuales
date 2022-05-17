<?php

class NotificacionEmail {
	protected $texto_notificacion = '';

	public function __construct($datos_notificacion) {
		$this->datos_notificacion = $datos_notificacion; //struct con los datos que se necesita para generar sms
	}
	
	public function envioEmail($msg, $email) {

		$cabeceras = "Content-type: text/html\r\n";
		$cabeceras .= "From: recargas_auto@jyctel.com\r\nContent-type: text/html\r\n";
		$cabeceras .= 'Cc: auxiliar@jyctel.com' . "\r\n";

		$res = mail('auxiliar@ensip.com','Contratos recargas automaticas', $msg, $cabeceras);
		//
		if ($email != '') {
			$res_comercial = php_mailer($msg, 'Contratos recargas automaticas', $email, array('recargas_auto@jyctel.com','diego@jyctel.com'));
		} 
		
		if (empty($email)) {
			mail('admin@jyctel.com','Contratos recargas automaticas - Sin validador', $msg, $cabeceras);
		}

		syslog( LOG_INFO, __FILE__ . ": ENVIO EMAIL : res: $res res_comercial :$res_comercial" );
	}

	/**
	 *	set texto_notificacion con datos_notificacion
	 */
	private function generarTextoNotificacion($id_contrato, $datos, $recargas) { 
		
		$msg = '<h2>Notificacion de recargas</h2>';
		$msg .="<p>";
		$msg .="|Pedido num: $id_contrato<br/>";
		$msg .="|Vendedor: {$datos->cod_vendedor} - {$datos->email_vendedor}<br/>";
		$msg .="|Estado: <b>Correcto</b><br/>";
		$msg .="|CardCode: {$datos->CardCode}<br/>";
		$msg .="|CardName: {$datos->CardName}<br/>";
		$msg .="|Oferta: {$datos->ofertas}<br/>";
		$msg .="|Fecha: {$datos->fecha}";
		$msg .="</p>";
		$msg .= "<p><b>Recargas correctas:</b></p>";
		
		foreach ($recargas as $recarga) {
			$msg .= '<p>' . $recarga['celular'] . " - ".$datos->recibe . '</p>';
		}
		return $msg;
	}

	/**
	 * 	envio email con texto_notificacion a dato_envio
	 */	
	public function notificar() {

		foreach ($this->datos_notificacion as $id_contrato => $recargas_contrato) {	
			
			$datos_contrato = (object)getContrato($id_contrato,'id,Cellular,CardCode,CardName,fecha,ofertas,cod_vendedor,id_oferta,(select email from PrepagosJyc.admin WHERE id = cod_vendedor) as email_vendedor');	
			if (empty($datos_contrato)) {
				syslog( LOG_INFO, __FILE__ . ": dato_envio para email empty" );
				continue;
			}
			$oferta = getOferta($datos_contrato->id_oferta, 'recarga_manual_cuba as recibe');
			$datos_contrato->recibe = $oferta->recibe;

			$texto_notificacion = $this->generarTextoNotificacion($id_contrato, $datos_contrato, $recargas_contrato);	
			if ($texto_notificacion == false) 
				continue;

			$this->envioEmail($texto_notificacion, $datos_contrato->email_vendedor);
		}
	}
}
