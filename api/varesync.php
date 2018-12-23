<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ------------- api/varesync.php ---------- lap 3.7.0----2018.04.24-------
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af The Free Software Foundation; enten i version 2
// af denne licens eller en senere version efter eget valg.
// Fra og med version 3.2.2 dog under iagttagelse af følgende:
// 
// Programmet må ikke uden forudgående skriftlig aftale anvendes
// i konkurrence med saldi.dk aps eller anden rettighedshaver til programmet.
// 
// Programmet er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
// 
// En dansk oversaettelse af licensen kan laeses her:
// http://www.saldi.dk/dok/GNU_GPL_v2.html
//
// Copyright (c) 2003-2018 saldi.dk aps
// ----------------------------------------------------------------------
// 
// 2018.04.24 Omskrevet variant delen så det inditificeres på variant_id i stedet for på stregkode så det er muligt at ændre stregkode på shop.

/*
@session_start();
$s_id=session_id();

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/ordrefunc.php");
*/
function varesync($valg) {
	global $db;

	db_modify("update shop_varer set saldi_variant='0' where saldi_variant is NULL",__FILE__ . " linje " . __LINE__);
	db_modify("update shop_varer set shop_variant='0' where shop_variant is NULL",__FILE__ . " linje " . __LINE__);
	$x=0;
	$qtxt="select * from shop_varer order by saldi_id,shop_id,saldi_variant,shop_variant";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while($r=db_fetch_array($q)) {
		if ($x) {
			if ($r['saldi_id']==$a && $r['shop_id']==$b && $r['saldi_variant']==$c && $r['shop_variant']==$d) {
#cho "sletter $r[id]<br>"; 	
				db_modify("delete from shop_varer where id = '$r[id]'",__FILE__ . " linje " . __LINE__);
			}
		}
		$a=$r['saldi_id'];$b=$r['shop_id'];$c=$r['saldi_variant'];$d=$r['shop_variant'];
		$x++;
	}
	
	
	if ($valg==1) {
		Print "Henter varer<br>";
		$qtxt="select max(shop_id) as shop_id from shop_varer";
		$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$next_id=$r['shop_id']+1;
	} else {
		Print "Opdaterer varer<br>";
		$next_id=1;
	}
	$qtxt="select box4 from grupper where art='API'";
#cho "$qtxt<br>";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	$api_fil=trim($r['box4']);
	$tmparray=explode("/",$api_fil);
	$lagerfil='';
	for ($x=0;$x<count($tmparray)-1;$x++) {
		$lagerfil.=$tmparray[$x]."/";
	}
	$lf=$lagerfil."files/shop_products.csv";
	$header="User-Agent: Mozilla/5.0 Gecko/20100101 Firefox/23.0";
#cho "$api_fil?products_id=*<br>";
	system ("/usr/bin/wget --no-check-certificate --spider --header='$header' $api_fil?products_id=* &\n");
	system ("cd ../temp/$db/\nwget --no-check-certificate --header='$header' $lf\n");
	$indhold=file_get_contents("../temp/$db/shop_products.csv");
	unlink("../temp/$db/shop_products.csv");
	$linje=explode("\n",$indhold);
	$shop_encode='';
	for ($y=0;$y<count($linje);$y++){
		if ($y==0) {
			$vars=explode(";",$linje[$y]);
			$var_antal=count($vars);	
		}
		$shop_id[$y]=0;
		
#		if ($var_antal==5) list($varenr[$y],$stregkode[$y],$salgspris[$y],$beskrivelse[$y],$gruppe[$y])=explode(";",$linje[$y]);
#		else 
		list($shop_id[$y],$varenr[$y],$stregkode[$y],$salgspris[$y],$beskrivelse[$y],$gruppe[$y],$tilbud[$y],$notes[$y])=explode(";",$linje[$y]);
		$shop_id[$y]=trim($shop_id[$y],'"');
		$varenr[$y]=trim($varenr[$y],'"');
		$salgspris[$y]=trim($salgspris[$y],'"');
		$gruppe[$y]=trim($gruppe[$y],'"');
		$beskrivelse[$y]=trim($beskrivelse[$y],'"');
		$stregkode[$y]=trim($stregkode[$y],'"');
		$notes[$y]=trim($notes[$y],'"');
		$tilbud[$y]=trim($tilbud[$y],'"');
#		$tilbud[$y]=str_replace('"',$tilbud[$y])*1;
		if (!$shop_id[$y]) {
			if (substr($stregkode[$y],0,3)=='EAN' && is_numeric(substr($stregkode[$y],3))) $shop_id[$y]=substr($stregkode[$y],3);
			#elseif (is_numeric($varenr[$y])) $shop_id[$y]=$varenr[$y];
		}
		if (!$shop_encode) {
			$tmp=$beskrivelse[$y];
			if (strpos($tmp,'æ') || strpos($tmp,'ø')  || strpos($tmp,'å')) $shop_encode='utf8';
			elseif (strpos($tmp,'Æ') || strpos($tmp,'Ø')  || strpos($beskrivelse[$y],'Å')) $shop_encode='utf8';
			else {
				($tmp=utf8_encode($beskrivelse[$y]));
				if (strpos($tmp,'æ') || strpos($tmp,'ø')  || strpos($tmp,'å')) $shop_encode='iso-8859';
				elseif (strpos($tmp,'Æ') || strpos($tmp,'Ø')  || strpos($tmp,'Å')) $shop_encode='iso-8859';
			}
		}
	}
	transaktion('begin');
	for ($y=0;$y<count($linje);$y++) {
		if ($shop_encode=='iso-8859') {
			$beskrivelse[$y]=utf8_encode($beskrivelse[$y]);
			$varenr[$y]=utf8_encode($varenr[$y]);
		}
		$beskrivelse[$y]=db_escape_string($beskrivelse[$y]);
		$varenr[$y]=db_escape_string($varenr[$y]);
		$qtxt="select id from varer where varenr='$varenr[$y]'";
		if (strlen($stregkode[$y])>5) $qtxt.=" or stregkode='$stregkode[$y]'";
		$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$vare_id[$y]=$r['id'];
		if (!$vare_id[$y] && $shop_id[$y] && is_numeric($shop_id[$y])) {
			$qtxt="select saldi_id from shop_varer where shop_id='$shop_id[$y]' and saldi_variant='0'";
#cho __line__." ".$qtxt."<br>";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			if ($r['saldi_id']) $vare_id[$y]=$r['saldi_id'];
		}
#cho __line__." Valg $valg Vare_id $vare_id[$y] && $shop_id[$y] S $salgspris[$y] G $gruppe[$y]<br>";
		if ($vare_id[$y] && $varenr[$y]) {
			if ($valg=='2') {
				$qtxt="update varer set varenr='$varenr[$y]',beskrivelse='$beskrivelse[$y]',stregkode='$stregkode[$y]',salgspris='$salgspris[$y]',special_price='0' where id = '$vare_id[$y]'";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				if ($tilbud[$y]) {
					$qtxt="update varer set special_price='$tilbud[$y]',special_from_date='2018-01-01',special_to_date='2099-12-31' where id = '$vare_id[$y]'";
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
			}
			$qtxt="select id from shop_varer where saldi_id='$vare_id[$y]'";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			if ($shop_id[$y]) {
				if ($r['id'])	$qtxt="update shop_varer set shop_id='$shop_id[$y]' where id='$r[id]'";
				else $qtxt="insert into shop_varer (saldi_id,shop_id) values ('$vare_id[$y]','$shop_id[$y]')";
#cho __line__." $qtxt<br>";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
		}	elseif ($valg=='1' && is_numeric($salgspris[$y]) && is_numeric($gruppe[$y])) {
			$qtxt="insert into varer (varenr,stregkode,beskrivelse,salgspris,gruppe,beholdning,lukket) values ('$varenr[$y]','$stregkode[$y]','$beskrivelse[$y]','$salgspris[$y]','$gruppe[$y]','0','')";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			if ($shop_id[$y]) {	
				$qtxt="select id from varer where varenr='$varenr[$y]'";
#cho __line__." ".$qtxt."<br>";
				$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
				$vare_id[$y]=$r['id'];
				$qtxt="insert into shop_varer (saldi_id,shop_id,saldi_variant,shop_variant) values ('$vare_id[$y]','$shop_id[$y]','0','0')";
#cho __line__." ".$qtxt."<br>";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
		}  
	}
	transaktion('commit');
#	$qtxt="select saldi_id from shop_varer where shop_id='24020'";
##cho __line__." ".$qtxt."<br>";
#	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
#	#cho __line__." if ($vare_id[$y]=$r[saldi_id])<br>";
#xit;	
	############################# Varianter #########################
	Print "Henter varianter<br>";
	
	$qtxt="SELECT column_name FROM information_schema.columns WHERE table_name='variant_varer' and column_name='variant_kostpris'";
	if (!$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		db_modify("ALTER TABLE variant_varer add column	variant_kostpris numeric(15,3)",__FILE__ . " linje " . __LINE__);
		db_modify("ALTER TABLE variant_varer add column	variant_salgspris numeric(15,3)",__FILE__ . " linje " . __LINE__);
		db_modify("ALTER TABLE variant_varer add column	variant_vejlpris numeric(15,3)",__FILE__ . " linje " . __LINE__);
		db_modify("ALTER TABLE variant_varer add column	variant_id int4",__FILE__ . " linje " . __LINE__);
	}

	$x=0;
	$qtxt="select * from varianter order by id";
#cho __line__." $qtxt<br>";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while($r=db_fetch_array($q)) {
		$var_id[$x]=$r['id'];
		$var_type[$x]=$r['beskrivelse'];
#cho __line__." $x var_id $var_id[$x] -> var_type $var_type[$x]<br>";
		$x++;
	}
	$x=0;
	$qtxt="select * from variant_typer order by variant_id,beskrivelse";
	$q=db_select($qtxt,__FILE__ . " linje " . __LINE__);
	while($r=db_fetch_array($q)) {
		$vt_id[$x]=$r['id'];
		$vt_var_id[$x]=$r['variant_id'];
		$vt_var[$x]=$r['beskrivelse'];
#cho __line__." $x vt_id $vt_id[$x] -> vt_var_id $vt_var_id[$x] -> vt_var $vt_var[$x]<br>";
		$x++;
	}
		$lf=$lagerfil."files/shop_variants.csv";
#cho "LF $lf<br>";
	$header="User-Agent: Mozilla/5.0 Gecko/20100101 Firefox/23.0";
#cho "$api_fil?variant=*<br>";
	$systxt="/usr/bin/wget --no-check-certificate --spider --header='$header' $api_fil?variant=* &\n";
	$result=system ($systxt);
#cho "$result<br>";
	if (file_exists("../temp/$db/shop_variants.csv")) unlink ("../temp/$db/shop_variants.csv");
	$systxt="cd ../temp/$db/\nwget --no-check-certificate --header='$header' $lf\n";
#cho "$systxt<br>";
	$result=system ($systxt);
#cho "$systxt<br>";
	if (!file_exists("../temp/$db/shop_variants.csv")) exit;
	$indhold=file_get_contents("../temp/$db/shop_variants.csv");
#	unlink("../temp/$db/shop_variants.csv");
	$linje=explode("\n",$indhold);
	$shop_encode='';
	for ($y=0;$y<count($linje);$y++){
		list($varenr[$y],$parent_id[$y],$variant_id[$y],$stregkode[$y],$variant[$y],$variant_type[$y],$variant_text[$y])=explode(";",$linje[$y]);
		$parent_id[$y]=trim($parent_id[$y],'"');
		$varenr[$y]=trim($varenr[$y],'"');
		$variant_id[$y]=trim($variant_id[$y],'"');
		$stregkode[$y]=trim($stregkode[$y],'"');
		$variant[$y]=trim($variant[$y],'"');
		$variant_type[$y]=trim($variant_type[$y],'"');
		$variant_text[$y]=trim($variant_text[$y],'"');
		$variant_id[$y]*=1;
		if (!$shop_encode) {
			$tmp=$variant_text[$y];
			if (strpos($tmp,'æ') || strpos($tmp,'ø')  || strpos($tmp,'å')) $shop_encode='utf8';
			elseif (strpos($tmp,'Æ') || strpos($tmp,'Ø')  || strpos($beskrivelse[$y],'Å')) $shop_encode='utf8';
			else ($tmp=utf8_encode($beskrivelse[$y]));
			if (strpos($tmp,'æ') || strpos($tmp,'ø')  || strpos($tmp,'å')) $shop_encode='iso-8859';
			elseif (strpos($tmp,'Æ') || strpos($tmp,'Ø')  || strpos($tmp,'Å')) $shop_encode='iso-8859';
		}
	}
	transaktion('begin');
	$m=0;
	for ($y=1;$y<count($linje);$y++) {
#cho "varisnt id $variant[$y]<br>";
		$parent_id[$y]*=1;
		$saldi_var_id[$y]=0;
		if ($variant_type[$y] && !in_array($variant_type[$y],$var_type) && !in_array($variant_type[$y],$mangler)) {
			echo "<big><b>Varianten \"$variant_type[$y]\" ikke oprettet</b></big><br><br>";
			$mangler[$m]=$variant_type[$y];
			$m++;
		}
		for ($x=0;$x<count($var_id);$x++) {
			if ($variant_type[$y]==$var_type[$x]) {
				$s_var_id[$y]=$var_id[$x];
			}
		}
#cho __line__." $s_var_id[$y]<br>";
		$s_variant[$y]=0;
		if ($parent_id[$y] && $s_var_id[$y]) {
			for ($x=0;$x<count($vt_id);$x++) {
				if ($s_var_id[$y]==$vt_var_id[$x] && $vt_var[$x]==$variant[$y]) {
					$s_variant[$y]=$vt_id[$x];
				}
			}
		}
#cho __line__." $s_variant[$y]<br>";
if ($parent_id[$y] && $variant_id[$y]) {

		#cho "variant id $s_variant[$y]<br>";
		if ($shop_encode=='iso-8859') {
			$variant_text[$y]=utf8_encode($variant_text[$y]);
			$varenr[$y]=utf8_encode($varenr[$y]);
		}
		$variant_text[$y]=db_escape_string($variant_text[$y]);
		$varenr[$y]=db_escape_string($varenr[$y]);
		$qtxt="select saldi_id,saldi_variant from shop_varer where shop_id='$parent_id[$y]' and shop_variant='$variant_id[$y]'";
#cho __line__." $qtxt<br>";
		if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
			$vare_id[$y]=$r['saldi_id'];
			$s_variant_id[$y]=$r['saldi_variant'];
			$qtxt="update variant_varer set variant_stregkode='$stregkode[$y]' where id='$s_variant_id[$y]'";
#cho __line__." ".$qtxt."<br>";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		} else {
			$qtxt="select saldi_id from shop_varer where shop_id='$parent_id[$y]'";
#cho __line__." $qtxt<br>";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$vare_id[$y]=$r['saldi_id'];
			$s_variant_id[$y]=NULL;
		}
#cho __line__." if ($vare_id[$y]=$r[saldi_id])<br>";
		if ($vare_id[$y] && $s_variant_id[$y]) {
			$qtxt="update variant_varer set vare_id='$vare_id[$y]',variant_type=$s_variant[$y] where id='$s_variant_id[$y]'";
#cho __line__." ".$qtxt."<br>";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		} elseif ($vare_id[$y]) {
				$qtxt="select id from variant_varer where variant_stregkode='$stregkode[$y]'";
#cho __line__." ".$qtxt."<br>";
				$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
				if (!$r['id']) {
#					$s_variant_id[$y]=$r['id'];
#					$qtxt="update variant_varer set vare_id='$vare_id[$y]',variant_type=$s_variant[$y] where id='$s_variant_id[$y]'";
#				} else {
					$qtxt="insert into variant_varer";
					$qtxt.="(vare_id,variant_type,variant_beholdning,variant_stregkode,lager,variant_salgspris,variant_kostpris,variant_vejlpris,variant_id)";
					$qtxt.="values ";
					$qtxt.="('$vare_id[$y]','$s_variant[$y]','1','$stregkode[$y]','0','0','0','0','1')";
#cho __line__." $qtxt<br>";				
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				$qtxt="select id from variant_varer where variant_stregkode='$stregkode[$y]'";
				$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
				$s_variant_id[$y]=$r['id'];
				}
				$qtxt="select id from shop_varer where shop_variant='$variant_id[$y]'";
#cho __line__." ".$qtxt."<br>";
				$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
				$qtxt=NULL;
				if ($r['id']) {
					$qtxt="update shop_varer set saldi_variant='$s_variant_id[$y]',shop_variant=$variant_id[$y] where id='$r[id]'";
				} elseif ($vare_id[$y] && $parent_id[$y] && $s_variant_id[$y] && $variant_id[$y]) {
					$qtxt="insert into shop_varer";
					$qtxt.="(saldi_id,shop_id,saldi_variant,shop_variant) ";
					$qtxt.="values ";
					$qtxt.="('$vare_id[$y]','$parent_id[$y]','$s_variant_id[$y]','$variant_id[$y]')";
				}
#cho __line__." ".$qtxt."<br>";
				
				if ($qtxt) {
					db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				$qtxt="update varer set varianter='1' where id=$vare_id[$y]";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				}
/*
			} elseif ($valg=='1') {
				$qtxt="select id from variant_varer where variant_stregkode='$stregkode[$y]'";
				$qtxt="insert into variant_varer";
				$qtxt.="(vare_id,variant_type,variant_beholdning,variant_stregkode,lager,variant_salgspris,variant_kostpris,variant_vejlpris) ";
				$qtxt.="values ";
				$qtxt.="('$vare_id[$y]','$s_variant[$y]','0','$stregkode[$y]','0','0','0','0')";
#cho __line__." ".$qtxt."<br>";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			}
*/
		}
	}
	}
	transaktion('commit');
}
?>