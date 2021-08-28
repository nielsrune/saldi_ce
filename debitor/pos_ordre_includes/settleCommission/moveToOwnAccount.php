<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// -- debitor/pos_ordre_includes/settleCommission/moveToOwnAccount.php- patch 3.9.9 -- 2021-01-10 --
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
// ----------------------------------------------------------------------

$minDate=$fakturadate[0];
$a=count($fakturadate)-1;
$maxDate=$fakturadate[$a];
$coAc=array();

if ($minDate < '2021-01-01') $minDate = '2021-01-01';

if ($commissionAccountNew) {
	$coAc[0] = $commissionAccountNew;
	$itNo[0] = 'kn%';
	if ($commissionAccountUsed) {
		$coAc[1] = $commissionAccountUsed;
		$itNo[1] = 'kb%';
	}
} elseif ($commissionAccountUsed) {
	$coAc[0] = $commissionAccountUsed;
	$itNo[0] = 'kb%';
}

for ($co=0;$co<count($coAc);$co++) { 
	$c=0;
	$cItemId=$cGroup=$itemId=array();

	$qtxt = "select moms from kontoplan where kontonr = '$coAc[$co]' and regnskabsaar <= '$regnaar' ";
	$qtxt.= "order by regnskabsaar desc limit 1";
#cho __line__." $qtxt<br>";	
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	if ($toVatCode=substr($r['moms'],1)) {
		$qtxt = "select box1,box2 from grupper where art = 'SM' and kodenr = '$toVatCode' ";
#cho __line__." $qtxt<br>";	
		$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$toVatAccount = $r['box1']*1;
		$toVatPercent  = $r['box2']*1;
	} else $toVatAccount=$toVatPercent=0;

	$qtxt = "select distinct(gruppe) as cgroup from varer where provision > '0' and varenr like '$itNo[$co]'";
#cho __line__." $qtxt<br>";	
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$cGroup[$c]=$r['cgroup'];
		$c++;
	}
	$v=0;
	$qtxt = "select distinct(ordrelinjer.vare_id) from ordrelinjer,ordrer,pos_betalinger where ";
	$qtxt.= "(ordrer.art like 'D%' or ordrer.art = 'PO') and ordrer.fakturadate >= '$minDate' and ordrer.fakturadate <= '$maxDate' ";
	$qtxt.= "and ordrelinjer.varenr like '$itNo[$co]' and ordrelinjer.kostpris > '0' ";
	$qtxt.= "and (ordrer.report_number = '0' or ordrer.report_number = '$reportNumber') and ordrelinjer.ordre_id = ordrer.id ";
	$qtxt.= "and pos_betalinger.ordre_id = ordrer.id and ordrer.felt_5 = '$kasse' order by ordrelinjer.vare_id";
