<?php
// ---------debitor/betalingsliste.php-----------------------Patch 3.5.9---2015.11.04---
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af The Free Software Foundation; enten i version 2
// af denne licens eller en senere version efter eget valg
// Fra og med version 3.2.2 dog under iagttagelse af følgende:
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
// -----------------------------------------------------------------------------------
//
// 2015.11.04 Kopieret fra kreditor (phr) 

@session_start();
$s_id=session_id();
		
$modulnr=12;	
$title="betalingsliste";	
$css="../css/standard.css";
		
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/settings.php");
include("../includes/std_func.php");

$sort=isset($_GET['sort'])? $_GET['sort']:Null;
$rf=isset($_GET['rf'])? $_GET['rf']:Null;
$vis=isset($_GET['vis'])? $_GET['vis']:Null;
print "<meta http-equiv=\"refresh\" content=\"150;URL=betalingsliste.php?sort=$sort&rf=$rf&vis=$vis\">";

if (!$sort) {
	$sort = "id";
	$rf = "desc";
}
?>
		<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0"><tbody>
		<tr><td height = "25" align="center" valign="top">
		<table width="100%" align="center" border="0" cellspacing="2" cellpadding="0"><tbody>
		<td width="10%" <?php echo $top_bund ?>><font face="Helvetica, Arial, sans-serif" color="#000066"><a href="rapport.php" accesskey=L>Luk</a></td>
		<td width="80%" <?php echo $top_bund ?> ><font face="Helvetica, Arial, sans-serif" color="#000066">betalingsliste</td>
		<td width="10%" <?php echo $top_bund ?>><font face="Helvetica, Arial, sans-serif" color="#000066"><?php echo"<a href=betalinger.php?id=0 accesskey=N>"?>Ny</a></td>
		</tbody></table>
		</td></tr>
		<tr><td valign="top">
		<table cellpadding="1" cellspacing="1" border="0" width="100%" valign = "top">
<?php

