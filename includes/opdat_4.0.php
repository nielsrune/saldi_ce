<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ------ includes/opdat_4.0.php-------rel. 4.0.4 ------2021-11-20---------------
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
// 20210828 LOE Added two tables for IP log
// 20210915 LOE Added sprog to regnskab table
// 20211009 PHR Some cleanup. + added language_id to online and scan_id to ordrer

if (!function_exists('opdat_4_0')) {
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
	$nextver='4.0.2';
	if ($fixNo<"2"){
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
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='ordrelinjer' and column_name='discounttxt'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER table ordrelinjer ADD column discounttxt varchar(25)",__FILE__ . " linje " . __LINE__);
				$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='ordrelinjer' and column_name='comment'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER table ordrelinjer ADD column comment varchar(25)",__FILE__ . " linje " . __LINE__);
				$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
			include("../includes/connect.php");
			$qtxt="UPDATE regnskab set version = '$nextver' where db = '$db'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
	$nextver='4.0.3'; #20210828
	if ($fixNo<"3"){
		include("../includes/connect.php");
		$r=db_fetch_array(db_select("select * from regnskab where id='1'",__FILE__ . " linje " . __LINE__));
		$tmp=$r['version'];
		if ($tmp<$nextver) {
			echo "opdaterer hovedregnskab til ver $nextver<br />";
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='settings' and column_name='var_grp'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE settings ADD column var_grp varchar(20)",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='settings' and column_name='user_id'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE settings ADD column user_id int",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='online' and column_name='language'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE online ADD column language_id int",__FILE__ . " linje " . __LINE__);
				$languages = "NULL".chr(9)."Dansk".chr(9)."English".chr(9)."Norsk";
				$qtxt = "insert into settings(var_name,var_grp,var_value,var_description,user_id) values ";
				$qtxt.= "('languages','globals','$languages','Avalilable languages','0')";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				$qtxt = "insert into settings(var_name,var_grp,var_value,var_description,user_id) values ";
				$qtxt.= "('languageId','globals','0','Active systemlanguage','0')";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
			$qtxt = "UPDATE regnskab set version = '$nextver' where id = '1'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
		include("../includes/online.php");
		if ($db!=$sqdb){
			$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='ordrer' and column_name='scan_id'";
			if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
				db_modify("ALTER TABLE ordrer ADD column scan_id numeric(15,0)",__FILE__ . " linje " . __LINE__);
}
			$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='brugere' and column_name='language_id'";
			if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
				db_modify("ALTER TABLE brugere ADD column language_id int",__FILE__ . " linje " . __LINE__);
			}
			db_modify("DELETE FROM tekster WHERE tekst_id >= '800'",__FILE__ . " linje " . __LINE__);
			$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			include("../includes/connect.php");
			$qtxt="UPDATE regnskab set version = '$nextver' where db = '$db'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
	$nextver='4.0.4'; #20210828
	if ($fixNo<"4"){
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
			$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='paperflow'";
			if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
				$qtxt ="CREATE TABLE paperflow (id serial NOT NULL,voucher_id int,upload_user_id int, upload_time varchar(10), ";
				$qtxt.="document varchar(40), insertion_user_id int, insertion_time varchar(10), lines int, ";
				$qtxt.="PRIMARY KEY (id))";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='ordrer' and column_name='due_date'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE ordrer ADD column due_date date",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='kontoplan' and column_name='system_account'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE kontoplan ADD column system_account boolean",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='kontoplan' and column_name='account_group'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE kontoplan ADD column account_group integer",__FILE__ . " linje " . __LINE__);
			}
			db_modify("DELETE FROM tekster WHERE tekst_id = '1465'",__FILE__ . " linje " . __LINE__);
			db_modify("UPDATE brugere set language_id='1'",__FILE__ . " linje " . __LINE__);
			$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			include("../includes/connect.php");
			$qtxt="UPDATE regnskab set version = '$nextver' where db = '$db'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
	$nextver='4.0.5'; #20210828
	if ($fixNo<"5") {
		include("../includes/connect.php");
		$r=db_fetch_array(db_select("select * from regnskab where id='1'",__FILE__ . " linje " . __LINE__));
		$tmp=$r['version'];
		if ($tmp<$nextver) {
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='regnskab' and column_name='global_id'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE regnskab ADD column global_id int default 0",__FILE__ . " linje " . __LINE__);
			}
			echo "opdaterer hovedregnskab til ver $nextver<br />";
			$qtxt = "UPDATE regnskab set version = '$nextver' where id = '1'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
		include("../includes/online.php");
		if ($db!=$sqdb){
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='settings' and column_name='var_grp'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE settings ADD column var_grp varchar(20)",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='settings' and column_name='user_id'";
			if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
				db_modify("ALTER TABLE settings ADD column user_id int",__FILE__ . " linje " . __LINE__);
			}
			if (!function_exists('findtekst')) include("../includes/std_func.php");
			db_modify("DELETE FROM tekster WHERE tekst_id = '437'",__FILE__ . " linje " . __LINE__);
			if (strpos(findtekst(768,$sprog_id),"'")) { #remove after rel 4.05
				$qtxt="delete from tekster where tekst_id = '767' or tekst_id = '768'";
				db_modify ($qtxt,__FILE__ . " linje " . __LINE__);
			}
			if (findtekst(644,1) == 'Advar ved for lav lagerbeholdning') {
				db_modify ("update tekster set tekst = '' where tekst_id = '644'",__FILE__ . " linje " . __LINE__); 
			}
			if (findtekst(390,$sprog_id)=="For at oprette en ny kategori skrives navnet p&aring; kategorien her") {
				db_modify("delete from tekster where tekst_id = '390'",__FILE__ . " linje " . __LINE__);
			}
			if (findtekst(644,1) == 'Advar ved for lav lagerbeholdning') {
				db_modify ("update tekster set tekst = '' where tekst_id = '644'",__FILE__ . " linje " . __LINE__); 
			}
			if (findtekst(671, 3) == 'blindtarm') db_modify("update tekster set tekst = '' where tekst_id = '671'",__FILE__ . " linje " . __LINE__);
			if (findtekst(795,1) == 'Aktivér Paperflow') db_modify ("update tekster set tekst = '' where tekst_id = '795'",__FILE__ . " linje " . __LINE__);
			if (findtekst(819,$sprog_id)==findtekst(689,$sprog_id)) {
				db_modify("delete from tekster where tekst_id >= '819' or tekst_id <= '825'",__FILE__ . " linje " . __LINE__);
			}
			if (findtekst(937, 1) == 'Retur') db_modify("update tekster set tekst = '' where tekst_id = '937'",__FILE__ . " linje " . __LINE__);
			if (findtekst(1001, 1) == 'Kredit') db_modify("update tekster set tekst = '' where tekst_id = '1001'",__FILE__ . " linje " . __LINE__);
			if (findtekst(1493, 1) == 'Kopi') db_modify("update tekster set tekst = '' where tekst_id = '1493'",__FILE__ . " linje " . __LINE__);

			$qtxt = "select id, var_value from settings where var_name = 'languages' order by id limit 1";
			$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			if ($r['id']) {
				db_modify("update settings set var_value = '$tmp' where var_name = 'languages'",__FILE__ . " linje " . __LINE__);
			}
			$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			include("../includes/connect.php");
			$qtxt="UPDATE regnskab set version = '$nextver' where db = '$db'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
		}
/*
	$nextver='4.0.6'; #20210828
	if ($fixNo<"6") {
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
			if ($db_type == "postgresql) {
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='mylabel' and column_name='condition'";
				if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
					db_modify("ALTER TABLE mylabel rename column condition to state",__FILE__ . " linje " . __LINE__);
				}
			}
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='paperflow' and column_name='lines'";
				if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
					db_modify("ALTER TABLE mylabel rename column lines to linecount",__FILE__ . " linje " . __LINE__);
				}
			}
			if (!function_exists('findtekst')) include("../includes/std_func.php");
			$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='rentalitems'";
			if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
				$qtxt ="CREATE TABLE rentalitems (id serial NOT NULL,rt_item_id int,item_id int, qty numeric(15,0), unit varchar(1), ";
				$qtxt.="PRIMARY KEY (id) )";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
			$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='rental' and column_name='rt_no'";
			if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
				$qtxt = "ALTER TABLE rental ADD rt_no int";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
			$qtxt = "SELECT DISTINCT(rt_item_id) AS rt_item_id from rental order by rt_item_id";
			$q0 = db_select($qtxt,__FILE__ . " linje " . __LINE__);
			while ($r0=db_fetch_array($q0)) {
				$x = 0;
				$na = $rtNo = array();
				$qtxt = "SELECT * from rental where rt_item_id = '$r0[rt_item_id]' order by rt_name";
				$q1 = db_select($qtxt,__FILE__ . " linje " . __LINE__);
				while ($r1=db_fetch_array($q1)) {
					$rtId[$x]       = $r1['id'];
					$rtItemId[$x]   = $r1['rt_item_id'];
					$rtItemName[$x] = $r1['rt_name'];
					$na = explode(" ",$rtItemName[$x]);
					$nb = count($na)-1;
					if (is_numeric($na[$nb]) && !in_array($na[$nb],$rtNo)) {
						$rtNo[$x] = $na[$nb];
						$l=strlen($rtItemName[$x])-strlen($rtNo[$x]);
						$rtItemName[$x] = trim(substr($rtItemName[$x],0,$l)); 
						$qtxt = "update rental set rt_name = '$rtItemName[$x]', rt_no = '$rtNo[$x]' where id = '$rtId[$x]'"; 
						db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					} else $rtNo[$x] =0;
					$x++;
				}
				$i=1;
				for ($x=0;$x<count($rtId);$x++) {
					while (!$rtNo[$x]) {
						if (!in_array($i,$rtNo)) {
							$rtNo[$x] = $i; 
							$qtxt = "update rental set rt_name = '$rtItemName[$x]', rt_no = '$rtNo[$x]' where id = '$rtId[$x]'"; 
							db_modify($qtxt,__FILE__ . " linje " . __LINE__);

						}
						$i++;
					}
				}
			}
			$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			include("../includes/connect.php");
			$qtxt="UPDATE regnskab set version = '$nextver' where db = '$db'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
*/			
}}
?>
