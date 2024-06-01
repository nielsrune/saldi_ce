<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- debitor/betalinger.php --- Patch 4.0.8 --- 2023.06.18 ---
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
// Copyright (c) 2003-2023 saldi.dk aps
// -----------------------------------------------------------------------------------
//
// 2015.11.04 Kopieret fra kreditor og tilrettet til debitorer (phr) 
// 2016.04.07 Indsat db_escape_string. Søg db_escape_string
// 2016.06.27 Indsat valg mellem BEC, Jyske Bank og Nordea.
// 2016.11.30 Tilføjet Danske Bank.
// 2017.05.05 Instætter kontonummer på afregninger fra kassekladde 20170502
// 2017.05.30 Tilføjet SDC - søg sdc (Samme som bec)
// 2020.07.04 PHR Added XML file format
// 2020.09.23 PHR error corrections in XML file format
// 2021.01.22 PHR more corrections in XML file format
// 2021.02.02 PHR more corrections in XML file format
// 2021.03.02 PHR changed $egen_bank to $myBank
// 2021.03.05 PHR removed <Id> section in between <Dbtr></Dbtr>  According to DNB
// 2021.03.22 PHR above line just active for $format == 'norskeBank' as it is requered by other banks??
// 2021.04.06 PHR some cleanup;
// 20211109 MSC - Implementing new design
// 20220201 PHR Moved functions to '../includes/payListFunc.php'
// 20230618 PHR BUG correction: When fetch from 'kontokort' the CURRENT list is now updated, if account exists in list.  
// 20220901 MSC - Implementing new design
// 20231213 MSC - Implementing new design

$dan_liste=$gem=$listenote=$slet_ugyldige=$udskriv=NULL;

@session_start();
$s_id=session_id();
		
$modulnr=12;	
$title="Betalinger til bank";
$css="../css/standard.css";
		
global $menu;
		
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/forfaldsdag.php");
include("../includes/payListFunc.php");

$reopen   = if_isset($_GET['reopen']);
$liste_id = if_isset($_GET['liste_id']);
#$sort    = if_isset($_GET['sort']);
#$rf      = if_isset($_GET['rf']);
#$vis     = if_isset($_GET['vis']);
$find     = if_isset($_POST['find']);
if (isset($_POST['mail']) && $_POST['mail']) {
	print "<meta http-equiv=\"refresh\" content=\"0;URL=mail_modtagere.php?liste_id=$liste_id\">";
	exit;
}

