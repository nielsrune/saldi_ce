<?php
// -----------debitor/historikkort.php-----patch 4.0.8 ----2023-07-12----
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
// 20190213 MSC - Rettet isset fejl og db fejl + rettet topmenu design til
// 20190215 MSC - Rettet topmenu design
// 20210728 LOE - Updated some texts with translated ones 
// 20220719 MSC - Implementing new design

@session_start();
$s_id=session_id();

?>
<script LANGUAGE="JavaScript">
<!--
function Slet()
{
var agree=confirm("Slet handling?");
if (agree)
        return true ;
else
        return false ;
}
// -->
</script>
<?php

$modulnr=6;
$title="Historik";
$css="../css/standard.css";

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/topline_settings.php");

if (!isset ($_GET['konto_id'])) $_GET['konto_id'] = NULL;
if (!isset ($historik_id)) $historik_id = NULL;
if (!isset ($_GET['handling'])) $_GET['handling'] = NULL;
if (!isset ($_GET['ordre_id'])) $_GET['ordre_id'] = NULL;
if (!isset ($_GET['fokus'])) $_GET['fokus'] = NULL;
if (!isset ($_POST['submit'])) $_POST['submit'] = NULL;
if (!isset ($kontaktet)) $kontaktet = NULL;
if (!isset ($oprettet)) $oprettet = NULL;
if (!isset ($kontaktes)) $kontaktes = 0;
if (!isset ($ansat_id)) $ansat_id = 0;
if (!isset ($ansat)) $ansat = 0;
if (!isset ($kontakt)) $kontakt = 0;
if (!isset ($_GET['id'])) $_GET['id'] = 0;
if (!isset ($_POST['historik_id'])) $_POST['historik_id'] = NULL;
if (!isset ($_POST['oprettet'])) $_POST['oprettet'] = NULL;
if (!isset ($r1['navn'])) $r1['navn'] = NULL;
if (!isset ($vis_bilag)) $vis_bilag = NULL;

print "<script language=\"javascript\" type=\"text/javascript\" src=\"../javascript/confirmclose.js\"></script>";

$id = $_GET['id']*1;
if ($_GET['konto_id']) $id = $_GET['konto_id'];
if (isset ($_GET['historik_id'])) $historik_id=$_GET['historik_id'];
$handling=$_GET['handling'];

if ($handling=='slet') {
	db_modify("delete from historik where id = $historik_id",__FILE__ . " linje " . __LINE__);
	$historik_id='';
}
 if (isset($_GET['returside'])) {
 	$returside= $_GET['returside'];
 	$ordre_id = $_GET['ordre_id'];
 	$fokus = $_GET['fokus'];
} else {
	if ($popup) $returside="../includes/luk.php";
	else $returside="historik.php";
}
$luk=if_isset($_GET['luk']);

if($luk) {
	if ($r=db_fetch_array(db_select("select * from navigator where bruger_id='$bruger_id' and session_id='$s_id' and side='historikkort.php'",__FILE__ . " linje " . __LINE__))) {
		db_modify("delete from navigator where bruger_id='$bruger_id' and session_id='$s_id' and side='historikkort.php'",__FILE__ . " linje " . __LINE__);
		print "<meta http-equiv=\"refresh\" content=\"0;URL=$r[returside]?valg=historik&konto_id=$r[konto_id]&ordre_id=$r[ordre_id]\">";
	} else print "<meta http-equiv=\"refresh\" content=\"0;URL=debitorliste.php\">";
	exit;
} elseif ($returside) db_modify("insert into navigator(bruger_id,session_id,side,returside,konto_id) values ('$bruger_id','$s_id','historikkort.php','$returside','$id')",__FILE__ . " linje " . __LINE__);


