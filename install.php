<?
// 
// RevSense Ad Management
// install.php
//
// (C) 2008 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//
// Installation Script

include_once("libs/startup.php");

$f = $_REQUEST[f];

$tpl = new XTemplate("templates/install.html");


// Lets get some intermediary information
$port = $_SERVER["SERVER_PORT"] == 80 ? "http" : "https"; 
$path   = "$port://" . $_SERVER[HTTP_HOST] . preg_replace("/install\.php.*?$/i", "", $_SERVER[REQUEST_URI]);
$p = urlencode($path);

// Step 1
// Check Permissions for all writable areas
$writable = array();
if(!is_writable('cache'))
	$writable[] = array("cache","directory");
if(!is_writable('settings.php'))
	$writable[] = array("settings.php", "file");
if(!is_writable('banners'))
	$writable[] = array("banners", "directory");
if(!is_writable('lang/EN.lng'))
	$writable('lang/EN.lng', 'file');

if(count($writable) > 0)
{
	$gen = new formgen();
	foreach($writable as $rec)
	{
		$gen->startrow("#FFFFEE");
		$gen->column("$rec[0] ($rec[1])");
		$gen->endrow();
	}
	
	$out  = "<font size=4 face=Arial,Helvetica,sans-serif><b>Set File & Directory Permissions</b></font><p>";
	$out .= "You need proper permissions for the webserver to write to the following files or directories.<p>";
	$out .= $gen->gentable("300", 0, 1, 3, "#EEEEEE") . "<p>";
	$out .= "You need to fix this problem before continuing.<br>";
	$out .= "<b>For UNIX servers:</b><br>";
	$out .= "<code>> chmod 0777 <b>filename</b></code><br>";
	$out .= "<code>> chmod 0777 <b>directory</b></code><p>";
	$out .= "<b>For Windows servers:</b><br>";
	$out .= "Use your FTP software to change the settings.<p>";
	$out .= "<A href=install.php>Continue when you are done.</a><p>&nbsp;";
	
	$tpl->assign("BODY", $out);
	$tpl->assign("TITLE", "INSTALLATION STEP 1: File Permissions");
	$tpl->parse("main");
	echo $tpl->text("main");
	exit;
}

