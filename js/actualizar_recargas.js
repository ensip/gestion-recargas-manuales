/*
 *	clase para procesar la actualización de las recargas guardadas para entrega manual y marcadas como entregadas
 *	Antes de actualizarla revisar de nuevo si se ha hecho
 * */

// 1 recoger todas las recargas a realizar:
// necesito: empresa, dato identificativo de la recarga:
// 	ensip - como se guarda en una tabla tengo el id de la tabla, de ahí puedo extraer todos los datos para rellenar las tablas de recarga hecha.
// 	jyctel: como las recargas se guardan en un contrato, necesito la id del contrato, y como cada recarga tiene un ID, el id de la recarga, para luego guardarlo en las tablas relativas.
// 
// 2 - enviar las recargas a realizar: en vez de enviar el paquete de recargas y que me devuelva el resultado de todas, se recorrerá el paquete de recargas seleccionadas y se írá completando una a una, marcado cada línea como hecha al momento de recibir el resultado.
	
class updateRecharges {
	constructor() {
		this.setEmpresa();
		this.setRecharges();
	}
	changeTrBg(id, bg_color) {
		$('#' +id).removeClass('tr_selected');
		$('#' +id).addClass(bg_color);
	}

	getIdtable() {
		return 'table_recharges_' + this.empresa;
	}
	getTextEstadoRecarga(estado) {
		return (estado == 1 ? 'Hecha' : 'Error');
	}
	
	notificarRecargas(recargas) {
	
		$.ajax({
			url : "ajax/ajax_notificar_recargas.php",
			async: false,
			type : 'POST',			                  
			data : {
				'notificar_recargas': recargas,
				'empresa' : this.empresa
			}
		}). done(function(json_res) { 	
			console.log(json_res);
			//var resultado = JSON.parse(json_res);
		});
	}

	processResult(res) {
		
		const exists = res.hasOwnProperty('error');
		if (exists) {
			alert(res.error);
		} else return true;
	}

	setEmpresa() {
		this.empresa = $( '#empresa' ).val();		
	}

	setRecharges() {

		let recharges = [];
		var id = this.getIdtable();
		var i = 0;
		$('#' + id + ' tbody tr').each(function(index, val) {
			var tr_id = $(this).attr('id');
			var input_selected = $('input[name="selected_' + tr_id + '"]');
			var value_selected = input_selected.val();
			var input_id_recharge = $('input[name="id_recharge_' + tr_id + '"]');
			var value_id_recharge = input_id_recharge.val();
			var tipo_recarga = '';
			if (typeof $('.tipo_recarga_0').attr('attr-tipo-recarga') !== 'undefined') {
				tipo_recarga = $('.tipo_recarga_' + tr_id).attr('attr-tipo-recarga');
			}

			//console.log(value_selected);
			if (value_selected != '-') {
				let data = {
					tr_id : tr_id,
					estado : value_selected,
					id: value_id_recharge,
					tipo_recarga:tipo_recarga
				}
				recharges[i] = data;
				i ++;
			}
		});
		if (recharges.length > 0) {
			this.recharges = recharges;
		}
	}

	update() {
		var res = this.updateRecharges();
		//console.log(res);
		if (this.processResult(res)) {
			//$(".results").html('<div class="text-center badge tr_success">RECARGAS ACTUALIZADAS!</div>');
			$(".results").html('');
		}
	}

	updateRecharges() {
		var self = this;
		var promises = [];
		var recargas_actualizadas = [];
		var recarga = [];
		//console.log(this.recharges);
		
		if (this.recharges.length > 0) {
			var dfd = $.Deferred();

			var i = 0;

			$.each (this.recharges, function(index, recharge) {
					
				var tr_id = recharge.tr_id;
			
				//console.log('tr_id-' + tr_id, recarga);
				//console.log('tr_id-' + tr_id, recarga[i - 1]);

				$('tr td.select_estado_recarga_'+tr_id).html('<img src="../../img/loading.gif" width="15" height="15">');

				if (recharge.estado > 0) {
					console.log('recarga', recharge);
					
					$.ajax({
						url : "ajax/ajax_actualizar_recargas.php",
						async: false,
						type : 'POST',			                  
						data : {
							'actualizar_recarga_manual': 1,
							'empresa' : self.empresa,						                      
							'recarga' : recharge
						}
					}). done(function(json_res) { //false : string
						console.log(json_res);
						
						var resultado = '';
						
						if (resultado = JSON.parse(json_res)){
						
							if (resultado.result == 1) {
									
								$(".selected_recharge#\\3" + tr_id).prop("disabled" , 'disabled');
								$('tr td.select_estado_recarga_'+tr_id).html('<div class="badge badge-success">Actualizada</div>');
								self.changeTrBg(tr_id, 'tr_success');
								
								$('.estado_recarga_' + tr_id).html( '<b>' + self.getTextEstadoRecarga(recharge.estado) + '</b>');
						
								recarga = {
									'contrato' : $('tr td.id_' + tr_id).html(),
									'celular' : $('tr td.celular_' + tr_id).html(),
								};
								recargas_actualizadas.push(recarga);
							} else {
								//alert(resultado.text);
								self.changeTrBg(tr_id, 'bg-danger');
							}
						}
						$('tr td.select_estado_recarga_'+tr_id).html('');
						
						//recarga[tr_id] = true;
					});
				} else {
					$('tr td.select_estado_recarga_'+tr_id).html('');
				}
				
				i ++;
			});
			dfd.resolve();
			promises.push(dfd)

		} else {
			return {'error' : 'No hay recargas seleccionadas'}
		}
		$.when.apply($, promises).done(function () {

			console.log(recargas_actualizadas);
			console.log(recargas_actualizadas.length);

			if (recargas_actualizadas.length > 0) {
				self.notificarRecargas(recargas_actualizadas);
			}
			$('#update_estado').prop("disabled", true);
			$('#update_estado').addClass("btn-light");	
			
			return {'result' : true};
		});
		return {'result' : false};
	}
}
