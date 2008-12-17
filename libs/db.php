<?
// 
// db.php
//

class database
{
	var $dsn;
	var $db;
	var $errormsg;
	var $result;
	var $rows;
	var $affected_rows;
	var $engine;
	var $tz;
	
	// Connect to the default database
	function connect()
	{
		global $DEFAULT;

		$connector = $DEFAULT[engine] . "_database";		
		$this->$connector($DEFAULT[host], $DEFAULT[database], $DEFAULT[user], $DEFAULT[password]);
		
		return ($this->db);
	}
	
	// Postgresql Database Server connection
	function pg_database($host, $database, $user, $password, $port=5432)
	{
		$this->dsn = "host=$host port=5432 dbname=$database user=$user password=$password";
		$this->db = @pg_connect($this->dsn);
		
		if(!$this->db)
		{
			$this->errormsg = @pg_last_error($this->db);
			return(0);
		}
		
		$this->engine = "pg";
		return(1);
	}
	
	// MySQL Database Server connection
	function mysql_database($host, $database, $user="", $password="")
	{
		$this->dsn = "";
		$this->db = @mysql_connect($host, $user, $password);
		if(!$this->db)
		{
			$this->errormsg = mysql_error();
			return(0);
		}
		
		if(!mysql_select_db($database, $this->db))
		{
			$this->errormsg = "Database <b>$database</b> was not found.<p>";
			return(0);
		}
		$this->engine = "mysql";
		return(1);
	}

	// SqLite Database connection
	function sqlite_database($database="")
	{
		$this->dsn = "";
		if($this->db = @sqlite_open($database, 0666, $sqliteerror))
		{
			return(1);
		}
		else
		{
			$this->errormsg = $sqliteerror;
		}

		return(0);
	}

	// Query the db - returning multiple records
	// Good only for up to 100 or so records.
	function getsql($sql, $binds=false)
	{
		// Replace placeholders with escaped binds
		if ($binds && is_array($binds)) {
			$s = preg_split('/\?/', $sql, -1, PREG_SPLIT_NO_EMPTY);
			$n = count($binds);
			for($x = 0; $x < $n; $x++) {
				$s[$x] .= "'" . $this->escape($binds[$x]) . "'";
			}
			$sql = implode($s);
		}

		$out = array();
		$this->query($sql);
		while($rec = $this->nextrow()) 
		{
			if($rec)
				$out[] = $rec;
		}
		
		return($out);
	}
	
	// Output SQL into csv
	function getcsv($sql="", $filename="output.csv")
	{
		// Send headers
		header("Pragma: public");
		header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT'); 
		header('Cache-Control: no-store, no-cache, must-revalidate');  
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');  
		header('Content-Transfer-Encoding: none'); 
		if(preg_match('/MSIE|Explorer|Microsoft|\.NET|Opera/i', $_SERVER[HTTP_USER_AGENT]))
		{
			header("Content-type: application/octetstream; name=\"$filename\"");
		}
		else
		{
			header('Content-Type: application/octet-stream; name="' . $filename . '"');  
			header('Content-Disposition: inline; filename="' . $filename . '"'); 
		}
		
		$this->query($sql);
		$x = 0;
		while($rec = $this->nextrow())
		{
			// Cleanup UNIX dates
			if($rec['date'])
			{
				if(!preg_match('/\-/', $rec[date]))
					$rec['date'] = date("M d Y h:i:s", $rec[date]);
			}
			
			if($x == 0)
			{
				$keys = array_keys($rec);
				$n = count($keys);
				for($x=0;$x<$n;$x++)
					$keys[$x] = lib_lang(ucfirst($keys[$x]));
					
				echo '"' . implode('","', array_values($keys)) . '"' . "\r\n";
			}
			echo '"' . implode('","', array_values($rec)) . '"' . "\r\n";
			$x++;
		}
		
		exit;
	}
	
	// Escape a string
	function escape($str="")
	{
		if($this->engine == "mysql")
			return(mysql_real_escape_string($str, $this->db));
			
		if($this->engine == "pg")
			return(pg_escape_string($str));
			
		if($this->engine == "sqlite")
			return(sqlite_escape_string($str));
			
		return($str);
	}
	
	// Do a query	
	function query($sql="")
	{
		// COnnect if we need to
		if(!$this->db)
			$this->connect();
		
		// $this->tz = lib_getmicrotime();
		$this->sql = $sql;
		
		if($this->db)
		{
			if($this->engine == "pg")
			{
				// Postgresql Query
				$this->result = pg_query($this->db, $sql);
				if($this->result)
				{
					$this->rows = pg_num_rows($this->result);
					$this->affected_rows = @pg_affected_rows($this->result);
					$this->log();
					return(1);
				}
			}
			elseif($this->engine == "mysql")
			{
				// MySQL Query
				$this->result = @mysql_query($sql, $this->db);
				if($this->result && !preg_match('/INSERT|UPDATE|DELETE/i', $this->sql))
				{
					$this->rows = @mysql_num_rows($this->result);
					$this->affected_rows = @mysql_affected_rows($this->db);
					$this->log();
					return(1);
				}
				
				if(@mysql_errno($this->db))
				{
					print "SQL ERROR: " . @mysql_error($this->db) . "<br>SQL: $this->sql";
					print "<hr>";
					exit;
				}
			}
			elseif($this->engine == "sqlite")
			{
				// SqLite Query
				$this->result = @sqlite_unbuffered_query($this->db, $sql);
				$this->rows = 0;
				$this->affected_rows = 0;
				$this->log();
				return(1);
			}
		}
		
		return(0);
	}
	
