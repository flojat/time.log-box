<?php
include_once("common/php_header.php");
include_once("common/html_header.php");

  $valuesToSave = date("Y-m-j H:i:s");
  $logFileName = "";
  $displayValue=date("d.m.Y H:i",time());

  foreach($_POST as $key => $value){
    if ($key == 'logfilename'){
        $logFileName = "$value.csv";
    } else{
        $valuesToSave .= ",$value($key)";
    }
  }
  $valuesToSave .= ",{$_SERVER['REMOTE_ADDR']}";
//log request with filename
  if ($logFileName != ""){
    if(!($datei = fopen("logs/".$logFileName,"a"))){
      //echo("geht nicht");
    }else{
     fwrite($datei,"$valuesToSave\n");
     fclose($datei);
    }
  }

/*******************************************************************************
1) Speichere werte aus Request in Variablen;
///phplogbox/time.logbox.php?logfilename=time.logbox.ch.csv&logbox_mac=38.74.17.190&p4=0&p3=0&p2=0&p1=1
*/


if (isset($_REQUEST['logbox_mac']) && $_REQUEST['logbox_mac'] !=""){
    //falls über Web-Protoyp könte auch jene aus der Session verwendet werden!!!!!!!
    $logbox_mac = $_REQUEST['logbox_mac'];
}else{
    $logbox_mac = '';
}

if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] !=""){
    $logbox_ip = $_SERVER['REMOTE_ADDR'];
}else{
    $logbox_ip = 'empty?';
}

if (isset($_POST['selected_proj_id']) && $_POST['selected_proj_id'] !=""){
    $selected_proj_id = $_POST['selected_proj_id'];
}else{
    $selected_proj_id = '';
}


//Wem ist die Logbox zur zeit zugeordnet?
// Diese Info fehlt bei einem request ab der Hardware log-box!!
$sqlquery=" SELECT `user_id` FROM `logboxes` WHERE `mac_address` = '{$logbox_mac}' ";
//echo $sqlquery;
$query=mysql_query($sqlquery);
echo mysql_error();
if(@mysql_num_rows($query)>0){
  list($employee_id)=mysql_fetch_row($query);
}


//Falls ein selected_proj_id vorhanden, wurde ein Button auf der Box gedrückt
if($selected_proj_id!=''){

      //falls nicht Standby; welche Proj_id wurde dem Button Zugeordnet?
      if($selected_proj_id >= 0){
          $button = "button_{$selected_proj_id}";
          $sqlquery=" SELECT `{$button}` FROM `logboxes` WHERE `mac_address` = \"$logbox_mac\" ";
          //echo $sqlquery;
          $query=mysql_query($sqlquery);
          echo mysql_error();
          if(@mysql_num_rows($query)>0){
            list($proj_id)=mysql_fetch_row($query);
          }
      }

      //hole den letzten Eintrag der gleichen logbox_mac und employee_id
      $sqlquery="SELECT `id`,`logbox_mac`,`project_id`,`user_id`,`start`,`stop`,`notes` FROM `entries`
                 WHERE `logbox_mac` = \"$logbox_mac\" AND `user_id` = \"$employee_id\" order by `id` desc ";
      //echo $sqlquery;
      $query=mysql_query($sqlquery);
      echo mysql_error();
      if(@mysql_num_rows($query)>0){
        list($id,$logged_logbox_mac,$logged_project_id,$logged_employee_id,$start,$stop,$notes)=mysql_fetch_row($query);
      }

      //Falls Stop leer ist, wird der ggf vorhandene vormalige Datensatz geschlossen
      if($stop==""){
        $query=mysql_query("UPDATE `entries` set `stop` = NOW() where `id`= $id ");
        echo mysql_error();
      }


      if($selected_proj_id >= 0){
        //Falls das Projekt nicht -1 = "Standby" = Taste 9 ist wird ein neuer Eintrag angelegt
        $sqlquery="INSERT INTO `entries` (`logbox_mac`,`logbox_ip`,`project_id`,`user_id`,`start`) values(\"$logbox_mac\", \"$logbox_ip\", $proj_id, $employee_id, NOW())";
        //echo $sqlquery;
        $query=mysql_query($sqlquery);
        echo mysql_error();
      }
}

