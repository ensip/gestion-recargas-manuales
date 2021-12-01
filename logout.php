<?php
	/**
	* OZ\User logout demo
	*/
	session_start();
	require_once( $_SERVER['DOCUMENT_ROOT'] . 'inc/clases/user-login.class.php'); 
	/* make it short */
	use OZ\User as User;
	include( $_SERVER['DOCUMENT_ROOT'] . 'inc/init_sql.php');
	
	User::init($sql_driver, $sql_host, $sql_name, $sql_user, $sql_pass);
	User::logout();
	header('Location: index.php');
	exit();
