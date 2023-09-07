<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --------------systemdata/brugere.php------------- lap 4.0.8 -- 2022-03-16 --
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
// Copyright (c) 2003 - 2022 saldi.dk aps
// ----------------------------------------------------------------------
// 20150327 CA  Topmenudesign tilføjet                             søg 20150327
// 20161104	PHR	Ændret kryptering af adgangskode
// 20181220 MSC - Rettet isset fejl
// 20190221 MSC - Rettet topmenu design
// 20190225 MSC - Rettet topmenu design
// 20190321 PHR Added 'read only' attribut at 'varekort'
// 20190415 PHR Corrected an error in module order printet on screen, resulting in wrong rights to certain modules
// 20200709 PHR Various changes in variable names and user deletion.
// 20230316 PHR Replaced *1 by (int)

@session_start();
$s_id=session_id();

$modulnr=1;
$title="Brugere";
$css="../css/standard.css";

$employeeId=$rights=$roRights=array();

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");

if (!isset ($colbg)) $colbg = NULL;
$modules=array('kontoplan','indstillinger','kassekladde','regnskab','finansrapport','debitorordre','debitorkonti','kreditorordre','kreditorkonti','varer','enheder','backup','debitorrapport','kreditorrapport','produktionsordre','varerapport');


if ($menu=='T') {  # 20150327 start
        include_once '../includes/top_header.php';
        include_once '../includes/top_menu.php';
        print "<div id=\"header\">\n";
        print "<div class=\"headerbtnLft\"></div>\n";
        print "</div><!-- end of header -->";
        print "<div id=\"leftmenuholder\">";
        include_once 'left_menu.php';
        print "</div><!-- end of leftmenuholder -->\n";
		print "<div class=\"maincontentLargeHolder\">\n";
        print "<table border=\"1\" cellspacing=\"0\" id=\"dataTable\" class=\"dataTable\"><tbody>";
} else {
	include("top.php");
	print "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" align=\"center\"><tbody>"; 
}  # 20150327 stop

$addUser=if_isset($_POST['addUser']);
$deleteUser=if_isset($_POST['deleteUser']);
$id=if_isset($_POST['id']);
$updateUser=if_isset($_POST['updateUser']);
$ret_id=if_isset($_GET['ret_id']);
$slet_id=if_isset($_GET['slet_id']);
if ($addUser || $updateUser) {
	$tmp=if_isset($_POST['random']);
	$brugernavn=trim(if_isset($_POST[$tmp]));
	$kode=trim(if_isset($_POST['kode']));
	$kode2=trim(if_isset($_POST['kode2']));
	$medarbejder=trim(if_isset($_POST['medarbejder']));
	$employeeId=if_isset($_POST['employeeId']);
	$rights=$_POST['rights'];
	$roRights = if_isset($_POST['roRights'],NULL);
	$rettigheder=NULL;
	for ($x=0;$x<16;$x++) {
		if (!isset($rights[$x])) $rights[$x]=NULL;
		if (!isset($roRights[$x])) $roRights[$x]=NULL;
		if ($roRights[$x]=='on') $rettigheder.='2';
		elseif ($rights[$x]=='on') $rettigheder.='1';
		else $rettigheder.='0';
	}
	$brugernavn=trim($brugernavn);
	if ($kode && $kode != $kode2) {
			$alerttext="Adgangskoder er ikke ens";
			print "<BODY onLoad=\"javascript:alert('$alerttext')\">";
			$kode=NULL;
			$ret_id=$id;
	}
	$tmp=substr($medarbejder,0,1);
	$employeeId[0]=(int)$employeeId[0];
	if ($addUser && $brugernavn) {
		$query = db_select("select id from brugere where brugernavn = '$brugernavn'",__FILE__ . " linje " . __LINE__);
		if ($row = db_fetch_array($query)) {
			$alerttext="Der findes allerede en bruger med brugenavn: $brugernavn!";
			print "<BODY onLoad=\"javascript:alert('$alerttext')\">";
#			print "<tr><td align=center>Der findes allerede en bruger med brugenavn: $brugernavn!</td></tr>";
		}	else {
			if (!$regnaar) $regnaar=1;
			$qtxt = "insert into brugere (brugernavn,kode,rettigheder,regnskabsaar,ansat_id) ";
			$qtxt.= "values ('$brugernavn','$kode','$rettigheder','$regnaar',$employeeId[0])";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
			$qtxt="select id from brugere where brugernavn = '$brugernavn' and kode = '$kode'";
			$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
			$id=$r['id'];
		}
	}
	if ($id && $kode && $brugernavn) {
		if (strstr($kode,'**********')) {
			db_modify("update brugere set brugernavn='$brugernavn', rettigheder='$rettigheder', ansat_id=$employeeId[0] where id=$id",__FILE__ . " linje " . __LINE__);
		} else {
			$kode=saldikrypt($id,$kode);
			db_modify("update brugere set brugernavn='$brugernavn', kode='$kode', rettigheder='$rettigheder', ansat_id=$employeeId[0] where id=$id",__FILE__ . " linje " . __LINE__);
	}
	}
} elseif (($deleteUser)) {
	$qtxt="select ansat_id from brugere where id ='$id'";
	$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	if ($r['ansat_id']) { 
		$qtxt="update ansatte set lukket='on', slutdate='".date("Y-m-d")."' where id = '$r[ansat_id]'";
		db_modify($qtxt,__FiLE__ . " linje " . __LINE__);
	}
	db_modify("delete from brugere where id = $id",__FILE__ . " linje " . __LINE__);
}

