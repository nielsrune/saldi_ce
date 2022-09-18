<?php
//                         ___   _   _   ___  _  
//                        / __| / \ | | |   \| |
//                        \__ \/ _ \| |_| |) | |    
//                        |___/_/ \_|___|___/|_|
//
// ----------includes/salgsstat.php-----------------------lap 3.6.0---2015-11-29--
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af "The Free Software Foundation", enten i version 2
// af denne licens eller en senere version, efter eget valg.
// Fra og med version 3.2.2 dog under iagttagelse af følgende:
// 
// Programmet må ikke uden forudgående skriftlig aftale anvendes
// i konkurrence med saldi.dk aps eller anden rettighedshaver til programmet.
//
// Dette program er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
//
// En dansk oversaettelse af licensen kan laeses her:
// http://www.saldi.dk/dok/GNU_GPL_v2.html
//
// Copyright (c) 2003-2016 saldi.dk aps
// ----------------------------------------------------------------------------
// 
// 20160309	- ændret $antal[$x][$y] til $r['antal'] da antal ikke skal summeres ved sumberegning
// 20210329 - Loe translated with findtekst function some of these texts

@session_start();
$s_id=session_id();
$modulnr=12;
$title="Salgsstatistik";

$css="../css/standard.css";

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/settings.php");
include("../includes/std_func.php");
include("../includes/db_query.php");


$dato_fra=if_isset($_GET['dato_fra']);
$dato_til=if_isset($_GET['dato_til']);
$konto_fra=if_isset($_GET['konto_fra']);
$konto_til=if_isset($_GET['konto_til']);
$kontonr=if_isset($_GET['kontonr']);
$firmanavn=if_isset($_GET['firmanavn']);
$adresse=if_isset($_GET['adresse']);
$postnr=if_isset($_GET['postnr']);
$bynavn=if_isset($_GET['bynavn']);
$varenr=if_isset($_GET['varenr']);
$varetekst=if_isset($_GET['varetekst']);
$detaljer=if_isset($_GET['detaljer']);
$ret=if_isset($_GET['ret']);
$art=if_isset($_GET['art']);
if ($ret) {
	begraens($dato_fra,$dato_til,$konto_fra,$konto_til,$kontonr,$firmanavn,$adresse,$postnr,$bynavn,$varenr,$varetekst,$detaljer,$art);
	exit;
}
if (isset($_POST['find']) && $_POST['find']) {
	$kontonr=if_isset($_POST['kontonr']);
	$firmanavn=if_isset($_POST['firmanavn']);
	$adresse=if_isset($_POST['adresse']);
	$postnr=if_isset($_POST['postnr']);
	$bynavn=if_isset($_POST['bynavn']);
	$varenr=if_isset($_POST['varenr']);
	$varetekst=if_isset($_POST['varetekst']);
	$detaljer=if_isset($_POST['detaljer']);
}
($detaljer)?$summeret=NULL:$summeret='on';

$day=date("d");
$month=date("m");
$year=date("y");

$tmp=$year-1;
if ($tmp<10) $tmp="0".$tmp;
list($fra,$til)=explode(":",$periode);
if (!$til) $til=date("dmY");
$rtekst="Klik her for at v&aelig;lge en anden periode";

if ($popup) $luk="../includes/luk.php";
elseif ($art=='D') $luk="../debitor/rapport.php";
else $luk="../kreditor/rapport.php";

