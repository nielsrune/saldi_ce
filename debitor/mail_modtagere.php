<?php
@session_start();
$s_id=session_id();
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ---------debitor/mail_mostagere.php---------------------Patch 3.6.5-----2016.04.07---
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af The Free Software Foundation; enten i version 2
// af denne licens eller en senere version efter eget valg
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
// Copyright (c) 2016 saldi.dk aps
// -----------------------------------------------------------------------------------

$modulnr=12;
$css="../css/standard.css";

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/forfaldsdag.php");

$liste_id=if_isset($_GET['liste_id']);
$mailtekst=if_isset($_POST['mailtekst']);
$emne=if_isset($_POST['emne']);

ini_set("include_path", ".:../phpmailer");
require("class.phpmailer.php");

$x=0;
$qtxt="select betalinger.modt_navn,betalinger.betalingsdato,betalinger.belob,kassekladde.kredit,kassekladde.beskrivelse from betalinger,kassekladde ";
$qtxt.="where betalinger.liste_id='$liste_id' and kassekladde.id = betalinger.bilag_id ";
$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
while ($r= db_fetch_array($q)){
	$beskrivelse=trim(str_replace('Afregning','',$r['beskrivelse']));
	list($start[$x],$slut[$x])=explode("-",$beskrivelse);
	$start[$x]=usdate($start[$x]);
	$slut[$x]=usdate($slut[$x]);
	$kontonr[$x]=$r['kredit'];
	$belob[$x]=$r['belob'];
	$r2=db_fetch_array(db_select("select sum(antal) as antal from ordrelinjer,ordrer where ordrer.art='PO' and ordrer.fakturadate >= '$start[$x]' and ordrer.fakturadate <= '$slut[$x]' and ordrelinjer.ordre_id = ordrer.id and ordrelinjer.varenr like '%$kontonr[$x]'",__FILE__ . " linje " . __LINE__));
	$antal[$x]=$r2['antal']*1;
	$r2=db_fetch_array(db_select("select firmanavn,email from adresser where kontonr='$kontonr[$x]'",__FILE__ . " linje " . __LINE__));
	$modt_navn[$x]=$r2['firmanavn'];
	$email[$x]=$r2['email'];
	$x++;
}
$mailantal=$x;
if ($_POST['testmail']) {
	$r=db_fetch_array(db_select("select * from adresser where art='S'",__FILE__ . " linje " . __LINE__));
	echo "sender til $r[email]<br>";
	$x=0;
	$mtxt=str_replace('$navn',$modt_navn[$x],$mailtekst);
	$mtxt=str_replace('$sum',$belob[$x],$mtxt);
	$mtxt=str_replace('$antal',$antal[$x],$mtxt);
	$mtxt=str_replace("\n","<br>",$mtxt);
	send_mail($emne,$mtxt,$r['email'],$r['email'],$r['firmanavn']);
}

if ($_POST['send_mails']) {
	$r=db_fetch_array(db_select("select * from adresser where art='S'",__FILE__ . " linje " . __LINE__));
	for ($x=0;$x<count($modt_navn);$x++) {
		$mtxt=str_replace('$navn',$modt_navn[$x],$mailtekst);
		$mtxt=str_replace('$sum',$belob[$x],$mtxt);
		$mtxt=str_replace('$antal',$antal[$x],$mtxt);
		$mtxt=str_replace("\n","<br>",$mtxt);
		send_mail($emne,$mtxt,$email[$x],$r['email'],$r['firmanavn']);
	}
}


