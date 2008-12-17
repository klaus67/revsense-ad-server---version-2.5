<?
//
// Keyword Extractor
//

class generator
{
	var $pattern;
	var $exclude;
	
	var $seed;
	var $maxwords;
	
	var $words;
	var $stopwords;

	var $elements;
	var $raw;

	function generator()
	{
		// Elements to grab
		$this->pattern[title] = '/<title>(.*?)<\/title>/im';
		$this->pattern[h1]  = '/<h1>(.*?)<\/h1>/im';
		$this->pattern[h2]  = '/<h2>(.*?)<\/h2>/im';
		$this->pattern[h3]  = '/<h3>(.*?)<\/h3>/im';
		$this->pattern[th]  = '/<th.*?>(.*?)<\/th>/im';
		$this->pattern[summary]  = '/summary="(.*?)"/im';
		$this->pattern[atitle]  = '/title="(.*?)"/im';
		$this->pattern[alt]  = '/alt="(.*?)"/im';
		$this->pattern[body] = '/<body.*?>(.*?)$/im';
	
		// Elements to exclude
		$this->exclude[script]= '/(<script.*?>.*?<\/script>)/im';
		$this->exclude[head]  = '/<head.*?>.*?<\/head>/im';
		$this->exclude[chars] = '/\&.*?;/m';
		$this->exclude[comments] = '/<\!\-\-.*?\-\->/m';
		
		$this->elements = array();
		$this->words = array();

		// Loadup stopwords
		$words = file("libs/stopwords.txt");
		foreach($words as $w)
		{
			$this->stopwords[strtolower(trim($w))] = 1;
		}


		return( TRUE );
	}

	// Return the top $n words from seed
	function results($n = 10)
	{
		arsort($this->seed);
		return( array_slice($this->seed, 0, $n));
	}

	// Operate on the document we fetched earlier
	// This is the main keyword generation routine
	// I will document it later!
	function parse_keywords()
	{
		// Title first
		$t = $this->get_keywords($this->elements[title][0], 20);
		if(count($t) > 0)
		{
			$this->words = array_merge($this->words, $t);
			
			// Grab some other words, based on the title words
			reset($t);
			while(list($seed, $rank) = each($t))
			{
				print "Looking for $seed\n";
				$results = $this->get_seed_keywords($this->elements[body][0], $seed, "r", 1);
				print_r($results);
			}
		}
	}
	
	// Grab top keywords from a document
	function get_keywords($text="", $limit=10)
	{
		if(!trim($text))
			return($text);

		$keywords = array();
		$k = array_count_values(preg_split("/[\s\+]+/",$this->filter(lib_filter($text)), -1, PREG_SPLIT_NO_EMPTY));
		arsort($k);

		return(array_slice($k,0,$limit));
	}

	// Grab adjacent keywords to a seed
	function get_seed_keywords($text="", $seed="", $direction="r", $words=1)
	{
		if(!trim($text))
			return($text);

		$regex = "/" . $seed . "/im";
		if(!preg_match($regex, $text))
			return($text);

		$ret = array();

		// Check on the right side now
		if($direction == "r")
		{
			if($words == 1)
				$regex = "/" . $seed . "\s+(\w+)\s+/im";
			if($words == 2)
				$regex = "/" . $seed . "\s+(\w+)\s+(\w+)\s+/im";
			if($words == 3)
				$regex = "/" . $seed . "\s+(\w+)\s+(\w+)\s+(\w+)\s+/im";
	
			if(preg_match_all($regex, $this->filter(lib_filter($text)), $matches))
			{
				$ret = $matches[0];
			}
		}

		// Then on the left side
		if($direction == "l")
		{
			if($words == 1)
				$regex = "/\b(\w+)\s+$seed/im";
			if($words == 2)
				$regex = "/\s+(\w+)\s+(\w+)\s+$seed/im";
			if($words == 3)
				$regex = "/\s+(\w+)\s+(\w+)\s+(\w+)\s+$seed/im";
	
			if(preg_match_all($regex, $this->filter(lib_filter($text)), $matches))
			{
				$ret = $matches[0];
			}
		}

		// Both sides
		if($direction == "lr" || $direction == "rl")
		{
			if($words == 1)
				$regex = "/\b(\w+)\s+$seed\s+(\w+)\s/im";
			if($words == 2)
				$regex = "/\b(\w+)\s+(\w+)\s+$seed\s+(\w+)\s+(\w+)\s/im";
			if($words == 3)
				$regex = "/\b(\w+)\s+(\w+)\s+(\w+)\s+$seed\s+(\w+)\s+(\w+)\s+(\w+)\s/im";

			if(preg_match_all($regex, $this->filter(lib_filter($text)), $matches))
			{
				$ret = $matches[0];
			}
		}
		
		return(array_unique($ret));
	}

