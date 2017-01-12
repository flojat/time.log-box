<?php

//http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."
include_once("common/php_header.php");
include_once("common/html_header.php");
session_destroy();
header('Location: login.php');
include_once("common/foot.php");
