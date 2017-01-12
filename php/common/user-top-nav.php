<?php
print "<div class='top'>
          <ul class='nav'>
            <li><a href='index.php' title='Hauptseite'>Home</a></li>
            <li><a href='prototype_hw_lcd.php?logbox_mac={$_SESSION['mac_address']}' title='Arbeitszeit erfassen' >Erfassen</a></li>
            <li><a href='entries.php' title='Arbeitszeit Auswerten' >Auswerten</a></li>
            <li><a href='logout.php' title='Abmelden' >Logout</a></li>
          </ul>
      </div>";
