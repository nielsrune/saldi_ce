<?php

// --- kreditor/rapport.php --- patch 4.0.5 --- 2023-03-23 ---
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
// 2023.03.23 PBLM Fixed minor errors


@session_start();
$s_id=session_id();
$css="../css/std.css";

$title="Kreditorrapport";
$modulnr=8;

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/forfaldsdag.php");
include("../includes/autoudlign.php");
include("../includes/rapportfunc.php");

if ($popup) $returside="../includes/luk.php";
else $returside="../index/menu.php";

if (isset($_GET['ny_rykker'])) {
	$dato_fra=$_GET['dato_fra'];
	$dato_til=$_GET['dato_til'];
	$konto_fra=$_GET['konto_fra'];
	$konto_til=$_GET['konto_til'];
#	$regnaar=$_GET['regnaar'];
	openpost($dato_fra, $dato_til, $konto_fra, $konto_til, $rapportart, 'K');
	exit;
} elseif ($rapportart=if_isset($_GET['rapportart'])) {
	$dato_fra  = if_isset($_GET['dato_fra'],NULL);
	$dato_til  = if_isset($_GET['dato_til'],NULL);
	$konto_fra = if_isset($_GET['konto_fra'],NULL);
	$konto_til = if_isset($_GET['konto_til'],NULL);
	$udlign    = if_isset($_GET['udlign'],NULL);
	if ($udlign) autoudlign($udlign);
	if ($rapportart == 'accountChart') include ('../includes/reportFunc/accountChart.php');
	$rapportart($dato_fra,$dato_til,$konto_fra,$konto_til,$rapportart, 'K');
	exit;
}
$rapportart=NULL;
if (isset($_POST['find'])) {
#	echo "find";
}
if (isset($_POST['openpost'])) $rapportart='openpost';
if (isset($_POST['kontosaldo'])) $rapportart='kontosaldo';
if (isset($_POST['accountChart'])) $rapportart='accountChart'; 
if (isset($_POST['dato'])) {
	$dato=$_POST['dato'];
	list($dato_fra,$dato_til)=($dato != 0 ? explode(":",$dato) : 0);
#	if (!$dato_til) {
#		$dato_til=$dato_fra;
#		$dato_fra='010100';
#	}
# echo "dato $dato | $dato_fra | $dato_til<br>"; 
	$fromdate=usdate($dato_fra);
	$todate=usdate($dato_til);
# echo "dato $dato | $dato_fra | $dato_til<br>"; 
}
if (isset($_POST['konto'])) {
	$konto=$_POST['konto'];
	echo $konto;
	list($konto_fra,$konto_til)=($konto != "" ? explode(":",$konto) : "");
	if(is_numeric($konto_fra) && !$konto_til) $konto_til=$konto_fra; 
# echo "konto $dato | $konto_fra | $konto_til<br>"; 
}

#echo "R $rapportart<br>";
$husk=if_isset($_POST['husk']);
if (isset($_POST['salgsstat']) && $_POST['salgsstat']) {
 	if ($husk) db_modify("update grupper set box1='$husk',box2='$dato_fra',box3='$dato_til',box4='$konto_fra',box5='$konto_til',box6='$rapportart' where art='DRV' and kodenr='$bruger_id'",__FILE__ . " linje " . __LINE__);
	print "<meta http-equiv=\"refresh\" content=\"1;URL=../includes/salgsstat.php?dato_fra=$dato_fra&dato_til=$dato_til&konto_fra=$konto_fra&konto_til=$konto_til&art=K\">"; 
	exit;
}

