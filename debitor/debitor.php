<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- debitor/debitor.php --- lap 4.0.1 --- 2021-03-12 ----
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
// 2013.02.10 Break ændret til break 1
// 2016.02.18 Udvælg fungerer nu også hvis debitor er med i flere kategorier. Søg 20160218
// 2016.06.06 Tilføjet mulighed for at skjule lukkede debitorer Søg box11 / skjul_lukkede
// 2018.12.05 Definering af variabler.
// 2018.12.17 msc Rettet design til
// 2019.01.07 MSC Rettet topmenu design til
// 2019.02.13 MSC - Rettet topmenu design til
// 2019.09.20 PHR - All search fiels was set to '0' if not set. Chanced to NULL
// 2020.05.14	PHR - Added option 'Kommission' 
// 2020.05.31	PHR - replaced 'addslashes' with 'db_escape_string' 
// 2020.06.23	PHR - various changes related to 'kommission' 
// 2020.10.25	PHR	- Added option for creating own mailtext for mySale - $mailText
// 2020.11.11	PHR	- Added ordinary mail til customers not using MySale
// 2021.01.13	PHR	- Added links written to file if mysale is active.
// 2021.01.25 PHR - Removed last change - link now written to table mysale in master DB. 
// 2021.03.12 PHR - added 'postnr' to numfelter.

#ob_start();
@session_start();
$s_id=session_id();

$adresseantal=$check_all=$hrefslut=$javascript=$kontoid=$linjebg=$linjetext=$nextpil=$ny_sort=$prepil=$tidspkt=$understreg=$udv2=NULL;
$find=$dg_id=$dg_navn=$selectfelter=array();

print "
<script LANGUAGE=\"JavaScript\">
<!--
function MasseFakt(tekst)
{
	var agree = confirm(tekst);
	if (agree)
		return true ;
	else
    return false ;
}
// -->
</script>
";
$css="../css/standard.css";
$modulnr=6;
$title="Debitorliste";
$firmanavn=NULL; 

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/udvaelg.php");
	
$id = if_isset($_GET['id']);
$returside=if_isset($_GET['returside']);
$valg= strtolower(if_isset($_GET['valg']));
$sort = if_isset($_GET['sort']);
$start = if_isset($_GET['start']);
$nysort = if_isset($_GET['nysort']);
$invite=$mailTo=$mySale=array();

