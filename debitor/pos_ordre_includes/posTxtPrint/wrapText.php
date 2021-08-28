<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- debitor/pos_ordre_includes/posTxtPrint/wrapText.php -- lap 3.9.3 -- 2020.09.05 ---
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
// Copyright (c) 2003-2020 saldi.dk aps
// ----------------------------------------------------------------------

if (!function_exists('wrapText')) {
	function wrapText($txt,$w,$preTxt,$postTxt,$filler=" ") {
		global $db;

		$leftover=$txt;
		$txts=explode(' ',$txt);
		$txt=$txts[0];
#	$leftover='';
		for ($t=1;$t<count($txts);$t++) {
			if ((strlen("$preTxt $txt$txts[$t]$postTxt") < $w)) $txt.=" $txts[$t]";
			else break 1;
		}
		$leftover=str_replace($txt,'',$leftover);
		while(strlen("$preTxt$txt$postTxt") < $w) {
			$txt.=$filler;
		}
		if (strlen("$preTxt$txt$postTxt") > $w) {
			$xw=strlen("$preTxt$txt$postTxt")-$w;
			$txt=substr($txt,0,$xw);
		}
		return array("$txt","$leftover");
	}
}
?>
