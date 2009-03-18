<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php if ( isset($_REQUEST['node']) ) { $node = $_REQUEST['node']; } else { $node = "home"; } ?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>TDTracHELP - <?php echo $node; ?></title>
<link href="../style.css" rel="stylesheet" type="text/css" />
<style type="text/css">
ul li { font-weight: bold; }
ul li ul li { font-weight: normal; }
</style>
</head>

<body>
<div class="main_holder">
<div class="site_name">
<span style="color:#03C102;">TDTrac</span><span style="color:#999999;">HELP</span>:<?php echo $node; ?></div>

<div class="content_block">

<?php

require_once("lib/helpnodes.php");

if ( !isset($helpnode[$node]) ) { $node = "error"; }

foreach ( $helpnode[$node] as $mynode ) {
  echo $mynode;
}

?>

</div><br clear="all" />
<div class="footer">Copyright &copy; 2008 JTSage</div>

<br clear="all" /> </div>
</body>
</html>
