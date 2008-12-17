<?
//
// RevSense User Management
//
// user.php
//
// (C) 2008 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class user extends main
{
	function _default()
	{	
	}	
	
	// Forgot password
	function forgot()
	{
		$tpl = new XTemplate("templates/user_forgot.html");	
		$f = $this->input->f;

		if($f[email])
		{
			$rec = $this->db->getsql("SELECT * FROM adrev_users WHERE email=?", array($f['email']));
			if($rec[0][email])
			{
				$tpl = new XTemplate("templates/user_forgot_email.txt");
				$tpl->assign("NAME", stripslashes($rec[0][name]));
				$tpl->assign("EMAIL", $rec[0][email]);
				$tpl->assign("PASSWORD", $rec[0][password]);
				$tpl->parse("main");
				$msg = $tpl->text("main");
				
				$from = "From: <".$this->default[adrevenue][email].">";
				mail($rec[0][email], "[".$this->default[adrevenue][name]."] Login Information", $msg, $from);

				$this->output->redirect(lib_lang("Please check your email address for your login information"), "index.php?section=user&action=login",2);
				exit;
			}
			
			$errormsg = "<li> " . lib_lang("Unable to find that email address");
		}

		$tpl->assign("EMAIL", stripslashes($f[email]));
		$tpl->assign("ERRORMSG", $errormsg);
		
		$tpl->parse("main");
		$this->title = lib_lang("Retrieve your Password");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();	
		exit;		
	}

	// Short registration form
	function register()
	{
		global $DEFAULT;
	
		$f = $this->input->f;

		if($f[email] && $f[name] && $f[password] && $f[postalcode] && $f[city] && $f[state] && $f[country])
		{
			// Check email for duplication		
			$em = $this->db->getsql("SELECT id FROM adrev_users WHERE email=?", array($f['email']));
			if($em[0][id])
				$errormsg .= "<li> " . lib_lang("That email address was already used");
			if(!lib_checkemail($f[email]))
				$errormsg .= "<li> " . lib_lang("Invalid Email Address Entered");
			if (!$f[postalcode])
				$errormsg .= "<li> " . lib_lang("Please enter your Postal/Zip Code"); 
			if(!$f[country])
				$errormsg .= "<li> " . lib_lang("Please choose a country");

			if(!$errormsg)
			{
				$i = array();
			
				$i[email] = trim(strtolower($f[email]));
				$i[country] = $f[country] ? $f[country] : "US";
				$i[password] = $f[password];
				$i[name] = $f[name];
				$i[organization] = $f[organization];
				$i[country] = $f[country];
				$i[street] = $f[street];
				$i[city] = $f[city];
				$i[state] = $f[state];
				$i[postalcode] = $f[postalcode];
				$i[url] = $f[url];
				$i[admin] = $_REQUEST[t] == "pub" ? 2 : 1; 
				$i[zid] = uniqid("");
				$i['date'] = time();
				$i[ip] = $_SERVER['REMOTE_ADDR'];					
				$i[status] = $this->default[adrevenue][confirm_registration] ? 0 : 1;
				$i[ref] = $f[ref];
				$this->db->insert("adrev_users", $i);
					
				// Notify us about new signups
				$d = array();
				reset($i);
				while(list($key, $val) = each($i)) {
					$d[] = strtoupper($key) . '=' . $val;
				}
				$data = implode("\n", $d);
				mail($this->default[adrevenue][email], lib_lang('New Advertiser Signup'), lib_lang('A new advertiser signed up') . "\n", $data);					
				
				// Send a confirmation email
				if($this->default[adrevenue][confirm_registration])
				{
					$tpl = new XTemplate("templates/user_confirm.txt");
					$tpl->assign("ZID", $i[zid]);
					$tpl->parse("main");
					$msg = $tpl->text("main");
					mail($i[email], "[".$this->default[adrevenue][name]."] - " . lib_lang("Welcome - confirm your registration!"), $msg, "From: <".$this->default[adrevenue][email].">");
					$this->output->redirect(lib_lang("Please check your email for confirmation instructions"), "index.php?section=user&action=login", 5);
				}
				else
				{
					$this->output->redirect(lib_lang("Saving your registration"), "index.php?section=user&action=login", 2);
				}
				exit;
			}
		}
		
		// Set default country
		if(!$f[country])
			$f[country] = "US";

		$form = new formgen();

		$adname = "Advertiser";
		if($_REQUEST[t] == "pub")
			$adname = lib_lang("Publisher Registration");
		else
			$adname = lib_lang("Advertiser Registration");
		
		$form->comment(lib_lang("Welcome! Please enter your information below."));
		if($this->default[adrevenue][confirm_registration])
			$form->comment(lib_lang("Please enter a valid email address, since you will receive account activation instructions there."));
			
		$form->comment("<font color=red>$errormsg</font>");
		$form->input("<b>".lib_lang("Email")."</b>", "f[email]", stripslashes($f[email]), 40, lib_lang("Your email will also be your login name"));
		$form->input("<b>".lib_lang("Password")."</b>", "f[password]", stripslashes($f[password]), 20);
		$form->input("<b>".lib_lang("Name")."</b>", "f[name]", stripslashes($f[name]), 40);
		$form->input(lib_lang("Organization"), "f[organization]", stripslashes($f[organization]), 40);
		$form->dropdown("<b>".lib_lang("Country")."</b>", "f[country]", lib_htlist_array($this->default[country], $f[country]));
		$form->input("<b>".lib_lang("Street")."</b>", "f[street]", stripslashes($f[street]), 40);
		$form->input("<b>".lib_lang("City")."</b>", "f[city]", stripslashes($f[city]), 20);
		$form->input("<b>".lib_lang("State")."</b>", "f[state]", stripslashes($f[state]), 10);
		$form->input("<b>".lib_lang("Zip/Postal Code")."</b>", "f[postalcode]", stripslashes($f[postalcode]), 10);		
		$form->input(lib_lang("Url"), "f[url]", stripslashes($f[url]), 50);
		$form->input(lib_lang("Referrer"), "f[ref]", stripslashes($f[ref]), 30);
		$form->hidden("section", "user");
		$form->hidden("action", "register");
		$form->hidden("t", $_REQUEST[t]);
		
		$this->title = $adname;
		$this->content = $form->generate("post", lib_lang("Register"));
		$this->display();
		$this->printpage();	
		exit;
	}	

	// Activate a user
	function activate()
	{
		global $DEFAULT;

		$zid = $_GET[zid];
		
		// Find the profile
		if($zid)
		{
			$z = $this->db->getsql("SELECT id FROM adrev_users WHERE zid=?", array($zid));
			if($z[0][id])
			{
				$this->db->getsql("UPDATE adrev_users SET status='1' WHERE zid=?", array($zid));
				$this->output->redirect(lib_lang("Thank you, your account was activated"), "index.php?section=user&action=login", 2);
				exit;
			}
		}

		
	}	

	// Login
	function login()
	{
		$tpl = new XTemplate("templates/user_login.html");
		$f = $this->input->f;
		
		if($f[email] && $f[password])
		{
		   
		     if(!lib_checkemail($f[email]))
			 {
			  $this->output->redirect(lib_lang("Incorrect login or password"), "index.php?section=user&action=login", 2);
			  exit;
			 }
			 
			$f[email] = stripslashes(strtolower($f[email]));
			$f[password] = stripslashes(strtolower($f[password]));
			
			$rec = $this->db->getsql("SELECT * FROM adrev_users WHERE email=? AND password=? AND status='1'", array($f['email'], $f['password']));
			
			if($rec[0]['zid'])
			{		
				$_SESSION[user] = $rec[0];
				$this->output->goback();
				exit;
			}
			$errormsg = "<li> " . lib_lang("Incorrect login or password");
		}

		$tpl->assign("ERRORMSG", $errormsg);
		$tpl->assign("LOGIN", stripslashes($f[login]));

		if($_SESSION[login_msg] == 1)
		{
			$tpl->parse("main.msg");
			$_SESSION[login_msg] = 0;
		}

		$tpl->parse("main");
		$this->title = lib_lang("Login");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();	
		exit;
	}	

	// Logout
	function logout()
	{
		global $f;

		$_SESSION['user'] = array();
		$this->output->redirect(lib_lang("You are now logged out"), "index.php");
		exit;
	}
	
}
?>
