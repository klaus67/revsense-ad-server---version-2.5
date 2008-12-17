<?= $s->zone_menu($s->input->f['id']) ?>
<br/>
<h1>Setup Publisher Rates [<?= $s->z[0]['name'] ?>]</h1>
<p>@@You can setup tiered publisher rates. Enter a Minimum and Maximum currency amount, and the percentage Rate to pay.@@<br/>@@Leaving this area blank will turn off the tiered system for this zone.@@</p> 

<form method="GET">
<table width="380" border="1" cellspacing="0" cellpadding="4">
<tr bgcolor="#CCCCCC">
	<td align="center"><strong>@@Min@@</strong></td>
	<td align="center"><strong>@@Max@@</strong></td>
	<td align="center"><strong>@@Rate@@&nbsp;%</strong></td>
	<td width="80%">&nbsp;</td>
</tr>
<? if (count($s->pr) > 0) { $x=0; ?>
<? foreach ($s->pr as $rec) { $x+= 1 ?>
<tr>
	<td align="right"><? echo number_format($rec[0],2) ?></td>
	<td align="right"><? echo number_format($rec[1],2) ?></td>
	<td align="right"><? echo number_format($rec[2],1) ?>%</td>
	<td align="center"><a href="?section=zone&action=rates&f[id]=<?= $s->input->f['id'] ?>&destroy=<? echo $x ?>"><img src="images/stock_delete-16.png" border="0" alt="Delete" /></a></td>
</tr>
<? } ?>
<? } ?>
<tr>
	<td><input type="text" name="min" size="10" value="0" /></td>
	<td><input type="text" name="max" size="10" value="0" /></td>
	<td><input type="text" name="rate" size="10" value="0" /></td>
	<td width="80%"><input type="submit" value="@@Add@@" /></td>
</tr>
</table>

<input type="hidden" name="section" value="zone" />
<input type="hidden" name="action" value="rates" />
<input type="hidden" name="f[id]" value="<?= $s->input->f['id'] ?>" />
</form>
