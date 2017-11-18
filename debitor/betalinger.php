<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ---------debitor/betalinger.php---------------------Patch 3.7.0-----2017.05.30---
// LICENS
//
// // Dette program er fri software. Du kan gendistribuere det og / eller
// // modificere det under betingelserne i GNU General Public License (GPL)
// // som er udgivet af "The Free Software Foundation", enten i version 2
// // af denne licens eller en senere version, efter eget valg.
// // Fra og med version 3.2.2 dog under iagttagelse af følgende:
// // 
// // Programmet må ikke uden forudgående skriftlig aftale anvendes
// // i konkurrence med saldi.dk ApS eller anden rettighedshaver til programmet.
// //
// // Dette program er udgivet med haab om at det vil vaere til gavn,
// // men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// // GNU General Public Licensen for flere detaljer.
// //
// // En dansk oversaettelse af licensen kan laeses her:
// // http://www.saldi.dk/dok/GNU_GPL_v2.html
// //
// // Copyright (c) 2003-2017 saldi.dk ApS
// -----------------------------------------------------------------------------------
//
// 2015.11.04 Kopieret fra kreditor og tilrettet til debitorer (phr) 
// 2016.04.07 Indsat db_escape_string. Søg db_escape_string
// 2016.06.27 Indsat valg mellem BEC, Jyske Bank og Nordea.
// 2016.11.30 Tilføjet Danske Bank.
// 2017.05.05 Instætter kontonummer på afregninger fra kassekladde 20170502
// 2017.05.30 Tilføjet SDC - søg sdc (Samme som bec)


@session_start();
$s_id=session_id();
		
$modulnr=12;	
$title="betalinger";
$css="../css/standard.css";
		
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/forfaldsdag.php");

$liste_id=isset($_GET['liste_id'])? $_GET['liste_id']:Null;
#$sort=isset($_GET['sort'])? $_GET['sort']:Null;
#$rf=isset($_GET['rf'])? $_GET['rf']:Null;
#$vis=isset($_GET['vis'])? $_GET['vis']:Null;
$find=isset($_GET['find'])? $_GET['find']:Null;

if (isset($_POST['mail']) && $_POST['mail']) {
	print "<meta http-equiv=\"refresh\" content=\"0;URL=mail_modtagere.php?liste_id=$liste_id\">";
	exit;
}

if ($dan_liste) {
	dan_liste($liste_id);
	exit;
}
if ($_POST['slet_ugyldige'] || $_POST['gem']|| $_POST['udskriv']) {
#cho "Gem $_POST[gem]<br>";
	$id=array();$erh=array();
	$slet_ugyldige=isset($_POST['slet_ugyldige'])? $_POST['slet_ugyldige']:Null;
#	$liste_id=($_POST['liste_id'])? $_POST['liste_id']:Null;
	$listenote=($_POST['listenote'])? $_POST['listenote']:Null;
	$udskriv=isset($_POST['udskriv'])? $_POST['udskriv']:Null;
	$format=isset($_POST['format'])? $_POST['format']:Null;
	$id=isset($_POST['id'])? $_POST['id']:Null;
	$erh=isset($_POST['erh'])? $_POST['erh']:Null;
	$fra_kto=isset($_POST['fra_kto'])? $_POST['fra_kto']:Null;
	$egen_ref=isset($_POST['egen_ref'])? $_POST['egen_ref']:Null;
	$til_kto=isset($_POST['til_kto'])? $_POST['til_kto']:Null;
	$kort_ref=isset($_POST['kort_ref'])? $_POST['kort_ref']:Null;
	$modt_navn=isset($_POST['modt_navn'])? $_POST['modt_navn']:Null;
	$belob=isset($_POST['belob'])? $_POST['belob']:Null;
	$valuta=isset($_POST['valuta'])? $_POST['valuta']:Null;
	$betalingsdato=isset($_POST['betalingsdato'])? $_POST['betalingsdato']:Null;
	$slet=isset($_POST['slet'])? $_POST['slet']:Null;
	$ugyldig=isset($_POST['ugyldig'])? $_POST['ugyldig']:Null;
	$antal=isset($_POST['antal'])? addslashes($_POST['antal']):Null;

	for ($x=1;$x<=$antal;$x++) {
		if ($slet_ugyldige && $ugyldig[$x] == $id[$x]) $slet[$x]='on';
#cho "$slet_ugyldige -- $ugyldig[$x] <br>";
		if ($slet[$x]=='on') {
			#cho "delete from betalinger where id='$id[$x]'<br>";
			db_modify("delete from betalinger where id='$id[$x]'",__FILE__ . " linje " . __LINE__);
		}	
		else {
			#cho "update betalinger set bet_type='$erh[$x]',fra_kto='$fra_kto[$x]',egen_ref='$egen_ref[$x]',til_kto='$til_kto[$x]',kort_ref='$kort_ref[$x]',modt_navn='$modt_navn[$x]',belob='$belob[$x]',valuta='$valuta[$x]',betalingsdato='$betalingsdato[$x]' where id='$id[$x]'<br>";
			db_modify("update betalinger set bet_type='$erh[$x]',fra_kto='$fra_kto[$x]',egen_ref='$egen_ref[$x]',til_kto='$til_kto[$x]',kort_ref='$kort_ref[$x]',modt_navn='".db_escape_string($modt_navn[$x])."',belob='$belob[$x]',valuta='$valuta[$x]',betalingsdato='$betalingsdato[$x]' where id='$id[$x]'",__FILE__ . " linje " . __LINE__);
		}
	}
	db_modify("update betalingsliste set listenote='$listenote' where id='$liste_id'",__FILE__ . " linje " . __LINE__);
	if ($udskriv) {
		$r=db_fetch_array(db_select("select bogfort from betalingsliste where id='$liste_id'",__FILE__ . " linje " . __LINE__)); 
		if ($r['bogfort']=='-') db_modify("update betalingsliste set bogfort='V', bogfort_af='$brugernavn' where id='$liste_id'",__FILE__ . " linje " . __LINE__);
	}
}

