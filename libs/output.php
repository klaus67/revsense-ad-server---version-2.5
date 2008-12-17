<?
// 
// RevSense Output controller
// output.php
//
// (C) 2008 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//


class output
{
	var $tpl;
	var $template;
	
	var $title;
	var $heading;
	var $keywords;
	var $description;
	var $crumbs;
	var $meta;
	var $warning;
	var $notice;
	
	var $content;
	var $leftmenu; 
	
	function output()
	{
		global $DEFAULT;
		if(!$this->template) {
			$this->template = $DEFAULT[template];
		}
			
		$this->meta = array();
		$this->warning = array();
		$this->notice = array();
		
		return(1);
	}
	
	// Actually display the content
	function display()
	{
		global $DEFAULT;
		
		// Manage the page

		
		#--- Deprecated -->		
		$this->tpl = new XTemplate($this->template, $this->meta);
		$this->tpl->assign("TITLE", strip_tags($this->title));
		$this->tpl->assign("HEADING", $this->heading ? $this->heading : $this->title);
		$this->tpl->assign("META",  implode("\n", $this->meta)); 
		$this->tpl->assign("BODY", $this->content . $home);
		$this->tpl->assign("DATE", str_replace(" ", "&nbsp;", date("M d, Y h:i:s a (T)")));
		$this->tpl->assign("YEAR", date("Y"));

		// Show user sections
		if($_SESSION[user][id])
		{
			if($_SESSION[user][admin] == 3)
				$this->tpl->parse("main.admin");
			
			// Get balance for this user
			$uid = $_SESSION[user][id];
			$db = new database;
			$b = $db->getsql("SELECT balance FROM adrev_users WHERE id='$uid'");
			$ads = $db->getsql('SELECT count(*) as num FROM adrev_ads WHERE userid=?', array($uid));

			$_SESSION[user][balance] = $b[0][balance];

			if ($b[0]['balance'] == 0 && $ads[0]['num'] > 0) {
				$this->warning[] = '@@Your ads are offline because of zero balance@@. <a href="?section=account">@@Add funds@@</a> @@to your account so your ads will start running@@';
			}

			if ($b[0]['balance'] < 10 && $ads[0]['num'] > 0 ) {
				$this->warning[] = '@@Your balance is low@@. <a href="?section=account">@@Add funds@@</a> @@to your account so your ads will keep running@@';
			}

			// Get publisher balance
			$p = $db->getsql('SELECT sum(amount) as total FROM adrev_aff_traffic WHERE affid=?', array($uid));
			$this->tpl->assign('PBAL', number_format($p[0]['total'], 2));

			if($_SESSION[user][balance] <= $this->default[adrevenue][min_payment] / 2) {
				$this->tpl->assign("BAL", "<font color=red>".number_format($_SESSION[user][balance],2)."</font>");
			} else {
				$this->tpl->assign("BAL", "<font color=green>".number_format($_SESSION[user][balance],2)."</font>");
			}
			
			if($_SESSION[user][admin] == 2) {
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
			$this->tpl->assign('WARNING', $this->warning[0]);
			$this->tpl->parse('main.warning');
		} 

		if (count($this->notice) > 0) {
			$this->tpl->assign('NOTICE', $this->notice[0]);
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
		global $DEFAULT;
		
		// Find any helpstrings
		$section = $_REQUEST[section];
		$action = $_REQUEST[action];
		
		$this->content = preg_replace('/##HELPSTR#(.*?)##/ims', "", $this->content); 
		
		echo $this->content;
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
		$_SESSION['flash'] = $msg;
		header('Location: ' . $url);
		exit();
	}

	function admin()
	{
		if($_SESSION[user][admin] <> 3 || !$_SESSION[user][zid])
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
		if($_SESSION[user][zid])
			return(1);
		
		// Save where we were
		$_SESSION[redir] = $_SERVER[REQUEST_URI];
	
		header("Location: index.php?section=user&action=login");
		exit;
	}

	// Go back to where we came based on the redir block
	function goback()
	{
		global $DEFAULT;
	
		if(!$_SESSION[redir])
		{
			header("Location: index.php");
			exit;
		}

		$url = $_SESSION[redir];
		$_SESSION[redir] = "";
	
		header("Location: $url");
		exit;
	}

}
?>
