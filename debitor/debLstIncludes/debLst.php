<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- debitor(debLstIncludes/debLst.php --- lap 4.0.1 --- 2021-03-23 ----
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

$udv1=$udvaelg;
$colspan=$vis_feltantal+1;
$dgcount=count($dg_liste);
(!$dgcount)?$dgcount=1:NULL; 
for($i=0;$i<$dgcount;$i++) {
	if ($dg_liste[$i]) {
		for($i2=0;$i2<=$dg_antal;$i2++	) {
			if($dg_liste[$i]==$dg_id[$i2]) {
				if (!$start && !$lnr) {
#					print "<tr><td colspan=\"$colspan\"><hr></td>";
					$tmp=$start+$linjeantal;
				}
				if (!$cat_liste[0]) {
					print "<tr><td></td><td colspan=\"2\"><b>$dg_navn[$i2]</b></td></tr>\n";
					print "<tr><td colspan=\"$colspan\"><hr></td>";
				}
				$udv1=$udvaelg." and gruppe=$dg_kodenr[$i2]";	
				break 1;
			} 
		}	
	}
	$catcount=count($cat_liste);
	(!$catcount)?$catcount=1:NULL; 
	for($i3=0;$i3<$catcount;$i3++) {
		if ($cat_liste[$i3]) {
			for($i4=0;$i4<=$cat_antal;$i4++	) {
				if($cat_liste[$i3]==$cat_id[$i4]) {
					if (!$start && !$lnr) {
						$tmp=$start+$linjeantal;
					}
					print "<tr><td colspan=\"$colspan\"><hr></td></tr><tr>";
					if ($dg_navn[$i2]) $tmp="<td colspan=\"2\"><b>$dg_navn[$i2]</b></td>";
					else $tmp=""; 
					print "<tr><td></td>$tmp<td colspan=\"2\"><b>$cat_beskrivelse[$i4]</b></td></tr>\n";
					print "<tr><td colspan=\"$colspan\"><hr></td>";
					$udv2=$udv1." and (kategori = '$cat_id[$i4]' or kategori LIKE '$cat_id[$i4]".chr(9)."%' ";
					$udv2.="or kategori LIKE '%".chr(9)."$cat_id[$i4]' or kategori LIKE '%".chr(9)."$cat_id[$i4]".chr(9)."%')";	#20160218
					break 1;
				} 
			}	
		}
		$i=0;
		if (!$udv2) $udv2=$udv1;	
		if (!$udv2) $udv2=$udvaelg;	
		if (strpos($sortering,'kontaktet desc')) $udv2.=' and adresser.kontaktet is not NULL';
		$qtxt="select * from adresser where art = 'D' $udv2 order by $sortering";
		$query = db_select($qtxt,__FILE__ . " linje " . __LINE__);
		while ($row=db_fetch_array($query)) {
			$debitorkort="debitorkort".$row['id'];
			$debId[$i]=$row['id'];
			$mySale[$i]=$row['mysale'];
			$email[$i]=$row['email'];
			$lnr++;
			if(($lnr>=$start && $lnr<$slut) || $udv2) { 
#				$debId[$i]=$row['id'];
#				$mySale[$i]=$row['mysale'];
#				$email[$i]=$row['email'];
				$adresseantal++;
				$javascript=$understreg=$hrefslut="";
				
				if ($valg=='kommission') {
					if ($mySale[$i]) {
						$tmp=trim($_SERVER['PHP_SELF'],'/');
						$txt = $row['id'] .'|'. $row['kontonr'] .'@'. $db .'@'. $_SERVER['HTTP_HOST'];
						$lnk=$myLink;
						for ($x=0;$x<strlen($txt);$x++) {
							$lnk.=dechex(ord(substr($txt,$x,1)));
							$understreg="<a href='$lnk' target='blank' >";
							$hrefslut="</a>";
						}
						fwrite($myFile, $db.chr(9).$email[$i].chr(9).$lnk."\n");
					}
				} else {
					if ($valg == 'rental') $understreg="<a href=rental.php?tjek=$row[id]&customerId=$row[id]>";
					else $understreg="<a href=".$valg."kort.php?tjek=$row[id]&id=$row[id]&returside=debitor.php>";
					$hrefslut="</a>";
				}
				$linjetext="";
				if ($linjebg!=$bgcolor) {$linjebg=$bgcolor; $color='#000000';}
				else {$linjebg=$bgcolor5; $color='#000000';}
				print "<tr bgcolor=\"$linjebg\"><td bgcolor=$bgcolor></td>\n";
				print "<td align=$justering[0] $javascript> $linjetext $understreg $row[kontonr]$hrefslut</span><br></td>\n";
				for ($x=1;$x<$vis_feltantal;$x++) {
					print "<td align=$justering[$x]>";
					$tmp=$vis_felt[$x];
					if ($vis_felt[$x]=='kontoansvarlig') {
						for ($y=1;$y<=$ansatantal;$y++) {
							if ($ansat_id[$y]==$row[$tmp]) print stripslashes($ansat_init[$y]);
						}
					} elseif ($vis_felt[$x]=='status') {
						for ($y=0;$y<=$status_antal;$y++) {
							if ($row[$tmp] && $status_id[$y]==$row[$tmp]) print stripslashes($status_beskrivelse[$y]);
						}
					} elseif ($vis_felt[$x]=='invoiced' || $vis_felt[$x]=='kontaktet' || $vis_felt[$x]=='kontaktes') {
						if ($row[$tmp]=='1970-01-01') print "";
						else print dkdato($row[$tmp]);
					} else print $row[$tmp];
					print "</td>"; 
				}
				print "<input type=hidden name=adresse_id[$adresseantal] value=$row[id]>";
				if ($valg=='kommission') {
					if ($mySale[$i]) $mySale[$i]="checked='checked'";
					print "<td align='center'><input type='checkbox' name='mySale[$i]' $mySale[$i]></td>";
				}
				if ($valg=='kommission' || $valg=='historik') {
					print "<input type='hidden' name='debId[$i]' value='$debId[$i]'>";  
					($email[$i])?$dis=NULL:$dis="disabled title='email mangler på  konto'";
 					if (!$dis && isset($_POST['chooseAll']) && $_POST['chooseAll']) $dis = "checked='checked'";				
					if ($valg=='kommission') print "<td align='center'><input type='checkbox' name='invite[$i]' $dis></td>";
					else print "<td align='center'><input type='checkbox' name='mailTo[$i]' $dis></td>";
				} 
				$i++;
				print "</tr>";
			} elseif($myFile && $mySale[$i]) {
				$txt = $row['id'] .'|'. $row['kontonr'] .'@'. $db .'@'. $_SERVER['HTTP_HOST'];
				$lnk=$myLink;
				for ($x=0;$x<strlen($txt);$x++) {
					$lnk.=dechex(ord(substr($txt,$x,1)));
				}
			}
		}
		if ($search && !$lnr) {
			print "<tr><td colspan='$colspan' align='center'><b><big>Ingen debitorer opfylder de angivne søgekriterier</big></b></td></tr>";
		}
	} 
}
?>
