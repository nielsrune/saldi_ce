<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- debitor/betalinger.php --- Patch 4.0.1 --- 2021.04.06 ---
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
// Copyright (c) 2003-2021 saldi.dk aps
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

$dan_liste=$gem=$listenote=$slet_ugyldige=$udskriv=NULL;

@session_start();
$s_id=session_id();
		
$modulnr=12;	
$title="betalinger";
$css="../css/standard.css";
		
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/forfaldsdag.php");

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
	$slet_ugyldige = $_POST['slet_ugyldige'];
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
		elseif (!isset($slet)) $slet[$x] = NULL;
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
		if ($r['bogfort']=='-') {
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
print "<table cellpadding='1' cellspacing='0' border='0' width='100%' valign = 'top'>";
if (!$liste_id) {
	$tidspkt=microtime();
	$listedate=date("Y-m-d");
	$qtxt = "insert into betalingsliste(listedate, listenote, oprettet_af, hvem, tidspkt, bogfort) values ";
	$qtxt.= "('$listedate', '$listenote', '$brugernavn', '$brugernavn', '$tidspkt', '-')";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	$r=db_fetch_array(db_select("select MAX(id) as id from betalingsliste where tidspkt='$tidspkt'",__FILE__ . " linje " . __LINE__));
	$liste_id=$r['id'];
} 

$tomorrow = date('U')+60*60*24;
$paydate  = date('dmY',$tomorrow);
$r=db_fetch_array(db_select("select * from adresser where art = 'S'",__FILE__ . " linje " . __LINE__));
$myBank=$r['bank_konto'];
$myReg=$r['bank_reg'];
$myName=$r['firmanavn'];
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
	$qtxt = "select var_value from settings where var_name = 'customerCommissionAccountUsed' and var_grp = 'items'";
	if ($find == 'saldo' && $r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$x=0;
		$custAccount=array();
		$qtxt = "select id, egen_ref from betalinger where liste_id = '$liste_id'";
		$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
		while ($r = db_fetch_array($q)) {
			list($a,$b) = explode('-',$r['egen_ref'],2);
			$custAccount[$x] = trim(str_replace('Afr:','',$a));
			$x++;
		}
		$x=0;
		$qtxt = "select * from adresser where art = 'D'";
#cho "$qtxt<br>";
		$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
		while ($r = db_fetch_array($q)) {
			$custId=$r['id'];
			$custNo=$r['kontonr'];
			$custName=$r['firmanavn'];
			$custBank=$r['bank_konto'];
			$custReg=$r['bank_reg'];
			while (strlen($custBank)<10) {
				$custBank='0'.$custBank; # kontonumre skal vaere paa 10 cifre
			}
			$custBank = $custReg.$custBank;
			$myRef="Afr: $custNo - $custName";
			$custRef="Afregning: $myName";
			$qtxt="select sum(amount) as amount from openpost where udlignet = '0' and konto_id='$custId'";
#cho "$qtxt<br>";
			$amount=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))[0];
			if ($amount < 0) {
				$amount = dkdecimal(abs($amount));
				if (!in_array($custNo,$custAccount)) {
					$qtxt = "insert into betalinger";
					$qtxt.= "(bet_type,fra_kto,egen_ref,til_kto,modt_navn,kort_ref,belob, betalingsdato,valuta,bilag_id,ordre_id,liste_id) ";
					$qtxt.= "values ";
					$qtxt.= "('ERH356','$myBank','".db_escape_string($myRef)."','".db_escape_string($custBank)."',";
					$qtxt.= "'".db_escape_string($custName)."','".db_escape_string($custRef)."','$amount','$paydate',"; 
					$qtxt.="'$currency', '0', '0','$liste_id')";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
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
	elseif ($format=='jyskebank') udskriv_jyskebank($db_id,$bruger_id,$liste_id);
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
		print "<td colspan=11><hr></td></tr>";
	}	else {
		print "<td><b> <span title= 'Her kan skrives en bem&aelig;rkning til kladden'>Bem&aelig;rkning:</b></td>";
		print "<td colspan=10><input type=\"text\" size=95 name=listenote value=\"$listenote\"></td></tr>";
	}
	#print"<tr><td colspan=11><hr></td></tr>";
	$paytitle = "Sæt en * i enden af datoen i øverste datofelt, for at sætte alle datoer til denne dato. F.eks: 010402021*";

	print "<tr>
		<td><span title=\"$erh_title\"><b>Betalingstype</b></span></td>
		<td><b>Fra konto</b></td>
		<td><b>Egen ref.</b></td>
		<td><b>Modtager konto</b></td>
		<td><b>Modtager ref.</b></td>
		<td><b>Modtager</b></td>
		<td align='center'><b>Bel&oslash;b</b></td>
		<td align='center'><b>Valuta</b></td>
		<td align='center' title='$paytitle'><b>Betalingsdato</b></td>
		<td align='center'><span title='Se i nyt vindue'><b>Se</b></span></td>";
		if ($bogfort!='V') print "<td align='center'><span title='Slet linjen fra listen'><b>Slet</b></span></td>";
		print "</tr>";
#print"<tr><td colspan=11><hr></td></tr>";
	$x=0;
# echo "select betalinger.bet_type as bet_type,betalinger.fra_kto as fra_kto, betalinger.egen_ref as egen_ref, betalinger.til_kto as til_kto, betalinger.modt_navn as modt_navn, betalinger.kort_ref as kort_ref, betalinger.belob as belob, betalinger.betalingsdato as betalingsdato, kassekladde.bilag as bilag, ordrer.modtagelse as modtagelse from betalinger, ordrer, kassekladde where betalinger.liste_id=$liste_id and ordrer.id=betalinger.ordre_id and kassekladde.id=betalinger.bilag_id order by betalinger.betalingsdato<br>";
	$erh=array();
	
$fejl=0;	
#$kn_kontrol=0;
$q=db_select("select * from betalinger where liste_id=$liste_id order by modt_navn,betalingsdato",__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$x++;
		$erh[$x]=$r['bet_type'];
		$fra_kto[$x]=$r['fra_kto'];
		$egen_ref[$x]=$r['egen_ref'];
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
				print "<input type=\"text\" style=\"text-align:right\" name=\"fra_kto[$x]\" size=\"15px\" value=\"$r[fra_kto]\"></span></td>";
				print "<td $2_bg[$x]><span title=\"$k2[$x]\">";
				print "<input type=\"text\" name=\"egen_ref[$x]\" size=\"30px\" value=\"$r[egen_ref]\"></span></td>";
				print "<td $k3_bg[$x]><span title=\"$k3[$x]\">";
				print "<input type=\"text\" style=\"text-align:right\" name=\"til_kto[$x]\" size=\"15px\" value=\"$r[til_kto]\"></span></td>";
				print "<td $k4_bg[$x]><span title=\"$k4[$x]\">";
				print "<input type=\"text\" style=\"text-align:left\" name=\"kort_ref[$x]\" size=\"30px\" value=\"$kort_ref[$x]\"></span></td>";
				print "<td $k5_bg[$x]><span title=\"$k5[$x]\">";
				print "<input type=\"text\" name=\"modt_navn[$x]\" size=\"30px\" value=\"$r[modt_navn]\"></span></td>";
				print "<td $k6_bg[$x]><span title=\"$k6[$x]\">";
				print "<input type=\"text\" style=\"text-align:right\" name=\"belob[$x]\" size=\"15px\" value=\"$r[belob]\"></span></td>";
				print "<td $k7_bg[$x]><span title=\"$k7[$x]\">";
				print "<input type=\"text\" style=\"text-align:right\" name=\"valuta[$x]\" size=\"5px\" value=\"$r[valuta]\"></span></td>";
				print "<td $k8_bg[$x]><span title=\"$k8[$x]\">";
				print "<input type=\"text\" style=\"text-align:right\" name=\"betalingsdato[$x]\" size=\"10px\" value=\"$r[betalingsdato]\">";
				print "</span></td>";
				if ($r['ordre_id']) {
					print "<td align=right onMouseOver=\"this.style.cursor = 'pointer'\"; ";
					print "onClick=\"javascript:k_ordre=window.open";
					print "('../debitor/ordre.php?id=$r[ordre_id]','k_ordre','width=800,height=400,scrollbars=1,resizable=1')\">";
					print "<span title=\"Se modtagelse i nyt vindue\"><u>M:$r2[modtagelse]</u></span></td>";
				} elseif ($r['bilag_id']) {
					print "<td align=right onMouseOver=\"this.style.cursor = 'pointer'\"; ";
					print "onClick=\"javascript:kaskl=window.open";
					print "('../finans/kassekladde.php?kladde_id=$r2[kladde_id]','kaskl','width=800,height=400,scrollbars=1,resizable=1')\">";
					print "<span title=\"Se bilag i nyt vindue\"><u>B:$r2[bilag]</u></span></td>";
				}
				print	"<td><span title=\"Slet linje fra liste\"><input type=\"checkbox\" name=\"slet[$x]\"></span></td>";
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
				print "<td align=right onMouseOver=\"this.style.cursor = 'pointer'\"; ";
				print "onClick=\"javascript:k_ordre=window.open";
				print "('../debitor/ordre.php?id=$r[ordre_id]','k_ordre','width=800,height=400,scrollbars=1,resizable=1')\">";
				print "<span title=\"Se modtagelse i nyt vindue\"><u>M:$r2[modtagelse]</u></span></td></tr>";
			} elseif ($r['bilag_id']) {
				print "<td align=right onMouseOver=\"this.style.cursor = 'pointer'\"; "; 
				print "onClick=\"javascript:kaskl=window.open";
				print "('../finans/}kassekladde.php?kladde_id=$r2[kladde_id]','kaskl','width=800,height=400,scrollbars=1,resizable=1')\">";
				print "<span title=\"Se bilag i nyt vindue\"><u>B:$r2[bilag]</u></span></td></tr>";		
			}
		}
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
		print "<input type=submit accesskey=\"g\" value=\"Gem\" name=\"gem\">";
		if(!$fejl && $modtagerantal) { 
			print "&nbsp;<select name=\"format\">";
			print "<option value=\"bec\">BEC</option>";
			print "<option value=\"danskebank\">Danske Bank</option>";
			print "<option value=\"jyskebank\">Jyske Bank</option>";
			print "<option value=\"nordea\">Nordea</option>";
			print "<option value=\"sdc\">SDC</option>";
			print "<option value=\"xml\">XML</option>";
			print "<option value=\"norskeBank\">Norske Bank XML</option>";
			print "</select>";
				print "&nbsp;<input type=submit accesskey=\"u\" value=\"Udskriv og luk\" name=\"udskriv\">";
			} else {
			print "<span title='Klik her for at fjerne alle ugyldige linjer'>&nbsp;<input type=submit accesskey=\"u\" value=\"Slet r&oslash;de\" name=\"slet_ugyldige\"></span>";
			}
		} else {
		print "<select name=\"format\">";
		print "<option value=\"bec\">BEC</option>";
		print "<option value=\"danskebank\">Danske Bank</option>";
		print "<option value=\"jyskebank\">Jyske Bank</option>";
		print "<option value=\"nordea\">Nordea</option>";
			print "<option value=\"sdc\">SDC</option>";
		print "<option value=\"xml\">XML</option>";
		print "<option value=\"norskeBank\">Norske Bank XML</option>";
		print "</select>";
		print "<input type=submit accesskey=\"u\" value=\"Udskriv\" name=\"udskriv\">";
		}
	if ($modtagerantal) print "&nbsp;<input type=submit accesskey=\"m\" value=\"Mail til modtagere\" name=\"mail\">";
}
}
	
