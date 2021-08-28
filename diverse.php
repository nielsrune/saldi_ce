<?php
// -------------------- systemdata/diverse.php ------ patch 2.0.4 --2009.01.29--------
// LICENS..
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere det under betingelserne i GNU General Public License (GPL)
// som er udgivet af The Free Software Foundation; enten i version 2
// af denne licens eller en senere version efter eget valg
//
// Dette program er udgivet med haab om at det vil vaere til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
//
// En dansk oversaettelse af licensen kan laeses her:
// http://www.fundanemt.com/gpl_da.html
//
// Copyright (c) 2004-2009 DANOSOFT ApS
// ----------------------------------------------------------------------

@session_start();
$s_id=session_id();
$title="Diverse Indstilinger";
$modulnr=1;
$css="../css/standard.css";

$diffkto=NULL;

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("top.php");

if(if_isset($_GET['sektion'])) $sektion=$_GET['sektion'];
if(if_isset($_GET['skiftnavn'])) $skiftnavn=$_GET['skiftnavn'];
else $skiftnavn="";

if ($_POST) {
	if ($sektion=='provision') {
		$id=$_POST['id'];
		$beskrivelse=$_POST['beskrivelse'];
		$box1=$_POST['box1'];
		$box2=$_POST['box2'];
		$box3=$_POST['box3'];
		$box4=$_POST['box4'];
		if  (($id==0) && ($r = db_fetch_array(db_select("select id from grupper where art = 'DIV' and kodenr='1'",__FILE__ . " linje " . __LINE__)))) $id=$r['id'];
		elseif ($id==0){
		db_modify("insert into grupper (beskrivelse, kodenr, art, box1, box2, box3, box4) values ('Provisionsrapport', '1', 'DIV', '$box1', '$box2', '$box3', '$box4')",__FILE__ . " linje " . __LINE__);
		} elseif ($id > 0) db_modify("update grupper set  box1 = '$box1', box2 = '$box2', box3 = '$box3' , box4 = '$box4' where id = '$id'",__FILE__ . " linje " . __LINE__);
	#######################################################################################
	} elseif ($sektion=='personlige_valg') {
		$p_id=$_POST['p_id'];
		$jsvars=$_POST['jsvars'];
		$popup=$_POST['popup'];
		if ($p_id) db_modify("update grupper set  box1='$jsvars',box2='$popup' where id = '$p_id'",__FILE__ . " linje " . __LINE__);
	#######################################################################################
	} elseif ($sektion=='div_valg') {
		$id_2=$_POST['id_2'];
		$box1=$_POST['box1'];
		$box2=$_POST['box2'];
		$box3=$_POST['box3'];
		$box4=$_POST['box4'];
		$box5=$_POST['box5'];
		$box6=$_POST['box6'];
		$box7=$_POST['box7'];
		$box8=$_POST['box8'];
		$box9=$_POST['box9'];
		$box10=$_POST['box10'];
		$id_3=$_POST['id_3'];
		$incl_moms=$_POST['incl_moms'];
		
		if  (($id_2==0) && ($r = db_fetch_array(db_select("select id from grupper where art = 'DIV' and kodenr='2'",__FILE__ . " linje " . __LINE__)))) $id_2=$r['id'];
		elseif ($id_2==0){
			db_modify("insert into grupper (beskrivelse,kodenr,art,box1,box2,box3,box4,box5,box6,box7,box8,box9,box10) values ('Div_valg','2','DIV','$box1','$box2','$box3','$box4','$box5','$box6','$box7','$box8','$box9','$box10')",__FILE__ . " linje " . __LINE__);
		} elseif ($id_2 > 0) {
			db_modify("update grupper set  box1='$box1',box2='$box2',box3='$box3',box4='$box4',box5='$box5',box6='$box6',box7='$box7',box8='$box8',box9='$box9',box10='$box10' where id = '$id_2'",__FILE__ . " linje " . __LINE__);
		}
		if  (($id_3==0) && ($r = db_fetch_array(db_select("select id from grupper where art = 'DIV' and kodenr='3'",__FILE__ . " linje " . __LINE__)))) $id_3=$r['id'];
		elseif ($id_3==0){
			db_modify("insert into grupper (beskrivelse,kodenr,art,box1) values ('Div_valg','3','DIV','$incl_moms')",__FILE__ . " linje " . __LINE__);
		} elseif ($id_3 > 0) {
			db_modify("update grupper set  box1='$incl_moms' where id = '$id_3'",__FILE__ . " linje " . __LINE__);
		}
	#######################################################################################
	} elseif ($sektion=='docubizz') {
		$id=$_POST['id'];
		$box1=$_POST['box1'];
		$box2=$_POST['box2'];
		$box3=$_POST['box3'];
		$box4=$_POST['box4'];
		$box5=$_POST['box5'];
		
		if  ((!$id) && ($r = db_fetch_array(db_select("select id from grupper where art = 'DocBiz'",__FILE__ . " linje " . __LINE__)))) $id=$r['id'];
		elseif (!$id){
			db_modify("insert into grupper (beskrivelse,kodenr,art,box1,box2,box3,box4,box5) values ('DocuBizz','1','DocBiz','$box1','$box2','$box4','$box5')",__FILE__ . " linje " . __LINE__);
		} elseif ($id > 0) {
			db_modify("update grupper set  box1='$box1',box2='$box2',box3='$box3',box4='$box4',box5='$box5' where id = '$id'",__FILE__ . " linje " . __LINE__);
		}
	} elseif ($sektion=='upload_dbz') {
		system ("../utils/docubizzexport.sh > ../temp/NULL");
		print "<BODY onLoad=\"JavaScript:alert('Data sendt til DocuBizz')\">";
#######################################################################################
	} elseif ($sektion=='orediff') {
		$id=$_POST['id'];
		$box1=$_POST['box1'];
		$box2=$_POST['box2']*1;
		if ($box1) $box1=usdecimal($box1);
		if ($box2 && !db_fetch_array(db_select("select id from kontoplan where kontonr = '$box2' and regnskabsaar='$regnaar'",__FILE__ . " linje " . __LINE__))){
			$tekst=findtekst(175,$sprog_id);	
			print "<BODY onLoad=\"JavaScript:alert('$tekst')\">";
			$diffkto=$box2;
			$box2='';
		}
		if  ((!$id) && ($r = db_fetch_array(db_select("select id from grupper where art = 'OreDif'",__FILE__ . " linje " . __LINE__)))) $id=$r['id'];
		elseif (!$id){
			db_modify("insert into grupper (beskrivelse,kodenr,art,box1,box2) values ('Oredifferencer','1','OreDif','$box1','$box2')",__FILE__ . " linje " . __LINE__);
		} elseif ($id > 0) {
			db_modify("update grupper set  box1='$box1',box2='$box2' where id = '$id'",__FILE__ . " linje " . __LINE__);
		}
######################################################################################
	} elseif ($sektion=='massefakt') {
		$id=$_POST['id'];
		$brug_mfakt=$_POST['brug_mfakt'];
		if ($brug_mfakt) {
			$brug_dellev=$_POST['brug_dellev'];
			$levfrist=$_POST['levfrist'];
		} else {
			$brug_dellev=NULL;
			$levfrist=0;
		}
		if  ((!$id) && ($r = db_fetch_array(db_select("select id from grupper where art = 'MFAKT'",__FILE__ . " linje " . __LINE__)))) $id=$r['id'];
		elseif (!$id){
			db_modify("insert into grupper (beskrivelse,kodenr,art,box1,box2,box3) values ('Massefakturering','1','MFAKT','$brug_mfakt','$brug_dellev','$levfrist')",__FILE__ . " linje " . __LINE__);
		} elseif ($id > 0) {
			db_modify("update grupper set  box1='$brug_mfakt',box2='$brug_dellev',box3='$levfrist' where id = '$id'",__FILE__ . " linje " . __LINE__);
		}
######################################################################################
	} elseif ($sektion=='kontoplan_io') {
		if ($_POST['submit']=="Eksporter") {
			list($tmp)=split(":",$_POST['regnskabsaar']);
			print "<BODY onLoad=\"javascript:exporter_kontoplan=window.open('exporter_kontoplan.php?aar=$tmp','lager','scrollbars=yes,resizable=yes,dependent=yes');exporter_kontoplan.focus();\">";				
		}
		elseif ($_POST['submit']=="Importer") {
			print "<BODY onLoad=\"javascript:importer_kontoplan=window.open('importer_kontoplan.php','kontoplan','scrollbars=yes,resizable=yes,dependent=yes');importer_kontoplan.focus();\">";				
		}
	} elseif ($sektion=='debitor_io') {
		if ($_POST['submit']=="Eksporter") {
			print "<BODY onLoad=\"javascript:exporter_debitor=window.open('exporter_debitor.php?aar=$tmp','debitor','scrollbars=yes,resizable=yes,dependent=yes');exporter_debitor.focus();\">";				
		}
		elseif ($_POST['submit']=="Importer") {
			print "<BODY onLoad=\"javascript:importer_debitor=window.open('importer_debitor.php','debitor','scrollbars=yes,resizable=yes,dependent=yes');importer_debitor.focus();\">";				
		}
	} elseif ($sektion=='regnskab') {
		if (strstr($_POST[submit],'Skift')) {
			$nyt_navn=trim(addslashes($_POST['nyt_navn']));
			include("../includes/connect.php");
			if (db_fetch_array(db_select("select id from regnskab where regnskab = '$nyt_navn'",__FILE__ . " linje " . __LINE__))) {
				print "<BODY onLoad=\"JavaScript:alert('Der findes allerede et regnskab med navnet $nyt_navn! Navn ikke &aelig;ndret')\">";
			} else {
				$r=db_fetch_array(db_select("select id from kundedata where regnskab_id = '$db_id'"));
				if (!$r['id']){
					$tmp=addslashes($regnskab);	
					db_modify("update kundedata set regnskab_id = '$db_id' where regnskab='$tmp'",__FILE__ . " linje " . __LINE__);
				}
				db_modify("update regnskab set regnskab = '$nyt_navn' where db='$db'",__FILE__ . " linje " . __LINE__);
			
			}
			include("../includes/online.php");
		}
	}
}
print "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\"><tbody>";

