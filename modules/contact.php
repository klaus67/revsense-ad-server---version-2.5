<?
// 
// AdRevenue Ad Management
// contact.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

// Contact Us

class contact extends main
{
	function _default()
	{
		$f = $this->input->f;

		if($f[email] && $f[subject] && $f[message])
		{
			$f[message] .= "\n\n--------------------\nIP:" . $_SERVER[REMOTE_ADDR] . "\n";
			$f[message] .= "Browser:" . $_SERVER[HTTP_USER_AGENT];
			
			mail($this->default[adrevenue][email], stripslashes($f[subject]), stripslashes($f[message]), "From: <$f[email]>");
			$this->output->redirect("Your message was sent", "index.php", 1);
			exit;
		}
		
		if(!$f[email])
			$f[email] = $_SESSION[user][email];
			
		// Show the form
		$form = new formgen();
		$form->comment(lib_lang("If you have a problem or a question, please contact us using the form below"));
		$form->input("<b>".lib_lang("Email")."</b>", "f[email]", $f[email], 30);
		$form->input("<b>".lib_lang("Subject")."</b>", "f[subject]", $f[subject], 60);
		$form->textarea("<b>".lib_lang("Message")."</b>", "f[message]", $f[message], 15,62);
		$form->hidden("section", "contact");
		
		$this->title = lib_lang("Contact Us");
		$this->content = $form->generate("post", lib_lang("Send Message"));
		$this->display();
		$this->printpage();	
		exit;				
	}
}

?>
