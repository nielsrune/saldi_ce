<?php
// --- finans/kladdeliste.php -------- patch 4.0.7 --- 2023.03.04 --- 
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
// http://www.saldi.dk/dok/GNU_GPL_v2.html
// Copyright (c) 2003-2023 Saldi.dk ApS
// -----------------------------------------------------------------------------------
// 20150722 PHR Vis alle/egne gemmes nu som cookie. 
// 20181220 MSC - Rettet ny kladde knap til Ny
// 20190130 MSC - Rettet topmenu design til
// 20210211 PHR - Some cleanup
// 20211112 MSC - Implementing new design
// 20220627 MSC - Implementing new design
// 20220930 MSC - Changed new button text to a plus icon, if the design is topmenu
// 20230708 LOE - A minor modification

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
if (strpos(findtekst(639,$sprog_id),'undtrykke')) {
	$qtxt = "update tekster set tekst = '' where tekst_id >= '600'";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
}
if ($menu=='T') {
			include_once '../includes/top_header.php';
			include_once '../includes/top_menu.php';
	print "<div id=\"header\">"; 
	print "<div class=\"headerbtnLft headLink\">&nbsp;&nbsp;&nbsp;</div>";     
	print "<div class=\"headerTxt\">$title</div>";     
	print "<div class=\"headerbtnRght headLink\"><a href=kassekladde.php?returside=kladdeliste.php&tjek=-1 accesskey=N title='Opret ny kassekladde'><i class='fa fa-plus-square fa-lg'></i></a></div>";     
	print "</div>";
	print "<div class='content-noside'>";
	print  "<table class='dataTable' border='0' cellspacing='1' width='100%'>";
} else {
#	if ($menu=='S') {
#		print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"0\"><tbody>";
#		print "<tr><td style = 'width:150px;'>";
#		include ('../includes/sidemenu.php');
#		print "</td><td><table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" width=\"100%\" valign = \"top\">";
#	}
print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>
		<tr><td height = \"25\" align=\"center\" valign=\"top\">
		<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"2\" cellpadding=\"0\"><tbody>";
		print "<td width=\"10%\"  title=\"".findtekst(1599, $sprog_id)."\" $top_bund><font face=\"Helvetica, Arial, sans-serif\" color=\"#000066\">"; #20210721
		if ($popup) print "<a href=../includes/luk.php accesskey=L>".findtekst(30,$sprog_id)."</a></td>";
		else print "<a href=../index/menu.php accesskey=L>".findtekst(30,$sprog_id)."</a></td>";
		print "<td width=\"80%\" $top_bund><font face=\"Helvetica, Arial, sans-serif\" color=\"#000066\">".findtekst(639,$sprog_id)."</td>";
		if ($popup) print "<td width=\"10%\" title=\"".findtekst(1600, $sprog_id)."\" $top_bund onclick=\"javascript:kladde=window.open('kassekladde.php?returside=kladdeliste.php&tjek=-1','kladde','$jsvars');kladde.focus();\"><a href=kladdeliste.php?sort=$sort&rf=$rf&vis=$vis accesskey=N>".findtekst(39,$sprog_id)."</a></td>";
		else print "<td width=\"10%\" title=\"".findtekst(1600, $sprog_id)."\" $top_bund><a href=kassekladde.php?returside=kladdeliste.php&tjek=-1 accesskey=N>".findtekst(39,$sprog_id)."</a></td>";	
print "</tbody></table></td></tr><tr><td valign=\"top\"><table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" width=\"100%\" valign = \"top\">";
}
if ($vis=='alle') {
	print "<tr>";
	print "<td colspan=1 align=left></td>";
	print "<td colspan=4 align=center><a href=kladdeliste.php?sort=$sort&rf=$rf>".findtekst(641,$sprog_id)."</a></td>";
	print "<td colspan=1 align=right class='imgNoTextDeco'></td>";
	print "</tr>";
}
else {
	print "<tr><td colspan=6 align=center title='".findtekst(1601, $sprog_id)."'><a href=kladdeliste.php?sort=$sort&rf=$rf&vis=alle>".findtekst(636,$sprog_id)."</a></td></tr>";}
	if ((!isset($linjebg))||($linjebg!=$bgcolor)) {$linjebg=$bgcolor; $color='#000000';
}
else {$linjebg=$bgcolor5; $color='#000000';}
print "<tr bgcolor=\"$linjebg\">";
if (($sort == 'id')&&(!$rf)) {print "<td width = 5%><b><a href=kladdeliste.php?sort=id&rf=desc&vis=$vis>Id</a></b></td>\n";}
else {print "<td width = 5% title='".findtekst(1602, $sprog_id)."'><b><a href=kladdeliste.php?sort=id&vis=$vis>Id</a></b></td>\n";}
if (($sort == 'kladdedate')&&(!$rf)) {print "<td width = 10%><b><a href=kladdeliste.php?sort=kladdedate&rf=desc&vis=$vis>".findtekst(635,$sprog_id)."</a></b></td>\n";} //20210318
else {print "<td width = 10% title='".findtekst(1603, $sprog_id)."'><b><a href=kladdeliste.php?sort=kladdedate&vis=$vis>".findtekst(635,$sprog_id)."</a></b></td>\n";}
if (($sort == 'oprettet_af')&&(!$rf)) {print "<td><b><a href=kladdeliste.php?sort=oprettet_af&rf=desc&vis=$vis>".findtekst(634,$sprog_id)."</a></b></td>\n";}
else {print "<td title='".findtekst(1604, $sprog_id)."'><b><a href=kladdeliste.php?sort=oprettet_af&vis=$vis>".findtekst(634,$sprog_id)."</a></b></td>\n";}
if (($sort == 'kladdenote')&&(!$rf)) {print "<td width = 70%><b><a href=kladdeliste.php?sort=kladdenote&rf=desc&vis=$vis>".findtekst(391,$sprog_id)."</a></b></td>\n";}
else {print "<td width = 70% title='".findtekst(1605, $sprog_id)."'><b><a href=kladdeliste.php?sort=kladdenote&vis=$vis>".findtekst(391,$sprog_id)."</a></b></td>\n";}
if (($sort == 'bogforingsdate')&&(!$rf)) {print "<td align=center><b><a href=kladdeliste.php?sort=bogforingsdate&rf=desc&vis=$vis>".findtekst(637,$sprog_id)."</a></b></td>\n";}
else {print "<td align=center><b><a href=kladdeliste.php?sort=bogforingsdate&vis=$vis>".findtekst(637,$sprog_id)."</a></b></td>\n";}
if (($sort == 'bogfort_af')&&(!$rf)) {print "<td><b><a href=kladdeliste.php?sort=bogfort_af&rf=desc&vis=$vis>Af</a></b></td>\n";}
else {print "<td title='".findtekst(1606, $sprog_id)."\"' align='center'><b><a href=kladdeliste.php?sort=bogfort_af&vis=$vis>".findtekst(638,$sprog_id)."</a></b></td>\n";}
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
			if ($tidspkt - trim(intval($b)) > 3600 || $row['hvem'] == $brugernavn) {
			if ($popup) print "<td onmouseover=\"this.style.cursor = 'pointer'\"; onclick=\"javascript:$kladde=window.open('kassekladde.php?tjek=$row[id]&kladde_id=$row[id]&returside=kladdeliste.php','$kladde','".$jsvars."');$kladde.focus();\"><span style=\"text-decoration: underline;\">$row[id]</a></span></td>";
			else print "<td><a href=kassekladde.php?tjek=$row[id]&kladde_id=$row[id]&returside=kladdeliste.php'>$row[id]</a></td>";
		}
		else {print "<td><span title= '".findtekst(1607, $sprog_id)." $row[hvem]'>$row[id]</span></td>";}
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
		if ($popup) print "<td  onmouseover=\"this.style.cursor = 'pointer'\"; onclick=\"javascript:$kladde=window.open('kassekladde.php?kladde_id=$row[id]&returside=kladdeliste.php','$kladde','".$jsvars."');$kladde.focus();\"><span style=\"text-decoration: underline;\">$row[id]</a></span></td>";
		else print "<td><a class=\"kladde\" href=kassekladde.php?tjek=$row[id]&kladde_id=$row[id]&returside=kladdeliste.php'>$row[id]</a></td>";
		}
		else {print "<td><span title= '".findtekst(1607, $sprog_id)." $row[hvem]'>$row[id]</span></td>";}#		print "<tr>";
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
			print "<tr><td colspan=\"2\" align=\"center\"><b>".findtekst(1089,$sprog_id)."</b></td><td colspan=\"4\"><hr></td></tr>";
		}
		$tjek++;
		$kladde="kladde".$row['id'];
		if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
		else {$linjebg=$bgcolor5; $color='#000000';}
		print "<tr bgcolor=\"$linjebg\">";
		if ($popup) print "<td  onmouseover=\"this.style.cursor = 'pointer'\"; onclick=\"javascript:$kladde=window.open('kassekladde.php?kladde_id=$row[id]&tjek=$row[id]&returside=kladdeliste.php','$kladde','".$jsvars."');$kladde.focus();\"><span style=\"text-decoration: underline;\">$row[id]</a></span></td>";
		else print "<td><a class=\"kladde\" href=kassekladde.php?kladde_id=$row[id]&tjek=$row[id]&returside=kladdeliste.php>$row[id]</a><br></td>";
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
			print "<tr><td colspan=\"2\" align=\"center\"><b>".findtekst(1093,$sprog_id)."</b></td><td colspan=\"4\"><hr></td></tr>";
		}
		$tjek++;
		$kladde="kladde".$row['id'];
		if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
		else {$linjebg=$bgcolor5; $color='#000000';}
		print "<tr bgcolor=\"$linjebg\">";
		if ($popup) print "<td  onmouseover=\"this.style.cursor = 'pointer'\"; onclick=\"javascript:$kladde=window.open('kassekladde.php?kladde_id=$row[id]&returside=kladdeliste.php','$kladde','".$jsvars."');$kladde.focus();\"><span style=\"text-decoration: underline;\">$row[id]</a></span></td>";
		else print "<td><a class=\"kladde\" href=kassekladde.php?kladde_id=$row[id]&returside=kladdeliste.php>$row[id]</a><br></td>";
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
	if ($menu=='T') {
		$newbutton= "<i class='fa fa-plus-square fa-lg'></i>";
	} else {
		$newbutton= "<u>".findtekst(39,$sprog_id)."</u>";
	}
	if (!$tjek) {
		print "<tr><td colspan=5 height=25> </td></tr>"; 
		print "<tr><td colspan=3 align=right>TIP 1: </td><td>".findtekst(640,$sprog_id)."; $newbutton ".findtekst(642,$sprog_id).".</td></tr>"; 
		if (db_fetch_array(db_select("select * from kladdeliste",__FILE__ . " linje " . __LINE__))) {
			print "<tr><td colspan=3 align=right>TIP 2: </td><td>".findtekst(597,$sprog_id)." <u>".findtekst(636,$sprog_id)."</u>.</td></tr>"; 
		}
	}
if ($menu=='T') {
	print "</tbody></table>";	
	include_once '../includes/topmenu/footer.php';
} else {
	print "</tbody>
</table>
	</td></tr>
	</tbody></table>";
?>
<!-- HOTKEYS -->
<script type="text/javascript">
var chars = [48,49,50,51,52,53,54,55,56,57,96,97,98,99,100,101,102,103,104,105];

var buffer = '';
var timer = null;
var timeout = 1000;

$(document).keyup(function(e){
	if(chars.indexOf(e.which) != -1){
		buffer = buffer + e.key;
		clearTimeout(timer);
		timer = setTimeout(function(){ $("a.kladde[href*='kladde_id\\="+buffer+"\\&']")[0].click(); }, timeout);
	} else if(e.which == 83){		// S (seneste)
		console.log( $("a.kladde") );
		$("a.kladde:first")[0].click();
	} else if(e.which == 76){		// L (luk)
		$("a[accesskey='L']")[0].click();
	}
});
</script>
<!-- HOTKEYS //-->

<?php
	include_once '../includes/oldDesign/footer.php';
}
?>
