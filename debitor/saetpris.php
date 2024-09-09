<?php
// --- debitor/saetpris.php ---patch 4.1.1 ----2024-08-15----
// LICENSE
//
// This program is free software. You can redistribute it and / or
// modify it under the terms of the GNU General Public License (GPL)
// which is published by The Free Software Foundation; either in version 2
// of this license or later version of your choice.
// However, respect the following:
// 
// It is forbidden to use this program in competition with Saldi.DK ApS
// or other proprietor of the program without prior written agreement.
//
// The program is published with the hope that it will be beneficial,
// but WITHOUT ANY KIND OF CLAIM OR WARRANTY.
// See GNU General Public License for more details.
// http://www.saldi.dk/dok/GNU_GPL_v2.html
//
// Copyright (c) 2003-2024 Saldi.dk ApS
// -----------------------------------------------------------------------
// 2015.03.04	PHR Tilføjet mulighed for at slette et sæt
// 2021.05.01 PHR Added  and box8!='0' as it is now set to 0 where updating 'varegrupper'
// 20220831 MSC - Implementing new design
// 20230829 MSC - Copy pasted new design into code
// 20231002 MSC - Copy pasted new design into code
// 20240815	PHR	Translations

@session_start();
$s_id=session_id();
$css="../css/standard.css";

$title="txt2115";

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/ordrefunc.php");

global $menu;

$id=if_isset($_GET['id']);
$saet=if_isset($_GET['saet']);
$forfra=if_isset($_GET['forfra']);

$r=db_fetch_array(db_select("select art,momssats from ordrer where id='$id'",__FILE__ . " linje " . __LINE__));
$art=$r['art'];
$momssats=$r['momssats'];

$r=db_fetch_array(db_SELECT("select box1 from grupper where art = 'DIV' and kodenr = '3'",__FILE__ . " linje " . __LINE__));
$incl_moms=$r['box1'];
$qtxt = "select box8 from grupper where art = 'DIV' and kodenr = '5' and box8!='' and box8!='0'"; 
if($r=db_fetch_array($q = db_SELECT($qtxt,__FILE__ . " linje " . __LINE__))) {
 $svid=$r['box8']*1;
	$r=db_fetch_array(db_SELECT("select varenr from varer where id = '$svid'",__FILE__ . " linje " . __LINE__));
	$svnr=$r['varenr'];
} else $svnr='S';
$r=db_fetch_array(db_SELECT("select id from varer where varenr = '$svnr'",__FILE__ . " linje " . __LINE__));
if (!$r['id']) print "<BODY onload=\"JavaScript:alert('Varenummer $svnr for sæt ikke gyldigt')\">";

if ($id && $forfra && $saet) {
	db_modify("delete from ordrelinjer where ordre_id='$id' and saet='$saet' and varenr='$svnr'",__FILE__ . " linje " . __LINE__);
	db_modify("update ordrelinjer set saet='0',rabat='0' where ordre_id='$id' and saet='$saet'",__FILE__ . " linje " . __LINE__);
} elseif ($slet=$_POST['slet']) {
	db_modify("delete from ordrelinjer where ordre_id='$id' and saet='$saet'",__FILE__ . " linje " . __LINE__);
	db_modify("update ordrelinjer set saet=saet-1 where ordre_id='$id' and saet > $saet",__FILE__ . " linje " . __LINE__);

}

if ($menu=='T') {
	include_once '../includes/top_header.php';
	include_once '../includes/top_menu.php';
	($art=='PO')?$href="pos_ordre.php?id=$id":$href="ordre.php?id=$id";
	print "<div id=\"header\">"; 
	print "<div class=\"headerbtnLft headLink\"><a href=$href accesskey=L title='Klik her for at komme tilbage'><i class='fa fa-close fa-lg'></i> &nbsp;".findtekst(30,$sprog_id)."</a></div>";     
	print "<div class=\"headerTxt\">$title</div>";     
	print "<div class=\"headerbtnRght headLink\">&nbsp;&nbsp;&nbsp;</div>";     
	print "</div>";
	print "<div class='content-noside'><center>";
} else {
print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\"><tbody>";
}

