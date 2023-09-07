<?php
// --- finans/kladdeliste.php --- Patch 3.9.9 --- 2021.02.11 ---
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
// Copyright (c) 2003-2021 saldi.dk ApS
// -----------------------------------------------------------------------------------
// 20150722 PHR Vis alle/egne gemmes nu som cookie. 
// 2018.12.20 MSC - Rettet ny kladde knap til Ny
// 2019.01.30 MSC - Rettet topmenu design til
// 2021.02.11 PHR - Some cleanup

@session_start();
$s_id=session_id();
	
$css="../css/standard.css";		
$modulnr=2;	
$title="kladdeliste";	
		
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");

if (!isset ($_COOKIE['saldi_kladdeliste'])) $_COOKIE['saldi_kladdeliste'] = NULL;

$sort=isset($_GET['sort'])? $_GET['sort']:Null;
$rf=isset($_GET['rf'])? $_GET['rf']:Null;
$vis=isset($_GET['vis'])? $_GET['vis']:Null;
print "<meta http-equiv=\"refresh\" content=\"150;URL=kladdeliste.php?sort=$sort&rf=$rf&vis=$vis\">";

if (isset($_GET['sort'])) {
	$cookievalue="$sort;$rf;$vis";
	setcookie("saldi_kladdeliste", $cookievalue, strtotime('+30 days'));
} else list ($sort,$rf,$vis) = array_pad(explode(";", $_COOKIE['saldi_kladdeliste']), 3, null);
if (!$sort) {
	$sort = "id";
	$rf = "desc";
}
if ($menu=='T') {
			include_once '../includes/top_header.php';
			include_once '../includes/top_menu.php';
			print "<div id=\"header\"> 
 		   		<div class=\"headerbtnLft\"></div>
  			  	<span class=\"headerTxt\">Kladdeliste</span>";     
			print "<div class=\"headerbtnRght\"><a href=\"kassekladde.php?returside=kladdeliste.php\" class=\"button green small right\">Ny</a></div>";       
			print "</div><!-- end of header -->
				<div class=\"maincontentLargeHolder\">\n";
			print  "<table class='dataTable2' border='0' cellspacing='1' width='75%'>";

#	$leftbutton="<a title=\"Klik her for at komme til startsiden\" href=\"../index/menu.php\" accesskey=\"L\">LUK</a>";
#	$rightbutton="<a href=\"#\">Ordremenu</a>\t";
#	if ($valg!='ordrer') $rightbutton.="\t<a href='ordreliste.php?valg=ordrer&konto_id=$konto_id&returside=$returside'>&nbsp;Ordreliste&nbsp;</a>";
#	if ($valg!='faktura') $rightbutton.="\t<a href='ordreliste.php?valg=faktura&konto_id=$konto_id&returside=$returside'>&nbsp;Fakturaliste&nbsp;</a>";
#	$rightbutton.="\t<a href=\"../debitor/ordre.php?returside=../debitor/ordreliste.php?konto_id=$konto_id\">Ny ordre/faktura</a>";
#	$rightbutton.="\t<a accesskey=V href=ordrevisning.php?valg=$valg>Visning</a>";
#	include("../includes/topmenu.php");
} else {
print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>
		<tr><td height = \"25\" align=\"center\" valign=\"top\">
		<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"2\" cellpadding=\"0\"><tbody>";
print "<td width=\"10%\"  title=\"Klik her for at lukke kladdelisten\" $top_bund><font face=\"Helvetica, Arial, sans-serif\" color=\"#000066\">";
if ($popup) print "<a href=../includes/luk.php accesskey=L>Luk</a></td>";
else print "<a href=../index/menu.php accesskey=L>Luk</a></td>";
print "<td width=\"80%\" $top_bund><font face=\"Helvetica, Arial, sans-serif\" color=\"#000066\">Kladdeliste</td>";
if ($popup) print "<td width=\"10%\" title=\"Klik her for at oprette en ny kassekladde\" $top_bund onClick=\"javascript:kladde=window.open('kassekladde.php?returside=kladdeliste.php&tjek=-1','kladde','$jsvars');kladde.focus();\"><a href=kladdeliste.php?sort=$sort&rf=$rf&vis=$vis accesskey=N>Ny</a></td>";
else print "<td width=\"10%\" title=\"Klik her for at oprette en ny kassekladde\" $top_bund><a href=kassekladde.php?returside=kladdeliste.php&tjek=-1 accesskey=N>Ny</a></td>";		
print "</tbody></table></td></tr><tr><td valign=\"top\"><table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" width=\"100%\" valign = \"top\">";
}
if ($vis=='alle') {print "<tr><td colspan=6 align=center><a href=kladdeliste.php?sort=$sort&rf=$rf>vis egne</a></td></tr>";}
else {print "<tr><td colspan=6 align=center title='Klik her for at se alle kladder'><a href=kladdeliste.php?sort=$sort&rf=$rf&vis=alle>Vis alle</a></td></tr>";}
if ((!isset($linjebg))||($linjebg!=$bgcolor)) {$linjebg=$bgcolor; $color='#000000';}
else {$linjebg=$bgcolor5; $color='#000000';}
print "<tr bgcolor=\"$linjebg\">";
if (($sort == 'id')&&(!$rf)) {print "<td width = 5%><b><a href=kladdeliste.php?sort=id&rf=desc&vis=$vis>Id</a></b></td>\n";}
else {print "<td width = 5% title='Klik her for at sortere p&aring; ID'><b><a href=kladdeliste.php?sort=id&vis=$vis>Id</a></b></td>\n";}
if (($sort == 'kladdedate')&&(!$rf)) {print "<td width = 10%><b><a href=kladdeliste.php?sort=kladdedate&rf=desc&vis=$vis>Dato</a></b></td>\n";}
else {print "<td width = 10% title='Klik her for at sortere p&aring; dato'><b><a href=kladdeliste.php?sort=kladdedate&vis=$vis>Dato</a></b></td>\n";}
if (($sort == 'oprettet_af')&&(!$rf)) {print "<td><b><a href=kladdeliste.php?sort=oprettet_af&rf=desc&vis=$vis>Ejer</a></b></td>\n";}
else {print "<td title='Klik her for at sortere p&aring; ejer (den der har oprettet kassekladden)'><b><a href=kladdeliste.php?sort=oprettet_af&vis=$vis>Ejer</a></b></td>\n";}
if (($sort == 'kladdenote')&&(!$rf)) {print "<td width = 70%><b><a href=kladdeliste.php?sort=kladdenote&rf=desc&vis=$vis>Bem&aelig;rkning</a></b></td>\n";}
else {print "<td width = 70% title='Klik her for at sortere p&aring; bem&aelig;rkning'><b><a href=kladdeliste.php?sort=kladdenote&vis=$vis>Bem&aelig;rkning</a></b></td>\n";}
if (($sort == 'bogforingsdate')&&(!$rf)) {print "<td align=center><b><a href=kladdeliste.php?sort=bogforingsdate&rf=desc&vis=$vis>Bogf&oslash;rt</a></b></td>\n";}
else {print "<td align=center><b><a href=kladdeliste.php?sort=bogforingsdate&vis=$vis>Bogf&oslash;rt</a></b></td>\n";}
if (($sort == 'bogfort_af')&&(!$rf)) {print "<td><b><a href=kladdeliste.php?sort=bogfort_af&rf=desc&vis=$vis>Af</a></b></td>\n";}
else {print "<td title='Klik her for at sortere p&aring; \"bogf&oslash;rt af\"' align='center'><b><a href=kladdeliste.php?sort=bogfort_af&vis=$vis>af</a></b></td>\n";}
print "</tr>\n";
$tjek=0;
#$sqhost = "localhost";
	
	if ($vis == 'alle') $vis = ''; 
	else $vis="and oprettet_af = '".$brugernavn."'";
	$tidspkt=date("U");
	$qtxt = "select * from kladdeliste where bogfort = '-' $vis order by $sort $rf";
	$query = db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($row = db_fetch_array($query)) {
		$tjek++;
		$kladde="kladde".$row['id'];
		if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
		else {$linjebg=$bgcolor5; $color='#000000';}
		print "<tr bgcolor=\"$linjebg\">";
		if (strpos(' ',$row['tidspkt'])) list ($a,$b)=explode(" ",$row['tidspkt']);
		elseif ($row['tidspkt']) $b=$row['tidspkt'];
		else $b = 0;
		if ($tidspkt - trim($b) > 3600 || $row['hvem'] == $brugernavn) {
			if ($popup) print "<td onMouseOver=\"this.style.cursor = 'pointer'\"; onClick=\"javascript:$kladde=window.open('kassekladde.php?tjek=$row[id]&kladde_id=$row[id]&returside=kladdeliste.php','$kladde','".$jsvars."');$kladde.focus();\"><span style=\"text-decoration: underline;\">$row[id]</a></span></td>";
			else print "<td><a href=kassekladde.php?tjek=$row[id]&kladde_id=$row[id]&returside=kladdeliste.php'>$row[id]</a></td>";
		}
		else {print "<td><span title= 'Kladde er l&aring;st af $row[hvem]'>$row[id]</span></td>";}
		$kladdedato=dkdato($row['kladdedate']);
		print "<td>$kladdedato<br></td>";
		print "<td>".htmlentities(stripslashes($row['oprettet_af']),ENT_QUOTES,$charset)."<br></td>";
		print "<td>".htmlentities(stripslashes($row['kladdenote']),ENT_QUOTES,$charset)."<br></td>";
		print "<td align = center>$row[bogfort]<br></td>";
		print "<td></td></tr>";
	}