if ($_POST['submit']){
	$submit=trim($_POST['submit']);
	$id=$_POST['id'];
	$egen_id=$_POST['egen_id'];
	$historik_id=$_POST['historik_id'];
	$ansat=$_POST['ansat'];
	$kontakt=$_POST['kontakt'];
	if ($_POST['oprettet']) $oprettet=$_POST['oprettet'];
	if ($_POST['kontaktet'])$kontaktet=usdate($_POST['kontaktet']);
	else $kontaktet=date("Y-m-d");
	$kontaktes=$_POST['kontaktes'];
	if ($kontaktes) $kontaktes=usdate($kontaktes);
	else $kontaktes=NULL;
	$notat=addslashes(trim($_POST['note']));

	if ($kontaktes || $notat) {
		$r = db_fetch_array(db_select("select id from ansatte where konto_id = '$egen_id' and navn = '$ansat'",__FILE__ . " linje " . __LINE__));
		$ansat_id=$r['id']*1;
		$r = db_fetch_array(db_select("select id from ansatte where konto_id = '$id' and navn = '$kontakt'",__FILE__ . " linje " . __LINE__));
		$kontakt_id=$r['id']*1;
		if ($historik_id) {
			db_modify("update historik set kontakt_id = $kontakt_id, ansat_id = $ansat_id, notat = '$notat', kontaktet = '$kontaktet' where id = $historik_id",__FILE__ . " linje " . __LINE__);
		} else {
			$notedate=date("Y-m-d");
			$r = db_fetch_array(db_select("select id from ansatte where konto_id = '$egen_id' and navn = '$ansat'",__FILE__ . " linje " . __LINE__));
			$ansat_id=$r['id']*1;
			$r = db_fetch_array(db_select("select id from ansatte where konto_id = '$id' and navn = '$kontakt'",__FILE__ . " linje " . __LINE__));
			$kontakt_id=$r['id']*1;
			if ($kontaktes) db_modify("insert into historik (konto_id, kontakt_id, ansat_id, notat, notedate, kontaktet, kontaktes) values ($id , $kontakt_id, $ansat_id, '$notat', '$notedate', '$kontaktet', '$kontaktes')",__FILE__ . " linje " . __LINE__);
			else db_modify("insert into historik (konto_id, kontakt_id, ansat_id, notat, notedate, kontaktet) values ('$id' , '$kontakt_id', '$ansat_id', '$notat', '$notedate', '$kontaktet')",__FILE__ . " linje " . __LINE__);
		}
		if ($kontaktes) {
			db_modify("update adresser set kontaktet = '$kontaktet', kontaktes = '$kontaktes' where id = $id",__FILE__ . " linje " . __LINE__);
			if ($historik_id) db_modify("update historik set kontaktes = '$kontaktes' where id = $historik_id",__FILE__ . " linje " . __LINE__);
		} else db_modify("update adresser set kontaktet = '$kontaktet' where id = $id",__FILE__ . " linje " . __LINE__);
	}
	$historik_id=0;
}
############################
if (!$id) print "<meta http-equiv=\"refresh\" content=\"0;URL=../includes/luk.php\">";
if (strstr($returside,'historikkort.php')) $returside="historik.php";

