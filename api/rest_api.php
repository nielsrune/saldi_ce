<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ----------/api/rest_api.php---------lap 3.6.7---2017-01-06	-----
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af The Free Software Foundation; enten i version 2
// af denne licens eller en senere version efter eget valg
// Fra og med version 3.2.2 dog under iagttagelse af følgende:
// 
// Programmet må ikke uden forudgående skriftlig aftale anvendes
// i konkurrence med saldi.dk aps eller anden rettighedshaver til programmet.
// 
// Dette program er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
//
// En dansk oversaettelse af licensen kan laeses her:
// http://www.saldi.dk/dok/GNU_GPL_v2.html
//
// Copyright (c) 2016-2017 saldi.dk aps
// ----------------------------------------------------------------------

include("../includes/connect.php");
include("../includes/db_query.php");
include("../includes/std_func.php");

$db=NULL;
$brugernavn=NULL;
$db_skriv_id=NULL;
$webservice='on';

function fetch_from_table($select,$from,$where,$order_by,$limit) {
	global $db;
	global $brugernavn;
	global $webservice;
	
	$log=fopen("../temp/$db/rest_api.log","a");
	$allowed_tables=array('adresser','batch_kob','batch_salg','kassekladde','openpost','ordrer','ordrelinjer','transaktioner','varer','shop_ordrer','shop_varer');
	fwrite($log,__line__." Query: select ".$select." from ".$from." where ".$where." order by ".$order_by." limit ".$limit."\n");
	if (strpos($select,';') || strpos($from,';') || strpos($where,';') || strpos($order_by,';') || strpos($limit,';')) {
		fwrite($log,__line__." sql_injection attempt\n");
		fclose ($log);
		return 'sql_injection attempt';
		exit;
	}
	(strpos($from,','))?$q_tables=explode(',',$from):$q_tables[0]=$from;
		fwrite($log,count($q_tables)." $q_tables[0] \n");
	for ($i=0;$i<count($q_tables);$i++) {		
		if (in_array($q_tables[$i],$allowed_tables)) fwrite($log,__line__." legal tabel ($q_tables[$i]) \n");
		else {
			fwrite($log,__line__." illegal tabel ($q_tables[$i]) \n");
			fclose ($log);
			return "illegal tabel ($q_tables[$i])";
			exit;
		}
	}
	$query="select $select from $from";
	if ($where) $query.=" where $where";
	if ($order_by) $query.=" order by $order_by";
	if ($limit) $query.=" limit $limit";
	fwrite($log,__line__." Query: $query\n");
	$result = array();
	$x=0;
	$y=0;
	$q=db_select($query,__FILE__ . " linje " . __LINE__);
	while ($y < db_num_fields($q)) {
		$fieldName[$y] = db_field_name($q,$y); 
		$fieldType[$y] = db_field_type($q,$y);
		$result[$x][$y]=$fieldName[$y]."(".$fieldType[$y].")";
fwrite($log,__line__." result ".$result[$x][$y]."\n");

		$y++;
	}
	$x++;
  while($result[$x]=db_fetch_array($q)) $x++;
	fclose ($log);
	return $result;
} #endfunc fetch_from_table

function update_table($update,$set,$where) {
	global $db;
	global $brugernavn;
	global $webservice;
	
	$log=fopen("../temp/$db/rest_api.log","a");
	$allowed_tables=array('ordrer','shop_ordrer','ordrelinjer','varer','shop_varer','vare_lev','adresser','ansatte','shop_adresser','kladdeliste','kassekladde');
	fwrite($log,__line__." Query: update ".$update." set ".$set." where ".$where."\n");
	if (strpos($update,';') || strpos($set,';') || strpos($where,';')) {
		fwrite($log,__line__." sql_injection attempt\n");
		fclose ($log);
		return 'sql_injection attempt';
		exit;
	}
	if (in_array($update,$allowed_tables)) fwrite($log,__line__." legal tabel ($update) \n");
	else {
		fwrite($log,__line__." illegal tabel ($update) \n");
		fclose ($log);
		return "illegal tabel ($update)";
		exit;
	}
	(strpos($set,','))?$q_sets=explode(',',$set):$q_sets[0]=$set;
	for ($i=0;$i<count($q_sets);$i++) {
		list ($q_set_a,$q_set_b)=explode("=",$q_sets[$i]);
		if (strtolower(trim($q_set_a))=='id') {
			fwrite($log,__line__." illegal field ($q_sets[$i]) \n");
			fclose ($log);
			return "illegal field ($q_sets[$i])";
			exit;
	} else fwrite($log,__line__." legal field ($q_sets[$i]) \n");
	}
	$query="update $update set $set where $where";
#	if ($where) $query.=" where $where";
	fwrite($log,__line__." Query: $query\n");
	$result = db_modify($query,__FILE__ . " linje " . __LINE__);
	$x=0;
	$y=0;
	fclose ($log);
	return $result;
}

