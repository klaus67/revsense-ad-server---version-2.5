<?
// 
// AdRevenue Library functions
// lib.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

// Language translation
function lib_lang($text="")
{
	global $DEFAULT, $INIFP, $W3LANG;

	$text = trim($text);

	if(!$DEFAULT[lang])
		$DEFAULT[lang] = "EN";

	$DEFAULT[lang] = strtoupper($DEFAULT[lang]);

	// Open the Language file
	if(!$W3LANG)
	{
		$lrecs = @file("lang/$DEFAULT[lang].lng");
		if(count($lrecs) > 0)
		{
			foreach($lrecs as $line)
			{
				list($key, $val) = explode("=", trim($line));
				$W3LANG[trim($key)] = trim($val);
			}
		}
		else
		{
			$W3LANG = array();
		}
	}
	// We dont have the text!
	if(!$W3LANG[$text])
	{
		$W3LANG[$text] = $text;
		$str = "$text=$text\n";
		
		$fp = fopen("lang/$DEFAULT[lang].lng", "a");
		fputs($fp, $str);
		fclose($fp);
	}

	return($W3LANG[trim($text)]);
}


// Get something from the cache and optionally expire it
function lib_cache_get($klass="", $key)
{
	global $DEFAULT;

	if(!$DEFAULT[adrevenue][cache])
		return ( FALSE );
	
	$name = "cache/" . $klass . "_" . $key . ".cache";
	if(!file_exists($name))
	{
		return( FALSE );
	}
	
	$CACHE = unserialize(join("",file($name)));
	$val = $CACHE[val];
	
	// See if our cache is too old
	$age = $DEFAULT[adrevenue][cache] ? $DEFAULT[adrevenue][cache] : 0;
	if($age > 0)
	{
		$s = stat($name);
		if($s[ctime] + $age < time())
		{
			unlink($name);
			unset($val);	
		}
	}
	
	return($val);
}

// Put something in the cache
function lib_cache_put($klass="", $key="", $val)
{
	global $DEFAULT;
	
	if(!$DEFAULT[adrevenue][cache])
		return( FALSE );
	
	$name = "cache/" . $klass . "_" . $key . ".cache";
	
	$fp = fopen($name, "w");
	$CACHE = array();
	$CACHE[ts] = time();
	if(flock($fp, LOCK_EX))
	{
		$CACHE[val] = $val;
		fputs($fp, serialize($CACHE));
		flock($fp, LOCK_UN);
	}
	fclose($fp);

	return( TRUE );
}


#- A cookie library where we can get and set values
class cookielib 
{
	var $cookie;
	
	function cookielib()
	{
		// Loadup the cookie
		$this->cookie = unserialize(base64_decode($_COOKIE[adrevenue]));

		if(!$this->cookie)
			$this->cookie = array();
	}

	function set($key="", $val="")
	{
		if(!$key)
			return("");
	
		// Do we have a userid?
		if(!$this->cookie[visitorid])
			$this->cookie[visitorid] = uniqid("");
	
		$this->cookie[$key] = $val;
		setcookie("adrevenue", base64_encode(serialize($this->cookie)), time()+ (86400 * 30));
	}

	function get($key = "")
	{
		return($this->cookie[$key]);
	}
}


#- Return status given a numeric value
function lib_status($status=1)
{
	if($status == 3)
		return("Active");
	elseif($status == "2")
		return("Pending");
	elseif($status == "1")
		return("Paused");
	elseif($status == "0")
		return("Offline");
	elseif($status == "-1")
		return("Rejected");
	return("N/A");
}

#- Apply stopwords to a string
function lib_stopwords($keyword="")
{
	global $stopwords;

	$keywords = trim(strtolower($keyword));
	$keylist = preg_split('/[\s,]+/', -1, PREG_SPLIT_NO_EMPTY);
	$n = count($keylist);
	if($n == 0)
		return($keyword);
	
	$out = array();
	for($x = 0; $x < $n; $x++)
	{
		if(!$stopwords[$keylist[$x]])
			$out[] = $keylist[$x];
	}

	$n = count($out);
	if(!$n)
		return($keyword);
	
	return( implode(" ", $out) );
}

