<?
// 
// AdRevenue Ad Management
// pay.authorize_net.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

// Authorize_net Payment Module
// Encapsulates IPN and web fowarding

class payment extends main
{
	// This sets up the variables for settings
	function mod_vars()
	{
		$s = array();
		$s[] = array('name'=>'man_email', 'label'=>'Notify Email', 'length'=>30);
		
		return($s);
	}
	
	function mod_info()
	{
		$s = array();
		$s[name] = "Manual Payments";
		$s[extern] = FALSE;
		$s[extern_description] = "Manual Payments";
		
		return($s);
	}
	
	function _default()
	{
		
	}
	
	// Get the payment form
	function form()
	{
		// Secured
		$this->output->secure();		
		
		$f = $this->input->f;
		
		$parts = explode(" ", $_SESSION[user][name]);
		
		if(!$f[country])
			$f[country] = "US";
		if(!$f[email])
			$f[email] = $_SESSION[user][email];
		if(!$f[first_name])
			$f[first_name] = trim($parts[0]);
		if(!$f[last_name])
			$f[last_name] = trim($parts[1]);			
		if(!$f[city])
			$f[city] = $_SESSION[user][city];
		if(!$f[state])
			$f[state] = $_SESSION[user][state];
		if(!$f[zip])
			$f[zip] = $_SESSION[user][postalcode];
		if(!$f[address])
			$f[address] = $_SESSION[user][street];
		if(!$f[amount])
			$f[amount] = $f[amount];
		
		// Setup a form
		$form = new formgen();
		if($f[errormsg])
			$form->comment("<li> <font color=red><b>$f[errormsg]</b></font>");
		$form->comment("<i>@@Please fill all applicable fields@@</i><br>&nbsp;");
		$form->comment("<b>@@Address Information@@</b>");
		$form->input("@@First Name@@", "f[first_name]", $f[first_name], 30);
		$form->input("@@Last Name@@", "f[last_name]", $f[last_name], 30);
		$form->input("@@Telephone@@", "f[phone]", $f[phone], 15);
		$form->input("@@Email@@", "f[email]", $f[email], 30);
		$form->input("@@Address@@", "f[address]", $f[address], 50);
		$form->input("@@City@@", "f[city]", $f[city], 20);
		$form->input("@@State@@", "f[state]", $f[state], 20);
		$form->input("@@Zip@@", "f[zip]", $f[zip], 10);
		$form->dropdown("@@Country@@", "f[country]", lib_htlist_array($this->default[country], $f[country]));
		
		// You can comment out these sections or add new fields as necessary
		$form->comment("<b>@@Billing Information@@</b>");
		$form->input("@@CardNumber@@", "f[card_num]", $f[card_num], 30);
		$form->input("@@ExpireDate@@", "f[exp_date]", $f[exp_date], 10, "@@Format should be MM/YYYY@@");
		$form->input("@@Card Code@@", "f[card_code]", $f[card_code], 5, "@@3 or 4 digit number on the back of your card@@");
		$form->hidden("f[amount]", $f[amount]);
		$form->hidden("section", "pay");
		$form->hidden("action", "process");
	
		$this->title = lib_lang("Enter your Credit Card Information");
		$this->content = $form->generate("post", lib_lang("Submit"));
		$this->display();
		$this->printpage();	
		exit;				
	}

	// Authorize the payment
	function process()
	{
		// Secured
		$this->output->secure();
		
		$f = $this->input->f;
		
		// If we have no amount, then go back to the form
		if(!$this->input->f[amount])
			$this->form();
		
		// Save the payment attempt
		$tok = md5(uniqid(rand(), true));
		$i = array();
		$i[userid] = $_SESSION[user][id];
		$i[token] = $tok;
		$i[date] = time();
		$i[amount] = $this->input->f[amount];
		$this->db->insert("adrev_tokens", $i);
		
		$inv = $this->db->getsql("SELECT id FROM adrev_tokens WHERE token=?", array($tok));
		$invoice = $inv[0][id];

		// Get the last insert id
		$last = $this->db->getsql("SELECT id FROM adrev_tokens WHERE token=?", array($tok));
		$invoice = $last[0][id];
		
		// Setup and send the message
		$msg .= "AdRevenue Payment Data:\n\n";
		reset($this->input->f);
		while(list($key,$val) = each($f))
		{
			$msg .= "$key = $val\n";
		}

		$msg .= "\n\nApprove Payment:\n";
		$msg .= $this->default[adrevenue][hostname] . "ipn.php?t=$i[token]";		
		
		mail($this->default[adrevenue][man_email], "[".$this->default[adrevenue][name]."] - Payment Pending", $msg, "From: <".$this->default[adrevenue][email].">");
		
		$this->output->redirect(lib_lang("Your payment was sent to the Administrator. It will be approved shortly."), "index.php?section=account", 3);
		exit;
	}
	
	// Manual payment confirmation
	function external()
	{
		// Load the token record first, this prevents duplicate transactions
		$token = $_GET[t];
		$t = $this->db->getsql("SELECT * FROM adrev_tokens WHERE token=?", array($token));
		
		// assign posted variables to local variables
		if($t)
		{
			$i = array();
			$i[token] = $t;
			$i[txid] = uniqid("");
			$i[status] = "Completed";
			$i[txndate] = time();
			$this->db->update("adrev_tokens", "token", $i[token], $i);
		}
		
		// Accept the payment only once, just in case IPN flakes out!
		if($t[0][status] <> "Completed")
		{
			// Add the payment
			$j = array();
			$j[date] = time();
			$j[userid] = $t[0][userid];
			$j[description] = "Payment completed - $i[txid]";
			$j[amount] = $t[0][amount];
			$this->db->insert("adrev_payments", $j);
			
			// Compute the balance and update it
			$uid = $t[0][userid];
			$b = $this->db->getsql("SELECT sum(amount) as spend FROM adrev_traffic WHERE userid=?", array($uid));
			$spend = $b[0][spend];

			// Grab payment history summary
			$h = $this->db->getsql("SELECT sum(amount) as paid FROM adrev_payments WHERE userid=?", array($uid));		
			$paid = $h[0][paid];

			// Update balance
			$balance = $paid - $spend;
			$ts = time();
			$this->db->getsql("UPDATE adrev_users SET balance=?,balance_update=? WHERE id=?", array($balance, $ts, $uid));			
		}

		$this->output->redirect(lib_lang("Thank You"), "index.php", 2);
		exit;
	}	
	
}
