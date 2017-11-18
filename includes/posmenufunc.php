<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
//----------------- includes/posmenufunc.php -----ver 3.6.6---- 2017.02.07 ----------
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af The Free Software Foundation; enten i version 2
// af denne licens eller en senere version efter eget valg.
// Fra og med version 3.2.2 dog under iagttagelse af følgende:
// 
// Programmet må ikke uden forudgående skriftlig aftale anvendes
// i konkurrence med DANOSOFT ApS eller anden rettighedshaver til programmet.
// 
// Programmet er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
// 
// En dansk oversaettelse af licensen kan laeses her:
// http://www.saldi.dk/dok/GNU_GPL_v2.html
//
// Copyright (c) 2003-2017 DANOSOFT ApS
// ----------------------------------------------------------------------
// 2015.08.20 Mulig for pris på varegenveje. Søg $pris
// 2015.10.23	Diverse nye ændringer 
// 2015.11.29	Tilføjet knap Konant på beløb & Betalingskort på beløb.
// 2016.01.28 Tilføjet systemknap Stamkunder. Se funktion stamkunder i ordrefunc.php
// 2016.01.31 Tilføjet systemknap Kontoudtog & Udskriv sidste. 
// 2016.04.18 Alle 'på beløb' disables når der ikke er varer.
// 2016.09.27 Det skal være muligt at betale med kort/kontant osv når der er kontonr og kunde ikke har kredit
// 2016.10.06 !$sum rettet til (!$sum && $a!='Kontant') da det skal være muligt at afslutte en 0 bon 20161006
// 2017.02.07	Tilføjet knap sæt
// 2017.03.24 Afd kommer nu fra global (pos_ordre).

