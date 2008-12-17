<?
// 
// AdRevenue Environment Controller
// http.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class http
{
	var $remote_addr;
	var $referer;
	var $request;
	var $user_agent;
	var $hostname;
	
	function http()
	{
		global $f;
		
		$this->remote_addr = $_SERVER[REMOTE_ADDR];
		$this->referer = $_SERVER[HTTP_REFERER];
		$this->request = $_SERVER[REQUEST_URI];
		$this->user_agent = $_SERVER[HTTP_USER_AGENT];
		
		if($_SERVER[SERVER_PORT] == 80)
			$method = "http";
		else
			$method = "https";
		$this->hostname =  "$method://" . $_SERVER[HTTP_HOST] . preg_replace('/(index\.php.*?)$/i', "", $_SERVER[REQUEST_URI]);
		return(1);
	}
}
?>
