<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- includes/docsIncludes/docPool.php --- ver 4.1.0 --- 2024-03-29 ---
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
// Copyright (c) 2003-2024 Saldi.dk ApS
// ----------------------------------------------------------------------

function docPool($sourceId,$source,$kladde_id,$bilag,$fokus,$poolFile,$docFolder,$docFocus){
	global $bruger_id,$db,$exec_path;
	global $params,$regnaar,$sprog_id,$userId;
	
	$afd =  $beskrivelse = $debet = $dato = $fakturanr = $kredit = $projekt = $readOnly = $sag = $sum = NULL;

	(isset($_POST['unlink']) && $_POST['unlink'])?$unlink=1:$unlink=0;
	(isset($_POST['rename']) && $_POST['rename'])?$rename=1:$rename=0;
	(isset($_POST['unlinkFile']) && $_POST['unlinkFile'])?$unlinkFile=$_POST['unlinkFile']:$unlinkFile=NULL;
	
	$insertFile   = if_isset($_POST['insertFile'],NULL);
	$newFileName  = if_isset($_POST['newFileName'],NULL);
	$descFile     = if_isset($_POST['descFile'],NULL);
	
	$afd         = if_isset($_POST['afd'],NULL);
	$bilag       = if_isset($_POST['bilag'],$bilag);
	$beskrivelse = if_isset($_POST['beskrivelse'],NULL);
	$dato        = if_isset($_POST['dato'],NULL);
	$debet       = if_isset($_POST['debet'],NULL);
	$fakturanr   = if_isset($_POST['fakturanr'],NULL);
	$kredit      = if_isset($_POST['kredit'],NULL);
	$projekt     = if_isset($_POST['projekt'],NULL);
	$sag         = if_isset($_POST['sag'],NULL);
	$sum         = if_isset($_POST['sum'],NULL);

	if ($insertFile && $poolFile) {
		include ("docsIncludes/insertDoc.php");
#		Echo "Indsltter $poolFile<br>";
		exit;
	}
	if ($sourceId) {
		$qtxt = "select * from kassekladde where id = '$sourceId'";
		if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
			if (!$afd)         $afd         = $r['afd']; 
			if (!$bilag)       $bilag       = $r['bilag']; 
			if (!$beskrivelse) $beskrivelse = $r['beskrivelse']; 
			if (!$dato)        $dato        = dkdato($r['transdate']);
			if (!$debet)       $debet       = $r['debet']; 
			if (!$fakturanr)   $fakturanr   = $r['faktura']; 
			if (!$kredit)      $kredit      = $r['kredit']; 
			if (!$projekt)     $projekt     = $r['projekt']; 
			if (!$sag)         $sag         = $r['sag']; 
			if (!$sum)         $sum         = dkdecimal($r['amount']); 
		}
	}
	if ($rename && $newFileName && $newFileName != $poolFile) {
	  $legalChars = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','x','y','z');
		array_push($legalChars,'0','1','2','3','4','5','6','7','8','9','_','-','.','(',')');
		$nfn = trim($newFileName);
		$nfn = str_replace('æ','ae',$nfn);
		$nfn = str_replace('Æ','AE',$nfn);
		$nfn = str_replace('ø','oe',$nfn);
		$nfn = str_replace('Ø','OE',$nfn);
		$nfn = str_replace('å','aa',$nfn);
		$nfn = str_replace('Å','AA',$nfn);
		$newFileName = '';
		for ($x=0;$x<strlen($nfn);$x++) {

			$c1=substr($nfn,$x,1);
			$c2=strtolower($c1);
			if (!in_array($c2,$legalChars)) $c1 = '_';
			$newFileName.= $c1;
		}
		$tmpA = explode('.',$poolFile);
		if (count($tmpA) > 1) $ext = end($tmpA);
		else $ext = NULL;
		$newFileName= trim($newFileName,' ._');
		$tmpA = explode('.',$newFileName);
		if (count($tmpA) > 1) $newExt = end($tmpA);
		else $newExt = NULL;
		if (strtolower($ext) != strtolower($newExt)) $newFileName.= ".$ext";
		$newFileName= trim($newFileName,' ._');
		rename($docFolder."/$db/pulje/$poolFile",$docFolder."/$db/pulje/$newFileName");
		$poolFile = $newFileName;
	}
	if ($unlink && $unlinkFile) {
		if ($descFile) unlink("../".$docFolder."/$db/pulje/$descFile");
		if ($unlinkFile) unlink($unlinkFile);
		elseif (isset($_POST['poolFile'])) {
			$poolFile=if_isset($_POST['poolFile']);
#cho "slettter ../".$docFolder."/$db/pulje/$poolFile<br>";
			if ($poolFile) unlink("../".$docFolder."/$db/pulje/$poolFile");
		}
#exit;
		print "<meta http-equiv=\"refresh\" content=\"0;URL=../includes/documents.php?$params&openPool=1\">";
		exit;
	}
	if ($insertFile) {
				include("docsIncludes/uploadDoc.php");
		exit;
	}
	$r=db_fetch_array(db_select("select * from grupper where art='bilag'",__FILE__ . " linje " . __LINE__));
	$google_docs=$r['box7'];

