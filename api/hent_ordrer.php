<?php
// ------------- api/hent_ordrer.php ---------- lap 3.5.6----2015.06.08-------
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af The Free Software Foundation; enten i version 2
// af denne licens eller en senere version efter eget valg
// dog under iagttagelse af følgende:
// 
// Programmet må ikke uden forudgående skriftlig aftale anvendes
// i konkurrence med DANOSOFT ApS eller anden rettighedshaver til programmet.
//
// Dette program er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
//
// En dansk oversaettelse af licensen kan laeses her:
// http://www.saldi.dk/dok/GNU_GPL_v2.html
//
// Copyright (c) 2004-2015 DANOSOFT ApS
// ----------------------------------------------------------------------

@session_start();
$s_id=session_id();

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/ordrefunc.php");

(isset($_GET['shop_db']))?$shop_db=$_GET['shop_db']:$shop_db=NULL;
(isset($_SERVER['HTTP_REFERER']))?list($shopurl,$null)=explode("/hent_ordrer.php",$_SERVER['HTTP_REFERER']):$shopurl=NULL;

$r=db_fetch_array(db_select("select box2 from grupper where art='DIV' and kodenr='5'",__FILE__ . " linje " . __LINE__));
#$shopvars=explode("&",$r['box2']);
$y=0;
/*
for ($x=0;$x<count($shopvars);$x++) {
	if (strpos($shopvars[$x],"=")) {
		list($shopvar[$y],$shopvalue[$y])=explode("=",$shopvars[$x]);
		
		$y++;
	}
}
*/
(strpos($r['box2'],'opdat_status=1'))?$opdat_status=1:$opdat_status=0;
(strpos($r['box2'],'shop_fakt=1'))?$shop_fakt=1:$shop_fakt=0;
(strpos($r['box2'],'betaling=kort'))?$kortbetaling=1:$kortbetaling=0;
(strpos($r['box2'],'betaling=kort'))?$kortbetaling=1:$kortbetaling=0;
($kortbetaling)?$betalingsbet='betalingskort':$betalingsbet='netto+8';


$prefix=$_GET['prefix'];
$nye_ordrer=$_GET['nye_ordrer'];
$afd='4';
$kasse='4';
$ref='internet';
$hvem='internet';
$dg=52;
$art='DO';
$gruppe=1;

$ordreliste=explode(',',$nye_ordrer);
$lockfil="../api/$db.lock";
$x=0;
if (file_exists($lockfil)) {
	if ($fp=fopen($lockfil,"r")) { 
		$a=fgets($fp);
		fclose($fp);
		if (date("U")-$a>3) unlink($lockfil);
	}
}
while (file_exists($lockfil)) {
	sleep(2);
	$x++;
	if ($x>10) {
		echo "Importfejl prøv igen senere<bt>";
		exit;
	}
}
$fp=fopen($lockfil,"w");
fwrite($fp,date("U"));
fclose($fp);
	for ($x=0;$x<count($ordreliste);$x++) {
	transaktion('begin');
	$ordre_id[$x]=overfoer_data($shopurl,$ordreliste[$x]);
	if ($shop_fakt && $ordre_id[$x]) {
		fakturer_ordre($ordre_id[$x]);
	}
	if ($ordre_id[$x]) {
		#echo "OID ".$ordre_id[$x]."<br>";
	}
	transaktion('commit');
	$q=db_select("select * from ordrelinjer where varenr='Levering' and pris ='0.000' and ordre_id='$ordre_id[$x]'",__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		if (substr($r['beskrivelse'],0,3)=='FRI') {
			$qtxt="update ordrelinjer set kostpris='450' where id='$r[id]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);	
		}
	}

#	exit;
#	break 1;
}
unlink($lockfil);
unlink($filnavn);
$alerttxt=count($ordreliste)." ordrer importeret fra shop";
if ($shopurl && $opdat_status=='1') {
#cho "opdaterer";
#	print "<meta http-equiv=\"refresh\" content=\"0;URL=$shopurl&prefix=$prefix&ordreliste=$nye_ordrer&status=2\">";
}
print "<body onload=\"window.close();\">";
// #xit;