#- Return date ranges given a textal representation
function lib_date_range($text="today")
{
	// Calculate real dates
	if($text == "yesterday")
	{
		$startdate = date("Ymd", strtotime("yesterday"));
		$enddate   = $startdate;
	}
	elseif($text == "thisweek")
	{
		$startdate = date("Ymd", strtotime("last Sunday"));
		$enddate   = date("Ymd", strtotime("today"));
	}
	elseif($text == "lastweek")
	{
		$startdate = date("Ymd", strtotime("last Sunday") - (86400 * 7));
		$enddate   = date("Ymd", strtotime("last Saturday"));
	}
	elseif($text == "thismonth")
	{
		$startdate = date("Ymd", strtotime("1 " . date("M")));
		$enddate = date("Ymd");
	}
	elseif($text == "lastmonth")
	{
		$startdate = date("Ymd", mktime(0,0,0,date("m")-1,1,date("Y")) );
		$enddate = date("Ymd", mktime(0,0,0,date("m"),-1,date("Y")) );
	}
	elseif($text == "all")
	{
		$startdate = "20040301";
		$enddate = date("Ymd");
	}
	else
	{
		$startdate = date("Ymd");
		$enddate   = $startdate;
	}	

	return( array($startdate, $enddate) );	
}

#- Given an array of choices, return
#- the sql fragment to query it
function lib_sql_options($opt = array(), $column = "")
{	
	if(count($opt) > 0)
	{
		$arr = array();
		foreach($opt as $n)
		{
			$arr[] = "($column & (2^$n)) != 0";
		}
		
		$ret =  implode(" OR ", $arr);
	}
	else
	{
		$ret = "($column & (2^16)) != 0";
	}
	
	return($ret);
}

#- Give an array of choices, return
#- a powers of 2 number
function lib_options($opt = array())
{
	if(count($opt) > 0)
	{
		foreach($opt as $n)
		{
			$n = $n * 1.0;
			$num += pow(2,$n);
		}
	}
	else
	{
		// Set the any flag
		$num = 0;
	}

	return($num);
}

// Return an array of choices from an integer bitwise value
function lib_bit_options($num = 0)
{
	$ret = array();
	for($x = 0; $x<= 31; $x++)
	{
		if($num & pow(2,$x))
			$ret[] = $x;
	}

	return($ret);
}


# Get a page on the web via CURL
function lib_getpage($url="", $user_agent="")
{
    global $http_status;

	if(!$user_agent)
    	$user_agent = "Mozilla/4.0 (compatible; MSIE 5.5; Windows 98; Win 9x 4.90)";
	
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_USERAGENT, $user_agent);
	curl_setopt ($ch, CURLOPT_HEADER, 0);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec ($ch);
	$http_status = array();
	$http_status = curl_getinfo($ch);
	curl_close ($ch);

	return($result);
}

// Get microtime
function lib_getmicrotime(){ 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
    }

// Lib debug
function lib_debug($var)
{
	print "<pre>";
	print_r($var);
	print "</pre>";
	print "<hr>";
}

#-----------------------------------------#
# iif function                            #
# Why is not this in PHP beats me!        #
#-----------------------------------------#
function iif($condition,$value_true="",$value_false="")
  {
     if($condition)
       return $value_true;
     else
       return $value_false;
  }

#- Return an HT List, given an array of items
#- and a default value. Does not return the name or anything
#- returns something like: <option>Item</option>
function lib_htlist($items="", $default="")
  {
    $retval = "";
    for($x=0;$x<count($items);$x++)
    {
		$item = trim($items[$x]);
      if($default == $item)
        $retval .= "\t<option value=\"$item\" selected>$item</option>\n";
      else
        $retval .= "\t<option value=\"$item\">$item</option>\n";
    }
    return($retval);
  }#end lib_htlist


