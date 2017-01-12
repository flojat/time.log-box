<?php

include_once("common/php_header.php");
include_once("common/html_header.php");

if (isset($_GET['back']) && $_GET['back'] !=""){
    $back = $_GET['back'];
}else{
    $back = "index.php";
}
/*
id int(11)
name 	varchar(255)
isactive 	tinyint(1)
needcomment 	tinyint(1)
needcategory 	tinyint(1)
client_id 	int(11)
*/
//Falls Form gesendet wird gibts ein Update oder ein Insert oder ein Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] =="Save") {

  $isactive = isset($_POST['isactive']) ? 1 : 0;
  $needcomment = isset($_POST['needcomment']) ? 1 : 0;
  $needcategory = isset($_POST['needcategory']) ? 1 : 0;



    if(isset($_POST['id']) && $_POST['id'] !=""){
      $sqlquery="UPDATE `projects`
                 set  `name`='{$_POST['name']}',
                      `isactive`= {$isactive},
                      `needcomment`= {$needcomment},
                      `needcategory`= {$needcategory},
                      `client_id`= {$_POST['client_id']}
                   WHERE `id` =   {$_POST['id']}
                   LIMIT 1 ";
        $query=mysql_query($sqlquery);
        echo mysql_error();

    }else{
      $sqlquery="INSERT INTO `projects` (name,isactive,needcomment,needcategory,client_id)
                 values('{$_POST['name']}',{$isactive},{$needcomment},
                        {$needcategory}, {$_POST['client_id']})";
        $query=mysql_query($sqlquery);
        echo mysql_error();

    }

    $isactive = 0;
    $needcomment = 0;
    $needcategory = 0;



}else if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] =="Delete" ){
  $query=mysql_query("DELETE from `projects` WHERE `id` = {$_POST['id']} LIMIT 1 ");
  echo mysql_error();
}

/*-----------------------------------------------------------------------------
- Form zum erfassen einer enuen oder bearbeiten einer bestehenden log-box -----
-----------------------------------------------------------------------------*/

//falls eine ID gesendet wird soll die LogBox bearbeitet werden können(onklick in Tabellenzeile)
if (isset($_GET['id']) && $_GET['id'] !=""){
    $id = $_GET['id'];
    $sqlquery="SELECT `id`, `name`, `isactive`, `needcomment`, `needcategory`, `client_id`
               FROM `projects` WHERE `id` = $id ;";
    //echo $sqlquery;
    $query=mysql_query($sqlquery);
    echo mysql_error();
    list($id,$name,$isactive,$needcomment,$needcategory,$client_id)=mysql_fetch_row($query);
}
$id = isset($id) ? $id : '';
$name = isset($name) ? $name : '';
$isactive = isset($isactive) ? $isactive : 0;
$needcomment = isset($needcomment) ? $needcomment : 0;
$needcategory = isset($needcategory) ? $needcategory : 0;
$client_id = isset($client_id) ? $client_id : '';


print "<form method=post action='{$_SERVER['SCRIPT_NAME']}' name='entry_form'>";
print "<div id='edit_table' class='entries table edit'>";
print "   <div class='trh'><div class='td'><label>id</label><input readonly name='id'  type='text' value='{$id}'/></div></div>";
print "   <div class='trh'><div class='td'><label>name</label><input name='name' size='45' type='text' value='{$name}'/></div></div>";
print "   <div class='trh'><div class='td'><label>isactive</label><input type='checkbox' name='isactive' id='isactive'". ($isactive==1 ? 'checked=checked' : '')."></div></div>";
print "   <div class='trh'><div class='td'><label>needcomment</label><input type='checkbox' name='needcomment' id='needcomment'". ($needcomment==1 ? 'checked=checked' : '')."></div></div>";
print "   <div class='trh'><div class='td'><label>needcategory</label><input type='checkbox' name='needcategory' id='needcategory'". ($needcategory==1 ? 'checked=checked' : '')."></div></div>";
print "<div class='trh'>
           <div class='td'>";
print         getClientList("client_id", isset($client_id) ? $client_id : '');
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

  $query=mysql_query("SELECT `id`, `name`, `isactive`, `needcomment`, `needcategory`, `client_id`
                      FROM `projects` WHERE 1 order by `$sort` desc ");

  echo mysql_error();

  if(@mysql_num_rows($query)>0){
    print "<p><b>Erfasste time.log-box Projects</b></p>
          <table class='tasklist' width=\"800px\" border=0 cellpadding=2 cellspacing=0>
              <tr>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=id\" title='Sortieren nach ID'>ID</a></b></td>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=name\" title='Sortieren nach name' >name</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=isactive\" title='Sortieren nach isactive' >isactive</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=needcomment\" title='Sortieren nach needcomment' >needcomment</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=needcategory\" title='Sortieren nach needcategory' >needcategory</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=client_id\" title='Sortieren nach client_id' >client_id</a></b></th>
                </tr>\n";

                for($i=0;list($id,$name,$isactive,$needcomment,$needcategory,$client_id)=mysql_fetch_row($query);$i++) {

                    if(($i%2)==0){ $bgcolor=$_config_tbl_bgcolor1;
                    }else{         $bgcolor=$_config_tbl_bgcolor2;}

                  // time() - strtotime(date_CH_to_EN($faellig))) > 0

                    print "<tr onmouseover=\"setPointer(this, 'over', '#$bgcolor', '#$_config_tbl_bghover', '')\" onmouseout=\"setPointer(this, 'out', '#$bgcolor', '#$_config_tbl_bghover', '')\" onclick=\"location.href='{$_SERVER['SCRIPT_NAME']}?id=$id'\">
                            <td width=110 valign=top bgcolor=\"#$bgcolor\" >$id</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$name</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$isactive</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$needcomment</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$needcategory</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$client_id</td>
                          </tr>";
                }
                print "</table><br>";
}
else{
        print "<b>no time.log-box Projects found</b><br><br>";
}


include_once("common/foot.php");
?>
