<?php
/**
 * TDTrac Installation Program
 * 
 * Contains main program logic.
 * @package tdtrac
 * @version 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
ob_start(); session_start(); 

## PROGRAM DETAILS. DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING
$TDTRAC_VERSION = "3.2.1";
$TDTRAC_PERMS = array("addshow", "editshow", "viewshow", "addbudget", "editbudget", "viewbudget", "addhours", "edithours", "viewhours", "adduser");
$INSTALL_FILES = array(
	"index.php",
	"rcpt.php",
	"./lib/helpnodes.php",
	"./lib/install.inc.php",
	"./lib/budget.php",
	"./lib/dbaseconfig.php",
	"./lib/admin.php",
	"./lib/formlib.php",
	"./lib/functions-load.php",
	"./lib/htmllib.php",
	"./lib/hours.php",
	"./lib/user.php",
	"./lib/messaging.php",
	"./lib/todo.php",
	"./lib/json.php",
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
	"rcpts",
	"todo");

require_once("config.php");
require_once("lib/formlib.php");
$page_title = substr($_SERVER['REQUEST_URI'], 1); 
preg_match("/install.php\?(.+)$/", $page_title, $match);
$page_title = $match[1];
if ( $page_title == "" || $page_title == "install.php" ) { $page_title = "home"; }
require_once("lib/htmllib.php");
foreach ( makeHeader("Installer") as $line ) { echo "{$line}\n"; }

echo "<h3>TDTrac{$TDTRAC_VERSION} Installer</h3>\n";
$sqllink = 1; $noinstall = 0;

switch ($page_title) {
    case "doinstall" :
	require_once("lib/dbaseconfig.php");
	require_once("lib/install.inc.php");
	echo "<h3>Installation DONE!</h3>";
	break;
    case "site" :
	if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { 
		$filename = "config.php";
		$fh = fopen($filename, 'a');
		fwrite($fh, "<?php\n");
		fwrite($fh, "\$TDTRAC_CPNY = \"{$_REQUEST['cpny']}\";\n");
		fwrite($fh, "\$TDTRAC_SITE = \"{$_REQUEST['site']}\";\n");
		fwrite($fh, "\$TDTRAC_DAYRATE = \"{$_REQUEST['dayrate']}\";\n");
		fwrite($fh, "\$TDTRAC_PAYRATE = \"{$_REQUEST['payrate']}\";\n?>\n");
		header("Location: install.php");
	} else {
		$form = new tdform(array('action' => "install.php?site"));
		
		$fes = $form->addText(array('id' => 'cpny', 'label' => "Site Name", 'preset' => $TDTRAC_CPNY));
		$fes = $form->addText(array('id' => 'site', 'label' => "Site URL", 'preset' => $TDTRAC_SITE));
		$fes = $form->addInfo("Enter 1 for Daily rate, 0 for Hourly");
		$fes = $form->addText(array('id' => 'dayrate', 'label' => "Day Rate", 'preset' => $TDTRAC_DAYRATE));
		$fes = $form->addText(array('id' => 'payrate', 'label' => "Default Pay Rate", 'preset' => $TDTRAC_PAYRATE));
		
		echo join("\n", $form->output('Save Values'));
	}
	break;
    case "mysql" :
	if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
		$filename = "config.php";
		$fh = fopen($filename, 'a');
		fwrite($fh, "<?php\n");
		fwrite($fh, "\$MYSQL_SERVER = \"{$_REQUEST['server']}\";\n");
		fwrite($fh, "\$MYSQL_USER = \"{$_REQUEST['user']}\";\n");
		fwrite($fh, "\$MYSQL_PASS = \"{$_REQUEST['password']}\";\n");
		fwrite($fh, "\$MYSQL_DATABASE = \"{$_REQUEST['dbase']}\";\n");
		fwrite($fh, "\$MYSQL_PREFIX = \"{$_REQUEST['prefix']}\";\n?>\n");
		header("Location: install.php");
	} else {
		$form = new tdform("install.php?mysql", "form1", 1, 'genform', "MySQL Config");
		
		$fes = $form->addInfo("hostname[:port]");
		$fes = $form->addText(array('id' => 'server', 'label' => "MySQL Host", 'preset' => $MYSQL_SERVER));
		$fes = $form->addText(array('id' => 'user', 'label' => "Username", 'preset' => $MYSQL_USER));
		$fes = $form->addText(array('id' => 'password', 'label' => "Password", 'preset' => $MYSQL_PASS));
		$fes = $form->addText(array('id' => 'dbase', 'label' => "Database", 'preset' => $MYSQL_DATABASE));
		$fes = $form->addText(array('id' => 'prefix', 'label' => "Table Prefix", 'preset' => $MYSQL_PREFIX));
		
		echo join("\n", $form->output('Save Values'));
	}
	break;
    case "home" :
	echo "<div class=\"installer\"><ul><li>Checking Enviroment...<ul>\n";
	  // Config File
  	  $perms = substr(sprintf('%o', fileperms("config.php")), -4);
	  echo ($perms == "0666") ? "<li style=\"color:green\"><b>OK::</b> config.php - World Writable</li>" : "<li style=\"color:red\"><b>FAIL::</b> config.php - Must be writable by webserver</li>";
          if ( $perms <> "0666" ) { $sqllink = 0; }
	echo "</ul></li></ul>";
	echo "<ul><li>Checking Config...";
	echo ( $sqllink ) ? "<a href=\"install.php?site\">[-Edit Values-]</a>" : "";
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
        echo ( $sqllink ) ? "<a href=\"install.php?mysql\">[-Edit Settings-]</a>" : "";
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
				echo "<li style=\"color: red\"><b>FAILURE::</b> {$row[0]} Table Already Exists.</li>\n";
				$noinstall = 1;
			}
		}
		echo ( !$noinstall ) ? "<li style=\"color: green\"><b>SUCCESS::</b> No Existing Tables will be clobbered</li>\n" : "";
	  }
	echo "</ul></li></ul></div><div style=\"text-align: center\">\n";
	if ( !$noinstall ) { 
		echo "<a href=\"install.php?doinstall\">[-Proceed With Installation-]</a>\n";
	} else {
		echo "Unable To Install - Please Correct Above Errors\n";
	}
	echo "</div>\n";
	break;
}

foreach ( makeFooter('', false) as $line ) { echo "{$line}\n"; }

function merge_defaults($orig, $override) {
	foreach ( $orig as $key=>$value ) {
		if ( isset($override[$key]) ) { $orig[$key] = $override[$key]; }
	}
	return $orig;
}
?>