// Displaanzeige setzen: Falls offener Eintrag das Projekt anzeigen,
// falls alle Einträge geschlossen Standby anzeigen
//hole den letzten Eintrag der gleichen logbox_mac und employee_id
    $sqlquery="SELECT `project_id`,DATE_FORMAT(start, '%m.%d  %H:%i'),DATE_FORMAT(stop, '%m.%d  %H:%i') FROM `entries`
               WHERE `logbox_mac` = \"$logbox_mac\" AND `user_id` = \"$employee_id\" order by `id` desc ";
    //echo $sqlquery;
    $query=mysql_query($sqlquery);
    echo mysql_error();
    if(@mysql_num_rows($query)>0){
      list($logged_project_id,$start,$stop)=mysql_fetch_row($query);
    }

    if($stop != ''){
        $display_time = $stop;
        $display_projectname ='... in Standby';
    }else{
        $display_time = $start;
        //hole projektname
        $query=mysql_query(" SELECT name FROM projects WHERE projects.id = '{$logged_project_id}' ");
        if(@mysql_num_rows($query)>0){
           list($display_projectname) = mysql_fetch_row($query);
        }
        //nice String; max 17, but if a spce between 13 & 17 take space to cut
        if(strrpos ( $display_projectname , ' ' ,13) != FALSE && strrpos ( $display_projectname , ' ' ,13) <= 17 ){
        echo strrpos ( $display_projectname , ' ' ,13);
           $display_projectname = substr ( $display_projectname , 0, strrpos ( $display_projectname , ' ' ,13) );
        }else{
           $display_projectname = substr ( $display_projectname , 0, 17);
        }
    }
 $displayValue= "<div>".$display_projectname."<br>seit ".$display_time."</div>";


 //hole alle Projekt-IDs von der Logbox
 $sqlquery="SELECT button_0, button_1, button_2, button_3, button_4, button_5, button_6, button_7, button_8
                     FROM logboxes
                     WHERE mac_address = '{$_SESSION['mac_address']}' ";
 $query=mysql_query($sqlquery);
 $pids_of_logbox = mysql_fetch_row($query);
 for ($i = 0; $i < count($pids_of_logbox); ++$i) {
   $msqlquery = "SELECT name FROM projects WHERE projects.id = $pids_of_logbox[$i] ";
   //echo $msqlquery
   $query=mysql_query($msqlquery);
   $buttons[$i] = mysql_fetch_row($query);
  }
    print "<div class='main'>";
    include_once("common/user-top-nav.php");
    print "<div class='logbox lcd' ><div class='info'>$displayValue</div></div>";
    print "<div class='logbox' >
            <form method=post action=\"".$_SERVER['SCRIPT_NAME']."\" name=\"logbox_form\">
            <input type=\"hidden\" name=\"logfilename\" value=\"prototype_hw_lcd.php.userId_{$_SESSION['user_id']}.log\">
            <input type=\"hidden\" name=\"logbox_mac\" value=\"{$_SESSION['mac_address']}\">";

              for($i=0; $i<count($buttons) ;$i++) {

                //nice String; max 19, but if a spce between 15 & 19 take space to cut
                if(strrpos ( $display_projectname , ' ' ,13) != FALSE && strrpos ( $display_projectname , ' ' ,13) <= 17 ){
                    $projektname = substr ( $buttons[$i][0] , 0, strrpos ( $buttons[$i][0] , ' ' ,13) );
                } else{
                    $projektname = substr ( $buttons[$i][0] , 0, 17);
                }
               //print "<input title=\"Projekt $i\" class=\"button\" name=\"selected_proj_id\" type=\"submit\" value=\"$i\" />";
                print "<button  title=\"Projekt: {$buttons[$i][0]}\"
                                class=\"project_button\" name=\"selected_proj_id\"
                                value=\"$i\" />";
                print        ($projektname !="") ? $projektname : '&nbsp;' ;
                print "</button>";
              }
              print "<button  title=\"Go to Standby\"
                              class=\"project_button\" name=\"selected_proj_id\"
                              value=\"-1\" />std-by</button>";
    print "</form>
          </div></div>";

/*
Fälle:
Starteintrag
- alle des gleichen Projektes geschlossen

Stopeintrag
- einer des gleichen Projektes noch offen



Letzte EInträge holen (mit "DESC")
****
SELECT MAX(ID) FROM tabelle_namen

SELECT xy FROM tabelle
ORDER BY datum DESC
LIMIT 1

*/

 /*

  $query=mysql_query("SELECT `id`,`logbox_id`,`project_id`,`employee_id`,`start`,`stop`,`notes` FROM `entries` WHERE 1  order by `$sort` desc ");
  echo mysql_error();

  if(@mysql_num_rows($query)>0){
    list($id,$logbox_id,$project_id,$employee_id,$start,$stop,$notes)=mysql_fetch_row($query);$i++)
    }


$query=mysql_query("INSERT INTO `entries` (`logbox_id`,`project_id`,`employee_id`,`start`,`stop`) values ('$domain','$aliase','$kontakt','".date_CH_to_EN($startDate)."','$betrag','$waehrung','$abrechnungsart','$text')");

$domain_id=mysql_insert_id();

if(!($error=mysql_error())) {
}

 */

/*
 //berechne Differensz zwischen zwei Seitspemel in Sekunden
    $query=mysql_query("SELECT TIME_TO_SEC(TIMEDIFF(stop,start))");
    echo mysql_error();
    if(@mysql_num_rows($query)>0){
      list($seconds)=mysql_fetch_row($query);
    }
*/

//log all request
if( isset($_GET['debug'] ) && $_GET['debug'] == 'true'){
?>
<html>
  <head>
      <meta http-equiv="content-type" content="text/html; charset=utf-8">
      <title>logbos</title>
      <link rel="stylesheet" href="../web/format.css" type="text/css">
      <link rel="stylesheet" href="../default.css" type="text/css">
      <script type="text/javascript" src="fckeditor/fckeditor.js"></script>
  </head>
    <body>
        <div id="request">
          <?php
            print "<pre>";
            print print_r($_GET);
            print "<br/><br/>";
            print  print_r($_REQUEST);
            print "<br/><br/>";
            print $_SERVER["QUERY_STRING"];
            print "<br/><br/>";
            print var_dump($_SERVER);
            print "</pre>";
          ?>
        </div>
    </body>
</html>
<?php
    }
include_once("common/foot.php");
?>