if (!$emne) $emne="Afregning";
if (!$mailtekst) {
	$r=db_fetch_array(db_select("select * from adresser where art='S'"));
	$mailtekst='Kære $navn'."\n".'Du har i peroden 01.februar 2017 til 20 februar 2017 opnået et tilgodehavende på kr. $sum'."\n";
	$mailtekst.='I perioden er der solgt i alt $antal varer fra din stand'."\n\n";
	$mailtekst.="Beløbet vil blive overført til din konto en af de nærmeste dage\n\n";
	$mailtekst.="Med venlig hilsen\n";
	$mailtekst.="$r[firmanavn]\n";
}
print "<a href=betalinger.php?liste_id=$liste_id>Tilbage til betalingsliste</a>";
print "<center>";
print "<form name=\"mail_modtagere\" action=\"mail_modtagere.php?liste_id=$liste_id\" method=\"post\">";
print "<table><tbody>";
#print "<tr><td><b>Periode</b></td></tr>";
#print "<tr><td><input style=\"width:100px\" type=\"text\" name=\"start\" value=\"".dkdato($start)."\"> til <input style=\"width:100px\" type=\"text\" name=\"start\" value=\"dkdato($start)\"></td></tr>";
print "<tr><td><b>Emne.</b></td></tr>";
print "<tr><td><input style=\"width:800px\" type=\"text\" name=\"emne\" value=\"$emne\"></td></tr>";
print "<tr><td></td></tr>";
print "<tr><td><b>Mailtekst.</b> Du kan bruger $"."navn som navn på modtager, $"."sum som det beløb der overføres   og $"."antal som antal solgte enheder.</td></tr>";
print "<tr><td>HTML koder accepteres</td></tr>";
#print "<tr><td>Fed="."<"."b"."><b>tekst</b><"."/b"."></td></tr>";
print "<tr><td><textarea rows='16' cols='100' name='mailtekst'>$mailtekst</textarea></td></tr>";
print "<tr><td></td></tr>";
print "<tr><td style=\"text-align:center;\"><input type='submit' name='opdater' value='Opdater'>&nbsp;";
print "<input type='submit' name='send_mails' value='Send mail' onClick=\"return confirm('send $mailantal mails nu?')\">&nbsp;";
print "<input type='submit' name='testmail' value='Send testmail'></td></tr>";
print "<tr><td></td></tr>";
print "<tr><td><b>Eksempel</b> (Første 4 modtagere af $mailantal)</td></tr>";
for ($x=0;$x<$mailantal;$x++) {
	print "<tr><td></td></tr>";
	$eksempel=str_replace('$navn',$modt_navn[$x],$mailtekst);
	$eksempel=str_replace('$sum',$belob[$x],$eksempel);
	$eksempel=str_replace('$antal',$antal[$x],$eksempel);
	$eksempel=str_replace("\n","<br>",$eksempel);
	print "<tr><td>$eksempel</td></tr>";
	print "<tr><td><hr></td></tr>";
	if ($x>2) break 1;
}

print "</tbody></table>";
print "<form>";


function send_mail($subjekt,$mailtekst,$modtager,$afsendermail,$afsendernavn) {
	global $db;
	global $brugernavn;
	global $bgcolor;
	global $bgcolor5;
	global $charset;
	
#	echo $charset;
	
	$mailtekst=str_replace("\n","<br>",$mailtekst);
#	if ($charset == 'UTF-8') {
#		$subjekt=utf8_decode($subjekt);
#		$mailtekst=utf8_decode($mailtekst);
#	}
#	echo $mailtekst;
	$tmpmappe="../temp/$db/afr_mail";
	mkdir($tmpmappe);
	if ($subjekt && $mailtekst && $modtager && $afsendermail && $afsendernavn) {	
		$mailtext = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTMP 4.01 Transitional//EN\">\n";
		$mailtext .= "<html><head><meta content=\"text/html; charset=ISO-8859-15\" http-equiv=\"content-type\">\n";
		$mailtext .=$mailtekst;
		$mailtext .= "</html>\n";			
		if ($charset=="UTF-8") {
			$subjekt=utf8_decode($subjekt);
			$mailtext=utf8_decode($mailtext);
			$afsendernavn=utf8_decode($afsendernavn);
			$afsendermail=utf8_decode($afsendermail);
		}
#	echo $mailtext;
#		$fp=fopen("$tmpmappe/afregning.html","w");
#		fwrite($fp,$mailtext);
#		fclose ($fp);

		$mail = new PHPMailer();
		$mail->IsSMTP();                                   // send via SMTP
		$mail->Host  = "localhost"; // SMTP servers
		$mail->SMTPAuth = false;     // turn on SMTP authentication

		if (strpos($_SERVER['SERVER_NAME'],'saldi.dk')) { #20121029
			if ($_SERVER['SERVER_NAME']=='ssl.saldi.dk') $mail->From = $db.'@ssl.saldi.dk'; #20140128
			elseif ($_SERVER['SERVER_NAME']=='ssl2.saldi.dk') $mail->From = $db.'@ssl2.saldi.dk'; #20140128
			elseif ($_SERVER['SERVER_NAME']=='ssl3.saldi.dk') $mail->From = $db.'@ssl3.saldi.dk'; #20140128
			else $mail->From = 'kanikkebesvares@saldi.dk'; #20140128
			$mail->FromName = $afsendernavn;
		} else {
			$mail->From = $afsendermail;
			$mail->FromName = $afsendernavn;
		}
		$splitter=NULL;
		$mail->AddAddress($modtager); 
		$mail->AddBCC($afsendermail); 
		$mail->AddReplyTo($afsendermail,$afsendernavn);

		$mail->WordWrap = 50;                              // set word wrap
#		$mail->AddAttachment("$tmpmappe/afregning.html");      // attachment
		$mail->IsHTML(true);                               // send as HTML
		$mail->Subject  =  $subjekt;
		
		$mailbody = "<html><body>\n";
    $mailbody .= "$mailtext\n";
		$mailbody .= "</body></html>";

/*
		$mailaltbody = "Hermed fremsendes kontoudtog fra ".$afsendernavn.".\n\n";
    $mailaltbody .= "Den vedlagte fil er en HTML-fil og kan ses i din webbrowser eksempelvis \n";
		$mailaltbody .= "ved at dobbeltklikke på den.\n";
		$mailaltbody .= "-- \n";
		$mailaltbody .= $row['firmanavn']."\n";
		if ( $row['addr1'] ) $mailaltbody .= $row['addr1']."\n";
		if ( $row['addr2'] ) $mailaltbody .= $row['addr2']."\n";
		if ( $row['postnr'] ) $mailaltbody .= $row['postnr']." ".$row['bynavn']."\n";
		if ( $row['tlf'] ) $mailaltbody .= "tlf ".$row['tlf'];
		if ( $row['fax'] ) $mailaltbody .= " * fax ".$row['fax'];
		if ( $row['cvr'] ) $mailaltbody .= " * cvr ".$row['fax'];
*/		
#		if ($charset=="UTF-8"){
#				$mailbody=utf8_decode($mailbody);
#				$mailaltbody=utf8_decode($mailaltbody);
#		}
		$mail->Body     =  $mailbody;
#		$mail->AltBody  =  $mailaltbody;
		if(!$mail->Send()){
			 echo "Fejl i afsendelse til $modtager<p>";
 				echo "Mailer Error: " . $mail->ErrorInfo;
 		 		exit;
		}
		echo "Afregning sendt til $modtager<br>";
#			sleep(2);
	}	
#	unlink("$tmpmappe/afregning.html");
#	rmdir($tmpmappe);
}






