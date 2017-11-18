<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// -----------lager/rapport.php------------patch 3.6.7-------2017.05.02----
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af "The Free Software Foundation", enten i version 2
// af denne licens eller en senere version, efter eget valg.
// Fra og med version 3.2.2 dog under iagttagelse af følgende:
// 
// Programmet må ikke uden forudgående skriftlig aftale anvendes
// i konkurrence med saldi.dk ApS eller anden rettighedshaver til programmet.
//
// Dette program er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
//
// En dansk oversaettelse af licensen kan laeses her:
// http://www.saldi.dk/dok/GNU_GPL_v2.html
//
// Copyright (c) 2003-2017 saldi.dk ApS
// ----------------------------------------------------------------------
// 2013.02.10 Break ændret til break 1
// 2013.03.18 $modulnr ændret fra 12  til 15
// 2013.08.27 Større omskrivning for bedre datovalg og detaljeringsgrad 
// 2014.11.05 Større omskrivning
// 2015.01.19 Kostpriser på varelinjer er forkert i ordrer < 010115 med anden valuta en DKK. 
//		Derfor findes kostpriser med ordrefunc funktionen find_kostpris. Søg "/*find_kostpris*/" 
// 2015.09.03 Lagerregulering kom ikke med ved tilgang. Tilføjet $bk_id & $bs_id samt ændret ordre_id til hhv. $k_ordre_id/$s_ordre_id & 
// antal til hhv. $k_antal/$s_antal. 
// 2015.11.05 lagerreguleringer blev trukket fra i til-/afgang. Skal lægges til. #20151105  
// 2015.11.06 Ikke lagerførte samlevarer (sæt) medtages nu ikke da disse er repræsenteret af de varer som indgår i sættet. #20151106  
// 2015.12.10 Kostpriser blev altid trukket fra varekort - se også find_kostpriser i ordrefunc.php #21051210
// 2016.02.01 # Flyttet db *-1 under sammentælling da sammentælling af db blev forkert ved negativt salg.
// 2016.04.18 Tilføjet selection på afdeling. Søg $afd
// 2016.04.18 Negatativ regulering blev vist uden fortegn. #20160418
// 2016.08.04 Datofejl v visning af detaljeret regulering da $fakturadate blev nullet ved inden søgning efter både køb & salg
// 2016.08.24	$x erstattes af $f da $x blev brugt i 'for løkken' 20160824
// 2016.08.26 tilføjet $r[antal]*0 i "if" linje for at udelukke rabatter.
// 2017.01.14 Order by .... id rettet til Order by .... batch_salg.id da .id er 'ambiguous'
// 2017.04.19 Flyttet sammentælling af reguleringssum "en tuborg" ned da negativ regulering ikke kom med. 
// 2017.05.02 Sammentælling af reguleringssum viste hele beholdningen.
// 2017.05.02 Tilføjet selection på sælger. Søg $ref
// 2017.08.01 Tilføjet selection på leverandør. Søg $lev

	@session_start();
	$s_id=session_id();
	$css="../css/standard.css";
 
	$title="Varerapport";
	$modulnr=15;

	$vk_kost=NULL;
	
	include("../includes/connect.php");
	include("../includes/online.php");
	include("../includes/std_func.php");
	include("../includes/forfaldsdag.php");
	include("../includes/ordrefunc.php");
#	include("../includes/db_query.php");

if ($popup) $returside="../includes/luk.php";
else $returside="../index/menu.php";

if (isset($_POST['submit']) && $_POST['submit']) {
	$submit=strtolower(trim($_POST['submit']));
	$varegruppe=trim($_POST['varegruppe']);
	$afd=$_POST['afd'];
	$ref=$_POST['ref'];
	$lev=$_POST['lev'];
	$date_from=usdate($_POST['dato_fra']);
	$date_to=usdate($_POST['dato_til']);
#	$md=$_POST['md'];
	$varenr = $_POST['varenr'];
	$varenavn = $_POST['varenavn'];
	$detaljer = $_POST['detaljer'];
	$kun_salg = $_POST['kun_salg'];
	$vk_kost = $_POST['vk_kost'];
	
	$varenr = trim($varenr);
	$varenavn = trim($varenavn);
} else {
	$varegruppe=if_isset($_GET['varegruppe']);
	$afd=if_isset($_GET['afd']);
	$ref=if_isset($_GET['ref']);
	$lev=if_isset($_GET['lev']);
	$date_from=if_isset($_GET['date_from']);
	$date_to=if_isset($_GET['date_to']);
	$varenr=if_isset($_GET['varenr']);
	$varenavn=if_isset($_GET['varenavn']);
	$detaljer = $_GET['detaljer'];
	$kun_salg = $_GET['kun_salg'];
	$submit=if_isset($_GET['submit']);
}
#$md[1]="januar"; $md[2]="februar"; $md[3]="marts"; $md[4]="april"; $md[5]="maj"; $md[6]="juni"; $md[7]="juli"; $md[8]="august"; $md[9]="september"; $md[10]="oktober"; $md[11]="november"; $md[12]="december";

#if (strstr($varegruppe, "ben post")) {$varegruppe="openpost";}
#cho "$date_from, $date_to, $varenr, $varenavn, $varegruppe,$detaljer<br>";
if ($submit == 'ok') varegruppe ($date_from, $date_to, $varenr, $varenavn, $varegruppe,$detaljer,$kun_salg,$vk_kost,$afd,$lev,$ref); 
elseif ($submit == 'lagerstatus') print print "<meta http-equiv=\"refresh\" content=\"0;URL=lagerstatus.php?varegruppe=$varegruppe\">";
elseif (strpos($submit,'ageropt')) print print "<meta http-equiv=\"refresh\" content=\"0;URL=optalling.php?varegruppe=$varegruppe\">";
else 	forside ($date_from,$date_to,$varenr,$varenavn,$varegruppe,$detaljer,$kun_salg,$vk_kost,$afd,$lev,$ref);
#cho "$submit($regnaar, $date_from, $date_to, $varenr, $varenavn, $varegruppe)";

