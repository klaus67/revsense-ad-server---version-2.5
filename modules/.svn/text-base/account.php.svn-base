<?
// 
// RevSense Ad Management
// ads.php
//
// (C) 2004-2007 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class account extends main
{
	// Show the main page
	function _default()
	{
		$this->output->secure();		
		$f = $this->input->f;
		$uid = $_SESSION[user][id];
		
		$tpl = new XTemplate("templates/account.html");
		
		// Compute the balance and update it
		$b = $this->db->getsql("SELECT sum(amount) as spend FROM adrev_traffic WHERE userid=?", array($uid));
		$spend = $b[0][spend];

		// Grab payment history summary
		$h = $this->db->getsql("SELECT sum(amount) as paid FROM adrev_payments WHERE userid=?", array($uid));		
		$paid = $h[0][paid];

		if ($_SESSION['user']['admin'] == 2) {
			// Get the publisher balance
			$bal = $this->db->getsql('SELECT sum(amount) as balance FROM adrev_aff_traffic WHERE affid=?', array($uid));
			if ($bal[0]['balance'] > 0) {
				$tpl->assign('PUB_BALANCE', number_format($bal[0]['balance'],2));
				$tpl->parse('main.transfer');
			}
		}

		// Update balance
		$balance = $paid - $spend;
		$_SESSION[user][balance] = $balance;
		$ts = time();
		$this->db->getsql("UPDATE adrev_users SET balance=?,balance_update=? WHERE id=?", array($balance, $ts, $uid));
		
		$history = $this->db->getsql("SELECT * FROM adrev_payments WHERE userid=? ORDER BY date DESC", array($uid));
		if(count($history) > 0)
		{
			foreach($history as $rec)
			{
				$bgcolor = iif($bgcolor == "#FFFFFF", "#FFFFEE", "#FFFFFF");
				$tpl->assign("BGCOLOR", $bgcolor);				
				$tpl->assign("DATE", date("M d Y", $rec[date]));
				$tpl->assign("TYPE", iif($rec[amount] > 0, "CREDIT", "DEBIT"));
				$tpl->assign("DESC", stripslashes($rec[description]));
				$tpl->assign("AMOUNT", number_format($rec[amount],2));
				$tpl->parse("main.list");
			}
		}
		
		$tpl->assign("MIN2", number_format($this->default[adrevenue][min_payment], 2));
		$tpl->assign("MINIMUM", number_format($this->default[adrevenue][min_payment],2));
		$tpl->assign("BALANCE", number_format($balance,2));
		$tpl->parse("main");
		$this->title = lib_lang("Manage Payments");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		
		return (TRUE);
	}
	
	// Accept a payment
	function pay()
	{
	}

	
	
}

?>