if ($vis=='alle') print "<tr><td colspan=6 align=center><a href=betalingsliste.php?sort=$sort&rf=$rf>vis egne</a></td></tr>";
else print "<tr><td colspan=6 align=center><a href=betalingsliste.php?sort=$sort&rf=$rf&vis=alle>vis alle</a></td></tr>";
if ((!isset($linjebg))||($linjebg!=$bgcolor)) {$linjebg=$bgcolor; $color='#000000';}
else {$linjebg=$bgcolor5; $color='#000000';}
print "<tr bgcolor=\"$linjebg\">";
if (($sort == 'id')&&(!$rf)) print "<td width = 5%><b><a href=betalingsliste.php?sort=id&rf=desc>Id</a></b></td>\n";
else print "<td width = 5%><b><a href=betalingsliste.php?sort=id>Id</a></b></td>\n";
if (($sort == 'listedate')&&(!$rf)) print "<td width = 10%><b><a href=betalingsliste.php?sort=listedate&rf=desc>Dato</a></b></td>\n";
else print "<td width = 10%><b><a href=betalingsliste.php?sort=listedate>Dato</a></b></td>\n";
if (($sort == 'oprettet_af')&&(!$rf)) print "<td><b><a href=betalingsliste.php?sort=oprettet_af&rf=desc>Ejer</a></b></td>\n";
else print "<td><b><a href=betalingsliste.php?sort=oprettet_af>Ejer</a></b></td>\n";
if (($sort == 'listenote')&&(!$rf)) print "<td width = 70%><b><a href=betalingsliste.php?sort=listenote&rf=desc>Bem&aelig;rkning</a></b></td>\n";
else print "<td width = 70%><b><a href=betalingsliste.php?sort=listenote>Bem&aelig;rkning</a></b></td>\n";
if (($sort == 'bogforingsdate')&&(!$rf)) print "<td align=center><b><a href=betalingsliste.php?sort=bogforingsdate&rf=desc>Lukket</a></b></td>\n";
else print "<td align=center><b>Lukket</b></td>\n"; 
if (($sort == 'bogfort_af')&&(!$rf)) print "<td><b><a href=betalingsliste.php?sort=bogfort_af&rf=desc>Af</a></b></td>\n";
else print "<td><b><a href=betalingsliste.php?sort=bogfort_af>af</a></b></td>\n";
print "</tr>\n";
 
	if ($vis == 'alle') $vis = ''; 
	else $vis="and oprettet_af = '".$brugernavn."'";
	$tidspkt=date("U");
	$qtxt="select * from betalingsliste where bogfort = '-' $vis order by $sort $rf";
	$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)){
		$liste="liste".$r['id'];
		if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
		else {$linjebg=$bgcolor5; $color='#000000';}
		print "<tr bgcolor=\"$linjebg\">";
		if (($tidspkt-($r['tidspkt'])>3600)||($r['hvem']==$brugernavn)) {
			print "<td><a href=\"betalinger.php?tjek=$r[id]&liste_id=$r[id]&returside=betalingsliste.php\">$r[id]</a></td>";
		}
		else print "<td><span title= 'liste er l&aring;st af $r[hvem]'>$r[id]</span></td>";
		$listedato=dkdato($r['listedate']);
		print "<td>$listedato<br></td>";
		print "<td>".htmlentities(stripslashes($r['oprettet_af']))."<br></td>";
		print "<td>".htmlentities(stripslashes($r['listenote']))."<br></td>";
		print "<td align = center>$r[bogfort]<br></td>";
		print "<td></td></tr>";
	}
	print "<tr><td colspan=6><hr></td></tr>";
	$q = db_select("select * from betalingsliste where bogfort = '!' $vis order by $sort $rf",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		$liste="liste".$r[id];
		if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
		else {$linjebg=$bgcolor5; $color='#000000';}
		print "<tr bgcolor=\"$linjebg\">";
		if (($tidspkt-($r['tidspkt'])>3600)||($r[hvem]==$brugernavn)) {
			print "<td><a href=\"betalinger.php?liste_id=$r[id]&returside=betalingsliste.php\">$r[id]</a></td>";
		}
		else print "<td><span title= 'liste er l&aring;st af $r[hvem]'>$r[id]</span></td>";
#		print "<tr>";
#		print "<td> $r[id]<br></td>";
		$listedato=dkdato($r['listedate']);
		print "<td>$listedato<br></td>";
                print "<td>".htmlentities(stripslashes($r['oprettet_af']),ENT_QUOTES,$charset)."<br></td>";
                print "<td>".htmlentities(stripslashes($r['kladdenote']),ENT_QUOTES,$charset)."<br></td>";
		print "<td align = center>$r[bogfort]<br></td>";
		print "</tr>";
	}
	if ($r)print "<tr><td colspan=6><hr></td></tr>";
	$q = db_select("select * from betalingsliste where bogfort = 'V' $vis order by $sort $rf",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)){
		$liste="liste".$r['id'];
		if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
		else {$linjebg=$bgcolor5; $color='#000000';}
		print "<tr bgcolor=\"$linjebg\">";
		print "<td><a href=\"betalinger.php?liste_id=$r[id]&returside=betalingsliste.php'\">$r[id]</a></td>";
#		print "<td><a href=kasseliste.php?liste_id=$r[id]&returside=betalingsliste.php>$r[id]</a><br></td>";
		$listedato=dkdato($r['listedate']);
		print "<td>$listedato<br></td>";
                print "<td>".htmlentities(stripslashes($r['oprettet_af']),ENT_QUOTES,$charset)."<br></td>";
                print "<td>".htmlentities(stripslashes($r['kladdenote']),ENT_QUOTES,$charset)."<br></td>";
## Da der ikke blev sat bogfringsdato foer ver. 0.23 skal det saettes hak ved lister bogfrt fr denne version...
		if ($r['bogforingsdate']){
			$bogforingsdato=dkdato($r['bogforingsdate']);
			print "<td align = center>$bogforingsdato<br></td>";
		}
		else {print "<td align = center>$r[bogfort]<br></td>";}
		print "<td>$r[bogfort_af]<br></td>";

		print "</tr>";
	}

?>
</tbody>
</table>
	</td></tr>
</tbody></table>

</body></html>