#############################################################################################################
function forside($date_from,$date_to,$varenr,$varenavn,$varegruppe,$detaljer,$kun_salg,$vk_kost,$afd,$lev,$ref) {

	#global $connection;
	global $brugernavn;
	global $top_bund;
	global $md;
	global $returside;
	global $popup;
	global $jsvars;

#	$regnaar=$regnaar*1; #fordi den er i tekstformat og skal vaere numerisk
	($date_from)?$dato_fra=dkdato($date_from):$dato_fra="01-01-".date("Y");
	($date_to)?$dato_til=dkdato($date_to):$dato_til=date("d-m-Y");
	if (!$varenr) $varenr="*";
	if (!$varenavn) $varenavn="*";
	if ($detaljer) $detaljer='checked';
	if ($kun_salg) $kun_salg='checked';
	
	
#	if (!$regnaar) {
#		$query = db_select("select regnskabsaar from brugere where brugernavn = '$brugernavn'",__FILE__ . " linje " . __LINE__);
#		$row = db_fetch_array($query);
#		$regnaar = $row['regnskabsaar'];
#	}
#	$query = db_select("select * from grupper where art = 'RA' order by box2",__FILE__ . " linje " . __LINE__);
#	$x=0;
#	while ($row = db_fetch_array($query)){
#		$x++;
#		$regnaar_id[$x]=$row['id'];
#		$regn_beskrivelse[$x]=$row['beskrivelse'];
#		$start_md[$x]=$row['box1']*1;
#		$start_aar[$x]=$row['box2']*1;
#		$slut_md[$x]=$row['box3']*1;
#		$slut_aar[$x]=$row['box4']*1;
#		$regn_kode[$x]=$row['kodenr'];
#		if ($regnaar==$row['kodenr']){$aktiv=$x;}
#	}
#	$antal_regnaar=$x;

	print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"0\" align=\"center\"><tbody>"; #A
	print "<tr><td width=100%>";
	print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"3\" cellpadding=\"0\"><tbody>"; #B
	print "<td width=\"10%\" $top_bund><a href=$returside accesskey=L>Luk</a></td>";
	print "<td width=\"80%\" $top_bund>Varerapport - forside</td>";
	print "<td width=\"10%\" $top_bund><a href='../utils/batch_salg_rabat.php?bogfor=0&md=8' target='blank'>|</a></td></tr>";
	print "</tbody></table></td></tr>"; #B slut
	print "</tr><tr><td height=\"60%\" \"width=100%\" align=\"center\" valign=\"bottom\">";
#	print "<form name=regnskabsaar action=rapport.php method=post>";
#	print "<table cellpadding=\"1\" cellspacing=\"5\" border=\"1\"><tbody>";
#	print "<tr><td align=center><h3>Rapporter<br></h3></td></tr>";
#	print "<tr><td align=center><table cellpadding=\"1\" cellspacing=\"1\" border=\"1\" width=\"100%\"><tbody>";
#	print "<tr><td> Regnskabs&aring;r</td><td width=100><select class=\"inputbox\" name=regnaar>";
#	print "<option>$regnaar - $regn_beskrivelse[$aktiv]</option>";
#	for ($x=1; $x<=$antal_regnaar;$x++) {
#		if ($x!=$aktiv) {print "<option>$regn_kode[$x] - $regn_beskrivelse[$x]</option>";}
#	}
	
#	print "</td><td width=100 align=center><input type=submit value=\"Opdat&eacute;r\" name=\"submit\"></td>";
#	print "</form>";
	$vg_nr[0]='0';
	$vg_navn[0]='Alle';
	$x=1;
	$q = db_select("select * from grupper where art = 'VG' order by kodenr",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)){
		$vg_nr[$x]=$r['kodenr'];
		$vg_navn[$x]=$r['beskrivelse'];
		$x++;
	}
	$afd_nr[0]='0';
	$afd_navn[0]='Alle';
	$x=1;
	$q = db_select("select * from grupper where art = 'AFD' order by kodenr",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		$afd_nr[$x]=$r['kodenr'];
		$afd_navn[$x]=$r['beskrivelse'];
		$x++;
	} 
	$lev_id[0]='0';
	$lev_nr[0]='0';
	$lev_navn[0]='Alle';
	$x=0;
	$q = db_select("select distinct(lev_id) from vare_lev",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		$l_id[$x]=$r['lev_id'];
		$x++;
	}
	$x=1;
	$q = db_select("select * from adresser where art = 'K' order by firmanavn",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		if (in_array($r['id'],$l_id)) {
			$lev_id[$x]=$r['id'];
			$lev_nr[$x]=$r['kontonr'];
			$lev_navn[$x]=$r['firmanavn'];
			$x++;
		}
	}
	$ref_nr[0]='0';
	$ref_navn[0]='Alle';
	$x=1;
	$q = db_select("select * from brugere order by brugernavn",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		$ref_nr[$x]=$r['id'];
		$ref_brugernavn[$x]=$r['brugernavn'];
		$ref_ansat[$x]=$r['ansat_id'];
		$ref_navn[$x]=NULL;
		$x++;
	} 
	for ($x=1;$x<count($ref_nr);$x++) {
		if ($ref_ansat[$x]) {
			$r = db_fetch_array(db_select("select navn from ansatte where id=$ref_ansat[$x]",__FILE__ . " linje " . __LINE__));
			$ref_navn[$x]=$r['navn'];
		}  
		if (!$ref_navn[$x]) $ref_navn[$x]=$ref_brugernavn[$x];
	}
	
	print "<form name=rapport action=rapport.php method=post>";
	print "<table cellpadding=\"1\" cellspacing=\"1\" border=\"1\"><tbody>";
	print "<tr><td align=\"center\" colspan=\"3\"><h3>Varerapport<br></h3></td></tr>";
	print "<tr><td>Varegruppe</td><td colspan=\"2\"><select class=\"inputbox\" name=\"varegruppe\" style=\"width:200px;\">";
	for ($x=0;$x<count($vg_nr);$x++) {
		if ($varegruppe == $vg_nr[$x]) print "<option value=$vg_nr[$x]>$vg_nr[$x] : $vg_navn[$x]</option>";
 	}
	for ($x=0;$x<count($vg_nr);$x++) {
		if ($varegruppe != $vg_nr[$x]) print "<option value=$vg_nr[$x]>$vg_nr[$x] : $vg_navn[$x]</option>";
	}
	print "</select>";
	print "<!--Kostpris fra varekort --><input type=\"hidden\" name=\"vk_kost\" value=\"$vk_kost\">";
	print "</td></tr>";
	if (count($lev_id)>1) {
		print "<tr><td> Leverandør </td><td colspan=\"2\"><select class=\"inputbox\" name=\"lev\" style=\"width:200px;\">";
		for ($x=0;$x<count($lev_id);$x++) {
			if ($lev == $lev_id[$x]) print "<option value='$lev_id[$x]'>$lev_navn[$x]</option>";
		}
		for ($x=0;$x<count($lev_id);$x++) {
			if ($lev != $lev_id[$x]) print "<option value='$lev_id[$x]'>$lev_nr[$x] : $lev_navn[$x]</option>";
		}
		print "</select></td></tr>";
	}
	if (count($afd_nr)>1) { 
		print "<tr><td> Afdeling </td><td colspan=\"2\"><select class=\"inputbox\" name=\"afd\" style=\"width:200px;\">";
		for ($x=0;$x<count($afd_nr);$x++) {
			if ($afd == $afd_nr[$x]) print "<option value=$afd_nr[$x]>$afd_nr[$x] : $afd_navn[$x]</option>";
		}
		for ($x=0;$x<count($afd_nr);$x++) {
			if ($afd != $afd_nr[$x]) print "<option value=$afd_nr[$x]>$afd_nr[$x] : $afd_navn[$x]</option>";
		}
		print "</select></td></tr>";
	}
	if (count($ref_nr)>1) {
		print "<tr><td> Sælger </td><td colspan=\"2\"><select class=\"inputbox\" name=\"ref\" style=\"width:200px;\">";
		for ($x=0;$x<count($ref_nr);$x++) {
			if ($ref == $ref_nr[$x]) print "<option value=$ref_nr[$x]>$ref_brugernavn[$x]</option>";
		}
		for ($x=0;$x<count($ref_nr);$x++) {
			if ($ref != $ref_nr[$x]) print "<option value=$ref_nr[$x]>$ref_brugernavn[$x]</option>";
		}
		print "</select></td></tr>";
	}
	print "<tr>";
	print "	<td> Periode</td>";
	print "	<td colspan=\"1\"><input class=\"inputbox\" style=\"width:97px;\" type=\"text\" name=\"dato_fra\" value=\"$dato_fra\"></td>";
	print "	<td colspan=\"1\"><input class=\"inputbox\" style=\"width:97px;\" type=\"text\" name=\"dato_til\" value=\"$dato_til\"></td>";
	print "	</tr>";
	print "<tr><td>Varenr</td><td colspan=\"2\"><input class=\"inputbox\" style=\"width:200px;\" name=\"varenr\" value=\"$varenr\"></td></tr>";
	print "<tr><td>Varenavn</td><td colspan=\"2\"><input class=\"inputbox\" style=\"width:200px;\" name=\"varenavn\" value=\"$varenavn\"></td></tr>";
	print "<tr><td>Detaljeret</td><td colspan=\"2\"><input type=\"checkbox\" name=\"detaljer\" $detaljer></td></tr>";
	print "<tr><td>Kun salg / DB</td><td colspan=\"2\"><input type=\"checkbox\" name=\"kun_salg\" $kun_salg></td></tr>";
	print "<tr><td colspan='3' align=center><input type=submit value=\"  OK  \" name=\"submit\"></td></tr>";
	print "</tbody></table>";
	print "<tr><td ALIGN=\"center\" Valign=\"top\" height=39%><table cellpadding=\"1\" cellspacing=\"1\" border=\"0\"><tbody>\n";
