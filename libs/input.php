<?
// 
// Revsense Input Controller
// input.php
//
// (C) 2004 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class input
{
	var $action;
	var $section;
	var $method;
	var $f;
	
	function input()
	{
		$this->action = $_REQUEST[action];
		$this->section = $_REQUEST[section];
		$this->method  = $_SERVER[REQUEST_METHOD];
		$this->f = $_REQUEST[f];

		return(1);
	}
	
	// Payment Form
	function payment_form()
	{
		// Show the form
		$month = array(1=>"01",2=>"02",3=>"03",4=>"04",5=>"05",6=>"06",7=>"07",8=>"08",9=>"09",10=>"10",11=>"11",12=>"12");
		$year = array(date("Y"), date("Y")+1, date("Y")+2, date("Y")+3,date("Y")+4, date("Y")+5, date("Y")+6, date("Y")+7, date("Y")+8);
		$cards = array('Visa'=>lib_lang('Visa'), 
						'MC'=>lib_lang('Mastercard'), 
						'AMEX'=>lib_lang('American Express'), 
						'Discover'=>lib_lang('Discover')
					  );
		
		$form = new formgen();
		$form->comment(lib_lang("Your Billing Information"));
		$form->input("<b>".lib_lang("First Name")."</b>", "f[first_name]", $f[first_name], 30);
		$form->input("<b>".lib_lang("Last Name")."</b>", "f[last_name]", $f[last_name], 30);
		$form->dropdown("<b>".lib_lang("Card Type")."</b>", "f[cardtype]", lib_htlist_array($cards, $f[cardtype]));
		$form->dropdown("<b>".lib_lang("Expire Month")."</b>", "f[last_name]", lib_htlist_array($month, $f[month]));
		$form->dropdown("<b>".lib_lang("Expire Year")."</b>", "f[last_year]", lib_htlist_array($year, $f[year]));
		$form->input("<b>".lib_lang("Card Number")."</b>", "f[cardnumber]", $f[cardnumber], 20);
		$form->line();
		$form->comment("<font size=3><b>".lib_lang("Billing Address")."</b></font>");
		$form->dropdown(lib_lang("Country"), "f[country]", lib_htlist_array($this->default[country], $f[country]));
		$form->input("<b>".lib_lang("Address 1")."</b>", "f[address1]", $f[address1], 50);		
		$form->input("<b>".lib_lang("Address 2")."</b>", "f[address2]", $f[address2], 50);
		$form->input("<b>".lib_lang("City")."</b>", "f[city]", $f[city], 25);
		if($this->default[adrevenue][country] == "US")
			$form->dropdown(lib_lang("US State"), "f[state]", lib_htlist_array($this->default[states], $f[state]));
		else
			$form->input("<b>".lib_lang("State")."</b>", "f[state]", $f[state], 20);
		$form->input("<b>".lib_lang("Phone")."</b>", "f[phone]", $f[phone], 25);
		$form->input("<b>".lib_lang("Email")."</b>", "f[email]", $f[email], 25);
		$form->hidden("section", "pay");
		$form->hidden("action", "form");		
		$form->hidden("f[amount]", $f[amount]);
		
		$this->output->title = lib_lang("Edit Your Profile");
		$this->output->content = $form->generate("post", lib_lang("Save Profile"));
		$this->output->display();
		$this->output->printpage();	
		exit;	
	}
}



?>
