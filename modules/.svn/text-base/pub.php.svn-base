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

class pub extends main
{
	function _default()
	{
		
	}
	
	// Show ad code for retrieval
	function ads()
	{
		$this->output->secure();
		$f = $this->input->f;

		// Grab the zones in question
		//$data = $this->db->getsql("SELECT * FROM adrev_zones WHERE aff_percent > '0' AND status='1' ORDER BY name");
		$data = $this->db->getsql("SELECT a.*,b.name as adtypename FROM adrev_zones a, adrev_ad_types b
										WHERE a.style=b.id AND (a.aff_percent > '0' OR length(a.pub_rates) > 5) AND a.status='1' ORDER BY name");
		$gen = new formgen();
		$gen->startrow("#FFFFFF");
			$gen->column(lib_lang("Click on one of the links below to fetch its ad code."));
		$gen->endrow();
		$tabletop = $gen->gentable("100%", 0, 1, 3, "#FFFFFF");
		
		if(count($data) > 0)
		{
			$gen = new formgen();
			$gen->startrow("#CCCCCC");
			$gen->column("<b>@@Zone@@</b>");
			$gen->column("<b>@@Type@@</b>");
			$gen->column("<b>@@Rate@@</b>","","","","right");
			$gen->column("<b>@@Commission@@</b>","","","","right");
			$gen->column("<b>@@Layout@@</b>");
			$gen->endrow();
			
			foreach($data as $rec)
			{
				$rec[name] = stripslashes($rec[name]);
				$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";
				$gen->startrow($bgcolor);
				$gen->column("<a href=?section=pub&action=code&f[id]=$rec[id] title=\"Get ad code for $rec[name]\">$rec[name]</a>");	
				$gen->column($rec[rate_type]);
				$gen->column(number_format($rec[rate],2),"","","","right");
				if ($rec['aff_percent'] > 0) {
					$gen->column(number_format($rec[aff_percent],2)."@@%@@","","","","right");
				} else {
					$gen->column('@@varies@@',"","","","right");
				}
				$gen->column(lib_lang($rec[adtypename]));
				$gen->endrow();
			}
		}
		
		$this->title = lib_lang("Get Ad Code");
		$this->content = $tabletop . "<p>" . $gen->gentable("500", 0, 1, 3, "#FFFFFF");
		$this->display();
		$this->printpage();		
		exit;		
	}
	
	function code()
	{
		// Show ad code
		$this->output->secure();
		$f = $this->input->f;

		// Loadup the zone
		$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($f['id']));
		if(!$z[0][id])
		{
			$this->output->redirect(lib_lang("Error locating this code!"), "index.php?section=pub&action=ads", 3);
			exit;
		}

		// Show the form		
		$tpl = new XTemplate("templates/zone_pub_code.html");
	
		$domain = $this->default[adrevenue][hostname];
		$url = "$domain$path"."index.php";
		$uid = $_SESSION[user][id];
	
		if(!$z[0][keywords_enable])
		{
			$tpl->assign("URL", "$url?section=serve&id=$f[id]&affid=$uid");
		}
		else
		{
			$tpl->assign("AFFID", $_SESSION[user][id]);
			$tpl->assign("ZONE", $f[id]);
			$tpl->assign("DOMAIN", $domain);			
			$tpl->parse("main.keywords");
			$tpl->assign("URL", "$url?section=serve&id=$f[id]&affid=$uid&keyword=");			
		}
		
		$tpl->assign("ID", $f[id]);
		$tpl->assign("TZ", date("T"));
		
		$tpl->parse("main");
		$this->title = lib_lang("Get Ad Code") . ": " . stripslashes($z[0][name]);	
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();			
		exit;				
	}
	
