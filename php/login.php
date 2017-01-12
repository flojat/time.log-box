<?php

//http://".$_SERVER['SERVER_NAME'].$_SERVER['SCRIPT_NAME']."
include_once("common/php_header.php");
include_once("common/html_header.php");

//Falls Form gesendet wird gibts ein Update oder ein Insert oder ein Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit']) && $_POST['submit'] =="Login") {

  if( isset($_POST['email']) && $_POST['email'] !='' )
  $email = $_POST['email'];

  if( isset($_POST['mac_address']) && $_POST['mac_address'] !='' )
  $mac_address = $_POST['mac_address'];

  if( isset($_POST['password']) && $_POST['password'] !='' )
  $pwd = sha1 ($_POST['password']);

  if(isset($email) && isset($mac_address) && isset($pwd) ){

    $sqlquery=" SELECT user.id, user.email, `mac_address`
                FROM user, logboxes
                WHERE user.email =   '{$email}'
                AND logboxes.mac_address =   '{$mac_address}'
                AND user.password =   '{$pwd}'
                AND user.id = `user_id` ";
      $query=mysql_query($sqlquery);
      echo mysql_error();
      list($user_id, $email_db,$mac_address_db)=mysql_fetch_row($query);

      if((strcmp ( $email_db , $email )==0) && (strcmp ( $email_db , $email )==0) ){
        session_start();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['mac_address'] = $mac_address_db;
        header('Location: prototype_hw_lcd.php');
      }
  }


}

print "<form method=post action='{$_SERVER['SCRIPT_NAME']}' name='login_form'>";
print "<div id='edit_table' class='entries table edit'>";
print "   <div class='trh'><div class='td'><label>Username</label><input name='email'  type='text' value=''/></div></div>";
print "   <div class='trh'><div class='td'><label>Password</label><input name='password' size='45' type='password' value=''/></div></div>";
print "   <div class='trh'><div class='td'><label>DeviceID</label><input name='mac_address' size='45' type='text' value=''/></div></div>";
print "<div class='trh'>
         <div class='td' valign=top>
           <input title='Speichern' class='button' name='submit' type='submit' value='Login' />
         </div>
      </div>
  </div><!-- end of edit_table -->
</form>";











include_once("common/foot.php");