if (!function_exists('menubuttons')) {
function menubuttons($id,$menu_id,$vare_id,$plads) {

	global $afd,$afd_navn,$afd_lager;
	global $betalingsbet,$bgcolor,$bgcolor5,$betvaluta,$bon,$bord,$bordnr,$bordnavn,$brugernavn;
	global $fokus,$folger;
	global $indbetaling;
	global $kasse,$kontonr;
	global $momssats;
	global $pris_ny;
	global $sum;
	global $url;
	global $varenr_ny,$vare_id,$vare_id_ny;

	$b=NULL;
	
	$dd=date("Y-m-d");
	$tt=date("H:i:s");
	
	if ($kasse=="?") find_kasse($kasse);
	($vare_id || $vare_id_ny)?$disabled="disabled=\"disabled\"":$disabled=NULL; 
	$r = db_fetch_array(db_select("select box2,box3,box4,box7,box10 from grupper where art = 'POS' and kodenr='2'",__FILE__ . " linje " . __LINE__)); 
	$x=$kasse-1;
	$optalassist=$r['box2'];
	$tmp=explode(chr(9),$r['box3']);
	$printserver=trim($tmp[$x]);
	$tmp=explode(chr(9),$r['box4']); #20131205
	$terminal_ip=strtolower(trim($tmp[$x]));
	$tmp=explode(chr(9),$r['box10']); #20140820
	$koekkenprint=strtolower(trim($tmp[$x]));
	($r['box7'])?$bord=explode(chr(9),$r['box7']):$bord=NULL; #20140508

	if ($varenr_ny && $plads=='H') { #20140702
		$tmp=strtolower($varenr_ny);
		$r=db_fetch_array(db_select("select id,folgevare from varer where lower(varenr) = '$tmp' or lower(stregkode) = '$tmp'",__FILE__ . " linje " . __LINE__));
		$vare_id=$r['id'];
		if ($r['folgevare']<0) {
			$menu_id=$r['folgevare']*-1;
			$folger=$vare_id;
		} 
	} elseif ($folger && $plads=='H') {
		$r=db_fetch_array(db_select("select folgevare from varer where id = '$folger'",__FILE__ . " linje " . __LINE__));
		if ($menu_id != abs($r['folgevare'])) $folger=NULL;
	} #elseif ($folger && $plads!='H') $menu_id=NULL;
	$kasse=trim($kasse);
/* 20170324
	$r=db_fetch_array(db_select("select * from grupper where art = 'POS' and kodenr='1'",__FILE__ . " linje " . __LINE__));
	$kasseantal=$r['box1']*1;
	$afdnr=explode(chr(9),$r['box3']);
	$tmp=$kasse-1;
	$afd=$afd[$tmp];
	$r = db_fetch_array(db_select("select * from grupper where art = 'AFD' and kodenr='$afd'",__FILE__ . " linje " . __LINE__));
	$afd_navn=$r['beskrivelse'];
*/
	$tid=date("H:i");
	if (!$menu_id && $menu_id!='0' && $afd) {
	$qtxt="select kodenr from grupper where art='POSBUT' and kode='$plads' and box1='$afd' and (box7 < box8) and (box7<='$tid' and box8>='$tid')";
	if ($afd) $qtxt.=" and (box12='$afd' or box12='') order by box12 desc limit 1";
		$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$menu_id=$r['kodenr'];
	}
	if (!$menu_id && $menu_id!='0') {
		$qtxt="select kodenr from grupper where art='POSBUT' and kode='$plads' and (box7 < box8) and (box7<='$tid' and box8>='$tid')";
		if ($afd) $qtxt.=" and (box12='$afd' or box12='') order by box12 desc limit 1";
		$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$menu_id=$r['kodenr'];
		if (!$menu_id && $menu_id!='0') { #her tages højde for at slut tidspkt kan være mindre en starttidspkt
			$qtxt="select kodenr from grupper where art='POSBUT' and (box7 > box8) and ((box7>='$tid' and box8>='$tid') or (box7<='$tid' and box8<='$tid'))";
			if ($afd) $qtxt.=" and (box12='$afd' or box12='') order by box12 desc limit 1";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$menu_id=$r['kodenr'];
		}
	}
	$qtxt="select * from grupper where art='POSBUT' and kodenr='$menu_id'";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	($r['kode']=='H')?$menu='sidemenu':$menu='bundmenu'; 
	$menuid=$r['kodenr'];
	$beskrivelse=str_replace("\n","\n ",$r['box1']);
	$cols=$r['box2'];
	$rows=$r['box3'];
	$height=$r['box4'];
	$width=$r['box5'];
	$radius=$r['box11'];
	$fontsize=$r['box10'];
	$cellspacing=0;
	if (!$fontsize) $fontsize=$height*$width/200;

#	$stil="style=\"width:".$width."px;height:".$height."px;text-align:center;font-size:".$fontsize."px; background-color:#$b;\"";
	$stil="STYLE=\"display: table-cell;moz-border-radius:".$radius."px;-webkit-border-radius:".$radius."px;width:".$width."px;height:".$height."px;";
	$stil.="text-align:center;vertical-align:middle;font-size:".$fontsize."px;border: 1px solid #$bgcolor5;white-space: normal;background-color: $b;\"";
	$nostil="STYLE=\"display: table-cell;moz-border-radius:0px;-webkit-border-radius:0px;width:".$width."px;height:".$height."px;text-align:center;";
	$nostil.="vertical-align:middle;font-size:".$fontsize."px;white-space: normal;border: 0px solid #$bgcolor;\"";
#		background-color: $bgcolor;\"";
	$twidth=$width*$cols."px";
	$theight=$height*$rows."px";
	print "<table border=\"0\" cellspacing=\"$cellspacing\" cellpadding=\"1\" width=\"$twidth\" height=\"$theight\" valign=\"top\"><tbody>\n"; # table 1 ->
	if (substr($beskrivelse,0,1) != "!") print "<tr><td colspan=\"$cols\" align=\"center\" bgcolor=\"$bgcolor5\">$beskrivelse</td></tr>\n";
	for ($x=1;$x<=$rows;$x++) {
		print "<tr>\n";
		for ($y=1;$y<=$cols;$y++) {
			$qtxt="select * from pos_buttons where menu_id=$menuid and row='$x' and col='$y'";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$a=str_replace("\n","\n ",$r['beskrivelse']);
			$b=$r['color'];
			$c=$r['vare_id']*1;
			$d=$r['funktion']*1;
			if ($a=='--') {
				print "<td><input type=\"button\" $stil value= \"\"></td>"; 
			} elseif ($a=='---') {
				print "<td width=\"".$width."px\" $nostil></td>"; 
			} elseif ($a || $a=='0') {
				if ($d<6) {
					$knap="<input type=\"button\" $stil value= \"$a\">";
					$knap=str_replace("background-color: ;","background-color: $b;",$knap);
				}
				if (!$d || $d==1) {
					if (strpos($a,'$pris')) {
						$ugedag=date('N');
						$uxtid=date("U");
						$tidspkt=date("H:i:s");
						$qtxt="select salgspris from varetilbud where vare_id=$c and ugedag='$ugedag' and startdag<='$uxtid' and slutdag >='$uxtid' and starttid <='$tidspkt' and sluttid >='$tidspkt'";
						$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
						if ($r['salgspris']) $pris=$r['salgspris'];
						else {
						$qtxt="select salgspris,special_price,special_from_date,special_to_date,special_from_time,special_to_time from varer where id=$c";
						$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
							if ($r['special_from_date'] <= $dd && $r['special_to_date'] >= $dd && $r['special_from_time'] <= $tt && $r['special_to_time'] >= $tt) { 
						$pris=$r['special_price'];
							} else $pris=$r['salgspris'];
						}
						$pris=dkdecimal($pris+$pris*$momssats/100,2);
						if (strpos($knap,'$pris')) {
							$knap=str_replace('$pris'," $pris",$knap);
							$tmp=1;
							$knap=str_replace("\n"," ",$knap,$tmp);
						}
					}
					print "<td><a style=\"text-decoration:none\" href=pos_ordre.php?id=$id&$menu=$menu_id&vare_id=$vare_id&vare_id_ny=$c&varenr_ny=$varenr_ny&pris_ny=$pris_ny&folger=$folger&fokus=$fokus&bordnr=$bordnr&lager=$afd_lager>$knap</a>\n";
				} elseif ($d==2) print "<td><a style=\"text-decoration:none\" href=pos_ordre.php?id=$id&vare_id=$vare_id&$menu=$c&varenr_ny=$varenr_ny&pris_ny=$pris_ny&folger=$folger&fokus=$fokus&bordnr=$bordnr&lager=$afd_lager>$knap</a>\n";
				elseif ($d==3) print "<td><a style=\"text-decoration:none\" href=pos_ordre.php?id=$id&konto_id=$c&varenr_ny=$varenr_ny&pris_ny=$pris_ny&folger=$folger&fokus=$fokus&bordnr=$bordnr&lager=$afd_lager>$knap</a>\n";
				elseif ($d==4) print "<td><a style=\"text-decoration:none\" href=pos_ordre.php?id=$id&spec_func=spec_$c&varenr_ny=$varenr_ny&pris_ny=$pris_ny&folger=$folger&fokus=$fokus&bordnr=$bordnr&lager=$afd_lager>$knap</a>\n";
				elseif ($d==5) {
				  $tmp=str_replace("background-color: ;","background-color: $b;",$stil);
				  print "<td><INPUT TYPE=\"button\" $tmp NAME=\"$a\" VALUE=\"$a\" onclick=\"pos_ordre.$fokus.value += '$a';pos_ordre.$fokus.focus();\">\n";
				} elseif ($d==6) {
					if ($c==1) {
						$txt='Bord';
						for ($z=0;$z<count($bord);$z++) {
							if ($bordnr==$z) $txt=$bord[$z];
						}
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><INPUT $disabled $tmp TYPE=\"submit\" NAME=\"bordvalg\" VALUE=\"$txt\">";
					} elseif ($c=='2') {
						$txt=str_replace('$brugernavn',$brugernavn,$a);
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><INPUT $disabled onclick=\"window.location.href='pos_ordre.php?id=$id&skift_bruger=1&bordnr=$bordnr'\" type=\"button\" $tmp value= \"$txt\">\n";
					} elseif ($c=='3') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><INPUT $disabled $tmp TYPE=\"submit\" NAME=\"del_bord\" VALUE=\"Del bord\">";
					} elseif ($c=='4') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><INPUT $tmp TYPE=\"submit\" NAME=\"OK\" VALUE=\"Enter\">";
					} elseif ($c=='5') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><input $disabled type=\"button\" onclick=\"window.location.href='pos_ordre.php?id=$id&find_bon=1'\" $tmp value=\"$a\">\n";
#						$knap=str_replace("background-color: ;","background-color: $b;",$knap);
#						print "<td>".$knap;
					} elseif ($c=='6') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><INPUT $disabled $tmp TYPE=\"submit\" NAME=\"flyt_bord\" VALUE=\"Flyt bord\">";
					} elseif ($c=='7') {
						$knap="<input $disabled type=\"button\" onclick=\"window.location.href='pos_ordre.php?id=$id&kasse=$kasse&kassebeholdning=on&bordnr=$bordnr'\" $stil value=\"$a\">\n";
						$knap=str_replace("background-color: ;","background-color: $b;",$knap);
						print "<td>".$knap;
					} elseif ($c=='8') {
						$knap="<input $disabled type=\"button\" onclick=\"window.location.href='pos_ordre.php?id=$id&kasse=?&bordnr=$bordnr'\" $stil value=\"Kasse $kasse\">\n";
						$knap=str_replace("background-color: ;","background-color: $b;",$knap);
						print "<td>".$knap;
					} elseif ($c=='9' || $c=='23') {
						$kstil=str_replace("background-color: ;","background-color: $b;",$stil);
						$qtxt="select sum(ordrelinjer.antal) as iordre, sum(ordrelinjer.leveret) as bestilt from ordrelinjer,varer ";
						$qtxt.="where ordrelinjer.ordre_id='$id' and varer.kategori!='' and ordrelinjer.vare_id=varer.id";
						$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)); 
						if ($r['iordre']>$r['bestilt']) $kstil=str_replace($b,'#ff0000',$kstil);
						if ($c==9) print "<td><INPUT $disabled $kstil TYPE=\"submit\" NAME=\"koekken\"VALUE=\"$a\">";
						else print "<td><INPUT $disabled $kstil TYPE=\"submit\" NAME=\"send_koekken\"VALUE=\"$a\">";
					} elseif ($c=='10') { #Luk
						$knap="<input $disabled type=\"button\" onclick=\"window.location.href='../index/menu.php'\" $stil value=\"$a\">\n";
						$knap=str_replace("background-color: ;","background-color: $b;",$knap);
						print "<td>".$knap;
					} elseif ($c=='11') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><INPUT $disabled $tmp TYPE=\"submit\" NAME=\"skuffe\" VALUE=\"Skuffe\">";
					} elseif ($c=='12') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><INPUT $disabled $tmp TYPE=\"submit\" NAME=\"udskriv\" VALUE=\"Udskriv\">";
					} elseif ($c=='13') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td onclick=\"return confirm('Slet alt og start forfra')\"><INPUT TYPE=\"submit\" $tmp NAME=\"forfra\"VALUE=\"Forfra\" onclick=\"pos_ordre.$fokus.value += 'f';pos_ordre.$fokus.focus();\">\n";
					} elseif ($c=='14') {
						$knap="<input $disabled onclick=\"window.location.href='pos_ordre.php?id=$id&skift_bruger=2&brugernavn=$a&bordnr=$bordnr&$menu=$menu_id'\" type=\"button\" $stil value= \"$a\">\n";
						if (strtolower($brugernavn)==strtolower($a)) $knap=str_replace("background-color: ;","background-color: #00ff00;",$knap);
						else $knap=str_replace("background-color: ;","background-color: $b;",$knap);
						print "<td>".$knap;
					} elseif ($c=='15') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><INPUT TYPE=\"button\" $tmp NAME=\"clear\" VALUE=\"Ryd\" onclick=\"pos_ordre.$fokus.value = '';pos_ordre.$fokus.focus();\">";
					} elseif ($c=='16') {
						($disabled)?$dis="disabled='disabled'":$dis=NULL;
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<TD><INPUT TYPE=\"submit\" $dis $tmp NAME=\"afslut\" VALUE=\"$a\" onclick=\"pos_ordre.$fokus.value += 'a';pos_ordre.$fokus.focus();\">\n";
					} elseif ($c=='17') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						($disabled)?$dis=NULL:$dis="disabled='disabled'";
						print "<td><INPUT TYPE=\"submit\" $tmp $dis NAME=\"$a\" VALUE=\"$a\" onclick=\"pos_ordre.$fokus.value += 'p';pos_ordre.$fokus.focus();\">\n";
					} elseif ($c=='18') {
						($disabled)?$dis=NULL:$dis="disabled='disabled'";
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><INPUT TYPE=\"submit\" $tmp $dis NAME=\"$a\" VALUE=\"$a\" onclick=\"pos_ordre.$fokus.value += 'r';pos_ordre.$fokus.focus();\">\n";
					} elseif ($c=='19') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<TD><INPUT TYPE=\"submit\" $stil NAME=\"tilbage\"VALUE=\"Tilbage\" onclick=\"pos_ordre.$fokus.value += 't';pos_ordre.$fokus.focus();\"></TD>\n";