#	if ($sourceId && $source == 'kassekladde') {
	if ($source == 'kassekladde' && $kladde_id) {
		$qtxt = "select bogfort from kladdeliste where id='$kladde_id'";
		$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		($r['bogfort'] != '-')?$readOnly=1:$readOnly=0;
	} elseif ($sourceId && $source == 'creditorOrder') {
		$qtxt = "select status from ordrer where id='$sourceId'";
		$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		($r['status'] >= '3')?$readOnly=1:$readOnly=0;
	}
	$dir=$docFolder."/".$db."/pulje";
	$url="://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
	$url=str_replace("/includes/documents.php","/temp/$db/pulje/",$url);
	if ($_SERVER['HTTPS']) $url="s".$url;
	$url="http".$url;
	if (!$poolFile) {
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) !== false) {
					if (substr($file,0,1)!='.' && substr($file,-5)!='.desc') {
						if (!$poolFile) $poolFile=$file;
					}
				}
				closedir($dh);
			}
		}
	}
	$poolParams =
	"openPool=1&".
	"docFolder=$docFolder&".
	"poolFile=$poolFile&".
	"fokus=$fokus&".
	"bilag=$bilag";
	print "<form name=\"gennemse\" action=\"documents.php?$params&$poolParams\" method=\"post\">\n";
	print "<tr><td width=15% height=\"70%\" align=center><table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"border: 3px solid rgb(180, 180, 255); padding: 0pt 0pt 1px;\"><tbody>\n";
	print "<tr><td width=100% align=center>\n";
	$fil_nr=0;
#cho "$sourceId<br>";
		if (is_dir($dir)) {
			$files = scandir($dir);
			sort($files);
			foreach ($files as $file) {
				if ($file != '.' && $file != '..' && substr($file,-5)!='.desc') {
# 				if (substr($file,0,1)!='.' && substr($file,-5)!='.desc') {
					if (substr($file,0,7) == '__UTF-8') {
						$newFile = trim($file,'_');
						$newFile = substr($newFile,5);
						if (substr($newFile,-4,1) != '.' && strtolower(substr($newFile,-3)) == 'pdf') {
							$newFile = str_replace('pdf','.pdf',$newFile);
						}
						$from = $docFolder."/".$db."/pulje/".$file;
						$to   = $docFolder."/".$db."/pulje/".$newFile;
						system ("mv '$from' '$to'\n");
						$file = $newFile;
					}
					if (strpos($file,' ')) {
						$newFile = str_replace (' ','_',$file);
						$from = $docFolder."/".$db."/pulje/".$file;
						$to   = $docFolder."/".$db."/pulje/".$newFile;
						system ("mv '$from' '$to'\n");
						$file = $newFile;
					}
					$ext = strtolower(substr($file,-4));
					if ( $ext != '.pdf' ) {
						if ($ext == 'html') {
							$newFile = str_replace('html','pdf',$file);
							$from = $docFolder."/".$db."/pulje/".$file;
							$to = $docFolder."/".$db."/pulje/".$newFile;
							system ("weasyprint -e UTF-8 $from $to");
							if (file_exists($to)) {
								unlink($from);
								$file = $newFile;
							}
						} elseif ($ext == 'ejpg' || $ext == '.jpg') {
							$newFile = str_replace(substr($file,-4),'.pdf',$file);
							$from = $docFolder."/".$db."/pulje/".$file;
							$to = $docFolder."/".$db."/pulje/".$newFile;
							system ("convert '$from' '$to'");
							if (file_exists($to)) {
								unlink($from);
								$file = $newFile;
							}
						}
					}
					($file == $poolFile)?$bgcolor='#aaaaaa':$bgcolor='#ffffff';
					$fil_nr++;
/*
					$hreftxt = "../includes/documents.php?funktion=gennemse&sourceId=$sourceId&source=$source";
					$hreftxt.= "&bilag=$bilag&sourceId=$sourceId&dato=$dato&fokus=$fil_nr&poolFile=$file ";
					$hreftxt.= "onfocus=\"document.forms[0].fokus.value=this.name;\" id=\"$fil_nr\"";
*/					
					$hreftxt = "../includes/documents.php?$params&$poolParams&docFocus=$fil_nr&poolFile=$file ";
#					$hreftxt.= "onfocus=\"document.forms[0].fokus.value=this.name;\" id=\"$fil_nr\"";
					print "<tr><td bgcolor=\"$bgcolor\">";
					print "<a href='$hreftxt' onfocus=\"document.forms[0].docFocus.value=this.name;\" id=\"$fil_nr\">$file</a></td></tr>\n";
				}
			}
#			closedir($dh);
		}
