<?php
@session_start();
$s_id=session_id();

include("../includes/connect.php");
include("../includes/online.php");
include("../includes/std_func.php");
include("../includes/stykliste.php");

$i = 0;
$vareId=array();

$qtxt = "select id from varer where samlevare = 'on' and lukket = '0' order by id"; 
echo "$qtxt<br>";
$q = db_select($qtxt,__FILE__ . " linje " . __LINE__);
while ($r=db_fetch_array($q)) {
	$vareId[$i] = $r['id'];
	$i++;
}
for ($i=0; $i <count($vareId); $i++) {
echo "(float)stykliste($vareId[$i],0,'')<br>";
	$kostpris[$i]=(float)stykliste($vareId[$i],0,'');
echo "K $kostpris[$i]<br>";
	$qtxt = "update varer set kostpris = '$kostpris[$i]' where id = '$vareId[$i]'";
echo "$qtxt<br>";
  db_modify($qtxt,__FILE__ . " linje " . __LINE__);
}
?>
