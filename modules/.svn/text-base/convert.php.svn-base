<?
// 
// RevSense Ad Management
// redir.php
//
// (C) 2004-2006 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

#- Class to generate 1x1 gif
class gifpix
{
        var $start = '47494638396101000100800000';
        var $color = 'ffffff';
        var $black = '000000';
        var $marker = '21f904';
        var $transparent = '01';
        var $end = '000000002c00000000010001000002024401003b';

        function hex2bin($s)
        {
                $bin = "";
                for ($i = 0; $i < strlen($s); $i += 2)
                {
                        $bin .= chr(hexdec(substr($s,$i,2)));
                }
                return $bin;
        }

        function create($color = -1)
        {
                if (($color!= -1) && (strlen($color)==6))
                {
                        $this->transparent = '00';
                        if ($color == '000000')
                                $this->black = 'ffffff';

                        $this->color = $color;
                }

                $hex = $this->start . $this->color . $this->black . $this->marker . $this->transparent . $this->end;
                return $this->hex2bin($hex);
        }
}

class convert extends main
{
	var $zid;
	
	function _default()
	{
		// Grab the cookie
		$c = new cookielib();
		$k = $c->get("conv");

		// Update
		if($k[id] > 0)
		{
			// Get the ad info
			$adinfo = $this->db->getsql("SELECT id,userid,zone FROM adrev_ads WHERE id=?", array($k['id']));
			$ad = $adinfo[0];
			
			// Get the zone info
			$zone = $this->db->getsql("SELECT rate,rate_type,aff_percent FROM adrev_zones WHERE id=?", array($ad['zone']));
			$z = $zone[0];
			
			if($ad[id] && $_GET[id] == $ad[userid])
			{	
				$date = date("Y-m-d");
				$t = $this->db->getsql("SELECT * FROM adrev_traffic 
										WHERE adid=? AND date=? AND keywordid=?", array($k['id'], $date, $k['kid']));				
				if(!$t[0][id])
				{
					// Add a new traffic record
					$i = array();
					$i[userid] = $t[0][userid];
					$i[date] = $date;
					$i[adid] = $ad[id];
					$i[keywordid] = $k[id];
					$i[amount] = iif($z[rate_type] == "CPA", $z[rate], 0);
					$this->db->insert("adrev_traffic", $i);
					
					$amount = $i[amount];
				}
				else
				{
					$id = $t[0][id];
					$amount = iif($z[rate_type] == "CPA", $z[rate], 0);
					$this->db->getsql("UPDATE adrev_traffic SET orders=orders+1,amount=amount+{$amount} WHERE id=?", array($id));
				}
				
				// If this is a CPA type, update units
				if($z[rate_type] == "CPA")
				{
					$this->db->getsql("UPDATE adrev_ads SET total_units=total_units+1 WHERE id=?", array($ad['id']));
				}
				
				// Update the affiliate cut
				if($z[aff_percent] > 0 && $z[rate_type] == "CPA" && $amount)
				{
					$i = array();
					$i[date] = time();
					$i[affid] = $k[affid];
					$i[adtype] = $z[rate_type];
					$i[adid] = $ad[id];
					$i[ip] = $_SERVER[REMOTE_ADDR];
					$i[referer] = $_SERVER[HTTP_REFERER];
					$i[amount] = $amount * ($z[aff_percent] / 100);
					$this->db->insert("adrev_aff_traffic", $i);					
				}
				
				// Attempt to expire this ad
				if(($ad[total_units] >= $ad[units] && $ad[units] > 0) || ($ad[expires] > 0 && $ad[expires] <= time()))
				{
					$this->db->getsql("UPDATE adrev_ads SET status=-2 WHERE id=?", array($ad['id']));
				}				
				
				// Update the running totals for the customer
				if($amount > 0)
				{
					$t = time();
					$this->db->getsql("UPDATE adrev_users SET balance=balance-$amount, balance_update=? WHERE id=?", array($t, $ad['userid']));
				}
				
				// Clear the conversion cookie
				$c->set("conv", array());
			}
		}
		
		// Send the Gif		
		header("Content-type: image/gif");
		$gifpix = new gifpix;
		echo $gifpix->create();		
	}
}

?>
