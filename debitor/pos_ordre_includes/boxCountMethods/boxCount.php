<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- debitor/pos_ordre_includes/boxCountMethods/boxCount.php --- lap 4.0.8 - 2023.06.25 -
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
// but WITHOUT ANY KIND OF CLAIM OR WARRANTY. See
// GNU General Public License for more details.
//
// Copyright (c) 2003-2023 Saldi.dk ApS
// ----------------------------------------------------------------------
//
// LN 20190215 Make function to count the box when submitting
// 20190314 PHR Added $ny_kortsum to function setCreditCards
// 20190319 LN Change "Heutiger Umsatz" to "Dagens omsætning" in the danish version
// 20190320 LN Add new button to box count page
// 20200111 PHR changed "if ($optalt)" to if "($_POST['$optael'])" to avoid 'Red' Difference until [Beregn] clicked
// 20200202	PHR	function specifyAmount. Added $db as global, kr_200 to logfile and fclose $log(); 
// 20200202	PHR	function cashCountResult. Added $db as global, specification of $log, fopen $log, and fclose $log(); 
// 20200202	PHR	function cashCountResult. Added $db and $kasse as globals, specification of $log, fopen $log, and fclose $log(); 
// 20200925 PHR Added button [Kasse] to make it possible to open drawer.
// 20210517 PHR Outcommented setting '$ny_kortsum' to '$kortsum' and made 'card diff' red. (whish from Havemøbelland)
// 20230655 PHR	if (count) changed to if ($count) 

function setSpecifiedCashText() 
{ 
    $country = db_fetch_array(db_select("select land from adresser where art = 'S'",__FILE__ . " linje " . __LINE__))['land'];
    if ($country == "Switzerland") {
        return ["fiveRappen" => "5 rappen", "tenRappen" => "10 rappen", "twentyRappen" => "20 rappen", 
        "half" => "½ franc", "one" => "1 franc", "two" => "2 franc", "five" => "5 franc", "ten" => "10 franc", 
        "twenty" => "20 franc", "fifty" => "50 franc", "hundred" => "100 franc", "twoHundred" => "200 franc", 
        "fiveHundred" => "500 franc", "thousand" => "1000 franc", "other" => "Anderes franc", 
        "turnover" => "Heutiger umsatz", "headline" => "Zählkasse für Box ", 
        "subline" => "(Anzahl Münzen / Banknoten jeder Art)"];
    } else {
        return ["half" => "50 øre", "one" => "1 kr", "two" => "2 kr", "five" => "5 kr", "ten" => "10 kr", 
                "twenty" => "20 kr", "fifty" => "50 kr", "hundred" => "100 kr", "twoHundred" => "200 kr", 
                "fiveHundred" => "500 kr", "thousand" => "1000 kr", "other" => "Andet",
                "turnover" => "Dagens omsætning", "headline" => "Optæl kassebeholdning for kasse ", 
                "subline" => "(Antal mønter/sedler af hver slags)"];
    }
}

function setCashCountText() 
{
		echo "<!-- setCashCountText() -->";
    $country = db_fetch_array(db_select("select land from adresser where art = 'S'",__FILE__ . " linje " . __LINE__))['land'];
    if ($country == "Switzerland") {
        return ["portfolio" => "Morgen portfolio", "newPortfolio" => "Neues morgen portfolio", "dayApproach" => "Heutiger Ansatz", 
                "expInv" => "Erwartetes inventar", "countInv" => "Gezähltes inventar", "diff" => "Unterschied", "fromBox" => "Aus der Box genommen", "currency" => "SFR", "calculate" => "Berechnen", "cancel" => "Rückgängig", "printLast" => "Zuletzt drucken",
                "accept" => "Genehmigen","drawer" => "Schublade"];
    } else {
        return ["portfolio" => "Morgenbeholdning", "newPortfolio" => "Ny Morgenbeholdning", "dayApproach" => "Dagens tilgang", 
                "expInv" => "Forventet beholdning", "countInv" => "Optalt beholdning", "diff" => "Difference", "fromBox" => "Udtages fra kasse", "currency" => "DKK", "calculate" => "Beregn", "cancel" => "Fortryd", "printLast" => "Udskriv sidste",
                "accept" => "Godkend","drawer" => "Skuffe"];
    }
}