if ($saet=$_POST['saetvalg']) {
	if ($saet=='nyt_saet') {
		$r=db_fetch_array(db_select("select max(saet) as saet from ordrelinjer where ordre_id='$id'",__FILE__ . " linje " . __LINE__));
		$saet=$r['saet']+1;
#		$r=db_fetch_array(db_select("select * from varer where varenr='$svnr'",__FILE__ . " linje " . __LINE__));
#		opret_ordrelinje($id,$r['id'],$r['varenr'],1,$r['beskrivelse'],0,0,100,'$art','','','0','on','','','',$saet);
#		db_modify("update ordrelinjer set samlevare='on' where ordre_id='$id' and saet='$saet' and varenr='$svnr'",__FILE__ . " linje " . __LINE__);
	}
} elseif ($linje_id=$_POST['linje_id']) {
	$saet=$_POST['saet'];
	$medtag=$_POST['medtag'];
	$pris=$_POST['pris'];
	$antal=$_POST['antal'];
	$varenr=$_POST['varen'];
	$varemomssats=$_POST['momssats'];
	$normalpris=$_POST['normalpris'];
	$ny_saetpris=usdecimal($_POST['ny_saetpris'])*1;
	$saetpris=$_POST['saetpris'];
	$kostsum=$_POST['kostsum'];
	$normalsum=$_POST['normalsum'];
	$ny_rabat=0;
	$rabatsum=0;
	$saetsum=0;
	$tjeksum=0;

	if ($r=db_fetch_array(db_select("select varenr from ordrelinjer where ordre_id='$id' and saet='$saet' and samlevare='on'",__FILE__ . " linje " . __LINE__))) {
		$svnr=$r['varenr'];
	}
	db_modify("update ordrelinjer set posnr=posnr+100 where ordre_id = $id",__FILE__ . " linje " . __LINE__);
	
	if ($ny_saetpris) {
		if (!db_fetch_array(db_select("select id from ordrelinjer where ordre_id='$id' and varenr='$svnr' and saet='$saet'",__FILE__ . " linje " . __LINE__))){
			$r=db_fetch_array(db_select("select * from varer where varenr='$svnr'",__FILE__ . " linje " . __LINE__));
			opret_ordrelinje($id,$r['id'],$r['varenr'],1,$r['beskrivelse'],0,0,100,'$art','','','0','on','','','',$saet);
			db_modify("update ordrelinjer set posnr='99',samlevare='on',lev_varenr='$ny_saetpris' where ordre_id='$id' and saet='$saet' and varenr='$svnr'",__FILE__ . " linje " . __LINE__);
		}
		$ny_rabat=$normalsum-$ny_saetpris;
		$ny_rabat=afrund($ny_rabat*100/$normalsum,3);
	}
	for ($x=0;$x<count($linje_id);$x++) {
		if ($medtag[$x]=='on') {
			db_modify("update ordrelinjer set saet='$saet' where id = $linje_id[$x]",__FILE__ . " linje " . __LINE__);
			if ($ny_rabat && $varenr[$x]!=$svnr) {
				db_modify("update ordrelinjer set posnr='$x',rabat='$ny_rabat' where id = $linje_id[$x] and varenr!= '$svnr'",__FILE__ . " linje " . __LINE__);
				$linjesum=$antal[$x]*$pris[$x];
				$linjesum-=$linjesum*$ny_rabat/100;
				if ($incl_moms) $linjesum+=$linjesum*$varemomssats[$x]/100;
				$linjesum=afrund($linjesum,3);
				$tjeksum+=$linjesum;
			}
		} elseif($saet) {
			db_modify("update ordrelinjer set saet='0',rabat='0' where id = $linje_id[$x] and saet='$saet' and varenr != '$svnr'",__FILE__ . " linje " . __LINE__);
		}
	}
	if ($ny_rabat) {
		$diff=afrund($ny_saetpris-$tjeksum,3);
		$lev_varenr=$ny_saetpris."|".$ny_rabat;
		db_modify("update ordrelinjer set pris='$diff',lev_varenr='$lev_varenr',rabat='20',momsfri='' where ordre_id='$id' and saet='$saet' and varenr = '$svnr'",__FILE__ . " linje " . __LINE__);
	}
}
$x=1;
$q=db_select("select id from ordrelinjer where ordre_id='$id'order by saet,posnr",__FILE__ . " linje " . __LINE__);
while ($r=db_fetch_array($q)) {
	db_modify("update ordrelinjer set posnr='$x' where id = '$r[id]'",__FILE__ . " linje " . __LINE__);
	$x++;
}

