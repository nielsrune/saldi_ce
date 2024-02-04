<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- finans/pbsm602import.php --- patch 4.0.8 --- 2023.09.08 ---
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
// Copyright (c) 2003 - 2023 Saldi.dk ApS
// ----------------------------------------------------------------------
//
// 20200102 PHR Instead of finding the latest invoice it now finds the oldest unpaid invoice. 20200102
// 20230711 PHR Better recognition of debitor account for BS042 / '0297
// 20230908 PHR Expanded debitor recognition character from 13 to 19 in above
// 20231003 PHR Reversed above.
//
@session_start();
$s_id=session_id();
$css="../css/standard.css";

$title="Import til kassekladde";
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/settings.php");
include("../includes/std_func.php");

print "<div align=\"center\">";

if(($_GET)||($_POST)) {

	if ($_GET) {
		$kladde_id=$_GET['kladde_id'];
		$bilag=$_GET['bilagsnr'];
	}
	else {
		$submit=$_POST['submit'];
		$kladde_id=$_POST['kladde_id'];
		$filnavn=$_POST['filnavn'];
		$modkonto=$_POST['modkonto'];
#		$feltnavn=$_POST['feltnavn'];
#		$feltantal=$_POST['feltantal'];
#		$kontonr=$_POST['kontonr'];
		$bilag=$_POST['bilag']*1; #*1 tilfojet 06.07.12
#		$datoformat=$_POST['datoformat'];
	}
	print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>";
	print "<tr><td height = \"25\" align=\"center\" valign=\"top\">";
	print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"2\" cellpadding=\"0\"><tbody>";
	print "<td width=\"10%\" $top_bund><font face=\"Helvetica, Arial, sans-serif\" color=\"#000066\"><a href=kassekladde.php?kladde_id=$kladde_id accesskey=L>Luk</a></td>";
	print "<td width=\"80%\" $top_bund><font face=\"Helvetica, Arial, sans-serif\" color=\"#000066\">Importer til kassekladde (Kassekladde $kladde_id)</td>";
	print "<td width=\"10%\" $top_bund ><font face=\"Helvetica, Arial, sans-serif\" color=\"#000066\"></td>";
	print "</tbody></table>";
	print "</td></tr>";

	if (basename($_FILES['uploadedfile']['name'])) {
		$filnavn="../temp/".$db."_".str_replace(" ","_",$brugernavn).".csv";
		if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $filnavn)) {
			vis_data($kladde_id, $filnavn, $bilag, $modkonto);
	} else echo "Der er sket en fejl under hentningen, pr&oslash;v venligst igen";
}	elseif ($submit=='Flyt' && $kladde_id && $filnavn && $modkonto) flyt_data($kladde_id, $filnavn, $bilag, $modkonto);
elseif ($submit && $kladde_id && $filnavn) vis_data($kladde_id, $filnavn, $bilag, $modkonto);
else print "<meta http-equiv=\"refresh\" content=\"0;URL=importer.php?kladde_id=$kladde_id&bilag=$bilag\">";
} else print "<meta http-equiv=\"refresh\" content=\"0;URL=kassekladde.php?kladde_id=$kladde_id&bilag=$bilag\">";

