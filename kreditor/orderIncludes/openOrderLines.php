<?php
for ($x=1; $x<=$linjeantal; $x++)	{
	if ($varenr[$x]) {
		$ialt=($pris[$x]-($pris[$x]/100*$rabat[$x]))*$antal[$x];
		$ialt=afrund($ialt,2);
		$sum=$sum+$ialt;
		if ($momsfri[$x]!='on' && !$omvbet[$x]) $momssum=$momssum+$ialt;
		#$ialt=dkdecimal($ialt,2);
		$dkpris=dkdecimal($pris[$x],2);
		$dkrabat=dkdecimal($rabat[$x],2);
		if ($antal[$x]) {
			if ($art=='KK') $dkantal[$x]=dkdecimal($antal[$x]*-1,2); 
			else $dkantal[$x]=dkdecimal($antal[$x],2);
			if (substr($dkantal[$x],-1)=='0') $dkantal[$x]=substr($dkantal[$x],0,-1);
			if (substr($dkantal[$x],-1)=='0') $dkantal[$x]=substr($dkantal[$x],0,-2);
		}
	}
	else {$dkantal[$x]=''; $dkpris=''; $dkrabat=''; $ialt='';}
	print "<input type='hidden' name='linje_id[$x]' value='$linje_id[$x]'>";
	print "<input type='hidden' name='vare_id[$x]' value='$vare_id[$x]'>";
	print "<input type='hidden' name='kred_linje_id[$x]' value='$kred_linje_id[$x]'>";
	print "<input type='hidden' name='serienr[$x]' value='$serienr[$x]'>";
	print "<input type='hidden' name='omvbet[$x]' value='$omvbet[$x]'>";
	print "<tr>";
	print "<td><input class='inputbox' type='text' style='text-align:right' size=3 name=posn$x value='$x' onchange='javascript:docChange = true;'></td>";
	print "<td title='".findtekst(1513, $sprog_id)."'><input class='inputbox' type='text' style='background: none repeat scroll 0 0 #e4e4ee' readonly=readonly size=7 name=vare$x onfocus='document.forms[0].fokus.value=this.name;' value='".htmlentities($varenr[$x])."'></td>"; #20180305
	print "<td><input class='inputbox' type='text' size=7 name=lev_varenr$x value=\"".htmlentities($lev_varenr[$x])."\" ";
	print "onchange='javascript:docChange = true;'></td>";
	print "<td><input class='inputbox' type='text' style='text-align:right' size=4 name=anta$x value='$dkantal[$x]' "; print "onchange='javascript:docChange = true;'></td>";
	print "<td><input class='inputbox' type='text' style='background: none repeat scroll 0 0 #e4e4ee' readonly=readonly size=3 value='$enhed[$x]'></td>";
	print "<td><input class='inputbox' type='text' size=58 name=beskrivelse$x value= \"".htmlentities($beskrivelse[$x])."\" onchange='javascript:docChange = true;'></td>";
	print "<td><input class='inputbox' type='text' style='text-align:right' size=10 name=pris$x value='$dkpris' onchange='javascript:docChange = true;'></td>";
	print "<td><input class='inputbox' type='text' style='text-align:right' size=4 name=raba$x value='$dkrabat' onchange='javascript:docChange = true;'></td>";
	if ($art=='KK') $ialt=$ialt*-1;
	if ($varenr[$x]) $tmp=dkdecimal($ialt,2);
	else $tmp=NULL;
	print "<td align=right><input class='inputbox' type='text' style='background: none repeat scroll 0 0 #e4e4ee;text-align:right' readonly='readonly' size=10 value='$tmp'></td>";
	if ($vis_projekt && !$projekt[0]) {
print "<td><select class='inputbox' NAME=projekt[$x]>";
for ($a=0; $a<=$prj_antal; $a++) {
	if ($projekt[$x]!=$list[$a]) print "<option  value='$list[$a]' title='$beskriv[$a]'>$list[$a]</option>";
	else print "<option value='$list[$a]' title='$beskriv[$a]' selected='selected'>$list[$a]</option>";
}
print "</option></td>";
}
if ($status>=1) {
	if ($vare_id[$x]) {
		$r = db_fetch_array(db_select("select id, varenr, gruppe from varer where id = '$vare_id[$x]'",__FILE__ . " linje " . __LINE__));
		if ($r['id'] && !$r['gruppe']) { # 20211201 
		alert("Vare med varenummer $varenr[$x] er ikke tilknyttet en varegruppe (Pos nr. $x)");
	} else {
		$row = db_fetch_array(db_select("select box9 from grupper where kodenr = '$row[gruppe]' and art = 'VG'",__FILE__ . " linje " . __LINE__));
		$box9[$x] = trim($row['box9']);
		$tidl_lev[$x]=0;
	}
	if ($art=='KK') {
		$dklev[$x]=dkdecimal($leveres[$x]*-1,2);
		$modtag_returner="returner";
	} else {
		$dklev[$x]=dkdecimal($leveres[$x],2);
		$modtag_returner="modtag";
	}
	if (substr($dklev[$x],-1)=='0') $dklev[$x]=substr($dklev[$x],0,-1);
	if (substr($dklev[$x],-1)=='0') $dklev[$x]=substr($dklev[$x],0,-2);

	if (($antal[$x]>=0)&&($art!='KK')) {
		$qtxt = "select * from batch_kob where linje_id = '$linje_id[$x]' and ordre_id=$id and vare_id = $vare_id[$x]";
		$query = db_select($qtxt,__FILE__ . " linje " . __LINE__);
		while($row = db_fetch_array($query)) $tidl_lev[$x]=$tidl_lev[$x]+$row['antal'];
		if (afrund($antal[$x]-$tidl_lev[$x],2)) $status=1;
		$temp=0;
		$query = db_select("select * from reservation where linje_id = $linje_id[$x] and batch_salg_id=0",__FILE__ . " linje " . __LINE__);
		if ($row = db_fetch_array($query)) {
			if ($antal[$x]-$tidl_lev[$x]!=$row['antal']) {
				$qtxt = "update reservation set antal=$antal[$x]-$tidl_lev[$x] where linje_id=$linje_id[$x] and batch_salg_id=0";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
		} elseif ($antal[$x]-$tidl_lev[$x]!=$row['antal']) {
			if (($antal[$x]>=0)&&($tidl_lev[$x]<0)) {
				$txt = "Antal m&aring; ikke &aelig;ndres til positivt tal, n&aring;r der er returneret varer (Pos nr. $posnr[$x])";
				print "<BODY onLoad='javascript:alert($txt)'>";
				$antal[$x]=$tidl_lev[$x];
			} else {
				$qtxt = "insert into reservation (linje_id, vare_id, batch_salg_id, antal) values	";
				$qtxt.= "($linje_id[$x], $vare_id[$x], 0, $antal[$x]-$tidl_lev[$x])";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
		}
	}
	if ($antal[$x]<0) {
		$tidl_lev[$x]=0;
		$query = db_select("select antal from batch_kob where linje_id = '$linje_id[$x]'",__FILE__ . " linje " . __LINE__);
		while ($row = db_fetch_array($query)) {
			if ($art=='KK') $tidl_lev[$x] = $tidl_lev[$x] - $row['antal'];
			else $tidl_lev[$x] = $tidl_lev[$x] + $row['antal'];
	 	}
	}
	$dk_tidl_lev[$x] = dkdecimal($tidl_lev[$x],2);
	if (substr($dk_tidl_lev[$x],-1)=='0') $dk_tidl_lev[$x]=substr($dk_tidl_lev[$x],0,-1);
	if (substr($dk_tidl_lev[$x],-1)=='0') $dk_tidl_lev[$x]=substr($dk_tidl_lev[$x],0,-2);
	if (afrund(abs($antal[$x])-abs($tidl_lev[$x]),3)!=0) {
		if (abs($antal[$x])!=abs($leveres[$x])) {
			print "<td title='".findtekst(1514, $sprog_id)." ".$modtag_returner."e ".findtekst(1073, $sprog_id).".'>";
			print "<input class='inputbox' type='text' style='background: none repeat scroll 0 0 #ffa; text-align:right' ";
			print "size='4' name='leve$x' value='$dklev[$x]' onchange='javascript:docChange = true;'></td>\n";
		} else {
			print "<td title='".findtekst(424, $sprog_id)." ".$modtag_returner."et endnu.'>";
			print "<input class='inputbox' type='text' style='text-align:right' size='4' name='leve$x' value='$dklev[$x]' ";
			print "onchange='javascript:docChange = true;'></td>\n";
		}
	} else {
		print "<td title='Alt ".$modtag_returner."et.'>";
		print "<input class='inputbox' type='text' readonly='readonly' style='background: none repeat scroll 0 0 #e4e4ee; ";
		print "text-align:right' size='4' name='leve$x' value='$dklev[$x]' onchange='javascript:docChange = true;'></td>\n";
	}
	print "<td>($dk_tidl_lev[$x])</td>";
}
	}
	if ($labelprint) {
if ($varenr[$x]) {
	$txt = "<a href='../lager/labelprint.php?id=$vare_id[$x]&beskrivelse=".urlencode($beskrivelse[$x]);
	$txt.= "&stregkode=".urlencode($varenr[$x])."&pris=$salgspris[$x]&enhed=$enhed[$x]' target='blank'>";
	$txt.= "<img src='../ikoner/print.png' style='border: 0px solid;'></a>";
} else $txt=NULL;
print "<td>$txt</td>";
	}
	if (($status>0)&&($serienr[$x])) {
$txt = "<input type=button value='Serienr.' name='vis_snr$x' onchange='javascript:docChange = true;'>";
print "<td onClick='serienummer($linje_id[$x])'>$txt</td>";
	}
	if ($antal[$x]<0 && $art!='KK' && $box9[$x]=='on') {
$txt = "<span title= '".findtekst(1496, $sprog_id)."'><img alt='".findtekst(1515, $sprog_id)."' src=../ikoner/serienr.png>";
print "<td align=center onClick='batch($linje_id[$x])'>$txt</td>";
	}
	if ($omlev) {
$txt = "<input class='inputbox' type='checkbox' style='background: none repeat scroll 0 0 #e4e4ee' ";
$txt.= "name='omvbet[$x]' onchange='javascript:docChange = true;' $omvbet[$x]>";
print "<td valign='top'>$txt</td>\n";
	}
	print "</tr>\n";
}
print "<tr>";
print "<td><input class='inputbox' type='text' style='text-align:right' size=3 name=posn0 value=$x></td>";
if ($art!='KK') {
	print "<td><input class='inputbox' type='text' size=7 name=vare0 onfocus='document.forms[0].fokus.value=this.name;'></td>";
	print "<td><input class='inputbox' type='text' size=7 name=lev_v0></td>";
	print "<td><input class='inputbox' type='text' style='text-align:right' size=4 name=anta0></td>";
	print "<td><input class='inputbox' type='text' style='background: none repeat scroll 0 0 #e4e4ee' readonly=readonly size=3></td>";
}
else {
	print "<td><input class='inputbox' type='text' style='background: none repeat scroll 0 0 #e4e4ee' readonly=readonly size=7></td>";
	print "<td><input class='inputbox' type='text' style='background: none repeat scroll 0 0 #e4e4ee' readonly=readonly size=7></td>";
	print "<td><input class='inputbox' type='text' style='background: none repeat scroll 0 0 #e4e4ee' readonly=readonly size=2></td>";
	print "<td><input class='inputbox' type='text' style='background: none repeat scroll 0 0 #e4e4ee' readonly=readonly size=3></td>";
}
if ($konto_id) print "<td><input class='inputbox' type='text' size=58 name=beskrivelse0 onfocus='document.forms[0].fokus.value=this.name;'></td>";
else print "<td><input class='inputbox' type='text' size=58 name=beskrivelse0 onfocus='document.forms[0].fokus.value=this.name;'></td>";
print "<td><input class='inputbox' type='text' style='text-align:right' size=10 name=pris0></td>";
print "<td><input class='inputbox' type='text' style='text-align:right' size=4 name=raba0></td>";
print "<td><input class='inputbox' type='text' style='background: none repeat scroll 0 0 #e4e4ee' readonly=readonly size=10></td>";
#if ($status==1) {print "<td><input class='inputbox' type='text' style='text-align:right' size=2 name=modt0></td>";}
print "</tr>\n";
print "<input type='hidden' name='sum' value='$sum'>";
$moms=$momssum/100*$momssats;
if ($art=='KK') $moms=$moms-0.0001; #Ellers runder den op istedet for ned?
else $moms=$moms+0.0001; #Ellers runder den ned istedet for op?
$moms=afrund($moms,3);
if ($id) db_modify("update ordrer set sum='$sum', moms='$moms' where id='$id'",__FILE__ . " linje " . __LINE__);
if ($art=='KK') {
	$sum=$sum*-1;
	$moms=$moms*-1;
}
$ialt=$sum+$moms;
#$sum=dkdecimal($sum,2);
#$moms=dkdecimal($moms,2);
print "<tr><td colspan='9'><table border='1' bordercolor='#ffffff' cellspacing='0' cellpadding='0' width='100%'><tbody>";
print "<tr>";
print "<td align=center>".findtekst(887,$sprog_id)."</td><td align=center>".dkdecimal($sum,2)."</td>";
print "<td align=center>".findtekst(770,$sprog_id)."</td><td align=center>".dkdecimal($moms,2)."</td>";
print "<td align=center>I alt</td><td align=right>".dkdecimal($ialt,2)."</td>";

print "</tbody></table></td></tr>\n";
print "<input type='hidden' name='fokus'>";
print "<tr><td align=center colspan='10' border = '1'>";
print "<table width='100%' border='0' cellspacing='0' cellpadding='1'><tbody><tr>";
print "<td align='center'><input type='submit' style = 'width:120px;' accesskey='g' ";
print "value='Gem' name='save' onclick='javascript:docChange = false;'></td>";
print "<td align='center'><input type=submit style = 'width:120px;' accesskey='o' ";
print "value='Opslag' name='lookup' onclick='javascript:docChange = false;'></td>";
if ($art=='KK') {
	print "<td align='center'>";
	print "<!--Returnér --><input type='submit' style = 'width:120px;' accesskey='m' ";
	print "value='".findtekst(937, $sprog_id)."' ";
	print "name='return' onclick='javascript:docChange = false;'></td>";
} else {
	print "<td align=center><input type=submit style = 'width:120px;'"; 
	print "accesskey='m' value='".findtekst(1485, $sprog_id)."' ";
	print "name='receive' onclick='javascript:docChange = false;'></td>";
}
if ($status > 1 && $bogfor==1){
	print "<td align=center><input type=submit style = 'width:120px;'accesskey='b' value='".findtekst(1065, $sprog_id)."' ";
	print "name='postNow' onclick='javascript:docChange = false;'></td>";
}
if (count($posnr) == 0 && $id) {
	print "<td align=center><input type='submit' style = 'width:120px;' value='".findtekst(1099, $sprog_id)."' ";
	print "name='delete' onclick='javascript:docChange = false;'></td>";
}	elseif ($id && $art=='KO') {
	print "<td align=center><span title='".findtekst(1506, $sprog_id)."'>";
	print "<input type = 'submit' style = 'width:120px;' value='".findtekst(880, $sprog_id)."' ";
	print "name='print' onclick='javascript:docChange = false;'></span></td>";
	print "<td align=center><span title='".findtekst(1960, $sprog_id)."'>";
	print "<input type=submit style = 'width:120px;' value='".findtekst(1959, $sprog_id)."' ";
	print "name='split' onclick='javascript:docChange = false;'>";
	print "</span></td>";
}
print "<td align=center><span title='".findtekst(1516, $sprog_id)."'>";
print "<input type=submit style = 'width:120px;' value='CSV' name='csv' ";
print "onClick=\"javascript:ordre2csv=window.open('ordre2csv.php?id=$ordre_id','ordre2csv','scrollbars=1,resizable=1')\">";
print "</span></td>";
if ($konto_id) $r=db_fetch_array(db_select("select kreditmax from adresser where id = '$konto_id'",__FILE__ . " linje " . __LINE__));
if ($kreditmax=$r['kreditmax']*1) {
	if ($valutakurs) $kreditmax=$kreditmax*100/$valutakurs;
	$q=db_select("select * from openpost where konto_id = '$konto_id' and udlignet='0'",__FILE__ . " linje " . __LINE__);
	$tilgode=0;
	while($r=db_fetch_array($q)) {
if (!$r['valuta']) $r['valuta']='DKK';
if (!$r['valutakurs']) $r['valutakurs']=100;
if ($valuta=='DKK' && $r['valuta']!='DKK') $opp_amount=$r['amount']*$r['valutakurs']/100;
elseif ($valuta!='DKK' && $r['valuta']=='DKK') {
	$qtxt = "select kurs from grupper, valuta where grupper.art='VK' and grupper.box1='$valuta' and ";
	$qtxt.= "valuta.gruppe = ".nr_cast("grupper.kodenr")." and valuta.valdate <= '$r[transdate]' order by valuta.valdate desc";
	if ($r3=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
$opp_amount=$r['amount']*100/$r3['kurs'];
	} else alert("Ingen valutakurs for faktura $r[faktnr]");
}
elseif ($valuta!='DKK' && $r['valuta']!='DKK' && $r['valuta']!=$valuta) {
	$tmp==$r['amount']*$r['valuta']/100;
 	$opp_amount=$tmp*100/$r['valutakurs'];
}	else $opp_amount=$r['amount'];
$tilgode=$tilgode+$opp_amount;
	}
	if ($kreditmax<$ialt+$tilgode) {
$tmp=	dkdecimal(($ialt+$tilgode)-$kreditmax,2);
alert("Kreditmax overskrides med $valuta $tmp");
	}
}# end  if ($kreditmax....

?>