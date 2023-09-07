<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- debitor/pos_ordre_includes/exitFunc/exit.php --- lap 4.0.5 --- 2021.12.03---
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
//
// LN 20190510 Move function find_bon here
// 20210125 PHR Varouis changes related to voucer.
// 20211203 PHR drawer will now remail closed if no cash is involved


function afslut($id,$betaling,$betaling2,$modtaget,$modtaget2,$indbetaling,$godkendt,$kortnavn,$payment_id=null) {
print "\n<!-- Function afslut (start)-->\n";
	global $afd;
	global $betalingsbet,$bruger_id,$brugernavn;
	global $charset;
	global $db;
	global $indbetaling; #20160902
	global $kasse;
	global $momssats;
	global $pobelob;
	global $regnaar,$retur;
	global $tracelog;

	list($modtaget,$valmodt,$betvaluta,$betvalkurs)=explode(chr(9),posvaluta($modtaget));

	$modtaget  = (float)$modtaget;
	$modtaget2 = (float)$modtaget2;



	if ($id && $betaling) {
		include('../debitor/pos_ordre_includes/voucherFunc/voucherPay.php');
#cho __FILE__ ." ".__LINE__."<br>";
		voucherPay($id, $betaling, $modtaget);
	if (!isset($_COOKIE['giftcard']) || $_COOKIE['giftcard'] == true) {
#cho __line__." Giftcard true<br>";		
		$sum = betaling($id, $momssats, $betaling, $betaling2, $modtaget, $modtaget2, $kasse);
#cho __line__." Sum $sum<br>";		
	} else {
#cho __line__." Giftcard not true<br>";		
		setcookie('giftcard', '', time()-3600);
		print "<meta http-equiv=\"refresh\" content=\"0;URL=pos_ordre.php?id=$id\">\n";
		exit(0);
	}
	}
	
#cho __LINE__."<br>";	
// Indsat til Claus ---->
/*	
	$qtxt="select box5 from grupper where art = 'POS' and kodenr='1'";
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$korttyper=explode(chr(9),$r['box5']);
	$qtxt="select box4 from grupper where art = 'POS' and kodenr='3'";
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$gavekort=explode(chr(9),$r['box4']);
	for ($z=0;$x<count($korttyper);$x++) {
		if ($korttyper[$x] == $betaling && $gavekort[$x]) {
		}
	}
*/	
// <----	sualC lit tasnI 
#cho __LINE__."<br>";	
	$r = db_fetch_array(db_select("select sum,moms,konto_id from ordrer where id = '$id'",__FILE__ . " linje " . __LINE__));
	$sum=$r['sum'];
	$moms=$r['moms'];
	$konto_id=$r['konto_id'];
	$tmp=$modtaget+$modtaget2;
#cho "$sum > $tmp<br>\n";
	$q=db_select("select * from pos_betalinger where ordre_id = '$id' and betalingstype != '!' order by id",__FILE__ . " linje " . __LINE__);
	while($r=db_fetch_array($q)) {
		$tmp+=$r['amount'];
	}
	$a=pos_afrund($sum+$moms,'','');
	$b=pos_afrund($tmp,'','');
	$diff=afrund($a-$b,2);
#cho __FILE__." ".__LINE__."<br>";	
	if (!$indbetaling && $diff > 0) { #20160902
		delbetal($id,$betaling,$betaling2,$modtaget,$modtaget2,$indbetaling,$godkendt,$kortnavn,$betvaluta,$betvalkurs);
		print "<meta http-equiv=\"refresh\" content=\"0;URL=pos_ordre.php?id=$id&betaling=ukendt\">\n";
		exit;
	}
	if (!$indbetaling && $diff > 0) { #20160902
		$tmp=$sum-$tmp;
		print "<meta http-equiv=\"refresh\" content=\"0;URL=pos_ordre.php?id=$id&modtaget=$tmp\">\n";
		exit;
	}
	$tmp=array();
	$betalingskort=array();
#cho __FILE__." ".__LINE__." $godkendt | $kortnavn<br>";	
	if ($godkendt!='OK') { #20131205
		$r = db_fetch_array(db_select("select box3,box4,box5,box6 from grupper where art = 'POS' and kodenr='2'",__FILE__ . " linje " . __LINE__)); 
		$x=$kasse-1;
		$tmp=explode(chr(9),$r['box3']);
		$printserver=trim($tmp[$x]);
		$tmp=explode(chr(9),$r['box4']);
		$terminal_ip=trim($tmp[$x]);
		$betalingskort=explode(chr(9),$r['box5']);
		$div_kort_kto=trim($r['box6']);
		if ($terminal_ip) { # 20131210  div ændringer i rutine
			$r = db_fetch_array(db_select("select box4,box5 from grupper where art = 'POS' and kodenr = '1'",__FILE__ . " linje " . __LINE__));
			$kortantal=$r['box4']*1;
			$korttyper=explode(chr(9),$r['box5']);
			if ($div_kort_kto) {
				$betalingskort[$kortantal]='on';
				$korttyper[$kortantal]='Betalingskort';
				$kortantal++;
			}
			if (in_array($betaling,$korttyper) || in_array($betaling2,$korttyper)) {
				$amount=0;
				for($x=0;$x<$kortantal;$x++) {
					if ($betaling==$korttyper[$x] && $betalingskort[$x] && !$amount) $amount=$modtaget;
					elseif ($betaling==$korttyper[$x] && $betalingskort[$x] && $amount) return ("Der kan ikke betales med 2 betalingskort");
					if ($betaling2==$korttyper[$x] && $betalingskort[$x] && !$amount) $amount=$modtaget2;
					elseif ($betaling2==$korttyper[$x] && $betalingskort[$x] && $amount) return ("Der kan ikke betales med 2 betalingskort");
				}
			}
			if ($amount) {
				if (substr($amount,0,1)=="/" && $betaling) {
					delbetal($id,$amount);
				}
				if (!$printserver) $printserver='localhost';
				$belob=dkdecimal($amount,2); 
				$belob=str_replace(".","",$belob);

				if ($_SERVER['HTTPS']) $server='https://';
				else $server='http://';

				$server.=$_SERVER['SERVER_NAME'];
				$serverfile=$_SERVER['PHP_SELF'];
				$url=$server.$serverfile;

				if ($_COOKIE['salditerm']) $terminal_ip=$_COOKIE['salditerm'];
				#Her skal laves noget javascript hejs til tjek af lokal IP
				if ($terminal_ip=='box' || $terminal_ip=='saldibox') {
					$filnavn="http://saldi.dk/kasse/".$_SERVER['REMOTE_ADDR'].".ip";
					if ($fp=fopen($filnavn,'r')) {
						$terminal_ip=trim(fgets($fp));
						fclose ($fp);
						$tmp=setcookie("salditerm",$terminal_ip,time()+3600,'/');
					}
				} #else $terminal_ip=$printserver;
				$tidspkt=date("U");
				$qtxt="insert into pos_betalinger(ordre_id,betalingstype,amount,valuta,valutakurs) values ";
				$qtxt.="('$id','!','$modtaget','$betvaluta','$betvalkurs')";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				$qtxt="select max(id) as pos_bet_id from pos_betalinger where ordre_id='$id' and betalingstype='!'";
				$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)); 
				$pos_bet_id=$r['pos_bet_id'];

        # Check for flatpay or ip term
        $qtxt = "SELECT var_value FROM settings WHERE var_name='terminal_type' AND pos_id=$kasse";
		    $r = db_fetch_array(db_select($qtxt, __FILE__ . " linje " . __LINE__)); 

        if ($r[0] == "Flatpay") {
          $tmp="payments/flatpay.php?amount=$belob&id=$id";
          setcookie("saldi_bet",$tmp,time()+60*60*24*7);
          print "<meta http-equiv=\"refresh\" content=\"0;URL=$tmp\">\n";
          exit;
        } else if (str_starts_with($r[0], "Vibrant")) {
          $tmp="payments/vibrant.php?amount=$belob&id=$id";
          setcookie("saldi_bet",$tmp,time()+60*60*24*7);
          print "<meta http-equiv=\"refresh\" content=\"0;URL=$tmp\">\n";
          exit;
        }

				$tmp="http://$terminal_ip/pointd/kvittering.php?url=$url&server=$server&serverfile=$serverfile&id=$id&db=$db&pos_bet_id=$pos_bet_id&kommando=kortbetaling&belob=$belob&betaling=$betaling&betaling2=$betaling2&modtaget=$modtaget&modtaget2=$modtaget2&indbetaling=$indbetaling&tidspkt=$tidspkt";
				setcookie("saldi_bet",$tmp,time()+60*60*24*7);
				print "<meta http-equiv=\"refresh\" content=\"0;URL=$tmp\">\n";
				exit;
			}
		}
	} elseif ($kortnavn) { #20140129
		$qtxt = "select box3,box4,box5,box6 from grupper where art = 'POS' and kodenr='2'";
		$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)); 
		
		$x=$kasse-1;
		$tmp=explode(chr(9),$r['box3']);
		$printserver=trim($tmp[$x]);
		$tmp=explode(chr(9),$r['box4']);
		$terminal_ip=trim($tmp[$x]);
		$betalingskort=explode(chr(9),$r['box5']);
		$div_kort_kto=trim($r['box6']);
		if ($terminal_ip && $div_kort_kto) { 
			$r = db_fetch_array(db_select("select box4,box5 from grupper where art = 'POS' and kodenr = '1'",__FILE__ . " linje " . __LINE__));
			$kortantal=$r['box4']*1;
			$korttyper=explode(chr(9),$r['box5']);
			$lkt=explode(chr(9),strtolower($r['box5']));
			$lk=strtolower($kortnavn);
			if (in_array($lk,$lkt)) {
				for($x=0;$x<$kortantal;$x++) {
					if ($lk==$lkt[$x] && $betaling=='Betalingskort') $betaling=$korttyper[$x];
					if ($lk==$lkt[$x] && $betaling2=='Betalingskort') $betaling2=$korttyper[$x];
				}
			} elseif ($betaling=='Betalingskort') $betaling.="|".$kortnavn;
			elseif ($betaling2=='Betalingskort') $betaling2="|".$kortnavn;
		}
	}
	$projekt=NULL;
	$tid=date("H:i");
	$qtxt="select box9 from grupper where art='POSBUT' and (box7 < box8) and (box7<'$tid' and box8>'$tid')";
	if ($afd) $qtxt.=" and (box12='$afd' or box12='') order by box12 desc limit 1";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$projekt=$r['box9'];
	if (!$projekt) {
		$qtxt="select box9 from grupper where art='POSBUT' and (box7 > box8) and ((box7>'$tid' and box8>'$tid') or (box7<'$tid' and box8<'$tid'))";
		if ($afd) $qtxt.=" and (box12='$afd' or box12='') order by box12 desc limit 1";
		($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)))?$projekt=$r['box9']:$projekt='';
	}
	$hurtigfakt='on';
	$moms=0;
	$dd=date("Y-m-d");
	$r=db_fetch_array(db_select("select konto_id,status,fakturanr,momssats,betalingsbet from ordrer where id = '$id'",__FILE__ . " linje " . __LINE__));
	$momssats=$r['momssats'];
	$status=$r['status'];
	$konto_id=$r['konto_id'];
	$betalingsbet=$r['betalingsbet'];

	#cho	 __FILE__." ".__LINE__." settlePOS<br>";	
