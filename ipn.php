<?
// 
// AdRevenue Ad Management System
// index.php
//
// (C) 2008 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

// Include our main lib
include_once("libs/startup.php");

// Get section and action
$action  = $_REQUEST[action];

// Go to the home page if we have no section
if(!$section)
	$section = "pay";
	
// Loadup the section
include_once("modules/pay.php");
$s = new pay;
$s->init();
$s->external();

exit;
?>
