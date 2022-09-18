<?php
// -- ---------systemdata/stamdata.php------------------ ver 4.0.1 -- 2021-08-20 --
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
// 2012.08.21 Tilføjet leverandørservice - PBS
// 2014.11.20 Opdater mastersystem ved ændring af email.
// 2015.01.23 Indhente virksomhedsdata fra CVR via CVRapi - tak Niels Rune https://github.com/nielsrune
// 20150331 CA  Topmenudesign tilføjet søg 20150331
// 2018.12.20 MSC - Rettet isset fejl
// 20190304 Set countryConfig depending on the users permission
// 20210628 LOE Translated some texts to English and Norsk

@session_start();
$s_id=session_id();
$css="../css/standard.css";

$title="Stamdata";
$modulnr=1;
 
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("stamkort_includes/calcCountryPermissions.php"); # LN 20190304 Use file to handle countryConfig
include("../debitor/pos_ordre_includes/helperMethods/helperFunc.php"); #20190304

if ($menu=='T') {  # 20150331 start
        include_once '../includes/top_header.php';
        include_once '../includes/top_menu.php';
        print "<div id=\"header\">\n";
        print "<div class=\"headerbtnLft\"></div>\n";
        print "</div><!-- end of header -->";
        print "<div id=\"leftmenuholder\">";
        include_once 'left_menu.php';
        print "</div><!-- end of leftmenuholder -->\n";
        print "<div class=\"maincontent\">\n";
        print "<table border=\"1\" cellspacing=\"0\" id=\"dataTable\" class=\"dataTable\"><tbody>";
} else {
        include("top.php");
print "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" align=\"center\"><tbody>";
}  # 20150331 stop


if (!isset ($notes)) $notes = NULL;

if ($_POST) {
    $country = isset($_POST['landeconfig']) ?  $_POST['landeconfig'] : getCountry();
	$id=$_POST['id'];
	$kontonr=trim($_POST['kontonr']);
	$firmanavn=addslashes(trim($_POST['firmanavn']));
	$addr1=addslashes(trim($_POST['addr1']));
	$addr2=addslashes(trim($_POST['addr2']));
	$postnr=addslashes(trim($_POST['postnr']));
	$bynavn=addslashes(trim($_POST['bynavn']));
	$kontakt=addslashes(trim($_POST['kontakt']));
	$tlf=addslashes(trim($_POST['tlf']));
	$fax=addslashes(trim($_POST['fax']));
	$cvrnr=addslashes(trim($_POST['cvrnr']));
	$ans_id=if_isset($_POST['ans_id']);
	$ans_ant=$_POST['ans_ant'];
	$lukket_ant=if_isset($_POST['lukket_ant']);
	$posnr=if_isset($_POST['posnr']);
	$bank_navn=addslashes(trim($_POST['bank_navn']));
	$bank_reg=addslashes(trim($_POST['bank_reg']));
	$bank_konto=addslashes(trim($_POST['bank_konto']));
	$email=addslashes(trim($_POST['email']));
	$ny_email=addslashes(trim($_POST['ny_email']));
	$mailfakt=addslashes(trim(if_isset($_POST['mailfakt'])));
	$vis_lukket=trim(if_isset($_POST['vis_lukket']));
	$pbs_nr=trim($_POST['pbs_nr']);
	$pbs=trim(if_isset($_POST['pbs']));
	$gruppe=if_isset($_POST['gruppe'])*1;
	$fi_nr=trim($_POST['fi_nr']);
	if ($postnr && !$bynavn) $bynavn=bynavn($postnr);
	if ($id==0) {
		$qtxt="insert into adresser"; $qtxt.="(kontonr,firmanavn,addr1,addr2,postnr,bynavn, land,tlf,fax,cvrnr,art,bank_navn,bank_reg,bank_konto,";
		$qtxt.="email,mailfakt,pbs_nr,pbs,bank_fi,gruppe,kontakt)";
		$qtxt.="values"; $qtxt.="('$kontonr','$firmanavn','$addr1','$addr2','$postnr','$bynavn',"; $qtxt.="'$country','$tlf','$fax','$cvrnr','S','$bank_navn','$bank_reg','$bank_konto',";
		$qtxt.="'$ny_email','$mailfakt','$pbs_nr','$pbs','$fi_nr','$gruppe','$kontakt')";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		$qtxt="select id from adresser where art = 'S'";
		$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$id = $r['id'];
	}	elseif ($id > 0) {
		$qtxt = "update adresser set kontonr = '$kontonr',firmanavn = '". db_escape_string($firmanavn) ."',";
		$qtxt.= "addr1 = '". db_escape_string($addr1) ."',addr2 = '". db_escape_string($addr2) ."',";
		$qtxt.= "postnr = '". db_escape_string($postnr) ."',land = '". db_escape_string($country) ."',";
		$qtxt.= "bynavn = '". db_escape_string($bynavn) ."',tlf = '". db_escape_string($tlf) ."',fax = '". db_escape_string($fax) ."',";
		$qtxt.= "cvrnr = '". db_escape_string($cvrnr) ."',bank_navn='". db_escape_string($bank_navn) ."',";
		$qtxt.= "bank_reg='". db_escape_string($bank_reg) ."',bank_konto='". db_escape_string($bank_konto) ."',";
		$qtxt.= "email='". db_escape_string($ny_email) ."',mailfakt='". db_escape_string($mailfakt) ."',";
		$qtxt.= "notes = '". db_escape_string($notes) ."',pbs_nr='". db_escape_string($pbs_nr) ."',pbs='". db_escape_string($pbs) ."',";
		$qtxt.= "bank_fi='". db_escape_string($fi_nr) ."',gruppe='$gruppe',kontakt='". db_escape_string($kontakt) ."'";
		$qtxt.="where art = 'S'";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		for ($x=1; $x<=$ans_ant; $x++) {
			if (($posnr[$x])&&($posnr[$x]!='-')&&($ans_id[$x])){db_modify("update ansatte set posnr = '$posnr[$x]' where id = '$ans_id[$x]'",__FILE__ . " linje " . __LINE__);}
			elseif($ans_id[$x]){ db_modify("delete from ansatte where id = '$ans_id[$x]'",__FILE__ . " linje " . __LINE__);}
		}
		for ($x=1; $x<=$lukket_ant; $x++) {
			if (($posnr[$x])&&($ans_id[$x])){db_modify("update ansatte set posnr = '$posnr[$x]' where id = '$ans_id[$x]'",__FILE__ . " linje " . __LINE__);}
			elseif($ans_id[$x]){ db_modify("delete from ansatte where id = '$ans_id[$x]'",__FILE__ . " linje " . __LINE__);}
		}
	}
	if ($email!=$ny_email) {
		include("../includes/connect.php");
		db_modify("update regnskab set email='$ny_email' where db='$db'",__FILE__ . " linje " . __LINE__); 
		include("../includes/online.php");
	}
}