regnskabsnavn($regnskab,$skiftnavn);
provision();
personlige_valg();
div_valg();
if ($docubizz) docubizz();
orediff($diffkto);
massefakt();
sprog();
kontoplan_io();
debitor_io();

print "</form>";
#print "</tbody></table></td></tr>";

function regnskabsnavn($regnskab,$skiftnavn) 
{
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>Skift navn p&aring; regnskab</u></b></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	if (!$skiftnavn) {
		print "<tr><td colspan=6>Dit regnskab hedder: \"$regnskab\". Klik <a href=diverse.php?sektion=regnskab&skiftnavn=ja>her</a> for at &aelig;ndre navnet.</td></tr>";
	} else  {
		print "<form name=diverse action=diverse.php?sektion=regnskab method=post>"; 
		print "<tr><td colspan=6>Skriv nyt navn p&aring; regnskab <input type = text size=\"40\" name=\"nyt_navn\" value =\"$regnskab\"> og klik <input type=submit value=\"Skift&nbsp;navn\" name=\"submit\"></td></tr>";
		print "</form>";
	}
	print "<tr><td colspan=6><br></td></tr>";
}

function provision() 
{
	$bet=NULL; $ref=NULL; $kua=NULL; $smart=NULL;
	$kort=NULL; $batch=NULL;
	
	$q = db_select("select * from grupper where art = 'DIV' and kodenr = '1'",__FILE__ . " linje " . __LINE__);
	$r = db_fetch_array($q);
	$id=$r['id'];
	$beskrivelse=$r['beskrivelse'];
	$kodenr=$r['kodenr'];
	$box1=$r['box1'];
	$box2=$r['box2'];
	$box3=$r['box3'];
	$box4=$r['box4'];

	if ($box1=='ref') $ref="checked";
	elseif ($box1=='kua') $kua="checked";
	else $smart="checked";

	if ($box2=='kort') $kort="checked";
	else $batch="checked";

	if ($box4=='bet') $bet="checked";
	else $fak="checked";

	print "<form name=diverse action=diverse.php?sektion=provision method=post>";
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>Grundlag for provisionsberegning</u></b></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	print "<input type=hidden name=id value='$id'>";
	print "<tr><td>Beregn provision p&aring; ordrer som er faktureret eller faktureret og betalt</td><td></td><td align=center>Faktureret</td><td align=center>Betalt</td></tr>";
	print "<tr><td></td><td></td><td align=center><input type=radio name=box4 value=fak title='Provision beregnes p&aring; fakturerede ordrer' $fak></td><td align=center><input type=radio name=box4 value=bet title= 'Provision beregnes p&aring; betalte ordrer' $bet></td></tr>";
	print "<tr><td>Kilde for personinfo</td><td align=center>Ref.</td><td align=center>Kundeans.</td><td align=center>Begge</td></tr>";
	print "<tr><td></td><td align=center><input type=radio name=box1 value=ref title='Provision tilfalder den der er angivet som referenceperson p&aring; de enkelte ordrer' $ref></td><td align=center><input type=radio name=box1 value=kua title= 'Provision tilfalder den kundeansvarlige' $kua></td><td align=center><input type=radio name=box1 value=smart title='Provision tilfalder den kundeansvarlige s&aring;fremt der er tildelt en s&aring;dan, ellers til den som er referenceperson p&aring; de enkelte ordrer' $smart></td></tr>";
	print "<tr><td>Kilde for kostpris</td><td></td><td align=center>Indk&oslash;bspris</td><td align=center>Varekort</td></tr>";
	print "<tr><td></td><td></td><td align=center><input type=radio name=box2 value=batch title='Anvend varens reelle indk&oslash;bspris som kostpris.' $batch></td><td align=center><input type=radio name=box2 value=kort title='Anvend kostpris fra varekort.' $kort></td></tr>";
	print "<tr><td>Sk&aelig;ringsdato for provisionsberegning</td><td></td><td></td><td align=center><SELECT NAME=box3 title='Dato hvorfra og med (i foreg&aring;ende m&aring;ned) til (dato i indev&aelig;rende m&aring;ned)provisionsberegning foretages'>";
	if ($box3) print"<option>$box3</option>";
	for ($x=1; $x<=28; $x++) { 
		print "<option>$x</option>";
	}
	print "</SELECT></td></tr>";;
	print "<tr><td><br></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<td><br></td><td><br></td><td><br></td><td align = center><input type=submit accesskey=\"g\" value=\"Gem / opdater\" name=\"submit\"></td>";
	print "</form>";
} # endfunc provision

