<?
// 
// RevSense Zone Management
// zone.php
//
// (C) 2004-2006 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class zone extends main
{	
	function _default()
	{
		$this->zone_list();
	}

	// Create a new zone
	function zone_new()
	{
		$this->output->admin();
		$f = $this->input->f;

		if($f[zone_style])
		{
			$z = $this->db->getsql("SELECT * FROM adrev_ad_types WHERE id=?", array($f['zone_style']));
			$f[zone_name] = $z[0]['name'];
		}
		
		if($f[zone_style] && $f[zone_name])
		{
			$t = unserialize($z[0][template]);
			$t[name] = $f[zone_name];
			$t[style] = $f[zone_style];

			if($z[0][id] && count($t) > 0)
			{
				$this->db->insert("adrev_zones", $t);
				$this->output->redirect(lib_lang("Your zone was created"), "index.php?section=zone&action=zone_define&f[id]={$z[0]['id']}", 1);
				exit;
			}
		}

		$this->output->redirect("", "index.php?section=zone", 1);
		exit;
	}

	// Clone this zone for a new ad_type
	function zone_clone()
	{
		$this->output->admin();
		$f = $this->input->f;

		// Get zone info
		$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($f['id']));

		if($f[id] && $f[type_name])
		{
			$y = $z[0];
			unset($y[name]);
			unset($y[rate_type]);
			unset($y[rate]);
			unset($y[description]);
			unset($y[keywords]);
			unset($y[daypart_days]);
			unset($y[daypart_hours]);
			unset($y[default_ad]);
			unset($y[keywords_enable]);
			unset($y[keywords_fuzzy]);
			unset($y[rtype]);
			unset($y[style]);
			unset($y[status]);
			unset($y[auto_approve]);
			unset($y[daypart_enable]);
			unset($y[max_zone_ads]);
			unset($y[id]);

			$i = array();
			$i[name] = stripslashes($f[type_name]);
			$i[template] = serialize($y);
			$this->db->insert("adrev_ad_types", $i);
			$this->output->redirect(lib_lang("The new Ad Type was created"), "index.php?section=zone", 2);
			exit;
		}


		$tpl = new XTemplate("templates/zone_define.html");	
		
		$tpl->assign("ZONES", $this->db->htlist("adrev_zones", "id", "name", $f[id]));
		$tpl->assign("ID", $f[id]);
		$tpl->assign("ZONE_MENU", $this->zone_menu($f[id]));
	
		$types = $this->db->getsql("SELECT id,name FROM adrev_ad_types ORDER BY name");
		foreach($types as $rec)
		{
			$tpl->assign("NAME", $rec[name]);
			$tpl->parse("main.list");
		}
	
		$tpl->parse("main");
		$this->title = "[".stripslashes($z[0][name])."] : " . lib_lang("Create Ad Type");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;		

	}

	// Show the ads we have in this zone
	function zone_ads()
	{
		global $DEFAULT;
				
		$this->output->admin();
		$f = $this->input->f;

		// Delete an ad
		if($_REQUEST[c] == "delete" && $f[adid])
		{
			// See if there are stats
			//$st = $this->db->getsql("SELECT id FROM adrev_traffic WHERE adid='$f[adid]' LIMIT 1");
			//if(!$st[0][id])
			//{
				$this->db->getsql("DELETE FROM adrev_ads WHERE id=?", array($f['adid']));
				$this->db->getsql("DELETE FROM adrev_keyword_map WHERE adid=?", array($f['adid']));
				$this->db->getsql("DELETE FROM adrev_traffic WHERE adid=?", array($f['adid']));
				$this->output->redirect(lib_lang("The ad was deleted."), "index.php?section=zone&action=zone_ads&f[id]=$f[id]", 1);
				exit;
			//}
			//else
			//{
				//$this->output->redirect("@@The ad could not be deleted because stats exist@@", "index.php?section=zone&action=zone_ads&f[id]=$f[id]", 3);
			//}
			//exit;
		}
	
		// Perform commands in this action
		if($_GET[c] == "default" && $f[adid])
			$this->db->getsql("UPDATE adrev_zones SET default_ad=? WHERE id=?", array($f['adid'], $f['id']));
		if($_GET[c] == "activate" && $f[adid])
			$this->db->getsql("UPDATE adrev_ads SET status='1' WHERE id=?", array($f['adid']));
		if($_GET[c] == "deactivate" && $f[adid])
			$this->db->getsql("UPDATE adrev_ads SET status='-1' WHERE id=?", array($f['adid']));			
		if($_GET[c] == "del_default" && $f[id])
			$this->db->getsql("UPDATE adrev_zones SET default_ad='0' WHERE id=?", array($f['id']));
			
		// Get zone info
		$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($f['id']));
		
		// Get the ads
		$binds = array();
		$sqlextra = array();
		$extra = "";
		if($f[id]) {
			$sqlextra[] = "a.zone=?";
			$binds[] = $f['id'];
		}
		if($f[email]) {
			$sqlextra[] = "b.email=?";
			$binds[] = $f['email'];
		}
		if($f[status] <> "all" && $f[status]) {
			$sqlextra[] = "a.status=?";
			$binds[] = $f['status'];
		}
		
		if(count($sqlextra) > 0)
			$extra = "AND " . implode(" AND ", $sqlextra);

		$ads = $this->db->getsql("SELECT a.*,b.email,c.name FROM adrev_ads a, adrev_users b, adrev_zones c
									WHERE a.userid=b.id AND a.zone=c.id $extra
									ORDER BY date DESC LIMIT 100", $binds);

		$tpl = new XTemplate("templates/zone_ads.html");
									
		if(count($ads))
		{
			if($f[id])
				$tpl->parse("main.default_title");
			
			foreach($ads as $rec)
			{
				$exp = $rec[expires] > 0 ? str_replace(" ", "&nbsp;", date("M d Y", $rec[expires])) : "<center>-</center>";
				
				if($f[id])
				{
					$tpl->assign("EMAIL", $f[email]);
					$tpl->assign("ADID", $rec[id]);
					$tpl->assign("ZONEID", $f[id]);
					$tpl->assign("DEFAULT", iif($rec[id] == $z[0][default_ad], "red", "#999999"));
					$tpl->assign("DEFAULTTEXT", iif($rec[id] == $z[0][default_ad], "<font color=red>Default&nbsp;Ad</font>", "set&nbsp;default"));	
					$tpl->parse("main.list.default_row");
				}
				
				$bgcolor = iif($bgcolor == "#FFFFFF", "#FFFFEE", "#FFFFFF");
				$tpl->assign("BGCOLOR", $bgcolor);				
				$tpl->assign("ZONE", str_replace(" ", "&nbsp;", $rec[name]));
				$tpl->assign("ZONEID", $rec[zone]);
				$tpl->assign("ADID", $rec[id]);
				$tpl->assign("ZID", $rec[zid]);
				$tpl->assign("TITLE", iif(trim($rec[title]), htmlentities(stripslashes($rec[title]), ENT_QUOTES), "[ no title ]"));
				$tpl->assign("DATE", str_replace(" ", "&nbsp;", date("M d Y", $rec[date])));
				$tpl->assign("ADEMAIL", $rec[email]);
				$tpl->assign("EMAIL", $f[email]);
				$tpl->assign("EXPIRES", $exp);
				$tpl->assign("USERID", $rec[userid]);
				$tpl->assign("STATUS", $DEFAULT[status][$rec[status]]);
				
				
				
				if($rec[status] == 2)
				{
					// Pending
					$tpl->assign("CHANGEACTION", lib_lang("Activate"));
					$tpl->assign("CHANGESTATUS", "activate");
				}
				elseif($rec[status] == -1)
				{
					// Deactivated
					$tpl->assign("CHANGEACTION", lib_lang("Reactivate"));
					$tpl->assign("CHANGESTATUS", "activate");
				}
				elseif($rec[status] == 1)
				{
					// Active
					$tpl->assign("CHANGEACTION", lib_lang("Deactivate"));
					$tpl->assign("CHANGESTATUS", "deactivate");					
				}
				elseif($rec[status] == 3)
				{
					// Paused
					$tpl->assign("CHANGEACTION", lib_lang("Resume"));
					$tpl->assign("CHANGESTATUS", "activate");						
				}
				$tpl->parse("main.list");
			}
		}
		

		
		if($z[0][default_ad])
		{
			$ad = $this->db->getsql("SELECT id,zid,title FROM adrev_ads WHERE id=?", array($z[0][default_ad]));
			$title = $ad[0][title];
			$id = $ad[0][id];
			$zid = $ad[0][zid];
			$default = "[ <a href=?section=zone&action=zone_ads&c=del_default&f[id]=$f[id]&f[email]=$f[email]&f[status]=$f[status]><b><font color=red>" . lib_lang("Remove default ad") . "</font></b></a> ]";
			$tpl->assign("DEFAULT_AD", $default);
		}
	
		if(!$f[status])
			$f[status] = "all";
	
		$tpl->assign("STATUS", lib_htlist_array($DEFAULT[rstatus], $f[status]));
		$tpl->assign("ZONES", $this->db->htlist("adrev_zones", "id", "name", $f[id]));
		$tpl->assign("EMAIL", $f[email]);
		$tpl->assign("ZONE_MENU", $this->zone_menu($f[id]));

		
		$tpl->parse("main");
		$this->title = lib_lang("Manage Ads");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;		
	}
	
	// List the zones we have
	function zone_list()
	{
		$dates = $_REQUEST['dates'] ? $_REQUEST['dates'] : "all";
		list($sd, $ed) = lib_date_range($dates);
			
		$this->output->admin();		
		$zones = $this->db->getsql("SELECT a.*, b.name as style FROM adrev_zones a, adrev_ad_types b
										WHERE a.style=b.id
										ORDER BY a.name");
										
		$tpl = new XTemplate("templates/zone_list.html");
		if(count($zones) > 0)
		{
			foreach($zones as $rec)
			{
				// Count number of total ads
				$a = $this->db->getsql("SELECT count(*) as num FROM adrev_ads WHERE zone=?", array($rec['id']));
				$tpl->assign("ADS", $a[0][num]);
				
				// Grab stats for this ad
				$stats = $this->db->getsql("SELECT sum(a.clicks) as clicks, sum(a.impressions) as impressions, 
											sum(a.amount) as amount, sum(a.orders) as orders 
											FROM adrev_traffic a, adrev_ads b
											WHERE a.adid=b.id AND b.zone=? 
											AND a.date BETWEEN ? AND ?", array($rec['id'], $sd, $ed));
											
				// Add the stats columns
				$tpl->assign("CLICKS", $stats[0]['clicks'] > 0 ? $stats[0]['clicks'] : 0);
				$tpl->assign("IMPRESSIONS", $stats[0]['impressions'] > 0 ? $stats[0]['impressions'] : 0);
				$tpl->assign("ORDERS", $stats[0]['orders'] > 0 ? $stats[0]['orders'] : 0);
				$tpl->assign("AMOUNT", $stats[0]['amount'] > 0 ? $stats[0]['amount'] : 0);
				
				// Build totals
				$total_clicks += $stats[0]['clicks'] > 0 ? $stats[0]['clicks'] : 0;
				$total_impressions += $stats[0]['impressions'] > 0 ? $stats[0]['impressions'] : 0;
				$total_orders += $stats[0]['orders'] > 0 ? $stats[0]['orders'] : 0;
				$total_amount += $stats[0]['amount'] > 0 ? $stats[0]['amount'] : 0;
				$total_ads += $a[0]['num'] > 0 ? $a[0]['num'] : 0;

				// And number of pending ads
				$a = $this->db->getsql("SELECT count(*) as num FROM adrev_ads WHERE zone=? AND status='2'", array($rec['id']));
				$tpl->assign("PENDING_ADS", $a[0][num]);
				
				$bgcolor = iif($bgcolor == "#FFFFFF", "#FFFFEE", "#FFFFFF");
				
				$tpl->assign("ID", $rec[id]);
				$tpl->assign("BGCOLOR", $bgcolor);
				$tpl->assign("NAME", stripslashes($rec[name]));
				$tpl->assign("STYLE", stripslashes($rec[style]));
				if($rec[status] == 1)
					$tpl->assign("STATUS", lib_lang("Active"));
				elseif($rec[status] == 2)
					$tpl->assign("STATUS", lib_lang("Admin&nbsp;only"));
				else
					$tpl->assign("STATUS", lib_lang("Inactive"));
				$tpl->assign("APPROVE", $rec[auto_approve] ? lib_lang("Automatic") : lib_lang("Manual"));
				$tpl->assign("RATE_TYPE", $rec[rate_type]);
				$tpl->assign("RATE", number_format($rec[rate],2));
				if($rec[ad_sort] == "bid")
					$sorter = "Bidded";
				elseif($rec[ad_sort] == "rand")
					$sorter = "Random";
				elseif($rec[ad_sort] == "asc")
					$sorter = "FIFO";
				elseif($rec[ad_sort] == "desc")
					$sorter = "LIFO";
				$tpl->assign("SORT", $sorter);
				$tpl->assign("PUB", $rec[aff_percent]);
				$tpl->parse("main.list");
			}
		}
		
		$tpl->assign("ZONE_STYLE", $this->db->htlist("adrev_ad_types", "id", "name"));
		
		$tpl->assign("TOTAL_CLICKS", number_format($total_clicks));
		$tpl->assign("TOTAL_IMPRESSIONS", number_format($total_impressions));
		$tpl->assign("TOTAL_ORDERS", number_format($total_orders));
		$tpl->assign("TOTAL_AMOUNT", number_format($total_amount,2));
		$tpl->assign("TOTAL_ADS", number_format($total_ads));
		if($total_clicks > 0 || $total_impressions > 0)
		{
			$tpl->parse("main.totals");
		}
		
		$mdates = array("today"=>lib_lang('Today'), "yesterday"=>lib_lang('Yesterday'), "thisweek"=>lib_lang('This Week'), 
						"lastweek"=>lib_lang('Last Week'), "thismonth"=>lib_lang('This Month'), 
						"lastmonth"=>lib_lang('Last Month'), 'all'=>lib_lang('All Time'));

		$tpl->assign("MDATES", lib_htlist_array($mdates, $dates));

		$tpl->parse("main");
		$this->title = lib_lang("Manage Zones");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;
	}
	
	// Delete a zone
	function zone_delete()
	{
		$this->output->admin();
		$f = $this->input->f;
		
		// Only delete if no ads are in there
		$ads = $this->db->getsql("SELECT id FROM adrev_ads WHERE zone=? LIMIT 1", array($f['id']));
		if($ads[0][id])
		{
			$this->output->redirect(lib_lang("You have to delete ads in this zone first"), "index.php?section=zone&action=zone_ads&f[id]=$f[id]", 4);
			exit;
		}

		$this->db->getsql("DELETE FROM adrev_zones WHERE id=?", array($f['id']));
		$this->output->redirect(lib_lang("The zone was deleted"), "index.php?section=zone	", 1);
		exit;
	}
	
	// Define the main characteristics of zones
	// Also creates a new zone
	function zone_define()
	{
		$this->output->admin();		
		$f = $this->input->f;
		
		// Loadup for editing
		$z = $this->db->getsql("SELECT a.*,b.name as adtypename FROM adrev_zones a, adrev_ad_types b
										WHERE a.style=b.id AND a.id=?", array($f['id']));
		if($f[name])
		{
			$i = array();
			$i[name] = substr($f[name],0,30);
			$i[description] = $f[description];
			$i[status] = $f[status] ? $f[status] : 0;
			$i[auto_approve] = $f[auto_approve] ? $f[auto_approve] : 0;
			$i[rtype] = $f[rtype];
			if($i[rtype] == 2)
				$i[ad_sort] = "bid";	
			$i[rate_type] = $f[rate_type];
			$i[rate] = str_replace(array('$',','), "", $f[rate]);
			$i[aff_percent] = str_replace(array('%','$',','), "", $f[aff_percent]);
			$i[runtime] = $f[runtime];
			$i[units] = str_replace(array('$',','), "", $f[units]);
			$i['pub_only'] = $f['pub_only'] == 1 ? 1 : 0;
			
			// Update the current zone
			$this->db->update("adrev_zones", "id", $f[id], $i);
			$this->output->redirect(lib_lang("The zone was updated"), "index.php?section=zone&action=zone_define&f[id]=$f[id]", 1);				
			exit;
		}
		
		if($z[0][id])
			$f = $z[0];
		
		// Set some defaults
		if(!$f[name])
		{
			if(!$f[style])
				$f[style] = 1;
			if(!$f[status])
				$f[status] = 1;
			if(!$f[auto_approve])
				$f[auto_approve] = 1;
		}
		
		// Show the form
		$tpl = new XTemplate("templates/zone_main.html");	
		$types = $this->default[ad_types];
		
		$tpl->assign("RATE_TYPE", lib_htlist_array($types, $f[rate_type]));
		$tpl->assign("RATE", number_format($f[rate],2));
		$tpl->assign("ID", $f[id]);
		$tpl->assign("NAME", stripslashes($f[name]));
		$tpl->assign("DESCRIPTION", stripslashes($f[description]));
		$tpl->assign("STYLE", $f[adtypename]);
		$tpl->assign("STATUS_$f[status]", "CHECKED");
		$tpl->assign("AUTO_APPROVE_$f[auto_approve]", "CHECKED");
		$tpl->assign("RTYPE_$f[rtype]", "CHECKED");
		$tpl->assign("AFF_PERCENT", $f[aff_percent]);
		$tpl->assign("BUTTON", $f[id] ? lib_lang("Update") : lib_lang("Create Zone"));
		$tpl->assign("RUNTIME", $f[runtime]);
		$tpl->assign("UNITS", $f[units]);
		$tpl->assign('PUB_ONLY', $f['pub_only'] == 1 ? 'CHECKED' : '');
		
		if($f[id])
			$tpl->assign("ZONE_MENU", $this->zone_menu($f[id]));
		
		$tpl->parse("main");
		
		if($f[id])
			$this->title = lib_lang("Edit Zone") . " [" . stripslashes($f[name]) . "]";
		else
			$this->title = lib_lang("Create new Zone");
			
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();			
		exit;
	}
	
	// Manage Format of Ad
	function zone_format()
	{
		$this->output->admin();		
		$f = $this->input->f;
		
		$field_types = $this->default[field_types];
		
		// Loadup the zone
		$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($f['id']));
		if(count($f[ad_format]) == 0)
		{
			$f[ad_format] = unserialize(stripslashes($z[0][ad_format]));		
		}

		// Error finding zone	
		if(!$z[0][id])
		{
			$this->output->redirect(lib_lang("The zone could not be found"), "index.php?section=zone", 3);
			exit;
		}

		// Save the data
		if($f[submit])
		{
			// Cleanup the ad_format array();
			reset($f[ad_format]);
			$n = count($f[ad_format]);
			for($x = 0; $x <= $n; $x++)
			{
				if(!$f[ad_format][$x][type])
					unset($f[ad_format][$x]);
			}

			$i = array();
			$i[ad_sort] = iif($z[0][rtype] == 2, "bid", $f[ad_sort]);
			$i[urls_target] = $f[urls_target];
			$i[urls_hide] = $f[urls_hide] ? 1 : 0;
			$i[urls_tracking] = $f[urls_tracking] ? 1 : 0;
			$i[ad_format] = serialize($f[ad_format]);
			$i[template] = trim(stripslashes($f[template]));
			$this->db->update("adrev_zones", "id", $f[id], $i);

			$this->output->redirect(lib_lang("Zone Format options were updated"), "index.php?section=zone&action=zone_format&f[id]=$f[id]",1);
			exit;			
		}
		
		// Show the form
		$tpl = new XTemplate("templates/zone_format.html");

		$sortlist = array("asc"=>lib_lang("Oldest at top"), "desc"=>lib_lang("Newest at Top"), "rand"=>lib_lang("Random"), "bid"=>lib_lang("By Bid Price"));
		$tpl->assign("AD_SORT", lib_htlist_array($sortlist, $z[0][ad_sort]));
		
		$target = array("_self"=> lib_lang("Open ad in same window"), "_new"=>lib_lang("Open ad in new window"));
		$tpl->assign("URLS_TARGET", lib_htlist_array($target, $z[0][urls_target]));
	
		$tpl->assign("URLS_HIDE", $z[0][urls_hide] ? "CHECKED" : "");
		$tpl->assign("URLS_TRACKING", $z[0][urls_tracking] ? "CHECKED" : "");

		
		// Show the rows
		$row = 0;
		while(list($key, $val) = each ($field_types))
		{
			$row++;
			$tpl->assign("NAME", iif($f[ad_format][$row][name], $f[ad_format][$row][name], $val));
			$tpl->assign("TYPE", $key);
			$tpl->assign("ROW", $row);
			$tpl->assign("CHECKED", iif($f[ad_format][$row][type], "CHECKED", ""));
			$tpl->assign("BGCOLOR", iif($f[ad_format][$row][type], "#D2FFC4", "#FFFFFF"));

			// Non Image formats
			if($key <> "IMAGE")
			{
				$font = iif(!$f[ad_format][$row][font], "Verdana,Arial,Helvetica,sans-serif", $f[ad_format][$row][font]);
				$tpl->assign("FONT", "<input type=text name=\"f[ad_format][$row][font]\" value=\"$font\" size=10>");
				
				$size = iif(!$f[ad_format][$row][size], "1", $f[ad_format][$row][size]);
				$tpl->assign("SIZE", "<input type=text name=\"f[ad_format][$row][size]\" value=\"$size\" size=1>");

				$color = iif(!$f[ad_format][$row][color], "black", $f[ad_format][$row][color]);
				$tpl->assign("COLOR", "<input type=text name=\"f[ad_format][$row][color]\" value=\"$color\" size=6>");				

				$bold = iif(!$f[ad_format][$row][bold], "", "CHECKED");
				$tpl->assign("BOLD", "<input type=checkbox name=\"f[ad_format][$row][bold]\" value=\"1\" $bold>");
				
				// Set some better default sizes
				$size = 20;
				if($key == "TITLE")
					$size = 25;
				if($key == "DESCRIPTION")
					$size = 64;
				if($key == "URL")
					$size = 512;
				if($key == "DISPLAY_URL")
					$size = 25;
				if($key == "EMAIL")
					$size = 64;
				if($key == "PHONE")
					$size = 15;
				if($key == "FAX")
					$size = 15;
				if($key == "CONTENT")
					$size = 8192;
				if(preg_match('/CUSTOM/', $key))
					$size = 32;
				
				$max_length = iif(!$f[ad_format][$row][max_length], $size, $f[ad_format][$row][max_length]);
				$tpl->assign("MAX_LENGTH", "<input type=text name=\"f[ad_format][$row][max_length]\" value=\"$max_length\" size=5>");

				$tpl->assign("MAX_UPLOAD", "&nbsp;");
				$tpl->assign("HEIGHT", "&nbsp;");
				$tpl->assign("WIDTH", "&nbsp;");
			}	
			else
			{
				$tpl->assign("BOLD", "&nbsp;");
				$tpl->assign("COLOR", "&nbsp;");
				$tpl->assign("SIZE", "&nbsp;");
				$tpl->assign("FONT", "&nbsp;");
				$tpl->assign("MAX_LENGTH", "&nbsp;");

				$max_upload = iif(!$f[ad_format][$row][max_length], "65536", $f[ad_format][$row][max_length]);
				$tpl->assign("MAX_UPLOAD", "<input type=text name=\"f[ad_format][$row][max_length]\" value=\"$max_upload\" size=5>");
				
				$height = iif(!$f[ad_format][$row][height], "60", $f[ad_format][$row][height]);
				$tpl->assign("HEIGHT", "<input type=text name=\"f[ad_format][$row][height]\" value=\"$height\" size=4>");				
				
				$width = iif(!$f[ad_format][$row][width], "480", $f[ad_format][$row][width]);
				$tpl->assign("WIDTH", "<input type=text name=\"f[ad_format][$row][width]\" value=\"$width\" size=4>");				
			}	

			$tpl->parse("main.formatlist");
		}
	
		
		// Grab the ad type for this zone
		$style = $z[0][style];
		$s = $this->db->getsql("SELECT * FROM adrev_ad_types WHERE id=?", array($style));

		$tpl->assign("FIELD_TYPES", lib_htlist_array($field_types, '0'));
		$tpl->assign("ID", $f[id]);
		$tpl->assign("ZONE_MENU", $this->zone_menu($f[id]));
		$tpl->assign("TEMPLATE", htmlentities(stripslashes($z[0][template]),ENT_QUOTES));

		// Preview the template
		$ad = $this->db->getsql("SELECT zid FROM adrev_ads WHERE zone=? LIMIT 1", array($f['id']));
		if(!$ad[0][zid])
		{
			$tpl->assign("PREVIEW", lib_lang("You need to place at least one ad in this zone for a preview to be generated"));
		}
		else
		{
			include_once("modules/preview.php");
			$p = new preview();
			$p->main();
			$p->zid = $ad[0][zid];
			$preview = $p->display();
			$tpl->assign("PREVIEW", $preview);
		}
		
		$tpl->parse("main");
		$this->title = lib_lang("Manage") . " [".stripslashes($z[0][name])."] : " . lib_lang("Format");	
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();			
		exit;			
	}
	
	// Manage layout of ads
	function zone_layout()
	{
		$this->output->admin();		
		$f = $this->input->f;
		
		// Loadup the zone
		$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($f['id']));
		if(!$z[0][id])
		{
			$this->output->redirect(lib_lang("The zone could not be found"), "index.php?section=zone", 3);
			exit;
		}

		// Update the zone
		if($f[submit])
		{
			$i = array();
			$i[max_zone_ads] = $f[max_zone_ads];
			$i[max_display_ads] = $f[max_display_ads];
			$i[cols] = $f[cols];
			$i[rows] = $f[rows];
			$i[layout_template] = stripslashes($f[layout_template]);
			$this->db->update("adrev_zones", "id", $f[id], $i);
			
			$this->output->redirect(lib_lang("Layout options were updated"), "index.php?section=zone&action=zone_layout&f[id]=$f[id]",1);
			exit;			
		}
		
		// Setup some defaults
		$default_layout = join("", file("templates/zone_default_layout.html"));
		$z[0][max_zone_ads] = iif(!$z[0][max_zone_ads], 1, $z[0][max_zone_ads]);
		$z[0][cols] = iif(!$z[0][cols], 1, $z[0][cols]);
		$z[0][rows] = iif(!$z[0][rows], 1, $z[0][rows]);
		$z[0][layout_template] = iif(!$z[0][layout_template], $default_layout, $z[0][layout_template]);
		
		// Show the form		
		$tpl = new XTemplate("templates/zone_layout.html");
		$tpl->assign("MAX_ZONE_ADS", $z[0][max_zone_ads]);		
		$tpl->assign("MAX_DISPLAY_ADS", $z[0][max_display_ads]);		
		$tpl->assign("COLS", $z[0][cols]);
		$tpl->assign("ROWS", $z[0][rows]);
		$tpl->assign("LAYOUT_TEMPLATE", htmlentities(stripslashes($z[0][layout_template])));				
		$tpl->assign("ID", $f[id]);
		$tpl->assign("ZONE_MENU", $this->zone_menu($f[id]));
		
		$tpl->parse("main");
		$this->title = lib_lang("Manage") . " [".stripslashes($z[0][name])."] : " . lib_lang("Layout");	
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();			
		exit;			
	}
	
	// Manage Dayparting
	function zone_daypart()
	{
		$this->output->admin();		
		$f = $this->input->f;

		// Loadup the zone
		$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($f['id']));
		if(!$z[0][id])
		{
			$this->output->redirect(lib_lang("The zone could not be found"), "index.php?section=zone", 3);
			exit;
		}

		// Update the zone
		if($f[submit])
		{
			$i = array();
			$i[daypart_enable] = $f[daypart_enable] ? 1 : 0;
			$i[daypart_days] = lib_options($f[daypart_days]);
			$i[daypart_hours] = lib_options($f[daypart_hours]);
			$this->db->update("adrev_zones", "id", $f[id], $i);
			
			$this->output->redirect(lib_lang("Daypart options were updated"), "index.php?section=zone&action=zone_daypart&f[id]=$f[id]",1);
			exit;			
		}

		// Show the form		
		$tpl = new XTemplate("templates/zone_daypart.html");
	
		// Days
		$f[daypart_days] = lib_bit_options($z[0][daypart_days]);
		for($day =0; $day < 7; $day++)
		{
			$tpl->assign("DAY", $day);
			if(in_array($day, $f[daypart_days]))
				$tpl->assign("DAYPART_DAY", "CHECKED");
			else
				$tpl->assign("DAYPART_DAY", "");
				
			$tpl->parse("main.days");
		}
		
		// Hours
		$f[daypart_hours] = lib_bit_options($z[0][daypart_hours]);
		for($hour =0; $hour < 24; $hour++)
		{
			$tpl->assign("HOUR_TITLE", $hour);
			$tpl->parse("main.hour_title");
			
			$tpl->assign("HOUR", $hour);
			if(in_array($hour, $f[daypart_hours]))
				$tpl->assign("DAYPART_HOUR", "CHECKED");
			else
				$tpl->assign("DAYPART_HOUR", "");
			$tpl->parse("main.hours");
		}
		
		$tpl->assign("ID", $f[id]);
		$tpl->assign("TZ", date("T"));
		$tpl->assign("DAYPART_ENABLE", $z[0][daypart_enable] ? "CHECKED" : "");
		$tpl->assign("ZONE_MENU", $this->zone_menu($f[id]));
		
		$tpl->parse("main");
		$this->title = lib_lang("Manage") . "[".stripslashes($z[0][name])."] : " . lib_lang("Dayparting");	
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();			
		exit;		
	}

	// Show Ad Code
	function zone_code()
	{
		$this->output->admin();		
		$f = $this->input->f;

		// Loadup the zone
		$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($f['id']));
		if(!$z[0][id])
		{
			$this->output->redirect(lib_lang("The zone could not be found"), "index.php?section=zone", 3);
			exit;
		}

		// Show the form		
		$tpl = new XTemplate("templates/zone_ad_code.html");
	
		$domain = $this->default[adrevenue][hostname];
		$url = "$domain"."index.php";
	
		if(!$z[0][keywords_enable])
		{
			$tpl->assign("URL", "$url?section=serve&id=$f[id]");
		}
		else
		{
			$tpl->assign("AFFID", 0);
			$tpl->assign("ZONE", $f[id]);
			$tpl->assign("DOMAIN", $domain);			
			$tpl->parse("main.keywords");
			$tpl->assign("URL", "$url?section=serve&id=$f[id]&keyword=");			
		}
		
		$tpl->assign("ID", $f[id]);
		$tpl->assign("TZ", date("T"));
		$tpl->assign("ZONE_MENU", $this->zone_menu($f[id]));

		
		$tpl->parse("main");
		$this->title = lib_lang("Manage") . " [".stripslashes($z[0][name])."] : " . lib_lang("Ad Codes");	
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();			
		exit;		
	}
	
	// Manage Keyword Targetting
	function zone_keywords()
	{
		$this->output->admin();		
		$f = $this->input->f;

		// Loadup the zone
		$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($f['id']));
		if(!$z[0][id])
		{
			$this->output->redirect(lib_lang("The zone could not be found"), "index.php?section=zone", 3);
			exit;
		}
		
		// Update the zone
		if($f[submit])
		{
			$i = array();
			$i[keywords] = strtolower($f[keywords]);
			$i[keywords_enable] = $f[keywords_enable] ? 1 : 0;
			$i[keywords_fuzzy] = $f[keywords_fuzzy] ? 1 : 0;
			$i[keywords_max] = $f[keywords_max] ? $f[keywords_max] : 0;
			$this->db->update("adrev_zones", "id", $f[id], $i);
			
			// Update keywords
			$k = explode("\n", $i[keywords]);
			if(count($k) > 0)
			{
				foreach($k as $keyword)
				{
					// Look for this keyword
					$key = trim(strtolower($keyword));
					$l = $this->db->getsql("SELECT id FROM adrev_keywords WHERE keyword=?", array($key));
					if(!$l[0][id])
					{
						// Add the keyword
						$i = array();
						$i[keyword] = $key;
						$i[fuzzy_keyword] = metaphone($key);
						$i[mincpc] = 0;
						$this->db->insert("adrev_keywords", $i);
					}
				}
			}
			
			$this->output->redirect(lib_lang("Keyword options were updated"), "index.php?section=zone&action=zone_keywords&f[id]=$f[id]",1);
			exit;
		}
		
		// Show the form
		$tpl = new XTemplate("templates/zone_keywords.html");
		$tpl->assign("ID", $f[id]);
		$tpl->assign("KEYWORDS_ENABLE", $z[0][keywords_enable] ? "CHECKED" : "");
		$tpl->assign("KEYWORDS_FUZZY_" . $z[0][keywords_fuzzy], "CHECKED");
		$tpl->assign("KEYWORDS", stripslashes($z[0][keywords]));
		$tpl->assign("KEYWORDS_MAX", $z[0][keywords_max]);
		$tpl->assign("ZONE_MENU", $this->zone_menu($f[id]));
		
		$tpl->parse("main");
		$this->title = lib_lang("Manage") . " [".stripslashes($z[0][name])."] : " . lib_lang("Keywords");	
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();			
		exit;		
		
	}
	
	// Publisher rates
	function rates() {

		$this->output->admin;

		$this->z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($this->input->f['id']));

		$p = false;
		$this->pr = array();

		$p = false;
		if ($this->z[0]['pub_rates']) {
			$p = @unserialize($this->z[0]['pub_rates']);
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
				$data = array('pub_rates'=>serialize($tmp));
				$this->db->update('adrev_zones', 'id', $this->z[0]['id'], $data);				
			} else {
				// Delete the key altogether
				$this->db->getsql("UPDATE adrev_zones SET pub_rates='' WHERE id=?", array($this->z[0]['id']));
			}
			$this->output->redirect('Publisher Rate Deleted', 'index.php?section=zone&action=rates&f[id]=' . $this->z[0]['id']);
			exit;
		}
		
		// Add a new item
		if (is_numeric($_GET['min']) && is_numeric($_GET['max']) && is_numeric($_GET['rate'])) {
			// Add to the settings table
			$this->pr[] = array($_GET['min'], $_GET['max'], $_GET['rate']);

			$data = array('pub_rates'=>serialize($this->pr));
			$this->db->update('adrev_zones', 'id', $this->z[0]['id'], $data);				

			$this->output->redirect('Publisher Rate Added', 'index.php?section=zone&action=rates&f[id]=' . $this->z[0]['id']);
			exit;
		}
		
	}

	// Get the zone menu
	function zone_menu($id=0)
	{
		if(!$id)
			return("");
		
		$tpl = new XTemplate("templates/zone_menu.html");
		$tpl->assign("ID", $id);
		$tpl->parse("main");
		return($tpl->text("main"));
	}
}
?>
