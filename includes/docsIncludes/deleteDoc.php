<?php
// --- includes/docsIncludes/deleteDoc.php --- patch 4.0.6------2022.03.05---
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
if ($deleteDoc) {
	$tmpA = explode("/",$deleteDoc);
	$x    = count($tmpA)-1;
	$fileName = $tmpA[$x];
	$qtxt = "delete from documents where source = '$source' and source_id = '$sourceId' and filename = '".db_escape_string($fileName)."'";
	db_modify($qtxt,__FILE__ . " linje " . __LINE__);
	unlink($deleteDoc);
	$deleteDoc = $showDoc = NULL;
}
?>