function kontoplan_io() 
{
	$x=0;
	$q = db_select("select * from grupper where art = 'RA' order by  kodenr",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		$x++;
		$id[$x]=$r['id'];
		$beskrivelse[$x]=$r['beskrivelse'];
		$kodenr[$x]=$r['kodenr'];
	}
	$antal_regnskabsaar=$x;
	print "<form name=diverse action=diverse.php?sektion=kontoplan_io method=post>";
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>Indl&aelig;s  / udl&aelig;s kontoplan</b></u></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	print "<tr><td colspan=2>Eksporter kontoplan</td><td align=center><SELECT NAME=regnskabsaar title='V&aelig; det regnskabs&aring;r hvorfra kontoplanen skal eksporteres'>";
	if ($box3[$x]) print"<option>$box3[$x]</option>";
	for ($x=1; $x<=$antal_regnskabsaar; $x++) { 
		print "<option>$kodenr[$x] : $beskrivelse[$x]</option>";
	}
	print "</SELECT></td>";;
	print "<td align = center><input type=submit accesskey=\"e\" value=\"Eksporter\" name=\"submit\"></td><tr>";
	print "<tr><td colspan=3>Importer kontoplan (erstatter kontoplanen for nyeste regnskabs&aring;r) </td>";
	print "<td align = center><input type=submit accesskey=\"i\" value=\"Importer\" name=\"submit\"></td><tr>";
#	print "</tbody></table></td></tr>";
	print "</form>";

} # endfunc kontoplan_io

