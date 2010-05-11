<?php
/**
 * TDTrac Upgrade Program
 * 
 * Contains main program logic.
 * @package tdtrac
 * @version 1.3.1
 * @author J.T.Sage <jtsage@gmail.com>
 */
ob_start(); session_start(); 

## PROGRAM DETAILS. DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING
$TDTRAC_VERSION = "1.3.1";
$TDTRAC_PERMS = array("addshow", "editshow", "viewshow", "addbudget", "editbudget", "viewbudget", "addhours", "edithours", "viewhours", "adduser");
$INSTALL_FILES = array(
	"index.php",
	"help.php",
	"rcpt.php",
	"./lib/helpnodes.php",
	"./lib/install.inc.php",
	"./lib/budget.php",
	"./lib/dbaseconfig.php",
	"./lib/email.php",
	"./lib/footer.php",
	"./lib/formlib.php",
	"./lib/functions-load.php",
	"./lib/header.php",
	"./lib/home.php",
	"./lib/hours.php",
	"./lib/login.php",
	"./lib/messaging.php",
	"./lib/permissions.php",
	"./lib/reciept.php", 
	"./lib/show.php" );
$INSTALL_TABLES = array(
	"tdtrac",
	"users",
	"budget",
	"groupnames",
	"hours",
	"msg",
	"permissions",
	"shows",
	"usergroups",
	"rcpts");
	

require_once("config.php");
$page_title = "updater";
require_once("lib/header.php");
$page_title = "home";

echo "<h3>TDTrac{$TDTRAC_VERSION} Updater</h3>\n";
$sqllink = 1; $noinstall = 0;