function insert_into_table($insert,$fields,$values) {
	global $db;
	global $brugernavn;
	global $webservice;
	
	$log=fopen("../temp/$db/rest_api.log","a");
	$allowed_tables=array('ordrer','shop_ordrer','ordrelinjer','varer','shop_varer','vare_lev','adresser','ansatte','shop_adresser','kladdeliste','kassekladde');
	fwrite($log,__line__." Query: insert into ".$insert." fields ".$fields." values ".$values."\n");
	if (strpos($insert,';') || strpos($fields,';') || strpos($values,';')) {
		fwrite($log,__line__." sql_injection attempt\n");
		fclose ($log);
		return 'sql_injection attempt';
		exit;
	}
	if (in_array($insert,$allowed_tables)) fwrite($log,__line__." legal tabel ($insert) \n");
	else {
		fwrite($log,__line__." illegal tabel ($insert) \n");
		fclose ($log);
		return "illegal tabel ($insert)";
		exit;
	}
	(strpos($fields,','))?$q_fieldss=explode(',',$fields):$q_fieldss[0]=$fields;
	for ($i=0;$i<count($q_fieldss);$i++) {
		if (strtolower(trim($q_fieldss[$i]))=='id') {
			fwrite($log,__line__." illegal field ($q_fieldss[$i]) \n");
			fclose ($log);
			return "illegal field ($q_fieldss[$i])";
			exit;
		} else fwrite($log,__line__." legal field ($q_fieldss[$i]) \n");
	}
	$query="insert into $insert ($fields) values ($values)";
	fwrite($log,__line__." Query: $query\n");
	$result = db_modify($query,__FILE__ . " linje " . __LINE__);
	$x=0;
	$y=0;
	fclose ($log);
	return $result;
}