if ($menu=='T') {
	$center = "align=center";
	$width = "width=20%";
	include_once '../includes/top_header.php';
	include_once '../includes/top_menu.php';
	print "<div id=\"header\">"; 
	print "<div class=\"headerbtnLft headLink\"><a href=\"javascript:confirmClose('historikkort.php?luk=luk.php')\" accesskey=L title='Klik her for at komme tilbage'><i class='fa fa-close fa-lg'></i> &nbsp;".findtekst(30,$sprog_id)."</a></div>";     
	print "<div class=\"headerTxt\">$title</div>";     
	print "<div class=\"headerbtnRght headLink\">&nbsp;&nbsp;&nbsp;</div>";     
	print "</div>";
	print "<div class='content-noside'>";
} elseif ($menu=='S') {
	$center = "";
	$width = "width=10%";
	print "<table width='100%' height='100%' border='0' cellspacing='0' cellpadding='0'><tbody>\n"; #tabel1 start
	print "<tr><td align='center' valign='top' height='1%'>\n";
	print "<table width='100%' align='center' border='0' cellspacing='4' cellpadding='0'><tbody>\n";#tabel2a start

	$tekst=findtekst(154,$sprog_id);

	print "<td width='10%' align=center><a href=\"javascript:confirmClose('historikkort.php?luk=luk.php')\" accesskey=L>
		   <button style='$buttonStyle; width:100%' onMouseOver=\"this.style.cursor='pointer'\">".findtekst(30, $sprog_id)."</button></a></td>\n";

	print "<td width='80%' align=center style='$topStyle'>".findtekst(1668, $sprog_id)."</td>\n";

	print "<td width='10%' align=center>
		   <a href=\"javascript:confirmClose('debitorkort.php?returside=historikkort.php&id=$id&ordre_id=$ordre_id&fokus=$fokus','$tekst')\" accesskey=N>
		   <button style='$buttonStyle; width:100%' onMouseOver=\"this.style.cursor='pointer'\">".findtekst(39, $sprog_id)."</button></a><br></td>\n";

	print "</tbody></table>\n";#tabel2a slut
	print "</td></tr>\n";
	print "<tr><td height=\"99%\"  width=\"100%\" valign=\"top\">";
} else {
	$center = "";
	$width = "width=10%";
print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>\n"; #tabel1 start
print "<tr><td align=\"center\" valign=\"top\" height=\"1%\">\n";
print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"4\" cellpadding=\"0\"><tbody>\n";#tabel2a start
$tekst=findtekst(154,$sprog_id);
#if ($returside=="debitorkort.php") print "<td width=\"10%\" align=center><div class=\"top_bund\"><a href=\"javascript:confirmClose('$returside?id=$id&ordre_id=$ordre_id&fokus=$fokus&konto_id=$id','$tekst')\" accesskey=L>Luk</a></div></td>\n";
#print "<td width=\"10%\" align=center><div class=\"top_bund\"><a href=\"javascript:confirmClose('$returside?returside=$returside&id=$ordre_id&fokus=$fokus&konto_id=$id','$tekst')\" accesskey=L>Luk</a></div></td>\n";
	print "<td width=\"10%\" align=center><div class=\"top_bund\"><a href=\"javascript:confirmClose('historikkort.php?luk=luk.php')\" accesskey=L>".findtekst(30, $sprog_id)."</a></div></td>\n";
	print "<td width=\"80%\" align=center><div class=\"top_bund\">".findtekst(1668, $sprog_id)."</div></td>\n";
	print "<td width=\"10%\" align=center><div class=\"top_bund\"><a href=\"javascript:confirmClose('debitorkort.php?returside=historikkort.php&id=$id&ordre_id=$ordre_id&fokus=$fokus','$tekst')\" accesskey=N>".findtekst(39, $sprog_id)."</a><br></div></td>\n";
print "</tbody></table>\n";#tabel2a slut
print "</td></tr>\n";
print "<tr><td height=\"99%\"  width=\"100%\" valign=\"top\">";
}
print "<table class='dataTableForm' width=\"100%\" cellpadding=\"1\" cellspacing=\"1\" border=\"0\"><tbody>";#tabel2b start

if ($id > 0){
	$q = db_select("select * from adresser where id = '$id'",__FILE__ . " linje " . __LINE__);
	$r = db_fetch_array($q);
	$kontonr=trim($r['kontonr']);
	$firmanavn=htmlentities(trim($r['firmanavn']),ENT_COMPAT,$charset);
	$addr1=htmlentities(trim($r['addr1']),ENT_COMPAT,$charset);
	$addr2=htmlentities(trim($r['addr2']),ENT_COMPAT,$charset);
	$postnr=trim($r['postnr']);
	$bynavn=htmlentities(trim($r['bynavn']),ENT_COMPAT,$charset);
	$land=htmlentities(trim($r['land']),ENT_COMPAT,$charset);
	$tlf=trim($r['tlf']);
	$fax=trim($r['fax']);
	$email=trim($r['email']);
	$web=trim($r['web']);
	$notes=htmlentities(trim($r['notes']),ENT_COMPAT,$charset);
	if ($r['oprettet']) $oprettet=dkdato($r['oprettet']);
	if ($r['kontaktet']) $kontaktet=dkdato($r['kontaktet']);
	if ($r['kontaktes']) $kontaktes=dkdato($r['kontaktes']);
}