if (!$saet) {
	$s=array();
	$x=0;
	$q=db_select("select distinct(saet) as saet from ordrelinjer where ordre_id='$id' and samlevare='on' and varenr!='$svnr' order by saet",__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$s[$x]=$r['saet'];
	}
	$saet=1;
	while(in_array($saet,$s)) $saet++; 
}

$r=db_fetch_array(db_select("select box2 from grupper where art='OreDif'",__FILE__ . " linje " . __LINE__));
$difkto=$r['box2'];
$x=0;
$linje_id=$medtag=array();
$r=db_fetch_array(db_select("select count(saet) as saet from ordrelinjer where ordre_id='$id' and saet='$saet'",__FILE__ . " linje " . __LINE__));
$valgt=$r['saet'];
$q=db_select("select * from ordrelinjer where ordre_id='$id' and (saet='0' or saet='$saet' or saet is NULL) order by ordrelinjer.posnr",__FILE__ . " linje " . __LINE__);
while ($r=db_fetch_array ($q)) {
	if (!$valgt || $r['saet']) {
		$linje_id[$x]=$r['id'];
		$varenr[$x]=$r['varenr'];
		$antal[$x]=$r['antal']*1;
		$pris[$x]=$r['pris']*1;
		$kostpris[$x]=$r['kostpris']*1;
		$rabat[$x]=$r['rabat']*1;
		$beskrivelse[$x]=$r['beskrivelse'];
		$medtag[$x]=$r['saet'];
		$momsfri[$x]=$r['momsfri'];
		$varemomssats[$x]=$r['momssats'];
		$samlevare[$x]=$r['samlevare'];
		if ($samlevare[$x]){
			$svnr=$varenr[$x];
			$snavn=$beskrivelse[$x];
		}
		$normalpris[$x]=$antal[$x]*$pris[$x];
		if ($momsfri[$x]) $varemomssats[$x]=0;
		elseif ($incl_moms) {
			$kostpris[$x]+=$kostpris[$x]*$varemomssats[$x]/100;
			$normalpris[$x]+=$normalpris[$x]*$varemomssats[$x]/100;
		} 
		$linjepris[$x]=afrund($normalpris[$x]-($normalpris[$x]*$rabat[$x]/100),3);
		$x++;
	}
}
$saetpris=0;
$normalsum=0;
$kostsum=0;

$txt914  = findtekst(914,$sprog_id); //Beskrivelse
$txt915  = findtekst(915,$sprog_id); //Pris
$txt916  = findtekst(916,$sprog_id); //Antal
$txt1091 = findtekst(1091,$sprog_id); //Opdater
$txt2114 = findtekst(2114,$sprog_id); //Medtag
$txt3074 = findtekst(30774,$sprog_id); //Sæt