	// Parse out an HTML page
	function parse_html_page($url = "")
	{
		$this->raw = @implode("", @file($url));
		if(!$this->raw)
		{
			$this->seed = array();
			return( FALSE );
		}
		$this->raw = str_replace(array("\n", "\r"), " ", $this->raw);

		// Grab other meta tags
		if(preg_match_all('/<meta name="(title|description|keywords|keyword)".*?content="(.*?)".*?>/im', $this->raw, $matches))
		{
			$n = count($matches[1]);
			for($x = 0; $x < $n; $x++)
			{
				$key = strtolower(trim($matches[1][$x]));
				$val = $this->filter($matches[2][$x]);
				if($key && $val)
					$this->elements[$key][] = $val;
			}
		}

		// Exclude parts we do not need
		$this->raw = preg_replace($this->exclude[script], "", $this->raw);
		$this->raw = preg_replace($this->exclude[comments], "", $this->raw);

		// Grab the other parts
		reset($this->pattern);
		while(list($key, $regex) = each($this->pattern))
		{
			if(preg_match_all($regex, $this->raw, $matches))
			{
				$n = count($matches[1]);
				for($x = 0; $x < $n; $x++)
				{
					$val = $matches[1][$x];
					if($val)
						$this->elements[$key][] = $val;
				}
			}
		}
	
		// Clean up HTML from the body
		$this->raw = preg_replace($this->exclude[head], "", $this->raw);

		// Strip tags from the HTML
		$search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
                 "'<[\/\!]*?[^<>]*?>'si",          // Strip out HTML tags
                 "'&(amp|#38);'i",
                 "'&(lt|#60);'i",
                 "'&(gt|#62);'i",
                 "'&(nbsp|#160);'i",
                 "'&(iexcl|#161);'i",
                 "'&(cent|#162);'i",
                 "'&(pound|#163);'i",
                 "'&(copy|#169);'i",
                 "'&#(\d+);'e");                    // evaluate as php

		$replace = array (" ",
                 " ",
                 "&",
                 "<",
                 ">",
                 " ",
                 chr(161),
                 chr(162),
                 chr(163),
                 chr(169),
                 "chr(\\1)");
		$this->elements[body][0] = preg_replace($search, $replace, $this->elements[body][0]);

		// Translate HTML entities to actual characters
		$this->elements[body][0] = strtr($this->elements[body][0], array_flip(get_html_translation_table(HTML_ENTITIES)));

		// Translate other character set entities
		if(preg_match_all('/\&#(\d+);/m', $this->elements[body][0], $matches))
		{
			$chars = array_unique($matches[1]);
			$r = array();
			$s = array();
			foreach($chars as $c)
			{
				$r[] = chr($c);
				$s[] = "&#$c;";
			}
			$this->elements[body][0] = str_replace($s, $r, $this->elements[body][0]);
		}

