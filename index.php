<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title>SALDI</title>
</head>
<noscript>
	<body>
		<h1>Saldi kr&aelig;ver JavaScript for at k&oslash;re.</h1>

		<p>Sl&aring; enten JavaScript til i din browsere 
		eller anskaf dig en browser, som underst&oslash;tter 
		JavaScript.
		</p>

</noscript>
<body onload="javascript:window.open('index/index.php', '', ',statusbar=0,menubar=0,titlebar=0,toolbar=0,scrollbars=1,resizable=1,top=0, left=0,width=+screen.widthheight=+screen.height,fullscreen=1');">
	
<?php
if ( stristr ($_SERVER["HTTP_HOST"], "saldi.dk" ) ) {
?>

<big>SALDI &aring;bnes i et nyt vindue</big><br>

<?php
} else {
?>
<script type="text/javascript"><!--
	
document.write("<h1>Saldi</h1>");
	
document.write("<p>");
document.write("I dette katalog er Saldi installeret, og selve systemet &aring;bnes i et nyt vindue.");
document.write("</p>");

document.write("<p>");
document.write("Hvis dette ikke sker, s&aring; har du nok sl&aring;et popup-vinduer fra i din browser. S&aring; sl&aring; ");
document.write("venligst acceptering af popup-vinduer til, n&aring;r de kommer fra den maskine, hvor Saldi ligger p&aring;. Ellers vil ");
document.write("Saldi ikke virke.");
document.write("</p>");

document.write("<h2>Opdateringskontrol</h2>");

document.write("<p>");
document.write("Her foretages en opdateringskontrol, som dog kr&aelig;ver adgang til ");
document.write("<a href=\"http://www.saldi.dk/\">Saldis hjemmesiden</a>. Hvis der ikke ");
document.write("fremkommer en ramme med opdateringsoplysninger efter dette, s&aring; er der ");
document.write("ingen forbindelse til <a href=\"http://www.saldi.dk/\">www.saldi.dk</a>, ");
document.write("og derved er det heller ikke muligt at kontrollere, om det er vigtigt at opgradere. ");
document.write("&Aring;rsagen kan v&aelig;re, at din maskine ikke har adgang til internettet.");
document.write("</p>");

saldi_version = "2.0.4";
saldi_hovedversion = "2.0";

// Frigivelsesdato og -time. Angives som ����MMDDTT - Kan muligvis give problemer efter &aring;r 2147.
saldi_frigivet = "2009020300"; 
saldi_besked_farve = "#ccc";

//--></script>

<script type="text/javascript" src="http://www.saldi.dk/js/opdateringskontrol/saldi_2_0_6.js" ></script>

<h2>Mere om Saldi</h2>

<p>Se mere om Saldi p&aring; projektets hjemmeside 
<a href="http://www.saldi.dk/">www.saldi.dk</a>.
</p>

<hr />

<p style="text-align:center">&copy; <a href="http://www.danosoft.dk/">DANOSOFT ApS</a></p>


<?php
}
?>

</body>
</html>