if (!$valg) $valg="debitor";
if ((isset($_POST['kommission']) || isset($_POST['historik'])) && $_POST['debId']) {
	$debId=$_POST['debId'];
	if (isset($_POST['mySale'])) $mySale=$_POST['mySale'];
	if (isset($_POST['invite'])) $invite=$_POST['invite'];
	if (isset($_POST['mailTo'])) $mailTo=$_POST['mailTo'];
	$start*=1;

	for ($i=0;$i<count($debId);$i++) {
		$qtxt="select id,kontonr,firmanavn,email from adresser where id='$debId[$i]'";
		$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$custId[$i]   = $r['id'];
		$custNo[$i]   = $r['kontonr'];
		$custName[$i] = $r['firmanavn'];
		$custMail[$i] = $r['email'];
		if (!isset($mySale[$i])) $mySale[$i]=NULL;
		if (!isset($invite[$i])) $invite[$i]=NULL;
		if ($mySale[$i] || $invite[$i]) {
		#			db_modify("update adresser set mysale='on' where id = '$debId[$i]'",__FILE__ . " linje " . __LINE__);
			$tmp=trim($_SERVER['PHP_SELF'],'/');
			list ($folder,$tmp)=explode('/',$tmp,2);
			$lnk[$i]="https://". $_SERVER['HTTP_HOST'] .'/'. $folder ."/mysale/mysale.php?id=";
			$lnk[$i]=str_replace('bizsys','mysale',$lnk[$i]);
			$txt = $custId[$i] .'|'. $custNo[$i] .'@'. $db  .'@'. $_SERVER['HTTP_HOST'];
			for ($x=0;$x<strlen($txt);$x++) {
				$lnk[$i].=dechex(ord(substr($txt,$x,1)));
			}
		} # 	else db_modify("update adresser set mysale='' where id = '$debId[$i]'",__FILE__ . " linje " . __LINE__);

	}
	include("../includes/connect.php");
#			$qtxt = "CREATE TABLE mysale (id serial NOT NULL,deb_id int, db varchar(20), email varchar(60), link text, PRIMARY KEY (id))";
#			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	
	$x=0;
	$qtxt="select * from mysale where db='$db'";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$myId[$x]=$r['id'];
		$myAccId[$x]=$r['deb_id'];
		$x++;
	}
	for ($i=0;$i<count($debId);$i++) {
		if (in_array($debId[$i],$myAccId)) {
			for ($x=0;$x<count($myAccId);$x++) {
				if ($myAccId[$x] == $debId[$i]) {
					if ($mySale[$i] || $invite[$i]) $qtxt = "update mysale set email = '$custMail[$i]', link = '$lnk[$i]'";
					else $qtxt = "update mysale set email = '', link = ''";
					$qtxt.= " where id = $myId[$x]";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
			}
		} elseif ($mySale[$i] || $invite[$i]) {
			$qtxt="insert into mysale (deb_id,db,email,link) values ('$debId[$i]','$db','$custMail[$i]','$lnk[$i]')";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
	include("../includes/online.php");
	if((count($invite) || count($mailTo)) && !class_exists('phpmailer')) {
		ini_set("include_path", ".:../phpmailer");
		require("class.phpmailer.php");
	}
	$qtxt="select * from adresser where art='S'";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$afsendermail=$r['email'];
	$afsendernavn=$r['firmanavn'];
	$from=$afsendermail;
	
	($r['felt_1'])?$smtp=$r['felt_1']:$smtp='localhost';
	($r['felt_2'])?$smtp_user=$r['felt_2']:$smtp_user=NULL;
	($r['felt_3'])?$smtp_pwd=$r['felt_3']:$smtp_pwd=NULL;
	($r['felt_4'])?$smtp_enc=$row['felt_4']:$smtp_enc=NULL;

	for ($i=0;$i<count($debId);$i++) {
		if (!isset($invite[$i])) $invite[$i]=NULL;
		if (!isset($mailTo[$i])) $mailTo[$i]=NULL;
		if ($invite[$i] || $mailTo[$i]) {
			if ($invite[$i]) db_modify("update adresser set mysale='$invite[$i]' where id = '$debId[$i]'",__FILE__ . " linje " . __LINE__);
			$qtxt="select * from adresser where art='S'";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));

			if ($invite[$i]) { 
				$myLink="<a href='$lnk[$i]'>Mit Salg</a>";
				$mailText = "Kære $custName[$i],<br><br>Klik på nedestående link for at se dit salg.<br><br>";
				$mailText.= "$myLink<br><br>";
				$mailText.= "Bedste hilsner<br>$afsendernavn<br>";
				$varGrp='mySale';
			} else {
				$varGrp='debitor';
			}
			$qtxt="select var_value from settings where var_name = 'mailSubject' and var_grp = '$varGrp'";
			$r2=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			if ($r2['var_value']) $subject=$r2['var_value'];
			else $subject = "Adgang til dit salg hos $afsendernavn";
			$qtxt="select var_value from settings where var_name = 'mailText' and var_grp = '$varGrp'";
			$r2=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			if ($r2['var_value']) {
				$mailText=$r2['var_value'];
				$mailText=str_replace("\n","<br>",$mailText);
				$mailText=str_replace('$kunde',$custName[$i],$mailText);
				$mailText=str_replace('$link',$myLink,$mailText);
			}
			if ($charset=="UTF-8") {
				$subject=utf8_decode($subject);
				$mailText=utf8_decode($mailText);
				$afsendernavn=utf8_decode($afsendernavn);
			}
			$mail = new PHPMailer();
			$mail->IsSMTP();                                   // send via SMTP
			$mail->SMTPDebug  = 2;
			$mail->Host  = $smtp; // SMTP servers 
			if ($smtp!='localhost') {
				if ($smtp_user) {
					$mail->SMTPAuth = true;     // turn on SMTP authentication
					$mail->Username = $smtp_user;  // SMTP username
					$mail->Password = $smtp_pwd; // SMTP password
					if ($smtp_enc) $mail->SMTPSecure = $smtp_enc; // SMTP kryptering
				}
			} else {
				$mail->SMTPAuth = false;
				if (strpos($_SERVER['SERVER_NAME'],'saldi.dk')) { #20121016
					$from = $db.'@'.$_SERVER['SERVER_NAME']; #20130731
					$from = str_replace('bizsys','post',$db);
				}
			}
			if ($subject && $mailText) {
				$mail->From = $from;
				$mail->FromName = $afsendernavn;
				$mail->AddReplyTo($afsendermail);
				$mail->AddAddress($custMail[$i]);
				$mail->WordWrap = 50;  // set word wrap
				$mail->IsHTML(true);   // send as HTML
				$ren_text=html_entity_decode($mailText,ENT_COMPAT,$charset);
				$ren_text=str_replace("<br>","\n",$ren_text);
				$ren_text=str_replace("<b>","*",$ren_text);
				$ren_text=str_replace("</b>","*",$ren_text);
				$ren_text=str_replace("<a href='$lnk'>". $lnk ."</a>"," $lnk ",$ren_text);
				$ren_text=str_replace("<hr>","------------------------------",$ren_text);
				$mail->Subject  =  "$subject";
				$mail->Body     =  "$mailText";
				$mail->AltBody  =  "$ren_text";
				$svar=NULL;
				print "<!--";
				if(!$mail->Send()){
					$svar = "Mailer Error: " . $mail->ErrorInfo;
				}
				print "-->";
				if ($svar) {
					echo $svar."<br>";
					exit;
				}
				echo "Mail sendt til $custName[$i] &lt;$custMail[$i]&gt;<br>";
				flush();
				usleep (250000);
			}
		} else {
			if (!isset($mySale[$i])) $mySale[$i]=NULL;
			db_modify("update adresser set mysale='$mySale[$i]' where id = '$debId[$i]'",__FILE__ . " linje " . __LINE__);
		}
	}
	print "<meta http-equiv='refresh' content='2'>";
}

