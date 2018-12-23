<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ------ includes/opdat_3.7.php-------lap 3.7.2 ------2018-11-26---------------
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af "The Free Software Foundation", enten i version 2
// af denne licens eller en senere version, efter eget valg.
// Fra og med version 3.2.2 dog under iagttagelse af følgende:
// 
// Programmet må ikke uden forudgående skriftlig aftale anvendes
// i konkurrence med saldi.dk ApS eller anden rettighedshaver til programmet.
//
// Dette program er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
//
// En dansk oversaettelse af licensen kan laeses her:
// http://www.saldi.dk/dok/GNU_GPL_v2.html
//
// Copyright (c) 2003-2018 saldi.dk ApS
// ----------------------------------------------------------------------
//

function opdat_3_7($under_nr, $lap_nr){
	global $version;
	global $db;
	global $db_id;
	global $regnskab;
	global $regnaar;
	global $db_type;
	$s_id=session_id();

	$nextver='3.7.1';
	if ($lap_nr<"1"){
		include("../includes/connect.php");
		$r=db_fetch_array(db_select("select * from regnskab where id='1'",__FILE__ . " linje " . __LINE__));
		$tmp=$r['version'];
		if ($tmp<$nextver) {
			echo "opdaterer hovedregnskab til ver $nextver<br />";
			$qtxt="UPDATE regnskab set version = '$nextver' where id = '1'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
		include("../includes/online.php");
		if ($db!=$sqdb){
			$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
		include("../includes/connect.php");
		$qtxt="UPDATE regnskab set version = '$nextver' where db = '$db'";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	}
	$nextver='3.7.2';
	if ($lap_nr<"2"){
		global $exec_path,$timezone;
		include("../includes/connect.php");
		$r=db_fetch_array(db_select("select * from regnskab where id='1'",__FILE__ . " linje " . __LINE__));
		$tmp=$r['version'];
		if ($tmp<$nextver) {
			$qtxt="CREATE TABLE settings (id serial NOT NULL, var_name text, var_value text, var_description text, PRIMARY KEY (id))";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			if (!isset($timezone)) $timezone='Europe/Copenhagen';
			$qtxt="insert into settings(var_name,var_value,var_description) values ('timezone','$timezone','')";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			echo "opdaterer hovedregnskab til ver $nextver<br />";
			$qtxt="UPDATE regnskab set version = '$nextver' where id = '1'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
		include("../includes/online.php");
		if ($db!=$sqdb){
#			include ("../includes/settings.php");
			transaktion('begin');
			$qtxt="CREATE TABLE settings ";
			$qtxt.="(id serial NOT NULL, var_name text, var_grp text, var_value text, var_description text, user_id integer, PRIMARY KEY (id))";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$fp=fopen("../includes/settings.php","r");
			$log=fopen("../temp/$db/opdat.log","w");
			while ($line=fgets($fp)) {
				$tmp=trim($line);
				if ($tmp && substr($tmp,0,1)=='$') {
					$tmp=substr($tmp,1);
					fwrite ($log,"$tmp\n");
					list ($name,$tmp)=explode("=",$tmp,2);
					fwrite ($log,"$name -> $tmp\n");
					$qu=0;
					$sq=0;
					$value='';
					$description='';
					for ($x=0;$x<strlen($tmp);$x++) {
						if (!$qu && substr($tmp,$x,1)=='"') $qu++;
						elseif ($qu == 1 && substr($tmp,$x-1,1) != '\\' && substr($tmp,$x,1) == '"') $qu++;
						elseif ($qu == 1) $value.=substr($tmp,$x,1); 
						if ($qu > 1) {
							if (!$sq &&  substr($tmp,$x,1)=='#') $sq++;
							elseif ($sq) $description.=substr($tmp,$x,1);
						}
					}
					fwrite ($log,"$name -> $value\n");
					$name=db_escape_string($name);
					$value=db_escape_string($value);
					fwrite ($log,"$name -> $value\n");
					$qtxt="insert into settings(var_name,var_grp,var_value,var_description,user_id) values ('$name','globals','$value','$description','0')";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
			}
			fclose($fp);
			fclose ($log);
			$qtxt="UPDATE grupper set box1='$nextver' where art = 'VE'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			transaktion('commit');
		}	
		include("../includes/connect.php");
		$qtxt="UPDATE regnskab set version = '$nextver' where db = '$db'";
		db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	}
}
?>
