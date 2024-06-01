<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// -------systemdata/logoupload.php-----patch 4.0.8 ----2023-07-22-------
//                           LICENSE
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
//
// Copyright (c) 2003-2023 Saldi.dk ApS
// ----------------------------------------------------------------------
//
// 20131118 PK Har ændret upload af baggrund. Det er nu muligt at vælge forskellige baggrund til Tilbud, Ordrer og Faktura
// 20131118 PK Har fjernet upload af jpg og eps logo og tilføjet pdf bilag til mail (Tilbud, Ordrer og Faktura)
// 20131118 PK Man kan preview og slette den enkelte uploadede fil. Ved preview er der oprettet et nyt document 'view_logoupload.php'
// 20161123 PK Har ændret upload størrelse fra 1mb til 10mb
// 20170224 PHR	Tilføjet mulighed for upload af generel baggrund.
// 20190225 MSC - Rettet topmenu design og isset fejl
// 20210803 LOE - Translated some texts here and included the required file
// 20220615 PHR - Creates folder logolib if not exists

@session_start();
$s_id=session_id();
$css="../css/standard.css";
$title="SALDI - logoindl&aelig;sning";

include("../includes/connect.php");
include("../includes/settings.php");
include("../includes/online.php");
include("../includes/db_query.php");
include("../includes/std_func.php"); #20210803

if (!isset ($_POST['bilagfil'])) $_POST['bilagfil'] = null;

global $db_id;
global $menu;
global $sprog_id; 
print "<div align=\"center\">";
if ($menu=='T') {
	#	print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">";
		include_once '../includes/top_header.php';
		include_once '../includes/top_menu.php';
		print "<div id=\"header\">\n";
		print "<div class=\"headerbtnLft\"></div>\n";
	#	print "<span class=\"headerTxt\">Systemsetup</span>\n";     
	#	print "<div class=\"headerbtnRght\"><!--<a href=\"index.php?page=../debitor/debitorkort.php;title=debitor\" class=\"button green small right\">Ny debitor</a>--></div>";       
		print "</div><!-- end of header -->";
		print "<div id=\"leftmenuholder\">";
		include_once 'left_menu.php';
		print "</div><!-- end of leftmenuholder -->\n";
		print "<div class=\"maincontentLargeHolder\">\n";
		print "<table border=\"1\" cellspacing=\"0\" id=\"dataTable\" class=\"dataTable2\"><tbody>";
	} else {
print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>";
print "<tr><td height = \"25\" align=\"center\" valign=\"top\">";
print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"2\" cellpadding=\"0\"><tbody>";
print "<td width=\"10%\" $top_bund><font face=\"Helvetica, Arial, sans-serif\" color=\"#000066\"><a href=\"formularkort.php\" accesskey=\"L\">".findtekst(30, $sprog_id)."</a></td>"; #20210803
print "<td width=\"80%\" $top_bund><font face=\"Helvetica, Arial, sans-serif\" color=\"#000066\">".findtekst(1745, $sprog_id)."</td>";
print "<td width=\"10%\" $top_bund><font face=\"Helvetica, Arial, sans-serif\" color=\"#000066\"><br></td>";
print "</tbody></table>";
print "</td></tr>";
	}
if (!file_exists("../logolib")) mkdir("../logolib",0777); 
if (!file_exists("../logolib/$db_id")) mkdir("../logolib/$db_id",0777); 

if (isset($_GET['slet_bilag'])) {
	$slet_bilag=$_GET['slet_bilag'].".pdf";
	unlink("../logolib/$db_id/$slet_bilag");
	upload();
	exit;
}
	
