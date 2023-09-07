<?php
$id=$_GET['id'];
$filnavn=$id."_ok.php";
unlink($filnavn);
$txt="Betalingen er godkendt";
print "<BODY onload=\"javascript:alert('$txt')\">";
?>
 
