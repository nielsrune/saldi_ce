<?php
// --- includes/documents.php --- patch 4.0.6------2022.04.14---
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
// Copyright (c) 2010-2022 Saldi.dk ApS
// ----------------------------------------------------------------------

@session_start();
$s_id=session_id();
$css="../css/std.css";

$title="Documents";
include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");

if (!isset($userId) || !$userId) $userId = $bruger_id;

print "<div align=\"center\">";

if(($_GET)||($_POST)) {

	$funktion=if_isset($_GET['funktion']);
	if (isset($_GET['sourceId'])) {
		$fokus		= $_GET['fokus'];
		$sourceId = $_GET['sourceId'];
		$source   = if_isset($_GET['source']);
		$showDoc  = if_isset($_GET['showDoc']);
		$deleteDoc  = if_isset($_GET['deleteDoc']);
	} 
	if (isset($_POST['sourceId'])) {
		$sourceId  = $_POST['sourceId'];
		$source    = $_POST['source'];
	}
}
if (isset($_GET['test'])) exit;

$qtxt = "select var_value from settings where var_name = 'globalId'";
if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) $globalId = $r['var_value'];
else alert ('Missing global ID');
/*
$qtxt = "select * from grupper where art='bilag'";	
if ($r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
	$box1=$r['box1'];
	$box2=$r['box2'];
	$box3=$r['box3'];
	$box4=$r['box4'];
	$box6=$r['box6'];
	if ($box1 && $box2 && $box3 && $box4 && !$box6) {
	$box4 = trim("/",$box4);
		if (!file_exists("../temp/$db/ftp")) mkdir ("../temp/$db/ftp", 0777, true);
	if (!file_exists("../temp/$db/ftp/$globalId")) {
			mkdir ("../temp/$db/ftp/$globalId", 0777, true);
			system ("/usr/bin/curlftpfs $box2:$box3@$box1 ../temp/$db/ftp/$globalId");
		}
	}
}
*/
#if (file_exists("../temp/$db/ftp/$globalId")) $docFolder = "../temp/$db/ftp";
if (file_exists('../owncloud')) $docFolder = '../owncloud';
elseif (file_exists('../bilag')) $docFolder = '../bilag';
elseif (file_exists('../documents')) $docFolder = '../documents';

// ---------- Main table start ---------
print "<table width=\"100%\" height=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>"; 
print "<tr><td colspan= \"3\" height = \"25\" align=\"center\" valign=\"top\">";
// ---------- Header table start ---------
print "<table width=\"100%\" align=\"center\" border=\"0\" cellspacing=\"2\" cellpadding=\"0\"><tbody>";
include("docsIncludes/header.php");
// ---------- Header table end ---------
print "</tbody></table>";
print "</td></tr><tr><td width = '20%'>";
// ---------- Left table start ---------
print "<table width=\"100%\" height=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tbody>";
if ($deleteDoc) include("docsIncludes/deleteDoc.php");
include("docsIncludes/listDocs.php");
include("docsIncludes/uploadDoc.php");
// ---------- Left table end ---------
print "</tbody></table>";
print "</td><td width = '80%'>";
// ---------- Right table start ---------
print "<table width=\"100%\" height=\"98%\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tbody>";
include("docsIncludes/showDoc.php");
// ---------- Right table end ---------
print "</tbody></table>";
print "</td></tr>";
// ---------- Main table start ---------
print "</tbody></table>";
print "</body></html>";

?>
