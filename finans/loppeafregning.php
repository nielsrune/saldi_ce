<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- finans/loppeafregning.php --- Patch 4.0.3 --- 2021.10.01 ---
/// LICENSE
//
// This program is free software. You can redistribute it and / or
// modify it under thefrom kontoplan where regnskabsaar terms of the GNU General Public License (GPL)
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
// Copyright (c) 2016-2021 saldi.dk ApS
// -----------------------------------------------------------------------------------
// 20180621 PHR Summer tillægges moms hvis ordrelinjer er momsbelagt. Søg momsfri & momssats
// 20211020 PHR Comparing $gruppesum[$y] to $totalsum and regulating $gruppesum[$y] if diff less than 0.1 to avoid diff in ledger.

@session_start();
$s_id=session_id();
		
$modulnr=12;	
$title="loppeafregning";
$css="../css/standard.css";
		
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/forfaldsdag.php");

$kladde_id=if_isset($_GET['kladde_id']);
#echo "$kladde_id=if_isset($_GET[kladde_id])<br>";	
$fra=if_isset($_POST['fra']);
$til=if_isset($_POST['til']);
$vareprefix=if_isset($_POST['vareprefix']);
$varegruppe=if_isset($_POST['varegruppe']);
#$modkonto=if_isset($_POST['modkonto']);
$bilag=if_isset($_POST['bilag'])*1;
$provision=if_isset($_POST['provision']);
$kundedel=100-usdecimal($provision);

$x=0;
$q=db_select("select * from grupper where art = 'VG' order by kodenr",__FILE__ . " linje " . __LINE__);
while ($r=db_fetch_array($q)) {
	$v_gr[$x]=$r['kodenr'];
	$vg_bskr[$x]=$r['beskrivelse'];
	$vg_modkonto[$x]=0;
	$x++;
}

#echo "$kladde_id && $modkonto && $fra && $til && $vareprefix && $provision<br>";
if ($kladde_id && $fra && $til && $vareprefix && $provision && $varegruppe) {
	$qtxt="select varer.varenr,varer.gruppe,batch_salg.pris,batch_salg.antal,ordrelinjer.momssats,ordrelinjer.momsfri ";
	$qtxt.="from batch_salg,varer,ordrelinjer ";
	$qtxt.="where varer.varenr like '".$vareprefix."%' and ordrelinjer.id=batch_salg.linje_id ";
	if ($varegruppe) $qtxt.="and varer.gruppe='$varegruppe' "; 
	$qtxt.="and batch_salg.vare_id=varer.id and batch_salg.fakturadate >='".usdate($fra)."' "; 
	$qtxt.="and batch_salg.fakturadate <='".usdate($til)."' order by varer.gruppe,varer.varenr";
	$dd=date("Y-m-d");
	$x=0;
	$y=0;
	$varenr=array();
	$totalsum=0;
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		if (in_array($r['varenr'],$varenr)) {
			$sum[$x]+=$r['antal']*$r['pris'];
			$gruppesum[$y]+=$r['antal']*$r['pris'];
			if (!$r['momsfri']) {
				$sum[$x]+=$r['antal']*$r['pris']*$r['momssats']/100;
				$gruppesum[$y]+=$r['antal']*$r['pris']*$r['momssats']/100;
			}
		} else {
			$x++;
			$varenr[$x]=$r['varenr'];
			$konto[$x]=str_replace($vareprefix,'',$varenr[$x])*1;
			$sum[$x]=$r['antal']*$r['pris'];
			if (!$r['momsfri']) $sum[$x]+=$r['antal']*$r['pris']*$r['momssats']/100;
			$gruppe[$x]=$r['gruppe'];
			if ($x>1 && $gruppe[$x]!=$gruppe[$x-1]) $y++;
			$gruppesum[$y]+=$r['antal']*$r['pris'];
			if (!$r['momsfri']) $gruppesum[$y]+=$r['antal']*$r['pris']*$r['momssats']/100;
		}
	}