function overfoer_data($shopurl,$shop_ordre_id){
	global $afd;
	global $art;
	global $db;
	global $dg;
	global $charset;
	global $encoding;
	global $gruppe;
	global $hvem;
	global $kasse;
	global $momssats; #Vigtig!
	global $prefix;
	global $ref;

	$filnavn=trim($shopurl)."/".$prefix."_".trim($shop_ordre_id);
#cho "$filnavn<br>";
	$betalingsbet='Netto';
	$betalingsdage=8;
	if (!$fp=fopen($filnavn,'r')) {
		system("cd ../temp/$db\nwget --no-check-certificate $filnavn");
		$tmparray=explode("/",$filnavn);
		$tmp=$tmparray[count($tmparray)-1];
		$filnavn="../temp/$db/$tmp";
	$fp=fopen($filnavn,'r');
echo "F $filnavn<br>";
exit;
	}
	$x=0;
	$y=0;
	$ordresum=0;
	while($linje=fgets($fp)) {
#cho $linje."<br>";		
		if ($encoding!='UTF-8') $linje=utf8_encode($linje);
		$linje=db_escape_string($linje);
		if ($x==0) {
			list($date,$ordre_fornavn,$ordre_efternavn,$ordre_email,$ordresum,$forsendelse,$vaegt,$valuta,$betaling)=explode(chr(9),$linje);
		} elseif ($x==1) {
			list($shop_konto_id,$firmanavn,$fornavn,$efternavn,$adresse,$postnr,$bynavn,$land,$tlf,$cvrnr,$email)=explode(chr(9),$linje);
			} elseif ($x==2) {
			list($lev_konto_id,$lev_firmanavn,$lev_fornavn,$lev_efternavn,$lev_adresse,$lev_postnr,$lev_bynavn,$lev_land,$lev_tlf,$lev_cvrnr,$lev_email)=explode(chr(9),$linje);
		} elseif (trim($linje)) {
			list($shop_vare_id[$y],$varenr[$y],$antal[$y],$beskrivelse[$y],$pris[$y])=explode(chr(9),$linje);
			$y++;
		}
		$x++;
	}
	fclose($fp);
	$shop_konto_id*=1;
	$firmanavn=trim($firmanavn);
	$fornavn=trim($fornavn);
	$efternavn=trim($efternavn);
	$adresse=trim($adresse);
	$postnr=trim($postnr);
	$bynavn=trim($bynavn);
	$land=trim($land);
	$tlf=trim($tlf);
	$cvrnr=trim($cvrnr);
	$email=trim($email);
	if (!$fornavn) $fornavn=$ordre_fornavn;
	if (!$efternavn) $efternavn=$ordre_efternavn;
	if (!$email) $email=$ordre_email;
	if (!$firmanavn) $firmanavn=$fornavn." ".$efternavn;
	$tlf=str_replace(" ","",$tlf);
	$num_tlf=str_replace("+","",$tlf)*1;
	$valuta='DKK';
	$valutakurs='100';
#cho "$shop_konto_id -> $firmanavn -> $fornavn -> $efternavn -> $adresse -> $postnr -> $bynavn -> $land -> ";
#cho "$tlf -> $cvrnr -> $email -> $fornavn -> $efternavn -> $ordre_email -> $firmanavn<br>";
#xit;
	if ($tlf || $email) {
		$fortsaet='OK';
	} else {
		return(0);
		exit;
	} 
	if ($r=db_fetch_array(db_select("select saldi_id from shop_ordrer where shop_id='$shop_ordre_id'",__FILE__ . " linje " . __LINE__))) {
		return(0);
		exit;
	}
	
	$r=db_fetch_array (db_select("select saldi_id from shop_adresser where shop_id='$shop_konto_id'",__FILE__ . " linje " . __LINE__));
	$saldi_id=$r['saldi_id'];
	$qtxt="select id from shop_ordrer where shop_id='$shop_ordre_id'";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	if ($r['id']) {
		return;
		exit;
	}
	#transaktion("begin");
		if (!$saldi_id) {
		if ($tlf || $num_tlf) {
			$qtxt="select id,kontonr from adresser where art = 'D' and ";
			$qtxt.="(lower(firmanavn)='".db_escape_string(strtolower($firmanavn))."' or lower(addr1)='".db_escape_string(strtolower($adresse))."') and "; 
			$qtxt.="(tlf='$tlf' or kontonr='$num_tlf')";
			if ($r=db_fetch_array (db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				$saldi_id=$r['id']*1;
				$kontonr=$r['kontonr'];
			}
		}
		if ($saldi_id) {
			db_modify("insert into shop_adresser(saldi_id,shop_id)values('$saldi_id','$shop_konto_id')",__FILE__ . " linje " . __LINE__);  
		} else {
			if ($tlf && $num_tlf && !$r=db_fetch_array(db_select("select id from adresser where art = 'D' and kontonr='$num_tlf'",__FILE__ . " linje " . __LINE__))) {
				$kontonr=$num_tlf;
			} else { 
			$x=0;
			$q=db_select("select kontonr from adresser where art = 'D' order by kontonr",__FILE__ . " linje " . __LINE__);
			while($r=db_fetch_array($q)) {
				$ktonr[$x]=$r['kontonr'];
				$x++;
			}
			$kontonr=1000;
			while(in_array($kontonr,$ktonr)) $kontonr++;
		}
			$qtxt="insert into adresser(kontonr,firmanavn,addr1,postnr,bynavn,land,cvrnr,email,tlf,gruppe,art,betalingsbet,betalingsdage) values ('$kontonr','".db_escape_string($firmanavn)."','".db_escape_string($adresse)."','".db_escape_string($postnr)."','".db_escape_string($bynavn)."','".db_escape_string($land)."','".db_escape_string($cvrnr)."','".db_escape_string($email)."','".db_escape_string($telefon)."','$gruppe','D','$betalingsbet','$betalingsdage')";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		$r=db_fetch_array(db_select("select id from adresser where kontonr='$kontonr' and art = 'D'",__FILE__ . " linje " . __LINE__));
		$saldi_id=$r['id'];
		db_modify("insert into shop_adresser(saldi_id,shop_id)values('$saldi_id','$shop_konto_id')",__FILE__ . " linje " . __LINE__);  
		}
	} else {
		$r=db_fetch_array(db_select("select kontonr from adresser where id = '$saldi_id'",__FILE__ . " linje " . __LINE__));
		$kontonr=$r['kontonr'];
	}
	$qtxt="select max(ordrenr) as ordrenr from ordrer where art='DO'";
#cho "$qtxt<br>";
	$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
	$ordrenr=$r['ordrenr']+1;
#cho "ONR: $ordrenr<br>";
	$projektnr=0;
	$qtxt="select box1 from grupper where art='DG' and kodenr = '$gruppe'";
	$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
	$momsgruppe=str_replace('S','',$r['box1']);
	$qtxt="select box2 from grupper where art='SM' and kodenr = '$momsgruppe'";
	$r=db_fetch_array(db_select("$qtxt",__FILE__ . " linje " . __LINE__));
	$momssats=$r['box2']*1;
	if (!$valuta)$valuta='DKK';
	if ($valuta=='DKK') {
		$valutakurs=100;
	} else {
		$qtxt="select box2 from grupper where art='VK' and box1 = '$valuta'";
		if ($r=db_fetch_array(db_modify($qtxt,__FILE__ . " linje " . __LINE__))) $valutakurs=$r['box2']*1;
		else $valutakurs=100;
	}
	$qtxt="insert into ordrer(ordrenr,konto_id,kontonr,firmanavn,addr1,addr2,postnr,bynavn,email,art,projekt,momssats,betalingsbet,betalingsdage,status,ordredate,valuta,valutakurs,afd,ref,hvem,felt_5) values ('$ordrenr','$saldi_id','$kontonr','".db_escape_string($firmanavn)."','".db_escape_string($adresse)."','".db_escape_string($addr2)."','".db_escape_string($postnr)."','".db_escape_string($bynavn)."','".db_escape_string($email)."','$art','$projektnr','$momssats','$betalingsbet','$betalingsdage','0','$date','$valuta','$valutakurs','$afd','$ref','$hvem','$kasse')";
#cho $qtxt."<br>";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	$r=db_fetch_array(db_select("select max(id) as id from ordrer where kontonr='$kontonr'",__FILE__ . " linje " . __LINE__));
	$ordre_id=$r['id'];
	db_modify("insert into shop_ordrer(saldi_id,shop_id)values('$ordre_id','$shop_ordre_id')",__FILE__ . " linje " . __LINE__);  
	$posnr=0;
	for ($x=0;$x<count($shop_vare_id);$x++) {
		$r=db_fetch_array (db_select("select saldi_id from shop_varer where shop_id='$shop_vare_id[$x]'",__FILE__ . " linje " . __LINE__));
		$vare_id[$x]=$r['saldi_id'];
		if ($vare_id[$x]) {
			$r=db_fetch_array (db_select("select varenr,samlevare from varer where id='$vare_id[$x]'",__FILE__ . " linje " . __LINE__));
			$samlevare[$x]=$r['samlevare'];
		} else {
			$r=db_fetch_array(db_select("select id,samlevare from varer where varenr='$varenr[$x]' or stregkode='$varenr[$x]'",__FILE__ . " linje " . __LINE__));
			$vare_id[$x]=$r['id'];
			$samlevare[$x]=$r['samlevare'];
			if (!$vare_id[$x]) {
				$r=db_fetch_array(db_select("select id,samlevare from varer where varenr='$shop_vare_id[$x]'",__FILE__ . " linje " . __LINE__));
				$vare_id[$x]=$r['id'];
				$samlevare[$x]=$r['samlevare'];
			}	
			if (!$vare_id[$x]) {
				$r=db_fetch_array(db_select("select id,samlevare from varer where beskrivelse='$beskrivelse[$x]'",__FILE__ . " linje " . __LINE__));
				$vare_id[$x]=$r['id'];
				$samlevare[$x]=$r['samlevare'];
			}	
			if (!$vare_id[$x] && $varenr[$x] && $beskrivelse) {
				$kostpris[$x]=$pris[$x]-$pris[$x]/100*$dg;
				db_modify("insert into varer(varenr,beskrivelse,salgspris,kostpris,gruppe)values('$varenr[$x]','$beskrivelse[$x] (INDSAT FRA SHOP)','$pris[$x]','$kostpris[$x]','1')",__FILE__ . " linje " . __LINE__);
				$r=db_fetch_array(db_select("select id,samlevare from varer where varenr='$varenr[$x]'",__FILE__ . " linje " . __LINE__));
				$vare_id[$x]=$r['id'];
				$samlevare[$x]=$r['samlevare'];
				$smlv='on';
			}
			db_modify("insert into shop_varer(saldi_id,shop_id)values('$vare_id[$x]','$shop_vare_id[$x]')",__FILE__ . " linje " . __LINE__);  
		}
		db_modify("update varer set publiceret='on' where id = '$vare_id[$x]'",__FILE__ . " linje " . __LINE__);
		if ($samlevare[$x]=='on') {
#cho "$ordre_id,$vare_id[$x],$pris[$x]*1.25,'25',$antal[$x],'on'<br>";

			opret_saet($ordre_id,$vare_id[$x],$pris[$x]*1.25,25,$antal[$x],on);
		} else opret_ordrelinje($ordre_id,$vare_id[$x],$varenr[$x],$antal[$x],$beskrivelse[$x],$pris[$x],0,100,'DO','',$posnr,'0','','','','0');
		$ordresum+=$pris[$x]*$antal[$x];
	}
	$momssum=$ordresum/4;
	$qtxt="update ordrer set sum='$ordresum',moms='$momssum' where id='$ordre_id'";
#cho $qtxt."	<br>";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);	
	return("$ordre_id");
}

function fakturer_ordre($saldi_id){
	global $art;

echo __line__;
exit; 
	#transaktion('begin');	
	$qtxt="select shop_id from shop_ordrer where saldi_id='$saldi_id'";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$shop_id=$r['shop_id'];
	$qtxt="update ordrer set levdate=ordredate,fakturadate=ordredate,betalingsbet='Kreditkort',betalingsdage='0',ref='Internet',kundeordnr='$shop_id' where id='$saldi_id'";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);	
	$qtxt="update ordrelinjer set leveres = antal where ordre_id='$saldi_id'";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);	
	#cho "bogfører $ordre_id<br>";
	$svar=levering($saldi_id);
	if ($svar=='OK') $svar=bogfor($saldi_id);
	#cho "bogført $svar<br>";
	$r=db_fetch_array(db_select("select * from ordrer where id = '$saldi_id'",__FILE__ . " linje " . __LINE__));
	$betalt=$r['sum']+$r['moms'];
#cho "insert into pos_betalinger(ordre_id,betalingstype,amount) values ('$saldi_id','betalingskort','$betalt')<br>";
	db_modify("insert into pos_betalinger(ordre_id,betalingstype,amount) values ('$saldi_id','Betalingskort','$betalt')",__FILE__ . " linje " . __LINE__);
	if ($svar=='OK') return("$saldi_id"); 
	else print "<body onload=\"javascript:alert('$svar');window.close();\">";
}
#include_once('includes/bottom.php'); 
#print "</body></html>"
#http://havemobelland.dk/saldi/?shop_db=1234&filmappe=files&gruppe=1&&opdat_status=0&$shop_fakt=1&betalingsbet=kreditkort&popup=1
#http://prestashop.saldi.dk/osc/saldiapi/?shop_db=www217osc&filmappe=files&gruppe=1&&opdat_status=0&$shop_fakt=1&betalingsbet=kreditkort&popup=1
?>