$saldinames=array('ssl.saldi.dk','ssl2.saldi.dk','ssl3.saldi.dk','ssl4.saldi.dk','udvikling.saldi.dk');
$q = db_select("select * from adresser where art = 'S'",__FILE__ . " linje " . __LINE__);
$r = db_fetch_array($q);
$countryConfig = $r['land'];
$id=$r['id']*1;
$kontonr=$r['kontonr'];
$kontakt=$r['kontakt'];
$firmanavn=$r['firmanavn'];
$addr1=$r['addr1'];
$addr2=$r['addr2'];
$postnr=$r['postnr'];
$bynavn=$r['bynavn'];
#$kontakt=$r['kontakt'];
$tlf=$r['tlf'];
$fax=$r['fax'];
$cvrnr=$r['cvrnr'];
$bank_navn=$r['bank_navn'];
$bank_reg=$r['bank_reg'];
$bank_konto=$r['bank_konto'];
$email=$r['email'];
($r['mailfakt'])? $mailfakt='checked':$mailfakt='';
$pbs_nr=$r['pbs_nr']; 
$pbs=$r['pbs']; 
$fi_nr=$r['bank_fi'];
$smtp=$r['felt_1']; 
$gruppe=$r['gruppe'];
if (!$gruppe) $gruppe=1;
while(strlen($gruppe)<5) $gruppe='0'.$gruppe; 
#	$id=0;