$findlink=NULL;
if (!$liste_id) $liste_id=0;
else {
	$r=db_fetch_array(db_select("select bogfort from betalingsliste where id='$liste_id'",__FILE__ . " linje " . __LINE__));
	if ($r['bogfort']=='-') $findlink="<a href=betalinger.php?liste_id=$liste_id&find=nye>Find nye</a>";
}
$linjebg=$bgcolor;
#$erh_title= "ERH351 = FI-kort 71\nERH352 = FI-kort 04 & 15\nERH354 = FI-kort 01 & 41\nERH355 = Bankoverf. med normal advisering\nERH356 = Bankoverf. med straks advisering\nERH357 = FI-kort 73\nERH358 = FI-kort 75\nERH400 = Udenlandsk overf&oslash;rsel\nSDC3 = Bankoverf. med kort advisering (SDC)\nSDCK020 = FI-kort 71 (SDC)\nSDCK037 = Udenlandsk overf&oslash;rsel (SDC)";
$erh_title= "ERH355 = Bankoverf. med normal advisering";


?>
		<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0"><tbody>
		<tr><td height = "25" align="center" valign="top">
		<table width="100%" align="center" border="0" cellspacing="2" cellpadding="0"><tbody>
		<td width="10%" <?php echo $top_bund ?>><font face="Helvetica, Arial, sans-serif" color="#000066"><a href=../debitor/betalingsliste.php accesskey=L>Luk</a></td>
		<td width="80%" <?php echo $top_bund ?> ><font face="Helvetica, Arial, sans-serif" color="#000066">Betalinger til bank</td>
		<td width="10%" <?php echo $top_bund ?> ><font face="Helvetica, Arial, sans-serif" color="#000066"><?php print $findlink;?></td>

		</tbody></table>
		</td></tr>
		<tr><td valign="top">
		<table cellpadding="1" cellspacing="0" border="0" width="100%" valign = "top">
