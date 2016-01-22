<?php
// ---------systemdata/kontokort.php-----lap 3.2.9----2013-02-10 --------
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af The Free Software Foundation; enten i version 2
// af denne licens eller en senere version efter eget valg
// Fra og med version 3.2.2 dog under iagttagelse af følgende:
// 
// Programmet må ikke uden forudgående skriftlig aftale anvendes
// i konkurrence med DANOSOFT ApS eller anden rettighedshaver til programmet.
//
// Dette program er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
//
// En dansk oversaettelse af licensen kan laeses her:
// http://www.fundanemt.com/gpl_da.html
//
// Copyright (c) 2004-2013 DANOSOFT ApS
// ----------------------------------------------------------------------
//
// 2013.02.10 Break ændret til break 1

@session_start();
$s_id=session_id();
$title="Kontokort";
$css="../css/standard.css";

?>
	<script LANGUAGE="JavaScript">
	<!--
	function confirmSlet()
	{
		var agree=confirm("Slet?");
		if (agree)
       return true ;
		else
       return false ;
		}
	// -->
	</script>
<?php
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/genberegn.php");

$kontotype=NULL; # ellers hentes typen fra connect.php  

$id = if_isset($_GET['id']);
if (isset($_POST['slet'])){
	$id=$_POST['id']*1;
	$kontonr=$_POST['kontonr']*1;
	
	db_modify("delete from kontoplan where id = $id",__FILE__ . " linje " . __LINE__);
	$q = db_select("select id from kontoplan where kontonr >= $kontonr and regnskabsaar = '$regnaar' order by kontonr",__FILE__ . " linje " . __LINE__);
	if ($r=db_fetch_array($q)) $id=$r['id'];
	else $id=0;
} elseif ($_POST['gem']){
	$id=$_POST['id'];
	$kontonr=round($_POST['kontonr'],0);
	$beskrivelse=addslashes($_POST['beskrivelse']);
	$kontotype=if_isset($_POST['kontotype']);
#	$katagori=if_isset($_POST['katagori']);
	$moms=if_isset($_POST['moms']);
	$fra_kto=$_POST['fra_kto']*1;	
	$til_kto=if_isset($_POST['kontonr']);
	$saldo=if_isset($_POST['saldo']);
	$valuta=if_isset($_POST['valuta']);
	$ny_valuta=if_isset($_POST['ny_valuta']);
#cho $ny_valuta."<br>";
	$genvej=if_isset($_POST['genvej']);
	$lukket=if_isset($_POST['lukket']);
	if ($kontotype!='Sum' && $kontotype!='Resultat'){
		$fra_kto=0;
		$til_kto=0;
	}
	if (!$valuta) $valuta='DKK'; 
	if (!$ny_valuta) $ny_valuta='DKK'; 

	if ($ny_valuta != $valuta) {
		$dd=date("Y-m-d");
#cho $dd."<br>$saldo<br>$ny_valuta<br>";
		if ($saldo) {
			if ($valuta=='DKK') {
				$valutakode=0;
				$kurs=100;
			} else {
				$r=db_fetch_array(db_select("select kodenr from grupper where art='VK' and box1 = '$valuta'",__FILE__ . " linje " . __LINE__));
				$valutakode=$r['kodenr'];
				$r=db_fetch_array(db_select("select kurs from valuta where gruppe='$valutakode' and valdate <= '$dd' order by valuta.valdate desc limit 1",__FILE__ . " linje " . __LINE__));
				if ($r['kurs']) {
					$kurs=$r['kurs'];
				}
			}
			if ($ny_valuta=='DKK') {
				$ny_valutakode=0;
				$ny_kurs=100;
			} else {
				$r=db_fetch_array(db_select("select kodenr from grupper where art='VK' and box1 = '$ny_valuta'",__FILE__ . " linje " . __LINE__));
				$ny_valutakode=$r['kodenr']*1;
				$r=db_fetch_array(db_select("select kurs from valuta where gruppe='$ny_valutakode' and valdate <= '$dd' order by valuta.valdate desc limit 1",__FILE__ . " linje " . __LINE__));
				if ($r['kurs']) {
					$ny_kurs=$r['kurs'];
				}
			}
			db_modify("update kontoplan set valuta ='$ny_valutakode', valutakurs='$ny_kurs' where id = '$id'",__FILE__ . " linje " . __LINE__);
		}
	}
	if ($kontotype=='Overskrift'){
		$kontotype='H';
		$moms="";
	} elseif ($kontotype=='Drift') $kontotype='D';
	elseif ($kontotype=='Status') $kontotype='S';
	elseif ($kontotype=='Lukket') $kontotype='L';
	elseif ($kontotype=='Sum'){
		$kontotype='Z';
		$moms="";
	} elseif ($kontotype=='Resultat'){
		$kontotype='R';
		$moms="";
	}
	elseif ($kontotype=='Sideskift') $kontotype='X';
	

	if ($kontonr<1) print "<BODY onLoad=\"javascript:alert('Kontonummer skal v&aelig;re et positivt heltal')\">";
	elseif ($id==0) {
		$query = db_select("select id from kontoplan where kontonr = $kontonr and regnskabsaar = '$regnaar'",__FILE__ . " linje " . __LINE__);
		if ($row = db_fetch_array($query)){
			print "<BODY onLoad=\"javascript:alert('Der findes allerede en konto med nr: $kontonr')\">";
			$id=0;
		}
		else {
			$x=0;
			$query = db_select("select kodenr from grupper where art = 'RA' order by kodenr",__FILE__ . " linje " . __LINE__);
			while ($row = db_fetch_array($query)) {
				if ($row['kodenr']>=$x){$x=$row['kodenr'];}
			}
			for ($y=$regnaar; $y<=$x; $y++) {
				$query = db_select("select id from kontoplan where kontonr = $kontonr and regnskabsaar = '$y'",__FILE__ . " linje " . __LINE__);
				if(!$row = db_fetch_array($query)) {
					db_modify("insert into kontoplan (kontonr, beskrivelse, kontotype, primo, regnskabsaar, genvej) values ($kontonr, '$beskrivelse', '$kontotype', '0', '$y', '$genvej')",__FILE__ . " linje " . __LINE__);
				}
			}
			$query = db_select("select id from kontoplan where kontonr = $kontonr and regnskabsaar = '$regnaar'",__FILE__ . " linje " . __LINE__);
			$row = db_fetch_array($query);
			$id = $row['id'];
		}
	}
	elseif ($id > 0) {
		if (!$fra_kto){$fra_kto=0;}
		if (!$til_kto){$til_kto=0;}
		if ($r=db_fetch_array(db_select("select id from kontoplan where kontonr = $kontonr and regnskabsaar = '$regnaar' and id!='$id'",__FILE__ . " linje " . __LINE__))) {
			print "<BODY onLoad=\"javascript:alert('Der findes allerede en konto med nr: $kontonr')\">";
		} else db_modify("update kontoplan set kontonr = $kontonr, beskrivelse = '$beskrivelse', kontotype = '$kontotype', moms = '$moms', fra_kto = '$fra_kto', til_kto = '$til_kto', genvej='$genvej', lukket = '$lukket' where id = '$id'",__FILE__ . " linje " . __LINE__);
	}
	genberegn($regnaar);
}
if ($id > 0){
	$query = db_select("select * from kontoplan where id = '$id'",__FILE__ . " linje " . __LINE__);
	if ($row = db_fetch_array($query)){
		$id=$row['id'];
		$kontonr=$row['kontonr']*1;
		$beskrivelse=htmlentities(stripslashes($row['beskrivelse']),ENT_COMPAT,$charset);
		$kontotype=$row['kontotype'];
#		$katagori=$row['katagori'];
		$moms=$row['moms'];
		$fra_kto=$row['fra_kto'];
#		$til_kto=$row['til_kto'];
		$genvej=$row['genvej'];
		$lukket=$row['lukket'];
		$saldo=$row['saldo'];
		$valutakode=$row['valuta'];
		$valutakurs=$row['valutakurs'];
		if ($valutakode) {
#cho "select box1 from grupper where art='VK' and kodenr = '$valutakode'<br>";
			$r=db_fetch_array(db_select("select box1 from grupper where art='VK' and kodenr = '$valutakode'",__FILE__ . " linje " . __LINE__));
			$valuta=$r['box1'];
		} else $valuta='DKK';
		$r=db_fetch_array(db_select("select id from kontoplan where kontonr < '$kontonr' order by kontonr desc",__FILE__ . " linje " . __LINE__));
		$forrige=$r['id']*1;	
		$r=db_fetch_array(db_select("select id from kontoplan where kontonr > '$kontonr' order by kontonr",__FILE__ . " linje " . __LINE__));
		$naeste=$r['id']*1;	
	}
	$r=db_fetch_array(db_select("select id from kontoplan where kontotype = 'R' and regnskabsaar = '$regnaar'",__FILE__ . " linje " . __LINE__));
	$resultatkonto=$r['id']*1;

	if (!$kontonr) {
		print "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=kontoplan.php\">";
		exit;
	}
	$query = db_select("select * from grupper where kodenr='$regnaar' and art='RA'",__FILE__ . " linje " . __LINE__);
	$row = db_fetch_array($query);
	$startmaaned=$row['box1']*1;
	$startaar=$row['box2']*1;
	$slutmaaned=$row['box3']*1;
	$slutaar=$row['box4']*1;
	$slutdato=31;

	
	while (!checkdate($slutmaaned,$slutdato,$slutaar)) {
		$slutdato=$slutdato-1;
		if ($slutdato<28) break 1;
	}
	$regnstart = $startaar. "-" . $startmaaned . "-" . '01';
	$regnslut = $slutaar . "-" . $slutmaaned . "-" . $slutdato;

	$query = db_select("select id from transaktioner where transdate>'$regnstart' and transdate<'$regnslut' and kontonr='$kontonr'",__FILE__ . " linje " . __LINE__);
	if ($row = db_fetch_array($query)){$slet=0;}
	else {$slet=1;}

}
if ($kontotype=='H') $kontotype='Overskrift';
elseif($kontotype=='D') $kontotype='Drift';
elseif($kontotype=='S') $kontotype='Status';
elseif($kontotype=='Z') $kontotype='Sum';
elseif($kontotype=='R') $kontotype='Resultat';
elseif($kontotype=='X') $kontotype='Sideskift';