	// See if a table exists
	function exists($table="")
	{
		if(!$table)
			return (FALSE);
		if($this->engine == "mysql")
		{
			return(mysql_query("SELECT 1 FROM $table LIMIT 0", $this->db));
		}
		elseif($this->engine == "pg")
		{
			$rel = $this->query("SELECT relname FROM pg_class WHERE relname='$table'");
			if($rec[0][relname])
				return (TRUE);
		}
		
		return (FALSE);
	}
	
	// Get the next record
	function nextrow()
	{
		if($this->result)
		{
			if($this->engine == "pg")
				return(pg_fetch_array($this->result,NULL, PGSQL_ASSOC));
			elseif($this->engine == "mysql")
				return(@mysql_fetch_array($this->result, MYSQL_ASSOC));
			elseif($this->engine == "sqlite")
				return(@sqlite_fetch_array($this->result, SQLITE_ASSOC));
		}
		else
		{
			return(array());
		}
	}
	
	// Insert a row
	function insert($table="", $arr=array())
	{
		if(!is_array($arr))
			return(0);

		$names = implode(',', array_keys($arr));
		
		$values = array();
		reset($arr);
		while(list($k,$v) = each($arr))
		{
			$values[] = "'" . $this->escape(stripslashes($v)) . "'";
		}
		
		$vals = implode(",", $values);
		$sql = "INSERT INTO $table ($names) VALUES($vals)";

		return($this->getsql($sql));
     }

	 // Update a row
	 function update($table="", $key="",$val="", $arr=array())
	 {
		 if(!is_array($arr))
		 	return(0);
		
		$sql = array();
		while(list($k,$v) = each($arr))
		{
			$sql[] = "$k='" . $this->escape(stripslashes($v)) . "'";
		}
		
		$query = "UPDATE $table SET " . implode(", ", $sql) . " WHERE $key='$val'";

		return($this->getsql($query));
	 }
	 
	 // Make an HT List from the database
	 function htlist($table="",$keyfield="",$valuefield="",$default="", $other="", $separator="-")
	 { 
		$sql = "SELECT $keyfield,$valuefield FROM $table $other ORDER BY $valuefield";
	 	$result = $this->getsql($sql);
		
		if(count($result) > 0)     
		foreach($result as $rec)
		{
			// We might have multiple fields selected
			if(preg_match('/,/', $valuefield))
			{
				$parts = explode(",", trim($valuefield));
				$data = array();
				foreach($parts as $r)
					$data[] = $rec[$r];
				$rec[$valuefield] = implode($separator, $data);
			}
			
			if(strlen($rec[$valuefield]) > 64)
				$name = substr($rec[$valuefield],0,64) . "...";
			else
				$name = $rec[$valuefield];

			$id = $rec[$keyfield];
			if($default == $id)
				$clist .= "\t\t<option value=$id selected>$name</option>\n";
			else
				$clist .= "\t\t<option value=$id>$name</option>\n";
        }
		return ( $clist );		 
	 }
	 
	 // Perform keyword lookup
	 function get_keyword($keyword="", $add=1)
	 {
		if(!$keyword)
			return(0);
		
		// Lookup the keyword first
		$keyword = $this->escape(trim(strtolower($keyword)));
		if($keyword)
		{
			$k = $this->getsql("SELECT id FROM adrev_keywords WHERE keyword='$keyword'");
			if($k[0][id])
				return($k[0][id]);
			
			// Optionally add the keyword
			if($add)
			{
				$fuzzy = substr(metaphone($keyword), 0, 254);
				$this->getsql("INSERT INTO adrev_keywords (keyword, fuzzy_keyword) VALUES ('$keyword', '$fuzzy')");
				$k = $this->getsql("SELECT id FROM adrev_keywords WHERE keyword='$keyword'");
				if($k[0][id])
					return($k[0][id]);
			}
		}
		
		return(0);
	 }

	 // Log SQL queries with their times
	 function log()
	 {
		 return ( TRUE );
		 
	 	// $this->tz = lib_getmicrotime() - $this->tz;

	 	$fp = fopen("cache/sql.log", "a");
		if(flock($fp, LOCK_EX))
		{
			$sql = str_replace("\n", " ", $this->sql);
			fputs($fp, "$this->tz\t$sql\n");
			flock($fp, LOCK_UN);
		}

		fclose($fp);
	 }

}	

?>
