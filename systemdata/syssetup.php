<?php
// --- systemdata/syssetup.php --- lap 4.1.0 -- 2024-06-04 --
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
// Copyright (c) 2003-2024 saldi.dk aps
// ----------------------------------------------------------------------
//
// 20132127 Indsat kontrol for at kodenr er numerisk på momskoder.
// 20140621 Ændret "kontrol for at kodenr er numerisk på momskoder" til at acceptere "-".
// 20141002 Tilføjet felt for omvendt betaling på kunder og varer.
// 20141212 CA  Tekstbokse i CSS som erstatning for JavaScript Alert-bokse. Søg 20141212A
// 20141212 CA  Variablen spantilte ændret til spantitle. Søg 20141212B
// 20150130 CA  Test af Lager Tilgang og Lager Træk ved den anden angivet i stedet for ved Lagerført. Søg 20150130
// 20150424 CA  Omdøbt funktionen udskriv til skriv_formtabel og flyttet den til filen skriv_formtabel.inc.php
// 20160118 PHR $kode blev ikke sat 20160118 
// 20160808 PHR function nytaar $box3,$box3 rettet til box2,box3 (Tak til forumbruger 'ht'). 
// 20161022 PHR tilretning iht flere afd pr lager. 20161022
// 20170405 PHR	Ganger resultat med 1 for at undgå NULL værdier
// 20181102 PHR Oprydning, udefinerede variabler.
// 20181220 MSC - Rettet isset fejl
// 20190221 MSC - Rettet topmenu design
// 20190221 MSC - Rettet isset fejl
// 20190225 MSC - Rettet topmenu design
// 20200308 PHR	Added Mysqli
// 20200308 PHR Removed 'Lagertilgang', 'Lagertræk' & 'Lagerregulering' from 'Varegrupper' 
// 20200512 PHR	Removed $box5 from 3. instance of skriv_formtabel in 'varegrupper'
// 20200512 PHR	Different changes for changes 30300308 to look nice in Firefox
// 20210513 Loe	These texts were translated but not entered here previously
// 20220607 MSC - Implementing new design
// 20220614 MSC - Added div class divSys
// 20240407 PHR - save moved to syssetupIncludes/saveData.php
// 20240604 PHR PHP8

@session_start();
$s_id=session_id();

$nopdat=NULL;	

$modulnr=1;
$title="Systemsetup";
$css="../css/standard.css";
$genberegn=NULL;


include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("skriv_formtabel.inc.php");
include("../includes/genberegn.php");

if (!isset ($fejl)) $fejl = NULL;
$dd=date("Y-m-d");

if ($menu=='T') {
#	print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
	include_once '../includes/top_header.php';
	include_once '../includes/top_menu.php';
	print "<div id=\"header\">"; 
	print "<div class=\"headerbtnLft headLink\">&nbsp;&nbsp;&nbsp;</div>";     
	print "<div class=\"headerTxt\">$title</div>";     
	print "<div class=\"headerbtnRght headLink\">&nbsp;&nbsp;&nbsp;</div>";     
	print "</div>";
	print "<div class='content-noside'>";
	print "<div id=\"leftmenuholder\">";
	include_once 'left_menu.php';
	print "</div><!-- end of leftmenuholder -->\n";
	print "<div class=\"maincontentLargeHolder\">\n";
	print "<center><table border=\"0\" cellspacing=\"0\" id=\"dataTable\" class=\"dataTableSys\" width='100%' height='350px'><tbody>";
} elseif ($menu=='S') {
	include("top.php");
	print "<table cellpadding=\"1\" cellspacing=\"1\" border=\"1\"><tbody>";
} else {
	include("oldTop.php");
	print "<table cellpadding=\"1\" cellspacing=\"1\" border=\"1\"><tbody>";
}
$valg=if_isset($_GET['valg']);

include_once("syssetupIncludes/saveData.php");

