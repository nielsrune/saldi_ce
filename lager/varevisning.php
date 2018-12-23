<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ------ lager/varevisning.php --- lap 3.7.2 --- 2018.11.26 -----------
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af "The Free Software Foundation", enten i version 2
// af denne licens eller en senere version, efter eget valg.
// Fra og med version 3.2.2 dog under iagttagelse af følgende:
// 
// Programmet må ikke uden forudgående skriftlig aftale anvendes
// i konkurrence med saldi.dk ApS eller anden rettighedshaver til programmet.
//
// Dette program er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
//
// En dansk oversaettelse af licensen kan laeses her:
// http://www.saldi.dk/dok/GNU_GPL_v2.html
//
// Copyright (c) 2003-2018 saldi.dk ApS
// --------------------------------------------------------------------
// 20180328 Tilføjet $vis_lev_felt
// 2018.11.23 PHR $vis_kostpriser tilføjet
// 2018.11.26 PHR href på varenr tilføjet

	
@session_start();
$s_id=session_id();

$title="Varevisning";
$modulnr=9;	

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");

if ($popup) $returside="../includes/luk.php";
else $returside="varer.php	";



if (isset($_POST) && $_POST) {
	$vis_lukkede=if_isset($_POST['vis_lukkede']);	
	$vis_lev_felt=if_isset($_POST['vis_lev_felt']);
	$vis_kostpriser=if_isset($_POST['vis_kostpriser']);
	$href_vnr=if_isset($_POST['href_vnr']);
	
	$VG_antal=if_isset($_POST['VG_antal']);
	$alle_VG=if_isset($_POST['VG_0']);
	if ($alle_VG=='on') $vis_VG='on';
	else $vis_VG='';
	for ($x=1; $x<=$VG_antal; $x++) {
		$tmp="VG_"."$x";
		$tmp1="VG_id"."$x";
		$tmp2=if_isset($_POST[$tmp]);
		if ($tmp2=='on') $vis_VG=$vis_VG.','.$_POST[$tmp1];
	}	
	$K_antal=if_isset($_POST['K_antal']);
	$alle_K=if_isset($_POST['K_0']);
	if ($alle_K=='on') $vis_K='on';
	else $vis_K='';
	for ($x=1; $x<=$K_antal; $x++) {
		$tmp="K_"."$x";
		$tmp1="K_id"."$x";
		$tmp2=if_isset($_POST[$tmp]);
		if ($tmp2=='on') $vis_K=$vis_K.','.$_POST[$tmp1];
	}	
	$tmp2=trim($tmp2,',');

	$qtxt="update grupper set box2='$vis_VG', box3='$vis_K',"; $qtxt.="box4='$vis_lukkede".chr(9)."$vis_lev_felt".chr(9)."$vis_kostpriser".chr(9)."$href_vnr'";
	$qtxt.=" where art = 'VV' and box1 = '$brugernavn'";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
#	if ($popup) print "<BODY onLoad=\"javascript=opener.location.reload();\">";
#	print "<meta http-equiv=\"refresh\" content=\"0;URL=$returside\">";
}

print "

<div align=\"center\">

<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>
	<tr><td height = \"25\" align=\"center\" valign=\"top\">
		<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"2\" cellpadding=\"0\"><tbody>
			<td width=\"10%\" $top_bund><font face=\"Helvetica, Arial, sans-serif\"><small><a href=$returside accesskey=L>Luk</a></small></td>
			<td width=\"80%\" $top_bund><font face=\"Helvetica, Arial, sans-serif\"><small>Varevisning</a></small></td>
			<td width=\"10%\" $top_bund><font face=\"Helvetica, Arial, sans-serif\"><small><br></small></td>
			 </tr>
			</tbody></table>
	</td></tr>
 <tr><td valign=\"top\">
<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" width=\"100%\" valign = \"top\">
<tbody><td valign=top width=25%>";

print "<table border=\"0\" width=\"100%\" valign = \"top\"><tbody>";