if(isset($_POST['bgfil'])||($_POST['bilagfil'])) {
	

	$fejl = $_FILES['uploadedfile']['error'];
	$alert1 = findtekst(1746, $sprog_id);
	if ($fejl) {
		switch ($fejl) {
			case 2: print "<BODY onload=\"javascript:alert('$alert1')\">";
		}
		upload();
		exit;
	}
	if (!isset ($_POST['bilag_valg'])) $_POST['bilag_valg'] = null;
	if (!isset ($_POST['bg_valg'])) $_POST['bg_valg'] = null;
	$bilag_valg = $_POST['bilag_valg'];
	$bg_valg = $_POST['bg_valg'];
	$fil_stoerrelse = $_FILES['uploadedfile']['size'];
	$filetype = $_FILES['uploadedfile']['type'];
	$fileName= $_FILES['uploadedfile']['name'];
	$fra = $_FILES["uploadedfile"]["tmp_name"];
	$fil_stoerrelse = $_FILES["uploadedfile"]["size"];
	($bg_valg)?$valg=$bg_valg:$valg=$bilag_valg;
	//echo "valg: $valg"; exit;
//echo "filtype $filetype<br>";
//echo "filename $fileName<br>";
//echo "bg_valg: $bg_valg";
/*
	if ((strpos($filetype, 'eps'))||(strpos($fileName, '.eps'))) {
		$til = "../logolib/logo_$db_id.eps";
		if($fil_stoerrelse > 500000) {
			$tmp=ceil($fil_stoerrelse);
			print "<BODY onload=\"javascript:alert('Desv&aelig;rre - dit logo er for stort. Der acceptereres kun op til 500 kb, og logoet fylder $tmp kb')\">";
			upload();
			exit;
		} else $filetype="eps";
	} elseif ((strpos($filetype, 'jpeg'))||(strpos($fileName, '.jpg'))||(strpos($fileName, 'jpeg'))) {
		$til = "../logolib/logo_$db_id.jpg";
		if($fil_stoerrelse > 100000) {
			$tmp=ceil($fil_stoerrelse);
			print "<BODY onload=\"javascript:alert('Desv&aelig;rre - dit logo er for stort. Der acceptereres kun op til 100 kb, og logoet fylder $tmp kb')\">";
			upload();
			exit;
		} else $filetype="jpg";
	} else*/if ((strpos($filetype,'pdf'))||(strpos($fileName,'.PDF'))||(strpos($fileName,'pdf'))) {
		if($fil_stoerrelse > 10485760) {
			$tmp=ceil($fil_stoerrelse);
			system ("rm $filename");
			$tmp/=1024;
			$alert = findtekst(1747, $sprog_id);
			print "<BODY onload=\"javascript:alert('$alert $tmp MB')\">";
			upload();
			exit;
		}
		if (!file_exists("../logolib/$db_id")) system ("mkdir ../logolib/$db_id");
		$til = "../logolib/$db_id/$valg.pdf";
	} else {
		$alert1 = findtekst(1748, $sprog_id);
		print "<BODY onload=\"javascript:alert('$alert1')\">";
		//echo "Filformatet er ikke genkendt<br>";
		upload();
		exit;
	}
	if (move_uploaded_file($fra, $til)) {
		if ($filetype=="jpg") {
			$tmp=str_replace(".jpg","",$til);
			$fra=$tmp.".jpg";
			$til=$tmp.".eps";
			if (file_exists($convert)) {
				system ("$convert $fra $til");
				$alert = findtekst(1749, $sprog_id);
				print "<BODY onload=\"javascript:alert('$alert')\">";
				$alert1 = findtekst(1750, $sprog_id);
			} else print "<BODY onload=\"javascript:alert('$alert1')\">";
			unlink ($fra);
 		} else {

 		#			print "<!-- kommentar for at skjule uddata til siden \n";
			$pdftk=system("which pdftk");
#			print "-->\n";
			if ($pdftk) {
				$alert= findtekst(1751, $sprog_id);
				print "<BODY onload=\"javascript:alert('$alert')\">";
				upload();
				exit;
			} elseif (file_exists($pdf2ps)) {
				$pdffil=$til;
				$pdffil=str_replace("../logolib/$db_id/","",$pdffil);
				$psfil=str_replace(".pdf",".ps",$pdffil);
				system ("cd ../logolib/$db_id/\nrm $psfil\n$pdf2ps $pdffil");
				$alert1= findtekst(1751, $sprog_id);
				print "<BODY onload=\"javascript:alert('$alert1')\">";
			}
			else print "<BODY onload=\"javascript:alert('".findtekst(1752, $sprog_id)."')\">";
		}
	} else { $txt1= findtekst(1753, $sprog_id);
		
		print "<BODY onload=\"javascript:alert('$txt1')\">";
		echo "$txt1";
		upload();
	}
} else upload();
print "</tbody></table>";
################################################################################################################
function upload(){
	global $font;
	global $db_id;
	global $sprog_id; #20210803

	if(file_exists("../logolib/$db_id/bg.pdf")) {
		$bg="<a href=\"view_logoupload.php?vis=bg\">".findtekst(1754, $sprog_id)."</a>";
		$txt1= findtekst(1755, $sprog_id);
		$slet_bg="<a href=\"logoupload.php?slet_bilag=bg\" onclick=\"return confirm('$txt1')\">".findtekst(1099, $sprog_id)."</a>";
	} else {
		$bg="<i>".findtekst(1758, $sprog_id)."</i>";
		$slet_bg=NULL;
	}
	if(file_exists("../logolib/$db_id/tilbud_bg.pdf")) {
		$tilbud_bg="<a href=\"view_logoupload.php?vis=tilbud_bg\">".findtekst(1756, $sprog_id)."</a>";
		$txt= findtekst(1757, $sprog_id);
		$slet_tilbud_bg="<a href=\"logoupload.php?slet_bilag=tilbud_bg\" onclick=\"return confirm('$txt')\">".findtekst(1099, $sprog_id)."</a>";
	} else {
		$tilbud_bg="<i>".findtekst(1758, $sprog_id)."</i>";
		$slet_tilbud_bg=NULL;
	}
	if(file_exists("../logolib/$db_id/ordrer_bg.pdf")) {
		$ordrer_bg="<a href=\"view_logoupload.php?vis=ordrer_bg\">".findtekst(1759, $sprog_id)."</a>";
		$txt1=findtekst(1760, $sprog_id);
		$slet_ordrer_bg="<a href=\"logoupload.php?slet_bilag=ordrer_bg\" onclick=\"return confirm('$txt1')\">".findtekst(1099, $sprog_id)."</a>";
	} else {
		$ordrer_bg="<i>".findtekst(1758, $sprog_id)."</i>";
		$slet_ordrer_bg=NULL;
	}
	if(file_exists("../logolib/$db_id/faktura_bg.pdf")) {
		$txt= findtekst(1762, $sprog_id);
		$faktura_bg="<a href=\"view_logoupload.php?vis=faktura_bg\">".findtekst(1761, $sprog_id)."</a>";
		$slet_faktura_bg="<a href=\"logoupload.php?slet_bilag=faktura_bg\" onclick=\"return confirm('$txt')\">".findtekst(1099, $sprog_id)."</a>";
	} else {
		$faktura_bg="<i>".findtekst(1758, $sprog_id)."</i>";
		$slet_faktura_bg=NULL;
	}
	if(file_exists("../logolib/$db_id/tilbud_bilag.pdf")) {
		$txt1 = findtekst(1764, $sprog_id);
		$tilbud_bilag="<a href=\"view_logoupload.php?vis=tilbud_bilag\">".findtekst(1763, $sprog_id)."</a>";
		$slet_tilbud_bilag="<a href=\"logoupload.php?slet_bilag=tilbud_bilag\" onclick=\"return confirm('$txt1')\">".findtekst(1099, $sprog_id)."</a>";
	} else {
		$tilbud_bilag="<i>".findtekst(1767, $sprog_id)."</i>";
		$slet_tilbud_bilag=NULL;
	}
	if(file_exists("../logolib/$db_id/ordrer_bilag.pdf")) {
		$txt = findtekst(1766, $sprog_id);
		$ordrer_bilag="<a href=\"view_logoupload.php?vis=ordrer_bilag\">".findtekst(1765, $sprog_id)."</a>";
		$slet_ordrer_bilag="<a href=\"logoupload.php?slet_bilag=ordrer_bilag\" onclick=\"return confirm('$txt')\">".findtekst(1099, $sprog_id)."</a>";
	} else {
		$ordrer_bilag="<i>".findtekst(1767, $sprog_id)."</i>";
		$slet_ordrer_bilag=NULL;
	}
	if(file_exists("../logolib/$db_id/faktura_bilag.pdf")) {
		$txt1 = findtekst(1769, $sprog_id);
		$faktura_bilag="<a href=\"view_logoupload.php?vis=faktura_bilag\">".findtekst(1768, $sprog_id)."</a>";
		$slet_faktura_bilag="<a href=\"logoupload.php?slet_bilag=faktura_bilag\" onclick=\"return confirm('$txt1')\">".findtekst(1099, $sprog_id)."</a>"; #20210803
	} else {
		$faktura_bilag="<i>".findtekst(1767, $sprog_id)."</i>";
		$slet_faktura_bilag=NULL;
	}
	print "<tr><td width=\"100%\" align=\"center\">";
	print "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">";
	print "<colgroup>
						<col width=\"15%\">
						<col width=\"20%\">
						<col width=\"30%\">
						<col width=\"5%\">
						<col width=\"12%\">
						<col width=\"3%\">
						<col width=\"15%\">
				</colgroup>";
	print "<tbody>";
	//print "<tr><td>&nbsp;</td><td colspan=\"5\" align=center>$font Du har mulighed for at oploade en logo i form af en jpg eller eps fil. </td><td>&nbsp;</td></tr>";
	//print "<tr><td>&nbsp;</td><td colspan=\"5\" align=center>$font Eller du kan lave en hel side i PDF format og bruge den som baggrund for tilbud, ordrer og fakturaer</td><td>&nbsp;</td></tr>";
	//print "<tr><td>&nbsp;</td><td colspan=\"5\"align=center>$font Brug f.eks <a href=\"http://da.libreoffice.org\" target=\"blank\">Libre Office</a> som kan gemme direkte til PDF</td><td>&nbsp;</td></tr>";
	//print "<tr><td>&nbsp;</td><td colspan=\"5\"align=center>$font Max str. er 100 kb for jpg og 500 kb for eps &amp; PDF<br><br><br><hr width=\"100%\"><br></td><td>&nbsp;</td></tr>";
	print "<tr><td colspan=\"2\">&nbsp;</td><td align=\"justify\">$font ".findtekst(1770, $sprog_id)."<br><br>".findtekst(1771, $sprog_id)."<br><br>".findtekst(1772, $sprog_id)." <a href=\"http://da.libreoffice.org\" target=\"blank\">Libre Office</a> ".findtekst(1773, $sprog_id)."<br>".findtekst(1774, $sprog_id)."<br><br></td><td colspan=\"4\">&nbsp;</td></tr>";
	print "<tr><td>&nbsp;</td><td colspan=\"5\" align=\"center\">$font<hr width=\"100%\"><br></td><td>&nbsp;</td></tr>";
	print "</tbody>";

	print "<form enctype=\"multipart/form-data\" action=\"logoupload.php\" method=\"POST\">";
	print "<tbody>";
	print "<tr><td>&nbsp;";
	print "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10000000\">";
	print "<input type=\"hidden\" name=\"filtype\" value='PDF'></td>";
	print "<td align=left>$font ".findtekst(1775, $sprog_id)."</td>";
	print "<td><select name=\"bg_valg\">
					<option value=\"bg\">".findtekst(1776, $sprog_id)."</option>
					<option value=\"tilbud_bg\">".findtekst(812, $sprog_id)."</option>
					<option value=\"ordrer_bg\">".findtekst(107, $sprog_id)."</option>
					<option value=\"faktura_bg\">".findtekst(1777, $sprog_id)."</option>
				</select>";
	print "<input name=\"uploadedfile\" type=\"file\" /><br /></td><td>$font ".findtekst(1776, $sprog_id)."</td><td>$font $bg&nbsp;</td><td>$font $slet_bg&nbsp;</td><td>&nbsp;</td></td></tr>";
	print "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>$font ".findtekst(812, $sprog_id)."</td><td>$font $tilbud_bg&nbsp;</td><td>$font $slet_tilbud_bg&nbsp;</td><td>&nbsp;</td></tr>";
	print "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>$font ".findtekst(107, $sprog_id)."</td><td>$font $ordrer_bg&nbsp;</td><td>$font $slet_ordrer_bg&nbsp;</td><td>&nbsp;</td></tr>";
	print "<tr><td>&nbsp;</td><td>&nbsp;</td><td align=center><input class='button green medium' type=\"submit\" name=\"bgfil\" value=\"".findtekst(1360, $sprog_id)."\"></td><td>$font ".findtekst(1777, $sprog_id)."</td><td>$font $faktura_bg&nbsp;</td><td>$font $slet_faktura_bg&nbsp;</td><td>&nbsp;</td></tr>";
	//print "<tr><td width=20%>&nbsp;</td><td>&nbsp;</td><td width=20%>&nbsp;</td></tr>";
	print "<tr><td>&nbsp;</td><td colspan=\"5\" align=\"center\"><br><hr width=\"100%\"><br></td><td>&nbsp;</td></tr>";
	print "</tbody>";
	print "</form>";
	
	print "<form enctype=\"multipart/form-data\" action=\"logoupload.php\" method=\"POST\">";
	print "<tbody>";
	print "<tr><td>&nbsp;";
	print "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10000000\">";
	print "<input type=\"hidden\" name=\"filtype\" value='logo'></td>";
	print "<td align=left>$font ".findtekst(1778, $sprog_id)."</td>";
	print "<td><select name=\"bilag_valg\">
					<option value=\"tilbud_bilag\">".findtekst(812, $sprog_id)."</option>
					<option value=\"ordrer_bilag\">".findtekst(107, $sprog_id)."</option>
					<option value=\"faktura_bilag\">".findtekst(1777, $sprog_id)."</option>
				</select>";
	print "<input name=\"uploadedfile\" type=\"file\" /><br /></td><td>$font ".findtekst(812, $sprog_id)."</td><td>$font $tilbud_bilag&nbsp;</td><td>$font $slet_tilbud_bilag&nbsp;</td><td>&nbsp;</td></tr>";
	print "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>$font ".findtekst(107, $sprog_id)."</td><td>$font $ordrer_bilag&nbsp;</td><td>$font $slet_ordrer_bilag&nbsp;</td><td>&nbsp;</td></tr>";
	print "<tr><td>&nbsp;</td><td>&nbsp;</td><td align=\"center\"><input class='button green medium' type=\"submit\" name=\"bilagfil\" value=\"".findtekst(1360, $sprog_id)."\"></td><td width=5%>$font ".findtekst(1777, $sprog_id).":</td><td>$font $faktura_bilag&nbsp;</td><td>$font $slet_faktura_bilag&nbsp;</td><td>&nbsp;</td></tr>";
	//print "<tr><td width=20%>&nbsp;</td><td>&nbsp;</td><td width=20%>&nbsp;</td></tr>";
	print "<tr><td>&nbsp;</td><td colspan=\"5\" align=\"center\"><br><hr width=\"100%\"><br></td><td>&nbsp;</td></tr>";
	print "</tbody>";
	print "</form>";
	
	print "</table>";
	print "</td></tr>";
}



print "</tbody></table>";
print "</td></tr>";

?>
