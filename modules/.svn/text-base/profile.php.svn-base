<?
// 
// AdRevenue Ad Management
// profile.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

// User can edit their info

class profile extends main
{
	function _default()
	{
		$this->output->secure();
		$f = $this->input->f;
		
		if($f[email] && $f[password] && $f[name] && $f[url])
		{
			if($_SESSION[user][admin] == 3)
			{
				$this->db->update("adrev_users", "id", $_REQUEST[id], $f);
				$this->output->redirect(lib_lang("The profile was updated"), "index.php?section=".$_REQUEST[redir], 1);
			}
			else
			{
				$this->db->update("adrev_users", "id", $_SESSION[user][id], $f);
				$this->output->redirect(lib_lang("Your profile was updated"), "index.php?section=profile", 1);
			}
			exit;
		}
		
		if($_SESSION[user][admin] == 3 && $_REQUEST[id])
			$id = $_REQUEST[id]; 
		else
			$id = $_SESSION[user][id];
		
		$rec = $this->db->getsql("SELECT * FROM adrev_users WHERE id=?", array($id));
		$f = $rec[0];

		// Grab the list of language modules 
		$modules = array();
		if($handle = opendir("lang"))
		{
			while( FALSE !== ($file = readdir($handle)))
			{
				if(preg_match('/^(.*?)\.lng/i', $file, $match))
					$modules[$match[1]] = $match[1];
			}
			closedir($handle);
		}

		$form = new formgen();
		$form->input("<b>".lib_lang("Email")."</b>", "f[email]", stripslashes($f[email]), 40);
		$form->input("<b>".lib_lang("Password")."</b>", "f[password]", stripslashes($f[password]), 20);
		$form->input("<b>".lib_lang("Name")."</b>", "f[name]", stripslashes($f[name]), 40);
		$form->input(lib_lang("Organization"), "f[organization]", stripslashes($f[organization]), 40);
		$form->dropdown(lib_lang("Country"), "f[country]", lib_htlist_array($this->default[country], $f[country]));
		$form->input(lib_lang("Street"), "f[street]", stripslashes($f[street]), 40);
		$form->input(lib_lang("City"), "f[city]", stripslashes($f[city]), 20);
		$form->input(lib_lang("State"), "f[state]", stripslashes($f[state]), 10);
		$form->input(lib_lang("Zip"), "f[postalcode]", stripslashes($f[postalcode]), 10);		
		$form->input("<b>".lib_lang("Url")."</b>", "f[url]", stripslashes($f[url]), 50);
		$form->dropdown(lib_lang("Language"), "f[lang]", lib_htlist_array($modules, $f[lang]));
		$form->hidden("section", "profile");
		$form->hidden("id", $id);
		$form->hidden("redir", $_REQUEST[redir]);
		
		$this->title = lib_lang("Edit Profile");
		$this->content = $form->generate("post", lib_lang("Save Profile"));
		$this->display();
		$this->printpage();	
		exit;
	}
}