// Step 2
// Decide what database we want to use, and create the settings file
if(!$DEFAULT[engine] || !$DEFAULT[host] || !$DEFAULT[database] || !$DEFAULT[user])
{
	if($f[engine] && $f[host] && $f[user])
	{
		// Set some psuedo defaults
		$DEFAULT[engine] = $f[engine];
		$DEFAULT[host] = $f[host];
		$DEFAULT[database] = $f[database];
		$DEFAULT[user] = $f[user];
		$DEFAULT[password] = $f[password];
		
		// Test the connection
		$db = new database();
		$db->connect();
		
		if(!$db->errormsg)
		{
			// Write to the settings file
			$fp = fopen("settings.php","w");
			if($fp)
			{
				$schema = @file("cache/revsense.sql");
				if(@count($schema) == 0)
				{
					$errormsg .= "<li> ERROR: Could not access database creating instructions. Please contact W3matter.com";
				}
				else	
				{
					foreach($schema as $s)
					{
						if(preg_match('/CREATE TABLE\s+(.*?)\s/i', $s, $match))
						{
							$table = trim($match[1]);
							if($db->exists($table))
							{
								$found = TRUE;
								$tables[] = "<li> $table";
							}
						}
					}
					
					if($found)
					{
						$errormsg .= "<li> ERROR: some revsense tables currently exist:<br><i>". implode(",", $tables) . "</i><br>They should be dropped first!";
					}
					else
					{
						foreach($schema as $s)
						{
							if(trim($s))
							{
								$db->errormsg = "";
								$db->getsql($s);
								if($db->errormsg)
									$error .= $db->errormsg . "<br>"; 
							}
						}
						
						if($error)
							$errormsg .= "<li> ERROR: There were some errors creating the tables:<br><font size=1>$error</font>";
					}
				}
				
				// Now we add the extra settings
				if(!$errormsg)
				{	
					$db->getsql("INSERT INTO adrev_settings VALUES ('paypal_email','$f[email]')");	
					$db->getsql("INSERT INTO adrev_settings VALUES ('currency','$f[currency]')");
					$db->getsql("INSERT INTO adrev_settings VALUES ('name','$f[organization]')");
					$db->getsql("INSERT INTO adrev_settings VALUES ('url','$f[url]')");
					$db->getsql("INSERT INTO adrev_settings VALUES ('hostname','$f[url]')");
					$db->getsql("INSERT INTO adrev_settings VALUES ('email','$f[email]')");
					$db->getsql("INSERT INTO adrev_settings VALUES ('default_redir','$f[url]')");

					// Add a new user
					$i = array();
					$i[admin] = 3;
					$i[zid] = uniqid("");
					$i[date] = time();
					$i[ip] = $_SERVER[REMOTE_ADDR];
					$i[status] = 1;
					$i[email] = $f[email];
					$i[password] = $f[admin_password];
					$i[name] = "Administrator";
					$i[organization] = $f[organization];
					$i[country] = $f[country];
					$i[url] = $f[url];
					$db->insert("adrev_users", $i);					
				}
				
				// Finally, we create the settings file
				if(!$errormsg)
				{
					fputs($fp, "<?\n");
					fputs($fp, "// revsense Configuration Settings\n");
					fputs($fp, "// Generated on " . date("r") . "\n\n");
					fputs($fp, "\$DEFAULT[engine]='$f[engine]';\n");
					fputs($fp, "\$DEFAULT[host]='$f[host]';\n");
					fputs($fp, "\$DEFAULT[database]='$f[database]';\n");
					fputs($fp, "\$DEFAULT[user]='$f[user]';\n");
					fputs($fp, "\$DEFAULT[password]='$f[password]';\n");
					fputs($fp, "?>");				
					fclose($fp);
					
					header("Location: index.php");
				}
				
			}
			else
			{
				$errormsg = "<li> ERROR creating file <b>settings.php</b>. Make sure permissions are correct.";
			}
		}
		else
		{
			$errormsg = "<li> $db->errormsg";
		}
	}
	
	// Show the form
	$form = new formgen();
	if(!$f[url])
		$f[url] = $path;
	if(!$f[country])
		$f[country] = "US";
	if(!$f[currency])
		$f[currency] = "USD";
	if(!$f[organization])
		$f[organization] = "My Advertising Portal";
		
	$dbs = array("mysql"=>"MySQL");
	if($errormsg)
		$form->comment("<font color=red>$errormsg</font>");
		
	$form->comment("<font size=3><b>Enter your database settings:</b></font>");
	$form->dropdown("<b>DB Type</b>", "f[engine]", lib_htlist_array($dbs, $f[engine]), "Choose the kind of database server you have"); 
	$form->input("<b>Hostname</b>", "f[host]", stripslashes($f[host]), 40, "The Domain Name or IP Address for your Database Server");
	$form->input("<b>Username</b>", "f[user]", stripslashes($f[user]), 20, "Database Login");
	$form->input("Password", "f[password]", stripslashes($f[password]), 20, "Database Password");
	$form->input("<b>DB Name</b>", "f[database]", stripslashes($f[database]), 20, "The name of the database where Revsense Data will live. You should have already created this.");
	$form->comment("<hr size=1><b><font size=3>Enter your Site Settings:</font></b><br>"); 
	$form->input("<b>Admin&nbsp;Email</b>", "f[email]", stripslashes($f[email]), 30);
	$form->input("<b>Admin&nbsp;Password</b>", "f[admin_password]", stripslashes($f[admin_password]), 20);
	$form->input("<b>Site Name</b>", "f[organization]", stripslashes($f[organization]), 30);
	$form->input("<b>URL</b>", "f[url]", stripslashes($f[url]), 50, "The URL where Revsense is installed. (It must have the trailing slash \"/\" at the end).");
	$form->dropdown("<b>Country</b>", "f[country]", lib_htlist_array($DEFAULT[country],$f[country]));
	$form->input("<b>Currency</b>", "f[currency]", $f[currency],5, "Your currency code -- eg: USD");		
	$out = $form->generate("post", "Create Revsense Installation") . "<p>&nbsp;";
	
	$tpl->assign("BODY", $out);
	$tpl->assign("TITLE", "INSTALLATION STEP 2: Database Settings");
	$tpl->parse("main");
	echo $tpl->text("main");
	exit;	
}


exit;
?>
