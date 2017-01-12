<?php

include_once("common/php_header.php");
include_once("common/html_header.php");

if (isset($_GET['back']) && $_GET['back'] !=""){
    $back = $_GET['back'];
}else{
    $back = "index.php";
}
/*
id  int(11) AUTO_INCREMENT
firstname 	varchar(255)
name 	varchar(255)
email 	varchar(255)
password 	varchar(255)
*/


//Falls Form gesendet wird gibts ein Update oder ein Insert oder ein Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] =="Save") {

    if(isset($_POST['id']) && $_POST['id'] !=""){
      $pwd = sha1 ($_POST['password']);
      $sqlquery="UPDATE `user`
                 set `firstname`='{$_POST['firstname']}',
                      `name`=    '{$_POST['name']}',
                      `email`=   '{$_POST['email']}',
                      `password`='{$pwd}'
                   WHERE `id` =   {$_POST['id']}
                   LIMIT 1 ";
        $query=mysql_query($sqlquery);
        echo mysql_error();

    }else{
      $pwd = sha1 ($_POST['password']);
      $sqlquery="INSERT INTO `user` (firstname,name,email,password)
                 values('{$_POST['firstname']}','{$_POST['name']}',
                        '{$_POST['email']}','{$pwd}')";
        $query=mysql_query($sqlquery);
        echo mysql_error();

    }

}else if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] =="Delete" ){
  $query=mysql_query("DELETE from `user` WHERE `id` = {$_POST['id']} LIMIT 1 ");
  echo mysql_error();
}

/*-----------------------------------------------------------------------------
- Form zum erfassen einer enuen oder bearbeiten einer bestehenden log-box -----
-----------------------------------------------------------------------------*/
//falls eine ID gesendet wird soll die LogBox bearbeitet werden können(onklick in Tabellenzeile)
if (isset($_GET['id']) && $_GET['id'] !=""){
    $id = $_GET['id'];
    $sqlquery="SELECT `id`, `firstname`, `name`, `email`, `password`
               FROM `user` WHERE `id` = $id ;";
    //echo $sqlquery;
    $query=mysql_query($sqlquery);
    echo mysql_error();
    list($id,$firstname,$name,$email,$password)=mysql_fetch_row($query);
}
$id = isset($id) ? $id : '';
$firstname = isset($firstname) ? $firstname : '';
$name = isset($name) ? $name : '';
$email = isset($email) ? $email : '';
$password = isset($password) ? $password : '';


print "<form method=post action='{$_SERVER['SCRIPT_NAME']}' name='entry_form'>";
print "<div id='edit_table' class='table edit'>";
print "   <div class='trh'><div class='td'><label>id</label><input readonly=readonly name='id'  type='text' value='{$id}'/></div></div>";
print "   <div class='trh'><div class='td'><label>firstname</label><input name='firstname'  type='text' value='{$firstname}'/></div></div>";
print "   <div class='trh'><div class='td'><label>name</label><input name='name'  type='text' value='{$name}'/></div></div>";
print "   <div class='trh'><div class='td'><label>email</label><input name='email'  type='text' value='{$email}'/></div></div>";
print "   <div class='trh'><div class='td'><label>password</label><input name='password'  type='text' value='{$password}'/></div></div>";
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

  $query=mysql_query("SELECT `id`, `firstname`, `name`, `email`, `password`
                      FROM `user` WHERE 1 order by `$sort` desc ");

  echo mysql_error();

  if(@mysql_num_rows($query)>0){
    print "<p><b>Erfasste time.log-box User</b></p>
          <table class='entries' width=\"800px\" border=0 cellpadding=2 cellspacing=0>
              <tr>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=id\" title='Sortieren nach ID'>ID</a></b></td>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=firstname\" title='Sortieren nach firstname' >firstname</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=name\" title='Sortieren nach name' >name</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=email\" title='Sortieren nach email' >email</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=password\" title='Sortieren nach password' >password</a></b></th>
                </tr>\n";

                for($i=0;list($id,$firstname,$name,$email,$password)=mysql_fetch_row($query);$i++) {

                    if(($i%2)==0){ $bgcolor=$_config_tbl_bgcolor1;
                    }else{         $bgcolor=$_config_tbl_bgcolor2;}

                  // time() - strtotime(date_CH_to_EN($faellig))) > 0

                    print "<tr style=\"cursor:pointer;\" onmouseover=\"setPointer(this, 'over', '#$bgcolor', '#$_config_tbl_bghover', '')\" onmouseout=\"setPointer(this, 'out', '#$bgcolor', '#$_config_tbl_bghover', '')\" onclick=\"location.href='{$_SERVER['SCRIPT_NAME']}?id=$id'\">
                            <td width=110 valign=top bgcolor=\"#$bgcolor\" >$id</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$firstname</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$name</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$email</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$password</td>
                          </tr>";
                }
                print "</table><br>";
}
else{
        print "<b>no time.log-box User found</b><br><br>";
}


include_once("common/foot.php");
?>
