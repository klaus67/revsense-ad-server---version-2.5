<?
// 
// AdRevenue Ad Management
// settings.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

// Edit Site Settings

class settings extends main
{
	function _default()
	{
		$this->output->admin();
		$f = $this->input->f;
		
		// Save Settings
		if(count($f) > 0)
		{
			// set defaults for checkboxes
			if(!$f[confirm_registration])
				$f[confirm_registration] = 0;
			if(!$f[approve_ads])
				$f[approve_ads] = 0;
				
			reset($f);
			while(list($key,$val) = each($f))
			{
				$this->save($key,$val);
			}
			
			$this->output->redirect("The site settings were updated", "index.php?section=settings", 1);
			exit;			
		}
		
		// Loadup stuff
		$settings = $this->db->getsql("SELECT * FROM adrev_settings");
		$set = array();
		foreach($settings as $rec)
			$set[$rec[name]] = stripslashes($rec[value]);

		// Grab list of payment modules
		$modules = array();
		if($handle = opendir("modules"))
		{
			while( FALSE !== ($file = readdir($handle)))
			{
				if(preg_match('/pay\.(.*?)\.php/i', $file, $match))
					$modules[$match[1]] = lib_lang(ucfirst($match[1]));
			}
			closedir($handle);
		}
			
		$form = new formgen();
		$form->comment("<font size=3><b>".lib_lang("Site Properties")."</b></font>");
		$form->input(lib_lang("Site Name"), "f[name]", stripslashes($set[name]), 40);	
		$form->input(lib_lang("URL"), "f[url]", stripslashes($set[url]), 50);
		$form->input(lib_lang("Email"), "f[email]", stripslashes($set[email]), 30);	
		$form->input(lib_lang("Language"), "f[language]", stripslashes($set[language]), 5);
		$form->input(lib_lang("P3P Header"), "f[p3p]", stripslashes($set[p3p]), 60, lib_lang('Compact Privacy Policy Header') . '. <a href="http://www.w3.org/P3P/usep3p.html" target="_new">@@More about P3P@@</a>');
		$form->input(lib_lang("Caching"), "f[cache]", stripslashes($set[cache]), 5, lib_lang("Caching time in seconds"));		
		$form->checkbox(lib_lang("Registrations must be confirmed by email"), "f[confirm_registration]", 1, iif($set[confirm_registration] == 1,"CHECKED",""));		
		$form->line();
		$form->comment("<font size=3><b>".lib_lang("Fraud Protection")."</b></font>");		
		$form->input(lib_lang("Duplicate Clicks"), "f[dup_clicks]", stripslashes($set[dup_clicks]), 5, lib_lang("This is the duplicate clicks threshold seconds."));		
		$form->input(lib_lang("Duplicate Impressions"), "f[dup_impressions]", stripslashes($set[dup_impressions]), 5, lib_lang("This is the duplicate impressions threshold seconds."));
		$form->line();
		$form->comment("<font size=3><b>".lib_lang("Ad Settings")."</b></font>");	
		$form->checkbox(lib_lang("Automatically approve ads."), "f[approve_ads]", 1, iif($set[approve_ads],"CHECKED",""));
		$form->input(lib_lang("Min Bid"), "f[min_bid]", stripslashes($set[min_bid]), 10);
		$form->input(lib_lang("Max Bid"), "f[max_bid]", stripslashes($set[max_bid]), 10);
		$form->input(lib_lang("Min Payment"), "f[min_payment]", stripslashes($set[min_payment]), 10);
		$form->input(lib_lang("Currency"), "f[currency]", stripslashes($set[currency]), 5);
		$form->input(lib_lang("Symbol"), "f[currency_symbol]", stripslashes($set[currency_symbol]), 5);
		$form->input(lib_lang("Default Redir"), "f[default_redir]", stripslashes($set[default_redir]), 40, lib_lang("This is the URL that ads will go to if the url in the ad fails."));
		if($set[payment_module])
			$modlink = "<a href=?section=settings&action=pay_settings>@@Edit Payment Module Settings@@</a>";		
		$form->dropdown(lib_lang("Payment Module"), "f[payment_module]", lib_htlist_array($modules, $set[payment_module]), $modlink);
		$form->line();
		$form->comment("<font size=3><b>".lib_lang("Content")."</b></font>");
		$form->comment("<font size=2>".lib_lang("You can use HTML and images in any of the content fields below")."</font>");		
		$form->textarea(lib_lang("Terms and Conditions"), "f[terms]", $set[terms], 6,60,lib_lang("Enter your terms and conditions"));
		$form->textarea(lib_lang("Frontpage Content"), "f[frontpage]", $set[frontpage], 6,60,lib_lang("This content will appear to users who are not logged in."));	
		$form->textarea(lib_lang("Advertiser Welcome Page"), "f[content_adv_login]", $set[content_adv_login], 6,60,lib_lang("This content will appear to logged in advertisers"));
		$form->textarea(lib_lang("Publisher Welcome Page"), "f[content_pub_login]", $set[content_pub_login], 6,60,lib_lang("This content will appear to logged in publishers"));
		$form->textarea(lib_lang("FAQ"), "f[faq]", $set[faq], 6,60,lib_lang("Your FAQ"));		
		$form->hidden("section", "settings");
		
		$this->title = lib_lang("Edit AdRevenue Settings");
		$this->content = $form->generate("post", lib_lang("Save Settings"));
		$this->display();
		$this->printpage();	
		exit;		
	}
	
