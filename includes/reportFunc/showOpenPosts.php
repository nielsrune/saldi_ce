<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- includes/reportFunc/showOpenPosts.php --- lap 4.0.8 --- 2023.06.20 ---
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
// Copyright (c) 2003 - 2023 Saldi.dk ApS
// ----------------------------------------------------------------------

if (!function_exists('vis_aabne_poster')) {
function vis_aabne_poster($dato_fra,$dato_til,$konto_fra,$konto_til,$rapportart,$kontoart,$kun_debet,$kun_kredit) {
//cho "$dato_fra, $dato_til, $konto_fra, $konto_til, $rapportart, $kontoart<br>";

	global $bgcolor,$bgcolor5,$bruger_id;
	global $db;
	global $menu;
	global $sprog_id;

	if ($menu=='T') {
		$top_bund = "";
		$padding = "style='padding: 25px 20px 10px 20px;'";
	} else {
		$top_bund = (isset($top_bund) ? $top_bund : "");
		$padding = "";
	}
	
	$forfaldsum=$forfaldsum_plus8=$forfaldsum_plus30=$forfaldsum_plus60=$forfaldsum_plus90=$fromdate=$linjebg=$popup=$todate=NULL;
	
	
	if ($menu=='T') {
		print "<tr><td><div class='dataTablediv'><table width=100% cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class='dataTable'><thead>\n";
		print "<tr><th>Kontonr.</th><th>PBS</th><th>".findtekst(360,$sprog_id)."</th><th align=right class='text-right'>>90</th><th align=right  class='text-right'>60-90</th><th align=right class='text-right'>30-60</th><th align=right class='text-right'>8-30</th><th align=right class='text-right'>0-8</th><th align=right class='text-right'>I alt</th><th align=right</th>";
		print "</thead><tbody>";
	} else {
		print "<tr><td><table width=100% cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tbody>\n";
		print "<tr><td>Kontonr.</th><th>PBS</td><td>".findtekst(360,$sprog_id)."</td><td align=right>>90</td><td align=right>60-90</td><td align=right>30-60</td><td align=right>8-30</td><td align=right>0-8</td><td align=right>I alt</td><td></td>";
	}

	$currentdate=date("Y-m-d");
	if ($dato_fra && $dato_til) {
		$fromdate=usdate($dato_fra);
		$todate=usdate($dato_til);
	}	elseif ($dato_fra && !$dato_til) {
		$todate=usdate($dato_fra);
	}

	print "<form name=aabenpost action=rapport.php method=post>";

	if ($menu=='T') {
		print "";
	} else {
		print "<tr><td colspan=10><hr></td></tr>\n";
	}
		
	$x = 0;
	$op_id = array();
  $qtxt = "select distinct(konto_id) as konto_id from openpost ";
 # if (!$todate || $todate >= $currentdate)
  $qtxt.= "where udlignet = '0'"; #20200103
	$q=db_select("$qtxt",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		$op_id[$x]=$r['konto_id'];
		$x++;
	}

	if (is_numeric($konto_fra) && is_numeric($konto_til)) {
		$qtxt = "select * from adresser where ".nr_cast('kontonr').">='$konto_fra' and ".nr_cast('kontonr')."<='$konto_til' and art = '$kontoart' order by ".nr_cast('kontonr')."";
	} elseif ($konto_fra && $konto_fra!='*') {
		$konto_fra=str_replace("*","%",$konto_fra);
		$tmp1=strtolower($konto_fra);
		$tmp2=strtoupper($konto_fra);
		$qtxt = "select * from adresser where (firmanavn like '$konto_fra' or lower(firmanavn) like '$tmp1' or upper(firmanavn) like '$tmp2') and art = '$kontoart' order by firmanavn";
	}	else $qtxt = "select * from adresser where art = '$kontoart' order by firmanavn";
	$kontonr=array();
	$x=0;
	$q=db_select("$qtxt",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		if (in_array($r['id'],$op_id)) {
			$x++;
			$konto_id[$x]=$r['id'];
			if (!isset($kontoudtog[$x])) $kontoudtog[$x]=NULL;
			print "<input type=hidden name=konto_id[$x] value=$konto_id[$x]>";
			$kontonr[$x]=trim($r['kontonr']);
			$firmanavn[$x]=stripslashes($r['firmanavn']);
#cho "$x $firmanavn[$x]<br>";	
			$addr1[$x]=stripslashes($r['addr1']);
			$addr2[$x]=stripslashes($r['addr2']);
			$postnr[$x]=trim($r['postnr']);
			$bynavn[$x]=stripslashes($r['bynavn']);
			$email[$x]=trim($r['email']);
			$betalingsbet[$x]=trim($r['betalingsbet']);
			$betalingsdage[$x]=trim($r['betalingsdage']);
			$pbs[$x]=trim($r['pbs']);
			$pbs_nr[$x]=trim($r['pbs_nr']);
			($pbs[$x] && $pbs_nr[$x])?$pbs[$x]='&#10004;':$pbs[$x]=NULL;
		}
	}
	$kontoantal=$x;	
	$sum=0;
	$kontrolsum=0;
	$udlign=NULL;
	for ($x=1; $x<=$kontoantal; $x++) {
		$amount=0;
		$udlignet=1;
		$rykkerbelob=0;
		$forfalden=0;
		$forfalden_plus8=0;
		$forfalden_plus30=0;
		$forfalden_plus60=0;
		$forfalden_plus90=0;
		$kontrol=0;
		$y=0;
		$faktnr=array();
		$f=0;
		if ($kontoart=='D') $tmp="";
		else $tmp="desc";

		if ($todate) $qtxt="select * from openpost where transdate<='$todate' and konto_id='$konto_id[$x]' order by faktnr,amount $tmp";
		else $qtxt="select * from openpost where konto_id='$konto_id[$x]' order by faktnr,amount $tmp";
		$q=db_select("$qtxt",__FILE__ . " linje " . __LINE__);

		$ks=0;
		while ($r=db_fetch_array($q)) {
      if ($r['valutakurs']*1 && $r['valuta']!='-') {
				$kontrol+=afrund($r['amount']*$r['valutakurs']/100,2); //2012.03.30 afrunding rettet til 2 (Ørediff hos saldi_390) 
			} else {
				$kontrol+=afrund($r['amount'],2);
			}
			$ks+=$kontrol;
			if ($r['udlignet']!=1 || ($r['transdate'] <= $todate && $r['udlign_date'] && $r['udlign_date'] > $todate)) {
				if ($r['faktnr'] && !in_array($r['faktnr'],$faktnr)) {
					$f++;
					$faktnr[$f]=$r['faktnr'];
					$forfaldsdag=$r['forfaldsdate'];
#cho __line__." $r[udlignet] $r[transdate] $r[amount]<br>";
				} 
				elseif (!$r['faktnr']) $forfaldsdag=$r['transdate'];
				$oid=$r['id'];
				
				$transdate=$r['transdate'];
				
				if ($r['valuta']) $valuta=$r['valuta']; // <- 2009.05.05
				else $valuta='DKK';
				if ($r['valutakurs']) $valutakurs=$r['valutakurs'];
				else $valutakurs=100;
				$udlignet="0";
				($valuta=='DKK')?$amount=afrund($r['amount'],2):$amount=afrund($r['amount'],3); //2012.04.03 se saldi_ 
				if (!$forfaldsdag && $kontoart=='D' && $amount < 0) $forfaldsdag=$r['transdate'];
				elseif (!$forfaldsdag && $kontoart=='K' && $amount > 0) $forfaldsdag=$r['transdate'];
				elseif (!$forfaldsdag) $forfaldsdag=$r['forfaldsdate'];
/* 20230620
				if ($r['faktnr'] && !$r['kladde_id'] && ($amount > 0 || ($amount < 0 && strstr($r['beskrivelse'],"Lev. fakt"))) && $r['refnr']>1) { //rettet 20090715
					if ($r2=db_fetch_array(db_select("select betalingsbet, betalingsdage from ordrer where id='$r[refnr]' and fakturanr = '$r[faktnr]'",__FILE__ . " linje " . __LINE__))){
						$betalingsbet[$x]=trim($r2['betalingsbet']);
						$betalingsdage[$x]=trim($r2['betalingsdage']);
						$tmp=usdate(forfaldsdag($transdate, $betalingsbet[$x], $betalingsdage[$x]));
						if ($tmp!=$forfaldsdag) {
							$forfaldsdag=$tmp;
							db_modify("update openpost set forfaldsdate = '$forfaldsdag' where id='$oid'",__FILE__ . " linje " . __LINE__);
						}
					}
				}
*/				
				$amount*=$valutakurs/100;
				$fakt_utid=strtotime($transdate);
				$forf_utid=strtotime($forfaldsdag);
				$dage=afrund(($forf_utid-$fakt_utid)/86400,0);
				$forfaldsdag_plus8=usdate(forfaldsdag($transdate, 'netto',$dage+8));
				$forfaldsdag_plus30=usdate(forfaldsdag($transdate, 'netto',$dage+30));
				$forfaldsdag_plus60=usdate(forfaldsdag($transdate, 'netto',$dage+60));
				$forfaldsdag_plus90=usdate(forfaldsdag($transdate, 'netto',$dage+90));
#if ($kontonr[$x] == '56958898') echo __line__." $kontonr[$x] $amount $forfaldsdag_plus90<br>";
				if ($forfaldsdag<$currentdate){$rykkerbelob=$rykkerbelob+$amount;}
				if (($forfaldsdag<$currentdate)&&($forfaldsdag_plus8>$currentdate)){$forfalden=$forfalden+$amount;}
				if (($forfaldsdag_plus8<=$currentdate)&&($forfaldsdag_plus30>$currentdate)){$forfalden_plus8=$forfalden_plus8+$amount;}
				if (($forfaldsdag_plus30<=$currentdate)&&($forfaldsdag_plus60>$currentdate)){$forfalden_plus30=$forfalden_plus30+$amount;}
				if (($forfaldsdag_plus60<=$currentdate)&&($forfaldsdag_plus90>$currentdate)){
					$forfalden_plus60=$forfalden_plus60+$amount;
				}
				if ($forfaldsdag_plus90<=$currentdate){
					$forfalden_plus90=$forfalden_plus90+$amount;
				}
			$y=$y+$amount;
 #if ($x==5) echo "$db $y<br>";
			}
		}
		if ($kun_debet && $y<=0) {$udlignet=1;$y=0;$kontrol=0;}  
		elseif ($kun_kredit && $y>=0) {$udlignet=1;$y=0;$kontrol=0;}  
		$kontrol=afrund($kontrol,2);
		($y>0) ? $y=afrund($y,2) : $y=afrund($y,2);
		if ($y>0.01||$udlignet=="0"||$kontrol)	{	
			if ($linjebg!=$bgcolor){$linjebg=$bgcolor; $color='#000000';}
			elseif ($linjebg!=$bgcolor5){$linjebg=$bgcolor5; $color='#000000';}
		
			$forfaldsum=$forfaldsum+$forfalden;
			$forfaldsum_plus8=$forfaldsum_plus8+$forfalden_plus8;
			$forfaldsum_plus30=$forfaldsum_plus30+$forfalden_plus30;
			$forfaldsum_plus60=$forfaldsum_plus60+$forfalden_plus60;
			$forfaldsum_plus90=$forfaldsum_plus90+$forfalden_plus90;
			$sum=$sum+$y;
 #if ($x==5) echo "$db $y<br>";
			$kontrolsum+=$kontrol;
			print "<tr bgcolor=\"$linjebg\">";
			print "<td><a href=rapport.php?rapportart=accountChart&kilde=openpost&kto_fra=$konto_fra&kilde_kto_til=$konto_til&dato_fra=$dato_fra&dato_til=$dato_til&konto_fra=$kontonr[$x]&konto_til=$kontonr[$x]&submit=ok>";
			print "<span title='Klik for detaljer'>$kontonr[$x]</span></a></td>";
			print "<td>$pbs[$x]</td>";
			print "<td>$firmanavn[$x]</td>";
/*	
	if ($forfalden_plus90 != 0) $color="rgb(255, 0, 0)";
	else $color="rgb(0, 0, 0)";
	$tmp=dkdecimal($forfalden_plus90);
	print "<td align=right><span style='color: $color;'>$tmp</span></td>";
	if ($forfalden_plus60 != 0) $color="rgb(255, 0, 0)";
	else $color="rgb(0, 0, 0)";
	$tmp=dkdecimal($forfalden_plus60);
	print "<td align=right><span style='color: $color;'>$tmp</span></td>";
	if ($forfalden_plus60 != 0) $color="rgb(255, 0, 0)";
	else $color="rgb(0, 0, 0)";
	$tmp=dkdecimal($forfalden_plus30);
	print "<td align=right><span style='color: $color;'>$tmp</span></td>";
	if ($forfalden_plus30 != 0) $color="rgb(255, 0, 0)";
	else $color="rgb(0, 0, 0)";
	$tmp=dkdecimal($forfalden_plus8);
	print "<td align=right><span style='color: $color;'>$tmp</span></td>";
	if ($forfalden != 0) $color="rgb(255, 0, 0)";
	else $color="rgb(0, 0, 0)";
	$tmp=dkdecimal($forfalden);
*/
			$forfalden_plus90=afrund($forfalden_plus90,2);
			$forfalden_plus60=afrund($forfalden_plus60,2);
			$forfalden_plus30=afrund($forfalden_plus30,2);
			$forfalden_plus8=afrund($forfalden_plus8,2);

			if (($kontoart=='D' && $forfalden_plus90 > 0) || ($kontoart=='K' && $forfalden_plus90 < 0)) $color="rgb(255, 0, 0)";
			else $color="rgb(0, 0, 0)";
			$tmp=dkdecimal($forfalden_plus90,2);
			print "<td align=right><span style='color: $color;'>$tmp</span></td>";
			if (($kontoart=='D' && $forfalden_plus60 > 0) || ($kontoart=='K' && $forfalden_plus60 < 0)) $color="rgb(255, 0, 0)";
			else $color="rgb(0, 0, 0)";
			$tmp=dkdecimal($forfalden_plus60,2);
			print "<td align=right><span style='color: $color;'>$tmp</span></td>";
			if (($kontoart=='D' && $forfalden_plus30 > 0) || ($kontoart=='K' && $forfalden_plus30 < 0)) $color="rgb(255, 0, 0)";
			else $color="rgb(0, 0, 0)";
			$tmp=dkdecimal($forfalden_plus30,2);
			print "<td align=right><span style='color: $color;'>$tmp</span></td>";
			if (($kontoart=='D' && $forfalden_plus8 > 0) || ($kontoart=='K' && $forfalden_plus8 < 0)) $color="rgb(255, 0, 0)";
			else $color="rgb(0, 0, 0)";
			$tmp=dkdecimal($forfalden_plus8,2);
			print "<td align=right><span style='color: $color;'>$tmp</span></td>";
			if (($kontoart=='D' && $forfalden > 0) || ($kontoart=='K' && $forfalden < 0)) $color="rgb(255, 0, 0)";
			else $color="rgb(0, 0, 0)";
			$tmp=dkdecimal($forfalden,2);
			print "<td align=right><span style='color: $color;'>$tmp</span></td>";
			if (afrund($kontrol,2)!=afrund($y,2)) {
				ret_openpost($konto_id[$x]);
				$tmp=dkdecimal($kontrol,2);
			} else $tmp=dkdecimal($y,2);
			if (abs($y)<0.01 && abs($kontrol)<0.01) {
				$udlign.=$konto_id[$x].",";
				print "<td align=right title=\"Klik her for at udligne &aring;bne poster\"><a href=\"rapport.php?submit=ok&rapportart=openpost&dato_fra=$dato_fra&dato_til=$dato_til&konto_fra=$konto_fra&konto_til=$konto_til&udlign=$konto_id[$x]\">$tmp</a></td>";
			}
			else {print "<td align=right>$tmp</td>";}
#cho "<td align='right'>($y) $sum</td>";
			if (($kontoudtog[$x]=='on')&&($kontoart=="D")) print "<td align=center><label class='checkContainerOrdreliste'><input type=checkbox name=kontoudtog[$x] checked><span class='checkmarkOrdreliste'></span></label>";
			elseif($kontoart=="D")  print "<td align=center><label class='checkContainerOrdreliste'><input type=checkbox name=kontoudtog[$x]><span class='checkmarkOrdreliste'></span></label>";
			print "</tr>\n";
		}
		print "<input type=hidden name=rykkerbelob[$x] value=$rykkerbelob>";
	}

	if (!isset ($forfaldsum_plus90)) $forfaldsum_plus90 = NULL;
	if (!isset ($forfaldsum_plus60)) $forfaldsum_plus60 = NULL;
	if (!isset ($forfaldsum_plus30)) $forfaldsum_plus30 = NULL;
	if (!isset ($forfaldsum_plus8)) $forfaldsum_plus8 = NULL;
	if (!isset ($forfaldsum)) $forfaldsum = NULL;

	$forfaldsum_plus90=afrund($forfaldsum_plus90,2);
	$forfaldsum_plus60=afrund($forfaldsum_plus60,2);
	$forfaldsum_plus30=afrund($forfaldsum_plus30,2);
	$forfaldsum_plus8=afrund($forfaldsum_plus8,2);

	if ($menu=='T') {
		print "</tbody><tfoot>";
		print "<tr><td colspan='2'><br></td><td><b>I alt</b></td>";
	} else {
		print "<tr><td colspan=10><hr></td></tr>\n";
		print "<tr><td colspan='2'><br></td><td><b>I alt</b></td>";
	}

	if ($forfaldsum_plus90 != 0) $color="rgb(255, 0, 0)";
	else $color="rgb(0, 0, 0)";
	$tmp=dkdecimal($forfaldsum_plus90,2);
	print "<td align=right><span style='color: $color;'>$tmp</span></td>";
	if ($forfaldsum_plus60 != 0) $color="rgb(255, 0, 0)";
	else $color="rgb(0, 0, 0)";
	$tmp=dkdecimal($forfaldsum_plus60,2);
	print "<td align=right><span style='color: $color;'>$tmp</span></td>";
	if ($forfaldsum_plus60 != 0) $color="rgb(255, 0, 0)";
	else $color="rgb(0, 0, 0)";
	$tmp=dkdecimal($forfaldsum_plus30,2);
	print "<td align=right><span style='color: $color;'>$tmp</span></td>";
	if ($forfaldsum_plus30 != 0) $color="rgb(255, 0, 0)";
	else $color="rgb(0, 0, 0)";
	$tmp=dkdecimal($forfaldsum_plus8,2);
	print "<td align=right><span style='color: $color;'>$tmp</span></td>";
	if ($forfaldsum != 0) $color="rgb(255, 0, 0)";
	else $color="rgb(0, 0, 0)";
	$tmp=dkdecimal($forfaldsum,2);
	print "<td align=right><span style='color: $color;'>$tmp</span></td>";
	$color="rgb(0, 0, 0)";
  ($sum<=$kontrolsum)?$tmp=dkdecimal($kontrolsum,2):$tmp=dkdecimal($sum,2);
	print "<td align=right><span style='color: $color;'>$tmp</span>";
	print "<td align=right></td>";
	print "<input type=hidden name=rapportart value=\"openpost\">";
	print "<input type=hidden name=dato_fra value=$dato_fra>";
	print "<input type=hidden name=dato_til value=$dato_til>";
	print "<input type=hidden name=konto_fra value=$konto_fra>";
	print "<input type=hidden name=konto_til value=$konto_til>";
	print "<input type=hidden name=kontoantal value=$kontoantal></td></tr>";

	if ($kontoart=='D') {
		$overlib4="<span class='CellComment'>".findtekst(242,$sprog_id)."</span>";
		print "<tr><td colspan='10' align='center' class='border-hr-top'><span title=\"Klik her for at maile kontoudtog til de modtagere som er afm&aelig;rket herover\">";
		print "<input type=submit value=\"Mail kontoudtog\" name=\"submit\"></span>&nbsp;&nbsp;";
		print "<span title='Klik her for at oprette rykker til de som er afm&aelig;rkede herover'>";
		print "<input type=submit value=\"Opret rykker\" name=\"submit\"></span>&nbsp;&nbsp;";
		if ($udlign) {
			$udlign=trim($udlign,"'");
			print "	<input type='button' onclick=\"location.href='rapport.php?rapportart=openpost&udlign=$udlign';\" title='Klik her for at udligne alle med saldoen' value='Udlign alle' />&nbsp;&nbsp;";
			print "<span class='CellWithComment'><input type=submit value=\"Ryk alle\" name=\"submit\"> $overlib4</span></td>"; 
		} else {
			print "<span class='CellWithComment'><input type=submit value=\"Ryk alle\" name=\"submit\"> $overlib4</span></td>";
		}
		print "</tr>\n";
	}
	print "</form>\n";

	if ($menu=='T') {
		print "</tfoot></table></div><br>";
	} else {
		print "<tr><td colspan=10><hr></td></tr>\n";
		print "</tbody></table>";
	}

	
}} //endfunc vis_aabne_poster

?>
