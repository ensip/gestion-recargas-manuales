<?php
include_once("/data/www/ensip/inc/clases/Class.Utilities.php");

class NotificacionSms {
	protected $texto_notificacion = '';

	public function __construct($datos_notificacion){
		$this->datos_notificacion = $datos_notificacion; //struct con los datos que se necesita para generar sms
	}

	private function formatTelf($dato) {

		if (!$dato || empty($dato)) {
			return '';
		}

		$prefix = $this->getPrefix($dato);

		return $prefix . $dato;
	}	

	/**
	*	set texto_notificacion con datos_notificacion
	*/
	private function generarTextoNotificacion($datos) {

		$cant_cel = 0;
		$celulares = '';
		//$datos = $this->datos_notificacion; //numero recargado
		foreach ($datos as $valores) {
			if (!empty($valores['celular'])) {
				if (isset($valores['cantidad']) && !empty($valores['cantidad'])) { //ahora mismo no hay cantidad
					$celulares .= $valores['celular'] . '-'. $valores['cantidad'] .$valores['divisa'] .',';
				} else {
					$celulares .= $valores['celular'] . ',';
				}
				$cant_cel ++;
			}
		}
		if (empty($celulares)) {
			return false;
		}
		$celulares = rtrim($celulares, ',');
		$text_sms = '';
		$text_sms = sprintf("La%s recarga%s a %s se ha%s realizado correctamente. Jyctel.",
			($cant_cel > 1 ? 's' : ''),
			($cant_cel > 1 ? 's' : ''),
			$celulares,
			($cant_cel > 1 ? 'n' : '')
		);

		return $text_sms;
	}
	
	/*
	 *  busca prefijos mas usados para enviar sms
	 *  usa (1), españa (6|34), francia (33), italia (39), uk (44), alemania (49), ecuador (593)
	 *	@param $num: string
	 *	@return prefix:string from prefix | empty 
	 *
	 */
	private function getPrefix($num) {
		//$prefixs = array( '1' , '6' , '34' , '33' , '39' , '44' , '49'  , '593');
		$prefixs = array( '6' , '7');
		foreach( $prefixs as $prefix ){

			if (preg_match('/^(' . $prefix . ')/', $num)) {
				if ($prefix == 6 || $prefix == 7) {
					$prefix = '34';
				}
				return $prefix;
			}
		}
		return '';
	}

	private function getProveedor() {
	
		$proveedor = getProveedorSmsGrm();
		return $proveedor;
	}	
	/**
	* 	envio sms con texto_notificacion a dato_envio
	 * 	@param 
	 * 		$dato_envio: telefono contrato Cellular
	 *		$texto_notificacion: texto generado con $datos_notificacion
	 *	@return
	 *		unsuccess:false
	 *		success: true
	 */	
	public function notificar() {
		
		foreach ($this->datos_notificacion as $id_contrato => $datos_contrato) {	
			
			$texto_notificacion = $this->generarTextoNotificacion($datos_contrato);	
			if ($texto_notificacion == false) {
				syslog( LOG_INFO, __FILE__ . ": texto_notificacion empty" );
				continue;
			}

			$dato_envio = $this->formatTelf(getContrato($id_contrato, 'Cellular'));	

			if (empty($dato_envio)) {
				syslog( LOG_INFO, __FILE__ . ": dato_envio empty" );
				continue;
			}	
			
			/*3 infobip_: toda notificacion por infobip*/
			$proveedor = $this->getProveedor(); //proveedor sms
			$data = array(
				'proveedor' => $proveedor,
				'sender_info' => 'Jyctel',
				'send_number' => $dato_envio,
				'message' => $texto_notificacion,
				'messageid' => substr(str_shuffle(str_repeat('0123456789',3)),0,9)
			);

			/*
			 *	return 
			 *		$resultID: string: 0 => ok
			 *		$msgID: string
			 *		$resultMess: string
			 * */
			$result = Utilities::send_sms($data, new DBConnect()); 
			
			syslog (LOG_INFO, __FILE__ .  " ENVIO SMS -> TELF:{$dato_envio} | ResultID: {$result->resultID} | estado: {$result->resultMess} | Provider: $proveedor");

			$data_insert = array(
				'dato_envio' => $dato_envio,
				'messageid' => $data['messageid'],
				'proveedor' => $proveedor,
				'resultID' => $result->resultID,
				'msgID' => $result->msgID,
				'resultMess' => $result->resultMess,
				'texto_notificacion' => $texto_notificacion
			);
			$res_ins = grmFactonline::insert_sms_cdr_notifications($data_insert);

			syslog( LOG_INFO, __FILE__ . "INFO SAP sms_cdr_notifications res-sms: ({$result->msgID})-> res-ins:$res_ins : T:$dato_envio");

			/*if ($result->resultID != 0) {
				syslog( LOG_INFO, __FILE__ . "INFO SAP sms_cdr_notifications -> sms no enviado : T:$dato_envio " );
			}*/
		}
	}

}
