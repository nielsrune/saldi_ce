<?php
// ----------------------------------------------------------------------
// LICENS
//
// Dette program er fri software. Du kan gendistribuere det og / eller
// modificere it under betingelserne i GNU General Public License (GPL)
// som er udgivet af The Free Software Foundation; enten i version 2
// af denne licens eller en senere version efter eget valg dog med med
// flgende tilfjelse:
//
// Dette program er udgivet med h�b om at det vil v�re til gavn,
// men UDEN NOGEN FORM FOR REKLAMATIONSRET ELLER GARANTI. Se
// GNU General Public Licensen for flere detaljer.
//
// En dansk overs�ttelse af licensen kan l�ses her:
// http://www.fundanemt.com/gpl_da.html
//
// Copyright (c) 2004-2006 DANOSOFT ApS
// ----------------------------------------------------------------------

function dkdecimal($tal)
{
	#  $tal = round($tal,2);
	#  $tal = str_replace(".",",",$tal);
	#  if (!strstr($tal, ",")) {$tal = $tal . ",00";}
	#  if (substr($tal,-2,1) == ",") {$tal = $tal . "0";}
	# Der kan dog forekomme fejlafrundinger, men differencen er 
	# h�jst 1 �re - alts� hvor den runder op, hvor den skulle runde 
	# ned.
	$tal=number_format($tal,2,",",".");
	return $tal;
}
?>
