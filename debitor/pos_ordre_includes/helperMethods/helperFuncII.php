<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ---- debitor/pos_ordre_includes/helperMethods/helperFuncII.php ---- lap 3.8.9----2020.02.11-------
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
// Copyright (c) 2003-2020 saldi.dk aps
// ----------------------------------------------------------------------
//
// 20190312 LN Make various helper functions for the pos_ordre and the report files
// 20190319 LN Added new function to return a unque box id
// 20200211	PHR	function getUniqueBoxId. Corrected function as it destroyed the global $db var.

function getVatArray($linjeantal, $dkkpris, $vatArray) {
	$vatRate = array();
	for($x=1; $x<=$linjeantal; $x++) { # 20190123 LN
		$price = $dkkpris[$x];
		$price = floatval(str_replace(',', '.', str_replace('.', '', $price)));
		$vatPercentage = $vatArray[$x];
		$vat = $price / 100 * $vatPercentage; 
		$vatRate[$vatPercentage]['percentage'] = truncate_number($vatPercentage);
		if (!isset($vatRate[$vatPercentage]['base']))  $vatRate[$vatPercentage]['base']  = 0; 
		if (!isset($vatRate[$vatPercentage]['vat']))   $vatRate[$vatPercentage]['vat']   = 0; 
		if (!isset($vatRate[$vatPercentage]['total'])) $vatRate[$vatPercentage]['total'] = 0; 
		$vatRate[$vatPercentage]['base'] += $price - $vat;
		$vatRate[$vatPercentage]['vat'] += $vat;
		$vatRate[$vatPercentage]['total'] += truncate_number($price); 
	}
	ksort($vatRate);
	return $vatRate;
}

function makeOrderIdArray($kasse,$date) {	
	$qtxt = "select id from ordrer where felt_5 = '$kasse' and fakturadate = '$date'"; 
	$orderQuery = db_select($qtxt, __FILE__ . "linje" . __LINE__);
	while ($order = db_fetch_array($orderQuery)) {
		$ordreIdArr[] = $order['id'];
	}
	ksort($ordreIdArr);
	return $ordreIdArr;
}

function getUniqueBoxId($kasse) {
	global $db,$db_id;
/*	$db = (int) filter_var($db, FILTER_SANITIZE_NUMBER_INT);
	$uri = $_SERVER['REQUEST_URI'];
	if (strpos($uri, "bizbeta")) {
		$uniqueShopId = "BI-";
	} elseif (strpos($uri, "grillbar")) {
		$uniqueShopId = "GR-";
	} else {
		$uniqueShopId = "BI-";
	}
	$uniqueShopId .= "$db-"; */
	$uniqueShopId = substr($db,0,2) . "-$db-";
	if ($kasse < 10) {
		$uniqueShopId .= sprintf("%02d", $kasse);
	} else {
		$uniqueShopId .= $kasse;
	}
	return $uniqueShopId;
}



?>