print "</td></tr>";
print "</form>";
##############################################################################################################
function betalingskontrol($erh,$fra_kto,$egen_ref,$til_kto,$kort_ref,$modt_navn,$belob,$valuta,$betalingsdato) {
	global $myCtry;
	
		$x=0;
		$k1[$x]=$k2[$x]=$k3[$x]=$k4[$x]=$k5[$x]=$k6[$x]=$k7[$x]=$k8[$x]=$k9[$x]=NULL;

	
	if (!$fra_kto || !is_numeric($fra_kto)) {
		if ($myCtry=='NO' && strlen($fra_kto)!=11) $k1[$x] = "Egen konto ikke gyldig";
		elseif (strlen($fra_kto)!=11) $k1[$x] = "Egen konto ikke gyldig"; 
	}
	if ($erh=='ERH351'||$erh=='ERH352'||$erh=='ERH358'||$erh=='SDCK020') {
		if (!$til_kto || !is_numeric($til_kto)||strlen($til_kto)!=8) $k3[$x] = "Modtager konto ikke gyldig - skal være på 8 cifre";
		if (!$kort_ref || !is_numeric($kort_ref)) {
			$k4[$x] = "Ugyldig betalingsidentifikation (modt. ref - må kun bestå af cifre)";
		} else {
			if ($erh=='ERH351'||$erh='SDCK020') {
				$len=15; #strlen af ERH351 og SDCK020 skal vaere 15
			} else { 
				$len=16;
			}
			for($x=strlen($kort_ref);$x<$len;$x++) $kort_ref='0'.$kort_ref;
			for ($x=$len-1;$x>=0;$x--) { #Beregning af kontrolciffer.
				$y=substr($kort_ref,$x,1)*2;
				$x--;
				$y=substr($kort_ref,$x,1)*1;
			}
			while ($y>9) { #Reduktion af kontrolciffer
				$y=substr($y,0,1)+$y=substr($y,1,1);	
			}
			if (substr($kort_ref,-1) != $y) $kommentar = "Ugyldig betalingsidentifikation (modt. ref - kontrolciffer passer ikke)";
		}
	} elseif ($erh=='ERH355'||$erh=='ERH356'||$erh='SDC3') {
		if ($myCtry=='NO' && strlen($til_kto)!=11) {
			$k3[$x] = "Modtager konto ikke gyldig - skal være på 11 cifre";
		}	elseif (!$til_kto || !is_numeric($til_kto) || ($myCtry!='NO' && strlen($til_kto)!=14)) {
			$k3[$x] = "Modtager konto ikke gyldig - skal være på 14 cifre (regnr. på 4 og kontonr på 10)";
		}
		if(!$kort_ref) $k4[$x] = "Modt ref skal udfyldes";
	}
	if (usdecimal($belob,2)<0.01) $k4[$x]="Bel&oslash;b skal være st&oslash;rre end 0";
	if ($valuta!='DKK') $k5[$x]="Ugyldig valuta, kun DKK kan anvendes";	
	if (strlen($betalingsdato)!=8) $k6[$x]="ugyldig dato - skal v&aelig;re i formatet ddmmyyyy";
	$dag=substr($betalingsdato,0,2);
	$md=substr($betalingsdato,2,2);
	$aar=substr($betalingsdato,4);
	$bd=$aar.$md.$dag;
	$dd=date("Ymd");
	if ($dd>$bd) $k8[$x]="Betalingsdato er overskredet";
	if (!checkdate($md, $dag, $aar)) $k8[$x]="ugyldig dato - skal v&aelig;re i formatet ddmmyyyy";
	#	echo "$kort_ref,$kommentar -- ";

	return(array($kort_ref,$k1[$x],$k2[$x],$k3[$x],$k4[$x],$k5[$x],$k6[$x],$k7[$x],$k8[$x],$k9[$x]));
}

