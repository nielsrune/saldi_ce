<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- admin/slet_regnskab.php --- patch 4.1.1 --- 2024.12.05---
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
// Copyright (c) 2003-2024 saldi.dk aps
// ----------------------------------------------------------------------
// 2020.02.27 PHR Check if db exist before dropping 20200227 
// 20241205 PHR Cleanup.

@session_start();
$s_id=session_id();

$title="Slet regnskaber";
$css="../css/standard.css";

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
if ($db != $sqdb) {
	print "<BODY onLoad=\"javascript:alert('Hmm du har vist ikke noget at g&oslash;re her! Dit IP nummer, brugernavn og regnskab er registreret!')\">";
	print "<meta http-equiv=\"refresh\" content=\"1;URL=../index/logud.php\">";
	exit;
}
$modulnr=103;

?>
<script LANGUAGE="JavaScript">
<!--
function Slet_Regnskab()
{
 var agree=confirm("Slet de valgte regnskaber?"); 
	if (agree)
		return true ;
	else
    return false ;
}
// -->
</script>
<?php
		
if (!$font) $font="Helvetica, Arial, sans-serif";
if (!$top_bund) $top_bund="style=\"border: 1px solid rgb(0, 0, 0); padding: 0pt 0pt 1px;\" align=\"center\" background=\"../img/knap_bg.gif\";";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><title>Slet regnskab</title><meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0"><tbody>
	<tr><td align="center" valign="top" height="25">
		<table width="100%" align="center" border="0" cellspacing="2" cellpadding="0"><tbody>
			<td width="10%" <?php echo $top_bund ?>><a href=../index/admin_menu.php accesskey=L>Luk</a></td>
			<td width="80%" <?php echo $top_bund ?> align="center">Slet regnskab</td>
			<td width="10%" <?php echo $top_bund ?> align = "right"><br></td>
		</tbody></table>
	</td></tr>