function kreditor_io() 
{
	$x=0;
	print "<form name=diverse action=diverse.php?sektion=kreditor_io method=post>";
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>Indl&aelig;s  / udl&aelig;s kreditorer</b></u></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	print "<tr><td colspan=3>Eksporter kontoplan</td>";
	print "<td align = center><input type=submit accesskey=\"e\" value=\"Eksporter\" name=\"submit\"></td><tr>";
	print "<tr><td colspan=3>Importer kontoplan (erstatter kontoplanen for nyeste regnskabs&aring;r) </td>";
	print "<td align = center><input type=submit accesskey=\"i\" value=\"Importer\" name=\"submit\"></td><tr>";
#	print "</tbody></table></td></tr>";
	print "</form>";

} # endfunc kreditor_io
function debitor_io() 
{
	$x=0;
	print "<form name=diverse action=diverse.php?sektion=debitor_io method=post>";
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>Indl&aelig;s  / udl&aelig;s debitorer</b></u></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	print "<tr><td colspan=3>Eksporter debitorer</td>";
	print "<td align = center><input type=submit accesskey=\"e\" value=\"Eksporter\" name=\"submit\"></td><tr>";
	print "<tr><td colspan=3>Importer debitorer</td>";
	print "<td align = center><input type=submit accesskey=\"i\" value=\"Importer\" name=\"submit\"></td><tr>";
#	print "</tbody></table></td></tr>";
	print "</form>";

} # endfunc kreditor_io