print "<tr><td valign = 'top'>";
print "<table border=0><tbody><tr><td>"; # 20150327
print "<form name='bruger' action='brugere.php' method='post'>";
print "<table cellpadding='0' cellspacing='0' border='0' width='70%'><tbody>"; #B

print "<tr><td colspan='2'></td>";
print str_repeat("<td align='center' width='8px'><br></td>", 30);
print "</tr>";
$modules=array('Sikkerhedskopi','Debitorrapport','Varemodtagelse','Kreditorrapport','Varelager','Produktionsordrer','Kreditorkonti','Varerapport','Kreditorordrer','Debitorkonti','Debitorordrer','Finansrapport','Regnskab','Kassekladde','Indstillinger','Kontoplan');

$cs=14;
for ($x=0;$x<count($modules);$x++) {
print "<tr><td colspan = '$cs' align='right'> $modules[$x] &nbsp;</td>";
	if ($x <= 6) {
		print str_repeat("<td align='center'>|</td>",$x);
		$x++;
		print "<td colspan = '$cs' align='left'> &nbsp;$modules[$x]</td></tr>";
	} 
	else {
		print str_repeat("<td align='center'>|</td>",$x);
	}
	$cs--;
}
print "<tr><td colspan = $cs align='right'> &nbsp;</td>"; print str_repeat("<td align=center>|</td>", $x); 
print "<td colspan=9></td></tr>";