if ($menu=='T') {
	include_once '../includes/topmenu/header.php';
	print "<div class='$kund'>$title</div>
	<div class='content-noside'>";
} else {
	include_once '../includes/oldDesign/header.php';
	print "<table width = 100% cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tbody>";
	print "<tr><td colspan=\"4\" height=\"8\">";
	print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"3\" cellpadding=\"0\"><tbody>"; #B
	$tekst="Klik her for at lukke \"Top100\"";
	print "<td width=\"10%\" $top_bund title='$tekst'><a href=\"$luk\" accesskey=L>".findtekst(30,$sprog_id)."</a></td>";
	print "<td width=\"80%\" $top_bund>".findtekst(922,$sprog_id)."</td>";
	print "<td width=\"10%\" $top_bund title='$rtekst'><a href=salgsstat.php?dato_fra=$dato_fra&dato_til=$dato_til&konto_fra=$konto_fra&konto_til=$konto_til&kontonr=$kontonr&firmanavn=$firmanavn&adresse=$adresse&postnr=$postnr&bynavn=$bynavn&varenr=$varenr&varetekst=$varetekst&detaljer=$detaljer&art=$art&ret=on accesskey=B>".findtekst(913,$sprog_id)."<br></a></td>";
	print "</tbody></table>";
	print "</td></tr>\n";
	print "<tr><td width=\"100%\">"; 
}
#$art='D';
/*
$qtxt="select * from adresser where art = $art order by firmanavn";
$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
while($r=db_fetch_array($q)){
	$konto_id[$x]=$r['konto_id'];
	$kontonr[$x]=$r['kontonr'];
	$firmanavn[$x]=$r['firmanavn'];
	$x;
}
*/
$x=0;
$y=0;

$qtxt="select ordrelinjer.vare_id,ordrelinjer.varenr,ordrelinjer.beskrivelse,ordrelinjer.antal,ordrelinjer.pris,ordrelinjer.rabat,";
$qtxt.="ordrer.konto_id,ordrer.kontonr,ordrer.firmanavn,ordrer.id,ordrer.fakturadate from ordrer,ordrelinjer,adresser ";
$qtxt.="where ordrer.status>='3' and ordrelinjer.vare_id !='0' and ordrelinjer.ordre_id=ordrer.id and adresser.id=ordrer.konto_id and adresser.art='$art' ";
if ($dato_fra && $dato_til) $qtxt.="and ordrer.fakturadate>='".usdate($dato_fra)."' and ordrer.fakturadate<='".usdate($dato_til)."' ";
if ($konto_fra && $konto_til) $qtxt.="and ordrer.kontonr>='$konto_fra' and ordrer.kontonr<='$konto_til' ";
elseif ($kontonr) $qtxt.="and ordrer.kontonr like '".str_replace('*','%',$kontonr)."' ";
if ($firmanavn) $qtxt.="and lower(ordrer.firmanavn) like '".str_replace('*','%',strtolower($firmanavn))."' ";
if ($adresse) $qtxt.="and ordrer.adresse like '".str_replace('*','%',strtolower($adresse))."' ";
if ($postnr) $qtxt.="and ordrer.postnr like '".str_replace('*','%',strtolower($postnr))."' ";
if ($bynavn) $qtxt.="and ordrer.bynavn like '".str_replace('*','%',strtolower($bynavn))."' ";
if ($varenr) $qtxt.="and ordrelinjer.varenr like '".str_replace('*','%',strtolower($varenr))."' ";
if ($varetekst) $qtxt.="and ordrelinjer.beskrivelse like '".str_replace('*','%',strtolower($varetekst))."' ";
$qtxt.="order by ordrer.kontonr,ordrelinjer.varenr";
#cho $qtxt."<br>";
$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
while($r=db_fetch_array($q)){
	if ($q_konto_id[$x] && $q_konto_id[$x]!=$r['konto_id']) {
		$x++;
		$y=0;
		$q_vare_id[$x]=array();
	}
	$q_konto_id[$x]=$r['konto_id'];
	$q_kontonr[$x]=$r['kontonr'];
	$q_firmanavn[$x]=$r['firmanavn'];
	if ($summeret) {
		if ($q_vare_id[$x][$y] && $q_vare_id[$x][$y]!=$r['vare_id']) {
			$q_pris[$x][$y]=$q_sum[$x][$y]/$q_antal[$x][$y];
			$y++;
		}
		$q_vare_id[$x][$y]=$r['vare_id'];
		$q_varenr[$x][$y]=$r['varenr'];
		$q_beskrivelse[$x][$y]=$r['beskrivelse'];
#		$antal[$x][$y]=$r['antal'];
		$q_pris[$x][$y]=$r['pris'];
		$q_rabat[$x][$y]=$r['rabat'];
		$q_antal[$x][$y]+=$r['antal'];
		$q_sum[$x][$y]+=$r['antal']*($q_pris[$x][$y]-($q_pris[$x][$y]/100*$q_rabat[$x][$y])); #20160309
	} else {
		($r['fakturadate'])?$q_faktdato[$x][$y]=dkdato($r['fakturadate']):$q_faktdato[$x][$y]='Ikke faktureret';
		$q_vare_id[$x][$y]=$r['vare_id'];
		$q_varenr[$x][$y]=$r['varenr'];
		$q_beskrivelse[$x][$y]=$r['beskrivelse'];
#		$antal[$x][$y]=$r['antal'];
		$q_pris[$x][$y]=$r['pris'];
		$q_rabat[$x][$y]=$r['rabat'];
		$q_antal[$x][$y]=$r['antal'];
		$q_sum[$x][$y]=$q_antal[$x][$y]*($q_pris[$x][$y]-($q_pris[$x][$y]/100*$q_rabat[$x][$y]));
		$y++;
	}
} 
($summeret)?$cols='5':$cols='7';
if ($menu=='T') {
	print "<center style='padding-bottom:5px;'>	<input onclick=\"location.href='#nav'\" style='width:450px;' type=\"button\" title='Klik her for at søge' value=\"".findtekst(913,$sprog_id)."\">";
	print "<div class='expandableSearch' id='nav' style='padding-top:5px;'>";
	begraens($dato_fra,$dato_til,$konto_fra,$konto_til,$kontonr,$firmanavn,$adresse,$postnr,$bynavn,$varenr,$varetekst,$detaljer,$art);
	print "</div>";
	print "</center>";
} else {
	print "";
}

