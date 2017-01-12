<?php

include_once("common/php_header.php");
include_once("common/html_header.php");

if (isset($_GET['back']) && $_GET['back'] !=""){
    $back = $_GET['back'];
}else{
    $back = "index.php";
}
//Falls Form gesendet wird gibts ein Update oder ein Insert oder ein Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] =="Save") {

    if(isset($_POST['id']) && $_POST['id'] !=""){
      $sqlquery="UPDATE `logboxes`
                  set `mac_address`='{$_POST['mac_address']}',
                      `user_id`={$_POST['user_id']} ,
                      `button_0`={$_POST['button_0']} ,
                      `button_1`={$_POST['button_1']} ,
                      `button_2`={$_POST['button_2']} ,
                      `button_3`={$_POST['button_3']} ,
                      `button_4`={$_POST['button_4']} ,
                      `button_5`={$_POST['button_5']} ,
                      `button_6`={$_POST['button_6']} ,
                      `button_7`={$_POST['button_7']} ,
                      `button_8`={$_POST['button_8']}
                   WHERE `id` = {$_POST['id']}
                   LIMIT 1 ";
        $query=mysql_query($sqlquery);
        echo mysql_error();

    }else{
      $sqlquery="INSERT INTO `logboxes` (mac_address,user_id,button_0,button_1,
                              button_2, button_3,button_4,button_5,button_6,
                              button_7,button_8)
                 values('{$_POST['mac_address']}',{$_POST['user_id']},
                        {$_POST['button_0']},{$_POST['button_1']},{$_POST['button_2']},
                        {$_POST['button_3']},{$_POST['button_4']},{$_POST['button_5']},
                        {$_POST['button_6']},{$_POST['button_7']},{$_POST['button_8']})";
        $query=mysql_query($sqlquery);
        echo mysql_error();

    }

}else if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] =="Delete" ){
  $query=mysql_query("DELETE from `logboxes` WHERE `id` = {$_POST['id']} LIMIT 1 ");
  echo mysql_error();
}