function fejl_i_betalingslinje($linjenr_fejl, $erh_fejl,$fra_kto_fejl,$egen_ref_fejl,$til_kto_fejl,$kort_ref_fejl,$modt_navn_fejl,$belob_fejl,$valuta_fejl,$betalingsdato_fejl)
{
	$retur="Fejl i linje ".$linjenr_fejl.":";
	if($erh_fejl) $retur.=" ".$erh_fejl.".";
	if($fra_kto_fejl) $retur.=" ".$fra_kto_fejl.".";
	if($egen_ref_fejl) $retur.=" ".$egen_ref_fejl.".";
	if($til_kto_fejl) $retur.=" ".$til_kto_fejl.".";
	if($kort_ref_fejl) $retur.=" ".$kort_ref_fejl.".";
	if($modt_navn_fejl) $retur.=" ".$modt_navn_fejl.".";
	if($belob_fejl) $retur.=" ".$belob_fejl.".";
	if($valuta_fejl) $retur.=" ".$valuta_fejl.".";
	if($betalingsdato_fejl) $retur.=" ".$betalingsdato_fejl.".";
	$retur.="<br />\n";
	
	return $retur;
}
function udskriv_nordea($db_id,$bruger_id,$liste_id) {
	$dd=date("Ymd");
	$filnavn="../temp/$db_id"."$bruger_id".".UTF";
	$fp=fopen("$filnavn","w");
	$qtxt="select firmanavn from adresser where art='S'";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$myName=$r['firmanavn'];
	
	$qtxt="select * from betalinger where liste_id=$liste_id order by betalingsdato";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		#$kort_ref = $r['kort_ref'];
#		if (substr($r['bet_type'], 0, 3)=="ERH") {
			$x++;
#			if ($betalingsdato<$dd)$betalingsdato=$dd;
#cho __LINE__."<br>";
			$linje='"0","45","","","","","","","","","","'; #felt 1-11
			$linje.=$r['modt_navn']; #felt 12 Modtager
			$linje.='","","","","","'; # felt 13-16 
			$linje.=substr($r['til_kto'],0,4); # felt 17 Til-regnr
			$tmp=substr($r['til_kto'],4,10); # 10 Til-konto-nr
			while (strlen($tmp)<10)$tmp="0".$tmp;
			$linje.=$tmp; # felt 17 Til-konto-nr
			$linje.='","","","","","","';  # felt 18-22
			$linje.=$r['kort_ref']; # felt 23 meddelelse til kunde  
			$linje.='","';
			$linje.=$r['kort_ref']; # felt 24   
			$linje.='","0","';# felt 25
			$linje.=$r['kort_ref']; # felt 26   
			$linje.='","","","","","DKK","","'; # felt 27-32
			$linje.=usdecimal($r['belob'],2); # felt 33;
			$linje.='","'; #Felt 33
			$linje.=str_replace("-","",usdate($r['betalingsdato'])); #Felt 34
			$linje.='","","","'; #Felt 35-36
			$linje.=substr($r['fra_kto'],0,4); # Felt 37 Til-regnr
			$tmp=substr($r['fra_kto'],4,10); # Til-konto-nr
			while (strlen($tmp)<10)$tmp="0".$tmp;
			$linje.=$tmp; # Felt 37 Til-konto-nr
			$linje.='","';
			$linje.=substr($r['egen_ref'],0,20); #Felt 38 (max 20 tegn)
			$linje.='","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","';
#			$linje.='","","","","","","","","","","","","","","","'; #Felt 39-52
#			$linje.=$r['kort_ref']; # felt 53 Kort advis
#			$linje.='","","","","","","","","","","","","","","","","","","","","","","","","","","","0","'; #Felt 54-91
#			$linje.=$r['kort_ref']; # felt 92 Modtagers identifikation af afsender  
			$linje.='"';
			$linje.="\r\n";
		fwrite($fp,$linje);
	}
	fclose($fp);
	print "<tr><td colspan=3 height=200 widht=100%><br></td></tr>\n";