#						print "<TD onclick=\"return confirm('Tilbage til varescanning')\"><INPUT TYPE=\"submit\" $stil NAME=\"tilbage\"VALUE=\"Tilbage\" onclick=\"pos_ordre.$fokus.value += 't';pos_ordre.$fokus.focus();\"></TD>\n";
					} elseif ($c=='20') {
						$knap="<input $disabled type=\"button\" onclick=\"window.location.href='pos_ordre.php'\" $stil value=\"$a\">\n";
						$knap=str_replace("background-color: ;","background-color: $b;",$knap);
						print "<td>".$knap;
						$r = db_fetch_array(db_select("select box13 from grupper where art = 'POS' and kodenr = '1'",__FILE__ . " linje " . __LINE__));
						$timeout=$r['box13']*1;
						if ($timeout && !$bon) {
							$r = db_fetch_array(db_select("select box1 from grupper where art = 'POS' and kodenr = '3'",__FILE__ . " linje " . __LINE__));
							$brugervalg=$r['box1'];
							if ($brugervalg) {
								$qtxt="select kodenr from grupper where art='POSBUT' and box6='U'";
								if ($afd) $qtxt.=" and (box12='$afd' or box12='') order by box12 desc limit 1";
								$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
								if ($r['kodenr']) print "<meta http-equiv=\"refresh\" content=\"$timeout;URL=pos_ordre.php?id=0&menuvalg=$r[kodenr]\">\n";
								else print "<meta http-equiv=\"refresh\" content=\"$timeout;URL=pos_ordre.php?id=0&skift_bruger=1\">\n";
							} else print "<meta http-equiv=\"refresh\" content=\"$timeout;URL=pos_ordre.php?id=0\">\n";
						}
					} elseif ($c=='21') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<TD align=\"center\"><INPUT TYPE=\"submit\" $tmp NAME=\"krediter\" VALUE=\"$a\">\n";
					} elseif ($c=='22') { #Kortterminal
						$dis=$disabled;
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						if ($terminal_ip=='box' && isset($_COOKIE['salditerm'])) $terminal_ip=$_COOKIE['salditerm'];
						if (!$terminal_ip || $terminal_ip=='box') {
							$filnavn="http://saldi.dk/kasse/".$_SERVER['REMOTE_ADDR'].".ip";
							if ($fp=fopen($filnavn,'r')) {
								$terminal_ip=trim(fgets($fp));
								fclose ($fp);
							} else {
								$dis=" disabled='disabled' ";
							}
						} #else setcookie("salditerm",$terminal_ip,time()+3600);
						if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) $url='https://';
						else $url='http://';
						$url.=$_SERVER['SERVER_NAME'];#.$_SERVER['PHP_SELF'];
						if (!strpos($url,$_SERVER['PHP_SELF'])) $url.=$_SERVER['PHP_SELF'];
						$href="http://$terminal_ip/pointd/point.php?url=$url&id=$id&kasse=$kasse";
						print "<td><input $dis type=\"button\" onclick=\"window.location.href='$href'\" $tmp value=\"$a\">\n";
					} elseif ($c=='23') {
						#se ved c==9
					} elseif ($c=='24') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						$a=str_replace('$bord',$bord[$bordnr],$a);
						print "<td><INPUT $disabled $tmp TYPE=\"submit\" NAME=\"kor_bord\"VALUE=\"$a\">";
					} elseif ($c=='25') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<TD align=\"center\"><INPUT TYPE=\"submit\" $disabled $tmp NAME=\"kontoopslag\" VALUE=\"$a\">\n";
					} elseif ($c=='26') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						(!$kontonr || $sum || $disabled)?$dis="disabled='disabled'":$dis=NULL;
						print "<TD align=\"center\"><INPUT TYPE=\"submit\" $dis $tmp NAME=\"indbetal\" VALUE=\"$a\" onclick=\"pos_ordre.$fokus.value += 'i';pos_ordre.$fokus.focus();\">\n";
					} elseif ($c=='27') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<TD align=\"center\"><INPUT TYPE=\"submit\" $tmp NAME=\"betaling\" VALUE=\"$a\" onclick=\"pos_ordre.$fokus.value += 'k';pos_ordre.$fokus.focus();\">\n";
					} elseif ($c=='28') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						list($txt,$m)=explode("+",$a);
 						print "<input type=\"hidden\" name=\"sidemenu\" value=\"$m\">";
						print "<td><INPUT $tmp TYPE=\"submit\" NAME=\"OK\" VALUE=\"$txt\">";
					} elseif ($c=='29') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<TD><INPUT TYPE=\"submit\" $tmp $disabled NAME=\"afslut\"VALUE=\"$a\" onclick=\"pos_ordre.$fokus.value = 'v';pos_ordre.$fokus.focus();\">\n";
					} elseif ($c=='30') {
						($disabled)?$dis="disabled='disabled'":$dis=NULL;
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						if ($sum && $kontonr) $returnconfirm="onclick=\"javascript:return confirm('Flyt denne ordre til kontant eller anden kunde?')\"";
#						elseif ($sum) $returnconfirm="onclick=\"javascript:return confirm('Flyt denne ordre til kunde?')\"";
#						else $returnconfirm=NULL;
						$returnconfirm=NULL;
						print "<TD align=\"center\"><INPUT TYPE=\"submit\" $dis $tmp NAME=\"stamkunder\" VALUE=\"$a\" $returnconfirm>\n";
					} elseif ($c=='31') {
						(!$kontonr || $disabled)?$dis="disabled='disabled'":$dis=NULL;
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<TD align=\"center\"><INPUT TYPE=\"submit\" $dis $tmp NAME=\"kontoudtog\" VALUE=\"$a\">\n";
					} elseif ($c=='32') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><INPUT $disabled $tmp TYPE=\"submit\" NAME=\"udskriv_sidste\" VALUE=\"$a\">";
					} elseif ($c=='33') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><INPUT $disabled $tmp TYPE=\"submit\" NAME=\"saet\" VALUE=\"$a\">";
					} elseif ($c=='34') {
						$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
						print "<td><a style=\"text-decoration: none\" href=\"udskriftsvalg.php?id=$id&valg=1&formular=3\"><INPUT TYPE=\"button\" $disabled $tmp VALUE=\"$a\"></a>";
					} else {
						$knap=str_replace('$kasse',$kasse,$knap);
						$knap=str_replace('$brugernavn',$brugernavn,$knap);
						$tmp=systemknap($c);
						if (strpos($tmp,'nclick')) $knap=str_replace(">",$tmp.">",$knap);
						else $knap=str_replace("type=\"button\"",$tmp,$knap);
						print "<td>".$knap;
					}
				} elseif ($d==7) {
					$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
					(((!$id && !$varenr_ny) || $betalingsbet!='Kontant') && !$indbetaling)?$tmp2="disabled=disabled ".$tmp:$tmp2=$tmp; #20160927 $kontonr rettet til $betalingsbet.:: 20161006
					if ($a=='Kontant') {
						print "<TD><INPUT TYPE=\"submit\" $tmp2 NAME='betaling' VALUE=\"$a\" onclick=\"pos_ordre.$fokus.value += 'c';pos_ordre.$fokus.focus();\"></TD>\n"; #20160927 $tmp rettet til $tmp2
					} elseif ($a=='Kontant på beløb') {
						print "<TD><INPUT TYPE=\"submit\" $tmp2 NAME='betaling' VALUE=\"$a\" onclick=\"pos_ordre.$fokus.value += 'c';pos_ordre.$fokus.focus();\"></TD>\n";
					} elseif ($a=='Konto') {
						($betalingsbet=='Kontant' || !$sum)?$tmp2="disabled=disabled ".$tmp:$tmp2=$tmp;
						print "<TD><INPUT TYPE=\"submit\" $tmp2 NAME='betaling' VALUE=\"$a\" onclick=\"pos_ordre.$fokus.value += 'c';pos_ordre.$fokus.focus();\"></TD>\n";
					} elseif ($a=='Betalingskort') {
						print "<TD><INPUT TYPE=\"submit\" $tmp2 NAME='betaling' VALUE=\"Betalings \nkort\" onclick=\"pos_ordre.$fokus.value += 'c';pos_ordre.$fokus.focus();\"></TD>\n";
					} elseif ($a=='Gem som tilbud') {
						print "<TD onclick=\"return confirm('Gem ordre som tilbud?')\"><INPUT TYPE=\"submit\" $tmp NAME=\"gem\" VALUE=\"Gem som tilbud\"></TD>\n";
					} else {
						#if ($betvaluta && $betvaluta!='DKK') $tmp2="disabled=\"disabled\" ".$tmp;
						print "<TD><INPUT TYPE=\"submit\" $tmp2 NAME='betaling' VALUE=\"$a\" onclick=\"pos_ordre.$fokus.value += 'd';pos_ordre.$fokus.focus();\"></TD>\n"; #20160418
					}
				} elseif ($d==8) {
					$tmp=str_replace("background-color: ;","background-color: $b;",$stil);
					(((!$id && !$varenr_ny) || !$sum || $kontonr) && !$indbetaling)?$tmp2="disabled=disabled ".$tmp:$tmp2=$tmp;
						print "<TD align=\"center\"><INPUT TYPE=\"submit\" $tmp2 NAME=\"betvaluta\" VALUE=\"$a\"></TD>\n";
				} else print "<td><br>";
				print "</td>\n";
			} else print "<td></td>"; 
		}
		print "</tr>\n";
	}
	print "</tbody></table>\n"; # <- table 1
}} # function menubuttons