print "<tr><td><b>Navn &nbsp;</b></td><td><b>Brugernavn</b></td></tr>";
$query = db_select("select * from brugere order by brugernavn",__FILE__ . " linje " . __LINE__);
while ($row = db_fetch_array($query)) {
	if ($row['id']!=$ret_id) {
		if ($row['ansat_id']) {
			$r2 = db_fetch_array(db_select("select initialer from ansatte where id = $row[ansat_id]",__FILE__ . " linje " . __LINE__));
		}	else {$r2['initialer']='';}
		print "<tr><td> $r2[initialer]&nbsp;</td><td><a href=brugere.php?ret_id=$row[id]>";
		($row['brugernavn'])?print $row['brugernavn']:print '?';
		print "</a></td>";
		for ($y=0; $y<=15; $y++) {
			($colbg!=$bgcolor)?$colbg=$bgcolor:$colbg=$bgcolor5;
			if ((substr($row['rettigheder'],$y,1)==2)) $color='yellow';
			elseif ((substr($row['rettigheder'],$y,1)==1)) $color='green';
			else $color='red';
			print "<td align='center' bgcolor='$colbg'><span style=\"color:$color;\"><big>*</big></span></td>";
		}
		print "</tr>";
	}
}
if ($ret_id) {
	$query = db_select("select * from brugere where id = $ret_id",__FILE__ . " linje " . __LINE__);
	$row = db_fetch_array($query);
	$userName=$row['brugernavn'];
	print "<tr><td></td>";
	print "<input type=hidden name=id value=$row[id]>";
	$tmp="navn".rand(100,999);				#For at undgaa at browseren "husker" et forkert brugernavn.
	print "<input type=hidden name=random value=$tmp>";	#For at undgaa at browseren "husker" et forkert brugernavn.
	print "<td><input class='inputbox' type='text' size=20 name='$tmp' value=\"$row[brugernavn]\"></td>";
	print "</tr><tr><td></td><td>Adgang til</td>\n";
	for ($x=0;$x<16;$x++) {
		(substr($row['rettigheder'],$x,1)>=1)?$checked='checked':$checked=NULL;
		print "<td><input class='inputbox' type='checkbox' name=\"rights[$x]\" $checked>\n</td>";
	}
	print "</tr><tr><td></td><td>Kun se</td>";
	for ($x=0;$x<16;$x++) {
		(substr($row['rettigheder'],$x,1)==2)?$checked='checked':$checked=NULL;
		print "<td>";
		if ($x==9) print "<input class='inputbox' type='checkbox' name=\"roRights[$x]\" $checked>\n";
		else {
			print "<input disabled='disabled' class='inputbox' type='checkbox' name='roRights[$x]'>\n";
#			print "<input type=hidden name='roRights[$x]' value=''>\n";
		}
		print "</td>";
	}
	print "</tr>";
	print "<tr><td>Adgangskode</td><td><input class=\"inputbox\" type=password size=20 name=kode value='********************'></td></tr>";
	print "<tr><td>Gentag kode</td><td><input class=\"inputbox\" type=password size=20 name=kode2 value='********************'></td></tr>";
	$x=0;
	if ($r2 = db_fetch_array(db_select("select id from adresser where art = 'S'",__FILE__ . " linje " . __LINE__))) {
		$employeeId=array();
		$q2 = db_select("select * from ansatte where konto_id = $r2[id]  and lukket!='on' order by initialer",__FILE__ . " linje " . __LINE__);
		while ($r2 = db_fetch_array($q2)) {
			$x++;
			$employeeId[$x]=$r2['id'];
			$employeeInitials[$x]=$r2['initialer'];
			if ($employeeId[$x]==$row['ansat_id']) {
				$employeeId[0]=$employeeId[$x];
				$employeeInitials[0]=$employeeInitials[$x];
			}		 
#			print "<input type = hidden name=employeeId[$x] value=$employeeId[$x]>";
		}
	}
	$ansat_antal=$x;
	print "<tr><td> Medarbejder</td>";
	print "<td><SELECT NAME=employeeId[0]>";
	print "<option value=\"$employeeId[0]\">$employeeInitials[0]</option>";
	for ($x=1; $x<=$ansat_antal; $x++) { 
		print "<option value=\"$employeeId[$x]\">$employeeInitials[$x]</option>";
	} 
	if ($medarbejder) print "<option></option>";
	print "</SELECT></td></tr>";
	print "</tbody></table></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<tr><td><br></td></tr>";
	if ($menu=='T') {
		$class = "class='button blue medium'";
	} else {
		$class = "class='inputbox'";
	}
	print "<td colspan='12' align = 'center'>";
	print "<input style='width:100px;background-color:44ff44;' type=submit value=\"Opdat&eacute;r\" name=\"updateUser\">&nbsp;";
	print "<input style='width:100px;background-color:ff4444;' type=submit value=\"Slet\" name=\"deleteUser\" onclick=\"confirm('Slet $userName?')\"></td>";
} else {
	$tmp="navn".rand(100,999);				#For at undgaa at browseren "husker" et forkert brugernavn.
	print "<input type=hidden name=random value = $tmp>";
	print "<tr><td> Ny&nbsp;bruger</td>";
	print "<td><input class=\"inputbox\" type=\"text\" size='20' name='$tmp'></td>";
	print "</tr><tr><td></td><td>Adgang</td>";
	for ($x=0;$x<16;$x++) {
		print "<td><input class='inputbox' type='checkbox' name=\"rights[$x]\"></td>\n";
	}
	print "</tr><tr><td></td><td>Kun se</td>";
	for ($x=0;$x<16;$x++) {
		print "<td>";
		if ($x==9) print "<input class='inputbox' type='checkbox' name='roRights[$x]'>\n";
		else {
			print "<input disabled='disabled' class='inputbox' type='checkbox' name='roRights[$x]'>\n";
		}
		print "</td>";
	}
	print "</tr>";
	print "<tr><td> Adgangskode</td><td><input class=\"inputbox\" type=password size=20 name=kode></td></tr>";
	print "<tr><td> Gentag kode</td><td><input class=\"inputbox\" type=password size=20 name=kode2></td></tr>";
	print "</tbody></table></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<td colspan=12 align = center>";
	print "<input style='width:200px;background-color:#aaaaff;' type=submit value=\"Tilf&oslash;j\" name=\"addUser\"></td>";
}
print "</tr>";
# print "</tbody></table></td></tr>";

?>
</tbody>
</table>
</td></tr>
</tbody></table>
<?php if ($menu=='T') print "</div> <!-- end of maincontent -->\n";  # 20150327 ?>
</body></html>
