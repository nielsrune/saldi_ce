<?php
// --- includes/docsIncludes/listDocs.php --- patch 4.0.6------2022.03.05---
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

print "<tr><td width='100%' height='100%' align='center' valign='middle'>";
if (strtolower(substr($showDoc,-3,3))=='pdf') {
	print "<iframe frameborder='no' width='100%' height='100%' scrolling='auto' src='$showDoc'></iframe>";
} else print "<img src='$showDoc' style='max-width:100%;height:auto;'>";
print "</td></tr>";

?>