function insert_shop_order($shop_ordre_id,$shop_addr_id,$firmanavn,$addr1,$addr2,$postnr,$bynavn,$land,$cvrnr,$ean,$institution,$tlf,$email,$ref,
	$kontakt,$lev_firmanavn,$lev_addr1,$lev_addr2,$lev_postnr,$lev_bynavn,$lev_land,$lev_tlf,$lev_email,$lev_kontakt,$betalingsbet,$betalingsdage,
	$ordredate,$lev_date,$momssats,$valuta,$valutakurs,$gruppe,$afd,$projekt,$ekstra1,$ekstra2,$ekstra3,$ekstra4,$ekstra5,$nettosum,$momssum) {

	global $db;
	global $brugernavn;
	global $webservice;
	
	#return "$nettosum,$momssum";
	
	$log=fopen("../temp/$db/rest_api.log","a");
	fwrite($log,__line__." Shop Ordre id: $shop_ordre_id\n");
	fwrite($log,__line__." Shop Kunde id: $shop_addr_id\n");
	
	if (!is_numeric($shop_ordre_id)) {
		fwrite($log,__line__." Ordernumber not numeric ($shop_ordre_id)\n");
		return "Ordernumber not numeric ($shop_ordre_id)";
		exit;
#	}	elseif (!is_numeric($shop_addr_id)) {
#		$qtxt=SELE into adresser()
#		fwrite($log,__line__." Customernumber not numeric ($shop_addr_id)\n");
#		return "Customernumber not numeric ($shop_addr_id)";
#		exit;
	}
	$tlf=str_replace(" ","",$tlf);
	$num_tlf=str_replace("+","",$tlf)*1;

	$shop_ordre_id*=1;
	$shop_addr_id*=1;

	if (!$shop_ordre_id || !is_integer($shop_ordre_id)) {
		fwrite($log,__line__." Illegal order id ($shop_ordre_id)\n");
		fclose ($log);
		return "Illegal order id ($shop_ordre_id)";
		exit;
	}
#	if (!$shop_addr_id || !is_integer($shop_addr_id)) {
#		fwrite($log,__line__." Illegal customer id ($shop_addr_id)\n");
#		fclose ($log);
#		return "Illegal customer id ($shop_addr_id)";
#		exit;
#	}
	$art='DO';
	if (!$firmanavn) {
		$firmanavn=$kontakt;
		$kontakt='';
	}

	if ($afd) {
		$kasse=0;
	} else $kasse=0;
#	exit;
	fwrite($log,__line__." select id from shop_ordrer where shop_id='$shop_ordre_id'\n");
	$r=db_fetch_array(db_select("select id,saldi_id from shop_ordrer where shop_id='$shop_ordre_id'",__FILE__ . " linje " . __LINE__));
	if ($r['id']) { 
		fwrite($log,__line__." Order id: $shop_ordre_id exists in saldi\n");
		fclose ($log);
#		return $r['saldi_id'];
		return "Order id: $shop_ordre_id exists in saldi";
		exit;
	}
	$qtxt="select saldi_id from shop_adresser where shop_id='$shop_addr_id'";
	fwrite($log,__line__." $qtxt\n");
	$r=db_fetch_array (db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$saldi_addr_id=$r['saldi_id'];
	fwrite($log,__line__." saldi_addr_id='$saldi_addr_id'\n");
	if (!$saldi_addr_id) {
		if ($tlf) {
			$qtxt="select id,kontonr from adresser where art = 'D' and ";
			$qtxt.="(lower(firmanavn)='".db_escape_string(strtolower($firmanavn))."' or lower(addr1)='".db_escape_string(strtolower($addr1))."') and "; 
			$qtxt.="(tlf='$tlf')";
			fwrite($log,__line__." $qtxt\n");
			if ($r=db_fetch_array (db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				$saldi_addr_id=$r['id']*1;
				$kontonr=$r['kontonr'];
				fwrite($log,__line__." saldi_addr_id $saldi_addr_id\n");
				fwrite($log,__line__." kontonr $kontonr\n");
			}
		}
		if ($saldi_addr_id && $shop_addr_id) {
			$qtxt="insert into shop_adresser(saldi_id,shop_id)values('$saldi_addr_id','$shop_addr_id')";
			fwrite($log,__line__." $qtxt\n");
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);  
		} else { #if ($shop_addr_id) {
			$qtxt="select id from adresser where art = 'D' and kontonr='$num_tlf'";
			fwrite($log,__line__." $qtxt\n");
			if ($tlf && !$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				$kontonr=$num_tlf;
				fwrite($log,__line__." kontonr $kontonr\n");
			} else { 
				$x=0;
				$qtxt="select kontonr from adresser where art = 'D' order by kontonr";
				fwrite($log,__line__." $qtxt\n");
				$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
				while($r=db_fetch_array($q)) {
					$ktonr[$x]=$r['kontonr'];
#					fwrite($log,__line__." kontonr $kontonr\n");
					$x++;
				}
				$kontonr=1000;
				while(in_array($kontonr,$ktonr)) $kontonr++;
					fwrite($log,__line__." kontonr $kontonr\n");
			}
			$qtxt="insert into adresser(kontonr,firmanavn,addr1,addr2,postnr,bynavn,land,cvrnr,email,tlf,gruppe,art,betalingsbet,betalingsdage,kontakt) ";
			$qtxt.="values ";
			$qtxt.="('$kontonr','".db_escape_string($firmanavn)."','".db_escape_string($addr1)."','".db_escape_string($addr2)."',";
			$qtxt.="'".db_escape_string($postnr)."','".db_escape_string($bynavn)."','".db_escape_string($land)."','".db_escape_string($cvrnr)."',";
			$qtxt.="'".db_escape_string($email)."','".db_escape_string($tlf)."','$gruppe','D','$betalingsbet','$betalingsdage',";
			$qtxt.="'".db_escape_string($kontakt)."')";
				fwrite($log,__line__." $qtxt\n");
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$r=db_fetch_array(db_select("select id from adresser where kontonr='$kontonr' and art = 'D'",__FILE__ . " linje " . __LINE__));
			$saldi_addr_id=$r['id'];
			if ($shop_addr_id) {
			fwrite($log,__line__." insert into shop_adresser(saldi_id,shop_id)values('$saldi_addr_id','$shop_addr_id')\n");
			db_modify("insert into shop_adresser(saldi_id,shop_id)values('$saldi_addr_id','$shop_addr_id')",__FILE__ . " linje " . __LINE__);  
		}
		}
	} else {
		fwrite($log,__line__." select kontonr from adresser where id = '$saldi_addr_id'\n");
		$r=db_fetch_array(db_select("select kontonr from adresser where id = '$saldi_addr_id'",__FILE__ . " linje " . __LINE__));
		$kontonr=$r['kontonr'];
		fwrite($log,__line__." kontonr $kontonr\n");
	}
	$qtxt="select ordrenr from ordrer where art='DO'";
	fwrite($log,__line__." $qtxt\n");
	$ordrenr=1;
	$q=db_select("$qtxt",__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		if ($ordrenr<=$r['ordrenr']) $ordrenr=$r['ordrenr']+1;
	}
	$projektnr=0;
	$qtxt="select box1 from grupper where art='DG' and kodenr = '$gruppe'";
	fwrite($log,__line__." $qtxt\n");
	$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
	$momsgruppe=str_replace('S','',$r['box1']);
	fwrite($log,__line__." $qtxt\n");
	$qtxt="select box2 from grupper where art='SM' and kodenr = '$momsgruppe'";
	$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
	$momssats=$r['box2']*1;
	if (!$valuta)$valuta='DKK';
	if ($valuta=='DKK') {
		$valutakurs=100;
	} else {
		$qtxt="select box2 from grupper where art='VK' and box1 = '$valuta'";
		fwrite($log,__line__." $qtxt\n");
		if ($r=db_fetch_array(db_modify($qtxt,__FILE__ . " linje " . __LINE__))) $valutakurs=$r['box2']*1;
		else $valutakurs=100;
	}
	$qtxt="insert into ordrer
		(ordrenr,konto_id,kontonr,firmanavn,addr1,addr2,postnr,bynavn,kontakt,email,art,projekt,momssats,betalingsbet,betalingsdage,status,
		ordredate,fakturadate,valuta,valutakurs,afd,ref,hvem,felt_5,kundeordnr,sum,moms) 
			values 
		('$ordrenr','$saldi_addr_id','$kontonr','".db_escape_string($firmanavn)."','".db_escape_string($addr1)."','".db_escape_string($addr2)."',
		'".db_escape_string($postnr)."','".db_escape_string($bynavn)."','".db_escape_string($kontakt)."','".db_escape_string($email)."','$art','$projektnr','$momssats',
		'$betalingsbet','$betalingsdage','0','$ordredate','$ordredate','$valuta','$valutakurs','$afd','$ref','','$kasse','$shop_ordre_id','$nettosum','$momssum')";
	fwrite($log,__line__." $qtxt\n");
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	$qtxt="select id from ordrer where ordrenr='$ordrenr' and kontonr='$kontonr'";
	fwrite($log,__line__." $qtxt\n");
	$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
	$saldi_ordre_id=$r['id'];
	fwrite($log,__line__."saldi_ ID $saldi_ordre_id\n");
	$qtxt="insert into shop_ordrer(saldi_id,shop_id)values('$saldi_ordre_id','$shop_ordre_id')";
	fwrite($log,__line__." ".$qtxt."\n");
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);  
	fclose ($log);
	return $saldi_ordre_id;
}

