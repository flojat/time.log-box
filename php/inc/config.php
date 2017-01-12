<?php

//Database
$_config_mysql_host="db1.sylon.net";
$_config_mysql_user="c297webec";
$_config_mysql_password="W3b3C16!";
$_config_mysql_db="c297webec";

$conn=mysql_connect($_config_mysql_host,$_config_mysql_user,$_config_mysql_password);
mysql_select_db($_config_mysql_db);

/*
für php.ini
//log_errors = on
//display_errors = on
//date.timezone = 'Europe/Zurich'
//error_reporting = E_ALL & ~E_NOTICE
*/

?>