#- Returns an HT list given an associative array of items
# and a default value
# List should be named with the indexes being the keys
function lib_htlist_array($items="", $default="")
  {
    $retval = "";
    while(list($k, $v) = each($items))
      {
      	if($default == $k)
      	  $retval .= "\t<option value=\"$k\" selected>$v</option>\n";
      	else
      	  $retval .= "\t<option value=\"$k\">$v</option>\n";
      }

    return($retval);
  }#end lib_htlist


#----------------------------------------------------#
# Return a HT List, from any table, given two fields #
#----------------------------------------------------#
function lib_db_htlist($table="",$keyfield="",$valuefield="",$default="", $other="", $user="", $pass="", $host="", $dbase="")
   {
     global $sql, $errormsg;

     $sql = "SELECT $keyfield,$valuefield FROM $table $other ORDER BY $valuefield";
     
     $result = lib_getsql($sql);
     if($result)     
       foreach($result as $rec)
        {
		  if(strlen($rec[$valuefield]) > 64)
          	$name = substr($rec[$valuefield],0,64) . "...";
		  else
		    $name = $rec[$valuefield];

	  $id   = $rec[$keyfield];
	  
	  if($default == $id)
	    $clist .= "\t\t<option value=$id selected>$name</option>\n";
	  else
	    $clist .= "\t\t<option value=$id>$name</option>\n";
        }

      return $clist;
   }#- End lib_categories

#  Convert to UNIX epoch date
function lib_toepoch($m,$d,$y)
  {
    $date = mktime(0, 0, 0, $m, $d, $y);
	return($date);
  }


#- Return an array from a mysql date y-m-d
function lib_sqldate($mysqldate)
  {
    return(list($year, $month, $date) = split('[/.-]', $mysqldate));
  }

#- convert a SQL date into a UNIX date
function lib_sqltoepoch($mysqldate)
  {
    $d = lib_sqldate($mysqldate);
    return(lib_toepoch($d[1],$d[2],$d[0]));
  }

# Convert to standard date
function lib_frepoch($epoch)
  {
    $date = getdate($epoch);
    $date = $date['mon'] . "-" . $date['mday'] . "-" . $date['year'];
    return($date);
  }