#	if ($popup) {
#		print "<tr><td ALIGN=center onClick=\"javascript:lagerstatus=window.open('lagerstatus.php','lagerstatus','$jsvars');lagerstatus.focus();\"><span title='Se lagerstatus p&aring; vilk&aring;rlig dato'><input type=\"submit\" style=\"width:120px;\" value=\"Lagerstatus\" name=\"submit\"></span></td>";
#		print "<td ALIGN=center onClick=\"javascript:optalling=window.open('optalling.php','optalling','$jsvars');optalling.focus();\"><span title='Funktion til opt&aelig;lling og regulering af varelager'><input type=\"submit\" style=\"width:120px;\" value=\"Lageropt&aelig;lling\" name=\"submit\"></span></td></tr>";
#	} else {
		print "";
		print "<tr><td ALIGN=center><span title='Se lagerstatus p&aring; vilk&aring;rlig dato'><input style=\"width:120px;\" type=submit value=\"Lagerstatus\" name=\"submit\"></span></td>";
		print "<td ALIGN=center><span title='Funktion til opt&aelig;lling og regulering af varelager'><input style=\"width:120px;\" type=submit value=\"Lageropt&aelig;lling\" name=\"submit\"></span></td></tr>";
#	}
	print "</form>";
	print "</tbody></table>\n";
	print "</td></tr>";
	
}