print "<tr><td width=\"100%\" align=\"center\" colspan=\"3\"><big><b>$txt30774 $saet</b></big></td></tr>";
print "<tr><td width=\"50%\" align=\"right\" valign=\"top\"><table cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tbody>";
print "<form name=\"saetpris\" align=\"center\" action=\"saetpris.php?id=$id\" method=post autocomplete=\"off\">\n";
print "<tr><td align=\"center\" colspan=\"4\">$snavn</td></tr>";
print "<tr><td colspan=\"4\"><hr></td></tr>";
print "<tr><td><b>$txt914</b></td><td style=\"width:40px;\" align=\"right\">&nbsp;<b>$txt916</b></td><td style=\"width:80px;\" align=\"right\">&nbsp;<b>$txt915</b></td><td align=\"center\">&nbsp;<b> $txt2114</b></td></tr>";
for ($x=0;$x<count($linje_id);$x++) {
	if (!$valgt || $medtag[$x]) {
		if (!$samlevare[$x]) { 
			$kostsum+=$kostpris[$x]*$antal[$x];
			$normalsum+=$normalpris[$x];
		}
		$saetpris+=$linjepris[$x];
		print "<input type=\"hidden\" name=\"linje_id[$x]\" value=\"$linje_id[$x]\">\n
					<input type=\"hidden\" name=\"varenr[$x]\" value=\"$varenr[$x]\">\n
					<input type=\"hidden\" name=\"pris[$x]\" value=\"$pris[$x]\">\n
					<input type=\"hidden\" name=\"antal[$x]\" value=\"$antal[$x]\">\n
					<input type=\"hidden\" name=\"normalpris[$x]\" value=\"$normalpris[$x]\">\n
					<input type=\"hidden\" name=\"beskrivelse[$x]\" value=\"$beskrivelse[$x]\">\n
					<input type=\"hidden\" name=\"momssats[$x]\" value=\"$varemomssats[$x]\">\n
					<input type=\"hidden\" name=\"leveret[$x]\" value=\"$leveret[$x]\">\n
					<input type=\"hidden\" name=\"notes[$x]\" value=\"$notes[$x]\">\n";
	if (!$samlevare[$x]) {
			print "<tr>";
			print "<td>$beskrivelse[$x]&nbsp;</td>\n";
			print "<td align=\"right\">".str_replace(".",",",$antal[$x])."</td>\n";
			print "<td align=\"right\">".dkdecimal($normalpris[$x])."</td>\n";
			($medtag[$x])?$medtag[$x]="checked":$medtag[$x]=NULL;
			print "<td align=\"right\"><input style=\"width:50px;height:30px;\" name=\"medtag[$x]\" type=\"checkbox\" $medtag[$x] onfocus=\"document.forms[0].fokus.value=this.name;\"></td>"; 
			print "<tr>";
		}
	}
}
print "<tr><td colspan=\"4\"><hr></td></tr>";
$saetpris=afrund($saetpris,2);
# $saetpris=pos_afrund($saetpris,$difkto);
print "<tr><td>
	<input type=\"hidden\" name=\"kostsum\" value=\"$kostsum\">
	<input type=\"hidden\" name=\"normalsum\" value=\"$normalsum\">
	<input type=\"hidden\" name=\"saetpris\" value=\"$saetpris\">
	<input type=\"hidden\" name=\"saet\" value=\"$saet\">
	<!--Kostpris</td><td colspan=\"2\" align=\"right\">".dkdecimal($kostsum)."--></td></tr>";
print "<tr><td>Normalpris</td><td title=\"Kostpris: ".dkdecimal($kostsum)."\" colspan=\"2\" align=\"right\">".dkdecimal($normalsum)."</td></tr>";
if (in_array("checked",$medtag)) {
	print "<tr><td>Sætpris</td><td  title=\"Kostpris: ".dkdecimal($kostsum)."\" colspan=\"2\" align=\"right\">
	<input type=\"text\" style=\"text-align:right\" value=\"".dkdecimal($saetpris)."\" name=\"ny_saetpris\"></td></tr>";
}
print "<tr><td colspan=\"4\"><hr></td></tr>";
print "<tr><td colspan=\"4\"><input type=\"hidden\" name=\"fokus\"><input type=\"hidden\" name=\"pre_fokus\" value=\"$fokus\">";
print "<input style=\"width:100%;height:40px;font-size:120%\" type=\"submit\" name=\"opdater\" value=\"$txt1091\"></td></tr>";
print "</form>";
print "</tbody></table></td><td width=\"10%\"><br></td>";
$fokus="ny_saetpris";
tastatur($id,$fokus,$saet);


