<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- lager/productCardIncludes/getChildRetailPrice.php --- lap 4.1.1 --- 2024-10-28 ---
// LICENS
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
// Copyright (c) 2024-2024 saldi.dk aps
// ----------------------------------------------------------------------
// This routine is called from productCardIncludes/showPrice.php if product is a 'samlevare' (made by other products) 
// it calculates the retail price and updates the product if different from existing retail price.

    $retailPrice = 0;
		$qtxt = "select varer.retail_price,styklister.antal from styklister,varer where styklister.indgaar_i = '$id' ";
		$qtxt.= "and varer.id = styklister.vare_id";
		$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
    while ($r = db_fetch_array($q)) {
      $retailPrice += $r['retail_price']*$r['antal'];
		}
		if ($retailPrice != $retail_price) {
			$retail_price = $retailPrice;
			$qtxt = "update varer set retail_price = '$retail_price' where id = '$id'";
			db_modify($qtxt,__FILE__ . " linje " . __LINE__);
		}
?>