<?php
if (!$liste_id) {
	$tidspkt=microtime();
	$listedate=date("Y-m-d");
	db_modify("insert into betalingsliste(listedate, listenote, oprettet_af, hvem, tidspkt, bogfort) values ('$listedate', '$listenote', '$brugernavn', '$brugernavn', '$tidspkt', '-')",__FILE__ . " linje " . __LINE__);
	$r=db_fetch_array(db_select("select MAX(id) as id from betalingsliste where tidspkt='$tidspkt'",__FILE__ . " linje " . __LINE__));
	$liste_id=$r['id'];
} 
if ($find) {
	$bilag_id_list=array();
	$ordre_id_list=array();
	$x=0; $y=0;
#cho "select bank_reg, bank_konto from adresser where art = 'S'<br>";
	$r=db_fetch_array(db_select("select firmanavn, bank_reg, bank_konto from adresser where art = 'S'",__FILE__ . " linje " . __LINE__));
	$tmp=$r['bank_konto'];
	$eget_navn=$r['firmanavn'];
	while (strlen($tmp)<10) {
		$tmp='0'.$tmp; # kontonumre skal vaere paa 10 cifre
	}
	$egen_bank=$r['bank_reg'].$tmp;
#cho "select ordre_id, bilag_id from betalinger<br>";
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
	$qtxt="select openpost.id as id,openpost.beskrivelse as egen_ref,openpost.amount as amount,openpost.valuta as valuta,openpost.faktnr as faktnr,openpost.transdate as transdate,openpost.bilag_id as bilag_id,openpost.forfaldsdate as forfaldsdate,openpost.betal_id as betal_id,adresser.erh as erh, openpost.refnr as refnr, openpost.kladde_id as kladde_id, openpost.konto_nr as kontonr, adresser.bank_reg as modt_reg, adresser.bank_konto as modt_konto, adresser.firmanavn as modt_navn,adresser.bank_fi as modt_fi,adresser.betalingsbet as betalingsbet,adresser.betalingsdage as betalingsdage from openpost, adresser where openpost.udlignet != '1' and openpost.amount < 0 and openpost.konto_id = adresser.id and adresser.art = 'D'";
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
			} elseif ($r['faktnr']) $kort_ref=$eget_navn.":".$r['faktnr'];
			else $kort_ref=$eget_navn;
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
			$qtxt.="('$erh','$egen_bank','$egen_ref','$modt_konto','".db_escape_string($r['modt_navn'])."','$kort_ref','$belob','$forfaldsdag',"; $qtxt.="'$valuta', '$bilag_id', '$ordre_id','$liste_id')";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
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
		list($kort_ref,$k1[$x],$k2[$x],$k3[$x],$k4[$x],$k5[$x],$k6[$x],$k7[$x],$k8[$x])=betalingskontrol($erh[$x],$r['fra_kto'],$r['egen_ref'],$r['til_kto'],$r['kort_ref'],$r['modt_navn'],$r['belob'],$r['valuta'],$r['betalingsdato']);