function sprog () {
	global $sprog_id;

	$x=0;
	$q = db_select("select * from grupper where art = 'SPROG' order by kodenr",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		$x++;
		$id[$x]=$r['id'];
		$beskrivelse[$x]=$r['beskrivelse'];
		$kodenr[$x]=$r['kodenr'];
		$sprogkode[$x]=$r['box1'];
	}
	$antal_sprog=$x;
	print "<form name=diverse action=diverse.php?sektion=sprog method=post>";
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>Sprog</b></u></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	$tekst1=findtekst(1,$sprog_id);
	$tekst2=findtekst(2,$sprog_id);	
	print "<tr><td title=\"Klik her for at rette tekster\"><a href=tekster.php?sprog_id=1>$tekst1</a></td><td><SELECT NAME=sprog title='$tekst2'>";
	if ($box3[$x]) print"<option>$box3[$x]</option>";
	for ($x=1; $x<=$antal_sprog; $x++) { 
		print "<option>$beskrivelse[$x]</option>";
	}
	print "</SELECT></td></tr>";
	print "<tr><td><br></td></tr>";
	$tekst1=findtekst(3,$sprog_id);
	print "<tr><td align = right colspan=4><input type=submit value=\"$tekst1\" name=\"submit\"></td></tr>";
#	print "<td align = center><input type=submit value=\"$tekst2\" name=\"submit\"></td>";
#	print "<td align = center><input type=submit value=\"$tekst3\" name=\"submit\"></td><tr>";
/*	
	print "</tbody></table></td></tr>";
*/
	print "</form>";
} # endfunc sprog

function jobkort () {
	global $sprog_id;

	$x=0;
	$q = db_select("select * from grupper where art = 'JOBKORT' order by kodenr",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		$x++;
		$id[$x]=$r['id'];
		$beskrivelse[$x]=$r['beskrivelse'];
		$kodenr[$x]=$r['kodenr'];
		$sprogkode[$x]=$r['box1'];
	}
	$antal_sprog=$x;
	print "<form name=diverse action=diverse.php?sektion=sprog method=post>";
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>Sprog</b></u></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	$tekst1=findtekst(1,$sprog_id);
	$tekst2=findtekst(2,$sprog_id);	
	print "<tr><td>	$tekst1</td><td><SELECT NAME=sprog title='$tekst2'>";
	if ($box3[$x]) print"<option>$box3[$x]</option>";
	for ($x=1; $x<=$antal_sprog; $x++) { 
		print "<option>$beskrivelse[$x]</option>";
	}
	print "</SELECT></td></tr>";
	print "<tr><td><br></td></tr>";
	$tekst1=findtekst(3,$sprog_id);
	print "<tr><td align = right colspan=4><input type=submit value=\"$tekst1\" name=\"submit\"></td></tr>";
#	print "<td align = center><input type=submit value=\"$tekst2\" name=\"submit\"></td>";
#	print "<td align = center><input type=submit value=\"$tekst3\" name=\"submit\"></td><tr>";
/*	
	print "</tbody></table></td></tr>";
*/
	print "</form>";

} # endfunc sprog