function insert_shop_orderline($ordre_id,$shop_vare_id,$shop_varenr,$antal,$beskrivelse,$pris,$momsfri,$rabat,$lager,$stregkode,$shop_variant) {
	global $db;
	global $brugernavn;
	global $webservice;
	
	$lager*=1;
	$log=fopen("../temp/$db/rest_api.log","a");
	fwrite($log,__line__." ".date("Y-m-d H:i:s")."\n");
	fwrite($log,__line__." insert_shop_orderline($ordre_id,$shop_vare_id,$shop_varenr,$antal,$beskrivelse,$pris,$momsfri,$rabat,$lager,$stregkode,$shop_variant)\n");
	if ($ordre_id && is_numeric($ordre_id)) {
		$qtxt="select status from ordrer where id='$ordre_id'";
		fwrite($log,__line__." ".$qtxt."\n");
		$r=db_fetch_array (db_select($qtxt,__FILE__ . " linje " . __LINE__));
		if ($r['status'] > 2) {
			fwrite($log,__line__." Order ID $ordre_id allready invoiced\n");
			fclose ($log);
			return "Order ID $ordre_id allready invoiced";
			exit;
		}
	} else {
		fwrite($log,__line__." Invalid order ID $ordre_id\n");
		fclose ($log);
		return "Invalid order ID $ordre_id";
		exit;
	}
	fwrite($log,__line__." $ordre_id status $r[status]\n");
	if ($shop_variant) {
		$qtxt="select saldi_variant from shop_varer where shop_variant = '$shop_variant'";
		fwrite($log,__line__." $qtxt\n");
		$r=db_fetch_array (db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$vare_id=$r['saldi_id'];
		$saldi_variant=$r['saldi_variant'];
	} elseif ($shop_vare_id) {
		$qtxt="select saldi_id from shop_varer where shop_id='$shop_vare_id'";
		fwrite($log,__line__." $qtxt\n");
		$r=db_fetch_array (db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$vare_id=$r['saldi_id'];
	} elseif ($shop_varenr) {
		$qtxt="select id from varer where varenr='$shop_varenr'";
		fwrite($log,__line__." $qtxt\n");
		$r=db_fetch_array (db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$vare_id=$r['id'];
	} else {
		fwrite($log,__line__." missing Item ID and Item number\n");
		fclose($log);
		return "missing Item ID and Item number";
		exit;
	}
	fwrite($log,__line__." Vare ID $vare_id\n");
	fwrite($log,__line__." Stregkode $stregkode\n");
	include("../includes/ordrefunc.php");
	fwrite($log,__line__." Samlevare = $samlevare\n");
	if ($vare_id) {
		$qtxt="select varenr,samlevare from varer where id='$vare_id'";
		fwrite($log,__line__." $qtxt\n");
		$r=db_fetch_array (db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$samlevare=$r['samlevare'];
		fwrite($log,__line__." Vare_id $vare_id - Samlevare $samlevare\n");
	} else {
		if ($stregkode) {
			$qtxt="select id,samlevare from varer where stregkode='$stregkode'";
		fwrite($log,__line__." $qtxt\n");
			if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$vare_id=$r['id'];
		$samlevare=$r['samlevare'];
#				$shop_variant=0;
				fwrite($log,__line__." Vare_id $vare_id - Samlevare $samlevare\n");
			}
		}
		if (!$vare_id && $stregkode) {
			$qtxt="select id,vare_id from variant_varer where variant_stregkode='$stregkode'";
			fwrite($log,__line__." $qtxt\n");
			if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				$vare_id=$r['vare_id'];
				$varenr=$stregkode;
				$samlevare=0;
				$saldi_variant=$r['id'];
				fwrite($log,__line__." Vare_id $vare_id - Shop variant $shop_variant - Saldi variant $saldi_variant\n");
			}
		}
		if (!$vare_id) {
			if ($shop_varenr) $qtxt="select id,samlevare from varer where varenr='$shop_varenr'";
			else $qtxt="select id,samlevare from varer where varenr='$shop_vare_id'";
			fwrite($log,__line__." $qtxt\n");
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$vare_id=$r['id'];
			$samlevare=$r['samlevare'];
		fwrite($log,__line__." Vare_id $vare_id - Samlevare $samlevare\n");
		}	
/*
		if (!$vare_id) {
			$qtxt="select id,samlevare from varer where beskrivelse='$beskrivelse'";
			fwrite($log,__line__." $qtxt\n");
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$vare_id=$r['id'];
			$samlevare=$r['samlevare'];
		fwrite($log,__line__." Vare_id $vare_id - Samlevare $samlevare\n");
		}	
*/
		fwrite($log,__line__." Vare_id $vare_id - Shop_vare_id $shop_vare_id - Shop_varenr $shop_varenr - Beskr. $beskrivelse\n");
		if (!$vare_id && ($shop_vare_id || $shop_varenr) && $beskrivelse) {
			($shop_varenr)?$varenr=$shop_varenr:$varenr=$shop_vare_id;
			if ($shop_kostpris) $kostpris=$shop_kostpris;
			elseif ($shop_dg) $kostpris=$pris-$pris/100*$shop_dg;
			else $kostpris=0;
			$qtxt="insert into varer(varenr,beskrivelse,salgspris,kostpris,gruppe)values('$varenr','$beskrivelse (INDSAT FRA SHOP)','$pris','$kostpris','1')";
			fwrite($log,__line__." $qtxt\n");
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt="select id from varer where varenr='$varenr'";
			fwrite($log,__line__." $qtxt\n");
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$vare_id=$r['id'];
			$samlevare='';
			$smlv='on';
		} elseif ($vare_id) fwrite($log,__line__." Vare $varenr eksisterer\n");
		if ($vare_id && $shop_vare_id) {
			$qtxt="insert into shop_varer(saldi_id,shop_id,saldi_variant,shop_variant)values('$vare_id','$shop_vare_id','$saldi_variant','$shop_variant')";
			fwrite($log,__line__." $qtxt\n");
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);  
		}
	}
	$qtxt="update varer set publiceret='on' where id = '$vare_id'";
	fwrite($log,__line__." $qtxt\n");
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	fwrite($log,__line__." Samlevare = $samlevare\n");
	if ($samlevare && $samlevare == 'on') {
		fwrite($log,__line__." Samlevare = $samlevare\n");
		fwrite($log,__line__." opret_saet($ordre_id,$vare_id,$pris*1.25,25,$antal,on)\n");
		opret_saet($ordre_id,$vare_id,$pris*1.25,25,$antal,on);
	} else {
		fwrite ($log,__line__, "Antal: $antal\n");
		fwrite ($log,__line__, "Beskrivelse: $beskrivelse\n");
		fwrite ($log,__line__, "Lager: $lager\n");
		fwrite($log,__line__." opret_ordrelinje($ordre_id,$vare_id,$varenr,$antal,$beskrivelse,$pris,$rabat,'100','DO',$momsfri,$posnr,'0','','','','0','','','','','',$lager,".__LINE__."\n");
		$linje_id=opret_ordrelinje($ordre_id,$vare_id,$varenr,$antal,$beskrivelse,$pris,$rabat,'100','DO',$momsfri,$posnr,'0','','','','0','','','','','',$lager,__LINE__);
	}
	$ordresum+=$pris*$antal[$x];
	fwrite($log,__line__." Linje ID $linje_id oprettet\n");
	fclose ($log);
	return $linje_id;
}
function fakturer_ordre($saldi_id,$udskriv_til) {
	global $db;
	global $brugernavn;
	global $webservice;
	#return "$nettosum,$momssum";

	include("../includes/ordrefunc.php");
	
	$log=fopen("../temp/$db/rest_api.log","a");
	fwrite($log,__line__." ".date("Y-m-d H:i:s")."\n");

	$qtxt="select * from ordrelinjer where ordre_id='$saldi_id'";
	fwrite($log,__line__." $qtxt\n");
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	$linjesum=0;
	while ($r=db_fetch_array($q)) {
		$linjesum+=$r['antal']*$r['pris']-($r['antal']*$r['pris']*$r['rabat']/100);
		fwrite($log,__line__." $linjesum+=$r[antal]*$r[pris]-($r[antal]*$r[pris]*$r[rabat]/100)\n");
	}
	$qtxt="select sum from ordrer where id='$saldi_id'";
	fwrite($log,__line__." $qtxt\n");
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$ordresum=$r['sum'];
	$r=db_fetch_array(db_select("select * from ordrer where id = '$saldi_id'",__FILE__ . " linje " . __LINE__));
	$betalt=$r['sum']+$r['moms'];
	$korttype=$r['felt_1'];
	$qtxt="update ordrer set fakturadate=ordredate where id='$saldi_id'";
	fwrite($log,__line__." $qtxt\n");
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);	
	$qtxt="update ordrelinjer set leveres = antal where ordre_id='$saldi_id'";
	fwrite($log,__line__." $qtxt\n");
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);	
	$svar=levering($saldi_id,'on',NULL,'on');
	if ($svar=='OK') $svar=bogfor($saldi_id,'on');
	#db_modify("insert into pos_betalinger(ordre_id,betalingstype,amount) values ('$saldi_id','$korttype','$betalt')",__FILE__ . " linje " . __LINE__);
	fwrite($log,__line__." Ordre ID $saldi_id faktureret ($svar)\n");
	fclose ($log);
	return($saldi_id); 

}

