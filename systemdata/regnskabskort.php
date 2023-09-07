<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- systemdata/regnskabskort.php --- lap 4.0.8 -- 2023-06-09 --
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
// ----------------------------------------------------------------------------
// 2013.02.10 Break ændret til break 1
// 2015-01-02 Tilrettet til dynamisk lagerværdi. Søg find_lagervaerdi
// 20150327 CA  Topmenudesign tilføjet søg 20150327
// 20170516 Div oprydning samt tilføjelse af laas_lager Søg laas
// 20190221 MSC - Rettet topmenu design og isset fejl
// 20190225 MSC - Rettet topmenu design
// 20210101 PHR - Various changes and cleanup. Accounting now alloewd by default.
// 20210709 LOE - Translated these texts
// 20220103 PHR - Made some cleanup, set it to save twice and set year active when new year created.
// 20220614 MSC - Implementing new design
// 20230609 PHR - php8

@session_start();
$s_id=session_id();

$aut_lager=$aaben=$beskrivelse=$laast=$setFinancialYear=NULL;
	
$modulnr=2;
$title="Regnskabskort";
$css="../css/standard.css";

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/genberegn.php");

print "<script language=\"javascript\" type=\"text/javascript\" src=\"../javascript/confirmclose.js\"></script>";
if ($menu=='T') {
	#	print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
		include_once '../includes/top_header.php';
		include_once '../includes/top_menu.php';
		print "<div id=\"header\">";
		print "<div class=\"headerbtnLft headLink\"><a href=regnskabsaar.php accesskey=L title='Klik her for at komme tilbage'><i class='fa fa-close fa-lg'></i> &nbsp;".findtekst(30,$sprog_id)."</a></div>";
		print "<div class=\"headerTxt\">$title</div>";
		print "<div class=\"headerbtnRght headLink\">&nbsp;&nbsp;&nbsp;</div>";
		print "</div>";
		print "<div class='content-noside'>";
		print "<div id=\"leftmenuholder\">";
		include_once 'left_menu.php';
		print "</div><!-- end of leftmenuholder -->\n";
		print "<div class=\"maincontentLargeHolder\">\n";
		print "<div class='divSys'>";
		print "<table class='dataTableSys' cellpadding=0 cellspacing=0 border=1 width='100%' bordercolor='$bgcolor5'><tbody>\n"; ############################	##table 3b start
	} else {
		include("top.php");
		print "<table cellpadding=\"1\" cellspacing=\"1\" border=\"1\"><tbody>";
print "<table width=100% height=100% border=0 cellspacing=0 cellpadding=0><tbody>"; ####################table 1a start.
		print "<tr><td align='center' valign=top>";
		print "<a href=\"javascript:confirmClose('regnskabsaar.php','". findtekst(154,$sprog_id) ."')\">";
		print "<button style = 'width:80px;'>Luk</button></a>";
		print "<table width=100% align='center' border=0 cellspacing=4 cellpadding=0><tbody>\n"; ##############table 2b start
print "<tr>\n";
print "</tbody></table>\n"; #####################################################table 2b slut.
print "</td></tr>\n";
print "<tr>\n";
print "<td align = center valign = center>";
	print "<table class='dataTable2' cellpadding=0 cellspacing=0 border=1><tbody>\n"; ############################	##table 3b start
	}

$id=if_isset($_GET['id']);

