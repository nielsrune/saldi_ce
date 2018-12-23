<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ---------------debitor/ordreliste.php---lap 3.6.7------2017-02-07----
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
// Copyright (c) 2004-2017 DANOSOFT ApS
// ----------------------------------------------------------------------

// 2012.10.04 Fjernet email fra $selectfelter og indsat søgerutine til emails - søg 20121004
// 2012.10.17 Tilføjet link til oioubl import for automatisk ordreoprettelse - søg 20121017
// 2014.11.06 Ændret søgning på firmanavn så kun det relevante firmanavn kommer med ved søgning. 20141106
// 2014.11.07 tilføjet <center>
// 2015.03.08 Tilføjet "and art like 'D%' and status < '3'" da den fjernede tidspkt på afsluttede ordrer. 20150308
// 2016.09.01	Tilføjet array tekstfelter til søgning med wildcard
// 2016.12.17	PHR Tilføjet vis_lagerstatus. Søg vis_lagerstatus: 
// 2017.02.09	PHR Tilføjet mulighed for at slette ordrer direkte fra liste.
// 2017.05.20	PHR fjernet valutaberegning på kostpriser da det gav forlert DB/DG
// 2017.06.01 PHR	Delvis tilbageført ændringer fra 20141106 da alle fakturaer ikke kommer med ved opslag fra debitorkort 20170601
// 2018.04.18	PHR	Mulighed for at gemme ordre ved klik på kundeordrenr. Søg $gem_id


#ob_start();
@session_start();
$s_id=session_id();

$check_all=NULL; 
$ialt_m_moms=NULL;
$ny_sort=NULL;
$shop_ordre_id=NULL;
$uncheck_all=NULL;

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
print "<script LANGUAGE=\"JavaScript\" SRC=\"../javascript/overlib.js\"></script>";

$css="../css/standard.css";

