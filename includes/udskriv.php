<?php #topkode_start
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// -------includes/udskriv.php----lap 3.7.2----2018.04.18-------------------
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
// 2013.03.20 Tilføjet mulighed for fravalg af logo på udskrift. Søg "PDF-tekst"
// 2013.12.02	Efter udskrivning af kreditorordre, åbnes ordre som debitorordre. Tilføjer $art. Søg $art.
// 2013.12.10	Efter udskrivning, åbnes ordren 2. gang v. popup. Tilføjet "|| $popup", søg 20131210
// 2014.06.13 Har sat gammel og ny kode sammen, så det virker til både saldi og stillads. Søg efter 'stillads' for indsat kode
// 2016.11.25 PHR Indført html som formulargenerator som alternativ til postscript. Søg htmfp, .htm & weasyprint
// 2017.03.24	PHR Blev smidt af efer udskriv som PDF grundet at de ikke mere kører i popup. Søg art='R'
// 2018.04.18	PHR Tilføjet udskriv til='fil'


@session_start();
$s_id=session_id();
header('Expires: Mon, 01 Jan 2017 05:00:00 GMT'); 
header('Cache-Control: no-store, no-cache, must-revalidate'); 
header('Cache-Control: post-check=0, pre-check=0', FALSE); 
header('Pragma: no-cache');
ini_set("display_errors", "0");

$css="../css/standard.css";		
		
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");

if (!isset($exec_path)) $exec_path="/usr/bin";

$ps_fil=if_isset($_GET['ps_fil']);
$valg=if_isset($_GET['valg']);
$logoart=if_isset($_GET['logoart']);
$id=if_isset($_GET['id']);
$udskriv_til=if_isset($_GET['udskriv_til']);
$udskrift=if_isset($_GET['udskrift']);
$bgr=if_isset($_GET['bgr']);# stillads
$art=if_isset($_GET['art']);
if ($udskriv_til=='historik') {
	historik($id,$ps_fil);
	$valg="tilbage";
}
if ($id && !$art) {
	$r=db_fetch_array(db_select("select art from ordrer where id = '$id'",__FILE__ . " linje " . __LINE__));
	$art=$r['art'];
} 
if (!$art) $art='DO';

