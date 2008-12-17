<?
// 
// AdRevenue Ad Management
// ads.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class useradmin extends main
{
	function _default()
	{
		$this->output->admin();

		$f = $this->input->f;
		$tpl = new XTemplate("templates/admin_user.html");

		if($f[search])
		{
			$f[search] = '%' . trim(str_replace("*", "%", $f[search])) . '%';
			// Perform a search 
			$users = $this->db->getsql("SELECT * FROM adrev_users
											WHERE admin IN (1,3) AND email LIKE ? OR name LIKE ?
												OR organization LIKE ? OR id=?
											ORDER BY email 
											LIMIT 50", array($f['search'], $f['search'], $f['search'], $f['search']));
		}
		else
		{
			// Grab the last 50 users
			$users = $this->db->getsql("SELECT * FROM adrev_users WHERE admin IN (1,3) ORDER BY date DESC LIMIT 50");
		}

		// Show users (if we have any)
		$admin = array(1=>lib_lang('Advertiser'), 2=>lib_lang('Publisher'), 3=>lib_lang('Administrator'));		
		if(count($users) > 0)
		{
			foreach($users as $rec)
			{
				$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";
				$tpl->assign("BGCOLOR", $bgcolor);
				$tpl->assign("ID", $rec[id]);
				$tpl->assign("EMAIL", stripslashes($rec[email]));
				$tpl->assign("TYPE", $admin[$rec[admin]]);
				$tpl->assign("NAME", stripslashes($rec[name]));
				$tpl->assign("ORG", stripslashes($rec[organization]));
				$tpl->assign("PASSWORD", "*********");
				$tpl->assign("STATUS", iif($rec[status]==1, "Active", "Inactive"));
				$tpl->assign("REGISTERED", date("M d Y", $rec[date]));
			
				$tpl->assign("BALANCE", number_format($rec[balance],2));
				$tpl->parse("users.results.list");
			}
			$tpl->parse("users.results");	
		}

		$tpl->assign("SEARCH", stripslashes($f[search]));
		$tpl->parse("users");
		$this->title = lib_lang("Manage Users");
		$this->content = $tpl->text("users");
		$this->display();
		$this->printpage();		
		exit;		
	}

	// View a user
	function view()
	{
		$this->output->admin();
		$f = $this->input->f;
		$tpl = new XTemplate("templates/admin_user.html");

		// Compute the balance and update it
		$b = $this->db->getsql("SELECT sum(amount) as spend FROM adrev_traffic WHERE userid=?", array($f['id']));
		$spend = $b[0][spend];

		// Grab payment history summary
		$h = $this->db->getsql("SELECT sum(amount) as paid FROM adrev_payments WHERE userid=?", array($f['id']));		
		$paid = $h[0][paid];

		// Update balance
		$balance = $paid - $spend;
		if($balance < 0)
		{
		  $balance = 0.00;
		}
		$ts = time();
		$this->db->getsql("UPDATE adrev_users SET balance='$balance',balance_update=? WHERE id=?", array($ts, $f['id']));

		// Grab Payment History records
		$h = $this->db->getsql("SELECT * FROM adrev_payments WHERE userid=?", array($f['id']));
		if(count($h) > 0)
		{
			foreach($h as $rec)
			{
				$bgcolor = iif($bgcolor == "#FFFFFF", "#FFFFEE", "#FFFFFF");
				$tpl->assign("BGCOLOR", $bgcolor);
				$tpl->assign("DATE", date("M d Y", $rec[date]));
				$tpl->assign("TYPE", iif($amount < 0, "DEBIT", "CREDIT"));
				$tpl->assign("DESC", stripslashes($rec[description]));
				$tpl->assign("AMOUNT", number_format($rec[amount],2));
				$tpl->parse("main.history");
			}
		}

		// Load the user
		$u = $this->db->getsql("SELECT * FROM adrev_users WHERE id=?", array($f['id']));
		$user = $u[0];

		// Grab the list of ads
		$a = $this->db->getsql("SELECT a.id,a.title,a.status,a.zid,b.name,b.rate_type  
								FROM adrev_ads a, adrev_zones b 
								WHERE a.zone=b.id AND a.userid=? 
								ORDER BY date DESC LIMIT 50", array($f['id']));
		if(count($a) > 0)
		{
			foreach($a as $rec)
			{
				$bgcolor = iif($bgcolor == "#FFFFFF", "#FFFFEE", "#FFFFFF");
				$tpl->assign("BGCOLOR", $bgcolor);
				$tpl->assign("TITLE", stripslashes($rec[title]));
				$tpl->assign("ZONE", $rec[name]); 
				$tpl->assign("TYPE", $rec[rate_type]);
				$tpl->assign("STATUS", $this->default[status][$rec[status]]);
				$tpl->assign("ZID", $rec[zid]);
				$tpl->parse("main.ads");
			}
		}

		// Setup the user section
		$admin = array(1=>lib_lang('Advertiser'), 2=>lib_lang('Publisher'), 3=>lib_lang('Administrator'));
		$status = array(1=>lib_lang('Active'), 0=>lib_lang('Inactive'));
		
		$tpl->assign("NAME", stripslashes($user[name]));
		$tpl->assign("EMAIL", stripslashes($user[email]));
		$tpl->assign("ID", $user[id]);
		$tpl->assign("ADMIN", lib_htlist_array($admin, $user[admin]));
		$tpl->assign("STATUS", lib_htlist_array($status, $user[status]));
		$tpl->assign("PASSWORD", stripslashes($user[password]));
		$tpl->assign("ORGANIZATION", stripslashes($user[organization]));
		$tpl->assign("COUNTRY", lib_htlist_array($this->default[country], $user[country]));
		$tpl->assign("STREET", stripslashes($user[street]));
		$tpl->assign("CITY", stripslashes($user[city]));
		$tpl->assign("STATE", stripslashes($user[state]));
		$tpl->assign("POSTALCODE", stripslashes($user[postalcode]));
		$tpl->assign("URL", stripslashes($user[url]));
		$tpl->assign("REF", stripslashes($user[ref]));
		$tpl->parse("main.user");

		$tpl->assign("SEARCH", stripslashes($f[search]));
		$tpl->parse("users");

		$tpl->assign("BALANCE", number_format($balance,2));

		$tpl->parse("main");
		$this->title = lib_lang("Manage Users");
		$this->content = $tpl->text("users") . "<br>" . $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;		
	
	}

	// Credit or Debit account
	function addfunds()
	{
		$this->output->admin();
		$f = $this->input->f;

		if($f[action] && $f[amount] && $f[id])
		{
			// Save the amount
			$i = array();
			$i[date] = time();
			$i[userid] = $f[id];
			$i[description] = stripslashes($f[description]);
			$i[amount] = iif($f[action] == "credit", $f[amount], $f[amount] * -1);
			$i[amount] = str_replace(array('$',','), "", $i[amount]);
			$this->db->insert("adrev_payments", $i);

			$this->output->redirect(lib_lang("The funds were applied"), "index.php?section=useradmin&action=view&f[id]=$f[id]",1);
			exit;
		}
		
		$this->output->redirect(lib_lang("Please enter all fields"), "index.php?section=useradmin&action=view&f[id]=$f[id]",1);
		exit;
	}

	// Update the user account
	function edituser()
	{
		$this->output->admin();
		$f = $this->input->f;

		$i = $f;
		unset($i[id]);
		$this->db->update("adrev_users", "id", $f[id], $i);

		$this->output->redirect(lib_lang("The account was updated"), "index.php?section=useradmin&action=view&f[id]=$f[id]",1);
		exit;
	}
	
	function deleteu()
	{
	  // Delete an ad
	    $this->output->admin();
		$f = $this->input->f;
		
		if($_REQUEST[c] == "delete" && $f[id]!= 1)
		{
			  	 
				$this->db->getsql("DELETE FROM adrev_users WHERE id=?", array($f['id']));
				$this->db->getsql("DELETE FROM adrev_ads WHERE userid=?", array($f['id']));
				$this->db->getsql("DELETE FROM adrev_traffic WHERE userid=?", array($f['id']));
				$this->output->redirect(lib_lang("The user was deleted."), "index.php?section=useradmin", 1);
				exit;
			
		}
		else
		$this->output->redirect(lib_lang("You cannot delete the administrator."), "index.php?section=useradmin", 3);
	
	}
}

?>
