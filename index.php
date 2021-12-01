<?php
session_start();
require_once( $_SERVER['DOCUMENT_ROOT'] . 'inc/clases/user-login.class.php' );
/* make it short */
use OZ\User as User;
include( $_SERVER['DOCUMENT_ROOT'] . 'inc/init_sql.php');	
User::init($sql_driver, $sql_host, $sql_name, $sql_user, $sql_pass);

/* check current user */
$user = false;
if(User::check()) {
	/* redirect user */
	header('Location: main.php?recargas=1');
	exit();
}

/* default values */
$login = '';
/* login routine */
$login_error = array();
if(isset($_POST['enter'])) {
	$login = !empty($_POST['login']) ? $_POST['login'] : '';
	$password = !empty($_POST['password']) ? $_POST['password'] : '';
	
	$error_flag = false;
	
	if(empty($login)) {
		/* login is required */
		$login_error['login'] = 'Login is required';
		$error_flag = true;
	}
	
	if(empty($password)) {
		/* password is required */
		$login_error['password'] = 'Password is required';
		$error_flag = true;
	}
	
	/* all checks passed */
	if(!$error_flag) {
		if(User::login($login, $password)) {
			/* redirect to user */
			header('Location: main.php?recargas=1');
			exit();
		}
		else {
			$login_error['general'] = 'Something wrong';
		}
	}
}
include_once( $_SERVER['DOCUMENT_ROOT'] .'inc/include_bootstrap.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Panel recargas cubacel manuales</title>
<?php		print getBootstrapCss(); ?>
       		<link rel="stylesheet" href="../../css/font-awesome.min.css">

	</head>
	<body>
		<div class="content">
			<div class="row d-flex justify-content-center">
				<div class="col-md-4 card m-2 p-0">
					<div class="card-header">
						<h3>Login</h3>
					</div>
					<div class="card-body">
					<form action="" method="post">
						<div class="form-group">
							<label for="login">Login</label>
							<input type="text" class="form-control" name="login" id="login" placeholder="Login" value="<?php echo $login; ?>"/>
							<?php if(!empty($login_error['login'])) { ?>
								<br/>
								<div class="alert alert-danger" role="alert"><?php echo $login_error['login']; ?></div>
							<?php } ?>
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" class="form-control" name="password" id="password" placeholder="Password" value=""/>
							<?php if(!empty($login_error['password'])) { ?>
								<br/>
								<div class="alert alert-danger" role="alert"><?php echo $login_error['password']; ?></div>
							<?php } ?>
						</div>
						<button type="submit" name="enter" class="btn btn-primary">Login</button>
						<?php if(!empty($login_error['general'])) { ?>
							<br/><br/>
							<div class="alert alert-danger" role="alert"><?php echo $login_error['general']; ?></div>
						<?php } ?>
					</form>
					</div>
				</div>
			</div>
		</div>
		<?php print getBootstrapJs();?>
	</body>
</html>