function specifyAmount($omsatning, $kassediff, $optalt, $db, $kasse, $log, $ifs, $ore_50, $kr_1, $kr_2, $kr_5, $kr_10, $kr_20, $kr_50, $kr_100, $kr_200, $kr_500, $kr_1000, $kr_andet, $fiveRappen = 0, $tenRappen = 0, $twentyRappen = 0) {
	global $db;
	
	$txt = setSpecifiedCashText();
	$country = db_fetch_array(db_select("select land from adresser where art = 'S'",__FILE__ . " linje " . __LINE__))['land'];

	print "<input type=\"hidden\" name=\"omsatning\" value=\"$omsatning\">\n";
	print "<input type=\"hidden\" name=\"kassediff\" value=\"$kassediff\">\n";
	print "<input type=\"hidden\" name=\"tidl_optalt\" value=\"$optalt\">\n";
	$logfil="../temp/".$db."/kasseopg".str_replace("-","",$kasse).".log";
	$log=fopen("$logfil","a");
	print "<tr><td colspan=\"3\" align=\"center\"><b><big>$txt[headline] $kasse</big></b></td></tr>\n";
	fwrite($log,"\n".date("Y-m-d H:i")."\n\n");
	print "<tr><td colspan=\"3\" align=\"center\">$txt[subline]</td></tr>\n";

	if ($country == "Switzerland")  {
        $temp = $txt['fiveRappen'];
        print "<tr><td align=\"right\">$temp</td><td> </td> <td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"rappen_5\" value=\"$fiveRappen\"></td></tr>\n";
        $temp = $txt['tenRappen'];
        print "<tr><td align=\"right\">$temp</td><td> </td> <td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"rappen_10\" value=\"$tenRappen\"></td></tr>\n";
        $temp = $txt['twentyRappen'];
        print "<tr><td align=\"right\">$temp</td><td> </td> <td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"rappen_20\" value=\"$twentyRappen\"></td></tr>\n";
	}
	$temp = $txt['half'];
	print "<tr><td align=\"right\">$temp</td> <td> </td><td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"ore_50\" value=\"$ore_50\"></td></tr>\n";
	
	$temp = $txt['one'];
	print "<tr><td align=\"right\">$temp</td> <td> </td><td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"kr_1\" value=\"$kr_1\"></td></tr>\n";
	$temp = $txt['two'];
	print "<tr><td align=\"right\">$temp</td> <td> </td><td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"kr_2\" value=\"$kr_2\"></td></tr>\n";
	$temp = $txt['five'];
	print "<tr><td align=\"right\">$temp</td> <td> </td><td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"kr_5\" value=\"$kr_5\"></td></tr>\n";
	$temp = $txt['ten'];
	print "<tr><td align=\"right\">$temp</td> <td> </td><td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"kr_10\" value=\"$kr_10\"></td></tr>\n";
	$temp = $txt['twenty'];
	print "<tr><td align=\"right\">$temp</td> <td> </td><td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"kr_20\" value=\"$kr_20\"></td></tr>\n";
	$temp = $txt['fifty'];
	print "<tr><td align=\"right\">$temp</td> <td> </td><td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"kr_50\" value=\"$kr_50\"></td></tr>\n";
	$temp = $txt['hundred'];
	print "<tr><td align=\"right\">$temp</td> <td> </td><td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"kr_100\" value=\"$kr_100\"></td></tr>\n";
	$temp = $txt['twoHundred'];
	print "<tr><td align=\"right\">$temp</td> <td> </td><td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"kr_200\" value=\"$kr_200\"></td></tr>\n";
	$temp = $txt['fiveHundred'];
	print "<tr><td align=\"right\">$temp</td> <td> </td><td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"kr_500\" value=\"$kr_500\"></td></tr>\n";
	$temp = $txt['thousand'];
	print "<tr><td align=\"right\">$temp</td><td> </td> <td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"kr_1000\" value=\"$kr_1000\"></td></tr>\n";
	$temp = $txt['other'];
	print "<tr><td align=\"right\">$temp</td> <td> </td> <td align=\"right\"><input style=\"width:100;text-align:right;font-size:$ifs;\" name=\"kr_andet\" value=\"".dkdecimal($kr_andet,2)."\"></td></tr>\n";
	
	
    fwrite($log,"Five rappen $fiveRappen \n Ten rappen $tenRappen \n Twenty rappen $twentyRappen \n1 kr $kr_1\n2 kr $kr_2\n");
    fwrite($log,"5 kr $kr_5\n10 kr $kr_10\n20 kr $kr_20\n50 kr $kr_50\n100 kr $kr_100\n200 kr $kr_200\n500 kr $kr_500\n");
    fwrite($log,"1000 kr $kr_1000\nAndet kr $kr_andet\n");

	fclose ($log);
}