#	if ( $fejl_i_liste ) {
#		print "<tr><td width='20%'><br /></td><td><b>Advarsel: Fejl i betalinger:</b><br>\n";
#		print $fejl_i_liste;
#		print "</td><td width='20%'><br /></td></tr>\n";
#	}
	print "<tr><td width=40%><br></td><td $top_bund title=\"Klik på knappen for at &aring;bne betalingsfilen eller h&oslash;jreklik for at gemme\"> <a href='$filnavn'>Se / gem betalingsfil</a></td><td width=40%><br></td></tr>\n";

}
function udskriv_danskebank($db_id,$bruger_id,$liste_id) {
	$dd=date("Ymd");
	$filnavn="../temp/$db_id"."$bruger_id".".UTF";
	$fp=fopen("$filnavn","w");
	$qtxt="select firmanavn from adresser where art='S'";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$myName=$r['firmanavn'];
	
	$qtxt="select * from betalinger where liste_id=$liste_id order by betalingsdato";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		#$kort_ref = $r['kort_ref'];
#		if (substr($r['bet_type'], 0, 3)=="ERH") {
			$x++;
#			if ($betalingsdato<$dd)$betalingsdato=$dd;
#cho __LINE__."<br>";
			$linje='"CMBO",'; #felt 1
			$linje.='"'.$r['fra_kto'].'",'; # Felt 2 Fra-reg+ktonr
			$linje.='"'.$r['til_kto'].'",'; # Felt 3 Til-reg+ktonr
			$linje.='"'.str_replace('.','',$r['belob']).'",'; # felt 4; Beløb
			if (usdate($r['betalingsdato'])>date('Y-m-d')) $linje.='"'.$r['betalingsdato'].'",'; #Felt 5 Betalingsdato DDMMYYYY
			else $linje.='"",'; #Felt 5 Betaling snarest
			$linje.='"DKK",'; # felt 6 Valuta
			$linje.='"",'; # felt 7 Blank = Standard transfer
			$linje.='"","","","","","","","","","","","",'; # felt 8-19 Ubrugt
			$linje.='"'.$r['egen_ref'].'",'; # felt 20 meddelelse til egen kto  
			$linje.='"",'; # felt 21 Ubrugt
			$linje.='"'.$r['kort_ref'].'"'; # felt 22 meddelelse til kunde  
			$linje.="\r\n";
		fwrite($fp,$linje);
	}
	fclose($fp);
	print "<tr><td colspan=3 height=200 widht=100%><br></td></tr>\n";
#	if ( $fejl_i_liste ) {
#		print "<tr><td width='20%'><br /></td><td><b>Advarsel: Fejl i betalinger:</b><br>\n";
#		print $fejl_i_liste;
#		print "</td><td width='20%'><br /></td></tr>\n";
#	}
	print "<tr><td width=40%><br></td><td $top_bund title=\"Klik på knappen for at &aring;bne betalingsfilen eller h&oslash;jreklik for at gemme\"> <a href='$filnavn'>Se / gem betalingsfil</a></td><td width=40%><br></td></tr>\n";

}
function udskriv_jyskebank($db_id,$bruger_id,$liste_id){
	$filnavn="../temp/$db_id"."$bruger_id".".txt";
	$fp=fopen("$filnavn","w");
#		if (substr($r['bet_type'], 0, 3)=="ERH") {
		$linje='"';
		$linje.="IB000000000000";
		$linje.='","';
		$linje.=date("Ymd");
		$linje.='","';
		$tmp='';
		while (strlen($tmp)<90)$tmp.=' '; #felt3
		$linje.=$tmp;		
		$linje.='","';
		$tmp='';
		for ($i=0;$i<3;$i++){
			while (strlen($tmp)<255)$tmp.=' '; #felt4-6
			$linje.=$tmp;		
			if ($i<2) $linje.='","';
		}
		$linje.='"';
		$linje.="\r\n";
		fwrite($fp,$linje);
#	}
	$x=0;
	$sum=0;
	$qtxt="select * from betalinger where liste_id=$liste_id order by betalingsdato";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$kort_ref = $r['kort_ref'];
		if (substr($r['bet_type'], 0, 3)=="ERH") {
			$x++;
#cho __LINE__."<br>";		
			$linje='"';
			$linje.="IB030202000005"; #1 Trans-type 
			$linje.='","';
			$linje.="0001"; #2 Index
			$linje.='","';
			$linje.=substr($r['betalingsdato'],-4).substr($r['betalingsdato'],2,2).substr($r['betalingsdato'],0,2); #3 Eksp-dato
			$linje.='","';
			$tmp=usdecimal($r['belob'],2)*100; #4 Transbeløb
			$sum+=$tmp;
			while (strlen($tmp)<13)$tmp="0".$tmp;
			$linje.=$tmp; 
			$linje.="+"; #Beløb skal slutte med et + 
			$linje.='","';
			$tmp=$r['valuta']; #5 Mønt
			while (strlen($tmp)<3)$tmp.=" ";
			$linje.=$tmp;
			$linje.='","';
			$linje.='2'; #6 Fra-type
			$linje.='","';
			$tmp=$r['fra_kto']; #7 Fra-konto
			while (strlen($tmp)<15)$tmp="0".$tmp;
			$linje.=$tmp; 
			$linje.='","';
			$linje.='2'; #8 Overførselstype
			$linje.='","';
			$tmp=substr($r['til_kto'],0,4); # 9Til-regnr
			$linje.=$tmp;
			$linje.='","';
			$tmp=substr($r['til_kto'],4,10); # 10 Til-konto-nr
			while (strlen($tmp)<10)$tmp="0".$tmp;
			$linje.=$tmp;
			$linje.='","';
			$linje.='0'; #11 Adviseringstype
			$linje.='","';
			$tmp=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $r['kort_ref']),0,20)); #12cPosteringstekst
			while (strlen($tmp)<35)$tmp.=" ";
			$linje.=$tmp;
			$linje.='","';
			$tmp=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $r['modt_navn']),0,32)); #Navn
			while (strlen($tmp)<32)$tmp.=" ";
			$linje.=$tmp;
			$linje.='","';
			$tmp='';
			while (strlen($tmp)<32)$tmp.=" "; #felt 14 Adresse-1
			$linje.=$tmp;
			$linje.='","';
			$tmp='';
			while (strlen($tmp)<32)$tmp.=" "; #felt 15 Adresse-2
			$linje.=$tmp;
			$linje.='","';
			$tmp='';
			while (strlen($tmp)<4)$tmp.=" "; #felt 16 Postnr
			$linje.=$tmp;
			$linje.='","';
			$tmp='';
			while (strlen($tmp)<32)$tmp.=" "; #felt 17 Bynavn
			$linje.=$tmp;
			$linje.='","';
			$tmp='';
			$tmp.=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $r['egen_ref']),0,20)); #18 Eget-bilagnr
			while (strlen($tmp)<35)$tmp.=" ";
			$linje.=$tmp;
			$linje.='","';
			$tmp='';
			for ($i=0;$i<9;$i++) {
				$tmp='';
				while (strlen($tmp)<35)$tmp.=" "; #felt 19-27
				$linje.=$tmp;
				$linje.='","';
			}
			$linje.=" ";# felt 28 Blanke
			$linje.='","';
			$tmp='';
			while (strlen($tmp)<215)$tmp.=" "; #felt 29 Reserveret
			$linje.=$tmp;
			$linje.='"';
			$linje.="\r\n";
			#			if ($r['bet_type']=="ERH351") $kort_ref="71".$kort_ref;