if (!function_exists('systemknap')) {
function systemknap($system_id) {
	global $id;
	global $kasse;
	global $bord;
	global $bordnr;

	if ($system_id==2) {
		$href="pos_ordre.php?id=$id&skift_bruger=1&bordnr=$bordnr";
		$return="onclick=\"window.location.href='$href'\"";
	} elseif ($system_id==7) {
		$href="pos_ordre.php?id=$id&kasse=$kasse&kassebeholdning=on&bordnr=$bordnr";
		$return="onclick=\"window.location.href='$href'\"";
	} elseif ($system_id==8) {
		$tmp='Bord';
		for ($x=0;$x<count($bord);$x++) {
			if ($bordnr==$x) $tmp=$bord[$x]; 
		}
		$href="pos_ordre.php?id=$id&kasse=?&bordnr=$tmp"; 
		$return="onclick=\"window.location.href='$href'\"";
	} elseif ($system_id==10) {
		$href="../index/menu.php";
		$return="onclick=\"window.location.href='$href'\"";
	} elseif ($system_id==11) {
		$return="TYPE=\"submit\" NAME=\"skuffe\"VALUE=\"Skuffe\"";
	} elseif ($system_id==13) {

	$return="TYPE=\"submit\" NAME=\"skuffe\"VALUE=\"Skuffe\"";
	}

	

	
	return ($return);
}}

