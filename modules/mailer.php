<?
// 
// AdRevenue Ad Management System
// mailer.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

//
// Sends messages when accounts are low
// messages go to account holder and a summary to administrator
//
class mailer extends main
{
	var $accounts;
	var $min;
	var $minbal;
	
	function _default()
	{
		set_time_limit(0);
		
		$this->minbal = 20.00;
		
		$this->send();
		
		return(TRUE);
	}
	
	// Cycle through the accounts and determine balances
	function send()
	{
		// Grab list of advertisers
		$this->accounts = $this->db->getsql("SELECT * FROM adrev_users WHERE balance <= ? AND status='1' ORDER BY email", array($this->minbal));
		
		if(count($this->accounts) > 0)
		{
			$admin = array();
			foreach($this->accounts as $rec)
			{
				$tpl = new XTemplate("templates/user_notify_email.txt");
				$tpl->assign("USER", $rec[name]);
				$tpl->assign("SITENAME", $this->default[adrevenue][name]);
				$tpl->assign("URL", $this->default[adrevenue][url]);
				$tpl->assign("LOGIN", $rec[email]);
				$tpl->assign("PASSWORD", $rec[password]);
				$tpl->assign("SITEEMAIL", $this->default[adrevenue][email]);
				$tpl->parse("main");
				$msg = $tpl->text("main");
				
				$admin[] = "$rec[name]\t$rec[email]\t$rec[balance]";
				mail($rec[email], "[" . $this->default[adrevenue][name] . "] " . lib_lang("Account balance low"), $msg, "From: " . $this->default[adrevenue][name] . "<" . $this->default[adrevenue][email] . ">");
			}
			
			$amsg  = lib_lang("Dear Admin") . ",\n\n";
			$amsg .= lib_lang("The following users have low balances") . ":\n\n";
			foreach($admin as $a)
			{
				$amsg .= "$a\n";
			}
			
			// Send message to admin
			mail($this->default[adrevenue][email], "[" . $this->default[adrevenue][name] . "] " . lib_lang("Account Balances Summary"), $amsg, "From: " . $this->default[adrevenue][name] . "<" . $this->default[adrevenue][email] . ">"); 
		}
		
		return(TRUE);
	}
	
}
?>
