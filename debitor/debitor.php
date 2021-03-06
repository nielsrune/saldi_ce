<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ---------------debitor/debitor.php---lap 3.8.2------2018-09-20----
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af The Free Software Foundation; enten i version 2
// af denne licens eller en senere version efter eget valg.
// Fra og med version 3.2.2 dog under iagttagelse af følgende:
// 
// Programmet må ikke uden forudgående skriftlig aftale anvendes
// i konkurrence med saldi.dk aps eller anden rettighedshaver til programmet.
// 
// Programmet er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
// 
// En dansk oversaettelse af licensen kan laeses her:
// http://www.saldi.dk/dok/GNU_GPL_v2.html
//
// Copyright (c) 2003-2019 saldi.dk aps
// ----------------------------------------------------------------------
// 2013.02.10 Break ændret til break 1
// 2016.02.18 Udvælg funger nu også hvis debitor er med i flere kategorier. Søg 20160218
// 2016.06.06 Tilføjet mulighed for at skjule lukkede debitorer Søg box11 / skjul_lukkede
// 2018.12.05 Definering af variabler.
// 2018.12.17 msc Rettet design til
// 2019.01.07 MSC Rettet topmenu design til
// 2019.02.13 MSC - Rettet topmenu design til
// 2019.09.20 PHR - All search fiels was set to '0' if not set. Chanced to NULL

#ob_start();
@session_start();
$s_id=session_id();

$adresseantal=$check_all=$hrefslut=$javascript=$kontoid=$linjebg=$linjetext=$nextpil=$ny_sort=$prepil=$tidspkt=$understreg=$udv2=NULL;
$find=$dg_id=$dg_navn=$selectfelter=array();

print "
<script LANGUAGE=\"JavaScript\">
<!--
function MasseFakt(tekst)
{
	var agree = confirm(tekst);
	if (agree)
		return true ;
	else
    return false ;
}
// -->
</script>
";
$css="../css/standard.css";
$modulnr=6;
$title="Debitorliste";
$firmanavn=NULL; 

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/udvaelg.php");
	
$id = if_isset($_GET['id']);
$returside=if_isset($_GET['returside']);
$valg= strtolower(if_isset($_GET['valg']));
$sort = if_isset($_GET['sort']);
$start = if_isset($_GET['start']);
$nysort = if_isset($_GET['nysort']);

if (!$valg) $valg="debitor";

$sort=str_replace("adresser.","",$sort);
if ($sort && $nysort==$sort) $sort=$sort." desc";
elseif ($nysort) $sort=$nysort;
$r=db_fetch_array(db_select("select box7 from grupper where art = 'DIV' and kodenr = '2'",__FILE__ . " linje " . __LINE__));
$jobkort=$r['box7'];

if (!$r=db_fetch_array(db_select("select id from grupper where art = 'DLV' and kode='$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__))) {
#	db_modify("update grupper set box2='$returside' where id='$r[id]'",__FILE__ . " linje " . __LINE__);
#} else { 
	if ($valg=="debitor") {
		$box3="kontonr".chr(9)."firmanavn".chr(9)."addr1".chr(9)."addr2".chr(9)."postnr".chr(9)."bynavn".chr(9)."kontakt".chr(9)."tlf".chr(9)."kontoansvarlig";
		$box5="right".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left";
		$box4="5".chr(9)."35".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10";
		$box6="Kontonr".chr(9)."Firmanavn".chr(9)."Adresse".chr(9)."Adresse 2".chr(9)."Postnr".chr(9)."By".chr(9)."Kontakt".chr(9)."Telefon".chr(9)."S&aelig;lger";
	} else {
		$box3="kontonr".chr(9)."firmanavn".chr(9)."addr1".chr(9)."addr2".chr(9)."postnr".chr(9)."bynavn".chr(9)."kontakt".chr(9)."tlf".chr(9)."kontoansvarlig";
		$box5="right".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left";
		$box4="5".chr(9)."35".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10";
		$box6="Kontonr".chr(9)."Firmanavn".chr(9)."Adresse".chr(9)."Adresse 2".chr(9)."Postnr".chr(9)."By".chr(9)."Kontakt".chr(9)."Telefon".chr(9)."S&aelig;lger";
	}
	db_modify("insert into grupper(beskrivelse,kode,kodenr,art,box3,box4,box5,box6,box7)values('debitorlistevisning','$valg','$bruger_id','DLV','$box3','$box4','$box5','$box6','100')",__FILE__ . " linje " . __LINE__);
} else {
	$qtxt="select box1,box2,box7,box9,box10,box11 from grupper where art = 'DLV' and kode='$valg' and kodenr = '$bruger_id'";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)); 
	$dg_liste=explode(chr(9),$r['box1']);
	$cat_liste=explode(chr(9),$r['box2']);
	$skjul_lukkede=$r['box11'];
	$linjeantal=$r['box7'];
	if (!$sort) $sort=$r['box9'];
	$find=explode("\n",$r['box10']);
}
	