if (!function_exists('tastatur')) {
function tastatur($kasse,$status) {
	print "\n<!-- Function tastatur (start)-->\n";

	global $afd_lager;
	global $betalingsbet;
	global $bgcolor;
	global $bgcolor2;
	global $bgcolor5;
	global $bruger_id;
	global $brugernavn;
	global $bon;
	global $bordnr;
	global $db;
	global $db_id;
	global $fokus;
	global $id;
	global $indbetaling;
	global $kontonr;
	global $modtaget;
	global $modtaget2;
	global $popup;
	global $regnskab;
	global $returside;
	global $sum;
	global $terminal_ip;
	global $vare_id;
	global $vare_id_ny;
	global $varelinjer;
	global $varenr_ny;
	global $vis_saet;

	if ($kasse=="?") find_kasse($kasse);
	
	$r = db_fetch_array(db_select("select box2,box3,box4,box7,box10 from grupper where art = 'POS' and kodenr='2'",__FILE__ . " linje " . __LINE__)); 
	$x=$kasse-1;
	$optalassist=$r['box2'];
	$tmp=explode(chr(9),$r['box3']);
	$printserver=trim($tmp[$x]);
	$tmp=explode(chr(9),$r['box4']); #20131205
	$terminal_ip=strtolower(trim($tmp[$x]));
	$tmp=explode(chr(9),$r['box10']); #20140820
	$koekkenprint=strtolower(trim($tmp[$x]));
	($r['box7'])?$bord=explode(chr(9),$r['box7']):$bord=NULL; #20140508

	($vare_id && $vare_id_ny)?$disabled="disabled=\"disabled\"":$disabled=NULL; 

	$stil=find_stil('knap',1,0.7);
	$knap="<input type=\"button\" $stil value= \"$a\">\n";
	
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) $url='https://';
	else $url='http://';
	$url.=$_SERVER['SERVER_NAME'];#.$_SERVER['PHP_SELF'];
	if (!strpos($url,$_SERVER['PHP_SELF'])) $url.=$_SERVER['PHP_SELF'];
	print "<tr>\n";
	$href="pos_ordre.php?id=$id&kasse=?&bordnr=$bordnr";
	print "<td width=\"$width\"><input $disabled type=\"button\" onclick=\"window.location.href='$href'\" $stil value=\"Kasse: $kasse\"></td>\n";
	$href="pos_ordre.php?id=$id&skift_bruger=1&bordnr=$bordnr";
	print "<td width=\"$width\"><input $disabled type=\"button\" onclick=\"window.location.href='$href'\" $stil value=\"$brugernavn\"></td>\n";
	print "<td width=\"$width\">\n";
#	if ($optalassist) {
	$href="pos_ordre.php?id=$id&kasse=$kasse&kassebeholdning=on&bordnr=$bordnr";
	print "<input $disabled type=\"button\" onclick=\"window.location.href='$href'\" $stil value=\"Kasse\nopt&aelig;lling\">\n";
#	} else {
#	}
	print "</td>\n";
/*
	print "<td>$regnskab Kasse: <a href=pos_ordre.php?id=$id&kasse=?>$kasse</a></td>\n";
	print "<td><a href=pos_ordre.php?id=$id&kasse=$kasse&kassebeholdning=on>Kasseopt&aelig;lling</a></td>\n";
*/
	if ($terminal_ip) {
		if ($terminal_ip=='box' && $_COOKIE['salditerm']) $terminal_ip=$_COOKIE['salditerm'];
		if ($terminal_ip=='box' || $terminal_ip=='saldibox') {
#			$filnavn="$url/kasse/".$_SERVER['REMOTE_ADDR'].".ip";
			$filnavn="http://saldi.dk/kasse/".$_SERVER['REMOTE_ADDR'].".ip";
			if ($fp=fopen($filnavn,'r')) {
				$terminal_ip=trim(fgets($fp));
				fclose ($fp);
			}
		} else {
			setcookie("salditerm",$terminal_ip,time()+3600);
		}	
			$href="http://$terminal_ip/pointd/point.php?url=$url&id=$id&kasse=$kasse";
			print "<td width=\"$width\"><input $disabled type=\"button\" onclick=\"window.location.href='$href'\" $stil value=\"Kort\nterminal\"></td>\n";
#		print "<td><a href=http://$terminal_ip/pointd/point.php?url=$url&id=$id&kasse=$kasse>Kortterminal</a></td>\n"; #20131205
	} else print "<td width=\"$width\"></td>\n";
	$href="pos_ordre.php?id=$id&find_bon=1;";
	print "<td width=\"$width\"><input $disabled type=\"button\" onclick=\"window.location.href='$href'\" $stil value=\"Find\nbon\"></td>\n";
	$tmp=str_replace("background-color: $bgcolor5",'background-color:#ff0000',$stil);
	if ($popup) $href="../includes/luk.php";
	else $href="../index/menu.php";
	print "<td><input $disabled type=\"button\" onclick=\"window.location.href='$href'\" $tmp value=\"Luk\"></td>\n";
#	print "<td width=\"$width\" align=\"right\" valign=\"top\"><a href='pos_ordre.php?luk=1&returside=$returside'><div class=\"luk\"></div></a></td></tr>\n";
	print "</tr>\n";
	print "<tr><td colspan=\"6\"><hr></td></tr>\n";
	print "<tr>\n";
	if (count($bord) && $status<'3') {
		$tmp='Bord';
		for ($x=0;$x<count($bord);$x++) {
#			$y=$x+1;
			if ($bordnr==$x) $tmp=$bord[$x]; 
		}
		$kstil=$stil;
#		if ($id && $db=="bizsys_72") { #20150613
			$qtxt="select sum(ordrelinjer.antal) as iordre, sum(ordrelinjer.leveret) as bestilt from ordrelinjer,varer ";
			$qtxt.="where ordrelinjer.ordre_id='$id' and varer.kategori!='' and ordrelinjer.vare_id=varer.id";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)); 
			if ($r['iordre']>$r['bestilt']){
				$kstil=str_replace($bgcolor,'#ff0000',$stil);
			} elseif ($r['iordre']) {
				$kstil=str_replace($bgcolor,'#00ff00',$stil);
			} else $kstil=$stil;