$V110ADDS = array(
   "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}tdtrac` (  
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(10) NOT NULL,
  `value` varchar(35) NOT NULL,
    PRIMARY KEY  (`id`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;",
  "INSERT INTO `{$MYSQL_PREFIX}tdtrac` (`name`, `value`) VALUES ( 'version', '1.1.0' )"
);
$V120ADDS = array(
  "ALTER TABLE `{$MYSQL_PREFIX}budget` ADD pending tinyint(4) unsigned NOT NULL DEFAULT '0'",
  "ALTER TABLE `{$MYSQL_PREFIX}budget` ADD needrepay tinyint(4) unsigned NOT NULL DEFAULT '0'",
  "ALTER TABLE `{$MYSQL_PREFIX}budget` ADD gotrepay tinyint(4) unsigned NOT NULL DEFAULT '0'",
  "ALTER TABLE `{$MYSQL_PREFIX}hours` ADD submitted tinyint(4) unsigned NOT NULL DEFAULT '0'",
  "INSERT INTO `{$MYSQL_PREFIX}tdtrac` (`name`, `value`) VALUES ( 'version', '1.2.0' )"
);  
$V121ADDS = array(
  "ALTER TABLE `{$MYSQL_PREFIX}users` ADD limithours tinyint(4) unsigned NOT NULL DEFAULT '1'",
  "INSERT INTO `{$MYSQL_PREFIX}tdtrac` (`name`, `value`) VALUES ( 'version', '1.2.1' )"
);
$V122ADDS = array(
  "ALTER TABLE `{$MYSQL_PREFIX}users` ADD lastlogin timestamp",
  "INSERT INTO `{$MYSQL_PREFIX}tdtrac` (`name`, `value`) VALUES ( 'version', '1.2.2' )"
);
$V124ADDS = array(
  "ALTER TABLE `{$MYSQL_PREFIX}shows` ADD closed tinyint(4) unsigned NOT NULL DEFAULT '0'",
  "INSERT INTO `{$MYSQL_PREFIX}tdtrac` (`name`, `value`) VALUES ( 'version', '1.2.4' )"
);
$V125ADDS = array(
  "ALTER TABLE `{$MYSQL_PREFIX}budget` ADD tax float NOT NULL DEFAULT '0'",
  "INSERT INTO `{$MYSQL_PREFIX}tdtrac` (`name`, `value`) VALUES ( 'version', '1.2.5' )",
  "INSERT INTO `{$MYSQL_PREFIX}msg` (`toid`, `fromid`, `body`) VALUES ('1', '1', 'Updated to v1.2.5 :: Added Tax Tracking')"
);
$V126ADDS = array(
  "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}rcpts` (
    `imgid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `type` varchar(100) NOT NULL,
    `name` varchar(25) NOT NULL,
    `data` mediumblob NOT NULL,
    `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `handled` tinyint(3) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`imgid`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;",
  "ALTER TABLE `{$MYSQL_PREFIX}budget` ADD  `imgid` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0'",
  "INSERT INTO `{$MYSQL_PREFIX}tdtrac` (`name`, `value`) VALUES ( 'version', '1.2.6' )",
  "INSERT INTO `{$MYSQL_PREFIX}msg` (`toid`, `fromid`, `body`) VALUES ('1', '1', 'Updated to v1.2.6 :: Added Reciept by E-Mail Tracking')"
);
$V130ADDS = array(
  "INSERT INTO `{$MYSQL_PREFIX}tdtrac` (`name`, `value`) VALUES ( 'version', '1.3.0' )",
  "INSERT INTO `{$MYSQL_PREFIX}msg` (`toid`, `fromid`, `body`) VALUES ('1', '1', 'Updated to v1.3.0 :: New Theme!')"
);
  

switch ($page_title) {
    case "doinstall" :
	require_once("lib/dbaseconfig.php");
	require_once("lib/install.inc.php");
	echo "Installation DONE!";
	break;
	
	
    
    case "home" :
		echo "<div class=\"installer\"><ul><li>Checking Enviroment...<ul>\n";
		// Config File
		$perms = substr(sprintf('%o', fileperms("config.php")), -4);
		echo ($perms == "0666") ? "<li style=\"color:red\"><b>FAIL::</b> config.php - World Writable - POTENTIAL UNSAFE!</li>" : "<li style=\"color:green\"><b>OK::</b> config.php - Permissions are secure</li>";
          
		echo "</ul></li></ul>";
		echo "<ul><li>Checking Config...";
		echo "<ul>\n";
		echo "<li><b>Site Name::</b> {$TDTRAC_CPNY}</li>\n";
		echo "<li><b>Site URL::</b> {$TDTRAC_SITE}</li>\n";
		echo "<li><b>Day Rate Payroll::</b> ";
		echo ($TDTRAC_DAYRATE) ? "YES" : "NO";
		echo "</li>\n";
		echo "<li><b>Per Day/Hour Pay Rate::</b> \${$TDTRAC_PAYRATE}</li>\n";
		echo "</ul></li></ul>\n";
		
	echo "<ul><li>Checking Files...<ul>\n";
	  // Check File Exists
	  foreach ($INSTALL_FILES as $file) {
		echo "<li style=\"color:";
		echo ( file_exists($file) ) ? "green\"><b>FOUND::</b> {$file}" : "red\"><b>NOT FOUND::</b> {$file} <b>!!ERROR!!</b>";
		echo "</li>\n";
	  } 
	echo "</ul></li></ul>\n";
	echo "<ul><li>Checking MySQL From config.php... ";
        echo "<ul>\n";
	  $db = mysql_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS);
	  if (!$db) {
		echo "<li style=\"color: red\"><b>FAILURE::</b> Could not connect: " . mysql_error() . "</li>\n"; $noinstall = 1;
	  } else {
		echo "<li style=\"color: green\"><b>SUCCESS::</b> Connected to mysql host</li>\n";
	  }
	  $dbr = mysql_select_db($MYSQL_DATABASE, $db);
	  if (!$dbr) {
		echo "<li style=\"color: red\"><b>FAILURE::</b> Cannot Use {$MYSQL_DATABASE}: " . mysql_error() . "</li>\n"; $noinstall = 1;
	  } else {
		echo "<li style=\"color: green\"><b>SUCCESS::</b> Connected to database</li>\n";
	  }
	  if ( !$noinstall ) {
		$sql = "SHOW TABLES";
		$result = mysql_query($sql, $db);
		while ( $row = mysql_fetch_array($result) ) {
			$found = 0;
			foreach ( $INSTALL_TABLES as $check ) {
				$check = $MYSQL_PREFIX . $check;
				if ( $row[0] == $check ) { $found = 1; }
			}
			if ( $found ) {
				echo "<li style=\"color: green\"><b>FOUND::</b> {$row[0]} Table Already Exists.</li>\n";
			}
		}
		echo ( !$noinstall ) ? "<li style=\"color: green\"><b>SUCCESS::</b> Found Acceptable Installation</li>\n" : "";
	  }
	echo "</ul></li></ul>";
	echo "<ul><li>Checking Version... <ul>";	

	$better110 = 0;
	$sql = "SHOW TABLES";
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$found = 0;
		$check = $MYSQL_PREFIX . 'tdtrac';
		if ( $row[0] == $check ) { $found = 1; }
		if ( $found ) {
				echo "<li style=\"color: green\"><b>VERSION::</b> 1.1.0 or greater confirmed.</li>\n";
				$better110 = 1;
		}
	}

	if (!$better110) { // 1.1.0 UPDGRADE
		echo "<li style=\"color: red\"><b>VERSION::</b> Version String Not Found, assuming pre 1.1.0.</li>"; 
		foreach ( $V110ADDS as $thissql ) {
			$result = mysql_query($thissql, $db);
			echo mysql_error();  
		}
		echo "<li style=\"color: green\"><b>UPGRADE::</b> Upgraded to version 1.1.0</li>";
		$didinstall = 1;
	}
	else { // POST 1.1.0 UPGRADE
		$found120 = 0; $found121 = 0; $found122 = 0; $found124 = 0; $found125 = 0; $found126 = 0; $found130 = 0;
		$sql = "SELECT name, value FROM `{$MYSQL_PREFIX}tdtrac` WHERE name = 'version' ORDER BY id DESC";
		$result = mysql_query($sql,$db);
		while ( $verline = mysql_fetch_array($result) ) {
			if ( $verline['value'] == "1.2.0" ) { $found120 = 1; }
			if ( $verline['value'] == "1.2.1" ) { $found121 = 1; $found120 = 1;}
			if ( $verline['value'] == "1.2.2" ) { $found122 = 1; $found121 = 1; $found120 = 1;}
			if ( $verline['value'] == "1.2.4" ) { $found124 = 1; $found122 = 1; $found121 = 1; $found120 = 1; }
			if ( $verline['value'] == "1.2.5" ) { $found125 = 1; $found124 = 1; $found122 = 1; $found121 = 1; $found120 = 1; }
			if ( $verline['value'] == "1.2.6" ) { $found125 = 1; $found124 = 1; $found122 = 1; $found121 = 1; $found120 = 1; $found126 = 1;}
			if ( $verline['value'] == "1.3.0" ) { $found125 = 1; $found124 = 1; $found122 = 1; $found121 = 1; $found120 = 1; $found126 = 1; $found130 = 1;}
		} 
		if ( !$found120 ) { // 1.2.0 UPGRADE
			foreach ( $V120ADDS as $thissql ) { $result = mysql_query($thissql, $db); echo mysql_error(); }
                        echo "<li style=\"color: green\"><b>UPGRADE::</b> Upgraded to version 1.2.0</li>"; $didinstall = 1;
		} else { echo "<li style=\"color: green\"><b>VERSION::</b> 1.2.0 confirmed</li>\n"; }

		if ( $found120 && !$found121 ) { // 1.2.1 UPGRADE
			foreach ( $V121ADDS as $thissql ) { $result = mysql_query($thissql, $db); echo mysql_error(); }
                        echo "<li style=\"color: green\"><b>UPGRADE::</b> Upgraded to version 1.2.1</li>"; $didinstall = 1;
		} else { echo "<li style=\"color: green\"><b>VERSION::</b> 1.2.1 confirmed</li>\n"; }

		if ( $found120 && $found121 && !$found122 ) { // 1.2.2 UPGRADE
			foreach ( $V122ADDS as $thissql ) { $result = mysql_query($thissql, $db); echo mysql_error(); }
                	echo "<li style=\"color: green\"><b>UPGRADE::</b> Upgraded to version 1.2.2</li>"; $didinstall = 1;
		} else { echo "<li style=\"color: green\"><b>VERSION::</b> 1.2.2 confirmed</li>\n"; }

		if ( $found120 && $found121 && $found122 && !$found124 ) { // 1.2.4 UPGRADE
                        foreach ( $V124ADDS as $thissql ) { $result = mysql_query($thissql, $db); echo mysql_error(); }
                        echo "<li style=\"color: green\"><b>UPGRADE::</b> Upgraded to version 1.2.4</li>"; $didinstall = 1;
                } else { echo "<li style=\"color: green\"><b>VERSION::</b> 1.2.4 confirmed</li>\n"; }

                if ( $found120 && $found121 && $found122 && $found124 && !$found125 ) { // 1.2.5 UPGRADE
                        foreach ( $V125ADDS as $thissql ) { $result = mysql_query($thissql, $db); echo mysql_error(); }
                        echo "<li style=\"color: green\"><b>UPGRADE::</b> Upgraded to version 1.2.5</li>"; $didinstall = 1;
                } else { echo "<li style=\"color: green\"><b>VERSION::</b> 1.2.5 confirmed</li>\n"; }

                if ( $found120 && $found121 && $found122 && $found124 && $found125 && !$found126 ) { // 1.2.6 UPGRADE
                        foreach ( $V126ADDS as $thissql ) { $result = mysql_query($thissql, $db); echo mysql_error(); }
                        echo "<li style=\"color: green\"><b>UPGRADE::</b> Upgraded to version 1.2.6</li>"; $didinstall = 1;
                } else { echo "<li style=\"color: green\"><b>VERSION::</b> 1.2.6 confirmed</li>\n"; }
                
                if ( $found120 && $found121 && $found122 && $found124 && $found125 && $found126 && !$found130 ) { // 1.3.0 UPGRADE
                        foreach ( $V130ADDS as $thissql ) { $result = mysql_query($thissql, $db); echo mysql_error(); }
                        echo "<li style=\"color: green\"><b>UPGRADE::</b> Upgraded to version 1.3.0</li>"; $didinstall = 1;
                } else { echo "<li style=\"color: green\"><b>VERSION::</b> 1.3.0 confirmed</li>\n"; }
	}	
	
	echo "</ul></li></ul></div><div style=\"text-align: center\">\n";
	if ( $didinstall ) { 
		echo "RELOAD THE PAGE PLEASE!\n";
	} else {
		echo "Nothing More To Do!\n";
	}
	echo "</div>\n";
	break;
}

require_once("lib/footer.php");

?>