if (isset($_POST['submit']) || $rapportart) {
	if (!$rapportart) {
		$submit=strtolower(trim($_POST['submit']));
		$rapportart=strtolower(trim($_POST['rapportart']));
		$dato_fra=$_POST['dato_fra'];
		$dato_til=$_POST['dato_til'];
	} else {
# echo "update grupper set box1='$regnaar',box2='$dato_fra',box3='$dato_til',box4='$konto_fra',box5='$konto_til',box6='$rapportart' where art='KRV' and kodenr='$bruger_id'<br>";
		db_modify("update grupper set box1='$husk',box2='$dato_fra',box3='$dato_til',box4='$konto_fra',box5='$konto_til',box6='$rapportart' where art='KRV' and kodenr='$bruger_id'",__FILE__ . " linje " . __LINE__);
		$submit='ok';
	}
#	$md=$_POST['md'];
#	if (isset($_POST['konto_fra']) && strpos($_POST['konto_fra'],":")) {
#		list ($konto_fra, $firmanavn) = explode(":", $_POST['konto_fra']);
		$konto_fra = trim($konto_fra);
#	}
#	if (isset($_POST['konto_til']) && strpos($_POST['konto_til'],":")) {
#		list ($konto_til, $firmanavn) = explode(":", $_POST['konto_til']);
		$konto_til = trim($konto_til);
#	}
#	if (isset($_POST['regnaar']) && strpos($_POST['regnaar'],"-")) {
#		list ($regnaar, $firmanavn)= explode("-", $_POST['regnaar']);
		$firmanavn = (isset($firmanavn) ? trim($firmanavn) : "");
#	}
	if (($submit=="mail kontoudtog")||($submit=="opret rykker")||($submit=="ryk alle")){
		$kontoantal=$_POST['kontoantal'];
		$konto_id=$_POST['konto_id'];
		$kontoudtog=$_POST['kontoudtog'];
		$rykkerbelob=$_POST['rykkerbelob'];
		$y=0;
		for($x=1; $x<=$kontoantal; $x++){
			if (($kontoudtog[$x]=='on')&&(($submit=="mail kontoudtog")||($rykkerbelob[$x]>0))) {
				$tmp=$tmp.$konto_id[$x].";";
				$y++;			
			}
		}
		$kontoantal=$y;
		if ($tmp){
			if ($submit=="mail kontoudtog") {
				print "<BODY onLoad=\"window.open('mail_kontoudtog.php?kontoliste=$tmp&dato_fra=$dato_fra&dato_til=$dato_til&kontoantal=$kontoantal','','$jsvars')\">";
			} else {
				print "<BODY onLoad=\"window.open('ny_rykker.php?kontoliste=$tmp&kontoantal=$kontoantal','','$jsvars')\">";
				$ny_rykker=1;
			} 
		} elseif ($submit=="ryk alle") {
			print "<BODY onLoad=\"window.open('ny_rykker.php?kontoliste=alle&kontoantal=max','','$jsvars')\">";
			$ny_rykker=1;
		} else {
			if ($submit=="mail kontoudtog") {print "<BODY onLoad=\"javascript:alert('Der er ikke afm&aelig;rket nogen konti til modtagelse af kontoudtog')\">";}
			else {
				print "<BODY onLoad=\"javascript:alert('Der er ikke afm&aelig;rket nogen konti til modtagelse af rykker eller bel&oslash;bet er ikke forfaldent til betaling')\">";
			}
		}
/*
		if (!strstr($dato_fra," ")) { 
			if ($md[$dato_fra]) $dato_fra=$regnaar." ".$md[$dato_fra];
			else $dato_fra=$regnaar." ".$dato_fra;
			if ($md[$dato_til]) $dato_til=$regnaar." ".$md[$dato_til];
			else $dato_til=$regnaar." ".$dato_til;
		}
*/
		$submit='ok';
	}elseif ( $submit=="slet" || $submit=="udskriv" || strstr($submit,"bogf") || $submit=="ny rykker" || $submit=="afslut") {
		$rykkerantal=if_isset($_POST['rykkerantal']);
		$rykker_id=if_isset($_POST['rykker_id']);
		$rykkerbox=if_isset($_POST['rykkerbox']);
		if ($submit=="slet") {
			for($x=1; $x<=$rykkerantal; $x++){
				if (isset($rykkerbox[$x]) && $rykkerbox[$x]=='on') {
					db_modify("delete from ordrelinjer where ordre_id=$rykker_id[$x]",__FILE__ . " linje " . __LINE__);	
					db_modify("delete from ordrer where id=$rykker_id[$x]",__FILE__ . " linje " . __LINE__);	
				}
			}
		} elseif ($submit=="udskriv" || $submit=="ny rykker" || $submit=="afslut") {
			$tmp='';
			$tmp2=0;
			for($x=1; $x<=$rykkerantal; $x++){
				if ($rykkerbox[$x]=='on') {
					if ($tmp) $tmp=$tmp.";";
					$tmp=$tmp.$rykker_id[$x];
					$tmp2++;	
				}
			}
			if ($submit=="udskriv" && $tmp2>0) print "<BODY onLoad=\"window.open('rykkerprint.php?rykker_id=$tmp&kontoantal=$tmp2','','$jsvars')\">";
			elseif ($submit=="ny rykker" && $tmp2>0) {
				print "<BODY onLoad=\"window.open('ny_rykker.php?rykker_id=$tmp&kontoantal=$tmp2','','$jsvars')\">";
				$ny_rykker=1;
			} elseif ($submit=="afslut" && $tmp2>0) {
				print "<BODY onLoad=\"window.open('afslut_rykker.php?rykker_id=$tmp&kontoantal=$tmp2','','$jsvars')\">";
				$ny_rykker=1;
			} 
		} elseif (strstr($submit,"bogf")) {
			for($x=1; $x<=$rykkerantal; $x++){
				if ($rykkerbox[$x]=='on') bogfor_rykker($rykker_id[$x]);
			}
		}
/*
		if (!strstr($dato_fra," ")) { 
			if ($md[$dato_fra]) $dato_fra=$regnaar." ".$md[$dato_fra];
			else $dato_fra=$regnaar." ".$dato_fra;
			if ($md[$dato_til]) $dato_til=$regnaar." ".$md[$dato_til];
			else $dato_til=$regnaar." ".$dato_til;
		}
*/
		$submit='ok';
	}
# echo "KF $konto_fra<br>";
} elseif(isset($_GET['konto_fra'])) {
	$rapportart=$_GET['rapportart'];
	$dato_fra=$_GET['dato_fra'];
	$dato_til=$_GET['dato_til'];
	$konto_fra=$_GET['konto_fra'];
	$konto_til=$_GET['konto_til'];
#	$regnaar=$_GET['regnaar'];
	$submit=$_GET['submit'];
	if ($udlign=$_GET['udlign']) autoudlign($udlign);
}	elseif (isset($_GET['kontonr'])){
	$konto_fra=$_GET['kontonr'];
	$konto_til=$_GET['kontonr'];
	$submit="ok";
	$rapportart=$_GET['rapportart'];
/*
	$row = db_fetch_array(db_select("select * from grupper where art = 'RA' and kodenr='$regnaar'",__FILE__ . " linje " . __LINE__));
		$start_md[$x]=$row['box1']*1;
		$start_aar[$x]=$row['box2']*1;
		$slut_md[$x]=$row['box3']*1;
		$slut_aar[$x]=$row['box4']*1;
		$dato_fra="$row[box2] $row[box1]";
		$dato_til="$row[box4] $row[box3]";
*/
} 
#if ($dato_fra) $dato_fra=find_maaned_nr($dato_fra); 
#if ($dato_til) $dato_til=find_maaned_nr($dato_til); 
$submit = if_isset($submit,'forside');
if (strstr($rapportart, "ben post")) $rapportart="openpost";
if ($submit != 'ok') $submit='forside';
elseif ($rapportart) $submit=$rapportart;

#echo "$dato_fra,$dato_til,$konto_fra,$konto_til,$rapportart,'K'<br>";
$submit(if_isset($dato_fra), if_isset($dato_til), if_isset($konto_fra), if_isset($konto_til),$rapportart,'K');

?>
	
</html>