$sort=str_replace("adresser.","",$sort);
if ($sort && $nysort==$sort) $sort=$sort." desc";
elseif ($nysort) $sort=$nysort;
$r=db_fetch_array(db_select("select box7 from grupper where art = 'DIV' and kodenr = '2'",__FILE__ . " linje " . __LINE__));
$jobkort=$r['box7'];
$qtxt = "select var_value from settings where var_grp='debitor' and var_name='mySale'";
($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)))?$showMySale=trim($r['var_value']):$showMySale=NULL;
$qtxt = "select var_value from settings where var_grp='rental'";
($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)))?$showRental=trim($r['var_value']):$showRental=NULL;
$x = 0;
$qtxt = "select id,box3,box6 from grupper where art = 'DLV' and kode='$valg' and kodenr = '$bruger_id'";
$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
while ($r=db_fetch_array($q)) {
	if ($x > 0) db_modify("delete from grupper where id='$r[id]'",__FILE__ . " linje " . __LINE__);
	elseif ($valg=='kommission' && date('Y-m') == '2020-06' && substr($r['box6'],-4)=='lger') {
		$box3 = "kontonr".chr(9)."firmanavn".chr(9)."addr1".chr(9)."addr2".chr(9)."postnr".chr(9);
		$box3.= "bynavn".chr(9)."kontakt".chr(9)."tlf".chr(9)."invoiced";
		$box6 = "Kontonr".chr(9)."Firmanavn".chr(9)."Adresse".chr(9)."Adresse 2".chr(9);
		$box6.= "Postnr".chr(9)."By".chr(9)."Kontakt".chr(9)."Telefon".chr(9)."Sidste faktura";
		db_modify("update grupper set box3 = '$box3',box6 = '$box6' where id='$r[id]'",__FILE__ . " linje " . __LINE__);
	} 
	$x++;
}
if ($x == 0) {
	$box7 = 100;
	if ($valg=='debitor') {
		$box3 = "kontonr".chr(9)."firmanavn".chr(9)."addr1".chr(9)."addr2".chr(9)."postnr".chr(9);
		$box3.= "bynavn".chr(9)."kontakt".chr(9)."tlf";
		$box5 = "right".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left";
		$box4 = "5".chr(9)."35".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10";
		$box6 = "Kontonr".chr(9)."Firmanavn".chr(9)."Adresse".chr(9)."Adresse 2".chr(9);
		$box6.= "Postnr".chr(9)."By".chr(9)."Kontakt".chr(9)."Telefon";
	} elseif ($valg=='kommission') {
		$box3 = "kontonr".chr(9)."firmanavn".chr(9)."addr1".chr(9)."addr2".chr(9)."postnr".chr(9);
		$box3.= "bynavn".chr(9)."kontakt".chr(9)."tlf".chr(9)."invoiced";
		$box5="right".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left";
		$box4="5".chr(9)."35".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10";
		$box6 = "Kontonr".chr(9)."Firmanavn".chr(9)."Adresse".chr(9)."Adresse 2".chr(9);
		$box6.= "Postnr".chr(9)."By".chr(9)."Kontakt".chr(9)."Telefon".chr(9)."Sidste faktura";
	} elseif ($valg=='rental') {
		$box3 = "kontonr".chr(9)."firmanavn".chr(9)."addr1".chr(9)."addr2".chr(9)."postnr".chr(9)."bynavn";
		$box4 = "5".chr(9)."35".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10";
		$box5 = "right".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left";
		$box7 = 50;
	} else {
		$box3 = "kontonr".chr(9)."firmanavn".chr(9)."addr1".chr(9)."addr2".chr(9)."postnr".chr(9);
		$box3.= "bynavn".chr(9)."kontakt".chr(9)."tlf";
		$box5="right".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left".chr(9)."left";
		$box4 = "5".chr(9)."35".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10".chr(9)."10";
		$box6 = "Kontonr".chr(9)."Firmanavn".chr(9)."Adresse".chr(9)."Adresse 2".chr(9);
		$box6.= "Postnr".chr(9)."By".chr(9)."Kontakt".chr(9)."Telefon";
	}
	$qtxt = "insert into grupper(beskrivelse,kode,kodenr,art,box3,box4,box5,box6,box7) values ";
	$qtxt.= "('debitorlistevisning','$valg','$bruger_id','DLV','$box3','$box4','$box5','$box6','$box7')";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
} else {
	$qtxt="select box1,box2,box7,box9,box10,box11 from grupper where art = 'DLV' and kode='$valg' and kodenr = '$bruger_id'";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)); 
	$dg_liste=explode(chr(9),$r['box1']);
	$cat_liste=explode(chr(9),$r['box2']);
	$skjul_lukkede=$r['box11'];
	$linjeantal=$r['box7'];
	if (!$sort) $sort=$r['box9'];
	$find=explode("\n",$r['box10']);
}
	
