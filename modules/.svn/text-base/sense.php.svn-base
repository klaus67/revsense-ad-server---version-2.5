<?
//
// AdRevenue Ad Management
// sense.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//
// Revsense (TM) System
//

class sense extends main
{
	var $zone;
	var $z;
	var $affid;
	var $url;
	var $ip;
	var $query;
	var $words;

	function _default()
	{
		// Lets find some keywords
		$keyword = $this->sense_page();

		if($keyword)
		{
			include_once("modules/serve.php");	
			$s = new serve();
			$s->zone = $this->zone;
			$s->affid = $this->affid;
			$s->fuzzy = 1;
			$s->format = "js";
			$s->keyword = $keyword;
			$s->_default();
		}
		exit;
	}

	function sense_page()
	{
		global $DEFAULT;
		
		$this->zone = $_GET[id];
		$this->affid = $_GET[affid];
		$this->url = str_replace("www.", "", strtolower(trim($_SERVER[HTTP_REFERER])));
		$this->ip = $_SERVER[REMOTE_ADDR];
		$this->query = $_GET[q];
		$d = date("r");
		
		if($this->url)
		{
			// Parse out the query string if we are handed the parameter
			$keywords = array();
			$p = parse_url($this->url);
			if($this->query)
			{
				parse_str($p[query], $output);
				if(trim($output[$this->query]))
					return(strtolower(trim($output[$this->query])));
			}
		
			if(count($keywords) == 0)
			{				
				$u = md5($this->url);
				$keyword = $this->cache($u);
				if($keyword)
				{
					$this->logger("Found $keyword in cache for $this->url");
					return($keyword);
				}
				else
				{					
					// Grab the page and cleanup
					$page = @strip_tags(@implode("", @file($this->url)));
					$page = @str_replace(array("\n", "\r"), " ", $page);
					$page = @preg_replace('/<script.*?<\/script/ism', ' ', $page);
					$page = @preg_replace('/&nbsp;|&amp;|#.*?;|\W+|\s+|<.*?>|<.*?\/>|nbsp|amp/ism', " ", $page);
					if(!$page)
						return(FALSE);
					
					// Grab keywords in this zone
					$k = $this->db->getsql("SELECT a.keyword, a.id
												FROM adrev_keywords a, adrev_keyword_map b 
												WHERE a.id=b.keywordid AND b.zoneid=?
												ORDER BY b.bid DESC", array($this->zone));

					// Get outta here if there are no keywords
					if(count($k) == 0)
						return(FALSE);
						
					// Gather keywords
					foreach($k as $r)
						$keywords[$r[keyword]] = $r[id];
						
					$this->logger("TRYING WITH KEYWORDS: " . implode(",", array_keys($keywords)));
						
					// Preg through the page to find matches
					$regex = "/(" . @implode(")|(", @array_keys($keywords)) . ")/i";
					if(@preg_match_all($regex, $page, $matches))
					{
						$found = array();
						foreach($matches[0] as $rec)
						{
							$ky = @strtolower(trim($rec));
							if(!$found["$ky"])
								$found["$ky"] = 1;
							else
								$found["$ky"]++;
						}
					
						// We have keywords
						if(@count($found) > 0)
						{
							// Sort
							@arsort($found);
							
							// Cache this page
							$a = array_keys($found);
							$arf = array_shift($a);
							$this->cache($u, $arf);
							$this->logger("FOUND KEYWORD: $arf");
							return($arf);
						}
					}					
					
				}
			}
		}
		return(FALSE);		
	}

	function logger($line="")
	{
			// Log what we did
			$fp = fopen("cache/".date("Ymd")."_".$this->z[id]."_sense_log.log", "a");
			if(flock($fp, LOCK_EX))
			{
				$ip  = $_SERVER[REMOTE_ADDR];
				$ld  = date("r");
				$line  = "$ld\t$ip\t" . $line . "\n";
				fputs($fp, $line, strlen($line));
				flock($fp, LOCK_UN);
			}
			fclose($fp);
	}
	
	// Cache a sense URL
	function cache($key="", $val="")
	{
		if(!$key)
			return(FALSE);
		
		$fp = fopen("cache/sense_url_" . $key . ".cache", "w");
		if(!$val)
		{
			$val = fgets($fp);
		}
		else
		{
			fputs($fp, $val);
		}
		fclose($fp);
		
		return($val);
	}
}

?>
