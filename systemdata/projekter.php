<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ ^ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- systemdata/projekter.php --- lap 3.9.9 --- 2023-03-23 ---
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
// Copyright (c) 2003-2020 saldi.dk aps
// ----------------------------------------------------------------------
// 20160118 div smårettelser.
// 20210211 PHR Some cleanup
// 20230323 PBLM Fixed minor error

@session_start();
$s_id=session_id();

$cfg = $nopdat = NULL;	

$modulnr=1;
$title="Systemsetup";
$css="../css/standard.css";
	
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("top.php");

$id=if_isset($_GET['id']);
$tilpas=if_isset($_GET['tilpas']);
$slet=if_isset($_GET['slet']);

$gem=if_isset($_POST['submit']);

if ($gem) {
	if ($tilpas) {
		$cfg=if_isset($_POST['cfg']);
		if($id) db_modify("update grupper set box1='$cfg' where id='$id'",__FILE__ . " linje " . __LINE__);
		else db_modify("insert into grupper (beskrivelse,art,kodenr,box1) values ('projekt','PRJ','0','$cfg')",__FILE__ . " linje " . __LINE__);
	} else {
		$beskrivelse=db_escape_string(trim(if_isset($_POST['beskrivelse'])));
		$projektarray=if_isset($_POST['projektnr']);
		$cfg=if_isset($_GET['cfg']);
		$prcfg=explode("|",$cfg);
		$cols=count($prcfg);
		$projektnr=NULL;
		for($y=0;$y<$cols;$y++) {
			$projektnr.=$projektarray[$y];
		}
		$projektnr=db_escape_string(trim($projektnr));
#		echo "cfg $cfg $projekt_nr<br";
		if(db_fetch_array(db_select("SELECT id FROM grupper where beskrivelse='$beskrivelse' and art = 'PRJ' and kodenr = '$projektnr' and id != '$id'",__FILE__ . " linje " . __LINE__))) {
			print "<BODY onLoad=\"javascript:alert('Projektnummer er allerede i brug')\">";
		} elseif (!$slet) {
			if($id) db_modify("update grupper set kodenr='$projektnr',beskrivelse='$beskrivelse' where id='$id'",__FILE__ . " linje " . __LINE__);
			elseif ($beskrivelse && $projektnr) {
				db_modify("insert into grupper (beskrivelse,art,kodenr) values ('$beskrivelse','PRJ','$projektnr')",__FILE__ . " linje " . __LINE__);
			} else echo "både Projektnr og Beskrivelse skal udfyldes<br>";
		}
 	}
	$id=0;
	$tilpas=NULL;
} elseif ($slet && $id) {
	db_modify("delete from grupper where art='PRJ'and id = $id",__FILE__ . " linje " . __LINE__);
	$id=0;
}
$qtxt="SELECT * FROM grupper where beskrivelse='projekt' and art = 'PRJ' and kodenr = '0' order by id";
if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
$cfg_id=$r['id']*1;
$cfg=$r['box1'];
db_modify("delete from grupper where art='PRJ' and kodenr='0' and id != $cfg_id",__FILE__ . " linje " . __LINE__);
}
if (!$cfg) $cfg=4;
/*
if (!$cfg) {
	$cfg=4;
	$q=db_select("SELECT * FROM grupper where art = 'PRJ' and kodenr != '0'",__FILE__ . " linje " . __LINE__);
	while($r=db_fetch_array($q)) {
		if (strlen($r['kodenr'])>$cfg) $cfg=strlen($r['kodenr']);
	}
	if ($cfg_id) update 
echo "insert into grupper (beskrivelse,art,kodenr,box1) values ('projekt','PRJ','0','$cfg')<br>";
	db_modify("insert into grupper (beskrivelse,art,kodenr,box1) values ('projekt','PRJ','0','$cfg')",__FILE__ . " linje " . __LINE__);
}
*/


print "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tbody>";
	if ($tilpas) tilpas($cfg_id,$cfg);
	else rediger($id);
print "</tbody></table>";
print "</body></html>";