if ($valg=="tilbage" && !$bgr) {
	if ((!$id && $art!='R') || $popup ) { # 20131210
		print "<meta http-equiv=\"refresh\" content=\"0;URL=../includes/luk.php\">";
  	exit;
	} else {
		if ($art=='R') print "<meta http-equiv=\"refresh\" content=\"0;URL=../debitor/rykker.php?rykker_id=$id\">";
		elseif (substr($art,0,1)=='K') print "<meta http-equiv=\"refresh\" content=\"0;URL=../kreditor/ordre.php?id=$id\">";
		elseif ($art=='PO') print "<meta http-equiv=\"refresh\" content=\"0;URL=../debitor/pos_ordre.php?id=$id\">";
		else print "<meta http-equiv=\"refresh\" content=\"0;URL=../debitor/ordre.php?id=$id\">";
  	exit;
	}
} elseif ($valg=="tilbage" && $bgr) {# stillads
  if ($popup || (!$id && $art!='R')) {
		print "<meta http-equiv=\"refresh\" content=\"0;URL=../includes/luk.php\">";
  	exit;
	} else {
		if ($art=='R') print "<meta http-equiv=\"refresh\" content=\"0;URL=../debitor/rykker.php?rykker_id=$id\">";
		else print "<meta http-equiv=\"refresh\" content=\"0;URL=../debitor/ordre.php?id=$id\">";
  	exit;
	}
}
if (!$valg) {
	$qtxt="select id,box1 from grupper where art='PV'";
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
	if ($r['box1']=='on') {
		$ip=$_SERVER['REMOTE_ADDR'];
		print "<!--!";
		system("lpq -P $ip", $tjek);
		print "-->";
		if ($tjek) {
			$ip=NULL;
			$valg="pdf";
		} else $valg='ip';
	} else $valg="pdf";
}
if ($valg) {
  $r = db_fetch_array(db_select("select box1,box2,box3 from grupper where art='PV'",__FILE__ . " linje " . __LINE__));
  if ($valg=="pdf" || $valg=="ip")  {
		print "<!--";
    if ($r['box2']) {
			system ("$r[box2] ../temp/$ps_fil ../temp/$ps_fil.pdf");
		} elseif ($r['box3'] && $udskrift!='kontokort') {
			$i=1;
			if (file_exists("../temp/".$ps_fil.".htm")) {
				$indfil="../temp/".$ps_fil.".htm";
				$udfil="../temp/".$ps_fil.".pdf";
				system ("weasyprint -e UTF-8 $indfil $udfil");
			} else {
			while(file_exists("../temp/".$ps_fil."_".$i.".htm")) {
				$indfil="../temp/".$ps_fil."_".$i.".htm";
				$udfil="../temp/".$ps_fil."_".$i.".pdf";
				system ("weasyprint -e UTF-8 $indfil $udfil");
				$i++;	
}
			}
			system ("pdftk ".$ps_fil."_*.pdf output ".$ps_fil.".pdf");
		} else {
			system ("$exec_path/ps2pdf ../temp/$ps_fil ../temp/$ps_fil.pdf");
		}
		system ("$exec_path/gs -q -dNOPAUSE -dBATCH -sDEVICE=tiffg4 -r200 -sPAPERSIZE=a4 -sOutputFile=../temp/$ps_fil.tiff ../temp/$ps_fil");
		print "-->";
		if (file_exists("../temp/$ps_fil.pdf")) {
			if (strpos($ps_fil,'tilbud') && file_exists("../logolib/$db_id/tilbud_bg.pdf")) $bg_fil="../logolib/$db_id/tilbud_bg.pdf";
			elseif (strpos($ps_fil,'ordre') && file_exists("../logolib/$db_id/ordrer_bg.pdf")) $bg_fil="../logolib/$db_id/ordrer_bg.pdf";
			elseif (strpos($ps_fil,'fakt') && file_exists("../logolib/$db_id/faktura_bg.pdf")) $bg_fil="../logolib/$db_id/faktura_bg.pdf";
			elseif (file_exists("../logolib/$db_id/bg.pdf")) $bg_fil="../logolib/$db_id/bg.pdf";
			print "<!-- kommentar for at skjule uddata til siden \n";
			if (system("which pdftk") && file_exists($bg_fil) && $udskriv_til != 'PDF-tekst' && $udskriv_til != 'fil') {
				$out="../temp/".$ps_fil."x.pdf";
				system ("$exec_path/pdftk ../temp/$ps_fil.pdf background $bg_fil output $out");
				unlink ("../temp/$ps_fil.pdf");
				system  ("mv $out ../temp/$ps_fil.pdf");
			}
			print "--> \n";
			if ($valg=='ip') {
				print "<!--!";
				system("lpr -P $ip ../temp/$ps_fil.pdf &");
				print "--> \n";
				if ($art=='PO') print "<meta http-equiv=\"refresh\" content=\"0;URL=../debitor/pos_ordre.php?id=$id\">";
				else print "<meta http-equiv=\"refresh\" content=\"0;URL=../debitor/ordre.php?id=$id\">";
  exit;
			} elseif ($udskriv_til=='fil') {
				$r=db_fetch_array(db_select("select * from ordrer where id='$id'",__FILE__ . " linje " . __LINE__));
				print "<meta http-equiv=\"refresh\" content=\"0;URL=../debitor/ordreliste.php?gem_id=$id&gem=../temp/$ps_fil.pdf&download=$r[kundeordnr]_$r[firmanavn].pdf\">";
#				print "<span>Højreklik og vælg 'Gem som'<a href='../temp/$ps_fil.pdf' download='$r[kundeordnr]_$r[firmanavn].pdf'>$r[kundeordnr]_$r[firmanavn].pdf</a></span>";
				exit;
			}
			print "<table width=100% height=100%><tbody>";
  		print "<td width=\"10%\" height=\"1%\" $top_bund><a href=\"udskriv.php?valg=tilbage&id=$id&art=$art\" accesskey=\"L\">Luk</a></td>";
			print "<td width=\"80%\" $top_bund align=\"center\" title=\"Klik her for at &aring;bne filen i nyt vindue, h&oslash;jreklik her for at gemme.\">";
			print "<a href=../temp/$ps_fil.pdf target=blank>Vis PDF udskrift</a>";
#			print "<a href=../temp/$ps_fil.htm target=blank>Vis HTML udskrift</a>";
			print "</td>";
  		print "<td width=\"10%\" $top_bund align = \"right\"title=\"Klik her for at &aring;bne filen i tiff format\"><a href=\"../temp/$ps_fil.tiff\">TIFF-version</a></td>";
			print "<tr><td width=100% height=99% align=\"center\" valign=\"middle\" colspan=\"3\"><iframe frameborder=\"0\" width=\"100%\" height=\"100%\" scrolling=\"auto\" src=\"../temp/$ps_fil.pdf\"></iframe></td></tr>";
			print "</tbody></table>";
			print exit;
		} else print "<BODY onload=\"javascript:alert('PDF-fil ikke fundet - er PS2PDF installeret?')\">";
	}
  if ($valg=="printer") {
    system ("$r[box1] ../temp/$ps_fil");
    print "<meta http-equiv=\"refresh\" content=\"0;URL=../includes/luk.php\">";
    exit;
  }
	if ($valg==-1)	$ps_fil="formularprint.php?id=$id&formular=$formular";
	else {
		$ip=$_SERVER['REMOTE_ADDR'];
		print "<!--!";
		system("lpq -P $ip", $tjek);
		print "-->";
		if ($tjek) $ip=NULL;
		$ps_fil="formularprint.php?id=$id&formular=3&udskriv_til=printer&ip=$ip";
}


  #xit;
}
print "<table width=\"100%\" height=\"75%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>";
print "<tr><td height=\"1%\" align=\"center\" valign=\"top\">";
print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\"><tbody>";
print "<td width=\"10%\" $top_bund><a href=\"udskriv.php?valg=tilbage\" accesskey=\"L\">Luk</a></td>";
print "<td width=\"80%\" $top_bund align=\"center\">Udskriftsvalg</td>";
print "<td width=\"10%\" $top_bund align = \"right\"><br></td>";
print "</tbody></table>";
print "</td></tr>";
print "<tr><td height=\"99%\" align = center valign = middle>";
print "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\"><tbody>";
print "<tr><td align=center> <a href='udskriv.php?valg=pdf&ps_fil=$ps_fil'>PDF</a></td></tr>";
print "<tr><td align=center> <a href='udskriv.php?valg=printer&ps_fil=$ps_fil'>Printer</a></td></tr>";
print "</tbody></table></td>";
print "</tbody></table>";
exit;