function cashCountResult($pfnavn, $kasse, $id, $byttepenge, $ny_morgen, $tilgang, $forventet, $optalt, $kassediff, $color, $mellemkonto, $udtages) {
	echo "<!-- ". __file__ ." cashCountResult -->\n";
	global $bruger_id,$db;

	$qtxt="select box3 from grupper where art = 'POS' and kodenr='2'";
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)); 
	$x=$kasse-1;
	$tmp=explode(chr(9),$r['box3']);
	$printserver=trim($tmp[$x]);
	if (!$printserver)$printserver='localhost';
	if ($printserver=='box' || $printserver=='saldibox') {
		$filnavn="http://saldi.dk/kasse/".$_SERVER['REMOTE_ADDR'].".ip";
		if ($fp=fopen($filnavn,'r')) {
			$printserver=trim(fgets($fp));
			fclose ($fp);
		}
	}

	$logfil="../temp/".$db."/kasseopg".str_replace("-","",$kasse).".log";
	$log=fopen("$logfil","a");
	
	$txtArray = setCashCountText();
    
	$calc = $txtArray['calculate'];
	$drawer = $txtArray['drawer'];
	$cancel = $txtArray['cancel'];
	$printLast = $txtArray['printLast'];
	$portfolio = $txtArray['portfolio'];
	$newPortfolio = $txtArray['newPortfolio'];
	$dayApproach = $txtArray['dayApproach'];
	$expInv = $txtArray['expInv'];
	$countInv = $txtArray['countInv'];
	$diff = $txtArray['diff'];
	$fromBox = $txtArray['fromBox'];
	$curr = $txtArray['currency'];
    
	print "<tr><td align=\"center\" colspan=\"3\">";
	print "<span onclick='window.open(\"http://$printserver/saldiprint.php?skuffe=1\")'>";
	print "<button type='button' style='width:100px'>$drawer</button></span>&nbsp;";
	print "<input type='submit' style='width:100px' name='optael' value=\"$calc\">&nbsp;";
	print "<input type='submit' style='width:100px' name='optael' value=\"$cancel\">";
	print "</td></tr>\n";
	if (file_exists("$pfnavn")) {
		print "<tr><td align=\"center\" colspan=\"3\">";
		print "<a href=pos_ordre.php?id=$id&kasse=$kasse&udskriv_kasseopg=$pfnavn>";
		print "<input type=\"button\" name=\"optael\" value=\"$printLast\"></a></td></tr>\n";
	}
	print "<tr><td colspan=\"2\"><b>$portfolio</b></td><td align=\"right\"><b>".dkdecimal($byttepenge,2)."</b> $curr</td></tr>\n";
	print "<tr><td colspan=\"2\"><b>$dayApproach</b></td><td align=\"right\"><b>".dkdecimal($tilgang,2)."</b> $curr</td></tr>\n";
	print "<tr><td colspan=\"2\"><b>$expInv</b></td><td align=\"right\"><b>".dkdecimal($forventet,2)."</b> $curr</td></tr>\n";
	print "<tr><td colspan=\"2\"><b>$countInv</b></td><td align=\"right\"><b>".dkdecimal($optalt,2)."</b> $curr</td></tr>\n";
	fwrite($log,"$portfolio $byttepenge\n");
	fwrite($log,"$dayApproach $tilgang\n");
	fwrite($log,"$expInv ");
	fwrite($log,$forventet."\n");
	fwrite($log,"$countInv $optalt $curr\n");
	fwrite($log,"$diff ");
	fwrite($log,$optalt-($byttepenge+$tilgang)."\n");
	$count = if_isset($_POST['optael'],NULL);
	if ($count) {
		(afrund($kassediff,2)*1)?$color='red':$color='black'; #20200111
		if ($kassediff) {
			print "<tr><td colspan=\"2\"><span style='color:$color;'><b>$diff</b></span</td>";
			print "<td align=\"right\"><span style='color:$color;'><b>".dkdecimal($kassediff,2)."</b> $curr</span></td></tr>\n";
		}
		if ($optalt || $optalt=='0'){
			if ($mellemkonto) {
				$optplusbyt=$optalt-$byttepenge;
				print "<tr><td colspan=\"2\"><b>$fromBox</b></td><td align=\"right\">";
				print "<input type=\"hidden\" name='optplusbyt' value='$optplusbyt'>";
				print "<input type=\"text\" style=\"width:100;text-align:right;font-size:$ifs;\"";
				# (!$udtages && $udtages!='0')?$tmp=NULL:$tmp=dkdecimal(pos_afrund($udtages,'',''),2);
				print "name=\"udtages\" value=\"".dkdecimal(pos_afrund($udtages,'',''),2)."\"> $curr</td></tr>\n";
			} else {
				($udtages=0);
			}
			fwrite($log,"Udtages $udtages\n");
		}  
		#print "<tr><td colspan=\"2\"><b>$newPortfolio</b></td><td align=\"right\"><b>".dkdecimal($ny_morgen,2)." $curr</b></td></tr>\n";
	} else {
		($optalt)?$tmp=$udtages:$tmp=$byttepenge*-1; 
		print "<tr><td colspan='3'><input type='hidden'name='udtages' value=\"".dkdecimal($tmp,2)."\"><hr></td></tr>";
	}
}

