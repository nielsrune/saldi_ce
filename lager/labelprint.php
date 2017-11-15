<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ----------/lager/labelprint.php----------------lap 3.6.7---2017-06-28	---
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af "The Free Software Foundation", enten i version 2
// af denne licens eller en senere version, efter eget valg.
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
// Copyright (c) 2003-2017 saldi.dk aps
// ----------------------------------------------------------------------
// 
// 2014.06.17 Tilføjet pris pr. enhed på etiketter, hvis de er der. PHR - Danosoft. 20140617
// 2014.09.01 Tilføjet opsætning til Cognitive printer - Anvendes hvis det ikke er beskrivelse.
// 2015.09.02 Indsat kontrol for uønsket sql kald.
// 2016.05.05 PHR $salgspris mm blev ikke sat hvis $incl_moms ikke var sat. #20160505
// 2017.01.17 PHR Tilføjet $notes. #20170117
// 2017.06.28 PHR Tilføjet $lokation & $lev_varenr. #20170628

@session_start();
$s_id=session_id();
$title="Labelprint";
$modulnr=9;
$css="../css/standard.css";
$bg='nix';

$diffkto=NULL;

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");

$id=if_isset($_GET['id'])*1;
$img=if_isset($_GET['src']);


$banned=array('<?','<?php','?>');
$r=db_fetch_array(db_select("select box1 from grupper where art='LABEL'",__FILE__ . " linje " . __LINE__));
$txt=$r['box1'];
for ($x=0;$x<count($banned);$x++) {
	if (strpos($txt,$banned[$x])) {
		print "<BODY onload=\"JavaScript:alert('Illegal værdi i labeltekst')\">";
		exit;
	}
}
$r=db_fetch_array(db_select("select * from varer where id='$id'",__FILE__ . " linje " . __LINE__));
$momsfri='on';
$salgspris=$r['salgspris']; #20160505
$salgspris2=$r['salgspris2'];
$special_price=$r['special_price'];
$incl_moms=0;
if($r2=db_fetch_array(db_select("select box7 from grupper where art='VG' and kodenr='$r[gruppe]' and box7!='on'",__FILE__ . " linje " . __LINE__))) {
	$momsfri = $r2['box7'];
	if($r2=db_fetch_array(db_select("select box1 from grupper where art='DIV' and kodenr = '5'",__FILE__ . " linje " . __LINE__))) {
		$incl_moms=$r2['box1'];
		$r2=db_fetch_array(db_select("select box2 from grupper where art='SM' and kodenr = '$incl_moms'",__FILE__ . " linje " . __LINE__));
		$incl_moms=$r2['box2']*1;
		$salgspris=$r['salgspris']*=(100+$incl_moms)/100;
		$salgspris2=$r['salgspris2']*=(100+$incl_moms)/100;
		$special_price=$r['special_price']*=(100+$incl_moms)/100;
	}
}
$dkkpris=str_replace(',00',',-',dkdecimal($salgspris));
$txt=str_replace('$beskrivelse',$r['beskrivelse'],$txt);
$txt=str_replace('$varenr',$r['varenr'],$txt);
$txt=str_replace('$trademark',$r['trademark'],$txt);
if ($r['stregkode']) $txt=str_replace('$stregkode',$r['stregkode'],$txt);
else $txt=str_replace('$stregkode',$r['varenr'],$txt);
if (strpos($txt,'$enhedspris/$enhed')) {
	if ($r['enhed'] && $r['indhold']) {
		$txt=str_replace('$enhedspris',dkdecimal(($r['pris']/$r['indhold'])),$txt);
		$txt=str_replace('$enhed',$r['enhed'],$txt);
	} else {
		$txt=str_replace('($enhedspris/$enhed)','',$txt);
		$txt=str_replace('$enhedspris/$enhed','',$txt);
	}
} else {
	$txt=str_replace('$enhedspris',dkdecimal(($r['pris']/$r['indhold'])),$txt);
	$txt=str_replace('$enhed',$r['enhed'],$txt);
}
if (strpos($txt,'$lev_varenr[')) { #20170628
	$x=1;
	$qtxt="select * from vare_lev where vare_id='$id' order by posnr";
	$q2=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while($r2=db_fetch_array($q2)) {
		$txt=str_replace('$lev_varenr['.$x.']',$r2['lev_varenr'],$txt);
		$x++;
	}
}
$txt=str_replace('$kostpris',$r['kostpris'],$txt);
$txt=str_replace('$img',$img,$txt);
$txt=str_replace('$pris',dkdecimal($salgspris),$txt);
$txt=str_replace('$dkkpris',$dkkpris,$txt);
$txt=str_replace('$enhed',$r['enhed'],$txt);
$txt=str_replace('$location',$r['location'],$txt); #20170628
$txt=str_replace('$lokation',$r['location'],$txt); #20170628
$txt=str_replace('$varemrk',$r['trademark'],$txt);
$txt=str_replace('$indhold',$r['indhold'],$txt);
$txt=str_replace('$notes',$r['notes'],$txt);
$txt=str_replace('$special_pris',$special_price,$txt);
$txt=str_replace('$special_fra_dato',dkdato($r['special_from_date']),$txt);
$txt=str_replace('$special_til_dato',dkdato($r['special_to_date']),$txt);
$txt=str_replace('$special_fra_tid',substr($r['special_from_time'],0,5),$txt);
$txt=str_replace('$special_til_tid',substr($r['special_to_time'],0,5),$txt);

$fp=fopen("../temp/$db/label.html",'w');
fwrite($fp,$txt);
fclose($fp);
include ("../temp/$db/label.html");
/*
if ($beskrivelse) { #Dymo
  print "<tr><td></td></tr>\n";
  print "<tr><td align=\"center\"><font face=\"verdana\" size=\"2\">$beskrivelse</font></td></tr>\n";
  print "<tr><td align=\"center\"><font face=\"verdana\" size=\"2\">Pris: ".dkdecimal($pris);
  if ($enhed && $indhold) {
		print " (".dkdecimal($pris/$indhold)."/$enhed)";
  }
  print "</font></td></tr>\n";
  print "<tr><td align=\"center\"><img style=\"border:0px solid;width:250px;height:30px;overflow:hidden;\" alt=\"\" src=\"$src\"></td></tr>\n";
  print "<tr><td align=\"center\"><font face=\"verdana\" size=\"2\">$stregkode</font></td></tr>\n";
} else { # Cognetive
  $dkkpris=dkdecimal($pris);
  $dkkpris=str_replace(',00',',-',$dkkpris);
	print "<center>\n";
  print "<table  border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>";
  print "<tr><td align=\"center\" rowspan=\"2\"><font face=\"verdana\" size=\"5\">$dkkpris</font></td>";
  print "<td width=\"6px\"></td><td align=\"center\"><img style=\"border:0px solid;width:150px;height:50px;\" alt=\"\" src=\"$src\"></td></tr>\n";
  print "<tr><td rowspan=\"2\"></td><td align=\"center\"><font face=\"verdana\" size=\"2\">$stregkode</font></td></tr>\n";
}
*/
print "<body onload=\"javascript:window.print();\">\n";
#javascript:window.close();
print " <br>\n";
?>
