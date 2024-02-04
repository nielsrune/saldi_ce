<?php
// --- kreditor/ordreliste.php -----patch 4.0.8 ----2023-10-17----------
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
// Copyright (c) 2003-2023 Saldi.dk ApS
// ----------------------------------------------------------------------
// 2014.03.19 addslashes erstattet med db_escape_string
// 2104.09.16	Tilføjet oioublimport i bunden
// 20211125 PHR Added 'Skan Bilag'
// 20220728 MSC - Implementing new design
// 20221106 PHR - Various changes to fit php8 / MySQLi
// 20230317 LOE - Applied some translated texts, and Also fixed some undefined variable errors and some more.
// 20230525 PHR - php8
// 20231017 PHR Fixed an error in account selection ($firma);

ob_start();
@session_start();
$s_id=session_id();

$css="../css/std.css";
$modulnr=5;
$title="Leverandører • Ordreliste";
$dk_dg=$firmanavn=$hrefslut=$hurtigfakt=$konto_id=$linjebg=NULL;
$checked=$returside=$totalkost=$understreg=$vis_projekt=$nbsp=NULL;

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/udvaelg.php");
	
global $menu;

$ordrenumre = if_isset($_GET['ordrenumre']);
$kontonumre = if_isset($_GET['kontonumre']);
$modtagelsesnumre = if_isset($_GET['modtagelsesnumre']);
$fakturanumre = if_isset($_GET['fakturanumre']);
$ordredatoer = if_isset($_GET['ordredatoer']);
$lev_datoer = if_isset($_GET['lev_datoer']);
$lev_navne = if_isset($_GET['lev_navne']);
$fakturadatoer = if_isset($_GET['fakturadatoer']);
$genfaktdatoer = if_isset($_GET['genfaktdatoer']);
$summer = if_isset($_GET['summer']);
$firma = if_isset($_GET['firma']);
$ref[0] = if_isset($_GET['ref']);
$projekt[0] = if_isset($_GET['projekt']);
$valg= if_isset($_GET['valg']);
$sort = if_isset($_GET['sort']);
$nysort = if_isset($_GET['nysort']);
$kontoid= if_isset($_GET['kontoid']);

$tidspkt=date("U");

$r2=db_fetch_array(db_select("select max(id) as id from grupper",__FILE__ . " linje " . __LINE__));