$modulnr=5;
$title="Ordreliste - Debitorer";
$dk_dg=NULL; 
$checked=$cols=NULL;
$fakturadatoer=$fakturanumre=$firma=$firmanavn=$firmanavn_ant=NULL; 
$genfakt=$genfaktdatoer=$genfakturer=NULL;
$hreftext=$hurtigfakt=NULL; 
$konto_id=$kontonumre=NULL; 
$lev_datoer=$linjebg=NULL; 
$ordredatoer=$ordrenumre=NULL;
$ref=NULL;
$summer=NULL;
$totalkost=$tr_title=NULL; 
$understreg=NULL;
$vis_projekt=$vis_ret_next=NULL;
$find=array(NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/udvaelg.php");
	
#print "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"><html><head><title>Ordreliste - Kunder</title><meta http-equiv=\"content-type\" content=\"text/html; charset=ISO-8859-1\"></head>";

$id = if_isset($_GET['id']);
$konto_id = if_isset($_GET['konto_id']);
$returside=if_isset($_GET['returside']);
$valg= strtolower(if_isset($_GET['valg']));
$sort = if_isset($_GET['sort']);
$nysort = if_isset($_GET['nysort']);
$kontoid= if_isset($_GET['kontoid']);
$genberegn = if_isset($_GET['genberegn']);
$start = if_isset($_GET['start']);
$vis_lagerstatus = if_isset($_GET['vis_lagerstatus']);
$gem=if_isset($_GET['gem']);
$gem_id=if_isset($_GET['gem_id']);
$download=if_isset($_GET['download']);

if (!$returside && $konto_id && !$popup) $returside="debitorkort.php?id=$konto_id";

if (isset($_GET['valg'])) setcookie("saldi_ordreliste","$valg");
else $valg = $_COOKIE['saldi_ordreliste'];

$r2=db_fetch_array(db_select("select max(id) as id from grupper",__FILE__ . " linje " . __LINE__));

if (db_fetch_array(db_select("select id from grupper where art = 'DIV' and kodenr = '3' and box4='on'",__FILE__ . " linje " . __LINE__))) $hurtigfakt='on';
if ($valg=="tilbud" && $hurtigfakt) $valg="ordrer"; 
if (!$valg) $valg="ordrer";
$tjek=array("tilbud","ordrer","faktura","pbs");
if (!in_array($valg,$tjek)) $valg='ordrer';
#if ($valg=="ordrer" && $sort=="fakturanr") $sort="ordrenr";
if ($nysort=='sum_m_moms') $nysort='sum'; 
$sort=str_replace("ordrer.","",$sort);
if ($sort && $nysort==$sort) $sort=$sort." desc";
elseif ($nysort) $sort=$nysort;

$r2=db_fetch_array(db_select("select max(id) as id from grupper",__FILE__ . " linje " . __LINE__));

if ($r=db_fetch_array(db_select("select id from adresser where art = 'S' and pbs_nr > '0'",__FILE__ . " linje " . __LINE__))) {
 $pbs=1;
} else $pbs=0;

if (!$r=db_fetch_array(db_select("select id from grupper where art = 'OLV' and kode='$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__))) {
#	db_modify("update grupper set box2='$returside' where id='$r[id]'",__FILE__ . " linje " . __LINE__);
#} else { 
	if ($valg=="tilbud") {
		$box3="ordrenr,ordredate,kontonr,firmanavn,ref,sum";
		$box5="right,left,left,left,left,right";
		$box4="50,100,100,150,100,100";
		$box6="Tilbudsnr.,Tilbudsdato,Kontonr.,Firmanavn,S&aelig;lger,Tilbudssum";
	} elseif ($valg=="ordrer") {
		$box3="ordrenr,ordredate,levdate,kontonr,firmanavn,ref,sum";
		$box5="right,left,left,left,left,left,right";
		$box4="50,100,100,100,150,100,100";
		$box6="Ordrenr.,Ordredato,Levdato,Kontonr.,Firmanavn,S&aelig;lger,Ordresum";
	} elseif ($valg=="faktura") {
		$box3="ordrenr,ordredate,fakturanr,fakturadate,nextfakt,kontonr,firmanavn,ref,sum";
		$box5="right,left,right,left,left,left,left,left,right";
		$box4="50,100,100,100,100,150,100,100,100";
		$box6="Ordrenr.,Ordredato,Fakt.nr.,Fakt.dato,Genfakt.,Kontonr.,Firmanavn,S&aelig;lger,Fakturasum";
	}
	$r2=db_fetch_array(db_select("select max(id) as id from grupper",__FILE__ . " linje " . __LINE__));
#cho "max_id=$r2[id]<br>";	
	db_modify("insert into grupper (beskrivelse,kode,kodenr,art,box2,box3,box4,box5,box6,box7) values ('Ordrelistevisning','$valg','$bruger_id','OLV','$returside','$box3','$box4','$box5','$box6','100')",__FILE__ . " linje " . __LINE__);
} else {
	$r=db_fetch_array(db_select("select box2,box7,box8,box9 from grupper where art = 'OLV' and kode='$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__)); 
	if (!$returside) {
		$returside=$r['box2'];
		if (strstr($returside,"debitorkort.php?id=") && !$konto_id) {
			list($tmp,$konto_id)=explode("=",$returside);
		}
	}
	$linjeantal=$r['box7'];
	if (!$sort) $sort=$r['box8'];
#cho "$r[box9]<br>";
	$find=explode("\n",$r['box9']);
}
if (!$returside) {
#	$r=db_fetch_array(db_select("select box2,box7 from grupper where art = 'OLV' and kode='$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__)); 
#	$returside=$r['box2'];
#	$linjeantal=$r['box7'];
	if ($popup) $returside= "../includes/luk.php";
	else $returside= "../index/menu.php";
} elseif (!$popup && $returside=="../includes/luk.php") $returside="../index/menu.php";
db_modify("update grupper set box2='$returside',box8='$sort' where art = 'OLV' and kode='$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__);
if (!$popup) db_modify("update ordrer set hvem='', tidspkt='' where hvem='$brugernavn' and art like 'D%' and status < '3'",__FILE__ . " linje " . __LINE__); #20150308
		
$tidspkt=date("U");
 
#if (isset($_POST)) {
if ($submit=if_isset($_POST['submit'])) {
	if (strstr($submit, "Genfaktur")) $submit="Genfakturer";
	$find=if_isset($_POST['find']);
	$valg=if_isset($_POST['valg']);
	$sort = if_isset($_POST['sort']);
	$nysort = if_isset($_POST['nysort']);
	$firma=if_isset($_POST['firma']);
	$kontoid=if_isset($_POST['kontoid']);
	$firmanavn_ant=if_isset($_POST['firmanavn_antal']);
}
/* 20141106
if (($firma)&&($firmanavn_ant>0)) {
	for ($x=1; $x<=$firmanavn_ant; $x++) {
		$tmp="firmanavn$x";
		if ($firma==$_POST[$tmp]) {
			$tmp="konto_id$x";
			$kontoid=$_POST[$tmp];
		}
	}
}
elseif ($firmanavn_ant>0) {$kontoid='';}
*/
if (!$valg) $valg = "ordrer";
if (!$sort) $sort = "firmanavn";

$sort=str_replace("ordrer.","",$sort); #2008.02.05
$sortering=$sort;

if ($valg!='faktura') {
#	$fakturanumre='';
#	$fakturadatoer='';
	$genfakturer='';
}
if ($valg=="tilbud") {$status="status = 0";}
elseif ($valg=="faktura") {$status="status >= 3";}
else {$status="(status = 1 or status = 2)";}

if (db_fetch_array(db_select("select distinct id from ordrer where projekt > '0' and $status",__FILE__ . " linje " . __LINE__))) $vis_projekt='on';
	$ordre_id = if_isset($_POST['ordre_id']);
	$checked = if_isset($_POST['checked']);
	

$slet_valgte=if_isset($_POST['slet_valgte']); 
if ($slet_valgte=='Slet') {
	include("../includes/ordrefunc.php");
	$y=0;
	for ($x=0; $x<count($ordre_id); $x++){
		$c=$ordre_id[$x];
		if ($checked[$c]=="on") {
			slet_ordre($ordre_id[$x]);
		}
	}
}
if ($submit=="Udskriv" || $submit=="Send mails"){
	$y=0;
	for ($x=0; $x<count($ordre_id); $x++){
		$c=$ordre_id[$x];
		if ($checked[$c]=="on") {
			$y++;
			if (!$udskriv) $udskriv=$ordre_id[$x];
			else $udskriv=$udskriv.",".$ordre_id[$x];
		}
	}
	if ($y>0) {
		if ($submit=="Udskriv") print "<BODY onload=\"JavaScript:window.open('formularprint.php?id=-1&ordre_antal=$y&skriv=$udskriv&formular=4&udskriv_til=PDF' , '' , ',statusbar=no,menubar=no,titlebar=no,toolbar=no,scrollbars=yes, location=1');\">";
		elseif ($submit=="Send mails") print "<BODY onload=\"JavaScript:window.open('formularprint.php?id=-1&ordre_antal=$y&skriv=$udskriv&formular=4&udskriv_til=email' , '' , ',statusbar=no,menubar=no,titlebar=no,toolbar=no,scrollbars=yes, location=1');\">";
	}
	else print "<BODY onload=\"javascript:alert('Ingen fakturaer er markeret til udskrivning!')\">";
}
if (isset($_POST['check'])||isset($_POST['uncheck'])) {
	if (isset($_POST['check'])) $check_all='on';
	else $uncheck_all='on';
}
if ($submit=="Genfakturer" || $submit=="Ret"){
	for ($x=0; $x<count($ordre_id); $x++){
		$c=$ordre_id[$x];
		if ($checked[$c]=="on") {
			$y++;
			if (!$genfakt) $genfakt=$c;
			else $genfakt=$genfakt.",".$c;
		}
	}
	if ($y>0) {
		if ($popup) { 
			if ($submit=="Ret") {
				print "<BODY onload=\"JavaScript:window.open('ret_genfakt.php?ordreliste=$genfakt' , '' , ',statusbar=no,menubar=no,titlebar=no,toolbar=no,scrollbars=yes, location=1');\">";
			} else print "<BODY onload=\"JavaScript:window.open('genfakturer.php?id=-1&ordre_antal=$y&genfakt=$genfakt' , '' , ',statusbar=no,menubar=no,titlebar=no,toolbar=no,scrollbars=yes, location=1');\">";
		} else {
			if ($submit=="Ret") print "<meta http-equiv=\"refresh\" content=\"0;URL=ret_genfakt.php?ordreliste=$genfakt\">";
			else print "<meta http-equiv=\"refresh\" content=\"0;URL=genfakturer.php?id=-1&ordre_antal=$y&genfakt=$genfakt\">";
		}
	}
	else print "<BODY onload=\"javascript:alert('Ingen fakturaer er markeret til genfakturering!')\">";
} 
if ($menu=='T') {
include_once '../includes/top_header.php';
include_once '../includes/top_menu.php';
print "<div id=\"header\"> 
    <div class=\"headerbtnLft\"></div>
    <span class=\"headerTxt\">Debitor - Ordreliste</span>";     
print "<div class=\"headerbtnRght\"><!--<a href=\"index.php?page=debitor/ordre&amp;title=debitor\" class=\"button green small right\">Ny ordre</a>--></div>";       
print "</div><!-- end of header -->
<div class=\"maincontentLargeHolder\">\n";
print  "<table border=\"0\" cellspacing=\"0\" id=\"dataTable\" class=\"dataTable\">";

#	$leftbutton="<a title=\"Klik her for at komme til startsiden\" href=\"../index/menu.php\" accesskey=\"L\">LUK</a>";
#	$rightbutton="<a href=\"#\">Ordremenu</a>\t";
#	if ($valg!='ordrer') $rightbutton.="\t<a href='ordreliste.php?valg=ordrer&konto_id=$konto_id&returside=$returside'>&nbsp;Ordreliste&nbsp;</a>";
#	if ($valg!='faktura') $rightbutton.="\t<a href='ordreliste.php?valg=faktura&konto_id=$konto_id&returside=$returside'>&nbsp;Fakturaliste&nbsp;</a>";
#	$rightbutton.="\t<a href=\"../debitor/ordre.php?returside=../debitor/ordreliste.php?konto_id=$konto_id\">Ny ordre/faktura</a>";
#	$rightbutton.="\t<a accesskey=V href=ordrevisning.php?valg=$valg>Visning</a>";
#	include("../includes/topmenu.php");
} elseif ($menu=='S') {
	include("../includes/sidemenu.php");
} else {
	print "<tr><td height = 25 align=center valign=top>";
	print "<table width=100% align=center border=0 cellspacing=2 cellpadding=0><tbody><td width=10% $top_bund>"; # Tabel 1.1 ->
	#if ($popup) print "<a href=../includes/luk.php accesskey=L>Luk</a></td>";
	#else print "<a href=\"../index/menu.php\" accesskey=\"L\">Luk</a></td>";
	print "<a href=$returside accesskey=L>Luk</a></td>";
	print "<td width=80% $top_bund align=center><table border=0 cellspacing=2 cellpadding=0><tbody>\n"; # Tabel 1.1.1 ->
	if ($valg=='tilbud' && !$hurtigfakt) {print "<td width = 20% align=center $knap_ind>&nbsp;Tilbud&nbsp;</td>";}
	elseif (!$hurtigfakt) {print "<td width = 20% align=center><a href='ordreliste.php?valg=tilbud&konto_id=$konto_id&returside=$returside'>&nbsp;Tilbud&nbsp;</a></td>";}
	if ($valg=='ordrer') {print "<td width = 20% align=center $knap_ind>&nbsp;Ordrer&nbsp;</td>";}
	else {print "<td width = 20% align=center><a href='ordreliste.php?valg=ordrer&konto_id=$konto_id&returside=$returside'>&nbsp;Ordrer&nbsp;</a></td>";}
	if ($valg=='faktura') print "<td width = 20% align=center $knap_ind>&nbsp;Faktura&nbsp;</td>";
	else print "<td width = 20% align=center><a href='ordreliste.php?valg=faktura&konto_id=$konto_id&returside=$returside'>&nbsp;Faktura&nbsp;</a></td>";
	if ($valg=='pbs') print "<td width = 20% align=center $knap_ind>&nbsp;PBS&nbsp;</td>";
	elseif ($pbs) print "<td width = 20% align=center><a href='ordreliste.php?valg=pbs&konto_id=$konto_id&returside=$returside'>&nbsp;PBS&nbsp;</a></td>";
	print "</tbody></table></td>\n"; # <- Tabel 1.1.1
	if ($valg=='pbs') {
		if ($popup) print "<td width=10% $top_bund onclick=\"javascript:ordre=window.open('pbs_import.php?returside=x','ordre','scrollbars=1,resizable=1');ordre.focus();\"><a accesskey=N href=ordreliste.php?sort=$sort>Import PBS</a></td>\n";
		else  print "<td width=10% $top_bund><a href=pbs_import.php?returside=ordreliste.php>Import PBS</a></td>\n";
		include("pbsliste.php");
		exit;
	}
	if ($valg=='pbs') {
	#	if ($popup) print "<td width=10% $top_bund onclick=\"javascript:ordre=window.open('pbs_import.php?returside=ordreliste.php','ordre','scrollbars=1,resizable=1');ordre.focus();\"><a accesskey=N href=ordreliste.php?sort=$sort>Import PBS</a></td>\n";
	#	else  print "<td width=10% $top_bund><a href=pbs_import.php?returside=ordreliste.php>Import PBS</a></td>\n";
	} else {
		print "<td width=5% $top_bund><a accesskey=V href=ordrevisning.php?valg=$valg>Visning</a></td>\n";
		if ($popup) {
			print "<td width=5% $top_bund onclick=\"javascript:ordre=window.open('ordre.php?returside=ordreliste.php&konto_id=$konto_id','ordre','scrollbars=1,resizable=1');ordre.focus();\"><a accesskey=N href='".$_SERVER['PHP_SELF']."'>Ny</a></td>\n";
		} else {
			print "<td width=5%  $top_bund><a href=ordre.php?konto_id=$konto_id&returside=ordreliste.php?konto_id=$konto_id>Ny</a></td>\n";
		}
		print "</tbody></table></td></tr>\n"; # <- Tabel 1.1.1
	}
	if ($valg=='ordrer') { #20121017
		$dir = '../ublfiler/ind/';
		if (file_exists("$dir")) {
			$vis_xml=0;
			$filer = scandir($dir);
			for ($x=0;$x<count($filer);$x++) {
				if (substr($filer[$x],-3)=='xml') $vis_xml=1; 
			}
			if ($vis_xml) print "<tr><td align=\"center\"><a href=\"ubl2ordre.php\" target=\"blank\">Importer UBL til ordrer</a></td></tr>";
		}
	}
	print "<center>"; #20141107
}
#cho "select box3,box4,box5, box6 from grupper where art = 'OLV' and kodenr = '$bruger_id' and kode='$valg'<br>";
$r = db_fetch_array(db_select("select box3,box4,box5, box6 from grupper where art = 'OLV' and kodenr = '$bruger_id' and kode='$valg'",__FILE__ . " linje " . __LINE__));
$vis_felt=explode(",",$r['box3']);
$feltbredde=explode(",",$r['box4']);
$justering=explode(",",$r['box5']);
$feltnavn=explode(",",$r['box6']);
$vis_feltantal=count($vis_felt);
$selectfelter=array("konto_id","firmanavn","addr1","addr2","bynavn","land","lev_navn","lev_addr1","lev_addr2","lev_postnr","lev_bynavn","lev_kontakt","ean","institution","betalingsbet","betalingsdage","art","momssats","ref","betalt","valuta","sprog","mail_fakt","pbs","mail","mail_cc","mail_bcc","mail_subj","mail_text","udskriv_til");
$tekstfelter=array("cvrnr","email","kontakt"); #20160901

####################################################################################
$udvaelg=NULL;
$tmp=trim($find[0]);
for ($x=1;$x<$vis_feltantal;$x++) $tmp=$tmp."\n".trim($find[$x]);
$tmp=addslashes($tmp);
#cho "update grupper set box9='$tmp' where art = 'OLV' and kode='$valg' and kodenr = '$bruger_id'<br>";
db_modify("update grupper set box9='$tmp' where art = 'OLV' and kode='$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__);

for ($x=0;$x<$vis_feltantal;$x++) {
	if ($feltbredde[$x]<=10) $feltbredde[$x]*=10;
	if (!$feltbredde[$x]) $feltbredde[$x]=100;
	$find[$x]=addslashes(trim($find[$x]));
	if ($find[$x]=="-") $find[$x]=NULL; 
# 20141106
	if ($konto_id && $find[$x] && ($vis_felt[$x]=='firmanavn' || $vis_felt[$x]=='kontonr') && !strpos("$find[$x]",":")) { #Tilføjet '$konto_id &&' 
		$d=0;
		$tmplist=array();
		if ($vis_felt[$x]=='firmanavn' && !$konto_id) $q=db_select("select distinct(konto_id) as konto_id from ordrer where firmanavn = '$find[$x]'",__FILE__ . " linje " . __LINE__);
		elseif(!$konto_id) {
			$q=db_select("select distinct(konto_id) as konto_id from ordrer where kontonr = '$find[$x]'",__FILE__ . " linje " . __LINE__);
		}
		while ($r=db_fetch_array($q)) {
			$d++;
			$tmpliste[$d]=$r['konto_id'];
		}
		if ($d) {
			$tmp=$d;
				$udvaelg.="and(ordrer.konto_id='$tmpliste[1]'";
			for($d=2;$d<=$tmp;$d++) {
				$udvaelg.=" or ordrer.konto_id='$tmpliste[$d]'";
			} 
			$udvaelg.=")";
		} elseif (!$konto_id) $udvaelg.="and ordrer.konto_id='0'"; 
	} else {

		$tmp=$vis_felt[$x];
		if ($tmp=='ordrenr' && $find[$x]) {
			if (strlen($find[$x])>=11) $find[$x]=substr($find[$x],0,10);
			$find[$x]*=1;
		}
		if ($tmp=='kontonr' && $find[$x]) {
			$find[$x]*=1;
		}
		if ($vis_felt[$x]=='sum_m_moms' && is_numeric($find[$x])) {
			$udvaelg=$udvaelg." and ordrer.sum+ordrer.moms='$find[$x]'";
		} elseif (in_array($vis_felt[$x],$selectfelter) && ($find[$x]||$find[$x]=="0")) {
			$udvaelg=$udvaelg." and ordrer.$tmp='$find[$x]'";
		} elseif ((strpos($vis_felt[$x],"date") || $vis_felt[$x]=="nextfakt") && ($find[$x]||$find[$x]=="0")) {
			if ($vis_felt[$x]=="nextfakt") $genfakturer="1";
			$tmp2="ordrer.".$tmp."";
			$udvaelg=$udvaelg.udvaelg($find[$x],$tmp2, 'DATO');
		} elseif ($vis_felt[$x]=="sum" && ($find[$x]||$find[$x]=="0")) {
			$tmp2="ordrer.".$tmp."";
			$udvaelg=$udvaelg.udvaelg($find[$x],$tmp2, 'BELOB');
		} elseif (in_array($vis_felt[$x],$tekstfelter) && $find[$x]) { #20121004 20160901
			$tmp2="ordrer.".$tmp."";
			$udvaelg=$udvaelg.udvaelg($find[$x],$tmp2,'');
		} elseif ($find[$x]||$find[$x]=="0") {
			$tmp2="ordrer.".$tmp."";
			$udvaelg=$udvaelg.udvaelg($find[$x],$tmp2, 'NR');
		}
	}
}
if ($udvaelg) $linjeantal=10000;
if (strstr($sortering,'fakturanr')) {
	if ($db_type=='mysql') $sortering=str_replace("fakturanr","CAST(ordrer.fakturanr AS SIGNED)",$sortering); 
	else $sortering=str_replace("fakturanr","to_number(textcat('0',ordrer.fakturanr),text(99999999))",$sortering);
} else $sortering="ordrer.".$sortering;
# if (strstr($udvaelg,'fakturanr')) $udvaelg=str_replace("fakturanr","fakturanr::varchar::numeric",$udvaelg);
$ordreliste="";

if ($valg=="tilbud") $status="status < 1";
elseif ($valg=="ordrer" && $hurtigfakt) $status="status < 3"; 
elseif ($valg=="ordrer") $status="(status = 1 or status = 2)"; 
else $status="status >= 3";

$ialt=0;
$lnr=0;
if (!$linjeantal) $linjeantal=100;
#$start=0;
$slut=$start+$linjeantal;
$ordreantal=0;

if ($konto_id) $udvaelg=$udvaelg."and konto_id=$konto_id";
#cho "select count(id) as antal from ordrer where (art = 'DO' or art = 'DK') and $status $udvaelg<br>";
$r=db_fetch_array(db_select("select count(id) as antal from ordrer where (art = 'DO' or art = 'DK' or (art = 'PO' and konto_id > '0')) and $status $udvaelg",__FILE__ . " linje " . __LINE__));
$antal=$r['antal'];

print " </td></tr>\n<tr><td align=center valign=top>";
print "<table border=0 valign='top'><tbody>\n<tr>";
if ($start>0) {
	$tmp=$start-$linjeantal;
	if ($tmp<0) $tmp=0;
	print "<td><a href=ordreliste.php?start=$tmp&valg=$valg&konto_id=$konto_id><img src=../ikoner/left.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td>";
} else print "<td></td>";
# if ($valg=='tilbud') {
for ($x=0;$x<$vis_feltantal;$x++) {
		if (!$feltbredde[$x]) $feltbredde[$x]*="100";
		elseif ($feltbredde[$x]<15) $feltbredde[$x]*="10";
	if ($feltbredde[$x]) {
		$width="width=\"$feltbredde[$x]px\"";
	} else $width="";
	print "<td align=$justering[$x] $width style=\"border:1px solid $bgcolor;\"><b><a href='ordreliste.php?nysort=$vis_felt[$x]&sort=$sort&valg=$valg'>$feltnavn[$x]</b></td>\n";
}
$tmp=$start+$linjeantal;
if ($antal>$slut) print "<td align=right><a href=ordreliste.php?start=$tmp&valg=$valg&konto_id=$konto_id><img src=../ikoner/right.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td>";
print "</tr>\n";

#################################### Sogefelter ##########################################


print "<form name=\"sogefelter\" action=\"ordreliste.php?konto_id=$konto_id&sort=$sort\" method=\"post\">";
print "<input type=hidden name=valg value=$valg>";
print "<input type=hidden name=sort value='$ny_sort'>";
print "<input type=hidden name=nysort value='$sort'>";
print "<input type=hidden name=kontoid value=$kontoid>";

print "<tr><td></td>";
#if ($valg=='tilbud') {
	for ($x=0;$x<$vis_feltantal;$x++) {
		if (!$feltbredde[$x]) $feltbredde[$x]*="100";
		elseif ($feltbredde[$x]<15) $feltbredde[$x]*="10";
		if ($feltbredde[$x]) {
			$width="width:$feltbredde[$x]px";
		} else $width="";
		if ($konto_id && ($vis_felt[$x]=="kontonr" || $vis_felt[$x]=="firmanavn")) $span = 'Listen er &aring;bnet fra debitorkort - s&oslash;gefelt deaktiveret';
		elseif (strpos($vis_felt[$x],"nr")) $span = 'Skriv et nummer eller skriv to adskilt af kolon (f.eks 345:350)';
		elseif (strpos($vis_felt[$x],"date") || $vis_felt[$x]=="nextfakt") $span = 'Skriv en dato eller to datoer adskilt af kolon (f.eks 011009:311009)';
		elseif ($vis_felt[$x]=="sum") $span = 'Skriv et beb&oslash;b eller to adskilt af kolon (f.eks 525,25:525,50)';
		else $span=''; 
		print "<td align=$justering[$x]><span title= '$span'>";
#cho "$konto_id && ($vis_felt[$x]==\"kontonr\" || $vis_felt[$x]==\"firmanavn\"<br>";		
		if ($konto_id && ($vis_felt[$x]=="kontonr" || $vis_felt[$x]=="firmanavn")) {
			$r=db_fetch_array(db_select("select $vis_felt[$x] as tmp from adresser where id='$konto_id'",__FILE__ . " linje " . __LINE__));
			print "<input class=\"inputbox\" type=text readonly=$readonly style=\"text-align:$justering[$x];$width;\" name=find[$x] value=\"$r[tmp]\">";
		} elseif (in_array($vis_felt[$x],$selectfelter)) {
			$tmp=$vis_felt[$x];
			print "<SELECT NAME=\"find[$x]\" class=\"inputbox\" style=\"$width;\">";
#cho "select distinct($tmp) from ordrer where (art = 'DO' or art = 'DK') and status <= 1<br>";			
			if ($valg=="tilbud") $status = "status < 1";
			elseif ($valg=="ordrer" && $hurtigfakt) $status  = "status <= 2";
			elseif ($valg=="ordrer") $status  = "(status >= 1 and status <= 2)";
			else $status  = "status >= 3";
			$q=db_select("select distinct($tmp) from ordrer where (art = 'DO' or art = 'DK' or (art = 'PO' and konto_id > '0')) and $status order by $tmp",__FILE__ . " linje " . __LINE__);
			print "<option>".stripslashes($find[$x])."</option>";
			if ($find[$x]) print "<option></option>";
			while ($r=db_fetch_array($q)) {
				print "<option>$r[$tmp]</option>";
			}
			print "</SELECT></td>";			
		} else print "<input class=\"inputbox\" type=text style=\"text-align:$justering[$x];$width;\" name=find[$x] value=\"$find[$x]\">";
	}
	print "</td>\n";  
print "<td><input type=submit value=\"OK\" name=\"submit\"></td>";
print "</form></tr><td></td>\n";

######################################################################################################################
#if ($genfakt) $checked=array();
if ($vis_lagerstatus) {
	$x=0;
	$qtxt="select kodenr from grupper where art='VG' and box8='on'";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$ls_vgr[$x]=$r['kodenr'];
		$x++;
	}
}
print "<form name=ordreliste action=ordreliste.php?valg=$valg$hreftext&start=$start&sort=$sort method=post>";
$qtxt="select * from ordrer where (art = 'DO' or art = 'DK' or (art = 'PO' and konto_id > '0')) and $status $udvaelg order by $sortering";
$query = db_select($qtxt,__FILE__ . " linje " . __LINE__);
$l=0;
$lnr=0;
while ($row=db_fetch_array($query)) {
	$lnr++;
	if($lnr>=$start && $lnr<$slut) {
		$ordreantal++;
		if ($ordreliste) $ordreliste=$ordreliste.",".$id;
		else $ordreliste=$id;
		$ordre="ordre".$id;
		$sum=$row['sum'];
		$kostpris=$row['kostpris'];
		$valutakurs=$row['valutakurs'];
		$nextfakt=$row['nextfakt'];
		$sum_m_moms=$row['sum']+$row['moms'];
		$id=$row['id'];
		if ($valg=='faktura') {
			$udlignet=0;
			if ($r=db_fetch_array(db_select("select udlignet from openpost where faktnr = '$row[fakturanr]' and konto_id='$row[konto_id]' and amount='$sum_m_moms'",__FILE__ . " linje " . __LINE__))) {
				$udlignet=$r['udlignet']*1;	
			} else { # 20101220 Denne del er indsat grundet enkelte forekomster med manglende faktnr  
				$tmp1="Faktura - ".$row['fakturanr'];
				$tmp2="Faktura - ".$row['fakturanr']." - ".$row['fakturadate'];
#cho "select id,udlignet from openpost where (beskrivelse = '$tmp1' or beskrivelse = '$tmp2') and konto_id='$row[konto_id]' and amount='$tmp'<br>";
				if ($r=db_fetch_array(db_select("select id,udlignet from openpost where (beskrivelse = '$tmp1' or beskrivelse = '$tmp2') and konto_id='$row[konto_id]' and amount='$sum_m_moms'",__FILE__ . " linje " . __LINE__))) {
					$udlignet=$r['udlignet']*1;
#cho "update openpost set faktnr='$row[fakturanr]' where id = '$r[id]'<br>";
					db_modify("update openpost set faktnr='$row[fakturanr]' where id = '$r[id]'",__FILE__ . " linje " . __LINE__);
					$message=$db." | ".$tmp2." | ".$brugernavn." ".date("Y-m-d H:i:s")." | $fejltekst";
					$headers = 'From: fejl@saldi.dk'."\r\n".'Reply-To: fejl@saldi.dk'."\r\n".'X-Mailer: PHP/' . phpversion();
					mail('fejl@saldi.dk', 'SALDI Opdater openpost', $message, $headers);
				}
			}
		}
		if (($tidspkt-($row['tidspkt'])>3600)||($row['hvem']==$brugernavn || $row['hvem']=='')) {
			if ($popup) {
				$javascript="onclick=\"javascript:$ordre=window.open('ordre.php?tjek=$id&id=$id&returside=ordreliste.php','$ordre','scrollbars=1,resizable=1');$ordre.focus();\" onmouseover=\"this.style.cursor = 'pointer'\" ";
				$understreg='<span style="text-decoration: underline;">';
				$hrefslut="";
			} else {
				$javascript="";
				$understreg="<a href=ordre.php?tjek=$id&id=$id&returside=ordreliste.php>";
				$hrefslut="</a>";
			}
			$linjetext="";
		} else {
			$javascript="onclick=\"javascript:$ordre.focus();\"";
			$understreg='';
			$linjetext="<span title= 'Ordre er l&aring;st af $row[hvem]'>";
		}
		if ( $valg == "ordrer" && $bgnuance1 ) {
			$q2=db_select("select antal,leveres,leveret from ordrelinjer where ordre_id = '$id'",__FILE__ . " linje " . __LINE__);
			$levstatus=0;
			while ($r2=db_fetch_array($q2)) {
				if ( $levstatus === "Mangler" ) {
					continue;
				} 
				if ( ( $r2['leveret'] > 0 &&  $r2['antal'] > $r2['leveret'] ) || ( $r2['antal'] > ($r2['leveres']+$r2['leveret'] ) ) ) {
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
				if ($row['art']=='DK') {
					$tr_title="Mangler modtagelse af en eller flere vare.";
				} else {
					$tr_title="Mangler levering af en eller flere vare.";
				}
			} elseif ( $levstatus == "Leveret" ) {
				$bgnuance=0;
				$color='#555555';
				if ($row['art']=='DK') {
					$tr_title="Alt modtaget, mangler kun at sende kreditnota.";
				} else {
					$tr_title="Alt leveret, mangler kun at fakturere.";
				}
			} else {
				$bgnuance=0;
				$color='#000000';
				if ($row['art']=='DK') {
					$tr_title="Intet modtaget.";
				} else {
					$tr_title="Intet leveret.";
				}
			}
			$linjebg=linjefarve($linjebg, $bgcolor, $bgcolor5, $bgnuance1, $bgnuance);
			print "<tr bgcolor=\"$linjebg\" title='$tr_title'><td bgcolor=$bgcolor></td>";
		} elseif ($vis_lagerstatus) {
			$linjebg=NULL;
#			$lnr=0;
#			$r=db_fetch_array(db_select("select count(antal) as linjeantal from ordrelinjer where ordre_id='$id' and antal != '0'"));
#			$ls_linjeantal=$r['linjeantal'];
			$spantxt="<table><tbody>";
			$spantxt.="<tr><td>varenr</td><td>Beholdn.</td><td>Antal</td><td>Leveret</td><td>I tilbud</td><td>I ordre</td><td>I forslag</td><td>Bestilt</td></tr>";
			$q=db_select("select * from ordrelinjer where ordre_id='$id' and antal != '0'");
			while ($r=db_fetch_array($q)) {
				$r2=db_fetch_array(db_select("select beholdning,gruppe from varer where id='$r[vare_id]'"));
				if (in_array($r2['gruppe'],$ls_vgr)) {
#					$lnr++;
#					$beholdning=$r2['beholdning'];	
					$tmp=find_beholdning($r['vare_id'],NULL);
#					$i_tilbud=$tmp[1];
#					$it_ordrenr=$tmp[5];
#					$i_ordre=$tmp[2];
#					$io_ordrenr=$tmp[6];
#					$i_forslag=$tmp[3];
#					$if_ordrenr=$tmp[7];
#					$bestilt=$tmp[4];
#					$b_ordrenr=$tmp[8];
#if ($id=='33186') echo "($r2[beholdning]+$tmp[4]-($r[antal]-$r[leveret])<=0)<br>";
					if ($r2['beholdning']-($r['antal']-$r['leveret'])<0 && $r2['beholdning']+$tmp[4]-($r['antal']-$r['leveret'])>=0) $spanbg="#FFFF00";	
					elseif ($r2['beholdning']-($r['antal']-$r['leveret'])<0) $spanbg="#FF0000";
					else $spanbg="#00FF00";
					if ($spanbg!="#00FF00") {
						$spantxt.="<tr bgcolor=$spanbg><td>$r[varenr]</td><td align=right>".dkdecimal($r2[beholdning]*1,0)."</td>";
						$spantxt.="<td align=right>".dkdecimal($r[antal]*1,0)."</td><td align=right>".dkdecimal($r[leveret]*1,0)."</td>";
						$spantxt.="<td align=right>$tmp[1]</td><td align=right>$tmp[2]</td><td align=right>$tmp[3]</td><td align=right>$tmp[4]</td></tr>";
						if (!$linjebg || $linjebg=="#FFFF00") {
							if ($r2['beholdning']-($r['antal']-$r['leveret'])<0 && $r2['beholdning']+$tmp[4]-($r['antal']-$r['leveret'])>=0) $linjebg="#FFFF00";	
							elseif ($r2['beholdning']-($r['antal']-$r['leveret'])<0) $linjebg="#FF0000";
						}
					} #else echo "$linjeantal<br>"; 
#				} elseif ($spanbg!="#00FF00") {
#					$spantxt.="<tr bgcolor=#00FF00><td>$r[varenr]</td><td colspan=7 align=center>Ikke lagervare</td></tr>";
				}
			}
			$spantxt.="<tr><td>Grøn</td><td colspan=7>Klar til levering (Varer vises ikke)</td></tr>";
			$spantxt.="<tr><td>Gul</td><td colspan=7>Mangler varer, varer er bestilt</td></tr>";
			$spantxt.="<tr><td>Rød</td><td colspan=7>Mangler varer, varer ikke bestilt</td></tr>";
			$spantxt.="</tbody></table>";
			if (!$linjebg) $linjebg="#00FF00";
#cho "$spantxt";
			print "<tr bgcolor=\"$linjebg\" title=''><td bgcolor=\"$bgcolor\">";
#			if ($linjebg!="#00FF00") print "<span onmouseover=\"return overlib('".$spantxt."', WIDTH=800);\" onmouseout=\"return nd();\"><u></u></span>";
			print "</td>";
		} else {
			if ($linjebg!=$bgcolor) {
				$linjebg=$bgcolor; $color='#000000';
			} else {
				$linjebg=$bgcolor5; $color='#000000';
			}
			print "<tr bgcolor=\"$linjebg\" title='$tr_title'><td bgcolor=$bgcolor></td>";
		}

		if ($row['art']=='DK') {
			print "<td align=$justering[0] $javascript style='color:$color'> (KN)&nbsp;$linjetext $understreg $row[ordrenr]$hrefslut</span><br></td>";
		} else {
			print "<td align=$justering[0] $javascript style='color:$color'>";
			if ($vis_lagerstatus && $linjebg!="#00FF00") print "<span onmouseover=\"return overlib('".$spantxt."', WIDTH=800);\" onmouseout=\"return nd();\">";
			print "$linjetext $understreg $row[ordrenr]$hrefslut";
			if ($vis_lagerstatus && $linjebg!="#00FF00") print "</span>";
			print "</span><br></td>";
		}
#		print "<td></td>";
		$row['ordredato']=dkdato($row['ordredate']);
#		print "<td>$ordredato<br></td>";
#		$levdato=dkdato($row['levdate']);
#		print "<td>$levdato<br></td>";
#		print"<td></td>";
		for ($x=1;$x<$vis_feltantal;$x++) {
			print "<td align=$justering[$x] style='color:$color'>";
			if ($vis_felt[$x]=="sum" || $vis_felt[$x]=='sum_m_moms') {
				if ($genberegn) $kostpris=genberegn($id);
				if ($valutakurs && $valutakurs!=100) {
					$sum=$sum*$valutakurs/100;
					$sum_m_moms=$sum_m_moms*$valutakurs/100;
#					$kostpris=$kostpris*$valutakurs/100; #20170520
#					$sum=bidrag($sum, $kostpris,'1');
#					print "a".dkdecimal($sum,2);
#					$tmp=dkdecimal($sum,2);
				} elseif ($valg!='faktura') {
					 if($vis_felt[$x]=="sum") print dkdecimal($sum,2);
					 else print dkdecimal($sum_m_moms,2);
				}
				if ($valg=='faktura') {
					$sum=bidrag($vis_felt[$x],$sum,$sum_m_moms,$kostpris,$udlignet);
#					if ($checked[$id]=='on' || $check_all) $checked[$id]='checked';
#					print "<td align=right><input class=\"inputbox\" type=\"checkbox\" name=\"checked[$ordreantal]\" $checked[$id]></td>";
#					print "<input type=hidden name=ordre_id[$ordreantal] value=$id>";
				} 
			} elseif ($vis_felt[$x]=='kundeordnr' && $valg=='faktura') {
				$tmp=$vis_felt[$x];
				if ($gem_id==$row['id']) print "<a href='$gem' download='$download' title='Højreklik og vælg \"gem som\"'><font color='green'>$row[$tmp]</font></a>";
				else print "<a href='formularprint.php?id=$row[id]&ordre_antal=1&formular=4&udskriv_til=fil'>$row[$tmp]</a>";
#				print "<span onclick=window.open('formularprint.php?id=$row[id]&ordre_antal=1&formular=4&udskriv_til=fil')>$row[$tmp]</span>";
			} elseif (strpos($vis_felt[$x],"date") || $vis_felt[$x]=="nextfakt") {
	 			print dkdato($row[$vis_felt[$x]]);
			} else {
				$tmp=$vis_felt[$x];
				print $row[$tmp];
			}
			print "</td>"; 
		}
		if ($uncheck_all) $checked[$id]=NULL;
		elseif ((isset($checked[$id]) && $checked[$id]=='on') || $check_all) $checked[$id]='checked';
		if ($valg=='faktura' || ($valg=='ordrer' && $nextfakt)) {
			$vis_ret_next=1;
			print "<td align=right><input class=\"inputbox\" type=\"checkbox\" name=\"checked[$id]\" $checked[$id]></td>";
		} else {
			if ($checked[$id]=='on' || $check_all) $checked[$id]='checked';
			print "<td><input class=\"inputbox\" type=\"checkbox\" name=\"checked[$id]\" $checked[$id]></td>";
		}
		print "<input type=hidden name=ordre_id[$l] value=$id>";
		$ialt+=$sum;	
		$ialt_m_moms+=$sum_m_moms;	
		$l++;
	}# endif ($lnr>=$start && $lnr<$slut)
}# endwhile

$colspan=$vis_feltantal+2;
if ($valg) {		
	if ($vis_projekt) $colspan++;
	if ($check_all) {
		print "<tr><td align=right colspan=$colspan><input type=\"submit\" style=\"width:100px\"; name=\"uncheck\" value=\"".findtekst(90,$sprog_id)."\">";
	} else {
		print "<tr><td align=right colspan=$colspan><input type=\"submit\" style=\"width:100px\"; name=\"check\" value=\"".findtekst(89,$sprog_id)."\">";
	}
#	print "<tr><td align=right colspan=$colspan><input type=\"submit\" style=\"width:100px\"; name=\"opdat\" value=\"Opdater\">";
	print "	</td></tr>\n";
	print "<input type=hidden name=ordre_antal value='$ordreantal'>";
	print "<input type=hidden name=valg value='$valg'>";
	print "<input type=hidden name=ordrenumre value='$ordrenumre'>";
	print "<input type=hidden name=kontonumre value='$kontonumre'>";
	print "<input type=hidden name=fakturanumre value='$fakturanumre'>";
	print "<input type=hidden name=ordredatoer value='$ordredatoer'>";
	print "<input type=hidden name=lev_datoer value='$lev_datoer'>";
	print "<input type=hidden name=fakturadatoer value='$fakturadatoer'>";
	print "<input type=hidden name=genfaktdatoer value='$genfaktdatoer'>";
	print "<input type=hidden name=summer value='$summer'>";
	print "<input type=hidden name=ref value='$ref[0]'>";
	print "<input type=hidden name=firma value='$firma'>";
	print "<input type=hidden name=kontoid value='$kontoid'>";
	print "<input type=hidden name=sort value='$sort'>";
	print "<input type=hidden name=nysort value='$nysort'>";
	print "</tr><tr><td colspan=$colspan align=right>";
	if ($valg=='faktura') {
	if ($genfakturer) print "<input type=submit value=\"Genfaktur&eacute;r\" name=\"submit\">&nbsp;";
	if (strlen("which ps2pdf")) {
		if (in_array('udskriv_til',$vis_felt)) {
			for ($i=1;$i<=count($vis_felt);$i++) {
				if ($vis_felt[$i]=='udskriv_til') $z=$i;
			}
#cho "fins $find[$z]<br>";		
			if ($find[$z]=='email') {
					print "<span title=\"Sender valgte fakturaer som e-mail\"><input type=submit style=\"width:100px\"; value=\"Send mails\" name=\"submit\" onclick=\"return confirm('Er du sikker på at du vil udsende de valgte $valg pr mail?')\"></span><br>";
			} 
		}  
			print "<span title=\"Udskriver valgte fakturaer som PDF\"><input type=submit style=\"width:100px\"; value=\"Udskriv\" name=\"submit\" onclick=\"return confirm('Udskriv de valgte $valg?')\"></span></td>";
	} else {
			print "<input type=submit value=\"Udskriv\" name=\"submit\" style=\"width:100px\"; disabled=\"disabled\"></td>";
	}
	} else {
		print "<input type=submit style=\"width:100px;\" value=\"Slet\" name=\"slet_valgte\" onclick=\"return confirm('Er du sikker på at du vil slette de valgte $valg?')\">";
	}
	print "</tr>\n";
}

if ($valg=="ordrer") {
#	if ($vis_projekt) $colspan++;
	if ($vis_ret_next) {
		if ($check_all) { 
#			print "<tr><td align=right colspan=$colspan><input type=\"submit\" style=\"width:100px;\" name=\"uncheck\" value=\"".findtekst(90,$sprog_id)."\">";
		} else {
#			print "<tr><td align=right colspan=$colspan><input type=\"submit\" style=\"width:100px;\" name=\"check\" value=\"".findtekst(89,$sprog_id)."\">";
		}
		print "	</td></tr>\n";
	}
	print "<input type=hidden name=ordre_antal value='$ordreantal'>";
	print "<input type=hidden name=valg value='$valg'>";
	print "<input type=hidden name=ordrenumre value='$ordrenumre'>";
	print "<input type=hidden name=kontonumre value='$kontonumre'>";
	print "<input type=hidden name=fakturanumre value='$fakturanumre'>";
	print "<input type=hidden name=ordredatoer value='$ordredatoer'>";
	print "<input type=hidden name=lev_datoer value='$lev_datoer'>";
	print "<input type=hidden name=fakturadatoer value='$fakturadatoer'>";
	print "<input type=hidden name=genfaktdatoer value='$genfaktdatoer'>";
	print "<input type=hidden name=summer value='$summer'>";
	print "<input type=hidden name=ref value='$ref[0]'>";
	print "<input type=hidden name=firma value='$firma'>";
	print "<input type=hidden name=kontoid value='$kontoid'>";
	print "<input type=hidden name=sort value='$sort'>";
	print "<input type=hidden name=nysort value='$nysort'>";
	print "</tr><tr><td colspan=$colspan align=right>";
#	if (in_array('on',$checked)) {
	if ($vis_ret_next) print "<span title='Klik her for at rette detaljer i abonnementsordrer'><input type=\"submit\" style=\"width:100px\"; value=\"Ret\" name=\"submit\"></td>";
#	}
}
print "</form></tr>\n";

if ($r=db_fetch_array(db_select("select id from grupper where art = 'OLV' and kode = '$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__))) {
	db_modify("update grupper set box1='$ordreliste' where id='$r[id]'",__FILE__ . " linje " . __LINE__);
} #else db_modify("insert into grupper (beskrivelse,kode,kodenr,art,box1) values ('Ordrelistevisning','$valg','$bruger_id','OLV','$ordreliste')",__FILE__ . " linje " . __LINE__);

#if ($valg=='tilbud') {$cols=7;}
#elseif ($valg=='faktura') {$cols=12;}
#else {$cols=8;}
#if ($vis_projekt) $cols++;
print "<tr><td colspan=$colspan><hr></td></tr>\n";
#$cols=$cols-4;
$dk_db=dkdecimal($ialt-$totalkost,2);		
if ($ialt!=0) {$dk_dg=dkdecimal(($ialt-$totalkost)*100/$ialt,2);}		
$ialt=dkdecimal($ialt,2);
$ialt_m_moms=dkdecimal($ialt_m_moms,2);
#$cols--;
print "<tr><td colspan=$colspan width=100%>";
print "<table border='0' width='100%'><tbody>";
if ($valg=='faktura') {
	print "<td width=30%></td><td width=40% align=center><span title= 'Klik for at genberegne DB/DG'><b><a href=ordreliste.php?genberegn=1&valg=$valg accesskey=G>Samlet oms&aelig;tning / db / dg (excl. moms.) </a></td><td width=30% align=right><b>$ialt / $dk_db / $dk_dg%</td></tr>\n";
	print "<td width=30%><br></td><td width=40% align=center><span title= ''><b>Samlet oms&aelig;tning incl. moms.</td><td width=30% align=right><b>$ialt_m_moms</td></tr>\n";
} else {
	print "<td width=30%>";
	if ($valg=='ordrer' && !$vis_lagerstatus) {
		print "<span title='Hold musen over de respektive ordrenumre for at se beholdninger mm'>";
		print "<a href=\"ordreliste.php?vis_lagerstatus=on&valg=$valg\">Vis lagerstatus</a>";
		print "</span>";
	}
	print "</td><td width=40% align=center>Samlet oms&aelig;tning incl./excl. moms</td><td width=30% align=right><b>$ialt_m_moms ($ialt)</td></tr>\n";
}
if ($genberegn==1) print "<meta http-equiv=\"refresh\" content=\"0;URL='ordreliste.php?genberegn=2&valg=$valg'\">";
#$cols++;
if ($valg=='faktura'){$cols++;}
#$cols=$cols+4;
print "<tr><td colspan=$colspan><hr></td></tr>\n";
$r=db_fetch_array(db_select("select box1 from grupper where art='MFAKT' and kodenr='1'",__FILE__ . " linje " . __LINE__));
if ($r['box1'] && $ialt!="0,00") {
	$tekst="Faktur&eacute;r alt som kan leveres?";
	print "<tr><td colspan=\"3\"><span title='Klik her for at importere en csv fil'><a href=csv2ordre.php target=\"_blank\">CSV import</a></span></td><td colspan=\"".($colspan-3)."\" align=right><span title='Klik her for at fakturere alle ordrer p&aring; listen'><a href=massefakt.php?valg=$valg onclick=\"return MasseFakt('$tekst')\">Faktur&eacute;r alt</a></span></td></tr>";
} else print "<tr><td colspan=\"3\"><span title='Klik her for at importere en csv fil'><a href=csv2ordre.php target=\"_blank\">CSV import</a></span></td></tr>";
#cho "select box4 from grupper where art='API'<br>";
$r=db_fetch_array(db_select("select box4 from grupper where art='API'",__FILE__ . " linje " . __LINE__));
$api_fil=trim($r['box4']);
if ($api_fil) {
	if (file_exists("../temp/$db/shoptidspkt.txt")) {
		$fp=fopen("../temp/$db/shoptidspkt.txt","r");
		$tidspkt=fgets($fp);
	} else $tidspkt = 0;
	fclose ($fp);
	if ($tidspkt < date("U")-1200 || $shop_ordre_id) {
		$fp=fopen("../temp/$db/shoptidspkt.txt","w");
		fwrite($fp,date("U"));
		fclose ($fp);
	$header="User-Agent: Mozilla/5.0 Gecko/20100101 Firefox/23.0";
#cho 	"/usr/bin/wget --spider --no-check-certificate --header='$header' $api_fil?put_new_orders=1 \n<br>";
		$api_txt="$api_fil?put_new_orders=1";
		exec ("nohup /usr/bin/wget  -O - -q  --no-check-certificate --header='$header' '$api_txt' > /dev/null 2>&1 &\n");
}	
}
$r=db_fetch_array(db_select("select box2 from grupper where art='DIV' and kodenr='5'",__FILE__ . " linje " . __LINE__));

if ($apifil=$r['box2']) {
	(strpos($r['box2'],'opdat_status=1'))?$opdat_status=1:$opdat_status=0;
	(strpos($r['box2'],'shop_fakt=1'))?$shop_fakt=1:$shop_fakt=0;
	(strpos($r['box2'],'betaling=kort'))?$kortbetaling=1:$kortbetaling=0;
	($kortbetaling)?$betalingsbet='betalingskort':$betalingsbet='netto+8';
	if (substr($apifil,0,4)=='http') {
		$apifil=trim(str_replace("/?","/hent_ordrer.php?",$apifil));
		$apifil=$apifil."&saldi_db=$db";
		$saldiurl="://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		if ($_SERVER['HTTPS']) $saldiurl="s".$saldiurl;
		$saldiurl="http".$saldiurl;
		if ($shop_fakt) {
			$qtxt="select max(shop_id) as shop_id from shop_ordrer";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$next_id=$r['shop_id']+1;
			$apifil.="&next_id=$next_id";
		}
		if ($shop_fakt) {
			$shop_ordre_id=if_isset($_GET['shop_ordre_id']);
			$shop_ordre_id*=1;
			$apifil.="&shop_fakt=$shop_fakt&popup=1&shop_ordre_id=$shop_ordre_id";
		}	
		$apifil.="&saldiurl=$saldiurl";
		$apifil.="&random=".rand();
		if ($shop_fakt) {
			if (file_exists("../temp/$db/shoptidspkt.txt")) {
				$fp=fopen("../temp/$db/shoptidspkt.txt","r");
				$tidspkt=fgets($fp);
			} else $tidspkt = 0;
			fclose ($fp);
			if ($tidspkt < date("U")-300 || $shop_ordre_id) {
				$fp=fopen("../temp/$db/shoptidspkt.txt","w");
				fwrite($fp,date("U"));
				fclose ($fp);
				if ($db=='bizsys_52') {
					print "<BODY onload=\"javascript:window.open('$apifil','hent:ordrer','width=10,height=10,top=1024,left=1280')\">";
				} else exec ("nohup /usr/bin/wget --spider $api_fil  > /dev/null 2>&1 &\n");
			} else {
				$tjek=$next_id-50;
				$qtxt="select shop_id from shop_ordrer where shop_id >= '$tjek'";
				$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
				while ($r=db_fetch_array($q)) {
					while ($r['shop_id']!=$tjek && $tjek<$next_id) {
#						echo "Shop ordre $r[shop_id] $tjek mangler<br>";
						$tmp=$apifil."&shop_ordre_id=$tjek";
#cho "$tmp<br>";						
						print "<BODY onload=\"javascript:window.open('$tmp'	,'hent:ordrer','width=10,height=10,top=1024,left=1280')\">";
						$tjek++;
					} 					
#					echo "Shop ordre $r[shop_id] $tjek OK<br>";
					$tjek++;
				}
	}
#			if ($db=='bizsys_49')	print "<tr><td colspan=\"3\"><span title='Klik her for at hente nye ordrer fra shop' onclick=\"javascript:window.open('$apifil')\"><a href>Hent ordrer fra shop</a></span></td></tr>";	
		} else print "<tr><td colspan=\"3\"><span title='Klik her for at hente nye ordrer fra shop' onclick=\"JavaScript:window.open('$apifil','hent:ordrer','width=10,height=10,top=1024,left=1280')\">SHOP import</span></td></tr>";	
}
}
print "</tbody></table></td>";

#print "<body onload=\"javascript:window.open('$url','opdat:beholdning');\">";
function genberegn($id) {
	$kostpris=0;
	$q0 = db_select("select id,vare_id,antal,pris,kostpris,saet,samlevare from ordrelinjer where ordre_id = $id and posnr>0 and vare_id > 0",__FILE__ . " linje " . __LINE__);
	while ($r0=db_fetch_array($q0)) {
		if ($r1=db_fetch_array(db_select("select provisionsfri, gruppe from varer where id = '$r0[vare_id]'",__FILE__ . " linje " . __LINE__))) {
			if ((!$r1[provisionsfri])&&($r1=db_fetch_array(db_select("select box9 from grupper where art = 'VG' and ".nr_cast(kodenr)."='$r1[gruppe]' and box9 = 'on' ",__FILE__ . " linje " . __LINE__)))) {
/*
				$batch_tjek='0';
				$q1 = db_select("select antal, batch_kob_id from batch_salg where linje_id = '$r0[id]' and batch_kob_id != 0",__FILE__ . " linje " . __LINE__);	
				while ($r1=db_fetch_array($q1)) {
					if ($r2=db_fetch_array(db_select("select pris, fakturadate, linje_id from batch_kob where id = '$r1[batch_kob_id]'",__FILE__ . " linje " . __LINE__))) {
						if ($r2['fakturadate']<'2000-01-01') $r2=db_fetch_array(db_select("select pris from ordrelinjer where id = '$r2[linje_id]'",__FILE__ . " linje " . __LINE__));
						$batch_tjek=1;
						$tmpp=$r2['pris']*$r1['antal'];
						$kostpris=$kostpris+$r2['pris']*$r1['antal'];
					}
				}
				if ($batch_tjek<1) {
					$r2=db_fetch_array(db_select("select kostpris from varer where id = $r0[vare_id]",__FILE__ . " linje " . __LINE__));	
					$kostpris=$kostpris+$r2['kostpris']*$r0['antal'];
				}
*/
			$kostpris+=$r0['kostpris']*$r0['antal'];
			}
			elseif ($r1['provisionsfri']) $kostpris+=$r0['pris']*$r0['antal'];
			else {	
				if ($r0['saet'] && $r0['samlevare'] && $r0['kostpris']) { 
					$r0['kostpris']=0;
					db_modify("update ordrelinjer set kostpris='0' where id = '$r0[id]'");
				}
				$kostpris+=$r0['kostpris']*$r0['antal'];
#					$r2=db_fetch_array(db_select("select kostpris from varer where id = $r0[vare_id]",__FILE__ . " linje " . __LINE__));	
#					$kostpris=$kostpris+$r2['kostpris']*$r0['antal'];
			}
		}
	} 
	db_modify("update ordrer set kostpris=$kostpris where id = $id",__FILE__ . " linje " . __LINE__);#xit;
	return $kostpris;
}

function bidrag ($feltnavn,$sum,$sum_m_moms,$kostpris,$udlignet){
	global $ialt;
	global $totalkost;
	global $genberegn;

	$ialt=$ialt+$sum;
	$totalkost=$totalkost+$kostpris;
	$dk_db=dkdecimal($sum-$kostpris,2);		
	$sum=round($sum,2);
	$kostpris=round($kostpris,2);
	if ($sum) $dk_dg=dkdecimal(($sum-$kostpris)*100/$sum,2);		
	else $dk_dg='0,00';
	($feltnavn=='sum')?$tmp=$sum:$tmp=$sum_m_moms;
	$tmp=dkdecimal($tmp,2);
	if ($genberegn) {print "<span title= 'db: $dk_db - dg: $dk_dg%'>$tmp/$dk_db/$dk_dg%<br></span>";}
	else {
		if ($udlignet) $span="style='color: #000000;' title='db: $dk_db - dg: $dk_dg%'";
		else $span="style='color: #FF0000;' title='Ikke udlignet\r\ndb: $dk_db - dg: $dk_dg%'";
		print "<span $span>$tmp<br></span>";
	}
}

?>
</tbody>
</table>
	</td></tr>
</tbody></table>

</body></html>