if (db_fetch_array(db_select("select * from grupper where ART = 'FTP' and box1 !='' and box2 !='' and box3 !=''",__FILE__ . " linje " . __LINE__))) $vis_bilag=1;
$vis_bilag=1;
print "<form name='historikkort' action='historikkort.php?returside=$returside' method='post'>";
print "<input type='hidden' name=\"id\" value='$id'>";
print "<tr><td colspan='6' $center>";
print "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" width=\"800\"><tbody>";#tabel3a start;
print "<tr><td title=\"$notes\"><a href=debitorkort.php?id=$id&returside=historikkort.php>$firmanavn</a></td><td></td><td></td><td> ".findtekst(65, $sprog_id)."</td><td>";
#print "<tr><td>$firmanavn</td><td></td><td></td><td> Oprettet</td><td>";
if ($oprettet) print " $oprettet";
else print " <input type=text name=oprettet size=11 onchange=\"javascript:docChange = true;\">";
print "</td></tr>\n";
print "<tr><td> $addr1</td><td> $addr2</td></tr>\n";
print "<tr><td> $postnr $bynavn</td><td> $land</td><td></td><td> ".findtekst(1669, $sprog_id)."</td><td> $kontaktet</td></tr>\n";
print "<tr><td> Tlf: $tlf</td><td> ";
if ($fax) print "Fax: $fax";
print "</td><td></td><td> ".findtekst(1670, $sprog_id)."</td><td> $kontaktes</td>\n";
	if (db_fetch_array(db_select("select * from grupper where art = 'DIV' and kodenr = '2' and box7='on'",__FILE__ . " linje " . __LINE__))) {
		$url="jobliste.php?kontonr=$kontonr&konto_id=$id&returside=historikkort.php";
		$jobkort="<a href=$url><input type=\"button\" style=\"width:75px\" value=\"jobkort\" onClick=\"window.navigate('$url')\"></a>";
		print "<td>$jobkort</a></td></tr>";
	} else print "<tr>";


if ($email || $web) print "<tr>"; 
if ($email) print "<tr><td> mail $email</td>";
if ($web) print "<td width=\"50%\"> web $web</td>";
if ($email || $web) print "</tr>\n"; 
$hrtd="align='center'";
print "</tbody></table></td></tr>";#tabel3a slut;
print "<tr><td $hrtd colspan=6><hr class='hrtd'></td></tr>";
print "<tr><td $width><table border=0 width=100%><tbody>";#tabel3b start;
if ($historik_id) {
	$r=db_fetch_array(db_select("select * from historik where id = '$historik_id'",__FILE__ . " linje " . __LINE__));
	$notat=($r['notat']);
	$kontaktet=dkdato($r['kontaktet']);
	if ($r['kontaktes']) $kontaktes=dkdato($r['kontaktes']);
	else $kontaktes=NULL;
	$ansat_id=$r['ansat_id']*1;
	$kontakt_id=$r['kontakt_id']*1;
	$r = db_fetch_array(db_select("select id, navn from ansatte where id = $ansat_id",__FILE__ . " linje " . __LINE__));
	$ansat=$r['navn'];
	$r = db_fetch_array(db_select("select id, navn from ansatte where id = $kontakt_id",__FILE__ . " linje " . __LINE__));
	$kontakt=$r['navn'];
} else {$notat=''; $kontaktet=''; $kontaktes=''; $kontakt_id='';}
$ansat_id=$ansat_id*1;
if ($ansat_id) {
	$r=db_fetch_array(db_select("select navn from ansatte where id = $ansat_id and lukket != 'on'",__FILE__ . " linje " . __LINE__));
	$ansat_navn=$r['navn'];
} else $ansat_navn='';
$r = db_fetch_array(db_select("select id from adresser where art='S'",__FILE__ . " linje " . __LINE__));
$egen_id=$r['id']*1;
print "<input type=hidden name=egen_id value=$egen_id>";
print "<tr><td colspan =\"2\" $center><select name='ansat' value=\"$ansat\">";
if ($ansat_navn) print "<option>$ansat_navn</option>";
$q = db_select("select id, navn from ansatte where konto_id = $egen_id and lukket != 'on' and id != $ansat_id",__FILE__ . " linje " . __LINE__);
while ($r = db_fetch_array($q)){
	print "<option>$r[navn]</option>";
}
print "</SELECT></td></tr>\n";
print "<tr><td colspan =\"2\" $center>".findtekst(1671, $sprog_id)."</td></tr>\n";
print "<tr><td colspan =\"2\" $center><SELECT NAME=kontakt value=\"$kontakt\">";
$q = db_select("select id, navn, tlf, mobil, email, notes from ansatte where konto_id = $id",__FILE__ . " linje " . __LINE__);
while ($r = db_fetch_array($q)){
	print "<option title=\"D: $r[tlf] M: $r[mobil] E: $r[email] B: $r[notes]\">$r[navn]</option>\n";
}
print "<option></option>";

