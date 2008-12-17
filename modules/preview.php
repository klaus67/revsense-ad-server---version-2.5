<?
//
// AdRevenue Ad Management
// preview.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class preview extends main
{
	var $zid;
	var $affid;
	var $keyid;

	function _default()
	{
	}
	
	// Return an id in various formats
	function format_ad($output="xml")
	{
		if(!$this->zid)
		{
			return("");
		}

		// Output HTML
		if($output == "html")
		{
			return($this->display());
		}
			
		// Output Javscript
		if($output == "js")
		{
			$o = explode("\n", str_replace("'","\'",$this->display()));
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
			$nocache = md5(uniqid("")) . " | " . $_SERVER[REMOTE_ADDR];
			$out  = "// W3matter.com AdRevenue\n";
			$out .= "// $date\n";
			$out .= "// Ad Code: $nocache\n\n";
		
			$out .= "document.write('" . implode("');\ndocument.write('", $oo) . "');\n";	
			return($out);
		}

		// All other formats
#		$layout = lib_cache_get("ad_$output", $this->zid);
		if($layout)
			return($layout);
		
		// Grab data
#		$ad = lib_cache_get("ad", $this->zid);
		if(!$ad)
		{
			$a = $this->db->getsql("SELECT * FROM adrev_ads WHERE zid=?", array($this->zid));
			$ad = $a[0];
			if($ad[id] && $this->default[adrevenue][cache] > 0)
				lib_cache_put("ad", $this->zid, $ad);
		}

#		$zone = lib_cache_get("zone", $ad[zone]);
		if(!$zone)
		{
			$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($ad['zone']));
			$zone = $z[0];
			if($zone[id] && $this->default[adrevenue][cache] > 0)
				lib_cache_put("zone", $ad[zone], $zone);
		}
		
		if(!$ad[id] || !$zone[id])
			return("");

		$ad_format = unserialize(stripslashes($zone[ad_format]));
		$template  = stripslashes($zone[template]);
		
		$search = array();
		$replace = array();
		foreach($ad_format as $rec)
			$hash[$rec[type]] = $ad[strtolower($rec[type])];

		// If we have a keyword, then get its URL
		if($this->keyid)
		{
			$k = $this->db->getsql("SELECT url FROM adrev_keyword_map WHERE keywordid=? AND adid=? LIMIT 1", array($this->keyid, $ad['id']));
			if($k[0][url])
				$hash[URL] = $k[0][url];
		}

		// Put a tracking URL in the hash
		$hash[trackurl] = $this->http->hostname . "index.php?section=redir&zid=$this->zid&affid=$this->affid&kid=$this->keyid";

		// XML
		if($output == "xml")
		{
			$out = "<ad>\n";
			while(list($key,$val) = each($hash))
			{
				$out .= "\t<$key><![CDATA[$val]]></$key>\n";
			}
			$out .= "</ad>\n";
		}
		elseif($output == "csv")
		{
			$out = '"' . implode('","', array_values($hash)) . '"' . "\r\n";
		}
	
		lib_cache_put("ad_$output", $this->zid, $out);
	
		return($out);
	}
	
	// Generate an ad for HTML or Javascript purposes
	function display($ad = array())
	{
		if(!$this->zid)
			return("");

		// Try to grab from the cache
#		$template = lib_cache_get("ad_html", $this->zid);
		if($template)
		{
			return($template);
		}

		// Grab data
#		$ad = lib_cache_get("ad", $this->zid);
		if(!$ad[id])
		{
			$a = $this->db->getsql("SELECT * FROM adrev_ads WHERE zid=?", array($this->zid));
			$ad = $a[0];
			if($ad[id] && $this->default[adrevenue][cache] > 0)
			{
				lib_cache_put("ad", $this->zid, $ad);
			}
		}

#		$zone = lib_cache_get("zone", $ad[zone]);
		if(!$zone)
		{
			$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($ad['zone']));
			$zone = $z[0];
			if($z[0][id] && $this->default[adrevenue][cache] > 0)
				lib_cache_put("zone", $zone[id], $zone);
		}
		
		if(!$ad[id] || !$zone[id])
			return("");

		$ad_format = unserialize(stripslashes($zone[ad_format]));
		$template  = stripslashes($zone[template]);

		$search = array();
		$replace = array();
		foreach($ad_format as $rec)
		{
			$type = $rec[type];
			$val = stripslashes($ad[strtolower($type)]);
			
			// Deal with URL based on zone settings
			// We will replace the URLSTRING variable
			if($type == "URL")
			{
				$urlstr = "";
				// If we have a keyword, then get its URL
				if($this->keyid)
				{
					$k = $this->db->getsql("SELECT url FROM adrev_keyword_map WHERE keywordid=? AND adid=? LIMIT 1", array($this->keyid, $ad['id']));
					if($k[0][url])
						 $val = $k[0][url];
				}
				
				// Use tracking url or not
				$hostname = rtrim($this->default[adrevenue][hostname],"/") . "/index.php?section=redir&zid=$this->zid&affid=$this->affid&kid=$this->keyid";

				if(!$zone[urls_tracking])
					$urlstr = " href=\"$hostname\"";
				else
					$urlstr = " href=\"$val\"";

				// Hide URL in the status bar
				if($zone[urls_hide])
				{
					$parts = parse_url($val);
					if($ad[display_url])
						$parts[host] = $ad[display_url];
					$urlstr .= " onmouseover=\"window.status='Go to: $parts[host]';return true\"";
					$urlstr .= " onmouseout=\"window.status=''; return true\"";
				}
				
				// Setup target
				$urlstr .= " target=\"$zone[urls_target]\"";
				
				$search[] = "{"."URLSTRING"."}";
				$replace[] = $urlstr;
			}
			elseif($type <> "IMAGE")
			{
				// Contrain Length
				if($rec[max_length] > 0)
					$val = substr($val, 0, $rec[max_length]);

				// Set search name
				$search[] = "{".$type."}";
				
				if ($type != 'CONTENT'){
					// Optionally make value bold
					if($rec[bold] == 1 && $type <> "URL")
						$val = "<b>$val</b>";
				
					// Setup the font
					$val = "<font face=\"$rec[font]\" size=\"$rec[size]\" color=\"$rec[color]\">$val</font>";
					$replace[] = $val;			

					// Font
					$search[] = "{".$type."_FONT"."}";
					$replace[] = $rec[font];
				
					// Size
					$search[] = "{".$type."_SIZE"."}";
					$replace[] = $rec[size];
				
					// Color
					$search[] = "{".$type."_COLOR"."}";
					$replace[] = $rec[color];
				} else {
					$replace[] = $val;
				}
			}
			else
			{
				// This is an image
				if(preg_match('/\.swf$/i', $val))
				{
					// Handle flash images
					$imgstr  = "<OBJECT classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" ";
					$imgstr .= "codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0\"";
					$imgstr .= "WIDTH=\"$rec[width]\" HEIGHT=\"$rec[height]\" id=\"$this->zid\">";
					$imgstr .= "<PARAM NAME=movie VALUE=\"$val\"><PARAM NAME=quality VALUE=high>";
					$imgstr .= "<PARAM NAME=bgcolor VALUE=#FFFFFF>";
					$imgstr .= "<EMBED src=\"$val\" quality=high bgcolor=#FFFFFF ";
					$imgstr .= "WIDTH=\"$rec[width]\" HEIGHT=\"$rec[height]\" NAME=\"$this->zid\""; 
					$imgstr .= "ALIGN=\"\" TYPE=\"application/x-shockwave-flash\""; 
					$imgstr .= "PLUGINSPAGE=\"http://www.macromedia.com/go/getflashplayer\">";
					$imgstr .= "</EMBED>\n</OBJECT>\n";
				}
				else
				{
					// Deal with alternate images
					$imgstr = "<img src=\"$val\" width=\"$rec[width]\" height=\"$rec[height]\" alt=\"Click here\" border=\"0\">";
				}
				
				$search[] = "{" . "IMAGESTRING" . "}";
				$replace[] = $imgstr;

				// Also add image
				$search[] = "{" . "IMAGE" . "}";
				$replace[] = $val;
			}
		}
	
		// Run through the template and do replacements
		$template = str_replace($search, $replace, $template);

		// Then get rid of any stray template variables
		$template = preg_replace('/\{.*?\}/', "", $template);
		
		// Save the ad in the cache
		lib_cache_put("ad_html", $this->zid, $template);
		
		return($template);
	}


	
}
?>