echo "<!-- UDDATA for ".$x.": k1:".$k1[$x]." k2:". $k2[$x]." k3:".$k3[$x]." k4:".$k4[$x]." k5:".$k5[$x]." k6:".$k6[$x]." K7:".$k7[$x]." K8:".$k8[$x]. "-->\n";
		if($k1[$x]||$k2[$x]||$k3[$x]||$k4[$x]||$k5[$x]||$k6[$x]||$k7[$x]||$k8[$x]) {
			$fejl_i_liste.=fejl_i_betalingslinje($x, $k1[$x], $k2[$x], $k3[$x], $k4[$x], $k5[$x], $k6[$x], $k7[$x], $k8[$x]); # 20140717
		}
	}
}
if ($udskriv) {
	if ($format=='bec') udskriv_bec($db_id,$bruger_id,$liste_id);
	elseif ($format=='danskebank') udskriv_danskebank($db_id,$bruger_id,$liste_id);
	elseif ($format=='jyskebank') udskriv_jyskebank($db_id,$bruger_id,$liste_id);
	elseif ($format=='nordea') udskriv_nordea($db_id,$bruger_id,$liste_id);
	elseif ($format=='sdc') udskriv_sdc($db_id,$bruger_id,$liste_id);
} else { 
	print "<form name=\"betalinger\" action=\"betalinger.php?liste_id=$liste_id\" method=\"post\">";
	$r=db_fetch_array(db_select("select listenote, bogfort from betalingsliste where id='$liste_id'",__FILE__ . " linje " . __LINE__));
	$listenote=$r['listenote'];
	$bogfort=$r['bogfort'];
	print "<tr>";
	if ($bogfort!='-'){
		print "<td colspan=11 align=center><b> $listenote</b></td></tr>";
		print "<td colspan=11><hr></td></tr>";
	}	else {
		print "<td><b> <span title= 'Her kan skrives en bem&aelig;rkning til kladden'>Bem&aelig;rkning:</b></td>";
		print "<td colspan=10><input type=\"text\" size=95 name=listenote value=\"$listenote\"></td></tr>";
	}
	#print"<tr><td colspan=11><hr></td></tr>";
	print "<tr>
		<td><span title=\"$erh_title\"><b>Betalingstype</b></span></td>
		<td><b>Fra konto</b></td>
		<td><b>Egen ref.</b></td>
		<td><b>Modtager konto</b></td>
		<td><b>Modtager ref.</b></td>
		<td><b>Modtager</b></td>
		<td align=center><b>Bel&oslash;b</b></td>
		<td align=center><b>Valuta</b></td>
		<td align=center><b>Betalingsdato</b></td>
		<td align=center><span title='Se i nyt vindue'><b>Se</b></span></td>";
		if ($bogfort!='V') print "<td align=center><span title='Slet linjen fra listen'><b>Slet</b></span></td>";
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
#				if ($erh[$x]!='ERH351') print "<option>ERH351</option>\n";
#				if ($erh[$x]!='ERH352') print "<option>ERH352</option>\n";
#				if ($erh[$x]!='ERH354') print "<option>ERH354</option>\n";
#				if ($erh[$x]!='ERH355') print "<option>ERH355</option>\n";
				if ($erh[$x]!='ERH356') print "<option>ERH356</option>\n";
#				if ($erh[$x]!='ERH357') print "<option>ERH357</option>\n";
#				if ($erh[$x]!='ERH358') print "<option>ERH358</option>\n";
#				if ($erh[$x]!='ERH400') print "<option>ERH400</option>\n";
#				if ($erh[$x]!='SDC3') print "<option>SDC3</option>\n";
#				if ($erh[$x]!='SDCK020') print "<option>SDCK020</option>\n";
#				if ($erh[$x]!='SDCK037') print "<option>SDCK037</option>\n";
#				print "<option>Slet</option>\n";
				print "</SELECT></span></td>\n";
				print "
					<td $k1_bg[$x]><span title=\"$k1[$x]\"><input type=\"text\" style=\"text-align:right\" name=\"fra_kto[$x]\" size=\"15px\" value=\"$r[fra_kto]\"></span></td>
					<td $k2_bg[$x]><span title=\"$k2[$x]\"><input type=\"text\" name=\"egen_ref[$x]\" size=\"30px\" value=\"$r[egen_ref]\"></span></td>
					<td $k3_bg[$x]><span title=\"$k3[$x]\"><input type=\"text\" style=\"text-align:right\" name=\"til_kto[$x]\" size=\"15px\" value=\"$r[til_kto]\"></span></td>
					<td $k4_bg[$x]><span title=\"$k4[$x]\"><input type=\"text\" style=\"text-align:left\" name=\"kort_ref[$x]\" size=\"30px\" value=\"$kort_ref[$x]\"></span></td>
					<td $k5_bg[$x]><span title=\"$k5[$x]\"><input type=\"text\" name=\"modt_navn[$x]\" size=\"30px\" value=\"$r[modt_navn]\"></span></td>
					<td $k6_bg[$x]><span title=\"$k6[$x]\"><input type=\"text\" style=\"text-align:right\" name=\"belob[$x]\" size=\"15px\" value=\"$r[belob]\"></span></td>
					<td $k7_bg[$x]><span title=\"$k7[$x]\"><input type=\"text\" style=\"text-align:right\" name=\"valuta[$x]\" size=\"5px\" value=\"$r[valuta]\"></span></td>
					<td $k8_bg[$x]><span title=\"$k8[$x]\"><input type=\"text\" style=\"text-align:right\" name=\"betalingsdato[$x]\" size=\"10px\" value=\"$r[betalingsdato]\"></span></td>";
				if ($r['ordre_id'])	print "<td align=right onmouseover=\"this.style.cursor = 'pointer'\"; onclick=\"javascript:k_ordre=window.open('../debitor/ordre.php?id=$r[ordre_id]','k_ordre','width=800,height=400,scrollbars=1,resizable=1')\"><span title=\"Se modtagelse i nyt vindue\"><u>M:$r2[modtagelse]</u></span></td>";
				else print "<td align=right onmouseover=\"this.style.cursor = 'pointer'\"; onclick=\"javascript:kaskl=window.open('../finans/kassekladde.php?kladde_id=$r2[kladde_id]','kaskl','width=800,height=400,scrollbars=1,resizable=1')\"><span title=\"Se bilag i nyt vindue\"><u>B:$r2[bilag]</u></span></td>";		
				print	"<td><span title=\"Slet linje fra liste\"><input type=\"checkbox\" name=\"slet[$x]\"></span></td>";
				print "</tr>";
				print "<input type=\"hidden\" name=\"id[$x]\" value=\"$r[id]\">";
				print "<input type=\"hidden\" name=\"antal\" value=\"$x\">";
		} else {
			if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
			else {$linjebg=$bgcolor5; $color='#000000';}
			print "<tr bgcolor=\"$linjebg\">";
			print "<td>$erh[$x]</td><td>$r[fra_kto]</td><td>$r[egen_ref]</td><td>$r[til_kto]</td><td>$kort_ref[$x]</td><td>$r[modt_navn]</td><td align=right>$r[belob]</td><td align=center>$r[valuta]</td><td align=right>$r[betalingsdato]</td>";
				if ($r['ordre_id'])	print "<td align=right onmouseover=\"this.style.cursor = 'pointer'\"; onclick=\"javascript:k_ordre=window.open('../debitor/ordre.php?id=$r[ordre_id]','k_ordre','width=800,height=400,scrollbars=1,resizable=1')\"><span title=\"Se modtagelse i nyt vindue\"><u>M:$r2[modtagelse]</u></span></td></tr>";
				else print "<td align=right onmouseover=\"this.style.cursor = 'pointer'\"; onclick=\"javascript:kaskl=window.open('../finans/kassekladde.php?kladde_id=$r2[kladde_id]','kaskl','width=800,height=400,scrollbars=1,resizable=1')\"><span title=\"Se bilag i nyt vindue\"><u>B:$r2[bilag]</u></span></td></tr>";		
		}
	}
	$modtagerantal=$x;
	#($kn_kontrol)?$modtagerantal=0:$modtagerantal=$x;
	
	print "<tr><td colspan=11 align=center>";
	if ($bogfort!='V') {
		print "<input type=submit accesskey=\"g\" value=\"Gem\" name=\"gem\">";
		if(!$fejl && $modtagerantal) { 
			print "&nbsp;<select name=\"format\">";
			print "<option value=\"bec\">BEC</option>";
			print "<option value=\"danskebank\">Danske Bank</option>";
			print "<option value=\"jyskebank\">Jyske Bank</option>";
			print "<option value=\"nordea\">Nordea</option>";
			print "<option value=\"sdc\">SDC</option>";
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
		print "</select>";
		print "<input type=submit accesskey=\"u\" value=\"Udskriv\" name=\"udskriv\">";
		}
	if ($modtagerantal) print "&nbsp;<input type=submit accesskey=\"m\" value=\"Mail til modtagere\" name=\"mail\">";
}
	
print "</td></tr>";
print "</form>";
##############################################################################################################
function betalingskontrol($erh,$fra_kto,$egen_ref,$til_kto,$kort_ref,$modt_navn,$belob,$valuta,$betalingsdato) {
	$k1[$x]=NULL;$k2[$x]=NULL;$k3[$x]=NULL;$k4[$x]=NULL;$k5[$x]=NULL;$k6[$x]=NULL;$k7[$x]=NULL;$k8[$x]=NULL;
	if (!$fra_kto || !is_numeric($fra_kto)||strlen($fra_kto)!=14) $k1[$x] = "Egen konto ikke gyldig";
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
		if (!$til_kto || !is_numeric($til_kto)||strlen($til_kto)!=14) $k3[$x] = "Modtager konto ikke gyldig - skal være på 14 cifre (regnr. på 4 og kontonr på 10)";
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
	$eget_navn=$r['firmanavn'];
	
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
	$eget_navn=$r['firmanavn'];
	
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

?>
</tbody>
</table>
	</td></tr>
</tbody></table>

</body></html>