function historik($id,$filnavn) {
global $db;
global $bruger_id;
global $sprog_id;
global $exec_path;


	if (!file_exists("$filnavn")) {
	print "<BODY onload=\"javascript:alert('indl&aelig;sning af $filnavn fejlet')\">";
		return ('indl&aelig;sning af $filnavn fejlet');
	}
	$dd=date("Y-m-d");
	$r=db_fetch_array(db_select("select * from ordrer where id='$id'",__FILE__ . " linje " . __LINE__));
	$kundeordnr=$r['kundeordnr'];
	$konto_id=$r['konto_id'];
	$kontakt=$r['kontakt'];
	$ref=$r['ref'];
	$status=$r['status'];
	$art=$r['art'];

	if (!$status) $notat = findtekst(488,$sprog_id);
	elseif ($status==1 || $status==2) $notat = findtekst(489,$sprog_id);
	elseif ($art=='DO') $notat = findtekst(498,$sprog_id);
	else $notat = findtekst(499,$sprog_id);
	$tidspkt=date("H:i");
	$notat=str_replace('$time',$tidspkt,$notat);

	$r=db_fetch_array(db_select("select id from adresser where art='S'",__FILE__ . " linje " . __LINE__));
	$egen_id=$r['id']*1;

	$r=db_fetch_array(db_select("select * from ansatte where konto_id='$egen_id' and navn = '$ref'",__FILE__ . " linje " . __LINE__));
	$ansat_id=$r['id']*1;
	
	$r=db_fetch_array(db_select("select * from ansatte where konto_id='$konto_id' and navn = '$kontakt'",__FILE__ . " linje " . __LINE__));
	$kontakt_id=$r['id']*1;

	db_modify("insert into historik(konto_id,kontakt_id,ansat_id,notat,notedate,kontaktet) values ('$konto_id','$kontakt_id','$ansat_id','$notat','$dd','$dd')",__FILE__ . " linje " . __LINE__);
	$r=db_fetch_array(db_select("select id from historik where konto_id='$konto_id' and kontakt_id='$kontakt_id' and ansat_id='$ansat_id' and notat='$notat' and notedate='$dd'",__FILE__ . " linje " . __LINE__));
	$bilag_id=$r['id'];

	$r=db_fetch_array(db_select("select * from grupper where art='FTP'",__FILE__ . " linje " . __LINE__));
	$box1=$r['box1'];
	$box2=$r['box2'];
	$box3=$r['box3'];
	$mappe=$r['box5'];
	$undermappe="debitor_$konto_id";
	$ftpfilnavn="doc_".$bilag_id;

	$fp=fopen("../temp/$db/ftpscript.$bruger_id","w");
	if ($fp) {
		fwrite ($fp,"mkdir $mappe\ncd $mappe\nmkdir $undermappe\ncd $undermappe\nput $ftpfilnavn\nbye\n");
	}
	fclose($fp);
	$pdfnavn=$ftpfilnavn.".pdf";
	$kommando="cd \"../temp/$db\"\nrm \"$ftpfilnavn\"\nmv \"../$filnavn\" \"$ftpfilnavn\"\n$exec_path/ps2pdf \"$ftpfilnavn\"\n rm \"$ftpfilnavn\"\nmv \"$pdfnavn\" \"$ftpfilnavn\"\n$exec_path/ncftp ftp://".$box2.":".$box3."@".$box1." < ftpscript.$bruger_id > ftplog\nrm $ftpfilnavn\n";#rm ftpscript.$bruger_id";
	system ($kommando);
		$fp=fopen("../temp/$db/ftpscript.$bruger_id","w");
		if ($fp) {
			fwrite ($fp, "cd $mappe\ncd $undermappe\nget $ftpfilnavn\nbye\n");
		}
		fclose($fp);
		$kommando="cd ../temp/$db\n$exec_path/ncftp ftp://".$box2.":".$box3."@".$box1." < ftpscript.$bruger_id >> ftplog\n";
		system ($kommando);
		$langt_filnavn="../temp/$db/".$ftpfilnavn;
		if (file_exists($langt_filnavn)) {
			$tmp=explode("/",$filnavn);
			$filnavn=($tmp[count($tmp)-1]);
			$filnavn.=".pdf";
			$filnavn=db_escape_string($filnavn);
			db_modify("update historik set dokument='$filnavn' where id='$bilag_id'",__FILE__ . " linje " . __LINE__);
			$alerttekst=findtekst(490,$sprog_id);
			print "<BODY onload=\"javascript:alert('$alerttekst')\">";
		} else {
			$alerttekst=findtekst(506,$sprog_id);
			print "<BODY onload=\"javascript:alert('$alerttekst')\">";
		}
}
?>
