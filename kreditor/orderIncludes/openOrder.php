<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- kreditor/orderIncludes/openOrders.php --- lap 4.0.5 --- 2022.02.18 ---
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
// Copyright (c) 2003-2022 saldi.dk aps
// ----------------------------------------------------------------------

print "<!-- BEGIN orderIncludes/openOrders.php -->";
print "<table cellpadding='1' cellspacing='0' bordercolor='#ffffff' border='1' valign = 'top' width=80%'><tbody>";
include ("orderIncludes/openOrderData.php");
$x=0;
$qtxt = "select * from ordrelinjer where ordre_id = '$id' order by posnr";
$query = db_select($qtxt,__FILE__ . " linje " . __LINE__);
while ($row = db_fetch_array($query))	{
	if ($row['posnr']>0) {
		$x++;
		$linje_id[$x]=$row['id'];
		$kred_linje_id[$x]=$row['kred_linje_id'];
		$posnr[$x]=$row['posnr'];
		$varenr[$x]=trim($row['varenr']);
		$lev_varenr[$x]=trim($row['lev_varenr']);
		$beskrivelse[$x]=trim($row['beskrivelse']);
		$pris[$x]=$row['pris'];
		$rabat[$x]=$row['rabat'];
		$antal[$x]=$row['antal'];
		$leveres[$x]=$row['leveres'];
		$enhed[$x]=$row['enhed'];
		$vare_id[$x]=$row['vare_id'];
		$momsfri[$x]=$row['momsfri'];
		$projekt[$x]=$row['projekt'];
		$serienr[$x]=$row['serienr'];
		$samlevare[$x]=$row['samlevare'];
		($row['omvbet'])?$omvbet[$x]='checked':$omvbet[$x]='';
	}
}
$linjeantal=$x;
print "<input type='hidden' name='linjeantal' value='$linjeantal'>";
$sum=0;
#if ($status==1){$status=2;}

print "<!-- END orderIncludes/openOrders.php -->";

?>
	