#			$linje="\"$r[bet_type]\",\"$r[fra_kto]\",\"$r[egen_ref]\",\"$r[til_kto]\",\"$r[modt_navn]\",\"00\",\"0\",,\"".str_replace(".", "", $r['belob'])."\",\"".substr($r['betalingsdato'],0,4).substr($r['betalingsdato'],-2)."\",\"$kort_ref\",,,,,,,,,\"N\"\r\n";
		} elseif ($r['bet_type']=="SDCK020") { # SDC betalingstype K020 - FI-kort 71 
#cho __LINE__."<br>";		
			$bet_type=substr($r['bet_type'], 3);
			$linje=$bet_type.$r['fra_kto'].$r['betalingsdato'];
			$linje.=sprintf("%015.2f", str_replace(",", ".", str_replace(".", "", $r['belob'])))."N";
			$linje.=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $r['egen_ref']),0,20));
			$linje.=substr($r['til_kto'],0,8)."71";
			$linje.=sprintf("%015s", substr($kort_ref,0,15)); 
			$linlaengde=strlen($linje); # Linjelængden skal være på 87 tegn ifølge specifikationen - kan testes for senere
			echo "\n<!-- SDCK020 linjelængde er ".strlen($linje)." (skal være 87) -->\n"; # Linjelængden skal være på 87 tegn ifølge specifikationen - kan testes for senere
			$linje.="\r\n";
		} elseif ($r['bet_type']=="SDC3") { # SDC betalingstype 3 - bankovf. med kort advisering
#cho __LINE__."<br>";		
			$linje="3".$r['fra_kto'].substr($r['betalingsdato'],0,4).substr($r['betalingsdato'],-2);
			$linje.=sprintf("%015.2f", str_replace(",", ".", str_replace(".", "", $r['belob'])))."N";
			$linje.=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $r['egen_ref']),0,20));
			$linje.=substr($r['til_kto'], 0, 4).sprintf("%010s", substr($r['til_kto'], 4))."    ";
			$linje.=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $kort_ref),0,20));
			$linlaengde=strlen($linje); # Linjelængden skal være på 95 tegn ifølge specifikationen - kan testes for senere
			$linje.="\r\n";
		}
		fwrite($fp,$linje);
	}	
#	if (substr($r['bet_type'], 0, 3)=="ERH") {
		$linje='"';
		$linje.="IB999999999999";
		$linje.='","';
		$linje.=date("Ymd");
		$linje.='","';
		$tmp=$x;
		while (strlen($tmp)<6)$tmp='0'.$tmp; #felt3
		$linje.=$tmp;		
		$linje.='","';
		$tmp=$sum;
		while (strlen($tmp)<13)$tmp='0'.$tmp; #felt4
		$linje.=$tmp;
		$linje.="+";
		$linje.='","';
		$tmp='';
		while (strlen($tmp)<64)$tmp.=' '; #felt4
		$linje.=$tmp;		
		$linje.='","';
		$tmp='';
		for ($i=0;$i<3;$i++){
			while (strlen($tmp)<255)$tmp.=' '; #felt4
			$linje.=$tmp;		
			if ($i<2) $linje.='","';
		}
		$linje.='"';
		$linje.="\r\n";
		fwrite($fp,$linje);
#	}
	fclose($fp);
	print "<tr><td colspan=3 height=200 widht=100%><br></td></tr>\n";