#	}
	if ($poolFile) {
		$tmp="../".$docFolder."/$db/pulje/$poolFile";
		if (!is_dir("../temp/$db/pulje")) mkdir("../temp/$db/pulje");
		system("cd ../temp/$db/pulje\nrm *\ncp $tmp .\n");
	} else {
		$ccalert= __line__." ".findtekst(1416, $sprog_id);
		print "<BODY onLoad=\"javascript:alert('$ccalert')\">\n";
		$tmp="documents.php?$params";
		print "<meta http-equiv=\"refresh\" content=\"0;URL=$tmp\">\n";
		exit;
	}
	print "</td></tr>\n";
	print "<tr><td width=100% align=center><br></td></tr>\n";
	print "</table></td>\n";
	print "<td rowspan=\"2\" width=85% height=\"100%\" align=center><table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"border: 3px solid rgb(180, 180, 255); padding: 0pt 0pt 1px;\"><tbody>";
	print	"<tr><td width=100% align=center>";
	$corrected = 0;
	$ext = pathinfo($poolFile, PATHINFO_EXTENSION);
	$fullName = "$docFolder/$db/pulje/$poolFile";
#	cho __line__." $fullName<br>";
	$descFile = $newName = str_replace($ext,'.desc',$fullName);
	if (strpos($fullName,'æ')) {
		$newName = str_replace('æ','ae',$fullName);
		$poolFile = str_replace('æ','ae',$poolFile);
		exec("mv \"$fullName\" \"$newName\"\n");
		$fullName = $newName ;
		$corrected = 1;
	}
	if (strpos($fullName,'ø')) {
		$newName = str_replace('ø','oe',$fullName);
		$poolFile = str_replace('ø','oe',$poolFile);
		exec("mv \"$fullName\" \"$newName\"\n");
		$fullName = $newName ;
		$corrected = 1;
	}
	if (strpos($fullName,'å')) {
		$newName = str_replace('å','aa',$fullName);
		$poolFile = str_replace('å','aa',$poolFile);
		exec("mv \"$fullName\" \"$newName\"\n");
		$fullName = $newName ;
		$corrected = 1;
	}

	if (strpos($fullName,'Æ')) {
		$newName = str_replace('Æ','AE',$fullName);
		$poolFile = str_replace('Æ','AE',$poolFile);
		exec("mv \"$fullName\" \"$newName\"\n");
		$fullName = $newName ;
		$corrected = 1;
	}
	if (strpos($fullName,'Ø')) {
		$newName = str_replace('Ø','OE',$fullName);
		$poolFile = str_replace('Ø','OE',$poolFile);
		exec("mv \"$fullName\" \"$newName\"\n");
		$fullName = $newName ;
		$corrected = 1;
	}
	if (strpos($fullName,'Å')) {
		$newName = str_replace('Å','AA',$fullName);
		$poolFile = str_replace('Å','AA',$poolFile);
		exec("mv \"$fullName\" \"$newName\"\n");
		$fullName = $newName ;
		$corrected = 1;
	}