function access_check(){
	global $sqhost;
	global $squser;
	global $sqpass;
	global $db;
	global $brugernavn;
	global $webservice;

	if (isset($_GET['db'])) {
		$db=$_GET['db'];
		$log=fopen("../temp/$db/rest_api.log","a");
		list($master,$db_skriv_id)=explode('_',$db);
	}	else {
		fwrite($log,__line__." Missing db\n");
		fclose($log);
		return 'missing db';
		exit;
	}
	$ip=$_SERVER['REMOTE_ADDR'];
	fwrite($log,date("Y-m-d H:i:s")."\n");
	fwrite($log,__line__." ip: $ip\n");
		$connection = db_connect ("$sqhost", "$squser", "$sqpass", "$db", __FILE__ . " linje " . __LINE__);
	if (!$connection) {
		fwrite($log,__line__." Unable to connect to $db\n");
		fclose($log);
		return( "Unable to connect to $db");
		exit;
	}
	if (isset($_GET['saldiuser'])) {
		$brugernavn=$_GET['saldiuser'];
		fwrite($log,__line__." saldibruger: v\n");
	}	else {
		fwrite($log,__line__." Missing saldiuser\n");
		fclose($log);
		return 'Missing saldiuser';
		exit;
	}
	$q=db_select("select * from grupper where art = 'API' and kodenr = '1'",__FILE__ . " linje " . __LINE__);
	$r = db_fetch_array(db_select("select * from grupper where art = 'API' and kodenr = '1'",__FILE__ . " linje " . __LINE__));
	$api_key=trim($r['box1']);
	if (strpos($r['box2'],',')) $ip_list=explode(',',trim($r['box2']));
	else $ip_list[0]=trim($r['box2']);
	#echo $r['box2']."<br>";
		if ($api_key != $_GET['key']) {
		$log=fopen("../temp/$db/rest_api.log","a");
		
		fwrite($log,__line__." Access denied (key) $api_key != $_GET[key]\n");
		return "Access denied (key) $api_key != $_GET[key]";
	} elseif (!in_array($ip,$ip_list) && !in_array('*',$ip_list)) {
		fwrite($log,__line__." Access denied (key) ($ip) != $r[box2]\n");
		return "Access denied ($ip) $r[box2]";
	} else {
		fwrite($log,__line__." Access granted $ip\n");
		return 'OK';
	}
	fclose ($log);
	exit;
}