if ($_POST) {
	$id = if_isset($_POST['id']);
	$beskrivelse = if_isset($_POST['beskrivelse']);
	$kodenr = if_isset($_POST['kodenr']);
	$kode=if_isset($_POST['kode']);
	$startmd = if_isset($_POST['startmd']);
	$startaar = if_isset($_POST['startaar']);
	$slutmd = if_isset($_POST['slutmd']);
	$slutaar = if_isset($_POST['slutaar']);
	$aaben=trim($_POST['aaben']);
	$fakt=if_isset($_POST['fakt'],0);
	$modt=if_isset($_POST['modt'],0);
	$no_faktbill=trim(if_isset($_POST['no_faktbill']));
	$faktbill=trim(if_isset($_POST['faktbill']));
	$modtbill=trim(if_isset($_POST['modtbill']));
	$kontoantal = if_isset($_POST['kontoantal']);
	$kontonr = if_isset($_POST['kontonr']);
	$debet=if_isset($_POST['debet']);
	$kredit=if_isset($_POST['kredit']);
	$saldo = if_isset($_POST['saldo']);
	$overfor_til = if_isset($_POST['overfor_til']);
#		$primotal = if_isset($_POST['primotal']);
	$aar=date("Y");
	$topaar=$aar+10;
	$bundaar=$aar-20;
	$fejl=0;
	$startmd=$startmd*1;
	$startaar=$startaar*1;
	$slutmd=$slutmd*1;
	$slutaar=$slutaar*1;
	
	if (!$beskrivelse){
		$beskrivelse = $startaar;
		if ($startaar != $slutaar) $beskrivelse.= "/".$slutaar;
	}
	if (($startmd<1)||($startmd>12)){
		alert('Startm&aring;ned skal v&aelig;re mellem 1 og 12!');
		$startmd="";
	}
	elseif ($startmd<10) $startmd="0".$startmd;
	if (($slutmd<1)||($slutmd>12)){
		alert('Slutm&aring;ned skal v&aelig;re mellem 1 og 12!');
		$slutmd="";
	}
	elseif ($slutmd<10) $slutmd="0".$slutmd;
	if (($startaar<$bundaar)||($startaar>$topaar)){
		alert("Startår skal være mellem $bundaar og $topaar!");
		$startaar="";
	}
	if (($slutaar<$bundaar)||($slutaar>$topaar)){
		alert('Slut&aring;r skal v&aelig;re mellem $bundaar og $topaar!');
		$slutaar="";
	}
	$startdato=$startaar.$startmd;
	$slutdato=$slutaar.$slutmd;
	if ($slutdato<=$startdato){
		alert('Regnskabs&aring;r skal slutte senere end det starter');
		$aaben="";
	}
	$qtxt = "select id from grupper where kodenr = '$kodenr' and art = 'RA'";
	if ((($id!=0)||(!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)))) &&
	(($startmd)&&($slutmd)&&($startdato)&&($slutdato)&&($startaar)&&($slutaar)&&($beskrivelse))) {
		transaktion("begin");
		($id == 0)?$nn=2:$nn=1;
		for($n=0;$n<$nn;$n++) { // 1. time it must be rerun to catch and update last year's result. 
		if ($id==0){
			$qtxt="insert into grupper (beskrivelse,kodenr,kode,art,box1,box2,box3,box4,box5)";
			$qtxt.=" values ";
			$qtxt.="('".db_escape_string($beskrivelse)."','$kodenr','$kode','RA','$startmd','$startaar','$slutmd','$slutaar','$aaben')";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$query = db_select("select id from grupper where kodenr = '$kodenr' and art = 'RA'",__FILE__ . " linje " . __LINE__);
			$row = db_fetch_array($query);
			$id = $row['id'];
			$setFinancialYear=1; 
		}
		if ($kodenr==1) {
			for ($x=1; $x<=$kontoantal; $x++) {
				$sum=0;
				if ($debet[$x]) $sum+=usdecimal($debet[$x]);
				if ($kredit[$x]) $sum-=usdecimal($kredit[$x]);
				db_modify ("update kontoplan set primo='$sum' where kontonr='$kontonr[$x]' and regnskabsaar=1",__FILE__ . " linje " . __LINE__);
			}
			if (db_fetch_array(db_select("select * from grupper where art = 'RB'",__FILE__ . " linje " . __LINE__))) {
				$qtxt = "update grupper set box1 = '$fakt', box2 = '$modt', box3 = '$faktbill', box4 = '$modtbill', box5 = '$no_faktbill' ";
				$qtxt.= "where art = 'RB'";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			} else {
				$qtxt = "insert into grupper (beskrivelse,kodenr,kode,art,box1,box2,box3,box4,box5) values "; 
				$qtxt.= "('Regnskabsbilag','1','1','RB','$fakt','$modt','$faktbill','$modtbill','$no_faktbill')";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
		} 
		if (($id>0)&&($kodenr>0)) {
			$qtxt = "update grupper set beskrivelse = '".db_escape_string($beskrivelse)."', kodenr = '$kodenr', kode = '$kode', ";
			$qtxt.= "box1 = '$startmd', box2 = '$startaar', box3 = '$slutmd', box4 = '$slutaar', box5 = '$aaben' where id = '$id'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			if ($kodenr==1){
				for ($x=1; $x<=$kontoantal; $x++) {
					if ($saldo[$x] && $overfor_til[$x]) {
						$qtxt = "update kontoplan set primo=primo+$saldo[$x],overfor_til=$overfor_til[$x] where ";
						$qtxt = "kontonr='$kontonr[$x]' and regnskabsaar=$kodenr";
						db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					}
				}
			} else {
				$query = db_select("select id from kontoplan where regnskabsaar=$kodenr",__FILE__ . " linje " . __LINE__);
				if($row = db_fetch_array($query)) {
					$qtxt = 	
					db_modify ("update kontoplan set primo='0' where  regnskabsaar=$kodenr",__FILE__ . " linje " . __LINE__);
					for ($x=0; $x<=$kontoantal; $x++) {
						$overfor_til[$x]=(int)$overfor_til[$x];
						$kontonr[$x]=$kontonr[$x]*1;
						if ($overfor_til[$x]) {
							$saldo[$x]*=1; #phr 20110605
							$qtxt = "update kontoplan set overfor_til='$overfor_til[$x]' ";
							$qtxt.= "where kontonr='$kontonr[$x]' and (regnskabsaar=$kodenr-1 or regnskabsaar='$kodenr')";
							db_modify ($qtxt,__FILE__ . " linje " . __LINE__);
							$qtxt = "update kontoplan set primo=primo+'$saldo[$x]' where kontonr='$overfor_til[$x]' and regnskabsaar=$kodenr";
							db_modify ($qtxt,__FILE__ . " linje " . __LINE__);
					}
				}
				} else {
					$query = db_select("select * from kontoplan where regnskabsaar=$kodenr-1 order by kontonr",__FILE__ . " linje " . __LINE__);
					$y=0;
					while ($row = db_fetch_array($query)) {
						if ($row['kontotype']=="S") { 
						$belob=$row['saldo'];
						} else $belob='0';
						if (!$belob) $belob='0';
						if (!$row['fra_kto']) $row['fra_kto']='0';
						if (!$row['til_kto']) $row['til_kto']='0';
						if (!$row['overfor_til']) $row['overfor_til']='0';
						$qtxt = "insert into kontoplan(kontonr,beskrivelse,kontotype,moms,fra_kto,til_kto,lukket,primo,regnskabsaar,overfor_til,genvej)";
						$qtxt.= " values ";
						$qtxt.= "('$row[kontonr]','".db_escape_string($row['beskrivelse'])."','$row[kontotype]','$row[moms]',";
						$qtxt.= "'$row[fra_kto]','$row[til_kto]','$row[lukket]','$belob','$kodenr','$row[overfor_til]','$row[genvej]')";
						db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					}
				}	
			}
		}
		if (isset($_POST['laas_lager']) && $_POST['laas_lager']) {
			$fra=$startaar."-".$startmd."01";
			$til=usdate("31-".$slutmd."-".$slutaar);
			print "<meta http-equiv=\"refresh\" content=\"1;URL=laas_lager.php?fra=$fra&til=$til\">"; 

		}
		}
		transaktion("commit");
	}
}
if ($setFinancialYear) {
	include("../includes/connect.php");
	db_modify("update online set regnskabsaar = '$kodenr' where session_id = '$s_id'",__FILE__ . " linje " . __LINE__);
	if ($revisor) {
		$qtxt = "update revisor set regnskabsaar = '$kodenr' where brugernavn = '$brugernavn' and db_id='$db_id'";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	}
	include("../includes/online.php");
	if (!$revisor) db_modify("update brugere set regnskabsaar = '$kodenr' where id = '$bruger_id'",__FILE__ . " linje " . __LINE__);
}

