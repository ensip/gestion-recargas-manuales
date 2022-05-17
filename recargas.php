<?php
includeFuncionesGenerales();
include_once('inc/recargas_process_post.php');
?>
<div class="pt-1 pb-3 px-4">
	<section>
		<div class="d-flex flex-wrap justify-content-between">
			<div class="flex-column card col-md-4 m-2 p-0">
				<div class="card-header p-1 text-center">
					<h6 class="m-0 text-dark">Exportar recargas por estado</h6>	
				</div>
				<div class="card-body p-1">
					<form action="main.php?recargas=1" method="post" class="form-inline m-1" onsubmit="event.preventDefault();exportaRecargasForm();">
						<div class="form-group row my-1 px-3 col-md-12">
						<input type="date" name="fecha_exportar_inicial" class="form-control form-control-sm mr-1" value="<?=date('Y-m-01')?>" required>
							<input type="date" name="fecha_exportar_final" class="form-control form-control-sm mr-1" value="<?=date('Y-m-d')?>" required>
						</div>
						<div class="form-group row my-1 px-3 col-md-12">
							<select name="empresa" class="form-control form-control-sm mr-1" required>
								<option value="">Empresa</option>
<?php 
if (isset($empresas)) { 
	foreach ($empresas as $empresa) { ?>
		<option value="<?=$empresa['id']?>"><?=$empresa['nombre']?></option>
				
<?php	}
} ?>
								
							</select>
							<select name="estado_recargas" class="form-control form-control-sm mr-1" required>
								<option value="">Estado recargas</option>
<?php foreach (estadosExportar() as $key => $estado) {?>
								<option value="<?=$key?>" <?=($estado == 3 ? 'selected':'')?>><?=$estado?></option>

<?php } ?>
							</select>
							<input type="hidden" name="exportar_recargas_guardadas" value="1">
							<button type="submit" class="btn btn-sm btn-success p-1" title="Exportar recargas" id="exportar_recargas">Exportar</button>
						
						</div>
					</form>

					<h6 class="p-1 cantidad_exportar"><span class="badge badge-info p-2"></span></h6>
					<div id="file_exportar" class="p-1"></div>
				</div>
			</div>
			<div class="flex-column card col-md-3 m-2 p-0">
				<div class="card-header p-1 text-center">
					<h6 class="m-0 text-dark">Actualizar recargas manuales</h6>
				</div>
				<div class="card-body p-1">
					<form action="" method="post" class="form-inline m-1" enctype="multipart/form-data">
						<select name="empresa_importar" class="form-control form-control-sm mr-1" required>
								<option value="">Empresa</option>
<?php 
if (isset($empresas)) { 
	foreach ($empresas as $empresa) { ?>
		<option value="<?=$empresa['id']?>"><?=$empresa['nombre']?></option>
				
<?php	}
} ?>
								
						</select>
						<div class="form-group row my-2 px-3 col-md-12">
							<div class="custom-file col-md-10 col-xs-12 mr-2">
								<input name="archivo" type="file" class="custom-file-input" id="archivo" required="">
								<label class="custom-file-label" for="archivo">Escoge el archivo</label>
							</div>
							<input name="subir" type="submit" value="Subir" class="btn btn-success btn-sm p-1">
							<!--<input name="comprobar" type="submit" value="Comprobar" class="btn btn-success btn-sm ml-1">-->
						</div>
					</form>
				</div>
				<div class="card-footer p-1">
					<?=$resultado_importar?>
				</div>
			</div>
			<div class="flex-column col-md-3 col-sm-12 p-1 mr-2">
				<ul class="list-group m-1 estats-recargas">
					<li class="list-group-item p-1 bg-light">
						<h6 class="text-center text-dark m-0">Recargas Pendientes</h6>	
					</li>
<?php 
$cant_pendientes = 0;
$cant_pendientes_empresas = array();

if (isset($empresas)) { 
	foreach ($empresas as $key => $empresa) { 
		$cant_pendientes_empresas[$empresa['nombre']] = 0;
?>
					<li class="list-group-item d-flex justify-content-between align-items-center py-1 px-5">
						<div class="col-md-4">
							<h6><?=$empresa['nombre']?></h6>
						</div>
<?php 
		$cant_pendientes = getCantPendientes($empresa['id']);
	
		if ($cant_pendientes > 0) {
			$cant_pendientes_empresas[$empresa['nombre']] = 1;
		}
		$badge = ($cant_pendientes > 0) ? 'danger' : 'info';
?>
						<div class="col-md-4">
							<span class="badge badge-<?=$badge?> badge-pill badge-<?=$empresa['id']?>">
								<button type="submit" class="btn btn-sm p-0 text-light" value="3" name="recargas_pendientes" id="recargas_pendientes_<?=$empresa['id']?>"><?=$cant_pendientes?></button>
							</span>
						</div>
					 </li>
<?php	}
} ?>
				</ul>
<?php
if (!empty($cant_pendientes_empresas)) {
?>
				<div class="card text-center">
					<div class="card-header">
						<h6>Envio SMS alertas</h6>			
<?php
	foreach ($cant_pendientes_empresas as $empresa => $value) {
		if (!$value && alertaDesactivada($empresa)) {
?>
						<div class="alerta_sms_activada mt-1">
							<button type="button" class="btn btn-small btn-danger" onclick="activarSMS('<?=strtolower($empresa)?>');">Activar alertas <?=$empresa?></button>
						</div>
<?php 		} ?>
<?php 	} ?>
					</div>
				</div>
			</div>
<?php } ?>
		</div>
	</section>
	<hr class="my-3">
	<section>
		<div class="">
			<ul class="nav nav-tabs">
<?php 
if (isset($empresas)) { 
	foreach ($empresas as $empresa) { ?>
			  <li class="nav-item">
			  <a href="#" class="nav-link " id="<?=$empresa['id']?>"><?=$empresa['nombre']?></a>
			  </li>
<?php	}
} ?>
			</ul>
			<div class="tab-content py-1" id="listado-recargas"></div>
			<input type="hidden" value="-1" id="tab-selected">
		</div>

	</section>

</div>
