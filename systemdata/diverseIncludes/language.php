<?php

function language () {
	include("../includes/languages.php"); #20210112
	//include("../includes/connect.php");
	
	global $sprog_id; 
	global $bgcolor;
	global $bgcolor5;
	global $bruger_id;
	global $brugernavn;
	$csvfile = "../importfiler/tekster.csv";
	$g1 =csv_to_array($csvfile);
	$x=0;
	
	$q = db_select("select*from settings where var_grp = 'localization' order by var_name ASC",__FILE__ . " linje " . __LINE__);
					while ($r = db_fetch_array($q)) {
					$id[$x]=$r['id'];
					$beskrivelse[$x]=$r['var_name'];
					$kodenr[$x]=$r['var_value'];
					//$sprogkode[$x]=$r['box1'];
					
					$x++;
				}
				
	$antal_sprog=$x;
	print "<form name=diverse action=diverse.php?sektion=sprog method=post>";
	print "<tr><td colspan='6'><hr></td></tr>";
	print "<tr bgcolor='$bgcolor5'><td colspan='6'><b><u>".findtekst(801,$sprog_id)."</b></u></td></tr>"; // 20210303
	print "<tr><td colspan='6'><br></td></tr>";

	if (isset($_POST['newLanguageName']) && $_POST['newLanguageName'])  {
		
		if (in_array($_POST['newLanguageName'],$beskrivelse)) {
			$var_name = $_POST['newLanguageName'];	
			$m = db_select("select * from settings where var_name = '$var_name'",__FILE__ . " linje " . __LINE__);
			$n = db_fetch_array($m);

			$sprog_id= $n['id'];
		
			db_modify("update brugere set sprog_id = '$sprog_id' where id ='$bruger_id'",__FILE__ . " linje " . __LINE__);				
			alert ($_POST['newLanguageName']." eksisterer allerede");
			print "<meta http-equiv=\"refresh\" content=\"0;URL=../systemdata/diverse.php?sektion=sprog\">\n";  
		} else {
			if($_POST['newLanguageName']=='English'){
				$newar = engdan($g1, "English");
				$var_name = $_POST['newLanguageName'];
				db_modify("insert into settings (var_name,  var_grp , var_value , var_description, user_id)values('$var_name','localization','$var_name','$var_name language', 0)",__FILE__ . " linje " . __LINE__);
				$m = db_select("select * from settings where var_name = '$var_name'",__FILE__ . " linje " . __LINE__);
				$n = db_fetch_array($m);
				$sprog_id= $n['id'];
				db_modify("update brugere set sprog_id = '$sprog_id' where id ='$bruger_id'",__FILE__ . " linje " . __LINE__);
				foreach ($newar as $key => $value){
					 $g=str_replace("'","''", $value);
                     $h =trim($key, " ");
					 if($g!==""){

				       db_modify("insert into tekster ( sprog_id, tekst_id, tekst )  values ('$sprog_id', '$h', '$g')",__FILE__ . " linje " . __LINE__);						
					 }
				}

				echo "opretter ". $_POST['newLanguageName']."<br>";
				print "<meta http-equiv=\"refresh\" content=\"0;URL=../systemdata/diverse.php?sektion=sprog\">\n"; 

			}elseif($_POST['newLanguageName']=='Danish'){
	
				$newar = engdan($g1,"Danish"); // 20210303
				$var_name = $_POST['newLanguageName'];
				db_modify("insert into settings (var_name,  var_grp , var_value , var_description, user_id)values('$var_name','localization','$var_name','$var_name language', 0)",__FILE__ . " linje " . __LINE__);
				$m = db_select("select * from settings where var_name = '$var_name'",__FILE__ . " linje " . __LINE__);
				$n = db_fetch_array($m);
				$sprog_id= $n['id'];
				db_modify("update brugere set sprog_id = '$sprog_id' where id ='$bruger_id'",__FILE__ . " linje " . __LINE__);
				foreach ($newar as $key => $value){
					$g=str_replace("'","''", $value);
					$h =trim($key, " ");
					if($g!==""){

					  db_modify("insert into tekster ( sprog_id, tekst_id, tekst )  values ('$sprog_id', '$h', '$g')",__FILE__ . " linje " . __LINE__);						
					}
			    }

				    echo "opretter ". $_POST['newLanguageName']."<br>";
					print "<meta http-equiv=\"refresh\" content=\"0;URL=../systemdata/diverse.php?sektion=sprog\">\n"; 
	
			} 	  
        } 
    }elseif(isset($_POST['sprog']) && $_POST['sprog']=='English'|| $_POST['sprog']=='Danish') { // 20210224
     
  
   //update here
            $var_name = $_POST['sprog'];	
			$m = db_select("select * from settings where var_name = '$var_name'",__FILE__ . " linje " . __LINE__);
			$n = db_fetch_array($m);

			$sprog_id= $n['id'];
		
			db_modify("update brugere set sprog_id = '$sprog_id' where id ='$bruger_id'",__FILE__ . " linje " . __LINE__);				
			alert ($_POST['newLanguageName']." eksisterer allerede");
			print "<meta http-equiv=\"refresh\" content=\"0;URL=../systemdata/diverse.php?sektion=sprog\">\n"; 

	}

	if (isset($_POST['sprog']) && $_POST['sprog']=='addLanguage') {
		// add language
    

		print "<tr><td title='Hvilket sprog vil du tilføje'>Nyt sprog</td>";
		#print "<td title='Hvilket sprog vil du tilføje'><input name='newLanguageName' type='text' class='inputbox' style='width:200px'></td></tr>";
 #####
		print"<td> <SELECT class ='inputbox' NAME = 'newLanguageName' title=''>";
		foreach ($languages as $k => $v) {

			print "<option  value='$v'>$v</option>";


		}


		print "</SELECT></td></tr>";


 #####
		print "<tr><td title='Hvilket sprog vil du anvende som skabelon'>".findtekst(803,$sprog_id)."</td>";
		print "<td><SELECT class='inputbox' NAME='languageTemplate' title=''>";
#		if ($box3[$x]) print"<option>$box3[$x]</option>";
		for ($x=0; $x<$antal_sprog; $x++) {
			print "<option>$beskrivelse[$x]</option>";
		}
		print "</SELECT></td></tr>";
	} else {
		$tekst1=findtekst(1,$sprog_id);
		$tekst2=findtekst(2,$sprog_id);
		print "<tr><td title='Klik her for at rette tekster'><a href=tekster.php?sprog_id=1>$tekst1</a></td>";
		print "<td><SELECT class='inputbox' NAME='sprog' title='$tekst2'>";
#		if ($box3[$x]) print"<option>$box3[$x]</option>";
		for ($x=0; $x<$antal_sprog; $x++) {
			print "<option>$beskrivelse[$x]</option>";
		}
		$tekst1=findtekst(4,$sprog_id);
		print "<option value='addLanguage'>$tekst1</option>";
		print "</SELECT></td></tr>";
	}
    print "<tr><td><br></td></tr>";
    
	$tekst1=findtekst(3,$sprog_id);
	print "<tr><td align = right colspan='4'><input class='button green medium' type=submit value='$tekst1' name='submit'></td></tr>";
 #	print "<td align = center><input type=submit value='$tekst2' name='submit'></td>";
 #	print "<td align = center><input type=submit value='$tekst3' name='submit'></td><tr>";
 /*
	print "</tbody></table></td></tr>";
 */
	print "</form>";
} # endfunc sprog
 
?>