if ($id > 0) {
	$qtxt = "select kodenr from grupper where id = '$id' and art = 'RA'";
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$preNo = $r['kodenr'] -1;
	if ($preNo) {
		$qtxt = "select box10 from grupper where art = 'RA' and kodenr = '$preNo'";
#cho "$qtxt<br>";
		$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		($r['box10'] == 'on')?$preDeleted = 1:$preDeleted = 0;
	}

	$query = db_select("select * from grupper where id = '$id' and art = 'RA'",__FILE__ . " linje " . __LINE__);
	if ($row = db_fetch_array($query)) {
		genberegn($row['kodenr']);
#cho __line__." preDeleted $preDeleted<br>";
		if ($row['kodenr']==1 || $preDeleted) aar_1($row['id'], $row['kodenr'], $row['beskrivelse'], $row['box1'], $row['box2'], $row['box3'], $row['box4'], $row['box5'],'');
		else {
			aar_x($row['id'], $row['kodenr'], $row['beskrivelse'], $row['box1'], $row['box2'], $row['box3'], $row['box4'], $row['box5'],$row['box9']);
		}
	}
} else {
#	Print "<BODY onload=\"javascript:alert('$tekst')\">";
#	print "<BODY onload=\"javascript:velkommen=window.open('velkommen.html','velkommen','".$jsvars."';) velkommen.focus();\";"
	print "<BODY onload=\"javascript:docChange = true;\">";
	$x=0;
	$q = db_select("select * from grupper where art = 'RA' order by kodenr desc",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		if ($x <= $r['kodenr']) $x = $r['kodenr'];
	}
	$query = db_select("select * from grupper where art = 'RA' and kodenr='$x'",__FILE__ . " linje " . __LINE__);
	if ($row = db_fetch_array($query)) {
		$x++;
		if ($row['box3']==12) {
			$startmd=1;
			$startaar=$row['box4']+1;
		} else {
			$startmd=$row['box3']+1;
			$startaar=$row['box4'];
		}
		$slutmd=$row['box3'];
		$slutaar=$row['box4']+1;
		$deleted = $row['box10'];
	} else {
		$beskrivelse=date(Y);
		$startaar=date(Y);
		$startmd='01';
		$slutaar=date(Y);
		$slutmd='12';
		$aaben='on';
	}

	if ($x==0) aar_1($id, 1, $beskrivelse, $startmd, $startaar, $slutmd, $slutaar, $aaben,$aut_lager);
	else aar_x($id, $x, $beskrivelse, $startmd, $startaar, $slutmd, $slutaar, $aaben,$aut_lager);
}
	
