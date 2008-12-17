<?
//
// Revsense Ad Management
// doc.php
//
// (C) 2004-2006 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class doc extends main
{
	function _default()
	{
		header("Location: index.php");
		exit;
	}
	
	function terms()
	{
		$this->title = "@@Terms and conditions@@";
		$this->heading = "@@Advertising Terms & Conditions@@";
		
		$this->content = $this->default[adrevenue][terms];
		$this->display();
		$this->printpage();
		exit;		
	}
	
	function faq()
	{
		$this->title = "@@Frequently Asked Questions@@";
		$this->heading = "@@Frequently Asked Questions@@";
		
		$this->content = $this->default[adrevenue][faq];
		$this->display();
		$this->printpage();
		exit;			
	}
}