for ($x=0;$x<count($q_konto_id);$x++) {
	print"<div class='dataTablediv'><table width=\"100%\" class='dataTable'><tbody>";
#	print "<tr><td>$konto_id[$x]</td></tr>";
	if ($menu=='T') {
		if ($x) print "<br>";
	} else {
	if ($x) print "<tr><td colspan=\"$cols\"><hr></td></tr>";
	}
	print "<tr><td width=10%><b>".findtekst(284,$sprog_id).":</b></td><td>$q_kontonr[$x]</td></tr>";
	print "<tr><td width=10%><b>".findtekst(360,$sprog_id).":</b></td><td>$q_firmanavn[$x]</td></tr>";
	if ($periode) print "<tr><td><b>".findtekst(899,$sprog_id)."</b></td><td>$periode</td></tr>";
	print "<tr>";
	if (!$summeret) print "</td><td align=\"left\"><b>".findtekst(635,$sprog_id)."</b></td>";

	if ($menu=='T') {
		print "<tr><td colspan=10 class='border-hr-bottom'></td></tr>\n";
	} else {
		print "<tr><td colspan=10><hr></td></tr>\n";
	}

	print"<table width=\"100%\" class='dataTableNTH'><thead>";
	print "<th>".findtekst(917,$sprog_id)."</th><th>".findtekst(914,$sprog_id)."</th><th class='text-right'>".findtekst(916,$sprog_id)."</th><th class='text-right'>".findtekst(915,$sprog_id)."</th>";
	if (!$summeret) print "<th class='text-right'>".findtekst(428,$sprog_id)."</th>";
	print "<th class='text-right'>Sum</th></tr></thead><tbody>";
	for ($y=0;$y<count($q_vare_id[$x]);$y++) {
		print "<tr>";
		if (!$summeret) print "</td><td align=\"left\">".$q_faktdato[$x][$y]."</td>";
		print "<td>".$q_varenr[$x][$y]."</td>";
		print "<td>".$q_beskrivelse[$x][$y]."</td>";
		print "<td align=\"right\">".dkdecimal($q_antal[$x][$y])."</td>";
		print "<td align=\"right\">".dkdecimal($q_pris[$x][$y])."</td>";
		if (!$summeret) {
			print "<td align=\"right\">".dkdecimal($q_rabat[$x][$y])."</td>";
		}
		print "<td align=\"right\">".dkdecimal($q_sum[$x][$y])."</td>";
		print "</tr>";
	}
	print "</tbody><tfoot><tr><td></td><tr></tfoor>";
	print "</table></div><br>";

	}

	if ($menu=='T') {
		print "<center><input type='button' onclick=\"location.href='../debitor/rapport.php'\" accesskey='L' value='".findtekst(30,$sprog_id)."'></center>";
	} else {
		print "";
	}

	if ($menu=='T') {
		include_once '../includes/topmenu/footerDebRapporter.php';
	} else {
		include_once '../includes/oldDesign/footer.php';
	}