if(isset($_GET['returside'])) {
 	$returside= $_GET['returside'];
	if ($r=db_fetch_array(db_select("select id from grupper where art = 'OLV' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__))) {
		db_modify("update grupper set box2='$returside' where id='$r[id]'",__FILE__ . " linje " . __LINE__);
	} else db_modify("insert into grupper (beskrivelse,kode,kodenr,art,box2) values ('Ordrelistevisning','$brugernavn','$bruger_id','OLV','$returside')",__FILE__ . " linje " . __LINE__);
} else {
$r2=db_fetch_array(db_select("select max(id) as id from grupper",__FILE__ . " linje " . __LINE__));
	$r=db_fetch_array(db_select("select box2 from grupper where art = 'OLV' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__)); 
	$returside=$r['box2'];
}

if ($returside == 'ordreliste.php') $returside = NULL;
if (!$returside) {
	if ($popup) $returside= "../includes/luk.php";
	else $returside= "../index/menu.php";
}
 
#if (isset($_POST)) {
if ($submit=if_isset($_POST['submit'])) {
	$ordrenumre = if_isset($_POST['ordrenumre']);
	$modtagelsesnumre = if_isset($_POST['modtagelsesnumre']);
	$kontonumre = if_isset($_POST['kontonumre']);
	$fakturanumre = if_isset($_POST['fakturanumre']);
	$ordredatoer = if_isset($_POST['ordredatoer']);
	$lev_datoer = if_isset($_POST['lev_datoer']);
	$lev_navne = if_isset($_POST['lev_navne']);
	$fakturadatoer = if_isset($_POST['fakturadatoer']);
	$genfaktdatoer = if_isset($_POST['genfaktdatoer']);
	$summer = if_isset($_POST['summer']);
	$firma = if_isset($_POST['firma']);
	$ref[0] = if_isset($_POST['ref']);
	$projekt[0] = if_isset($_POST['projekt']);
	$valg=if_isset($_POST['valg']);
	$sort = if_isset($_POST['sort']);
	$nysort = if_isset($_POST['nysort']);
	$firma=if_isset($_POST['firma']);
	$kontoid=if_isset($_POST['kontoid']);
}
list($kontoid,$firma) = explode('|',$firma,2);

if ($valg) {
	$cookievalue="$ordrenumre;$kontonumre;$fakturanumre;$ordredatoer;$lev_datoer;$fakturadatoer;$genfaktdatoer;$summer;$firma;$kontoid;$ref[0];$sort;$valg;$nysort;$modtagelsesnumre";
	setcookie("kred_ord_lst", $cookievalue);
}
elseif (isset($_COOKIE['kred_ord_lst']) && $_COOKIE['kred_ord_lst']) {
	list ($ordrenumre, $kontonumre, $fakturanumre, $ordredatoer, $lev_datoer, $fakturadatoer, $genfaktdatoer, $summer, $firma, $kontoid, $ref[0], $sort, $valg, $nysort, $modtagelsesnumre) = explode(";", $_COOKIE['kred_ord_lst']);#
}
ob_end_flush();	//Sender det "bufferede" output afsted...
	
if (!$valg) $valg = "ordrer";
if (!$sort) $sort = "firmanavn";
elseif ($nysort==$sort) $sort=$sort." desc";
elseif ($nysort) $sort=$nysort;
if ($valg!='faktura') {
	$fakturanumre='';
	$fakturadatoer='';
	$genfaktdatoer='';
}
if ($valg=="forslag") $status="status = 0";
elseif ($valg=="faktura") $status="status >= 3";
else $status="status = 1 or status = 2";

$paperflow=NULL;
$qtxt="select var_value from settings where var_grp='creditor' and var_name='paperflow'";
if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) $paperflow = $r['var_value'];
if ($paperflow) $paperflow="checked='checked'";

if (db_fetch_array(db_select("select distinct id from ordrer where (art='DK' or art='KK') and projekt > '0' and $status",__FILE__ . " linje " . __LINE__))) $vis_projekt='on';
if (db_fetch_array(db_select("select id from grupper where art = 'DIV' and kodenr = '3' and box4='on'",__FILE__ . " linje " . __LINE__))) $hurtigfakt='on';

$hreftext="&ordrenumre=$ordrenumre&kontonumre=$kontonumre&fakturanumre=$fakturanumre&ordredatoer=$ordredatoer&lev_datoer=$lev_datoer&fakturadatoer=$fakturadatoer&genfaktdatoer=$genfaktdatoer&summer=$summer&ref=$ref[0]&kontoid=$kontoid&modtagelsesnumre=$modtagelsesnumre";
#if ($valg!="faktura") print "<meta http-equiv=\"refresh\" content=\"60;URL='ordreliste.php?sort=$sort&valg=$valg$hreftext'\">";
 
 
if ($submit=="Udskriv"){
	$ordre_antal = if_isset($_POST['ordre_antal']);
	$ordre_id = if_isset($_POST['ordre_id']);
	$checked = if_isset($_POST['checked']);
	
	for ($x=1; $x<=$ordre_antal; $x++){
		if ($checked[$x]=="on") {
			$y++;
			if (!$udskriv) $udskriv=$ordre_id[$x];
			else $udskriv=$udskriv.",".$ordre_id[$x];
		}
	}
	if ($y>0) {
		print "<BODY onLoad=\"JavaScript:window.open('formularprint.php?id=-1&ordre_antal=$y&skriv=$udskriv&formular=4' , '' , ',statusbar=no,menubar=no,titlebar=no,toolbar=no,scrollbars=yes, location=1');\">";
	}
	else print "<BODY onLoad=\"javascript:alert('Ingen fakturaer er markeret til udskrivning!')\">";
}
if (isset($_POST['check'])||isset($_POST['uncheck'])) {
	$ordre_antal = if_isset($_POST['ordre_antal']);
	$ordre_id = if_isset($_POST['ordre_id']);
	if (isset($_POST['check'])) $check_all='on';
}

if ($menu=='T') {
	include_once '../includes/top_header.php';
	include_once '../includes/top_menu.php';
	print "<div id=\"header\">"; 
	print "<div class=\"headerbtnLft headLink\">&nbsp;&nbsp;&nbsp;</div>";     
	print "<div class=\"headerTxt\">$title</div>";     
	print "<div class=\"headerbtnRght headLink\"><a accesskey=N href='ordre.php?returside=ordreliste.php' title='Opret ny ordre'><i class='fa fa-plus-square fa-lg'></i></a></div>";     
	print "</div>";
	print "<div class='content-noside'>";
} else {
print "<table width=100% height=100% border=0 cellspacing=0 cellpadding=0><tbody>";
print "<tr><td height = 25 align=center valign=top>";
print "<table width=100% align=center border=0 cellspacing=2 cellpadding=0><tbody>";
print "<td width=10% $top_bund><a href=$returside accesskey=L>Luk</a></td>";
# print "<td width=50%$top_bund align=center>Kundeordrer</td>";

print "<td width=80% $top_bund align=center><table border=0 cellspacing=2 cellpadding=0><tbody>";
	if (!$hurtigfakt) {
		print "<td width = 20% align=center ";
		if ($valg=='forslag') print $knap_ind .">&nbsp;Forslag&nbsp;";
		else print "<td width = 20% align=center><a href='ordreliste.php?sort=$sort&valg=forslag$hreftext'>&nbsp;Forslag&nbsp;</a>";
		print "</td>";
	}
	print "<td width = 20% align=center ";
	if ($valg=='ordrer') print $knap_ind .">&nbsp;Ordrer&nbsp";
	else print "><a href='ordreliste.php?sort=$sort&valg=ordrer$hreftext'>&nbsp;Ordrer&nbsp;</a>";
	print "</td><td width = 20% align=center "; 
	if ($valg=='faktura') print $knap_ind .">&nbsp;Faktura&nbsp;";
	else print "><a href='ordreliste.php?sort=$sort&valg=faktura$hreftext'>&nbsp;Faktura&nbsp;</a>";
	if ($paperflow) {
	print "</td><td width = 20% align=center "; 
		if ($valg=='skanBilag') print  $knap_ind .">&nbsp;Skan bilag&nbsp;";
		else print "><a href='ordreliste.php?sort=$sort&valg=skanBilag$hreftext'>&nbsp;Skan bilag&nbsp;</a>";
	print "</td>";
	}
print "</tbody></table></td>";
if ($popup) print "<td width=10% $top_bund onClick=\"javascript:ordre=window.open('ordre.php?returside=ordreliste.php','ordre','scrollbars=1,resizable=1');ordre.focus();\"><a accesskey=N href=ordreliste.php?sort=$sort>Ny</a></td>";
else print "<td width=10% $top_bund><a href=ordre.php?returside=ordreliste.php>Ny</a></td>";
print "</td></tr>\n";
#print "<tr><td></td><td align=center><table border=1	cellspacing=0 cellpadding=0><tbody>";
#print "<td width = 20%$top_bund align=center><a href=ordreliste.php?valg=forslag accesskey=T>Forslag</a></td>";
#print "<td width = 20% bgcolor=$bgcolor5 align=center> Ordrer</td>";
#print "<td width = 20% bgcolor=$bgcolor5 align=center> Faktura</td>";
#print "</tbody></table></td><td></td</tr>\n";

print "</tbody></table>";
}
print " </td></tr>\n<tr><td align=center valign=top>";
if ($valg != 'skanBilag') {
print "<table cellpadding=1 cellspacing=1 border=0 width=100% valign = top class='dataTable'>";
print "<tbody>";
print "	<tr>";
print "<td align=right><b><a href='ordreliste.php?nysort=ordrenr&sort=$sort&valg=$valg$hreftext' title='Ordrenummer'>Ordrenr.</b></td>";
if ($valg=='faktura') {
	print " <td align=right width=50><b><a href='ordreliste.php?nysort=modtagelse&sort=$sort&valg=$valg$hreftext' title='Modtagelsesnummer'>Modt.nr.</b></td>";
	print " <td align=right width=50><b><a href='ordreliste.php?nysort=fakturanr&sort=$sort&valg=$valg$hreftext' title='Fakturanr.'>Fakt.nr.</b></td>";
}
print "	<td width=50></td>";
if ($valg=='forslag') {print "<td><b><a href='ordreliste.php?nysort=ordredate&sort=$sort&valg=$valg$hreftext'>Forslagsdato</b></td>";}
else {
	print "<td><b><a href='ordreliste.php?nysort=ordredate&sort=$sort&valg=$valg$hreftext'>Ordredato</b></td>";
	print "<td><b><a href='ordreliste.php?nysort=levdate&sort=$sort&valg=$valg$hreftext' title='Modtagelsesdato'>Modt.dato</b></td>";
}
if ($valg=='faktura') {
	print "<td><b><a href='ordreliste.php?nysort=fakturadate&sort=$sort&valg=$valg$hreftext' title='Fakturadato'>Fakt.dato</b></td>";
}
print "<td><b><a href='ordreliste.php?nysort=kontonr&sort=$sort&valg=$valg$hreftext' title='Kontonummer'>".findtekst(804,$sprog_id)."</b></td>";
print "<td><b><a href='ordreliste.php?nysort=firmanavn&sort=$sort&valg=$valg$hreftext'>".findtekst(360,$sprog_id)."</a></b></td>";
print "<td><b><a href='ordreliste.php?nysort=lev_navn&sort=$sort&valg=$valg$hreftext'>".findtekst(814,$sprog_id)."</a></b></td>";
print "<td title='Vores reference'><b> ".findtekst(815,$sprog_id)."</a></b></td>";
if ($vis_projekt) print "<td title='Projektnummer'><b>".findtekst(816,$sprog_id)."</a></b></td>";
if ($valg=='forslag') {print "<td align=right><b><a href='ordreliste.php?nysort=sum&sort=$sort&valg=$valg$hreftext'>".findtekst(826,$sprog_id)."</a></b></td>";}
elseif ($valg=='ordrer'){print "<td align=right><b><a href='ordreliste.php?nysort=sum&sort=$sort&valg=$valg$hreftext'>".findtekst(887,$sprog_id)."</a></b></td>";}
else {
	print "<td align=right><b><a href='ordreliste.php?nysort=sum&sort=$sort&valg=$valg$hreftext'>".findtekst(885,$sprog_id)."";
	print "</a></b></td>";
}
print "</tr>\n";
#################################### Sogefelter ##########################################

print "<form name=ordreliste action=ordreliste.php method=post>";
print "<input type=hidden name=valg value=$valg>";
print "<input type=hidden name=sort value=$sort>";
print "<input type=hidden name=nysort value=$nysort>";
print "<input type=hidden name=kontoid value=$kontoid>";
print "<tr>";
print "<td align=right><span title= 'Angiv et ordrenummer eller angiv to adskilt af kolon (f.eks 345:350)'><input class=\"inputbox\" type=text size=5 name=ordrenumre value=$ordrenumre></td>";
if ($valg=='faktura') {
print "<td align=right><span title= 'Angiv et modtagelsesnummer eller angiv to adskilt af kolon (f.eks 345:350)'><input class=\"inputbox\" type=text size=5 name=modtagelsesnumre value=$modtagelsesnumre></td>";
print "<td align=right><span title= 'Angiv et fakturanummer eller angiv to adskilt af kolon (f.eks 345:350)'><input class=\"inputbox\" type=text size=5 name=fakturanumre value=$fakturanumre></td>";
} 
print "<td width=50></td>";
print "<td><span title= 'Angiv en dato eller angiv to adskilt af kolon (f.eks 010605:300605)'><input class=\"inputbox\" type=text size=10 name=ordredatoer value=$ordredatoer></td>";
if ($valg!='forslag') {print "<td><span title= 'Angiv en dato eller angiv to adskilt af kolon (f.eks 010605:300605)'><input class=\"inputbox\" type=text size=10 name=lev_datoer value=$lev_datoer></td>";}
if ($valg=='faktura') {
	print "<td><span title= 'Angiv en dato eller angiv to adskilt af kolon (f.eks 010605:300605)'><input class=\"inputbox\" type=text size=10 name=fakturadatoer value=$fakturadatoer></td>";
}
print "<td><span title= 'Angiv et kontonr. eller angiv to adskilt af kolon (f.eks 43000000:43999999)'><input class=\"inputbox\" type=text size=10 name=kontonumre value=$kontonumre></td>";

$x=0;
if (!$konto_id) {$konto_id=array();}
if (($kontoid)&&(!$firma)){
	$row = db_fetch_array(db_select("select firmanavn from adresser where id = $kontoid",__FILE__ . " linje " . __LINE__));
	$firma=$row['firmanavn'];
}
$qtxt = "select konto_id from ordrer where (art = 'KK' or art = 'KO') and $status order by firmanavn";
$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
while ($r = db_fetch_array($q)) {
	if (!in_array($r['konto_id'], $konto_id)) {
		$konto_id[$x]=$r['konto_id'];
		$qtxt = "select firmanavn from adresser where id = $konto_id[$x]";
		$r2 = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		 $firmanavn[$x]=$r2['firmanavn'];
		if (strlen($firmanavn[$x])>35) $firmanavn[$x]=substr($firmanavn[$x],0,30)."...";
		 print "<input type=hidden name=firmanavn$x value='$firmanavn[$x]'>";
		 print "<input type=hidden name=konto_id$x value=$konto_id[$x]>";
		$x++;
	 } 
}
 
print "<td><span title= 'V&aelig;lg et firma'><SELECT NAME='firma' value='$firma' style='width:100px'>";
print "<option>$firma</option>";
print "<option>$nbsp</option>";
for ($x=1;$x<count($konto_id); $x++) {
	print "<option value = '$konto_id[$x]|$firmanavn[$x]'>$firmanavn[$x]</option>";
}
print "</SELECT></td>";

print "<td><span title= 'Navn p&aring; modtager. Der kan s&aring;ges med * f&aelig;r og efter tekstes'><input class=\"inputbox\" type=text size=10 name=lev_navne value=$lev_navne></td>";

$x=0;
if (!$ref) {$ref=array();}
$query = db_select("select ref from ordrer where art='KO' order by ref",__FILE__ . " linje " . __LINE__);
while ($row = db_fetch_array($query)) {
	 if (!in_array($row['ref'], $ref)) {
		 $x++;
		 $ref[$x]=$row['ref'];
	 } 
}

$refantal=$x;	
print "<td><span title= 'V&aelig;lg en referanceperson'><SELECT NAME='ref' value='$ref[0]' style='width:100px'>";
print "<option>$ref[0]</option>";
for ($x=1;$x<=$refantal; $x++) {print "<option>$ref[$x]</option>";}
if ($ref[0]!=if_isset($ref[$x])) {print "<option>$ref[$x]</option>";}
if ($ref[0]) {print "</SELECT></td>";}

if ($vis_projekt) {
	$x=0;
	if (!$projekt) {$projekt=array();}
	print "<td><span title= 'V&aelig;lg et projektnr'><SELECT NAME=projekt value=$projekt[0]>";
	$q = db_select("select kodenr, beskrivelse from grupper where art='PRJ' order by box2",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		$x++;
		if ($projekt[0]!=$r['kodenr']) print "<option title='$r[beskrivelse]'>$r[kodenr]</option>";
		else print "<option selected='selected' title='$r[beskrivelse]'>$r[kodenr]</option>";
	}
	if (!$projekt[0]) print "<option selected='selected'></option>";
	else print "<option></option>";
}
print "<td align=right><span title= 'Angiv et bel&oslash;b eller angiv to adskilt af kolon (f.eks 10000,00:14999,99)'><input class=\"inputbox\" type=text size=10 name=summer value=$summer></td>";
print "<td><input class=\"inputbox\" type=submit value=\"OK\" name=\"submit\"></td>";
print "</form></tr>\n";
####################################################################################
$udvaelg='';
if ($ordrenumre) {
	$udvaelg=$udvaelg.udvaelg($ordrenumre, 'ordrenr', 'NR');
}
if ($modtagelsesnumre) {
	$udvaelg=$udvaelg.=udvaelg($modtagelsesnumre, 'modtagelse', 'NR');
}
if ($fakturanumre) {
	$udvaelg=$udvaelg.=udvaelg($fakturanumre, 'fakturanr', 'NR');
}
if ($kontonumre) {
	$udvaelg=$udvaelg.=udvaelg($kontonumre, 'kontonr', 'NR');
}
if ($ordredatoer) {
	$udvaelg=$udvaelg.udvaelg($ordredatoer, 'ordredate', 'DATO');
}
if ($lev_datoer) {
	$udvaelg=$udvaelg.udvaelg($lev_datoer, 'levdate', 'DATO');
}
if ($fakturadatoer){
	$udvaelg=$udvaelg.udvaelg($fakturadatoer, 'fakturadate', 'DATO');
}
if ($genfaktdatoer){
	$udvaelg=$udvaelg.udvaelg($genfaktdatoer, 'nextfakt', 'DATO');
}
if ($ref[0]) {$udvaelg= $udvaelg." and ref='$ref[0]'";}
if ($projekt[0]) {$udvaelg= $udvaelg." and projekt='$projekt[0]'";}
if ($summer) { $udvaelg=$udvaelg.udvaelg($summer, 'sum', 'BELOB');}

if ($kontoid){
	$udvaelg=$udvaelg.udvaelg($kontoid, 'konto_id', 'NR');
}

if ($valg=="forslag") { 
	$ialt=0;
	$query = db_select("select * from ordrer where (art = 'KO' or art = 'KK') and status < 1 $udvaelg order by $sort",__FILE__ . " linje " . __LINE__);
	while ($row =db_fetch_array($query)) {
		$ordre="ordre".$row['id'];
		$sum=$row['sum'];
		$kostpris=$row['kostpris'];
		$valutakurs=$row['valutakurs'];
		$orderTime  = $row['tidspkt'];
		if (!$orderTime) $orderTime = 0; 
		if (($tidspkt-($orderTime)>3600)||($row['hvem']==$brugernavn)){
			if ($popup) {
				$javascript="onClick=\"javascript:$ordre=window.open('ordre.php?tjek=$row[id]&id=$row[id]&returside=ordreliste.php','$ordre','scrollbars=1,resizable=1');$ordre.focus();\" onMouseOver=\"this.style.cursor = 'pointer'\" ";
				$understreg='<span style="text-decoration: underline;">';
				$hrefslut="";
			} else {
				$javascript="";
				$understreg="<a href=ordre.php?tjek=$row[id]&id=$row[id]&returside=ordreliste.php>";
				$hrefslut="</a>";
			}
				$linjetext="";
		}	else {
			$javascript="onClick=\"javascript:$ordre.focus();\"";
			$understreg='';
			$linjetext="<span title= 'Ordre er l&aring;st af $row[hvem]'>";
		}
		if ($linjebg!=$bgcolor) {
			$linjebg=$bgcolor; $color='#000000';
		} else {
			$linjebg=$bgcolor5; $color='#000000';
		}

		print "<tr bgcolor=\"$linjebg\" style='color:$color'>";
		if ($row['art']=='KK') {
			print "<td align=right $javascript style='color:$color'> (KN)&nbsp;$linjetext $understreg $row[ordrenr]$hrefslut</span><br></td>";
		} else {
			print "<td align=right $javascript style='color:$color'> $linjetext $understreg $row[ordrenr]$hrefslut</span><br></td>";
		}
		print "<td></td>";
		$ordredato=dkdato($row['ordredate']);
		print "<td>$ordredato<br></td>";
#		$levdato=dkdato($row['levdate']);
#		print "<td>$levdato<br></td>";
#		print"<td></td>";
		print "<td>$row[kontonr]<br></td>";
		print "<td>".$row['firmanavn']."<br></td>";
		print "<td>".$row['lev_navn']."<br></td>";
		print "<td>$row[ref]<br></td>";
		if ($vis_projekt) print "<td>$row[projekt]<br></td>";
		if ($valutakurs && $valutakurs!=100) {
			$sum=$sum*$valutakurs/100;
		} 
		$ialt=$ialt+$sum;
		print "<td align=right>".dkdecimal($sum)."<br></td><td></td></tr>\n";
	}
} elseif ($valg=='ordrer') {
	$ialt=0;
	
	if ($hurtigfakt) $qtxt = "select * from ordrer where (art = 'KO' or art = 'KK') and (status < 3) $udvaelg order by $sort";
	else $qtxt = "select * from ordrer where (art = 'KO' or art = 'KK') and (status = 1 or status = 2) $udvaelg order by $sort";
	$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($row =db_fetch_array($q)){
		$ordre="ordre".$row['id'];
		$sum=$row['sum'];
		$kostpris=$row['kostpris'];
		$valutakurs=$row['valutakurs'];
		$orderTime  = $row['tidspkt'];
		if (!$orderTime) $orderTime = 0; 
		if (($tidspkt-($orderTime)>3600)||($row['hvem']==$brugernavn)){
			if ($popup) {
				$javascript="onClick=\"javascript:$ordre=window.open('ordre.php?tjek=$row[id]&id=$row[id]&returside=ordreliste.php','$ordre','scrollbars=1,resizable=1');$ordre.focus();\" onMouseOver=\"this.style.cursor = 'pointer'\"";
				$understreg='<span style="text-decoration: underline;">';
				$hrefslut="";
			} else {
				$javascript="";
				$understreg="<a href=ordre.php?tjek=$row[id]&id=$row[id]&returside=ordreliste.php>";
				$hrefslut="</a>";
			}
			$linjetext="";
		} else {
			$javascript='';
			$understreg='';
			$linjetext="<span title= 'Kladde er l&aring;st af $row[hvem]'>";
		}

		if ( $bgnuance1 ) {
			$q2=db_select("select antal,leveres,leveret from ordrelinjer where ordre_id = '$row[id]'",__FILE__ . " linje " . __LINE__);
			$levstatus=0;
			while ($r2=db_fetch_array($q2)) {
				if ( $levstatus === "Mangler" ) {
					continue;
				}
				if ( ( $r2['leveret'] > 0 && $r2['antal'] > $r2['leveret'] ) || ( $r2['antal'] > ($r2['leveres']+$r2['leveret'] ) ) ) {
					$levstatus="Mangler";
				} elseif ( $r2['leveret'] == 0 && ( $r2['antal'] == $r2['leveres'] ) ) {
					if ( $levstatus === "Leveret" ) {
						$levstatus="Mangler";
					} else {
						$levstatus="Intet";
					}
				} elseif ( ( ! $levstatus == "Intet" ) && $r2['leveret'] > 0 && $r2['antal'] == $r2['leveret'] ) {
					$levstatus="Leveret";
				}
			}
			if ( $levstatus == "Mangler" ) {
				$bgnuance=$bgnuance1;
				$color='#000000';
				if ($row['art']=='KK') {
					$tr_title="Mangler returnering af en eller flere vare.";
				} else {
					$tr_title="Mangler modtagelse af en eller flere vare.";
				}
			} elseif ( $levstatus == "Leveret" ) {
				$bgnuance=0;
				$color='#555555';
				if ($row['art']=='KK') {
					$tr_title="Alt returneret, mangler kun at modtage kreditnota, s&aring; det kan bogf&oslash;res.";
				} else {
					$tr_title="Alt modtaget, mangler kun at modtage faktura, s&aring; det kan bogf&oslash;res.";
				}
			} else {
				$bgnuance=0;
				$color='#000000';
				if ($row['art']=='KK') {
					$tr_title="Intet returneret.";
				} else {
					$tr_title="Intet modtaget.";
				}
			}
			$linjebg=linjefarve($linjebg, $bgcolor, $bgcolor5, $bgnuance1, $bgnuance);
			print "<tr style='color: $color; background: $linjebg' title='$tr_title'>";
		} else {
			if ($linjebg!=$bgcolor) {
				$linjebg=$bgcolor; $color='#000000';
			} else {
				$linjebg=$bgcolor5; $color='#000000';
			}
			print "<tr style='color: $color; background: $linjebg'>";
		}

		if ($row['art']=='KK') {
			print "<td align=right $javascript>(KN)&nbsp;$understreg $linjetext $row[ordrenr]</span><br>$hrefslut</td>";
		} else {
			print "<td align=right $javascript> $understreg $linjetext $row[ordrenr]</span><br>$hrefslut</td>";
		}
		print "<td></td>";
		$ordredato=dkdato($row['ordredate']);
		print "<td>$ordredato<br></td>";
		$levdato=dkdato($row['levdate']);
		print "<td>$levdato<br></td>";
		print "<td>$row[kontonr]<br></td>";
		print "<td>".$row['firmanavn']."<br></td>";
		print "<td>".$row['lev_navn']."<br></td>";
		print "<td>$row[ref]<br></td>";
		if ($vis_projekt) print "<td>$row[projekt]<br></td>";
		if ($valutakurs && $valutakurs!=100) {
			$sum=$sum*$valutakurs/100;
		} 
		$ialt=$ialt+$sum;
		print "<td align=right>".dkdecimal($sum)."<br></td><td></td></tr>\n";
	}
} else {
	$x=0;
	$ialt=0;
	$query = db_select("select * from ordrer where (art = 'KO' or art = 'KK') and status >= 3 $udvaelg order by $sort",__FILE__ . " linje " . __LINE__);
	while ($row =db_fetch_array($query)) {
		$x++;
		$ordre="ordre".$row['id'];
		$sum=$row['sum'];
		$kostpris=$row['kostpris'];
		$valutakurs=$row['valutakurs'];
		$javascript="onClick=\"javascript:$ordre=window.open('ordre.php?&id=$row[id]&returside=ordreliste.php','$ordre','scrollbars=1,resizable=1');$ordre.focus();\" onMouseOver=\"this.style.cursor = 'pointer'\"";
		if ($linjebg!=$bgcolor) {
			$linjebg=$bgcolor; $color='#000000';
		} else {
			$linjebg=$bgcolor5; $color='#000000';
		}

		print "<tr style='color: $color; background: $linjebg'>";
		if ($popup) {
			if ($row['art']=='KK') {
				print "<td align=right $javascript ><span style='color: rgb(255, 0, 0); text-decoration: underline;'>$row[ordrenr]<br></span></td>";
			} else {
				print "<td align=right	$javascript> $understreg <span style='text-decoration: underline;'> $row[ordrenr]<br></span></td>";
			}
		} else {
			if ($row['art']=='DK') {
				print "<td align=right><a href=ordre.php?&id=$row[id]&returside=ordreliste.php><span style='color: rgb(255, 0, 0);'>$row[ordrenr]<br></a></span></td>";
			} else {
				print "<td align=right><a href=ordre.php?&id=$row[id]&returside=ordreliste.php>$row[ordrenr]<br></a></td>";
			}
		}
		print "<td align=right>$row[modtagelse]</td>";
		print "<td align=right>$row[fakturanr]</td>";
		print"<td></td>";
		$ordredato=dkdato($row['ordredate']);
		print "<td>$ordredato<br></td>";
		$levdato=dkdato($row['levdate']);
		print "<td>$levdato<br></td>";
		$faktdato=dkdato($row['fakturadate']);
		print "<td>$faktdato<br></td>";
		print "<td>$row[kontonr]<br></td>";
		print "<td>".$row['firmanavn']."<br></td>";
		print "<td>".$row['lev_navn']."<br></td>";
		print "<td>$row[ref]<br></td>";
		if ($vis_projekt) {
			print "<td>$row[projekt]<br></td>";
		}
		if ($valutakurs && $valutakurs!=100) {
			$sum=$sum*$valutakurs/100;
		} 
		$ialt=$ialt+$sum;
		print "<td align=right>".dkdecimal($sum)." <br></td>";
		print "<td></td></tr>\n";
	}
	$colspan=12;
	if ($vis_projekt) $colspan++;
	print "	</td></tr>\n";
	print "<input type=hidden name=ordre_antal value='$x'>";
	print "<input type=hidden name=valg value='$valg'>";
	print "<input type=hidden name=ordrenumre value='$ordrenumre'>";
	print "<input type=hidden name=kontonumre value='$kontonumre'>";
	print "<input type=hidden name=modtagelsesnumre value='$modtagelsesnumre'>";
	print "<input type=hidden name=fakturanumre value='$fakturanumre'>";
	print "<input type=hidden name=ordredatoer value='$ordredatoer'>";
	print "<input type=hidden name=lev_datoer value='$lev_datoer'>";
	print "<input type=hidden name=fakturadatoer value='$fakturadatoer'>";
	print "<input type=hidden name=genfaktdatoer value='$genfaktdatoer'>";
	print "<input type=hidden name=summer value='$summer'>";
	print "<input type=hidden name=ref value='$ref[0]'>";
	print "<input type=hidden name=firma value='$firma'>";
	print "<input type=hidden name=lev_navne value='$lev_navne'>";
	print "<input type=hidden name=kontoid value='$kontoid'>";
	print "<input type=hidden name=sort value='$sort'>";
	print "<input type=hidden name=nysort value='$nysort'>";
	print "<tr><td colspan=$colspan align=right>";
}

if ($valg=='forslag') {
	$cols=8;
} elseif ($valg=='faktura') {
	$cols=12;
} else {
	$cols=9;
}

if ($vis_projekt) $cols++;
print "<tr><td colspan=20><hr></td></tr>\n";
$cols=$cols-4;
$dk_db=dkdecimal($ialt-$totalkost);		
if ($ialt!=0) {
	$dk_dg=dkdecimal(($ialt-$totalkost)*100/$ialt);
}
$ialt=dkdecimal($ialt);
$cols--;
if ($valg=='faktura') $cols--;
print "<tr><td colspan=3></td><td align=center colspan=$cols-4><span title= 'Klik for at genberegne DB/DG'><b>Samlet oms&aelig;tning (excl. moms.)</td><td align=right colspan=2><b>$ialt</td><td></td></tr>\n";
} else {
	include "scanAttachments/frontpage/frontTable.php";
	include "scanAttachments/scanAttachments.php";
}
#if ($genberegn==1) print "<meta http-equiv=\"refresh\" content=\"0;URL='ordreliste.php?genberegn=2$hreftext'\">";

$cols++;
if ($valg=='faktura') $cols++;
$cols=$cols+4;
print "<tr><td colspan=20><hr></td></tr>\n";
if ($valg == 'skanBilag' && $r=db_fetch_array(db_select("select * from grupper where art='bilag'",__FILE__ . " linje " . __LINE__)) && $box6=$r['box6']) {
	print "<tr><td colspan=\"4\" width=\"100%\" align=\"left\" valign=\"top\"><span title=\"Klik her for at importere en elektronisk faktura af typen oioubl\"><a href=ublimport.php?funktion=gennemse>Importer OIOUBL faktura</a></span></td></tr>";
}

print "</tbody>
</table>
	</td></tr>
</tbody></table>";

if ($menu=='T') {
	include_once '../includes/topmenu/footer.php';
} else {
	include_once '../includes/oldDesign/footer.php';
}

?>
