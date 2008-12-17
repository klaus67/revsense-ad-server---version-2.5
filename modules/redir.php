<?
// 
// AdRevenue Ad Management
// redir.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class redir extends main
{
	var $zid;
	var $affid;
	var $kid;
	var $z;
	
	function _default()
	{
		// Instantiate the cookie Library
		$c = new cookielib();

		// Get our variables
		$this->zid = $_GET[zid];
		$this->affid = $_GET[affid];
		$this->kid = $_GET[kid];

		// Grab the Ad Information
		$a = $this->db->getsql("SELECT * FROM adrev_ads WHERE zid=?", array($this->zid));
		$ad = $a[0];
		
		if(!$a[0][id])
		{
			header("Location: " . $this->default[adrevenue][default_redir]);
			exit;
		}

		// Grab the zone
		$zone = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($ad['zone']));
		$this->z = $zone[0];

		// Check if we are outside of the click threshold (for fraud)
		$k = $c->get("_k_".$this->zid);
		$win = time() - $k;
		if($k > 0 && $win < $this->default[adrevenue][dup_clicks])
		{
			header("Location: " . $ad[url]);
		}
		else
		{
			// Setup the cookie	
			$c->set("_k_".$this->zid, time());

			// Set a conversion cookie
			$cad = array('id'=>$ad[id], 'affid'=>$this->affid, 'kid'=>$this->kid);
			$c->set("conv", $cad);
			
			if($this->kid > 0)
			{
				$k = $this->db->getsql("SELECT * FROM adrev_keywords WHERE id=?", array($this->kid));
				$keywordrate = $k[0][mincpc];
			}
			
			// Try to load this record
			$date = date("Y-m-d");
			$this->kid = $this->kid ? $this->kid : 0;
			$this->affid = $this->affid ? $this->affid : 0;
			$a = $this->db->getsql("SELECT id FROM adrev_traffic 
										WHERE adid=? AND date=? AND keywordid=?", array($ad['id'], $date, $this->kid));

			// Calculate rate to post here
			$t = time();
			$amount = 0;
			
			
			if($this->z[rate_type] == "CPC")
			{
			
			       if($this->z[rate] >= $ad[bid])
					$amount = $this->z[rate];
				   if($ad[bid] >= $this->z[rate])
					$amount = $ad[bid];
				  if($keywordrate >= $amount)
					 $amount = $keywordrate;
					 
				   //Added 11-17-04
				   if($this->z[keywords_enable]==1 AND $this->z[ad_sort] == "bid")
					{
					  $k = $this->db->getsql("SELECT * FROM adrev_keyword_map WHERE keywordid=?", array($this->kid));
				      $amount = $k[0][bid];
					}
					//End-11-16-04
					
				  
				
			}
			
			
			// Post amounts
			$amount = $amount > 0 ? $amount : 0;
			$id = $a[0][id];
			if(!$ad[id])
			{
				// Add a new traffic record
				$i = array();
				$i[userid] = $ad[userid];
				$i[date] = $date;
				$i[adid] = $ad[id];
				$i[keywordid] = $this->kid;
				$i[impressions] = 1;
				$i[clicks] = 1;
				$i[amount] = $amount;
				$this->db->insert("adrev_traffic", $i);
			}
			else
			{
				$this->db->getsql("UPDATE adrev_traffic SET clicks=clicks+1, amount=amount+{$amount} WHERE id=?", array($id));
				
				if($this->z[rate_type] == "CPC")
				{
					// Update ad units
					$this->db->getsql("UPDATE adrev_ads SET total_units=total_units+1 WHERE id=?", array($ad['id']));
					$ad[total_units] += 1;				
				}
			}

			// Update real time balance if we actually have an amount
			$t = time();
			if($amount > 0)
			{
				if($this->affid > 0 && $this->z[aff_percent] > 0)
				{
					$i = array();
					$i[date] = time();
					$i[affid] = $this->affid;
					$i[adtype] = $this->z[rate_type];
					$i[adid] = $ad[id];
					$i[ip] = $_SERVER[REMOTE_ADDR];
					$i[referer] = $_SERVER[HTTP_REFERER];
					$i[amount] = $amount * ($this->z[aff_percent] / 100);
					$this->db->insert("adrev_aff_traffic", $i);
				}

				$this->db->getsql("UPDATE adrev_users SET balance=balance-$amount, balance_update=? WHERE id=?", array($t, $ad['userid']));
			}

			// Attempt to expire this ad
			if(($ad[total_units] >= $ad[units] && $ad[units] > 0) || ($ad[expires] > 0 && $ad[expires] <= time()))
			{
				$this->db->getsql("UPDATE adrev_ads SET status=-2 WHERE id=?", array($ad['id']));
			}			
			
			// Log into the clicklog
			$fp = fopen("cache/".date("Ymd")."_clicks.csv", "a");
			if(flock($fp, LOCK_EX))
			{
				$ref = $_SERVER[HTTP_REFERER];
				$req = $_SERVER[REQUEST_URI];
				$uid = $c->cookie[visitorid];
				$ip  = $_SERVER[REMOTE_ADDR];
				$ld  = date("r");
				$line  = "\"$ld\",\"$ip\",\"$uid\",\"$this->affid\",\"$amount\",\"$ad[url]\",\"$ref\",\"$req\"\n";
				fputs($fp, $line, strlen($line));
				flock($fp, LOCK_UN);
			}
			fclose($fp);

			// Redirect
			header("Location: $ad[url]");
			exit;
		}
	
		exit;
	}
}

?>
