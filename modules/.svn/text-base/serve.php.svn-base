<?
//
// RevSense Ad Management
// serve.php
//
// (C) 2004-2007 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class serve extends main
{
	var $zone;
	var $z;
	var $affid;
	
	var $rows;
	var $cols;
	var $ad_sort;
	var $output;
	var $keywordid;
	var $keywordrate;
	var $keyword;
	var $fuzzy;
	
	var $dup;
	
	var $adlist;

	function _default()
	{
		// Our Duplicate flag
		$this->dup = 1;
		
		// Get variables
		if(!$this->zone)
			$this->zone = $_GET['id'];
		if(!$this->affid)
			$this->affid = $_GET['affid'] ? $_GET['affid'] : 0;
		if(!$this->rows)
			$this->rows = $_GET['rows'] ? $_GET['rows'] : 0;
		if(!$this->cols)
			$this->cols = $_GET['cols'] ? $_GET['cols'] : 0;
		if(!$this->ad_sort)
			$this->ad_sort = $_GET['ad_sort'] ? $_GET['ad_sort'] : "asc";
		if(!$this->format)
			$this->format = $_GET['output'] ? $_GET['output'] : "html";
		if(!$this->keyword)
			$this->keyword = $_GET['keyword'];
			
		$this->keywordid = 0;
		$this->keywordrate = 0.00;
	
		// Setup default headers
		// P3P: CP="NOI DSP COR PSAo PSDo OUR BUS OTC"
		if (!$this->default['adrevenue']['p3p']) {
			header('P3P: CP="NOI DSP COR PSAo PSDo OUR BUS OTC"');
		} else {
			header('P3P: CP="' . $this->default['adrevenue']['p3p'] . '"');
		}

		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("X-Vendor: W3matter LLC | RevSense | http://www.revsense.com"); 
		
		// Get the zone
		if(!$this->zone)
		{
			return("");
		}
			
		$this->z = lib_cache_get("zone", $this->zone);

		if(!$this->z)
		{
			$p = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($this->zone));
			$this->z = $p[0];
			lib_cache_put("zone", $this->zone, $p[0]);
		}
		// Check if this zone is active
		if($this->z['status'] == 0)
			return("");
			
		// Check dayparting
		if($this->z['daypart_enable'] == 1)
		{
			$day = date("w");
			$hour = date("G");
			if(!in_array($hour, lib_bit_options($this->z['daypart_hours'])))
				return("");
				
			if(!in_array($day, lib_bit_options($this->z['daypart_days'])))
				return("");
		}
		
		// Check geotargetting
		// **** This function will be added in a point release **** //
		
		// Override some zone settings that a user might have passed in
		if($_GET['rows'] > 0)
			$this->z['rows'] = $_GET['rows'];
		if($_GET['cols'] > 0)
			$this->z['cols'] = $_GET['cols'];
		if($_GET['max_display_ads'] > 0)
			$this->z['max_display_ads'] = $_GET['max_display_ads'];
		if($_GET['ad_sort'])
			$this->z['ad_sort'] = $_GET['ad_sort'];
		if($_GET['rows'] && $_GET['cols'])
			$this->z['max_display_ads'] = $_GET['rows'] * $_GET['cols'];
		if(!$_GET['fuzzy'])
			$this->z['keywords_fuzzy'] = $this->fuzzy ? $this->fuzzy : $this->z['keywords_fuzzy'];

		// Get ads	
		if(!$this->ads())
		{
			return("");
		}
		
		// Update the log and billing info
		$this->impressions();

		include_once("modules/preview.php");
		$x = 0;
		$c = new preview;
		$c->affid = $this->affid;
		$c->keyid = $this->keywordid;
		if($this->format == "js" || $this->format == "html" || $this->format == "raw")
		{
			reset($this->adlist);
			if($this->z['layout_template'])
				$tpl = new XTemplate("MEM", "<!-- BEGIN: main -->\n" . $this->z['layout_template'] . "\n<!-- END: main -->");
			else
			
				$tpl = new XTemplate("MEM", "<!-- BEGIN: main -->\n" . 
										join("", file("templates/zone_default_layout.html")) . 
										"\n<!-- END: main -->\n");
			foreach($this->adlist as $rec)
			{
				$x++;
				$parsed = 0;
				$c->zid = $rec['zid'];
				$oz = $c->format_ad("html");
				$tpl->assign("AD", $oz);
				$tpl->parse("main.row.col");
				if($x >= $this->z['cols'])
				{
					$x = 0;
					$parsed = 1;
					$tpl->parse("main.row");
				}
			}
		
			// Finish up parsing
			if(!$parsed)
				$tpl->parse("main.row");
		
			$tpl->parse("main");
			$out = $tpl->text("main");
			
			if($this->format == "js")
			{
				$o1 = str_replace("'","\'",$out);
				$o1 = str_ireplace('<script', "<scr'+'ipt", $o1);
				$o1 = str_ireplace('</script>', "</scr'+'ipt>", $o1);
				$o = explode("\n", $o1);
				$oo = array();
				if(count($o) > 0)
				{
					foreach($o as $rec)
					{
						if(strlen(trim($rec)) > 0)
							$oo[] = trim($rec);
					}
				}
			
				$date = date("r");
				$nocache = md5(uniqid("")) . " | " . $_SERVER['REMOTE_ADDR'] . " | " . iif($this->dup, 'DUP', '');
				$out  = "// W3matter.com | RevSense | http://www.w3matter.com\n";
				$out .= "// $date\n";
				$out .= "// Ad Code: $nocache\n\n";
				
				$out .= "document.write('" . implode("');\ndocument.write('", $oo) . "');\n";	
				header("Content-type: text/javascript");
				echo $out;
				exit;
			}
		}
		elseif($this->format == "xml")
		{
			// XML output
			$out = '<?xml version="1.0" encoding="UTF-16"?>'."\n";
			reset($this->adlist);
			foreach($this->adlist as $ad)
			{
				$c->zid = $ad['zid'];
				$out .= $c->format_ad("xml");
			}
			header("Content-type: text/xml");
		}
		else
		{
			// CSV output
			reset($this->adlist);
			foreach($this->adlist as $ad)
			{
				$c->zid = $ad['zid'];
				$out .= $c->format_ad("csv");
			}
			header("Content-type: text/csv");
		}
		
		$net = lib_getmicrotime() - $this->tz;
		header("X-Server-Time: $net");
		echo $out;
		return ("");
	}

	// Do the billing for these ads
	function impressions()
	{
		// Instantiate the cookielib
		$c = new cookielib();

		// Iterate through the ads
		reset($this->adlist);
		$inlist = array();
		foreach($this->adlist as $ad)
		{
			// Check if we are below the impressions window
			$k = $c->get("_i_".$ad['id']."_".$this->keywordid);
			$win = time() - $k;
			if($k > 0 && $win < $this->default['adrevenue']['dup_impressions'])
			{
				continue;
			}
			
			// We have at least 1 non-duplicate ad
			$this->dup = 0;
			
			// Save the impressions count
			$c->set("_i_".$ad['id']."_".$this->keywordid, time());
			
			// Try to load this record
			$date = date("Y-m-d");
			$a = $this->db->getsql("SELECT * FROM adrev_traffic WHERE adid=? AND date=? AND keywordid=?", array($ad['id'], $date, $this->keywordid));

			// Calculate rate to post here
			$t = time();
			if($this->z['rate_type'] == "CPM")
			{
				// Portion of cost per 1000 impressions
				$amount = ($ad['bid']*1) ? $ad['bid'] / 1000 : $this->z['rate'] / 1000;
				// Update ad units
				$this->db->getsql("UPDATE adrev_ads SET total_units=total_units+1 WHERE id=?", array($ad['id']));
				$ad['total_units'] += 1;
			}
			elseif($this->z['rate_type'] == "CPD")
			{
				// Cost per day
				if(!$a[0]['id'])
				{
					$amount = $ad['bid'] > 0 ? $ad['bid'] : $this->z['rate'];
					$this->db->getsql("UPDATE adrev_ads SET total_units=total_units+1 WHERE id=?", array($ad['id']));
					$ad['total_units'] += 1;
				}
				else
					$amount = 0; // we already posted it today!
			}
			elseif($this->z['rate_type'] == 'CPI')
			{
				// Cost per impression
				$amount = ($ad['bid']*1) ? $ad['bid'] : $this->z['rate'];
				// Update ad units
				$this->db->getsql("UPDATE adrev_ads SET total_units=total_units+1 WHERE id=?", array($ad['id']));
				$ad['total_units'] += 1;
			
			}
			else
			{
				$amount = 0;
			}

			// Log traffic stats and amounts
			if(!$a[0]['id'])
			{
				// Add a new traffic record
				$i = array();
				$i['userid'] = $ad['userid'];
				$i['date'] = $date;
				$i['adid'] = $ad['id'];
				$i['keywordid'] = $this->keywordid;
				$i['impressions'] = 1;
				$i['amount'] = $amount;
				$this->db->insert("adrev_traffic", $i);
			}
			else
			{
				// Update it
				$amount = $amount ? $amount : 0;
				$id = $a[0]['id'];
				$this->db->getsql("UPDATE adrev_traffic SET impressions=impressions+1, amount=amount+$amount WHERE id=?", array($id));
			}

			// Update real time balance if we actually have an amount
			$t = time();
			if($amount > 0)
			{
				$this->db->getsql("UPDATE adrev_users SET balance=balance-{$amount}, balance_update=? WHERE id=?", array($t, $ad['userid']));

				if($this->affid > 0)
				{
					$aff_amount = $amount * ($this->z['aff_percent'] / 100);
					$pub_rates = false;

					// Check if we have tiered affiliate rates
					if ($this->z['pub_rates'] && !$aff_amount) {
						$pub_rates = @unserialize($this->z['pub_rates']);
						// Zone affiliate tiered rates
					} elseif ($this->default['adrevenue']['pub_rates'] && !$aff_amount) {
						// Global affiliate tiered rates
						$pub_rates = @unserialize($this->default['adrevenue']['pub_rates']);
					}

					// Set the apropro tiered affiliate rate
					if (is_array($pub_rates) && $aff_amount == 0) {
						$sp = $this->db->getsql('SELECT sum(amount) as total FROM adrev_aff_traffic WHERE affid=?', array($this->affid));
						if ($sp[0]) {
							$sp[0]['total'] = $sp[0]['total'] > 0 ? $s[0]['total'] = 0 : 0;
							foreach($pub_rates as $rate) {
								if ($sp[0]['total'] >= $rate[0] && $sp[0]['total'] <= $rate[1] && $rate[2] > 0) {
									$aff_amount = $amount * ($rate[2] / 100);
									break;
								}
							}
						}
					}

					if ($aff_amount > 0) {
						$i = array();
						$i['date'] = time();
						$i['affid'] = $this->affid;
						$i['adtype'] = $this->z['rate_type'];
						$i['adid'] = $ad['id'];
						$i['ip'] = $_SERVER['REMOTE_ADDR'];
						$i['referer'] = $_SERVER['HTTP_REFERER'];
						$i['amount'] = $aff_amount;
						$i['spend'] = $amount;
						$this->db->insert("adrev_aff_traffic", $i);
					}
				}
			}
			
			// Attempt to expire this ad
			if(($ad['total_units'] >= $ad['units'] && $ad['units'] > 0) || ($ad['expires'] > 0 && $ad['expires'] <= time()))
			{
				$this->db->getsql("UPDATE adrev_ads SET status=-2 WHERE id=?", array($ad['id']));
			}
			
			// Try to expire based on ad spend
			if ($ad['spend_limit'] > 0) {
				// Get the ad balance (this sucks)
				$ab = $this->db->getsql('SELECT sum(amount) as total FROM adrev_traffic WHERE adid=?', array($ad['id']));
				if ($ab[0]['total'] > 0 && $ab[0]['total'] >= $ad['spend_limit']) {
					# Expire the ad
					$this->db->getsql("UPDATE adrev_ads SET status=-2 WHERE id=?", array($ad['id']));
				}
			}

		}

		return( TRUE );
	}

	
	
	// Grab ads for this zone
	function ads()
	{
		// Set the order for ads in this zone
		$order = "";
		if($this->z['ad_sort'] == "asc")
			$order = "ORDER BY a.id";
		elseif($this->z['ad_sort'] == "desc")
			$order = "ORDER BY a.id DESC";
		elseif($this->z['ad_sort'] == "bid")
			$order = "ORDER BY  a.bid DESC";
			
		if($this->z['keywords_enable'] <> 1)
		{
			
			
			$ads = lib_cache_get("zoneads", $this->zone);
			
			// Just grab all the active ads in this zone
				$ads = $this->db->getsql("SELECT a.id,a.userid,a.bid,a.zid,a.daypart_days,
											a.daypart_hours,a.startdate,a.expires, a.units, a.total_units
										FROM adrev_ads a, adrev_users b 
										WHERE a.userid=b.id AND a.zone=? 
											AND a.status = '1' AND b.balance > 0 $order", array($this->zone));
				
				if(count($ads) > 0 && $this->default['adrevenue']['cache'] > 0)
				{
					lib_cache_put("zoneads", $this->zone, $ads);
				}
			
		}
		else
		{
			// If there is no keyword
			if(!trim($this->keyword))
				return ( FALSE );
		
			// Try to find a keyword
			$keyword = strtolower(trim($this->keyword));
			if(!$this->z['keywords_fuzzy'])
			{
				// Exact matching
				$keywordid = $this->db->get_keyword($keyword,0);
			}
			else
			{
				// Fuzzy matching
				// Delete stopwords first
				#$keyword = trim(lib_stopwords($keyword));

				// Compute metaphone now
				$mphone = metaphone($keyword);
				if(!$mphone)
					return ( FALSE );
					
				$keywordid = "";
				$words = $this->db->getsql("SELECT * FROM adrev_keywords WHERE fuzzy_keyword=?", array($mphone));
				if(count($words) > 0)
				{
					// Look for the one that is closest using a reverse bubble sort
					$keywordid = $words[0]['id'];
					$z = 100;
					foreach($words as $w)
					{
						// Lookup the distance between these candidates we got back
						// for the best fuzzy match
						$l = levenshtein($w['keyword'], $keyword);
						if($l <= $z)
						{
							$keywordid = $w['id'];
							$z = $l;
						}
					}
				}
			}

			// We have no keyword ID
			if(!$keywordid)
			{
				return ( FALSE );
			}
			else
			{
				// Grab the ID and the keyword rate
				$this->keywordid = $keywordid;
				$w = $this->db->getsql("SELECT mincpc FROM adrev_keywords WHERE id=?", array($keywordid));
				$this->keywordrate = $w[0]['mincpc'] ? $w[0]['mincpc'] : $this->default['adrevenue']['min_bid'];
			}

			//Try from the keyword cache
			$ads = lib_cache_get("zoneads" . $keywordid, $this->zone);

			// If the cache is empty, grab from the DB
			
			
				// Decide on the sort order
				if($this->z['ad_sort'] == "bid")
					$order = "ORDER BY c.bid DESC";

				// Grab ads with a specific keyword id
				$ads = $this->db->getsql("SELECT a.id,a.userid,a.bid,a.zid,a.daypart_days,
											a.daypart_hours,a.startdate,a.expires,a.units,a.total_units
										FROm adrev_ads a, adrev_users b, adrev_keyword_map c
										WHERE a.userid=b.id AND a.id=c.adid AND a.zone=? AND a.status='1'
											AND c.keywordid=? AND b.balance >0 $order", array($this->zone, $keywordid));
				
				if(count($ads) > 0 && $this->default['adrevenue']['cache'] > 0)
				{
					lib_cache_put("zoneads" . $keywordid, $this->zone, $ads);
				}
			
		}
		
		$n = count($ads);

		// We have no ads
		if($n == 0)
		{
			// Try for a default ad
			if($this->z['default_ad'] > 0)
				$ads = $this->db->getsql("SELECT id,userid FROM adrev_ads WHERE id=?", array($this->z['default_ad']));

			if(count($ads) == 0)
				return( FALSE );
		}		

		// Setup a simple array of the ads
		// Makes it easier to manipulate later
		$a = array();
		foreach($ads as $ad)
		{
			// Are we before start date? Skip the ad then
			if($ad['startdate'] > 0 && $ad['startdate'] > time())
				continue;
					
			// Do we have dayparting turned on?
			if($ad['daypart_hours'] > 0 && $ad['daypart_days'] > 0)
			{
				$day = date("w");
				$hour = date("G");
				if(!in_array($hour, lib_bit_options($ad['daypart_hours'])) && $ad['daypart_hours'] > 0)
					continue;
				if(!in_array($day, lib_bit_options($ad['daypart_days'])) && $ad['daypart_days'] > 0)
					continue;				
			}
			
			$a[] = $ad;
		}

		// Order the set randomly if we requested it
		reset($a);
		if($this->z['ad_sort'] == "rand")
			shuffle($a);	

		// Grab the quantity we need
		reset($a);
		if($n > $this->z['max_display_ads'])
			$a = array_slice($a, 0, $this->z['max_display_ads']);		


		// Set the number of ads to get back
		$this->adlist = $a;

		return ( TRUE );
	}
	
}

?>
