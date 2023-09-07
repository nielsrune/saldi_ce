<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- debitor/kassespor.php --- lap 4.0.7 --- 2023-02-06 ---
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
// Copyright (c) 2008-2023 Saldi.dk ApS
// ----------------------------------------------------------------------
//
// 20230118 PHR Added '$mSQt[$x] !=	 0 && ' as orders with some qty less than 0 could not split. 
// 20230206 PHR id_seq id now updated after inserting new orderlines.

print "<!-- BEGIN orderIncludes/moveOrderLines.php -->";
#print "moveOrderLines.php<br>";

$mQt    = $_POST['mQt'];  
$mSQt   = $_POST['mSQt'];
$maxQt  = $_POST['maxQt'];
$maxSQt = $_POST['maxSQt'];
for ($x=1;$x <= count($mQt);$x++) {
if (usdecimal($mQt[$x],3)  > $maxQt[$x]) {
		$mQt[$x]  = $maxQt[$x];
		$submit = 'split';
	}
	if ($mSQt[$x] !=	 0 && usdecimal($mSQt[$x],3) > $maxSQt[$x]) {
		$mSQt[$x] = $maxSQt[$x];
		$submit = 'split';
	}
}

if ($submit == 'split') alert("Nogle værdier sat for højt, antal reguleret til maks muligt. Kontroller og prøv igen.");
else {
	$newId = $_POST['MoveItemsTo'];
	if ($newId == '0') {
		$qtxt = "select max(id) as new_id FROM ordrer";
		$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$newId = $r['new_id']+1;
		$qtxt = "CREATE TEMPORARY TABLE temp_table AS SELECT * FROM ordrer WHERE id='$id'";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		$qtxt = "UPDATE temp_table SET id='$newId' WHERE id='$id'";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		$qtxt = "INSERT INTO ordrer SELECT * FROM temp_table";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		$qtxt = "SELECT setval('ordrer_id_seq', $newId)";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		$qtxt = "DROP TABLE temp_table";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	}
		for ($x=1;$x <= count($linje_id);$x++) {
		if ($mQt[$x] && $antal[$x] == $mQt[$x]) {
			$qtxt = "UPDATE ordrelinjer SET ordre_id = '$newId' WHERE id='$linje_id[$x]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "UPDATE batch_kob SET ordre_id = '$newId' WHERE linje_id='$linje_id[$x]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		} elseif ($mQt[$x]) {
			$qtxt = "CREATE TEMPORARY TABLE temp_table AS SELECT * FROM ordrelinjer WHERE id = '$linje_id[$x]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "select max(id) as new_id FROM ordrelinjer";
			$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$newLineId = $r['new_id']+1;
			$qtxt = "UPDATE temp_table SET id='$newLineId' WHERE id='$linje_id[$x]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "INSERT INTO ordrelinjer SELECT * FROM temp_table WHERE id='$newLineId'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "SELECT setval('ordrelinjer_id_seq', $newLineId)";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			if (!$antal[$x]) $antal[$x] = 0; 
			if ($mQt[$x]) $antal[$x] = $antal[$x] - $mQt[$x];
			$qtxt = "UPDATE ordrelinjer SET antal = $antal[$x] WHERE id='$linje_id[$x]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "UPDATE ordrelinjer SET antal = '$mQt[$x]', ordre_id = '$newId' WHERE id='$newLineId'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "DROP TABLE temp_table";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
##cho __line__."if ($mSQt[$x] && $antal[$x] == $mSQt[$x])<br>";
		if ($mSQt[$x] && $antal[$x] == $mSQt[$x]) {
			$qtxt = "UPDATE ordrelinjer SET ordre_id = '$newId' WHERE id='$linje_id[$x]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "UPDATE batch_kob SET ordre_id = '$newId' WHERE linje_id='$linje_id[$x]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		} elseif ($mSQt[$x]) {
#cho __line__." $mQt[$x] | $mSQt[$x] | $maxQt[$x] | $maxSQt[$x]<br>";
			#cho __line__." flytter ID $linje_id[$x] | $mSQt[$x] ($maxQt[$x]) $varenr[$x] ej lev<br>";
			$qtxt = "CREATE TEMPORARY TABLE temp_table AS SELECT * FROM ordrelinjer WHERE id = '$linje_id[$x]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "select max(id) as new_id FROM ordrelinjer";
			$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$newLineId = $r['new_id']+1;
			$qtxt = "UPDATE temp_table SET id='$newLineId' WHERE id='$linje_id[$x]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "INSERT INTO ordrelinjer SELECT * FROM temp_table WHERE id='$newLineId'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "SELECT setval('ordrelinjer_id_seq', $newLineId)";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
#cho __line__." $antal[$x] = $antal[$x] - $mSQt[$x]<br>";
			$antal[$x] = $antal[$x] - $mSQt[$x];
			$leveret[$x] = $leveret[$x] - $mSQt[$x];
			$qtxt = "UPDATE ordrelinjer SET antal = $antal[$x] WHERE id='$linje_id[$x]'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "UPDATE ordrelinjer SET antal = '$mSQt[$x]', ordre_id = '$newId' WHERE id='$newLineId'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "SELECT id FROM serienr WHERE kobslinje_id='$linje_id[$x]' ORDER BY id";
			$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
			$y=0;
			while ($r = db_fetch_array($q)) {
				if ($y < $mSQt[$x]) {
					$qtxt = "UPDATE serienr SET kobslinje_id = '$newLineId' WHERE id = '$r[id]'";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$y++;
			}

			$qtxt = "SELECT * FROM batch_kob WHERE linje_id='$linje_id[$x]' ORDER BY id";
#cho __line__." $qtxt<br>";
			$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
			$bk = $mvRm= 0;
			while ($r = db_fetch_array($q)) {
				$bkId[$bk]  = $r['id'];
				$bkQt[$bk]  = $r['antal']*1;
				$bkRm[$bk] = $r['rest']*1;
				$bkDt[$bk] = $r['kobsdate'];
				$mvRm     += $bkRm[$bk]; 
				#cho __line__." $bk bkQt $bkQt[$bk] bkRm $bkRm[$bk] x $x mSQt $mSQt[$x] Kbsd $bkDt[$bk]<br>";
				
				$bk++;
			}
			$mvQt = $mSQt[$x];
			for ($bk=0;$bk<count($bkId);$bk++) {
#cho __line__." $bk mv $mvQt ($bkQt[$bk])<br>";
				$deliveryDate = $bkDt[$bk];
				if ($mvQt && $bkQt[$bk] && $bkQt[$bk] <= $mvQt) {
#cho __line__." $bkQt[$bk] <= $mvQt<br>";
					$qtxt = "UPDATE batch_kob SET ordre_id = '$newId', linje_id = '$newLineId' WHERE id = $bkId[$bk]";
				#cho __line__." $qtxt<br>";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					$mvQt-= $bkQt[$bk];
#cho __line__." $mvQt = $mvQt - $bkQt[$bk]<br>";
				} else {
					$bkQt[$bk]-= $mvQt;
#					$mvQt = 0;
					if ($bkRm[$bk] >= $mvRm) {
						$bkRm[$bk] -= $mvRm;
						$mvRm = 0;
				} else {
						$mvRm -= $bkRm[$bk]; 
						$bkRm[$bk] = 0;
				}
				$qtxt = "UPDATE batch_kob SET antal = '$bkQt[$bk]', rest = '$bkRm[$bk]' WHERE id = $bkId[$bk]";
##cho __line__." $qtxt<br>";
#				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
			}
			if ($mvQt) {
				$qtxt = "insert into batch_kob (kobsdate,vare_id,ordre_id,linje_id,antal,rest) values ('$deliveryDate','$vare_id[$x]','$newId','$newLineId','$mvQt','$mvRm')";
#cho __line__." $qtxt<br>";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
			$newQty = $antal[$x];
#			$mvQt = $mSQt[$x];

			$qtxt = "select * from batch_kob WHERE linje_id='$linje_id[$x]' order by id";
			$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
			while ($r = db_fetch_array($q)) {
				$oldQty = $r['antal'];
				$oldId = $r['id'];
				if ($mvQt) {
#cho __line__." $oldQty >= $mvQt<br>";
					if ($mvQt && $oldQty >= $mvQt) {
						$qtxt = "update batch_kob set antal = antal-$mvQt where id = '$oldId'";
						$mvQt = 0;
					} elseif ($mvQt) {
						$qtxt = "update batch_kob set antal = 0 where id = '$oldId'";
						$mvQt-= $oldQty;
					}
					#cho __line__." $qtxt<br>";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
			}

#			$qtxt = "UPDATE batch_kob SET antal = $antal[$x] WHERE linje_id='$linje_id[$x]'";
##cho __line__." $qtxt<br>";
#			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt = "DROP TABLE temp_table";
#cho __line__." $qtxt<br>";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
transaktion('commit');
/*
	$qtxt = "SELECT MAX(id) - nextval('ordrer_id_seq') as nextval FROM ordrer"; #20230206
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	if ($r['nextval'] > 0) {
		$qtxt = "SELECT setval('ordrer_id_seq', (SELECT MAX(id) FROM ordrer))";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	}
	$qtxt = "SELECT MAX(id) - nextval('ordrelinjer_id_seq') as nextval FROM ordrelinjer"; 
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	if ($r['nextval'] > 0) {
		$qtxt = "SELECT setval('ordrelinjer_id_seq', (SELECT MAX(id) FROM ordrelinjer))";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	}
*/	
	print "<meta http-equiv=\"refresh\" content=\"0;URL=ordre.php?id=$id\">";
	exit;
}
?>