function aar_1($id, $kodenr, $beskrivelse, $startmd, $startaar, $slutmd, $slutaar, $aaben,$aut_lager) {
	global $sprog_id, $bgcolor5;
	global $menu;
	$row = db_fetch_array(db_select("select MAX(kodenr) as kodenr from grupper where art = 'RA'",__FILE__ . " linje " . __LINE__));
	if ($row['kodenr'] > $kodenr) $laast=1;  
	if ($row = db_fetch_array(db_select("select * from grupper where art = 'RB' order by kodenr",__FILE__ . " linje " . __LINE__))) {
		$fakt=$row['box1']*1;
		$modt=$row['box2']*1;
		$faktbill=trim($row['box3']);
		$modtbill=trim($row['box4']);
		$no_faktbill=trim($row['box5']);
	
	} else {
		$fakt='1';
		$modt='1';
		$no_faktbill='on';
		$modtbill='on';
	}
	if (!$fakt) $fakt='1';
	if (!$modt) $modt='1';
	
	print "<form name='aar_1' action='regnskabskort.php' method='post'>";
	if ($id) print "<tr><td colspan=4 align = center><big><b>".findtekst(1226,$sprog_id)." $beskrivelse</big></td></tr>\n";
	else {
		print "<tr><td colspan=4 align = center><big><b>Velkommen til som SALDI bruger</b></big><br />
			Du skal f&oslash;rst oprette dit 1. regnskabs&aring;r, f&oslash;r du kan bruge systemet.<br /><br /> 
			Systemet er allerede sat op, s&aring; det passer til de fleste virksomheder. Hvis dit 1. regnskabs&aring;r<br />
			passer med perioden 1. januar $startaar til 31. december $slutaar, skal du blot trykke p&aring; knappen [Gem],<br />
			og dit regnskab er klar til brug.<br /><br />
			Hvis der er noget, du er i tvivl om, er du velkommen til at kontakte os p&aring; telefon 4690 2208<br /> 
			God forn&oslash;jelse.<br /><br />
			</td></tr>\n";
		print "<tr><td colspan=4 align = center><big><b>".findtekst(1227,$sprog_id)." $beskrivelse</big></td></tr>\n";
	}
	print "<tr><td colspan=4 align='center'><table width=100% border=0><tbody><tr>"; #########################table 4c start
	print "<tr><td></td><td align='center'>Start</td><td align='center'>Start</td><td align='center'>".findtekst(1216,$sprog_id)."</td><td align='center'>".findtekst(1216,$sprog_id)."</td><td align='center'>".findtekst(1086,$sprog_id)."</td></tr>\n";
	print "<tr><td align='center'>".findtekst(914,$sprog_id)."</td><td align='center'>".findtekst(1217,$sprog_id)."</td><td align='center'>".findtekst(1218,$sprog_id)."</td><td align='center'>".findtekst(1217,$sprog_id)."</td><td align='center'>".findtekst(1218,$sprog_id)."</td><td align='center'>".findtekst(1219,$sprog_id)."</tr>\n";
	print "<input type=hidden name=kodenr value=1><input type=hidden name='id' value='$id'>\n";
	print "<tr><td align='center'><input type=text size='30' name='beskrivelse' value=\"$beskrivelse\" onchange=\"javascript:docChange = true;\"></td>\n";
	if ($laast) $type="readonly=readonly";
	else $type="type=text";
	print "<td align='center'><input $type style='text-align:right' size='2' name=startmd value='$startmd' onchange=\"javascript:docChange = true;\"></td>\n";
	print "<td align='center'><input $type style='text-align:right' size='4' name=startaar value='$startaar' onchange=\"javascript:docChange = true;\"></td>\n";
	print "<td align='center'><input $type style='text-align:right' size='2' name=slutmd value='$slutmd' onchange=\"javascript:docChange = true;\"></td>\n";
	print "<td align='center'><input $type style='text-align:right' size='4' name=slutaar value='$slutaar' onchange=\"javascript:docChange = true;\"></td>\n";
	if (strstr($aaben,'on')) {
		print "<td align='center'><input type='checkbox' name='aaben' checked onchange=\"javascript:docChange = true;\"></td>\n";
	} else {
		print "<td align='center'><input type='checkbox' name='aaben' onchange=\"javascript:docChange = true;\"></td>\n";
	}

	if ($menu=='T') {
		$styleborder="bordercolor='$bgcolor5'";
	} else {
		print "";
	}
	print "</tr>\n</tbody></table></td></tr>\n"; ###################################################table 4c slut
	print "<tr><td colspan=4 width=100% align='center'><table heigth=100% border=0><tbody>"; ###########################table 5c start
	print "<td align='center' valign=\"top\"><table heigth=100% border=1 $styleborder><tbody>\n";  #################################table 6d start	print "<tr><td align='center'>1. faktnr</td><td align='center'>1. modt. nr.</td><tr>";
	print "<tr>\n <td>".findtekst(1220,$sprog_id)."</td>\n";
	print " <td align='center'><input type=text style='text-align:right' size='4' name=fakt value=$fakt onchange=\"javascript:docChange = true;\"></td>\n</tr>\n";  
	print "<tr>\n <td>".findtekst(1221,$sprog_id)."</td>\n";
	print " <td align='center'><input type=text style='text-align:right' size='4' name=modt value=$modt onchange=\"javascript:docChange = true;\"></td>\n</tr>\n";  
	print "</tbody></table></td>\n"; ##########################################################table 6d slut
	print "<td><table border=1 $styleborder><tbody>"; ##############################################table 7d start
	if ($no_faktbill) $no_faktbill="checked"; 
	if ((!$no_faktbill)&&($faktbill)) $faktbill="checked"; 
	if ($modtbill) $modtbill="checked";
	print "<tr><td align='center'>".findtekst(1222,$sprog_id)."</td><td align='center'><input type='checkbox' name=no_faktbill $no_faktbill onchange=\"javascript:docChange = true;\"></td></tr>\n"; #20210709
	print "<tr><td align='center'>".findtekst(1223,$sprog_id)."</td><td align='center'><input type='checkbox' name=faktbill $faktbill onchange=\"javascript:docChange = true;\"></td></tr>\n";
	print "<tr><td align='center'>".findtekst(1224,$sprog_id)."</td><td align='center'><input type='checkbox' name=modtbill $modtbill onchange=\"javascript:docChange = true;\"></td></tr>\n";
	print "</tbody></table></td>\n"; ##########################################################table 7d slut
	print "<td valign=\"top\"><table border=0><tbody>\n"; ##############################################table 8d start
	print "<tr><td><input class='button green medium' style='width:150px' type=submit accesskey=\"g\" value=\"".findtekst(3,$sprog_id)."\" name=\"submit\" onclick=\"javascript:docChange = false;\"></td></tr>\n";
	print "</tbody></table></td></tr>\n";#####################################################table8d slut
	print "</td></tbody></table></td></tr>\n";#####################################################table5c slut
	print "<tr><td colspan=2 align='center'> ".findtekst(1225,$sprog_id)."</td><td align = center> ".findtekst(1000,$sprog_id)."</td><td align = center> ".findtekst(1001,$sprog_id)."</td></tr>\n";
	$y=0;
	$debetsum=0;
	$kreditsum=0;
	$qtxt = "select id, kontonr, primo, beskrivelse from kontoplan ";
	$qtxt.= "where kontotype='S' and regnskabsaar='$kodenr' order by kontonr";
#cho "$qtxt<br>";
	$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($row = db_fetch_array($q)) {
		$y++;
		print "<tr><input type=hidden name=kontonr[$y] value=$row[kontonr]>";
		$debet[$y]="0,00";
		$kredit[$y]="0,00";
		if ($row['primo']>0) {
			$debet[$y]=dkdecimal($row['primo'],2);
			$debetsum=$debetsum+$row['primo'];
		}
		elseif ($row['primo']<0) {
			$kredit[$y]=dkdecimal($row['primo']*-1,2);
			$kreditsum=$kreditsum+($row['primo']*-1);
		}
		print "<td>$row[kontonr]</td>";
		print "<td>$row[beskrivelse]</td>";
		print "<td align=right><input type=text style='text-align:right' size=10 name=debet[$y] value=$debet[$y] onchange=\"javascript:docChange = true;\"></td>";
		print "<td align=right><input type=text style='text-align:right' size=10 name=kredit[$y] value=$kredit[$y] onchange=\"javascript:docChange = true;\"></td></tr>\n";
	}
	print "<td></td><td></td><td align=right>".dkdecimal($debetsum,2)."</td><td align=right>".dkdecimal($kreditsum,2)."</td></tr>\n";
	if (abs($debetsum-$kreditsum)>0.009) {
		print "<BODY onload=\"javascript:alert('Konti er ikke i balance')\">";
	}
	
#	print "<tr><td colspan = 3> Overfr �ningsbalance</td><td align='center'><input type='checkbox' name=primotal checked></td></tr>\n";
	print "<input type=hidden name=kontoantal value=$y>";
	print "<tr><td colspan='4' align='center'>";
	print "<input class='buttom green medium' style='width:150px;' type='submit' accesskey=\"g\" value=\"".findtekst(471, $sprog_id)."\" style=\"width:100px\" ";
	print "name=\"submit\" onclick=\"javascript:docChange = false;\">";
#	print "&nbsp;&nbsp;<input class='button green medium' type=submit value=\"".findtekst(1009,$sprog_id)." ".findtekst(1218,$sprog_id)."\" name=\"delete\"  style=\"width:100px\" ";
#	print "onclick=\"javascript:docChange = false;\">";
	print "</td></tr>\n";
	print "</form></tbody></table>";

	print "</div></div>";
	if ($menu=='T') {
		include_once '../includes/topmenu/footer.php';
	} else {
		include_once '../includes/oldDesign/footer.php';
	}
	exit;
}