	// Show stats
	function stats()
	{
		$this->output->secure();
		$f = $this->input->f;
		$uid = $_SESSION[user][id];
		$dates = array('today'=>'Today', 'yesterday' => 'Yesterday', 'last7'=>'Last 7 days', 'thismonth' => 'This Month', 'lastmonth' => 'Last Month');

		// Get the balance
		$bal = $this->db->getsql('SELECT sum(amount) as balance FROM adrev_aff_traffic WHERE affid=?', array($uid));
		$balance = $bal[0]['balance'];

		if (is_numeric($_GET['transfer']) && $_GET['transfer'] > 0 && $_GET['transfer'] <= $balance) {

			// Make credit
			$i = array();
			$i['date'] = time();
			$i['affid'] = $uid;
			$i['adtype'] = 'XFER';
			$i['adid'] = 0;
			$i['ip'] = $_SERVER['REMOTE_ADDR'];
			$i['referer'] = 'Transfer to Advertising credit.';
			$i['amount'] = $_GET['transfer'] * -1;
			$this->db->insert('adrev_aff_traffic', $i);

			// Post to adrev_payments
			$i = array();
			$i['date'] = time();
			$i['userid'] = $uid;
			$i['description'] = 'Transferred publisher credit';
			$i['amount'] = $_GET['transfer'];
			$this->db->insert('adrev_payments', $i);

			$this->output->redirect('@@Amount transferred to advertiser credit@@', 'index.php?section=account', 1);
			exit;
		}

		$start = mktime(0,0,0, date('m'), date('d'), date('Y'));
		$end = mktime(23,59,59, date('m'), date('d'), date('Y'));

		if ($_GET['date'] == 'yesterday') {
			$start = mktime(0,0,0,date('m'), date('d')-1, date('Y'));
			$end = mktime(23,59,59,date('m'), date('d')-1, date('Y'));
		} elseif ($_GET['date'] == 'last7') {
			$start = time() - (86400 * 7);
			$end = time();
		} elseif ($_GET['date'] == 'thismonth') {
			$start = mktime(0,0,0, date('m'), 1, date('Y'));
			$end = mktime(0,0,0, date('m'), date('t'), date('Y'));
		} elseif ($_GET['date'] == 'lastmonth') {
			$start = mktime(0,0,0, date('m')-1, 1, date('Y'));
			$end = mktime(0,0,0, date('m')-1, date('t', $start), date('Y'));
		}

		// By Zone
		$data = $this->db->getsql('SELECT count(a.id) as num, sum(a.amount) as total, a.adtype, b.zone 
									FROM adrev_aff_traffic a, adrev_ads b 
									WHERE a.affid=? AND a.adid=b.id AND (a.date BETWEEN ? AND ?) AND a.amount > 0 
									GROUP BY b.zone 
									ORDER BY b.zone', array($uid, $start, $end));

		// By Ad
		$data2 = $this->db->getsql('SELECT count(*) as num, adid, sum(amount) as earned, sum(spend) AS spend, max(referer) AS referer
									FROM adrev_aff_traffic
									WHERE affid=? AND (date BETWEEN ? AND ?)
									GROUP BY adid', array($uid, $start, $end));

		
		$htlist = lib_htlist_array($dates, $_GET['date']);
		$table = '<form method="GET"><strong>@@View@@: </strong><select name="date" onChange="submit();">' . $htlist . 
					'</select><input type="submit" value="Go" />' . 
					'<input type="hidden" name="section" value="pub" /><input type="hidden" name="action" value="stats" /></form><hr size=1 noshade />';

		$records = array();
		if(count($data) > 0)
		{
			$table .= '<div style="width: 100%; text-align: right;">
						<form method="GET">@@Transfer balance for advertiser credit@@: <input type="text" name="transfer" value="' . number_format($balance,3) . '" size="8" />
						<input type="submit" value="@@Transfer@@" /><input type="hidden" name="section" value="pub" /><input type="hidden" name="action" value="stats" />
					  </div>';

			foreach($data as $rec)
			{
				$records[$rec[zone]] = array($rec[adtype], $rec[num], $rec[total]);
			}
			
			if(count($records) > 0)
			{
				$gen = new formgen();
				$gen->startrow("#CCCCCC");
				$gen->column("<b>".lib_lang("Zone")."</b>");
				$gen->column("<b>".lib_lang("Earned")."</b>","","","","right");
				$gen->column();
				$gen->endrow();
				
				reset($records);
				$earned = 0;
				while(list($zone,$rec) = each($records))
				{
					if($rec[0] == "CPC")
						$type = "Clicks";
					elseif($rec[0] == "CPM")
						$type = "Impressions";
					else
						$type = "Days";
					
					// Get zone name
					$z = $this->db->getsql("SELECT name FROM adrev_zones WHERE id=?", array($zone));
					$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";				
					$gen->startrow($bgcolor);
					$gen->column(stripslashes($z[0][name]));
					$gen->column($rec[2],"","","","right");
					$gen->column("<a href=\"?section=pub&action=download&f[id]=$zone\" title=\"Download to CSV\">@@Download Stats to CSV@@</a>");
					$gen->endrow();	
					
					$earned += $rec[2];
				}
				
				$gen->startrow("#FFFFFF");
				$gen->column();
				$gen->column("<b>".number_format($earned,2) . "</b>","#CCCCCC","","","right");
				$gen->column();
				$table .= $gen->gentable("75%", 0, 1, 3, "#FFFFFF");
			}

			if (count($data2) > 0) {
				$table .= '<br/><h1>@@Detail by Units@@</h1>';

				$gen = new formgen();
				$gen->startrow('#CCCCCC');
				$gen->column('<b>@@Description@@</b>');
				$gen->column('<b>Impressions</b>', '', '', '', 'right');
				$gen->column('<b>@@Earned@@</b>', '', '', '', 'right');
				$gen->column('<b>@@Spend@@</b>', '', '', '', 'right');
				$gen->column('<b>@@Avg@@%', '', '', '', 'right');
				$gen->endrow();

				$hits = 0;
				$earned = 0;
				$spend = 0;
				foreach($data2 as $rec) {
					$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";				
					$gen->startrow($bgcolor);
					if ($rec['adid'] == 0) {
						$gen->column($rec['referer']);
					} else {
						$a = $this->db->getsql('SELECT title,url FROM adrev_ads WHERE id=?', array($rec['adid']));
						$gen->column($a[0]['title'] ? '<a href="'.$a[0]['url'].'" target="_new">'.$a[0]['title'].'</a>' : '[@@Deleted Ad@@]');
					}

					$avg = $rec['earned'] > 0 && $rec['spend'] > 0 ? $rec['earned'] * 100 / $rec['spend'] : 0;
					$gen->column(number_format($rec['num']), '', '', '', 'right');
					$gen->column(number_format($rec['earned'],3), '', '', '', 'right');
					$gen->column(number_format($rec['spend'],3), '', '', '', 'right');
					$gen->column(number_format($avg,1) . '%', '', '', '', 'right');
					$gen->endrow();
					$hits += $rec['num'];
					$earned += $rec['earned'];
					$spend += $rec['spend'];
				}
				$gen->startrow('#FFFFFF');
				$gen->column();
				$gen->column('<b>'.number_format($hits).'<b/>','#CCCCCC', '', '', 'right');
				$gen->column('<b>'.number_format($earned,3).'<b/>','#CCCCCC', '', '', 'right');
				$gen->column("<b>".number_format($spend,2) . "</b>","#CCCCCC","","","right");
				$gen->column();
				$table .= $gen->gentable("75%", 0, 1, 3, "#FFFFFF");
				$table .= '<li><b>@@Impressions@@</b>: @@How many hits your website contributed for each unit@@</li>';
				$table .= '<li><b>@@Earned@@</b>: @@How much you actually earned@@</li>';
				$table .= '<li><b>@@Spend@@</b>: @@How much Revenue you contributed@@</li>';
				$table .= '<li><b>@@Avg@@%</b>: @@The average percentage rate used to calculate your Earnings@@</li>';
			}
		}
		
		$this->title = lib_lang("My Earnings") . ': <span style="color: green;">' . number_format($balance,3) . '</span>' ;
		$this->content = $table;
		$this->display();
		$this->printpage();		
		exit;		
	}
	
	// Show History
	function history()
	{
		$this->output->secure();
		$f = $this->input->f;
		$uid = $_SESSION[user][id];
		
		$gen = new formgen();
		$gen->startrow("#CCCCCC");
		$gen->column("<b>".lib_lang("Date")."</b>");
		$gen->column("<b>".lib_lang("Description")."</b>");
		$gen->column("<b>".lib_lang("Amount")."</b>","","","","right");			
		$gen->endrow();
		
		// Get the data
		$data = $this->db->getsql("SELECT * FROM adrev_payments WHERE userid=? ORDER BY date", array($uid));
		$total = 0;
		if(count($data) > 0)
		{
			foreach($data as $rec)
			{
				$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";				
				$gen->startrow($bgcolor);
				$gen->column(date("M d Y", $rec[date]));
				$gen->column($rec[description]);
				$gen->column(number_format($rec[amount],2),"","","","right");			
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
	
	// Download stats for a zone
	function download()
	{
		$this->output->secure();
		$f = $this->input->f;
		$uid = $_SESSION[user][id];	
		$inlist = "";
		$in = array();
		$ads = $this->db->getsql("SELECT id FROM adrev_ads WHERE zone=?", array($f['id']));
		if(count($ads) > 0)
		{
			foreach($ads as $rec)
				$in[] = $rec[id];
			$inlist = implode(",", $in);
			$this->db->getcsv("SELECT date,adtype,ip,referer,amount FROM adrev_aff_traffic WHERE affid='{$uid}' AND adid IN ({$inlist}) ORDER BY date");
			exit;
		}
		
		$this->output->redirect("@@There are no stats to download@@", "index.php?section=pub&action=stats", 3);
		exit;
	}
}