function begraens($dato_fra,$dato_til,$konto_fra,$konto_til,$kontonr,$firmanavn,$adresse,$postnr,$bynavn,$varenr,$varetekst,$detaljer,$art) {
	global $db;
	global $menu;
	($detaljer)?$detaljer='checked':$detaljer=NULL;
	print "<center>";
	print "<form name=\"".findtekst(918,$sprog_id)."\" action=\"salgsstat.php?dato_fra=$dato_fra&dato_til=$dato_til&konto_fra=$konto_fra&konto_til=$konto_til&kontonr=$kontonr&firmanavn=$firmanavn&adresse=$adresse&postnr=$postnr&bynavn=$bynavn&varenr=$varenr&varetekst=$varetekst&detaljer=$detaljer&art=$art\" method=\"post\">";
	print "<table width=25%><tbody>";
	print "<tr><td width=50%><b>".findtekst(284,$sprog_id).":</b></td><td><input type=\"text\" name=\"kontonr\" value=\"$kontonr\"></td></tr>"; #20210329
	print "<tr><td width=50%><b>".findtekst(360,$sprog_id).":</b></td><td><input type=\"text\" name=\"firmanavn\" value=\"$firmanavn\"></td></tr>";
	print "<tr><td width=50%><b>".findtekst(140,$sprog_id).":</b></td><td><input type=\"text\" name=\"adresse\" value=\"$adresse\"></td></tr>";
	print "<tr><td width=50%><b>".findtekst(650,$sprog_id).":</b></td><td><input type=\"text\" name=\"postnr\" value=\"$postnr\"></td></tr>";
	print "<tr><td width=50%><b>".findtekst(910,$sprog_id).":</b></td><td><input type=\"text\" name=\"bynavn\" value=\"$bynavn\"></td></tr>";
	print "<tr><td width=50%><b>".findtekst(917,$sprog_id).":</b></td><td><input type=\"text\" name=\"varenr\" value=\"$varenr\"></td></tr>";
	print "<tr><td width=50%><b>".findtekst(919,$sprog_id).":</b></td><td><input type=\"text\" name=\"varetekst\" value=\"$varetekst\"></td></tr>";
	print "<tr><td width=50%><b>".findtekst(920,$sprog_id).":</b></td><td align=\"right\"><label class='checkContainerVisning' style='padding-left: 20px;'><input type=\"checkbox\" name=\"detaljer\" $detaljer><span class='checkmarkVisning'></span></label></td></tr>";
	print "<tr><td>&nbsp;</td></tr>";
	print "<tr><td colspan=\"2\" align=\"center\">";
	if ($menu=='T') {
		print "<input type=\"submit\" name=\"find\" value=\"".findtekst(913,$sprog_id)."\">";
		print "&nbsp;•&nbsp;";
		print "<input onclick=\"location.href='#luk'\" type=\"button\" value=\"".findtekst(921,$sprog_id)."\">";
	} else {
		print "<input style=\"width:80px\" type=\"submit\" name=\"find\" value=\"".findtekst(913,$sprog_id)."\">";
	print "&nbsp;";
		print "<input style=\"width:80px\" type=\"submit\" name=\"fortryd\" value=\"".findtekst(921,$sprog_id)."\">";
	}
	print "</td></tr>";

	print "</tbody></table>";
}
?>
