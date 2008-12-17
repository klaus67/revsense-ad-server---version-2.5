<?
//
// (C) 2004 W3matter.com
//
// This is commercial code!

// This will generate forms and tables based on values passed in

class formgen
{
	var $form;
	var $table;
	var $csv;
	
	function formgen()
	{
		$this->form = "";
	}
	
	// Generate the form
	function generate($method="get", $submitvalue="Submit", $title="")
	{
		$out .= "<Table width=100% border=0 cellspacing=0 cellpadding=4>\n";
		$out .= "<form method=\"$method\">\n";
		if($title)
		{
			$out .= "<tr><Td colspan=2>$title<p></td></tr>\n";
		}
		$out .= $this->form;
		$out .= "<tr>\n";
		$out .= "	<td colspan=2><input type=submit value=\"$submitvalue\"></td>\n";
		$out .= "</tr>\n";
		$out .= "</form>\n";
		$out .= "</table>\n";
		
		return($out);
	}
	
	// Input Element
	function input($title="", $name="", $value="", $width=30, $notes="")
	{
		$value = htmlentities(stripslashes($value));
		if($notes)
		{
			$addnote = "<br><font size=1>$notes</font>";
			$valign = "valign=top";
		}
		$this->form .= "<tr>\n";
		$this->form .= "	<td width=1 $valign>$title:</td>\n";
		$this->form .= "	<td width=99%><input type=text name=\"$name\" value=\"$value\" size=\"$width\">$addnote</td>\n";
		$this->form .= "</tr>\n";
	}
	
	// Checkbox element
	function checkbox($title="", $name="", $value="1", $checked = "")
	{
		$this->form .= "<tr>\n";
		$this->form .= "	<td width=1>&nbsp;</td>\n";
		$this->form .= "	<td width=99%><input type=checkbox name=\"$name\" value=\"$value\" $checked> $title</td>\n";
		$this->form .= "</tr>\n";		
	}
	
	// Textarea elements
	function textarea($title="", $name="", $value="", $rows=3, $cols=60, $notes="")
	{
		$value = htmlentities(stripslashes($value));
		if($notes)
			$addnote = "<br><font size=1>$notes</font>";		
		$this->form .= "<tr>\n";
		$this->form .= "	<td width=1 valign=top>$title:</td>\n";
		$this->form .= "	<td width=99%><textarea name=\"$name\" rows=$rows cols=$cols wrap=virtual>$value</textarea>$addnote</td>\n";
		$this->form .= "</tr>\n";		
	}
	
	// Draw a line
	function line()
	{
		$this->form .= "<tr><Td colspan=2><hr size=1 noshade></td></tr>\n";
	}
	
	// Place a comment
	function comment($comment="")
	{
		$this->form .= "<tr><td colspan=2>$comment</td></tr>\n";
	}
	
	// Hidden element
	function hidden($name="", $value="")
	{
		$this->form .= "<input type=hidden name=\"$name\" value=\"$value\">\n";		
	}
	
	// Dropdown
	function dropdown($title="", $name="", $options="", $notes="")
	{
		if($notes)
			$addnote = "<br><font size=1>$notes</font>";
		$this->form .= "<tr>\n";
		$this->form .= "	<td width=1 valign=top>$title:</td>\n";
		$this->form .= "	<td width=99%>\n";
		$this->form .= "	<select name=\"$name\">\n";
		$this->form .= "		$options\n";
		$this->form .= "	</select>$addnote\n";
		$this->form .= "	</td>\n";
		$this->form .= "</tr>\n";		
	}
	
	// Add a table row
	function startrow($bgcolor="#FFFFFF")
	{
		$this->table .= "<tr>\n";
        $this->thead = true;
	}
	
	// End a table row
	function endrow()
	{
		$this->table .= "</tr>\n";
        $this->thead = false;
	}
	
	// Add a column
	function column($content="&nbsp;", $bgcolor="", $width="", $valign="", $align="",$style="",$colspan="")
	{
		if($bgcolor)
			$bgcolor="bgcolor=\"$bgcolor\"";
		if($width)
			$width="width=\"$width\"";
		if($valign)
			$valign="valign=\"$valign\"";
		if($align)
			$align="align=\"$align\"";
		if($style)
			$style="style=\"$style\"";
		if($colspan)
			$colspan="colspan=\"$colspan\"";
        
        if ($this->thead) {
            $this->table .= "<th $bgcolor $width $valign $align $colspan>$content</th>\n";
        } else {
            $this->table .= "<td $bgcolor $width $valign $align $colspan>$content</td>\n";
        }
	}
	
	// Generate the table
	function gentable($width="100%", $border=0, $cellspacing=0, $cellpadding=0, $bgcolor="")
	{
		if($bgcolor)
			$bgcolor = "bgcolor=\"$bgcolor\"";
		
		//$out = "<table width=\"$width\" border=\"$border\" cellspacing=\"$cellspacing\" cellpadding=\"$cellpadding\" $bgcolor>\n";
        $out .= "<table class=\"xtable\" width=\"{$width}\" cellspacing=\"0\">";
		$out .= $this->table;
		$out .= "</table>\n";
		
		return($out);
	}
}
?>