#xit;

	include("settlePOS.php"); #20190510
	
	if ($svar=='OK') { #20150213
		transaktion("commit");
		$qtxt = "select id from grupper where art = 'POS' and kodenr = '1' and box10 = 'on'";
		if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
			if ($tracelog) {
				fwrite ($tracelog, __file__." ".__line__." Calls: pos_txt_print($id,$betaling,$betaling2,$modtaget,$modtaget2,$indbetaling)\n");
			}
			pos_txt_print($id,$betaling,$betaling2,$modtaget,$modtaget2,$indbetaling);
		}
	}
	$qtxt = "select id from grupper where art = 'POS' and kodenr = '1' and box10='on'";
	echo "$qtxt<br>";
	
	if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
echo "ID $r[id]<br>";
	if ($tracelog) {
			fwrite ($tracelog, __file__." ".__line__." Calls: pos_txt_print($id,$betaling,$betaling2,$modtaget,$modtaget2,$indbetaling)\n");
		}
		pos_txt_print($id,$betaling,$betaling2,$modtaget,$modtaget2,$indbetaling);
	 } elseif (!$konto_id && ($betaling == 'Kontant' || $betaling2 == 'Kontant' || $modtaget+$modtaget2 != $sum+$moms)) {#20160211+20211203
		$url="://".$_SERVER['SERVER_NAME'].=$_SERVER['PHP_SELF'];
		$url=str_replace("/debitor/pos_ordre.php","",$url);
		if ($_SERVER['HTTPS']) $url="s".$url;
		$url="http".$url;
		$returside=$url."/debitor/pos_ordre.php";
		$bon='';
		if ($printserver=='box') {
			$filnavn="http://saldi.dk/kasse/".$_SERVER['REMOTE_ADDR'].".ip";
			if ($fp=fopen($filnavn,'r')) {
				$printserver=trim(fgets($fp));
				fclose ($fp);
				if ($printserver) setcookie("saldi_printserver",$printserver,time()+60*60*24*7,'/');
			} 
		}
		if ($printserver=='box' || !$printserver) $printserver=$_COOKIE['saldi_printserver'];
		print "<meta http-equiv=\"refresh\" content=\"0;URL=http://$printserver/saldiprint.php?&url=$url&bruger_id=$bruger_id&bon=&bonantal=1&id=$id&skuffe=1&returside=$returside&logo=\">\n";
		exit;
  } else { #20160211
		print "<meta http-equiv=\"refresh\" content=\"0;URL=pos_ordre.php?id=$id\">\n";
		exit;
	}
	print "\n<!-- Function afslut (slut)-->\n";
	return(NULL);
} #endfunc afslut.



?>

