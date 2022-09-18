<?php
// --- includes/docsIncludes/listDocs.php --- patch 4.0.6------2022.05.10---
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
// 20220510 PHR Not attatchments from not invioced orders can now be deleted. 

print "<tr><td valign='top' align = 'center'>";
$qtxt = "select filename,filepath from documents where source = '$source' and source_id = '$sourceId' order by id";
$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
while ($r=db_fetch_array($q)) {
	if (!$showDoc) {
		$fileName = $r['filename'];
		$showDoc  = $docFolder."/".$r['filepath']."/$fileName";
	} else {
		$tmpA = explode("/",$showDoc);
		$x = count($tmpA)-1;
		$fileName = $tmpA[$x];
	}
	print "<a href = 'documents.php?source=$source&sourceId=$sourceId&fokus=$fokus&showDoc=".urlencode("$docFolder/$r[filepath]/$r[filename]")."'>";
	print "<button style = 'width:90%;height:35px;'>$r[filename]</button></a></br><br>";
}
$locked = 0;
if ($source == 'creditor') {
	$qtxt = "select status from ordrer where id = '$sourceId'";
	$r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)); 
	($r['art'] >= '3')?$locked='1':$locked='0'; 
}
$qtxt = "select art from documents where source = '$source' and source_id = '$sourceId'";
$qtxt.= "and filename = '".db_escape_string($fileName)."'";
$qtxt = "select timestamp from documents where source = '$source' and source_id = '$sourceId'";
$qtxt.= "and filename = '".db_escape_string($fileName)."'";
if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
	if ($locked == 0 || date('U') - $r['timestamp'] < 60*60*24) {
		print "</td></tr>";
		print "<tr><td valign='top' align = 'center'></br>";
		print "<a href = 'documents.php?source=$source&sourceId=$sourceId&fokus=$fokus&deleteDoc=".urlencode($showDoc)."' onclick=\"return onfirm('Slet $fileName?')\">";
		print "<button style = 'width:90%;height:35px;'>Delete $fileName</button></a></br>";
		print "</td></tr>";
	}
}
?>