print "</tbody></table>";
################################################################################################################
function vis_data($kladde_id, $filnavn, $bilag, $modkonto){
	global $bgcolor,$bgcolor5;
	global $db;
	global $charset;
	global $bruger_id;

	if ($modkonto) {
		db_modify("update grupper set box14='$modkonto' where ART='KASKL' and kode='2' and kodenr='$bruger_id'",__FILE__ . " linje " . __LINE__);
	} elseif ($r=db_fetch_array(db_select("select * from grupper where ART = 'KASKL' and kode='2' and kodenr='$bruger_id'",__FILE__ . " linje " . __LINE__))) {
			$modkonto=if_isset($r['box14']);
			#box1 - 3 bruges af import.php
	} else {
		db_modify ("insert into grupper (beskrivelse,art,kode,kodenr) values ('Kassekladdeimport','KASKL','2','$bruger_id')",__FILE__ . " linje " . __LINE__);
	}

	$fp=fopen("$filnavn","r");
	if ($fp) {
		$y=0;
		$feltantal=0;
#	for ($y=1; $y<20; $y++) {
		while ($linje=fgets($fp)) {
			$linje=trim(utf8_encode($linje));
			if ($linje && substr($linje,0,5)=='BS042') {
				if (substr($linje,13,4)=='0297') {
					$y++;
					$skriv_linje[$y]=1;
					$amount[$y]=substr($linje,115,13)/100;
					$invoice[$y]=substr($linje,38,5);
					$debitor[$y] = (int)substr($linje,25,13);
					$qtxt = "select kontonr, sum, moms from ordrer where art = 'DO' and fakturanr = '$invoice[$y]'";
					if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
						($amount[$y] == $r['sum'] + $r['moms'])?$debitor[$y] = $r['kontonr']:$debitor[$y] = '';
					}
					$beskrivelse[$y]="Indbetaling via FI kort. Kunde $debitor[$y]";
					$date[$y]=usdate(substr($linje,103,6));
					$belob[$y]=dkdecimal($amount[$y]);
					if (!$amount[$y]) $skriv_linje[$y]=0;
					list($aar,$maaned,$dag)=explode("-",$date[$y]);
					if (checkdate($maaned,$dag,$aar)) $dato[$y]=dkdato($date[$y]);
					else $skriv_linje[$y]=0;
				} elseif ($linje && substr($linje,13,4)=='0236') {
					$y++;
					$skriv_linje[$y]=1;
					$debitor[$y]=substr($linje,25,15)*1;
					$beskrivelse[$y]="Indbetaling via BS. Kunde $debitor[$y]";
					$date[$y]=usdate(substr($linje,103,6));
					$aftalenr[$y]==substr($linje,40,9);
					$amount[$y]=substr($linje,115,13)/100;
					$belob[$y]=dkdecimal($amount[$y]);
					if (!$amount[$y]) $skriv_linje[$y]=0;
					list($aar,$maaned,$dag)=explode("-",$date[$y]);
					if (checkdate($maaned,$dag,$aar)) $dato[$y]=dkdato($date[$y]);
					else $skriv_linje[$y]=0;
				} elseif ($db == 'saldi_6') {
#					echo substr($linje,13,4)."<br>";
				}
			}
		}
	}  
	$linjeantal=$y;
	fclose ($fp);
	print "<tr><td width=100% align=center><table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\"><tbody>";
	print "<form enctype=\"multipart/form-data\" action=\"pbsm602import.php\" method=\"POST\">";
	#print "<tr><td colspan=6 width=100% align=center> $filnavn</td></tr>";
	print "<tr><td colspan=\"5\" align=\"center\">Modkonto<input class=\"inputbox\" style=\"text-align:right;width:60px\" type=\"text\" name=\"modkonto\" value=\"$modkonto\"> ";
	print "<input type=\"hidden\" name=\"filnavn\" value=$filnavn>";
	print "<input type=\"hidden\" name=\"kladde_id\" value=$kladde_id>";
	print "&nbsp; <input type=\"submit\" name=\"submit\" value=\"Vis\" />";
	if ($kladde_id && $filnavn && $modkonto) print "&nbsp; <input type=\"submit\" name=\"submit\" value=\"Flyt\" /></td></tr>";
	print "<tr><td colspan=\"5\"><hr></td></tr>\n";
	print "<tr><td><span title='Angiv 1. bilagsnummer'><input class=\"inputbox\" style=\"text-align:right;width:60px\" type=\"text\" name=bilag value=$bilag></span></td>";
	print "<td><b>Kundenr</b></td><td><b>Tekst</b></td><td><b>Dato</b></td><td><b>Bel&oslash;b</b></td></tr>";
	print "</form>";
	$linjebg=$bgcolor;
	$date[0]=$date[1];
	for ($x=1;$x<=$linjeantal;$x++) {
		($linjebg==$bgcolor)?$linjebg=$bgcolor5:$linjebg=$bgcolor;
		if ($skriv_linje[$x]==1) {
			if ($date[0]!=$date[$x])$bilag++;
			$txtcolor="0,0,0";
		} else {
			$txtcolor="255,0,0";
	}	
		print "<tr bgcolor=\"$linjebg\" style=\"color:rgb($txtcolor);\"><td align=\"right\" width=\"10px\">$bilag</td><td>$debitor[$x]</td><td>$beskrivelse[$x]</td><td>$dato[$x]</td><td>$belob[$x]</td></span></tr>";
	}
	print "</tbody></table>";
	print "</td></tr>";
} # function vis_data;