function tastatur($id,$fokus,$saet) {
	global $art,$sprog_id;

	$txt30   = findtekst(30,$sprog_id); //Tilbage
	$txt1099 = findtekst(1099,$sprog_id); //Slet
	$txt1814 = findtekst(1814,$sprog_id); //Slet hele sættet fra ordren?
	$txt2116 = findtekst(2116,$sprog_id); //Forfra
	$txt2117 = findtekst(2117,$sprog_id); //Rud

	$x=0;
	$q=db_select("select saet from ordrelinjer where ordre_id='$id' and saet>'0' group by saet order by saet",__FILE__ . " linje " . __LINE__);
	while($r=db_fetch_array($q)) {
		$saets[$x]=$r['saet'];
		$x++;
	}
	if (!$x) $saets[0]=1; 
	
	print "\n<!-- Function tastatur (start)-->\n";
	print "<TD height=\"100%\" valign=\"top\" align=\"left\" width=\"45%\"><TABLE BORDER=\"0\" CELLPADDING=\"4\" CELLSPACING=\"4\" class='tablePadding'><TBODY>\n";
	print "<TR>\n";
		$stil="STYLE=\"width:80px;height:40px;font-size:120%;\"";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"one\"   VALUE=\"1\" onclick=\"saetpris.$fokus.value += '1';saetpris.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"two\"   VALUE=\"2\" onclick=\"saetpris.$fokus.value += '2';saetpris.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"three\" VALUE=\"3\" onclick=\"saetpris.$fokus.value += '3';saetpris.$fokus.focus();\"></TD>\n";
		print "</TR><TR>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"four\"  VALUE=\"4\" onclick=\"saetpris.$fokus.value += '4';saetpris.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"five\"  VALUE=\"5\" onclick=\"saetpris.$fokus.value += '5';saetpris.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"six\"   VALUE=\"6\" onclick=\"saetpris.$fokus.value += '6';saetpris.$fokus.focus();\"></TD>\n";
		print "</TR><TR>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"seven\" VALUE=\"7\" onclick=\"saetpris.$fokus.value += '7';saetpris.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"eight\" VALUE=\"8\" onclick=\"saetpris.$fokus.value += '8';saetpris.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"nine\"  VALUE=\"9\" onclick=\"saetpris.$fokus.value += '9';saetpris.$fokus.focus();\"></TD>\n";
		print "</TR><TR>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"zero\"  VALUE=\",\" onclick=\"saetpris.$fokus.value += ',';saetpris.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"zero\"  VALUE=\"0\" onclick=\"saetpris.$fokus.value += '0';saetpris.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"clear\" VALUE=\"$txt2117\" onclick=\"saetpris.$fokus.value = '';saetpris.$fokus.focus();\"></TD>\n";
		print "</TR><TR>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"forfra\" VALUE='$txt2116' onclick=\"window.location.href='saetpris.php?id=$id&forfra=1&saet=$saet'\"></TD>\n";
		print "<FORM ACTION=\"saetpris.php?id=$id\" method=\"post\" autocomplete=\"off\">\n";
		print "<TD collspan=\"2\"><SELECT $stil NAME=\"saetvalg\" onchange=\"this.form.submit()\">>";
		if ($saet) print "<OPTION VALUE=\"$saet\">$txt30774 $saet</OPTION>";		
		for($x=0;$x<count($saets);$x++){
			if ($saets[$x]!=$saet) print "<OPTION VALUE=\"$saets[$x]\">$txt3074 $saets[$x]</OPTION>";		
		}
		print "<OPTION VALUE=\"nyt_saet\">Nyt sæt</OPTION>";
		print "</SELECT>";
		print "</FORM>";
		print "</TD>\n";
		($art=='PO')?$href="pos_ordre.php?id=$id":$href="ordre.php?id=$id";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"tilbage\"  VALUE=\"$txt30\" onclick=\"window.location.href='$href'\"></TD>\n";
		print "</TR><TR>\n";
		print "<FORM ACTION=\"saetpris.php?id=$id&saet=$saet\" method=\"post\" autocomplete=\"off\">\n";
		print "<TD><INPUT TYPE=\"submit\" $stil onclick=\"return confirm('$txt1814');\" NAME=\"slet\" VALUE=\"$txt1099\"></TD>\n";
		print "<TD></TD>\n";
		print "<TD><TD>\n";
	print "</TR>\n";
	print "</TBODY></TABLE></TD></TR>\n";
	print "\n<!-- Function tastatur (slut)-->\n";
}

print "</tbody></table>";

if ($menu=='T') {
	include_once '../includes/topmenu/footer.php';
} else {
	include_once '../includes/oldDesign/footer.php';
}

?>
<script language="javascript">
document.saetpris.<?php echo $fokus?>.focus();
</script>
<?php
#cho $fokus;<b>

#####################################################################################################

