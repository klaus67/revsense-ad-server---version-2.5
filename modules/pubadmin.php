<?
// 
// Revsense Ad Management
// ads.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class pubadmin extends main
{
	function _default()
	{
		$this->output->admin();

		$f = $this->input->f;
		$tpl = new XTemplate("templates/pub_user.html");

		if($f[search])
		{
			$f['search'] = '%' . trim(str_replace("*", "%", $f[search])) . '%';
			// Perform a search 
			$users = $this->db->getsql("SELECT * FROM adrev_users
											WHERE admin='2' AND email LIKE ? OR name LIKE ?
												OR organization LIKE ? OR id=?
											ORDER BY email 
											LIMIT 50", array($f['search'], $f['search'], $f['search'], $f['search']));
		} else {
			// Grab the last 50 users
			$users = $this->db->getsql("SELECT * FROM adrev_users WHERE admin='2' ORDER BY date DESC LIMIT 50");
		}

		// Show users (if we have any)
		$admin = array(1=>lib_lang('Advertiser'), 2=>lib_lang('Publisher'), 3=>lib_lang('Administrator'));
		
		if(count($users) > 0)
		{
			// Setup the table header
			$gen = new formgen();
			$gen->startrow("#CCCCCC");
			$gen->column("<b>".lib_lang("ID")."</b>");
			$gen->column("<b>".lib_lang("Name")."</b>");
			$gen->column("<b>".lib_lang("Organization")."</b>");
			$gen->column("<b>".lib_lang("Email")."</b>");
			$gen->column("<b>".lib_lang("Password")."</b>");
			$gen->column("<b>".lib_lang("Status")."</b>");
			$gen->column("<b>".lib_lang("Registered")."</b>");
			$gen->column("<b>".lib_lang("Balance")."</b>","","","","right");
			$gen->column();
			$gen->column();	
			$gen->column();
			$gen->endrow();
				
			foreach($users as $rec)
			{
				$rec[name] = stripslashes($rec[name]);
				$rec[organization] = stripslashes($rec[organization]);
				
				$bal = $this->db->getsql("SELECT sum(amount) as balance FROM adrev_aff_traffic WHERE affid=?", array($rec['id']));
				$rec[balance] = $bal[0][balance];
				
				$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";
				$gen->startrow($bgcolor);
				$gen->column($rec[id]);
				$gen->column("<a href=?section=pubadmin&action=stats&f[id]=$rec[id] title=\"@@View stats for@@ $rec[name]\">$rec[name]</a>");
				$gen->column("<a href=\"$rec[url]\" title=\"Go to $rec[url]\" target=_new>".stripslashes($rec[organization])."</a>");
				$gen->column(stripslashes($rec[email]));
				$gen->column(stripslashes($rec[password]));
				$gen->column(iif($rec[status]==1, "Active", "Inactive"));
				$gen->column(date("M d Y", $rec[date]));
				$gen->column(number_format($rec[balance],2),"","","","right");
				$gen->column("<a href=?section=profile&id=$rec[id]&redir=pubadmin title=\"@@Edit@@ $rec[name]\">@@Edit@@</a>","",1);
				$gen->column("<a href=?section=pubadmin&action=history&f[id]=$rec[id] title=\"@@Payment History@@ $rec[name]\">@@History@@</a>","",1);
				$gen->column("<a href=\"?section=user&action=login&f[email]=$rec[email]&f[password]=$rec[password]\" title=\"@@Login as@@ $rec[name]\">@@Login@@</a>","",1);				
				$gen->endrow();
			}	
			
			$tpl->assign("TABLE", $gen->gentable("100%", 0, 1, 3, "#FFFFFF"));
		}

		$tpl->assign("SEARCH", stripslashes($f[search]));
		$tpl->parse("users");
		$this->title = lib_lang("Manage Publishers");
		$this->content = $tpl->text("users");
		$this->display();
		$this->printpage();		
		exit;		
	}

	// Publisher rates
	function rates() {
		$this->output->admin;
		
		$p = false;
		$this->pr = array();

		$p = false;
		if ($this->default['adrevenue']['pub_rates']) {
			$p = @unserialize($this->default['adrevenue']['pub_rates']);
			if (is_array($p)) {
				$this->pr = $p;
			} else {
				$p = false;
			}
		}		

		// Delete an array item
		if (intval($_GET['destroy']) > 0) {
			$i = intval($_GET['destroy']) -1;
			if ($this->pr[$i]) {
				unset($this->pr[$i]);
			}

			// Renumber the array and save it
			if (count($this->pr) > 0) {
				$tmp = array();
				reset($this->pr);
				foreach($this->pr as $r) {
					$tmp[] = $r;
				}
				// Save the data
				$data = array('value'=>serialize($tmp));
				$this->db->update('adrev_settings', 'name', 'pub_rates', $data);				
			} else {
				// Delete the key altogether
				$this->db->getsql("DELETE FROM adrev_settings WHERE name='pub_rates'");
			}
			$this->output->redirect('Publisher Rate Deleted', 'index.php?section=pubadmin&action=rates');
			exit;
		}
		
		// Add a new item
		if (is_numeric($_GET['min']) && is_numeric($_GET['max']) && is_numeric($_GET['rate'])) {
			// Add to the settings table
			$this->pr[] = array($_GET['min'], $_GET['max'], $_GET['rate']);

			if (!$p) {
				$data = array('name'=>'pub_rates', 'value'=>serialize($this->pr));
				$this->db->insert('adrev_settings', $data);
			} else {
				$data = array('value'=>serialize($this->pr));
				$this->db->update('adrev_settings', 'name', 'pub_rates', $data);				
			}
			$this->output->redirect('Publisher Rate Added', 'index.php?section=pubadmin&action=rates');
			exit;
		}
		
	}
	
	// View the payment history for this person
	function history()
	{
		$this->output->admin();
		$f = $this->input->f;

		$gen = new formgen();
		$gen->startrow("#CCCCCC");
		$gen->column("<b>".lib_lang("Date")."</b>");
		$gen->column("<b>".lib_lang("Description")."</b>");
		$gen->column("<b>".lib_lang("Amount")."</b>","","","","right");			
		$gen->endrow();
		
		// Get the data
		$data = $this->db->getsql("SELECT * FROM adrev_payments WHERE userid=? ORDER BY date", array($f['id']));
		$total = 0;
		if(count($data) > 0)
		{
			foreach($data as $rec)
			{
				$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";				
				$gen->startrow($bgcolor);
				$gen->column(date("M d Y", $rec[date]));
				$gen->column($rec[description]);
				$gen->column(number_format($rec[amount],3),"","","","right");			
				$gen->endrow();
				$total += $rec[amount];				
			}
			
			$gen->startrow("#FFFFFF");
			$gen->column();
			$gen->column();
			$gen->column(number_format($total,2),"#CCCCCC","","","right");
			$gen->endrow();
			
		}
		
		$this->title = lib_lang("Transaction History");
		$this->content = $gen->gentable("400", 0, 1, 3, "#FFFFFF");
		$this->display();
		$this->printpage();		
		exit;
	}
	
	// Pay the affiliate
	function pay()
	{
		$this->output->admin();
		$f = $this->input->f;

		if($f[amount] && $f[description] && $f[date] && $f[id])
		{	
			// Put in the credit
			/*
			$i = array();
			$i[date] = time();
			$i[userid] = $f[id];
			$i[description] = "$f[description] - $f[date]";
			$c = $this->db->getsql("SELECT balance FROM adrev_users WHERE id='$f[id]'");
			$current = $c[0];
			$finalb = $f[amount] + $current[balance];
			$this->db->getsql("UPDATE adrev_users SET balance=$finalb WHERE id='$f[id]'");
			$i[amount] = str_replace(array(",",'$',"-"), "", $f[amount]);
			$this->db->insert("adrev_payments", $i);
			
			$i[amount] = $i[amount] * -1;
			$i[description] = "Traffic - to $f[date]";
			$this->db->insert("adrev_payments", $i);
			*/
			
			// Optionally Delete the logs
			// This will just summarize the logs
				$cutoff = strtotime($f[date]) + 86400;

				// Summarize
				$rec = $this->db->getsql('SELECT sum(amount) as total FROM adrev_aff_traffic WHERE affid=? AND date < ?', array($f[id], $cutoff));
				$i = array();
				$i['date'] = $cutoff;
				$i['affid'] = $f['id'];
				$i['adtype'] = 'EARN';
				$i['adid'] = 0;
				$i['ip'] = $_SERVER['REMOTE_ADDR'];
				$i['referer'] = 'Earnings to ' . $f['date'];
				$i['amount'] = $rec[0]['total'];
				$this->db->insert('adrev_aff_traffic', $i);

				// Payment
				$i = array();
				$i['date'] = $cutoff;
				$i['affid'] = $f['id'];
				$i['adtype'] = 'PAID';
				$i['adid'] = 0;
				$i['ip'] = $_SERVER['REMOTE_ADDR'];
				$i['referer'] = 'Payments on ' . $f['date'];
				$i['amount'] = $rec[0]['total'] * -1;
				$this->db->insert('adrev_aff_traffic', $i);

				// Delete Logs
				$this->db->getsql('DELETE FROM adrev_aff_traffic WHERE adid > 0 AND affid=?', array($f['id']));

			// We're done
			$this->output->redirect("The account was paid", "index.php?section=pubadmin&action=stats&f[id]={$f['id']}", 1);
			exit;
		}
		
		// Grab the balance
		$recs = $this->db->getsql("SELECT count(*) as num, sum(amount) as amount FROM adrev_aff_traffic 
									WHERE affid=?", array($f['id']));
		$balance = $recs[0][amount];
		
		// Show the form
		$form = new formgen();
		$form->comment(lib_lang("You can adjust the account balance of the publisher here."));
		$form->comment(lib_lang("Balance") . ": <b>" . lib_lang('$') . number_format($balance,2) . "</b>");
		$form->input("<b>".lib_lang("Amount")."</b>", "f[amount]", number_format($balance,2), 10);
		$form->input("<b>".lib_lang("Description")."</b>", "f[description]", lib_lang("Publisher Payment"), 40);
		$form->input("<b>".lib_lang("Cutoff Date")."</b>", "f[date]", date("m/d/Y"),10, "MM/DD/YYYY");
		$form->hidden("section", "pubadmin");
		$form->hidden("action", "pay");
		$form->hidden("f[id]", $f[id]);
		
		$this->title = lib_lang("Pay Publisher");
		$this->content = $form->generate("post", lib_lang("Submit"));
		$this->display();
		$this->printpage();	
		exit;		
	}
	
	// Download a publisher's stats
	function download_stats()
	{
		$this->output->admin();
		$f = $this->input->f;
		$this->db->getcsv("SELECT * FROM adrev_aff_traffic WHERE affid='$f[id]' ORDER BY ip");		
		exit;
	}
	
	// View a publisher's stats
	function stats()
	{
		$this->output->admin();
		$f = $this->input->f;
		$tpl = new XTemplate("templates/pub_user.html");
		
		if(!$f[page])
			$f[page] = 1;
		if(!$f[date])
			$f[date] = "thismonth";
		if(!$f[sort])
			$f[sort] = "date DESC,ip";			
		list($startdate, $enddate) = lib_date_range($f['date']);
		
		// Grab the data for that page
		$limit = 100;
		$offset = ($f[page]-1) * $limit;
		
		// Count records in set first
		$sdate = strtotime($startdate);
		$edate = strtotime($enddate) + 86400;
		$recs = $this->db->getsql("SELECT count(*) as num, sum(amount) as amount FROM adrev_aff_traffic 
									WHERE affid=? AND date BETWEEN ? AND ?", array($f['id'], $sdate, $edate));
		$z = $recs[0][num];
		$amount = $recs[0][amount];
		$pages = ceil($z/$limit);
		$prevpage = $f[page] -1;
		$nextpage = $f[page] +1;
		if($f[page] > 1)
			$pager .= "<a href=?section=pubadmin&action=stats&f[id]=$f[id]&f[page]=$prevpage&f[date]=$f[date]&f[sort]=$f[sort]&f[ip]=$f[ip]>&laquo;Previous</a>&nbsp;";
		$pager .= "<b>".number_format($z)."</b> records. Page <font color=red>$f[page]</font> of <b>$pages</b> pages. ";
		if($pages > $f[page])
			$pager .= "&nbsp;<a href=?section=pubadmin&action=stats&f[id]=$f[id]&f[page]=$nextpage&f[date]=$f[date]&f[sort]=$f[sort]&f[ip]=$f[ip]>Next&raquo;</a>";

		// Show up to 20 page selector
		$pagelist = "";
		for($x =1; $x <= $pages; $x++)
		{
			if($x == $f[page])
				$pagelist .= "&nbsp;<b>$x</b>";
			else
				$pagelist .= "&nbsp;<a href=?section=pubadmin&action=stats&f[id]=$f[id]&f[page]=$x&f[date]=$f[date]&f[sort]=$f[sort]&f[ip]=$f[ip]>$x</a>";
			
			if($x >= 20)
				break;
		}
		
		$pager .= "&nbsp;&nbsp;&nbsp;$pagelist";
		
		$tpl->assign("PAGER", $pager);
		
		if($z > 0)
		{
			if($f[ip])
				$extra = "AND ip='$f[ip]'";
			$recs = $this->db->getsql("SELECT * FROM adrev_aff_traffic 
									WHERE affid=? AND (date BETWEEN ? AND ?) {$extra}
									ORDER BY ? LIMIT {$limit} OFFSET {$offset}", array($f['id'], $sdate, $edate, $f['sort']));
									
			$gen = new formgen();
			$gen->startrow("#CCCCCC");
			$gen->column("<b>".lib_lang("Date")."</b>");
			$gen->column("<b>".lib_lang("Type")."</b>");
			$gen->column("<b>".lib_lang("IP")."</b>");
			$gen->column("<b>".lib_lang("Amount")."</b>","","","","right");
			$gen->column("<b>".lib_lang("Page")."</b>");
			$gen->endrow();
			
			foreach($recs as $rec)
			{
				$ref = stripslashes($rec[referer]);
				if(strlen($ref) > 60)
				{
					$ref = substr($ref,0,30) . "..." . substr($ref,-30);
				}
				
				$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";
				$gen->startrow($bgcolor);				
				$gen->column(date("m-d-Y h:i:sa", $rec[date]));
				$gen->column($rec[adtype]);
				$gen->column($rec[ip]);
				$gen->column(number_format($rec[amount],3),"","","","right");
				$gen->column("<A href=\"$rec[referer]\" title=\"Open in new window\" target=\"_new\">$ref</a>");
				$gen->endrow();
			}
			$tpl->assign("TABLE", $gen->gentable("100%", 0, 1, 3, "#FFFFFF"));
		}
		
		$dates = array("today"=>lib_lang('Today'), "yesterday"=>lib_lang('Yesterday'), "thisweek"=>lib_lang('This Week'), 
						"lastweek"=>lib_lang('Last Week'), "thismonth"=>lib_lang('This Month'), 
						"lastmonth"=>lib_lang('Last Month'), all=>lib_lang('All Time'));		
		
		$tpl->assign("DATELIST", lib_htlist_array($dates, $f[date]));
		$tpl->assign("SORTLIST", lib_htlist_array(array('ip,date DESC'=>'IP Address', 'date DESC,ip'=>'Date'), $f[sort]));
		$tpl->assign("ID", $f[id]);
		$tpl->assign("IP", $f[ip]);
		$tpl->assign("BALANCE", number_format($amount,3));
		
		$tpl->parse("stats");
		$this->title = lib_lang("Publisher Statistics");
		$this->content = $tpl->text("stats");
		$this->display();
		$this->printpage();		
		exit;		
	
	}
}

?>