function personlige_valg() 
{
	global $sprog_id;
	global $popop;
	global $bruger_id;
	
	$r = db_fetch_array(db_select("select * from grupper where art = 'USET' and kodenr = '$bruger_id'",__FILE__ . " linje " . __LINE__));
	$p_id=$r['id'];
	$jsvars=$r['box1'];
	if ($r['box2']) $popup='checked';

	print "<form name=personlige_valg action=diverse.php?sektion=personlige_valg&popop=$popop method=post>";
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>Personlige valg</u></b></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	print "<input type=hidden name=p_id value='$p_id'>";
	print "<input type=hidden name=id_3 value='$id_3'>";
	
	print "<tr><td title=\"".findtekst(207,$sprog_id)."\">".findtekst(208,$sprog_id)."</td><td><input type=checkbox name=popup $popup></td></tr>";
	print "<tr><td title=\"".findtekst(209,$sprog_id)."\">".findtekst(210,$sprog_id)."</td><td colspan=4><input type=text size=60 name=jsvars value=\"$jsvars\"></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<td><br></td><td><br></td><td><br></td><td align = center><input type=submit accesskey=\"g\" value=\"Gem / opdater\" name=\"submit\"></td>";
	print "</form>";
} # endfunc personlige_valg
function div_valg() 
{
	global $sprog_id;
	global $docubizz;
	
	$folge_s_tekst=NULL;$gruppevalg=NULL;$kuansvalg=NULL;
	$ref=NULL; $kua=NULL; $smart=NULL;
	$kort=NULL; $batch=NULL; 
			
	$q = db_select("select * from grupper where art = 'DIV' and kodenr = '2'",__FILE__ . " linje " . __LINE__);
	$r = db_fetch_array($q);
	$id_2=$r['id'];
	$beskrivelse=$r['beskrivelse']; $kodenr=$r['kodenr'];	$box1=$r['box1'];	 $box2=$r['box2']; $box3=$r['box3']; $box4=$r['box4']; $box5=$r['box5']; $box6=$r['box6'];$box7=$r['box7'];$box8=$r['box8'];$box9=$r['box9'];$box10=$r['box10'];
	if ($box1=='on') $gruppevalg="checked"; if ($box2=='on') $kuansvalg="checked"; if ($box3=='on') $folge_s_tekst="checked"; 
	if ($box4=='on') $hurtigfakt="checked";	if ($box5=='on') $straks_bogf="checked";	if ($box6=='on') $docubizz="checked";
	if ($box7=='on') $jobkort="checked";if ($box8=='on') $vis_nul_lev="checked";if ($box9=='on') $negativt_lager="checked";
	if ($box10=='on') $betalingsliste="checked";

	$q = db_select("select * from grupper where art = 'DIV' and kodenr = '3'",__FILE__ . " linje " . __LINE__);
	$r = db_fetch_array($q);
	$id_3=$r['id'];
	if ($r['box1']=='on') $incl_moms="checked";
		
	print "<form name=diverse action=diverse.php?sektion=div_valg method=post>";
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>Diverse valg</u></b></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	print "<input type=hidden name=id_2 value='$id_2'>";
	print "<tr><td title=\"".findtekst(186,$sprog_id)."\">".findtekst(162,$sprog_id)."</td><td><input type=checkbox name=box1 $gruppevalg></td></tr>";
	print "<tr><td title=\"".findtekst(187,$sprog_id)."\">".findtekst(163,$sprog_id)."</td><td><input type=checkbox name=box2 $kuansvalg></td></tr>";
	print "<tr><td title=\"".findtekst(188,$sprog_id)."\">".findtekst(164,$sprog_id)."</td><td><input type=checkbox name=box3 $folge_s_tekst></td></tr>";
	print "<tr><td title=\"".findtekst(197,$sprog_id)."\">".findtekst(196,$sprog_id)."</td><td><input type=checkbox name=incl_moms $incl_moms></td></tr>";
	print "<tr><td title=\"".findtekst(189,$sprog_id)."\">".findtekst(169,$sprog_id)."</td><td><input type=checkbox name=box8 $vis_nul_lev></td></tr>";
	$q = db_select("select id from grupper where art = 'VG' and box9='on'",__FILE__ . " linje " . __LINE__);
	if (!db_fetch_array($q)) print "<tr><td title=\"".findtekst(190,$sprog_id)."\">".findtekst(165,$sprog_id)."</td><td><input type=checkbox name=box4 $hurtigfakt></td></tr>";
	print "<tr><td title=\"".findtekst(191,$sprog_id)."\">".findtekst(166,$sprog_id)."</td><td><input type=checkbox name=box5 $straks_bogf></td></tr>";
	print "<tr><td title=\"".findtekst(192,$sprog_id)."\">".findtekst(183,$sprog_id)."</td><td><input type=checkbox name=box9 $negativt_lager></td></tr>";
	print "<tr><td title=\"".findtekst(185,$sprog_id)."\">".findtekst(184,$sprog_id)."</td><td title=\"".findtekst(185,$sprog_id)."\"><input type=checkbox name=box10 $betalingsliste></td></tr>";
	print "<tr><td title=\"".findtekst(193,$sprog_id)."\">".findtekst(167,$sprog_id)."</td><td><input type=checkbox name=box6 $docubizz></td></tr>";
	print "<tr><td title=\"".findtekst(194,$sprog_id)."\">".findtekst(168,$sprog_id)."</td><td><input type=checkbox name=box7 $jobkort></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<td><br></td><td><br></td><td><br></td><td align = center><input type=submit accesskey=\"g\" value=\"Gem / opdater\" name=\"submit\"></td>";
	print "</form>";
} # endfunc div_valg
function docubizz() 
{
	?>
	<script Language="JavaScript">
	<!--
	function Form1_Validator(docubizz) {
		if (docubizz.box3.value != docubizz.pw2.value) {
		alert("Kodeord skal v&aelig;re ens.");
		docubizz.box3.focus();
		return (false);
		}
	}
	//--></script>

	<?php
	$q = db_select("select * from grupper where art = 'DocBiz'",__FILE__ . " linje " . __LINE__);
	$r = db_fetch_array($q);
	$id=$r['id'];
	$ftpsted=$r['box1'];
	$ftplogin=$r['box2'];
	$ftpkode=$r['box3'];
	$ftp_dnld_mappe=$r['box4'];
	$ftp_upld_mappe=$r['box5'];
	
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>DocuBizz</u></b></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	
	print "<form name=docubizz action=diverse.php?sektion=docubizz method=post onsubmit=\"return Form1_Validator(this)\">";
	print "<input type=hidden name=id value='$id'>";
	print "<tr><td>Navn eller IP p&aring; ftpserver</td><td colspan=2><input type=text name=box1 size=20 value=\"$ftpsted\"></td></tr>";
	print "<tr><td>Mappe til download p&aring; ftpserver</td><td colspan=2><input type=text name=box4 size=20 value=\"$ftp_dnld_mappe\"></td></tr>";
	print "<tr><td>Mappe til upload p&aring; ftpserver</td><td colspan=2><input type=text name=box5 size=20 value=\"$ftp_upld_mappe\"></td></tr>";
	print "<tr><td>Brugernavn p&aring; ftpserver</td><td colspan=2><input type=text name=box2 size=20 value=\"$ftplogin\"></td></tr>";
	print "<tr><td>Kodeord p&aring; ftpserver</td><td colspan=2><input type=password name=box3 size=20 value=\"$ftpkode\"></td></tr>";
	print "<tr><td>Gentag kodeord</td><td colspan=2><input type=password name=pw2 size=20 value=\"$ftpkode\"></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<tr><td><br></td><td><br></td><td><br></td><td align = center><input style=\"width: 8em\" type=submit accesskey=\"g\" value=\"Gem / opdater\" name=\"submit\"></td><tr>";
	print "</form>";
	print "<form name=upload_dbz action=diverse.php?sektion=upload_dbz method=post>";
	print "<tr><td><br></td></tr>";
	print "<tr><td colspan=3>Opdater Docubizz server</td><td align = center><input style=\"width: 8em\" type=submit accesskey=\"g\" value=\"Send data\" name=\"submit\"></td><tr>";
	print "</form>";
	
} # endfunc docubizz
function orediff($diffkto) 
{
	global $sprog_id;
	
	$q = db_select("select * from grupper where art = 'OreDif'",__FILE__ . " linje " . __LINE__);
	$r = db_fetch_array($q);
	$id=$r['id'];
	$maxdiff=dkdecimal($r['box1']);
	if (!$diffkto) $diffkto=$r['box2'];
	
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>".findtekst(170,$sprog_id)."</u></b></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	
	print "<form name=orediff action=diverse.php?sektion=orediff method=post onsubmit=\"return Form1_Validator(this)\">";
	print "<input type=hidden name=id value='$id'>";
	print "<tr><td title=\"".findtekst(171,$sprog_id)."\">".findtekst(172,$sprog_id)."</td><td colspan=2><input type=text style=\"text-align:right\" name=box1 size=3 value=\"$maxdiff\"></td></tr>";
	print "<tr><td title=\"".findtekst(173,$sprog_id)."\">".findtekst(174,$sprog_id)."</td><td colspan=2><input type=text style=\"text-align:right\" name=box2 size=3 value=\"$diffkto\"></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<tr><td><br></td><td><br></td><td><br></td><td align = center><input style=\"width: 8em\" type=submit accesskey=\"g\" value=\"Gem / opdater\" name=\"submit\"></td><tr>";
	print "</form>";
	
} # endfunc docubizz
function massefakt () {
	global $sprog_id;
	global $docubizz;
	
	$folge_s_tekst=NULL;$gruppevalg=NULL;$kuansvalg=NULL;
	$ref=NULL; $kua=NULL; $smart=NULL;
	$kort=NULL; $batch=NULL; 
			
	$q = db_select("select * from grupper where art = 'MFAKT' and kodenr = '1'",__FILE__ . " linje " . __LINE__);
	$r = db_fetch_array($q);
	$id=$r['id'];
	if ($r['box1'] == 'on') $brug_mfakt='checked';
	else $brug_mfakt='';
	if ($r['box2'] == 'on') $brug_dellev='checked';
	else $brug_dellev='';
	$levfrist=$r['box3'];
		
	print "<form name=diverse action=diverse.php?sektion=massefakt method=post>";
	print "<tr><td colspan=6><hr></td></tr>";
	print "<tr><td colspan=6><b><u>".findtekst(200,$sprog_id)."</u></b></td></tr>";
	print "<tr><td colspan=6><br></td></tr>";
	print "<input type=hidden name=id value='$id'>";
	print "<tr><td title=\"".findtekst(202,$sprog_id)."\">".findtekst(201,$sprog_id)."</td><td><input type=checkbox name=brug_mfakt $brug_mfakt></td></tr>";
	print "<tr><td title=\"".findtekst(204,$sprog_id)."\">".findtekst(203,$sprog_id)."</td><td><input type=checkbox name=brug_dellev $brug_dellev></td></tr>";
	print "<tr><td title=\"".findtekst(206,$sprog_id)."\">".findtekst(205,$sprog_id)."</td><td><input type=text style=\"text-align:right\" name=levfrist size=1 value=\"$levfrist\"></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<tr><td><br></td></tr>";
	print "<td><br></td><td><br></td><td><br></td><td align = center><input type=submit accesskey=\"g\" value=\"Gem / opdater\" name=\"submit\"></td>";
	print "</form>";
} # endfunc massefakt

?>
</tbody></table>
</body></html>