$possible_url = array("fetch_from_table,update_table,insert_shop_order,insert_shop_orderline");
$value = "An error has occurred";
if (isset($_GET['action'])){# && in_array($_GET['action'], $possible_url)){
	$action=trim($_GET['action']);
	$svar = access_check();
	if ($svar == 'OK') {#exit(json_encode($value));
		$log=fopen("../temp/$db/rest_api.log","a");
		fwrite($log,__line__." Action:".$_GET['action']."\n");
		fclose ($log);
		if ($action=='fetch_from_table') {
			$value = fetch_from_table($_GET['select'],$_GET['from'],str_replace("**","%",$_GET['where']),$_GET['order_by'],$_GET['limit']);
##############################################
		} elseif ($action=='update_table') {
			$value = update_table($_GET['update'],$_GET['set'],str_replace("**","%",$_GET['where']));
##############################################
		}	elseif ($action=='insert_into_table') {
			$value = insert_into_table($_GET['insert'],$_GET['fields'],$_GET['values']);
##############################################
		}	elseif ($action=='insert_shop_order') {
			$shop_ordre_id=if_isset($_GET['shop_ordre_id']);
			$shop_addr_id=if_isset($_GET['shop_addr_id']);
			$firmanavn=if_isset($_GET['firmanavn']);
			$addr1=if_isset($_GET['addr1']);
			$addr2=if_isset($_GET['addr2']);
			$postnr=if_isset($_GET['postnr']);
			$bynavn=if_isset($_GET['bynavn']);
			$land=if_isset($_GET['land']);
			$cvr=if_isset($_GET['cvr']);
			$ean=if_isset($_GET['ean']);
			$institution=if_isset($_GET['institution']);
			$tlf=if_isset($_GET['tlf']);
			$email=if_isset($_GET['email']);
			$ref=if_isset($_GET['ref']);
			$kontakt=if_isset($_GET['kontakt']);
			$lev_firmanavn=if_isset($_GET['lev_firmanavn']);
			$lev_addr1=if_isset($_GET['lev_addr1']);
			$lev_addr2=if_isset($_GET['lev_addr2']);
			$lev_postnr=if_isset($_GET['lev_postnr']);
			$lev_bynavn=if_isset($_GET['lev_bynavn']);
			$lev_land=if_isset($_GET['lev_land']);
			$lev_tlf=if_isset($_GET['lev_tlf']);
			$lev_email=if_isset($_GET['lev_email']);
			$lev_kontakt=if_isset($_GET['lev_kontakt']);
			$betalingsbet=if_isset($_GET['betalingsbet']);
			$betalingsdage=if_isset($_GET['betalingsdage']);
			$ordredate=if_isset($_GET['ordredate']);
			$lev_date=if_isset($_GET['lev_date']);
			$momssats=if_isset($_GET['momssats']);
			$valuta=if_isset($_GET['valuta']);
			$valutakurs=if_isset($_GET['valutakurs']);
			$gruppe=if_isset($_GET['gruppe']);
			$afd=if_isset($_GET['afd']);
			$nettosum=if_isset($_GET['nettosum'])*1;
			$momssum=if_isset($_GET['momssum'])*1;
			$projekt=if_isset($_GET['projekt']);
			$ekstra1=if_isset($_GET['ekstra1']);
			$ekstra2=if_isset($_GET['ekstra2']);
			$ekstra3=if_isset($_GET['ekstra3']);
			$ekstra4=if_isset($_GET['ekstra4']);
			$ekstra5=if_isset($_GET['ekstra5']);
			$params="$shop_ordre_id,$shop_addr_id,$firmanavn,$addr1,$addr2,$postnr,$bynavn,$land,$cvr,$ean,$institution,$tlf,$email,";
			$params.="$ref,$kontakt,$lev_firmanavn,$lev_addr1,$lev_addr2,$lev_postnr,$lev_bynavn,$lev_land,$lev_tlf,$lev_email,$lev_kontakt,";
			$params.="$betalingsbet,$betalingsdage,$ordredate,$lev_date,$momssats,$valuta,$valutakurs,$gruppe,$afd,$projekt,$ekstra1,$ekstra2,";
			$params.="$ekstra3,$ekstra4,$ekstra5,$nettosum,$momssum";
			$log=fopen("../temp/$db/rest_api.log","a");
			fwrite($log,__line__." insert_shop_order($params)\n");
			fclose ($log);
			$value = insert_shop_order($shop_ordre_id,$shop_addr_id,$firmanavn,$addr1,$addr2,$postnr,$bynavn,$land,$cvr,$ean,$institution,$tlf,$email,
				$ref,$kontakt,$lev_firmanavn,$lev_addr1,$lev_addr2,$lev_postnr,$lev_bynavn,$lev_land,$lev_tlf,$lev_email,$lev_kontakt,$betalingsbet,
				$betalingsdage,$ordredate,$lev_date,$momssats,$valuta,$valutakurs,$gruppe,$afd,$projekt,$ekstra1,$ekstra2,$ekstra3,$ekstra4,$ekstra5,
				$nettosum,$momssum);
##############################################
		} elseif ($action=='insert_shop_orderline') {
			$log=fopen("../temp/$db/rest_api.log","a");
			$ordre_id=if_isset($_GET['saldi_ordre_id']);
			fwrite($log,__line__." ordre_id >$ordre_id<\n");
			$vare_id=if_isset($_GET['vare_id']);
			$varenr=if_isset($_GET['varenr']);
			$stregkode=if_isset($_GET['stregkode']);
			$antal=if_isset($_GET['antal']);
			fwrite($log,__line__." antal >$antal<\n");
			$beskrivelse=if_isset($_GET['beskrivelse']);
			$pris=if_isset($_GET['pris']);
			$momsfri=if_isset($_GET['momsfri']);
			$rabat=if_isset($_GET['rabat']);
			$lager=if_isset($_GET['lager']);
			$variant=if_isset($_GET['variant']);
			$params="$ordre_id,$vare_id,$varenr,$antal,$beskrivelse,$pris,$momsfri,$rabat,$lager,$stregkode,$variant";
			fwrite($log,__line__." insert_shop_ordeline($params)\n");
			fclose ($log);
			$value = insert_shop_orderline($ordre_id,$vare_id,$varenr,$antal,$beskrivelse,$pris,$momsfri,$rabat,$lager,$stregkode,$variant);
##############################################
		} elseif ($action=='fakturer_ordre') {
			$ordre_id=if_isset($_GET['saldi_ordre_id']);
			$udskriv_til=if_isset($_GET['udskriv_til']);
			$value = fakturer_ordre($ordre_id,$udskriv_til);
##############################################
		} else $value="Illegal action ($action)";
	} else $value=$svar;
} else $value=$_GET['action'];

//return JSON array
exit(json_encode($value));
?> 