##############################################################################################################################
if ($nopdat!=1) {
	$x=0;
($valg=="projekter")?$sort='kodenr desc':$sort='kodenr';
#	else {
#		if ($db_type=='mysql' || $db_type=='mysqli') $tmp="CAST(kodenr AS SIGNED)";
#		else $tmp="to_number(textcat('0',kodenr),text(99999999))";
#	} 
	$feltbredde=6;
	$stockIO=NULL;
	$qtxt = "SELECT * FROM grupper ";
	$qtxt.= "WHERE ((art = 'SM' OR art = 'KM'  OR art = 'EM' OR art = 'YM' OR art = 'MR' OR art = 'DG' OR art = 'KG' ";
	$qtxt.= "OR art = 'VG' OR art = 'POS' OR art = 'OreDif') and fiscal_year = '$regnaar') ";
	$qtxt.= "OR art = 'AFD' OR art = 'LG' OR art = 'VPG' OR art = 'VTG' OR art = 'VRG' ";
	$qtxt.= "order by kodenr";
	if ($valg=="projekter") $qtxt.=' desc';
	$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($row = db_fetch_array($q)){
		$x++;
		$id[$x]=$row['id'];
		$beskrivelse[$x]=htmlentities(stripslashes($row['beskrivelse']),ENT_COMPAT,$charset);
		$kodenr[$x]=$row['kodenr'];
		if (strlen($kodenr[$x]) > $feltbredde) $feltbredde=strlen($kodenr[$x]); 
		$kode[$x]=$row['kode'];
		$art[$x]=$row['art'];
		$box1[$x]=$row['box1'];
		$box2[$x]=$row['box2'];
		$box3[$x]=$row['box3'];
		$box4[$x]=$row['box4'];
		$box5[$x]=$row['box5'];
		$box6[$x]=$row['box6'];
		$box7[$x]=$row['box7'];
		$box8[$x]=$row['box8'];
		$box9[$x]=$row['box9'];
		$box10[$x]=$row['box10'];
		$box11[$x]=$row['box11'];
		$box12[$x]=$row['box12'];
		$box13[$x]=$row['box13'];
		$box14[$x]=$row['box14'];
		if ($art[$x]=='VG' && $box1[$x] && $box2[$x]) $stockIO=1;
	}
}
if (!$valg) $valg='moms';
$y=$x+1;
print "<tr><td valign = top><table border=0><tbody>";
print "<form name=syssetup action=syssetup.php method=post>";
if ($valg=='moms'){
	$spantxt1='En beskrivende tekst efter eget valg';
	$spantxt2='Det nummer i kontoplanen som salgsmomsen skal konteres p&aring;.';
	$spantxt3='Moms %.';
	$spantxt4='Map til';
	$spantxt5='Momskode hos SKAT';
	print "<tr><td></td><td colspan=3><b><span title='Den moms du skal betale til SKAT'>".findtekst(994,$sprog_id)."</span></td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td align=\"center\"><span title='$spantxt1'>".findtekst(914,$sprog_id)."</span></td>";
	print "<td align=\"center\"><span title='$spantxt2'>".findtekst(440,$sprog_id)."</span></td>";
	print "<td align=\"center\"><span title='$spantxt3'>".findtekst(995,$sprog_id)."</span></td>";
	print "<td></td><td align=\"center\"><span title='$spantxt5'>$spantxt4</span></td></tr>\n";		#20210513
	$y=skriv_formtabel('SM',$x,$y,$art,$id,'S',$kodenr,$beskrivelse,$box1,'6' ,$box2,'6','','6',$box4,'6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','2');
	print "<tr><td><br></td></tr>\n";
	$spantxt2='Det nummer i kontoplanen som k&oslash;bsmomsen skal konteres p&aring;.';
	print "<tr><td></td><td colspan=3><b><span title='Den moms du skal have retur fra SKAT'>".findtekst(996,$sprog_id)."</span></td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td align=\"center\"><span title='$spantxt1'>".findtekst(914,$sprog_id)."</span></td>";
	print "<td align=\"center\"><span title='$spantxt2'>".findtekst(440,$sprog_id)."<span></td>";
	print "<td align=\"center\"><span title='$spantxt3'>".findtekst(995,$sprog_id)."</span></td>\n";
	print "<td></td><td align=\"center\"><span title='$spantxt5'>$spantxt4</span></td></tr>\n";		#20210513
	$y=skriv_formtabel('KM',$x,$y,$art,$id,"K",$kodenr,$beskrivelse,$box1,'6',$box2,'6','','6',$box4,'6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','2');
	print "<tr><td><br></td></tr>\n";
	$spantxty2='Konto til postering af salgsmoms for ydelsesk&oslash;b i udlandet';
	$spantxty4='Konto til postering af k&oslash;bsmoms for ydelsesk&oslash;b i udlandet';
	$spantxty5="Ved ydelsesk&oslash;b i udlandet,skal der betales dansk moms p&aring; vegne af s&aelig;lgeren. \nSamtidig kan k&oslash;bsmomsen tr&aelig;kkes fra s&aring; resultatet bliver 0.";
	print "<tr><td></td><td colspan=3><b><span title='$spantxty5'>".findtekst(997,$sprog_id)."</td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td align=\"center\"><span title='$spantxt1'>".findtekst(914,$sprog_id)."</span></td><td align=\"center\"><span title='$spantxt2'>".findtekst(440,$sprog_id)."<span></td><td align=\"center\"><span title='$spantxt3'>".findtekst(995,$sprog_id)."</span></td><td align=\"center\"> <span title='$spantxt4'>".findtekst(1013,$sprog_id)."</span></td>\n";
	print "<td align=\"center\"><span title='$spantxt5'>$spantxt4</span></td></tr>\n";		#20210513
	$y=skriv_formtabel('YM',$x,$y,$art,$id,"Y",$kodenr,$beskrivelse,$box1,'6',$box2,'6',$box3,'6',$box4,'6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','2');
	print "<tr><td><br></td></tr>\n";
	$spantxt2='Konto til postering af salgsmoms for k&oslash;b i udlandet';
	$spantxte4='Konto til postering af k&oslash;bsmoms for k&oslash;b i udlandet';
	$spantxte5="Ved varek&oslash;b i udlandet,skal der betales dansk moms p&aring; vegne af s&aelig;lgeren. \nSamtidig kan k&oslash;bsmomsen tr&aelig;kkes fra s&aring; resultatet bliver 0";
	print "<tr><td></td><td colspan=3><b><span title='$spantxte5'>".findtekst(998,$sprog_id)."</span></b></td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td align=\"center\"><span title='$spantxt1'>".findtekst(914,$sprog_id)."</span></td><td align=\"center\"><span title='$spantxt2'>".findtekst(440,$sprog_id)."<span></td><td align=\"center\"><span title='$spantxt3'>".findtekst(995,$sprog_id)."</span></td><td align=\"center\"> <span title='$spantxt4'>".findtekst(1013,$sprog_id)."</span></td>\n";
	print "<td align=\"center\"><span title='$spantxt5'>$spantxt4</span></td></tr>\n";		#20210513
	$y=skriv_formtabel('EM',$x,$y,$art,$id,"E",$kodenr,$beskrivelse,$box1,'6',$box2,'6',$box3,'6',$box4,'6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','2');
	print "<tr><td><br></td></tr>\n";
	print "<tr><td></td><td colspan=3><b>".findtekst(1009,$sprog_id)."</b></td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td align=\"center\"><span title='$spantxt1'>".findtekst(914,$sprog_id)."</span></td><td align=\"center\"><span title='F&oslash;rste kontonummer som skal indg&aring; i rapporten'>Fra</span></td><td align=\"center\"><span title='Sidste kontonummer som skal indg&aring; i rapporten'>Til</span></td><td><span title='Kontonummer for samlet varek&oslash;b i EU'>Rubrik A1</span></td><td><span title='Kontonummer for samlet ydelsesk&oslash;b i EU'>Rubrik A2</span></td><td><span title='Kontonummer for samlet varesalg i EU'>Rubrik B1</span></td><td><span title='Kontonummer for samlet ydelsessalg i EU'>Rubrik B2</span></td><td><span title='Kontonummer for samlet vare- og ydelsessalg uden for EU'>Rubrik C</span></td></tr>\n";
	$y=skriv_formtabel('MR',$x,$y,$art,$id,"R",$kodenr,$beskrivelse,$box1,'6',$box2,'6',$box3,'6',$box4,'6',$box5,'6',$box6,'6',$box7,'6','-','6','-','6','-','6','-','6','-','6','-','6','-','2');
}
elseif($valg=='debitor'){
	print "<tr><td>";
	print infoboks('<span style=\'font-size:80%; font-weigth:bold; padding:0px 2px 0px 2px; font-family:monospace; background: #0000ff; color: #ffffff\'>i</span>', '<h2>Debitorhjælp</h2><p>Her er lidt tekst omkring brugen af debitorgrupper.</p>', 'info', 'infoboks1');
	print "</td><td colspan=2><b>".findtekst(1008,$sprog_id)."</td><td></td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td align=\"center\">".findtekst(914,$sprog_id)."</td><td align=\"center\"><span title='Momsgruppe som debitorgruppen skal tilknyttes'>".findtekst(1011,$sprog_id)."</span></td><td align=\"center\"><span title='Samlekonto for debitorgruppen'>Samlekt.</span></td><td align=\"center\">".findtekst(776,$sprog_id)."</td>";
	print "<td align=\"center\"><span title=\"".findtekst(1010,$sprog_id)."\">".findtekst(801,$sprog_id)."</td>";
	print "<td align=\"center\"><span title=\"Modkonto ved udligning af &aring;bne poster\">".findtekst(1013,$sprog_id)."</td>";
#	$spantitle="RABAT!\nHer angives rabatsatsen i procent for kundegruppen."; # 20141212B spantilte -> spantitle (start)
#	print "<td align=\"center\"><span title=\"".$spantitle."\">Rabat</td>";
	$spantitle="Provisionsprocent!\nHer angives hvor stor en procentdel af d&aelig;kningsbidraget det medg&aring;r ved beregning af provision.";
	print "<td align=\"center\"><span title=\"".$spantitle."\">".findtekst(657,$sprog_id)."</td>\n";
	$spantitle="Business to business!\nAfm&aelig;rk her,hvis der skal anvendes b2b priser ved salg til denne kundegruppe";
	print "<td align=\"center\"><span title=\"".$spantitle."\">B2B</td>\n";
	$spantitle="Omvendt betaligspligt!\nAfm&aelig;rk her,hvis denne kundegruppe er omfattet af omvendt betalingspligt";
 	print "<td align=\"center\"><span title=\"".$spantitle."\">OB</td></tr>\n"; # 20141212B spantilte -> spantitle (slut)
#cho "$id[$x] $beskrivelse[$x]<br>";
	$y=skriv_formtabel('DG',$x,$y,$art,$id,'D',$kodenr,$beskrivelse,$box1,'6',$box2,'6',$box3,'10',$box4,'10',$box5,'6','-','4',$box7,'4',$box8,'checkbox',$box9,'checkbox','-','2','-','2','-','2','-','2','-','2');
	print "<tr><td><br></td></tr>\n";
	print "<tr><td></td><td colspan=2><b>Kreditorgrupper</td><td></td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td align=\"center\">".findtekst(914,$sprog_id)."</td><td align=\"center\"><span title='Momsgruppe som debitorgruppen skal tilknyttes'>".findtekst(1011,$sprog_id)."</span></td>";
	print "<td align=\"center\"><span title='Samlekonto for debitorgruppen'>Samlekt.</span></td><td align=\"center\">".findtekst(776,$sprog_id)."</td>";
	print "<td align=\"center\"><span title=\"Det sprog der skal anvendes ved kommunikation med kreditoren\">".findtekst(801,$sprog_id)."</span></td>";
	print "<td align=\"center\"><span title=\"Modkonto ved udligning af &aring;bne poster\">".findtekst(1013,$sprog_id)."</span></td>";
	print "<td align=\"center\"><span title=\"Momsgruppe for salgsmoms ved omvendt betalingspligt\">S.moms grp.</span></td>";
	print "<td align=\"center\" title=\"Omvendt betaligspligt!\nAfm&aelig;rk her,hvis denne leverandørgruppe er omfattet af omvendt betalingspligt\">O/B<!-- box9 --></td></tr>\n";
#	print "<td align=\"center\"><span title=\"Omvendt betaligspligt!\"Afm&aelig;rk her,hvis denne leverandørgruppe er omfattet af omvendt betalingspligt>O/B</span></td></tr>\n";
	$y=skriv_formtabel('KG',$x,$y,$art,$id,'K',$kodenr,$beskrivelse,$box1,'6',$box2,'6',$box3,'10',$box4,'10',$box5,'10',$box6,'6','-','6','-','6',$box9,'checkbox','-','6','-','6','-','6','-','6','-','2');
}
elseif($valg=='afdelinger'){
	print "<tr><td></td><td colspan=3 align=\"center\"><b>".findtekst(772,$sprog_id)."</td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td>".findtekst(914,$sprog_id)."</td><td>".findtekst(608,$sprog_id)."</td></tr>\n";
	$y=skriv_formtabel('AFD',$x,$y,$art,$id,'&nbsp;',$kodenr,$beskrivelse,$box1,'10',"-",'2',"-",'2','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','2');
}
elseif($valg=='projekter'){
	print "<tr><td></td><td colspan=3 align=\"center\"><b>".findtekst(773,$sprog_id)."</td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td>".findtekst(914,$sprog_id)."</td></tr>\n";
	$y=skriv_formtabel('PRJ',$x,$y,$art,$id,'&nbsp;',$kodenr,$beskrivelse,'-','2',"-",'2',"-",'2','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','2');
}
elseif($valg=='lagre'){
	print "<tr><td></td><td colspan=3 align=\"center\"><b>".findtekst(3,$sprog_id)."</td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td>".findtekst(914,$sprog_id)."</td><td align=\"center\">Afd.</td></tr>\n";
	$y=skriv_formtabel('LG',$x,$y,$art,$id,'&nbsp;',$kodenr,$beskrivelse,$box1,'2',"-",'2',"-",'2','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','2');
}
elseif($valg=='varer'){
	$t6="Hvis varegruppen er omfattet af omvendt betalingspligt afmærkes dette felt";
	$q = db_select("select id from grupper where art = 'DIV' and kodenr = '2' and box4='on'",__FILE__ . " linje " . __LINE__);
	if (db_fetch_array($q)){
		print "<tr><td></td><td colspan=10 align=\"center\"><b>".findtekst(774,$sprog_id)."</td></tr><tr><td colspan=13><hr></td></tr>\n";
		print "<tr>";
		print "<td align=\"center\"></td><td></td><td></td>";
		if ($stockIO) print "<td align=\"center\">".findtekst(608,$sprog_id)."-</td><td align=\"center\">".findtekst(608,$sprog_id)."-</td>";
		print "<td align=\"center\"><!--K&oslash;b--></td><td align=\"center\"><!--".findtekst(1007,$sprog_id)."--></td>";
		#<td align=\"center\">Lager-</td>";
		print "<td title=\"$t6\" align=\"center\">Omvendt-</td><td align=\"center\">".findtekst(770,$sprog_id)."-</td><td align=\"center\">".findtekst(608,$sprog_id)."-</td><td align=\"center\">Batch-</td><td align=\"center\">Opera-</td>\n";
		print "<td title='Kontonummer for enten k&oslash; af Varek&oslash;b i EU (Rubrik A1) eller Ydelsesk&oslash;b i EU (Rubrik A2) - se Indstillinger - Moms'>".findtekst(1012,$sprog_id)."</td>\n";
		print "<td title='Kontonummer for enten Varesalg til EU (Rubrik B1) eller Ydelsessalg til EU (Rubrik B2) - se Indstillinger - Moms'>".findtekst(1007,$sprog_id)."</td>\n";
		print "<td title='Kontonummer for en af Varek&oslash;b uden for EU, Ydelsesk&oslash;b uden for EU eller Vare- og ydelsesk&oslash;b uden for EU.'>".findtekst(1012,$sprog_id)." uden</td>\n";
		print "<td title='Kontonummer for en af Varesalg uden for EU, Ydelsessalg uden for EU eller Vare- og ydelsessalg uden for EU (Rubrik C). Hvis en af de to f&oslash;rste angives, s&aring; skal kontonummeret v&aelig;re blandt de kontonumre, som summeres til en samlekonto for Vare- og ydelsessalg uden for EU (Rubrik C).'>Salg uden</td></tr>\n";
		print "<tr><td></td><td>Nr.</td><td align=\"center\">".findtekst(914,$sprog_id)."</td>";
		if ($stockIO) print "<td align=\"center\">tilgang</td><td align=\"center\">tr&aelig;k</td>";
		print "<td align=\"center\">".findtekst(1012,$sprog_id)."</td><td align=\"center\">Salg<!--".findtekst(1007,$sprog_id)."--></td>";
		#<td align=\"center\">regulering</td>
		print "<td  title=\"$t6\" align=\"center\">betaling</td><td align=\"center\">fri</td><td align=\"center\">f&oslash;rt</td><td>kontrol</td><td align=\"center\">tion</td>\n";
		print "<td title='Kontonummer for enten Varek&oslash;b i EU (Rubrik A1) eller Ydelsesk&oslash;b i EU (Rubrik A2) - se Indstillinger - Moms'>i EU</td>\n";
		print "<td title='Kontonummer for enten Varesalg til EU (Rubrik B1) eller Ydelsessalg til EU (Rubrik B2) - se Indstillinger - Moms'>til EU</td>\n";
		print "<td title='Kontonummer for en af Varek&oslash;b uden for EU, Ydelsesk&oslash;b uden for EU eller Vare- og ydelsesk&oslash;b uden for EU.'>for EU</td>\n";
		print "<td title='Kontonummer for en af Varesalg uden for EU, Ydelsessalg uden for EU eller Vare- og ydelsessalg uden for EU (Rubrik C). Hvis en af de to f&oslash;rste angives, s&aring; skal kontonummeret v&aelig;re blandt de kontonumre, som summeres til en samlekonto for Vare- og ydelsessalg uden for EU (Rubrik C).'>for EU</td></tr>\n";
		if ($stockIO) {
		$y=skriv_formtabel('VG',$x,$y,$art,$id,'&nbsp;',$kodenr,$beskrivelse,$box1,'4',$box2,'4',$box3,'4',$box4,'4','-','',$box6,'checkbox',$box7,'checkbox',$box8,'checkbox',$box9,'checkbox',$box10,'checkbox',$box11,'4',$box12,'4',$box13,'4',$box14,'4');
	} else {
			$y=skriv_formtabel('VG',$x,$y,$art,$id,'&nbsp;',$kodenr,$beskrivelse,'-','','-','',$box3,'4',$box4,'4','-','',$box6,'checkbox',$box7,'checkbox',$box8,'checkbox',$box9,'checkbox',$box10,'checkbox',$box11,'4',$box12,'4',$box13,'4',$box14,'4');
		}
	} else {
		print "<tr><td colspan=20 align=\"center\"><b>".findtekst(774,$sprog_id)."</td></tr><tr><td colspan=20><hr></td></tr>\n";
		print "<tr><td  title=\"$t6\" align=\"center\"></td><td></td><td></td>";
		if ($stockIO) {
			print "<td align=\"center\">".findtekst(608,$sprog_id)."-</td><td align=\"center\">".findtekst(608,$sprog_id)."-</td>";
		}	
		print "<td align=\"center\">".findtekst(110,$sprog_id)."-</td><td align=\"center\">".findtekst(110,$sprog_id)."-</td>";
#		print "<td align=\"center\">Lager-</td>";
		print "<td align=\"center\">Omvendt-</td><td align=\"center\">".findtekst(770,$sprog_id)."-</td><td align=\"center\">".findtekst(608,$sprog_id)."-</td><td align=\"center\">Batch-</td><td align=\"center\">Opera-</td>\n";
		print "<td title='Kontonummer for enten k&oslash; af Varek&oslash;b i EU (Rubrik A1) eller Ydelsesk&oslash;b i EU (Rubrik A2) - se Indstillinger - Moms'>".findtekst(1012,$sprog_id)."</td>\n";
		print "<td title='Kontonummer for enten Vare".findtekst(1007,$sprog_id)." til EU (Rubrik B1) eller Ydelsessalg til EU (Rubrik B2) - se Indstillinger - Moms'>".findtekst(1007,$sprog_id)."</td>\n";
		print "<td title='Kontonummer for en af Varek&oslash; uden for EU, Ydelsesk&oslash; uden for EU eller Vare- og ydelsesk&oslash; uden for EU.'>K&oslash;b uden</td>\n";
		print "<td title='Kontonummer for en af Varesalg uden for EU, Ydelsessalg uden for EU eller Vare- og ydelsessalg uden for EU (Rubrik C). Hvis en af de to f&oslash;rste angives, s&aring; skal kontonummeret v&aelig;re blandt de kontonumre, som summeres til en samlekonto for Vare- og ydelsessalg uden for EU (Rubrik C).'>Salg uden</td></tr>\n";
		print "<tr><td></td><td>Nr.</td><td align=\"center\">".findtekst(914,$sprog_id)."</td>";
		if ($stockIO) print "<td align=\"center\">tilgang</td><td align=\"center\">tr&aelig;k</td>";
		print "<td align=\"center\">".findtekst(1012,$sprog_id)."</td><td align=\"center\">".findtekst(1007,$sprog_id)."</td>";
#		print "<td align=\"center\">regulering</td>";
		print "<td  title=\"$t6\" align=\"center\">betaling</td><td align=\"center\">fri</td><td align=\"center\">f&oslash;rt</td><td align=\"center\">kontrol</td><td align=\"center\">tion</td>\n";
		print "<td title='Kontonummer for enten k&oslash; af Varek&oslash;b i EU (Rubrik A1) eller Ydelsesk&oslash;b i EU (Rubrik A2) - se Indstillinger - Moms'>i EU</td>\n";
		print "<td title='Kontonummer for enten Varesalg til EU (Rubrik B1) eller Ydelsessalg til EU (Rubrik B2) - se Indstillinger - Moms'>til EU</td>\n";
		print "<td title='Kontonummer for en af Varek&oslash; uden for EU, Ydelsesk&oslash; uden for EU eller Vare- og ydelsesk&oslash; uden for EU.'>for EU</td>\n";
		print "<td title='Kontonummer for en af Varesalg uden for EU, Ydelsessalg uden for EU eller Vare- og ydelsessalg uden for EU (Rubrik C). Hvis en af de to f&oslash;rste angives, s&aring; skal kontonummeret v&aelig;re blandt de kontonumre, som summeres til en samlekonto for Vare- og ydelsessalg uden for EU (Rubrik C).'>for EU</td></tr>\n";
		if ($stockIO) {
			$y=skriv_formtabel('VG',$x,$y,$art,$id,'&nbsp;',$kodenr,$beskrivelse,$box1,'4',$box2,'4',$box3,'4',$box4,'4','-','',$box6,'checkbox',$box7,'checkbox',$box8,'checkbox',$box9,'checkbox',$box10,'checkbox',$box11,'4',$box12,'4',$box13,'4',$box14,'4');
		} else {
			$y=skriv_formtabel('VG',$x,$y,$art,$id,'&nbsp;',$kodenr,$beskrivelse,'-','','-','',$box3,'4',$box4,'4','-','',$box6,'checkbox',$box7,'checkbox',$box8,'checkbox',$box9,'checkbox',$box10,'checkbox',$box11,'4',$box12,'4',$box13,'4',$box14,'4');
		}
	}
	print "<tr><td colspan=20 align=\"center\"><hr><b>Prisgrupper</td></tr><tr><td colspan=20><hr></td></tr>\n";
	print "<tr><td colspan=20><table width='100%' align=\"center\"><tbody>";
	print "<tr><td align=\"center\"></td><td></td><td></td><td align=\"center\">Kost-</td><td align=\"center\">".findtekst(1007,$sprog_id)."-</td><td align=\"center\">Vejl-</td><td align=\"center\">B2B-</td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td align=\"center\">".findtekst(914,$sprog_id)."</td><td align=\"center\">pris</td><td align=\"center\">pris</td><td align=\"center\">pris</td><td align=\"center\">pris</td></tr>\n";
	$y=skriv_formtabel('VPG',$x,$y,$art,$id,'&nbsp;',$kodenr,$beskrivelse,$box1,'4',$box2,'4',$box3,'4',$box4,'4','-','6','-','2','-','0','-','0','-','0','-','0','-','0','-','0','-','0','-','0');
	print "</tbody></table></td></tr>";
	print "<tr><td colspan=20 align=\"center\"><hr><b>Tilbudsgrupper</td></tr><tr><td colspan=20><hr></td></tr>\n";
	print "<tr><td colspan=20><table width='100%'><tbody>";
	print "<tr><td align=\"center\"></td><td></td><td></td><td align=\"center\">Kost-</td><td align=\"center\">".findtekst(1007,$sprog_id)."-</td><td align=\"center\">Start-</td><td align=\"center\">Slut-</td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td align=\"center\">".findtekst(914,$sprog_id)."</td><td align=\"center\">pris</td><td align=\"center\">pris</td><td align=\"center\">dato</td><td align=\"center\">dato</td></tr>\n";
	$y=skriv_formtabel('VTG',$x,$y,$art,$id,'&nbsp;',$kodenr,$beskrivelse,$box1,'4',$box2,'4',$box3,'7',$box4,'7','-','6','-','2','-','0','-','0','-','0','-','0','-','0','-','0','-','0','-','0');
	print "</tbody></table></td></tr>";
	print "<tr><td colspan=20><table width='100%'><tbody>";
	// Rabatgrupper
	print "<tr><td colspan=20 align=\"center\"><hr><b>".findtekst(1006,$sprog_id)."</td></tr><tr><td colspan=20><hr></td></tr>\n";
	print "<tr><td></td><td>Nr.</td><td align=\"center\">".findtekst(914,$sprog_id)."</td><td align=\"center\">Type</td><td align=\"center\">Stk. rabat</td><td align=\"center\">v. antal</td></tr>\n";
	$y=skriv_formtabel('VRG',$x,$y,$art,$id,'&nbsp;',$kodenr,$beskrivelse,$box1,'2',$box2,'20',$box3,'20','-','2','-','4','-','2','-','4','-','2','-','7','-','7','-','0','-','0','-','0','-','0');
	print "</tbody></table></td></tr>";
}
elseif($valg=='formularer'){
	print "<tr><td></td><td colspan=5 align=\"center\"><b>".findtekst(780,$sprog_id)."</td></tr>\n";
	print "<tr><td></td><td colspan=5 align=\"center\"><a href=\"logoupload.php?upload=Yes\">".findtekst(1004,$sprog_id)."</a></td></tr>\n";
	print "<tr><td></td><td></td><td align=\"center\">".findtekst(914,$sprog_id)."</td><td align=\"center\">".findtekst(1005,$sprog_id)."</td><td align=\"center\">PDF-kommando</td><td align=\"center\"></td><td align=\"center\"></td></tr>\n";
	$y=skriv_formtabel('PV',$x,$y,$art,$id,'&nbsp;',$kodenr,$beskrivelse,$box1,'20',$box2,'20','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6','-','6');
}
print "<tr><td><br></td></tr>\n";
print "</tbody></table></td>";
print "<input type = \"hidden\" name=antal value=$y><input type = \"hidden\" name=valg value=$valg>";
print "<tr><td colspan = 3 align = center><input class='button green medium' type=submit accesskey=\"g\" value=\"".findtekst(471,$sprog_id)."\" name=\"submit\"></td></tr>\n";
print "</form>";
print "</div>";

