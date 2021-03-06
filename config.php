﻿<?php

	/**
	*
	*Aqui ficarão as configurações gerais do sistema, tais como: Login e senha do banco de dados, Ambiente de desenvolvimento, etc...
	*/

	$ds = DIRECTORY_SEPARATOR;
	$pasta = explode($ds,getcwd());
	$pasta = end($pasta);

	header("Content-type: text/html; charset=utf-8");
	define('DEBUG',true);
    define('ABSPATH', dirname( __FILE__ ));
	define('URI_BASE',"http://".$_SERVER['SERVER_NAME']."/".$pasta."/index.php");
	define('ROOT_URL',"http://".$_SERVER['SERVER_NAME']."/".$pasta."/");
	define('VIEW_BASE',"http://".$_SERVER['SERVER_NAME']."/".$pasta."/views/");//Recupera a pasta da view

	if(DEBUG === true){
		define("HOST","localhost");
		define("USERDB","root");
		define("PASSDB","");
		define("NAMEDB","webMuseu");
		define('CHARSETDB', 'utf8' );
	}

	require_once("./autoloader.php");

?>