print "<form name=stamkort action=stamkort.php method=post>";
print "<tr><td valign=\"top\">\n"; # 20150331
print "<table border=\"0\" cellspacing=\"0\" class=\"dataTable\"><tbody>"; # 20150331
print "<input type=hidden name=id value='$id'><input type=\"hidden\" name=\"kontonr\" value=\"0\"><input type=hidden name=email value='$email'>";
print "<tr><td>".findtekst(28,$sprog_id)."</td><td><input class=\"inputbox\" type=\"text\" style='width:200;' name=\"firmanavn\" value=\"$firmanavn\"></td></tr>";
print "<tr><td>Adresse</td><td><input class=\"inputbox\" type=\"text\" style='width:200;' name=\"addr1\" value=\"$addr1\"></td></tr>";
print "<tr><td>Adresse2</td><td><input class=\"inputbox\" type=\"text\" style='width:200;' name=\"addr2\" value=\"$addr2\"></td></tr>";
print "<tr><td>".findtekst(363, $sprog_id)."</td><td><input class=\"inputbox\" type=\"text\" size=\"3\" name=\"postnr\" value=\"$postnr\"><input class=\"inputbox\" type=\"text\" size=19 name=bynavn value=\"$bynavn\"></td></tr>";
if(db_fetch_array(db_select("select id from ansatte where konto_id = '$id' and lukket != 'on'",__FILE__ . " linje " . __LINE__))) {
	$tekst=findtekst(1880, $sprog_id); #20210820
	print "<tr><td title = \"".$tekst."\">e-mail/kopi til ref</td><td><input class=\"inputbox\" type=\"text\" style='width:180;' name=\"ny_email\" value=\"$email\"><input  title = \"$tekst\" type=\"checkbox\" name=\"mailfakt\" $mailfakt></td></tr>";
} else {
	print "<tr><td>e-mail</td><td><input class=\"inputbox\" type=\"text\" style='width:180;' name=\"ny_email\" value=\"$email\"></td></tr>";
}
print "<tr><td>Bank</td><td><input class=\"inputbox\" type=\"text\" style='width:200;' name=\"bank_navn\" value=\"$bank_navn\"></td></tr>\n";
print "<tr><td>Email ".findtekst(594, $sprog_id)."</td><td><input class=\"inputbox\" type=\"text\" style='width:200;' name=\"kontakt\" value=\"$kontakt\"></td></tr>";
#cho $_SERVER["SERVER_NAME"]."<br>";
if (in_array($_SERVER["SERVER_NAME"],$saldinames)) {
#	if (substr($db,0,6)=='bizsys' || substr($db,0,7)=='grillbar') {
#		$href='https://bizsys.dk/wp-content/uploads/2018/05/Bizsys-databehandleraftale.pdf';
#	} else 
	$href='http://saldi.dk/dok/saldi_gdpr_20180525.pdf';
	print "<tr><td>Databehandleraftale</td><td><a href=\"$href\" target=\"blank\"><button type='button' style='width:200px;'>Databehandleraftale</button></a></td></tr>";
}
print "</tbody></table>\n"; # 20150331
print "</td>\n"; # 20150331
print "<td valign=\"top\">\n"; # 20150331
print "<table border=\"0\" cellspacing=\"0\" class=\"dataTable\"><tbody>"; # 20150331
print "<tr><td>".findtekst(376, $sprog_id).".</td><td><input class=\"inputbox\" type=\"text\" style='width:150;' name=\"cvrnr\" value=\"$cvrnr\" title=\"Tast CVR-nr. omsluttet af *, +, eller / for at importere data fra Erh
vervsstyrelsen (Data leveres af CVR API)\" style=\"background-image: url('../img/search-white.png'); background-repeat: no-repeat; background-position: right;\"></td></tr>";
print "<tr><td>Telefon</td><td><input class=\"inputbox\" type=\"text\" style='width:150;' name=\"tlf\" value=\"$tlf\" title=\"Tast telefonnr. omsluttet af *, +, eller / for at importere data fra Erhvervsstyrelsen (Data leveres af CVR API)\" style=\"background-image: url('../img/search-white.png'); background-repeat: no-repeat; background-position: right;\"></td></tr>";
print "<tr><td>Telefax</td><td><input class=\"inputbox\" type=\"text\" style='width:150;' name=\"fax\" value=\"$fax\"></td></tr>";
print "<tr><td>".findtekst(385, $sprog_id)." ".findtekst(591, $sprog_id).".</td><td><input class=\"inputbox\" type=\"text\" style='width:150;' name=\"pbs_nr\" value=\"$pbs_nr\">";
if ($pbs_nr) {
	print "<select class=\"inputbox\" name=\"pbs\">";
	if ($pbs=='B') print "<option value=\"B\">Basis løsning</option><option value=\"\">Total løsning</option><option value=\"L\">Lev. Service</option>";
	elseif ($pbs=='L') print "<option value=\"L\">Lev. Service</option><option value=\"B\">Basis løsning</option><option value=\"\">Total løsning</option>";
	else print "<option value=\"\">Total løsning</option><option value=\"B\">Basis løsning</option><option value=\"L\">Lev. Service</option>";
	print "</select></td></tr>";
	print "<tr><td>PBS Debitorgruppe</td><td><input class=\"inputbox\" type=\"text\" style='width:150;' name=\"gruppe\" value=\"$gruppe\">";
}
if (!isset ($returside)) $returside = NULL;
if (!isset ($ordre_id)) $ordre_id = NULL;
if (!isset ($fokus)) $fokus = NULL;
if (!isset ($vis_lukket)) $vis_lukket = NULL;


