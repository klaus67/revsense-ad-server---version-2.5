<?
// 
// RevSense Ad Management
// ads.php
//
// (C) 2004-2006 W3matter LLC
// This is commercial software!
// Please read the license at:
// http://www.w3matter.com/license
//

class ads extends main
{
	function _default()
	{
		// By default, show ads
		$this->listing();
	}

	// Renew an ad
	function renew()
	{
		$this->output->secure();	
		
		$f = $this->input->f;
		$uid = $_SESSION['user']['id'];	
		
		$a = $this->db->getsql("SELECT * FROM adrev_ads WHERE zid=? AND userid=?", array($f['id'], $uid));
		$ad = $a['0'];
		if($ad['id'])
		{
			$this->db->getsql("UPDATE adrev_ads SET expires=0, total_units=0, status=1 WHERE id=?", array($ad['id'])); 
			$this->output->redirect("The ad was renewed", "index.php?section=ads", 2);
			exit;
		}
		
		$this->output->redirect("There was a problem renewing your ad", "index.php?section=ads", 3);
		exit;
	}
	
	// Show the conversion code
	function conversioncode()
	{
		$tpl = new XTemplate("templates/ads_conversion.html");

		$tpl->assign("UID", $_SESSION['user']['id']);
		$tpl->parse("main");
		$this->title = lib_lang("Ad Conversion Code");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;
	}