if ($popup) $returside= "../includes/luk.php";
else $returside= "../index/menu.php";

db_modify("update grupper set box9='$sort' where art = 'DLV' and kode='$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__);
		
$tidspkt=date("U");
 
if ($submit=if_isset($_POST['submit'])) {
	$find=if_isset($_POST['find']);
	$valg=if_isset($_POST['valg']);
	$sort = if_isset($_POST['sort']);
	$nysort = if_isset($_POST['nysort']);
	$firma=if_isset($_POST['firma']);
}


if (!$valg) $valg = "debitor";
if (!$sort) $sort = "firmanavn";

$sort=str_replace("adresser.","",$sort);
$sortering=$sort;

if ($menu=='T') {
	include_once '../includes/top_header.php';
	include_once '../includes/top_menu.php';
	if ($valg=='debitor') {
	print "<div id=\"header\"> 
		<div class=\"headerbtnLft\"></div>
		<span class=\"headerTxt\">Konti</span>";     
	print "<div class=\"headerbtnRght\"><a href=\"debitorkort.php?returside=debitor.php\" class=\"button green small right\">Ny</a></div>";
	} if ($valg=='historik') {
	print "<div id=\"header\"> 
		<div class=\"headerbtnLft\"></div>
		<span class=\"headerTxt\">Historik</span>";     
	print "<div class=\"headerbtnRght\"></div>";
	}
	print "</div><!-- end of header -->
	<div class=\"maincontentLargeHolder\">\n";
	
	#	$leftbutton="<a title=\"Klik her for at komme til startsiden\" href=\"../index/menu.php\" accesskey=\"L\">LUK</a>";
	#	$rightbutton="<a href=\"#\">Ordremenu</a>\t";
	#	if ($valg!='ordrer') $rightbutton.="\t<a href='ordreliste.php?valg=ordrer&konto_id=$konto_id&returside=$returside'>&nbsp;Ordreliste&nbsp;</a>";
	#	if ($valg!='faktura') $rightbutton.="\t<a href='ordreliste.php?valg=faktura&konto_id=$konto_id&returside=$returside'>&nbsp;Fakturaliste&nbsp;</a>";
	#	$rightbutton.="\t<a href=\"../debitor/ordre.php?returside=../debitor/ordreliste.php?konto_id=$konto_id\">Ny ordre/faktura</a>";
	#	$rightbutton.="\t<a accesskey=V href=ordrevisning.php?valg=$valg>Visning</a>";
	#	include("../includes/topmenu.php");
	} else {
print "<table width=100% height=100% border=0 cellspacing=0 cellpadding=0><tbody>\n";
print "<tr><td height = 25 align=center valign=top>";
print "<table width=100% align=center border=0 cellspacing=2 cellpadding=0><tbody><td width=10% $top_bund>\n";
print "<a href=$returside accesskey=L>Luk</a></td>";
print "<td width=80% $top_bund align=center><table border=0 cellspacing=2 cellpadding=0><tbody>\n";

if ($valg=='debitor') print "<td width = 20% align=center $knap_ind>&nbsp;Debitorer&nbsp;</td>";
else print "<td width = 20% align=center><a href='debitor.php?valg=debitor&returside=$returside'>&nbsp;Debitorer&nbsp;</a></td>";
if ($valg=='historik') print "<td width = 20% align=center $knap_ind>&nbsp;Historik&nbsp;</td>";
else print "<td width = 20% align=center><a href='debitor.php?valg=historik&returside=$returside'>&nbsp;Historik&nbsp;</a></td>";
if ($jobkort)	print "<td width = 20% align=center><a href=jobliste.php title =\"Klik her for at skifte til joblisten\">".findtekst(38,$sprog_id)."</a></td>";
print "</tbody></table></td>\n";
print "<td width=5% $top_bund><a accesskey=V href=debitorvisning.php?valg=$valg>Visning</a></td>\n";
#if ($popup) {
#		print "<td width=5% $top_bund onClick=\"javascript:debitor=window.open('debitorkort.php?returside=debitor.php','ordre','scrollbars=1,resizable=1');ordre.focus();\"><a accesskey=N href=debitor.php?sort=$sort>Ny</a></td>\n";
#	} else {
		print "<td width=5%  $top_bund><a href=debitorkort.php?returside=debitor.php>Ny</a></td>\n";
#	}
print "</td></tr>\n";
#print "<tr><td></td><td align=center><table border=1	cellspacing=0 cellpadding=0><tbody>\n";
#print "<td width = 20%$top_bund align=center><a href=debitor.php?valg=tilbud accesskey=T>Tilbud</a></td>";
#print "<td width = 20% bgcolor=$bgcolor5 align=center> Ordrer</td>";
#print "<td width = 20% bgcolor=$bgcolor5 align=center> Faktura</td>";
#print "</tbody></table></td><td></td</tr>\n";

print "</tbody></table>";
print " </td></tr>\n<tr><td align=\"center\" valign=\"top\" width=\"100%\">";

	}

$r = db_fetch_array(db_select("select box3,box4,box5,box6,box8,box11 from grupper where art = 'DLV' and kodenr = '$bruger_id' and kode='$valg'",__FILE__ . " linje " . __LINE__));
$vis_felt=explode(chr(9),$r['box3']);
$feltbredde=explode(chr(9),$r['box4']);
$justering=explode(chr(9),$r['box5']);
$feltnavn=explode(chr(9),$r['box6']);
$vis_feltantal=count($vis_felt);
$select=explode(chr(9),$r['box8']);

$y=0;
for ($x=0;$x<=$vis_feltantal;$x++) {
	if (isset($select[$x]) && isset($vis_felt[$x]) && $select[$x] && $vis_felt[$x]) {
		$selectfelter[$y]=$vis_felt[$x];
		$y++;
	}
}
$numfelter=array("rabat","momskonto","kreditmax","betalingsdage","gruppe","kontoansvarlig");
####################################################################################
$udvaelg=NULL;
$tmp=trim($find[0]);
for ($x=1;$x<$vis_feltantal;$x++) {
	if (isset($find[$x])) $tmp=$tmp."\n".trim($find[$x]);
}
$tmp=addslashes($tmp);
db_modify("update grupper set box10='$tmp' where art = 'DLV' and kode='$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__);

if ($skjul_lukkede) $udvaelg = " and lukket != 'on'";
for ($x=0;$x<$vis_feltantal;$x++) {
	if (isset($find[$x])) {
	$find[$x]=addslashes(trim($find[$x]));
	$tmp=$vis_felt[$x];
		if ($tmp) {
	if ($find[$x] && !in_array($tmp,$numfelter)) {
				$tmp2="adresser.".$tmp;
				$udvaelg.=udvaelg($find[$x],$tmp2, '');
	} elseif ($find[$x]||$find[$x]=="0") {
				$tmp2="adresser.".$tmp;
				$udvaelg.=udvaelg($find[$x],$tmp2, 'NR');
			}
		}
	}
}

if (count($dg_liste)) {
	$x=0;
	$q=db_select("select * from grupper where art = 'DG' order by beskrivelse",__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$x++;
		$dg_id[$x]=$r['id'];
		$dg_kodenr[$x]=$r['kodenr']*1;
		$dg_navn[$x]=$r['beskrivelse'];
	}
	$dg_antal=$x;
}

if (count($cat_liste)) {
	$r=db_fetch_array(db_select("select box1,box2 from grupper where art='DebInfo'",__FILE__ . " linje " . __LINE__));
	$cat_id=explode(chr(9),$r['box1']);
	$cat_beskrivelse=explode(chr(9),$r['box2']);
	$cat_antal=count($cat_id);
}

$sortering="adresser.".$sortering;

$ialt=0;
$lnr=0;
if (!$linjeantal) $linjeantal=100;
$slut=$start+$linjeantal;
$adresserantal=0;

$r=db_fetch_array(db_select("select count(id) as antal from adresser where art = 'D' $udvaelg",__FILE__ . " linje " . __LINE__));
$antal=$r['antal'];
if ($menu=='T'){
	print "<table class='dataTable' cellpadding=1 cellspacing=1 border=0 valign=top width=100%><tbody>\n<tr>";
} else {
print "<table cellpadding=1 cellspacing=1 border=0 valign=top width=100%><tbody>\n<tr>";
}
if ($start>0) {
	$prepil=$start-$linjeantal;
	if ($prepil<0) $prepil=0;
	print "<td><a href=debitor.php?start=$prepil&valg=$valg><img src=../ikoner/left.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td>";
} else {
	print "<td>";
	if (file_exists("rotary_addrsync.php")) print "<a href=\"rotary_addrsync.php\" target=\"blank\" title=\"Klik her for at synkronisere medlemsinfo\">!</a>";
	print "</td>";
}
for ($x=0;$x<$vis_feltantal;$x++) {
	if ($feltbredde[$x]) $width="width=$feltbredde[$x]";
	else $width="";
	print "<td align=$justering[$x] $width><b><a href='debitor.php?nysort=$vis_felt[$x]&sort=$sort&valg=$valg'>$feltnavn[$x]</b></td>\n";
}
if ($antal>$slut && !$dg_liste[0] && !$cat_liste[0]) {
	$nextpil=$start+$linjeantal;
	print "<td align=right><a href=debitor.php?start=$nextpil&valg=$valg><img src=../ikoner/right.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td><tr>";
}
print "</tr>\n";
if ($dg_antal || $cat_antal) $linjeantal=0;
#################################### Sogefelter ##########################################


print "<form name=debitorliste action=debitor.php method=post>\n";
print "<input type=hidden name=valg value=$valg>\n";
print "<input type=hidden name=sort value='$ny_sort'>\n";
print "<input type=hidden name=nysort value='$sort'>\n";
print "<input type=hidden name=kontoid value=$kontoid>\n";


print "<tr><td></td>"; #giver plase til venstrepil v. flere sider
if (!$start) {
	for ($x=0;$x<$vis_feltantal;$x++) {
		$span=''; 
		if (!isset($feltbredde[$x])) $feltbredde[$x]=0;
		if (!isset($justering[$x])) $justering[$x]=0;
		if (!isset($find[$x])) $find[$x]=NULL;
		print "<td align=$justering[$x]><span title= '$span'>";
		if ($vis_felt[$x]=="kontoansvarlig") {
			$ansat_id=array();$ansat_init=array();
			$y=0;
			$q=db_select("select distinct(ansatte.id) as ansat_id,ansatte.initialer as initialer from ansatte,adresser where adresser.art='S' and ansatte.konto_id=adresser.id order by ansatte.initialer",__FILE__ . " linje " . __LINE__);
			while ($r=db_fetch_array($q)) {
				$y++;
				$ansat_id[$y]=$r['ansat_id'];
				$ansat_init[$y]=$r['initialer'];
			}
			$ansatantal=$y;
			if (in_array($vis_felt[$x],$selectfelter)) {
				print "<SELECT NAME=\"find[$x]\">";
				if (!$find[$x]) print "<option value=\"\"></option>";
				for ($y=1;$y<=$ansatantal;$y++) if ($ansat_init[$y] && $find[$x]==$ansat_id[$y]) print "<option value=\"$ansat_id[$y]\">".stripslashes($ansat_init[$y])."</option>";
				if ($find[$x]) print "<option value=\"\"></option>";
				for ($y=1;$y<=$ansatantal;$y++) if ($ansat_init[$y] && $find[$x]!=$ansat_id[$y]) print "<option value=\"$ansat_id[$y]\">".stripslashes($ansat_init[$y])."</option>";
				print "</SELECT></td>\b";
			}
			#			print "<input class=\"inputbox\" type=text readonly=$readonly size=$feltbredde[$x] style=\"text-align:$justering[$x]\" name=find[$x] value=\"$r[tmp]\">";
		} elseif ($vis_felt[$x]=="status") {
			$status_id=array();$status_init=array();
			$r=db_fetch_array(db_select("select box3,box4 from grupper where art='DebInfo'",__FILE__ . " linje " . __LINE__));
			$status_id=explode(chr(9),$r['box3']);
			$status_beskrivelse=explode(chr(9),$r['box4']);
			$status_antal=count($status_id);
			if (in_array($vis_felt[$x],$selectfelter)) {
				print "<SELECT NAME=\"find[$x]\">";
				if (!$find[$x]) print "<option value=\"\"></option>";
				for ($y=0;$y<$status_antal;$y++) {
					if ($status_beskrivelse[$y] && $find[$x]==$status_id[$y]) print "<option value=\"$status_id[$y]\">".stripslashes($status_beskrivelse[$y])."</option>";
				}
				if ($find[$x]) print "<option value=\"\"></option>";
				for ($y=0;$y<$status_antal;$y++) {
					if ($status_beskrivelse[$y] && $find[$x]!=$status_id[$y]) print "<option value=\"$status_id[$y]\">".stripslashes($status_beskrivelse[$y])."</option>";
				}
				print "</SELECT></td>\n";
			}
			#			print "<input class=\"inputbox\" type=text readonly=$readonly size=$feltbredde[$x] style=\"text-align:$justering[$x]\" name=find[$x] value=\"$r[tmp]\">";
		} elseif (in_array($vis_felt[$x],$selectfelter)) {
			$tmp=$vis_felt[$x];
			print "<SELECT NAME=\"find[$x]\">";
			$q=db_select("select distinct($tmp) from adresser where art = 'D'");
			print "<option>".stripslashes($find[$x])."</option>";
			if ($find[$x]) print "<option></option>";
			while ($r=db_fetch_array($q)) {
				print "<option>$r[$tmp]</option>";
			}
			print "</SELECT></td>\n";			
		} else print "<input class=\"inputbox\" type='text' size='$feltbredde[$x]' style='text-align:$justering[$x]' name='find[$x]' value=\"$find[$x]\">";
	}
	print "</td>\n";  
print "<td><input class='button blue small' type=submit value=\"OK\" name=\"submit\"></td>\n";
print "</form></tr>\n<td></td>\n";
}
######################################################################################################################
$udv1=$udvaelg;
$colspan=$vis_feltantal+1;
$dgcount=count($dg_liste);
(!$dgcount)?$dgcount=1:NULL; 
for($i=0;$i<$dgcount;$i++) {
	if ($dg_liste[$i]) {
		for($i2=0;$i2<=$dg_antal;$i2++	) {
			if($dg_liste[$i]==$dg_id[$i2]) {
				if (!$start && !$lnr) {
#					print "<tr><td colspan=\"$colspan\"><hr></td>";
					$tmp=$start+$linjeantal;
				}
				if (!$cat_liste[0]) {
					print "<tr><td></td><td colspan=\"2\"><b>$dg_navn[$i2]</b></td></tr>\n";
					print "<tr><td colspan=\"$colspan\"><hr></td>";
				}
				$udv1=$udvaelg." and gruppe=$dg_kodenr[$i2]";	
				break 1;
			} 
		}	
	}
	$catcount=count($cat_liste);
	(!$catcount)?$catcount=1:NULL; 
	for($i3=0;$i3<$catcount;$i3++) {
		if ($cat_liste[$i3]) {
			for($i4=0;$i4<=$cat_antal;$i4++	) {
				if($cat_liste[$i3]==$cat_id[$i4]) {
					if (!$start && !$lnr) {
#						print "<td colspan=\"$colspan\"><b>$cat_beskrivelse[$i4]</b></td></tr>\n";
#						print "<tr><td colspan=\"$colspan\"><hr></td>";
						$tmp=$start+$linjeantal;
#						if ($antal>$slut) print "<td align=center><a href=debitor.php?start=$tmp&valg=$valg><img src=../ikoner/right.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td><tr>";
					}
					print "<tr><td colspan=\"$colspan\"><hr></td>";
					if ($dg_navn[$i2]) $tmp="<td colspan=\"2\"><b>$dg_navn[$i2]</b></td>";
					else $tmp=""; 
					print "<tr><td></td>$tmp<td colspan=\"2\"><b>$cat_beskrivelse[$i4]</b></td></tr>\n";
					print "<tr><td colspan=\"$colspan\"><hr></td>";
					$udv2=$udv1." and (kategori = '$cat_id[$i4]' or kategori LIKE '$cat_id[$i4]".chr(9)."%' ";
					$udv2.="or kategori LIKE '%".chr(9)."$cat_id[$i4]' or kategori LIKE '%".chr(9)."$cat_id[$i4]".chr(9)."%')";	#20160218
					break 1;
				} 
			}	
		}
	
	if (!$udv2) $udv2=$udv1;	
	if (!$udv2) $udv2=$udvaelg;	

	$query = db_select("select * from adresser where art = 'D' $udv2 order by $sortering",__FILE__ . " linje " . __LINE__);
	while ($row=db_fetch_array($query)) {
		$debitorkort="debitorkort".$row['id'];
		$lnr++;
		if(($lnr>=$start && $lnr<$slut) || $udv2) { 
			$adresseantal++;

#			if ($row['hvem']==$brugernavn) {
#				if ($popup) {
#					$javascript="onClick=\"javascript:".$valg."kort=window.open('".$valg."kort.php?tjek=$row[id]&id=$row[id]&returside=debitor.php','$debitorkort','scrollbars=1,resizable=1');$debitorkort.focus();\" onMouseOver=\"this.style.cursor = 'pointer'\" ";
#					$understreg='<span style="text-decoration: underline;">';
#					$hrefslut="";
#				} else {
					$javascript="";
	  			$understreg="<a href=".$valg."kort.php?tjek=$row[id]&id=$row[id]&returside=debitor.php>";
					$hrefslut="</a>";
#				}
				$linjetext="";
#			}	else {
#				$javascript="onClick=\"javascript:$debitorkort.focus();\"";
#				$understreg='';
#				$linjetext="<span title= 'Kortet er l&aring;st af $row[hvem]'>";
#			}

#			$javascript="onClick=\"javascript:$debitorkort.focus();\"";
#				$understreg='';
#				$linjetext="<span title= 'Kortet er l&aring;st af $row[hvem]'>";
			if ($linjebg!=$bgcolor) {$linjebg=$bgcolor; $color='#000000';}
			else {$linjebg=$bgcolor5; $color='#000000';}
			print "<tr bgcolor=\"$linjebg\"><td bgcolor=$bgcolor></td>\n";
			print "<td align=$justering[0] $javascript> $linjetext $understreg $row[kontonr]$hrefslut</span><br></td>\n";
			for ($x=1;$x<$vis_feltantal;$x++) {
				print "<td align=$justering[$x]>";
				$tmp=$vis_felt[$x];
				if ($vis_felt[$x]=='kontoansvarlig') {
					for ($y=1;$y<=$ansatantal;$y++) {
						if ($ansat_id[$y]==$row[$tmp]) print stripslashes($ansat_init[$y]);
					}
				} elseif ($vis_felt[$x]=='status') {
					for ($y=0;$y<=$status_antal;$y++) {
						if ($row[$tmp] && $status_id[$y]==$row[$tmp]) print stripslashes($status_beskrivelse[$y]);
					}
				} else print $row[$tmp];
				print "</td>"; 
			}
			print "<input type=hidden name=adresse_id[$adresseantal] value=$row[id]>";
#			$colspan=$vis_feltantal+2;

#		if ($r=db_fetch_array(db_select("select id from grupper where art = 'DLV' and kode = '$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__))) {
#			db_modify("update grupper set box1='$debitorliste' where id='$r[id]'",__FILE__ . " linje " . __LINE__);
#		} 
	}
	}}}
#print "<tr><td colspan=$colspan><hr></td></tr>\n";
#$cols--;

print "<tr>";
if ($prepil || $prepil=='0')	print "<td colspan=$colspan><a href=debitor.php?start=$prepil&valg=$valg><img src=../ikoner/left.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td>\n";
else print "<td colspan=$colspan><br></td>\n";
if ($nextpil) print "<td align=right><a href=debitor.php?start=$nextpil&valg=$valg><img src=../ikoner/right.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td></tr>\n";
else print "<td></td>";
print "</tr>\n";
$colspan++;
print "<tr><td colspan=$colspan width=100%><hr></td></tr>\n";
#print "<table border=0 width=100%><tbody>";

#print "</tbody></table></td>";
#print "<tr><td colspan=$colspan><hr></td></tr>\n";


?>
</tbody>
</table>
	</td></tr>
</tbody></table>

</body></html>
