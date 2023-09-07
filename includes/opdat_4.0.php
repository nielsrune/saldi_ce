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
// 20221004 MLH Added coloumns on_price_list, tier_price_multiplier, salgspris_multiplier, tier_price_method, tier_price_rounding, salgspris_method, salgspris_rounding to the products table

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
	$nextver='4.0.6'; #20210828
	if ($fixNo<"6") {
		include("../includes/connect.php");
		$r=db_fetch_array(db_select("select * from regnskab where id='1'",__FILE__ . " linje " . __LINE__));
		$tmp=$r['version'];
		if ($tmp<$nextver) {
			echo "opdaterer hovedregnskab til ver $nextver<br />";
				$qtxt = "SELECT character_maximum_length FROM information_schema.columns WHERE table_name='regnskab' and column_name='email'";
				$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
				if ($r['character_maximum_length'] < 50) {
					$qtxt = "ALTER TABLE regnskab ALTER column email TYPE varchar(60)";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
			$qtxt = "UPDATE regnskab set version = '$nextver' where id = '1'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
		include("../includes/online.php");
			include("../includes/std_func.php");
		if ($db!=$sqdb){
				$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='rental'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "CREATE TABLE rental (id serial NOT NULL,rt_item_id int, rt_name varchar(40), PRIMARY KEY (id))";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
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
				$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='documents'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "CREATE TABLE documents (id serial NOT NULL,global_id int,filename text, filepath text, source varchar(20), ";
					$qtxt.= "source_id int, timestamp varchar(10), user_id int, PRIMARY KEY (id) )";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='varetilbud'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "CREATE TABLE varetilbud ";
					$qtxt.= "(id serial NOT NULL,vare_id integer,startdag numeric(15,0),slutdag numeric(15,0),starttid time,sluttid time,";
					$qtxt.= "ugedag integer,salgspris numeric(15,2),kostpris numeric(15,2),PRIMARY KEY (id))";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='adresser' and column_name = 'productlimit'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE adresser ADD COLUMN productlimit numeric(15,0)";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				db_modify ("update tekster set tekst = '' where tekst_id = '3'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '989'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1001'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1064'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1065'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1066'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1078'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1090'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1356'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1412'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1416'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1419'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1421'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1490'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1523'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1524'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1525'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1526'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1527'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1544'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1545'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1597'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1598'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1903'",__FILE__ . " linje " . __LINE__); 
				db_modify ("update tekster set tekst = '' where tekst_id = '1904'",__FILE__ . " linje " . __LINE__); 

				transaktion('begin');
				$qtxt = "SELECT table_name FROM information_schema.columns WHERE table_name='mylabel' and ";
				$qtxt.= "column_name = 'sold' and data_type = 'boolean'";
				if (db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
					$qtxt = "ALTER TABLE mylabel RENAME COLUMN sold to oldsold";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					$qtxt = "ALTER TABLE mylabel ADD COLUMN sold integer default 0";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					$qtxt = "UPDATE mylabel SET sold = 1 where oldsold is TRUE";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					$qtxt = "ALTER TABLE mylabel DROP COLUMN oldsold";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}	
				$qtxt = "SELECT table_name FROM information_schema.columns WHERE table_name='mylabel' and ";
				$qtxt.= "column_name = 'created' and data_type = 'boolean'";
				if (db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
					$qtxt = "ALTER TABLE mylabel ADD COLUMN created varchar(15)";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					$qtxt = "UPDATE mylabel SET created = lastprint";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					$qtxt = "UPDATE mylabel SET created = '0' where created is NULL";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt = "SELECT column_name FROM information_schema.columns WHERE table_name='ordrelinjer' and ";
				$qtxt.= "column_name = 'barcode'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
					$qtxt = "ALTER TABLE ordrelinjer ADD COLUMN barcode varchar(20)";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				transaktion('commit');
				include("../includes/connect.php");
				$qtxt="UPDATE regnskab set version = '$nextver' where db = '$db'";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
		}
		$nextver='4.0.7'; #20221004
		if ($fixNo<"7") {
			include("../includes/connect.php");
			$r=db_fetch_array(db_select("select * from regnskab where id='1'",__FILE__ . " linje " . __LINE__));
			$tmp=$r['version'];
			if ($tmp<$nextver) {
				echo "opdaterer hovedregnskab til ver $nextver<br />";
				$qtxt = "UPDATE regnskab set version = '$nextver' where id = '1'";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
			include("../includes/online.php");
			include("../includes/std_func.php");
			if ($db!=$sqdb)	{
				transaktion('begin'); 
				// tier_price
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='varer' and column_name = 'tier_price_multiplier'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE varer ADD COLUMN tier_price_multiplier numeric(15,2) default 0";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='varer' and column_name = 'tier_price_method'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE varer ADD COLUMN tier_price_method varchar(15) default 'percentage'";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='varer' and column_name = 'tier_price_rounding'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE varer ADD COLUMN tier_price_rounding varchar(15) default 'no_rounding'";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				// salgspris
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='varer' and column_name = 'salgspris_multiplier'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE varer ADD COLUMN salgspris_multiplier numeric(15,2) default 0";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='varer' and column_name = 'salgspris_method'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE varer ADD COLUMN salgspris_method varchar(15) default 'percentage'";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='varer' and column_name = 'salgspris_rounding'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE varer ADD COLUMN salgspris_rounding varchar(15) default 'no_rounding'";
						db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				// retail_price
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='varer' and column_name = 'retail_price_multiplier'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE varer ADD COLUMN retail_price_multiplier numeric(15,2) default 0";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='varer' and column_name = 'retail_price_method'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE varer ADD COLUMN retail_price_method varchar(15) default 'percentage'";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='varer' and column_name = 'retail_price_rounding'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE varer ADD COLUMN retail_price_rounding varchar(15) default 'no_rounding'";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				// on_price_list
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='varer' and column_name = 'on_price_list'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE varer ADD COLUMN on_price_list integer default 1";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				transaktion('commit'); 
				include("../includes/connect.php");
				$qtxt="UPDATE regnskab set version = '$nextver' where db = '$db'";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
		}
		$nextver='4.0.8'; #20221004
		if ($fixNo<"8") {
			include("../includes/connect.php");
			$r=db_fetch_array(db_select("select * from regnskab where id='1'",__FILE__ . " linje " . __LINE__));
			$tmp=$r['version'];
			if ($tmp<$nextver) {
				echo "opdaterer hovedregnskab til ver $nextver<br />";
				$qtxt = "UPDATE regnskab set version = '$nextver' where id = '1'";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
			include("../includes/online.php");
			include("../includes/std_func.php");
			if ($db!=$sqdb)	{
				transaktion('begin');
				$variantId = array();
				$qtxt = "SELECT data_type FROM information_schema.columns ";
				$qtxt.= "WHERE table_name='variant_varer' and column_name = 'variant_type' and data_type != 'integer'";
				if (db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$x=0;
					$qtxt = "select id, variant_type from variant_varer order by id";  
					$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
					while ($r = db_fetch_array($q)) {
						$variantId[$x]   = $r['id'];
						$variantType[$x] = (int)trim($r['variant_type']);
					$x++;
				}
					$qtxt = "ALTER TABLE variant_varer RENAME COLUMN variant_type TO var_type_delete";
							db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					$qtxt = "ALTER TABLE variant_varer ADD COLUMN variant_type integer";

					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					for ($x=0;$x<count($variantId);$x++) {
						$qtxt = "update variant_varer set variant_type = '$variantType[$x]' where id = '$variantId[$x]'";
						db_modify($qtxt,__FILE__ . " linje " . __LINE__);
						}
					}
				$qtxt = "SELECT data_type FROM information_schema.columns ";
				$qtxt.= "WHERE table_name='varer' and column_name = 'gruppe' and data_type != 'integer'";
				if (db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$x=0;
					$qtxt = "select id, gruppe from varer order by id";
					$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
					while ($r = db_fetch_array($q)) {
						$vareId[$x]   = $r['id'];
						$vareGruppe[$x] = (int)$r['gruppe'];
						$x++;
				}
						$qtxt = "ALTER TABLE varer RENAME COLUMN gruppe TO gruppe_delete";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					$qtxt = "ALTER TABLE varer ADD COLUMN gruppe integer default 0";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					for ($x=0;$x<count($vareId);$x++) {
						$qtxt = "update varer set gruppe = '$vareGruppe[$x]' where id = '$vareId[$x]'";
						db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					}
				}
				$qtxt = "SELECT data_type FROM information_schema.columns ";
				$qtxt.= "WHERE table_name='grupper' and column_name = 'kodenr' and data_type != 'integer'";
				if (db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$x=0;
					$qtxt = "select id, kodenr from grupper order by id";
					$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
					while ($r = db_fetch_array($q)) {
						$gruppeId[$x]   = $r['id'];
						$gruppeKode[$x] = trim($r['kodenr']);
						if (!$gruppeKode[$x]) $gruppeKode[$x] = 0; 
						$x++;
					}
					$qtxt = "ALTER TABLE grupper RENAME COLUMN kodenr to kodenr_delete";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					$qtxt = "ALTER TABLE grupper ADD COLUMN kodenr integer";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					for ($x=0;$x<count($gruppeId);$x++) {
						$qtxt = "update grupper set kodenr = '$gruppeKode[$x]' where id = '$gruppeId[$x]'";
						db_modify($qtxt,__FILE__ . " linje " . __LINE__);
					}
				}
				$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='table_plan'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "CREATE TABLE table_plan (id serial NOT NULL, height int, width int, posx int, posy int, ";
					$qtxt.= "name varchar(25), tooltip varchar(40), type varchar(25), pageid int, PRIMARY KEY (id))";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='table_pages'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "CREATE TABLE table_pages (id serial NOT NULL, name varchar(40), PRIMARY KEY (id))";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT id FROM table_pages where name = 'Restaurant'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "insert into table_pages (name) values ('Restaurant')";
				}
				$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='settings' and column_name = 'group_id'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE settings ADD COLUMN group_id int";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='pos_buttons' and column_name = 'fontcolor'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					$qtxt = "ALTER TABLE pos_buttons ADD COLUMN fontcolor character varying(6)";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
				$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='settings' and column_name='pos_id'";
				if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
					db_modify("ALTER TABLE settings ADD column pos_id int",__FILE__ . " linje " . __LINE__);
				}
#				$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='vibrant_terms'";
#				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
#					$qtxt = "	";
#					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
#				}
				$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='loen' and column_name='mentor'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					db_modify("ALTER TABLE loen ADD column mentor text",__FILE__ . " linje " . __LINE__);
				}
				$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='loen' and column_name='mentor_rate'";
				if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
					db_modify("ALTER TABLE loen ADD column mentor_rate numeric(15,3)",__FILE__ . " linje " . __LINE__);
				}
				db_modify("delete from lagerstatus where beholdning is NULL",__FILE__ . " linje " . __LINE__);
			$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				transaktion('commit');
			include("../includes/connect.php");
			$qtxt="UPDATE regnskab set version = '$nextver' where db = '$db'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
	}
	}
}
?>