function flyt_data($kladde_id, $filnavn, $bilag, $modkonto){
	global $charset;

	transaktion('begin');
	$fp=fopen("$filnavn","r");
	if ($fp) {
		$y=0;
		$feltantal=0;
#	for ($y=1; $y<20; $y++) {
		while ($linje=fgets($fp)) {
			$linje=trim(utf8_encode($linje));
			if ($linje && substr($linje,0,5)=='BS042') {
				if (substr($linje,13,4)=='0297') {
					$y++;
					$skriv_linje[$y]=1;
					$amount[$y]=substr($linje,115,13)/100;
					$invoice[$y]=substr($linje,38,5);
					$debitor[$y] = (int)substr($linje,25,13);
					$qtxt = "select kontonr, sum, moms from ordrer where art = 'DO' and fakturanr = '$invoice[$y]'";
					if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
						($amount[$y] == $r['sum'] + $r['moms'])?$debitor[$y] = $r['kontonr']:$debitor[$y] = '';
					}
					$beskrivelse[$y]="Indbetaling via FI kort. Kunde $debitor[$y]";
					$date[$y]=usdate(substr($linje,103,6));
					if (!$amount[$y]) $skriv_linje[$y]=0;
					list($aar,$maaned,$dag)=explode("-",$date[$y]);
					if (checkdate($maaned,$dag,$aar)) $dato[$y]=dkdato($date[$y]);
					else $skriv_linje[$y]=0;
				} elseif ($linje && substr($linje,13,4)=='0236') {
					$y++;
					$skriv_linje[$y]=1;
					$debitor[$y]=substr($linje,25,15)*1;
					$beskrivelse[$y]="Indbetaling via BS. Kunde $debitor[$y]";
					$date[$y]=usdate(substr($linje,103,6));
					$aftalenr[$y]==substr($linje,40,9);
					$amount[$y]=substr($linje,115,13)/100;
					if (!$amount[$y]) $skriv_linje[$y]=0;
					list($aar,$maaned,$dag)=explode("-",$date[$y]);
					if (checkdate($maaned,$dag,$aar)) $dato[$y]=dkdato($date[$y]);
					else $skriv_linje[$y]=0;
				}
			}
		}
	}
	$linjeantal=$y;
	fclose ($fp);
	unlink($filnavn); # sletter filen.
	$sum=0;
	$date[0]=$date[1];
	if ($linjeantal>1) {
		for ($x=1;$x<=$linjeantal;$x++) {
			if ($skriv_linje[$x]==1) {
#			$bilag++;
				$qtxt = "select faktnr from openpost where amount = '$amount[$x]' and konto_nr = '$debitor[$x]' and udlignet='0' ";
				$qtxt.= "order by transdate limit 1"; #20200102
				$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
				$faktura=$r['faktnr'];
				db_modify("insert into kassekladde (bilag,transdate,beskrivelse,d_type,debet,k_type,kredit,amount,kladde_id,faktura) values ('$bilag','$date[$x]','$beskrivelse[$x]','F','0','D','$debitor[$x]','$amount[$x]','$kladde_id','$faktura')",__FILE__ . " linje " . __LINE__);
				if ($date[0]!=$date[$x]) {
					db_modify("insert into kassekladde (bilag,transdate,beskrivelse,d_type,debet,k_type,kredit,amount,kladde_id,faktura) values ('$bilag','$date[0]','PBS Samlet betaling','F','$modkonto','F','0','$sum','$kladde_id','')",__FILE__ . " linje " . __LINE__);
					$bilag++;
					$date[0]!=$date[$x];
					$sum=0;;
				}
				$sum+=$amount[$x];
			}
		}	
		db_modify("insert into kassekladde (bilag,transdate,beskrivelse,d_type,debet,k_type,kredit,amount,kladde_id,faktura) values ('$bilag','$date[0]','PBS Samlet betaling','F','$modkonto','F','0','$sum','$kladde_id','')",__FILE__ . " linje " . __LINE__);
	} elseif ($skriv_linje[$linjeantal]==1) { 
		$r=db_fetch_array(db_select("select faktnr from openpost where amount = '$amount[$linjeantal]' and konto_nr = '$debitor[$linjeantal]' order by transdate desc",__FILE__ . " linje " . __LINE__));
		$faktura=$r['faktnr'];
		db_modify("insert into kassekladde (bilag,transdate,beskrivelse,d_type,debet,k_type,kredit,amount,kladde_id,faktura) values ('$bilag','$date[$linjeantal]','$beskrivelse[$linjeantal]','F','$modkonto','D','$debitor[$linjeantal]','$amount[$linjeantal]','$kladde_id','$faktura')",__FILE__ . " linje " . __LINE__);
	}
	transaktion('commit');
	print "<meta http-equiv=\"refresh\" content=\"0;URL=kassekladde.php?kladde_id=$kladde_id\">";
}
