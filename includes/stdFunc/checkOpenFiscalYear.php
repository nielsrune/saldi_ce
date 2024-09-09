<?php
function checkOpenFiscalYear($date) {
	# Er regnskabsåret åben
	$i = 0;
	$ym = substr($date,0,6);
	$open = 0;
	$qtxt = "select * from grupper where art = 'RA' and box5 = 'on' order by kodenr"; // #box5 = accounting allowed
	$q = db_select($qtxt, __FILE__ . " linje " . __LINE__);
	while ($r = db_fetch_array($q)) {
		$startdate[$i] = $r['box2'].$r['box1'];
		$enddate[$i]   = $r['box4'].$r['box2'];
		if ($ym > $startdate[$i] && $ym < $enddate[$i]) $open =1;
	}
	return ($open);
}
?>
