<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// // ---------------------includes/online.php----lap 4.0.5---2022-01-05---
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
// Copyright (c) 2003-2022 saldi.dk aps
// ----------------------------------------------------------------------
// 20120905 $ansat_navn bliver nu sat her. Søg 20120905
// 20130120 $sag_rettigheder bliver nu sat her. Søg 20130120
// 20140112 ændres headertekst til så google ikke tror at siderne er på norsk 
// 20140117 Remmet 2 linjer og fjernet id  + $g_id fre 3. linje (Gav efterfølgende duplicate key value violates unique constraint "grupper_pkey") . Søg 20140117
// 20140502	Indsat javascript i header til ordrelinje udfoldning - PHR Danosoft.Søg 20140502  
// 20150104 Indsat kontrol for om database er blevet opdateret. Søg 20150104
// 20150104 Ændret alert til tekstboks. Søg tekstboks
// 20170213 Initialiserer $meta_returside.
// 20171009 Table / body styles flyttet fra css/pos.css så font size kan sættes som variabel. søg 21071009
// 20190110 timezone og andre systemvariabler hentes nu fra tabellen settings. 
// 20190412 customAlertText hentes nu fra tabellen settings. 
// 20190412 PHR - "1)!='1')" changed to "1)<'1')" to support read only function. 20190529
// 20190605 PHR Check for version from 'regnskab' before trying to fetch from 'settings' 20190605.
// 20190704 RG (Rune Grysbæk) Mysqli implementation 
// 20190821 PHR Check if account is closed ($lukket). 
// 20200225 PHR Some corrections regarding MySQLi;
// 20200928 PHR Added '$db_id &&' to avoid error if no db_id when calling labelprint.php from mylabel.php 20200928 
// 20200930 PHR Added  'order by logtime desc limit 1' to 'select * from online'
// 20201218 LOE Added this to replace the previous function as it displays autosize not a function error 20201218
// 20210916 LOE Added lingua as language set for admins* 
// 20211001 PHR	Simplyfying language
// 20211018 LOE fixed some bugs..checking if the select and fetch query is true.
// 20211220 PHR removed if ($r['box5']) from '20210928'. Why was it set?? 
// 20220105 PHR Sets $sprog_id=$languageID
// 20220212 PHR	MySale users removed from 'grupper' 

