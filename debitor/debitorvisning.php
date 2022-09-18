<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// -----------debitor/debitorvisning.php--------lap 3.9.2--2020-06-23-----
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
// 
// Copyright (c) 2003-2020 saldi.dk aps
// ----------------------------------------------------------------------
// 2016.06.06 Tilføjet mulighed for at skjule lukkede debitorer Søg box11 / skjul_lukkede
// 2020.06.23	PHR - various changes related to 'kommission' 
// 20210701  LOE - translated with findtekst function some of these texts
	
@session_start();
$s_id=session_id();

if (isset($_GET['valg'])) $valg=($_GET['valg']);
else $valg="debitor";



$modulnr=6;
$css="../css/standard.css";

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
if ($valg=="debitor") $title=findtekst(112,$sprog_id);
else $title=findtekst(1129,$sprog_id);
$sort=trim(if_isset($_GET['sort']));

if ($popup) $returside="../includes/luk.php"; 
else $returside="$side.php";

$sektion=if_isset($_GET['sektion']);

if (isset($_POST) && $_POST) {
	if ($sektion=='3') {
		$dg_antal=if_isset($_POST['dg_antal']);
		$dg_id=if_isset($_POST['dg_id']);
		$dg_liste=if_isset($_POST['dg_liste']);
		$cat_antal=$_POST['cat_antal'];
		$cat_id=$_POST['cat_id'];
		$cat_liste=$_POST['cat_liste'];
		$box11=if_isset($_POST['skjul_lukkede']);

		$box1="";
		for ($x=0; $x<=$dg_antal; $x++) {
			if ($dg_liste[$x]) {
				($box1)?$box1.=chr(9).$dg_id[$x]:$box1=$dg_id[$x];
			}
		}
		$box2="";
		for ($x=0; $x<=$cat_antal; $x++) {
			if ($cat_liste[$x]) {
				($box2)?$box2.=chr(9).$cat_id[$x]:$box2=$cat_id[$x];
			}
		}
		$qtxt = "update grupper set box1='$box1',box2='$box2',box11='$box11',kode = '$valg' where art = 'DLV' and kodenr = '$bruger_id'";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	} elseif ($sektion=='4') {
		$vis_feltantal=if_isset($_POST['vis_feltantal']);
		$vis_linjeantal=if_isset($_POST['vis_linjeantal']);
		$vis_felt=if_isset($_POST['vis_felt']);
		$feltbredde=if_isset($_POST['feltbredde']);
		$justering=if_isset($_POST['justering']);
		$feltnavn=if_isset($_POST['feltnavn']);
		$select=if_isset($_POST['select']);
#	if (!isset($vis_felt[0])) $vis_felt[0]="";
		$box3='kontonr';
		$box4=$feltbredde[0]*1;
		$box5=$justering[0];
		$box6=db_escape_string($feltnavn[0]);
		if (!$vis_linjeantal) $vis_linjeantal=50; 
		$box7=$vis_linjeantal*1;
		$box8=$select[0];
		for ($x=1;$x<=$vis_feltantal;$x++) {
			if (!isset($vis_felt[$x])) $vis_felt[$x]="";
			$box3=$box3.chr(9).$vis_felt[$x];
			$feltbredde[$x]=$feltbredde[$x]*1;
			$box4=$box4.chr(9).$feltbredde[$x];
			$box5=$box5.chr(9).$justering[$x];
			$box6=$box6.chr(9).db_escape_string($feltnavn[$x]);
			$box8=$box8.chr(9).$select[$x];
		}
		
		$qtxt = "update grupper set box3='$box3',box4='$box4',box5='$box5',box6='$box6',box7='$box7',box8='$box8' ";
		$qtxt.= "where art = 'DLV' and kode='$valg' and kodenr = '$bruger_id'";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	}
}

