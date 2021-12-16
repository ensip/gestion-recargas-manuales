function isNumberKey(evt)
{
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode != 46 && charCode > 31 
	&& (charCode < 48 || charCode > 57))
	return false;
	return true;
}  

function isNumericKey(evt)
{
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if (charCode != 46 && charCode > 31 
	&& (charCode < 48 || charCode > 57))
	return true;
	return false;
} 

$(function() {
	$("#f_ini").datetimepicker( {format:'YYYY-MM-DD'});
	$("#f_fin").datetimepicker({format:'YYYY-MM-DD'});
});

function activarSMS(empresa) {

	$.post( "./ajax/ajax_generico.php", { alerta_empresa:empresa })
	.done(function(json_data) {
		
		console.log(json_data);	
		var data = '';
		
		try {
			data = JSON.parse(json_data);
		} catch (e) {

			$(".alerta_sms_activada").html('<h4><div class="badge badge-danger">Error al actualizar</div></h4>');
		}
		
		if (data != '') {
			if (data.error) {
				$(".alerta_sms_activada").html('<h4><div class="badge badge-danger">Error al actualizar</div></h4>');

			} else  {
				$(".alerta_sms_activada").html('<h4><div class="badge badge-success">Activada alerta</div></h4>');
			}
		}

	}).fail(function(response) {
		    alert('Error: ' + response.responseText);
	});
}

function activarBotonActualizar(estado = '') {

	$('#update_estado').focus().prop("disabled", estado);
	if (!estado) {
		$('#update_estado').removeClass("btn-light");
		$('#update_estado').addClass("btn-info");
	} else {
		$('#update_estado').removeClass("btn-info");
		$('#update_estado').addClass("btn-light");
	}

}

function activarBotonExportar(estado = false) {

	$('#exportar_listado').focus().prop("disabled", estado);
	if (!estado) {
		$('#exportar_listado').removeClass("btn-light");
		$('#exportar_listado').addClass("btn-info");
	} else {
		$('#exportar_listado').removeClass("btn-info");
		$('#exportar_listado').addClass("btn-light");
	}
}

function buscarListados(tab_empresa) {
		
		$("#listado-recargas").html();
		$("#listado-recargas").html('<h6 class="badge badge-info"><img src="../../img/loading.gif" width="15" height="15"> ... Cargando recargas ' + tab_empresa + ' ...</h6>');

		$.post( "./ajax/ajax_listados_recargas.php", { listado_recargas:1, empresa: tab_empresa })
		.done(function(json_data) {

			//console.log(json_data);	
			var data = '';
			
			try {
				data = JSON.parse(json_data);
			} catch (e) {
				
				$("#listado-recargas").html('<div class="col-md-5 text-center offset-md-3 my-2 alert alert-danger">Error al cargar listado recargas</div>');
			}
			
			if (data != '') {
				if (data.error && data.error == 'no-recargas') {
					$("#listado-recargas").html('<div class="col-md-5 text-center offset-md-3 my-2 alert alert-warning">No hay recargas de <b>' + tab_empresa +'</b> pendientes de hacerse</div>');

				} else  {
					$("#listado-recargas").html();
					$("#listado-recargas").html(data);
				}
				$('#tab-selected').val(1);
			}

		}).fail(function(response) {
			    alert('Error: ' + response.responseText);
		});
}

function changeEstado(id) {
	$( document ).ready(function() {
		$(".results").html('<img src="../../img/loading.gif" width="15" height="15">');
		uR = new updateRecharges();
		uR.update();
	});
}

function checkAll(e) {
	if(e.checked){
		$(".checkSingle").each(function(){
			this.checked=true;
		})              
		activarBotonExportar();
	}else{
		$(".checkSingle").each(function(){
			this.checked=false;
		})              
		activarBotonExportar(true);
	}
}

