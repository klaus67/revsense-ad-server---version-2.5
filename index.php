<?
// 
// RevSense Ad Management System
// index.php
//
// (C) 2004,2005,2006,2007 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//


// Get section and action
$section = $_REQUEST['section'];
$action  = $_REQUEST['action'];

// Include our main lib
include_once("libs/startup.php");

// Determine if we need to install
if(!$DEFAULT['database'])
{
	header("Location: install.php");
	exit;
}

// Go to the home page if we have no section
if(!$section)
	$section = "home";
	
// Call whatever modules we now have
$exc = "modules/" . $section . ".php";
if(file_exists($exc))
{
	// Loadup the section
	include_once($exc);
	$s = new $section;
	
	// Run the action if this is not an install
	if($action != "install")
		$s->main();

	$action = $action ? $action : '_default';
	$s->$action();

	$template = "templates/{$section}/{$action}.php";
	if (file_exists($template)) {
		ob_start();
		include_once($template);	
		$s->output->content = ob_get_contents();
		ob_end_clean();
		$s->output->display();
		$s->output->printpage();
	}
}
else
{
	print "<h2>RevSense Error: [ $section ] not found</h2>";
	print "That module does not exist. Several problems could cause this:<br>";
	print "<ol>";
	print "<li> You entered an invalid request (URL)";
	print "<li> You did not upload files correctly";
	print "<li> You do not have read permissions on the <b>modules</b> directory";
	print "<li> The module you are requesting is an add-on to RevSense";
	print "</ol>";
	print "For help, please <a href=http://www.w3matter.com/support>contact us</a> for support and ";
	print "refer to this screen.";
}

exit;
?>