###########################################################################################################################

###########################################################################################################################

###########################################################################################################################
function kontotjek ($konto) { 
	global $regnaar;
	$fejl=NULL;
	$konto = (int)$konto;
	if ($konto) {
		$qtxt="SELECT id FROM kontoplan WHERE kontonr = '$konto' and (kontotype = 'D' or kontotype = 'S') and regnskabsaar='$regnaar'";
#cho "$qtxt<br>";
		$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$konto_id=$r['id'];
#cho "KONTO_ID $konto_id<br>";		
if (!$konto_id=$r['id']){
			print tekstboks('Kontonr: '.$konto.' kan ikke anvendes!'); # 20141212A
			$fejl=1;
#			print "<BODY onLoad=\"javascript:alert('Kontonr: $konto kan ikke anvendes!!')\">";
		} else #cho "ID $r[id]<br>";
#cho $fejl;
	return $fejl;
	}
}
###########################################################################################################################
function sprogtjek ($sprog) { 
	$fejl=NULL;
	if ($sprog) {
		$tmp=strtolower($sprog);
		$query = db_select("SELECT id FROM formularer WHERE lower(sprog) = '$tmp'",__FILE__ . " linje " . __LINE__);
		if (!db_fetch_array($query)) { 
			print tekstboks('Der eksisterer ikke nogen formular med '.$sprog.' som sprog!'); # 20141212A
			$fejl=1;
		}
	return $fejl;
	}
}
###########################################################################################################################
function momsktotjek ($art,$konto) {
	$fejl=NULL;
	if ($konto) {
		if ($art=='DG') {$momsart="art='SM'";}
		if ($art=='KG') {$momsart="(art='KM' or art='YM' or art='EM')";}
		$kode=substr($konto,0,1);
		$kodenr=substr($konto,1,1);
		$qtxt="SELECT id FROM grupper WHERE $momsart and kodenr = '$kodenr' and kode = '$kode'";
		$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		if (!$r['id']) { 
			if ($art=='DG') print tekstboks('Salgsmomsgruppe: '.$konto.' findes ikke!');
			if ($art=='KG') print tekstboks('K&oslash;bsmomskonto: '.$konto.' findes ikke!');
			$fejl=1;
		}
		return $fejl;
	}
}
###########################################################################################################################
function afdelingstjek ($konto) {
	$fejl=NULL;
	$qtxt="SELECT id FROM grupper WHERE art='AFD' and kodenr = '$konto'";
	$r=db_fetch_array(db_select("SELECT id FROM grupper WHERE art='AFD' and kodenr = '$konto'",__FILE__ . " linje " . __LINE__));
	if (!$r['id'])	{
		tekstboks('Afdeling: '.$konto.' findes ikke!');
		$fejl=1;
	}
	return $fejl;
}
###########################################################################################################################
function opdater_varer($kodenr,$art,$box1,$box2,$box3,$box4) {
	if ($art=='VPG' && $kodenr) {
		if ($box1)$box1=usdecimal($box1);
		if ($box2)$box2=usdecimal($box2);
		if ($box3)$box3=usdecimal($box3);
		if ($box4)$box4=usdecimal($box4);
		if ($kodenr != '-') {
		if ($box1) db_modify("update varer set kostpris='$box1' where prisgruppe = '$kodenr'",__FILE__ . " linje " . __LINE__);
		if ($box2) db_modify("update varer set salgspris='$box2' where prisgruppe = '$kodenr'",__FILE__ . " linje " . __LINE__);
		if ($box3) db_modify("update varer set retail_price='$box3' where prisgruppe = '$kodenr'",__FILE__ . " linje " . __LINE__);
		if ($box4) db_modify("update varer set tier_price='$box4' where prisgruppe = '$kodenr'",__FILE__ . " linje " . __LINE__);
		return($box1.";".$box2.";".$box3.";".$box4);
	} 
	} 
	if ($art=='VTG' && $kodenr) {
		if ($box1)$box1=usdecimal($box1);
		if ($box2)$box2=usdecimal($box2);
		if ($box3)$box3=usdate($box3);
		if ($box4)$box4=usdate($box4);
		if ($kodenr != '-') {
		if ($box1) db_modify("update varer set special_price='$box1' where tilbudgruppe = '$kodenr'",__FILE__ . " linje " . __LINE__);
		if ($box2) db_modify("update varer set campaign_cost='$box2' where tilbudgruppe = '$kodenr'",__FILE__ . " linje " . __LINE__);
		if ($box3) db_modify("update varer set special_from_date='$box3' where tilbudgruppe = '$kodenr'",__FILE__ . " linje " . __LINE__);
		if ($box4) db_modify("update varer set special_to_date='$box4' where tilbudgruppe = '$kodenr'",__FILE__ . " linje " . __LINE__);
		}
		return($box1.";".$box2.";".$box3.";".$box4);
	} 
	if ($art=='VRG' && $kodenr) {
		if ($box2)$box2=usdecimal($box2);
		if ($box3)$box3=usdecimal($box3);
		if ($kodenr != '-') {
			if ($box1) {
				db_modify("update varer set m_type='$box1' where rabatgruppe = '$kodenr'",__FILE__ . " linje " . __LINE__);
			}
			if ($box2) {
				db_modify("update varer set m_rabat='$box2' where rabatgruppe = '$kodenr'",__FILE__ . " linje " . __LINE__);
			}
			if ($box3) {
				db_modify("update varer set m_antal='$box3' where rabatgruppe = '$kodenr'",__FILE__ . " linje " . __LINE__);
			}
		}
	}
}
function titletxt($art,$felt) {
	$titletxt=NULL;
	if ($art=='VG') {
		if ($felt=='box1') $titletxt="Skriv kontonummeret for lagertilgang. Dette felt skal kun udfyldes hvis varen er lagerf&oslash;rt og lagerv&aelig;rdien skal reguleres automatisk";
		elseif ($felt=='box2') $titletxt="Skriv kontonummeret for lagerafgang. Dette felt skal kun udfyldes hvis varen er lagerf&oslash;rt og lagerv&aelig;rdien skal reguleres automatisk";
		elseif ($felt=='box3') $titletxt="Skriv kontonummeret for varek&oslash;b. Dette felt SKAL udfyldes";
		elseif ($felt=='box4') $titletxt="Skriv kontonummeret for varesalg. Dette felt SKAL udfyldes";
		elseif ($felt=='box5') $titletxt="Skriv kontonummeret for lagerregulering. Dette felt skal udfyldes hvis varen er lagerf&oslash;rt";
	}
	if ($art=='DG' || $art=='KG') {
		if ($felt=='box3') $titletxt="Valuta styres af samlekontoen";
	}
	return($titletxt);
}

print "
</tbody>
</table>
</td></tr>
</tbody></table>
</div>
";


if ($menu=='T') {
	include_once '../includes/topmenu/footer.php';
} else {
	include_once '../includes/oldDesign/footer.php';
}

?>