function checkSingle(e) {
	if (e.checked){
		console.log('checked');
		var isAllChecked = 0;
		$(".checkSingle").each(function(){
			if(!this.checked)
				isAllChecked = 1;
		});              
		if(isAllChecked == 0){ 
			$("#checkAll").prop("checked", true); 
		}     
		activarBotonExportar();
	}else {
		console.log('not checked');
		$("#checkAll").prop("checked", false);
	
		var marcados = 0;
		$(".checkSingle").each(function(res){
			if(this.checked) {
				marcados = 1;
				return false;
			}
		});            
		console.log(marcados);
		if (!marcados)
			activarBotonExportar(true);

	}
}

function checkTabSelected(empresa = 'ensip') {
	
	if ($('#tab-selected').val() == '-1') {
		buscarListados(empresa);
		$('.nav-item a.nav-link#ensip').addClass('active');
	}
}
function exportaRecargas(post) {
		
	$("#file_exportar").html('<h6 class="badge badge-info"><img src="../../img/loading.gif" width="15" height="15"> ... Exportando recargas ...</h6>');

	$.post( "./ajax/ajax_exportar_recargas.php", { exportar_recargas : 1, post })
		.done(function(json_data) {
			
			console.log(json_data);	
			
			var data = '';
			
			try {
				data = JSON.parse(json_data);
			} catch (e) {
				alert('Error al exportar');	
				$("#file_exportar").html('');
			}
			
			console.log(data);	
			
			if (data != '') {
				if (data.error) {
					$("#file_exportar").html('');
					alert(data.error);	
				} else  {
					buscarListados(data.empresa);
					$('.cantidad_exportar span').html('Cantidad: ' + data.cantidad);
					var link = (data.link != '' ? data.link : '<h6 class="badge badge-warning">No hay recargas</h6>');
					$('#file_exportar').html(link);
				}
			}

		}).fail(function(response) {
			alert('Error: ' + response.responseText);
			$("#file_exportar").html('');
		});
}
function exportaRecargasForm() {
	
	var post_values = $('form').serializeArray();

	exportaRecargas(post_values);
	
}

function selectNewState(e) {
	
	var estado = $(e).val();
	var id = $(e).attr('id');
	
	var estado_boton = true;

	if (estado != '') {
		estado_boton = false;
	} else {
		estado = '';
		$('.table_recharges tr#' +id).removeClass('tr_selected');
	}
		
	$('input[name="selected_' + id + '"]').val(estado);

	var actualizar_estado = 0;
	$(".selected_recharge").each(function(){
		estado = $(this).val();	
		if (estado != '') {
			actualizar_estado = 1;
			$('.table_recharges tr#' +id).addClass('tr_selected');
		} 
	});            
	console.log(estado);
	if (actualizar_estado) {
		estado_boton = false;
	}

	activarBotonActualizar(estado_boton);
}
function selectNewsStates() {

	var estado = $('.select_recharges').children("option:selected").val();
	var actualizar_estado = 0;
	
	$(".selected_recharge").each(function(){
		$(this).val(estado);	
		actualizar_estado = 1;
		
		if (estado) {
			$('.table_recharges tr#' + $(this).attr('id')).addClass('tr_selected');
		} else {
			$('.table_recharges tr#' + $(this).attr('id')).removeClass('tr_selected');

		}
		$('input[name="selected_' + $(this).attr('id') + '"]').val(estado);
	});            

	if (actualizar_estado)
		activarBotonActualizar((estado != '' ? false : true));
}

$( document ).ready(function() {
	checkTabSelected();
		
	$('.nav-item a.nav-link').bind('click', function(){
		$('.nav-item a.nav-link').removeClass('active');

		var tab_empresa = $(this).attr('id');
		$('.nav-item a.nav-link#' + tab_empresa).addClass('active');
		buscarListados(tab_empresa);
	});

	$('select[name="empresa"], select[name="empresa_importar"]').change(function(){
		if ($(this).val() != '') {
			buscarListados($(this).val());
		}

	});

});
