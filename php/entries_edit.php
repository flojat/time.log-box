<?php

include_once("common/php_header.php");
include_once("common/html_header.php");

  if( isset($_POST['submit'])){ $submit = $_POST['submit']; }else{ $submit = ''; }

   if( isset($_POST['back']) ){ $back = $_POST['back']; }else{$back = $_GET['back'];}

   if( isset($_POST['id'] )){ $id = $_POST['id']; }else{ $id = $_GET['id'];}




  //"SELECT `id`,`logbox_id`,`project_id`,`employee_id`,`start`,`stop`,`notes` FROM `entries` WHERE 1"
 /*
  $query=mysql_query("SELECT `id`,`logbox_mac`,`project_id`,`employee_id`,`start`,`stop`,`notes`
                      FROM `entries` entr,`projects` pro
                      WHERE pro.id = project_id AND
                      order by `$sort` desc ");
   */
//submit = save
if($submit == "Save"){

    $start = $_POST['start'];
    $notes = $_POST['notes'];
    $sqlQuery="";

    if( isset($_POST['stop']) ){
        $stop = $_POST['stop'];
        if( $stop == "NULL" || $stop == "" ){
          $sqlQuery = "UPDATE  entries set start=\"$start\", stop=NULL, notes=\"$notes\"  WHERE entries.id='$id' ";
        }else{
          $sqlQuery = "UPDATE  entries set start=\"$start\", stop=\"$stop\",notes=\"$notes\"  WHERE entries.id='$id' ";
        }

    }else{  $sqlQuery = "UPDATE  entries set start=\"$start\" ,notes=\"$notes\"  WHERE entries.id='$id' ";

    }

   // print "<p>".$sqlQuery."</p>";

    if( $query=mysql_query($sqlQuery)){
        header("Location: ".$back);
    }else{
        echo mysql_error();
    }

}else if($submit == "Delete"){

    $sqlQuery = "DELETE FROM entries WHERE entries.id='$id' limit 1 ";
    if( $query=mysql_query($sqlQuery)){
        header("Location: ".$back);
    }else{
        echo mysql_error();
    }

}else{

  $query=mysql_query("  SELECT entries.id,  logbox_mac, projects.name project, concat(user.firstname,' ', user.name) employee ,start,stop, TIMEDIFF(stop,start) worked,notes
                        FROM entries,projects,user
                        WHERE entries.id='$id' AND projects.id = entries.project_id AND entries.user_id = user.id");

  echo mysql_error();

  if(@mysql_num_rows($query)>0){

  print "<h2>Bearbeite Datensatz</h2>\n";

  print "<form method=post action=\"".$_SERVER['SCRIPT_NAME']."\" name=\"entry_form\">
          <input type=\"hidden\" name=\"back\" id=\"back\" value=\"$back\">";

  print  "<div id='edit_table' class='table edit'>";

          list($id,$logbox_mac,$project_id,$employee_id,$start,$stop,$worked,$notes)=mysql_fetch_row($query);
          $bgcolor=$_config_tbl_bgcolor1;

            print "<div class='trh'>
                      <div class='td'>
                          <input readonly name=\"id\" size=\"20\" type=\"text\" value=\"$id\" />
                          <input readonly name=\"logbox_mac\" size=\"20\" type=\"text\" value=\"$logbox_mac\" />
                      </div>
                    </div>
                    <div class='trh'>
                      <div class='td'>
                          <input readonly name=\"project_id\" size=\"20\" type=\"text\" value=\"$project_id\" />
                          <input readonly name=\"employee_id\" size=\"20\" type=\"text\" value=\"$employee_id\" />
                      </div>
                    </div>
                    <div class='trh'>
                      <div class='td'>
                          <input name=\"start\" size=\"20\" type=\"text\" value=\"$start\" />
                          <input name=\"stop\" size=\"20\" type=\"text\" value=\"$stop\" />
                      </div>
                    </div>
                    <!-- div class='trh'>
                      <div class='td'><input readonly name=\"worked\" size=\"20\" type=\"text\" value=\"$worked\" />
                          <input title=\"Zurück\" class=\"button\" name=\"backButton\" type=\"button\" value=\"&lt;-\" onClick=\"window.location.href='$back'\" />
                          <input title=\"Löschen\" class=\"button\" id=\"delButton\" name=\"submit\" type=\"submit\" value=\"X\" disabled=\"disabled\" />
                          <input type=\"checkbox\" class=\"checkbox\" name=\"disableDeletButton\" value=\"\" id=\"disableDeletButton\" onclick=\"if (!$(this).is(':checked')) { $('#delButton').prop('disabled', true);}else{ $('#delButton').prop('disabled', false);}\" >
                          <input title=\"Speichern\" class=\"button\" name=\"submit\" type=\"submit\" value=\"ok\" />
                      </div>
                    </div -->
                    <div class='trh'>
                      <div class='td' valign=top><textarea name=\"notes\" rows=\"2\" cols=\"30\" >$notes</textarea></div>
                    </div>
                    <div class='trh'>
                      <div class='td' valign=top>
                        <input title=\"Zurück\" class=\"button\" name=\"backButton\" type=\"button\" value=\"&lt;- Back\" onClick=\"window.location.href='$back'\" />
                        &nbsp;<input title=\"Löschen\" class=\"button\" id=\"delButton\" name=\"submit\" type=\"submit\" value=\"Delete\" disabled=\"disabled\" />
                        <input type=\"checkbox\" class=\"checkbox\" name=\"disableDeletButton\" value=\"\" id=\"disableDeletButton\" onclick=\"if (!$(this).is(':checked')) { $('#delButton').prop('disabled', true);}else{ $('#delButton').prop('disabled', false);}\" >
                        &nbsp;<input title=\"Speichern\" class=\"button\" name=\"submit\" type=\"submit\" value=\"Save\" />
                      </div>
                    </div>
            </div>
        </form>";
        /*
            print "<div class='trh'>
                      <div class='td'><input readonly name=\"id\" size=\"15\" type=\"text\" value=\"$id\" /></div>
                      <div class='td'><input readonly name=\"logbox_mac\" size=\"15\" type=\"text\" value=\"$logbox_mac\" /></div>
                      <div class='td'><input readonly name=\"project_id\" size=\"15\" type=\"text\" value=\"$project_id\" /></div>
                      <div class='td'><input readonly name=\"employee_id\" size=\"15\" type=\"text\" value=\"$employee_id\" /></div>
                      <div class='td'><input name=\"start\" size=\"15\" type=\"text\" value=\"$start\" /></div>
                      <div class='td'><input name=\"stop\" size=\"15\" type=\"text\" value=\"$stop\" /></div>
                      <div class='td'><input readonly name=\"worked\" size=\"15\" type=\"text\" value=\"$worked\" /><br>
                          <input title=\"Zurück\" class=\"button\" name=\"backButton\" type=\"button\" value=\"&lt;-\" onClick=\"window.location.href='$back'\" />
                          <input title=\"Löschen\" class=\"button\" id=\"delButton\" name=\"submit\" type=\"submit\" value=\"X\" disabled=\"disabled\" />
                          <input type=\"checkbox\" class=\"checkbox\" name=\"disableDeletButton\" value=\"\" id=\"disableDeletButton\" onclick=\"if (!$(this).is(':checked')) { $('#delButton').prop('disabled', true);}else{ $('#delButton').prop('disabled', false);}\" >
                          <input title=\"Speichern\" class=\"button\" name=\"submit\" type=\"submit\" value=\"ok\" />
                      </div>
                      <div class='td' valign=top bgcolor=\"#$bgcolor\" $style><textarea name=\"notes\" rows=\"2\" cols=\"30\" >$notes</textarea></div>
                  </div>
            </div>
        </form>";
*/
    }else{
      print "<b>no entries found</b><br><br>";
    }
}

include_once("common/foot.php");
?>