#include("../includes/connect.php"); #20211001
if (isset($_COOKIE['timezone'])) { #20190110
	$timezone=$_COOKIE['timezone'];
	date_default_timezone_set($timezone);
} else {
	
	date_default_timezone_set('Europe/Copenhagen');
	#$r=db_fetch_array(db_select("select lukket,version from regnskab where id='1'",__FILE__ . " linje " . __LINE__)); # 20190605
	$r=db_fetch_array(db_select("select lukket,version from regnskab where id='1' and db = '$sqdb'",__FILE__ . " linje " . __LINE__)); # 20210930
	if (isset($dbver) && $dbver >= '3.7.2') {
		$r=db_fetch_array(db_select("select id, var_value from settings where var_name='timezone'",__FILE__ . " linje " . __LINE__));
		if ($r['var_value']) {
		$timezone=$r['var_value'];
		} else {	
			$timezone='Europe/Copenhagen';
			if ($r['id']) $qtxt="update settings set var_value='$timezone' where id='$r[id]'";
			else {
				$qtxt="insert into settings (var_name,var_value,var_description)";
				$qtxt.=" values ";
				$qtxt.="('timezone','$timezone','Generel tidszone. Anvendes hvis der ikke er sat tidszone i det enkelte regnskab')";
			}
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
		$r=db_fetch_array(db_select("select var_value from settings where var_name='alertText'",__FILE__ . " linje " . __LINE__));
		
			// ($r['var_value'])?$customAlertText=$r['var_value']:$customAlertText=NULL;
			// $r=db_fetch_array(db_select("select var_value from settings where var_name='ps2pdf'",__FILE__ . " linje " . __LINE__));
			// ($r['var_value'])?$ps2pdf=$r['var_value']:$ps2pdf=NULL;
			// $r=db_fetch_array(db_select("select var_value from settings where var_name='pdftk'",__FILE__ . " linje " . __LINE__));
			// ($r['var_value'])?$pdftk=$r['var_value']:$pdftk=NULL;
			// $r=db_fetch_array(db_select("select var_value from settings where var_name='ftp'",__FILE__ . " linje " . __LINE__));
			// ($r['var_value'])?$ftp=$r['var_value']:$ftp=NULL;
			// $r=db_fetch_array(db_select("select var_value from settings where var_name='dbdump'",__FILE__ . " linje " . __LINE__));
			// ($r['var_value'])?$dbdump=$r['var_value']:$dbdump=NULL;
			// $r=db_fetch_array(db_select("select var_value from settings where var_name='tar'",__FILE__ . " linje " . __LINE__));
			// ($r['var_value'])?$tar=$r['var_value']:$tar=NULL;
			// $r=db_fetch_array(db_select("select var_value from settings where var_name='zip'",__FILE__ . " linje " . __LINE__));
			// ($r['var_value'])?$zip=$r['var_value']:$zip=NULL;
			// $r=db_fetch_array(db_select("select var_value from settings where var_name='systemLanguage'",__FILE__ . " linje " . __LINE__));
			// ($r['var_value'])?$systemLanguage=$r['var_value']:$systemLanguage='Dansk';
			$r? $customAlertText=$r['var_value']:$customAlertText=NULL; #20211018
		$r=db_fetch_array(db_select("select var_value from settings where var_name='ps2pdf'",__FILE__ . " linje " . __LINE__));
			$r? $ps2pdf=$r['var_value']:$ps2pdf=NULL;
		$r=db_fetch_array(db_select("select var_value from settings where var_name='pdftk'",__FILE__ . " linje " . __LINE__));
			$r ?$pdftk=$r['var_value']:$pdftk=NULL;
		$r=db_fetch_array(db_select("select var_value from settings where var_name='ftp'",__FILE__ . " linje " . __LINE__));
			$r ?$ftp=$r['var_value']:$ftp=NULL;
		$r=db_fetch_array(db_select("select var_value from settings where var_name='dbdump'",__FILE__ . " linje " . __LINE__));
			$r ?$dbdump=$r['var_value']:$dbdump=NULL;
		$r=db_fetch_array(db_select("select var_value from settings where var_name='tar'",__FILE__ . " linje " . __LINE__));
			$r ?$tar=$r['var_value']:$tar=NULL;
		$r=db_fetch_array(db_select("select var_value from settings where var_name='zip'",__FILE__ . " linje " . __LINE__));
			$r? $zip=$r['var_value']:$zip=NULL;
			$r=db_fetch_array(db_select("select var_value from settings where var_name='systemLanguage'",__FILE__ . " linje " . __LINE__));
			$r ?$systemLanguage=$r['var_value']:$systemLanguage='Dansk';
		
	}
}

if (!isset($meta_returside)) $meta_returside=NULL;
$db_skriv_id=NULL; #bruges til at forhindre at skrivninger til masterbasen logges i de enkelte regnskaber.
if (!isset($modulnr))$modulnr=NULL;
if (!isset($db_type))$db_type="postgres";
if (!isset($db_type))$db_type="postgres";
$ip=$_SERVER['REMOTE_ADDR'];
$ip=substr($ip,0,10);
$sag_rettigheder=NULL;
#if ($title!="kreditorexport"){
$unixtime=date("U");
#include("../includes/connect.php"); #20211001
	$q = db_select("select * from online where session_id = '$s_id' order by logtime desc limit 1",__FILE__ . " linje " . __LINE__);
	if ($r = db_fetch_array($q)) {
		$dbuser              = trim($r['dbuser']);
		$db	                 = trim($r['db']);
		$regnaar             = trim($r['regnskabsaar']);
		$brugernavn          = db_escape_string($r['brugernavn']);
		$rettigheder         = $r['rettigheder'];
		$superUserPermission = $rettigheder;
		$revisor             = $r['revisor'];
		$logtime             = $r['logtime'];
		($r['language_id'])? $languageID = $r['language_id'] : $languageID = 0;
		$sprog_id            = $languageID;
		if ($logtime) db_modify("update online set logtime = '$unixtime' where session_id = '$s_id'",__FILE__ . " linje " . __LINE__);
	} elseif ($title!='login' && $title!='opdat' && $title!='logud' && $title!='Aaben regnskab') {
		if ($webservice) return ('Session expired');
		else {
			if (!isset($nextver)) $nextver=NULL;
			if (!$nextver) {# 20150125
				include("../includes/std_func.php");
				$txt='&nbsp;Din session er udl&oslash;bet - du skal logge ind igen';
				print tekstboks($txt);
				print "<meta http-equiv=\"refresh\" content=\"4;URL=../index/logud.php\">";
				exit;
			}
		}
	}
#}
if (substr($db,0,7) == ' develop') ini_set('display_errors',1);
else ini_set('display_errors',0);

$labelprint=0;
if($sqdb=='udvikling') $labelprint=1;
elseif($sqdb=='severinus') $labelprint=1;
elseif($db=='bizsys_22') $labelprint=1;
elseif($db=='bizsys_25') $labelprint=1;

if($db=='severinus_22') $kundedisplay=1;
elseif($db=='grillbar_59') $kundedisplay=1;
#elseif($db=='udvikling_5') $kundedisplay=1;
else $kundedisplay=0;

#if ($db == 'udvikling_5') $timezone='America/Godthab';
#if (!isset($timezone)) $timezone='Europe/Copenhagen';
#date_default_timezone_set($timezone);

if ($modulnr && $modulnr<100 && $db==$sqdb) { #Lukker vinduet hvis revisorbruger er logget af
	include("../includes/std_func.php");
	$txt='Du har logget ud - vinduet lukkes';
	print tekstboks($txt);
	print "<meta http-equiv=\"refresh\" content=\"4;URL=../includes/luk.php\">";
	exit;
}
if ($row = db_fetch_array(db_select("select * from regnskab where db = '$db'",__FILE__ . " linje " . __LINE__))){
	$db_id = $row['id'];
	$db_skriv_id = $db_id;
	$db_ver = $row['version'];  # 20150104
	$regnskab = $row['regnskab'];
	$max_posteringer = $row['posteringer'];
	$lukket=$row['lukket'];
	
}
/*
if ($row = db_fetch_array(db_select("select * from regnskab where db = 'develop'",__FILE__ . " linje " . __LINE__))){
	$lingua= $row['sprog']; #20210916
}
*/
#.............	
	$query = db_select("select * from online where session_id = '$s_id' and brugernavn ='$brugernavn'",__FILE__ . " linje " . __LINE__);
	if ($row = db_fetch_array($query)) {
		$lang1 = trim($row['sprog']);	
}
	$_SESSION['Language'] = $lang1; #20210922
#..............





if ($db_id && $db && $sqdb && $db!=$sqdb) { #20200928
	if (!isset($nextver)) { # 20150104
			if ($version>$db_ver) { 
			if ($db_type=='mysql') {
				if (!mysql_select_db($db)) die( "Unable to connect to MySQL");
			}	elseif ($db_type=='mysqli') { #RG_mysqli
				$connection = db_connect ($sqhost, $squser, $sqpass, $db);
				if (!mysqli_select_db($connection, $db)) die( "Unable to connect to MySQLi");
			} else {
				$connection = db_connect ($sqhost, $squser, $sqpass, $db, __FILE__ . " linje " . __LINE__);
				if (!$connection) die( "Unable to connect to PostgreSQL");
			}
			if (tbl_exists('grupper')) {
			$r = db_fetch_array(db_select("select box1 from grupper where art='VE'",__FILE__ . " linje " . __LINE__));
			include("../includes/connect.php");
			if ($r['box1']>$db_ver) {
				$db_ver=$r['box1'];
				db_modify("update regnskab set version = '$db_ver' where id = '$db_id'", __FILE__ . " linje " . __LINE__);
			}
			if ($version>$db_ver && $title!='Aaben regnskab') {
					include("../includes/tjek4opdat.php");
					tjek4opdat($db_ver,$version);
				}
			} else {
				echo "stopper her";
				exit;
			}
		}
	}
	if ($db_type=='mysql') {
		if (!mysql_select_db($db)) die( "Unable to connect to MySQL");
	} elseif ($db_type=='mysqli') {
		if (!mysqli_select_db($connection,$db)) die( "Unable to connect to MySQL");
	} else {
	$connection = db_connect ($sqhost, $squser, $sqpass, $db, __FILE__ . " linje " . __LINE__);
		if (!$connection) die( "Unable to connect to PostgreSQL");
	}	
	if (!$revisor) {
		if ($lukket) {
			echo "regnskabet er lukket";
			print "<meta http-equiv=\"refresh\" content=\"4;URL=../index/index.php\">";
			exit;
		}
		$r=db_fetch_array(db_select("select * from brugere where brugernavn= '$brugernavn'",__FILE__ . " linje " . __LINE__));
		$bruger_id=$r['id'];
		$rettigheder = trim($r['rettigheder']);
		$ansat_id=$r['ansat_id']*1;
#		if ($r['language_id']) $languageID=$sprog_id=$r['language_id'];
		if ($ansat_id) { #20120905 
		$r = db_fetch_array(db_select("select * from ansatte where id = '$ansat_id'",__FILE__ . " linje " . __LINE__));
			$ansat_navn=$r['navn'];
			if (isset($r['gruppe'])) {
				$ansat_gruppe=$r['gruppe']*1; #20120905
				$r = db_fetch_array(db_select("select * from grupper where id = '$ansat_gruppe'",__FILE__ . " linje " . __LINE__));
				$sag_rettigheder=$r['box2'];
			}
		} else {
			$ansat_navn=$brugernavn;
			$sag_rettigheder=NULL;	
		}
	}	else $bruger_id=-1;
	if (!$sprog_id)$sprog_id=1;
	
	if (!strpos($css,'mysale')) {
	$jsvars="statusbar=0,menubar=0,titlebar=0,toolbar=0,scrollbars=1,resizable=1,dependent=1";
	$qtxt="select box1,box2,box3,box4,box5 from grupper where art = 'USET' and kodenr = '$bruger_id'";
	if (!$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
#		$r = db_fetch_array(db_select("select max(id) as id from grupper",__FILE__ . " linje " . __LINE__)); 20140117 
#		$g_id=$r['id']+1;
		db_modify("insert into grupper(beskrivelse,art,kodenr,box1,box2,box3,box4,box5) values ('Usersettings','USET','$bruger_id','$jsvars','','on','#eeeef0','')",__FILE__ . " linje " . __LINE__);
	} else {
		$jsvars=$r['box1'];
		$popup=$r['box2'];
		$menu=$r['box3'];
		$bgcolor=$r['box4'];
			$bgnuance1=$r['box5']; #20210928
		if (strpos($jsvars,"reziseable")) { #tilfoejet 20090730 grundet stavefejl i reziseable
			$jsvars=str_replace("reziseable","resizable",$jsvars);
			db_modify("update grupper set box1='$jsvars' where  art = 'USET' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__);
		}
	}
	$textcolor="#000077";
	$textcolor2="#009900";
	$textcolor3="#6666aa"; # Svagere tekst til det som er mindre vigtigt
	if (!isset($bgcolor)) $bgcolor="#eeeef0"; #alm baggrund
	if (!isset($bgcolor2)) $bgcolor2="#BEBCCE"; #top & bundlinjer
	if (!isset($bgcolor3)) $bgcolor3="#cccccc";
	if (!isset($bgcolor4)) $bgcolor4="#d0d0f0";
	if (!isset($bgcolor5)) {
		$bgcolor5=NULL;
		for ($x=1;$x<=5;$x=$x+2) {
			$a=hexdec(substr($bgcolor,$x,2));
			if ($a<="224")$a+=32;
			else $a-=32;
			$bgcolor5.=dechex($a);
		}
		# $bgcolor5="#e0e0f0";
	}
if (!isset($bgnuance1)) $bgnuance1="+01+01-55"; # Aendring af nuancen til gult ved skiftende linjer
	}
	if ($menu=='T') {
		$header='nix';
		$bg='nix';
		$css=NULL;
	}
	if (($rettigheder)&&($modulnr)&&(substr($rettigheder,$modulnr,1)<'1')) { #20190529
		include("../includes/std_func.php");
		$txt="Du har ikke nogen rettigheder her - din aktivitet er blevet logget";
		print tekstboks($txt);
		exit;
	}
}



if ($header!='nix') {
	if ($db_encode=="UTF8") $charset="UTF-8";
	else $charset="ISO-8859-1";
	PRINT "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n
	<html>\n
	<head><title>$title</title><meta http-equiv=\"content-type\" content=\"text/html; charset=$charset;\">\n
	<meta http-equiv=\"content-language\" content=\"da\">\n
	<meta name=\"google\" content=\"notranslate\">\n";
	if($meta_returside) print "$meta_returside"; #20140502
	if ($css) PRINT "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css\">\n";
	else print "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/saldimenu.css\"/>\n";
	if (substr($title,0,3)=='POS') { # 21071009
		($title=='POS_ordre' && isset($_COOKIE['saldi_pfs']))?$pfs=$_COOKIE['saldi_pfs']:$pfs=10;
		print "<style> body {font-family: Arial, Helvetica, sans-serif;font-size: ".$pfs."pt;} </style>";
		print "<style> table {font-family: Arial, Helvetica, sans-serif;font-size: ".$pfs."pt;} </style>";
	}
	print "<script type=\"text/javascript\" src=\"../javascript/jquery-1.8.0.min.js\"></script>\n"; #20140502
	print "<script type=\"text/javascript\" src=\"../javascript/jquery.autosize.js\"></script>\n"; #20140502
	print "<script LANGUAGE=\"JavaScript\" SRC=\"../javascript/overlib.js\"></script>\n"; 
	print "<script language=\"javascript\" type=\"text/javascript\" src=\"../javascript/confirmclose.js\"></script>\n"; #20140502
#	print "<script src=\"../javascript/sweetalert.min.js\"></script>";
#	print "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/sweetalert.css\">";

	#print "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/main.css\"/>\n";
	print "
	<script type=\"text/javascript\">
	
	var linje_id=0;
	var vare_id=0;
	var antal=0;
	function serienummer(linje_id,antal){
		window.open(\"serienummer.php?linje_id=\"+ linje_id,\"\",\"left=10,top=10,width=400,height=400,scrollbars=yes,resizable=yes,menubar=no,location=no\")
	}
	function batch(linje_id,antal){
		window.open(\"batch.php?linje_id=\"+ linje_id,\"\",\"left=10,top=10,width=400,height=400,scrollbars=yes,resizable=yes,menubar=no,location=no\")
	}
	function stykliste(vare_id){
		window.open(\"../lager/fuld_stykliste.php?id=\"+ vare_id,\"\",\"left=10,top=10,width=400,height=400,scrollbars=yes,resizable=yes,menubar=no,location=no\")
	}
	
	</script>";
	#20140502 -->
	?> 
	
		<script type="text/javascript">
// jQuery funktion til autosize på textarea 
	$(document).ready(function(){
		$('.autosize').autosize();
	});
	// jQuery funktion til ordrelinjer i ordre.php. Ved tryk på enter submitter formen og ved shift+enter laver den ny linje i textarea
	$(function() {
		$('textarea.comment').keyup(function(e) {
				if (e.which == 13 && ! e.shiftKey) {
						$("#submit").click();
				}
		});
	});
// $(document).on('focus', 'textarea', function(){
//            autosize($('textarea'));  //20201218
//});	
	</script>
	<?php
	# <-- 20140502
	PRINT "</head>\n";
}
if ($bg!='nix') {
	if (!$bgcolor) $bgcolor="#000000";
	PRINT "<body bgcolor=\"$bgcolor\" link=\"#000000\" vlink=\"#000000\" alink=\"#000000\">\n";
}
?>