/*
	if (strpos($fullName,'(')) {
		$newName = str_replace('(','_',$fullName);
		$poolFile = str_replace('(','_',$poolFile);
		exec("mv \"$fullName\" \"$newName\"\n");
		$fullName = $newName ;
		$corrected = 1;
	}
	if (strpos($fullName,')')) {
		$newName = str_replace(')','_',$fullName);
		$poolFile = str_replace(')','_',$poolFile);
		exec("mv \"$fullName\" \"$newName\"\n");
		$fullName = $newName ;
		$corrected = 1;
	}
*/
	if (strpos($fullName,'?')) {
		$newName = str_replace('?','_',$fullName);
		$poolFile = str_replace('ø','oe',$poolFile);
		exec("mv \"$fullName\" \"$newName\"\n");
		$fullName = $newName ;
		$corrected = 1;
	}
	if (!$ext) {
		$fileType = strtolower(file_get_contents($fullName, FALSE, NULL, 0, 4));
		if ($fileType == '%pdf') $newName = $fullName.'.pdf';
		else $newName = $fullName;
		$newName = str_replace('ø','oe',$newName);
		$poolFile = str_replace('ø','oe',$poolFile);
		exec("mv \"$fullName\" \"$newName\"\n");
		if (file_exists($newName) && $newName != $fullName) {
			$fullName = $newName ;
			if ($fileType == '%pdf') $poolFile = $poolFile.'.pdf';
			$corrected = 1;
		} else $corrected = 0;
	}
	if (strtolower($ext) != 'pdf') {
		$choices = array('bmp','jpg','jpeg','png','tif','tiff');
#		$tmp=str_replace($fullName);
		if (in_array(strtolower($ext),$choices)) {
			$fs = filesize($fullName);
			if ($fs > 500000) {
				$reduce = round (50000000 / $fs, 0);
				exec("$exec_path/mogrify -resize $reduce% $fullName");
			}
			$newName =  str_replace($ext,'pdf',$fullName);
			$tmp = str_replace($ext,'pdf',$poolFile);
			if (file_exists("$docFolder/$db/pulje/$tmp")) $poolFile = $tmp;
			exec("$exec_path/convert $fullName $newName");
			if (file_exists($newName))  {
				if (filesize($newName) > 10) {
					unlink($fullName);
					$fullName = $newName;
					$corrected = 1;
				} else {
					unlink($newName);
					$corrected = 0;
				}
			}
		}
	}
	if (!file_exists($fullName) && file_exists("$docFolder/$db/pulje/$poolFile")) {
		$fullName = "$docFolder/$db/pulje/$poolFile";
		$corrected = '0';
	}