print "</SELECT></td></tr>\n";
if (!$kontaktet) $kontaktet=date("d-m-Y");
print "<tr><td $center>den</td><td $center><input type=text size=11 name=kontaktet value=\"$kontaktet\" onchange=\"javascript:docChange = true;\"></td></tr>\n";
print "<tr $center><td> ".findtekst(1672, $sprog_id)."</td>";
print "<td $center><input type=text size=11 name=kontaktes value=\"$kontaktes\" onchange=\"javascript:docChange = true;\"></td></tr>\n";
if ($historik_id) {
	print "<input type=hidden name=historik_id value=$historik_id>";
	print "<td $center><input class='button green medium' type=submit accesskey=\"g\" value=\"".findtekst(471, $sprog_id)."\" name=\"submit\" onclick=\"javascript:docChange = false;\"></td></tr>\n";
} else {
	print "<td colspan=2 $center><input class='button green medium' type=submit accesskey=\"g\" value=\"".findtekst(3, $sprog_id)."\" name=\"submit\" onclick=\"javascript:docChange = false;\"></td></tr>\n";
}
print "</td></tbody></table></td>";#tabel3b slut;
print "<td colspan=5><div class='textwrapper'><textarea name=\"note\" rows=\"10\" style='width:100%;' onchange=\"javascript:docChange = true;\">$notat</textarea></div></td></tr>\n";
print "</form>";
$q = db_select("select * from historik where konto_id = $id order by kontaktet desc, id desc",__FILE__ . " linje " . __LINE__);
print "<tr><td $hrtd colspan=6><hr class='hrtd'></tr>";
while ($r=db_fetch_array($q)){
	$ansat_id=$r['ansat_id']*1;
	$kontakt_id=$r['kontakt_id']*1;
	$ansat=str_replace(" ","&nbsp;",$r1['navn']);
	$notedato=dkdato($r['notedate']);
	$kontaktet=dkdato($r['kontaktet']);
	if ($r['kontaktes']) $kontaktes=dkdato($r['kontaktes']);
	else $kontaktes='';
	$r1 = db_fetch_array(db_select("select navn from ansatte where id = $ansat_id",__FILE__ . " linje " . __LINE__));
	$ansat=str_replace(" ","&nbsp;",$r1['navn']);
	$r1 = db_fetch_array(db_select("select navn, tlf, mobil, email, notes from ansatte where id = $kontakt_id",__FILE__ . " linje " . __LINE__));
	$kontakt=str_replace(" ","&nbsp;",$r1['navn']);
	$notat=str_replace("  ","&nbsp;&nbsp;",htmlentities($r['notat'],ENT_COMPAT,$charset));
	$notat=str_replace("\n","<br>",$notat);
	$dokument=$r['dokument'];
	print "<tr><td><table border=0 width=100%><tbody>";
	print "<tr><td colspan=2 width=100% >$ansat&nbsp;=&gt;<span title=\"D: $r1[tlf] M: $r1[mobil] E: $r1[email] B: $r1[notes]\">&nbsp;$kontakt</span></td></tr>";
	print "<tr><td colspan=2>$kontaktet &nbsp; $kontaktes</td></tr>";
#	if ($r[notedate]==date("Y-m-d")) 
	print "<tr><td><a href=historikkort.php?id=$id&historik_id=$r[id]&handling=ret>&nbsp;&nbsp;ret&nbsp;&nbsp;</a>&nbsp;&nbsp;&nbsp;<a href=historikkort.php?id=$id&historik_id=$r[id]&handling=slet onClick=\"return Slet()\">&nbsp;slet&nbsp;</a></td>";
	if ($vis_bilag) {
		if ($dokument) print "<td title=\"".findtekst(1454, $sprog_id).": $dokument\" align=right><a href=\"../includes/bilag.php?kilde=historik&filnavn=$dokument&kilde_id=$id&bilag_id=$r[id]\"><img style=\"border: 0px solid\" alt=\"clip med papir\" src=\"../ikoner/paper.png\"></a></td>";
		else print "<td title=\"".findtekst(1455, $sprog_id)."\" align=right><a href=\"../includes/bilag.php?kilde=historik&&ny=ja&kilde_id=$id&bilag_id=$r[id]\"><img style=\"border: 0px solid\" alt=\"papirclip\" src=\"../ikoner/clip.png\"></a></td>";
	} 
	print "</tbody></table>";

	print "</td><td style='vertical-align:top' width=90%>$notat</td></tr>";
	print "<tr><td $hrtd colspan=6><hr class='hrtd'></tr>";
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
