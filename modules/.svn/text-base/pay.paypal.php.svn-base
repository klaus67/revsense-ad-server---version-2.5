<?
// 
// AdRevenue Ad Management
// pay.paypal.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

// Paypal Payment Module
// Encapsulates IPN and web fowarding

class payment extends main
{
	
	// This sets up the variables for settings
	function mod_vars()
	{
		$s = array();
		$s[] = array('name'=>'paypal_email', 'label'=>'Paypal&nbsp;Email', 'length'=>30);
		$s[] = array('name'=>'paypal_item', 'label'=>'Paypal&nbsp;Item', 'length'=>10);
		
		return($s);
	}
	
	function mod_info()
	{
		$s = array();
		$s[name] = "Paypal";
		$s[extern] = TRUE;
		$s[extern_description] = "Your Paypal IPN URL";
		
		return($s);
	}	
	
	// Manages IPN
	function _default()
	{
		
	}
	
	// Get the payment form
	function form()
	{
		if($this->input->f[amount] >= $this->default[adrevenue][min_payment])
			$this->process();
		else
			$this->output->redirect("Please enter a valid amount", "index.php?section=account", 2);
			
		exit;
	}
	
	// Authorize the payment at the gateway
	function process()
	{
		// Secured
		$this->output->secure();
		
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
		
		// Get the last insert id
		$last = $this->db->getsql("SELECT id FROM adrev_tokens WHERE token=?", array($tok));
		$invoice = $last[0][id];
		
		// Setup the URL
		$url = "https://www.paypal.com/xclick/?";
		$i = array();
		$i[business] = $this->default[adrevenue][paypal_email];
		$i[receiver_email] = $this->default[adrevenue][paypal_email];
		$i[item_name] = $this->default[adrevenue][name] . " - " . lib_lang("Advertising");
		$i[item_number] = $this->default[adrevenue][paypal_item];
		$i[quantity] = 1;
		$i[amount] = str_replace(array('$',',',' '), "", $this->input->f[amount]);
		$i[page_style] = "PayPal";
		$i[no_shipping] = "1";
		$i['return'] = $this->default[adrevenue][hostname] . "index.php?section=pay&action=success";
		$i['cancel'] = $this->default[adrevenue][hostname] . "index.php?section=pay&action=failure";
		$i[no_note] = 1;
		$i[custom] = $tok;
		$i[currency_code] = $this->default[adrevenue][currency];
		$i[invoice] = $invoice;		
		$i[lc] = $_SESSION[user][country];
		$i[notify_url] = $this->default[adrevenue][hostname] . "ipn.php";
		
		$query = array();
		while(list($key, $val) = each($i))
		{
			$query[] = "$key=" . urlencode($val);
		}
		$url .= implode("&", $query);
		
		// Forward payment to Paypal
		header("Location: $url");
		exit;
	}

	// This is an internal confirmation
	// Via Paypal
	function external()
	{
		// Our external system will call this
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';

		foreach ($_POST as $key => $value) 
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		// post back to PayPal system to validate
		$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
		$fp = fsockopen('www.paypal.com', 80, $errno, $errstr, 30);
		
		// Paypal Testing Site
		#$fp = fsockopen('www.eliteweaver.co.uk', 80, $errno, $errstr, 30);
		
		fputs($fp, $header . $req);
		fclose($fp);

		// Load the token record first, this prevents duplicate transactions
		$token = $_POST['custom'];
		$t = $this->db->getsql("SELECT * FROM adrev_tokens WHERE token=?", array($token));
		
		// assign posted variables to local variables
		if($_POST['custom'])
		{
			$i = array();
			$i[token] = $_POST['custom'];
			$i[txid] = $_POST['txn_id'];
			$i[status] = $_POST['payment_status'];
			$i[txndate] = time();
			$this->db->update("adrev_tokens", "token", $i[token], $i);
		}
		
		// Accept the payment only once, just in case IPN flakes out!
		if($_POST['payment_status'] == "Completed")
		{
			// Add the payment
			$j = array();
			$j[date] = time();
			$j[userid] = $t[0][userid];
			$j[description] = "Paypal Payment received - $i[status] - $i[txid]";
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

		return( TRUE);
	}
}

?>