if ($popup) $returside= "../includes/luk.php";
else $returside= "../index/menu.php";

db_modify("update grupper set box9='$sort' where art = 'DLV' and kode='$valg' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__);
		
$tidspkt=date("U");
 
if ($search=if_isset($_POST['search'])) {
	$find=if_isset($_POST['find']);
	$valg=if_isset($_POST['valg']);
	$sort = if_isset($_POST['sort']);
	$nysort = if_isset($_POST['nysort']);
	$firma=if_isset($_POST['firma']);
}


if (!$valg) $valg = "debitor";
if (!$sort) $sort = "firmanavn";

$sort=str_replace("adresser.","",$sort);
$sortering=$sort;

if ($menu=='T') {
	include_once '../includes/top_header.php';
	include_once '../includes/top_menu.php';
	if ($valg=='debitor') {
	print "<div id=\"header\"> 
		<div class=\"headerbtnLft\"></div>
		<span class=\"headerTxt\">Konti</span>";     
	print "<div class=\"headerbtnRght\"><a href=\"debitorkort.php?returside=debitor.php\" class=\"button green small right\">Ny</a></div>";
	} if ($valg=='historik') {
	print "<div id=\"header\"> 
		<div class=\"headerbtnLft\"></div>
		<span class=\"headerTxt\">Historik</span>";     
	print "<div class=\"headerbtnRght\"></div>";
	}
	print "</div><!-- end of header -->
	<div class=\"maincontentLargeHolder\">\n";
	
	} else {
print "<table width=100% height=100% border=0 cellspacing=0 cellpadding=0><tbody>\n";
print "<tr><td height = 25 align=center valign=top>";
print "<table width=100% align=center border=0 cellspacing=2 cellpadding=0><tbody><td width=10% $top_bund>\n";
print "<a href=$returside accesskey=L>Luk</a></td>";
print "<td width=80% $top_bund align=center><table border=0 cellspacing=2 cellpadding=0><tbody>\n";

if ($valg=='debitor') print "<td width = 20% align=center $knap_ind>&nbsp;Debitorer&nbsp;</td>";
else print "<td width = 20% align=center><a href='debitor.php?valg=debitor&returside=$returside'>&nbsp;Debitorer&nbsp;</a></td>";
if ($valg=='historik') print "<td width = 20% align=center $knap_ind>&nbsp;Historik&nbsp;</td>";
else print "<td width = 20% align=center><a href='debitor.php?valg=historik&returside=$returside'>&nbsp;Historik&nbsp;</a></td>";
	if ($valg=='kommission') print "<td width = 20% align=center $knap_ind>&nbsp;Kommission&nbsp;</td>";
	elseif ($showMySale) {
		print "<td width = 20% align=center><a href='debitor.php?valg=kommission&returside=$returside'>&nbsp;Kommission&nbsp;</a></td>";
	}	
	if ($valg=='rental') print "<td width = 20% align=center $knap_ind>&nbsp;Booking&nbsp;</td>";
	elseif ($showRental) {
		print "<td width = 20% align=center><a href='debitor.php?valg=rental&returside=$returside'>&nbsp;Booking&nbsp;</a></td>";
	}	
	$title="Klik her for at skifte til joblisten";
	if ($jobkort)	print "<td width = 20% align=center><a href='jobliste.php' title ='$title'>".findtekst(38,$sprog_id)."</a></td>";
print "</tbody></table></td>\n";
print "<td width=5% $top_bund><a accesskey=V href=debitorvisning.php?valg=$valg>Visning</a></td>\n";
	print "<td width=5%  $top_bund>";
	if ($valg=='kommission' ||$valg=='historik') print "<a href=mailTxt.php?valg=$valg&returside=debitor.php>Mailtekst</a></td>\n";
	else print "<a href=debitorkort.php?returside=debitor.php>Ny</a></td>\n";
print "</td></tr>\n";
print "</tbody></table>";
print " </td></tr>\n<tr><td align=\"center\" valign=\"top\" width=\"100%\">";
	}

$r = db_fetch_array(db_select("select box3,box4,box5,box6,box8,box11 from grupper where art = 'DLV' and kodenr = '$bruger_id' and kode='$valg'",__FILE__ . " linje " . __LINE__));
$vis_felt=explode(chr(9),$r['box3']);
$feltbredde=explode(chr(9),$r['box4']);
$justering=explode(chr(9),$r['box5']);
$feltnavn=explode(chr(9),$r['box6']);
$vis_feltantal=count($vis_felt);
$select=explode(chr(9),$r['box8']);

$y=0;
for ($x=0;$x<=$vis_feltantal;$x++) {
	if (isset($select[$x]) && isset($vis_felt[$x]) && $select[$x] && $vis_felt[$x]) {
		$selectfelter[$y]=$vis_felt[$x];
		$y++;
	}
}
$numfelter=array("rabat","momskonto","kreditmax","betalingsdage","gruppe","kontoansvarlig","postnr","kontonr");
####################################################################################
$udvaelg=NULL;
$tmp=trim($find[0]);
for ($x=1;$x<$vis_feltantal;$x++) {
	if (isset($find[$x])) {
		$tmp=$tmp."\n".trim($find[$x]);
	}
}
$qtxt="update grupper set box10='". db_escape_string($tmp) ."' where art = 'DLV' and kode='$valg' and kodenr = '$bruger_id'";
db_modify($qtxt,__FILE__ . " linje " . __LINE__);

if ($skjul_lukkede) $udvaelg = " and lukket != 'on'";
for ($x=0;$x<$vis_feltantal;$x++) {
	if (isset($find[$x])) {
		$find[$x]=trim($find[$x]);
	$tmp=$vis_felt[$x];
		if ($tmp) {
			if ($find[$x] && $tmp=='invoiced') {
				$tmp2="adresser.".$tmp;
				$udvaelg.=udvaelg($find[$x],$tmp2, 'DATO');
			} elseif ($find[$x] && !in_array($tmp,$numfelter)) {
				$tmp2="adresser.".$tmp;
				$udvaelg.=udvaelg($find[$x],$tmp2, '');
	} elseif ($find[$x]||$find[$x]=="0") {
				$tmp2="adresser.".$tmp;
				$udvaelg.=udvaelg(db_escape_string($find[$x]),$tmp2, 'NR');
			}
		}
	}
}

if (count($dg_liste)) {
	$x=0;
	$q=db_select("select * from grupper where art = 'DG' order by beskrivelse",__FILE__ . " linje " . __LINE__);
	while ($r=db_fetch_array($q)) {
		$x++;
		$dg_id[$x]=$r['id'];
		$dg_kodenr[$x]=$r['kodenr']*1;
		$dg_navn[$x]=$r['beskrivelse'];
	}
	$dg_antal=$x;
}

if (count($cat_liste)) {
	$r=db_fetch_array(db_select("select box1,box2 from grupper where art='DebInfo'",__FILE__ . " linje " . __LINE__));
	$cat_id=explode(chr(9),$r['box1']);
	$cat_beskrivelse=explode(chr(9),$r['box2']);
	$cat_antal=count($cat_id);
}

$sortering="adresser.".$sortering;

$ialt=0;
$lnr=0;
if (!$linjeantal) $linjeantal=100;
$slut=$start+$linjeantal;
$adresserantal=0;

$r=db_fetch_array(db_select("select count(id) as antal from adresser where art = 'D' $udvaelg",__FILE__ . " linje " . __LINE__));
$antal=$r['antal'];
if ($menu=='T'){
	print "<table class='dataTable' cellpadding='1' cellspacing='1' border='0' valign='top' width='100%'><tbody>\n<tr>";
} else {
	print "<table cellpadding='1' cellspacing='1' border='0' valign='top' width='100%'><tbody>\n<tr>";
}
if ($start>0) {
	$prepil=$start-$linjeantal;
	if ($prepil<0) $prepil=0;
	print "<td><a href=debitor.php?start=$prepil&valg=$valg><img src=../ikoner/left.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td>";
} else {
	print "<td>";
	if (file_exists("rotary_addrsync.php")) print "<a href=\"rotary_addrsync.php\" target=\"blank\" title=\"Klik her for at synkronisere medlemsinfo\">!</a>";
	print "</td>";
}
if ($valg != 'rental' || $start == 0) {
for ($x=0;$x<$vis_feltantal;$x++) {
	if ($feltbredde[$x]) $width="width=$feltbredde[$x]";
	else $width="";
	print "<td align=$justering[$x] $width><b><a href='debitor.php?nysort=$vis_felt[$x]&sort=$sort&valg=$valg'>$feltnavn[$x]</b></td>\n";
}
}
if ($valg=='kommission') {
	$tmp=trim($_SERVER['PHP_SELF'],'/');
	list ($folder,$tmp)=explode('/',$tmp,2);
	$myLink="https://". $_SERVER['HTTP_HOST'] .'/'. $folder ."/mysale/mysale.php?id=";
	$myLink=str_replace('bizsys','mysale',$myLink);
	print "<td align='center'><b>Aktiv</b></td>";
	print "<td align='center'><b>Inviter</b></td>";
}
if ($antal>$slut && !$dg_liste[0] && !$cat_liste[0]) {
	$nextpil=$start+$linjeantal;
	print "<td align=right><a href=debitor.php?start=$nextpil&valg=$valg><img src=../ikoner/right.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td><tr>";
}
print "</tr>\n";
if ($dg_antal || $cat_antal) $linjeantal=0;
#################################### Sogefelter ##########################################



print "<tr><td></td>"; #giver plase til venstrepil v. flere sider
if (!$start) {
print "<form name=debitorliste action=debitor.php method=post>\n";
print "<input type=hidden name=valg value=$valg>\n";
print "<input type=hidden name=sort value='$ny_sort'>\n";
print "<input type=hidden name=nysort value='$sort'>\n";
print "<input type=hidden name=kontoid value=$kontoid>\n";

	for ($x=0;$x<$vis_feltantal;$x++) {
		$span=''; 
		if (!isset($feltbredde[$x])) $feltbredde[$x]=0;
		if (!isset($justering[$x])) $justering[$x]=0;
		if (!isset($find[$x])) $find[$x]=NULL;
		print "<td align=$justering[$x]><span title= '$span'>";
		if ($vis_felt[$x]=="kontoansvarlig") {
			$ansat_id=array();$ansat_init=array();
			$y=0;
			$qtxt = "select distinct(ansatte.id) as ansat_id,ansatte.initialer as initialer from ansatte,adresser where ";
			$qtxt.= "adresser.art='S' and ansatte.konto_id=adresser.id order by ansatte.initialer";
			$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
			while ($r=db_fetch_array($q)) {
				$y++;
				$ansat_id[$y]=$r['ansat_id'];
				$ansat_init[$y]=$r['initialer'];
			}
			$ansatantal=$y;
			if (in_array($vis_felt[$x],$selectfelter)) {
				print "<SELECT NAME=\"find[$x]\">";
				if (!$find[$x]) print "<option value=\"\"></option>";
				for ($y=1;$y<=$ansatantal;$y++) if ($ansat_init[$y] && $find[$x]==$ansat_id[$y]) print "<option value=\"$ansat_id[$y]\">".stripslashes($ansat_init[$y])."</option>";
				if ($find[$x]) print "<option value=\"\"></option>";
				for ($y=1;$y<=$ansatantal;$y++) if ($ansat_init[$y] && $find[$x]!=$ansat_id[$y]) print "<option value=\"$ansat_id[$y]\">".stripslashes($ansat_init[$y])."</option>";
				print "</SELECT></td>\b";
			}
			#			print "<input class=\"inputbox\" type=text readonly=$readonly size=$feltbredde[$x] style=\"text-align:$justering[$x]\" name=find[$x] value=\"$r[tmp]\">";
		} elseif ($vis_felt[$x]=="status") {
			$status_id=array();$status_init=array();
			$qtxt = "select box3,box4 from grupper where art='DebInfo'";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$status_id=explode(chr(9),$r['box3']);
			$status_beskrivelse=explode(chr(9),$r['box4']);
			$status_antal=count($status_id);
			if (in_array($vis_felt[$x],$selectfelter)) {
				print "<SELECT NAME=\"find[$x]\">";
				if (!$find[$x]) print "<option value=\"\"></option>";
				for ($y=0;$y<$status_antal;$y++) {
					if ($status_beskrivelse[$y] && $find[$x]==$status_id[$y]) {
						print "<option value=\"$status_id[$y]\">".stripslashes($status_beskrivelse[$y])."</option>";
					}
				}
				if ($find[$x]) print "<option value=\"\"></option>";
				for ($y=0;$y<$status_antal;$y++) {
					if ($status_beskrivelse[$y] && $find[$x]!=$status_id[$y]) {
						print "<option value=\"$status_id[$y]\">".stripslashes($status_beskrivelse[$y])."</option>";
					}
				}
				print "</SELECT></td>\n";
			}
			#			print "<input class=\"inputbox\" type=text readonly=$readonly size=$feltbredde[$x] style=\"text-align:$justering[$x]\" name=find[$x] value=\"$r[tmp]\">";
		} elseif (in_array($vis_felt[$x],$selectfelter)) {
			$tmp=$vis_felt[$x];
			print "<SELECT NAME=\"find[$x]\">";
			$q=db_select("select distinct($tmp) from adresser where art = 'D'");
			print "<option>".stripslashes($find[$x])."</option>";
			if ($find[$x]) print "<option></option>";
			while ($r=db_fetch_array($q)) {
				print "<option>$r[$tmp]</option>";
			}
			print "</SELECT></td>\n";			
		} else {
			if ($vis_felt[$x]=='invoiced') $titletxt="skriv et dato i formatet ddmmyy eller et interval adskilt af : fx. 010520:300620";
			else $titletxt= '';
			print "<input class=\"inputbox\" title=\"$titletxt\" type='text' size='$feltbredde[$x]' style='text-align:$justering[$x]' ";
			print "name='find[$x]' value=\"$find[$x]\">";
		}
	}
	print "</td>\n";  
	if ($valg=='kommission') print "<td colspan='2'></td>";

	print "<td><input class='button blue small' type='submit' value=\"Søg\" name=\"search\"></td>\n";
print "</form></tr>\n<td></td>\n";
}
######################################################################################################################
if ($valg=='kommission' || $valg=='historik') {
	$action="debitor.php?start=$start&valg=$valg&sort=$sort";
	print "<form name='kommission' action='$action' method='post'>";
			}
if ($valg == 'rental') include ("../debitor/debLstIncludes/debRentalLst.php");
else include ("../debitor/debLstIncludes/debLst.php");
print "<tr>";
if ($prepil || $prepil=='0')	print "<td colspan=$colspan><a href=debitor.php?start=$prepil&valg=$valg><img src=../ikoner/left.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td>\n";
else print "<td colspan=$colspan><br></td>\n";
if ($valg == 'kommission' || $valg == 'historik') {
#	$colspan++;
	print "<td colspan='2' align='right'>";
	if ($valg == 'kommission') print "<input style='width:75px;' type='submit' name='kommission' value='OK'>";
	else print "<input style='width:75px;' type='submit' name='historik' value='Send'>";
	print "</td></tr>";
	print "<tr><td colspan='$colspan'>";
	print "<td colspan='2' align='right'><input style='width:75px;' type='submit' name='chooseAll' value='Vælg alle'></td>";
	print "</form>";
}	
if ($nextpil) print "<td align=right><a href=debitor.php?start=$nextpil&valg=$valg><img src=../ikoner/right.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td></tr>\n";
else print "<td></td>";
print "</tr>\n";
$colspan++;
print "<tr><td colspan=$colspan width=100%><hr></td></tr>\n";
#print "<table border=0 width=100%><tbody>";

#print "</tbody></table></td>";
#print "<tr><td colspan=$colspan><hr></td></tr>\n";


?>
</tbody>
</table>
	</td></tr>
</tbody></table>

</body></html>
