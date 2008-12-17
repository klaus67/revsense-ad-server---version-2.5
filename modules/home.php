<?
//
// RevSense Home page
//
// home.php
// Shows the home page
//
// (C) 2004-2006 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class home extends main
{
	function _default()
	{
		$this->title = lib_lang("Welcome to ") . $this->default[adrevenue][name];
		$this->heading = $this->title;

		if($_SESSION[user][admin] == 2)
			$this->content = $this->default[adrevenue][content_pub_login];
		elseif($_SESSION[user][admin] == 1 || $_SESSION[user][admin] == 3)
		{
			// Do we have ads in our account?
			$uid = $_SESSION['user']['id'];
			$ads = $this->db->getsql("SELECT count(*) as num FROM adrev_ads WHERE userid=?", array($uid));
			if ($ads[0]['num'] > 0) {
				header('Location: ?section=ads');
				return;
			}
				
			$this->content = $this->default[adrevenue][content_adv_login];
		}
		else
			$this->content = $this->default[adrevenue][frontpage];
			
		$this->display();
		$this->printpage();
		exit;
	}
}

?>