##################################################################################################
function varegruppe($date_from,$date_to,$varenr,$varenavn,$varegruppe,$detaljer,$kun_salg,$vk_kost,$afd,$lev,$ref) {

#	global $connection;
	global $top_bund;
	global $md;
	global $returside;
	global $jsvars;
	global $bgcolor;
	global $bgcolor5;
	
	if ($detaljer) $cols=9;
	elseif ($kun_salg) $cols=7;
	else $cols=11;

	list($gruppenr, $tmp)=explode(":",$varegruppe); 

#	if ($returside) $luk= "<a accesskey=L href=\"$returside\">";
#	else 
	$luk= "<a accesskey=L href=\"rapport.php?varegruppe=$varegruppe&afd=$afd&lev=$lev&ref=$ref&date_from=$date_from&date_to=$date_to&varenr=$varenr&varenavn=$varenavn&detaljer=$detaljer&kun_salg=$kun_salg\">";

	print "<table width = 100% cellpadding=\"1\" cellspacing=\"1\" border=\"0\"><tbody>";
	print "<tr><td colspan=\"$cols\" height=\"9\">";
	print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"3\" cellpadding=\"0\"><tbody>"; #B
	print "<td width=\"10%\" $top_bund>$luk Luk</a></td>";
	print "<td width=\"80%\" $top_bund>Rapport | varesalg | ".dkdato($date_from)." - ".dkdato($date_to);
	if ($afd) {
		$r=db_fetch_array(db_select("select beskrivelse from grupper where art = 'AFD' and kodenr = '$afd'",__FILE__ . " linje " . __LINE__));
		print " | $r[beskrivelse]";
	}
	if ($lev) {
		$r = db_fetch_array(db_select("select kontonr,firmanavn from adresser where id='$lev'",__FILE__ . " linje " . __LINE__));
		$lev_nr=$r['kontonr'];
		$lev_navn=$r['firmanavn'];
		if ($lev_navn) print " | $lev_nr ($lev_navn)";
		$x=0;
		$q=db_select("select vare_id from vare_lev where lev_id='$lev'",__FILE__ . " linje " . __LINE__);
		while($r=db_fetch_array($q)) {
			$lev_vare_id[$x]=$r['vare_id'];
			$x++;
		}
	}
	if ($ref) {
		$r = db_fetch_array(db_select("select brugernavn,ansat_id from brugere where id='$ref'",__FILE__ . " linje " . __LINE__));
		$ref_brugernavn=$r['brugernavn'];
		$ref_ansat=$r['ansat_id'];
		$ref_navn=NULL;
		if ($ref_ansat) {
			$r = db_fetch_array(db_select("select navn from ansatte where id=$ref_ansat",__FILE__ . " linje " . __LINE__));
			$ref_navn=$r['navn'];
		}  
		if (!$ref_navn) $ref_navn=$ref_brugernavn;
		if ($ref_navn) print " | $ref_navn";
	}
	print "</td>";
	print "<td width=\"10%\" $top_bund><br></td>";
	print "</tbody></table>"; #B slut
	print "</td></tr>";
	$lagergruppe=array();
	if ($gruppenr) {
		$qtxt="select box8,box9 from grupper where kodenr ='$gruppenr' and art='VG'";
		$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$batch_kontrol=$r['box9'];
		if ($r['box8']=='on') $lagergruppe[0]=$gruppenr;	
	} else {
		$x=0;
		$qtxt="select kodenr,box8,box9 from grupper where art='VG' order by kodenr";
		$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
		while ($r = db_fetch_array($q)){
			if ($r['box8']=='on') {
				$lagergruppe[$x]=$r['kodenr'];
				$x++;
			}	
		}
	}
	$antal=0;
	$k_antal=0;
	$s_antal=0;
	$kontonr=array();
	$x=0;
	$tmp="";
	if ($gruppenr) $tmp = "where ".nr_cast(gruppe)."=$gruppenr"; 
	if ($varenr && $varenr != '*') {
		if (strstr($varenr, "*")) {
			if (substr($varenr,0,1)=='*') $varenr="%".substr($varenr,1);
			if (substr($varenr,-1,1)=='*') $varenr=substr($varenr,0,strlen($varenr)-1)."%";
		} 
		$low=strtolower($varenr);
		$upp=strtoupper($varenr);
		if ($tmp) $tmp.=" and (varenr LIKE '".db_escape_string($varenr)."' or lower(varenr) LIKE '".db_escape_string($low)."' or upper(varenr) LIKE '".db_escape_string($upp)."')";
		else $tmp =  "where (varenr LIKE '".db_escape_string($varenr)."' or lower(varenr) LIKE '".db_escape_string($low)."' or upper(varenr) LIKE '".db_escape_string($upp)."')";
	}
	if ($varenavn && $varenavn != '*') {
		if (strstr($varenavn, "*")) {
			if (substr($varenavn,0,1)=='*') $varenavn="%".substr($varenavn,1);
			if (substr($varenavn,-1,1)=='*') $varenavn=substr($varenavn,0,strlen($varenavn)-1)."%";
		} 
		$low=strtolower($varenavn);
		$upp=strtoupper($varenavn);
		if ($tmp) $tmp.=" and (beskrivelse LIKE '".db_escape_string($varenavn)."' or lower(beskrivelse) LIKE '".db_escape_string($low)."' or upper(beskrivelse) LIKE '".db_escape_string($upp)."')";
		else $tmp =  "where (beskrivelse LIKE '".db_escape_string($varenavn)."' or lower(beskrivelse) LIKE '".db_escape_string($low)."' or upper(beskrivelse) LIKE '".db_escape_string($upp)."')";
	}
	$vare_id=array();
	$x=0;
	$qtxt="select id,gruppe,samlevare from varer $tmp order by beskrivelse";
	$query = db_select("$qtxt",__FILE__ . " linje " . __LINE__);
	while ($row = db_fetch_array($query)) {
		if (!$lev || in_array($row['id'],$lev_vare_id)) { 
		if (!$row['samlevare'] || in_array($row['gruppe'],$lagergruppe)) { #20151105
			$x++;
			$vare_id[$x]=$row['id'];
		}
		}
#cho "A $vare_id[$x]<br>";
	}
	$v_id=array();
	$x=0;
	# finder alle konti med bevaegelser i den anfoerte periode eller aabne poster fra foer perioden
	$qtxt="select  batch_salg.vare_id,batch_salg.pris from batch_salg,varer";
	if ($afd || $ref) $qtxt.=",ordrer";
	$qtxt.=" where batch_salg.fakturadate>='$date_from' and batch_salg.fakturadate<='$date_to' and batch_salg.vare_id = varer.id";
	if ($afd) $qtxt.=" and batch_salg.ordre_id = ordrer.id and ordrer.afd='$afd'";
	if ($ref) $qtxt.=" and batch_salg.ordre_id = ordrer.id and (ordrer.ref='$ref_navn' or ordrer.ref='$ref_brugernavn')";
	$qtxt.=" order by varer.beskrivelse";
	$query = db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($row = db_fetch_array($query)) {
		if ((in_array(trim($row['vare_id']), $vare_id))&&(!in_array(trim($row['vare_id']), $v_id))) {
			$x++;
			$v_id[$x]=trim($row['vare_id']);
#cho "B $v_id[$x]<br>";
		}
	}
 #cho "select vare_id, pris from batch_kob where fakturadate>='$date_from' and fakturadate<='$date_to' order by vare_id<br>";	
	$query = db_select("select batch_kob.vare_id,batch_kob.pris from batch_kob,varer where batch_kob.fakturadate>='$date_from' and batch_kob.fakturadate<='$date_to' and batch_kob.vare_id = varer.id order by varer.beskrivelse",__FILE__ . " linje " . __LINE__);
	while ($row = db_fetch_array($query)) {
		if ((in_array(trim($row['vare_id']), $vare_id))&&(!in_array(trim($row['vare_id']), $v_id))) {
			$x++;
			$v_id[$x]=trim($row['vare_id']);
		}
	}
	$vareantal=$x;
	
#	 print "<tr><td colspan=8><hr></td></tr>";
#	print "<tr><td width=10%> Dato</td><td width=10%> Bilag</td><td width=50%> Tekst</td><td width=10% align=right> Debet</td><td width=10% align=right> Kredit</td><td width=10% align=right> Saldo</td></tr>";

	if (!$detaljer) {
		if ($kun_salg) {
			print "<tr><td><b>Varenr.</b></td>
			<td><b>Enhed</b></td>
			<td><b>Beskrivelse</b></td>
			<td align=\"right\"><b>Solgt</b></td>
			<td align=\"right\"><b>Salgspris</b></td>
			<td align=\"right\"><b>DB</b></td>
			<td align=\"right\"><b>DG</b></td>";
		} else { 
		print "<tr><td><b>Varenr.</b></td>
				<td><b>Enhed</b></td>
				<td><b>Beskrivelse</b></td>
				<td align=\"right\"><b>Købt</b></td>
				<td align=\"right\"><b>K&oslash;bspris</b></td>
				<td align=\"right\"><b>Solgt</b></td>
				<td align=\"right\"><b>Salgspris</b></td>
				<td align=\"right\"><b>Reguleret</b></td>
				<td align=\"right\"><b>DB</b></td>
				<td align=\"right\"><b>DG</b></td>
				<td align=\"right\"><b>Til- / afgang</b></td></tr>";
	}
	}
	$tt_kobt=0;
	$tt_solgt=0;
	$tt_regul=0;
	$tt_k_pris=0;
	$tt_s_pris=0;
	$tt_kost=0;
	$tt_db=0;
	$varenr=array();
	$enhed=array();
	$beskrivelse=array();
	for ($x=1; $x<=$vareantal; $x++) {
		$r = db_fetch_array(db_select("select * from varer where id=$v_id[$x]",__FILE__ . " linje " . __LINE__));
		$varenr[$x]=$r['varenr'];
		$enhed[$x]=$r['enhed'];
		$beskrivelse[$x]=$r['beskrivelse'];
		$v_kostpris[$x]=$r['kostpris'];
		$samlevare[$x]=$r['samlevare'];
	}
	for ($x=1; $x<=$vareantal; $x++) {
		$y=0;
		$fakturadate=array();
		$bk_id=array();
		$linje_id=array();
		$k_ordre_id=array();
		$k_antal=array();
		$pris=array();
		$t_kobt=0;
		$t_regul=0;
		$t_k_pris=0;
		$t_moms=0;
		$qtxt="select * from batch_kob where vare_id='$v_id[$x]' order by fakturadate,ordre_id";
		$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
		while ($r = db_fetch_array($q)) {
			$ok=1;
			if (!$kun_salg && $r['fakturadate'] && $r['fakturadate'] <= $date_to && $r['fakturadate'] >= $date_from) {
				$bk_id[$y]=$r['id'];
#				$bs_id[$y]=NULL;
				$linje_id[$y]=$r['linje_id'];		
				$fakturadate[$y]=$r['fakturadate'];
				$k_ordre_id[$y]=$r['ordre_id'];
				if ($vk_kost) {
					$k_antal[$y]=$r['antal'];
					$pris[$y]=$v_kostpris;
				} else {
					$k_antal[$y]=$r['antal'];
					$pris[$y]=$r['pris'];
				}
				if ($linje_id[$y]) {
					$qtxt="select momssats,momsfri,omvbet from ordrelinjer where id='$linje_id[$y]'";
					if ($r1 = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
						$momssats[$y]=$r1['momssats'];
						$momsfri[$y]=$r1['momsfri'];
						$omvbet[$y]=$r1['omvbet'];
						if ($momsfri[$y] || $omvbet[$y]) $moms[$y]=0;
						elseif ($momssats[$y]=='') {
							$momssats[$y]=find_varemomssats($linje_id[$y]);
						}	else $moms[$y]=$pris[$y]/100*$momssats[$y];
					} else $ok=0;
				} else {
					if (!$k_ordre_id[$y]) {
						$t_regul+=$k_antal[$y];
						$tt_regul+=$k_antal[$y];
					}
					$ok=0;
				}
				if ($ok) {
					$t_kobt+=$k_antal[$y];
					$t_k_pris+=$pris[$y]*$k_antal[$y];
					$tt_kobt+=$k_antal[$y];
					$tt_k_pris+=$pris[$y]*$k_antal[$y];
					$t_moms+=$moms[$y];
				}
				$y++;
			} else {
#				if ($r['ordre_id']) $tt_kobt=$r['antal'];
#				else $tt_regul+=$r['antal'];
 			}
		}
		$linjebg=$bgcolor;
		if ($detaljer) {
			print "<tr><td colspan=\"$cols\"><hr></td></tr>";
			print "<tr><td><br></td></tr>";
			print "<tr><td><br></td></tr>";
			print "<tr><td colspan=\"3\"><b>$varenr[$x] $beskrivelse[$x]</b></td></tr>";
#			if ($enhed[$x]) print "<tr><td colspan=\"3\">$enhed[$x]</td></tr>";
#			print "<tr><td colspan=\"3\"><b>$beskrivelse[$x]</b></td></tr>";
			print "<tr><td></td></tr>";
			if (!$kun_salg) {
			print "<tr><td>Købsdato</td><td align=\"right\">Antal</td><td align=\"right\">Pris</td><td align=\"right\">Moms</td><td align=\"right\">Incl. moms</td><td align=\"right\">Ordre</td></tr>";
			print "<tr><td colspan=\"$cols\"><hr></td></tr>";
			for ($y=0;$y<count($k_antal);$y++) {
				if ($k_ordre_id[$y]) {
						($linjebg==$bgcolor)?$linjebg=$bgcolor5:$linjebg=$bgcolor;
						print "<tr bgcolor=\"$linjebg\"><td>".dkdato($fakturadate[$y])."</td><td align=\"right\">".dkdecimal($k_antal[$y],2)."</td>";
					$linjepris=$pris[$y]*$k_antal[$y];
					$kobssum+=$t_kobt;
						print "<td align=\"right\" $bac>".dkdecimal($pris[$y],2)."</td><td align=\"right\">".dkdecimal($moms[$y],2)."</td><td align=\"right\">".dkdecimal($pris[$y]+$moms[$y],2)."</td>";
					print "<td align=right onClick=\"javascript:k_ordre=window.open('../kreditor/ordre.php?id=$k_ordre_id[$y]&returside=../includes/luk.php','k_ordre','width=800,height=400,$jsvars')\"> <u>Se</u></td></tr>";
				}
			}
			print "<tr><td colspan=\"$cols\"><hr></td></tr>";
			print "<tr><td></td><td align=\"right\"><b>".dkdecimal($t_kobt,2)."</b></td><td align=\"right\"><b>".dkdecimal($t_k_pris,2)."</b></td><td align=\"right\"><b>".dkdecimal($t_moms,2)."</b></td><td align=\"right\"><b>".dkdecimal($t_k_pris+$t_moms,2)."</b></td></tr>";
			print "<tr><td colspan=\"$cols\"><hr></td></tr>";
		}
		}
#		$fakturadate=array(); #remmet 20160804
		$bs_id=array();
#		$linje_id=array(); #remmet 20160804
		$s_ordre_id=array();
		$s_antal=array();
#		$pris=array(); 
		$t_solgt=0;
#		$t_regul=0;
		$t_s_pris=0;
		$t_kost=0;
		$t_moms=0;
		$t_db=0;
#		$y=0; #remmet 20160804
		$qtxt="select * from batch_salg";
		if ($afd || $ref) $qtxt.=",ordrer";
		$qtxt.=" where batch_salg.vare_id='$v_id[$x]'";
		if ($afd) $qtxt.=" and batch_salg.ordre_id=ordrer.id and ordrer.afd=$afd";
		if ($ref) $qtxt.=" and batch_salg.ordre_id = ordrer.id and (ordrer.ref='$ref_navn' or ordrer.ref='$ref_brugernavn')";
		$qtxt.=" order by batch_salg.fakturadate,batch_salg.id"; #20170114
		$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
		while ($r = db_fetch_array($q)) {
			$ok=1;
			if ($r['antal']*1 && $r['fakturadate'] && $r['fakturadate'] <= $date_to && $r['fakturadate'] >= $date_from) { #20160824
				$bs_id[$y]=$r['id'];
#				$bk_id[$y]=NULL;
				$fakturadate[$y]=$r['fakturadate'];
				$s_antal[$y]=$r['antal'];
				$pris[$y]=$r['pris'];
				$s_ordre_id[$y]=$r['ordre_id'];
				$linje_id[$y]=$r['linje_id'];
				if ($linje_id[$y]) {
					if ($r1 = db_fetch_array(db_select("select ordrelinjer.id,ordrelinjer.kostpris,ordrelinjer.momssats,ordrelinjer.momsfri,ordrelinjer.omvbet from ordrelinjer where ordrelinjer.id='$linje_id[$y]'",__FILE__ . " linje " . __LINE__))){
						if ($vk_kost) $kostpris[$y]=$v_kostpris;
						else $kostpris[$y]=$r1['kostpris'];
						$momssats[$y]=$r1['momssats'];
						$momsfri[$y]=$r1['momsfri'];
						$omvbet[$y]=$r1['omvbet'];
						if ($momsfri[$y] || $omvbet[$y]) $moms[$y]=0;
						elseif ($momssats[$y]=='') {
							$momssats[$y]=find_varemomssats($linje_id[$y]);
						}	else $moms[$y]=$pris[$y]/100*$momssats[$y];
					} else $ok=0;
					list($koordpr,$koordnr,$koordant,$koordid,$koordart)=explode(chr(9),find_kostpris($v_id[$x],$linje_id[$y]));
					$kobs_ordre_pris=explode(",",$koordpr);
					$ko_ant[$y]=count($kobs_ordre_pris);
 					$kobs_ordre_id=explode(",",$koordid);
 					$kobs_ordre_antal=explode(",",$koordant);
					$kobs_ordre_art=explode(",",$koordart);
#20151210 ->
					if ($ko_ant[$y]) {
						$kostpris[$y]=0;
						for($z=0;$z<$ko_ant[$y];$z++) {
							$kostpris[$y]+=$kobs_ordre_pris[$z];
						}
						$kostpris[$y]/=$ko_ant[$y];
					} elseif ($vk_kost) $kostpris[$y]=$v_kostpris;
				} else $ok=0;
				if ($ok) {
#<- 20151210
					if ($s_ordre_id[$y]) {
						$t_solgt+=$s_antal[$y];
						$t_s_pris+=$pris[$y]*$s_antal[$y];
						$tt_solgt+=$s_antal[$y];
						$tt_s_pris+=$pris[$y]*$s_antal[$y];
						$t_moms+=$moms[$y]*$s_antal[$y];
						$t_kost+=$kostpris[$y]*$s_antal[$y];
						$tt_kost+=$kostpris[$y]*$s_antal[$y];
						$db[$y]=$pris[$y]-$kostpris[$y];
						$t_db+=$db[$y]*$s_antal[$y];
						$tt_db+=$db[$y]*$s_antal[$y];
						if ($s_antal[$y]<0)$db[$y]*=-1; # 20160201 # Flyttet under sammentælling
						if ($pris[$y]!=0) {
							$dg[$y]=$db[$y]*100/$pris[$y];
						} else $dg[$y]=0;
					}
				} else { #20170419
					$t_regul-=$s_antal[$y];
					$tt_regul-=$s_antal[$y];
				}
				$y++;
			} else {
#				if ($r['ordre_id']) $tt_solgt+=$r['antal'];
#				else $tt_regul+=$r['antal'];
			}
		}
	
		if ($t_s_pris && $t_db) $t_dg=$t_db*100/$t_s_pris;
		else $t_dg=100;
		if ($detaljer) {
			print "<tr><td>Salgsdato</td><td align=\"right\">Antal</td><td align=\"right\">Pris</td><td align=\"right\">Moms</td><td align=\"right\">Incl.moms</td><td align=\"right\">Kostpris</td><td align=\"right\">DB</td><td align=\"right\">DG</td><td align=\"right\">Ordre</td></tr>";
			for ($y=count($bk_id);$y<count($linje_id);$y++) {
				if ($s_ordre_id[$y]) {
					($linjebg==$bgcolor)?$linjebg=$bgcolor5:$linjebg=$bgcolor;
					print "<tr bgcolor=\"$linjebg\"><td>".dkdato($fakturadate[$y])."</td><td align=right>".dkdecimal($s_antal[$y],2)."</td>";
					print "<td align=right>".dkdecimal($pris[$y],2)."</td>";
					print "<td align=right>".dkdecimal($moms[$y],2)."</td>";
					print "<td align=right>".dkdecimal($pris[$y]+($moms[$y]),2)."</td>";
					print "<td align=right>".dkdecimal($kostpris[$y],2)."</td>";
					print "<td align=right>".dkdecimal($db[$y],2)."</td>";
					print "<td align=right> ".dkdecimal($dg[$y],2)."%</td>";
					print "<td align=right title=\"\" onClick=\"javascript:s_ordre=window.open('../debitor/ordre.php?id=$s_ordre_id[$y]&returside=../includes/luk.php','s_ordre','width=800,height=400,$jsvars')\"> <u>Se</u></td></tr>";
				}
			}
			print "<tr><td colspan=\"$cols\"><hr></td></tr>";
			($linjebg==$bgcolor)?$linjebg=$bgcolor5:$linjebg=$bgcolor;
			print "<tr bgcolor=\"$linjebg\"><td></td>";
			print "<td align=right> <b>".dkdecimal($t_solgt,2)."</b></td>";
			print "<td align=right> <b>".dkdecimal($t_s_pris,2)."</b></td>";
			print "<td align=right> <b>".dkdecimal($t_moms,2)."</b></td>";
			print "<td align=right> <b>".dkdecimal($t_s_pris+$t_moms,2)."</b></td>";
			print "<td align=right> <b>".dkdecimal($t_kost,2)."</b></td>";
			print "<td align=right> <b>".dkdecimal($t_db,2)."</b></td>";
			print "<td align=right> <b>".dkdecimal($t_dg,2)."%</b></td></tr>";
			if (!$kun_salg) print "<tr><td colspan=\"$cols\"><hr></td></tr>";
			if (!$afd && !$lev && !$ref && !$kun_salg) {
			print "<tr><td>Lagerreguleret</td><td align=\"right\">Antal</td></tr>";
			$fd=array_unique($fakturadate); #20160804
			sort($fd);
			for ($f=0;$f<count($fd);$f++) { #20160824
			for ($y=0;$y<count($bk_id);$y++) {
					if ($fd[$f]==$fakturadate[$y] && !$k_ordre_id[$y] && $bk_id[$y]) {
						($linjebg==$bgcolor)?$linjebg=$bgcolor5:$linjebg=$bgcolor;
						print "<tr bgcolor=\"$linjebg\"><td>".dkdato($fakturadate[$y])."</td><td align=right>".dkdecimal($k_antal[$y],2)."</td></tr>";
					}
				}
				for ($y=count($bk_id);$y<count($linje_id);$y++) {
					if ($fd[$f]==$fakturadate[$y] && !$s_ordre_id[$y] && $bs_id[$y]) {
						($linjebg==$bgcolor)?$linjebg=$bgcolor5:$linjebg=$bgcolor;
						print "<tr bgcolor=\"$linjebg\"><td>".dkdato($fakturadate[$y])."</td><td align=right>-".dkdecimal($s_antal[$y],2)."</td></tr>"; #20160418
			}
				}
			}
#cho "$t_kobt+$t_regul-$t_solgt<br>";
			if (!$kun_salg) {	
			print "<tr><td colspan=\"$cols\"><hr></td></tr>";
				($linjebg==$bgcolor)?$linjebg=$bgcolor5:$linjebg=$bgcolor;
				print "<tr bgcolor=\"$linjebg\"><td></td><td align=right> <b>".dkdecimal($t_regul,2)."</b></td><tr>"; #20151105
				print "<tr><td colspan=\"$cols\"><hr></td></tr>";
				($linjebg==$bgcolor)?$linjebg=$bgcolor5:$linjebg=$bgcolor;
				print "<tr bgcolor=\"$linjebg\"><td>Samlet til-/afgang i perioden</td><td align=right> <b>".dkdecimal($t_kobt+$t_regul-$t_solgt,2)."</b></td><tr>"; #20151105
			}
			}
		} else {
			print "<tr><td>$varenr[$x]</td>";
			print "<td>$enhed[$x]</td>";
			print "<td>$beskrivelse[$x]</td>";
			if ($kun_salg) {
				print "<td align=right>".dkdecimal($t_solgt,2)."</td>";
				print "<td align=right>".dkdecimal($t_s_pris,2)."</td>";
				print "<td align=right>".dkdecimal($t_db,2)."</td>";
				print "<td align=right>".dkdecimal($t_dg,2)."%</td>";
			} else {
			print "<td align=right>".dkdecimal($t_kobt,2)."</td>";
			print "<td align=right>".dkdecimal($t_k_pris,2)."</td>";
			print "<td align=right>".dkdecimal($t_solgt,2)."</td>";
			print "<td align=right>".dkdecimal($t_s_pris,2)."</td>";
			print "<td align=right>".dkdecimal($t_regul,2)."</td>";
			print "<td align=right>".dkdecimal($t_db,2)."</td>";
			print "<td align=right>".dkdecimal($t_dg,2)."%</td>";
			print "<td align=right>".dkdecimal($t_kobt+$t_regul-$t_solgt,2)."</td><tr>";#20151105
		}
		}
		if ($detaljer && !$kun_salg) print "<tr><td colspan=\"$cols\"><hr></td></tr>";
	}
	if (!$detaljer) {
		if ($tt_s_pris && $tt_db) $tt_dg=$tt_db*100/$tt_s_pris;
		else $tt_dg=100;
			print "<tr><td colspan=\"$cols\"><hr></td></tr>";
		if ($kun_salg) {
		print "<tr><td Colspan=\"3\"><b>Summeret</b></td>
			<td align=\"right\">Solgt</td>
			<td align=\"right\">Salgspris</td>
			<td align=\"right\">DB</td>
			<td align=\"right\">DG</td>";
		}	else {
			print "<tr><td Colspan=\"3\"><b>Summeret</b></td>
				<td align=\"right\">Købt</td>
				<td align=\"right\">K&oslash;bspris</td>
				<td align=\"right\">Solgt</td>
				<td align=\"right\">Salgspris</td>
				<td align=\"right\">Reguleret</td>
				<td align=\"right\">DB</td>
				<td align=\"right\">DG</td>
				<td align=\"right\">Samlet til-/afgang i perioden</td></tr>";
		}
		print "<tr><td>$varenr[$x]</td>";
		print "<td>$enhed[$x]</td>";
		print "<td>$beskrivelse[$x]</td>";
		if (!$kun_salg) {
		print "<td align=right> <b>".dkdecimal($tt_kobt,2)."</b></td>";
		print "<td align=right> <b>".dkdecimal($tt_k_pris,2)."</b></td>";
		}
		print "<td align=right> <b>".dkdecimal($tt_solgt,2)."</b></td>";
		print "<td align=right> <b>".dkdecimal($tt_s_pris,2)."</b></td>";
		if (!$kun_salg) print "<td align=right> <b>".dkdecimal($tt_regul,2)."</b></td>";
		print "<td align=right> <b>".dkdecimal($tt_db,2)."</b></td>";
		print "<td align=right> <b>".dkdecimal($tt_dg,2)."%</b></td>";
		if (!$kun_salg) print "<td align=right><b>".dkdecimal($tt_kobt+$tt_regul-$tt_solgt,2)."</b></td>";
		print "</tr>";
	}
/*
	print "<tr><td colspan=9><hr></td></tr>";
	print "<tr><td></td><td></td><td></td><td align=\"right\"><b>";
	print dkdecimal($t_solgt);
#	print "</b></td><td align=right> <b>".dkdecimal($talkob)."</b></td>";
	print "<td align=right> <b>".dkdecimal($t_s_pris)."</b></td>";
	print "<td align=right> <b>".dkdecimal($t_moms)."</b></td>";
	print "<td align=right> <b>".dkdecimal($t_s_pris+$t_moms)."</b></td>";
	$db[$y]=$t_s_pris-$t_kost;
	print "<td align=right> <b>".dkdecimal($t_db)."</b></td>";
	if ($tsalg!=0) {$dg[$y]=$db[$y]*100/$talsalg;}
	else {$dg[$y]=100;}
	print "<td align=right> <b>".dkdecimal($dg[$y])."</b></td></tr>";
	print "<tr><td colspan=8><hr></td></tr>";
*/
	print "</tbody></table>";
}
#############################################################################################################

?>
</html>