	// Payment module settings
	function pay_settings()
	{
		$this->output->admin();
		$f = $this->input->f;
		
		// Set the default to be PayPal
		if(!$this->default[adrevenue][payment_module])
			$this->default[adrevenue][payment_module] = "paypal";	
			
		$mod = "pay." . $this->default[adrevenue][payment_module] . ".php";
		include_once($mod);
		$opt = payment::mod_vars(); 
		$info = payment::mod_info();
		
		if(count($f) > 0)
		{
			reset($f);
			while(list($key,$val) = each($f))
			{
				$this->save($key,$val);
			}
			
			$this->output->redirect("The payment settings were updated", "index.php?section=settings&action=pay_settings", 1);
			exit;
		}
		
		// Show the form
		$form = new formgen();
		$form->comment("<b>" . lib_lang("Enter the settings for your $info[name] payment module") . "</b><br>&nbsp;");
		if($info[extern] == TRUE)
		{
			$form->comment(lib_lang($info[extern_description]) . ": <font color=red>" . $this->default[adrevenue][hostname] . "ipn.php<p>" . "</font>");
		}
		
		foreach($opt as $rec)
		{
			$name = $rec[name];
			$v = $this->db->getsql("SELECT value FROM adrev_settings WHERE name=?", array($name));
			$form->input(lib_lang($rec[label]), "f[$name]", stripslashes($v[0][value]), $rec[length]);
		}
		$form->hidden("submit", "1");
		
		$this->title = lib_lang("Edit $info[name] Payment Settings");
		$this->content = $form->generate("post", lib_lang("Save Payment Settings"));
		$this->display();
		$this->printpage();	
		exit;			
	}
	
	// Save A settings
	function save($key="", $val="")
	{
		if(!$key)
			return( FALSE );
		$val = $this->db->escape(stripslashes($val));
		
		$i = $this->db->getsql("SELECT name FROM adrev_settings WHERE name=?", array($key));
		if($i[0][name])
		{
			$this->db->getsql("UPDATE adrev_settings SET value=? WHERE name=?", array($val, $key));
			if($i[0][name] == "url")
				$this->db->getsql("UPDATE adrev_settings SET value=? WHERE name='hostname'", array($val));
		}
		else
			$this->db->getsql("INSERT INTO adrev_settings (name,value) VALUES (?, ?)", array($key, $val));
		
		return( TRUE );
	}
}

?>
