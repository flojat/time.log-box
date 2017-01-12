<?php


//http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."
include_once("common/php_header.php");
include_once("common/html_header.php");

if (isset($_GET['sort']) && $_GET['sort'] !=""){
  $sort=$_GET['sort'];
}else{
  $sort = "id";
}

if (isset($_REQUEST['show_as_admin']) && $_REQUEST['show_as_admin'] !=""){
  $_SESSION['show_as_admin'] = TRUE;
}

  //"SELECT `id`,`logbox_id`,`project_id`,`employee_id`,`start`,`stop`,`notes` FROM `entries` WHERE 1"
 /*
  $query=mysql_query("SELECT `id`,`logbox_mac`,`project_id`,`employee_id`,`start`,`stop`,`notes`
                      FROM `entries` entr,`projects` pro
                      WHERE pro.id = project_id AND
                      order by `$sort` desc ");


select `project_id`, `employee_id`, sum(TIME_TO_SEC(TIMEDIFF(stop,start)))/60/60 worked
from entries
where 1
group by `project_id`


Tagesrapport:

select projects.name Kunde, concat(employees.firstname,' ',employees.name ) MA, ROUND(sum(TIME_TO_SEC(TIMEDIFF(stop,start)))/60/60, 2) h, DATE(NOW()) Tag
from entries, projects, employees
where entries.project_id = projects.id and  employee_id = employees.id and date(start) = DATE(NOW())
group by `project_id`

 display_db_table($table, $global_dbh,TRUE, "border='2'");

   */
  //print "<p>=".$_POST['employees_id']."</p>";


  if (isset($_POST['employees_id']) && !($_POST['employees_id'] == "all") ){
    $selected_employees_id="'".$_POST['employees_id']."'";
  }else{
    $selected_employees_id = "user.id";
  }

  if (isset($_POST['project_id']) && !($_POST['project_id'] == "all") ){
    $selected_project_id="'".$_POST['project_id']."'";
  }else{
    $selected_project_id = "projects.id";
  }


  if (isset($_POST['beginn_date'])){
    $sql_beginn_date="'".$_POST['beginn_date']."'";
    $beginn_date = $_POST['beginn_date'];
  }else{
    $sql_beginn_date = " DATE(NOW()) ";
    $beginn_date =  date("Y-m-d");
  }

  if (isset($_POST['end_date'])){
    $sql_end_date="'".$_POST['end_date']."'";
    $end_date = $_POST['end_date'] ;
  }else{
    $sql_end_date = " DATE(NOW()) ";
    $end_date =  date("Y-m-d");
    //$sql_end_date = " DATE_ADD(DATE(NOW()),INTERVAL 1 DAY ) ";
    //$end_date =  date("Y")."-".date("m")."-".(date("d")+1);
  }

  if (isset($_POST['search_text']) && !($_POST['search_text'] == "") ){
    $search_text=$_POST['search_text'];
    $search_text_query_line = " AND notes like '%".str_replace("'","",$search_text)."%' ";
  }else{
    $search_text='';
    $search_text_query_line="";
  }

  include_once("common/user-top-nav.php");

  print "<div id='selectarea'>\n
        <form method=post action=\"{$_SERVER['SCRIPT_NAME']}\" name=\"entry_form\">\n";



              if(isset($_SESSION['show_as_admin']) && $_SESSION['show_as_admin']){
                print "<select name=\"employees_id\" onchange=\"this.form.submit()\" >\n";
                    print "<option value=\"all\">Alle Mitarbeitenden</option>\n";
                    $query=mysql_query("  SELECT id, concat(firstname, ' ',name) employee
                                          FROM user
                                          WHERE 1 ");
                    echo mysql_error();
                    if(@mysql_num_rows($query)>0){
                        for($i=0;list($employees_id,$employee_name)=mysql_fetch_row($query);$i++) {
                            if("'".$employees_id."'" == $selected_employees_id){
                                print "<option selected=selected value=\"$employees_id\">$employee_name</option>\n";
                            }else{
                                print "<option value=\"$employees_id\">$employee_name</option>\n";
                            }
                        }
                    }
                print "</select>\n";
              }else{
                print "<input type='hidden' name=\"employees_id\" value=\"{$_SESSION['user_id']}\"/>\n";
              }



          print "<select name=\"project_id\" onchange=\"this.form.submit()\" >\n";
          print "<option value=\"all\">Alle Projekte</option>\n";

          //if(isset($_SESSION['show_as_admin']) && $_SESSION['show_as_admin']){
              $sqlquery= "SELECT DISTINCT projects.id, projects.name FROM projects, projects_has_user, user
                          WHERE isactive=1
                          AND projects.id = projects_has_user.projects_id
                          AND projects_has_user.user_id = user.id
                          AND $selected_employees_id = user.id ";
              //echo $sqlquery;
              $query=mysql_query($sqlquery);
        //  }else{
        //    $query=mysql_query("SELECT id, name FROM projects WHERE isactive=1 ");
      //    }
              echo mysql_error();
              if(@mysql_num_rows($query)>0){
                  for($i=0;list($project_id,$project_name)=mysql_fetch_row($query);$i++) {
                    if("'".$project_id."'" == $selected_project_id){
                        print "<option selected=selected value=\"$project_id\">$project_name</option>\n";
                    }else{
                        print "<option value=\"$project_id\">$project_name</option>\n";
                    }
                  }
              }
          print "</select>\n";

          print "<label>von </label><input id=\"datepickerFrom\" name=\"beginn_date\" size=\"12\" type=\"text\" value=\"$beginn_date\" onchange=\"this.form.submit()\"/>\n";

          print "<label>bis </label><input id=\"datepickerTo\" name=\"end_date\" size=\"12\" type=\"text\" value=\"$end_date\" onchange=\"this.form.submit()\"/>\n";

          print "<label>txt</label><input id=\"search_text\" name=\"search_text\" size=\"12\" type=\"text\" value=\"$search_text\" onchange=\"this.form.submit()\"/>\n";

          print "<input title=\"Reset\" class=\"button\" name=\"resetButton\" type=\"button\" value=\"Reset\" onClick=\"window.location.href='".$_SERVER['SCRIPT_NAME']."'\" />";

  print "</form></div>\n";

  //print "<p>$search_text_query_line</p>";


//$query_string= "select DATE(start) Tag, projects.name Kunde, concat(employees.firstname,' ',employees.name ) MA, ROUND(sum(TIME_TO_SEC(TIMEDIFF(stop,start)))/60/60, 2) h
$query_string= "select projects.name Kunde, concat(user.firstname,' ',user.name ) MA, ROUND(sum(TIME_TO_SEC(TIMEDIFF(stop,start)))/60/60, 2) h
                  from entries, projects, user
                  WHERE entries.project_id = $selected_project_id
                  AND entries.project_id = projects.id
                  AND user.id = user_id
                  AND user.id = $selected_employees_id
                  AND date(start) BETWEEN $sql_beginn_date AND $sql_end_date
                  $search_text_query_line
                  group by `project_id`, user.id";
        //          group by `project_id`, employees.id, Tag";

$query_string2= " select  '' '<b>Total Stunden</b>', concat(user.firstname,' ',user.name ) '', ROUND(sum(TIME_TO_SEC(TIMEDIFF(stop,start)))/60/60, 2) h
                  from entries, projects, user
                  WHERE entries.project_id = $selected_project_id
                  AND entries.project_id = projects.id
                  AND user.id = user_id
                  AND user.id = $selected_employees_id
                  AND date(start) BETWEEN $sql_beginn_date AND $sql_end_date
                  $search_text_query_line
                  group by user.id";

 //print"<p>$query_string</p>";
 print "<div id=\"tagesrapport\">";
 print "<h2>Rapport</h2>";
    display_db_query($query_string, TRUE, "class='entries'");
    display_db_query($query_string2, FALSE, "class='entries' style='margin-top:1em;' ");


 print "</div>";


$query_string=" SELECT entries.id ,logbox_mac,projects.name project,concat(user.firstname,' ', user.name) employee ,Date(start),Time(start),Time(stop), TIMEDIFF(stop,start) worked,notes
                FROM entries,projects,user
                WHERE entries.project_id = $selected_project_id
                AND entries.project_id = projects.id
                AND user.id = entries.user_id
                AND entries.user_id = $selected_employees_id
                AND date(start) BETWEEN $sql_beginn_date AND $sql_end_date
                $search_text_query_line
                ORDER BY `$sort` desc ";

/*
                 AND notes like '%".$search_text str_replace("*","%",$search_text)."%'
                $search_text

*/

//print"<p>$query_string</p>";
$query=mysql_query($query_string);
echo mysql_error();

/*
 print "<h2>Erfasste Zeiteinheiten</h2>";
 print "<div id='entries_table' class='table tasklist'>";

  if(@mysql_num_rows($query)>0){
          print "<div class='trh'>
                  <!-- div class='td'><a href=\"$PHP_SELF?sort=id\" title='Sortieren nach ID'>ID</a></b></div>
                  <div class='th'><a href=\"$PHP_SELF?sort=logbox_mac\" title='Sortieren nach logbox_mac' >logbox_mac</a></div -->
                  <div class='th'><a href=\"$PHP_SELF?sort=project_id\" title='Sortieren nach project_id' >project_id</a></div>
                  <div class='th'><a href=\"$PHP_SELF?sort=employee_id\" title='Sortieren nach employee_id' >employee_id</a></div>
                  <div class='th'><a href=\"$PHP_SELF3#\" title='Sortieren nach start' >Datum</a></div>
                  <div class='th'><a href=\"$PHP_SELF?sort=start\" title='Sortieren nach start' >start</a></div>
                  <div class='th'><a href=\"$PHP_SELF?sort=stop\" title='Sortieren nach stop' >stop</a></div>
                  <div class='th'><a href=\"$PHP_SELF?sort=worked\" title='Sortieren nach worked' >worked</a></div>
                  <div class='th'><a href=\"$PHP_SELF?sort=notes\" title='Sortieren nach notes' >notes</a></div>
                </div>\n";
                for($i=0;list($id,$logbox_mac,$project_id,$employee_id,$date,$start,$stop,$worked,$notes)=mysql_fetch_row($query);$i++) {
                    print "<div class='tr' title='klicken um zu bearbeiten'  onclick=\"location.href='edit_entry.php?id=$id&back=http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."'\">
                            <!-- div class='td'>$id</div>
                            <div class='td nowrap'>$logbox_mac</div -->
                            <div class='td nowrap'>$project_id</div>
                            <div class='td nowrap'>$employee_id</div>
                            <div class='td'>$date</div>
                            <div class='td'>$start</div>
                            <div class='td'>$stop</div>
                            <div class='td'>$worked</div>
                            <div class='td'>$notes</div>
                          </div>";
                }
                print "</div>";
}
else{
        print "<b>no entries found</b><br><br>";
}
*/

    print "<div id='stundenrapport'>";

    if(@mysql_num_rows($query)>0){
      print "<h2>Erfasste Zeit:</h2>
            <table class='entries'>
                <tr>
                    <!-- th><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=id\" title='Sortieren nach ID'>ID</a></b></td>
                    <th><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=logbox_mac\" title='Sortieren nach logbox_mac' >logbox_mac</a></th -->
                    <th><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=project_id\" title='Sortieren nach project_id' >project_id</a></th>
                    <th><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=user_id\" title='Sortieren nach employee_id' >employee_id</a></th>
                    <th><a href=\"{$_SERVER['SCRIPT_NAME']}#\" title='Sortieren nach Datum' >Datum</a></th>
                    <th><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=start\" title='Sortieren nach start' >start</a></th>
                    <th><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=stop\" title='Sortieren nach stop' >stop</a></th>
                    <th><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=worked\" title='Sortieren nach worked' >worked</a></th>
                    <th><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=notes\" title='Sortieren nach notes' >notes</a></th>
                  </tr>\n";
                  for($i=0;list($id,$logbox_mac,$project_id,$employee_id,$date,$start,$stop,$worked,$notes)=mysql_fetch_row($query);$i++) {
                      print "<tr style=\"cursor:pointer;\" title='klicken um zu bearbeiten'  onclick=\"location.href='entries_edit.php?id=$id&back=http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."'\">
                              <!-- td>$id</td>
                              <td>$logbox_mac</td -->
                              <td>$project_id</td>
                              <td>$employee_id</td>
                              <td>$date</td>
                              <td>$start</td>
                              <td>$stop</td>
                              <td>$worked</td>
                              <td>$notes</td>
                            </tr>";
                  }
                  print "</table>";
    }
    else{
          print "<b>no entries found</b><br><br>";
    }
    print "</div>";
print "</div>";

include_once("common/foot.php");