if ($reopen && $liste_id) {
	$qtxt = "update betalingsliste set bogfort='-' where id='$liste_id'";
	echo "$qtxt<br>";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
}
if ($dan_liste) {
	dan_liste($liste_id);
	exit;
}
if (isset($_POST['slet_ugyldige']) || isset($_POST['gem']) || isset($_POST['udskriv'])) {
#cho "Gem $_POST[gem]<br>";
	$id = $erh = array();
	$slet_ugyldige = if_isset($_POST['slet_ugyldige'],NULL);
#	$liste_id      = if_isset($_POST['liste_id']);
	$listenote     = if_isset($_POST['listenote']);
	$udskriv       = if_isset($_POST['udskriv']);
	$format        = if_isset($_POST['format']);
	$id            = if_isset($_POST['id']);
	$erh           = if_isset($_POST['erh']);
	$fra_kto       = if_isset($_POST['fra_kto']);
	$egen_ref      = if_isset($_POST['egen_ref']);
	$til_kto       = if_isset($_POST['til_kto']);
	$kort_ref      = if_isset($_POST['kort_ref']);
	$modt_navn     = if_isset($_POST['modt_navn']);
	$belob         = if_isset($_POST['belob']);
	$valuta        = if_isset($_POST['valuta']);
	$betalingsdato = if_isset($_POST['betalingsdato']);
	$slet          = if_isset($_POST['slet']);
	$ugyldig       = if_isset($_POST['ugyldig']);
	$antal         = if_isset($_POST['antal']);

	(substr($betalingsdato[1],-1) == '*')?$allPayDates=str_replace('*','',$betalingsdato[1]):$allPayDates=NULL;
	for ($x=1;$x<=$antal;$x++) {
		if ($allPayDates) $betalingsdato[$x]=$allPayDates;
		if ($slet_ugyldige && $ugyldig[$x] == $id[$x]) $slet[$x]='on';
		elseif (!isset($slet[$x])) $slet[$x] = NULL;
		if ($slet[$x]=='on') {
			#cho "delete from betalinger where id='$id[$x]'<br>";
			db_modify("delete from betalinger where id='$id[$x]'",__FILE__ . " linje " . __LINE__);
		} else {
			$qtxt = "update betalinger set bet_type='$erh[$x]',fra_kto='$fra_kto[$x]',egen_ref='$egen_ref[$x]',til_kto='$til_kto[$x]',";
			$qtxt.= "kort_ref='$kort_ref[$x]',modt_navn='".db_escape_string($modt_navn[$x])."',belob='$belob[$x]',valuta='$valuta[$x]',";
			$qtxt.= "betalingsdato='$betalingsdato[$x]' where id='$id[$x]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
	db_modify("update betalingsliste set listenote='$listenote' where id='$liste_id'",__FILE__ . " linje " . __LINE__);
	if ($udskriv) {
		$r=db_fetch_array(db_select("select bogfort from betalingsliste where id='$liste_id'",__FILE__ . " linje " . __LINE__)); 
		if ($r['bogfort']!='V	') {
			$qtxt = "update betalingsliste set bogfort='V', bogfort_af='$brugernavn' where id='$liste_id'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
}

$findlink=NULL;
if (!$liste_id) $liste_id=0;
else {
		$findlink='';#"<a href=betalinger.php?liste_id=$liste_id&find=nye>Find nye</a>";
}
$linjebg=$bgcolor;
$erh_title= "ERH355 = Bankoverf. med normal advisering";

if ($menu=='T') {
	include_once '../includes/top_header.php';
	include_once '../includes/top_menu.php';
	print "<div id=\"header\">"; 
	print "<div class=\"headerbtnLft headLink\">&nbsp;&nbsp;&nbsp;</div>";     
	print "<div class=\"headerTxt\">$title</div>";     
	print "<div class=\"headerbtnRght headLink\">&nbsp;&nbsp;&nbsp;</div>";     
	print "</div>";
	print "<div class='content-noside'>";
	print "<div class='dataTablediv'><table cellpadding='1' cellspacing='0' border='0' width='100%' valign = 'top' class='dataTable'><thead>";
} else {
	include_once '../includes/oldDesign/header.php';
print "<table width='100%' height='100%' border='0' cellspacing='0' cellpadding='0'><tbody>";
print "<tr><td height = '25' align='center' valign='top'>";
print "<table width='100%' align='center' border='0' cellspacing='2' cellpadding='0'><tbody>";
print "<form name='paylist' action='betalinger.php?liste_id=$liste_id' method = 'post'>";
print "<td width='10%' $top_bund><font face='Helvetica, Arial, sans-serif' color='#000066'>";
print "<a href=../debitor/betalingsliste.php accesskey=L>Luk</a></td>";
print "<td width='80%' $top_bund><font face='Helvetica, Arial, sans-serif' color='#000066'>Betalinger til bank</td>";
print "<td width='10%' $top_bund><font face='Helvetica, Arial, sans-serif' color='#000066'>";

$r=db_fetch_array(db_select("select bogfort from betalingsliste where id='$liste_id'",__FILE__ . " linje " . __LINE__));
if ($r['bogfort']=='-') {

	print "<select name = 'find' style = 'width:100%;' onchange='this.form.submit()'>";
	print "<option value = ''></option>";
	print "<option value = 'fromList'>Fra liste</option>";
	print "<option value = 'saldo'>Fra kontokort</option>";
	print "</select>";
} # else print "<a href = 'betalinger.php?liste_id=$liste_id&reopen=$liste_id'>Lås op</a>";
print "</td></tr>";
print "</form>";
print "</tbody></table>";
print "</td></tr>";
print "<tr><td valign='top'>";
	print "<table cellpadding='1' cellspacing='0' border='0' width='100%' valign = 'top'><tbody>";
}

if (!$liste_id) {
	$tidspkt=microtime();
	$listedate=date("Y-m-d");
	$qtxt = "insert into betalingsliste(listedate, listenote, oprettet_af, hvem, tidspkt, bogfort) values ";
	$qtxt.= "('$listedate', '$listenote', '$brugernavn', '$brugernavn', '$tidspkt', '-')";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	$r=db_fetch_array(db_select("select MAX(id) as id from betalingsliste where tidspkt='$tidspkt'",__FILE__ . " linje " . __LINE__));
	$liste_id=$r['id'];
	print "<meta http-equiv='refresh' content='0; url=betalinger.php?liste_id=$liste_id'>";
} 

$tomorrow = date('U')+60*60*24;
$paydate  = date('dmY',$tomorrow);
$r=db_fetch_array(db_select("select * from adresser where art = 'S'",__FILE__ . " linje " . __LINE__));
$myBank=$r['bank_konto'];
$myReg=$r['bank_reg'];
$myName=str_replace('&','og',$r['firmanavn']);
$myEan=$r['ean'];
$myCtry=$r['land'];
if (strtolower($myCtry)=='norway') $myCtry = 'NO'; 
($myCtry == 'NO')?$currency = 'NOK':$currency = 'DKK';
if ($find) {
	$bilag_id_list=array();
	$ordre_id_list=array();
	$x=0; $y=0;
#cho "select bank_reg, bank_konto from adresser where art = 'S'<br>";

	while (strlen($myBank)<10) {
		$myBank='0'.$myBank; # kontonumre skal vaere paa 10 cifre
	}
	$myBank = $myReg.$myBank;
#cho "select ordre_id, bilag_id from betalinger<br>";
	if ($find == 'saldo') {
		$qtxt = "select * from adresser where art = 'D' and lukket != 'on'";
		$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
		while ($r = db_fetch_array($q)) {
			$custId=$r['id'];
			$custNo=$r['kontonr'];
			$custName=str_replace('&','og',$r['firmanavn']);
			$custBank=$r['bank_konto'];
			$custReg=$r['bank_reg'];
			while (strlen($custBank)<10) {
				$custBank='0'.$custBank; # kontonumre skal vaere paa 10 cifre
			}
			$custBank = $custReg.$custBank;
			$myRef="Afr: $custNo - $custName";
			$custRef="Afregning: $myName";
			$qtxt="select sum(amount) as amount from openpost where konto_id='$custId'";
			$amount=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))[0];
			if (round($amount,2) < 0) {
				$amount = dkdecimal(abs($amount));
				$qtxt = "select id from betalinger where egen_ref ='".db_escape_string($myRef)."' ";
				$qtxt.= "and liste_id = '$liste_id'";
				if ($id = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))[0]) {
						$qtxt = "update betalinger set belob = '$amount' where id = '$id'";
				} else {
					$qtxt = "insert into betalinger";
					$qtxt.= "(bet_type,fra_kto,egen_ref,til_kto,modt_navn,kort_ref,belob, betalingsdato,valuta,bilag_id,ordre_id,liste_id) ";
					$qtxt.= "values ";
					$qtxt.= "('ERH356','$myBank','".db_escape_string($myRef)."','".db_escape_string($custBank)."',";
					$qtxt.= "'".db_escape_string($custName)."','".db_escape_string($custRef)."','$amount','$paydate',"; 
					$qtxt.="'$currency', '0', '0','$liste_id')";
				}
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
		}
	} elseif ($find) {
	$q=db_select("select ordre_id, bilag_id from betalinger",__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)){
		if ($r['bilag_id']) {
			$x++;
			$bilag_id_list[$x]=$r['bilag_id']*1;
		}
		if ($r['ordre_id']) {
			$y++;
			$ordre_id_list[$y]=$r['ordre_id']*1;
		}
	}
		$qtxt = "select openpost.id as id,openpost.beskrivelse as egen_ref,openpost.amount as amount,openpost.valuta as valuta,";
		$qtxt.= "openpost.faktnr as faktnr,openpost.transdate as transdate,openpost.bilag_id as bilag_id,openpost.forfaldsdate as forfaldsdate,";
		$qtxt.= "openpost.betal_id as betal_id,adresser.erh as erh, openpost.refnr as refnr, openpost.kladde_id as kladde_id,";
		$qtxt.= "openpost.konto_nr as kontonr,adresser.bank_reg as modt_reg, adresser.bank_konto as modt_konto, adresser.firmanavn as modt_navn,";
		$qtxt.= "adresser.bank_fi as modt_fi,adresser.betalingsbet as betalingsbet,adresser.betalingsdage as betalingsdage from openpost, adresser";
		$qtxt.= " where openpost.udlignet != '1' and openpost.amount < 0 and openpost.beskrivelse like 'Afr:%'";
		$qtxt.= " and openpost.konto_id = adresser.id and adresser.art = 'D'";
#cho "qtxt $qtxt<br>";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)){
		$ordre_id=0;
#cho "$ordre_id<br>";		
		$medtag=1;
		$egen_ref=$r['egen_ref'];
		if (substr($egen_ref,0,9)=='Afregning' && $r['kontonr'] && $r['kladde_id']) {
			$tmp="Afr kto:".$r['kontonr']." -";  
			$egen_ref=str_replace('Afregning',$tmp,$egen_ref);
		}
		$kladde_id=$r['kladde_id']*1;
		$bilag_id=$r['bilag_id']*1;
		$refnr=$r['refnr']*1;
		if ($kladde_id && in_array($bilag_id,$bilag_id_list)) $medtag=0;
		if (!$kladde_id && in_array($refnr,$ordre_id_list)) $medtag=0;
		elseif (!$kladde_id) $ordre_id=$refnr;
		if ($medtag) {
			$tmp=$r['modt_konto'];
			if ($tmp) {
				for($x=strlen($r['modt_konto']);$x<10;$x++) $tmp="0".$tmp;
			}
			$modt_konto=$r['modt_reg'].$tmp;
			if ($r['erh']) $erh=$r['erh'];
			elseif ($r['modt_fi']) $erh="ERH351"; # Skal give SDCK020, hvis betalingslister sendes til SDC-bank i stedet for BEC (ERH)
			else $erh="ERH356";
			if ($erh=="ERH351" || $erh=="ERH357" || $erh=="ERH358" || $erh=="SDCK020") {
				$modt_konto = $r['modt_fi']; 
				$kort_ref=$r['betal_id'];
				} elseif ($r['faktnr']) $kort_ref=$myName.":".$r['faktnr'];
				else $kort_ref=$myName;
			if ($r['forfaldsdate']) {
				$forfaldsdag=str_replace("-","",dkdato($r['forfaldsdate']));
			} else $forfaldsdag=str_replace("-","",forfaldsdag($r['transdate'], $r['betalingsbet'], $r['betalingsdage']));
			$belob=dkdecimal($r['amount']*-1);
			$valuta=$r['valuta'];
			if (!$valuta) $valuta='DKK';
			if ($r['betal_id']) {
				if (substr($r['betal_id'],0,1)=="+") {
					$betal_id=substr($r['betal_id'],1);
					list($tmp,$tmp2)=explode("<",$betal_id);
					if($tmp=='04'||$tmp=='15') $erh='ERH352';
					elseif($tmp=='71') $erh='ERH351'; # Skal give SDCK020, hvis betalingslister sendes til SDC-bank i stedet for BEC (ERH)
					elseif($tmp=='73') $erh='ERH357'; # Skal give SDCK073, hvis betalingslister sendes til SDC-bank i stedet for BEC (ERH)
					elseif($tmp=='75') $erh='ERH358'; # Skal give SDCK075, hvis betalingslister sendes til SDC-bank i stedet for BEC (ERH)
					$tmp2=(str_replace("+",";",$tmp2));#split fungerer ikke med "+" som skilletegn?
					list($kort_ref,$modt_konto)=explode(";",$tmp2);
					$kort_ref=trim($kort_ref);
				} else $kort_ref=$r['betal_id'];	
			}
			$qtxt="insert into betalinger";
			$qtxt.="(bet_type,fra_kto, egen_ref, til_kto, modt_navn, kort_ref, belob, betalingsdato, valuta, bilag_id, ordre_id, liste_id) ";
			$qtxt.="values ";
				$qtxt.="('$erh','$myBank','$egen_ref','$modt_konto','".db_escape_string($r['modt_navn'])."','$kort_ref','$belob','$forfaldsdag',"; $qtxt.="'$valuta', '$bilag_id', '$ordre_id','$liste_id')";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
}
}
$kort_ref=array();
if ($udskriv) {
	$q=db_select("select * from betalinger where liste_id=$liste_id",__FILE__ . " linje " . __LINE__);
	$x=0;
	$fejl_i_liste=NULL;
	while ($r=db_fetch_array($q)) {
		$x++;
		list($kort_ref,$k1[$x],$k2[$x],$k3[$x],$k4[$x],$k5[$x],$k6[$x],$k7[$x],$k8[$x],$k9[$x])=betalingskontrol($erh[$x],$r['fra_kto'],$r['egen_ref'],$r['til_kto'],$r['kort_ref'],$r['modt_navn'],$r['belob'],$r['valuta'],$r['betalingsdato']);
echo "<!-- UDDATA for ".$x.": k1:".$k1[$x]." k2:". $k2[$x]." k3:".$k3[$x]." k4:".$k4[$x]." k5:".$k5[$x]." k6:".$k6[$x]." K7:".$k7[$x]." K8:".$k8[$x]." K9:".$k9[$x]. "-->\n";
		if($k1[$x]||$k2[$x]||$k3[$x]||$k4[$x]||$k5[$x]||$k6[$x]||$k7[$x]||$k8[$x]||$k9[$x]) {
			$fejl_i_liste.=fejl_i_betalingslinje($x, $k1[$x], $k2[$x], $k3[$x], $k4[$x], $k5[$x], $k6[$x], $k7[$x], $k8[$x], $k9[$x]); # 20140717
		}
	}
}
if ($udskriv) {
	if ($format=='bec') udskriv_bec($db_id,$bruger_id,$liste_id);
	elseif ($format=='danskebank') udskriv_danskebank($db_id,$bruger_id,$liste_id);
	elseif ($format=='bankdata') udskriv_bankdata($db_id,$bruger_id,$liste_id);
	elseif ($format=='nordea') udskriv_nordea($db_id,$bruger_id,$liste_id);
	elseif ($format=='sdc') udskriv_sdc($db_id,$bruger_id,$liste_id);
	elseif ($format=='xml' || $format=='norskeBank') udskriv_xml($db_id,$bruger_id,$liste_id);
} else { 
	print "<form name=\"betalinger\" action=\"betalinger.php?liste_id=$liste_id\" method=\"post\">";
	$r=db_fetch_array(db_select("select listenote, bogfort from betalingsliste where id='$liste_id'",__FILE__ . " linje " . __LINE__));
	$listenote=$r['listenote'];
	$bogfort=$r['bogfort'];
	print "<tr>";
	if ($bogfort!='-'){
		print "<td colspan=11 align='center'><b> $listenote</b></td></tr>";
		if ($menu=='T') {
		print "<tr><td colspan=11 class='border-hr-bottom'></td></tr>\n";
	} else {
		print "<tr><td colspan=11><hr></td></tr>\n";
	}
	}	else {
		print "<td><b> <span title= 'Her kan skrives en bem&aelig;rkning til kladden'>Bem&aelig;rkning:</b></td>";
		print "<td colspan=10><input type=\"text\" style='width:100%;' name=listenote value=\"$listenote\"></td></tr>";
		if ($menu=='T') {
			$r=db_fetch_array(db_select("select bogfort from betalingsliste where id='$liste_id'",__FILE__ . " linje " . __LINE__));
			if ($r['bogfort']=='-') {
				print "<center><select name = 'find' style = 'width:50%;' onchange='this.form.submit()'>";
				print "<option value = ''></option>";
				print "<option value = 'fromList'>Fra liste</option>";
				print "<option value = 'saldo'>Fra kontokort</option>";
				print "</select></center>";
			}
		} else {
			print "";
		}
	}
	#print"<tr><td colspan=11><hr></td></tr>";
	$paytitle = "Sæt en * i enden af datoen i øverste datofelt, for at sætte alle datoer til denne dato. F.eks: 010402021*";
	print "<tr>
		<th><span title=\"$erh_title\"><b>Betalingstype</b></span></td>
		<th><b>Fra konto</b></td>
		<th><b>Egen ref.</b></td>
		<th><b>Modtager konto</b></td>
		<th><b>Modtager ref.</b></td>
		<th><b>Modtager</b></td>
		<th align='right' class='text-right'><b>Bel&oslash;b</b></td>
		<th align='center' class='text-center'><b>Valuta</b></td>
		<th align='right' class='text-right' title='$paytitle'><b>Betalingsdato</b></td>
		<th align='center' class='text-center'><span title='Se i nyt vindue'><b>Se</b></span></td>";
		if ($bogfort!='V') print "<th align='center'><span title='Slet linjen fra listen'><b>Slet</b></span></th>";
		print "</tr>";
		if ($menu=='T') {
			print "</thead><tbody>";
		} else {
			print "";
		}
		
#print"<tr><td colspan=11><hr></td></tr>";
	$x=0;
# echo "select betalinger.bet_type as bet_type,betalinger.fra_kto as fra_kto, betalinger.egen_ref as egen_ref, betalinger.til_kto as til_kto, betalinger.modt_navn as modt_navn, betalinger.kort_ref as kort_ref, betalinger.belob as belob, betalinger.betalingsdato as betalingsdato, kassekladde.bilag as bilag, ordrer.modtagelse as modtagelse from betalinger, ordrer, kassekladde where betalinger.liste_id=$liste_id and ordrer.id=betalinger.ordre_id and kassekladde.id=betalinger.bilag_id order by betalinger.betalingsdato<br>";
	$erh=array();
	
$fejl=0;	
#$kn_kontrol=0;
$q=db_select("select * from betalinger where liste_id=$liste_id order by modt_navn,betalingsdato",__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$x++;
		$k1_bg[$x] = $k2_bg[$x] = $k3_bg[$x] = $k4_bg[$x] = $k5_bg[$x] = $k6_bg[$x] = $k7_bg[$x] = $k8_bg[$x] = '';
		$erh[$x]=$r['bet_type'];
		$fra_kto[$x]=$r['fra_kto'];
		$egen_ref=$r['egen_ref'];
		$til_kto[$x]=$r['til_kto'];
		$kort_ref[$x]=$r['kort_ref'];
		$belob[$x]=$r['belob'];
		$valuta[$x]=$r['valuta'];
		$betalingsdato[$x]=$r['betalingsdato'];
		if ($r['ordre_id']) {
#			$kn_kontrol=1;
			$r2=db_fetch_array(db_select("select modtagelse from ordrer where id = '$r[ordre_id]'",__FILE__ . " linje " . __LINE__));
		} elseif ($r['bilag_id']) {
			$r2=db_fetch_array(db_select("select kladde_id, bilag from kassekladde where id = '$r[bilag_id]'",__FILE__ . " linje " . __LINE__));
		}
		if ($bogfort && $bogfort!='V') {
			list($kort_ref[$x],$k1[$x],$k2[$x],$k3[$x],$k4[$x],$k5[$x],$k6[$x],$k7[$x],$k8[$x])=betalingskontrol($erh[$x],$r['fra_kto'],$r['egen_ref'],$r['til_kto'],$r['kort_ref'],$r['modt_navn'],$r['belob'],$r['valuta'],$r['betalingsdato']);
			if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
			else {$linjebg=$bgcolor5; $color='#000000';}
			print "<tr bgcolor=\"$linjebg\">";
			if($k1[$x]||$k2[$x]||$k3[$x]||$k4[$x]||$k5[$x]||$k6[$x]||$k7[$x]||$k8[$x]) {
				$fejl=1;
				print "<input type=\"hidden\" name=\"ugyldig[$x]\" value=\"$r[id]\">";
				if ($k1[$x]) $k1_bg[$x]="bgcolor=\"#FF0000\"";
				else $k1_bg[$x]="bgcolor=\"$linjebg\"";
				if ($k2[$x]) $k2_bg[$x]="bgcolor=\"#FF0000\"";
				else $k2_bg[$x]="bgcolor=\"$linjebg\"";
				if ($k3[$x]) $k3_bg[$x]="bgcolor=\"#FF0000\"";
				else $k3_bg[$x]="bgcolor=\"$linjebg\"";
				if ($k4[$x]) $k4_bg[$x]="bgcolor=\"#FF0000\"";
				else $k4_bg[$x]="bgcolor=\"$linjebg\"";
				if ($k5[$x]) $k5_bg[$x]="bgcolor=\"#FF0000\"";
				else $k5_bg[$x]="bgcolor=\"$linjebg\"";
				if ($k6[$x]) $k6_bg[$x]="bgcolor=\"#FF0000\"";
				else $k6_bg[$x]="bgcolor=\"$linjebg\"";
				if ($k7[$x]) $k7_bg[$x]="bgcolor=\"#FF0000\"";
				else $k7_bg[$x]="bgcolor=\"$linjebg\"";
				if ($k8[$x]) $k8_bg[$x]="bgcolor=\"#FF0000\"";
				else $k8_bg[$x]="bgcolor=\"$linjebg\"";
			} else print "<input type=\"hidden\" name=\"ugyldig[$x]\" value=\"\">";
				print "<td><span title=\"$erh_title\"><SELECT NAME=erh[$x]>\n";
				if ($erh[$x]) print "<option>$erh[$x]</option>\n";
				if ($erh[$x]!='ERH356') print "<option>ERH356</option>\n";
				print "</SELECT></span></td>\n";
				print "<td $k1_bg[$x]><span title=\"$k1[$x]\">";
				print "<input type=\"text\" style=\"text-align:right\" name=\"fra_kto[$x]\" size=14 value=\"$r[fra_kto]\"></span></td>";
				print "<td $2_bg[$x]><span title=\"$k2[$x]\">";
				print "<input type=\"text\" name=\"egen_ref[$x]\" size=20 value=\"$r[egen_ref]\"></span></td>";
				print "<td $k3_bg[$x]><span title=\"$k3[$x]\">";
				print "<input type=\"text\" style=\"text-align:right\" name=\"til_kto[$x]\" size=12 value=\"$r[til_kto]\"></span></td>";
				print "<td $k4_bg[$x]><span title=\"$k4[$x]\">";
				print "<input type=\"text\" style=\"text-align:left\" name=\"kort_ref[$x]\" size=10 value=\"$kort_ref[$x]\"></span></td>";
				print "<td $k5_bg[$x]><span title=\"$k5[$x]\">";
				print "<input type=\"text\" name=\"modt_navn[$x]\" size=15 value=\"$r[modt_navn]\"></span></td>";
				print "<td $k6_bg[$x]><span title=\"$k6[$x]\">";
				print "<input type=\"text\" style=\"text-align:right\" name=\"belob[$x]\" size=10 value=\"$r[belob]\"></span></td>";
				print "<td $k7_bg[$x]><span title=\"$k7[$x]\">";
				print "<input type=\"text\" style=\"text-align:right\" name=\"valuta[$x]\" size=3 value=\"$r[valuta]\"></span></td>";
				print "<td $k8_bg[$x]><span title=\"$k8[$x]\">";
				print "<input type=\"text\" style=\"text-align:right\" name=\"betalingsdato[$x]\" size=10 value=\"$r[betalingsdato]\">";
				print "</span></td>";
				if ($r['ordre_id']) {
					print "<td align=right>";
					print "<a href='../debitor/ordre.php?id=$r[ordre_id]' target='_blank'>";
					print "<span title=\"Se modtagelse i nyt vindue\">M:$r2[modtagelse]</span></a></td>";
				} elseif ($r['bilag_id']) {
					print "<td align=right>";
					print "<a href='../finans/kassekladde.php?kladde_id=$r2[kladde_id]' target='_blank'>";
					print "<span title=\"Se bilag i nyt vindue\">B:$r2[bilag]</span></a></td>";
				} else {
					print "<td></td>";
				}
				print	"<td><span title=\"Slet linje fra liste\"><label class='checkContainerOrdreliste'><input type=\"checkbox\" name=\"slet[$x]\"><span class='checkmarkOrdreliste'></span></span></label></td>";
				print "</tr>";
				print "<input type=\"hidden\" name=\"id[$x]\" value=\"$r[id]\">";
				print "<input type=\"hidden\" name=\"antal\" value=\"$x\">";
		} else {
			if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
			else {$linjebg=$bgcolor5; $color='#000000';}
			print "<tr bgcolor=\"$linjebg\">";
			print "<td>$erh[$x]</td><td>$r[fra_kto]</td><td>$r[egen_ref]</td><td>$r[til_kto]</td><td>$kort_ref[$x]</td>";
			print "<td>$r[modt_navn]</td><td align=right>$r[belob]</td><td align='center'>$r[valuta]</td><td align=right>$r[betalingsdato]</td>";
			if ($r['ordre_id'])	{
				print "<td align=right class='text-right'>";
				print "<a href='../debitor/ordre.php?id=$r[ordre_id]' target='_blank'>";
				print "<span title=\"Se modtagelse i nyt vindue\">M:$r2[modtagelse]</span></a></td></tr>";
			} elseif ($r['bilag_id']) {
				print "<td align=right class='text-right'>";
				print "<a href='../finans/kassekladde.php?kladde_id=$r2[kladde_id]' target='_blank'>";
				print "<span title=\"Se bilag i nyt vindue\">B:$r2[bilag]</span></a></td></tr>";		
			}
		}
	}

	if ($menu=='T') {
		print "</tbody><tfoot>";
	} else {
		print "";
	}

	$modtagerantal=$x;
	#($kn_kontrol)?$modtagerantal=0:$modtagerantal=$x;
	if (!$modtagerantal) {
		print "<tr><td colspan=11 align='center'>";
		print "<br><br>Vælg i feltet foroven til højre hvorfra du vil trække betalingsinformationer.<br>";
		print "<br>Vil du trække betalinger dannet i en kassekladde vælges 'fra liste'<br>";
		print "<br>Vil du i stedet trække alle registrede skyldige beløb til kunder, vælges 'fra kontokort'<br><br><br>";
		print "</td></tr>";
	} else {
	print "<tr><td colspan=11 align='center'>";
	if ($bogfort!='V') {
		print "<input type=submit accesskey=\"g\" value=\"Gem\" name=\"gem\"> &nbsp;•&nbsp;";
		if(!$fejl && $modtagerantal) { 
			print "&nbsp;<select name=\"format\">";
			print "<option value=\"bec\">BEC</option>";
			print "<option value=\"danskebank\">Danske Bank</option>";
			print "<option value=\"bankdata\">Jyske Bank</option>";
			print "<option value=\"nordea\">Nordea</option>";
			print "<option value=\"sdc\">SDC</option>";
			print "<option value=\"xml\">XML</option>";
			print "<option value=\"norskeBank\">Norske Bank XML</option>";
			print "</select>";
			print "&nbsp;•&nbsp;<input type=submit accesskey=\"u\" value=\"Udskriv og luk\" name=\"udskriv\">";
			} else {
			print "&nbsp;•&nbsp;<span title='Klik her for at fjerne alle ugyldige linjer'><input type=submit accesskey=\"u\" value=\"Slet r&oslash;de\" name=\"slet_ugyldige\"></span>";
			}
		} else {
		print "<select name=\"format\" style='width:200px;'>";
		print "<option value=\"bec\">BEC</option>";
		print "<option value=\"danskebank\">Danske Bank</option>";
		print "<option value=\"bankdata\">Jyske Bank</option>";
		print "<option value=\"nordea\">Nordea</option>";
			print "<option value=\"sdc\">SDC</option>";
		print "<option value=\"xml\">XML</option>";
		print "<option value=\"norskeBank\">Norske Bank XML</option>";
		print "</select>&nbsp;•&nbsp;";
		print "<input type=submit accesskey=\"u\" value=\"Udskriv\" name=\"udskriv\">&nbsp;•&nbsp;";
		}
	if ($modtagerantal) print "&nbsp;•&nbsp;<input type=submit accesskey=\"m\" value=\"Mail til modtagere\" name=\"mail\">";
}
}
	
print "</td></tr>";

if ($menu=='T') {
	print "</tfoot></table></div>";
	include_once '../includes/topmenu/footer.php';
} else {
	print "</tbody></table>";
	include_once '../includes/oldDesign/footer.php';
}
print "</form>";
?>