print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>";
print "<tr><td align=\"center\"  height=1% valign=\"top\">";
print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"2\" cellpadding=\"0\"><tbody>";
print "<tr><td width=\"10%\" $top_bund ><a href=kontoplan.php accesskey=L>Luk</a></td>";
print "<td width=\"80%\"  $top_bund >  Kontokort</td>";
print "<td width=\"10%\"  $top_bund > <a href=kontokort.php accesskey=N>Ny</a><br></td></tr>";
if ($forrige) print "<tr><td colspan=2><a href='kontokort.php?id=$forrige'><img src=../ikoner/left.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td>";
else print "<tr><td colspan=2></td>";
if ($naeste) print "<td align=\"right\"><a href='kontokort.php?id=$naeste'><img src=../ikoner/right.png style=\"border: 0px solid; width: 15px; height: 15px;\"></a></td>";
else print "<td></td></tr>";
print "</tbody></table>";
print "</td></tr>";
print "<td align = center valign = center height=99%>";
print "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\"><tbody>";



print "<form name=kontokort action=kontokort.php method=post>";
print "<input type=hidden name=id value='$id'>";
print "<input type=hidden name=valuta value='$valuta'>";
print "<input type=hidden name=saldo value='$saldo'>";
if ($id && $saldo) {
	print "<tr><td><font face=\"Helvetica, Arial, sans-serif\">Kontonr.</td><td><br></td><td colspan=2> $kontonr</td></tr>\n";
	print "<input type=hidden name=kontonr value=\"$kontonr\">";
}
else print "<tr><td><font face=\"Helvetica, Arial, sans-serif\">Kontonr.</td><td><br></td><td colspan=2><input type=text size=6 name=kontonr value=\"$kontonr\"></td></tr>\n";
print "<tr><td><font face=\"Helvetica, Arial, sans-serif\">Kontonavn</td><td><br></td><td colspan=2><input type=text size=25 name=beskrivelse value=\"$beskrivelse\"></td></tr>\n";
print "<tr><td><font face=\"Helvetica, Arial, sans-serif\">Kontotype</td><td><br></td>";
print "<td colspan=2><SELECT NAME=kontotype>";
#print "<td>sa $saldo sl $slet<br></td>";
if ($kontotype) print "<option>$kontotype</option>\n";
if ($saldo) {
	if ($kontotype!='Drift') print "<option>Drift</option>\n";
	if ($kontotype!='Status') print "<option>Status</option>\n";
	if (!$resultatkonto && $kontotype!='Resultat') print "<option>Resultat</option>\n";
} else {
	if ($kontotype!='Overskrift') print "<option>Overskrift</option>\n";
	if ($kontotype!='Drift') print "<option>Drift</option>\n";
	if ($kontotype!='Status') print "<option>Status</option>\n";
	$r=db_fetch_array($query = db_select("select id from kontoplan where regnskabsaar = '$regnaar' and kontotype='R'",__FILE__ . " linje " . __LINE__));
	if (!$r['id'] && $kontotype!='Resultat') print "<option>Resultat</option>\n";
	if ($kontotype!='Sum') print "<option>Sum</option>\n";
	$r=db_fetch_array($query = db_select("select id from kontoplan where regnskabsaar = '$regnaar' and kontotype='X'",__FILE__ . " linje " . __LINE__));
	if (!$r['id'] && $kontotype!='Sideskift') print "<option>Sideskift</option>\n";
}
print "</SELECT></td></tr>\n";

