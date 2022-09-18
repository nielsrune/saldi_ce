<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- includes/betweenUpdates.php --- rel. 4.0.5 --- 2022-06-19 ---
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
// Copyright (c) 2022 Saldi.dk ApS
// ----------------------------------------------------------------------
// The content of this file must be moved to opdat_4.0 in section 4.0.6 when 4.0.6 is to be released. 
include ('../includes/std_func.php');
include ('../includes/db_query.php');
if (!function_exists('findtekst')) include("../includes/std_func.php");
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
	$qtxt ="CREATE TABLE documents (id serial NOT NULL,global_id int,filename text, filepath text, source varchar(20), source_id int, timestamp varchar(10), user_id int, ";
	$qtxt.="PRIMARY KEY (id) )";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
}
$qtxt="SELECT table_name FROM information_schema.columns WHERE table_name='varetilbud'";
if (!db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
	$qtxt = "CREATE TABLE varetilbud ";
	$qtxt.= "(id serial NOT NULL,vare_id integer,startdag numeric(15,0),slutdag numeric(15,0),starttid time,sluttid time,";
	$qtxt.= "ugedag integer,salgspris numeric(15,2),kostpris numeric(15,2),PRIMARY KEY (id))";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
}
if ($sprog_id == '3' && findtekst(1419,3) == 'Ingen fakturaer er merket for omfakturering!') {
	db_modify ("update tekster set tekst = '' where tekst_id >= '1419'",__FILE__ . " linje " . __LINE__); 
}
if (findtekst(1421,1) == 'Ordre er låst af') {
	db_modify ("update tekster set tekst = '' where tekst_id >= '1421'",__FILE__ . " linje " . __LINE__); 
}
if (findtekst(1524,1) == 'Faktura sendes pr. mail til' || findtekst(1524,3) == 'Faktura sendt pr. post til') {
	db_modify ("update tekster set tekst = '' where tekst_id >= '1523' and  tekst_id <= '1527'",__FILE__ . " linje " . __LINE__); 
}
if (findtekst(1903,1) == 'Gebruikers IP herstellen') {
	db_modify ("update tekster set tekst = '' where tekst_id >= '1903'",__FILE__ . " linje " . __LINE__); 
}
if (findtekst(1904,1) == 'IP adres invoeren om te beperken') {
	db_modify ("update tekster set tekst = '' where tekst_id >= '1904'",__FILE__ . " linje " . __LINE__); 
}
if (findtekst(1001,1) == 'Kreditér') {
	db_modify ("update tekster set tekst = '' where tekst_id >= '1001'",__FILE__ . " linje " . __LINE__); 
}
if (findtekst(989,1) == 'Fakture') {
	db_modify ("update tekster set tekst = '' where tekst_id >= '989'",__FILE__ . " linje " . __LINE__); 
}
if (substr(findtekst(1490,1),0,2) == '"I') {
	db_modify ("update tekster set tekst = '' where tekst_id >= '1490'",__FILE__ . " linje " . __LINE__); 
}
/*

$qtxt = "SELECT table_name FROM information_schema.columns WHERE table_name='pbs_kunder' and column_name = 'ktonr'";
if (db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
	$qtxt = "ALTER TABLE pbs_kunder DROP COLUMN ktonr";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
}
transaktion('begin');
$qtxt = "SELECT table_name FROM information_schema.columns WHERE table_name='pbs_kunder' and ";
$qtxt.= "column_name = 'kontonr' and (data_type = 'character varying' or data_type = 'integer')";
if (db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
	if (!$db_type || $db_type == "postgresql") $qtxt = "ALTER TABLE pbs_kunder ALTER COLUMN kontonr TYPE bigint USING (kontonr::numeric(20))";
	else $qtxt = "ALTER TABLE pbs_kunder MODIFY kontonr bigint";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
}
transaktion('commit');
transaktion('begin');
$qtxt = "SELECT table_name FROM information_schema.columns WHERE table_name='adresser' and ";
$qtxt.= "column_name = 'kontonr' and (data_type = 'character varying' or data_type = 'integer')";
if (db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
	if (!$db_type || $db_type == "postgresql") $qtxt = "ALTER TABLE adresser ALTER COLUMN kontonr TYPE bigint USING (kontonr::numeric(20))";
	else $qtxt = "ALTER TABLE adresser MODIFY kontonr bigint";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
}
transaktion('commit');
transaktion('begin');
$qtxt = "SELECT table_name FROM information_schema.columns WHERE table_name='ordrer' and ";
$qtxt.= "column_name = 'kontonr' and (data_type = 'text')";
if (db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))){
	$qtxt  = "UPDATE ordrer SET kontonr = '0' WHERE kontonr = '' OR kontonr IS NULL";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	if (!$db_type || $db_type == "postgresql") $qtxt = "ALTER TABLE ordrer ALTER COLUMN kontonr TYPE bigint USING (kontonr::numeric(20))";
	else $qtxt = "ALTER TABLE ordrer MODIFY kontonr bigint";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
}
transaktion('commit');
*/
?>
