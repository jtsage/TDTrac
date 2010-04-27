<?php
/**
 * TDTrac Help Display
 * 
 * Contains main program logic.
 * @package tdtrac
 * @version 1.3.0
 */
if ( isset($_REQUEST['node']) ) { $node = $_REQUEST['node']; } else { $node = "home"; }
$html = <<<ENN
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>TDTracHELP - {$node}</title>
<link href="td130.css" rel="stylesheet" type="text/css" />
<style type="text/css">
ul { margin-left: 30px; }
ul li { font-weight: bold; }
ul li ul li { font-weight: normal; }
</style>
<script type="text/javascript" src="TDTracCalendar.js"></script>
</head>

<body>

<div id="upbg"></div>

<div id="outer">
	<div id="header">;
		<div id="headercontent">
			<h1>TDTracHELP<sup>Online</sup></h1>
			<h2>Budget and Payroll Tracking
		</div>
	</div>
	<div id="content">
		<div id="normalcontent">
ENN;

echo $html;
require_once("lib/helpnodes.php");

if ( !isset($helpnode[$node]) ) { $node = "error"; }

foreach ( $helpnode[$node] as $mynode ) {
  echo $mynode;
}

require_once("lib/footer.php");
?>
