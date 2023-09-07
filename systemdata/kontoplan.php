<?php
// ------------------systemdata/kontoplan.php-----lap 3.6.2-----2016-01-16----
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
// Copyright (c) 2004-2016 DANOSOFT ApS
// ----------------------------------------------------------------------
//
// 20160116 Tilføjet valuta  
// 20160129	Valutakode og kurs blev ikke sat ved oprettelse af ny driftskonti.

@session_start();
$s_id=session_id();
$title="Kontoplan";
$css="../css/standard.css";
$modulnr="0";
$linjebg='';
	
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("chartOfAccountIncludes/dbLogic.php");
if ($popup) $returside="../includes/luk.php";
else $returside="../index/menu.php";

if ($menu=='T') {
#	print "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
	include_once '../includes/top_header.php';
	include_once '../includes/top_menu.php';
	print "<div id='header'>\n";
	print "<div class='headerbtnLft'></div>\n";
#	print "<span class='headerTxt'>Systemsetup</span>\n";     
#	print "<div class='headerbtnRght'><!--<a href='index.php?page=../debitor/debitorkort.php;title=debitor' class='button green small right'>Ny debitor</a>--></div>";       
	print "</div><!-- end of header -->\n";
	print "<div class='maincontentLargeHolder'>\n";
	print "<div id='leftmenuholder'>\n";
	include_once 'left_menu.php';
	print "</div><!-- end of leftmenuholder -->\n";
	print "<div class='rightContent'>\n";
	print "<table border='0' cellspacing='0' id='dataTable' class='dataTable'><tbody>\n"; # -> 1
} else {
	print "<div align='center'>";
	print "<table width='100%' height='100%' border='0' cellspacing='0' cellpadding='0'><tbody>";
	print "<tr><td height='25' align='center' valign='top'>";
	print "<table width='100%' align='center' border='0' cellspacing='2' cellpadding='0'><tbody>";
	print "<td width='10%' $top_bund align='left'><a href=$returside accesskey=L>Luk</a></td>";
	print "<td width='80%' $top_bund align='center'>Kontoplan</td>";
	print "<td width='10%' $top_bund align='right'><a href=kontokort.php accesskey=N>Ny</a></td>";
	print "</tbody></table>";
	print "</td></tr>";
	print "<tr><td valign='top'>";
	print "<table cellpadding='0' cellspacing='1' border='0' width='100%' valign = 'top'>";
	print "<tbody>";
}
print "<tr>\n";
print "<td><b> Kontonr.</b></td>\n";
print "<td><b> Kontonavn</b></td>\n";
print "<td><b> Type</b></td>\n";
print "<td align='center'><b>Moms</b></td>\n";
print "<td align='center'><b>Saldo</b></td>\n";
print "<td align='center'><b>Valuta</b></td>\n";
print "<td align='center'><b>Genvej</b></td>\n";
print "</tr>\n";
for ($i=0;$i<count($accountId);$i++) {
	$weight  = 'normal';
	$size    = 'medium';
	if ($linjebg!=$bgcolor) {
		$linjebg = $bgcolor; 
		$color   = '#000000';
	}	elseif ($linjebg!=$bgcolor5) {
		$linjebg=$bgcolor5; 
		$color='#000000';
	}	if ($accountType[$i]=='H') {
		$linjebg=$bgcolor4; 
		$color='$000000';
		$weight  = 'bold';
		$size    = 'medium';
		}
	print "<tr bgcolor='$linjebg' >\n";
	print "<td><a href='kontokort.php?id=$accountId[$i]'>
		<span style='color:$color;'>$accountNo[$i]</span></a><br></td>\n";
	print "<td><span style='font-weight:$weight;color:$color;'>$accountName[$i]<br></span></td>\n";
	if     ($accountType[$i] == 'H') print "<td><span style='color:$color;'><br></span></td>\n";
	elseif ($accountType[$i] == 'D') print "<td><span style='color:$color;'>Drift<br></span></td>\n";
	elseif ($accountType[$i] == 'S') print "<td><span style='color:$color;'>Status<br></span></td>\n";
	elseif ($accountType[$i] == 'Z') print "<td>
		<span style='color:$color;'>Sum $countFrom[$i] - $countTo[$i]<br></span></td>\n";
	elseif ($accountNo[$i]=='R') print "<td>
		<span style='color:$color;'>Resultat = $countFrom[$i]<br></span></td>\n";
	else print "<td><span style='color:$color;'>Sideskift<br></span></td>\n";
	print "<td align='center'><span style='color:$color;'>$vat[$i]<br></span></td>\n";
	if (($accountNo[$i]!='H')&&($accountNo[$i]!='X')) {
		print "<td align='right' title='DKK ".dkdecimal((float)$balance[$i],2)."'>
		<span style='color:$color;'>".dkdecimal($balance[$i]*100/$exchangeRate[$i],2)."<br></span></td>\n";
	} else print "<td><br></td>\n";
	print "<td align='center'><span style='color:$color;'>$currencyName[$i]<br></span></td>\n";
	print "<td align='center'><span style='color:$color;'>$shortCut[$i]<br></span></td>\n";
		print "</tr>\n";
	if ($accountNo[$i]=='H') {
		$linjebg=$bgcolor4;
		$color='#000000';
	}
}

if (!$menu =='T') {
print "</tbody>
</table>
	</td></tr>
</tbody></table>";
} else {
print "</tbody></table></div><!-- end of rightContent --></div><!-- end of maincontentLargeHolder -->";
}
?>
</div><!-- end of wrapper --></body></html>