function aar_x($id, $kodenr, $beskrivelse, $startmd, $startaar, $slutmd, $slutaar, $aaben,$aut_lager) {
	global $overfor_til,$regnaar,$sprog_id,$menu;
	$debetsum=0;
	$kreditsum=0;
	
	$r=db_fetch_array(db_select("select max(kodenr) as max_aar from grupper where art = 'RA'",__FILE__ . " linje " . __LINE__));
	$max_aar=$r['max_aar'];
	
	$pre_regnaar=$kodenr-1;
	$query = db_select("select * from grupper where art = 'RA' and kodenr = '$pre_regnaar'",__FILE__ . " linje " . __LINE__);
	if ($row = db_fetch_array($query)) {
		$pre_startmd=$row['box1'];
		$pre_startaar=$row['box2'];
		$pre_slutmd=$row['box3'];
		$pre_slutaar=$row['box4'];
		$pre_laast_lager=$row['box9'];
		$preDeleted      = $row['box10'];
	}
	$pre_slutdato=31;
	while (!checkdate($pre_slutmd, $pre_slutdato, $pre_slutaar)) {
		$pre_slutdato=$pre_slutdato-1;
		if ($pre_slutdato<28) break 1;
	}
	$pre_regnstart = $pre_startaar. "-" . $pre_startmd . "-" . '01';
	$pre_regnslut = $pre_slutaar . "-" . $pre_slutmd . "-" . $pre_slutdato;

	if (!$beskrivelse){
		$beskrivelse = $startaar;
		if ($startaar != $slutaar) $beskrivelse.= "/".$slutaar;
	}

	print "<form id = '1' name='aar_x' action='regnskabskort.php' method='post'>";
	if ($id) print "<tr><td colspan=5 align = center><big><b>".findtekst(1206,$sprog_id)." $kodenr. ".findtekst(894,$sprog_id).": $beskrivelse</td></tr>\n";
	else {
		print "<tr><td colspan=5 align = center><big><b>". findtekst(1232,$sprog_id)." $kodenr. ".findtekst(894,$sprog_id).": $beskrivelse</td></tr>\n";
		$aaben='on';
	}
	print "<tr><td colspan=5 align='center'><table width=100% border=0><tbody><tr>"; ###########################table 8d start
	print "<tr><td></td><td align='center'>Start</td><td align='center'>Start</td><td align='center'>".findtekst(1216,$sprog_id)."</td><td align='center'>".findtekst(1216,$sprog_id)."</td><td align='center'>".findtekst(1086,$sprog_id)."</td></tr>\n";
	print "<tr><td align='center'>".findtekst(914,$sprog_id)."</td><td align='center'>".findtekst(1217,$sprog_id)."</td><td align='center'>".findtekst(1218,$sprog_id)."</td><td align='center'>".findtekst(1217,$sprog_id)."</td><td align='center'>".findtekst(1218,$sprog_id)."</td><td align='center'>".findtekst(1219,$sprog_id)."</td></tr>\n";
	print "<tr><input type=hidden name=kodenr value=$kodenr><input type=hidden name=id value='$id'	>";
	print "<td align='center'><input type=text size=30 name=beskrivelse value=\"$beskrivelse\" onchange=\"javascript:docChange = true;\"></td>";
	print "<td align='center'><input readonly=readonly style='text-align:right' size='2' name=startmd value=$startmd></td>";
	print "<td align='center'><input readonly=readonly style='text-align:right' size='4' name=startaar value=$startaar></td>";
	print "<td align='center'><input type=text style='text-align:right' size='2' name=slutmd value=$slutmd onchange=\"javascript:docChange = true;\"></td>";
	print "<td align='center'><input type=text style='text-align:right' size='4' name=slutaar value=$slutaar onchange=\"javascript:docChange = true;\"></td>";
	(strstr($aaben,'on'))?$aaben='checked':$aaben=NULL;
	if (!$id) $checked='checked';
	print "<td align='center'><input type='checkbox' name='aaben' $aaben onchange=\"javascript:docChange = true;\"></td>";
	print "</tr>\n</tbody></table></td></tr>\n"; #####################################################table 8d slut
	print "<tr><td colspan=2 align='center'> ".findtekst(1231,$sprog_id)." $kodenr. ".findtekst(894,$sprog_id).":</td><td align = center> ".findtekst(1073,$sprog_id)."</td><td align = center> ".findtekst(1228,$sprog_id)." ".findtekst(904,$sprog_id)."</td><td align = center> ".findtekst(39,$sprog_id)." ".findtekst(1229,$sprog_id)."</td></tr>\n";
	$tmp=$kodenr;
	$kontoantal=0;
	while ($kontoantal<1&&$tmp>0){ #Hvis der ikke er oprettet konti for indevaerende regsskabsaar, hentes konti fra forrige.
		$query = db_select("select primo, kontonr, beskrivelse from kontoplan where kontotype='S' and regnskabsaar='$tmp' order by kontonr",__FILE__ . " linje " . __LINE__);
		while ($row = db_fetch_array($query)) {
			$kontoantal++;
			$primo[$kontoantal]=$row['primo'];
			$kontonr[$kontoantal]=$row['kontonr'];
		} 
		$tmp--;
	}
	$pre_regnaar=$kodenr-1;
	$r=db_fetch_array(db_select("select box2,box9 from grupper where kodenr='$pre_regnaar' and art='RA'",__FILE__ . " linje " . __LINE__));
	($r['box2'] >= '2015' && !$r['box9'])?$aut_lager='on':$aut_lager=NULL;
	if (!$pre_regnaar) {
		echo "regnaar mangler";
		exit;
	}	
	if ($aut_lager) {
		$x=0;
		$varekob=array();
		$laas_lager=1;
		$q=db_select("select box1,box2,box3 from grupper where art = 'VG' and box8 = 'on'",__FILE__ . " linje " . __LINE__);
		while ($r=db_fetch_array($q)) {
			if ($r['box1'] && !in_array($r['box3'],$varekob)) {
				$varelager_i[$x]=$r['box1'];
				$varelager_u[$x]=$r['box2'];
				$varekob[$x]=$r['box3'];
				if ($varelager_i[$x] || $varelager_u[$x]) $laas_lager=0;
				$x++;
			}
		}
		if ($laas_lager) {
			db_modify("update grupper set box9='on' where kodenr='$pre_regnaar' and art='RA'",__FILE__ . " linje " . __LINE__);
			$aut_lager=NULL;
		}
	}
	$ny_sum=0;
	$resultat=0;
	$q = db_select("select * from kontoplan where kontotype='D' and regnskabsaar=$pre_regnaar order by kontonr",__FILE__ . " linje " . __LINE__);
	$y=0;
	while ($r = db_fetch_array($q)) {
		$resultat+=afrund($r['primo'],2);
		$q2 = db_select("select * from transaktioner where transdate>='$pre_regnstart' and transdate<='$pre_regnslut' and kontonr='$r[kontonr]'",__FILE__ . " linje " . __LINE__);
		while ($r2 = db_fetch_array($q2)) $resultat+=afrund($r2['debet']-$r2['kredit'],2);
		if ($aut_lager) {
			if (in_array($r['kontonr'],$varekob)) {
				$l_a_primo[$x]=find_lagervaerdi($r['kontonr'],$pre_regnstart,'start');
				$l_a_sum[$x]=find_lagervaerdi($r['kontonr'],$pre_regnslut,'slut');
# Varekøb (debet) debiteres lager primo og krediteres lager saldo. Dvs tallet mindskes hvis lager øges 
				$resultat+=$l_a_primo[$x]; 
				$resultat-=$l_a_sum[$x];		
			}
			if (in_array($r['kontonr'],$varelager_i) || in_array($r['kontonr'],$varelager_u)) {
				$l_a_primo[$x]=find_lagervaerdi($r['kontonr'],$pre_regnstart,'start');
				$l_a_sum[$x]=find_lagervaerdi($r['kontonr'],$pre_regnslut,'slut');
# Varelager (debet) krediteres lager primo og og debiteres lager saldo.  Dvs tallet øges hvis lager øges
				$resultat-=$l_a_primo[$x];
				$resultat+=$l_a_sum[$x];
			}
		}
	}
	$resultat=afrund($resultat,2);
	$r=db_fetch_array(db_select("select * from kontoplan where kontotype='X' and regnskabsaar=$pre_regnaar",__FILE__ . " linje " . __LINE__));
	$sideskift=$r['kontonr']*1;
	if ($sideskift) {
		$q2=db_select("select * from transaktioner where transdate>='$pre_regnstart' and transdate<='$pre_regnslut' and kontonr='$sideskift'",__FILE__ . " linje " . __LINE__);
		while ($r2=db_fetch_array($q2)) $resultat+=afrund($r2['debet']-$r2['kredit'],2);
		$saldosum=$resultat;
		print "<td><br /></td>";
		print "<td>Resultat</td>";
		print "<input type='hidden' name='kontonr[0]' value='$sideskift'>";
		print "<td width='10' align='right'><input type=hidden name=saldo[0] value=$resultat>".dkdecimal($resultat,2)."</td>";
		print "<td><SELECT NAME=overfor_til[0]>";
		if (isset($r['overfor_til']) && $r['overfor_til']) print "<option>$r[overfor_til]</option>";  
		print "<option></option>";
		for ($x=1;$x<=$kontoantal;$x++) print "<option>$kontonr[$x]</option>";
		print "</SELECT></td>";
		print "<td width='10'><br /></td></tr>\n";
		if (isset($ny_primo[$y])) $ny_sum+=$ny_primo[$y];
	}
#cho "select * from kontoplan where kontotype='S' and regnskabsaar='$pre_regnaar' order by kontonr<br>";
	$query = db_select("select * from kontoplan where kontotype='S' and regnskabsaar='$pre_regnaar' order by kontonr",__FILE__ . " linje " . __LINE__);
	$y=0;
	while ($row = db_fetch_array($query)) {
		$y++;
		$ny_primo[$y]=0;
    for ($x=1; $x<=$kontoantal; $x++) {
			if ($kontonr[$x]==$row['kontonr']) {
				$ny_primo[$y]=$primo[$x]; 
#				$overfor_til[$y]=$row['overfor_til'];
			}
		}
		$belob=0;
		$belob=$row['primo'];
		print "<tr><input type=hidden name=kontonr[$y] value=$row[kontonr]>";
		$q2 = db_select("select * from transaktioner where transdate>='$pre_regnstart' and transdate<='$pre_regnslut' and kontonr='$row[kontonr]'",__FILE__ . " linje " . __LINE__);
		while ($r2 = db_fetch_array($q2)) {
			 $belob+=afrund($r2['debet']-$r2['kredit'],2);
		}
		if ($aut_lager) {
			if (in_array($row['kontonr'],$varekob)) {
				$l_a_primo[$x]=find_lagervaerdi($row['kontonr'],$pre_regnstart,'');
				$l_a_sum[$x]=find_lagervaerdi($row['kontonr'],$pre_regnslut,'');
# Varekøb (debet) debiteres lager primo og krediteres lager saldo. Dvs tallet mindskes hvis lager øges 
				$belob+=$l_a_primo[$x]; 
				$belob-=$l_a_sum[$x];		
			}
			if (in_array($row['kontonr'],$varelager_i) || in_array($row['kontonr'],$varelager_u)) {
				$l_a_primo[$x]=find_lagervaerdi($row['kontonr'],$pre_regnstart,'start');
				$l_a_sum[$x]=find_lagervaerdi($row['kontonr'],$pre_regnslut,'slut');
# Varelager (debet) krediteres lager primo og og debiteres lager saldo.  Dvs tallet øges hvis lager øges
				$belob-=$l_a_primo[$x];
				$belob+=$l_a_sum[$x];
			}
		}
		
		$saldosum=$saldosum+$belob;
		print "<td>$row[kontonr]</td>";
		print "<td>$row[beskrivelse]</td>";
		print "<td width=10 align=right><input type=hidden name=saldo[$y] value=$belob>".dkdecimal($belob,2)."</td>";
		print "<td><SELECT NAME=overfor_til[$y]>";
		if ($row['overfor_til'] && in_array($row['overfor_til'],$kontonr)) print "<option>$row[overfor_til]</option>";  
		elseif (in_array($row['kontonr'],$kontonr)) print "<option>$row[kontonr]</option>";
		else print "<option></option>";
		for ($x=1;$x<=$kontoantal;$x++) print "<option>$kontonr[$x]</option>";
		print "</SELECT></td>";
		
		print "<td width=10 align=right><input type=hidden name=ny_primo[$y] value=$ny_primo[$y]>".dkdecimal($ny_primo[$y],2)."</td></tr>\n";
		$ny_sum=$ny_sum+$ny_primo[$y];
	}
	print "<td></td><td></td><td align=right>".dkdecimal($saldosum,2)."</td><td></td><td align=right>".dkdecimal($ny_sum,2)."</td></tr>\n";
	if ($debetsum-$kreditsum!=0) {print "<BODY onload=\"javascript:alert('Konti er ikke i balance')\">";}
#	print "<tr><td colspan = 3> Overfør åbningsbalance</td><td align='center'><input type='checkbox' name=primotal checked></td></tr>\n";
	print "<input type=hidden name=kontoantal value=$y>";
	print "<tr><td colspan = 5 align = center><input class='button green medium' type=submit accesskey=\"g\" value=\"".findtekst(471, $sprog_id)."\"  style=\"width:150px\" name=\"submit\" onclick=\"javascript:docChange = false;\">";
	if ($aut_lager && date("Y-m-d")>$pre_regnslut) {
		$title="Bogfører alle lagerbevægelser i det foregående år. Bør gøres umiddelbart efter årsskifte og lageroptælling".
		$confirmtxt="";
		print "&nbsp;<a href=laas_lager.php?regnaar=$pre_regnaar&regnaar_id=$id&print=0><input title=\"$title\" type=\"button\" value=\"".findtekst(1230, $sprog_id)."\" onclick=\"return confirm('Bogfør og lås lagerprimo? Obs - Vær tålmodig det kan tage flere minutter')\"></a>"; 
	}
	# if ($regnaar==$max_aar) print "<input type=submit value=\"Slet\" name=\"submit\" onclick=\"javascript:docChange = false;\">";
	print "</td></tr>\n";
	print "</form>";

print "</tbody></table></td></tr>\n";# table 3b slut
print "</tbody></table></div></div>";


if ($menu=='T') {
	include_once '../includes/topmenu/footer.php';
} else {
	include_once '../includes/oldDesign/footer.php';
}
	exit;
}
######################################################################################################################
?>