print "<div align=\"center\">";
print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>"; #Tabel 1 ->
print "<tr><td height = \"25\" align=\"center\" valign=\"top\">";
print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"4\" cellpadding=\"0\"><tbody>"; #Tabel 1.1 ->
sektion_1();
print "</tbody></table>"; #  <- Tabel 1.1
print "	</td></tr><tr><td valign=\"top\"><table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" width=\"100%\" valign = \"top\"><tbody>"; # Tabel 1.2 -> 
print "</tbody></table></td></tr><tr></tr>"; # <- tabel 1.2
print "<tr><td width=100%><table border=0><tbody><tr><td>"; #tabel 1.3 ->
# sektion_2();
print "</td></tr></tbody></table></td></tr>"; # <- tabel 1.2
print "<tr><td width=\"100%\" valign=\"top\"><table border=\"1\" width=\"100%\"><tbody>"; #tabel 1.3 ->
print "<tr><td width=\"50%\" valign=\"top\"><table border=\"0\" width=\"100%\" height=\"100%\"><tbody>"; #tabel 1.3.1 ->
sektion_3();
print "</td></tr></tbody></table></td>"; # <- tabel 1.3.1
print "<td width=50% valign=\"top\"><table border=\"0\" width=\"100%\" height=\"100%\"><tbody>"; #tabel 1.3.2 ->
#print "<tr><td>sektion 4</td></tr>";
sektion_4();
print "</tbody></table></td></tr>"; # <- table 1.3.2
print "</tbody></table></td></tr>"; # <- table 1.3
print "</tbody></table>"; # <- table 1
print "</body></html>";



function sektion_1() {

global $valg;
global $sort;
global $title;
global $felter;	
	
print "<td width=\"10%\" align=center><div class=\"top_bund\"><a href=debitor.php?valg=$valg&sort=$sort accesskey=L>".findtekst(30,$sprog_id)."</a></div></td>
			<td width=\"80%\" align=center><div class=\"top_bund\">$title</a></div></td>
			<td width=\"10%\" align=center><div class=\"top_bund\"><br></div></td>
			 </tr>";
}

function sektion_2($valg,$sort,$title) {

global $valg;
global $sort;
global $side;
global $title;	
global $vis_felt;
global $feltbredde;
global $justering;
global $feltnavn;
global $vis_linjeantal;
global $vis_feltantal;
global $select;
global $bruger_id;

	$qtxt="select box3,box4,box5,box6,box7,box8 from grupper where art = 'DLV' and kode ='$valg' and kodenr = '$bruger_id'";
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
$vis_felt=explode(chr(9),$r['box3']);
$feltbredde=explode(chr(9),$r['box4']);
$justering=explode(chr(9),$r['box5']);
$feltnavn=explode(chr(9),$r['box6']);
$vis_linjeantal=$r['box7'];
$vis_feltantal=count($vis_felt)-1;
$select=explode(chr(9),$r['box8']);

print "<form name=sektion_2 action=debitorvisning.php?sort=$sort&side=$side&sektion=2 method=post>";
	print "<tr width=\"500px\"><td>".findtekst(1126,$sprog_id)." ".$valg."".findtekst(1128,$sprog_id)."";
	print "<td colspan=\"5\"><input type=text style=\"text-align:right\" size=2 name=vis_feltantal value=$vis_feltantal></td></tr>";
	print "<tr><td>".findtekst(1127,$sprog_id)." ".$valg."".findtekst(1128,$sprog_id)."</td>";
	print "<td colspan=\"5\"><input type=text style=\"text-align:right\" size=2 name=vis_linjeantal value=$vis_linjeantal></td>";
print "<td><input type=submit value=\"OK\" name=\"submit\"></td></tr>\n";
print "</form>";

}