print "</td></tr>";
#print "<input class=\"inputbox\" type=\"checkbox\" size=10 name=\"pbs\" value=\"$pbs\"></td></tr>";
print "<tr><td>FI ".findtekst(591, $sprog_id).".</td><td><input class=\"inputbox\" type=\"text\" style='width:150;' name=\"fi_nr\" value=\"$fi_nr\"></td></tr>";
print "<td>Reg./".findtekst(592, $sprog_id).".</td><td><input class=\"inputbox\" type=\"text\" style='width:50;' name=\"bank_reg\" value=\"$bank_reg\">";
print "<input class=\"inputbox\" type=\"text\" style='width:100;' name=\"bank_konto\" value=\"$bank_konto\"></td></tr>";

checkUserAndSetCountryConfig($countryConfig, $superUserPermission);

print "<tbody></table></td></tr>";
if ($id) {
	if (! $menu=='T') print "<tr><td colspan=2><hr></td></tr>";  # 20150331
	print "<tr><td colspan=2 align=center><table><tbody>";
	print "<tr><td> ".findtekst(588, $sprog_id)."</td><td> ".findtekst(654, $sprog_id).". / mobil</td><td> E-mail</td><td></td><td align=right><a href=\"ansatte.php?returside=$returside&ordre_id=$ordre_id&fokus=$fokus&konto_id=$id\">".findtekst(39,$sprog_id)." ".findtekst(589,$sprog_id)."</a></td></tr>"; #20210628
	if (! $menu=='T') print "<tr><td colspan='5'><hr></td></tr>";  # 20150331
			
	$taeller=0;
	while ($taeller < 1) {
		$x=0;
		if ($vis_lukket) $qtxt="select * from ansatte where konto_id = '$id' and lukket != 'on' order by posnr";
		else $qtxt="select * from ansatte where konto_id = '$id' and (lukket != 'on' or lukket is NULL) order by posnr";
		$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
		while ($r = db_fetch_array($q)) {
			$x++;
#		if ($x > 0) {print "<tr><td><br></td><td><br></td>";}
			print "<td><input class=\"inputbox\" type=\"text\" size=\"1\" name=\"posnr[$x]\" value=\"$x\">&nbsp;<a href=\"ansatte.php?returside=$returside&ordre_id=$ordre_id&fokus=$fokus&konto_id=$id&id=$r[id]\">$r[navn]</a></td>";
			print "<td>$r[tlf] / $r[mobil]</td><td colspan=2>$r[email]</td></tr>";
			print "<input type=\"hidden\" name=\"ans_id[$x]\" value=\"$r[id]\">";
		}
		if ($vis_lukket!="checked") print "<input type=\"hidden\" name=\"ans_ant\" value=\"$x\">";
		else print "<input type=\"hidden\" name=\"lukket_ant\" value=\"$x\">";
		$taeller++;
		if ($vis_lukket=='on') {
			$vis_lukket="checked";
			$taeller--;
			if (! $menu=='T') print "<tr><td colspan='5'><hr></td></tr>";  # 20150331 
		}	
		if ($taeller>0) {
			if (! $menu=='T') print "<tr><td colspan='5'><hr></td></tr>";  # 20150331
			print "<tr><td> ".findtekst(590, $sprog_id)."<input class=\"inputbox\" type=\"checkbox\" name=\"vis_lukket\" \"$vis_lukket\"></td></tr>";
		}
	}
	print "<tbody></table></td></tr>";
}
if (! $menu=='T') print "<tr><td colspan=2><br></td></tr>\n";  # 20150331
print "<tr><td colspan=2 align=center><input type=\"submit\" accesskey=\"g\" value=\"".findtekst(471, $sprog_id)."\" name=\"submit\"></td>";
?>
</tbody>
</table>
</td></tr>
</tbody></table>
<script language="javascript" type="text/javascript" src="../javascript/cvrapiopslag.js"></script>
</body></html>