$vis_VG=array();
$vis_K=array();
print "<form name='varevisning' action='varevisning.php' method='post'>";
if ($r = db_fetch_array(db_select("select * from grupper where art = 'VV' and box1 = '$brugernavn'",__FILE__ . " linje " . __LINE__))) {
	$vis_VG=explode(",",$r['box2']);
	$vis_K=explode(",",$r['box3']);
	list($vis_lukkede,$vis_lev_felt,$vis_kostpriser,$href_vnr)=explode(chr(9),$r['box4'],4);
} else {
	db_modify("insert into grupper(beskrivelse, art, box1)values('varevisning', 'VV', '$brugernavn')",__FILE__ . " linje " . __LINE__);
	$vis_VG[0]='on';
	$vis_K[0]='on';
}
print "<tr><td>$font Varegrupper</td></tr>";
$x=0;
if ($vis_VG[0]) $tmp='checked';
else $tmp='';
print "<tr><td><small>$font<input name= VG_$x type=checkbox $tmp> Alle varegrupper</small></td></tr>";
$q = db_select("select * from grupper where art = 'VG' order by beskrivelse",__FILE__ . " linje " . __LINE__);
while ($r = db_fetch_array($q)) {
	$x++;
	print "<input type=hidden name=VG_id$x value=$r[kodenr]>";
	if (in_array($r['kodenr'],$vis_VG)) $tmp='checked';
	else $tmp='';
	$beskrivelse=stripslashes($r['beskrivelse']);
	print "<tr><td><small>$font<input name=VG_$x type=checkbox $tmp> $beskrivelse</small></td></tr>";
#	if (substr($vis_VG,$x,1)=='1') $tmp='on';
#	else $tmp='';
#	print "<tr><td><small>$font<input name= VG_$x type=checkbox $tmp> $r[beskrivelse]</small></td></tr>";
}
print "<input type=hidden name='VG_antal' value='$x'>";
print "<form name='varevisning' action='varevisning.php' method='post'>";
print "</tbody></table></td>";
print "<td width=25%><table  border=\"0\" width=\"100%\"><tbody>";
print "<tr><td>$font Kreditorer</td></tr>";

$x=0;
if ($vis_K[0]) $tmp='checked';
else $tmp='';
print "<tr><td><small>$font<input name= K_$x type=checkbox $tmp> Alle leverand&oslash;rer</small></td></tr>";
$q = db_select("select distinct vare_lev.lev_id as lev_id, adresser.firmanavn as firmanavn from vare_lev, adresser where adresser.id=vare_lev.lev_id order by adresser.firmanavn",__FILE__ . " linje " . __LINE__);
while ($r = db_fetch_array($q)) {
	$x++;
	print "<input type=hidden name=K_id$x value=$r[lev_id]>";
	if (in_array($r['lev_id'],$vis_K)) $tmp='checked';
	else $tmp='';
	$firmanavn=stripslashes($r['firmanavn']);
	print "<tr><td><small>$font<input name=K_$x type=checkbox $tmp> $firmanavn</small></td></tr>";
}
print "<input type=hidden name=K_antal value=$x>";
print "</tbody></table></td>";
print "<td valign=top width=25%><table  border=\"0\" width=\"100%\"><tbody>";
if ($vis_lukkede) $vis_lukkede="checked";
if ($vis_lev_felt) $vis_lev_felt="checked";
if ($vis_kostpriser) $vis_kostpriser="checked";
if ($href_vnr) $href_vnr="checked";
print "<tr><td><small>$font<input name='vis_lukkede' type='checkbox' $vis_lukkede> Vis udg&aring;ede varer</small></td></tr>";
print "<tr><td><small>$font<input name='vis_lev_felt' type='checkbox' $vis_lev_felt> Vis søgefelt for kreditorer</small></td></tr>";
print "<tr><td><small>$font<input name='vis_kostpriser' type='checkbox' $vis_kostpriser> Vis kostpriser</small></td></tr>";
$title="Ved afmærkning åbnes kun kortet ved klik på varenr";
print "<tr><td title='$title'><small>$font<input name='href_vnr' title='$title' type='checkbox' $href_vnr> Href på varenr</small></td></tr>";
print "<tr><td height=200 valign=bottom><input type=submit accesskey=\"a\" value=\"OK\" name=\"submit\"></td></tr>\n";

print "</tbody></table></td>";
print "<td width=25%><br></td>";
?>
</tbody></table>

</body></html>
