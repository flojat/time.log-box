<?php

include_once("common/php_header.php");
include_once("common/html_header.php");

if (isset($_GET['back']) && $_GET['back'] !=""){
    $back = $_GET['back'];
}else{
    $back = "index.php";
}
/*
id int(11) AUTO_INCREMENT
name varchar(255)
description varchar(255)
*/


//Falls Form gesendet wird gibts ein Update oder ein Insert oder ein Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] =="Save") {

    if(isset($_POST['id']) && $_POST['id'] !=""){
      $sqlquery="UPDATE `categories`
                 set `name`='{$_POST['name']}',
                     `description`=  '{$_POST['description']}'
                 WHERE `id` =   {$_POST['id']}
                 LIMIT 1 ";
       $query=mysql_query($sqlquery);
       var_dump($query);
       echo mysql_error();

    }else{
       $sqlquery="INSERT INTO `categories` (name,description)
                  values('{$_POST['name']}','{$_POST['description']}')";
       $query=mysql_query($sqlquery);
       echo mysql_error();

    }

}else if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] =="Delete" ){
  $sqlquery="DELETE from `categories` WHERE `id` = {$_POST['id']} LIMIT 1 ";
  $query=mysql_query($sqlquery);
  echo mysql_error();
}

/*-----------------------------------------------------------------------------
- Form zum erfassen einer enuen oder bearbeiten einer bestehenden log-box -----
-----------------------------------------------------------------------------*/
//falls eine ID gesendet wird soll die LogBox bearbeitet werden können(onklick in Tabellenzeile)
if (isset($_GET['id']) && $_GET['id'] !=""){
    $id = $_GET['id'];
    $sqlquery="SELECT `id`, `name`, `description`
               FROM `categories` WHERE `id` = $id ;";
    $query=mysql_query($sqlquery);
    echo mysql_error();
    list($id,$name,$description)=mysql_fetch_row($query);
}
$id = isset($id) ? $id : '';
$name = isset($name) ? $name : '';
$description = isset($description) ? $description : '';


print "<form method=post action='{$_SERVER['SCRIPT_NAME']}' name='entry_form'>";
print "<div id='edit_table' class='table edit'>";
print "   <div class='trh'><div class='td'><label>id</label><input readonly name='id'  type='text' value='{$id}'/></div></div>";
print "   <div class='trh'><div class='td'><label>name</label><input name='name'  type='text' value='{$name}'/></div></div>";
print "   <div class='trh'><div class='td'><label>description</label><input name='description'  type='text' value='{$description}'/></div></div>";
print "</div>";

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
  $query=mysql_query("SELECT `id`, `name`, `description`
                      FROM `categories` WHERE 1 order by `$sort` desc ");
  echo mysql_error();

  if(@mysql_num_rows($query)>0){
    print "<p><b>Erfasste time.log-box work-categories</b></p>
          <table class='tasklist' width=\"800px\" border=0 cellpadding=2 cellspacing=0>
              <tr>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=id\" title='Sortieren nach ID'>ID</a></b></td>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=name\" title='Sortieren nach name' >name</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=description\" title='Sortieren nach description' >description</a></b></th>
                </tr>\n";

                for($i=0;list($id,$name,$description)=mysql_fetch_row($query);$i++) {

                    if(($i%2)==0){ $bgcolor=$_config_tbl_bgcolor1;
                    }else{         $bgcolor=$_config_tbl_bgcolor2;}

                  // time() - strtotime(date_CH_to_EN($faellig))) > 0

                    print "<tr onmouseover=\"setPointer(this, 'over', '#$bgcolor', '#$_config_tbl_bghover', '')\" onmouseout=\"setPointer(this, 'out', '#$bgcolor', '#$_config_tbl_bghover', '')\" onclick=\"location.href='{$_SERVER['SCRIPT_NAME']}?id=$id'\">
                            <td valign=top bgcolor=\"#$bgcolor\" >$id</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$name</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$description</td>
                          </tr>";
                }
                print "</table><br>";
}
else{
        print "<b>no time.log-box work-categories found</b><br><br>";
}


include_once("common/foot.php");
?>
