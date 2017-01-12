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
company 	varchar(255)
name 	varchar(255)
firstname 	varchar(255)
email 	varchar(255)
phone 	varchar(255)
address 	varchar(255)
zip 	mediumint(9)
city 	varchar(255)
password 	varchar(255)
*/


//Falls Form gesendet wird gibts ein Update oder ein Insert oder ein Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] =="Save") {

    if(isset($_POST['id']) && $_POST['id'] !=""){
      $pwd = sha1 ($_POST['password']);
      $sqlquery="UPDATE `clients`
                 set `company`='{$_POST['company']}',
                      `name`=    '{$_POST['name']}',
                      `firstname`='{$_POST['firstname']}',
                      `email`=   '{$_POST['email']}',
                      `phone`='{$_POST['phone']}',
                      `address`='{$_POST['address']}',
                      `zip`='{$_POST['zip']}',
                      `city`='{$_POST['city']}',
                      `password`='{$pwd}'
                   WHERE `id` =   {$_POST['id']}
                   LIMIT 1 ";

        //echo $sqlquery;
        $query=mysql_query($sqlquery);
        var_dump($query);
        echo mysql_error();

    }else{
      $pwd = sha1 ($_POST['password']);
      $sqlquery="INSERT INTO `clients` (company,name,firstname,email,phone,address,zip,city,password )
                 values('{$_POST['company']}','{$_POST['name']}',
                        '{$_POST['firstname']}','{$_POST['email']}',
                        '{$_POST['phone']}','{$_POST['address']}',
                        '{$_POST['zip']}','{$_POST['city']}','{$pwd}')";
        //echo $sqlquery;
        $query=mysql_query($sqlquery);
        var_dump($query);
        echo mysql_error();
    }

}else if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] =="Delete" ){
  $query=mysql_query("DELETE from `clients` WHERE `id` = {$_POST['id']} LIMIT 1 ");
  //var_dump($query);
  echo mysql_error();
}

/*-----------------------------------------------------------------------------
- Form zum erfassen einer enuen oder bearbeiten einer bestehenden log-box -----
-----------------------------------------------------------------------------*/
//falls eine ID gesendet wird soll die LogBox bearbeitet werden können(onklick in Tabellenzeile)

if (isset($_GET['id']) && $_GET['id'] !=""){
    $id = $_GET['id'];
    $sqlquery="SELECT `id`, `company`, `name`,`firstname`,`email`,`phone`,`address`,`zip`, `city`, `password`
               FROM `clients` WHERE `id` = $id ;";
    //echo $sqlquery;
    $query=mysql_query($sqlquery);
    echo mysql_error();
    list($id,$company,$name,$firstname,$email,$phone,$address,$zip,$city,$password)=mysql_fetch_row($query);
}
$id = isset($id) ? $id : '';
$company = isset($company) ? $company : '';
$name = isset($name) ? $name : '';
$firstname = isset($firstname) ? $firstname : '';
$email = isset($email) ? $email : '';
$phone = isset($phone) ? $phone : '';
$address = isset($address) ? $address : '';
$zip = isset($zip) ? $zip : '';
$city = isset($city) ? $city : '';
$password = isset($password) ? $password : '';


print "<form method=post action='{$_SERVER['SCRIPT_NAME']}' name='entry_form'>";
print "<div id='edit_table' class='table edit'>";
print "   <div class='trh'><div class='td'><label>id</label><input readonly name='id'  type='text' value='{$id}'/></div></div>";
print "   <div class='trh'><div class='td'><label>company</label><input name='company'  type='text' value='{$company}'/></div></div>";
print "   <div class='trh'><div class='td'><label>name</label><input name='name'  type='text' value='{$name}'/></div></div>";
print "   <div class='trh'><div class='td'><label>firstname</label><input name='firstname'  type='text' value='{$firstname}'/></div></div>";
print "   <div class='trh'><div class='td'><label>email</label><input name='email'  type='text' value='{$email}'/></div></div>";
print "   <div class='trh'><div class='td'><label>phone</label><input name='phone'  type='text' value='{$phone}'/></div></div>";
print "   <div class='trh'><div class='td'><label>address</label><input name='address'  type='text' value='{$address}'/></div></div>";
print "   <div class='trh'><div class='td'><label>zip</label><input name='zip'  type='text' value='{$zip}'/></div></div>";
print "   <div class='trh'><div class='td'><label>city</label><input name='city'  type='text' value='{$city}'/></div></div>";
print "   <div class='trh'><div class='td'><label>password</label><input name='password'  type='text' value='{$password}'/></div></div>";
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

  $query=mysql_query("SELECT `id`, `company`, `name`,`firstname`,`email`,`phone`,`address`,`zip`, `city`, `password`
                      FROM `clients` WHERE 1 order by `$sort` desc ");

  echo mysql_error();

  if(@mysql_num_rows($query)>0){
    print "<p><b>Erfasste time.log-box User</b></p>
          <table class='tasklist' width=\"800px\" border=0 cellpadding=2 cellspacing=0>
              <tr>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=id\" title='Sortieren nach ID'>ID</a></b></td>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=company\" title='Sortieren nach company' >company</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=name\" title='Sortieren nach name' >name</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=firstname\" title='Sortieren nach firstname' >firstname</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=email\" title='Sortieren nach email' >email</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=phone\" title='Sortieren nach phone' >phone</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=address\" title='Sortieren nach address' >address</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=zip\" title='Sortieren nach zip' >zip</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=city\" title='Sortieren nach city' >city</a></b></th>
                  <th><b><a href=\"{$_SERVER['SCRIPT_NAME']}?sort=password\" title='Sortieren nach password' >password</a></b></th>
                </tr>\n";

                for($i=0;list($id,$company,$name,$firstname,$email,$phone,$address,$zip,$city,$password)=mysql_fetch_row($query);$i++) {

                    if(($i%2)==0){ $bgcolor=$_config_tbl_bgcolor1;
                    }else{         $bgcolor=$_config_tbl_bgcolor2;}

                  // time() - strtotime(date_CH_to_EN($faellig))) > 0

                    print "<tr onmouseover=\"setPointer(this, 'over', '#$bgcolor', '#$_config_tbl_bghover', '')\" onmouseout=\"setPointer(this, 'out', '#$bgcolor', '#$_config_tbl_bghover', '')\" onclick=\"location.href='{$_SERVER['SCRIPT_NAME']}?id=$id'\">
                            <td valign=top bgcolor=\"#$bgcolor\" >$id</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$company</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$name</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$firstname</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$email</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$phone</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$address</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$zip</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$city</td>
                            <td valign=top bgcolor=\"#$bgcolor\" >$password</td>
                          </tr>";
                }
                print "</table><br>";
}
else{
        print "<b>no time.log-box Clients found</b><br><br>";
}


include_once("common/foot.php");
?>
