<?php
//ENSIP
class grmMobileLogs {

	private $data = array();
	private $estado_recarga = 0;

	public function __construct($data, $estado_recarga) {
		$this->data = $data;
		$this->estado_recarga = $estado_recarga;
	}

	private function generar_datos_mobile_logs() {
		
		$con = getConn();

		$data = $this->data;

		$campos_estado = $this->getCamposByEstado();

		$rec['user_id'] = mli_put($con, $data->id_usuario);
		$rec['fecha'] = mli_put($con, date('Y-m-d H:i:s'));
		$rec['operation'] = mli_put($con, 'return_payment');
		$rec['CurrencyCode'] = mli_put($con, 'CUC');
		$rec['country_code'] = mli_put($con, 'CU');
		$rec['euro_rate'] = mli_put($con, 0);
		$rec['amount'] = mli_put($con, str_replace(',', '.', $data->eur));
		$rec['to_send'] = mli_put($con, $data->cuc);
		$rec['mobOperator'] = mli_put($con, 'CU');
		$rec['mobNumber'] = mli_put($con, $data->numMobil);
		$rec['status'] = mli_put($con, $campos_estado['status']);
		$rec['message'] = mli_put($con, $campos_estado['message']);
		$rec['created'] = mli_put($con, time());
		$rec['ResultId'] = mli_put($con, $campos_estado['ResultId']);
		$rec['ResultStr'] = mli_put($con, $campos_estado['ResultStr']);
		$rec['ConfirmId'] = mli_put($con, time());
		$rec['plataforma'] = mli_put($con, 'API recargas pendientes no preventa ' . PROVIDER_MANUAL);
		$rec['token'] = mli_put($con,str_replace(array("\n", "\r", " "), '', $data->token));

		return $rec;
	}
	
	private function getCamposByEstado() {
		$campos_by_estado = array(
			1 => array(
				'status' => 1,
				'message' => 'Success',
				'ResultId' => 1,
				'ResultStr' => 'Success'
			),
			2 => array(
				'status' => 'FAILED',
				'message' => 'Error',
				'ResultId' => 0	,
				'ResultStr' => 'Ha ocurrido un error'
			)
		);
		return $campos_by_estado[$this->estado_recarga];
	}

	public function insert_mobile_logs() {
		$data = $this->generar_datos_mobile_logs();
		
		$con = getConn();

		$fields = '';
		$values = '';
		foreach ($data as $key => $value) {
			$fields .= sprintf("%s,", $key);
			$values .= sprintf("'%s',", $value);
		}

		$fields = rtrim($fields,',');
		$values = rtrim($values,',');

		$sql = sprintf("insert into %smobile_logs (%s) values (%s)", PREFIX_TABLE, $fields, $values);

		$stmt = $con->prepare($sql);
		if(!$stmt){
			return array('error' => "Prepare failed: (". $con->errno.") ".$con->error." $sql");
		}
		$stmt->execute();
		$affected_rows = $stmt->affected_rows;

		$stmt->close();
		$con->close();
			
		return 	$affected_rows;
	}
}
