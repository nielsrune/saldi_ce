<?php
// --- includes/docsIncludes/uploadDoc.php --- patch 4.0.6------2022.03.05---
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
if (isset($_FILES) && isset($_FILES['uploadedFile']['name'])) {
	$fileTypes = array('jpg','pdf','png');
	$fileName = basename($_FILES['uploadedFile']['name']);
	list($tmp,$fileType) = explode("/",$_FILES['uploadedFile']['type']);
	if (!in_array(strtolower($fileType),$fileTypes)) {
		alert ("File must be either pdf or jpg, not $fileType");
		$fileType = NULL;
	}
	$qtxt = "select var_value from settings where var_name = 'globalId'";
	if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) $globalId = $r['var_value'];
	else alert ('Missing global ID');
	if ($fileType && $docFolder && $globalId && $source == 'creditorOrder') {
		if (!file_exists("$docFolder/$globalId"))                 mkdir ("$docFolder/$globalId",0777);
		if (!file_exists("$docFolder/$globalId/creditor"))        mkdir ("$docFolder/$globalId/creditor",0777);
		if (!file_exists("$docFolder/$globalId/creditor/orders")) mkdir ("$docFolder/$globalId/creditor/orders",0777);
		$tmp = floor($sourceId/1000)*1000;
		$tmp2 = $tmp+1000;
		$filePath = "$globalId/creditor/orders/".$tmp."-".$tmp2;
 		if (!file_exists("$docFolder/$filePath")) mkdir ("$docFolder/$filePath",0777);
 		if (!file_exists("$docFolder/$filePath/$fileName")) {
			if(move_uploaded_file($_FILES['uploadedFile']['tmp_name'],"$docFolder/$filePath/$fileName")) {
				$qtxt = "insert into documents(global_id,filename,filepath,source,source_id,timestamp,user_id) values ";
				$qtxt.= "('$globalId','$fileName','$filePath','$source','$sourceId','". date('U') ."','$userId')";
				db_modify($qtxt,__FILE__ . " linje " . __LINE__);
				$showDoc = "$docFolder/$filePath/$fileName";
			} else alert("Upload to $docFolder/$filePath/$fileName failed");
		} else alert("$docFolder/$filePath/$fileName allready exists");
	} 
	$showDoc = "$docFolder/$filePath/$fileName";
	if (file_exists($showDoc)) {
		print "<meta http-equiv=\"refresh\" content=\"0;URL=documents.php?source=creditorOrder&sourceId=$sourceId&showDoc=$showDoc\">";
		exit;
	} else alert("Upload to $docFolder/$filePath/$fileName failed");
}

print "<tr><td width='100%' valign = 'top' align='center'>";
print "<form enctype='multipart/form-data' action='documents.php?source=$source&sourceId=$sourceId&fokus=$fokus&showDoc=".urlencode($showDoc)."' method='POST'>";
print "<input type='hidden' name='MAX_FILE_SIZE' value='100000000'>";
print findtekst(1414, $sprog_id).":<br><br><input class='inputbox' name='uploadedFile' type='file' accept='.pdf,.jpg,.png'><br><br>";
print "<input type='submit' value='".findtekst(1078, $sprog_id)."'>";
print "</form>";
print "</td></tr>";

?>
