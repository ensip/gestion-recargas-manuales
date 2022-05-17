<?php

class grmFactonline {
	private $celular = '';
	private $con = null;
	private $data = array();
	private $estado_recarga = 0;
	private $id_contrato = 0;
	private $id_recarga = 0;
	private $prefix_table = PREFIX_TABLE_JYCTEL;
	private $data_mobile_log = array(
		'CurrencyCode' => 'CUC',
		'euro_rate' => 0.74,
		'message' => array( 
			1 => 'Success', 
			2 => 'Ha ocurrido un error'),
		'mob_operator' => 'Cubacel Cuba',
		'operation' => 'return_payment',
		'user_id' => 15258
	);

	public function __construct($data = null) {

		$this->con = getConn(DB_factura);

		$this->id_contrato = $data['id_contrato'];
		$this->id_recarga = $data['id_recarga_hecha'];
		$this->celular = $data['celular'];
		
	}

	private function generar_datos_mobile_logs($data) {

		$con = $this->con;

		$campos_estado = $this->getCamposByEstado();
		$precio = obtener_precio_recarga($this->id_contrato);

		$pn = new PreciosNuevos();	
		$cup = $pn->convertCucToCup($data['monto']);
		$usd = $pn->convertMontoToUSD($cup) / 100;

		//recarga_manual_cuba contiene el valor a recargar por noriel
		if (isset($data['recarga_manual_cuba']) && !empty($data['recarga_manual_cuba'])) {
			
			//$usd = $data['recarga_manual_cuba'];	
			$usd = $pn->convertCupToUsd($data['recarga_manual_cuba']);
		}

		$rec['user_id'] = mli_put($con, $this->data_mobile_log['user_id']);
		$rec['operation'] = mli_put($con, $this->data_mobile_log['operation']);
		$rec['CurrencyCode'] = mli_put($con, $this->data_mobile_log['CurrencyCode']);
		$rec['euro_rate'] = mli_put($con, $this->data_mobile_log['euro_rate']);
	
		$rec['amount'] = mli_put($con, str_replace(',', '.', $precio));
		$rec['amount_cuc'] = mli_put($con, $usd);
		$rec['amount_usd'] = mli_put($con, $this->obtener_precio_usd($data['monto']));
		$rec['to_send'] = mli_put($con, $usd);

		$rec['mobOperator'] = mli_put($con, $this->data_mobile_log['mob_operator']);
		$rec['mobNumber'] = mli_put($con, $this->celular);
		$rec['status'] = mli_put($con, $campos_estado['status']);
		$rec['created'] = mli_put($con, time());
		$rec['ResultId'] = mli_put($con, $campos_estado['ResultId']);
		$rec['ResultStr'] = mli_put($con, $campos_estado['ResultStr']);
		$rec['ConfirmId'] = mli_put($con, $data['ConfirmId']);
		$rec['pago'] = mli_put($con, 'IDC_'.$this->id_contrato);
		$rec['message'] = mli_put($con, $this->data_mobile_log['message'][$this->estado_recarga]);
		$rec['plataforma'] = mli_put($con, PROVIDER_MANUAL);

		return $rec;
	}	

	private function getCamposByEstado() {
		$campos_by_estado = array(
			1 => array(
				'status' => 1,
				'ResultId' => 1,
				'ResultStr' => 'Success'
			),
			2 => array(
				'status' => 'FAILED',
				'ResultId' => 0	,
				'ResultStr' => 'Ha ocurrido un error'
			)
		);
		return $campos_by_estado[$this->estado_recarga];
	}

	/*	
	 *	comprueba cuantas recargas hechas tiene el contratos (insertadas en tabla recargas_contratos_hechas)
	 *
	 * */
	public function getCantidadRecargasInsertadas() {

		$sql = "select count(id) as cant from ".$this->prefix_table."recargas_contratos_hechas where id_c = " . (int)$this->id_contrato;
		$res = $this->con->query($sql);

		if ($res->num_rows > 0) {
			$row = $res->fetch_object();

			return $row->cant;
		}
		return 0;
	}

	/*	
	 *	comprueba cuantas recargas hechas tiene el contratos (insertadas en tabla recargas_contratos_hechas)
	 *
	 * */
	public function get_recargas_contratos_hechas($id_r = '') {

		$sql = "select id_c, id_r from ".$this->prefix_table."recargas_contratos_hechas where id_c = " . (int)$this->id_contrato . (!empty($id_r) ? ' and id_r = ' . $id_r : '');
		$res = $this->con->query($sql);

		$recargas_contratos_hechas = [];
		if ($res->num_rows > 0) {
			while ($rows = $res->fetch_object()) {
				$recargas_contratos_hechas[] = $row;
			}

		}
		return $recargas_contratos_hechas;
	}
	/*
	 *	OK probado
	 * */
	public function insert_recargas_contratos_hechas($id_recarga = '') {
		
		if (!$this->id_contrato || $this->id_recarga == '-1') {

			syslog(LOG_INFO, __FILE__ . ':' .__method__ .':datos id_recarga o id_contrato faltantes');
			return false;
		}

		if (!empty($id_recarga)) {
			$this->id_recarga = $id_recarga;
		}

		$sql = sprintf("insert into %srecargas_contratos_hechas (id_c,id_r) values (%s, %s)", $this->prefix_table, $this->id_contrato, $this->id_recarga);
		$res = $this->con->query($sql);
		syslog(LOG_INFO, __FILE__ . ':' .__method__ .':'.$sql . ':'.$res);

		return $res;
	}

	public function insert_mobile_logs($data) {

		$new_data = $this->generar_datos_mobile_logs($data);
		
		$fields = '';
		$values = '';
		foreach ($new_data as $key => $value) {
			$fields .= sprintf("%s,", $key);
			$values .= sprintf("'%s',", $value);
		}

		$fields = rtrim($fields,',');
		$values = rtrim($values,',');
		
		$sql = sprintf("insert into %smobile_logs (%s) values (%s)", $this->prefix_table, $fields, $values);
		$res = $this->con->query($sql);
		
		if($this->con->errno > 0){
			return array('error' => "Prepare failed: (". $this->con->errno.") ".$this->con->error." $sql");
		}

		return true;
	}

	public static function insert_sms_cdr_notifications($data) {
		
		$con = getConn(DB_factura);
		$sql = sprintf("INSERT INTO FACTONLINE.sms_cdr_notifications ".
			"(`sender_info`,`fecha`,`send_number`,`messageid`,`proveedor`,`resultID`,`msgID`,`resultMess`,`message`,`callback`)".
			" VALUE ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
			'Jyctel',
			date('Y-m-d H:i:s'),
			$data['dato_envio'],
			$data['messageid'],
			$data['proveedor'],
			$data['resultID'],
			$data['msgID'],
			$data['resultMess'],
			$data['texto_notificacion'],
			1
		);
		$res = $con->query($sql);
		
		if($con->errno > 0){
			return array('error' => "Prepare failed: (". $con->errno.") ".$con->error." $sql");
		}

		return true;

	}

	private function obtener_precio_usd($cuc) {
		$tax = 1.25;
		$cuc_rate = 0.999;
		$usd_currency = round($cuc / $cuc_rate, 2);
		return round($usd_currency * $tax, 2);
	}

	public function set_estado_recarga($estado_recarga) {
		$this->estado_recarga = $estado_recarga;
	}
}