/*-----------------------------------------------------------------------------
- Form zum erfassen einer enuen oder bearbeiten einer bestehenden log-box -----
-----------------------------------------------------------------------------*/
//falls eine ID gesendet wird soll die LogBox bearbeitet werden können(onklick in Tabellenzeile)
if (isset($_GET['id']) && $_GET['id'] !=""){
    $id = $_GET['id'];
    $query=mysql_query("SELECT `id`, `mac_address`, `user_id`, `button_0`, `button_1`, `button_2`, `button_3`, `button_4`, `button_5`, `button_6`, `button_7`, `button_8`
                        FROM `logboxes` WHERE `id` = $id ;");
    echo mysql_error();
    list($id,$mac_address,$user_id,$button_0,$button_1,$button_2,$button_3,$button_4,$button_5,$button_6,$button_7,$button_8)=mysql_fetch_row($query);
}
$id = isset($id) ? $id : '';
$mac_address = isset($mac_address) ? $mac_address : '';

print "<form method=post action='{$_SERVER['SCRIPT_NAME']}' name='entry_form'>";
print "<div id='edit_table' class='table edit'>";
print "<div class='trh'>
           <div class='td'>";
print "        <label>id</label><input readonly name='id' type='text'value='{$id}'/>";
print "       <label>mac</label><input name='mac_address' type='text' value='{$mac_address}'/>
           </div>
       </div>";

print "<div class='trh'>
           <div class='td'>";
print         getUserList("user_id", isset($user_id) ? $user_id : '');
print "     </div>
       </div>";


print "<div class='trh'>
            <div class='td'>";
print         getProjectList("button_0", isset($button_0) ? $button_0 : '');
print "     </div>
        </div>";

print "<div class='trh'>
            <div class='td'>";
print         getProjectList("button_1", isset($button_1) ? $button_1 : '');
print "     </div>
        </div>";

print "<div class='trh'>
            <div class='td'>";
print         getProjectList("button_2", isset($button_2) ? $button_2 : '');
print "     </div>
        </div>";

print "<div class='trh'>
            <div class='td'>";
print         getProjectList("button_3", isset($button_3) ? $button_3 : '');
print "     </div>
        </div>";

print "<div class='trh'>
            <div class='td'>";
print         getProjectList("button_4", isset($button_4) ? $button_4 : '');
print "     </div>
        </div>";

print "<div class='trh'>
            <div class='td'>";
print         getProjectList("button_5", isset($button_5) ? $button_5 : '');
print "     </div>
        </div>";

print "<div class='trh'>
            <div class='td'>";
print         getProjectList("button_6", isset($button_6) ? $button_6 : '');
print "     </div>
        </div>";

print "<div class='trh'>
            <div class='td'>";
print         getProjectList("button_7", isset($button_7) ? $button_7 : '');
print "     </div>
        </div>";

print "<div class='trh'>
            <div class='td'>";
print         getProjectList("button_8", isset($button_8) ? $button_8 : '');
print "     </div>
        </div>";

print "<div class='trh'>
         <div class='td' valign=top>
           <input title='Zurück' class='button' name='backButton' type='button' value='&lt;- Back' onClick=\"window.location.href='{$back}'\" />
           &nbsp;<input title='Löschen' class='button' id='delButton' name='submit' type='submit' value='Delete' disabled='disabled' />
           <input type='checkbox' class='checkbox' name='disableDeletButton' value='' id='disableDeletButton' onclick=\"if (!$(this).is(':checked')) { $('#delButton').prop('disabled', true);}else{ $('#delButton').prop('disabled', false);}\" >
           &nbsp;<input title='Speichern' class='button' name='submit' type='submit' value='Save' />
         </div>
      </div>
  </div><!-- end of edit_table -->
</form>";




/*-----------------------------------------------------------------------------
- Tabellendarstellung der erfassten Logboxen ----------------------------------
-----------------------------------------------------------------------------*/


  if (isset($_GET['sort']) && $_GET['sort'] !=""){
    $sort=$_GET['sort'];
  }else{
    $sort = "id";
  }


  //"SELECT `id`, `logbox_mac`, `employee_id`, `project_0_id`, `project_1_id`, `project_2_id`, `project_3_id`, `project_4_id`, `project_4_id`, `project_5_id` FROM `time_logboxes` WHERE 1 "

  $query=mysql_query("SELECT `id`, `mac_address`, `user_id`, `button_0`, `button_1`, `button_2`, `button_3`, `button_4`, `button_5`, `button_6`, `button_7`, `button_8`
                      FROM `logboxes` WHERE 1 order by `$sort` desc ");


  echo mysql_error();

  if(@mysql_num_rows($query)>0){
    print "<p><b>Erfasste time.log-box</b></p>
          <table class='tasklist' width=\"800px\" border=0 cellpadding=2 cellspacing=0>
              <tr>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=id\" title='Sortieren nach ID'>ID</a></b></td>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=mac_address\" title='Sortieren nach mac_address' >mac_address</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=user_id\" title='Sortieren nach user_id' >user_id</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=button_0\" title='Sortieren nach button_0' >button_0</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=button_1\" title='Sortieren nach button_1' >button_1</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=button_2\" title='Sortieren nach button_2' >button_2</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=button_3\" title='Sortieren nach button_3' >button_3</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=button_4\" title='Sortieren nach button_4' >button_4</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=button_5\" title='Sortieren nach button_5' >button_5</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=button_6\" title='Sortieren nach button_6' >button_6</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=button_7\" title='Sortieren nach button_7' >button_7</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=button_8\" title='Sortieren nach button_8' >button_8</a></b></th>
                </tr>\n";

                for($i=0;list($id,$logbox_mac,$employee_id,$button_0,$button_1,$button_2,$button_3,$button_4,$button_5,$button_6,$button_7,$button_8)=mysql_fetch_row($query);$i++) {

                    if(($i%2)==0){ $bgcolor=$_config_tbl_bgcolor1;
                    }else{         $bgcolor=$_config_tbl_bgcolor2;}

                  // time() - strtotime(date_CH_to_EN($faellig))) > 0

                    print "<tr onmouseover=\"setPointer(this, 'over', '#$bgcolor', '#$_config_tbl_bghover', '')\" onmouseout=\"setPointer(this, 'out', '#$bgcolor', '#$_config_tbl_bghover', '')\" onclick=\"location.href='{$_SERVER['SCRIPT_NAME']}?id=$id'\">
                            <td width=5 valign=top bgcolor=\"#$bgcolor\" >$id</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$logbox_mac</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$employee_id</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$button_0</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$button_1</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$button_2</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$button_3</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$button_4</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$button_5</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$button_6</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$button_7</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$button_8</td>
                          </tr>";
                }
                print "</table><br>";
}
else{
        print "<b>no time.log-box found</b><br><br>";
}


include_once("common/foot.php");
?>