#- Put together some input fields with dates
#- will make 3 fields:
#  $f[fieldname_month], $f[fieldname_day], $f[fieldname_year]
#  Will make all into drop down lists
#  USEAGE:
#  No date - will return today's date
#  A MYSQL date will return that date
#  An epoch date - will return that date
function lib_dateinput($name="date",$date="", $start=1920, $end=0)
   {   
	  if(!$date)
	   {
	     $curr_day = 0;
	     $curr_year = 0;
	     $curr_month = 0;
	   }
	 else
	   {
	     #- Check if this thing is in the MYSQL format
		 if(preg_match("/[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}/",$date))
		 {
		     $parts = explode("-", $date);
		     $curr_day = $parts[2];
		     $curr_year = $parts[0];
		     $curr_month = $parts[1];
		 }
		 if(preg_match("/[0-9]{10}/",$date))
		 {
 		     $curr_day = date("j",$date);
		     $curr_year = date("Y",$date);
		     $curr_month = date("n",$date);
		 }
	   }

     $max_days=31;
	 
	 // Send standard web page years
	 if($end == 0)
	 	$end = date("Y") - 18;
		 
	 for($x=$start;$x<=$start + $end;$x++)
	   $year[]=$x;

	 #- Setup months
	 $month[1]="January";
	 $month[2]="February";
	 $month[3]="March";
	 $month[4]="April";
	 $month[5]="May";
	 $month[6]="June";
	 $month[7]="July";
	 $month[8]="August";
	 $month[9]="September";
	 $month[10]="October";
	 $month[11]="November";
	 $month[12]="December";
	 
	 #- Prepare monthpart
	 $monthpart  = "\t<select name=f[$name" . "_month]" . ">\n";
	 $monthpart .= "\t<option value=0>-Month-</option>\n";
	 for($x=1; $x<13 ;$x++)
	   {
	     $xx = iif($x < 10, sprintf("%02s", $x), $x);
	     if($curr_month == $x)
		    $monthpart .= "\t\t<option value=$xx selected>$month[$x]</option>\n";
		 else
		    $monthpart .= "\t\t<option value=$xx>$month[$x]</option>\n";
	   }#-rof
	 $monthpart .= "\t</select>\n";

	 #- Prepare datepart
	 $datepart  = "\t<select name=f[$name" . "_day]" . ">\n";
	 $datepart .= "\t<option value=0>-Day-</option>\n";
	 $x=0;
	 for($x=1; $x<=$max_days; $x++)
	   {
	     $xx = iif($x < 10, sprintf("%02s", $x), $x);
	     if($curr_day == $x)
		   $datepart .= "\t\t<option value=$xx selected>$x</option>\n";
		 else
		   $datepart .= "\t\t<option value=$xx>$x</option>\n";
	   }
	 $datepart .= "\t</select>\n";

	 #- Prepare yearpart
	 $yearpart  = "\t<select name=f[$name" . "_year]" . ">\n";
	 $yearpart .= "\t<option value=0>-Year-</option>\n";
	 for($x=$year[0]; $x <= $year[count($year)-1]; $x++)
	    {
		  if($curr_year == $x)
		     $yearpart .= "\t\t<option value=$x selected>$x</option>\n";
		  else
		     $yearpart .= "\t\t<option value=$x>$x</option>\n";
		}#-rof
	 $yearpart .= "\t</select>";

	 $parts = $monthpart . $datepart . $yearpart;
	 return($parts);
   }#- end function


#- Check an e-mail address
function lib_checkemail($email) 
{
if(eregi("^[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,3}$", $email, $check)) 
{
	return TRUE;
}
return FALSE;
}


// Get Browser Information
function lib_getbrowser($ua="")
        {
                if(ereg('MSIE ([0-9].[0-9]{1,2})', $ua, $log_version))
                {
                        $BROWSER_VER=$log_version[1];
                        $BROWSER_AGENT='IE';
                }
                elseif(ereg('Opera ([0-9].[0-9]{1,2})',$ua,$log_version))
                {
                        $BROWSER_VER=$log_version[1];
                        $BROWSER_AGENT='OPERA';
                }
                elseif (ereg('Mozilla/([0-9].[0-9]{1,2})',$ua,$log_version))
                {
                        $BROWSER_VER=$log_version[1];
                        $BROWSER_AGENT='MOZILLA';
                }
                else
                {
                        $BROWSER_VER=0;
                        $BROWSER_AGENT='OTHER';
                }

                if (preg_match('/Win/', $ua))
                {
                        $BROWSER_PLATFORM='Win';
                }
                elseif(preg_match('/Mac/', $ua))
                {
                        $BROWSER_PLATFORM='Mac';
                }
                elseif(preg_match('/Linux/', $ua))
                {
                        $BROWSER_PLATFORM='Linux';
                }
                elseif(preg_match('/Unix/', $ua))
                {
                        $BROWSER_PLATFORM='Unix';
                }
                else
                {
                        $BROWSER_PLATFORM='Other';
                }

                return(array($BROWSER_AGENT, $BROWSER_VER, $BROWSER_PLATFORM));
        }   

function lib_authenticate()
{
	header('WWW-Authenticate: Basic realm="AdRevenue Text Ads Configuration"');
    header('HTTP/1.0 401 Unauthorized');
    echo "You must enter a valid login ID and password to access this resource\n";
    exit;
}

?>
