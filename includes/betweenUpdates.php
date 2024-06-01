<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- includes/betweenUpdates.php --- patch 4.0.9--- 2024.03.30 --
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
// http://www.saldi.dk/dok/GNU_GPL_v2.html
//
// Copyright (c) 2003-2024 Saldi.dk ApS
// ----------------------------------------------------------------------
// The content of this file must be moved to opdat_4.0 in section 4.1.0 when 4.1.0 is to be released.
	$chklst = $delete = array();
	$i=$x=0;
	$qtxt = "SELECT * FROM grupper WHERE ";
	$qtxt .= "art = 'SM' OR art = 'KM'  OR art = 'EM' OR art = 'YM' OR art = 'MR' OR art = 'DG' OR art = 'KG' ";
	$qtxt .= "OR art = 'KM' OR art = 'VG' OR art = 'POS' OR art = 'OreDif' order by id";
$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
while ($r = db_fetch_array($q)) {
		$tmp = $r['art']."|".$r['kodenr']."|".$r['fiscal_year'];
		if (in_array($tmp,$chklst)) {
			$delete[$i] = $r['id'];
			$i++;
		} else {
			$chklst[$x] = $tmp;
			$x++;
}
}
	for ($i=0;$i<count($delete);$i++) {
		$qtxt = "delete from grupper where id = $delete[$i]";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
?>