function sektion_3() {

	global $brugernavn;
	global $bruger_id;
	global $side;
	global $sort;
	global $valg;
	global $sort;
	global $title;	
	global $vis_felt;
	global $feltbredde;
	global $justering;
	global $feltnavn;
	global $vis_linjeantal;
	global $vis_feltantal;
	global $select;
	
	print "<tr><td colspan=3>".findtekst(1121,$sprog_id)."</td></tr>";
	
	print "<tr><td colspan=3>".findtekst(1122,$sprog_id)."</td></tr>";
	print "<tr><td colspan=3>".findtekst(1123,$sprog_id)."</td></tr>";
#	print "<tr><td colspan=3><hr></td></tr>";
	
	$r = db_fetch_array(db_select("select id,box1,box2,box11 from grupper where art = 'DLV' and kode ='$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__));
	$dg_liste=explode(chr(9),$r['box1']);
	$cat_liste=explode(chr(9),$r['box2']);
	($r['box11'])?$skjul_lukkede='checked':$skjul_lukkede=NULL;
	
	print "<form name=sektion_3 action=debitorvisning.php?sort=$sort&valg=$valg&sektion=3 method=post>";
	print "<tr><td colspan=3><table border=1 width=100%><tbody>";
	print "<tr><td>".findtekst(1124,$sprog_id)."</td><td><input name=\"skjul_lukkede\" type=\"checkbox\" $skjul_lukkede></td></tr>";
	print "<tr><td width=50%><table border=0 width=100%><tbody>";
	print "<tr><td><b>".findtekst(1125,$sprog_id)."</b><br><hr></td></tr>";
	$q = db_select("select * from grupper where art = 'DG' order by beskrivelse",__FILE__ . " linje " . __LINE__);
	$x=-1;
	while ($r = db_fetch_array($q)) {
		$x++;
		if (in_array($r['id'],$dg_liste)) $tmp='checked';
		else $tmp='';
		print "<tr><td><input name=\"dg_liste[$x]\" type=\"checkbox\" $tmp>$r[beskrivelse]</td></tr>";
		print "<input type=hidden name=dg_id[$x] value=$r[id]>";
	}
	print "<input type=hidden name=dg_antal value=$x>";
	print "</tbody></table>";
	print "</td><td valign=top><table border=0 width=100%><tbody>";
	print "<tr><td><b>".findtekst(388,$sprog_id)."</b><br><hr></td></tr>";
	
	$r=db_fetch_array(db_select("select box1,box2,box9 from grupper where art='DebInfo'",__FILE__ . " linje " . __LINE__));
	$cat_id=explode(chr(9),$r['box1']);
	$cat_beskrivelse=explode(chr(9),$r['box2']);
	$cat_antal=count($cat_id);

	for ($x=0;$x<$cat_antal;$x++) {
		if (in_array($cat_id[$x],$cat_liste)) $tmp='checked';
		else $tmp='';
		print "<tr><td><input name=\"cat_liste[$x]\" type=\"checkbox\" $tmp>$cat_beskrivelse[$x]</td></tr>";
		print "<input type=hidden name=cat_id[$x] value=$cat_id[$x]>";
	}
	print "<input type=hidden name=cat_antal value=$x>";
	
	print "</td></tr></tbody></table>";
	print "</td></tr></tbody></table></td> ";
#	print "<tr><td colspan=3><hr></td></tr>\n";
	print "<tr><td colspan=3 align = center><input type=submit accesskey=\"a\" value=\"OK\" name=\"submit\"></td></tr>\n";
	
	print "</form>";
}

function sektion_4() {

	global $valg;
	global $bruger_id;
	global $feltnavn;
	global $feltbredde;
	global $justering;
	global $select;
	global $justering;
	global $vis_feltantal;
	global $vis_felt;
	global $felter;
	
	$r = db_fetch_array(db_select("select box3,box4,box5,box6,box7,box8 from grupper where art = 'DLV' and kode ='$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__));
	$vis_felt=explode(chr(9),$r['box3']);
	$feltbredde=explode(chr(9),$r['box4']);
	$justering=explode(chr(9),$r['box5']);
	$feltnavn=explode(chr(9),$r['box6']);
	$vis_linjeantal=$r['box7'];
	$vis_feltantal=count($vis_felt)-1;
	$select=explode(chr(9),$r['box8']);
	
	print "<form name=sektion_4 action=debitorvisning.php?sort=$sort&valg=$valg&sektion=4 method=post>";
	
	print "<tr width=\"500px\"><td>".findtekst(1126,$sprog_id)." ".$valg."".findtekst(1128,$sprog_id)."</td><td colspan=\"5\"><input type=text style=\"text-align:right\" size=2 name=vis_feltantal value=$vis_feltantal></td></tr>";
	print "<tr><td>".findtekst(1127,$sprog_id)." ".$valg."".findtekst(1128,$sprog_id)."</td><td colspan=\"5\"><input type=text style=\"text-align:right\" size=2 name=vis_linjeantal value=$vis_linjeantal></td><tr>";
	print "<tr><td colspan=\"5\"><hr></td><tr>";

	$felter=array("firmanavn","addr1","addr2","postnr","bynavn","land","kontakt","tlf","fax","email","web","bank_navn","bank_reg","bank_konto","notes","rabat","momskonto","kreditmax","betalingsbet","betalingsdage","kontonr","cvrnr","ean","institution","art","gruppe","kontoansvarlig","oprettet","kontaktet","kontaktes","bank_fi","swift","erh","mailfakt","pbs","pbs_nr","pbs_date","felt_1","felt_2","felt_3","felt_4","felt_5","vis_lev_addr","kontotype","fornavn","efternavn","lev_firmanavn","lev_fornavn","lev_efternavn","lev_addr1","lev_addr2","lev_postnr","lev_bynavn","lev_land","lev_kontakt","lev_tlf","lev_email","lukket","status","invoiced");
	$fieldNames=array(findtekst(360,$sprog_id),"addr1","addr2","postnr","bynavn","land","kontakt","tlf","fax","email","web","bank_navn", "bank_reg","bank_konto","notes","rabat","momskonto","kreditmax","betalingsbet","betalingsdage","kontonr","cvrnr","ean", "institution","art","gruppe","kontoansvarlig","oprettet","kontaktet","kontaktes","bank_fi","swift","erh","mailfakt","pbs", "pbs_nr","pbs_date","felt_1","felt_2","felt_3","felt_4","felt_5","vis_lev_addr","kontotype","fornavn","efternavn","lev_firmanavn", "lev_fornavn","lev_efternavn","lev_addr1","lev_addr2","lev_postnr","lev_bynavn","lev_land","lev_kontakt","lev_tlf","lev_email", "lukket","status",findtekst(875,$sprog_id));

#	sort($felter);
#			for ($y=0;$y<count($felter);$y++) {
#			 print "$felter[$y]<br>";
#		}

	print "<tr><td colspan=\"5\">".findtekst(1117,$sprog_id)."</td></tr>"; #20210701
	print "<tr><td colspan=\"5\">".findtekst(1118,$sprog_id)."</td></tr>";
	print "<tr><td colspan=\"5\"><hr></td></tr>";
	print "<tr><td colspan=\"1\"><b>Felt</b></td><td align=\"center\"><b>".findtekst(539,$sprog_id)."</b></td><td align=\"center\"><b>".findtekst(540,$sprog_id)."</b></td><td align=\"center\"><b>".findtekst(541,$sprog_id)."</b></td><td align=\"center\" title=\"Angiver om feltets v&aelig;rdi skal kunne v&aelig;lges fra en liste\"><b>".findtekst(1119,$sprog_id)."</b></td></tr>";
	if (!$feltnavn[0]) $feltnavn[0]="Kontonr";
	print "<tr><td colspan=\"1\">".findtekst(804,$sprog_id)."</td>";
	print "<td align=\"center\"><input name=feltnavn[0] size=20 value=$feltnavn[0]></td>";
	print "<td align=\"center\"><input name=feltbredde[0] style=\"text-align:right\" size=2 value=$feltbredde[0]></td>";
	print "<td align=\"center\"><SELECT NAME=justering[0]>";
	if ($justering[0]) print "<option value=\"".$justering[0]."\">$justering[0]</option>";
	if ($justering[0] != "left") print "<option value=\"left\">left</option>"; 
	if ($justering[0] != "center") print "<option value=\"center\">center</option>"; 
	if ($justering[0] != "right") print "<option value=\"right\">right</option>"; 
	print "</SELECT></td>";
	($select[0])?$select[0]='checked':$select[0]='';
	print "<td align=\"center\"><input type=\"checkbox\" name=\"select[0]\" $select[0]></td>"; 
	print "</tr>\n";
	for ($x=1;$x<=$vis_feltantal;$x++) {
	if (!$feltnavn[$x]) $feltnavn[$x]=$vis_felt[$x];
		print "<tr><td colspan=\"1\"><SELECT NAME=vis_felt[$x]>";
		for ($y=0;$y<count($felter);$y++) {
			if ($felter[$y]==$vis_felt[$x]) print "<option value='$felter[$y]'>$fieldNames[$y]</option>";
		}
		for ($y=0;$y<count($felter);$y++) {
			if ($felter[$y]!=$vis_felt[$x]) print "<option value='$felter[$y]'>$fieldNames[$y]</option>";
		}
		print "</SELECT></td>";
		print "<td align=\"center\"><input name=feltnavn[$x] size=20 value='$feltnavn[$x]'></td>";
		print "<td align=\"center\"><input name=feltbredde[$x] size=2 style=\"text-align:right\" value=$feltbredde[$x]></td>";
		print "<td align=\"center\"><SELECT NAME=justering[$x]>";
		if ($justering[$x]) print "<option value=\"$justering[$x]\">$justering[$x]</option>";
		if ($justering[$x] != "left") print "<option value=\"left\">left</option>"; 
		if ($justering[$x] != "center") print "<option value=\"center\">center</option>"; 
		if ($justering[$x] != "right") print "<option value=\"right\">right</option>"; 
		print "</SELECT></td>";
		($select[$x])?$select[$x]='checked':$select[$x]='';
		print "<td align=\"center\"><input type=\"checkbox\" name=\"select[$x]\" $select[$x]></td>"; 
		print "</tr>\n";
	}
	print "<tr><td colspan=6><hr></td></tr>\n";
	print "<tr><td colspan=6 align = center><input type=submit accesskey=\"a\" value=\"OK\" name=\"submit\"></td></tr>\n";
	print "</form>";
}

?>
