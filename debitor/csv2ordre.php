<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// -------------debitor/csv2ordre.php----------lap 3.5.6-----2016-05-02----
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
// -----------------------------------------------------------------------
// 20160502 PHR Sat returnconfirm på [Hent] for at hindre dobbeltimport ved dobbeltklik.
// 20190812 PHR	More information from address is importrtes.

@session_start();
$s_id=session_id();
$css="../css/standard.css";

$title="Importer ordrer fra CSV";

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/ordrefunc.php");

print "<div align=\"center\">";

print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>";
print "<tr><td height = \"25\" align=\"center\" valign=\"top\">";
print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"2\" cellpadding=\"0\"><tbody>";
print "<td width=\"10%\" $top_bund><a href=ordreliste.php?valg=tilbud accesskey=L>Luk</a></td>";
print "<td width=\"80%\" $top_bund>$title</td>";
print "<td width=\"10%\" $top_bund><br></td>";
print "</tbody></table>";
print "</td></tr>";

if($_POST) {
	$submit=if_isset($_POST['submit']);
	$gruppe=if_isset($_POST['gruppe']);

	if (basename($_FILES['uploadedfile']['name'])) {
		$filnavn="../temp/".$db."_".str_replace(" ","_",$brugernavn).".csv";
		if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $filnavn)) {
			overfoer_data($filnavn);
		}
	}
} else {
	upload();
}

print "</tbody></table>";
#####################################################################################################
function upload(){
	global $charset;

	$x=0;
	$q=db_select("select kodenr,beskrivelse from grupper where art='DG' order by kodenr",__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array ($q)){
		$gruppe[$x]=$r['kodenr'];
		$gruppebeskr[$x]=$r['beskrivelse'];
		$x++;
	}
	print "<tr><td width=100% align=center><table border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>";
	print "<tr><td colspan=\"2\">Denne funktion importerer ordrer fra en tabulatorsapareret fil til ordrer</td></tr>";
	print "<tr><td colspan=\"2\">Filen skal have følgende format:</td></tr>";
	$txt=HtmlEntities("Kundenr<tab>Ordrenr<tab>Dato<tab>Projekt<tab>Telefon<tab>Navn<tab>Adresse1<tab>Adresse2<tab>Postnr<tab>Bynavn<tab>Email<tab>Varenummer<tab>Varenavn<tab>Antal<tab>Pris",ENT_COMPAT,$charset);
	$txt.="<br>".HtmlEntities("Hvis kundenummer ikke eksisterer i forvejen, oprettes en ny kunde i den valgte debitorgruppe.",ENT_COMPAT,$charset);
	$txt.="<br>".HtmlEntities("Hvis der ikke er angivet varenummer søges efter vare med samme navn. Hvis denne ikke findes, indsættes linjen som kommentar.",ENT_COMPAT,$charset);
	print "<tr><td colspan=\"2\">$txt<br></td></tr>";
	print "<tr><td colspan=\"2\"><hr></td></tr>";
	print "<form enctype=\"multipart/form-data\" action=\"csv2ordre.php\" method=\"POST\">";
	print "<tr><td width=\"150px\">Debitorgruppe</td><td align=\"right\"><select name=\"gruppe\" style=\"width:150px\">\n";
	for ($x=0;$x<count($gruppe);$x++)	print "<option value=\"$gruppe[$x]\">$gruppe[$x] $gruppebeskr[$x]</option>\n";
	print "</select></span></td></tr>";
	print "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"900000\">";
	print "<tr><td colspan=\"2\"><hr></td></tr>";
	print "<tr><td> V&aelig;lg datafil:</td><td><input name=\"uploadedfile\" type=\"file\"></td></tr>";
	print "<tr><td colspan=\"2\"><hr></td></tr>";
	print "<tr><td colspan=\"2\" align=center><input type=\"submit\" value=\"Hent\"value=\"Godkend\" onclick=\"javascript:return confirm('Importer ordrer?')\"></td></tr>";
	print "</form>";
	print "</tbody></table>";
	print "</td></tr>";
}