#	if ( $fejl_i_liste ) {
#		print "<tr><td width='20%'><br /></td><td><b>Advarsel: Fejl i betalinger:</b><br>\n";
#		print $fejl_i_liste;
#		print "</td><td width='20%'><br /></td></tr>\n";
#	}
	print "<tr><td width=40%><br></td><td $top_bund title=\"Klik på knappen for at &aring;bne betalingsfilen eller h&oslash;jreklik for at gemme\"> <a href='$filnavn'>Se / gem betalingsfil</a></td><td width=40%><br></td></tr>\n";
}
function udskriv_bec($db_id,$bruger_id,$liste_id){
	$filnavn="../temp/$db_id"."$bruger_id".".txt";
	$fp=fopen("$filnavn","w");
	$q=db_select("select * from betalinger where liste_id=$liste_id order by betalingsdato",__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$kort_ref = $r['kort_ref'];
		if (substr($r['bet_type'], 0, 3)=="ERH") {
			if ($r['bet_type']=="ERH351") $kort_ref="71".$kort_ref;
			$linje="\"$r[bet_type]\",\"$r[fra_kto]\",\"$r[egen_ref]\",\"$r[til_kto]\",\"$r[modt_navn]\",\"00\",\"0\",,\"".str_replace(".", "", $r['belob'])."\",\"".substr($r['betalingsdato'],0,4).substr($r['betalingsdato'],-2)."\",\"$kort_ref\",,,,,,,,,\"N\"\r\n";
		} elseif ($r['bet_type']=="SDCK020") { # SDC betalingstype K020 - FI-kort 71 
			$bet_type=substr($r['bet_type'], 3);
			$linje=$bet_type.$r['fra_kto'].$r['betalingsdato'];
			$linje.=sprintf("%015.2f", str_replace(",", ".", str_replace(".", "", $r['belob'])))."N";
			$linje.=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $r['egen_ref']),0,20));
			$linje.=substr($r['til_kto'],0,8)."71";
			$linje.=sprintf("%015s", substr($kort_ref,0,15)); 
			$linlaengde=strlen($linje); # Linjelængden skal være på 87 tegn ifølge specifikationen - kan testes for senere
			echo "\n<!-- SDCK020 linjelængde er ".strlen($linje)." (skal være 87) -->\n"; # Linjelængden skal være på 87 tegn ifølge specifikationen - kan testes for senere
			$linje.="\r\n";
		} elseif ($r['bet_type']=="SDC3") { # SDC betalingstype 3 - bankovf. med kort advisering
			$linje="3".$r['fra_kto'].substr($r['betalingsdato'],0,4).substr($r['betalingsdato'],-2);
			$linje.=sprintf("%015.2f", str_replace(",", ".", str_replace(".", "", $r['belob'])))."N";
			$linje.=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $r['egen_ref']),0,20));
			$linje.=substr($r['til_kto'], 0, 4).sprintf("%010s", substr($r['til_kto'], 4))."    ";
			$linje.=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $kort_ref),0,20));
			$linlaengde=strlen($linje); # Linjelængden skal være på 95 tegn ifølge specifikationen - kan testes for senere
			$linje.="\r\n";
		}
		fwrite($fp,$linje);
	}	
	fclose($fp);
	print "<tr><td colspan=3 height=200 widht=100%><br></td></tr>\n";
#	if ( $fejl_i_liste ) {
#		print "<tr><td width='20%'><br /></td><td><b>Advarsel: Fejl i betalinger:</b><br>\n";
#		print $fejl_i_liste;
#		print "</td><td width='20%'><br /></td></tr>\n";
#	}
	print "<tr><td width=40%><br></td><td $top_bund title=\"Klik på knappen for at &aring;bne betalingsfilen eller h&oslash;jreklik for at gemme\"> <a href='$filnavn'>Se / gem betalingsfil</a></td><td width=40%><br></td></tr>\n";
}
function udskriv_sdc($db_id,$bruger_id,$liste_id){
	$filnavn="../temp/$db_id"."$bruger_id".".txt";
	$fp=fopen("$filnavn","w");
	$qtxt="select * from betalinger where liste_id=$liste_id order by betalingsdato";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$kort_ref = $r['kort_ref'];
		$linje="3".$r['fra_kto'].substr($r['betalingsdato'],0,4).substr($r['betalingsdato'],-2);
		$linje.=sprintf("%015.2f", str_replace(",", ".", str_replace(".", "", $r['belob'])))."N";
		$linje.=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $r['egen_ref']),0,20));
		$linje.=substr($r['til_kto'], 0, 4).sprintf("%010s", substr($r['til_kto'], 4))."    ";
		$linje.=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $kort_ref),0,20));
#		$linlaengde=strlen($linje);
		$linje.="\r\n";
		fwrite($fp,$linje);
	}
	fclose($fp);
	print "<tr><td colspan=3 height=200 widht=100%><br></td></tr>\n";