#cho __line__." $qtxt<br>";	
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$itemId[$v]=$r['vare_id'];
#cho __line__." $itemId[$v]<br>";	
		$v++;
	}
	
	for ($c=0;$c<count($cGroup);$c++) {
		$v=0;
		$qtxt = "select id, provision from varer where gruppe = '$cGroup[$c]' and provision > '0' and varenr like '$itNo[$co]' order by id";
#cho __line__." $qtxt<br>";	
		$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
		while ($r=db_fetch_array($q)) {
			if (in_array($r['id'],$itemId)) {
				$cItemId[$c][$v]=$r['id'];
#cho __line__." ". $cItemId[$c][$v] ."<br>";	
				$v++;
			}
		}
	}
	for ($c = 0;$c < count($cGroup);$c++) {
		$commission = $commissionVat = 0;
		for ($v=0;$v<count($cItemId[$c]);$v++) {
			$qtxt = "select ordrelinjer.pris,ordrelinjer.antal,ordrelinjer.rabat,ordrelinjer.kostpris,ordrelinjer.momssats ";
			$qtxt.= "from ordrelinjer,ordrer,pos_betalinger where ";
			$qtxt.= "(ordrer.art like 'D%' or ordrer.art = 'PO') and ordrer.fakturadate >= '$minDate' and ordrer.fakturadate <= '$maxDate' ";
			$qtxt.= "and ordrelinjer.pris != '0' and ordrer.status = '3' and ordrelinjer.ordre_id = ordrer.id ";
			$qtxt.= "and pos_betalinger.ordre_id = ordrer.id and ordrelinjer.vare_id='". $cItemId[$c][$v] ."' and ordrer.felt_5 = '$kasse'";
#cho __line__." $qtxt<br>";	
			$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
			while ($r=db_fetch_array($q)) {
				$linePrice   = $r['pris']*$r['antal'] - ($r['pris']*$r['antal']*$r['rabat']/100);
#cho __line__." $r[rabat]<br>";	
				$lineVat     = $linePrice * $r['momssats']/100;
				$cost        = $r['kostpris'] * $r['antal'];
#cho __line__." $linePrice $cost<br>";	
				$costVat    +=afrund( $cost  * $r['momssats']/100,2);
				$commission += afrund($linePrice-$cost,2); 
// echo __line__." $commission<br>";	
#				$commissionVat += afrund($costVat,2); 
			}
		}
#cho __line__." $commission $commissionVat<br>";	
		$qtxt = "select box4 from grupper where art = 'VG' and kodenr = '$cGroup[$c]'";
#cho __line__." $qtxt<br>";	
		$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$fromAccount=$r['box4'];
		$qtxt = "select moms from kontoplan where kontonr = '$fromAccount' and regnskabsaar <= '$regnaar' ";
		$qtxt.= "order by regnskabsaar desc limit 1";
#cho __line__." $qtxt<br>";	
		$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		if ($fromVatCode=substr($r['moms'],1)) {
			$qtxt = "select box1,box2 from grupper where art = 'SM' and kodenr = '$fromVatCode' ";
			$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$fromVatAccount = $r['box1']*1;
			$fromVatPercent  = $r['box2']*1;
		} else $fromVatAccount=$fromVatPercent=0;
		if ($commission && $fromVatPercent) {
#			if ($costVat) $fromVat = afrund($commission * $fromVatPercent / (100+$fromVatPercent),2);
			$fromVat = afrund($commission * $fromVatPercent / 100,2);
echo __line__." $costVat $commission CV $fromVat<br>";
			$debet=$kredit=0;
			($fromVat > 0)?$debet=$fromVat:$kredit=abs($fromVat);
			$qtxt="insert into transaktioner (bilag,transdate,beskrivelse,kontonr,faktura,debet,kredit,kladde_id,afd,logdate,logtime,";
			$qtxt.="projekt,ansat,ordre_id,kasse_nr,report_number,moms)";
			$qtxt.=" values ";
			$qtxt.="('0','$dd','Moms af kommisionssalg, Kasse $kasse','$fromVatAccount','0','$debet','$kredit',0,'$afd','$dd','$logtime','',";
			$qtxt.="'$ansat_id','0','$kasse','$reportNumber','0')";
echo __line__." $qtxt<br>";	
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		} else $commissionVat = 0;
		if ($commission) {
			$debet=$kredit=0;
			($commission)?$debet=$commission:$kredit=abs($commission);
			$qtxt="insert into transaktioner (bilag,transdate,beskrivelse,kontonr,faktura,debet,kredit,kladde_id,afd,logdate,logtime,";
			$qtxt.="projekt,ansat,ordre_id,kasse_nr,report_number,moms)";
			$qtxt.=" values ";
			$qtxt.="('0','$dd','Kommisionssalg, Kasse $kasse','$fromAccount','0','$debet','$kredit',0,'$afd','$dd','$logtime','',";
			$qtxt.="'$ansat_id','0','$kasse','$reportNumber','$commissionVat')";
echo __line__." $qtxt<br>";	
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
		if ($commission && $toVatPercent) {
			if ($fromVatPercent) $toVat = afrund($commission * $toVatPercent / 100,2);
			else $toVat = afrund($commission * $toVatPercent / (100+$toVatPercent),2);
echo __line__." $costVat $commission CV $fromVat<br>";
			$debet=$kredit=0;
			($toVat > 0)?$kredit=$toVat:$debet=abs($toVat);
			$qtxt="insert into transaktioner (bilag,transdate,beskrivelse,kontonr,faktura,debet,kredit,kladde_id,afd,logdate,logtime,";
			$qtxt.="projekt,ansat,ordre_id,kasse_nr,report_number,moms)";
			$qtxt.=" values ";
			$qtxt.="('0','$dd','Moms af kommisionssalg, Kasse $kasse','$toVatAccount','0','$debet','$kredit',0,'$afd','$dd','$logtime','',";
			$qtxt.="'$ansat_id','0','$kasse','$reportNumber','0')";
echo __line__." $qtxt<br>";	
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		} else $toVat = 0;
		if ($commission) {
			if (!$fromVatPercent) $commission-= $toVat;
 			$debet=$kredit=0;
			($commission > 0)?$kredit=$commission:$debet=abs($commission);
			if ($debet) $toVat *= -1;
			$qtxt="insert into transaktioner (bilag,transdate,beskrivelse,kontonr,faktura,debet,kredit,kladde_id,afd,logdate,logtime,";
			$qtxt.="projekt,ansat,ordre_id,kasse_nr,report_number,moms)";
			$qtxt.=" values ";
			$qtxt.="('0','$dd','Kommisionssalg, Kasse $kasse','$coAc[$co]','0','$debet','$kredit',0,'$afd','$dd','$logtime',";
			$qtxt.="'','$ansat_id','0','$kasse','$reportNumber','$toVat')";
echo __line__." $qtxt<br>";	
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
}
#xit;
?> 