#	if ($x) $gruppesum[$y]+=$sum[$x];
	$y=0;
	for ($x=1;$x<=count($varenr);$x++) {
		$udbetales[$x]=afrund($sum[$x]/100*$kundedel,2);
		$totalsum+=$udbetales[$x];
#		$konto=str_replace($vareprefix,'',$varenr[$x]);
		$qtxt="insert into kassekladde (bilag,transdate,beskrivelse,d_type,debet,k_type,kredit,faktura,amount,kladde_id,valuta)";
		$qtxt.="  values ";
		$qtxt.="('$bilag','$dd','Afr: $konto[$x] $fra - $til','','0','D','$konto[$x]','','$udbetales[$x]','$kladde_id','0')";
#cho "$qtxt<br>";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
#		if ($x>1 && $gruppe[$x]!=$gruppe[$x-1]) {
#			for ($z=0;$z<count($v_gr);$z++) {
#				if ($v_gr[$z]==$gruppe[$x-1] && $gruppesum[$y]) {
#					$qtxt="insert into kassekladde (bilag,transdate,beskrivelse,d_type,debet,k_type,kredit,faktura,amount,kladde_id,valuta)";
#					$qtxt.="  values ";
#					$qtxt.="('$bilag','$dd','$afregning $fra - $til','F','$vg_modkonto[$z],'','0'','','$gruppesum[$y]','$kladde_id','0')";
#					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
#				}
#			}
#		}	
	}
	for ($z=0;$z<count($v_gr);$z++) {
		if ($v_gr[$z]==$gruppe[$x-1] && $gruppesum[$y]) {
			$gruppesum[$y]=afrund($gruppesum[$y]/100*$kundedel,2);
			if (abs($gruppesum[$y]-$totalsum) < 0.1) $gruppesum[$y] = $totalsum; #20211020
			$qtxt="insert into kassekladde (bilag,transdate,beskrivelse,d_type,debet,k_type,kredit,faktura,amount,kladde_id,valuta)";
			$qtxt.="  values ";
			$qtxt.="('$bilag','$dd','$afregning $fra - $til','F','$vg_modkonto[$z]','','0','','$gruppesum[$y]','$kladde_id','0')";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
/*	
	if ($totalsum) {
		$qtxt="insert into kassekladde (bilag,transdate,beskrivelse,d_type,debet,k_type,kredit,faktura,amount,kladde_id,valuta)";
		$qtxt.="  values ";
		$qtxt.="('$bilag','$dd','$afregning $fra - $til','','0','F','$modkonto','','$totalsum','$kladde_id','0')";
#cho "$qtxt<br>";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	}
*/	
	print count($varenr)." afregninger indsat i kasseklasse!<br><br>";
	print "<a href='kassekladde.php?kladde_id=$kladde_id'>Tilbage til kassekladde</a>";
} else {
	print "<form name='loppeafregning' action='loppeafregning.php?kladde_id=$kladde_id' method='post'>";
	print "<table><tbody>";
	print "<tr><td>Vareprefix</td><td><input type='text' name='vareprefix' value='$vareprefix'></td></tr>";
	print "<tr><td>Varegruppe</td><td><select name='varegruppe' value='$varegruppe'>";
	print "<option value=''></option>";
	for ($x=0;$x<count($v_gr);$x++) print "<option value='$v_gr[$x]'>$v_gr[$x] $vg_bskr[$x]</option>";
	print"</select></td></tr>";
	print "<tr><td>Provision</td><td><input type='text' name='provision' value='$provision'></td></tr>";
	print "<tr><td>Fra dato</td><td><input type='text' name='fra' value='$fra'></td></tr>";
	print "<tr><td>Til dato</td><td><input type='text' name='til' value='$til'></td></tr>";
	print "<tr><td>Bilag</td><td><input type='text' name='bilag' value='$bilag'></td></tr>";
#	print "<tr><td>Modkonto</td><td><input type='text' name='modkonto' value='$modkonto'></td></tr>";
	print "<tr><td colspan='2' style='text-align:center;'><input type='submit' name='loppeafregning' value='Dan liste'>";
	print "</form>";
}

?> 