#		}
		print "<td><INPUT $disabled $stil TYPE=\"submit\" NAME=\"bordvalg\" VALUE=\"$tmp\"></td>";
#		$href="../bordplaner/bordplan.php?id=$id";
#		print "<td width=\"$width\"><input $disabled type=\"button\" onclick=\"window.location.href='$href'\" $stil value=\"$tmp\"></td>\n";
		print "<td><INPUT $disabled $stil TYPE=\"submit\" NAME=\"flyt_bord\"VALUE=\"Flyt bord\"></td>\n"; #20140508
		print "<td><INPUT $disabled $stil TYPE=\"submit\" NAME=\"del_bord\"VALUE=\"Del bord\"></td>\n";
		if ($koekkenprint) print "<td><INPUT $disabled $kstil TYPE=\"submit\" NAME=\"koekken\"VALUE=\"Køkken\"></td>\n";
		print "<td><INPUT $disabled $stil TYPE=\"submit\" NAME=\"udskriv\"VALUE=\"Udskriv\"></td>\n";
		print "<td><INPUT $disabled $stil TYPE=\"submit\" NAME=\"skuffe\"VALUE=\"Skuffe\"></td>\n";
	}  
	print "</tr>\n";

	#	global $afslut;
	
	if ($betalingsbet=='Kontant') $betalingsbet=NULL; 
	
	$sum=afrund($sum,2);
	$modtaget=afrund($modtaget,2);
	$modtaget2=afrund($modtaget2,2);

	$r = db_fetch_array(db_select("select * from grupper where art = 'POS' and kodenr = '1'",__FILE__ . " linje " . __LINE__));
	$kortantal=$r['box4']*1;
