<?php
session_start();
include_once('../../inc/funciones.inc.php');
include_once('inc/conf.inc.php');
require_once('../../inc/clases/user-login.class.php');
include( $_SERVER['DOCUMENT_ROOT'] . 'inc/include_bootstrap.php');

//echo "<!--";print_r ($_SESSION);echo"-->";

use OZ\User as User;
include( $_SERVER['DOCUMENT_ROOT'] . 'inc/init_sql.php');
User::init($sql_driver, $sql_host, $sql_name, $sql_user, $sql_pass);

$user = false;
if(User::check()) {
	$user = User::getByID($_SESSION['user']['id']);
	syslog(LOG_INFO, __FILE__ . "user: ".serialize($user['id']) );
	
	$sql = "select permiso from admpermisos where id_admin = ".$user['id']." and estado = 1 and permiso = 'gestion-recargas-manual'";
	$res = getResultSQL( $sql );
	if (!$res->num_rows) {
		header('Location: logout.php');
		exit();
	}
} else {
	/* redirect to user account */
	header('Location: index.php');
	exit();
}

$links = array('recargas');
$active_li = array();
$include = '';
foreach( $links as $link_get ){
	if( isset( $_GET[$link_get] ) ){
		$active_li[$link_get] = 'active';
		$include  = $link_get . '.php';
		break;
	}
}
?>
<html>
<head>
	<title>Gestion mail BD</title>
	<?php print getBootstrapCss(); ?>
	<style>
		a{cursor:pointer;}
		.ui-datepicker {   margin-left: 100px; top:190px!important;}
		body{background-color:var(--light);}
		.container{ background-color:white;min-height:800px;max-width:95%;}
		.input-group{margin: 0px 2px;}
		.input-group-prepend .input-group-text{min-width:120px;}		
		.list-group-item:hover{ background-color:var(--light);}
		.card { margin:5px; }
		.card h6, .card button, ul h6{font-size:0.9rem!important;}
		.estats-recargas .badge{min-width:65px;}
		#listado-recargas .table-responsive { max-height: 550px; overflow: auto;}
		.tr_success {background-color:#78ffc7!important;}
		.tr_selected {background-color:rgb(153 47 197 / 25%)!important;}
		.form-control-sm {font-size:0.7rem!important;}
		#listado-recargas .table_recharges th {font-size: 0.75rem;}
		#listado-recargas .table_recharges td {font-size: 0.85rem;}
		#listado-recargas .table_recharges select { height: 1.5rem;padding:0.5px 30px; font-size:0.75rem;}
		.nav-tabs .nav-link.active {background-color: #ebf5ff;}
	</style>

</head>
<body>
	<nav class="navbar navbar-expand-lg navbar-light border-bottom bg-dark text-light justify-content-between p-1">
		<h6 class="m-0">PANEL RECARGAS CUBACEL MANUALES</h6>
		<h5 class="ml-5 float-right"><span class="badge badge-info"><a class="text-light" href="logout.php">LOGOUT</a></span></h5>
	</nav>
	<div class="container">
<?php
if( $include != '' ) include($include);

?>
	</div>
	<?php print getBootstrapJs(); ?>
	<script type="text/javascript" src="js/functions.js?v<?=time()?>"></script>
	<script type="text/javascript" src="js/actualizar_recargas.js?v<?=time()?>"></script>
</body>
</html>
