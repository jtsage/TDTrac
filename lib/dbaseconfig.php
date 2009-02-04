<?php
  $db = mysql_connect('localhost', 'tdtrac', 'tdtrac');
  if (!$db) {
    die('Could not connect: ' . mysql_error());
  }  

  $dbr = mysql_select_db('tdtrac', $db);
  if (!$dbr) {
    die ('Can\'t use tdtrac : ' . mysql_error());
  }
?>
