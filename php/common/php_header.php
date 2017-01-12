<?php
session_start();
//echo $_SERVER['SCRIPT_NAME'];
//echo strcmp ( $_SERVER['SCRIPT_NAME'] , '/web/login.php' );

//falls nicht loginseite prüfe auf Sessioninhalt der bei korrektem Login gesetzt wird
if( strcmp ( $_SERVER['SCRIPT_NAME'] , '/webec/php/login.php' )!=0 ){
    if( !isset($_SESSION['user_id']) | !isset($_SESSION['mac_address']) ) {
        session_destroy();
        header('Location: login.php');
    }
}
include("inc/config.php");
include("inc/functions.php");