<td align = center valign = center>
<table cellpadding="1" cellspacing="1" border="0"><tbody>
<?php
$id=array();$db_navn=array();$regnskab=array();$slet=array();
if (isset($_POST['regnskabsantal']) && $_POST['regnskabsantal']) {
	$regnskabsantal=if_isset($_POST['regnskabsantal'],0);
	$id       = if_isset($_POST['id'],array());
	$db_navn  = if_isset($_POST['db_navn'],array());
	$regnskab = if_isset($_POST['regnskab'],array());
	$slet     = if_isset($_POST['slet'],array());

	if (count($db_navn)) {
		$slet_antal=0;
		for ($x=1; $x<=count($db_navn); $x++) {
			if (isset($slet[$x]) && $slet[$x]=='on'){
			 	$slet_antal++;
				$mappe='../nedlagte_regnskaber/';
				$tmpmappe='../nedlagte_regnskaber/'.$db_navn[$x];
				if (!file_exists($mappe)) mkdir("$mappe", 0777);
				if (!file_exists($tmpmappe)) mkdir("$tmpmappe", 0777);
				if (file_exists($tmpmappe)) {
					if (file_exists("../logolib/logo_".$id[$x].".eps")) $logofil = "../logolib/logo_".$id[$x].".eps";
					if (file_exists("../logolib/$id[$x]/bg.pdf")) $bg = "../logolib/$id[$x]/bg.pdf";
					$dump_filnavn=$tmpmappe."/".trim($db_navn[$x].".sql");
					$info_filnavn=$tmpmappe."/backup.info";
					$tgz_filnavn=trim($db_navn[$x]."_".date("Ymd-Hi")).".tgz";
					$dat_filnavn=trim($db_navn[$x]."_".date("Ymd-Hi")).".sdat";
					$tidspkt= date("d-m-Y H:i");
					$infotekst="$regnskab[$x] slettet $tidspkt af $brugernavn";$fp=fopen($info_filnavn,"w");
					$fp=fopen($info_filnavn,"w");
					if ($fp) {
						$string = date("YmdHis").chr(9)."$db_navn[$x]".chr(9)."$db_ver".chr(9)."$regnskab[$x]".chr(9);
						$string.= "$charset".chr(9)."$db_type".chr(9)."$infotekst";
						fwrite($fp,$string."\n");					
					fclose($fp);
					}
					if ($db_type=='mysql') system ("mysqldump -h $sqhost -u $squser --password=$sqpass -n $db_navn[$x] > $dump_filnavn");
					else system ("export PGPASSWORD=$sqpass\npg_dump -h $sqhost -U $squser -f $dump_filnavn $db_navn[$x]");
#					system("export PGPASSWORD=$sqpass\npg_dump -h $sqhost -U $squser -f $dump_filnavn $db_navn[$x]");
					system ("cd $mappe\ntar -pzcf $tgz_filnavn $db_navn[$x]\nmv $tgz_filnavn $dat_filnavn\nrm -r $tmpmappe");
					if (file_exists("$mappe/$dat_filnavn")) {
# 						print "Sletter regnskab: $regnskab[$x]<br>";
						
					if ($r=db_fetch_array(db_select("select id from kundedata where regnskab_id='$id[$x]'",__FILE__ . " linje " . __LINE__))) {
							$qtxt="update kundedata set slettet='on' where id='$r[id]'";
							db_modify($qtxt,__FILE__ . " linje " . __LINE__); 
						} else {
							$qtxt="update kundedata set slettet='on',regnskab_id='$id[$x]' where regnskab='".db_escape_string($regnskab[$x])."'";
							db_modify($qtxt,__FILE__ . " linje " . __LINE__); 
						}
						$qtxt="delete from regnskab where id = $id[$x]";
						db_modify($qtxt,__FILE__ . " linje " . __LINE__);
						$qtxt="DROP DATABASE IF EXISTS $db_navn[$x]";
						db_modify($qtxt,__FILE__ . " linje " . __LINE__);
						$slettet_regnskab=$regnskab[$x];
					} else "print Backupfejl - $regnskab[$x] ikke slettet";
				}
			}	
		}
		if ($slet_antal==1)	print "<BODY onLoad=\"javascript:alert('$slettet_regnskab slettet')\">";
		else print "<BODY onLoad=\"javascript:alert('$slet_antal regnskaber slettet')\">";
		}
}
$q = db_select("select * from brugere where brugernavn = '$brugernavn'",__FILE__ . " linje " . __LINE__);
$r = db_fetch_array($q);
list($admin,$oprette,$slette,$tmp)=explode(",",$r['rettigheder'],4);
$adgang_til=explode(",",$tmp);

$x=0;
$q1= db_select("select id, regnskab, db from regnskab where db != '$sqdb' and lukket='on' order by id",__FILE__ . " linje " . __LINE__);
while ($r1=db_fetch_array($q1)) {
	if ($admin || in_array($r1['id'],$adgang_til)) {
		$x++;
		$id[$x]=$r1['id'];
		$regnskab[$x]=$r1['regnskab'];	
		$db_navn[$x]=$r1['db'];
	}
}
$regnskabsantal=$x;

print "<tr><td colspan=3>F&oslash;lgende regnskaber er markeret som lukket</td></tr>";
print "<form name=slet_regnskab action=slet_regnskab.php method=post>";
for ($x=1; $x<=$regnskabsantal; $x++) {
	print "<tr>";
#	print "<td>X $x</td>";
	print "<input type=\"hidden\" name=id[$x] value=\"$id[$x]\">";
	print "<input type=\"hidden\" name=db_navn[$x] value=\"$db_navn[$x]\">";
	print "<input type=\"hidden\" name=regnskab[$x] value=\"$regnskab[$x]\">";
	print "<td>$id[$x]</td><td>$regnskab[$x]</td>";
	print "<td><input type=checkbox name=slet[$x]</td>";
	print "</tr>";
}
print "<input type=\"hidden\" name=\"regnskabsantal\" value=\"$regnskabsantal\">";
print "<tr><td colspan=2 align=center><hr></td></tr>\n";
print "<tr><td colspan=2 align=center><input type=submit accesskey=\"a\" value=\"OK\" name=\"submit\" OnClick=\"return Slet_Regnskab()\"></td></tr>\n";
print "</form>";
?>
</tbody></table>
</body></html>