	// Show the stuff for a zone
	function adzone()
	{
		$id = $_GET['id'];
		$z = $this->db->getsql("SELECT a.*,b.name as typename 
									FROM adrev_zones a, adrev_ad_types b 
									WHERE a.style=b.id AND a.id=?", array($id));
		$zone = $z['0'];

		if(!$zone['id'])
		{
			// If no ID, redirect
			header("Location: index.php?section=ads&action=edit");
			exit;
		}

		$tpl = new XTemplate("templates/zone_show.html");

		$tpl->assign("ZONE", $id);
		$tpl->assign("TITLE", stripslashes($zone['name']));
		$tpl->assign("CONTENT", stripslashes($zone['description']));
		$tpl->assign("ID", $id);
		$tpl->assign("RATE_TYPE", $zone['rate_type']);
		$tpl->assign("TYPE", $zone['typename']);
		$tpl->assign("RATE", $zone['rate']);

		$tpl->parse("main");
		$this->title = lib_lang("Create New Ad in") . ": <font color=black>{$zone['name']}</font>" ;
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;
	}

	// Show the ads for this user
	function listing()
	{
		global $DEFAULT;

		$this->output->secure();	
		
		$f = $this->input->f;
		$uid = $_SESSION['user']['id'];
	
		// Change the status of an ad
		if($_REQUEST['c'] == "pause" && $f['id'])
		{
			$this->db->getsql("UPDATE adrev_ads SET status='3' WHERE zid=? AND userid=?", array($f['id'], $uid));			
			$this->output->redirect(lib_lang("Ad was paused"), "index.php?section=ads", 1);
			exit;
		}
		elseif($_REQUEST['c'] == "activate" && $f['id'])
		{
		     
			 $chk = $this->db->getsql("SELECT b.rate, a.bid, b.ad_sort, keywords_enable
									FROM adrev_ads a, adrev_zones b
									WHERE a.userid=?
									AND a.zid = ? 
									AND a.zone=b.id", array($uid, $f['id']));
			$check = $chk['0'];
			
			if($check['ad_sort']=='bid' AND $check['keywords_enable']!=1)
			{
						
			   if($check['bid'] >= $check['rate'])
			   {
			     $this->db->getsql("UPDATE adrev_ads SET status='1' WHERE zid=? AND userid=?", array($f['id'], $uid));
			     $this->output->redirect(lib_lang("Ad was reactivated"), "index.php?section=ads", 1);
			     exit;
			   }
			   else
			   {
	              $this->output->redirect(lib_lang("This ad is below the miniumn bidding amount and
	                                              cannot be activated"),"index.php?section=ads", 1);
			      exit;
		  	   }
		    }
			else
			{
			     $this->db->getsql("UPDATE adrev_ads SET status='1' WHERE zid=? AND userid=?", array($f['id'], $uid));
			     $this->output->redirect(lib_lang("Ad was reactivated"), "index.php?section=ads", 1);
			     exit;
			}
			  
		}
	     
		// Set some defaults
		$tpl = new XTemplate("templates/ads.html");
		list($startdate, $enddate) = lib_date_range($f['date']);		
		$dates = array("today"=>lib_lang('Today'), "yesterday"=>lib_lang('Yesterday'), "thisweek"=>lib_lang('This Week'), 
						"lastweek"=>lib_lang('Last Week'), "thismonth"=>lib_lang('This Month'), 
						"lastmonth"=>lib_lang('Last Month'), all=>lib_lang('All Time'));
		

		if ($f['zone_id'] > 0) {
			// Grab this users ads
			$ads = $this->db->getsql("SELECT a.*,b.name, b.rate, b.rtype, rate_type, ad_sort, a.status, a.zone, b.name, keywords_enable 
										FROM adrev_ads a, adrev_zones b
										WHERE a.userid=? AND a.zone=? AND a.zone=b.id
										ORDER BY a.id DESC", array($uid, $f['zone_id']));			
		} else {		
			// Grab this users ads
			$ads = $this->db->getsql("SELECT a.*,b.name, b.rate, b.rtype, rate_type, ad_sort, a.status, a.zone, b.name, keywords_enable 
										FROM adrev_ads a, adrev_zones b
										WHERE a.userid=? AND a.zone=b.id
										ORDER BY a.id DESC", array($uid));
		}
		
		if(count($ads) > 0)
		{
			foreach($ads as $rec)
			{
				// Loadup stats
				$stats = $this->db->getsql("SELECT count(id) as num, sum(clicks) as clicks, 
												sum(impressions) as impressions, sum(orders) as orders,
												sum(amount) as spend
											FROM adrev_traffic
											WHERE adid=? AND userid=? 
												AND date BETWEEN ? AND ?
											GROUP BY adid", array($rec['id'], $uid, $startdate, $enddate));
				
												
							
				$ef = "";
				$bf = "";
				$bgcolor = iif($bgcolor == "#FFFFFF", "#FFFFEE", "#FFFFFF");
				$status = $DEFAULT['status'][$rec['status']];
				
				if($rec['type'] == "CPC")
					$rec['type'] = lib_lang("per/click");
				elseif($rec['type'] == "CPM")
					$rec['type'] = lib_lang("per/1000");
				elseif($rec['type'] == "CPD")
					$rec['type'] = lib_lang("per/day");
			
				if($rec['rtype'] == 2)
				{
					$rate = $rec['bid'];
					
					$position = $this->db->getsql("SELECT zid,bid FROM adrev_ads WHERE zone=?
																				 AND bid >= ? 
																				 AND status != '-2'
																				 AND status != '-1'
																				 AND status != '3'
																				 ORDER BY bid DESC", array($rec['zone'], $rec['rate']));
					$pos = 0;
					foreach($position as $p)
					{
						$pos++;
						if($p['zid'] == $rec['zid'])
							break;
					}
				}
								
				if($rec['ad_sort']=='bid' AND $rec['keywords_enable']!=1)
				{
					 
					         if($rec['status']=='3')
							 {
							   $rate = $rec['bid'];
						       $pos = "<font color=red>Paused</font>";
						     }
							 if($rec['status']=='-1')
							 {
							   $rate = $rec['bid'];
						       $pos = "<font color=red>-</font>";
							 }
							 if($rec['bid'] < $rec['rate'])
							 {
				               $rate = $rec['bid'];
						       $pos = "<font color=red>Low_Bid</font>";
						 	 }
						     
				}
				else
				{
				    $rate = $rec['rate'];
				    $pos = "-";
				}
				    
				
				//End of if statement Added
			
				$exp = $rec['expires'] > 0 ? str_replace(" ", "&nbsp;", date("m/d/y", $rec['expires'])) : "<center>-</center>";
				$sta = $rec['startdate'] > 0 ? str_replace(" ", "&nbsp;", date("m/d/y", $rec['startdate'])) : "<center>-</center>";
				
				$tpl->assign("EXPIRES", $bf . $exp . $ef);
				$tpl->assign("BEGIN", $bf . $sta . $ef);
				$tpl->assign("POS", $bf . $pos . $ef);
				$tpl->assign("ZONE", $bf . $rec['name'] . $ef);
				$tpl->assign("RATE", $bf . number_format($rate,2) . "&nbsp;<font color=brown>" . $rec['rate_type'] . "</font>" . $ef);
				$tpl->assign("TITLE", $bf . $rec['title'] . $ef);
				$tpl->assign("BGCOLOR", $bgcolor);
				$tpl->assign("DATE", $bf . str_replace(" ", "&nbsp;", date("m/d/y", $rec['date'])) . $ef);
				$tpl->assign("ID", $rec['id']);
				$tpl->assign("ZID", $rec['zid']);
				$tpl->assign("STATUS", $this->default['status'][$rec['status']]);
				$tpl->assign("IMPRESSIONS", $bf . number_format($stats['0']['impressions']) . $ef);
				$tpl->assign("CLICKS", $bf . number_format($stats['0']['clicks']) . $ef);
				$tpl->assign("CTR", $bf . number_format(($stats['0']['clicks'] * 100)/iif(!$stats['0']['impressions'],1,$stats['0']['impressions']),2) . $ef);
				$tpl->assign("ORDERS", $bf . number_format($stats['0']['orders']) . $ef);
				$tpl->assign("CONVERSIONRATE", $bf . number_format(($stats['0']['orders'] * 100)/iif(!$stats['0']['clicks'],1,$stats['0']['clicks']),2) . $ef);	
				$tpl->assign("SPEND", $bf . number_format($stats['0']['spend'],2) . $ef);
				$tpl->assign("SPEND_LIMIT", $bf . number_format($rec['spend_limit'],2) . $ef);
				$tpl->assign("UNITS", $bf . number_format($rec['total_units']) . "/" . number_format($rec['units']) . $ef);
				
				if($rec['status'] == 1)
				{
					$checker  = "<a href=\"?section=ads&c=pause&f[id]={$rec['zid']}\" title=\"". lib_lang('Pause Ad') ."\">";
					$checker .= "@@Pause@@</a>";
					$tpl->assign("CHECKER", $checker);
				}
				elseif($rec['status'] == "3")
				{
					$checker  = "<a href=\"?section=ads&c=activate&f[id]={$rec['zid']}\" title=\"".lib_lang('Activate ad')."\">";
					$checker .= "<font color=red>@@Resume@@</font></a>";
					$tpl->assign("CHECKER", $checker);
				}
				elseif($rec['status'] == "-2")
				{
					$checker  = "<a href=\"?section=ads&action=renew&f[id]={$rec['zid']}\" title=\"".lib_lang('Renew ad')."\">";
					$checker .= "<font color=orange>@@Renew@@</font></a>";
					$tpl->assign("CHECKER", $checker);					
				}
				else
				{
					$tpl->assign("CHECKER", "&nbsp;");
				}

				// Totals
				$t_impressions += $stats['0']['impressions'];
				$t_clicks += $stats['0']['clicks'];
				$t_orders += $stats['0']['orders'];
				$t_spend  += $stats['0']['spend'];

				$tpl->parse("main.list");
			}
		}
			

		$tpl->assign("T_IMPRESSIONS", number_format($t_impressions));
		$tpl->assign("T_CLICKS", number_format($t_clicks));
		$tpl->assign("T_CTR", number_format(($t_clicks * 100)/iif(!$t_impressions,1,$t_impressions),2));
		$tpl->assign("T_ORDERS", number_format($t_orders));
		$tpl->assign("T_ROI",number_format(($t_orders * 100)/iif(!$t_clicks,1,$t_clicks),2));
		$tpl->assign("T_SPEND", number_format($t_spend,2));
		$tpl->assign("DATE", $f['date']);

		// Grab our zones
		$z = $this->db->getsql('SELECT b.id, b.name FROM adrev_ads a, adrev_zones b WHERE a.zone=b.id AND a.userid=? GROUP BY b.id, b.name', array($uid));
		if ($z[0]['id']) {
			$zlist = array();
			foreach($z as $rec) {
				$zlist[$rec['id']] = $rec['name'];
			}
			$tpl->assign('ZONELIST', lib_htlist_array($zlist, $f['zone_id']));
		}
		
		$tpl->assign("DROPDATE", lib_htlist_array($dates, $f['date']));
		$tpl->assign("STARTDATE", date("D d M", strtotime($startdate)));
		$tpl->assign("ENDDATE", date("D d M", strtotime($enddate)));
		
		$tpl->parse("main");
		$this->title = lib_lang("My Ads");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;			
	}
	
	// Create or edit an ad
	function edit()
	{
		$this->output->secure();	
		
		$f = $this->input->f;
		
		$uid = $_SESSION['user']['id'];
		
		if($_SESSION['user']['admin'] == 3)
		{
			$xad = $this->db->getsql("SELECT userid FROM adrev_ads WHERE zid=?", array($f['id']));
			$uid = $xad['0']['userid'];
		}		
		
		// Save the ad
		if($f['submit'] && count($f['data']) > 0)
		{
		     if(!isset($f['data']['TITLE']))
			    $errormsg .= "<li>". "<b>Your ads must have a title</b>";
		   			
			// Get the zone information
			$d = $this->db->getsql("SELECT * FROM adrev_ads WHERE zid=? AND userid=?", array($f['id'], $uid));
			if(!$f['zone'])
				$f['zone'] = $d['0']['zone'];
			if($f['zone'])
			{
				$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($f['zone']));
				$zone = $z['0'];
				$format = @unserialize($z['0']['ad_format']);
			}			
			// Upload the file
			if($_FILES['userfile']['name'])
			{
				// See if the extension if "png,jpg,gif"
				if(!preg_match('/png|gif|jpg|swf|jpeg/i', $_FILES['userfile']['name']))
					$errormsg .= "<li> ". lib_lang("You can only upload") . " <b>.png, .gif, .jpg, .swf, .jpeg</b> ". lib_lang("files");
				
				if(!$errormsg)
				{
					$e = pathinfo($_FILES['userfile']['name']);
					$filename = uniqid("") . "." . $e['extension'];
					
					$uploadfile = "banners/$filename";				
					if(move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile))
					{
						// Check size of image
						list($width, $height, $type, $attr) = getimagesize($uploadfile);
						if($width == $f['img_width'] && $height == $f['img_height'])
							$f['data']['IMAGE'] = $this->http->hostname . $uploadfile;
						else
							$errormsg .= "<li> ". lib_lang("Error uploading image") . " - $width " . lib_lang("IS NOT EQUAL TO") .
							" {$f['img_width']} " . lib_lang("OR") . " $height " . lib_lang("IS NOT EQUAL TO") . " {$f['img_height']}";
					}
					else
					{
						if($_FILES['userfile']['error'] == UPLOAD_ERR_FORM_SIZE || $_FILES['userfile']['error'] == UPLOAD_ERR_INI_SIZE)
							$errormsg .= "<li> " . lib_lang("Error uploading image - Image was too large");
						else
							$errormsg .= "<li> " . lib_lang("Error uploading image");
					}
				}
			}
			
			// Check for field lengths and values
			reset($format);
			while(list($key,$val) = @each($format))
			{
				if(strlen($f['data'][$val['type']]) > $val['max_length'])
					$errormsg .= "<li> " . lib_lang("Your") . " <b>{$val['name']}</b> " . lib_lang("is too long");
				if(strlen($f['data'][$val['type']]) == 0)
					$errormsg .= "<li> " . lib_lang("You must enter a value for") . " <b>{$val['name']}</b>";
			}
				
			// Save it if we are ok
			if(!$errormsg)
			{
				$i = array();
				
				reset($f['data']);
				while(list($key,$val) = each($f['data']))
				{
					$k = strtolower($key);
					$i[$k] = $val;
				}
				
				if(!$f['id'])
				{
					$i['userid'] = $_SESSION['user']['id'];
					$i['zid'] = uniqid("");
					$i['date'] = time();
					$i['status'] = iif($zone['auto_approve'] == 1, 1, 2);;
					$i['zone'] = $f['zone'];
					$i['bid'] = iif($zone['rtype'] == 2, $zone['rate'], 0);
					
					// Set min bid based on zone
					if($zone['rtype'] == 2)
					{
						$i['bid'] = str_replace(array('$',','), "", $f['bid']);
						if($i['bid'] < $zone['rate'])
							$i['bid'] = $zone['rate'];
					}

					// Set expire date
					if($zone['runtime'] > 0)
						$i['expires'] = time() + ($zone['runtime'] * 86400);
					else
						$i['expires'] = 0;
					
					// Set max impressions
					if($zone['units'] > 0)
						$i['units'] = $zone['units'];
					else
						$i['units'] = 0;					
					
					$this->db->insert("adrev_ads", $i);
					$this->output->redirect(lib_lang("Thank you. Your ad was created"), "index.php?section=ads");
				}
				else
				{	
					$i['bid'] = $f['bid'];
					
					$chk = $this->db->getsql("SELECT b.rate, a.bid, b.ad_sort, keywords_enable
									FROM adrev_ads a, adrev_zones b
									WHERE a.userid=?
									AND a.zid = ? 
									AND a.zone=b.id", array($uid, $f['id']));
			          
					$check = $chk['0'];
			
			        if($check['ad_sort']=='bid' AND $check['keywords_enable']!=1)
					{
					  
					
					   if($i['bid'] < $zone['rate'])
					    {
					   $this->db->getsql("UPDATE adrev_ads SET status='3', bid=? WHERE zid=? AND userid=?", array($i['bid'], $f['id'], $uid));
					  
						 $this->output->redirect(lib_lang("Your ad was updated. You have bidded under the minimum bidding amount. This ad has been remove from the bidding structure."), "index.php?section=ads&action=edit&f[id]={$f['id']}");
						 exit;
					    }
					 
					}
					else
					$this->db->getsql("UPDATE adrev_ads SET status='1' WHERE zid=? AND userid=?", array($f['id'], $uid));
					$this->db->update("adrev_ads", "zid", $f['id'], $i);
					
					$this->output->redirect(lib_lang("Your ad was updated"), "index.php?section=ads&action=edit&f[id]={$f['id']}");
				}
				
				exit;
			}
		}
		
		// Grab the ad, if we had none
		if($f['id'] && !$f['data']['title'])
		{
			$zid = $f['id'];
			$d = $this->db->getsql("SELECT * FROM adrev_ads WHERE zid=?", array($zid));
			$f = $d['0'];
			$f['id'] = $zid;
			$f['data'] = $f;
		}

		if($f['zone'])
		{
			$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($f['zone']));
			$zone = $z['0'];
			$format = @unserialize($z['0']['ad_format']);
		}
					
		// Show the form
		$tpl = new XTemplate("templates/ads_create.html");

	
		if($f['zone'])
		{
			if(count($format) == 0)
			{
				$this->output->redirect(lib_lang("There was an error. This zone is not yet configured correctly. Please contact the administrator"), "index.php?section=ads", 1);
				exit;
			}

			if(!$f['id'])
			{
				$tpl->assign("ZONE", $f['zone']);
				$tpl->parse("main.zone");
			}
			
			// Show the appropriate fields
			$bytes = 16384;
			while(list($key, $val) = @each($format))
			{	
				if($val['type'] == "IMAGE")
				{
					$bytes = $val['max_length'];
					$html  = "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"{$val['max_length']}\"/>\n";
					$html .= "<input name=\"userfile\" type=\"file\" size=30/>\n";
					$html .= "<input type=\"hidden\" name=\"f[img_width]\" value=\"{$val['width']}\">\n";
					$html .= "<input type=\"hidden\" name=\"f[img_height]\" value=\"{$val['height']}\">\n";
					$notes  = lib_lang("The maximum size allowed for uploaded images is"). " " . number_format($bytes) .lib_lang("bytes") . ", ";
					$notes .= lib_lang("and the dimensions must be") ." {$val['width']} " . lib_lang("wide") . " x {$val['height']} " . lib_lang("high").".";
					
					$tpl->assign("NAME", "<b>" . str_replace(array("-", " "), "&nbsp;", $val['name']) . "</b>");
					$tpl->assign("FIELD", $html);
					$tpl->assign("NOTES", $notes);
					$tpl->assign("BGCOLOR", "#EEEEEE");
					$tpl->parse("main.fields.notes");
					$tpl->parse("main.fields");
					
					$value = iif(!$f['data'][$val['type']], $f['data'][strtolower($val['type'])], $f['data'][$val['type']]);
					$tpl->assign("NAME", "</b>or&nbsp;Image URL<b>");
					$tpl->assign("FIELD", "<input type=\"text\" name=\"f[data][{$val['type']}]\" value=\"$value\" size=40>");
					$tpl->parse("main.fields");
				} else {
					$value = iif(!$f['data'][$val['type']], $f['data'][strtolower($val['type'])], $f['data'][$val['type']]);
					$size  = iif($val['max_length'] > 50, 50, $val['max_length']);
					
					if ($val['type'] == 'URL') {
						$size = 60;
						$val['max_length'] = 245;
					}
					
					$tpl->assign("BGCOLOR", "#FFFFFF");
					$tpl->assign("NAME", "<b>" . str_replace(array("-", " "), "&nbsp;", $val['name']) . "</b>");
					if($val['max_length'] < 250) {
						$tpl->assign("FIELD", "<input type=\"text\" name=\"f[data][{$val['type']}]\" value=\"{$value}\" size=\"{$size}\" maxlength=\"{$val['max_length']}\">");
					} else {
						$tpl->assign("FIELD", "<textarea name=\"f[data][{$val['type']}]\" rows=10 cols=60 wrap=virtual>{$value}</textarea><br/><small>@@Paste HTML Ad Code in Here@@</small>");
					}
					
					$tpl->parse("main.fields");
				}
				
			}
	
			// Show the bidding portion
			if($f['id'] && $zone['rtype'] == 2 && $zone['keywords_enable'] == 0)
			{
				// Grab the bids for other ads in this zone
				$bids = $this->db->getsql("SELECT id, zid, bid FROM adrev_ads 
												WHERE zone=? AND status NOT IN (-2, -1, 3) ORDER BY bid DESC", array($zone['id']));
				
				if(count($bids) > 0)
				{
					$x = 0;
					foreach($bids as $d)
					{
						$x++;
						$posnum = iif($d['zid'] == $f['zid'], "<font color=red><b>{$d['bid']}</b></font>", $d['bid']);
						$tpl->assign("POS$x", $posnum);
					}
				}
				
				$tpl->assign("BID", number_format($f['bid'],2));
				$tpl->assign("MINBID", number_format($zone['rate'],2));
				$tpl->parse("main.bidding");
			}
	
			$tpl->assign("BUTTON", lib_lang("Save Ad"));
		}
		else
		{
			$types = array('CPC'=>lib_lang('CPC - Cost per Click'), 
								'CPM' =>lib_lang('CPM - Cost per 1,000 Impressions'), 
								'CPD'=>lib_lang('CPD - Cost per Day'));	

			$tpl = new XTemplate("templates/ads_zonelist.html");
			
			if($_SESSION['user']['admin'] == 3)
				$zlist = $this->db->getsql("SELECT * FROM adrev_zones WHERE status=1 OR status=2 ORDER BY name");
			elseif ($_SESSION['user']['admin'] == 2)
				$zlist = $this->db->getsql("SELECT * FROM adrev_zones WHERE status=1 AND (aff_percent > 0 OR length(pub_rates) > 5)");
			else
				$zlist = $this->db->getsql("SELECT * FROM adrev_zones WHERE status=1 AND pub_only=0 ORDER BY name");
				
			if(!$zlist['0']['id'])
			{
				$this->output->redirect(lib_lang("No zones have been setup as yet"), "index.php", 3);
				exit;
			}
			
			foreach($zlist as $rec)
			{
				$tpl->assign("ID", $rec['id']);
				$tpl->assign("NAME", stripslashes($rec['name']));
				$tpl->assign("RATE", number_format($rec['rate'],2));
				$tpl->assign("RTYPE", $types[$rec['rate_type']]);
				$tpl->parse("main.list");
			}
						
		}
		
		if($f['id'])
			$tpl->assign("ADS_MENU", $this->menu($_REQUEST['f']['id']));
	
		$tpl->assign("ERRORMSG", $errormsg);	
		$tpl->assign("ID", $f['id']);		
		$tpl->parse("main");
		
		if($f['id'])
			$this->title = lib_lang("Edit Ad");
		else
			$this->title = lib_lang("Create new Ad");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;		
	}

	// Manage Keywords and bids
	function keyword_stats()
	{
		$this->output->secure();	
		$f = $this->input->f;		
		$uid = $_SESSION['user']['id'];

			

		// Grab the ad info
		$ad = $this->db->getsql("SELECT * FROM adrev_ads WHERE zid=? AND userid=?", array($f['id'], $uid));
		$id = $ad['0']['id'];
		$zoneid = $ad['0']['zone'];
		$z = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($zoneid));
		$zone = $z['0'];
		
		// Lets update bids
		if(count($_REQUEST['bid']) > 0)
		{
			$minbid = $zone['rate'];
			while(list($xid,$rec) = each($_REQUEST['bid']))
			{
				if(!$this->default['adrevenue']['max_bid'])
					$this->default['adrevenue']['max_bid'] = 50;
					
				if($rec['bid'] >= $minbid && $rec['bid'] <= $this->default['adrevenue']['max_bid'])
				{
					$this->db->getsql("UPDATE adrev_keyword_map SET bid=?, url=? WHERE id=? AND adid=?", array($rec['bid'], $rec['url'], $xid, $id));
				}
				   
				
			}
			
			
		}

		// Delete a keyword
		if($_REQUEST['delete'])
		{
			$kid = $_GET['delete'];
			$this->db->getsql("DELETE FROM adrev_keyword_map WHERE adid=? AND keywordid=?", array($id, $kid));
		}
		
		$tpl = new XTemplate("templates/keywords.html");
		
		// Check if we can add more keywords
		if($zone['keywords_max'] > 0 && $f['keyword'])
		{
			$num = $this->db->getsql("SELECT count(*) as num FROM adrev_keyword_map WHERE adid=?", array($id));
			if($num['0']['num'] >= $zone['keywords_max'])
			{
				$this->output->redirect("@@You cannot add any more keywords@@", "?section=ads&action=keyword_stats&f[id]={$f['id']}", 3);
				exit;
			}
		}
		
		// Add a new keyword
		if($f['keyword'])
		{
			$keywordid = $this->db->get_keyword($f['keyword']);
			$exists = $this->db->getsql("SELECT id FROM adrev_keyword_map WHERE keywordid=? AND adid=?", array($keywordid, $id));
			if(!$exists['0']['id'])
			{
				$i = array();
				$i['zoneid'] = $zoneid;
				$i['adid'] = $id;
				$i['keywordid'] = $keywordid;
				$i['url'] = $f['url'];
				$i['bid'] = 0;
				if($zone['rtype'] == 2 && $zone['rate_type'] == "CPC")
					$i['bid'] = $zone['rate'];
				$this->db->insert("adrev_keyword_map", $i);
			}			
		}

		// Setup some defaults
		list($startdate, $enddate) = lib_date_range($f['period']);		
		$dates = array("today"=>lib_lang('Today'), "yesterday"=>lib_lang('Yesterday'), "thisweek"=>lib_lang('This Week'), 
						"lastweek"=>lib_lang('Last Week'), "thismonth"=>lib_lang('This Month'), 
						"lastmonth"=>lib_lang('Last Month'), all=>lib_lang('All Time'));
		
		// Show the keywords, bids and other things
		$existing = $this->db->getsql("SELECT b.keyword,a.* FROM adrev_keyword_map a, adrev_keywords b
										WHERE a.keywordid=b.id AND a.adid=?
										ORDER BY b.keyword", array($id));
		if(count($existing) > 0)
		{
			$gen = new formgen();
			$gen->startrow("#EEEEEE");
			$gen->column("<b>@@Keyword@@</b>");
			$gen->column("<b>@@URL@@</b>");
			if($zone['rtype'] == 2 && $zone['rate_type'] == "CPC")
			{
				$gen->column("<b>@@Bid@@</b>","",1,"","center","");
				$gen->column("1","",1,"","center","");
				$gen->column("2","",1,"","center","");
				$gen->column("3","",1,"","center","");
				$gen->column("4","",1,"","center","");
				$gen->column("5","",1,"","center","");
			}
			#column($content="&nbsp;", $bgcolor="", $width="", $valign="", $align="",$style="")
			$gen->column("<b>@@Clicks@@</b>","",1,"","right","");
			$gen->column("<b>@@Views@@</b>","",1,"","right","");
			$gen->column("<b>@@CTR%@@</b>","",1,"","right","");
			$gen->column("<b>@@Actions@@</b>","",1,"","right","");
			$gen->column("<b>@@ROI%@@</b>","",1,"","right","");
			$gen->column("<b>@@Spend@@</b>","",1,"","right","");
			$gen->column("");
			$gen->endrow();
			
			foreach($existing as $rec)
			{
				$bgcolor = $bgcolor == "#FFFFFF" ? "#FFFFEE" : "#FFFFFF";
				$gen->startrow($bgcolor);
				$gen->column("<a href=?section=ads&action=stats&f[id]={$f['id']}&f[kid]={$rec['keywordid']}>{$rec['keyword']}</a>");
				
				$rec['url'] = iif(!$rec['url'], $ad['0']['url'], $rec['url']);
				$gen->column("<input type=text name=\"bid[{$rec['id']}][url]\" value=\"{$rec['url']}\" size=30>","",1,"","center","");
				if($zone['rtype'] == 2 && $zone['rate_type'] == "CPC")
				{
					// Grab other keywords like this one
					$rates = $this->db->getsql("SELECT bid,adid FROM adrev_keyword_map 
												WHERE keywordid=? AND zoneid=?
												ORDER BY bid DESC LIMIT 5", array($rec['keywordid'], $zone['id']));
					$ranking = array(1=>0,2=>0,3=>0,4=>0,5=>0);
					if(count($rates) > 0)
					{
						$x = 0;
						foreach($rates as $r)
						{
							$x++;
							$ranking[$x] = array($r['adid'],$r['bid']);
						}
					}
					
					$bid = number_format($rec['bid'],2);
					$gen->column("<input type=text name=\"bid[{$rec['id']}][bid]\" value=\"{$bid}\" size=5>","",1,"","center","");
					foreach($ranking as $r)
					{
						if($id <> $r['0'])
							$gen->column(number_format($r['1'],2),"",1,"","right","");
						else
							$gen->column("<b>" . number_format($r['1'],2) . "</b>","",1,"","right","");
					}
				}	
				// Grab any stats for this keyword
				$uid = $_SESSION['user']['id'];
				$stats = $this->db->getsql("SELECT count(id) as num, sum(clicks) as clicks, 
												sum(impressions) as impressions, sum(orders) as orders,
												sum(amount) as spend
											FROM adrev_traffic
											WHERE adid=? AND userid=? AND keywordid=? AND date BETWEEN ? AND ?
											GROUP BY adid", array($id, $uid, $rec['keywordid'], $startdate, $enddate));					
					
				$gen->column(number_format($stats['0']['clicks']),"",1,"","right","");
				$gen->column(number_format($stats['0']['impressions']),"",1,"","right","");
				$gen->column(number_format(($stats['0']['clicks'] * 100)/iif(!$stats['0']['impressions'],1,$stats['0']['impressions']),2),"",1,"","right","");
				$gen->column(number_format($stats['0']['orders']),"",1,"","right","");
				$gen->column(number_format(($stats['0']['orders'] * 100)/iif(!$stats['0']['clicks'],1,$stats['0']['clicks']),2),"",1,"","right","");
				$gen->column(number_format($stats['0']['spend'],2),"",1,"","right","");
				$gen->column("<A href=\"?section=ads&action=keyword_stats&f[id]={$f['id']}&delete={$rec['keywordid']}\">@@delete@@</a>","",1,"","center","");
					
				$t_impressions += $stats['0']['impressions'];
				$t_clicks += $stats['0']['clicks'];
				$t_orders += $stats['0']['orders'];
				$t_spend  += $stats['0']['spend'];								
			}
			
			$gen->startrow("#EEEEEE");
			$gen->column("","",1,"","right","",2);

			if($zone['rtype'] == 2 && $zone['rate_type'] == "CPC")
			{
				$gen->column("<input type=submit name=f[bidchange] value=\"@@Bid@@\">","",1,"","center");
				$gen->column("","",1,"","right","",5);
			}
		
			$gen->column("<b>" . number_format($t_clicks) . "</b>","",1,"","right","");
			$gen->column("<b>" . number_format($t_impressions) . "</b>","",1,"","right","");
			$gen->column("<b>" . number_format(($t_clicks * 100)/iif(!$t_impressions,1,$t_impressions),2) . "</b>","",1,"","right","");
			$gen->column("<b>" . number_format($t_orders) . "</b>","",1,"","right","");
			$gen->column("<b>" . number_format(($t_orders * 100)/iif(!$t_clicks,1,$t_clicks),2) . "</b>","",1,"","right","");
			$gen->column("<b>" . number_format($t_spend,2) . "</b>","",1,"","right","");
			$gen->column("","","",1,"","center","");
			$gen->endrow();			
			
			$tpl->assign("STATS", $gen->gentable("100%", 0, 1, 3, "#CCCCCC"));
		}

		
		// Show the top part
		$tpl->assign("PERIOD", date("D M d, Y", strtotime($startdate)) . " - " . date("D M d, Y", strtotime($enddate)));
		$tpl->assign("ID", $f['id']);
		$tpl->assign("KID", $f['kid']);
		$tpl->assign("PERIODLIST", lib_htlist_array($dates, $f['period']));	
		$tpl->assign("ADS_MENU", $this->menu($_REQUEST['f']['id']));
		
		// See if we have pre-defined keywords
		if(trim($zone['keywords']))
		{
			$exist = array();
			if(count($existing) > 0)
			{
				foreach($existing as $rec)
					$exist[$rec['keyword']] = 1;
			}
			
			// We have predefined keywords
			reset($existing);
			$keywords = preg_split('/[\n,]/', $zone['keywords'], -1, PREG_SPLIT_NO_EMPTY);
			$k = array();
			if(count($keywords) > 0)
			{
				foreach($keywords as $keyword)
				{
					$keyword = trim(strtolower($keyword));
					if(!$exist[$keyword])
						$k[$keyword] = $keyword;
				}
			}
			$options = lib_htlist_array($k);
			$field = "<select name=f[keyword]>$options</select>";
		}
		else
		{
			// There are no pre-defined keywords
			$field = "<input type=text name=f[keyword] size=20>";
		}
		
		$tpl->assign("KEYWORD", $field);
		
		$tpl->parse("main");
		$this->title = lib_lang("Manage Keywords");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;		
	}
	
	// Manage Keywords && bids
	function stats()
	{
		$this->output->secure();	
		$f = $this->input->f;		
		$uid = $_SESSION['user']['id'];
		
		$tpl = new XTemplate("templates/ads_keywords.html");
		
		// Grab the ad info
		$ad = $this->db->getsql("SELECT * FROM adrev_ads WHERE zid=?", array($f['id']));
		$id = $ad['0']['id'];
		$zoneid = $ad['0']['zone'];
		
		// Grab the zone information
		$zone = $this->db->getsql("SELECT * FROM adrev_zones WHERE id=?", array($zoneid));

		// Setup some defaults
		list($startdate, $enddate) = lib_date_range($f['period']);		
		$dates = array("today"=>lib_lang('Today'), "yesterday"=>lib_lang('Yesterday'), "thisweek"=>lib_lang('This Week'), 
						"lastweek"=>lib_lang('Last Week'), "thismonth"=>lib_lang('This Month'), 
						"lastmonth"=>lib_lang('Last Month'), all=>lib_lang('All Time'));

		// Show the stats for this word (up to 100 days worth)
		if(!$f['kid'])
			$f['kid'] = 0;

		$uid = $_SESSION['user']['id'];
		$stats = $this->db->getsql("SELECT * FROM adrev_traffic 
										WHERE adid=? AND keywordid=? 
										AND date BETWEEN ? AND ?
										ORDER BY date DESC
										LIMIT 100", array($id, $f['kid'], $startdate, $enddate));
		if(count($stats) > 0)
		{
			foreach($stats as $rec)
			{
				$bgcolor = iif($bgcolor == "#FFFFFF", "#FFFFEE", "#FFFFFF");
				$tpl->assign("BGCOLOR", $bgcolor);
				$tpl->assign("DATE", date("M d Y", strtotime($rec['date'])));
				$tpl->assign("CLICKS", number_format($rec['clicks']));
				$tpl->assign("VIEWS", number_format($rec['impressions']));
				$tpl->assign("ORDERS", number_format($rec['orders']));
				$tpl->assign("SPEND", number_format($rec['amount'],2));
				$tpl->assign("CTR", number_format(($rec['clicks'] * 100)/iif(!$rec['impressions'],1,$rec['impressions']),2));
				$tpl->parse("main.list");

				$t_impressions += $rec['impressions'];
				$t_clicks += $rec['clicks'];
				$t_orders += $rec['orders'];
				$t_spend  += $rec['amount'];
			}

			$tpl->assign("TVIEWS", number_format($t_impressions));
			$tpl->assign("TCLICKS", number_format($t_clicks));
			$tpl->assign("TCTR", number_format(($t_clicks * 100)/iif(!$t_impressions,1,$t_impressions),2));
			$tpl->assign("TORDERS", number_format($t_orders));
			$tpl->assign("TSPEND", number_format($t_spend,2));
			$tpl->parse("main.totals");
		}

		// Show the keyword list
		if($f['kid'])
		{
			$keywords = $this->db->getsql("SELECT b.id,b.keyword FROM adrev_keyword_map a, adrev_keywords b
											WHERE a.keywordid=b.id AND a.zoneid=? AND a.adid=?
											ORDER BY b.keyword", array($zoneid, $id));
			$klist = array();
			if(count($keywords) > 0)
			{
				foreach($keywords as $rec)
					$klist[$rec['id']] = $rec['keyword'];
					
				$tpl->assign("KEYWORDLIST", lib_htlist_array($klist, $f['kid']));
			}
		}
		
		$tpl->assign("PERIOD", date("D M d, Y", strtotime($startdate)) . " - " . date("D M d, Y", strtotime($enddate)));
		$tpl->assign("ID", $f['id']);
		$tpl->assign("KID", $f['kid']);
		$tpl->assign("PERIODLIST", lib_htlist_array($dates, $f['period']));	
		$tpl->assign("ADS_MENU", $this->menu($_REQUEST['f']['id']));
		$tpl->parse("main");
		$this->title = lib_lang("View Ad Stats");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;			
	}

	// Preview the ad in question
	function preview()
	{
		$this->output->secure();	
		$f = $this->input->f;		
		$uid = $_SESSION['user']['id'];

		include_once("modules/preview.php");
		$p = new preview();
		$p->zid= $f['id'];
		
		$tpl = new XTemplate("templates/ads_preview.html");
		$tpl->assign("PREVIEW", $p->display());
		$tpl->assign("ADS_MENU", $this->menu($_REQUEST['f']['id']));
		$tpl->assign('URL', trim($this->default['adrevenue']['hostname'],"/") . "/index.php?section=redir&zid={$p->zid}");
		$tpl->parse("main");

		$this->title = lib_lang("Preview Ad");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;			
	}

	// Schedule an ad
	function schedule()
	{
		$this->output->secure();	
		$f = $this->input->f;		
		$uid = $_SESSION['user']['id'];
		$tpl = new XTemplate("templates/ad_daypart.html");
		
		if($_SESSION['user']['admin'] == 3)
		{
			$xad = $this->db->getsql("SELECT userid FROM adrev_ads WHERE zid=?", array($f['id']));
			$uid = $xad['0']['userid'];
		}
		
		// Loadup the ad
		$ad = $this->db->getsql("SELECT a.*,b.rate_type FROM adrev_ads a, adrev_zones b
									WHERE a.zid=? AND a.userid=? AND a.zone=b.id", array($f['id'], $uid));
		
		// Update the ad
		if($f['submit'] && $ad['0']['id'])
		{
			// Compute the days
			$f['startdate'] = 0;
			$f['expires'] = 0;
			if($f['start_month'] && $f['start_day'] && $f['start_year'])
				$f['startdate'] = strtotime("{$f['start_month']}/{$f['start_day']}/{$f['start_year']} 00:00:01");
			if($f['expire_month'] && $f['expire_day'] && $f['expire_year'])
				$f['expires'] = strtotime("{$f['expire_month']}/{$f['expire_day']}/{$f['expire_year']} 23:59:59");			
			
			$i = array();
			$i['daypart_days'] = lib_options($f['daypart_days']);
			$i['daypart_hours'] = lib_options($f['daypart_hours']);
			$i['startdate'] = $f['startdate'];
			$i['expires'] = $f['expires'];
			$i['units'] = $f['units'];
			$i['spend_limit'] = $f['spend_limit'];
			$this->db->update("adrev_ads", "zid", $f['id'], $i);
			
			$this->output->redirect(lib_lang("Daypart options were updated"), "index.php?section=ads&action=schedule&f[id]={$f['id']}",1);
			exit;			
		}

		// Show the form		
		$tpl = new XTemplate("templates/ad_daypart.html");
	
		// Days
		$f['daypart_days'] = lib_bit_options($ad['0']['daypart_days']);
		for($day =0; $day < 7; $day++)
		{
			$tpl->assign("DAY", $day);
			if(in_array($day, $f['daypart_days']))
				$tpl->assign("DAYPART_DAY", "CHECKED");
			else
				$tpl->assign("DAYPART_DAY", "");
				
			$tpl->parse("main.days");
		}
		
		// Hours
		$f['daypart_hours'] = lib_bit_options($ad['0']['daypart_hours']);
		for($hour =0; $hour < 24; $hour++)
		{
			$tpl->assign("HOUR_TITLE", $hour);
			$tpl->parse("main.hour_title");
			
			$tpl->assign("HOUR", $hour);
			if(in_array($hour, $f['daypart_hours']))
				$tpl->assign("DAYPART_HOUR", "CHECKED");
			else
				$tpl->assign("DAYPART_HOUR", "");
			$tpl->parse("main.hours");
		}
		
		$tpl->assign("ID", $f['id']);
		$tpl->assign("TZ", date("T"));
		
		$tpl->assign("STARTDATE", lib_dateinput("start",$ad['0']['startdate'], date("Y"), 4));
		$tpl->assign("ENDDATE", lib_dateinput("expire",$ad['0']['expires'], date("Y"), 4));
		$tpl->assign("UNITS", $ad['0']['units']);
		$tpl->assign("TOTAL_UNITS", $ad['0']['total_units']);
		$tpl->assign('SPEND_LIMIT', $ad['0']['spend_limit']);
		
		if($ad['0']['rate_type'] == "CPC")
			$tpl->assign("SUNITS", "@@Clicks@@");
		elseif($ad['0']['rate_type'] == "CPM")
			$tpl->assign("SUNITS", "@@Impressions@@");
		elseif($ad['0']['rate_type'] == "CPD")
			$tpl->assign("SUNITS", "@@Days@@");
		elseif($ad['0']['rate_type'] == "CPA")
			$tpl->assign("SUNITS", "@@Orders@@");
		
		$tpl->assign("ADS_MENU", $this->menu($_REQUEST['f']['id']));
		$tpl->parse("main");
		$this->title = lib_lang("Scheduling");
		$this->content = $tpl->text("main");
		$this->display();
		$this->printpage();		
		exit;
	}

	// Get the ads menu
	function menu($id="")
	{
		$f = $this->input->f;
		
		// Grab the zone info
		$info = $this->db->getsql("SELECT a.title, a.status as ad_status, a.zid, b.* FROM adrev_ads a, adrev_zones b
									WHERE a.zone=b.id AND a.zid=?", array($f['id']));
									
		$tpl = new XTemplate("templates/ads_menu.html");
		if($info['0']['keywords_enable'] == 1)
		{
			$tpl->assign("ACTION", "keyword_stats");
			$tpl->assign("ACTION_NAME", "Keywords");
		}
		else
		{
			$tpl->assign("ACTION", "stats");
			$tpl->assign("ACTION_NAME", "Statistics");
		}

		// Turn on the scheduling option
		if($info['0']['daypart_enable'] == 0 && $info['0']['units'] == 0)
		{
			$tpl->assign("ID", $id);
			$tpl->parse("main.daypart");
		}
		
		$tpl->assign('XACTION_NAME', ucwords(str_replace('_', ' ', $_REQUEST['action'])));
		$tpl->assign("STATUS", $this->default['status'][$info['0']['ad_status']]);
		$tpl->assign("TITLE", stripslashes($info['0']['title']));
		$tpl->assign("ID", $id);
		$tpl->parse("main");
		return($tpl->text("main"));
	}
	
}
?>