/*
else {
}
	$query = db_select("select * from adresser where id=$konto_id[$x]",__FILE__ . " linje " . __LINE__);
	$row = db_fetch_array($query);
	if (!$email[$x]){$email[$x]=$row['email'];}
	print "<tr><td colspan=8><hr style=\"height: 10px; background-color: rgb(200, 200, 200);\"></td></tr>";
	print "<tr><td colspan=\"5\">$row[firmanavn]</td><td colspan=\"2\" align=\"right\">Dato</td><td align=right> ".date('d-m-Y')."</td></tr>\n";
	print "<tr><td colspan=\"5\">$row[addr1]</td><td colspan=\"2\" align=\"right\">Kontonr.</td><td align=right> $row[kontonr]</td></tr>\n";
	if ( $row['addr2'] ) print "<tr><td colspan=\"8\">$row[addr2]</td></tr>\n";
	print "<tr><td colspan=\"8\">$row[postnr] $row[bynavn]</td></tr>\n";
	print "<tr><td colspan=\"8\">Tlf. $row[tlf]</td></tr>\n";
	print "<tr><td><br></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<tr><td width=10%> Dato</td><td width=5%> Bilag</td><td width=5%> Faktura</td><td width=40% align=center> Tekst</td><td> Forfaldsdato</td><td width=10% align=right> Debet</td><td width=10% align=right> Kredit</td><td width=10% align=right> Saldo</td></tr>";
	print "<tr><td colspan=8><hr></td></tr>";
	$betalingsbet=trim($row['betalingsbet']);
	$betalingsdage=$row['betalingsdage'];
	$kontosum=0;
	$primo=0;
	$primoprint=0;
	if (!$fromdate[$x]) { #20150305-1
		$y=0;
		$q=db_select("select * from openpost where konto_id=$konto_id[$x] and transdate<='$todate[$x]' order by transdate, faktnr",__FILE__ . " linje " . __LINE__);
		while ($r = db_fetch_array($q)) {
			$td[$y]=$r['transdate'];
			($y)?$am[$y]=$am[$y-1]+afrund($r['amount'],2):$am[$y]=afrund($r['amount'],2);
			$y++;
		}
		for ($y=0;$y<count($td);$y++) {
			if (!$y) $fromdate[$x]=$td[$y];
			if ($am[$y]==0 && $y<count($td)-1) $fromdate[$x]=$td[$y+1]; 
			$fra[$x]=dkdato($fromdate[$x]);
		}
	}
	if (!$fromdate[$x]) {
		$fromdate[$x]=usdate($fra[$x]);
	}
	
	$query = db_select("select * from openpost where konto_id=$konto_id[$x] and transdate<='$todate[$x]' order by transdate, faktnr",__FILE__ . " linje " . __LINE__);
	while ($row = db_fetch_array($query)) {
		$amount=afrund($row['amount'],2);
		$forfaldsdag=NULL;
		$forfaldsdate=NULL;
		if ($row['forfaldsdate']) {
			$forfaldsdate=$row['forfaldsdate'];
			$forfaldsdag=dkdato($forfaldsdate);
		}
	 if ($row['transdate']<$fromdate[$x]) {
			$primoprint=0;
			$kontosum=$kontosum+$amount;
		}		 
		else { 
			if ($primoprint==0) {
				$tmp=dkdecimal($kontosum,2);
				$linjebg=$bgcolor5; $color='#000000';
				print "<tr bgcolor=\"$linjebg\"><td colspan=\"3\"></td><td> Primosaldo</td><td colspan=\"3\"></td><td align=right> $tmp</td></tr>";
				$primoprint=1;
			}
		    	if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
			elseif ($linjebg!=$bgcolor5){$linjebg=$bgcolor5; $color='#000000';}
			print "<tr bgcolor=\"$linjebg\"><td align=left> ".dkdato($row['transdate'])."&nbsp;</td><td> $row[refnr]</td><td align=right> $row[faktnr]&nbsp;</td><td> $row[beskrivelse]</td>";
			
			if ($amount < 0) $tmp=0-$amount;
			else $tmp=$amount;
			$tmp=dkdecimal($tmp,2);
			if (!$forfaldsdag && $amount > 0) {
				$forfaldsdag=forfaldsdag($row['transdate'], $betalingsbet, $betalingsdage);
				$forfaldsdate=usdate($forfaldsdag);
			}
			if (($row['udlignet']!='1')&&($forfaldsdate<$currentdate)) $stil="<span style='color: rgb(255, 0, 0);'>";
			else $stil="<span style='color: rgb(0, 0, 0);'>";
			if ($amount > 0) {
				print "<td>$stil"."$forfaldsdag</td><td align=right>$stil $tmp</td><td></td>";
				$forfaldsum=$forfaldsum+$amount;
			}
			else {print "<td></td><td></td><td align=right> $tmp</td>";}
			
			$kontosum=$kontosum+$amount;
			$tmp=dkdecimal($kontosum,2);
			print "<td align=right> $tmp</td>";
			print "</tr>";
		}
	}
	if ($primoprint==0) {
		$tmp=dkdecimal($kontosum,2);
		print "<tr><td></td><td></td><td></td><td> Primosaldo</td><td></td><td></td><td></td><td align=right> $tmp</td></tr>";
	}
	print "<tr><td colspan=8><hr></td></tr>";
 	print "<tr><td colspan=8> email til: <input type=text name=email[$x] value=$email[$x]> Periode: <input type=text style=\"text-align:right\" size=10 name=fra[$x] value=$fra[$x]> - <input type=text style=\"text-align:right\" size=10 name=til[$x] value=$til[$x]></td></tr>";
	print "<tr><td colspan=8><hr style=\"height: 10px; background-color: rgb(200, 200, 200);\"></td></tr>";
	print "<tr><td colspan=8><hr></td></tr>";
	print "<input type = hidden name=konto_id[$x] value=$konto_id[$x]>";
}
print "<input type = hidden name=kontoantal value=$kontoantal>";
print "<input type = hidden name=dato_fra value=$dato_fra>";
print "<input type = hidden name=dato_til value=$dato_til>";
#print "<input type = hidden name=regnaar value=$regnaar>";
print "<tr><td colspan=10 align=center><input type=submit value=\"&nbsp;&nbsp;&nbsp;&nbsp;Opdat&eacute;r&nbsp;&nbsp;&nbsp;&nbsp;\" name=\"submit\">&nbsp;<input type=submit value=\"Send mail(s)\" name=\"submit\"></td>";

print "</form>\n";

*/
?>