#	print "<tr><td colspan=6><hr></td></tr>";
	$query = db_select("select * from kladdeliste where bogfort = '!' $vis order by $sort $rf",__FILE__ . " linje " . __LINE__);
	while ($row = db_fetch_array($query)) {
		$kladde="kladde".$row[id];
		if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
		else {$linjebg=$bgcolor5; $color='#000000';}
		print "<tr bgcolor=\"$linjebg\">";
		if (($tidspkt-($row[tidspkt])>3600)||($row[hvem]==$brugernavn)) {
		if ($popup) print "<td  onMouseOver=\"this.style.cursor = 'pointer'\"; onClick=\"javascript:$kladde=window.open('kassekladde.php?kladde_id=$row[id]&returside=kladdeliste.php','$kladde','".$jsvars."');$kladde.focus();\"><span style=\"text-decoration: underline;\">$row[id]</a></span></td>";
		else print "<td><a href=kassekladde.php?tjek=$row[id]&kladde_id=$row[id]&returside=kladdeliste.php'>$row[id]</a></td>";
		}
		else {print "<td><span title= 'Kladde er l&aring;st af $row[hvem]'>$row[id]</span></td>";}
#		print "<tr>";
#		print "<td> $row[id]<br></td>";
		$kladdedato=dkdato($row['kladdedate']);
		print "<td>$kladdedato<br></td>";
		print "<td>".htmlentities(stripslashes($row['oprettet_af']),ENT_QUOTES,$charset)."<br></td>";
		print "<td>".htmlentities(stripslashes($row['kladdenote']),ENT_QUOTES,$charset)."<br></td>";
		print "<td align = center>$row[bogfort]<br></td>";
		print "</tr>";
	}
	$query = db_select("select * from kladdeliste where bogfort = 'S' $vis order by $sort $rf",__FILE__ . " linje " . __LINE__);
	$hr=$tjek;
	while ($row = db_fetch_array($query)){
		if ($hr==$tjek) {
			print "<tr><td colspan=\"2\" align=\"center\"><b>Simulerede kladder</b></td><td colspan=\"4\"><hr></td></tr>";
		}
		$tjek++;
		$kladde="kladde".$row['id'];
		if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
		else {$linjebg=$bgcolor5; $color='#000000';}
		print "<tr bgcolor=\"$linjebg\">";
		if ($popup) print "<td  onMouseOver=\"this.style.cursor = 'pointer'\"; onClick=\"javascript:$kladde=window.open('kassekladde.php?kladde_id=$row[id]&tjek=$row[id]&returside=kladdeliste.php','$kladde','".$jsvars."');$kladde.focus();\"><span style=\"text-decoration: underline;\">$row[id]</a></span></td>";
		else print "<td><a href=kassekladde.php?kladde_id=$row[id]&tjek=$row[id]&returside=kladdeliste.php>$row[id]</a><br></td>";
		$kladdedato=dkdato($row['kladdedate']);
		print "<td>$kladdedato<br></td>";
		print "<td>".htmlentities(stripslashes($row['oprettet_af']),ENT_QUOTES,$charset)."<br></td>";
		print "<td>".htmlentities(stripslashes($row['kladdenote']),ENT_QUOTES,$charset)."<br></td>";
## Da der ikke blev sat bogfringsdato foer ver. 0.23 skal det saettes hak ved kladder bogfrt fr denne version...
		if ($row['bogforingsdate']){
			$bogforingsdato=dkdato($row['bogforingsdate']);
			print "<td align = center>$bogforingsdato<br></td>";
		}
		else {print "<td align = center>$row[bogfort]<br></td>";}
		print "<td>$row[bogfort_af]<br></td>";

		print "</tr>";
	}
	$query = db_select("select * from kladdeliste where bogfort = 'V' $vis order by $sort $rf",__FILE__ . " linje " . __LINE__);
	$hr=$tjek;
	while ($row = db_fetch_array($query)){
		if ($hr==$tjek) {
			print "<tr><td colspan=\"2\" align=\"center\"><b>Bogførte kladder</b></td><td colspan=\"4\"><hr></td></tr>";
		}
		$tjek++;
		$kladde="kladde".$row['id'];
		if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
		else {$linjebg=$bgcolor5; $color='#000000';}
		print "<tr bgcolor=\"$linjebg\">";
		if ($popup) print "<td  onMouseOver=\"this.style.cursor = 'pointer'\"; onClick=\"javascript:$kladde=window.open('kassekladde.php?kladde_id=$row[id]&returside=kladdeliste.php','$kladde','".$jsvars."');$kladde.focus();\"><span style=\"text-decoration: underline;\">$row[id]</a></span></td>";
		else print "<td><a href=kassekladde.php?kladde_id=$row[id]&returside=kladdeliste.php>$row[id]</a><br></td>";
		$kladdedato=dkdato($row['kladdedate']);
		print "<td>$kladdedato<br></td>";
		print "<td>".htmlentities(stripslashes($row['oprettet_af']),ENT_QUOTES,$charset)."<br></td>";
		print "<td>".htmlentities(stripslashes($row['kladdenote']),ENT_QUOTES,$charset)."<br></td>";
## Da der ikke blev sat bogfringsdato foer ver. 0.23 skal det saettes hak ved kladder bogfrt fr denne version...
		if ($row['bogforingsdate']){
			$bogforingsdato=dkdato($row['bogforingsdate']);
			print "<td align = center>$bogforingsdato<br></td>";
		}
		else {print "<td align = center>$row[bogfort]<br></td>";}
		print "<td>$row[bogfort_af]<br></td>";

		print "</tr>";
	}
	if (!$tjek) {
		print "<tr><td colspan=5 height=25> </td></tr>"; 
		print "<tr><td colspan=3 align=right>TIP 1: </td><td>Du opretter en ny kassekladde ved at klikke p&aring; <u>Ny</u> &oslash;verst til h&oslash;jre.</td></tr>"; 
		if (db_fetch_array(db_select("select * from kladdeliste",__FILE__ . " linje " . __LINE__))) {
			print "<tr><td colspan=3 align=right>TIP 2: </td><td>Du kan se dine kollegers kladder ved at klikke p&aring; <u>Vis alle</u>.</td></tr>"; 
		}
	}
if ($menu=='T') print "</div>";	
?>
</tbody>
</table>
	</td></tr>
</tbody></table>

</body></html>