function overfoer_data($filnavn){
	global $charset;
	global $gruppe;


	$betalingsbet='Netto';
	$betalingsdage=8;

	$x=0;
	$oid=array();
	$q=db_select("select kontonr from adresser where art = 'D'",__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array ($q)){
		$kontonumre[$x]=$r['kontonr'];
		$x++;
	}
	$imp_antal=0;

	$fp=fopen("$filnavn","r");
	if ($fp) {
		$pre_kontonr=0;
		transaktion('begin');
		$fp=fopen("$filnavn","r");
		if ($fp) {
			$x=0;
			$imp_antal=0;
			while (!feof($fp)) {
				$skriv_linje=0;
				if ($linje=fgets($fp)) {
				$skriv_linje=1;
				if ($charset=='UTF-8') $linje=utf8_encode($linje);
				if ($x) $pre_kontonr=$kontonr;
				if (strpos($linje,chr(9))) list($kontonr,$ordrenr,$dato,$projekt,$telefon,$firmanavn,$addr1,$addr2,$postnr,$bynavn,$email,$varenr,$varenavn,$antal,$pris)=explode(chr(9),$linje);
				elseif (strpos($linje,';')) list($kontonr,$ordrenr,$dato,$projekt,$telefon,$firmanavn,$addr1,$addr2,$postnr,$bynavn,$email,$varenr,$varenavn,$antal,$pris)=explode(';',$linje);
				if (!is_numeric($kontonr)) $skriv_linje=0;
				if ($skriv_linje==1) {
					if (!in_array($kontonr,$kontonumre)) {
						db_modify("insert into adresser(kontonr,firmanavn,addr1,addr2,postnr,bynavn,email,tlf,gruppe,art,betalingsbet,betalingsdage) values ('$kontonr','".db_escape_string($firmanavn)."','".db_escape_string($addr1)."','".db_escape_string($addr2)."','".db_escape_string($postnr)."','".db_escape_string($bynavn)."','".db_escape_string($email)."','".db_escape_string($telefon)."','$gruppe','D','$betalingsbet','$betalingsdage')",__FILE__ . " linje " . __LINE__);
						$kontonumre[count($kontonumre)]=$kontonr;
					}
					if ($pre_kontonr!=$kontonr) {
						$qtxt="select * from adresser where art='D' and kontonr = '$kontonr'";
						$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
						$konto_id=$r['id'];
						if (!$firmanavn) $firmanavn=$r['firmanavn'];
						if (!$addr1) $addr1=$r['addr1'];
						if (!$addr2) $addr2=$r['addr2'];
						if (!$postnr) $postnr=trim($r['postnr']);
						if (!$bynavn) $bynavn = $r['bynavn'];
						if ($postnr && !$bynavn) $bynavn=bynavn($postnr);
						$land=$r['land'];
						$betalingsdage=$r['betalingsdage'];
						$betalingsbet=$r['betalingsbet'];
						$cvrnr=$r['cvrnr'];
						$ean=$r['ean'];
						$institution=$r['institution'];
						if (!$email) $email=$r['email'];
						$mail_fakt=$r['mailfakt'];
						($mail_fakt)?$udskriv_til='email':$udskriv_til='PDF';
						if ($r['pbs_nr']>0) {
							$pbs_nr=$r['pbs_nr'];
							$pbs='bs';
							$udskriv_til='PBS';
						}
						$kontakt=$r['kontakt'];
						$gruppe=$r['gruppe'];
						$kontoansvarlig=$r['kontoansvarlig'];

						$lev_firmanavn=$r['lev_firmanavn'];
						$lev_addr1=$r['lev_addr1'];
						$lev_addr2=$r['lev_addr2'];
						$lev_postnr=trim($r['lev_postnr']);
						$lev_bynavn = $r['lev_bynavn'];
						if ($lev_postnr && !$lev_bynavn) $lev_bynavn=bynavn($lev_postnr);
						$lev_land=$r['lev_land'];
						$lev_kontakt=$r['lev_kontakt'];

						$qtxt="select max(ordrenr) as ordrenr from ordrer where art='DO'";
						$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
						$ordrenr=$r['ordrenr']+1;

						$projektnr=0;
						if ($projekt) {
							$qtxt="select kodenr from grupper where art='PRJ' and beskrivelse = '$projekt'";
							$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
							$projektnr=$r['kodenr'];
						}
						$qtxt="select box1 from grupper where art='DG' and kodenr = '$gruppe'";
						$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
						$momsgruppe=str_replace('S','',$r['box1']);
						$qtxt="select box2 from grupper where art='SM' and kodenr = '$momsgruppe'";
						$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
						$momssats=$r['box2']*1;

						$qtxt = "insert into ordrer ";
						$qtxt.= "(ordrenr,konto_id,kontonr,firmanavn,addr1,addr2,postnr,bynavn,email,mail_fakt,udskriv_til,art,projekt,momssats,";
						$qtxt.= "betalingsbet,betalingsdage,status,ordredate,levdate,ean)";
						$qtxt.= " values ";
						$qtxt.= "('$ordrenr','$konto_id','$kontonr','".db_escape_string($firmanavn)."','".db_escape_string($addr1)."',";
						$qtxt.= "'".db_escape_string($addr2)."','".db_escape_string($postnr)."','".db_escape_string($bynavn)."',";
						$qtxt.= "'".db_escape_string($email)."','$mail_fakt','$udskriv_til','DO','$projektnr','$momssats','$betalingsbet','$betalingsdage',";
						$qtxt.= "'1','".usdate($dato)."','".usdate($dato)."','".db_escape_string($ean)."')";
						db_modify($qtxt,__FILE__ . " linje " . __LINE__);
						$r=db_fetch_array($q=db_select("select max(id) as id from ordrer where kontonr='$kontonr'",__FILE__ . " linje " . __LINE__));
						$ordre_id=$r['id'];
						if (!in_array($ordre_id,$oid)) $oid[count($oid)]=$ordre_id;
						$posnr=0;
						$imp_antal++;
					}
					$posnr++;
					if ($varenr) $qtxt="select id,varenr,salgspris,beskrivelse from varer where varenr = '$varenr'";
					else $qtxt="select id,varenr,salgspris,beskrivelse from varer where beskrivelse = '$varenavn'";
					$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
					if ($r['id']) {
						opret_ordrelinje($ordre_id,$r['id'],$r['varenr'],usdecimal($antal),$varenavn,usdecimal($pris),0,100,'DO','',$posnr,'0','on','','','0','','','','','');
						} else {
							if (!$varenavn) $varenavn="ukendt, $antal stk á $pris";
							db_modify("insert into ordrelinjer(ordre_id,posnr,beskrivelse) values ('$ordre_id','$posnr','".db_escape_string($varenavn)."')",__FILE__ . " linje " . __LINE__);
						}
					}
					$x++;
				}
			}
		}
		fclose($fp);
		for ($x=0;$x<count($oid);$x++) {
			$qtxt="select sum(antal*(pris-(pris*rabat/100))) as ordresum from ordrelinjer where ordre_id='$oid[$x]'";
			$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
			if ($r['ordresum']) db_modify ("update ordrer set sum='$r[ordresum]' where id=$oid[$x]",__FILE__ . " linje " . __LINE__);
		}
		transaktion('commit');
	}
	print "</tbody></table>";
	print "</td></tr>";
	print "<BODY onload=\"javascript:alert('$imp_antal ordrer importeret')\">";
	print "<meta http-equiv=\"refresh\" content=\"0;URL=../debitor/ordreliste.php?valg=tilbud\">";
	exit;
} # endfunc overfoer_data

function nummertjek ($nummer){
	$nummer=trim($nummer);
	$retur=1;
	$nummerliste=array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0", ",", ".", "-");
	for ($x=0; $x<strlen($nummer); $x++) {
		if (!in_array($nummer{$x}, $nummerliste)) $retur=0;
	}
	if ($retur) {
		for ($x=0; $x<strlen($nummer); $x++) {
			if ($nummer{$x}==',') $komma++;
			elseif ($nummer{$x}=='.') $punktum++;
		}
		if ((!$komma)&&(!$punktum)) $retur='US';
		elseif (($komma==1)&&(substr($nummer,-3,1)==',')) $retur='DK';
		elseif (($punktum==1)&&(substr($nummer,-3,1)=='.')) $retur='US';
		elseif (($komma==1)&&(!$punktum)) $retur='DK';
		elseif (($punktum==1)&&(!$komma)) $retur='US';
	}
	return $retur=chr(32);
}