if ($kontotype=='Drift'||$kontotype=='Status') {
	print "<tr><td><font face=\"Helvetica, Arial, sans-serif\">Moms</td><td><br></td>";
	print "<td colspan=2><SELECT NAME=moms>";
	print "<option>$moms</option>\n";
	$query = db_select("select kode, kodenr from grupper where art = 'KM' or art = 'SM' or art = 'EM' or art = 'YM'",__FILE__ . " linje " . __LINE__);
	if ($moms) print "<option></option>\n";
	while ($row = db_fetch_array($query)) {
		$tmp=$row['kode'].$row['kodenr'];
		if ($moms!=$tmp) print "<option>$tmp</option>\n";
	}
	print "</SELECT></td></tr>\n";
}

if ($kontotype=='Sum') {
	print "<tr><td><font face=\"Helvetica, Arial, sans-serif\">Konto fra</td><td><br></td><td><input type=text size=6 name=fra_kto value='$fra_kto'></td></tr>\n";
} elseif ($kontotype=='Resultat') {
	print "<tr><td><font face=\"Helvetica, Arial, sans-serif\">Resultatkonto</td><td><br></td><td><input type=text size=6 name=fra_kto value='$fra_kto'></td></tr>\n";
}
if (($kontotype=='Drift')||($kontotype=='Status')) {
	$x=0;
	$alfabet=array("A","B","C","E","F","G","H","I","J","L","M","N","O","P","Q","R","S","T","U","V","X","Y","Z"); 
	$tmp=array();
	$query = db_select("select genvej from kontoplan where regnskabsaar='$regnaar' order by genvej",__FILE__ . " linje " . __LINE__);
	while ($row = db_fetch_array($query)) {
		$x++;
		$tmp[$x]=$row['genvej'];
	}
	print "<tr><td><font face=\"Helvetica, Arial, sans-serif\">Genvej</td><td><br></td>";
	print "<td><SELECT NAME=genvej>";
	print "<option>$genvej</option>\n";
	if ($genvej) print "<option></option>\n";
	for ($x=0; $x<25; $x++) {
		if (!in_array($alfabet[$x], $tmp)) print "<option>$alfabet[$x]</option>\n";
	}
	print "</SELECT></td>";
	if ($kontotype=='Status') {
		$x=1;
		$valuta_kode[0]='DKK';
		$valuta_navn[0]='Danske kroner';
		$q=db_select("select beskrivelse,box1 from grupper where art='VK' order by box1",__FILE__ . " linje " . __LINE__);
		while ($r = db_fetch_array($q)) {
			$valuta_kode[$x]=$r['box1'];
			$valuta_navn[$x]=$r['beskrivelse'];
			$x++;
		}
		print "</tr><tr><td>Valuta</td><td></td>";
		print "<td><SELECT NAME=\"ny_valuta\">";
		for ($x=0;$x<count($valuta_kode);$x++){
			if ($valuta==$valuta_kode[$x])print "<option value='$valuta_kode[$x]'>$valuta_kode[$x] : $valuta_navn[$x]</option>"; 
		}
		for ($x=0;$x<count($valuta_kode);$x++){
			if ($valuta!=$valuta_kode[$x])print "<option value='$valuta_kode[$x]'>$valuta_kode[$x] : $valuta_navn[$x]</option>"; 
		}
		print "</select></td></tr>";
	} else print "<input type=\"hidden\" name=\"$valuta_kode[$x]\" value='DKK'>"; 
}
if ($kontotype=='Drift'||$kontotype=='Status') print "<tr><td colspan=\"2\">Saldo</td><td>$valuta: ".dkdecimal($saldo*100/$valutakurs)."</td><tr>";
if ($lukket=='on') $lukket="checked";
print "<tr><td colspan=\"2\">Lukket</td>";
print "<td><input type=checkbox name=lukket $lukket></td></tr>\n";
print "<tr><td><br></td></tr>\n";
print "<tr><td><br></td></tr>\n";
print "<tr><td colspan=4 align=center>";
print "<table width=\"100%\" cellpadding=\"0\" cellspaci ng=\"0\" border=\"0\"><tbody>";
print "<td align=center><input type=submit accesskey=\"g\" value=\"Gem/opdat&eacute;r\" name=\"gem\"></td>";
if ($slet==1) print "<td align = center><input type=submit accesskey=\"s\" value=\"&nbsp;&nbsp;&nbsp;&nbsp;Slet&nbsp;&nbsp;&nbsp;&nbsp;\" name=\"slet\" onclick=\"return confirm('Vil du slette konto $kontonr?')\" ></td>";
print "</tr>\n</tbody></table>";

?>
</tbody>
</table>
</td></tr>
<tr><td align = "center" valign = "bottom">
		<table width="100%" align="center" border="1" cellspacing="0" cellpadding="0"><tbody>
			<td width="100%" <?php echo $top_bund ?>><font face="Helvetica, Arial, sans-serif" color="#000066"><br></td>
		</tbody></table>
</td></tr>
</tbody></table>
</body></html>
