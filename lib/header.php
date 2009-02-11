<?php
GLOBAL $TDTRAC_VERSION, $TDTRAC_CPNY, $page_title, $login;
$html = <<<ENN
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>TDTrac{$TDTRAC_CPNY}:v{$TDTRAC_VERSION} - {$page_title}</title>
<link href="style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="TDTracCalendar.js"></script>
</head>

<body>
<div class="nav">
<div class="nav_holder"><ul id="menu">
<li><a href="/">Home</a></li>
<li><a href="/change-pass">Change Password</a></li>
<li><a href="/logout">Logout</a></li>
</ul>
</div>
</div>
<div class="main_holder">
<div class="site_name">
<span style="color:#03C102;">TDTrac</span><span style="color:#999999;">{$TDTRAC_CPNY}</span>:v{$TDTRAC_VERSION}</div>
<div class="banner">
ENN;

if ( $login[0] ) { 
	$html .= "<strong>Loggen In User:</strong> {$login[1]} (ID: ".perms_getidbyname($login[1]).") <strong>Group:</strong> "; 
	$groups = perms_getgroups($login[1]);
	foreach ( $groups as $group ) { $html .= "{$group} "; }
}

$html .= <<<ENN
</div>
<div class="content_block">
ENN;

echo $html;
?>