function setCreditCards($kontkonto, $kortnavn, $change_cardvalue, $kortsum, $ny_kortsum, $ifs, $kortdiff, $omsatning, $log, $id) {
	global $db,$kasse,$reportNumber;
	echo "<!-- ". __file__ ." setCreditCards -->\n";
	
	if (!$kortnavn)   $kortnavn   = array();
	if (!$kortsum)    $kortsum    = array();
	if (!$ny_kortsum) $ny_kortsum = array();

	$logfil="../temp/".$db."/kasseopg".str_replace("-","",$kasse).".log";
	$log=fopen("$logfil","a");

	$curr = setCashCountText()['currency'];
	
	for ($x=0;$x<count($kontkonto);$x++) {
		$kortsum[$x]    = if_isset($kortsum[$x],0);
		$ny_kortsum[$x] = if_isset($ny_kortsum[$x],0);
		if ($change_cardvalue) {
			print "<tr><td colspan=\"2\"><b>$kortnavn[$x]</b>(".dkdecimal($kortsum[$x],2).")</td><td align=\"right\">"; 
			print "<input type='text' style=\"width:100;text-align:right;font-size:$ifs;\" ";
			#if (!$ny_kortsum[$x] && $ny_kortsum[$x]!='0') $ny_kortsum[$x]=dkdecimal($kortsum[$x],2); #20210517
			print "name='ny_kortsum[$x]' value='$ny_kortsum[$x]'> $curr</td>";
		} else {
		print "<tr><td colspan=\"2\"><b>$kortnavn[$x]</b></td><td align=\"right\">"; 
			print "<b>".dkdecimal($kortsum[$x],2)."</b> $curr</td>\n";
		}
		print "</tr>\n";
		fwrite($log,"$kortnavn[$x]($kortsum[$x]) $ny_kortsum[$x]\n");
	}
	if ($kortdiff) {
		print "<tr><td colspan=\"2\"><span style='color:red;'><b>Difference på kort</b></span></td>"; #20210517
		print "<td align=\"right\" ><span style='color:red;'><b>".dkdecimal($kortdiff,2)."</b> $curr</span></td></tr>"; 
	}
	$txt = setSpecifiedCashText()['turnover'];
	print "<tr><td colspan=\"2\"><b>$txt</b><input type='hidden' name='card_total' value='".array_sum($kortsum)."'</td>";
	print "<td align=\"right\"><b>".dkdecimal($omsatning,2)."</b> $curr</td></tr>\n";
	fwrite($log,"Dagens omsætning $omsatning\n");
	if (isset($_SESSION['boxZreport']) && $_SESSION['boxZreport'] == true) {
		print "<td><input type = 'hidden' name='reportNumber' value='$reportNumber'>";
		print "<input $disabled style=\"font-size: 20px; border-radius: 12px; position: relative; left: 80px; top: 10px;\" ";
		print "type=\"submit\" name=\"zRapport\" value=\"Z-Rapport\" onclick=\"javascript:return\"></td>";
		unset($_SESSION['boxZreport']);
	}
	print "</form></tbody></table></td>\n";
    if (getCountry() != "Switzerland") {
        print file_get_contents("pos_ordre_includes/boxCountMethods/vejl_kasseoptDK.html");
	}
	fclose ($log);
}

?>

