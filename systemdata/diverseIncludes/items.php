<?php

function items($defaultProvision) {
	global $sprog_id;
	global $bgcolor;
	global $bgcolor5;
	global $db;
	global $labelprint;

	$customerCommissionAccountUsed = $customerCommissionAccountUsedId = NULL;
	$customerCommissionAccountNew = $customerCommissionAccountNewId = NULL;
	$confirmDescriptionChange = $confirmDescriptionChange_id = $confirmStockChange = $confirmStockChange_id = NULL;
	$commissionAccountUsed = $commissionAccountUsedId = $commissionFromDate = $DisItemIfNeg = $DisItemIfNeg_id = NULL;
	$ownCommissionAccountNew = $ownCommissionAccountNewId = $ownCommissionAccountUsed = $ownCommissionAccountNewUsed = NULL;
	$useCommission = $useCommission_id = $vatOnItemCard = $vatOnItemCard_id = NULL;
	
	
	
	db_modify("update settings set var_grp = 'items' where var_grp='varer'",__FILE__ . " linje " . __LINE__);
	$qtxt="select id from grupper WHERE art = 'DIV' and kodenr='5'";
	($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)))?$id=$r['id']:$id=0;
	$qtxt="select id,var_value from settings where var_name = 'vatOnItemCard' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$vatOnItemCard_id=$r['id'];
		if ($r['var_value']) $vatOnItemCard='checked';
	}
	/*
	if (!$vatOnItemCard_id) { // remove this after rel 3.7.6
		$q = db_select("select * from grupper where art = 'DIV' and kodenr = '5'",__FILE__ . " linje " . __LINE__);
		# OBS $box2,3,4,5,7,9 bruges under shop valg!!
		# OBS $box8 bruges under ordrelaterede valg!!
		$r = db_fetch_array($q);
		$id=$r['id'];$beskrivelse=$r['beskrivelse'];$kodenr=$r['kodenr'];$box1=trim($r['box1']);
		($box1)?$vatOnItemCard_id='checked':$vatOnItemCard_id=NULL;
	}
	*/
	$qtxt="select id,var_value from settings where var_name = 'DisItemIfNeg' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$DisItemIfNeg_id=$r['id'];
		if ($r['var_value']) $DisItemIfNeg='checked';
	}
	$qtxt="select id,var_value from settings where var_name = 'confirmDescriptionChange' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$confirmDescriptionChange_id=$r['id'];
		if ($r['var_value']) $confirmDescriptionChange='checked';
	} 
	$qtxt="select id,var_value from settings where var_name = 'confirmStockChange' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$confirmStockChange_id=$r['id'];
		if ($r['var_value']) $confirmStockChange='checked';
	}
	$qtxt="select id,var_value from settings where var_name = 'useCommission' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$useCommissionId=$r['id'];
		if ($r['var_value']) $useCommission='checked';
	}
	$qtxt="select id,var_value from settings where var_name = 'commissionAccountNew' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$commissionAccountNewId=$r['id'];
		$commissionAccountNew=$r['var_value'];
	}
	$qtxt="select id,var_value from settings where var_name = 'commissionAccountUsed' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$commissionAccountUsedId=$r['id'];
		$commissionAccountUsed=$r['var_value'];
	}
	$qtxt="select id,var_value from settings where var_name = 'customerCommissionAccountNew' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$customerCommissionAccountNewId=$r['id'];
		$customerCommissionAccountNew=$r['var_value'];
	}
	$qtxt="select id,var_value from settings where var_name = 'customerCommissionAccountUsed' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$customerCommissionAccountUsedId=$r['id'];
		$customerCommissionAccountUsed=$r['var_value'];
	}
	$qtxt="select id,var_value from settings where var_name = 'ownCommissionAccountNew' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$ownCommissionAccountNewId=$r['id'];
		$ownCommissionAccountNew=$r['var_value'];
	}
	$qtxt="select id,var_value from settings where var_name = 'ownCommissionAccountUsed' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$ownCommissionAccountUsedId=$r['id'];
		$ownCommissionAccountUsed=$r['var_value'];
	}
	$qtxt="select id,var_value from settings where var_name = 'commissionFromDate' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$commissionFromDate=dkdato($r['var_value']);
	}
	$qtxt="select id,var_value from settings where var_name = 'defaultCommission' and var_grp = 'items'";
	if ($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__))) {
		$defaultCommissionId = $r['id'];
		$defaultCommission   = $r['var_value'];
	}
	
	$qtxt="select var_value from settings where var_name = 'numberFormat' and var_grp = 'localization'";
	($r = db_fetch_array(db_select($qtxt,__FILE__ . " linje " . __LINE__)))?$numberFormat = $r['var_value']:$numberFormat = '.|,';
	print "<form name='vare_valg' action='diverse.php?sektion=vare_valg' method='post'>";
	print "<tr><td colspan='6'><hr></td></tr>";
	$text=findtekst(470,$sprog_id);
	print "<tr bgcolor='$bgcolor5'><td colspan='6'><b><u>$text<!--tekst 470--></u></b></td></tr>";
	print "<tr><td colspan='6'><br></td></tr>";
	print "<input type=hidden name='id' value='$id'>";
	print "<input type=hidden name='vatOnItemCard_id' value='$vatOnItemCard_id'>";
	print "<input type=hidden name='confirmDescriptionChange_id' value='$confirmDescriptionChange_id'>";
	print "<input type=hidden name='confirmStockChange_id' value='$confirmStockChange_id'>";
	print "<input type=hidden name='DisItemIfNeg_id' value='$DisItemIfNeg_id'>";
	print "<input type=hidden name='useCommissionId' value='$useCommissionId'>";
	print "<input type=hidden name='commissionAccountNewId' value='$commissionAccountNewId'>";
	print "<input type=hidden name='commissionAccountUsedId' value='$commissionAccountUsedId'>";
	print "<input type=hidden name='customerCommissionAccountNewId' value='$customerCommissionAccountNewId'>";
	print "<input type=hidden name='customerCommissionAccountUsedId' value='$customerCommissionAccountUsedId'>";
	print "<input type=hidden name='ownCommissionAccountNewId' value='$ownCommissionAccountNewId'>";
	print "<input type=hidden name='ownCommissionAccountUsedId' value='$ownCommissionAccountUsedId'>";
	print "<input type=hidden name='defaultCommissionId' value='$defaultCommissionId'>";
	
	/*
	$text=findtekst(468,$sprog_id);
	$title=findtekst(469,$sprog_id);
	print "<tr><td title='$title'>$text</td><td title='$title'><SELECT class='inputbox' name='box1'>";
	$r=db_fetch_array(db_select("select * from grupper where art = 'SM' and kodenr = '$box1'",__FILE__ . " linje " . __LINE__));
	if ($box1) $value="S".$box1.":".$r['beskrivelse'];
	print "<option value='$box1'>$value</option>";
	$q=db_select("select * from grupper where art = 'SM' order by kodenr",__FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		$value="S".$r['kodenr'].":".$r['beskrivelse'];
		print "<option value='$r[kodenr]'>$value</option>";
	}
	print "<option></option>";
	print "</select></td></tr>";
	*/

	$text="Vis priser med moms på varekort";
	$title="Når dette felt er afmærket bliver varen vist incl. moms på varekortet";
	print "<tr><td title='$title'>$text</td>";
	print "<td title='$title'><input type='checkbox' class='inputbox' name='vatOnItemCard' $vatOnItemCard></td></tr>";
	print "<td><br></td><td><br></td><td><br></td>";

	$text="Bekræft ændring af beskrivelse på varekort";
	$title="Når dette felt er afmærket skal ændring af beskrivelse på varekort bekræftes";
	print "<tr><td title='$title'>$text</td>";
	print "<td title='$title'><input type='checkbox' class='inputbox' name='confirmDescriptionChange' $confirmDescriptionChange></td></tr>";
	$text="Kvittèr ved ændring af beholdning på varekort";
	$title="Når dette felt er afmærket skal kvitteres ved ændring af beholdning på varekort";
	print "<tr><td title='$title'>$text</td>";
	print "<td title='$title'><input type='checkbox' class='inputbox' name='confirmStockChange' $confirmStockChange></td></tr>";
	print "<td><br></td><td><br></td><td><br></td>";

	$text="Sæt vare til udgået, når beholdning bliver negativ";
	$title="Når dette felt er afmærket bliver varen markeret som udgået når beholdningen bliver negativ";
	print "<tr><td title='$title'>$text</td>";
	print "<td title='$title'><input type='checkbox' class='inputbox' name='DisItemIfNeg' $DisItemIfNeg></td></tr>";
	#chooseProvisionForProductGroup($defaultProvision);
	$text="Kommisionsvarer.<br>Afmærk hvis der anvendes POS og der sælges varer i kommission ";
	$title="Når dette felt er afmærket vises 'Afregn kommission på kasseoptælling' og 'Kommissionsvare' på varekort ";
	print "<tr><td title='$title'>$text</td>";
	print "<td title='$title'><input type='checkbox' class='inputbox' name='useCommission' $useCommission></td></tr>";
	if ($useCommission) {
		$text  = "Standard kommissionssats";
		$title = "Sættes en værdi her, anvendes denne sats som udgangspunkt ved oprettelse af varer.\n";
		$title.= "Er feltet tomt beregnes skal satses skrives manuelt.";
		list($a,$b) = explode("|",$numberFormat);
		print "<tr><td title='$title'>$text</td>";
		print "<td title='$title'><input type='text' style='width:50px;text-align:right;' class='inputbox' ";
		print "name='defaultCommission' value= '". $defaultCommission ."'></td></tr>";

		$text  = "Indtægtskonto for kommisionssalg, nye varer";
		$title = "Angiv den konto i kontoplanen hvor indtægter fra kommissionssalg af nye skal bogføres.\n";
		$title.= "Er feltet tomt skal beløbet overføres manuelt i en kassekladde.";
		print "<tr><td title='$title'>$text</td>";
		print "<td title='$title'><input type='text' style='width:75px;text-align:right;' class='inputbox' ";
		print "name='commissionAccountNew' value= '$commissionAccountNew'></td></tr>";

		$text  = "Afregningskonto for kommisionssalg, nye varer";
		$title = "Angiv den konto i kontoplanen hvorfra afregning for kommissionssalg af nye varer skal trækkes.\n";
		$title.= "Er feltet tomt skal beløbet overføres manuelt i en kassekladde.";
		print "<tr><td title='$title'>$text</td>";
		print "<td title='$title'><input type='text' style='width:75px;text-align:right;' class='inputbox' ";
		print "name='customerCommissionAccountNew' value= '$customerCommissionAccountNew'></td></tr>";

		$text  = "Egen konto for kommisionssalg, nye varer";
		$title = "Angiv den konto i kontoplanen hvorfra kommission af salg af nye varer skal trækkes.\n";
		$title.= "Er feltet tomt skal beløbet overføres manuelt i en kassekladde.";
		print "<tr><td title='$title'>$text</td>";
		print "<td title='$title'><input type='text' style='width:75px;text-align:right;' class='inputbox' ";
		print "name='ownCommissionAccountNew' value= '$ownCommissionAccountNew'></td></tr>";

		$text  = "Indtægtskonto for kommisionssalg, brugte varer";
		$title = "Angiv den konto i kontoplanen hvor indtægter fra kommissionssalg af brugte skal bogføres.\n";
		$title.= "Er feltet tomt skal beløbet overføres manuelt i en kassekladde.";
		print "<tr><td title='$title'>$text</td>";
		print "<td title='$title'><input type='text' style='width:75px;text-align:right;' class='inputbox' ";
		print "name='commissionAccountUsed' value= '$commissionAccountUsed'></td></tr>";

		$text  = "Afregningskonto for kommisionssalg, brugte varer";
		$title = "Angiv den konto i kontoplanen hvorfra afregning for kommissionssalg af brugte skal bogføres.\n";
		$title.= "Er feltet tomt skal beløbet overføres manuelt i en kassekladde.";
		print "<tr><td title='$title'>$text</td>";
		print "<td title='$title'><input type='text' style='width:75px;text-align:right;' class='inputbox' ";
		print "name='customerCommissionAccountUsed' value= '$customerCommissionAccountUsed'></td></tr>";

		$text  = "Egen konto for kommisionssalg, brugte varer";
		$title = "Angiv den konto i kontoplanen hvorfra kommission af salg af brugte varer skal trækkes.\n";
		$title.= "Er feltet tomt skal beløbet overføres manuelt i en kassekladde.";
		print "<tr><td title='$title'>$text</td>";
		print "<td title='$title'><input type='text' style='width:75px;text-align:right;' class='inputbox' ";
		print "name='ownCommissionAccountUsed' value= '$ownCommissionAccountUsed'></td></tr>";
		
		$text  = "Konverter eksisterende varer?";
		$title = "Denne funktion anvendes til konvertering af varer oprettet til brug med programmet Loppestatus hvor kostprisen ";
		$title.= "blev brugt som kommissions procent.\n";   
		$title = "Afmærkes dette felt ændres alle varer hvor salgspris er 0, kostpris er mellem 0,10 og 0,50 og vareummeret starter med ";
		$title.= " &apos;kb&apos; eller &apos;kn&apos;.\n";
		$title.= "Hvis kostprisen f.eks. er 0,15 blive denne ændret til 0.85 og kommisionssatsen bliver sat til 15%";
		$title.= "Kontakt os gerne for assistance - +45 4690 2208";
		print "<tr><td title='$title'>$text</td>";
		print "<td title='$title'><input type='checkbox' class='inputbox' name='convertExisting'></td></tr>";
		$text  = "Startdato for afregning til kommissionskunder?";
		$title = "Næste afregning til  kommissionskunder vil vlive beregnet fra og med den dato der angives her.\n";
		print "<tr><td title='$title'>$text</td>";
		print "<td title='$title'><input type='text' class='inputbox' style='width:75px;' name='comissionFromDate' ";
		print "value='$commissionFromDate' placeholder='01-01-2020'></td></tr>";
	}
	print "<td><br></td><td><br></td><td><br></td>";
	$text=findtekst(471,$sprog_id);
	print "<td align = center><input class='button green medium' type=submit accesskey='g' value='$text' name='submit'><!--tekst 471--></td>";
	print "<tr><td><br></td></tr>";
	print "</form>";
} # endfunc vare_valg

 
?>
