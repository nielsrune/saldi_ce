<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// ---- debitor/pos_ordre_includes/voucherFunc/useVoucher.php --- lap 3.9.9 --- 2021.01.25 ---
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
// but WITHOUT ANY KIND OF CLAIM OR WARRANTY. See
// GNU General Public License for more details.
//
// Copyright (c) 2021 saldi.dk aps
// --------------------------------------------------------------------------

#function handleGiftcard($orderId) {
if (!function_exists('useVoucher')) {
function useVoucher($orderId, $voucherName) {
	$voucherName = str_replace('på beløb','',$voucherName); 
	
	$gkNb = $_POST['giftcardNumber']*1;
	$q = db_select("select id from voucher where barcode='$gkNb'", __FILE__ . "linje" . __LINE__);
	$r=db_fetch_array($q);
	if (!$voucherId = $r['id']) {
		#alert("Der eksisterer ikke et gavekort med nummeret " . $gkNb . ", gk id: " . $voucherId);
		alert("$voucherName nr " . $gkNb ." eksisterer ikke");
		$_COOKIE['giftcard'] = false;
		return 0;
		exit;
	} else {
#cho	print_r($_POST);
		$price = str_replace('q','',$_POST['price']);
		$sum = $_POST['sum'];
/*
		$qtxt = "select sum (amount*valutakurs/100) as paid from pos_betalinger where ordre_id='$orderId'";
		$r=db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__));
		$leftToPay = $_POST['sum'] - $r['paid'];
*/
#cho "$price=$sum<br>";
#		if (!$price) $price=$sum;
		
		$qtxt="select sum (amount) as amount, sum (vat) as vat from voucheruse where voucher_id='$voucherId'";
#cho "$qtxt<br>";
		$r = db_fetch_array(db_select($qtxt, __FILE__ . " linje " . __LINE__));
		$amount = $r['amount']*1;
		$vat    = $r['vat']*1;
		$newAmount = $amount + $vat - $price;
#cho "$newAmount = $amount + $vat - $price<br>";
		if ($newAmount < 0 && $gkNb) {
			$tmp = $amount+$vat;
			#alert("Der står ikke nok på gavekortet, amount: " . $amount . ", pris: " . $price . ", gk id: " . $voucherId);
			alert("Der står ikke nok på $voucherName #$gkNb, saldo: " . $tmp );
			$_COOKIE['giftcard'] = false;
#			print "<meta http-equiv=\"refresh\" content=\"0;URL=pos_ordre.php?id=$id\">\n";
#			exit;
		} elseif ($gkNb) {
			#alert("Betalingen bliver gennemført, pris: " . $price . ", gavekort beløb: " . $amount . ", gk id: " . $voucherId);
#			alert("Betalingen bliver gennemført");
			$subAmount = -1 * $price;
			($vat)?$subVat = afrund($subAmount / (($amount+$vat)/$vat),2):$subVat = 0;
#cho "$vat -> $subVat<br>";			
			$subAmount-= $subVat;
			if ($subAmount) {
				$qtxt = "insert into voucheruse (voucher_id, order_id, amount, vat) values ('$voucherId', '$orderId', '$subAmount', '$subVat')";
#cho "$qtxt<br>";
				db_modify($qtxt, __FILE__ . " linje " . __LINE__);
			#db_modify("update voucheruse set amount='$newAmount' where voucher_id='$voucherId'", __FILE__ . "linje" . __LINE__);
				$_COOKIE['giftcard'] = true;
				return $price;
			} else $_COOKIE['giftcard'] = false;
		}
	}
	return $price;
}}
?>