		// Get a list of all words
		$words = preg_split('/[\W\s]/', strtolower($this->elements[body][0]), -1, PREG_SPLIT_NO_EMPTY);
		if(count($words) > 0)
		{
			$this->words = array();
			foreach($words as $w)
			{
				if(!$this->stopwords[$w])
					$this->words[] = $w;
			}
		}

		// Get ProperCase words
		$regex = '/\s([A-Z].*?)\W/';
		$this->seed = array();
		if(preg_match_all($regex, $this->elements[body][0], $matches))
		{
			foreach($matches[1] as $rec)
			{
				$rec = trim($rec);
				if($this->stopwords[strtolower($rec)])
					continue;
			
				// Skip words less than 3 characters
				if(strlen($rec) < 3)
					continue;

				if($rec)
					$this->seed[] = preg_replace('/\W/', '', $rec);
			}

			// Run through the seeds and make composite words
			$seed = $this->seed;
			$n  = count($this->words);
			reset($seed);
			foreach($seed as $s)
			{
				$pos = array_search(strtolower($s), $this->words);
				if($pos > 0 && $pos < $n-1 )
					$this->seed[] = strtolower("$s " . $this->words[$pos+1]);
				if($pos > 0 && $pos < $n-2 )
					$this->seed[] = strtolower("$s " . $this->words[$pos+1] . " " . $this->words[$pos+2]);
			}

			$this->seed = array_count_values($this->seed);
		}

		// Rank words as to importance
		if(count($this->words) > 0)
		{
			// Create a hash from the body
			$words = array_count_values($this->words);
			reset($this->seed);
			reset($words);

			// Create a hash from the title
			$title = array_count_values(preg_split('/\W/', strtolower($this->elements[title][0]), -1, PREG_SPLIT_NO_EMPTY));

			// Create a hash from the keywords
			$keywords = array_count_values(preg_split('/\W/', strtolower($this->elements[keywords][0]), -1, PREG_SPLIT_NO_EMPTY));

			// Create a hash from the description
			$description = array_count_values(preg_split('/\W/', strtolower($this->elements[description][0]), -1, PREG_SPLIT_NO_EMPTY));
		
			while(list($w, $val) = each($this->seed))
			{
				// Found flag
				$found = 0;
			
				// Compare against the body
				if($words[strtolower($w)])
				{
					$this->seed[$w] += $words[strtolower($w)];
					$found++;
				}

				// Give extra points if found in the title
				$regex = "/$w/i";
				if(preg_match($regex, $this->elements[title][0]))
				{
					$this->seed[$w] *= 4;
					$found++;
				}

				// Extras for keywords
				if(preg_match($regex, $this->elements[keywords][0]))
				{
					$this->seed[$w] *= 3;
					$found++;
				}

				// Extras for description
				if(preg_match($regex, $this->elements[description][0]))
				{
					$this->seed[$w] *= 3;
					$found++;
				}

				// Demote words not found
				if(!$found)
					$this->seed[$w] = $this->seed[$w] / 4;
				else
					$this->seed[$w] += $found;
			}
		}

		// Dedup seed
		$s = array();
		reset($this->seed);
		while(list($word, $rank) = each($this->seed))
		{
			$s[strtolower($word)] += $rank;
		}

		$this->seed = $s;
		return ( TRUE );	
	}


	// Remove stopwords from a phrase
	function filter($phrase="")
	{
		if(!$this->stopwords)
			return($phrase);

		if(!is_array($this->stopwords))
		{
			$w = explode(",", $this->stopwords);
			if(count($w) > 0)
			{
				foreach($w as $r)
					$st[] = trim($r);
			}
			$this->stopwords = $st;
		}

		if(is_array($this->stopwords))
		{
			// Delete stopwords
			$phrase = str_replace($this->stopwords, " ", $phrase);

			// Delete extra spaces
			$phrase = preg_replace('/\s+/', " ", $phrase);
		}
		
		return(trim($phrase));
	}


	
}

?>
