<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ------ includes/opdat_3.9.php-------rel. 4.0.1 ------2021-03-12---------------
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
// Copyright (c) 2019 - 2021 Saldi.dk ApS
// ----------------------------------------------------------------------

function opdat_4_0($majorNo, $subNo, $fixNo){
	global $version;
	global $db;
	global $db_id;
	global $regnskab;
	global $regnaar;
	global $db_type;
	$s_id=session_id();

	$nextver='4.0.1';
	if ($fixNo<"1"){
		include("../includes/connect.php");
		$r=db_fetch_array(db_select("select * from regnskab where id='1'",__FILE__ . " linje " . __LINE__));
		$tmp=$r['version'];
		if ($tmp<$nextver) {
			echo "opdaterer hovedregnskab til ver $nextver<br />";
			$qtxt = "UPDATE regnskab set version = '$nextver' where id = '1'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
		include("../includes/online.php");
		if ($db!=$sqdb){
			$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='rentalitems'";
			if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
				$qtxt ="CREATE TABLE rentalitems (id serial NOT NULL,rt_item_id int,item_id int, qty numeric(15,0), unit varchar(1), ";
				$qtxt.="PRIMARY KEY (id) )";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='variant_varer' and column_name='variant_id'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE variant_varer ADD column variant_id int",__FILE__ . " linje " . __LINE__);
				db_modify("UPDATE variant_varer set variant_id='0' where variant_id is NULL",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='variant_varer' and column_name='variant_kostpris'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE variant_varer ADD column variant_kostpris numeric(15,3)",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='variant_varer' and column_name='variant_salgspris'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE variant_varer ADD column variant_salgspris numeric(15,3)",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='variant_varer' and column_name='variant_vejlpris'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE variant_varer ADD column variant_vejlpris numeric(15,3)",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='variant_varer' and column_name='variant_id'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE variant_varer ADD column variant_id int4",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='ordrer' and column_name='shop_status'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER table ordrer ADD column shop_status int",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='ordrer' and column_name='shop_id'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER table ordrer ADD column shop_id int",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='ordrelinjer' and column_name='rental_id'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER table ordrelinjer ADD column rental_id int",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
		include("../includes/connect.php");
		$qtxt="UPDATE regnskab set version = '$nextver' where db = '$db'";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	}
}

?>