function tilpas($id,$cfg) {
	print "<tr><td colspan=\"2\" align=center><b>Tilpas projekt ops&aelig;tning</td></tr>\n";
	$tekst="Skriv syntaks for projektnummereringen for at lette søgning.<br>
		Hvis du f.eks skriver 4|2|3|4 bliver projektnummeret opdelt i 4 grupper med hhv. 4,2,3,4 tegn i hvert felt.<br>
		Dette kan lette søgning, hvis du anvender lange og komplekse projektnumre.<br><hr>";
	print "<tr><td colspan=\"2\">$tekst</td>";
	print "<form name=\"projekter\" action=\"projekter.php?tilpas=1&id=$id\"  method=\"post\">";
	print "<tr><td>Projektopdeling: <input type=\"text\" name=\"cfg\" style=\"width:200px\" value=\"$cfg\"></td>
	<td align=\"right\"><input type=submit accesskey=\"g\" value=\"Gem\" name=\"submit\"></td></tr>";
	print "</form>";
}

function rediger($id) {
	global $cfg;
	global $db_encode;
	
	$projektnr = $pos =
	
	$id*=1;
	if ($id) {
		$r=db_fetch_array(db_select("SELECT kodenr,beskrivelse FROM grupper where id='$id'",__FILE__ . " linje " . __LINE__));
		$projektnr=$r['kodenr'];
		$beskrivelse=$r['beskrivelse'];
	}
	$rettekst="Klik her for at rette nr enner navn på projektet";
	$slettekst="Klik her for at slette projektet";
	$prcfg=explode("|",$cfg);
	$cols=count($prcfg);
	$colspan=$cols+3;
	$spantekst="klik her for at rette i projektopsætningen";
	print "<tr><td colspan=\"$colspan\" align=\"center\"><span title=\"$spantekst\"><b><a href=\"projekter.php?tilpas=1\">Projekter</a></b></span></td></tr>\n";
	print "<tr><td colspan=\"$cols\">Nr.</td><td>Beskrivelse</td></tr>\n";

	print "<form name=\"projekter\" action=\"projekter.php?id=$id&cfg=$cfg\"  method=\"post\">";
	print "<tr>";
	$pos=0;
	for($y=0;$y<$cols;$y++) {
		$width=$prcfg[$y]*10;
		$width=$width."px";
		print "<td><input class=\"inputbox\" type=\"text\" name=\"projektnr[$y]\" style=\"width:$width\" value=\"".mb_substr($projektnr,$pos,$prcfg[$y],$db_encode)."\"></td>";
		$pos+=$prcfg[$y];
	}
	$new_beskrivelse = if_isset($beskrivelse, 0);
	print "<td><input class=\"inputbox\" type=\"text\" name=\"beskrivelse\" style=\"width:200px\" value=\"$new_beskrivelse\"></td>
					<td colspan=\"2\"><input type=submit accesskey=\"g\" value=\"Gem\" name=\"submit\"></td>
				</tr>\n";
	print "</form>";
	$x=0;
	$q=db_select("SELECT * FROM grupper where art = 'PRJ' and kodenr != '0' order by kodenr desc",__FILE__ . " linje " . __LINE__);
	while($r=db_fetch_array($q)) {
		$x++;
		print "<tr>";
		$pos=0;
		if ($cols==1) print "<td>".$r['kodenr']."</td>";
		else {
			for($y=0;$y<$cols;$y++) {
				print "<td>".mb_substr($r['kodenr'],$pos,$prcfg[$y],$db_encode)."</td>";
				$pos+=$prcfg[$y];
			}
		}
		print "<td>$r[beskrivelse]</td>
			<td align=\"center\"><a title=\"$rettekst\" href=\"projekter.php?id=$r[id]&ret=1\"><img src=\"../ikoner/rename.png\" border=\"0\"></a></td>
			<td align=\"center\"><a title=\"$slettekst\" href=\"projekter.php?id=$r[id]&slet=1\" onclick=\"return confirm('Vil du slette dette projekt?')\"><img src=\"../ikoner/delete.png\" border=\"0\"></a></td>
			</tr>\n";
	}
}