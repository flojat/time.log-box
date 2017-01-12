<?php

include_once("common/php_header.php");
include_once("common/html_header.php");

//unset Adminoption
unset($_SESSION['show_as_admin']);
unset($show_as_admin);

if (isset($_GET['back']) && $_GET['back'] !=""){
    $back = $_GET['back'];
}else{
    $back = "index.php";
}
?>
<ul class='nav main'>
  <li><br/>Als normaler User
    <ul class='nav'>
      <li><a href="prototype_hw_lcd.php?logbox_mac=<?php echo $_SESSION['mac_address']; ?> ">Erfassen</a></li>
      <li><a href="entries.php">Auswertungen</a></li>
    </ul>
  </li>
  <li><br/>Als Administrator<span> !</span>
    <ul class='nav'>
      <li><a href="clients.php">Kunden</a></li>
      <li><a href="projects.php">Projekte</a></li>
      <li><a href="user.php">User</a></li>
      <li><a href="logboxes.php">Logging Devices</a></li>
      <li><a href="categories.php">Arbeitskategorien</a></li>
      <li><a href="entries.php?show_as_admin=1">Auswertungen</a></li>
    </ul>
  </li>
  <li><br/><a href="logout.php">Logout</a></li>
</ul>

<?php
include_once("common/foot.php");
?>
