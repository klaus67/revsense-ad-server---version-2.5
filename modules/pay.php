<?
// 
// AdRevenue Ad Management
// pay.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

// Payment Stub Module
// This calls whichever module is set to the default

class pay extends main
{
	var $module;
	var $token;

	// Initialize the correct module
	function init()
	{
		if($this->module)
			return( FALSE );
		
		// Set the default to be PayPal
		if(!$this->default[adrevenue][payment_module])
			$this->default[adrevenue][payment_module] = "paypal";
		
		$mod = "pay." . $this->default[adrevenue][payment_module] . ".php";
		include_once($mod);
		
		// Instantiate module
		$this->module = new payment();
		$this->module->main();
		
		return (TRUE);
	}
	
	// Show the form
	function form()
	{
		// If we are admin, just add the funds
		if($_SESSION[user][admin] == 3)
		{
			// Add the payment
			$j = array();
			$j[date] = time();
			$j[userid] = $_SESSION[user][id];
			$j[description] = "Admin Credit";
			$j[amount] = $this->input->f[amount];
			$this->db->insert("adrev_payments", $j);
			
			// Compute the balance and update it
			$uid = $_SESSION[user][id];
			$b = $this->db->getsql("SELECT sum(amount) as spend FROM adrev_traffic WHERE userid=?", array($uid));
			$spend = $b[0][spend];

			// Grab payment history summary
			$h = $this->db->getsql("SELECT sum(amount) as paid FROM adrev_payments WHERE userid=?", array($uid));		
			$paid = $h[0][paid];

			// Update balance
			$balance = $paid - $spend;
			$ts = time();
			$this->db->getsql("UPDATE adrev_users SET balance=?,balance_update=? WHERE id=?", array($balance, $ts, $uid));

			$this->output->redirect("@@Thank you Administrator@@.", "index.php?section=account", 1);
			exit;			
		}
		else
		{
			$this->init();
			$this->module->form();
		}
		return ( TRUE );
	}
	
	// Process a form
	function process()
	{
		$this->init();
		$this->module->process();
		return ( TRUE );
	}
	
	// External Hook for approval
	function external()
	{
		$this->init();
		$this->module->external();
		return ( TRUE );
	}
	
	// Manual approval of a payment, based on a token
	function approve()
	{
		$this->output->admin();
		$this->init();
		
		$token = $_GET[token];
		if(!$token)
		{
			$this->output->redirect("Invalid approval token", "index.php", 2);
			exit;
		}
		
		$p = $this->db->getsql("SELECT * FROM adrev_tokens WHERE token=? AND status='Pending'", array($token));
		if(!$p[0][id])
		{
			$this->output->redirect("Token not found, or transaction already processed");
			exit;
		}
		else
		{
			// Make the payment
			$i = array();
			$i[date] = time();
			$i[userid] = $p[0][userid];
			$i[description] = "Payment - " . $p[0][txid];
			$i[amount] = $p[0][amount];
			$this->db->insert("adrev_payments", $i);
			
			// Mark the token record as completed
			$id = $p[0][id];
			$this->db->getsql("UPDATE adrev_tokens SET status='Completed' WHERE id=?", array($id));
			
			$this->output->redirect("The payment has been approved", "index.php", 3);
			exit;
		}
		
		return ( FALSE );
	}
	
	// Manual rejection of a payment
	function reject()
	{
		$this->output->admin();
		$this->init();
		
		$token = $_GET[token];
		if(!$token)
		{
			$this->output->redirect("Invalid approval token", "index.php", 2);
			exit;
		}
		
		$p = $this->db->getsql("SELECT * FROM adrev_tokens WHERE token=? AND status='Pending'", array($token));
		if(!$p[0][id])
		{
			$this->output->redirect("Token not found, or transaction already processed");
			exit;
		}
		else
		{			
			// Mark the token record as rejected
			$id = $p[0][id];
			$this->db->getsql("UPDATE adrev_tokens SET status='Rejected' WHERE id=?", array($id));
			
			$this->output->redirect("The payment has been rejected", "index.php", 3);
			exit;
		}
		
		return ( FALSE );		
	}
	
	// Show a success page
	function success()
	{
		$tpl = new XTemplate("templates/account_success.html");
		$tpl->parse("main");
		$this->title = lib_lang("Thank You");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();	
		exit;		
	}
	
	// Show a failure page
	function failure()
	{
		$tpl = new XTemplate("templates/account_failure.html");
		$tpl->parse("main");
		$this->title = lib_lang("We're sorry");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();	
		exit;		
	}
}

?>
