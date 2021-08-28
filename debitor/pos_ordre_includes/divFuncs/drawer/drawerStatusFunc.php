<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- debitor/pos_ordre_includes/divFuncs/drawer/drawerStatusFunc.php --- lap 3.9.9----2021.01.10-------
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
// Copyright (c) 2019-2021 saldi.dk aps
// ----------------------------------------------------------------------
//
// 20190708 LN Check drawer status and tell to close if open
// 20201010 PHR function getDrawerStatus - added: ?returside=$returside

function getDrawerStatus() {
		$printserver = getPrintserver();
		$drawerStatusLocation = 'http://' . $printserver . '/drawerstatus.php';

		$url="://".$_SERVER['SERVER_NAME']. $_SERVER['PHP_SELF'];
		$url=str_replace("/debitor/pos_ordre.php","",$url);
		if ($_SERVER['HTTPS']) $url="s".$url;
		$url="http".$url;
		$returside=$url."/debitor/pos_ordre.php";

		//print "<meta http-equiv=\"refresh\" content=\"0; URL=$drawerStatusLocation\">\n";
		print "<meta http-equiv=\"refresh\" content=\"0;URL=http://$printserver/drawerstatus.php?returside=$returside\">\n";
		usleep(10000);
}

function preDrawerCheck() {
		usleep(250000);
		if(getCountry() == "Norway") {
				if (isset($_POST['betaling']) || (isset($_POST['skuffe']) && $_POST['skuffe'] == 'Skuffe')) {
				    $_SESSION['drawerstatus'] = true;
				} elseif (isset($_SESSION['drawerstatus']) && $_SESSION['drawerstatus'] == true) {
					session_destroy();
					getDrawerStatus();
				} elseif (isset($_GET['drawerstatus']) && $_GET['drawerstatus'] == 0) {
					unset($_GET['drawerstatus']);
					 alert("LUK SKUFFEN");
					 getDrawerStatus();
				}
		}
}

 function getPrintserver() {
	 $r = db_fetch_array(db_select("select * from grupper where art = 'POS' and kodenr = '2'",__FILE__ . " linje " . __LINE__));
	 $printer_ip=explode(chr(9),$r['box3']);
	 $printserver=$printer_ip[0];
	 return $printserver;
 }

?>