#	if ( $fejl_i_liste ) {
#		print "<tr><td width='20%'><br /></td><td><b>Advarsel: Fejl i betalinger:</b><br>\n";
#		print $fejl_i_liste;
#		print "</td><td width='20%'><br /></td></tr>\n";
#	}
	print "<tr><td width=40%><br></td><td $top_bund title=\"Klik på knappen for at &aring;bne betalingsfilen eller h&oslash;jreklik for at gemme\"> <a href='$filnavn'>Se / gem betalingsfil</a></td><td width=40%><br></td></tr>\n";
}
function udskriv_xml($db_id,$bruger_id,$liste_id){

	global $format,$top_bund;
	$r=db_fetch_array(db_select("select * from adresser where art = 'S'",__FILE__ . " linje " . __LINE__));
	$tmp=$r['bank_konto'];
	$myName=$r['firmanavn'];
	$myCvr=trim(str_replace(' ','',$r['cvrnr']));
	$myEan=$r['ean'];
	$myCtry=$r['land'];
	$myBIG=$r['swift'];

	if (strtolower(substr($myCtry,0,1))=='n') {
		$myCtry='NO';
		$myCcy='NOK';
		$x=0;
		$fp=fopen('../importfiler/BIC_NO.txt','r');
		while($line=fgets($fp)) {
			list($regLst[$x],$bicLst[$x])=explode(chr(9),trim($line));
			$x++;
		}
		fclose($fp);
	} else {
		$myCtry='DK';
		$myCcy='DKK';
	}
	
	$r=db_fetch_array(db_select("select tidspkt from betalingsliste where id='$liste_id'",__FILE__ . " linje " . __LINE__));
	$tidspkt = substr($r['tidspkt'],-10);

	$date    = date("Y-m-d",$tidspkt);
	$time    = date("H:i:s",$tidspkt);

	$qtxt="select * from betalinger where liste_id=$liste_id order by id";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	$x=0;
	while ($r=db_fetch_array($q)) {
		$EndToEndId[$x]  = $r['id'];
		$ordreId[$x]     = $r['ordre_id'];
		$bilagId[$x]     = $r['bilag_id'];
		$PmtInfId[$x]    = $tidspkt ."-". $x;
		$Id[$x]          = $r['til_kto'];
		$Nm[$x]          = urlencode($r['modt_navn']); 
		$Prtry[$x]       = urlencode($r['kort_ref']);
		$DbtrAcctId[$x]  = $r['fra_kto'];
		$CdtrAcctId[$x]  = $r['til_kto'];
		$ReqdExctnDt[$x] = usdate($r['betalingsdato']);
		$InstdAmt[$x]    = usdecimal($r['belob']);
		$Ctry[$x]        = $myCtry;
		$Ccy[$x]         = $myCcy;
		$BIC[$x]         = NULL;
		$DbNo[$x]        = NULL;
		$x++;
	}
	for ($x=0;$x<count($Id);$x++){

		if ($ordreId[$x]) {
			$qtxt = "select konto_id from ordrer where id='$ordreId[$x]'";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$DbNo[$x]=$r['konto_id'];
		}
		if (!$DbNo[$x] && $bilagId[$x]) {
			$qtxt = "select d_type,debet,k_type,kredit from kassekladde where id='$bilagId[$x]'";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$DbNo[$x]=NULL;
			if ($r['d_type']=='D' && $r['debet']) $DbNo[$x]=$r['debet'];
			elseif ($r['k_type']=='D' && $r['kredit']) $DbNo[$x]=$r['kredit'];
		} 
		if ($DbNo[$x]) {
			$qtxt = "select land,swift from adresser where art='D' and kontonr='$DbNo[$x]'";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$BIC[$x]=$r['swift'];
		}
		if ($myCtry=='NO') {
			$DbtrAcctId[$x]=substr($DbtrAcctId[$x],0,4).substr($DbtrAcctId[$x],-7);
			$CdtrAcctId[$x]=substr($CdtrAcctId[$x],-11);
			if (!$myBIC) {
				for ($b=0;$b<count($regLst);$b++) {
					if (substr($DbtrAcctId[$x],0,4)==$regLst[$b]) $myBIC=$bicLst[$b];
				}
			}
			if (!$BIC[$x]) {
				for ($b=0;$b<count($regLst);$b++) {
					if (substr($CdtrAcctId[$x],0,4)==$regLst[$b]) $BIC[$x]=$bicLst[$b];
				}
			}
		}
	}
	$filnavn="../temp/$db_id"."$bruger_id".".xml";
	$fp=fopen("$filnavn","w");
	$xmltxt = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n";
	$xmltxt.= "<Document xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"\r\n";
	$xmltxt.= "xmlns=\"urn:iso:std:iso:20022:tech:xsd:pain.001.001.03\"\r\n";
	$xmltxt.= "xsi:schemaLocation=\"urn:iso:std:iso:20022:tech:xsd:pain.001.001.03 pain.001.001.03.xsd\">\r\n";
	$xmltxt.= "  <CstmrCdtTrfInitn>\r\n";
	$xmltxt.= "    <GrpHdr>\r\n";
	$xmltxt.= "      <MsgId>". $db_id ."00000". $liste_id. "</MsgId>\r\n";
	$xmltxt.= "      <CreDtTm>". date("Y-m-d") ."T". date("H:i:s") ."</CreDtTm>\r\n";
	$xmltxt.= "      <NbOfTxs>". count($PmtInfId) ."</NbOfTxs>\r\n";
	$xmltxt.= "      <InitgPty>\r\n";
	$xmltxt.= "        <Nm>$myName</Nm>\r\n";
	$xmltxt.= "        <Id>\r\n";
	$xmltxt.= "          <OrgId>\r\n";
	$xmltxt.= "            <Othr>\r\n";
	$xmltxt.= "              <Id>". str_replace(' ','',$myCvr) ."</Id>\r\n";
	$xmltxt.= "              <SchmeNm>\r\n";
	$xmltxt.= "                <Cd>CUST</Cd>\r\n";
	$xmltxt.= "              </SchmeNm>\r\n";
	$xmltxt.= "            </Othr>\r\n";
 	$xmltxt.= "            <Othr>\r\n";
 	$xmltxt.= "              <Id>PROVISJON</Id>\r\n";
	$xmltxt.= "              <SchmeNm>\r\n";
	$xmltxt.= "                <Cd>BANK</Cd>\r\n";
	$xmltxt.= "              </SchmeNm>\r\n";
	$xmltxt.= "            </Othr>\r\n";
	$xmltxt.= "          </OrgId>\r\n";
	$xmltxt.= "        </Id>\r\n";
	$xmltxt.= "      </InitgPty>\r\n";
	$xmltxt.= "    </GrpHdr>\r\n";
	fwrite($fp,$xmltxt);
	for ($x=0;$x<count($PmtInfId);$x++) {
		$xmltxt = "    <PmtInf>\r\n";
		$xmltxt.= "      <PmtInfId>$PmtInfId[$x]</PmtInfId>\r\n";
		$xmltxt.= "      <PmtMtd>TRF</PmtMtd>\r\n";
		$xmltxt.= "      <PmtTpInf>\r\n";
		$xmltxt.= "        <InstrPrty>NORM</InstrPrty>\r\n";
		$xmltxt.= "        <SvcLvl>\r\n";
		$xmltxt.= "          <Cd>NURG</Cd>\r\n";
		$xmltxt.= "        </SvcLvl>\r\n";
		$xmltxt.= "        <CtgyPurp>\r\n";
		$xmltxt.= "          <Cd>SUPP</Cd>\r\n";
		$xmltxt.= "        </CtgyPurp>\r\n";
		$xmltxt.= "        </PmtTpInf>\r\n";
		$xmltxt.= "        <ReqdExctnDt>$ReqdExctnDt[$x]</ReqdExctnDt>\r\n";
		$xmltxt.= "        <Dbtr>\r\n";
		$xmltxt.= "          <Nm>$myName</Nm>\r\n";
		$xmltxt.= "          <PstlAdr>\r\n";
		$xmltxt.= "            <Ctry>$Ctry[$x]</Ctry>\r\n";
		$xmltxt.= "          </PstlAdr>\r\n";
		if ($format != 'norskeBank') {
			$xmltxt.= "          <Id>\r\n";
			$xmltxt.= "            <OrgId>\r\n";
			$xmltxt.= "              <Othr>\r\n";
			$xmltxt.= "               <Id>". str_replace(' ','',$myCvr) ."</Id>\r\n";
			$xmltxt.= "              <SchmeNm>\r\n";
			$xmltxt.= "                <Cd>BANK</Cd>\r\n";
			$xmltxt.= "              </SchmeNm>\r\n";
			$xmltxt.= "               </Othr>\r\n";
			$xmltxt.= "            </OrgId>\r\n";
			$xmltxt.= "          </Id>\r\n";
		}
		$xmltxt.= "        </Dbtr>\r\n";
		$xmltxt.= "        <DbtrAcct>\r\n";
		$xmltxt.= "          <Id>\r\n";
		$xmltxt.= "            <Othr><Id>$DbtrAcctId[$x]</Id>\r\n";
		$xmltxt.= "            <SchmeNm>\r\n";
		$xmltxt.= "              <Cd>BBAN</Cd>\r\n";
		$xmltxt.= "            </SchmeNm>\r\n";
		$xmltxt.= "            </Othr>\r\n";
		$xmltxt.= "          </Id>\r\n";
		$xmltxt.= "          <Ccy>$myCcy</Ccy>\r\n";
		$xmltxt.= "        </DbtrAcct>\r\n";
		if ($myBIC) {
			$xmltxt.= "       <DbtrAgt>\r\n";
			$xmltxt.= "         <FinInstnId>\r\n";
			$xmltxt.= "           <BIC>$myBIC</BIC>\r\n";
			$xmltxt.= "           <PstlAdr>\r\n";
			$xmltxt.= "             <Ctry>$myCtry</Ctry>\r\n";
			$xmltxt.= "           </PstlAdr>\r\n";
			$xmltxt.= "         </FinInstnId>\r\n";
			$xmltxt.= "       </DbtrAgt>\r\n";
		}
		$xmltxt.= "       	<CdtTrfTxInf>\r\n";
		$xmltxt.= "         <PmtId>\r\n";
		$InstrId = $x+1000;
		$xmltxt.= "           <InstrId>$InstrId</InstrId>\r\n";
		$xmltxt.= "           <EndToEndId>$EndToEndId[$x]</EndToEndId>\r\n";
		$xmltxt.= "         </PmtId>\r\n";
		$xmltxt.= "         <Amt>\r\n";
		$xmltxt.= "           <InstdAmt Ccy='$Ccy[$x]'>$InstdAmt[$x]</InstdAmt>\r\n";
		$xmltxt.= "         </Amt>\r\n";
		if ($BIC[$x]) {
			$xmltxt.= "         <CdtrAgt>\r\n";
			$xmltxt.= "           <FinInstnId>\r\n";
			$xmltxt.= "             <BIC>$BIC[$x]</BIC>\r\n";
			$xmltxt.= "             <PstlAdr>\r\n";
			$xmltxt.= "               <Ctry>$Ctry[$x]</Ctry>\r\n";
			$xmltxt.= "             </PstlAdr>\r\n";
			$xmltxt.= "           </FinInstnId>\r\n";
			$xmltxt.= "         </CdtrAgt>\r\n";
		}
		$xmltxt.= "         <Cdtr>\r\n";
		$xmltxt.= "           <Nm>$Nm[$x]</Nm>\r\n";
		$xmltxt.= "           <PstlAdr>\r\n";
		$xmltxt.= "             <Ctry>$Ctry[$x]</Ctry>\r\n";
		$xmltxt.= "           </PstlAdr>\r\n";
		$xmltxt.= "         </Cdtr>\r\n";
		$xmltxt.= "         <CdtrAcct>\r\n";
		$xmltxt.= "           <Id>\r\n";
		$xmltxt.= "             <Othr>\r\n";
		$xmltxt.= "               <Id>$CdtrAcctId[$x]</Id>\r\n";
		$xmltxt.= "               <SchmeNm>\r\n";
		$xmltxt.= "                 <Cd>BBAN</Cd>\r\n";
		$xmltxt.= "               </SchmeNm>\r\n";
		$xmltxt.= "             </Othr>\r\n";
		$xmltxt.= "           </Id>\r\n";
		$xmltxt.= "         </CdtrAcct>\r\n";
		$xmltxt.= "         <Purp>\r\n";
		$xmltxt.= "           <Prtry>$Prtry[$x]</Prtry>\r\n";
		$xmltxt.= "         </Purp>\r\n";
		$xmltxt.= "       </CdtTrfTxInf>\r\n";
		$xmltxt.= "    </PmtInf>\r\n";
	fwrite($fp,$xmltxt);
	}
	$xmltxt = "</CstmrCdtTrfInitn>\r\n";
	$xmltxt.= "</Document>\r\n";
	fwrite($fp,$xmltxt);
	fclose($fp);
	
/*
  
	$qtxt="select * from betalinger where liste_id=$liste_id order by betalingsdato";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$kort_ref = $r['kort_ref'];
		$linje="3".$r['fra_kto'].substr($r['betalingsdato'],0,4).substr($r['betalingsdato'],-2);
		$linje.=sprintf("%015.2f", str_replace(",", ".", str_replace(".", "", $r['belob'])))."N";
		$linje.=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $r['egen_ref']),0,20));
		$linje.=substr($r['til_kto'], 0, 4).sprintf("%010s", substr($r['til_kto'], 4))."    ";
		$linje.=sprintf("%-20s", substr(iconv("UTF-8", "Windows-1252//IGNORE", $kort_ref),0,20));
#		$linlaengde=strlen($linje);
		$linje.="\r\n";
		fwrite($fp,$linje);
	}
	fclose($fp);
*/	
	print "<tr><td colspan=3 height=200 widht=100%><br></td></tr>\n";
#	if ( $fejl_i_liste ) {
#		print "<tr><td width='20%'><br /></td><td><b>Advarsel: Fejl i betalinger:</b><br>\n";
#		print $fejl_i_liste;
#		print "</td><td width='20%'><br /></td></tr>\n";
#	}
	print "<tr><td width=40%><br></td><td $top_bund title=\"Klik på knappen for at &aring;bne betalingsfilen eller h&oslash;jreklik for at gemme\"> <a href='$filnavn'>Se / gem betalingsfil</a></td><td width=40%><br></td></tr>\n";
}

?>
</tbody>
</table>
	</td></tr>
</tbody></table>

</body></html>