#	if ($bruger_id == '-1') {
#		echo $corrected;
#		exit;
#	}
	if ($corrected == '1') {
		print "<meta http-equiv=\"refresh\" content=\"0;URL=../includes/documents.php?$params&openPool=1&poolFile=$poolFile\">";
	}
	if ($poolFile) {
		$ext = pathinfo($poolFile, PATHINFO_EXTENSION);
		if ($google_docs) $src="http://docs.google.com/viewer?url=$fullName&embedded=true";
		elseif($ext == "xml"){
			$apiKey = "6c772607-988c-4435-8d78-3670f4a0629d&d5610b95-e39d-4894-8a11-22eb350ed84e";
			$fileContent = file_get_contents($fullName);
			$data = [
				"language" => "",
				"base64EncodedDocumentXml" => base64_encode($fileContent)
			];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://easyubl.net/api/HumanReadable/HTMLDocument');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: ".$apiKey));
			$res = curl_exec($ch);
			curl_close($ch);
/* 			file_put_contents("../temp/$db/pulje/$poolFile.html", $res);
			$src = "../temp/$db/pulje/$poolFile.html"; */
			echo "<div style='width: 80%; margin:2rem auto;'>$res</div>";
		}
		else{ 
		$src=$tmp;
		print "<iframe style=\"width:100%;height:100%\" src=\"$fullName\" frameborder=\"0\">";
		print "</iframe></td></tr>\n";
	}
	}
	print "</tbody></table></td></tr>\n";
	print "<tr><td><table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"border: 3px solid rgb(180, 180, 255); padding: 0pt 0pt 1px;\"><tbody>";
	$tmp="../".$dir."/".$descFile.".desc";
	if (file_exists($tmp)) {
		system("cd ../temp/$db/pulje\ncp $tmp .\n");
		$fp=fopen("../temp/$db/pulje/$descFile.desc","r");
		while($linje=trim(fgets($fp))) {
			if (strtolower(substr($linje,0,6))=='bilag:') $bilag=trim(substr($linje,6));
			if (strtolower(substr($linje,0,5))=='dato:') $dato=trim(substr($linje,5));
			if (strtolower(substr($linje,0,12))=='beskrivelse:') $beskrivelse=trim(substr($linje,12));
			if (strtolower(substr($linje,0,6))=='debet:') $debet=trim(substr($linje,6));
			if (strtolower(substr($linje,0,7))=='kredit:') $kredit=trim(substr($linje,7));
			if (strtolower(substr($linje,0,10))=='fakturanr:') $fakturanr=trim(substr($linje,10));
			if (strtolower(substr($linje,0,4))=='sum:') $sum=trim(substr($linje,4));
			if (strtolower(substr($linje,0,4))=='sag:') $sag=trim(substr($linje,4));
			if (strtolower(substr($linje,0,4))=='afd:') $afd=trim(substr($linje,4));
			if (strtolower(substr($linje,0,8))==='projekt:') $projekt=trim(substr($linje,8));
		}
	}
	if ($source == 'kassekladde' && !$bilag && $bilag!='0') {
		$r=db_fetch_array(db_select("select max(bilag) as bilag from kassekladde where kladde_id='$sourceId'",__FILE__ . " linje " . __LINE__));
		$bilag=$r['bilag']+1;
	}
	
	if (!$dato) $dato=date("d-m-Y");
	print "<tr><td>Filnavn</td><td><input type=\"text\" style=\"width:150px\"
	name=\"newFileName\" value=\"$poolFile\"</td></tr>\n";
	print "<tr><td colspan=\"2\"><input style=\"width:100%\" type=\"submit\"
	name=\"rename\" value=\"Ret filnavn\"</tr>\n";
	print "<tr><td colspan=\"2\"><input style=\"width:100%\" type=\"submit\"
	name=\"insertFile\" value=\"".findtekst(1415, $sprog_id)."\"</tr>\n";
	print "<tr><td colspan=\"2\"><input style=\"width:100%\" type=\"submit\"
	name=\"unlink\" value=\"".findtekst(1099, $sprog_id)."\"</tr>\n";
	print "<tr><td>Bilag&nbsp;</td>";
	if ($readOnly) print "<td>$bilag</td><tr>";
	else print "<td><input type=\"text\" style=\"width:150px\" name=\"bilag\" value=\"$bilag\"</td></tr>\n";
	print "<tr><td>Dato&nbsp;</td>";
	if ($readOnly) print "<td> $dato</td><tr>";
	else print "<td><input type=\"text\" style=\"width:150px\" name=\"dato\" value=\"$dato\"</td></tr>\n";
	print "<tr><td>Beskrivelse&nbsp;</td>";
	if ($readOnly) print "<td> $beskrivelse</td><tr>";
	else print "<td><input type=\"text\" style=\"width:150px\" name=\"beskrivelse\" value=\"$beskrivelse\"</td></tr>\n";
	print "<tr><td>Debet&nbsp;</td>";
	if ($readOnly) print "<td> $debet</td><tr>";
	else print "<td><input type=\"text\" style=\"width:150px\" name=\"debet\" value=\"$debet\"</td></tr>\n";
	print "<tr><td>Kredit&nbsp;</td>";
	if ($readOnly) print "<td> $kredit</td><tr>";
	else print "<td><input type=\"text\" style=\"width:150px\" name=\"kredit\" value=\"$kredit\"</td></tr>\n";
	print "<tr><td>Fakturanr&nbsp;</td>";
	if ($readOnly) print "<td> $fakturanr</td><tr>";
	else print "<td><input type=\"text\" style=\"width:150px\" name=\"fakturanr\" value=\"$fakturanr\"</td></tr>\n";
	 print "<tr><td>Sum&nbsp;</td>";
	if ($readOnly) print "<td> $sum</td><tr>";
	else print "<td><input type=\"text\" style=\"width:150px\" name=\"sum\" value=\"$sum\"</td></tr>\n";
	print "<tr><td>Sag&nbsp;</td>";
	if ($readOnly) print "<td> $sag</td><tr>";
	else print "<td><input type=\"text\" style=\"width:150px\" name=\"sag\" value=\"$sag\"</td></tr>\n";
	print "<tr><td>Afd&nbsp;</td>";
	if ($readOnly) print "<td> $afd</td><tr>";
	else print "<td><input type=\"text\" style=\"width:150px\" name=\"afd\" value=\"$afd\"</td></tr>\n";
	print "<tr><td>Projekt&nbsp;</td>";
	if ($readOnly) print "<td> $projekt</td><tr>";
	else print "<td><input type=\"text\" style=\"width:150px\" name=\"projekt\" value=\"$projekt\"</td></tr>\n";
	print "</tbody></table></td></tr>\n";
	print "<input type=\"hidden\" style=\"width:150px\" name=\"unlinkFile\" value=\"$fullName\"</td></tr>\n";
	print "<input type=\"hidden\" style=\"width:150px\" name=\"descFile\" value=\"$descFile\"</td></tr>\n";
	print "</form>";
	print "<script language=\"javascript\">";
	print "document.gennemse.$docFocus.focus();";
	print "</script>";
	exit;

} # endfunc gennemse
?>