#cho "kortantal	$kortantal<br>\n";
	$korttyper=explode(chr(9),$r['box5']);
#cho "korttyper ".count($korttyper),"<br>\n";
#for ($i=0;$i<count($korttyper);$i++) #cho "$korttyper[$i]<br>\n";
	$vis_kontoopslag=$r['box11'];
	$vis_hurtigknap=$r['box12'];
	$vis_indbetaling=$r['box14'];
	$timeout=$r['box13']*1;

	$vis_gem=1;
	if ($varenr_ny || $fokus=='modtaget' || $fokus=='modtaget2') $vis_gem=0;
	print "<input type=hidden name=\"sum\" value=\"$sum\">\n";
	print "<input type=hidden name=\"kontonr\" value=\"$kontonr\">\n";
	$stil=find_stil('knap',1,1);
	$stil2=find_stil('knap',2,1);

	print "<TR><TD></TD>\n";
	if ($status < 3) {
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"one\"   VALUE=\"1\" onclick=\"pos_ordre.$fokus.value += '1';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"two\"   VALUE=\"2\" onclick=\"pos_ordre.$fokus.value += '2';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"three\" VALUE=\"3\" onclick=\"pos_ordre.$fokus.value += '3';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"plus\"  VALUE=\"+\" onclick=\"pos_ordre.$fokus.value += '+';pos_ordre.$fokus.focus();\"></TD>\n";
		if ($vis_saet) print "<td><INPUT $disabled ".find_stil('knap',1,0.7)." TYPE=\"submit\" NAME=\"saet\"VALUE=\"Sæt\"></td>\n";
		elseif ($db=='bizsys_48') print "<td><INPUT $disabled $stil TYPE=\"submit\" NAME=\"skuffe\"VALUE=\"Skuffe\"></td>\n";
	print "</tr>\n";
		print "<TD></TD></TR><TR><TD></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"four\"  VALUE=\"4\" onclick=\"pos_ordre.$fokus.value += '4';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"five\"  VALUE=\"5\" onclick=\"pos_ordre.$fokus.value += '5';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"six\"   VALUE=\"6\" onclick=\"pos_ordre.$fokus.value += '6';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"minus\" VALUE=\"-\" onclick=\"pos_ordre.$fokus.value += '-';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD></TD></TR><TR><TD></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"seven\" VALUE=\"7\" onclick=\"pos_ordre.$fokus.value += '7';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"eight\" VALUE=\"8\" onclick=\"pos_ordre.$fokus.value += '8';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"nine\"  VALUE=\"9\" onclick=\"pos_ordre.$fokus.value += '9';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"times\" VALUE=\"x\" onclick=\"pos_ordre.$fokus.value += '*'\"></TD>\n";
		print "<TD></TD></TR><TR><TD></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"zero\"  VALUE=\",\" onclick=\"pos_ordre.$fokus.value += ',';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"zero\"  VALUE=\"0\" onclick=\"pos_ordre.$fokus.value += '0';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"DoIt\"  VALUE=\"=\" onclick=\"pos_ordre.$fokus.value = eval(pos_ordre.$fokus.value);pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"div\"   VALUE=\"/\" onclick=\"pos_ordre.$fokus.value += '/';pos_ordre.$fokus.focus();\"></TD>\n";
		print "<TD></TD></TR><TR><TD></TD>\n";
		print "<TD><INPUT TYPE=\"button\" $stil NAME=\"clear\" VALUE=\"Ryd\" onclick=\"pos_ordre.$fokus.value = '';pos_ordre.$fokus.focus();\"></TD>\n";
		if ($id) {
			($afd_lager)?$returnconfirm="return confirm('Husk at vælge lager');":$returnconfirm=NULL;
			print "<TD><INPUT TYPE=\"submit\" $stil NAME=\"afslut\"VALUE=\"Afslut\" onclick=\"pos_ordre.$fokus.value += 'a';pos_ordre.$fokus.focus();".$returnconfirm."\"></TD>\n";
			print "<TD onclick=\"return confirm('Slet alt og start forfra')\"><INPUT TYPE=\"submit\" $stil NAME=\"forfra\"VALUE=\"Forfra\" onclick=\"pos_ordre.$fokus.value += 'f';pos_ordre.$fokus.focus();\"></TD>\n";
		} else print "<TD COLSPAN=\"2\"></TD>\n";
		if ($fokus=='modtaget' || $fokus=='modtaget2') {
			print "<TD onclick=\"return confirm('Tilbage til varescanning')\"><INPUT TYPE=\"submit\" $stil NAME=\"tilbage\"VALUE=\"Tilbage\" onclick=\"pos_ordre.$fokus.value += 't';pos_ordre.$fokus.focus();\"></TD>\n";
			print "<TD></TD></TR><TR>\n";
			print "<TD COLSPAN=\"3\"></TD>\n";
		}
		print "<TR><TD COLSPAN=\"6\"><HR></TD></tr>\n";
		print "<TR><TD></TD>\n";
		if ($fokus=='varenr_ny') print "<TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=\"varer\"VALUE=\"Varer\" onclick=\"pos_ordre.$fokus.value += 'v';pos_ordre.$fokus.focus();\"></TD>\n";
		elseif ($fokus=='antal_ny' || $fokus=='pris_ny') { #20130310 Tilføjet: || $fokus=='pris_ny' 
			if ($fokus=='antal_ny') print "<TD COLSPAN=\"1\"><INPUT TYPE=\"submit\" $stil NAME=\"pris\"VALUE=\"Pris\" onclick=\"pos_ordre.$fokus.value += 'p';pos_ordre.$fokus.focus();\"></TD>\n";
			else print "<TD COLSPAN=\"1\"></TD>\n";
			print "<TD COLSPAN=\"1\"><INPUT TYPE=\"submit\" $stil NAME=\"rabat\"VALUE=\"Rabat\" onclick=\"pos_ordre.$fokus.value += 'r';pos_ordre.$fokus.focus();\"></TD>\n";
		} elseif ($fokus=='modtaget' && ($modtaget>=$sum || !$modtaget==0.00) && !$indbetaling && $betalingsbet) {
			print "<TR><TD></TD><TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=\"betaling\" VALUE=\"Konto\" onclick=\"pos_ordre.$fokus.value += 'k';pos_ordre.$fokus.focus();\"></TD>\n";
		} elseif ($fokus=='modtaget2' && $modtaget+$modtaget2>=$sum && !$indbetaling && $betalingsbet) {
			print "<TR><TD></TD><TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=\"betaling2\" VALUE=\"Konto\" onclick=\"pos_ordre.$fokus.value += 'k';pos_ordre.$fokus.focus();\"></TD>\n";
		}	elseif ($indbetaling && $modtaget >= $indbetaling) {
			print "<TR><TD></TD><TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=\"betaling\" VALUE=\"Kontant\" onclick=\"pos_ordre.$fokus.value += 'c';pos_ordre.$fokus.focus();\"></TD>\n";
		} else print "<TD colspan=2></TD>\n";
		print "<TD colspan=2><INPUT TYPE=\"submit\" $stil2 NAME=\"OK\"  VALUE=\"Enter\"></TD></tr>\n";
		#if ($vis_hurtigknap && $fokus=='antal_ny') print "<TR><TD></TD><TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=\"betaling\" VALUE=\"Kontant p&aring; bel&oslash;b\" onclick=\"pos_ordre.$fokus.value += 'c';pos_ordre.$fokus.focus();\"></TD>\n";
		if ($vis_kontoopslag && !$varenr_ny && !$indbetaling) {
			print "<TR><TD></TD><TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=\"kontoopslag\" VALUE=\"Kontoopslag\"></TD>\n";
			if ($vis_saet && $fokus=='modtaget') print "<TD COLSPAN=\"2\" onclick=\"return confirm('Gem ordre som tilbud?')\"><INPUT TYPE=\"submit\" $stil2 NAME=\"gem\" VALUE=\"Gem som tilbud\"></TD>\n";
		}
		print "</tr>\n";
		if ((($fokus=='modtaget' || $fokus=='modtaget2') && (!$betalingsbet)) || ($indbetaling && $modtaget>=$indbetaling && $kontonr)) {
			if ($div_kort_kto) { #20140129
				($fokus=='modtaget2')?$tmp="betaling2":$tmp="betaling";
				print "<TR><TD></TD><TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=$tmp VALUE=\"Betalingskort\" onclick=\"pos_ordre.$fokus.value += 'd';pos_ordre.$fokus.focus();\"></TD></tr>\n";
			} else {
				for($x=0;$x<$kortantal;$x++) {
					($fokus=='modtaget2')?$tmp="betaling2":$tmp="betaling";
					print "<TR><TD></TD><TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=$tmp VALUE=\"$korttyper[$x]\" onclick=\"pos_ordre.$fokus.value += 'd';pos_ordre.$fokus.focus();\"></TD></tr>\n";
				}
			}
			if (!$indbetaling) {
				if ($fokus=='modtaget2') $tmp="betaling2";
				else $tmp="betaling";
				print "<TR><TD></TD><TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=$tmp VALUE=\"Kontant\" onclick=\"pos_ordre.$fokus.value += 'c';pos_ordre.$fokus.focus();\"></TD>\n";
#				if ($vis_saet) print "<TD COLSPAN=\"2\" onclick=\"return confirm('Gem ordre som tilbud?')\"><INPUT TYPE=\"submit\" $stil2 NAME=\"gem\" VALUE=\"Gem som tilbud\"></TD>\n";
				print "</TR>";
			}
#			print "<TR><TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=\"betaling\" VALUE=\"Konto\" onclick=\"pos_ordre.$fokus.value += 'k';pos_ordre.$fokus.focus();\"></TD></tr>\n";
		} elseif ($id && $kontonr && !$varelinjer && !$indbetaling)
		if ($vis_indbetaling) print "<TR><TD></TD><TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=\"indbetaling\" VALUE=\"Indbetaling\" onclick=\"pos_ordre.$fokus.value += 'i';pos_ordre.$fokus.focus();\"></TD>\n";
	} else {
#		print "<input type=\"hidden\" name=\"bon\" value = \"\">\n";
#		$stil2="STYLE=\"width: 9.5em;height: 2em;font-size:150%;\"";
		print "<TR><TD COLSPAN=\"6\"><br></TD></TR>\n";
		print "<TR><TD><br></TD>\n";
		print "<TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=\"udskriv\"VALUE=\"Udskriv\"></TD>\n";
		print "<TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=\"ny\"VALUE=\"Ny kunde\"></TD></TR>\n";
		print "<TD COLSPAN=\"6\"><br></TD></TR>\n";
		print "<TD COLSPAN=\"6\"><br></TD></TR>\n";
		print "<TR><TD><br></TD>";
		if ($id && $vis_saet) print "<TD COLSPAN=\"2\" align=\"center\"><a style=\"text-decoration: none\" href=udskriftsvalg.php?id=$id&valg=1&formular=3><INPUT TYPE=\"button\" $stil2 VALUE=\"Følgeseddel\"></a></TD>\n";
		else print "<TD><br></TD>";
		print "<TD COLSPAN=\"2\" align=\"center\"><INPUT TYPE=\"submit\" $stil2 NAME=\"krediter\"VALUE=\"Korrektion\"></TD>\n";
#		print "<TD COLSPAN=\"2\"><INPUT TYPE=\"submit\" $stil2 NAME=\"ny\"VALUE=\"Ny kunde\"></TD>\n";
		if ($timeout && !$bon) print "<meta http-equiv=\"refresh\" content=\"$timeout;URL=pos_ordre.php?id=0\">\n";
	}
	print "</tr>\n";
#	print "</TBODY></TABLE></TD></tr>\n";
	print "\n<!-- Function tastatur (slut)-->\n";
}}
