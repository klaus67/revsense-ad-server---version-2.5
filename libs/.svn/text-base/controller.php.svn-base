<?
// 
// Revsense SuperClass
// controller.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class main
{
	var $db;
	var $http;
	var $user;
	var $input;
	var $default;
	var $section;
	var $tz;
	
	function main()
	{
		global $DEFAULT;
		
		// Set defaults
		$this->default = $DEFAULT;
	
		$this->tz = lib_getmicrotime();
	
		// Setup database lib
		$this->db = new database();
		
		if($this->default['engine'] == "pg")
			$this->db->pg_database($this->default['host'], $this->default['database'], $this->default['user'], $this->default['password'], $this->default['port']);			
		if($this->default['engine'] == "mysql")
			$this->db->mysql_database($this->default['host'], $this->default['database'], $this->default['user'], $this->default['password']);

		// Loadup whatever settings we have
		$settings = array();
		/*
		if(file_exists("cache/settings.cache"))
		{
			$settings = @unserialize(@join("", @file("cache/settings.cache")));
			$s = stat("cache/settings.cache");
			if($s['ctime'] + 300 < time())
				unlink("cache/settings.cache");
		}
		*/

		// Grab from the DB
		if(@count($settings) == 0)
		{
			$settings = $this->db->getsql("SELECT * FROM adrev_settings");
			#$fp = fopen("cache/settings.cache", "w");
			#fputs($fp, serialize($settings));
			#fclose($fp);
		}
		
		if(@count($settings) > 0)
		{
			foreach($settings as $rec)
			{
				$this->default['revsense'][stripslashes($rec['name'])] = stripslashes($rec['value']); 
			}
			$DEFAULT['adrevenue'] = $this->default['revsense'];
		}										
	
		// Process input
		$this->http = new http;
		$this->input = new input;
		$this->output = new output;
		
		return(0);
	}
	
	// Actually display the content
	function display()
	{
		// Set some defaults
		$this->meta = array();
		$this->warning = array();
		$this->notice = array();

		// Get balance for this user
		$uid = $_SESSION['user']['id'];
		$b = $this->db->getsql("SELECT balance FROM adrev_users WHERE id=?", array($uid));
		$ads = $this->db->getsql('SELECT count(*) as num FROM adrev_ads WHERE userid=?', array($uid));
		$this->balance = $b[0]['balance'];
		
		// Get balance for publisher
		if ($_SESSION['user']['role'] == 2) {
			$p = $db->getsql('SELECT sum(amount) as total FROM adrev_aff_traffic WHERE affid=?', array($uid));
		}

		// 
		
		if ($_SESSION['flash']) {
			$this->notice[] = lib_lang($_SESSION['flash']);
			$_SESSION['flash'] = false;
		}
		
		return(true);
		#--- Deprecated -->		
		$this->tpl = new XTemplate($this->template, $this->meta);
		$this->tpl->assign("TITLE", strip_tags($this->title));
		$this->tpl->assign("HEADING", $this->heading ? $this->heading : $this->title);
		$this->tpl->assign("META",  implode("\n", $this->meta)); 
		$this->tpl->assign("BODY", $this->content . $home);
		$this->tpl->assign("DATE", str_replace(" ", "&nbsp;", date("M d, Y h:i:s a (T)")));
		$this->tpl->assign("YEAR", date("Y"));

		// Show user sections
		if($_SESSION['user']['id'])
		{
			if($_SESSION['user']['admin'] == 3)
				$this->tpl->parse("main.admin");
			
			// Get balance for this user
			$uid = $_SESSION['user']['id'];
			$db = new database;
			$b = $db->getsql("SELECT balance FROM adrev_users WHERE id='$uid'");
			$ads = $db->getsql('SELECT count(*) as num FROM adrev_ads WHERE userid=?', array($uid));

			$_SESSION['user']['balance'] = $b['0']['balance'];

			if ($b['0']['balance'] == 0 && $ads['0']['num'] > 0) {
				$this->warning[] = '@@Your ads are offline because of zero balance@@. <a href="?section=account">@@Add funds@@</a> @@to your account so your ads will start running@@';
			}

			if ($b['0']['balance'] < 10 && $ads['0']['num'] > 0 ) {
				$this->warning[] = '@@Your balance is low@@. <a href="?section=account">@@Add funds@@</a> @@to your account so your ads will keep running@@';
			}

			// Get publisher balance
			$p = $db->getsql('SELECT sum(amount) as total FROM adrev_aff_traffic WHERE affid=?', array($uid));
			$this->tpl->assign('PBAL', number_format($p['0']['total'], 2));

			if($_SESSION['user']['balance'] <= $this->default['revsense']['min_payment'] / 2) {
				$this->tpl->assign("BAL", "<font color=red>".number_format($_SESSION['user']['balance'],2)."</font>");
			} else {
				$this->tpl->assign("BAL", "<font color=green>".number_format($_SESSION['user']['balance'],2)."</font>");
			}
			
			if($_SESSION['user']['admin'] == 2) {
				$this->tpl->parse("main.logged_in.pub");
				$this->tpl->parse("main.logged_in");
			} else {
				$this->tpl->parse("main.logged_in");
			}

		}
		else
		{
			$this->tpl->parse("main.logged_out");
		}
		
		/*		
		if ($_SESSION['flash']) {
			$this->notice[] = $_SESSION['flash'];
			$_SESSION['flash'] = false;
		}

		if (count($this->warning) > 0) {
			$this->tpl->assign('WARNING', $this->warning['0']);
			$this->tpl->parse('main.warning');
		} 

		if (count($this->notice) > 0) {
			$this->tpl->assign('NOTICE', $this->notice['0']);
			$this->tpl->parse('main.notice');
		} 

		// Parse and output the page
		$this->tpl->parse("main");
		$this->content = $this->tpl->text("main");
		*/
		
		include_once('templates/main.html');
		
		return(1);
	}	

	// Print the page
	function printpage()
	{
		include_once('templates/main.html');
	}
	
	// Throw an error
	function error($errormsg="", $url="")
	{
		$this->title = "Error!";
		$this->heading = strip_tags($this->title);
		$this->redirect($errormsg, $url, 5);
		exit;
	}
	
	// Redirect to another page
	function redirect($msg="", $url = "", $timeout=2)
	{
		$_SESSION['flash'] = lib_lang($msg);
		header('Location: ' . $url);
		exit();
	}

	function admin()
	{
		if($_SESSION['user']['admin'] <> 3 || !$_SESSION['user']['zid'])
		{
			$this->redirect("You must be an administrator!", "index.php?section=user&action=login");
			exit;			
		}
		return(1);
	}
	
	// Manage security and redirection
	function secure()
	{	
		// We're ok, so just go back
		if($_SESSION['user']['zid']) {
			return(1);
		}
		
		// Save where we were
		$_SESSION['redir'] = $_SERVER['REQUEST_URI'];
	
		header("Location: index.php?section=user&action=login");
		exit;
	}

	// Go back to where we came based on the redir block
	function goback()
	{
		global $DEFAULT;
	
		if(!$_SESSION['redir'])
		{
			header("Location: index.php");
			exit;
		}

		$url = $_SESSION['redir'];
		$_SESSION['redir'] = "";
	
		header("Location: $url");
		exit;
	}
	
}
?>
